<?php
session_start();
$is_invalid = false;

if (isset($_POST["submit"])) {
    $servername = "localhost";  // Replace with your database server name
    $dbusername = "root";       // Replace with your database username
    $dbpassword = "";          // Replace with your database password
    $database = "eaziplux";     // Replace with your database name
    
    $conn = new mysqli($servername, $dbusername, $dbpassword, $database);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Check if the email already exists in the database
    $check_email_sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_email_sql);
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        // Email doesn't exist, proceed with user registration
        $email = $_POST['email'];
        $username = $_POST['username'];
        $phone = $_POST['phone'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $user_id = bin2hex(random_bytes(16)); // Generate unique user_id
        
        // SQL query to insert user data into the users table
        $insert_user_sql = "INSERT INTO users (user_id, email, username, phone_number, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_user_sql);
        $stmt->bind_param("sssss", $user_id, $email, $username, $phone, $password);
        
        if ($stmt->execute()) {
            // User registration successful
            
            // Create an entry in the virtual account table
            $create_virtual_account_sql = "INSERT INTO virtual_accounts (user_id, balance) VALUES (?, 0)";
            $stmt = $conn->prepare($create_virtual_account_sql);
            $stmt->bind_param("s", $user_id); // Use generated user_id
            
            if ($stmt->execute()) {
                // Virtual account creation successful
                header("Location: login.php");
                exit();
            } else {
                echo "Error creating virtual account: " . $stmt->error;
            }
        } else {
            echo "Error creating user: " . $stmt->error;
        }
    } else {
        // Email already exists, show an error message
        $is_invalid = true;
        $_SESSION['emailerror'] = "Email Already Taken!!!";
    }
    
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" size="662x662" href="../css/imgs/eaziplux.png">
    <link rel="stylesheet" href="../css/signup.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-VVN0P5EYQP"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-VVN0P5EYQP');
    </script>
</head>
<body>
    <main>
        <div class="cont">
            <div class="column2">
                <div class="container">
                    <form method="post">
                        <h2>Register</h2>
                        <?php if (isset($_SESSION["emailerror"])): ?>
                            <p><?php echo $_SESSION["emailerror"]; ?></p>
                        <?php endif; ?>
                        <p>Fill in Your Details</p>
                        <div class="txt-field">
                            <input type="email" name="email" required>
                            <label>Email</label>
                        </div>
                        <div class="txt-field">
                            <input type="text" name="username" required>
                            <label>Username</label>
                        </div>
                        <div class="txt-field">
                            <input type="tel" name="phone" required>
                            <label>Phone Number</label>
                        </div>
                        <div class="txt-field">
                            <input type="password" name="password" required>
                            <label>Password</label>
                        </div>
                        <div class="remember">
                            <input type="checkbox">
                            <label>Remember Me</label>
                        </div>
                        <input type="submit" name="submit" class="login" value="Register">
                        <div class="signup">Already a member? | <a href="login.php">Login</a></div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
