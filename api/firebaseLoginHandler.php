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