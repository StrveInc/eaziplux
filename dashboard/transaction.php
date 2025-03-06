<?php
session_start();

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Establish a database connection (use your database connection code here)
    $servername = "localhost";  // Replace with your database server name
    $db_username = "root";   // Replace with your database username
    $dbpassword = "";      // Replace with your database password
    $database = "eaziplux";   // Replace with your database name
    $conn = new mysqli($servername, $db_username, $dbpassword, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve the user's ID based on their username
    $user_id_query = "SELECT user_id FROM users WHERE username = '$username'";
    $result = $conn->query($user_id_query);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $user_id = $row["user_id"];

        // Fetch distinct months from the transaction history for the user
        $months_query = "SELECT DISTINCT MONTHNAME(timestamp) AS transaction_month FROM transaction_history WHERE user_id = '$user_id' ORDER BY transaction_month DESC";
        $months_result = $conn->query($months_query);

        // Fetch transaction history for the user
        $transaction_query = "SELECT * FROM transaction_history WHERE user_id = '$user_id' ORDER BY timestamp DESC";
        $transaction_result = $conn->query($transaction_query);

        if ($months_result->num_rows > 0 && $transaction_result->num_rows > 0) {
            // Build dropdown options for months
            $month_options = '';
            while ($month_row = $months_result->fetch_assoc()) {
                $month_options .= '<option value="' . $month_row["transaction_month"] . '">' . $month_row["transaction_month"] . '</option>';
            }

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
        } else {
            $month_options = '<option value="">No transactions found.</option>';
            $transaction_html = "No transactions found.";
        }
    } else {
        $month_options = '<option value="">User not found.</option>';
        $transaction_html = "User not found.";
    }
} else {
    header("Location: ../home/login.php"); // Redirect to the login page
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="../css/transaction.css">
    <meta charset="UTF-8">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" size="662x662" href="./css/imgs/eaziplux.png">
    <script src="https://kit.fontawesome.com/49c5823e25.js" crossorigin="anonymous"></script>
    <meta name="description"
        content="Manage your mobile data and pay bills seamlessly with Eazi Plux. Enjoy a convienient and secure platform for handling all your mobile-related transactions.">
    <meta charset="UTF-8">
    <meta name="keywords"
        content="discounted mobile data, airtime deals, bills payment app, online payment, mobile recharge, discounted airtime, bill management, digital transactions, cheap airtime, cheap data, Eazi Plux, best cheap data ">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-site-verification" content="2C-9r_1lFbvzBCAMqcq3p8EoPsKWrm_9aiWJWioJiVg" />
    <meta name="author" content="Vickman Tech">
    <title>Transaction History</title>
    <style>
        .failed {
            color: red;
        }
    </style>
</head>

<body>
    <header>
        <div>
            <div class="back">
                <a href="../home/dashboard.php">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            </div>
            <div class="head">Transaction History</div>
        </div>
        <div class="date">
            <select id="monthSelector">
                <?php echo $month_options; ?>
            </select>
        </div>
    </header>
    <main>
        <div class="container" id="transactionContainer">
            <?php echo $transaction_html; ?>
        </div>
    </main>
    <script>
        document.getElementById("monthSelector").addEventListener("change", function () {
            var selectedMonth = this.value;
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "filter_transactions.php?month=" + selectedMonth, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById("transactionContainer").innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        });
    </script>
</body>

</html>