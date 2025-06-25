<?php
session_start();
$message = "";

// Database connection
include '../config.php';

// Symfony Mailer setup
require_once('../vendor/autoload.php');
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

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

// Function to send password reset email using Symfony Mailer
function send_password_reset($get_name, $get_email, $token) {
    // Configure your SMTP DSN (see Symfony Mailer docs)
    $transport = Transport::fromDsn($_ENV['MAILER_DSN']);
    $mailer = new Mailer($transport);

    $os = getOS();
    $browser = getBrowser();

    $reset_link = "https://eaziplux.com.ng/home/password_change.php?token=$token&email=$get_email";
    $logo_url = "https://eaziplux.com.ng/css/imgs/eazipluxpure.png"; // Update to your logo URL

    $html = <<<EOT
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset - Eaziplux</title>
</head>
<body style="font-family: 'Poppins', Arial, sans-serif; background: #f7f8fa; margin:0; padding:0;">
    <div style="max-width: 480px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 32px;">
        <div style="text-align: center;">
            <img src="$logo_url" alt="Eaziplux Logo" style="width: 80px; margin-bottom: 20px;">
        </div>
        <h2 style="color: #222; text-align: center;">Reset your password</h2>
        <p style="color: #444; font-size: 16px;">Hi <b>$get_name</b>,</p>
        <p style="color: #444; font-size: 15px;">
            We received a request to reset your Eaziplux password.<br>
            Click the button below to set a new password.
        </p>
        <div style="text-align: center; margin: 32px 0;">
            <a href="$reset_link" style="background: #ffbf00; color: #222; text-decoration: none; font-weight: 600; padding: 14px 32px; border-radius: 30px; font-size: 16px; display: inline-block;">
                Reset Password
            </a>
        </div>
        <p style="color: #888; font-size: 13px; text-align: center;">
            If you did not request a password reset, please ignore this email.<br>
            <br>
            <b>Device:</b> $os<br>
            <b>Browser:</b> $browser
        </p>
        <hr style="border: none; border-top: 1px solid #eee; margin: 24px 0;">
        <div style="color: #aaa; font-size: 12px; text-align: center;">
            &copy; Eaziplux &mdash; Plot 1323 Olaleye Oluwa Street, Unity Estate<br>
            Need help? <a href="mailto:support@eaziplux.com.ng" style="color: #ffbf00;">Contact Support</a>
        </div>
    </div>
</body>
</html>
EOT;

    $email = (new Email())
        ->from('support@eaziplux.com.ng')
        ->to($get_email)
        ->subject('Reset your Eaziplux password')
        ->html($html);

    $mailer->send($email);
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

        $update_token = "UPDATE users SET reset_token='$token' WHERE email='$get_email' LIMIT 1";
        $update_token_run = mysqli_query($conn, $update_token);

        if ($update_token_run) {
            send_password_reset($get_name, $get_email, $token);
            $message = "A reset link has been sent to your email.";
        } else {
            $message = "Something went wrong.";
        }
    } else {
        $message = "Email not found.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" size="662x662" href="../css/imgs/eazipluxpure.png">
    <link rel="stylesheet" href="../css/resetpassword.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/49c5823e25.js" crossorigin="anonymous"></script>
    <title>Forgot Password</title>
    <meta charset="UTF-8">
    <style>
        .preloader-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(8px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .preloader-img { width: 100px; height: 100px; }
        .msg { color: #ffbf00; text-align: center; margin-bottom: 15px; font-size: 16px; }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="preloader-overlay" id="preloader">
        <img src="../css/imgs/eazi.gif" class="preloader-img" alt="Loading...">
    </div>
    <main>
        <div class="container">
            <div style="margin: auto; border: 0px solid white; margin-top: 4rem; font-size: 14px; font-weight: 600; width: 90%;">Enter your email address and we'll send you a password reset link.</div>
            <?php if (!empty($message)): ?>
                <div class="msg"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <div style="border: 0px solid white; width: 90%; margin: auto;">Email:</div>
                <div class="txt-field">
                    <input type="text" name="email" required>
                </div>
                <div class="reset">
                    <input name="submit" type="submit" value="Reset Password">
                </div>
            </form>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('form').addEventListener('submit', function() {
                document.getElementById('preloader').style.display = 'flex';
            });
        });
    </script>
</body>
</html>