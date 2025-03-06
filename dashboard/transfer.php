<?php
session_start();

// Check if the 'username' session variable is set
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("Location: ../home/login.php");
    exit;
}

// Check if the 'email' session variable is set
if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    // Handle the case where email is not set (you can redirect or perform other actions)
}

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$database = "eaziplux";

$conn = new mysqli($servername, $dbusername, $dbpassword, $database);







/*************************CARD**************************************************/



// Check the database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve the user's phone number from the database
$stmtPhone = $conn->prepare("SELECT phone_number FROM users WHERE email=?");
$stmtPhone->bind_param("s", $_SESSION["email"]);
$stmtPhone->execute();
$stmtPhone->bind_result($userPhone);
$stmtPhone->fetch();
$stmtPhone->close();

// Set the phone number in the session
$_SESSION["phone"] = $userPhone;

// Close the database connection
$conn->close();

// Set data for the first API call
$_SESSION['customer_data'] = array(
    "email" => $_SESSION["email"],
    "first_name" => "eazi-",
    "last_name" => $_SESSION['username'],
    "phone" => $_SESSION["phone"]
);

$customerCode = null;

// API endpoint URL for the first call
$urlCustomer = 'https://api.paystack.co/customer';

// API Key for Authorization for the first call
$authorization = 'Authorization: Bearer sk_live_2886d2746575c3f54e3c7b7e02eab1cd7da06313';

// Content type for the first call
$contentType = 'Content-Type: application/json';

// Data to be sent in the request for the first call
$dataCustomer = json_encode($_SESSION['customer_data']);

// Initialize cURL session for the first call
$chCustomer = curl_init();

// Set cURL options for a POST request for the first call
curl_setopt($chCustomer, CURLOPT_URL, $urlCustomer);
curl_setopt($chCustomer, CURLOPT_POST, 1);
curl_setopt($chCustomer, CURLOPT_POSTFIELDS, $dataCustomer);
curl_setopt($chCustomer, CURLOPT_RETURNTRANSFER, true);
curl_setopt(
    $chCustomer,
    CURLOPT_HTTPHEADER,
    array(
        $authorization,
        $contentType,
    )
);

// Execute cURL session for the first call
$responseCustomer = curl_exec($chCustomer);

// Check for cURL errors for the first call
if (curl_errno($chCustomer)) {

} else {
    // Decode and print the response for the first call
    $responseDataCustomer = json_decode($responseCustomer, true);

    $customerCode = $responseDataCustomer['data']['customer_code'];
}

// Close cURL session for the first call
curl_close($chCustomer);



$url = "https://api.paystack.co/dedicated_account";

$fields = [
    "customer" => $customerCode,
    "preferred_bank" => "wema-bank",
    "email" => $_SESSION["email"],
    "first_name" => "Eazi",
    "middle_name" => "",
    "last_name" => $_SESSION["username"],
    "phone" => $_SESSION["phone"],
];

$fields_string = http_build_query($fields);

// Initialize cURL session
$ch = curl_init();

// Set cURL options for a POST request
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
curl_setopt(
    $ch,
    CURLOPT_HTTPHEADER,
    array(
        "Authorization: Bearer sk_live_2886d2746575c3f54e3c7b7e02eab1cd7da06313",
        "Cache-Control: no-cache",
    )
);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute cURL session
$result = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {

} else {
    // Decode the result
    $decode = json_decode($result);

    // Extract required information
    $acctnumber = $decode->data->account_number;
    $acctname = $decode->data->account_name;
    $bankname = $decode->data->bank->name;

    // Close cURL session
    curl_close($ch);

    // Save to database
    // Replace the following with your database connection and query

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
    $stmt = $conn->prepare("UPDATE users SET acct_number=?, acct_name=?, bank_name=? WHERE email=?");
    $stmt->bind_param("ssss", $acctnumber, $acctname, $bankname, $_SESSION["email"]);
    $stmt->execute();

    $stmt->close();
    $conn->close();
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