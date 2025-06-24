<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../home/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
include '../config.php'; // Include your database connection details

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details and check for linked recipient code
$user_query  = "SELECT balance, account_code FROM virtual_accounts WHERE acct_id='$user_id'";
$user_result = $conn->query($user_query);
if ($user_result->num_rows != 1) {
    die("User not found.");
}
$user            = $user_result->fetch_assoc();
$account_balance = $user['balance'];
$recipient_code  = $user['account_code'];

$showLinkModal = false;
if (empty($recipient_code)) {
    $showLinkModal = true;
}

// Fetch payout account details from Paystack if recipient code exists
$payout_account = null;
$payout_error = null;
if (!empty($recipient_code)) {
    $paystack_secret_key = $_ENV['PK_SECRET'] ?? 'sk_test_xxx'; // Replace with your key or .env
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.paystack.co/transferrecipient/$recipient_code",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . $paystack_secret_key,
            "Content-Type: application/json"
        ],
    ]);
    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        $payout_error = "Unable to fetch payout account: $error";
    } else {
        $result = json_decode($response, true);
        if (isset($result['status']) && $result['status'] === true) {
            $payout_account = $result['data']['details'];
        } else {
            $payout_error = "Unable to fetch payout account: " . ($result['message'] ?? 'Unknown error');
        }
    }
}

function logTransaction($conn, $user_id, $transaction_id, $amount, $description, $status, $item, $transaction_type, $receiver) {
    $query = "INSERT INTO transaction_history (user_id, transaction_id, amount, description, status, item, transaction_type, receiver)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssdsssss", $user_id, $transaction_id, $amount, $description, $status, $item, $transaction_type, $receiver);
    $stmt->execute();
    $stmt->close();
}

// ...existing withdrawal processing code...
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Withdrawal Order</title>
    <link rel="stylesheet" href="../css/transaction.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&family=Truculenta:opsz,wght@12..72,100..900&display=swap"
        rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Poppins', Arial, sans-serif; background-color: #f7f8fa; }
        .container { max-width: 400px; margin: auto; padding: 20px; margin-top: 50px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); color: white; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; }
        input[type="number"] { width: 100%; height: 30px; border: 1px solid #ffbf00; border-radius: 4px; background: transparent; font-family: "poppins"; color: #fff;}
        button { background: transparent; color: #fff; padding: 10px; border: 1px solid #ffbf00; border-radius: 40px; width: 100%; font-size: 16px; font-family: "poppins";}
        .balance-box { background: #222; color: #ffbf00; padding: 10px 15px; border-radius: 6px; margin-bottom: 18px; font-size: 18px; text-align: center; }
        .modal, .overlay {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            background-color: rgba(0,0,0,0.85);
        }
        .overlay-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 500px;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.2);
            color: #000;
        }


        .modal-content{
            background: #fff;
            color: #222;
            margin: 0 auto;
            /* margin-top: 8vh; */
            /* padding: 32px 24px; */
            border-radius: 10px;
            max-width: 400px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.2);
            position: relative;
        }
        .close {
            color: #aaa;
            position: absolute;
            right: 18px;
            top: 12px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .view-payout-btn {
            background: #ffbf00;
            color: #222;
            border: none;
            border-radius: 30px;
            padding: 10px 24px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 18px;
            margin-top: 10px;
            display: block;
            width: 100%;
        }
        .payout-label {
            color: #888;
            font-size: 13px;
            margin-bottom: 2px;
        }
        .payout-value {
            font-size: 17px;
            font-weight: 600;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Withdrawal Order</h2>
    <div class="balance-box">
        Active Balance: ₦<?php echo number_format($account_balance, 2); ?>
    </div>
    <?php if ($payout_account): ?>
        <button class="view-payout-btn" onclick="showPayoutOverlay()">View Payout Account</button>
    <?php endif; ?>
    <form action="withdraw.php" method="POST" id="withdrawForm">
        <div class="form-group">
            <label for="amount">Withdrawal Amount (₦)</label>
            <input type="number" id="amount" name="amount" min="100" required>
            <small id="infoText" style="color:#ffbf00;display:block;margin-top:5px;font-size:12px;"></small>
        </div>
        <div class="form-group">
            <button type="submit" name="withdraw" id="withdrawBtn" disabled>Withdraw</button>
        </div>
    </form>
</div>
        <footer style="color: #ccc; position: absolute; bottom: 10px; width: 90%; left: 25px; font-size: 12px; text-align: center; border: 0px solid #ccc;">
    <div class="footer">
            <p>&copy; 2023 Eazi Plux. All rights reserved.</p>
        </div>
            <!-- <div>&copy; 2024 Strive inc. All right reserved <a href="https://eaziplux.com" target="_blank">Strive inc</a></div>     -->

    </footer>

<!-- Overlay for payout account -->
<div id="payoutOverlay" class="overlay">
    <div class="overlay-content">
        <span class="close" onclick="closePayoutOverlay()">&times;</span>
        <h3 style="text-align:center; margin-bottom:18px;">Payout Account Details</h3>
        <?php if ($payout_account): ?>
            <div class="payout-label">Bank Name</div>
            <div class="payout-value"><?php echo htmlspecialchars($payout_account['bank_name'] ?? ''); ?></div>
            <div class="payout-label">Account Number</div>
            <div class="payout-value"><?php echo htmlspecialchars($payout_account['account_number'] ?? ''); ?></div>
            <div class="payout-label">Account Name</div>
            <div class="payout-value"><?php echo htmlspecialchars($payout_account['account_name'] ?? ''); ?></div>
        <?php elseif ($payout_error): ?>
            <div style="color: #e74c3c; font-size: 12px;"><?php echo htmlspecialchars($payout_error); ?></div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal to display the standalone linkAccount file in an iframe if recipient code is empty -->
<div id="linkModal" class="modal">
  <div class="modal-content">
    <iframe src="linkAccount.php" style="width:100vw; height:100vh; border:none;"></iframe>
  </div>
</div>

<script>
function closeModal() {
    document.getElementById("linkModal").style.display = "none";
}
<?php if ($showLinkModal): ?>
document.getElementById("linkModal").style.display = "flex";
<?php endif; ?>

// JS for enabling/disabling the withdraw button
const amountInput = document.getElementById('amount');
const withdrawBtn = document.getElementById('withdrawBtn');
const infoText = document.getElementById('infoText');
const activeBalance = <?php echo json_encode($account_balance); ?>;
const charge = 50;

function validateWithdraw() {
    let amount = parseFloat(amountInput.value) || 0;
    let total = amount + charge;
    if (amount >= 100 && total <= activeBalance) {
        withdrawBtn.disabled = false;
        infoText.textContent = "Total deduction (including ₦50 charge): ₦" + total.toLocaleString();
    } else {
        withdrawBtn.disabled = true;
        if (amount < 100) {
            infoText.textContent = "Minimum withdrawal amount is ₦100";
        } else if (total > activeBalance) {
            infoText.textContent = "Insufficient balance for this withdrawal (₦" + total.toLocaleString() + " required)";
        } else {
            infoText.textContent = "";
        }
    }
}

amountInput.addEventListener('input', validateWithdraw);
window.addEventListener('DOMContentLoaded', validateWithdraw);

// Overlay logic for payout account
function showPayoutOverlay() {
    document.getElementById('payoutOverlay').style.display = 'block';
}
function closePayoutOverlay() {
    document.getElementById('payoutOverlay').style.display = 'none';
}
</script>
</body>
</html>