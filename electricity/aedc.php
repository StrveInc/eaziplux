<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="../css/mtnairtime.css">
    <meta charset="UTF-8">
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
    <title>AEDC ELECTRICITY</title>
    <style>
        span {
            color: green;
        }
    </style>
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
            <a href="../dashboard/electricity.php"><i class="fas fa-chevron-left" aria-hidden="true"></i></a>
        </div>
    </header>
    <main>
        <div class="container">
            <div class="network">
                <form method="post">
                    <div class="MOBILEMTN">
                        <a href="../electricity/aedc.php">
                            <div class="mtnlogo">
                                <img src="../css/imgs/aedc.png" alt="AEDC">
                            </div>
                        </a>
                    </div>
                    <div class="figa">
                        <div>
                            <p>Amount</p>
                            <input type="text" class="amount" name="amount" placeholder="min - 100" </input>
                        </div>
                    </div>
                    <div class="figa">
                        <div>
                            <p>Phone Number</p>
                            <input type="text" class="number" name="number" placeholder="08012345678">
                        </div>
                    </div>

                    <div class="figa">
                        <div>
                            <p>Customer ID</p>
                            <input type="text" class="customer" name="customer" placeholder="E.g...939012345678">
                        </div>
                    </div>
                    <input class="submit" name="submit" type="submit" value="Buy Electricity">
                </form>
            </div>
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

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Establish a database connection (use your database connection code here)
    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = "";
    $database = "eaziplux";

    $conn = new mysqli($servername, $dbusername, $dbpassword, $database);


    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve the user's virtual account balance
    // Assuming you store the username in the $_SESSION
    $username = $_SESSION['username'];

    // SQL query to fetch the user's ID based on their username
    $user_id_query = "SELECT user_id FROM users WHERE username = '$username'";
    $result = $conn->query($user_id_query);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $user_id = $row["user_id"];
        // Now that you have the user's ID, you can retrieve their balance
        $balance_query = "SELECT balance FROM virtual_accounts WHERE acct_id = $user_id";
        $balance_result = $conn->query($balance_query);

        if ($balance_result->num_rows == 1) {
            $balance_row = $balance_result->fetch_assoc();
            $account_balance = $balance_row["balance"];
        } else {
            $account_balance = 0; // Default balance if not found
        }
    } else {
        // Handle the case where the user's ID is not found
    }
} else {
    header("Location: ../home/login.php"); // Redirect to the dashboard page
    exit;
}

// Check if "number" and "amount" keys are set in the $_POST array
$number = isset($_POST["number"]) ? $_POST["number"] : null;
$amount = isset($_POST["amount"]) ? $_POST["amount"] : null;
$customer = isset($_POST["customer"]) ? $_POST["customer"] : null;
$responseMessage = null;
// Check if the account balance is sufficient
if (isset($_POST["submit"])) {
    if (!is_numeric($amount) || $amount < 100) {
        $responseMessage = "Amount must be numeric and at least 100 naira.";
        $_SESSION['message'] = $responseMessage;
        header('Location: ../failed.php');
    } elseif ($account_balance < $amount) {
        $transaction_description = "Insufficient Funds";
        $transaction_status = "Failed";
        $transaction_type = "Electricity";
        $item = "AEDC";
        $receiver = $customer;
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

        $responseMessage = "Transaction failed: Fund your Global Bills wallet.";
        $_SESSION['message'] = $responseMessage;
        header("Location: ../failed.php");
    } else {
        // Deduct the specified amount from the user's virtual account balance

        // Proceed with the transaction
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://gsubz.com/api/pay/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('serviceID' => 'abuja_electric', 'api' => 'ap_83c2ee17ca19e2c34e29a0f17cd5bd89', 'amount' => $amount, 'phone' => $number, 'customerID' => $customer),
                CURLOPT_HTTPHEADER => array(
                    'api: Bearer ap_83c2ee17ca19e2c34e29a0f17cd5bd89'
                ),
            )
        );

        $response = curl_exec($curl);

        curl_close($curl);

        echo '<pre>';
        print_r($response);
        echo '</pre>';

        // Your existing cURL request code

        $response = json_decode($response, true);

        $responseMessage = null;

        if ($response['status'] === 'TRANSACTION_FAILED') {

            // Check the description for specific failure reasons
            if (strpos($response['description'], 'AMOUNT_ABOVE_MAX') !== false) {
                $responseMessage = 'Transaction failed: Amount above maximum allowed.';
                $_SESSION['message'] = $responseMessage;
                header("Location: ../failed.php");

            } elseif (strpos($response['description'], 'INSUFFICIENT_BALANCE') !== false) {

                $transaction_description = "Error in Transaction";
                $transaction_status = "Failed";
                $transaction_type = "Electricity";
                $item = "AEDC";
                $receiver = $customer;
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


                $responseMessage = 'Service Currently Down: Try again in 5 minutes or Contact Us.';
                $_SESSION["message"] = $responseMessage;
                header("Location: ../failed.php");
            } else {

                $transaction_description = "Transaction Reversed";
                $transaction_status = "Reversed";
                $transaction_type = "Electricity";
                $item = "AEDC";
                $receiver = $customer;
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


                $responseMessage = 'Service Currently Down: Try again in 5 minutes or Contact Us.';
                $_SESSION['message'] = $responseMessage;
                header("Location: ../failed.php");
            }
        } else {
            $transaction_description = "Transaction Successful";
            $transaction_status = "Successful";
            $transaction_type = "Electricity";
            $item = "AEDC";
            $receiver = $customer;
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

            // Handle other cases if needed
            $new_balance = intval($account_balance) - intval($amount);

            // Update the balance in the database
            $update_balance_query = "UPDATE virtual_accounts SET balance = $new_balance WHERE acct_id = $user_id";
            $conn->query($update_balance_query);

            $responseMessage = 'You have successfully paid the sum of ' . $amount . ' for AEDC ELECTRICITY for ' . $customer;
            $_SESSION["message"] = $responseMessage;
            header("Location: ../success.php"); // Redirect to the dashboard page
            exit;
        }
    }
}
?>