<?php
if (isset($_SESSION['modal'])) {
    $type = $_SESSION['modal']['type'];
    $message = $_SESSION['modal']['message'];
    echo "<script>showModal('$type', " . json_encode($message) . ");</script>";
    unset($_SESSION['modal']);
}

// if(!isset($_SESSION['user_id'])){
//     header("Location: ../login.php");
//     exit();
// }

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
    href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&family=Truculenta:opsz,wght@12..72,100..900&display=swap"
    rel="stylesheet">
    <link rel="stylesheet" href="../css/mtnairtime.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" size="662x662" href="../css/imgs/eazipluxpure.png">
    <script src="https://kit.fontawesome.com/49c5823e25.js" crossorigin="anonymous"></script>
    <meta name="description"
        content="Manage your mobile data and pay bills seamlessly with Eazi Plux. Enjoy a convienient and secure platform for handling all your mobile-related transactions.">
    <meta name="keywords"
        content="discounted mobile data, airtime deals, bills payment app, online payment, mobile recharge, discounted airtime, bill management, digital transactions, cheap airtime, cheap data, Eazi plux, best cheap data ">
    <meta name="google-site-verification" content="2C-9r_1lFbvzBCAMqcq3p8EoPsKWrm_9aiWJWioJiVg" />
    <meta name="author" content="Vickman Tech">

    <script src="//code.jquery.com/jquery.min.js"></script>
    <script src="../slide/js/slide-to-submit.js"></script>
    <link rel="stylesheet" href="../slide/css/slide-to-submit.css">
    <title>Airtime Recharge</title>

    <style>
        span {
            color: green;
        }

        /* Styling for preloader overlay */
        .preloader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(19, 19, 19, 0.3);
            /* Transparent background with opacity */
            backdrop-filter: blur(8px);
            /* Apply blur effect to the background */
            display: none;
            /* Initially hide preloader */
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        /* Styling for preloader image */
        .preloader-img {
            width: 100px;
            /* Adjust size as needed */
            height: 100px;
            /* Adjust size as needed */
        }

        .network-select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 5px;
            /* border: 1px solid #ccc; */
        }

        .num-network-row {
            margin: auto;
            display: flex;
            /* gap: 10px; */
            align-items: center;
            margin-bottom: 15px;
            height: 40px;
            justify-content: left;
            width: 90%;
            border: 1px solid #ffbf00;
            border-radius: 5px;
        }

        .custom-select-wrapper {
            /* position: relative; */
            width: 20%;
            /* border: 1px solid #ccc;  */
        }

        .custom-select {
            /* background: #fff; */
            /* border: 1px solid #ccc; */
            border-radius: 5px;
            cursor: pointer;
            position: relative;
            /* padding: 2px 4px; */
            width: fit-content;
            /* Just enough for the logo */
            /* min-width: 38px;
            max-width: 38px; */
        }

        .custom-select .selected {
            display: flex;
            align-items: center;
            justify-content: center;
            /* width: fit-; */
            /* height: 32px;*/
            width: 100%; 
            padding: 0;
            /* border: 1px solid #ccc; */
            /* gap: 0; */
        }

        .custom-select .options {
            display: none;
            position: absolute;
            top: 110%;
            left: 0;
            width: 250px;
            background: #000;
            border: 1px solid #ccc;
            font-weight: 400;
            border-radius: 5px;
            z-index: 10;
        }

        .custom-select.open .options {
            display: block;
        }

        .custom-select .options div {
            padding: 6px 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            /* border: 1px solid #ccc; */
            /* width: 300px; */
            font-size: 18px;
            gap: 6px;
        }

        .custom-select .options div:hover {
            background: #f0f0f0;
        }

        .phone-input{
            width: 90%;
            /* margin-left: 10px; */
            /* border: 1px solid #ccc; */
            /* border-radius: 5px; */
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: left;
        }

        .phone-input input {
            width: 100%;
            /* padding: 8px; */
            /* border-radius: 5px; */
            text-align: left;
            margin-right: auto;
            height: 32px;
            font-size: 16px;
            font-family: "poppins";
            font-weight: 400;
            /* border: 1px solid #ccc; */
        }

        .selected svg {
            /* margin-left: 2px; */
            transition: transform 0.2s;
        }

        .custom-select.open .selected svg {
            transform: rotate(180deg);
        }

        .gradient-text {
background: linear-gradient(180deg, #043927, #6d945e);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    color: transparent;
}
    </style>
</head>

<body>
    <div class="preloader-overlay" id="preloader">
        <img src="../css/imgs/eazi.gif" class="preloader-img" alt="Loading...">
    </div>

    <main>
        <div style="font-weight: 400; color: #ffbf00; border:0px solid #ccc; width: 30%; border-right: none;  margin-left: auto; margin-top: 10px; padding: 1px; border-top-left-radius: 5px; font-size: 13px; display: flex; justify-content: center; align-items:center;"><img src="../css/svg/script.svg" style="width: 15px; padding: 3px;"/><a href="../dashboard/transaction.php?product=airtime" style="color: #ffbf00; text-decoration: none;">View orders</a></div>
        <div class="container">
            <div class="network">
                <div class="network-header">
                    <h1 class="gradient-text">Buy Airtime</h1>
                    <p style="color: #ccc; ">Recharge your phone with ease.</p>
                </div>
                <form method="post" action="../purchase/airtime.php" id="airtimeForm">
                <div class="input-error" id="inputError" style="color: red; font-size: 12px; width: 95%; text-align:right; margin-top: 2px; border: 0px solid white; height: 20px;"></div>    
                <div class="num-network-row" id="networkRow">
                        <!-- Custom Network Dropdown -->
                        <div class="custom-select-wrapper">
                            <div class="custom-select" id="networkSelect">
                                <div class="selected" style="gap:4px;">
                                    <!-- <div style="border: 1px solid white; height: 32px;"> -->
                                    <img src="../css/svg/MTN.svg" alt="Select Network" id="selectedLogo"
                                        style="width:45px;height:30px;vertical-align:middle;">
                                    <!-- </div> -->
                                    <div style="border: 0px solid white; padding: 0px; margin-left: -10px;">
                                    <svg id="dropdownArrow" width="18" height="18" viewBox="0 0 20 20" style="pointer-events:none;">
                                        <polyline points="6 8 10 12 14 8" fill="none" stroke="#888" stroke-width="2"/>
                                    </svg>
                                    </div>
                                </div>
                                <div class="options">
                                    <div data-value="mtn" data-label="MTN">
                                        <img src="../css/svg/MTN.svg"
                                            style="width:24px;height:24px;vertical-align:middle;"> MTN
                                    </div>
                                    <div data-value="airtel" data-label="Airtel">
                                        <img src="../css/svg/Airtel.svg"
                                            style="width:24px;height:24px;vertical-align:middle;"> Airtel
                                    </div>
                                    <div data-value="glo" data-label="Glo">
                                        <img src="../css/svg/Glo.svg"
                                            style="width:24px;height:24px;vertical-align:middle;"> Glo
                                    </div>
                                    <div data-value="9mobile" data-label="9mobile">
                                        <img src="../css/svg/9mobile.svg"
                                            style="width:24px;height:24px;vertical-align:middle;"> 9mobile
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="network" id="networkInput" value="mtn">
                        </div>
                        <!-- Phone Input -->
                        <div class="phone-input">
                            <input type="number" class="number" name="number" placeholder="08012345678" required>
                        </div>
                    </div>
                    <div class="fig">
                        <div class="icon">&#8358;</div>
                        <div class="amount-input">
                            <input type="number" class="amount" name="amount" placeholder="100 -10,000" required>
                        </div>
                    </div>

                    <input type="hidden" class="hidden-input" name="item" value="airtime">

                    <input class="submit" name="submit" onclick="openPopup()" type="submit" value="Pay Now">
                </form>
            </div>
    </main>
    <footer style="color: #ccc; position: absolute; bottom: 10px; width: 90%; left: 25px; font-size: 12px; text-align: center; border: 0px solid #ccc;">
    <div class="footer">
            <p>&copy; 2023 Eazi Plux. All rights reserved.</p>
        </div>
            <!-- <div>&copy; 2024 Strive inc. All right reserved <a href="https://eaziplux.com" target="_blank">Strive inc</a></div>     -->

    </footer> 


    <script>
        // Show preloader when form is submitted
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelector('form').addEventListener('submit', function () {
                document.getElementById('preloader').style.display = 'flex';
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const select = document.getElementById('networkSelect');
            const selected = select.querySelector('.selected');
            const options = select.querySelector('.options');
            const networkInput = document.getElementById('networkInput');
            const selectedLogo = document.getElementById('selectedLogo');
            const networkRow = document.getElementById('networkRow');
            const amountBorder = document.querySelector('.fig');

            // Set initial border color for MTN
            networkRow.style.borderColor = '#ffbf00';
            amountBorder.style.borderColor = '#ffbf00';

            selected.addEventListener('click', function (e) {
                select.classList.toggle('open');
            });

            options.querySelectorAll('div').forEach(option => {
                option.addEventListener('click', function () {
                    const value = this.getAttribute('data-value');
                    const img = this.querySelector('img').src;
                    selectedLogo.src = img;
                    networkInput.value = value;
                    select.classList.remove('open');

                    // Set border color based on selected network
                    switch (value) {
                        case 'mtn':
                            networkRow.style.borderColor = '#ffbf00'; // yellow
                            amountBorder.style.borderColor = '#ffbf00';
                            break;
                        case 'airtel':
                            networkRow.style.borderColor = '#e60000'; // red
                            amountBorder.style.borderColor = '#e60000';
                            break;
                        case 'glo':
                            networkRow.style.borderColor = '#008000'; // green
                            amountBorder.style.borderColor = '#008000';
                            break;
                        case '9mobile':
                            networkRow.style.borderColor = '#888888'; // grey
                            amountBorder.style.borderColor = '#888888';
                            break;
                        default:
                            networkRow.style.borderColor = '#ffbf00';
                            amountBorder.style.borderColor = '#ffbf00';
                    }
                });
            });

            // Close dropdown if clicked outside
            document.addEventListener('click', function (e) {
                if (!select.contains(e.target)) {
                    select.classList.remove('open');
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
    const phoneInput = document.querySelector('input[name="number"]');
    const amountInput = document.querySelector('input[name="amount"]');
    const payBtn = document.querySelector('input[type="submit"].submit');

    // Create error message element for phon
    // let phoneError = document.getElementsByCla'input-error');
    // phoneError.style.color = 'red';
    // phoneError.style.fontSize = '12px';
    // phoneError.style.marginTop = '2px';
    // phoneError.style.display = 'none';
    // phoneError.className = 'input-error';
    // phoneInput.parentNode.appendChild(phoneError);

    let phoneError = document.getElementById('inputError');

    // Create error message element for amount
    let amountError = document.createElement('div');
    amountError.style.color = 'red';
    amountError.style.fontSize = '12px';
    amountError.style.border = '0px solid red';
    amountError.style.marginTop = '2px';
    amountError.style.display = 'none';
    amountError.style.textAlign = 'right';
    amountError.className = 'input-error';
    amountInput.parentNode.appendChild(amountError);

    let phoneTimeout = null;

    function validateInputs(showPhoneError = false) {
        let valid = true;

        // Phone validation
        const phoneVal = phoneInput.value.trim();
        if (phoneVal === "") {
            phoneError.textContent = "";
            phoneError.style.display = 'block';
            valid = false;
        } else if (!/^\d{11}$/.test(phoneVal)) {
            if (showPhoneError) {
                phoneError.textContent = "Invalid number";
                phoneError.style.display = 'block';
            } else {
                phoneError.textContent = "";
                phoneError.style.display = 'block';
            }
            valid = false;
        } else {
            phoneError.textContent = "";
            phoneError.style.display = 'block';
        }

        // Amount validation
        const amountVal = parseInt(amountInput.value, 10);
        if (amountInput.value === "") {
            amountError.textContent = "";
            amountError.style.display = 'none';
            valid = false;
        } else if (isNaN(amountVal) || amountVal < 100) {
            amountError.textContent = "Minimum amount is ₦100";
            amountError.style.display = 'block';
            payBtn.disabled = true;
            payBtn.style.opacity = 0.6;
            payBtn.style.cursor = "not-allowed";
            valid = false;
        } else {
            amountError.textContent = "";
            amountError.style.display = 'none';
        }

        // Disable pay button if phone is invalid or amount is invalid
        if (!/^\d{11}$/.test(phoneVal) || isNaN(amountVal) || amountVal < 100) {
            payBtn.disabled = true;
            payBtn.style.opacity = 0.6;
            payBtn.style.cursor = "not-allowed";
        } else {
            payBtn.disabled = false;
            payBtn.style.opacity = 1;
            payBtn.style.cursor = "pointer";
        }
    }

    phoneInput.addEventListener('input', function () {
        // Hide error immediately when typing
        phoneError.textContent = "";
        phoneError.style.display = 'none';

        // Always validate for button state, but don't show error yet
        validateInputs(false);

        // Clear previous timeout
        if (phoneTimeout) clearTimeout(phoneTimeout);

        // Only show error if input is not empty and not valid after 3 seconds
        if (phoneInput.value.trim() !== "") {
            phoneTimeout = setTimeout(function () {
                validateInputs(true);
            }, 1500);
        }
    });

    amountInput.addEventListener('input', function () {
        validateInputs(false);
    });

    // Initial validation on page load
    validateInputs(false);
});
    </script>

</body>

</html>