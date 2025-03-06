<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../css/buyairtime.css">
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
    <title>BUY AIRTIME</title>
</head>

<body>
    <header>
        <div class="back">
            <a href="../home/dashboard.php">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
        </div>
        <div class="head">Airtime</div>
    </header>
    <main>
        <div class="container">
            <div class="network">
                <div class="col1">
                    <div class="net1">
                        <a href="../airtime/mtnairtime.php">
                            <div class="mobilelogo">
                                <img src="../css/imgs/mtnupdate.png" alt="mtn">
                            </div>
                        </a>
                    </div>

                    <div class="net2">
                        <a href="../airtime/9mobileairtime.php">
                            <div class="mobilelogo">
                                <img src="../css/imgs/9mobileupdate.png" alt="9mobile">
                            </div>
                        </a>
                    </div>
                </div>

                <div class="col2">
                    <div class="net3">
                        <a href="../airtime/airtelairtime.php">
                            <div class="mobilelogo">
                                <img src="../css/imgs/airtelupdate.png" alt="9mobile">
                            </div>
                        </a>
                    </div>


                    <div class="net4">
                        <a href="../airtime/gloairtime.php">
                            <div class="mobilelogo">
                                <img src="../css/imgs/gloupdate.jpg" alt="9mobile">
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

<?php 
//     if (isset($_SESSION['username'])) {
//         $username = $_SESSION['username'];
    
       
// $servername = "localhost";
// $username = "root";
// $dbpassword = "";
// $database = "eaziplux";
// $conn = new mysqli($servername, $username, $dbpassword, $database);
    
//         $_SESSION['conn'] = $conn;
    
//         if ($conn->connect_error) {
//             die("Connection failed: " . $conn->connect_error);
//         }
//         $username = $_SESSION['username'];
    
//         $user_id_query = "SELECT user_id FROM users WHERE username = '$username'";
//         $result = $conn->query($user_id_query);
    
//         if ($result->num_rows == 1) {
//             $row = $result->fetch_assoc();
//             $user_id = $row["user_id"];
    
//                 $balance_query = "SELECT balance FROM virtual_accounts WHERE acct_id = $user_id";
//             $balance_result = $conn->query($balance_query);
    
//             $_SESSION["USER_ID"] = $user_id;
    
//             if ($balance_result->num_rows == 1) {
//                 $balance_row = $balance_result->fetch_assoc();
//                 $account_balance = $balance_row["balance"];
    
//                 $_SESSION['ACCT'] = $account_balance;
//             } else {
//                 $account_balance = 0;
//             }
//         } else {
//         header("Location: ../home/signup.php");
//         }
//     } else {
//         header("Location: ../home/login.php");
//         exit;
//     }
?>