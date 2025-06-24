<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'config.php';
$log_file = 'webhook_error.log';

// Paystack Secret Key for Signature Verification
$secretKey = $_ENV['PK_SECRET']; // Replace with your real secret key

// Read raw input
$input = file_get_contents("php://input");
file_put_contents($log_file, "RAW INPUT: " . $input . PHP_EOL, FILE_APPEND);

// Verify signature
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
$computedSignature = hash_hmac('sha512', $input, $secretKey);

if ($signature !== $computedSignature) {
    file_put_contents($log_file, "Invalid signature. Rejecting request.\n", FILE_APPEND);
    http_response_code(401);
    exit("Invalid signature");
}

// Database connection


if ($conn->connect_error) {
    file_put_contents($log_file, "DB connection failed: " . $conn->connect_error . PHP_EOL, FILE_APPEND);
    http_response_code(500);
    exit("DB connection failed");
}
$conn->autocommit(true);

// Decode JSON
$data = json_decode($input, true);

// Extract data
$reference = $data['data']['reference'] ?? null;
$amount = isset($data['data']['amount']) ? floatval($data['data']['amount']) / 100 : 0;
$userEmail = $data['data']['customer']['email'] ?? null;
$status = $data['data']['status'] ?? 'success';

// Variables
$user_id = null;
$is_giftcard = false;
$converted_amount = null;

// Check for giftcard reference
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

// Fallback: get user_id by email
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

file_put_contents($log_file, "DEBUG: reference=$reference, amount=$amount, userEmail=$userEmail, user_id=$user_id\n", FILE_APPEND);

if ($user_id && $amount > 0) {
    $credit_amount = ($is_giftcard && $converted_amount) ? $converted_amount : $amount;

    $stmt = $conn->prepare("UPDATE virtual_accounts SET balance = balance + ? WHERE acct_id = ?");
    if (!$stmt) {
        file_put_contents($log_file, "Prepare failed: " . $conn->error . PHP_EOL, FILE_APPEND);
        http_response_code(500);
        exit("Prepare failed");
    }
    $stmt->bind_param("ds", $credit_amount, $user_id);
    if (!$stmt->execute()) {
        file_put_contents($log_file, "Execute failed: " . $stmt->error . PHP_EOL, FILE_APPEND);
        http_response_code(500);
        exit("Execute failed");
    }
    $affected = $stmt->affected_rows;
    $stmt->close();

    // Log transaction
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

    // Mark giftcard request as completed
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

    if ($affected > 0) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Balance updated']);
    } else {
        file_put_contents($log_file, "No update: user_id=$user_id, amount=$credit_amount\n", FILE_APPEND);
        http_response_code(200); // Always return 200 to avoid retries
        echo json_encode(['status' => 'fail', 'message' => 'No update made']);
    }
} else {
    file_put_contents($log_file, "Invalid data: " . json_encode($data) . PHP_EOL, FILE_APPEND);
    http_response_code(200); // Always return 200 to stop retries
    echo json_encode(['status' => 'fail', 'message' => 'Invalid data']);
}

$conn->close();
?>
