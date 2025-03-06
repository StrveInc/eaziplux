<?php
session_start();


// Function to make an API request
function makeApiRequest($url)
{
    $curl = curl_init();

    curl_setopt_array(
        $curl,
        array(
            CURLOPT_URL => $url,
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

    if ($response === false) {
        // Handle API request failure
        die("Error making API request: " . curl_error($curl));
    }

    curl_close($curl);

    // Decode the JSON response
    $responseArray = json_decode($response, true);

    if ($responseArray === null) {
        // Handle JSON decoding error
                echo"<script>";
                echo"alert('MTN Service is currently down')";
                echo"</script>";
    }

    return $responseArray;
}


$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$database = "eaziplux";

$conn = new mysqli($servername, $dbusername, $dbpassword, $database);

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
        $balance_query = "SELECT balance FROM virtual_accounts WHERE acct_id = '$user_id'";
        $balance_result = $conn->query($balance_query);

        if ($balance_result->num_rows == 1) {
            $balance_row = $balance_result->fetch_assoc();
            $account_balance = $balance_row["balance"];

            $_SESSION["USER_ID"] = $user_id;
            $_SESSION['ACCT'] = $account_balance;
        } else {
            $account_balance = 0; // Default balance if not found
        }
    } else {
        // Handle the case where the user's ID is not found
    }
}
// } else {
//     header("Location: ../home/login.php"); // Redirect to the login page
//     exit;
// }

// Fetch data plans from the API
$responseArray = makeApiRequest('https://gsubz.com/api/plans?service=mtn_sme');
$number = isset($_POST['number']) ? $_POST['number'] : null;
$phoneNumber = str_replace(" ", "", $number);
$user_id = $_SESSION["USER_ID"];
// Check if the transaction status is TRANSACTION_FAILED
function handleTransactionFailure($user_id, $responseArray, $phoneNumber, $adjustedPrice, $conn, $item)
{
    $failureReason = $responseArray["description"];
    $failureStatus = $responseArray["status"];


    if ($failureReason === "INSUFFICIENT_BALANCE") {
        // Handle insufficient balance error
        $transaction_description = "Error in Transaction";
        $transaction_status = "Failed";
        $transaction_type = "Data";
        $item = $item;
        $receiver = $phoneNumber;
        $transaction_time = date("F j, Y \a\\t g:i A");
        $transaction_id = "EP".time(); // Combine user ID and timestamp

        // Insert failed transaction details into the transaction_history table
        $log_transaction_query = "INSERT INTO transaction_history (user_id, transaction_id, amount, description, status, item, transaction_type, receiver, transaction_time) 
        VALUES ('$user_id', '$transaction_id', $adjustedPrice, '$transaction_description', '$transaction_status', '$item', '$transaction_type', '$receiver', '$transaction_time')";
        if ($conn->query($log_transaction_query) !== TRUE) {
            $_SESSION['message'] = "Error logging transaction: " . $conn->error;
            header("Location: ../failed.php");
            exit;
        }
        $_SESSION['message'] = "Service Currently Down: Try again in 5 minutes or Contact US.";
    } elseif ($failureStatus === "failed") {
        $transaction_description = "Transaction Reversed";
        $transaction_status = "Reversed";
        $transaction_type = "Data";
        $item = $item;
        $receiver = $phoneNumber;
        $transaction_time = date("F j, Y \a\\t g:i A");
        $transaction_id = "EP".time(); // Combine user ID and timestamp

        // Insert failed transaction details into the transaction_history table
        $log_transaction_query = "INSERT INTO transaction_history (user_id, transaction_id, amount, description, status, item, transaction_type, receiver, transaction_time) 
        VALUES ('$user_id', '$transaction_id', $adjustedPrice, '$transaction_description', '$transaction_status', '$item', '$transaction_type', '$receiver', '$transaction_time')";
        if ($conn->query($log_transaction_query) !== TRUE) {
            $_SESSION['message'] = "Error logging transaction: " . $conn->error;
            header("Location: ../failed.php");
            exit;
        }
        $_SESSION["message"] = "Transaction failed: Money Reversed.";
    } elseif ($failureStatus === "Reversed") {
        $transaction_description = "Transaction Reversed";
        $transaction_status = "Reversed";
        $transaction_type = "Data";
        $item = $item;
        $receiver = $phoneNumber;
        $transaction_time = date("F j, Y \a\\t g:i A");
        $transaction_id = "EP".time(); // Combine user ID and timestamp

        // Insert failed transaction details into the transaction_history table
        $log_transaction_query = "INSERT INTO transaction_history (user_id, transaction_id, amount, description, status, item, transaction_type, receiver, transaction_time) 
        VALUES ('$user_id', '$transaction_id', $adjustedPrice, '$transaction_description', '$transaction_status', '$item', '$transaction_type', '$receiver', '$transaction_time')";
        if ($conn->query($log_transaction_query) !== TRUE) {
            $_SESSION['message'] = "Error logging transaction: " . $conn->error;
            header("Location: ../failed.php");
            exit;
        }
        $_SESSION["message"] = "Transaction Failed: Money Reversed";
    } else {
        // Handle other transaction failure reasons as needed
        $_SESSION['message'] = "Transaction failed: $failureReason";
    }

    header("Location: ../failed.php");
    exit;
}

