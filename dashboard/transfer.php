<?php

session_start();

// Check if the 'username' session variable is set
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("Location: ../home/login.php");
    exit;
}

// For testing/demo purposes
// $_SESSION['email'] = 'strveinc@gmail.com';
// $_SESSION['user_id'] = 'TjwH83BKBmSnckZ21uJaG4hwNZ33';
// $_SESSION['username'] = 'victory';

// Handle reload after linking phone (reload only once)
if (isset($_SESSION['just_linked_phone'])) {
    unset($_SESSION['just_linked_phone']);
    // After reload, show account details, don't reload again
}

include '../config.php'; // Include your database connection details

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assign session value to a variable for bind_param
$user_id = $_SESSION["user_id"];
$stmt = $conn->prepare("SELECT acct_number, acct_name, bank_name FROM virtual_accounts WHERE acct_id=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($dbAcctNumber, $dbAcctName, $dbBankName);
$stmt->fetch();
$stmt->close();
$conn->close();

// Check if account number is set
// Fix trim() deprecation warning
$show_link_phone = empty(trim((string)$dbAcctNumber));

// Handle phone linking and DVA creation
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../css/transfer.css">
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" size="662x662" href="../css/imgs/eazipluxpure.png">
    <script src="https://kit.fontawesome.com/49c5823e25.js" crossorigin="anonymous"></script>
    <meta name="description"
        content="Manage your mobile data and pay bills seamlessly with Eazi Plux. Enjoy a convienient and secure platform for handling all your mobile-related transactions.">
    <meta charset="UTF-8">
    <meta name="keywords"
        content="discounted mobile data, airtime deals, bills payment app, online payment, mobile recharge, discounted airtime, bill management, digital transactions, cheap airtime, cheap data, Eazi Plux, best cheap data ">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-site-verification" content="2C-9r_1lFbvzBCAMqcq3p8EoPsKWrm_9aiWJWioJiVg" />
    <meta name="author" content="Vickman Tech">
    <title>FUND WALLET</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            /* background: #f0f0f0; */
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        button {
            background: green;
            font-weight: bold;
            font-size: medium;
            padding: .2rem;
            color: beige;
            border-radius: .5rem;
        }
        
        header {
            color: #fff;
            font-size: 16px;
            padding-block: 10px;
            text-align: center;
            width: 100%;
            background: rgb(0, 0, 0);
        }

        .marquee-container {
            width: 100%;
            overflow: hidden;
            position: relative;
            padding: 0;
            height: 20px;
            background: rgb(37, 37, 37);
        }

        .marquee {
            display: inline-block;
            white-space: nowrap;
            font-size: 10px;
            padding: 0;
            height: 14px;
            line-height: 20px;
            position: absolute; 
            animation: marquee 10s linear infinite;
        }

        .view-payout-btn {
            background: #ffbf00;
            color: #222;
            border: none;
            border-radius: 30px;
            margin: auto;
            padding: 10px 24px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 18px;
            margin-top: 30px;
            display: block;
            width: 90%;
        }

        .marquee p{
            color: white;
        }

        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
    </style>
</head>

<body>
<?php if ($show_link_phone): ?>
    <div style="background: black; color: #b8860b; border: 11x solid #ccc; border-radius: 5px; padding: 18px; margin: 20px auto; max-width: 400px; text-align: center; font-size: 16px; height: 70vh;">
        <span style="font-size: 22px; margin-right: 8px;">&#9432;</span>
        <strong>Action Required:</strong> Link your phone number to continue using your account.
        <form method="post" action="../api/linkPhone.php" style="margin-top:15px;">
            <input type="tel" name="phone" placeholder="Enter your phone number" required style="padding:8px; border-radius:5px; border:1px solid #ffbf00; width: 80%; background: transparent; color: white; font-size: 16px;">
            <button type="submit" name="link_phone" style="margin-top:20px; padding:8px 16px; border-radius:15px; background:#ffbf00; color:#222; border:none; width: 70%;">Link phone nmber</button>
        </form>
        <?php if (isset($link_error)) echo '<div style="color:red; margin-top:10px;">' . htmlspecialchars($link_error) . '</div>'; ?>
        <?php if (isset($link_success)) echo '<div style="color:green; margin-top:10px;">' . htmlspecialchars($link_success) . '</div>'; ?>
    </div>
<?php else: ?>
    <header>
       Manage Funds 
    </header>
    <div class="marquee-container">
        <div class="marquee">
            Kindly note that service fee of &#8358;20 will be charged for every successful funding ~ Eaziplux
        </div>
    </div>
    <main>
        <div style="font-size: 14px; margin-top: 10px; text-align: center;">Transfer to the below account to fund wallet</div>
        <div class="acctInfo">
            <div class="det">
                <div class="col1">Account Name</div> 
                <div class="col"><?php echo htmlspecialchars($dbAcctName); ?></div>
            </div>
            <div class="det">
                <div class="col1">Account Number</div>
                <div class="col"><?php echo htmlspecialchars($dbAcctNumber); ?><span onclick="copyText()" style="margin: auto; border: 1px dotted #ffbf00; margin-left: 8px; padding: 2px 3px; font-size: 10px; border-radius: 5px; color: #ffbf00">Copy</span></div>               
            </div>
            <div class="det">
                <div class="col1">Bank Name</div>
                <div class="col"><?php echo htmlspecialchars($dbBankName); ?></div>
            </div>
            <div style="font-size: 14px; margin-top: 5px; text-align: center; color: #ccc; font-weight: bold;">
                Other payment methods:
            </div>
            <div style="display: flex; justify-content: center; align-items: center; font-size: 13px; margin-top: 10px; color:#ffbf00;">
                <img src="../css/svg/card.svg" style="width: 20px; padding-right: 5px;"/><a style="text-decoration: none; color: #ffbf00;" href="./cardpayment.php">Pay with your card or USSD</a>
            </div>
        </div>
    </main>
    <div>
        <button class="view-payout-btn" onclick="window.location.href='withdraw.php'">Withdraw Funds</button>
    </div>
    <footer style="color: #ccc; position: absolute; bottom: 10px; width: 90%; left: 25px; font-size: 12px; text-align: center; border: 0px solid #ccc;">
        <div class="footer">
            <p>&copy; 2023 Eazi Plux. All rights reserved.</p>
        </div>
    </footer>
<?php endif; ?>

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

    <script>
        function copyText() {
            // Get the text from the PHP variable
            var textToCopy = "<?php echo $dbAcctNumber; ?>";

            // Create a temporary textarea element
            var textarea = document.createElement("textarea");
            textarea.value = textToCopy;

            // Append the textarea to the document
            document.body.appendChild(textarea);

            // Select the text in the textarea
            textarea.select();
            textarea.setSelectionRange(0, 99999); // For mobile devices

            // Copy the selected text
            document.execCommand("copy");

            // Remove the temporary textarea
            document.body.removeChild(textarea);

            // Provide feedback to the user (optional)
            alert("Account Number copied to clipboard!");
        }
    </script>

</body>

</html>