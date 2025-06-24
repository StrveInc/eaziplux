<?php
include '../config.php';
// Check the connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to save a transaction
function saveTransaction($conn, $transactionData) {
    // Prepare the SQL query
    $sql = "INSERT INTO transactions (user_id, amount, type, description, created_at) VALUES (?, ?, ?, ?, NOW())";

    // Initialize a prepared statement
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        // Bind parameters to the prepared statement
        mysqli_stmt_bind_param(
            $stmt,
            "idss", // i = integer, d = double, s = string
            $transactionData['user_id'],
            $transactionData['amount'],
            $transactionData['type'],
            $transactionData['description']
        );

        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            echo "Transaction saved successfully.";
        } else {
            echo "Error saving transaction: " . mysqli_error($conn);
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing statement: " . mysqli_error($conn);
    }
}

// Example usage
$transaction_description = "Transaction Reversed";
$transaction_status = "Reversed";
$transaction_type = "Data";
$item = $item;
$receiver = $phoneNumber;
$transaction_time = date("F j, Y \a\\t g:i A");
$transaction_id = "EP".time(); // Combine user ID and timestamp
$transaction_price = $amount;

saveTransaction($conn, $transactionData);

// Close the database connection
mysqli_close($conn);
?>