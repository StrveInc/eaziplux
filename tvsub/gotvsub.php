<!DOCTYPE html>
<html>

<head>
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
    <title>GOTV SUBSCRIPTION</title>
    <link rel="stylesheet" href="../css/mtnairtime.css">
    <style>
        span {
            color: blue;
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
            <a href="../dashboard/tvsub.php"><i class="fas fa-chevron-left" aria-hidden="true"></i></a>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="network">
                <form method="post">
                    <div class="MOBILEMTN">
                        <a href="dstvsub.php">
                            <div class="mtnlogo">
                                <img src="../css/imgs/gotvupdate.png" alt="dstv">
                            </div>
                        </a>
                    </div>
                    <div class="fig">
                        <p>PLAN</p>
                        <select id="plans" required name="plans">
                            <option value="gotv-smallie">GOTV SMALLIE - N1300</option>
                            <option value="gotv-jinja">GOTV JINJA - N2700</option>
                            <option value="gotv-jolli">GOTV JOLLI - N3950</option>
                            <option value="gotv-max">GOTV MAX - N5700</option>
                            <option value="gotv-supa">GOTV SUPA - N7600</option>

                        </select>
                    </div>
                    <div class="num1">
                        <p>Card Number</p>
                        <input type="text" class="number1" name="card_number" placeholder="08012345678">
                    </div>
                    <div class="num1">
                        <p>Phone number</p>
                        <input type="text" class="number1" name="number" placeholder="08012345678">
                    </div>
                    <input class="submit" name="submit" onclick="openPopup()" type="submit" value="Subscribe">
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



// Define an array with options and their corresponding prices
$options = array(
    'gotv-smallie' => 1300,
    'gotv-jinja' => 2700,
    'gotv-jolli' => 3950,
    'gotv-max' => 5700,
    'gotv-supa' => 7600
);

// Initialize variables to store the selected option and its price
$selectedPackage = '';
$selectedPrice = 0;
$responseMessage = null;

// Check if the form is submitted
if (isset($_POST['submit'])) {
    // Retrieve the selected option from the form
    $selectedPackage = isset($_POST['plans']) ? $_POST['plans'] : '';
    $decoderNumber = isset($_POST['sm_number']) ? $_POST['sm_number'] : '';
    $phoneNumber = isset($_POST['number']) ? $_POST['number'] : '';
    $product = "GOTV SUBSCRIPTION";

    // Retrieve the selected price based on the selected package
    if (isset($options[$selectedPackage])) {
        $selectedPrice = $options[$selectedPackage];
    } else {
        // Handle the case where the selected package is not in the options array
        $responseMessage = 'Invalid selected package';
        $_SESSION['message'] = $responseMessage;
        header("Location: ../failed.php");
        exit;
    }

    if ($account_balance < $selectedPrice) {
        $responseMessage = 'Transaction failed: Fund your Global Bills wallet.';

        $transaction_description = $product;
        $transaction_status = "FAILED";
        $transaction_id = "GB" . $user_id . time(); // Combine user ID and timestamp

        // Insert successful transaction details into the transaction_history table
        $log_transaction_query = "INSERT INTO transaction_history (user_id, transaction_id, amount, description, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($log_transaction_query);
        $stmt->bind_param("issss", $user_id, $transaction_id, $selectedPrice, $transaction_description, $transaction_status);
        $stmt->execute();

        $_SESSION['message'] = $responseMessage;
        header("Location: ../failed.php");
        exit;
    } else {
        // You can use $selectedPackage and $decoderNumber as needed
        // Send data to an endpoint using cURL
        $endpoint = 'https://vtu.ng/wp-json/api/v1/tv';
        $username = 'Vickman17';
        $password = '';
        $smartcardNumber = $decoderNumber;
        $variationId = $selectedPackage;

        // Build the URL with query parameters
        $url = $endpoint . '?' . http_build_query([
            'username' => $username,
            'password' => $password,
            'phone' => $phoneNumber,
            'service_id' => 'gotv',
            'smartcard_number' => $smartcardNumber,
            'variation_id' => $variationId,
        ]);

        // Initialize cURL session
        $ch = curl_init($url);

        // Set cURL options for a GET request
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute cURL session and get the response
        $response = curl_exec($ch);

        // Check the status in the response and construct the response message
        if ($response === false) {
            // Handle cURL error
            $transaction_description = $product;
            $transaction_status = "FAILED";
            $transaction_id = "GB" . $user_id . time(); // Combine user ID and timestamp

            // Insert successful transaction details into the transaction_history table
            $log_transaction_query = "INSERT INTO transaction_history (user_id, transaction_id, amount, description, status) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($log_transaction_query);
            $stmt->bind_param("issss", $user_id, $transaction_id, $selectedPrice, $transaction_description, $transaction_status);
            $stmt->execute();

            $responseMessage = 'cURL Error: ' . curl_error($ch);
            $_SESSION['message'] = $responseMessage;
            header("Location: ../failed.php");
        } else {
            // Process the response from the endpoint
            $responseData = json_decode($response, true);

            // Check the status in the response and construct the response message
            if (isset($responseData['code']) && $responseData['code'] === 'success') {
                $responseMessage = 'Request successful! Message: ' . $responseData['message'];
                $newBalance = intval($account_balance) - intval($options[$selectedPackage]);

                // Update the balance in the database using prepared statement
                $conn = new mysqli($servername, $db_username, $db_password, $db_name);

                // Check for database connection errors
                if ($conn->connect_error) {
                    die("Database Connection failed: " . $conn->connect_error);
                }

                $updateBalanceQuery = "UPDATE virtual_accounts SET balance = ? WHERE acct_id = ?";
                $stmt = $conn->prepare($updateBalanceQuery);
                $stmt->bind_param("ii", $newBalance, $user_id);
                $stmt->execute();
                $stmt->close();

                $transaction_description = $product;
                $transaction_status = "SUCCESS";
                $transaction_id = "GB" . $user_id . time(); // Combine user ID and timestamp

                // Insert successful transaction details into the transaction_history table
                $log_transaction_query = "INSERT INTO transaction_history (user_id, transaction_id, amount, description, status) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($log_transaction_query);
                $stmt->bind_param("issss", $user_id, $transaction_id, $selectedPrice, $transaction_description, $transaction_status);
                $stmt->execute();

                // Close the database connection
                $conn->close();
                $responseMessage = "You Have Successfully Purchased " . $selectedPrice . " Worth Of " . $product;
                $_SESSION['message'] = $responseMessage;
                header("Location: ../success.php");
                exit;
            } else if ($responseData['message'] === "Invalid Smartcard Number") {
                $responseMessage = "INVALID SMART CARD NUMBER. CHECK AND RETRY";
                $_SESSION['message'] = $responseMessage;
                header("Location: ../failed.php");
                exit;
            } else {
                $responseMessage = "Service Currently Down: Try Again in 5 Minute or Contact US.";
                $_SESSION['message'] = $responseMessage;
                $transaction_description = $product;
                $transaction_status = "FAILED";
                $transaction_id = "GB" . $user_id . time(); // Combine user ID and timestamp

                // Insert successful transaction details into the transaction_history table
                $log_transaction_query = "INSERT INTO transaction_history (user_id, transaction_id, amount, description, status) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($log_transaction_query);
                $stmt->bind_param("issss", $user_id, $transaction_id, $selectedPrice, $transaction_description, $transaction_status);
                $stmt->execute();

                header("Location: ../failed.php");
                exit;
            }
        }

        curl_close($ch);

        // Store the response message in the session



    }

}

$_SESSION["message"] = $responseMessage;


?>