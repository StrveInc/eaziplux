<?php
session_start();

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $servername = "localhost";
    $username = "root";
    $dbpassword = "";
    $database = "eaziplux";

    $conn = new mysqli($servername, $username, $dbpassword, $database);
    $_SESSION['conn'] = $conn;

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $username = $_SESSION['username'];

    $user_id_query = "SELECT user_id FROM users WHERE username = '$username'";
    $result = $conn->query($user_id_query);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $user_id = $row["user_id"];

        $balance_query = "SELECT balance FROM virtual_accounts WHERE acct_id = $user_id";
        $balance_result = $conn->query($balance_query);

        $_SESSION["USER_ID"] = $user_id;

        if ($balance_result->num_rows == 1) {
            $balance_row = $balance_result->fetch_assoc();
            $account_balance = $balance_row["balance"];

            $_SESSION['ACCT'] = $account_balance;
        } else {
            $account_balance = 0;
        }
    } else {
        header("Location: ../home/signup.php");
    }
} else {
    header("Location: ../home/login.php"); // Redirect to the dashboard page
    exit;
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="../css/buyairtime.css">
    <meta charset="UTF-8">
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELECTRICITY</title>
</head>

<body>
    <header>
        <div class="back">
            <a href="../home/dashboard.php"><i class="fa-solid fa-chevron-left"></i></a>
        </div>
        <div class="head">Electricity</div>
    </header>
    <main>
        <div class="container">
            <div class="company">
                <div class="col1">
                    <div class="elect">
                        <a href="../electricity/ikeja.php">
                            <div class="mobilelogo">
                                <img src="../css/imgs/ikeja.png" alt="mtn">
                            </div>
                        </a>
                    </div>


                    <div class="elect">
                        <a href="../electricity/aedc.php">
                            <div class="mobilelogo">
                                <img src="../css/imgs/aedc.png" alt="9mobile">
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col1">
                    <div class="elect">
                        <a href="../electricity/ibadan.php">
                            <div class="mobilelogo">
                                <img src="../css/imgs/ibedc.jpg" alt="9mobile">
                            </div>
                        </a>
                    </div>


                    <div class="elect">
                        <a href="../electricity/eko.php">
                            <div class="mobilelogo">
                                <img src="../css/imgs/ekedc.png" alt="9mobile">
                            </div>
                        </a>
                    </div>
                </div>


                <div class="col1">
                    <div class="elect">
                        <a href="../electricity/jos.php">
                            <div class="mobilelogo">
                                <img src="../css/imgs/jos.jpg" alt="mtn">
                            </div>
                        </a>
                    </div>


                    <div class="elect">
                        <a href="../electricity/kaduna.php">
                            <div class="mobilelogo">
                                <img src="../css/imgs/kaduna.png" alt="9mobile">
                            </div>
                        </a>
                    </div>
                </div>

                <div class="col1">
                    <div class="elect">
                        <a href="../electricity/kano.php">
                            <div class="mobilelogo">
                                <img src="../css/imgs/kano.png" alt="9mobile">
                            </div>
                        </a>
                    </div>


                    <div class="elect">
                        <a href="../electricity/port.php">
                            <div class="mobilelogo">
                                <img src="../css/imgs/phd.png" alt="9mobile">
                            </div>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </main>


    <script type="text/javascript">
        var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
        (function () {
            var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
            s1.async = true;
            s1.src = 'https://embed.tawk.to/658c01c070c9f2407f83aa82/1hilednbb';
            s1.charset = 'UTF-8';
            s1.setAttribute('crossorigin', '*');
            s0.parentNode.insertBefore(s1, s0);
        })();
    </script>
</body>

</html>