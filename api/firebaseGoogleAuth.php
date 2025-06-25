<?php
session_start();
header("Content-Type: application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config.php';

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}
    
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!isset($data['email']) || !isset($data['username']) || !isset($data['firebase_uid'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

$email = $data['email'];
$username = $data['username'];
$firebase_uid = $data['firebase_uid'];

// Check if the email already exists
$check_email_sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($check_email_sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    http_response_code(409);
    echo json_encode(["status" => "error", "message" => "Email already exists"]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Insert the user into the database
$insert_user_sql = "INSERT INTO users (email, username, user_id) VALUES (?, ?, ?)";
$stmt = $conn->prepare($insert_user_sql);
$stmt->bind_param("sss", $email, $username, $firebase_uid);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to register user"]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Set session
$_SESSION['user_id'] = $firebase_uid;
$_SESSION['username'] = $username;
$_SESSION['email'] = $email;

// Create a virtual account record for the user
$insert_virtual_sql = "INSERT INTO virtual_accounts (acct_id, email) VALUES (?, ?)";
$stmt = $conn->prepare($insert_virtual_sql);
$stmt->bind_param("ss", $firebase_uid, $email);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to create virtual account"]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

echo json_encode(["status" => "success", "message" => "User registered successfully and virtual account created"]);

$conn->close();
exit;
?>