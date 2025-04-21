<?php
session_start();
$isInvalid = false;

// Replace the external file include with direct connection details
$servername = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "eaziplux";

$conn = new mysqli($servername, $db_username, $db_password, $db_name);

// Check for database connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user["pass_word"])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];

        $_SESSION['customer_data'] = array(
            "email" => $_SESSION["email"],
            "first_name" => "",
            "last_name" => $_SESSION['username'],
            "phone" => $_SESSION["phone"]
        );

        // API endpoint URL
        $urlCustomer = 'https://api.paystack.co/customer';

        // API Key for Authorization
        $authorization = 'Authorization: Bearer sk_live_2886d2746575c3f54e3c7b7e02eab1cd7da06313';

        // Content type
        $contentType = 'Content-Type: application/json';

        // Data to be sent in the request
        $dataCustomer = json_encode($_SESSION['customer_data']);

        // Initialize cURL session
        $chCustomer = curl_init();

        // Set cURL options for a POST request
        curl_setopt($chCustomer, CURLOPT_URL, $urlCustomer);
        curl_setopt($chCustomer, CURLOPT_POST, 1);
        curl_setopt($chCustomer, CURLOPT_POSTFIELDS, $dataCustomer);
        curl_setopt($chCustomer, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $chCustomer,
            CURLOPT_HTTPHEADER,
            array(
                $authorization,
                $contentType,
            )
        );

        // Execute cURL session
        $responseCustomer = curl_exec($chCustomer);

        // Check for cURL errors
        if (curl_errno($chCustomer)) {
            echo 'Curl error: ' . curl_error($chCustomer);
        } else {
            // Decode and print the response
            $responseDataCustomer = json_decode($responseCustomer, true);


            $customerCode = $responseDataCustomer['data']['customer_code'];

        }

        // Close cURL session
        curl_close($chCustomer);

        $url = "https://api.paystack.co/dedicated_account";

        $fields = [
            "customer" => $customerCode,
            "preferred_bank" => "wema-bank"
        ];

        $fields_string = http_build_query($fields);

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options for a POST request
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                "Authorization: Bearer sk_live_2886d2746575c3f54e3c7b7e02eab1cd7da06313",
                "Cache-Control: no-cache",
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute cURL session
        $result = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        } else {
            // Decode the result
            $decode = json_decode($result);

            // Extract required information
            $acctnumber = $decode->data->account_number;
            $acctname = $decode->data->account_name;
            $bankname = $decode->data->bank->name;



            // Close cURL session
            curl_close($ch);

            // Save to database
            // Replace the following with your database connection and query
            $servername = "localhost";
            $db_username = "root";
            $db_password = "";
            $db_name = "eaziplux";

            $conn = new mysqli($servername, $db_username, $db_password, $db_name);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Use prepared statements to prevent SQL injection
            $stmt = $conn->prepare("UPDATE virtual_accounts SET acct_number=?, acct_name=?, bank_name=? WHERE email=?");
            $stmt->bind_param("ssss", $acctnumber, $acctname, $bankname, $_SESSION["email"]);
            $stmt->execute();

            $stmt->close();
            $conn->close();


            $_SESSION['acctnumber'] = $acctnumber;
            $_SESSION['acctname'] = $acctname;
            $_SESSION['bankname'] = $bankname;
        }







        header("Location: ../home/dashboard.php");
        exit;
    } else {
        $isInvalid = true;
    }

    // Display specific error messages if the query fails
    if (!$result) {
        echo "Error executing query: " . $conn->error;
    }

    $stmt->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="icon" type="image/png" size="662x662" href="../css/imgs/eaziplux.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-VVN0P5EYQP">
    </script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());

        gtag('config', 'G-VVN0P5EYQP');
    </script>

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
    <main>
        <div class="cont">
            <!---<div class="column1">
                <div class="slider">
                    <div class="slides">
                        <img src="../css/imgs/call.avif" alt="Slide 3" />
                        <img src="../css/imgs/happycash.jpg" alt="Slide 1" />
                        <img src="../css/imgs/film.jpg" alt="Slide 2" />
                        <img src="../css/imgs/call.avif" alt="Slide 3" />
                    </div>
                </div>
            </div>-->
            <div class="logo">
                <img src="../css/imgs/eazipluxpure.png" alt="eaziplux">
            </div>
            <div class="column2">
                <div class="container">
                    <form method="post">
                        <h3>
                            <?php if ($isInvalid): ?>
                                        <em> INVALID CREDENTIALS </em>
                            <?php endif; ?>
                        </h3>
                        <div class="txt-field">
                            <input type="email" placeholder="aisha@gmail.com" name="email" required class="email">
                            <!-- <span></span>-->
                            <!-- <label>Email</label>-->
                        </div>
                        <div class="txt-field">
                            <input type="password" placeholder="Password" name="password" required class="password">
                            <!--<span></span>-->
                            <!-- <label>Password</label>-->
                        </div>
                        <div class="forgotpass"><a href="resetpassword.php">Forgot Password?</a></div>
                        <input type="submit" name="submit" class="login" value="Login">
                        <div class="signup">Not a member? | <a href="signup.php">Signup</a></div>
                    </form>
                </div>
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