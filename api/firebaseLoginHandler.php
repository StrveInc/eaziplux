<?php
session_start();

// Add CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight (OPTIONS) requests
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200); // Respond with HTTP 200 OK for preflight
    exit;
}

// Database connection details
include '../config.php';

// Check for database connection errors
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle both GET and POST requests
if ($_SERVER["REQUEST_METHOD"] === "POST" || $_SERVER["REQUEST_METHOD"] === "GET") {
    $email = $_REQUEST['email'] ?? null; // Use $_REQUEST to handle both GET and POST
    $firebase_uid = $_REQUEST['firebase_uid'] ?? null;

    if (!$email || !$firebase_uid) {
        die("Missing required fields");
    }

    // Check if the user exists in the database
    $sql = "SELECT * FROM users WHERE email = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $firebase_uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Save user details in session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['phone'] = $user['phone'] ?? null;

        // Check if the referral_code is empty
        if (empty($user['referral_code'])) {
            // Generate a unique referral code
            $referral_code = strtoupper(substr(md5(uniqid($firebase_uid, true)), 0, 8));

            // Update the user's referral_code in the database
            $update_referral_sql = "UPDATE users SET referral_code = ? WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_referral_sql);
            $update_stmt->bind_param("ss", $referral_code, $firebase_uid);
            $update_stmt->execute();
            $update_stmt->close();

            // Optionally, store the referral code in the session
            $_SESSION['referral_code'] = $referral_code;
        } else {
            // Store the existing referral code in the session
            $_SESSION['referral_code'] = $user['referral_code'];
        }

        header("Location: ../home/dashboard.php"); // Redirect to the dashboard
        exit;
    } else {
        // Redirect to login with an error message
        header("Location: ../home/login.php?error=invalid_credentials");
        exit;
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit;
}

$conn->close();
?>