<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="../css/mtnairtime.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" size="662x662" href="./css/imgs/eaziplux.png">
    <script src="https://kit.fontawesome.com/49c5823e25.js" crossorigin="anonymous"></script>
    <meta name="description"
        content="Manage your mobile data and pay bills seamlessly with Eazi Plux. Enjoy a convienient and secure platform for handling all your mobile-related transactions.">
    <meta charset="UTF-8">
    <meta name="keywords"
        content="discounted mobile data, airtime deals, bills payment app, online payment, mobile recharge, discounted airtime, bill management, digital transactions, cheap airtime, cheap data, EaziPlux, best cheap data ">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-site-verification" content="2C-9r_1lFbvzBCAMqcq3p8EoPsKWrm_9aiWJWioJiVg" />
    <meta name="author" content="Vickman Tech">
    <title>Glo recharge</title>

    <style>
        span {
            color: green;
        }
    </style>
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
    </style>
</head>

<body>
<div class="preloader-overlay" id="preloader">
        <img src="../css/imgs/eazi.gif" class="preloader-img" alt="Loading..."> <!-- Replace with your preloader image -->
    </div>



    <main>
        <div class="container">
            <div class="network">
                <form method="post" action="../purchase/airtime.php" id="airtimeForm">
                    <div class="MOBILEMTN">
                        <a href="../airtime/mtnairtime.php">
                            <div class="mtnlogo">
                                <img src="../css/imgs/gloupdate.jpg" alt="GLO">
                            </div>
                        </a>
                    </div>
                    <div class="fig">
                        <p><i class="fa-solid fa-naira-sign"></i></p>
                        <input type="text" class="amount" name="amount" placeholder="100 -10,000"></input>
                    </div>
                    <div class="num">
                        <p>NG</p>
                        <input type="text" class="number" name="number" placeholder="08012345678">
                    </div>

                    <input type="hidden" class="hidden-input" name="item" value="glo">


                    <input class="submit" name="submit" onclick="openPopup()" type="submit" value="Buy Airtime">
                </form>
            </div>
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

</html>
