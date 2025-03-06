<?php
session_start();

// Establish a database connection (use your database connection code here)
$servername = "localhost";  // Replace with your database server name
$db_username = "root";   // Replace with your database username
$dbpassword = "";      // Replace with your database password
$database = "eaziplux";   // Replace with your database name
$conn = new mysqli($servername, $db_username, $dbpassword, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve the selected month from the GET parameter
$selectedMonth = $_GET['month'];

$username = $_SESSION["username"]; // Replace with your actual session variable name

// Query to get the user ID based on the username
$user_query = "SELECT user_id FROM users WHERE username = '$username'";
$user_result = $conn->query($user_query);

if ($user_result->num_rows > 0) {
    // User found, fetch the user ID
    $user_row = $user_result->fetch_assoc();
    $user_id = $user_row['user_id'];

    // Now you have the user ID, you can use it in your transaction query
} else {
    echo "User not found.";
}


// Convert the selected month to the format stored in your database (e.g., YYYY-MM)
$selectedMonthFormatted = date('Y-m', strtotime($selectedMonth));

// Retrieve transactions for the selected month and the specified user
$transaction_query = "SELECT *, DATE_FORMAT(timestamp, '%M %Y') AS transaction_month FROM transaction_history WHERE DATE_FORMAT(timestamp, '%Y-%m') = '$selectedMonthFormatted' AND user_id = '$user_id' ORDER BY timestamp DESC";
$transaction_result = $conn->query($transaction_query);

// Initialize an empty string to store HTML output for transactions
$transaction_html = '';

while ($row = $transaction_result->fetch_assoc()) {
                $transaction_id = $row["transaction_id"];
                $transaction_html .= '<div class="column">';
                $transaction_html .= '<div class="col1">';
                $transaction_html .= '<div class="type">';
                $transaction_html .= " " . $row["transaction_type"] . "";
                $transaction_html .= '</div>';
                $transaction_html .= '<div class="amount">';
                $transaction_html .= "" . number_format($row["amount"], 2) . "";
                $transaction_html .= '</div>';
                $transaction_html .= '</div>';
                $transaction_html .= '<div class="col2">';
                $transaction_html .= '<div>';
                $transaction_html .= "" . $row["transaction_time"] . "";
                $transaction_html .= '</div>';
                $transaction_html .= '<div>';
                $transaction_html .= "" . $row["status"] . "";
                $transaction_html .= '</div>';
                $transaction_html .= '</div>';
                $transaction_html .= '</div>';
 }


// Output the HTML for the filtered transactions
echo $transaction_html;

?>
