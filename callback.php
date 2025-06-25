<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *"); // CORS header added

include 'config.php';
$log_file = 'webhook_logger.log';

// Log webhook hit
file_put_contents($log_file, "Webhook hit at " . date("Y-m-d H:i:s") . PHP_EOL, FILE_APPEND);

// Log headers
$headers = getallheaders();
file_put_contents($log_file, "HEADERS: " . print_r($headers, true) . PHP_EOL, FILE_APPEND);

// Respond to Paystack immediately
http_response_code(200);
ignore_user_abort(true);
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}

// Load secret key
$secretKey = $_ENV['PK_SECRET'] ?? getenv('PK_SECRET') ?? 'sk_live_XXXXXX';
file_put_contents($log_file, "Secret Key Used: " . $secretKey . PHP_EOL, FILE_APPEND);

// Read raw input
$input = file_get_contents("php://input");
file_put_contents($log_file, "RAW INPUT: " . $input . PHP_EOL, FILE_APPEND);

// Verify signature
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
$computedSignature = hash_hmac('sha512', $input, $secretKey);

file_put_contents($log_file, "Signature Received: $signature" . PHP_EOL, FILE_APPEND);
file_put_contents($log_file, "Computed Signature: $computedSignature" . PHP_EOL, FILE_APPEND);

if ($signature !== $computedSignature) {
    file_put_contents($log_file, "Invalid signature. Rejecting request.\n", FILE_APPEND);
    exit("Invalid signature");
}

// Check DB connection
if ($conn->connect_error) {
    file_put_contents($log_file, "DB connection failed: " . $conn->connect_error . PHP_EOL, FILE_APPEND);
    exit("DB connection failed");
}
$conn->autocommit(true);

// Parse payload
$data = json_decode($input, true);

// Extract transaction info
$reference = $data['data']['reference'] ?? null;
$amount = isset($data['data']['amount']) ? floatval($data['data']['amount']) / 100 : 0;
$userEmail = $data['data']['customer']['email'] ?? null;
$status = $data['data']['status'] ?? 'success';

// Initialize
$user_id = null;
$is_giftcard = false;
$converted_amount = null;

// Giftcard check
if ($reference) {
    $stmt = $conn->prepare("SELECT user_id, converted_amount FROM giftcard_requests WHERE reference = ? LIMIT 1");
    $stmt->bind_param("s", $reference);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];
        $converted_amount = (float)$row['converted_amount'];
        $is_giftcard = true;
    }
    $stmt->close();
}

// Fallback: Get user by email
if (!$user_id && $userEmail) {
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];
    }
    $stmt->close();
}

file_put_contents($log_file, "DEBUG: reference=$reference, amount=$amount, email=$userEmail, user_id=$user_id\n", FILE_APPEND);

// Proceed with wallet update
if ($user_id && $amount > 0) {
    $credit_amount = ($is_giftcard && $converted_amount) ? $converted_amount : $amount - 20;

    $stmt = $conn->prepare("UPDATE virtual_accounts SET balance = balance + ? WHERE acct_id = ?");
    if (!$stmt) {
        file_put_contents($log_file, "Balance update prepare failed: " . $conn->error . PHP_EOL, FILE_APPEND);
        exit("Prepare failed");
    }

    $stmt->bind_param("ds", $credit_amount, $user_id);
    if (!$stmt->execute()) {
        file_put_contents($log_file, "Balance update execute failed: " . $stmt->error . PHP_EOL, FILE_APPEND);
        exit("Execute failed");
    }
    $stmt->close();

    // Transaction history logging
    $description = $is_giftcard ? "Giftcard funding" : "Wallet funding";
    $item = "funding";
    $transaction_type = "credit";
    $receiver = $userEmail;
    $transaction_time = date('Y-m-d H:i:s');
    $transaction_id = $reference;

    $log_stmt = $conn->prepare("INSERT INTO transaction_history (transaction_id, user_id, amount, description, status, item, transaction_type, receiver, transaction_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($log_stmt) {
        $log_stmt->bind_param(
            "ssdssssss",
            $transaction_id,
            $user_id,
            $credit_amount,
            $description,
            $status,
            $item,
            $transaction_type,
            $receiver,
            $transaction_time
        );
        if (!$log_stmt->execute()) {
            file_put_contents($log_file, "Transaction log failed: " . $log_stmt->error . PHP_EOL, FILE_APPEND);
        }
        $log_stmt->close();
    } else {
        file_put_contents($log_file, "Transaction log prepare failed: " . $conn->error . PHP_EOL, FILE_APPEND);
    }

    // Update giftcard status
    if ($is_giftcard) {
        $update = $conn->prepare("UPDATE giftcard_requests SET status = 'completed' WHERE reference = ?");
        if ($update) {
            $update->bind_param("s", $reference);
            if (!$update->execute()) {
                file_put_contents($log_file, "Giftcard status update failed: " . $update->error . PHP_EOL, FILE_APPEND);
            }
            $update->close();
        } else {
            file_put_contents($log_file, "Giftcard status update prepare failed: " . $conn->error . PHP_EOL, FILE_APPEND);
        }
    }

    file_put_contents($log_file, "SUCCESS: Credited $credit_amount to user_id $user_id\n", FILE_APPEND);
} else {
    file_put_contents($log_file, "FAIL: Invalid user or amount. Data: " . json_encode($data) . PHP_EOL, FILE_APPEND);
}

$conn->close();
exit("Webhook processed");

?>
