<?php
session_start();
?>


<!DOCTYPE html>
<html>

<head>
    <link rel="icon" type="image/png" size="662x662" href="../css/imgs/eaziplux.png">
    <link rel="stylesheet" href="../css/resetpassword.css">
    <title>Forgot Password</title>
    <meta charset="UTF-8">
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

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="preloader-overlay" id="preloader">
        <img src="../css/imgs/eazi.gif" class="preloader-img" alt="Loading..."> <!-- Replace with your preloader image -->
    </div>


    <main>
        <div class="container">
            <h2>Forgot Password?</h2>
            <p>Enter your email address to reset your password.</p>
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <div class="txt-field">
                    <input type="text" name="email">
                    <span></span>
                    <label>Email:</label>
                </div>
                <div>
                    <input name="submit" type="submit" value="Reset Password">
                </div>
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

// Database connection
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$database = "eaziplux";
$conn = mysqli_connect($servername, $dbusername, $dbpassword, $database);

require_once('../vendor/autoload.php');
use Postmark\PostmarkClient; // Move this outside the function

// Function to detect OS
function getOS() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    
    if (preg_match('/windows/i', $userAgent)) return "Windows";
    if (preg_match('/macintosh|mac os x/i', $userAgent)) return "MacOS";
    if (preg_match('/linux/i', $userAgent)) return "Linux";
    if (preg_match('/android/i', $userAgent)) return "Android";
    if (preg_match('/iphone|ipad|ipod/i', $userAgent)) return "iOS";
    
    return "Unknown";
}

// Function to detect browser
function getBrowser() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    
    if (strpos($userAgent, 'Firefox') !== false) return "Firefox";
    if (strpos($userAgent, 'Chrome') !== false) return "Chrome";
    if (strpos($userAgent, 'Safari') !== false) return "Safari";
    if (strpos($userAgent, 'Opera') !== false) return "Opera";
    if (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident/') !== false) return "Internet Explorer";
    
    return "Unknown";
}

// Function to send password reset email using Postmark
function send_password_reset($get_name, $get_email, $token) {
    $client = new PostmarkClient("5223c201-3d4f-48ed-9675-7348c1b631e7");
    $template_id = 38915017; // Replace with your actual template ID

    $os = getOS();
    $browser = getBrowser();
    
    $template_model = [
        "product_url" => "https://eaziplux.com.ng",
        "product_name" => "Eaziplux",
        "name" => $get_name,
        "action_url" => "https://eaziplux.com.ng/home/password_change.php?token=$token&email=$get_email",
        "operating_system" => $os,
        "browser_name" => $browser,
        "support_url" => "https://eaziplux.com.ng/support",
        "company_name" => "Eaziplux",
        "company_address" => "Plot 1323 olaleye oluwa street, unity estate"
    ];

    $sendResult = $client->sendEmailWithTemplate(
        "support@eaziplux.com.ng",
        $get_email,
        $template_id,
        $template_model
    );
}

// Handle form submission
if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $token = md5(rand());

    $check_email = "SELECT email, username FROM users WHERE email='$email' LIMIT 1";
    $check_email_run = mysqli_query($conn, $check_email);

    if (mysqli_num_rows($check_email_run) > 0) {
        $row = mysqli_fetch_array($check_email_run);
        $get_name = isset($row['username']) ? $row['username'] : '';
        $get_email = $row['email'];
        $test_email = "admin@eaziplux.com.ng";


        $update_token = "UPDATE users SET reset_token='$token' WHERE email='$get_email' LIMIT 1";
        $update_token_run = mysqli_query($conn, $update_token);

        if ($update_token_run) {
            send_password_reset($get_name, $test_email, $token);
            echo '<script>alert("A reset link has been sent to your email");</script>';
        } else {
            echo '<script>alert("Something went wrong.");</script>';
        }
    } else {
        echo '<script>alert("Email not found.");</script>';
    }
}
?>
