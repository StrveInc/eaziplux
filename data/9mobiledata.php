<?php
session_start();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/mtnairtime.css">
    <link rel="icon" type="image/png" size="662x662" href="./css/imgs/eazicon.png">
    <script src="https://kit.fontawesome.com/49c5823e25.js" crossorigin="anonymous"></script>
    <meta name="description"
        content="Manage your mobile data and pay bills seamlessly with Eazi Plux. Enjoy a convienient and secure platform for handling all your mobile-related transactions.">
    <meta charset="UTF-8">
    <meta name="keywords"
        content="discounted mobile data, airtime deals, bills payment app, online payment, mobile recharge, discounted airtime, bill management, digital transactions, cheap airtime, cheap data, Eazi Plux, best cheap data ">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-site-verification" content="2C-9r_1lFbvzBCAMqcq3p8EoPsKWrm_9aiWJWioJiVg" />
    <meta name="author" content="Vickman Tech">
    <title>9mobile Data</title>
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
<style>
    span {
        color: gray;
    }
</style>

<body>

    <div class="preloader-overlay" id="preloader">
        <img src="../css/imgs/eazi.gif" class="preloader-img" alt="Loading..."> <!-- Replace with your preloader image -->
    </div>


    <header>
        <div class="back">
            <a href="../dashboard/buydata.php"><i class="fas fa-chevron-left" aria-hidden="true"></i></a>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="network">
                <form method="post" action="../purchase/data.php">
                    <div class="MOBILEMTN">
                        <a href="9mobiledata.php">
                            <div class="mtnlogo">
                                <img src="../css/imgs/9mobileupdate.png" alt="9mobile">
                            </div>
                        </a>
                    </div>
                    <div class="fig">
                        <p>PLAN</p>
                        <select id="plans" name="plans">
                            <?php
                            $curl = curl_init();

                            curl_setopt_array(
                                $curl,
                                array(
                                    CURLOPT_URL => 'https://gsubz.com/api/plans?service=etisalat_data',
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_ENCODING => '',
                                    CURLOPT_MAXREDIRS => 10,
                                    CURLOPT_TIMEOUT => 0,
                                    CURLOPT_FOLLOWLOCATION => true,
                                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                    CURLOPT_CUSTOMREQUEST => 'GET',
                                )
                            );

                            $response = curl_exec($curl);

                            curl_close($curl);

                            // Decode the JSON response
                            $responseArray = json_decode($response, true);

                            // Check if JSON decoding was successful
                            if ($responseArray !== null) {
                                // Iterate through the plans to generate options for the dropdown
                                $plans = $responseArray['plans'];

                                $options = '';
                                foreach ($plans as $plan) {
                                    $displayName = $plan['displayName'];
                                    $value = $plan['value'];
                                    $price = $plan['price'];

                                    // Calculate the adjusted price with an additional 10%
                                    $adjustedPrice = $price + ($price * 0.095);
                                    $adjustedPrice = floor($adjustedPrice);

                                    $options .= "<option value='$value'>$displayName - $adjustedPrice</option>";
                                }

                                echo $options;
                            } else {
                                // Handle JSON decoding error
                                echo"<script>";
                                echo"alert('9mobile service is currently down')";
                                echo"</script>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="num">
                        <p>NG</p>
                        <input type="text" class="number" name="number" placeholder="08012345678">
                    </div>

                    <input type="hidden" class="hidden-input" name="item" value="9MOBILE">

                    <input class="submit" name="submit" onclick="openPopup()" type="submit" value="Buy Data">
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



