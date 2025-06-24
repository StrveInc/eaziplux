<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$log_file = 'webhook_error.log';

// Database connection
include 'config.php';

if ($conn->connect_error) {
    file_put_contents($log_file, "DB connection failed: " . $conn->connect_error . PHP_EOL, FILE_APPEND);
    http_response_code(500);
    exit("DB connection failed");
}
$conn->autocommit(true);

// Get webhook data
$data = json_decode(file_get_contents("php://input"), true);

// Extract from Paystack structure
$reference = isset($data['data']['reference']) ? $data['data']['reference'] : null;
$amount = isset($data['data']['amount']) ? floatval($data['data']['amount']) / 100 : 0; // Paystack sends amount in kobo
$userEmail = isset($data['data']['customer']['email']) ? $data['data']['customer']['email'] : null;
$status = isset($data['data']['status']) ? $data['data']['status'] : 'success';

// Find user_id and check if it's a giftcard funding
$user_id = null;
$is_giftcard = false;
$converted_amount = null;

// Check if reference matches a giftcard order
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

// If not a giftcard, try to get user_id by email
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
    // Credit user: use converted_amount for giftcard, else use amount
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

    // Log transaction to transaction_history
    $description = $is_giftcard ? "Giftcard funding" : "Wallet funding";
    $item = "funding";
    $transaction_type = "credit";
    $receiver = $userEmail;
    $transaction_time = date('Y-m-d H:i:s');
    $transaction_id = $reference; // Use Paystack reference as transaction_id

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

    // If giftcard, update status to completed
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
        http_response_code(400);
        echo json_encode(['status' => 'fail', 'message' => 'User not found or no update']);
    }
} else {
    file_put_contents($log_file, "Invalid data: " . json_encode($data) . PHP_EOL, FILE_APPEND);
    http_response_code(400);
    echo json_encode(['status' => 'fail', 'message' => 'Invalid data']);
}

$conn->close();
?>