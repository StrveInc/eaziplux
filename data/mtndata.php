<?php
session_start();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <script src="../js/confirmOverlay.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&family=Truculenta:opsz,wght@12..72,100..900&display=swap"
        rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/mtnairtime.css">
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
    <title>MTN Data</title>
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
            /* height: 32px;
            width: 100%; */
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
            border-radius: 5px;
            z-index: 10;
            font-weight: 500;
        }

        .custom-select.open .options {
            display: block;
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
    </style>


</head>

<body>

        <div class="preloader-overlay" id="preloader">
        <img src="../css/imgs/eazi.gif" class="preloader-img" alt="Loading..."> <!-- Replace with your preloader image -->
    </div>

    <main>
        <div style="font-weight: 400; color: #ffbf00; border:0px solid #ccc; width: 30%; border-right: none;  margin-left: auto; margin-top: 10px; padding: 1px; border-top-left-radius: 5px; font-size: 13px; display: flex; justify-content: center; align-items:center;"><img src="../css/svg/script.svg" style="width: 15px; padding: 3px;"/><a href="../dashboard/transaction.php?product=data" style="color: #ffbf00; text-decoration: none;">View orders</a></div>
        <div class="container">
            <div class="network">
                <div class="network-header">
                    <h1 class="gradient-text">Buy Data</h1>
                    <p style="color: #ccc; ">Recharge your phone with ease.</p>
                </div>
                <form method="post" action="../purchase/data.php" id="dataForm">
                <span id="phoneError" style="width: 95%; text-align: right; color: red; font-size: 13px; display: block; margin-top: 3px; border: px solid white; height: 20px;"></span>    
                <div class="num-network-row" id="networkRow" style="display:flex;align-items:center;gap:0px;">
                        <!-- Custom Network Dropdown -->
                        <div class="custom-select-wrapper">
                            <div class="custom-select" id="networkSelect">
                                <div class="selected" style="gap:1px;">
                                    <img src="../css/svg/MTN.svg" alt="Select Network" id="selectedLogo"
                                        style="width:45px;height:30px;vertical-align:middle;">
                                    <svg id="dropdownArrow" width="18" height="18" viewBox="0 0 20 20" style="pointer-events:none;">
                                        <polyline points="6 8 10 12 14 8" fill="none" stroke="#888" stroke-width="2"/>
                                    </svg>
                                </div>
                                <div class="options">
                                    <div data-value="mtn" data-label="MTN">
                                        <img src="../css/svg/MTN.svg" style="width:24px;height:24px;vertical-align:middle;"> MTN
                                    </div>
                                    <div data-value="airtel" data-label="Airtel">
                                        <img src="../css/svg/Airtel.svg" style="width:24px;height:24px;vertical-align:middle;"> Airtel
                                    </div>
                                    <div data-value="glo" data-label="Glo">
                                        <img src="../css/svg/Glo.svg" style="width:24px;height:24px;vertical-align:middle;"> Glo
                                    </div>
                                    <div data-value="9mobile" data-label="9mobile">
                                        <img src="../css/svg/9mobile.svg" style="width:24px;height:24px;vertical-align:middle;"> 9mobile
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="network" id="networkInput" value="mtn">
                        </div>
                        <!-- Phone Input -->
                        <div class="phone-input" style="flex:1;">
                            <input type="number" class="number" name="number" placeholder="08012345678" required>
                        </div>
                    </div>
                    <div class="fig" id="planDiv">
                        <!-- <p>PLAN</p> -->
                        <select id="plans" style="font-family: poppins; border: 0px solid white; width: 95%; font-size: 16px;" name="plans">
                            <!-- Options will be loaded dynamically -->
                        </select>
                    </div>
                    <input type="hidden" class="hidden-input" name="item" value="data">
                    <input type="hidden" name="price" id="planPrice" value="">
                    <input class="submit" name="submit"  type="submit" value="Buy Data">
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
    const plansDropdown = document.getElementById('plans');
    const networkRow = document.getElementById('networkRow');
    const fig = document.querySelector('.fig');
    const itemInput = document.querySelector('input[name="item"]');
    const planPriceInput = document.getElementById('planPrice');

    // Initial load for MTN
    loadPlans('mtn');
    networkRow.style.borderColor = '#ffbf00';
    fig.style.borderColor = '#ffbf00';

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
            loadPlans(value);
        });
    });

    // Close dropdown if clicked outside
    document.addEventListener('click', function (e) {
        if (!select.contains(e.target)) {
            select.classList.remove('open');
        }
    });

    function loadPlans(network) {
        plansDropdown.innerHTML = '<option>Loading...</option>';
        let service = '';
        switch (network) {
            case 'mtn':
                service = 'mtn_gifting';
                networkRow.style.borderColor = '#ffbf00';
                fig.style.borderColor = '#ffbf00';
                break;
            case 'airtel':
                service = 'airtel_sme';
                networkRow.style.borderColor = '#e60000';
                fig.style.borderColor = '#e60000';
                break;
            case 'glo':
                service = 'glo_sme';
                networkRow.style.borderColor = '#008000';
                fig.style.borderColor = '#008000';
                break;
            case '9mobile':
                service = 'etisalat_data';
                networkRow.style.borderColor = '#888888';
                fig.style.borderColor = '#888888';
                break;
            default:
                service = '';
        }
        if (!service) {
            plansDropdown.innerHTML = '<option>No plans available</option>';
            planPriceInput.value = '';
            itemInput.value = '';
            return;
        }
        fetch('../plans_proxy.php?service=' + encodeURIComponent(service))
            .then(res => res.json())
            .then(data => {
                if (data && data.plans) {
                    plansDropdown.innerHTML = '';
                    data.plans.forEach((plan, idx) => {
                        let markup = 0;
                        if (plan.price >= 100 && plan.price <= 499) {
                            markup = 0.10;
                        } else if (plan.price >= 500 && plan.price <= 1999) {
                            markup = 0.08;
                        } else if (plan.price >= 2000) {
                            markup = 0.05;
                        }
                        let price = Math.ceil(plan.price * (1 + markup));
                        let option = document.createElement('option');
                        option.value = plan.value;
                        option.textContent = `${plan.displayName} - ₦${price}`;
                        option.setAttribute('data-price', price);
                        option.setAttribute('data-displayname', plan.displayName);
                        plansDropdown.appendChild(option);
                        // Set the first plan's price and item as default
                        if (idx === 0) {
                            planPriceInput.value = price;
                            itemInput.value = plan.displayName;
                        }
                    });
                } else {
                    plansDropdown.innerHTML = '<option>No plans found</option>';
                    planPriceInput.value = '';
                    itemInput.value = '';
                }
            })
            .catch(() => {
                plansDropdown.innerHTML = '<option>Service unavailable</option>';
                planPriceInput.value = '';
                itemInput.value = '';
            });
    }

    // Update hidden price and item input when user changes plan
    plansDropdown.addEventListener('change', function() {
        let selectedOption = plansDropdown.options[plansDropdown.selectedIndex];
        let price = selectedOption.getAttribute('data-price');
        let displayName = selectedOption.getAttribute('data-displayname');
        planPriceInput.value = price ? price : '';
        itemInput.value = displayName ? displayName : '';
    });

    // Phone number validation logic
    const phoneInput = document.querySelector('input[name="number"]');
    const payBtn = document.getElementById('payBtn');

    // Create or get the error message span
    let phoneError = document.getElementById('phoneError');
    if (!phoneError) {
        phoneError = document.createElement('span');
        phoneError.id = 'phoneError';
        phoneError.style.color = 'red';
        phoneError.style.fontSize = '13px';
        phoneError.style.display = 'block';
        phoneError.style.marginTop = '3px';
        phoneInput.parentNode.appendChild(phoneError);
    }

    // Disable Buy Data button initially
    payBtn.disabled = true;
    payBtn.style.opacity = 0.6;
    payBtn.style.cursor = "not-allowed";

    let phoneTimeout = null;
    phoneInput.addEventListener('input', function () {
        phoneError.textContent = '';
        if (phoneTimeout) clearTimeout(phoneTimeout);

        // Only show error if user stops typing for 600ms
        phoneTimeout = setTimeout(function () {
            const value = phoneInput.value.trim();
            if (value.length === 11) {
                phoneError.textContent = '';
                payBtn.disabled = false;
                payBtn.style.opacity = 1;
                payBtn.style.cursor = "pointer";
            } else {
                if (value.length > 0 && value.length !== 11) {
                    phoneError.textContent = 'Invalid phone number';
                } else {
                    phoneError.textContent = '';
                }
                payBtn.disabled = true;
                payBtn.style.opacity = 0.6;
                payBtn.style.cursor = "not-allowed";
            }
        }, 600);
    });
});
</script>
<!-- Place this before </body> -->
<div id="confirmOverlay" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.55); z-index:99999; justify-content:center; align-items:center;">
    <div style=" border-radius:12px; max-width:350px; width:90%; padding:28px 18px 18px 18px; box-shadow:0 8px 32px rgba(0,0,0,0.18); text-align:center; position:relative;">
        <h2 style="margin-bottom:18px; color:#043927; font-size:20px;">Confirm Your Order</h2>
        <div style="margin-bottom:12px; text-align:left;">
            <div><b>Network:</b> <span id="confirmNetwork"></span></div>
            <div><b>Phone:</b> <span id="confirmPhone"></span></div>
            <div><b>Plan:</b> <span id="confirmPlan"></span></div>
            <div><b>Amount:</b> <span id="confirmAmount"></span></div>
        </div>
        <button id="confirmBtn" style="background:#043927; color:#fff; border:none; border-radius:6px; padding:10px 28px; font-size:16px; margin-right:10px; cursor:pointer;">Confirm</button>
        <button id="cancelBtn" style="background:#eee; color:#222; border:none; border-radius:6px; padding:10px 22px; font-size:16px; cursor:pointer;">Cancel</button>
    </div>
</div>
</body>

</html>

