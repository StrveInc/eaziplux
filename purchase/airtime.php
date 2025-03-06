<?php
session_start();

// Initialize variables
$account_balance = 0;
$user_id = null;
$responseMessage = null;

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = "";
    $database = "eaziplux";

    $conn = new mysqli($servername, $dbusername, $dbpassword, $database);

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

// Check if "number", "amount", and "item" keys are set in the $_POST array
$number = isset($_POST["number"]) ? filter_var($_POST["number"], FILTER_SANITIZE_STRING) : null;
$amount = isset($_POST["amount"]) ? filter_var($_POST["amount"], FILTER_SANITIZE_NUMBER_INT) : null;
$item = isset($_POST["item"]) ? filter_var($_POST["item"], FILTER_SANITIZE_STRING) : null;

// Determine the serviceID based on the item
$serviceID = null;
switch (strtolower($item)) {
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
        $responseMessage = "Invalid item specified.";
        $_SESSION['message'] = $responseMessage;
        header("Location: ../failed.php");
        exit;
}

// Check if the account balance is sufficient
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    if (!is_numeric($amount) || $amount < 100 || !preg_match('/^\d{11}$/', $number)) {
        $responseMessage = "Invalid input. Please enter a numeric amount above 100 and a valid 11-digit phone number.";
        $_SESSION['message'] = $responseMessage;
        header("Location: ../failed.php");
        exit;
    } elseif ($account_balance < $amount) {
        $responseMessage = 'Transaction failed: Fund your eaziplux wallet.';
        $transaction_description = "Insufficient Funds";
        $transaction_status = "failed";
        $transaction_type = "Airtime";
        $receiver = $number;
        $transaction_time = date("F j, Y \a\\t g:i A");
        $transaction_id = "EP".time(); // Combine user ID and timestamp

        // Insert failed transaction details into the transaction_history table
        $log_transaction_query = "INSERT INTO transaction_history (user_id, transaction_id, amount, description, status, item, transaction_type, receiver, transaction_time) 
        VALUES ('$user_id', '$transaction_id', $amount, '$transaction_description', '$transaction_status', '$item', '$transaction_type', '$receiver', '$transaction_time')";
        if ($conn->query($log_transaction_query) !== TRUE) {
            $_SESSION['message'] = "Error logging transaction: " . $conn->error;
            header("Location: ../failed.php");
            exit;
        }

        $_SESSION['message'] = $responseMessage;
        header("Location: ../failed.php");
        exit;
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
                CURLOPT_POSTFIELDS => array('serviceID' => $serviceID, 'api' => 'ap_3f856a5b46bb740150d03c990ce2f5d7', 'amount' => $amount, 'phone' => $number),
                CURLOPT_HTTPHEADER => array(
                    'api: Bearer ap_3f856a5b46bb740150d03c990ce2f5d7'
                ),
            )
        );

        $responses = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($responses, true);

        if ($response['status'] === 'TRANSACTION_FAILED') {
            $transaction_description = "Error in Transaction";
            $transaction_status = "Failed";
            $transaction_type = "Airtime";
            $receiver = $number;
            $transaction_time = date("F j, Y \a\\t g:i A");
            $transaction_id = "EP".time(); // Combine user ID and timestamp

            // Insert failed transaction details into the transaction_history table
            $log_transaction_query = "INSERT INTO transaction_history (user_id, transaction_id, amount, description, status, item, transaction_type, receiver, transaction_time) 
            VALUES ('$user_id', '$transaction_id', $amount, '$transaction_description', '$transaction_status', '$item', '$transaction_type', '$receiver', '$transaction_time')";
            if ($conn->query($log_transaction_query) !== TRUE) {
                $_SESSION['message'] = "Error logging transaction: " . $conn->error;
                header("Location: ../failed.php");
                exit;
            }

            $responseMessage = 'Transaction failed: ' . $response['description'];
            $_SESSION['message'] = $responseMessage;
            header("Location: ../failed.php");
            exit;
        } else {
            // Deduct the amount from the user's balance
            $new_balance = intval($account_balance) - intval($amount);

            // Update the balance in the database using prepared statement
            $update_balance_query = "UPDATE virtual_accounts SET balance = ? WHERE acct_id = ?";
            $stmt = $conn->prepare($update_balance_query);
            $stmt->bind_param("ds", $new_balance, $user_id);
            $stmt->execute();

            $responseMessage = 'Successfully purchased ' . strtoupper($item) . ' airtime worth of ₦' . $amount;

            $transaction_description = "Transaction Successful";
            $transaction_status = "Successful";
            $transaction_type = "Airtime";
            $receiver = $number;
            $transaction_time = date("F j, Y \a\\t g:i A");
            $transaction_id = "EP".time(); // Combine user ID and timestamp

            // Insert successful transaction details into the transaction_history table
            $log_transaction_query = "INSERT INTO transaction_history (user_id, transaction_id, amount, description, status, item, transaction_type, receiver, transaction_time) 
            VALUES ('$user_id', '$transaction_id', $amount, '$transaction_description', '$transaction_status', '$item', '$transaction_type', '$receiver', '$transaction_time')";
            if ($conn->query($log_transaction_query) !== TRUE) {
                $_SESSION['message'] = "Error logging transaction: " . $conn->error;
                header("Location: ../failed.php");
                exit;
            }

            $_SESSION["message"] = $responseMessage;
            header("Location: ../success.php");
            exit;
        }
    }
}
?>
