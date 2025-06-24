<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="../css/mtnairtime.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/49c5823e25.js" crossorigin="anonymous"></script>
    <title>Exam Pins</title>
        <style>
        /* Styling for preloader overlay */
        .preloader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.3); /* Transparent background with opacity */
            backdrop-filter: blur(8px); /* Apply blur effect to the background */
            display: none; /* Initially hide preloader */
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        /* Styling for preloader image */
        .preloader-img {
            width: 100px; /* Adjust size as needed */
            height: 100px; /* Adjust size as needed */
        }
    </style>
</head>

<body>
<div class="preloader-overlay" id="preloader">
        <img src="../css/imgs/eazi.gif" class="preloader-img" alt="Loading..."> <!-- Replace with your preloader image -->
    </div>

<header>
        <div class="back">
            <a href="../home/dashboard.php"><i class="fas fa-chevron-left" aria-hidden="true"></i></a>
        </div>
</header>
    <main>
        <div class="container">
            <div class="network">
                <form method="post">
                    <div class="MOBILEMTN">
                        <a href="mtndata.php">
                            <div class="mtnlogo">
                                <img src="../css/imgs/waec.png" alt="MTN">
                            </div>
                        </a>
                    </div>
                    <div class="fig">
                        <p>PLAN</p>
                        <select id="plans" name="plans">
                            <option>Waec Pin - 4000</option>
                        </select>
                    </div>
                    <div class="num">
                        <p>NG</p>
                        <input type="text" class="number" name="number" placeholder="08012345678">
                    </div>
                    <input class="submit" name="submit" onclick="openPopup()" type="submit" value="Buy E-pin">
                </form>
            </div>
    </main>
        <script>
        // JavaScript to show preloader when form is submitted
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('form').addEventListener('submit', function() {
                document.getElementById('preloader').style.display = 'flex';
            });
        });
    </script>

</body>

</html>
<?php
session_start();

include '../config.php';


if (!isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    $_SESSION['conn'] = $conn;

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $user_id_query = "SELECT user_id FROM users WHERE username = '$username'";
    $result = $conn->query($user_id_query);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $user_id = $row["user_id"];

        $balance_query = "SELECT balance FROM virtual_accounts WHERE acct_id = $user_id";
        $balance_result = $conn->query($balance_query);

        $_SESSION["USER_ID"] = $user_id;

        if ($balance_result->num_rows == 1) {
            $balance_row = $balance_result->fetch_assoc();
            $account_balance = $balance_row["balance"];

            $_SESSION['ACCT'] = $account_balance;
        } else {
            $account_balance = 0;
        }
    } else {
        header("Location: ../home/signup.php");
        exit;
    }
} else {
    header("Location: ../home/login.php");
    exit;
}

