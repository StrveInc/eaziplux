<?php

// Start or resume a session
session_start();

// Set data for the first API call
$_SESSION['customer_data'] = array(
    "email" => $_SESSION["email"],
    "first_name" => "eazi-",
    "last_name" => $_SESSION['username'],
    "phone" => $_SESSION["phone"]
);

// API endpoint URL
$urlCustomer = 'https://api.paystack.co/customer';

// API Key for Authorization
$authorization = 'Authorization: Bearer sk_live_2886d2746575c3f54e3c7b7e02eab1cd7da06313';

// Content type
$contentType = 'Content-Type: application/json';

// Data to be sent in the request
$dataCustomer = json_encode($_SESSION['customer_data']);

// Initialize cURL session
$chCustomer = curl_init();

// Set cURL options for a POST request
curl_setopt($chCustomer, CURLOPT_URL, $urlCustomer);
curl_setopt($chCustomer, CURLOPT_POST, 1);
curl_setopt($chCustomer, CURLOPT_POSTFIELDS, $dataCustomer);
curl_setopt($chCustomer, CURLOPT_RETURNTRANSFER, true);
curl_setopt(
    $chCustomer,
    CURLOPT_HTTPHEADER,
    array(
        $authorization,
        $contentType,
    )
);

// Execute cURL session
$responseCustomer = curl_exec($chCustomer);

// Check for cURL errors
if (curl_errno($chCustomer)) {
    echo 'Curl error: ' . curl_error($chCustomer);
} else {
    // Decode and print the response
    $responseDataCustomer = json_decode($responseCustomer, true);
    var_dump($responseDataCustomer);

    $customerCode = $responseDataCustomer['data']['customer_code'];

}

// Close cURL session
curl_close($chCustomer);

echo "<br/><br/><br/>";

echo $customerCode;



$url = "https://api.paystack.co/dedicated_account";

$fields = [
    "customer" => $customerCode,
    "preferred_bank" => "wema-bank",
    "email" => $_SESSION["email"],
    "first_name" => "Eazi",
    "middle_name" => "",
    "last_name" => $_SESSION["username"],
    "phone" => $_SESSION["phone"],
];

$fields_string = http_build_query($fields);

// Initialize cURL session
$ch = curl_init();

// Set cURL options for a POST request
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
curl_setopt(
    $ch,
    CURLOPT_HTTPHEADER,
    array(
        "Authorization: Bearer sk_live_2886d2746575c3f54e3c7b7e02eab1cd7da06313",
        "Cache-Control: no-cache",
    )
);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute cURL session
$result = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
} else {
    // Decode the result
    $decode = json_decode($result);


    print_r($decode);
    echo "<br><br><br>";

    // Extract required information
    $acctnumber = $decode->data->account_number;
    $acctname = $decode->data->account_name;
    $bankname = $decode->data->bank->name;

    // Close cURL session
    curl_close($ch);

    // Save to database
    // Replace the following with your database connection and query
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "root";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("UPDATE users SET acct_number=?, acct_name=?, bank_name=? WHERE email=?");
    $stmt->bind_param("ssss", $acctnumber, $acctname, $bankname, $_SESSION["email"]);
    $stmt->execute();

    $stmt->close();
    $conn->close();
}


// End the session
?>