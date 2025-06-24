<?php

session_start();

include '../config.php'; // Include your database connection details
if (!isset($_SESSION["user_id"])) {
    header("Location: ../home/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define your Paystack secret key
$paystack_secret_key = $_ENV['PK_SECRET'];

// Fetch bank list from Paystack API
$bank_curl = curl_init();
curl_setopt_array($bank_curl, array(
    CURLOPT_URL => "https://api.paystack.co/bank?country=nigeria",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer " . $paystack_secret_key,
        "Content-Type: application/json"
    ),
));
$bank_response = curl_exec($bank_curl);
$bank_error = curl_error($bank_curl);
curl_close($bank_curl);

$banks = [];
if ($bank_error) {
    $error_message = "Error fetching bank list: " . $bank_error;
} else {
    $bank_result = json_decode($bank_response, true);
    if (isset($bank_result['status']) && $bank_result['status'] === true) {
        $banks = $bank_result['data'];
    } else {
        $error_message = "Error fetching bank list: " . ($bank_result['message'] ?? 'Unknown error');
    }
}

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {    
    // Sanitize form input
    $bank_code      = $conn->real_escape_string($_POST['bank_code']);
    $account_number = $conn->real_escape_string($_POST['account_number']);
    $account_name   = $conn->real_escape_string($_POST['account_name']);
    
    // Prepare transfer recipient data
    $recipient_data = [
        "type"           => "nuban",
        "name"           => $account_name,
        "account_number" => $account_number,
        "bank_code"      => $bank_code,
        "currency"       => "NGN"
    ];
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.paystack.co/transferrecipient",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($recipient_data),
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer " . $paystack_secret_key,
            "Content-Type: application/json"
        ),
    ));
    
    $recipient_response = curl_exec($curl);
    $recipient_error = curl_error($curl);
    curl_close($curl);
    
    if ($recipient_error) {
        $error_message = "Curl Error: " . $recipient_error;
    } else {
        $recipient_result = json_decode($recipient_response, true);
        if (isset($recipient_result['status']) && $recipient_result['status'] === true) {
            $recipient_code = $recipient_result['data']['recipient_code'];
            
            // Save the recipient code in the user's record
            $update_query = "UPDATE virtual_accounts SET account_code = '$recipient_code' WHERE acct_id = '$user_id'";
            if ($conn->query($update_query) === TRUE) {
                $success_message = "Account linked successfully.";
            } else {
                $error_message = "Database update error: " . $conn->error;
            }
        } else {
            $error_message = "Error creating transfer recipient: " . ($recipient_result['message'] ?? 'Unknown error');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Link Bank Account</title>
        <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
        <link rel="stylesheet" href="../css/transaction.css">
    <style>
        body { font-family: 'Poppins', Arial, sans-serif; }
        .container {margin: auto; padding: 10px; margin-top: 10px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .form-group { margin: auto;}
        label { display: block; font-size: 12px; font-weight: 300; }
        input[type="text"]{
            width: 95%;
            padding: 8px;
            border: 1px solid #ffbf00;
            border-radius: 4px;
            background: transparent;
            color: white;
            margin-bottom: 15px;
        }
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ffbf00;
            border-radius: 4px;
            background: transparent;
            color: white;
            margin-bottom: 15px;
        }
        /* input[type="text"], select { width: 100%; padding: 8px; border: 1px solid #ffbf00; border-radius: 4px; background: transparent; color: white;} */
        button { background: transparent; color: #ffbf00; padding: 10px; width: 100%; border: 1px solid #ffbf00; border-radius: 4px; width: 100%; font-size: 16px; margin-top: 10px;}
        .error { background: #e74c3c; color: #fff; padding: 8px; border-radius: 4px; margin-bottom: 15px; }
        .success { background: #2ecc40; color: #fff; padding: 8px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="container">
    <div style="padding-bottom: 15px;">Link Bank Account</div>
    <div style="font-size: 10px; color: #ffbf00; border: .5px solid #ffbf00; padding: 3px; border-radius: 5px; text-align: center; margin: auto; margin-bottom: 40px">&#9432; Note the account you link will be your payout account on every withdrawal, Please link your personal bank account</div>
    <?php if (isset($error_message)): ?>
        <div class="error"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <?php if (isset($success_message)): ?>
        <div class="success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <form action="linkAccount.php" method="POST">
        <div class="form-group">
            <label for="bank_code">Select Bank</label>
            <select id="bank_code" name="bank_code" required>
                <option value="">-- Select Bank --</option>
                <?php 
                if(!empty($banks)):
                    foreach ($banks as $bank): ?>
                        <option value="<?php echo $bank['code']; ?>">
                            <?php echo $bank['name']; ?>
                        </option>
                    <?php endforeach;
                endif; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="account_number">Account Number</label>
            <input type="text" id="account_number" name="account_number" maxlength="10" style="width: 90%;" required>
        </div>
        <div class="form-group">
            <label for="account_name">Account Name</label>
            <input type="text" id="account_name" name="account_name" required>
        </div>
        <div class="form-group">
            <button type="submit">Link Account</button>
        </div>
    </form>
</div>
</body>
</html>