// Check if the form is submitted
if (isset($_POST['submit'])) {
    $selectedPlanValue = isset($_POST['plans']) ? $_POST['plans'] : '';
    $selectedPlanPrice = 0; // Initialize as a numeric value
    $item = isset($_POST['item']) ? $_POST['item'] : 'MTN';

    $serviceID = '';
    switch ($item) {
        case 'MTN':
            $serviceID = 'mtn_sme';
            break;
        case 'Airtel':
            $serviceID = 'airtel_cg';
            break;
        case 'Glo':
            $serviceID = 'glo_data';
            break;
        case '9MOBILE':
            $serviceID = 'etisalat_data';
            break;
        default:
            $serviceID = ''; // Default to MTN if item is not recognized
            break;
    }

    // Find the selected plan's price
    foreach ($responseArray['plans'] as $plan) {
        if ($plan['value'] == $selectedPlanValue) {
            $selectedPlanPrice = (float)$plan['price']; // Cast to float
            break;
        }
    }

    // Adjust the selected plan price
    $adjustedPrice = $selectedPlanPrice + $selectedPlanPrice * 0.05;
    $adjustedPrice = floor($adjustedPrice);

echo "<script>";
echo  "console.log($adjustedPrice)";
echo "</script>";

echo "<script>";
echo  "console.log($account_balance)";
echo "</script>";
    // Store the selected amount in the session
    $_SESSION['amount'] = $adjustedPrice;

    // Check if the user's virtual account balance is sufficient
    if ($account_balance < $adjustedPrice) {
        $transaction_description = "Insufficient Funds";
        $transaction_status = "Failed";
        $transaction_type = "Data";
        $item = $item;
        $receiver = $phoneNumber;
        $transaction_time = date("F j, Y \a\\t g:i A");
        $transaction_id = "EP".time(); // Combine user ID and timestamp

        // Insert failed transaction details into the transaction_history table
        $log_transaction_query = "INSERT INTO transaction_history (user_id, transaction_id, amount, description, status, item, transaction_type, receiver, transaction_time) 
        VALUES ('$user_id', '$transaction_id', $adjustedPrice, '$transaction_description', '$transaction_status', '$item', '$transaction_type', '$receiver', '$transaction_time')";
        if ($conn->query($log_transaction_query) !== TRUE) {
            $_SESSION['message'] = "Error logging transaction: " . $conn->error;
            header("Location: ../failed.php");
            exit;
        }

        $_SESSION['message'] = "Transaction failed: Fund your eaziplux wallet.";
       header("Location: ../failed.php");
    } else {
        // If the balance is sufficient, proceed with the purchase
        //purchaseData($adjustedPrice, $phoneNumber, $user_id, $selectedPlanValue, $conn, $serviceID, $item);
    }
}

