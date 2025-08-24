<?php
session_start();

include '../config.php';

// Check if the connection is successful
if ($conn->connect_errno) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve user data from session
if (isset($_SESSION['user_id'])) {
    $username = $_SESSION['username'];

    // Fetch user ID
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $user_id = $row["user_id"];

        // Fetch user balance
        $balance_query = "SELECT balance FROM virtual_accounts WHERE acct_id = ?";
        $stmt2 = $conn->prepare($balance_query);
        $stmt2->bind_param("s", $user_id);
        $stmt2->execute();
        $balance_result = $stmt2->get_result();

        if ($balance_result->num_rows == 1) {
            $balance_row = $balance_result->fetch_assoc();
            $account_balance = $balance_row["balance"];

            $_SESSION["USER_ID"] = $user_id;
            $_SESSION['ACCT'] = $account_balance;
        } else {
            $account_balance = 0; // Default balance if not found
        }
        $stmt2->close();
    }
    $stmt->close();
}

// Transaction logging helper (no transaction_time)
function logTransaction($conn, $user_id, $transaction_id, $amount, $description, $status, $item, $transaction_type, $receiver) {
    $query = "INSERT INTO transaction_history (user_id, transaction_id, amount, description, status, item, transaction_type, receiver)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssdsssss", $user_id, $transaction_id, $amount, $description, $status, $item, $transaction_type, $receiver);
    $stmt->execute();
    $stmt->close();
}

// Transaction handler: sets session and redirects to a single status page
function transactionHandler($type, $message) {
    $_SESSION['status_type'] = $type; // 'success' or 'failure'
    $_SESSION['status_message'] = $message;
    header("Location: ../success.php");
    exit;
}

// Handle transaction failure
function handleTransactionFailure($user_id, $responseArray, $failureReason, $phoneNumber, $adjustedPrice, $conn, $item) {
    // $failureReason = isset($responseArray["description"]) ? $responseArray["description"] : "Unknown error";
    $failureStatus = isset($responseArray["status"]) ? $responseArray["status"] : "Failed";
    $transaction_id = "EP".time();
    logTransaction($conn, $user_id, $transaction_id, $adjustedPrice, $failureReason, "Failed", $item, "Data", $phoneNumber);

    transactionHandler('failure', "Transaction failed: $failureReason");
}

// Check if the form is submitted
if (isset($_POST['submit'])) {
    $selectedNetwork = isset($_POST['network']) ? strtolower($_POST['network']) : 'mtn';
    $selectedPlanValue = isset($_POST['plans']) ? $_POST['plans'] : '';
    $phoneNumber = isset($_POST['number']) ? preg_replace('/[^\d]/', '', $_POST['number']) : '';
    $item = isset($_POST['item']) ? $_POST['item'] : 'data';
    $postedPrice = isset($_POST['price']) ? (int)$_POST['price'] : 0;
    $user_id = $_SESSION["USER_ID"];

    // Map network to serviceID
    switch ($selectedNetwork) {
        case 'mtn':
            $serviceID = 'mtn_gifting';
            break;
        case 'mtn-sme':
            $serviceID = 'mtn_sme';
            break;
        case 'airtel':
            $serviceID = 'airtel_gifting';
            break;
        case 'airtel-sme':
            $serviceID = 'airtel_sme';
            break;
        case 'glo':
            $serviceID = 'glo_data';
            break;
        case 'glo-sme':
            $serviceID = 'glo_sme';
            break;
        case '9mobile':
            $serviceID = 'etisalat_data';
            break;
        default:
            $serviceID = 'mtn_gifting'; // Default to MTN if no match
            break;
    }

    $adjustedPrice = $postedPrice;
    $_SESSION['amount'] = $adjustedPrice;

    // Check if the user's virtual account balance is sufficient
    if ($account_balance < $adjustedPrice) {
        $transaction_id = "EP".time();
        logTransaction($conn, $user_id, $transaction_id, $adjustedPrice, "Insufficient Funds", "Failed", $item, "Data", $phoneNumber);
        
        handleReferralCredit($conn, $user_id);
        transactionHandler('failure', "Transaction failed: Fund your eaziplux wallet.");
    } else {
        // If the balance is sufficient, proceed with the purchase
        purchaseData($adjustedPrice, $phoneNumber, $user_id, $selectedPlanValue, $conn, $serviceID, $item);
    }
}

