<?php
session_start();
include 'config.php'; // Include your database connection details

// Fetch card types and rates from the database
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
$rates_result = $conn->query("SELECT * FROM giftcard_rates WHERE status='active'");
if ($rates_result && $rates_result->num_rows > 0) {
    while ($row = $rates_result->fetch_assoc()) {
        $card_rates[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Calculator</title>
    <link rel="stylesheet" href="../css/rate-calculator.css">
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background-color: #000;
            color: #fff;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            text-align: center;
        }

        .dropdown, .input-field {
            margin-bottom: 20px;
        }

        select, input {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ffbf00;
            background-color: #000;
            color: #fff;
            font-size: 16px;
        }

        input[type="number"] {
            /* -moz-appearance: textfield; */
            width: 90%;
        }

        .result {
            margin-top: 20px;
            font-size: 18px;
            color: #ffbf00;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Rate Calculator</h1>
        <p>Calculate the rate for your gift card based on the selected card type, currency, and amount.</p>

        <!-- Card Type Dropdown -->
        <div class="dropdown">
            <label for="cardType">Select Card Type:</label>
            <select id="cardType">
                <?php foreach ($card_types as $card): ?>
                    <option value="<?= htmlspecialchars($card['id']) ?>" data-name="<?= htmlspecialchars($card['name']) ?>">
                        <?= htmlspecialchars($card['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Currency Dropdown -->
        <div class="dropdown">
            <label for="currency">Select Currency:</label>
            <select id="currency">
                <option value="USD">USD</option>
                <option value="CAD">CAD</option>
                <option value="AUD">AUD</option>
                <option value="EUR">EUR</option>
                <option value="GBP">GBP</option>
            </select>
        </div>

        <!-- Amount Input -->
        <div class="input-field">
            <label for="amount">Enter Amount:</label>
            <input type="number" id="amount" placeholder="Enter amount">
        </div>

        <!-- Result -->
        <div class="result" id="result">Converted Rate: ₦0.00</div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cardRates = <?php echo json_encode($card_rates); ?>;
            const currencyToCol = {
                "USD": "rate_usd",
                "CAD": "rate_cad",
                "AUD": "rate_aud",
                "EUR": "rate_eur",
                "GBP": "rate_gbp"
            };

            const cardTypeSelect = document.getElementById('cardType');
            const currencySelect = document.getElementById('currency');
            const amountInput = document.getElementById('amount');
            const resultDiv = document.getElementById('result');

            function calculateRate() {
                const cardTypeId = cardTypeSelect.value;
                const currency = currencySelect.value;
                const amount = parseFloat(amountInput.value);

                if (!cardTypeId || !currency || !amount || amount <= 0) {
                    resultDiv.textContent = 'Converted Rate: ₦0.00';
                    return;
                }

                // Find the correct rate row for this card and amount
                let rateRow = null;
                for (let i = 0; i < cardRates.length; i++) {
                    const row = cardRates[i];
                    if (
                        row.card_type_id == cardTypeId &&
                        amount >= parseFloat(row.min_amount) &&
                        amount <= parseFloat(row.max_amount)
                    ) {
                        rateRow = row;
                        break;
                    }
                }

                if (!rateRow) {
                    resultDiv.textContent = 'No valid rate found for the entered amount.';
                    return;
                }

                const rateCol = currencyToCol[currency];
                const rate = parseFloat(rateRow[rateCol]);
                if (!rate) {
                    resultDiv.textContent = 'No valid rate found for the selected currency.';
                    return;
                }

                const convertedRate = amount * (rate - 100); // Subtract 100 from the rate
                resultDiv.textContent = `Converted Rate: ₦${convertedRate.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            }

            cardTypeSelect.addEventListener('change', calculateRate);
            currencySelect.addEventListener('change', calculateRate);
            amountInput.addEventListener('input', calculateRate);
        });
    </script>
</body>

</html>