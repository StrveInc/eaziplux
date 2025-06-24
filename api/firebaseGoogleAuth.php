<?php
// filepath: c:\xampp\htdocs\eaziplux\api\firebaseGoogleAuth.php

session_start(); // Start the session to store user data
header("Content-Type: application/json");

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config.php';



// Paystack API Key
$paystack_secret_key = $_ENV['PK_SECRET']; // Replace with your Paystack secret key


// Check for database connection errors
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

// Validate input
if (!isset($_POST['email']) || !isset($_POST['username']) || !isset($_POST['firebase_uid'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

$email = $_POST['email'];
$username = $_POST['username'];
$firebase_uid = $_POST['firebase_uid'];

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

// Insert the user into the database
$insert_user_sql = "INSERT INTO users (email, username, user_id) VALUES (?, ?, ?)";
$stmt = $conn->prepare($insert_user_sql);
$stmt->bind_param("sss", $email, $username, $firebase_uid);

if ($stmt->execute()) {
    // Automatically log the user in by setting session variables
    $_SESSION['user_id'] = $firebase_uid;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;

    // Step 1: Create a customer on Paystack
    $paystack_customer_url = "https://api.paystack.co/customer";
    $customer_data = [
        "email" => $email,
        "first_name" => $username
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $paystack_customer_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($customer_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $paystack_secret_key",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
        $response_data = json_decode($response, true);
        if ($response_data['status']) {
            $customer_code = $response_data['data']['customer_code'];

            // Step 2: Create a virtual account for the customer
            $paystack_virtual_account_url = "https://api.paystack.co/dedicated_account";
            $virtual_account_data = [
                "customer" => $customer_code,
                "preferred_bank" => "wema-bank"
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $paystack_virtual_account_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($virtual_account_data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer $paystack_secret_key",
                "Content-Type: application/json"
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code === 200) {
                $response_data = json_decode($response, true);
                if ($response_data['status']) {
                    $account_number = $response_data['data']['account_number'];
                    $bank_name = $response_data['data']['bank']['name'];
                    $account_name = $response_data['data']['account_name'];
                    // $email = $response_data['data']['email'];

                    // Save the virtual account details in the database
                    $insert_virtual_account_sql = "INSERT INTO virtual_accounts (acct_id, acct_number, acct_name, bank_name, email) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($insert_virtual_account_sql);
                    $stmt->bind_param("sssss", $firebase_uid, $account_number, $account_name, $bank_name, $email);
                    $stmt->execute();
                }
            }
        }
    }

    echo json_encode(["status" => "success", "message" => "User registered successfully"]); 
    exit;
} else {    
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to register user"]);
    exit;
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>