<?php
session_start();

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
</head>

<body>
    <header>
        <div class="back">
            <a href="../dashboard/setting.php"><i class="fa-solid fa-chevron-left"></i></a>
        </div>
        <div class="head">
            Password Reset
        </div>
    </header>
    <main>
        <div class="container">
            <h2>Password Change</h2>
            <form method="post" action="">
                <!-- PHP logic moved to 'process_password_change.php' -->
                <div class="txt-field">
                    <input type="password" name="new_password" required>
                    <span></span>
                    <label>New Password:</label>
                </div>
                <div class="txt-field">
                    <input type="password" name="confirm_password" required>
                    <span></span>
                    <label>Confirm New Password:</label>
                </div>
                <div>
                    <input name="submit" type="submit" value="Change Password">
                </div>
            </form>
        </div>
    </main>
</body>

</html>

<?php

$servername = "localhost";  // Replace with your database server name
$dbusername = "bfgaqnyl_eaziplux";        // Replace with your database username
$dbpassword = "QrJQA2m437WfYuU9Fw3n";  // Replace with your database password
$database = "bfgaqnyl_eaziplux"; // Replace with your database name

$conn = mysqli_connect($servername, $dbusername, $dbpassword, $database);

if (isset($_POST['submit'])) {
    $new_password = mysqli_real_escape_string($conn, $_POST["new_password"]);
    $confirm_password = mysqli_real_escape_string($conn, $_POST["confirm_password"]);

    // Check if passwords match
    if ($new_password == $confirm_password) {
        // Retrieve email and reset_token from the URL parameters
        // Hash the new password before updating the database
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the user's password and reset_token in the database
        $update_password = "UPDATE users SET pass_word='$hashed_password', reset_token=NULL WHERE email='$email' AND reset_token='$token' LIMIT 1";
        $update_password_run = mysqli_query($conn, $update_password);

        if ($update_password_run) {
            echo '<script type="text/javascript">alert("Password successfully changed")</script>';
        } else {
            echo '<script type="text/javascript">alert("Error Updating password.")</script>';
        }
    } else {
        echo '<script type="text/javascript">alert("Password Does not match.")</script>';
    }
}
// Redirect back to the password_change.php page

?>