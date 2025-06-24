<?php
session_start();

include '../config.php'; // Include your database connection details

// Ensure both user_id and username are set
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

// (Optional) Remove or adjust reloading logic if unnecessary
if (!isset($_SESSION['page_reloaded'])) {
    $_SESSION['page_reloaded'] = true;
    echo '<script>
            window.onload = function() {
                setTimeout(function() {
                    location.reload();
                }, 1000);
            }
          </script>';
}

// // Retrieve the user's ID using the username
// $user_id_query = "SELECT user_id FROM users WHERE username = ?";
// $stmt = $conn->prepare($user_id_query);
// $stmt->bind_param("s", $username);
// $stmt->execute();
// $result = $stmt->get_result();

if ($_SESSION['user_id']) {
    $user_id = $_SESSION['user_id'];
    // $row = $result->fetch_assoc();

    // Retrieve the user's balance
    $balance_query = "SELECT balance FROM virtual_accounts WHERE acct_id = ?";
    $stmt = $conn->prepare($balance_query);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $balance_result = $stmt->get_result();

    if ($balance_result->num_rows == 1) {
        $balance_row = $balance_result->fetch_assoc();
        $account_balance = $balance_row["balance"];
        $_SESSION['acct'] = $account_balance;
    } else {
        $account_balance = 0; // Default balance if not found
    }
} else {
    // If the user's ID is not found, redirect to a useful page such as getintouch.php
    header("Location: login.php");
    exit;
}

// Get the current hour
$currentHour = date('H');

