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
function handleTransactionFailure($conn, $user_id, $amount, $item, $number, $description = "Error in Transaction", $status = "Failed") {
    $transaction_id = "EP".time();
    logTransaction($conn, $user_id, $transaction_id, $amount, $description, $status, $item, $item, $number);
    header("Location: ../success.php");
    exit;
}

// Check if "number", "amount", and "item" keys are set in the $_POST array
$number = isset($_POST["customer"]) ? filter_var($_POST["customer"], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
$amount = isset($_POST["amount"]) ? filter_var($_POST["amount"], FILTER_SANITIZE_NUMBER_INT) : null;
$item = isset($_POST["item"]) ? filter_var($_POST["item"], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
$network = isset($_POST["network"]) ? filter_var($_POST["network"], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
$meter = isset($_POST["meter"]) ? filter_var($_POST["meter"], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;



// Check if the account balance is sufficient and process the transaction
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    $total_amount = is_numeric($amount) ? $amount + 50 : $amount; // Add 50 only for balance check and deduction

    if (!is_numeric($amount) || $amount < 100 || !preg_match('/^\d{11}$/', $number)) {
        $_SESSION['status_type'] = 'failure';
        $_SESSION['status_message'] = 'Electricity token purchase failed. Please try again.';
        header("Location: ../success.php");
        exit;
    } elseif ($account_balance < $total_amount) {
        $_SESSION['status_message'] = 'Insufficient funds for this transaction.';
        handleTransactionFailure($conn, $user_id, $total_amount, $network, $meter, "Insufficient Funds", "Failed");
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
                    'serviceID' => $network,
                    'api' => 'ap_3f856a5b46bb740150d03c990ce2f5d7',
                    'amount' => $amount, // Send original amount to API
                    'phone' => $number,
                    'customerID' => $meter,
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
            logTransaction($conn, $user_id, "EP".time(), $total_amount, "Server error!", "Failed", $network, $item, $meter);
            $_SESSION['status_type'] = 'failure';
            $_SESSION['status_message'] = 'Server not responding. Try again!';
            header("Location: ../success.php");
        } else {
            // Deduct the total amount (amount + 50) from the user's balance
            $new_balance = intval($account_balance) - intval($total_amount);

            // Update the balance in the database using prepared statement
            $update_balance_query = "UPDATE virtual_accounts SET balance = ? WHERE acct_id = ?";
            $stmt = $conn->prepare($update_balance_query);
            $stmt->bind_param("ds", $new_balance, $user_id);
            $stmt->execute();
            $stmt->close();

            $transaction_id = "EP".time();
            logTransaction($conn, $user_id, $transaction_id, $total_amount, "Transaction Successful", "Successful", $network, $item, $number);

            $_SESSION['status_type'] = 'success';
            $_SESSION['status_message'] = 'Airtime purchase successful!';
            header("Location: ../success.php");
            exit;
        }
    }
}
?>
