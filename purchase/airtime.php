<?php
session_start();

include '../config.php'; // Include your database connection details

// Initialize variables
$account_balance = 0;
$user_id = null;
$responseMessage = null;

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch user ID using prepared statement
    $user_id_query = "SELECT user_id FROM users WHERE username = ?";
    $stmt = $conn->prepare($user_id_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $user_id = $row["user_id"];
        $stmt->close();

        // Fetch balance using prepared statement
        $balance_query = "SELECT balance FROM virtual_accounts WHERE acct_id = ?";
        $stmt = $conn->prepare($balance_query);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $balance_result = $stmt->get_result();

        if ($balance_result->num_rows == 1) {
            $balance_row = $balance_result->fetch_assoc();
            $account_balance = $balance_row["balance"];
        }
        $stmt->close();
    }
} else {
    header("Location: ../home/login.php");
    exit;
}

// Transaction logging helper (no transaction_time)
function logTransaction($conn, $user_id, $transaction_id, $amount, $description, $status, $item, $transaction_type, $receiver) {
    $query = "INSERT INTO transaction_history (user_id, transaction_id, amount, description, status, item, transaction_type, receiver)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssdsssss", $user_id, $transaction_id, $amount, $description, $status, $item, $transaction_type, $receiver);
    $stmt->execute();
    $stmt->close();
}

// Handle transaction failure
function handleTransactionFailure($conn, $user_id, $amount, $item, $number, $description = "Error in Transaction", $status = "failure") {
    $transaction_id = "EP".time();
    logTransaction($conn, $user_id, $transaction_id, $amount, $description, $status, $item, "Airtime", $number);
    header("Location: ../success.php");
    exit;
}

// Check if "number", "amount", and "item" keys are set in the $_POST array
$number = isset($_POST["number"]) ? filter_var($_POST["number"], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
$amount = isset($_POST["amount"]) ? filter_var($_POST["amount"], FILTER_SANITIZE_NUMBER_INT) : null;
$item = isset($_POST["item"]) ? filter_var($_POST["item"], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
$network = isset($_POST["network"]) ? filter_var($_POST["network"], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;

// Determine the serviceID based on the network
$serviceID = null;
switch (strtolower($network)) {
    case '9mobile':
        $serviceID = 'etisalat';
        break;
    case 'mtn':
        $serviceID = 'mtn';
        break;
    case 'airtel':
        $serviceID = 'airtel';
        break;
    case 'glo':
        $serviceID = 'glo';
        break;
    default:
        $_SESSION['status_message'] = "Invalid network specified.";
        $_SESSION['status_type'] = 'failure';
        header("Location: ../success.php");
        exit;
}

// Check if the account balance is sufficient and process the transaction
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    if (!is_numeric($amount) || $amount < 100 || !preg_match('/^\d{11}$/', $number)) {
        $_SESSION['status_type'] = 'failure';
        $_SESSION['status_message'] = 'Airtime purchase failed. Please try again.';
        header("Location: ../success.php");
        exit;
    }

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Lock the user's balance row
        $stmt = $conn->prepare("SELECT balance FROM virtual_accounts WHERE acct_id = ? FOR UPDATE");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $balance_result = $stmt->get_result();

        if ($balance_result->num_rows == 1) {
            $balance_row = $balance_result->fetch_assoc();
            $current_balance = $balance_row["balance"];

            if ($current_balance < $amount) {
                $conn->rollback();
                $_SESSION['status_message'] = 'Insufficient funds for this transaction.';
                handleTransactionFailure($conn, $user_id, $amount, $network, $number, "Insufficient Funds", "failure");
            } else {
                // Proceed with the transaction via the API
                $curl = curl_init();
                curl_setopt_array(
                    $curl,
                    array(
                        CURLOPT_URL => 'https://gsubz.com/api/pay/',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => array(
                            'serviceID' => $serviceID,
                            'api' => $_ENV['GSUBZ'],
                            'amount' => $amount,
                            'phone' => $number
                        ),
                        CURLOPT_HTTPHEADER => array(
                            'api: Bearer ap_3f856a5b46bb740150d03c990ce2f5d7'
                        ),
                    )
                );

                $responses = curl_exec($curl);
                curl_close($curl);

                $response = json_decode($responses, true);

                if (isset($response['status']) && $response['status'] === 'TRANSACTION_FAILED') {
                    $conn->rollback();
                    logTransaction($conn, $user_id, "EP".time(), $amount, "Transaction Failed", "Server Error!", $network, "Airtime", $number);
                    $_SESSION['status_type'] = 'failure';
                    $_SESSION['status_message'] = 'Server not responding. Try again!';
                    header("Location: ../success.php");
                    exit;
                } else {
                    // Deduct the amount from the user's balance
                    $new_balance = intval($current_balance) - intval($amount);

                    // Update the balance in the database using prepared statement
                    $update_balance_query = "UPDATE virtual_accounts SET balance = ? WHERE acct_id = ?";
                    $stmt2 = $conn->prepare($update_balance_query);
                    $stmt2->bind_param("ds", $new_balance, $user_id);
                    $stmt2->execute();
                    $stmt2->close();

                    $transaction_id = "EP".time();
                    logTransaction($conn, $user_id, $transaction_id, $amount, "Transaction Successful", "Successful", $network, "Airtime", $number);

                    $conn->commit();

                    $_SESSION['status_type'] = 'success';
                    $_SESSION['status_message'] = 'Airtime purchase successful!';
                    header("Location: ../success.php");
                    exit;
                }
            }
        } else {
            $conn->rollback();
            $_SESSION['status_type'] = 'failure';
            $_SESSION['status_message'] = 'Account not found.';
            header("Location: ../success.php");
            exit;
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['status_type'] = 'failure';
        $_SESSION['status_message'] = 'Transaction error. Please try again.';
        header("Location: ../success.php");
        exit;
    }
}
?>