// Function to handle the purchase logic
function purchaseData($adjustedPrice, $phoneNumber, $user_id, $selectedPlanValue, $conn, $serviceID, $item)
{
    // Use the captured data to make the purchase API request
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
                'api' => 'ap_3f856a5b46bb740150d03c990ce2f5d7',
                'amount' => $adjustedPrice,
                'phone' => $phoneNumber,
                'requestID' => "EP".time().uniqid()
            ),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ap_3f856a5b46bb740150d03c990ce2f5d7'
            ),
        )
    );

    $response = curl_exec($curl);

    curl_close($curl);

    // Decode the JSON response
    $responseArray = json_decode($response, true);

    // Check if the transaction status is TRANSACTION_FAILED
    if ($responseArray["status"] === "TRANSACTION_FAILED") {
        handleTransactionFailure($user_id, $responseArray, $phoneNumber, $adjustedPrice, $conn, $item);
    } elseif ($responseArray["status"] === "failed") {

        $transaction_description = "Transaction Reversed";
        $transaction_status = "Reversed";
        $transaction_type = "Data";
        $item = $item;
        $receiver = $phoneNumber;
        $transaction_time = date("F j, Y \a\\t g:i A");
        $transaction_id = "EP".time(); // Combine user ID and timestamp

        // Insert failed transaction details into the transaction_history table
        $log_transaction_query = "INSERT INTO transaction_history (user_id, transaction_id, amount, description, status, item, transaction_type, receiver, transaction_time) 
        VALUES ('$user_id', '$transaction_id', $adjustedPrice, '$transaction_description', '$transaction_status', '$item', '$transaction_type', '$receiver', '$transaction_time')";
        if ($conn->query($log_transaction_query) !== TRUE) {
            $_SESSION['message'] = "Error logging transaction: " . $conn->error;
            header("Location: ../failed.php");
            exit;
        }

        $_SESSION["message"] = "Transaction failed: Check Number or Try again later.";
        header("Location: ../failed.php");
    } elseif ($responseArray["status"] === "Reversed") {

        $transaction_description = "Transaction Reversed";
        $transaction_status = "Reversed";
        $transaction_type = "Data";
        $item = $item;
        $receiver = $phoneNumber;
        $transaction_time = date("F j, Y \a\\t g:i A");
        $transaction_id = "EP".time(); // Combine user ID and timestamp

        // Insert failed transaction details into the transaction_history table
        $log_transaction_query = "INSERT INTO transaction_history (user_id, transaction_id, amount, description, status, item, transaction_type, receiver, transaction_time) 
        VALUES ('$user_id', '$transaction_id', $adjustedPrice, '$transaction_description', '$transaction_status', '$item', '$transaction_type', '$receiver', '$transaction_time')";
        if ($conn->query($log_transaction_query) !== TRUE) {
            $_SESSION['message'] = "Error logging transaction: " . $conn->error;
            header("Location: ../failed.php");
            exit;
        }

        $_SESSION["message"] = "Transaction failed: Check Number or Try again later.";
        header("Location: ../failed.php");
    }else {
        // Transaction was successful, update the balance in the database
        $new_balance = $_SESSION['ACCT'] - $adjustedPrice;
        $update_balance_query = "UPDATE virtual_accounts SET balance = $new_balance WHERE acct_id = {$_SESSION['USER_ID']}";
        $conn->query($update_balance_query);

        $transaction_description = "Transaction Successful";
        $transaction_status = "Successful";
        $transaction_type = "Data";
        $item = $item;
        $receiver = $phoneNumber;
        $transaction_time = date("F j, Y \a\\t g:i A");
        $transaction_id = "EP".time(); // Combine user ID and timestamp

        // Insert failed transaction details into the transaction_history table
        $log_transaction_query = "INSERT INTO transaction_history (user_id, transaction_id, amount, description, status, item, transaction_type, receiver, transaction_time) 
        VALUES ('$user_id', '$transaction_id', $adjustedPrice, '$transaction_description', '$transaction_status', '$item', '$transaction_type', '$receiver', '$transaction_time')";
        if ($conn->query($log_transaction_query) !== TRUE) {
            $_SESSION['message'] = "Error logging transaction: " . $conn->error;
            header("Location: ../failed.php");
            exit;
        }
        // Echo a success message or perform any other actions as needed
        $_SESSION['message'] = 'Successfully purchased ₦'.$adjustedPrice." worth of ".$item." data";
        header("Location: ../success.php");
        exit;
    }
}

// HTML structure remains unchanged
?>