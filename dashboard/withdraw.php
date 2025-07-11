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
$user_query  = "SELECT balance, referral_earnings, account_code FROM virtual_accounts WHERE acct_id=?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
if ($user_result->num_rows != 1) {
    die("User not found.");
}
$user            = $user_result->fetch_assoc();
$account_balance = $user['balance'];
$referral_earnings = $user['referral_earnings']; // Fetch referral earnings
$recipient_code  = !empty($user['account_code']) ? $user['account_code'] : null;

echo "<script>console.log('User ID: $user_id, Recipient Code: $recipient_code');</script>";

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

// Process withdrawal order only if the withdrawal form is submitted and there is a linked recipient
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['withdraw']) && !empty($recipient_code)) {

    $amount = floatval($_POST['amount']); // Withdrawal amount in NGN
    $charge = 50; // ₦50 charge
    $total_deduction = $amount + $charge;

    // Determine the account to withdraw from
    $withdraw_from = $_POST['withdraw_from'] ?? 'active_balance';

    if ($withdraw_from === 'active_balance') {
        $current_balance = $account_balance;
        $update_query = "UPDATE virtual_accounts SET balance = ? WHERE acct_id = ?";
    } elseif ($withdraw_from === 'eaziflow_balance') {
        $current_balance = $referral_earnings;
        $update_query = "UPDATE virtual_accounts SET referral_earnings = ? WHERE acct_id = ?";
    } else {
        die("Invalid account selection.");
    }

    if ($total_deduction > $current_balance) {
        die("Insufficient balance for this withdrawal including a ₦50 charge.");
    }

    $paystack_secret_key = $_ENV['PK_SECRET']; // Or your actual secret key

    // Prepare transfer data. Amount must be in kobo.
    $transfer_data = [
        "source"    => "balance",
        "amount"    => $amount * 100,
        "recipient" => $recipient_code,
        "reason"    => "Withdrawal for user ID: $user_id"
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.paystack.co/transfer",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($transfer_data),
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . $paystack_secret_key,
            "Content-Type: application/json"
        ],
    ]);
    $transfer_response = curl_exec($curl);
    $transfer_error = curl_error($curl);
    curl_close($curl);

    if ($transfer_error) {
        die("Curl Error: " . $transfer_error);
    }
    $transfer_result = json_decode($transfer_response, true);
    if (!isset($transfer_result['status']) || $transfer_result['status'] !== true) {
        $error_msg = $transfer_result['message'] ?? 'Error initiating transfer';
        $_SESSION['status_type'] = "failure";
        $_SESSION['status_message'] = "Withdrawal failed: Payment server error";
        header('Location: ../success.php');
        exit;
    }

    // Update the selected account balance (deduct withdrawal amount + ₦50 charge)
    $new_balance = $current_balance - $total_deduction;
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ds", $new_balance, $user_id);
    if (!$update_stmt->execute()) {
        die("Failed to update account balance: " . $conn->error);
    }
    $update_stmt->close();

    $description = "Withdraw ₦" . number_format($amount, 2) . " with ₦50 charge";
    $status = "success";
    $item = "Funds";
    $transaction_type = "withdrawal";
    $receiver = $recipient_code; // The recipient code for the transfer
    $transaction_id = 'WD'.time(); // Use the Paystack transaction ID
    
    $_SESSION['status_type'] = "success";
    $_SESSION['status_message'] = "Withdrawal of ₦" . number_format($amount, 2) . " was successful. ₦50 charge applied.";

    // Optionally, log the transaction here
    logTransaction($conn, $user_id, $transaction_id, $total_deduction, $description, $status, $item, $transaction_type, $receiver);
    header("Location: ../success.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Withdrawal Order</title>
    <link rel="stylesheet" href="../css/transaction.css">
    <link href="https://fonts.googleapis.com/css2?family=Audiowide&display=swap" rel="stylesheet">
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
        .balance-box { background: #222; color: #ffbf00; padding: 10px 15px; border-radius: 6px; margin-bottom: 18px; font-size: 14px; text-align: center; }
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
            border-radius: 10px;
            max-width: 400px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.2);
            position: relative;
            width: 100vw;
            height: 100vh;
            padding: 0;
            display: flex;
            align-items: stretch;
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

        .balance-box {
            background: #222;
            color: #ffbf00;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 18px;
            font-size: 14px;
            text-align: center;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border 0.3s ease;
        }

        .balance-box.selected {
            border: 2px solid #ffbf00;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Withdrawal Order</h2>
    <form action="withdraw.php" method="POST" id="withdrawForm">
        <div style="font-size: 14px; color: #ffbf00; margin-bottom: 10px; font-weight: 600;">
            Select withdrawal account:
        </div>
        <div class="balance-box selectable" data-value="active_balance">
            <span>Active Balance: ₦<?php echo number_format($account_balance, 2); ?></span>
        </div>
        <div class="balance-box selectable" data-value="eaziflow_balance">
            <span>EaziFlow Earnings: ₦<?php 
                // Fetch referral earnings from the virtual_accounts table
                $referral_earnings_query = "SELECT referral_earnings FROM virtual_accounts WHERE acct_id = ?";
                $stmt3 = $conn->prepare($referral_earnings_query);
                $stmt3->bind_param("s", $user_id);
                $stmt3->execute();
                $referral_earnings_result = $stmt3->get_result();
                if ($referral_earnings_result->num_rows == 1) {
                    $referral_earnings_row = $referral_earnings_result->fetch_assoc();
                    $referral_earnings = $referral_earnings_row["referral_earnings"];
                    echo number_format($referral_earnings, 2);
                } else {
                    echo "0.00"; // Default value if no referral earnings found
                }
                $stmt3->close();
            ?></span>
        </div>
        <input type="hidden" name="withdraw_from" id="withdrawFrom" value="active_balance">
        <div class="form-group">
            <label for="amount">Withdrawal Amount (₦)</label>
            <input type="number" id="amount" name="amount" min="5000" required>
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
<?php
$recipient_code = isset($user['account_code']) ? trim($user['account_code']) : null;
?>
<!-- Debug: -->
<?php echo "<!-- recipient_code: " . var_export($recipient_code, true) . " -->"; ?>

<?php if (empty($recipient_code)): ?>
<div id="linkModal" class="modal" style="display:flex;">
  <div class="modal-content">
    <iframe src="linkAccount.php" style="width:100vw; height:100vh; border:none;"></iframe>
  </div>
</div>
<?php endif; ?>

<script>
// JS for enabling/disabling the withdraw button
const amountInput = document.getElementById('amount');
const withdrawBtn = document.getElementById('withdrawBtn');
const infoText = document.getElementById('infoText');
const withdrawFromInput = document.getElementById('withdrawFrom');
const balanceBoxes = document.querySelectorAll('.balance-box.selectable');

// Fetch balances from PHP
const activeBalance = <?php echo json_encode($account_balance); ?>;
const eaziFlowBalance = <?php echo json_encode($referral_earnings ?? 0); ?>;
const charge = 50;

function validateWithdraw() {
    let amount = parseFloat(amountInput.value) || 0;
    let total = amount + charge;

    // Get the selected account balance
    let selectedBalance = withdrawFromInput.value === "active_balance" ? activeBalance : eaziFlowBalance;

    if (amount >= 5000 && total <= selectedBalance) {
        withdrawBtn.disabled = false;
        infoText.textContent = "Total deduction (including ₦50 charge): ₦" + total.toLocaleString();
    } else {
        withdrawBtn.disabled = true;
        if (amount < 5000) {
            infoText.textContent = "Minimum withdrawal amount is ₦5000";
        } else if (total > selectedBalance) {
            infoText.textContent = "Insufficient funds in the selected account (₦" + total.toLocaleString() + " required)";
        } else {
            infoText.textContent = "";
        }
    }
}

// Account selection logic
document.addEventListener('DOMContentLoaded', function () {
    balanceBoxes.forEach(box => {
        box.addEventListener('click', function () {
            // Remove the 'selected' class from all balance boxes
            balanceBoxes.forEach(b => b.classList.remove('selected'));

            // Add the 'selected' class to the clicked box
            this.classList.add('selected');

            // Update the hidden input value
            withdrawFromInput.value = this.getAttribute('data-value');

            // Revalidate the withdrawal amount
            validateWithdraw();
        });
    });

    // Set the default selected box
    const defaultSelected = document.querySelector('.balance-box[data-value="active_balance"]');
    if (defaultSelected) {
        defaultSelected.classList.add('selected');
    }

    // Validate withdrawal on page load
    validateWithdraw();
});

// Validate withdrawal on input change
amountInput.addEventListener('input', validateWithdraw);

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