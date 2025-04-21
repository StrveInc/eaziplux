<?php
session_start();

// Check if the 'username' session variable is set
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("Location: ../home/login.php");
    exit;
}



// End the session
$servername = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "eaziplux";


$conn = new mysqli($servername, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Use prepared statements to prevent SQL injection
$stmt = $conn->prepare("SELECT acct_number, acct_name, bank_name FROM users WHERE email=?");
$stmt->bind_param("s", $_SESSION["email"]);
$stmt->execute();
$stmt->bind_result($dbAcctNumber, $dbAcctName, $dbBankName);
$stmt->fetch();
$stmt->close();
$conn->close();

// Output retrieved information


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../css/transfer.css">
    <meta charset="UTF-8">
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
    <title>FUND WALLET</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        button {
            background: green;
            font-weight: bold;
            font-size: medium;
            padding: .2rem;
            color: beige;
            border-radius: .5rem;
        }
    </style>
</head>

<body>
    <header>
        <div class="back">
            <a href="addfund.php"><i class="fa-solid fa-chevron-left"></i></a>
        </div>
        <div class="head">
            Account Details
        </div>
    </header>
    <main>
        <form id="payment-form" method="post">
            <div class="container">
                <div class="transfer">
                    <div class="det">
                        <p>Account Number: </p>
                        <div class="num">
                            <p id="copyText">
                                <?php echo $dbAcctNumber; ?>
                            </p>
                            <label onclick="copyText()"><i class="fas fa-copy"></i></label>
                        </div>
                    </div>

                    <div class="det">
                        <p>Account Name: </p>
                        <p>
                            <?php echo $dbAcctName; ?>
                        </p>
                    </div>

                    <div class="det">
                        <p>Bank Name: </p>
                        <p>
                            <?php echo $dbBankName; ?>
                        </p>
                    </div>
                </div>
            </div>
        </form>
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