// Process form submission
if (isset($_POST['submit'])) {

    $number = isset($_POST['number']) ? $_POST['number'] : null;
    $phoneNumber = str_replace(" ", "", $number);
    $amount = 4000;

    if (!is_numeric($amount) || $amount < 100 || !preg_match('/^\d{11}$/', $number)) {
        $responseMessage = "Invalid input. Please enter a numeric amount above 100 and a valid 11-digit phone number.";
        $_SESSION['message'] = $responseMessage;
        header("Location: ../failed.php");
        exit;
    } elseif ($account_balance < $amount) {
        $responseMessage = 'Transaction failed: Fund your Global Bills Wallet.';
        $transaction_description = "Insufficient Funds";
        $transaction_status = "Failed";
        $transaction_type = "E-Pin";
        $item = "Waec";
        $receiver = $phoneNumber;
        $transaction_time = date("F j, Y \a\\t g:i A");
        $transaction_id = "EP" . $user_id . time(); // Combine user ID and timestamp

        // Insert failed transaction details into the transaction_history table
        $log_transaction_query = "INSERT INTO transaction_history (user_id, transaction_id, amount, description, status, item, transaction_type, receiver, transaction_time) 
        VALUES ($user_id, '$transaction_id', $amount, '$transaction_description', '$transaction_status', '$item', '$transaction_type', '$receiver', '$transaction_time')";
        if ($conn->query($log_transaction_query) !== TRUE) {
            $_SESSION['message'] = "Error logging transaction: " . $conn->error;
            header("Location: ../failed.php");
            exit;
        }

        $_SESSION['message'] = $responseMessage;
        header("Location: ../failed.php");
        exit;
    } elseif ($response['status'] === "failed") {
        $transaction_description = "Transaction Reversed";
        $transaction_status = "Reversed";
        $transaction_type = "E-Pins";
        $item = "Waec";
        $receiver = $phoneNumber;
        $transaction_time = date("F j, Y \a\\t g:i A");
        $transaction_id = "EP" . $user_id . time(); // Combine user ID and timestamp

        // Insert failed transaction details into the transaction_history table
        $log_transaction_query = "INSERT INTO transaction_history (user_id, transaction_id, amount, description, status, item, transaction_type, receiver, transaction_time) 
        VALUES ($user_id, '$transaction_id', $amount, '$transaction_description', '$transaction_status', '$item', '$transaction_type', '$receiver', '$transaction_time')";
        if ($conn->query($log_transaction_query) !== TRUE) {
            $_SESSION['message'] = "Error logging transaction: " . $conn->error;
            header("Location: ../failed.php");
            exit;
        }


        $responseMessage = 'Transaction failed: Reversed.';
        $_SESSION["message"] = $responseMessage;
        header("Location: ../failed.php");
    } else {
        // API endpoint URL
        $url = 'https://gsubz.com/api/pay/';

        // Data to be sent in the POST request
        $data = array(
            'serviceID' => 'waec',
            'plan' => 'waecdirect',
            'api' => 'ap_3f856a5b46bb740150d03c990ce2f5d7',
            'amount' => '3800',
            'phone' => $phoneNumber,
        );

        // Initialize curl session
        $ch = curl_init();

        // Set curl options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Headers
        $headers = array(
            'Authorization: Bearer ap_3f856a5b46bb740150d03c990ce2f5d7',
            'Content-Type: application/x-www-form-urlencoded'
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Execute curl session
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }

        // Close curl session
        curl_close($ch);
        $response = json_decode($responses, true);

        if ($response['status'] === 'TRANSACTION_FAILED') {
            $transaction_description = "Error in Transaction";
            $transaction_status = "Failed";
            $transaction_type = "E-Pins";
            $item = "Waec";
            $receiver = $phoneNumber;
            $transaction_time = date("F j, Y \a\\t g:i A");
            $transaction_id = "EP" . $user_id . time(); // Combine user ID and timestamp

            // Insert failed transaction details into the transaction_history table
            $log_transaction_query = "INSERT INTO transaction_history (user_id, transaction_id, amount, description, status, item, transaction_type, receiver, transaction_time) 
        VALUES ($user_id, '$transaction_id', $amount, '$transaction_description', '$transaction_status', '$item', '$transaction_type', '$receiver', '$transaction_time')";
            if ($conn->query($log_transaction_query) !== TRUE) {
                $_SESSION['message'] = "Error logging transaction: " . $conn->error;
                header("Location: ../failed.php");
                exit;
            }
            // Check the description for specific failure reasons
            if (strpos($response['description'], 'AMOUNT_ABOVE_MAX') !== false) {
                $responseMessage = 'Transaction failed: Amount above maximum allowed.';
                $_SESSION['message'] = $responseMessage;
                header("Location: ../failed.php");
                die;
            } elseif (strpos($response['description'], 'INSUFFICIENT_BALANCE') !== false) {
                $responseMessage = 'Services Currently Down: Try Again in 5 Minute or Contact Us.';
                $_SESSION['message'] = $responseMessage;
                header("Location: ../failed.php");
                die;
            } else {
                $responseMessage = 'Services Currently Down: Try Again in 5 Minute or Contact Us.';
                $_SESSION["message"] = $responseMessage;
                header("Location: ../failed.php");
            }


        } else {
            // Handle other cases if needed
            $new_balance = intval($account_balance) - intval($amount);

            // Update the balance in the database using prepared statement
            $update_balance_query = "UPDATE virtual_accounts SET balance = ? WHERE acct_id = ?";
            $stmt = $conn->prepare($update_balance_query);
            $stmt->bind_param("ii", $new_balance, $user_id);
            $stmt->execute();

            $responseMessage = 'YOU HAVE SUCCESSFULLY PURCHASED E-PIN WORTH OF N' . $amount;
            $transaction_description = "Transaction Successful";
            $transaction_status = "Successful";
            $transaction_type = "E-Pins";
            $item = "Waec";
            $receiver = $number;
            $transaction_time = date("F j, Y \a\\t g:i A");
            $transaction_id = "EP" . $user_id . time(); // Combine user ID and timestamp

            // Insert failed transaction details into the transaction_history table
            $log_transaction_query = "INSERT INTO transaction_history (user_id, transaction_id, amount, description, status, item, transaction_type, receiver, transaction_time) 
        VALUES ($user_id, '$transaction_id', $amount, '$transaction_description', '$transaction_status', '$item', '$transaction_type', '$receiver', '$transaction_time')";
            if ($conn->query($log_transaction_query) !== TRUE) {
                $_SESSION['message'] = "Error logging transaction: " . $conn->error;
                header("Location: ../failed.php");
                exit;
            }

            $_SESSION["message"] = $responseMessage;
            header("Location: ../success.php");
            exit;
        }

    }
}
?>