<?php
session_start();
$isInvalid = false;
$errorMsg = "";

include '../config.php'; // Include your database connection details

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

    if ($user) {
        if (password_verify($password, $user["pass_word"])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];

            // Check if the referral_code is empty
            if (empty($user['referral_code'])) {
                // Generate a unique referral code
                $referral_code = strtoupper(substr(md5(uniqid($user['user_id'], true)), 0, 8));

                // Update the user's referral_code in the database
                $update_referral_sql = "UPDATE users SET referral_code = ? WHERE user_id = ?";
                $update_stmt = $conn->prepare($update_referral_sql);
                $update_stmt->bind_param("ss", $referral_code, $user['user_id']);
                $update_stmt->execute();
                $update_stmt->close();

                // Optionally, store the referral code in the session
                $_SESSION['referral_code'] = $referral_code;
            } else {
                // Store the existing referral code in the session
                $_SESSION['referral_code'] = $user['referral_code'];
            }

            // Redirect to dashboard or home
            header("Location: ../home/dashboard.php");
            exit;
        } else {
            $isInvalid = true;
            $errorMsg = "Incorrect password.";
        }
    } else {
        $isInvalid = true;
        $errorMsg = "Account does not exist.";
    }

    if (!$result) {
        $errorMsg = "Error executing query: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="icon" type="image/png" size="662x662" href="../css/imgs/eaziplux.png">
    <meta charset="UTF-8">
        <script src="https://kit.fontawesome.com/49c5823e25.js" crossorigin="anonymous"></script>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-auth-compat.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <script>
        // Firebase configuration
        const firebaseConfig = {
            apiKey: "<?php echo $_ENV['FIREBASE_API']; ?>",
            authDomain: "<?php echo $_ENV['AUTHDOMAIN']; ?>",
            projectId: "<?php echo $_ENV['PROJID']; ?>",
            storageBucket: "<?php echo $_ENV['STORAGEBUCK']; ?>",
            messagingSenderId: "<?php echo $_ENV['SENDERID']; ?>",
            appId: "<?php echo $_ENV['APPID']; ?>"
        };

        // Initialize Firebase
        try {
            firebase.initializeApp(firebaseConfig);
            console.log("Firebase initialized successfully");
        } catch (error) {
            console.error("Firebase initialization error:", error);
        }

        function isValidEmail(email) {
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            return emailRegex.test(email);
        }

        document.addEventListener('DOMContentLoaded', function () {
            const firebaseLoginButton = document.querySelector('#firebaseLoginButton');
            const normalLoginButton = document.querySelector('#normalLoginButton');
            const preloader = document.getElementById('preloader');
            const errorDiv = document.getElementById('errorDiv');

            // Firebase Google Login
            firebaseLoginButton.addEventListener('click', function (e) {
                e.preventDefault();
                preloader.style.display = 'flex';

                const provider = new firebase.auth.GoogleAuthProvider();

                firebase.auth().signInWithPopup(provider)
                    .then((result) => {
                        const user = result.user;
                        // Redirect to the server-side handler with the user's details
                        const redirectUrl = `../api/firebaseLoginHandler.php?email=${encodeURIComponent(user.email)}&firebase_uid=${encodeURIComponent(user.uid)}`;
                        window.location.href = redirectUrl;
                    })
                    .catch((error) => {
                        preloader.style.display = 'none';
                        if (errorDiv) {
                            errorDiv.textContent = error.message;
                            errorDiv.style.display = 'block';
                        }
                    });
            });

            // Normal Email and Password Login
            normalLoginButton.addEventListener('click', function (e) {
                e.preventDefault();

                const email = document.querySelector('input[name="email"]').value.trim();
                const password = document.querySelector('input[name="password"]').value;

                if (!isValidEmail(email)) {
                    if (errorDiv) {
                        errorDiv.textContent = "Please enter a valid email address.";
                        errorDiv.style.display = 'block';
                    }
                    return;
                }

                if (!password) {
                    if (errorDiv) {
                        errorDiv.textContent = "Please enter your password.";
                        errorDiv.style.display = 'block';
                    }
                    return;
                }

                // Show preloader and submit the form for normal login
                preloader.style.display = 'flex';
                document.querySelector('#normalLoginForm').submit();
            });
        });
    </script>

    <style>
        .preloader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(8px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .preloader-img {
            width: 100px;
            height: 100px;
        }
        .error-message {
            background: #e74c3c;
            color: #fff;
            padding: 10px 18px;
            border-radius: 6px;
            margin: 18px 0 10px 0;
            text-align: center;
            font-size: 15px;
            display: <?php echo $isInvalid ? 'block' : 'none'; ?>;
        }

        i{
            color: white;
            padding-right: 5px;
        }
    </style>
</head>

<body>
    <div class="preloader-overlay" id="preloader">
        <img src="../css/imgs/eazi.gif" class="preloader-img" alt="Loading...">
    </div>
    <main>
        <div class="cont">
            <div class="logo">
                <img src="../css/imgs/eazipluxpure.png" alt="eaziplux">
            </div>
            <div class="column2">
                <div class="container">
                    <!-- Error message display -->
                    <div id="errorDiv" class="error-message"><?php if ($isInvalid) echo htmlspecialchars($errorMsg); ?></div>
                    <div style="padding-left: 15px; font-size: 25px; font-weight: 700; color: white;">Login</div>
                    <form id="normalLoginForm" method="post">
                        <div class="txt-field">
                            <input type="email" placeholder="Email" name="email" required>
                        </div>
                        <div class="txt-field" style="margin-bottom: 0; position:relative;">
                            <input type="password" placeholder="Password" style="width: 85%;" name="password" id="login_password" required>
                            <span class="toggle-eye" onclick="togglePassword('login_password', this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;">
                                <i class="fa fa-eye-slash"></i>
                            </span>
                        </div>
                        <div style="color:rgba(255, 191, 0, 0.68); width: 90%; margin: auto; font-size: 11px; font-weight: 500; margin-bottom: 20px; text-align: right;">
                            <a href="resetpassword.php" style="color: red">Forgot your password?</a>
                        </div>
                    </form>
                    <div class="loginCont">
                        <button type="button" id="normalLoginButton" class="login">Login</button>
                    </div>
                    <div style="color: #ccc; text-align: center; margin: 10px 0; font-size: 14px;">
                        continue with
                    </div>
                    <div class="loginCont">
                        <button type="button" id="firebaseLoginButton" class="login" style="background-color: transparent; color: white; display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <img src="../css/svg/google.svg" alt="Google Icon" style="width: 20px; height: 20px;">Google
                        </button>
                    </div>
                    <div style="color: white; font-size: 14px; text-align: center; margin-top: 10px;">
                        Dont have an account? <a href="signup.php" style="color: #ffbf00; text-decoration: none;">Sign Up</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <div class="footer" style="position: absolute; bottom: 0; width: 100%; text-align: center; color: #ccc; font-size: 12px;">
            Powered by Strive inc.
        </div>
    </footer>
    <script>
        // Password visibility toggle for login password
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