<?php

include_once '../config.php';

session_start();
// Replace with your actual Paystack secret key
$paystackSecretKey = $_ENV['PK_SECRET'];
$amount = null;


// Function to create a Paystack payment
function createPaystackPayment($email, $amount, $reference)
{
    global $paystackSecretKey;

    $url = 'https://api.paystack.co/transaction/initialize';

    $data = [
        'email' => $email,
        'amount' => $amount,
        'reference' => $reference,
    ];

    $headers = [
        'Authorization: Bearer ' . $paystackSecretKey,
        'Content-Type: application/json',
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the entered amount from the form
    $amount = $_POST['amount'] * 100;

    // Example email (you may retrieve this from your user system)
    $email = $_SESSION["email"];

    // Generate a unique reference
    $reference = "EP" . time();

    // Create Paystack payment
    $paymentData = createPaystackPayment($email, $amount, $reference);

    // Check if payment creation was successful
    if ($paymentData['status']) {
        // Payment creation successful
        $paymentLink = $paymentData['data']['authorization_url'];
        $accessCode = $paymentData['data']['access_code'];

        // Redirect the customer to the payment link
        header("Location: $paymentLink");
        exit;
    } else {
        // Payment creation failed

    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <script src="https://kit.fontawesome.com/49c5823e25.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../css/cardpayment.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" size="662x662" href="../css/imgs/eaziplux.png">
    <title>Credit card</title>
        <style>
        /* Styling for preloader overlay */
        .preloader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.3); /* Transparent background with opacity */
            backdrop-filter: blur(8px); /* Apply blur effect to the background */
            display: none; /* Initially hide preloader */
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        /* Styling for preloader image */
        .preloader-img {
            width: 100px; /* Adjust size as needed */
            height: 100px; /* Adjust size as needed */
        }
        
         header {
            color: #fff;
            font-size: 16px;
            padding-block: 10px;
            text-align: center;
            /* position: fixed; */
            /* top: -5px; */
            /* border-bottom: .4px solid #ccc; */
            width: 100%;
            /* left: -1px; */
            background: rgb(0, 0, 0);
        }
    </style>
</head>

<body>

    <div class="preloader-overlay" id="preloader">
        <img src="../css/imgs/eazi.gif" class="preloader-img" alt="Loading..."> <!-- Replace with your preloader image -->
    </div>


    <header>
        Card funding
    </header>
    <main>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div style="font-size: 14px;">Enter top-up amount</div>  
            <div class="top">
            &#8358;
                <input type="number" id="amount" placeholder="100.00" name="amount" min="100" required>
            </div>
            <div>
                <button type="submit" name="submit">Pay with card</button>
            </div>
        </form>
    </main>

        <script>
        // JavaScript to show preloader when form is submitted
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('form').addEventListener('submit', function() {
                document.getElementById('preloader').style.display = 'flex';
            });
        });
    </script>
</body>

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

</html>