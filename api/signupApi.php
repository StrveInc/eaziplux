<?php
// filepath: c:\xampp\htdocs\eaziplux\api\signupApi.php

session_start(); // Start the session to store user data
header("Content-Type: application/json");

// Database connection details
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
if (!isset($_POST['email']) || !isset($_POST['username']) || !isset($_POST['phone']) || (!isset($_POST['firebase_uid']) && !isset($_POST['password']))) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

$email = $_POST['email'];
$username = $_POST['username'];
$phone = $_POST['phone'];
$firebase_uid = isset($_POST['firebase_uid']) ? $_POST['firebase_uid'] : null;
$password = isset($_POST['password']) ? $_POST['password'] : null;
$referred_by = isset($_POST['referral_code']) ? $_POST['referral_code'] : null;

// Generate a unique referral code
$referral_code = strtoupper(substr(md5(uniqid($email, true)), 0, 8));

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
if ($firebase_uid) {
    $insert_user_sql = "INSERT INTO users (email, username, phone, firebase_uid, referral_code, referred_by) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_user_sql);
    $stmt->bind_param("ssssss", $email, $username, $phone, $firebase_uid, $referral_code, $referred_by);
} else {
    $user_id = uniqid('user_', true);
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $insert_user_sql = "INSERT INTO users (email, username, phone, pass_word, user_id, referral_code, referred_by) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_user_sql);
    $stmt->bind_param("sssssss", $email, $username, $phone, $hashed_password, $user_id, $referral_code, $referred_by);
}

if ($stmt->execute()) {
    $_SESSION['user_id'] = isset($user_id) ? $user_id : $conn->insert_id;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['phone'] = $phone;
    $_SESSION['referral_code'] = $referral_code;

    // Step 1: Create a customer on Paystack
    $paystack_customer_url = "https://api.paystack.co/customer";
    $customer_data = [
        "email" => $email,
        "first_name" => $username,
        "last_name" => "Eazi",
        "phone" => $phone
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

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        echo json_encode(["status" => "error", "message" => "Paystack customer creation failed: " . $error_msg]);
        curl_close($ch);
        exit;
    }

    curl_close($ch);

    if ($http_code === 200) {
        $response_data = json_decode($response, true);
        if ($response_data['status']) {
            $customer_code = $response_data['data']['customer_code'];

            // Step 2: Create a virtual account for the customer
            $paystack_virtual_account_url = "https://api.paystack.co/dedicated_account";
            $virtual_account_data = [
                "customer" => $customer_code,
                "preferred_bank" => "test-bank"
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

            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                echo json_encode(["status" => "error", "message" => "Paystack virtual account creation failed: " . $error_msg]);
                curl_close($ch);
                exit;
            }

            curl_close($ch);

            if ($http_code === 200) {
                $response_data = json_decode($response, true);
                if ($response_data['status']) {
                    $account_number = $response_data['data']['account_number'];
                    $bank_name = $response_data['data']['bank']['name'];
                    $accountName = $response_data['data']['account_name'];

                    // Save the virtual account details in the database
                    $insert_virtual_account_sql = "INSERT INTO virtual_accounts (acct_id, acct_number, bank_name, acct_name, email) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($insert_virtual_account_sql);
                    $stmt->bind_param("sssss", $_SESSION['user_id'], $account_number, $bank_name, $accountName, $email);
                    $stmt->execute();

                    // Redirect to the dashboard
                    header("Location: ../home/dashboard.php");
                    exit;
                } else {
                    http_response_code(500);
                    echo json_encode(["status" => "error", "message" => "Failed to create virtual account"]);
                    exit;
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Paystack virtual account creation failed", "response" => $response]);
                exit;
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Paystack customer creation failed", "response" => $response]);
            exit;
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Paystack customer creation failed", "response" => $response]);
        exit;
    }
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to register user"]);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>