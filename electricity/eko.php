<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="../css/mtnairtime.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&family=Truculenta:opsz,wght@12..72,100..900&display=swap"
        rel="stylesheet">
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
    <title>EKO ELECTRICITY</title>
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
            width: 260px;
            background: #000;
            border: 1px solid #ccc;
            border-radius: 5px;
            z-index: 10;
            font-weight: 500;
            /* height: 200px; */
            overflow-y: auto; /* Allow scrolling if options exceed height */
        }

        .custom-select.open .options {
            display: block;
            height: 145px;

        }

        .custom-select .options div {
            padding: 6px 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            /* justify-content: space-around; */
            /* border: 1px solid #ccc; */
            /* width: 300px; */
            font-size: 18px;
            gap: 6px;
        }

        .custom-select .options div:hover {
            background: #f0f0f0;
        }

        .phone-input{
            width: 80%;
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
    font-family: "poppins";
}

.customer{
    /* border: 1px solid #ccc; */
    height: 39px;
    width: 100%;
    text-align: left;
    font-size: 16px;
}

        .marquee-container {
    width: 100%;
    overflow: hidden;
    position: relative;
    padding: 0;
    height: 20px; /* Reduced from 25px */
    background: rgb(37, 37, 37);
}

.marquee {
    display: inline-block;
    white-space: nowrap;
    font-size: 10px; /* Reduced from 14px */
    padding: 0;
    height: 14px;
    line-height: 20px; /* Adjusted to match the height */
    position: absolute; 
    /* border: 1px solid white; */
    animation: marquee 10s linear infinite;
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

        <div class="preloader-overlay" id="preloader">
        <img src="../css/imgs/eazi.gif" class="preloader-img" alt="Loading..."> <!-- Replace with your preloader image -->
    </div>
    <div class="marquee-container">
        <div class="marquee">
            Kindly note that convenience fee of &#8358;50 will be charged for this transaction ~ Eaziplux</p>    
        </div>
    </div>
    <main>
        <div style="font-weight: 400; color: #ffbf00; border:0px solid #ccc; width: 30%; border-right: none;  margin-left: auto; margin-top: 10px; padding: 1px; border-top-left-radius: 5px; font-size: 13px; display: flex; justify-content: center; align-items:center;"><img src="../css/svg/script.svg" style="width: 15px; padding: 3px;"/><a href="../dashboard/transaction.php?product=electricity" style="color: #ffbf00; text-decoration: none;">View orders</a></div>
        <div class="container">
            <div class="network">
                <div class="network-header">
                    <h1 class="gradient-text">Buy Electricity</h1>
                    <p style="color: #ccc; ">Restore power seamlessly.</p>
                </div>
                <form method="post" action="../purchase/electricity.php" id="dataForm">
                    <div class="num-network-row" id="networkRow" style="display:flex;align-items:center;gap:0px;">
                        <!-- Custom Network Dropdown -->
                        <div class="custom-select-wrapper">
                            <div class="custom-select" id="networkSelect">
                                <div class="selected" style="gap:1px;">
                                    <img src="../css/imgs/aedc.png" alt="Select Network" id="selectedLogo"
                                        style="width:35px;height:30px;vertical-align:middle;">
                                    <svg id="dropdownArrow" width="18" height="18" viewBox="0 0 20 20" style="pointer-events:none;">
                                        <polyline points="6 8 10 12 14 8" fill="none" stroke="#888" stroke-width="2"/>
                                    </svg>
                                </div>
                                <div class="options">
                                    <div data-value="abuja-electric" data-label="Abuja Electric">
                                        <img src="../css/imgs/aedc.png" style="width:24px;height:24px;vertical-align:middle;"> Abuja Electric
                                    </div>
                                    <div data-value="eko-electric" data-label="Eko Electric">
                                        <img src="../css/imgs/ekedc.png" style="width:24px;height:24px;vertical-align:middle;"> Eko Electric
                                    </div>
                                    <div data-value="ibadan-electric" data-label="Ibadan Electric">
                                        <img src="../css/imgs/ibadan.png" style="width:24px;height:24px;vertical-align:middle;"> Ibadan Electric
                                    </div>
                                    <div data-value="ikeja-electric" data-label="Ikeja Electric">
                                        <img src="../css/imgs/ikeja.png" style="width:24px;height:24px;vertical-align:middle;"> Ikeja Electric
                                    </div>
                                    <div data-value="jos-electric" data-label="Jos Electric">
                                        <img src="../css/imgs/jos.jpg" style="width:24px;height:24px;vertical-align:middle;"> Jos Electric
                                    </div>
                                    <div data-value="kaduna-electric" data-label="Kaduna Electric">
                                        <img src="../css/imgs/kaduna.png" style="width:24px;height:24px;vertical-align:middle;"> Kaduna Electric
                                    </div>
                                    <div data-value="kano-electric" data-label="Kano Electric">
                                        <img src="../css/imgs/kano.png" style="width:24px;height:24px;vertical-align:middle;"> Kano Electric
                                    </div>
                                    <div data-value="portharcourt-electric" data-label="Port Harcourt Electric">
                                        <img src="../css/imgs/phd.png" style="width:24px;height:24px;vertical-align:middle;"> Port Harcourt Electric
                                    </div>
                                    <div data-value="aba-electric" data-label="Aba Electric">
                                        <img src="../css/imgs/aba-electric.png" style="width:24px;height:24px;vertical-align:middle;"> Aba Electric
                                    </div>
                                    <div data-value="yola-electric" data-label="Yola Electric">
                                        <img src="../css/imgs/yola-electric.png" style="width:24px;height:24px;vertical-align:middle;"> Yola Electric
                                    </div>
                                    <div data-value="benin-electric" data-label="Benin Electric">
                                        <img src="../css/imgs/bedc.png" style="width:24px;height:24px;vertical-align:middle;"> Benin Electric
                                    </div>
                                    <div data-value="enugu-electric" data-label="Enugu Electric">
                                        <img src="../css/imgs/enugu-electric.png" style="width:24px;height:24px;vertical-align:middle;"> Enugu Electric
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="network" id="networkInput" value="abuja-electric">
                        </div>
                        <!-- Phone Input -->
                        <div class="phone-input" style="flex:1;">
                            <input type="number" class="meter" name="meter" placeholder="Meter number" required>
                        </div>
                    </div>
                    <div class="fig">
                        <div class="icon">&#8358;</div>
                        <div class="amount-input">
                            <input type="number" class="amount" name="amount" placeholder="100 -10,000" required>
                        </div>
                    </div>
                    <div class="fig" >
                        <input type="number" class="customer" name="customer" placeholder="Customer phone number" required>
                    </div>
                    <input type="hidden" class="hidden-input" name="item" value="Electricity">
                    <input class="submit" name="submit" onclick="openPopup()" type="submit" value="Buy Power">
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
        // JavaScript to show preloader when form is submitted
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('form').addEventListener('submit', function() {
                document.getElementById('preloader').style.display = 'flex';
            });
        });
    </script>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('networkSelect');
    const selected = select.querySelector('.selected');
    const options = select.querySelector('.options');
    const networkInput = document.getElementById('networkInput');
    const selectedLogo = document.getElementById('selectedLogo');
    // Remove plansDropdown, planPriceInput, itemInput, and loadPlans if not needed for electricity

    // Fix: Prevent click event from bubbling up and closing the dropdown immediately
    selected.addEventListener('click', function (e) {
        e.stopPropagation();
        select.classList.toggle('open');
    });

    // Also stop propagation on the options container
    options.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    options.querySelectorAll('div').forEach(option => {
        option.addEventListener('click', function () {
            const value = this.getAttribute('data-value');
            const img = this.querySelector('img').src;
            selectedLogo.src = img;
            networkInput.value = value;
            select.classList.remove('open');
        });
    });

    // Close dropdown if clicked outside
    document.addEventListener('click', function (e) {
        if (!select.contains(e.target)) {
            select.classList.remove('open');
        }
    });
});
</script>

</body>
</html>