// Customize greeting based on time of day
if ($currentHour >= 1 && $currentHour < 12) {
    $greeting = "Good morning";
} elseif ($currentHour >= 12 && $currentHour < 18) {
    $greeting = "Good afternoon";
} else {
    $greeting = "Good evening";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&family=Truculenta:opsz,wght@12..72,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/variable.css">
    <link rel="icon" type="image/png" size="662x662" href="../css/imgs/eaziplux.png">
    <script src="https://kit.fontawesome.com/49c5823e25.js" crossorigin="anonymous"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
  .my-icon path {
    fill: white;
  }
</style>


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
        .marquee-container {
    width: 100%;
    overflow: hidden; /* Hide overflowing content */
    position: relative;
    padding: 0;
}

.marquee {
    display: inline-block;
    white-space: nowrap; /* Prevent text wrapping */
    animation: marquee 15s linear infinite; /* Adjust animation duration as needed */
}

.marquee p{
    color: white;
}

@keyframes marquee {
    0% { transform: translateX(100%); } /* Start offscreen to the right */
    100% { transform: translateX(-100%); } /* Move to the left */
}
    
    </style>

</head>

<body>
   
    <div class="teal">
        <header>
            <div class="container container-nav">
                <div class="all">
                    <div class="tilte">
                        <?php if (isset($_SESSION['username'])): ?>
                                <div style="color: #ddd;">
                                    <div style="font-weight: 500; font-size: 14px; border: 0px solid">
                                        <?php echo $greeting ?> ,
                                    </div>
                                    <div style="font-size: 14px; font-weight:700 ; margin-top: -3px; border: 0px solid; width: 70%;  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo isset($username) ? $username : "Guest"; ?>
                                    </div>
                                </div>
                        <?php else: ?>
                                    <div class="log">
                                        <?php ?><a href="login.php">Login</a>
                                    </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <a href="../dashboard/setting.php">
                            <img style="width: 36px; height: 36px; padding: 6px;" src="../css/imgs/userOutline.svg"/>
                        </a>
                    </div>
                </div>
            </div>
        </header>



        <div class="topCont">
            <div class="baldash">
                <div style="margin: auto; font-size: 12px; border:0px solid black; text-align: left; width: 95%; margin-top: -3px; font-weight: 400; color: #ffbf00;">
                    <a style="text-decoration: none; color: #ffbf00;" href="../dashboard/transaction.php"><img src="../css/svg/script.svg" style="width: 9px; padding-right: 3px;">Transaction History </a>
                </div>
                <div class="bal">
                <div>
                    &#8358;
                </div>
                <div class="place" style="display: flex; align-items: center; justify-content: space-between;">    
                    <div class="balAmount" id="balanceDisplay">
                        <?php if (isset($account_balance)): ?>
                            <?php echo number_format($account_balance, 2); ?>
                        <?php else: ?>
                             <?php echo "0.00"; ?>
                        <?php endif; ?>
                    </div>
                    <div class="eye">
                        <i class="fa fa-eye-slash" id="balanceEye" onclick="toggleBalanceVisibility()" aria-hidden="true"></i>
                    </div>
                </div>
                </div>
                
                <div class="add">
                        <a href="../dashboard/transfer.php">
                           Manage funds
                        </a>
                    </div>
            </div>
        </div>
    </div>
    <main>
        <div class="services">
            <div class="column">
                <div class="col">
                    <a href="../airtime/mtnairtime.php">
                        <div class="icon">
                            <img src="../css/icon/signal.svg" />
                        </div>
                        <p>Airtime</p>
                    </a>
                </div>
                <div class="col">
                    <a href="../data/mtndata.php">
                        <div class="icon">
                        <img src="../css/icon/data.svg" />
                        </div>
                        <p>Data</p>
                    </a>
                </div>
                <div class="col">
                    <a href="../dashboard/giftcard.php">
                        <div class="icon">
                        <img src="../css/icon/giftcard.svg" />
                        </div>
                        <p>Gift Card</p>
                    </a>
                </div>
            </div>

            <div class="column">
                <div class="col">
                    <a href="#" onclick="lert()">
                        <div class="icon">
                            <img src="../css/icon/degree-credential.svg" />
                        </div>
                        <p>Exam pin</p>
                    </a>
                </div>
                <div class="col">
                    <a href="../electricity/eko.php">
                        <div class="icon">
                            <img src="../css/icon/electric.svg" />
                        </div>
                        <p>Electricity</p>
                    </a>
                </div>
                <div class="col">
                    <a href="../tvsub/dstvsub.php">
                        <div class="icon">
                            <img src="../css/icon/satellite.svg" />
                        </div>
                        <p>Tv sub</p>
                    </a>
                </div>
            </div>
            <!-- <div class="column">
                <div class="col">
                    <a href="#" onclick="lert()">
                        <div class="icon">
                            <img src="" />
                        </div>
                        <p>Gift Card</p>
                    </a>
                </div>
                <div class="col">
                    <a href="#" onclick="lert()">
                        <i class="fa-solid fa-comment-sms"></i>
                        <p>Bulk SMS</p>
                    </a>
                </div>
                <div class="col">
                    <a href="#" onclick="lert()">
                        <i class="fa-solid fa-ticket"></i>
                        <p>Tickets</p>
                    </a>
                </div>
            </div> -->
            </div>
            <div class="foot">
             <div class="advertBlock">
                <div style="width: 65%; border: 0px solid white; height: 100%; padding: 5px;">
                    <div style="border: 0px solid white; padding: 3px; font-weight: 500; font-size: 21px; margin-top: 4px;">Advertise with Us!</div>
                    <div style="font-size: 15px; padding: 3px;">Get noticed. contact us today</div>
                </div>
                <div style="width: 35%; height: 100%; background: black;">
                    <img 
                        src="../css/imgs/eazipluxpure.png" 
                        style="width: 100%; height: 100%; object-fit: contain;" 
                    />
                </div>
            </div>
            <div style="color: #ccc; border: 0px solid white; text-align: center; font-size: 12px; font-weight: 400; margin-top: 5px">Powered by Strive inc.</div>
            </div>
        </div>
    </main>
    
</body>

<script>
    function lert() {
        alert('COMING SOON👌');
    };

    function unable() {
        alert('CURRENTLY UNAVAILABLE⚠')
    };
</script>

<script>
    function toggleBalanceVisibility() {
        var balanceDisplay = document.getElementById('balanceDisplay');
        var eyeIcon = document.getElementById('balanceEye');
        var actualBalance = <?php echo json_encode(number_format($account_balance, 2)); ?>;

        if (balanceDisplay.innerText === '****') {
            // Show actual balance
            balanceDisplay.innerText = actualBalance;
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            // Hide balance
            balanceDisplay.innerText = "****";
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }


    function toggleMenu() {
        var menuToggle = document.querySelector('.menu-toggle');
        var menu = document.querySelector('.menu');
        menuToggle.classList.toggle('active');
        menu.classList.toggle('active');
    }
</script>

</html>