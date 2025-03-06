<?php
session_start();

$paystackSecretKey = "sk_live_2886d2746575c3f54e3c7b7e02eab1cd7da06313"; // Replace with your Paystack secret key
// Establish a database connection (use your database connection code here)
$servername = "localhost";  // Replace with your database server name
$username = "root";   // Replace with your database username
$dbpassword = "";      // Replace with your database password
$database = "eaziplux";   // Replace with your database name

// Create a connection to the database

// Function to update the virtual account balance
function updateVirtualAccountBalance($conn, $userEmail, $amount)
{
    // Retrieve user ID based on email
    $user_id_query = "SELECT user_id FROM users WHERE email = ?";

    $stmt = $conn->prepare($user_id_query);
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $user_id = $row["user_id"];

        // Update the virtual account balance
        $update_balance_query = "UPDATE virtual_accounts SET balance = balance + ? WHERE acct_id = ?";
        $stmt = $conn->prepare($update_balance_query);
        $stmt->bind_param("ds", $amount, $user_id);

        if ($stmt->execute()) {
            // Balance updated successfully
            // You may also log this update in another table if needed

        } else {
            // Error updating balance
            // Log the error or handle it accordingly
            echo "Error updating balance: " . $stmt->error;
        }
    }
}

// Establish a database connection
$conn = new mysqli($servername, $username, $dbpassword, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Capture the incoming Paystack webhook event
$webhookData = json_decode(file_get_contents("php://input"), true);

// Log the Paystack webhook event
file_put_contents("event.log", json_encode($webhookData) . "\n", FILE_APPEND);

// Check if the event is a charge success event
if ($webhookData && isset($webhookData['event']) && $webhookData['event'] === 'charge.success') {
    // Retrieve relevant data from the Paystack webhook
    $paystackReference = $webhookData['data']['reference'];
    $amount = $webhookData['data']['amount'] / 100; // Convert from kobo to your desired currency
    $userEmail = $webhookData['data']['customer']['email']; // Assuming customer email is available in the Paystack event

    // Now, you can update the virtual account balance in your database
    updateVirtualAccountBalance($conn, $userEmail, $amount);

    // Respond with a success message to Paystack (optional)
    echo json_encode(['status' => 'success']);
} else {
    // Invalid or unsupported Paystack webhook event
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid webhook event']);
}

// Close the database connection
$conn->close();
?>
