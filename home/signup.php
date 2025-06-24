<?php
session_start();
$is_invalid = false;
$errorMsg = "";
include '../config.php';

// AJAX email check
if (isset($_POST["check_email"])) {

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the email already exists in the database
    $check_email_sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_email_sql);
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['emailerror'] = "Email Already Taken!!!";
        echo json_encode(['status' => 'error', 'message' => 'Email already taken']);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Email is available']);
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" size="662x662" href="../css/imgs/eaziplux.png">
    <link rel="stylesheet" href="../css/signup.css">
    <script src="https://kit.fontawesome.com/49c5823e25.js" crossorigin="anonymous"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-auth-compat.js"></script>
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
            display: none;
        }

        i{
            color: white
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const emailField = document.querySelector("#emailField");
            const usernameField = document.querySelector("#usernameField");
            const phoneField = document.querySelector("#phoneField");
            const passwordField = document.querySelector("#passwordField");
            const proceedButton = document.querySelector("#proceedButton");
            const googleAuthButton = document.querySelector("#googleAuthButton");
            const errorDiv = document.getElementById('errorDiv');
            const preloader = document.getElementById('preloader');

            // Initially hide all fields except the email field
            usernameField.style.display = "none";
            phoneField.style.display = "none";
            passwordField.style.display = "none";
            proceedButton.textContent = "Proceed";

            proceedButton.addEventListener("click", function (e) {
                e.preventDefault();

                if (emailField.style.display !== "none") {
                    const email = document.querySelector("input[name='email']").value;

                    // Validate email via AJAX
                    preloader.style.display = 'flex';
                    fetch("", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                        },
                        body: `check_email=true&email=${encodeURIComponent(email)}`,
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            preloader.style.display = 'none';
                            if (data.status === "success") {
                                emailField.style.display = "none";
                                usernameField.style.display = "block";
                                errorDiv.style.display = "none";
                            } else {
                                errorDiv.textContent = data.message;
                                errorDiv.style.display = "block";
                            }
                        })
                        .catch(() => {
                            preloader.style.display = 'none';
                            errorDiv.textContent = "Network error. Please try again.";
                            errorDiv.style.display = "block";
                        });
                } else if (usernameField.style.display !== "none") {
                    usernameField.style.display = "none";
                    phoneField.style.display = "block";
                } else if (phoneField.style.display !== "none") {
                    phoneField.style.display = "none";
                    passwordField.style.display = "block";
                    proceedButton.textContent = "Register"; // Change button text to "Register"
                } else if (passwordField.style.display !== "none") {
                    // Submit the form
                    preloader.style.display = 'flex';
                    const form = document.querySelector("form");
                    form.action = "../api/signupApi.php";
                    form.enctype = "application/x-www-form-urlencoded";
                    form.method = "POST";
                    form.submit();
                }
            });

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

            // Google Authentication
            googleAuthButton.addEventListener("click", function () {
                preloader.style.display = 'flex';
                const provider = new firebase.auth.GoogleAuthProvider();

                firebase.auth().signInWithPopup(provider)
                    .then((result) => {
                        const user = result.user;
                        // Send user details to the server
                        fetch("../api/firebaseGoogleAuth.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded",
                            },
                            body: `email=${encodeURIComponent(user.email)}&username=${encodeURIComponent(user.displayName)}&firebase_uid=${encodeURIComponent(user.uid)}`
                        })
                            .then((response) => {
                                if (!response.ok) {
                                    throw new Error(`HTTP error! status: ${response.status}`);
                                }
                                return response.json();
                            })
                            .then((data) => {
                                if (data.status === "success") {
                                    window.location.href = "../home/dashboard.php";
                                } else {
                                    preloader.style.display = 'none';
                                    errorDiv.textContent = data.message;
                                    errorDiv.style.display = "block";
                                }
                            })
                            .catch((error) => {
                                preloader.style.display = 'none';
                                errorDiv.textContent = "Error sending data to server.";
                                errorDiv.style.display = "block";
                                console.error("Error sending data to server:", error);
                            });
                    })
                    .catch((error) => {
                        preloader.style.display = 'none';
                        errorDiv.textContent = error.message;
                        errorDiv.style.display = "block";
                        console.error("Google sign-in error:", error);
                    });
            });

            // Handle sign-in result from redirect
            firebase.auth().getRedirectResult()
                .then((result) => {
                    if (result.user) {
                        preloader.style.display = 'flex';
                        const user = result.user;
                        // Send user details to the server for database storage
                        fetch("../api/firebaseGoogleAuth.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded",
                            },
                            body: `email=${encodeURIComponent(user.email)}&username=${encodeURIComponent(user.displayName)}&firebase_uid=${encodeURIComponent(user.uid)}`
                        })
                            .then((response) => response.json())
                            .then((data) => {
                                if (data.status === "success") {
                                    window.location.href = "../home/dashboard.php";
                                } else {
                                    preloader.style.display = 'none';
                                    errorDiv.textContent = data.message;
                                    errorDiv.style.display = "block";
                                }
                            });
                    }
                })
                .catch((error) => {
                    preloader.style.display = 'none';
                    errorDiv.textContent = error.message;
                    errorDiv.style.display = "block";
                    console.error("Google sign-in error via redirect:", error);
                });
        });
    </script>
</head>
<body>
    <div class="preloader-overlay" id="preloader">
        <img src="../css/imgs/eazi.gif" class="preloader-img" alt="Loading...">
    </div>
    <main>
        <div class="logo">
            <img src="../css/imgs/eazipluxpure.png" alt="bfgaqnyl_eaziplux">
        </div>
        <div class="cont">
            <div class="column2">
                <div class="container">
                    <div id="errorDiv" class="error-message"></div>
                    <form method="post" action="../api/signupApi.php" id="signupForm">
                        <div style="color: white; padding-left: 15px; font-size: 25px; font-weight: 700;">Create an Account</div>
                        <div class="txt-field" id="emailField">
                            <input type="email" placeholder="Email" name="email" required>
                        </div>
                        <div class="txt-field" id="usernameField">
                            <input type="text" placeholder="Username" name="username" required>
                        </div>
                        <div class="txt-field" id="phoneField">
                            <input type="tel" placeholder="Phone number" name="phone" required>
                        </div>
                        <div class="txt-field" id="passwordField" style="position:relative;">
                            <input type="password" placeholder="Password" name="password" id="signup_password" style="width: 85%;" required>
                            <span class="toggle-eye" onclick="togglePassword('signup_password', this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;">
                                <i class="fa fa-eye-slash"></i>
                            </span>
                        </div>
                        <div class="loginCont">
                            <button id="proceedButton" class="login">Proceed</button>
                        </div>
                        <div style="color: #ccc; text-align: center; margin: 10px 0; font-size: 14px;">
                            continue with
                        </div>
                        <div class="loginCont">
                            <button type="button" id="googleAuthButton" class="login" style="background-color: transparent; color: white; display: flex; align-items: center; justify-content: center; gap: 10px;">
                                <img src="../css/svg/google.svg" alt="Google Icon" style="width: 20px; height: 20px;"> Google
                            </button>
                        </div>
                        <div style="color: white; font-size: 14px; text-align: center; margin-top: 10px;">
                            Already have an account? <a href="login.php" style="color: #ffbf00; text-decoration: none;">Login</a>
                        </div>
                    </form>
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
        // Password visibility toggle for signup password
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
