<?php
session_start();

$userEmail = $_SESSION['email']; // Replace with the actual user's email
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
        $stmt->bind_param("di", $amount, $user_id);

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
$reference = $_GET['reference'];
$paystackVerifyUrl = "https://api.paystack.co/transaction/verify/$reference";
$paystackSecretHeaders = [
    "Authorization: Bearer $paystackSecretKey",
];

$ch = curl_init($paystackVerifyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $paystackSecretHeaders);

$response = curl_exec($ch);
curl_close($ch);

// Check if the payment is successful
$paymentDetails = json_decode($response);

if ($paymentDetails && $paymentDetails->data->status === 'success') {
    // Payment was successful
    // Retrieve additional information from $paymentDetails as needed
    $transactionReference = $paymentDetails->data->reference;
    $amount = $paymentDetails->data->amount / 100; // Convert back to Naira from kobo

    // Now, you can update the virtual account balance in your database
    // Example: Call a function to update the balance (replace with your logic)
    updateVirtualAccountBalance($conn, $userEmail, $amount);

    // Respond with a success message
    echo "Payment successful! Transaction Reference: " . $transactionReference;
    header("Location: ../home/dashboard.php");
} else {
    // Payment failed
    echo "Payment failed.";
}

// Close the database connection
$conn->close();
?>