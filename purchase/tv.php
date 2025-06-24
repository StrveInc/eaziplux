<?php
session_start();

include '../config.php';

// Check DB connection
if ($conn->connect_errno) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user info from session
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Fetch user ID
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $user_id = $row["user_id"];

        // Fetch user balance
        $balance_query = "SELECT balance FROM virtual_accounts WHERE acct_id = ?";
        $stmt = $conn->prepare($balance_query);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $balance_result = $stmt->get_result();

        if ($balance_result->num_rows == 1) {
            $balance_row = $balance_result->fetch_assoc();
            $account_balance = $balance_row["balance"];
        } else {
            $account_balance = 0;
        }
    } else {
        $_SESSION['message'] = "User not found.";
        header("Location: ../failed.php");
        exit;
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
function handleTransactionFailure($conn, $user_id, $amount, $item, $receiver, $description = "Error in Transaction", $status = "Failed") {
    $transaction_id = "TV" . $user_id . time();
    logTransaction($conn, $user_id, $transaction_id, $amount, $description, $status, $item, "TV", $receiver);

    $_SESSION['message'] = "Transaction failed: $description";
    header("Location: ../failed.php");
    exit;
}

// Handle POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    $serviceID = isset($_POST['serviceID']) ? $_POST['serviceID'] : 'dstv';
    $apiKey = "ap_83c2ee17ca19e2c34e29a0f17cd5bd89";
    $authHeader = "Authorization: Bearer $apiKey";
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $amount = isset($_POST['amount']) ? intval($_POST['amount']) : 0;
    $customerID = isset($_POST['customerID']) ? $_POST['customerID'] : '';
    $variation = isset($_POST['variation']) ? $_POST['variation'] : '';
    $item = isset($_POST['item']) ? $_POST['item'] : 'TV SUBSCRIPTION';
    // Only add 50 for balance check and deduction, not for API call
    $total_amount = $amount + 50;

    // Check balance
    if ($account_balance < $total_amount) {
        handleTransactionFailure($conn, $user_id, $total_amount, $item, $customerID, "Insufficient Funds", "Failed");
    }

    // Prepare cURL POST
    $curl = curl_init();
    $postFields = [
        'serviceID' => $serviceID,
        'api' => $apiKey,
        'phone' => $phone,
        'amount' => $amount, // Send original amount to API
        'customerID' => $customerID
    ];
    if (!empty($variation)) {
        $postFields['variation'] = $variation;
    }

    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://gsubz.com/api/pay/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_HTTPHEADER => [$authHeader],
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    $responseData = json_decode($response, true);

    // Handle API response
    if (isset($responseData['status']) && strtolower($responseData['status']) === 'successful') {
        // Deduct balance (amount + 50)
        $new_balance = $account_balance - $total_amount;
        $update_balance_query = "UPDATE virtual_accounts SET balance = ? WHERE acct_id = ?";
        $stmt = $conn->prepare($update_balance_query);
        $stmt->bind_param("ds", $new_balance, $user_id);
        $stmt->execute();
        $stmt->close();

        $transaction_id = "TV" . $user_id . time();
        logTransaction($conn, $user_id, $transaction_id, $total_amount, "Transaction Successful", "Successful", $item, "TV", $customerID);

        $_SESSION['message'] = "You have successfully purchased ₦$amount worth of $item for $customerID";
        header("Location: ../success.php");
        exit;
    } else {
        $failMsg = isset($responseData['description']) ? $responseData['description'] : (isset($responseData['message']) ? $responseData['message'] : "Service Down");
        handleTransactionFailure($conn, $user_id, $total_amount, $item, $customerID, $failMsg, "Failed");
    }
}
?>


