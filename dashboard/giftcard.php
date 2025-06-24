<?php
session_start();
include '../config.php'; // Include your database connection details

if (isset($_SESSION['modal'])) {
    $type = $_SESSION['modal']['type'];
    $message = $_SESSION['modal']['message'];
    echo "<script>showModal('$type', " . json_encode($message) . ");</script>";
    unset($_SESSION['modal']);
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
    <title>Redeem giftcard</title>

    <style>
        span {
            color: green;
        }
        .preloader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(19, 19, 19, 0.3);
            backdrop-filter: blur(8px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .preloader-img {
            width: 100px;
            height: 100px;
        }
        .network-select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .num-network-row {
            margin: auto;
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            height: 40px;
            justify-content: left;
            width: 90%;
            border: 1px solid #ffbf00;
            border-radius: 5px;
            /* flex-wrap: wrap; */
        }
        .custom-select-wrapper {
            width: 20%;
        }
        .custom-select {
            border-radius: 5px;
            cursor: pointer;
            position: relative;
            width: fit-content;
        }
        .custom-select .selected {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 39.5px;
            width: 100%;
            border-top-right-radius: 5px;
            border-bottom-right-radius: 5px;
            background: #fff;
            padding: 0;
        }
        .custom-select .options {
            display: none;
            position: absolute;
            top: 110%;
            right: 0;
            width: 280px;
            background: white;
            border: 1px solid #ccc;
            font-weight: 400;
            border-radius: 5px;
            overflow-y: scroll;
            color: black;
            z-index: 10;
            height: 20vh;
            padding-block: 10px;
        }
        .custom-select.open .options {
            display: block;
        }
        .custom-select .options div {
            padding: 6px 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            font-size: 18px;
            gap: 6px;
        }
        .custom-select .options div:hover {
            background: #f0f0f0;
        }
        .phone-input{
            width: 90%;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: left;
        }
        .phone-input input {
            width: 100%;
            text-align: left;
            margin-right: auto;
            height: 32px;
            font-size: 16px;
            font-family: "poppins";
            font-weight: 400;
        }
        .selected svg {
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
        .custom-file-label {
            display: flex;
            margin: auto;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 2px dashed #ffbf00;
            border-radius: 8px;
            width: 90%;
            height: 90px;
            cursor: pointer;
            color: #888;
            font-size: 16px;
            transition: border-color 0.2s;
            margin-top: 8px;
            margin-bottom: 8px;
            position: relative;
            overflow: hidden;
            padding: 0;
        }
        .custom-file-label img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 8px;
            position: absolute;
            top: 0; left: 0;
            z-index: 1;
            background: #fff;
        }
        .file-label-text {
            font-size: 14px;
            color: #888;
            z-index: 2;
            position: relative;
            background: rgba(255,255,255,0.8);
            padding: 4px 8px;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="preloader-overlay" id="preloader">
        <img src="../css/imgs/eazi.gif" class="preloader-img" alt="Loading...">
    </div>

    <main>
        <div style="font-weight: 400; color: #ffbf00; border:0px solid #ccc; width: 30%; border-right: none;  margin-left: auto; margin-top: 10px; padding: 1px; border-top-left-radius: 5px; font-size: 13px; display: flex; justify-content: center; align-items:center;"><img src="../css/svg/script.svg" style="width: 15px; padding: 3px;"/><a href="./transaction.php?product=giftcard" style="color: #ffbf00; text-decoration: none;">View orders</a></div>
        <div class="container">
            <div class="network">
                <div class="network-header">
                    <div style="border: 0px solid white; width: 50%; margin: auto;">
                        <img src="../css/imgs/giftcard.png" alt="Giftcard Logo" class="giftcard-logo">
                    </div>
                    <p style="color: #ccc; ">Redeem giftcard.</p>
                    <div id="convertedAmount" style="margin:auto; color:#ccc; font-weight:400; font-size:14px; border:0px solid white; width: 90%; height: 20px;"></div>
                </div>
                <form method="post" action="../purchase/giftcard_handler.php" enctype="multipart/form-data" id="airtimeForm">
                    <div class="num-network-row" id="networkRow">
                        <!-- Custom Network Dropdown -->
                        <div>
                            <select name="currency" id="currency" style="width: 60px; height: 40px; font-size: 16px;">
                                <option value="USD">USD</option>
                                <option value="CAD">CAD</option>
                                <option value="AUD">AUD</option>
                                <option value="EUR">EUR</option>
                                <option value="GBP">GBP</option>
                            </select>
                        </div>
                        <!-- Amount Input -->
                        <div class="phone-input">
                            <input type="number" class="amount" name="number" placeholder="Amount" >
                        </div>
                        <!-- Giftcard Type Dropdown -->
                        <div class="custom-select-wrapper">
                            <div class="custom-select" id="networkSelect">
                                <div class="selected" style="gap:10px;">
                                    <img src="../css/giftsvg/amazon.svg" alt="Select Network" id="selectedLogo"
                                        style="width:44px;height:30px;vertical-align:middle;">
                                    <div style="border: 0px solid white; padding: 0px; margin-left: -13px;">
                                        <svg id="dropdownArrow" width="18" height="18" viewBox="0 0 20 20" style="pointer-events:none;">
                                            <polyline points="6 8 10 12 14 8" fill="none" stroke="#888" stroke-width="2"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="options">
                                    <?php
                                    // Fetch card types and rates from DB
                                    $card_types = [];
                                    $card_rates = [];
                                         if ($conn->connect_error) {
                                        die("Database connection failed: " . $conn->connect_error);
                                    }
                                    $result = $conn->query("SELECT id, name, image FROM card_types");
                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $card_types[] = $row;
                                        }
                                    }
                                    // Fetch rates for all cards
                                    $rates_result = $conn->query("SELECT * FROM giftcard_rates WHERE status='active'");
                                    if ($rates_result && $rates_result->num_rows > 0) {
                                        while ($row = $rates_result->fetch_assoc()) {
                                            $card_rates[] = $row;
                                        }
                                    }
                                    $conn->close();
                                    foreach ($card_types as $card): ?>
                                    <div data-value="<?= htmlspecialchars(strtolower($card['name'])) ?>" data-label="<?= htmlspecialchars($card['name']) ?>" data-id="<?= htmlspecialchars($card['id']) ?>">
                                        <img src="<?= htmlspecialchars($card['image']) ?>"
                                            style="width: 44px;height:34px;vertical-align:middle;"> <?= htmlspecialchars($card['name']) ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <input type="hidden" name="network" id="networkInput" value="amazon">
                            <input type="hidden" id="networkIdInput" value="<?= isset($card_types[0]['id']) ? $card_types[0]['id'] : '' ?>">
                        </div>
                    </div> <!-- END .num-network-row -->

                    <!-- The following content is now OUTSIDE .num-network-row -->
                    <div class="fig" style="width: 90%; height: 40px; border: 1px solid #ffbf00; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                        <input type="text" class="card_code" name="card_code" placeholder="Enter Card Code" style="width: 100%; height: 40px; font-size: 16px; margin: 10px 0;">
                    </div>
                    <div style="font-size: 14px;">
                        or
                    </div>
                    <div class="amount-input" style="margin-top: -5px;">
                        <label for="cardImageInput" class="custom-file-label" id="customFileLabel" style="padding:0;">
                            <span class="file-label-text">Upload Card Image</span>
                        </label>
                        <input type="file" class="card_image" name="card_image" id="cardImageInput" accept="image/*" style="display:none;"/>
                    </div>
                    <input type="hidden" name="converted_amount" id="convertedAmountInput" value="">
                    <input type="hidden" name="original_amount" id="originalAmountInput" value="">
                    <div style="width: 90%; margin: auto; text-align: center; margin-top: 40px;">
                        <input class="submit" name="submit" type="submit" value="Pay Now" style="opacity:0.6;cursor:not-allowed;">
                    </div>
                </form>
            </div>
        </div>
    </main>
    <footer style="color: #ccc; position: absolute; bottom: 10px; width: 90%; left: 25px; font-size: 12px; text-align: center; border: 0px solid #ccc;">
        <div class="footer">
            <p>&copy; 2023 Eazi Plux. All rights reserved.</p>
        </div>
    </footer>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Custom Dropdown Logic ---
    const select = document.getElementById('networkSelect');
    const selected = select ? select.querySelector('.selected') : null;
    const options = select ? select.querySelector('.options') : null;
    const networkInput = document.getElementById('networkInput');
    const selectedLogo = document.getElementById('selectedLogo');
    const networkRow = document.getElementById('networkRow');
    const amountBorder = document.querySelector('.fig');
    const networkIdInput = document.getElementById('networkIdInput');
    const optionsDivs = options ? options.querySelectorAll('div') : [];

    if (select && selected && options) {
        if (networkRow) networkRow.style.borderColor = '#ffbf00';
        if (amountBorder) amountBorder.style.borderColor = '#ffbf00';

        selected.addEventListener('click', function (e) {
            e.stopPropagation();
            select.classList.toggle('open');
        });

        optionsDivs.forEach(option => {
            option.addEventListener('click', function (e) {
                e.stopPropagation();
                const value = this.getAttribute('data-value');
                const img = this.querySelector('img').src;
                const id = this.getAttribute('data-id');
                if (selectedLogo) selectedLogo.src = img;
                if (networkInput) networkInput.value = value;
                if (networkIdInput) networkIdInput.value = id;
                select.classList.remove('open');
                if (networkRow) networkRow.style.borderColor = '#ffbf00';
                if (amountBorder) amountBorder.style.borderColor = '#ffbf00';
                if (typeof calculateConverted === "function") calculateConverted();
            });
        });

        // Close dropdown if clicked outside
        document.addEventListener('click', function (e) {
            if (!select.contains(e.target)) {
                select.classList.remove('open');
            }
        });
    }

    // --- Enable/disable submit button logic ---
    const cardImageInput = document.getElementById('cardImageInput');
    const payBtn = document.querySelector('input[type="submit"].submit');
    const cardCodeInput = document.querySelector('input[name="card_code"]');
    const amountInput = document.querySelector('input[name="number"]');
    const currencySelect = document.getElementById('currency');
    const convertedAmountDiv = document.getElementById('convertedAmount');

    function checkEnableButton() {
        const cardCodeFilled = cardCodeInput && cardCodeInput.value.trim() !== "";
        const cardImageFilled = cardImageInput && cardImageInput.files.length > 0;
        let enable = cardCodeFilled || cardImageFilled;

        // --- Disable if amount is out of allowed range for selected card ---
        const amount = parseFloat(amountInput.value);
        let selectedCardId = networkIdInput.value;
        let validAmount = false;
        if (selectedCardId && amount) {
            // Find the correct rate row for this card and amount
            let rateRow = null;
            for (let i = 0; i < cardRates.length; i++) {
                const row = cardRates[i];
                if (
                    row.card_type_id == selectedCardId &&
                    amount >= parseFloat(row.min_amount) &&
                    amount <= parseFloat(row.max_amount)
                ) {
                    rateRow = row;
                    break;
                }
            }
            validAmount = !!rateRow;
        }
        if (!validAmount) enable = false;

        if (enable) {
            payBtn.disabled = false;
            payBtn.style.opacity = 1;
            payBtn.style.cursor = "pointer";
        } else {
            payBtn.disabled = true;
            payBtn.style.opacity = 0.6;
            payBtn.style.cursor = "not-allowed";
        }
    }

    if (cardCodeInput) cardCodeInput.addEventListener('input', checkEnableButton);
    if (cardImageInput) cardImageInput.addEventListener('change', checkEnableButton);
    if (amountInput) amountInput.addEventListener('input', checkEnableButton);
    if (currencySelect) currencySelect.addEventListener('change', checkEnableButton);
    if (networkIdInput) networkIdInput.addEventListener('change', checkEnableButton);
    checkEnableButton();

    // --- Show preview of uploaded card image in the upload box ---
    const customFileLabel = document.getElementById('customFileLabel');
    const fileLabelText = customFileLabel ? customFileLabel.querySelector('.file-label-text') : null;
    let previewImg = null;

    if (cardImageInput && customFileLabel && fileLabelText) {
        cardImageInput.addEventListener('change', function () {
            if (previewImg) {
                customFileLabel.removeChild(previewImg);
                previewImg = null;
            }
            if (cardImageInput.files && cardImageInput.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg = document.createElement('img');
                    previewImg.src = e.target.result;
                    previewImg.style.width = '100%';
                    previewImg.style.height = '100%';
                    previewImg.style.objectFit = 'contain';
                    previewImg.style.position = 'absolute';
                    previewImg.style.top = 0;
                    previewImg.style.left = 0;
                    previewImg.style.borderRadius = '8px';
                    customFileLabel.insertBefore(previewImg, fileLabelText);
                    fileLabelText.textContent = cardImageInput.files[0].name;
                };
                reader.readAsDataURL(cardImageInput.files[0]);
            } else {
                fileLabelText.textContent = "Upload Card Image";
            }
        });
    }

    // --- Real-time rate calculation ---
    // Pass PHP rates to JS
    const cardRates = <?php echo json_encode($card_rates); ?>;
    // Map card name (lowercase) to id for quick lookup
    const cardNameToId = {
        <?php
        $pairs = [];
        foreach ($card_types as $card) {
            $key = strtolower($card['name']);
            $val = $card['id'];
            $pairs[] = "\"$key\": $val";
        }
        echo implode(',', $pairs);
        ?>
    };

    // Currency to DB column mapping
    const currencyToCol = {
        "USD": "rate_usd",
        "CAD": "rate_cad",
        "AUD": "rate_aud",
        "EUR": "rate_eur",
        "GBP": "rate_gbp"
    };

    function calculateConverted() {
        const amount = parseFloat(amountInput.value);
        const currency = currencySelect.value;
        let selectedCardId = networkIdInput.value;
        const convertedAmountInput = document.getElementById('convertedAmountInput');
        const originalAmountInput = document.getElementById('originalAmountInput');
        if (!selectedCardId || !amount) {
            convertedAmountDiv.textContent = '';
            if (convertedAmountInput) convertedAmountInput.value = '';
            if (originalAmountInput) originalAmountInput.value = '';
            return;
        }
        // Find the correct rate row for this card and amount
        let rateRow = null;
        for (let i = 0; i < cardRates.length; i++) {
            const row = cardRates[i];
            if (
                row.card_type_id == selectedCardId &&
                amount >= parseFloat(row.min_amount) &&
                amount <= parseFloat(row.max_amount)
            ) {
                rateRow = row;
                break;
            }
        }
        if (!rateRow) {
            convertedAmountDiv.textContent = 'Min amount: 10 Max amount: 1000';
            convertedAmountDiv.style.color = 'red';
            if (convertedAmountInput) convertedAmountInput.value = '';
            if (originalAmountInput) originalAmountInput.value = '';
            return;
        }
        const rateCol = currencyToCol[currency];
        let rate = parseFloat(rateRow[rateCol]);
        if (!rate) {
            convertedAmountDiv.textContent = '';
            if (convertedAmountInput) convertedAmountInput.value = '';
            if (originalAmountInput) originalAmountInput.value = '';
            return;
        }
        // Calculate original and converted amounts
        const originalTotal = amount * rate;
        // Subtract 100 from the rate before calculation for converted amount
        const convertedRate = rate - 100;
        const convertedTotal = amount * convertedRate;
        convertedAmountDiv.textContent = `Converted Rate: ₦${convertedTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        convertedAmountDiv.style.color = 'gold';
        if (convertedAmountInput) convertedAmountInput.value = convertedTotal.toFixed(2);
        if (originalAmountInput) originalAmountInput.value = originalTotal.toFixed(2);
    }

    if (currencySelect) currencySelect.addEventListener('change', calculateConverted);
    if (amountInput) amountInput.addEventListener('input', calculateConverted);

    // --- Preloader on submit ---
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function () {
            document.getElementById('preloader').style.display = 'flex';
        });
    }
});
</script>

</body>
</html>