// Function to handle the purchase logic
function purchaseData($adjustedPrice, $phoneNumber, $user_id, $selectedPlanValue, $conn, $serviceID, $item)
{
    // Begin transaction
    $conn->begin_transaction();

    try {
        // Lock the user's balance row
        $query = "SELECT balance, referral_earnings FROM virtual_accounts WHERE acct_id = ? FOR UPDATE";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $balance = $row['balance'];
            $referral_earnings = $row['referral_earnings'];
        } else {
            $conn->rollback();
            transactionHandler('failure', "Transaction failed: Unable to fetch account details.");
            return;
        }
        $stmt->close();

        // Check if the user's virtual account balance is sufficient
        if ($balance < $adjustedPrice) {
            $transaction_id = "EP".time();
            logTransaction($conn, $user_id, $transaction_id, $adjustedPrice, "Insufficient Funds", "Failed", $item, "Data", $phoneNumber);
            $conn->rollback();
            handleReferralCredit($conn, $user_id);
            transactionHandler('failure', "Transaction failed: Fund your eaziplux wallet.");
        }

        // Proceed with the purchase
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://gsubz.com/api/pay/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array(
                    'serviceID' => $serviceID,
                    'plan' => $selectedPlanValue,
                    'api' => $_ENV['GSUBZ'],
                    'amount' => '',
                    'phone' => $phoneNumber
                ),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $_ENV['GSUBZ'],
                ),
            )
        );

        $response = curl_exec($curl);
        curl_close($curl);

        $responseArray = json_decode($response, true);

        // Handle transaction status
        if (isset($responseArray["status"]) && (
            $responseArray["status"] === "TRANSACTION_FAILED" ||
            $responseArray["status"] === "failed" ||
            $responseArray["status"] === "Reversed"
        )) {
            $conn->rollback();
            handleTransactionFailure($user_id, $responseArray, 'Server error!', $phoneNumber, $adjustedPrice, $conn, $item);
        } else {
            // Transaction was successful, now debit the user
            $new_balance = $balance - $adjustedPrice;
            $update_balance_query = "UPDATE virtual_accounts SET balance = ? WHERE acct_id = ?";
            $stmt = $conn->prepare($update_balance_query);
            $stmt->bind_param("ds", $new_balance, $user_id);
            $stmt->execute();
            $stmt->close();

            $transaction_id = "EP" . time();
            logTransaction($conn, $user_id, $transaction_id, $adjustedPrice, "Transaction Successful", "Successful", $item, "Data", $phoneNumber);

            $conn->commit();

            handleReferralCredit($conn, $user_id);
            transactionHandler('success', 'Successfully purchased ₦' . $adjustedPrice . " worth of " . $item . " data");
        }
    } catch (Exception $e) {
        $conn->rollback();
        transactionHandler('failure', "Transaction error. Please try again.");
    }
}

//Debit user

function debitUser($adjustedPrice, $phoneNumber, $user_id, $selectedPlanValue, $conn, $serviceID, $item)
{

    // Fetch the user's balance and referral earnings
    $query = "SELECT balance, referral_earnings FROM virtual_accounts WHERE acct_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $balance = $row['balance'];
        $referral_earnings = $row['referral_earnings'];
    } else {
        transactionHandler('failure', "Transaction failed: Unable to fetch account details.");
        return;
    }
    $stmt->close();

    // Determine the source of funds
    $remainingAmount = $adjustedPrice;
    $useReferralEarnings = false;



    if ($balance >= $adjustedPrice) {
        // Deduct from balance
        $new_balance = $balance - $adjustedPrice;
        $update_balance_query = "UPDATE virtual_accounts SET balance = ? WHERE acct_id = ?";
        $stmt = $conn->prepare($update_balance_query);
        $stmt->bind_param("ds", $new_balance, $user_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($balance + $referral_earnings >= $adjustedPrice) {
        // Deduct from both balance and referral earnings
        $useReferralEarnings = true;

        if ($balance > 0) {
            $remainingAmount -= $balance;
            $new_balance = 0;
            $update_balance_query = "UPDATE virtual_accounts SET balance = ? WHERE acct_id = ?";
            $stmt = $conn->prepare($update_balance_query);
            $stmt->bind_param("ds", $new_balance, $user_id);
            $stmt->execute();
            $stmt->close();
        }

        // Deduct the remaining amount from referral earnings
        $new_referral_earnings = $referral_earnings - $remainingAmount;
        $update_referral_earnings_query = "UPDATE virtual_accounts SET referral_earnings = ? WHERE acct_id = ?";
        $stmt = $conn->prepare($update_referral_earnings_query);
        $stmt->bind_param("ds", $new_referral_earnings, $user_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Insufficient funds
        $transaction_id = "EP" . time();
        logTransaction($conn, $user_id, $transaction_id, $adjustedPrice, "Insufficient Funds", "Failed", $item, "Data", $phoneNumber);
        transactionHandler('failure', "Transaction failed: Insufficient funds in wallet and referral earnings.");
        return;
    }

}



// Function to handle referral credit
function handleReferralCredit($conn, $user_id)
{
    // Fetch the referred_by referral code for the current user
    $query = "SELECT referred_by FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $referred_by_code = $row['referred_by'];

        if ($referred_by_code) {
            // Fetch the referee's user_id using the referral code
            $referee_query = "SELECT user_id FROM users WHERE referral_code = ?";
            $stmt2 = $conn->prepare($referee_query);
            $stmt2->bind_param("s", $referred_by_code);
            $stmt2->execute();
            $referee_result = $stmt2->get_result();

            if ($referee_result->num_rows > 0) {
                $referee_row = $referee_result->fetch_assoc();
                $referee_user_id = $referee_row['user_id'];

                // Credit the referee's referral_earnings in the virtual_accounts table
                $update_referral_earnings_query = "UPDATE virtual_accounts SET referral_earnings = referral_earnings + 5 WHERE acct_id = ?";
                $stmt3 = $conn->prepare($update_referral_earnings_query);
                $stmt3->bind_param("s", $referee_user_id);
                $stmt3->execute();
                $stmt3->close();

                // Log the referral earning transaction
                $description = "Referral earning credited for user purchase";
                $transaction_id = "REF".time();
                logTransaction($conn, $user_id, $transaction_id, 5, $description, "Successful", "Referral Credit", "Referral", $referee_user_id);
            }

            $stmt2->close();
        }
    }

    $stmt->close();
}
?>