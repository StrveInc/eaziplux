<?php

session_start();

if (!isset($_SESSION['page_reloaded'])) {
    $_SESSION['page_reloaded'] = true; // Set the session variable to indicate page reload
    echo '<script>
            window.onload = function() {
                setTimeout(function() {
                    location.reload();
                }, 1000); // Reload the page after 1 second (1000 milliseconds)
            }
          </script>';
}

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = "";
    $database = "eaziplux";

    $conn = new mysqli($servername, $dbusername, $dbpassword, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve the user's virtual account balance
    $user_id_query = "SELECT user_id FROM users WHERE username = ?";
    $stmt = $conn->prepare($user_id_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $user_id = $row["user_id"];

        $_SESSION['USER_ID'] = $user_id;

        // Retrieve the user's balance
        $balance_query = "SELECT balance FROM virtual_accounts WHERE acct_id = ?";
        $stmt = $conn->prepare($balance_query);
        $stmt->bind_param("i", $user_id);
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
        // Handle the case where the user's ID is not found
        header("Location: ../home/getintouch.php"); // Redirect to the dashboard page
        exit;
    }

}


// Get the current hour
$currentHour = date('H');

// Customize the greeting based on the time of the day
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
    <link rel="icon" type="image/png" size="662x662" href="../css/imgs/eaziplux.png">
    <script src="https://kit.fontawesome.com/49c5823e25.js" crossorigin="anonymous"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

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
    <!-- <div class="marquee-container">
        <div class="marquee">
            <p>If patience dog see meat e for prefer am pass fatest bone! -Eaziplux</p>
        </div>
    </div> -->
    <div class="teal">
        <header>
            <div class="container container-nav">
                <div class="all">
                    <div class="logo">
                        <img src="../css/imgs/eazipluxpure.png" alt="eaziplux">
                    </div>
                    <div class="tilte">
                        <?php if (isset($_SESSION['username'])): ?>
                                    <div class=name>
                                        <p>
                                            <?php echo $greeting . "," ?>
                                        </p>
                                        <h3>
                                            <?php echo $_SESSION['username']; ?>
                                        </h3>
                                    </div>
                        <?php else: ?>
                                    <h3 class="log">
                                        <?php ?><a href="login.php">Login</a>
                                    </h3>
                        <?php endif; ?>
                    </div>
                </div>
            </div>


            <nav class="navbar">
                <div class="hmcontainer">
                    <img src="../css/imgs/userOutline.svg"/>
                </div>
            </nav>
        </header>


        <div class="topCont">
            <div class="baldash">
                <div class="bal">
                    <div class="place">
                        <p>total balance</p>

                        <p><i class="fa fa-eye-slash" onclick="toggleBalanceVisibility()" aria-hidden="true"></i></p>
                    </div>

                    <?php if (isset($account_balance)): ?>
                                <h1><i class="fa-solid fa-naira-sign"></i>
                                    <span id="balanceDisplay">
                                        <?php echo number_format($account_balance, 2); ?>
                                    </span>
                                </h1>
                    <?php else: ?>
                                <h1 id="balanceDisplay"><i class="fa-solid fa-naira-sign"></i>
                                    <?php echo "****"; ?>
                                </h1>
                    <?php endif; ?>

                </div>
                <div class="add">
                    <div class="addCont">
                        <div style="background: #043927;" class="additems">
                            <a href="../dashboard/addfund.php">
                                <img src="../css/svg/bankOutline.svg" />
                            </a>
                        </div>
                        <div class="small">Add funds</div>
                    </div>
                    
                    <div class="addCont">
                        <div style="background: grey;" class="additems">
                            <a href="../dashboard/addfund.php">
                                <img src="../css/svg/clock.svg" />
                            </a>
                        </div>
                        <div class="small">Schedule</div>
                    </div>

                    <div class="addCont">
                        <div style="background: black;" class="additems">
                            <a href="../dashboard/transaction.php">
                                <img src="../css/svg/newspaper.svg" />
                            </a>
                        </div>
                        <div class="small">
                            Transacations
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <main>
        <div class="services">
            <div class="column">
                <div class="col">
                    <a href="../dashboard/buyairtime.php">
                        <div class="icon">
                            <img src="../css/svg/phone.svg" />
                        </div>
                        <p>Airtime</p>
                    </a>
                </div>
                <div class="col">
                    <a href="../dashboard/buydata.php">
                        <div class="icon">
                        <img src="../css/svg/data.svg" />
                        </div>
                        <p>Data</p>
                    </a>
                </div>
                <div class="col">
                    <a href="#" onclick="lert()">
                        <div class="icon">
                        <img src="../css/svg/bankCardOne.svg" />
                        </div>
                        <p>Gift Card</p>
                    </a>
                </div>
            </div>

            <div class="column">
                <div class="col">
                    <a href="../dashboard/exampin.php">
                        <div class="icon">
                            <img src="" />
                        </div>
                        <p>Exam pin</p>
                    </a>
                </div>
                <div class="col">
                    <a href="../dashboard/electricity.php">
                        <div class="icon">
                            <img src="" />
                        </div>
                        <p>Electricity</p>
                    </a>
                </div>
                <div class="col">
                    <a href="../dashboard/tvsub.php">
                        <div class="icon">
                            <img src="" />
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
        if (balanceDisplay.innerText === '****') {
            // Show actual balance
            balanceDisplay.innerText = '<?php echo number_format($account_balance, 2); ?>';
        } else {
            // Hide balance
            balanceDisplay.innerText = '****';
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