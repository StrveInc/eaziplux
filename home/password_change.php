<?php
session_start();

$message = "";
$messageType = ""; // success or error

include '../config.php';



if (isset($_POST['submit'])) {
    $new_password = mysqli_real_escape_string($conn, $_POST["new_password"]);
    $confirm_password = mysqli_real_escape_string($conn, $_POST["confirm_password"]);

    if ($new_password == $confirm_password) {
        $email = $_GET['email'];
        $token = $_GET['token'];

        $check_token = "SELECT * FROM users WHERE email='$email' AND reset_token='$token' LIMIT 1";
        $check_token_run = mysqli_query($conn, $check_token);

        if (mysqli_num_rows($check_token_run) > 0) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_password = "UPDATE users SET pass_word='$hashed_password', reset_token=NULL WHERE email='$email' AND reset_token='$token' LIMIT 1";
            $update_password_run = mysqli_query($conn, $update_password);

            if ($update_password_run) {
                $message = "Password successfully changed. Redirecting to login.";
                $messageType = "success";
                // Redirect after 2 seconds
                echo "<script>setTimeout(function(){ window.location.href = '../home/login.php'; }, 2000);</script>";
            } else {
                $message = "Error updating password.";
                $messageType = "error";
            }
        } else {
            $message = "Link expired. Request a new link.";
            $messageType = "error";
        }
    } else {
        $message = "Passwords do not match.";
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="icon" type="image/png" size="662x662" href="../css/imgs/eaziplux.png">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <script src="https://kit.fontawesome.com/49c5823e25.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../css/resetpassword.css">
    <title>Password Change</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        .message-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }

        .message-box {
            background: #222;
            padding: 40px 30px;
            border-radius: 10px;
            text-align: center;
            min-width: 280px;
            max-width: 90vw;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3);
        }

        .message-box.success {
            border: 2px solid #4caf50;
        }

        .message-box.error {
            border: 2px solid #e74c3c;
        }

        .message-box .close-btn {
            margin-top: 20px;
            background: #ffbf00;
            color: #222;
            border: none;
            border-radius: 5px;
            padding: 8px 22px;
            font-size: 16px;
            cursor: pointer;
        }
    </style>
</head>

<body>
<div class="preloader-overlay" id="preloader">
        <img src="../css/imgs/eazi.gif" class="preloader-img" alt="Loading..."> <!-- Replace with your preloader image -->
    </div>

    <?php if (!empty($message)): ?>
    <div class="message-overlay" id="messageOverlay">
        <div class="message-box <?= $messageType ?>">
            <div><?= htmlspecialchars($message) ?></div>
            <?php if ($messageType !== "success"): ?>
                <button class="close-btn" onclick="document.getElementById('messageOverlay').style.display='none'">Close</button>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

    <main>
        <div class="container">
            <div style="border: 0px solid white; margin: auto; margin-top: 2rem; font-size: 20px; font-weight: 700; width: 90%;">Set new password</div>
            <form method="post" action="">
                <div class="txt-field" style="position:relative;">
                    <input type="password" name="new_password" id="new_password" placeholder="New Password" required>
                    <span class="toggle-eye" onclick="togglePassword('new_password', this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;">
                        <i class="fa fa-eye-slash"></i>
                    </span>
                </div>
                <div class="txt-field" style="position:relative;">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" required>
                    <span class="toggle-eye" onclick="togglePassword('confirm_password', this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;">
                        <i class="fa fa-eye-slash"></i>
                    </span>
                    <span></span>
                </div>
                <div class="reset">
                    <input name="submit" type="submit" value="Change Password">
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

        // Password visibility toggle
        function togglePassword(inputId, el) {
            const input = document.getElementById(inputId);
            const icon = el.querySelector('i');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = "password";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>

</html>