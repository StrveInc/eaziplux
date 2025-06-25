<?php

session_start();

// Check if the 'username' session variable is set
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("Location: ../home/login.php");
    exit;
}

// For testing/demo purposes
// $_SESSION['email'] = 'strveinc@gmail.com';
// $_SESSION['user_id'] = 'TjwH83BKBmSnckZ21uJaG4hwNZ33';
// $_SESSION['username'] = 'victory';

// Handle reload after linking phone (reload only once)
// if (isset($_SESSION['just_linked_phone'])) {
//     unset($_SESSION['just_linked_phone']);
//     // After reload, show account details, don't reload again
// }

include '../config.php'; // Include your database connection details

if ($conn->connect_error) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['link_phone'])) {
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    if (empty($phone)) {
        $link_error = "Phone number is required.";
    } elseif (!preg_match('/^\d{10,15}$/', $phone)) {
        $link_error = "Invalid phone number format.";
    } else {
        $user_email = $_SESSION['email'];
        $user_id = $_SESSION['user_id'];

        // 1. Get or create Paystack customer
        $paystack_secret_key = $_ENV['PK_SECRET'] ?? 'sk_test_xxx';
        $customer_code = null;

        // Create customer on Paystack
        $customer_data = [
            "email" => $user_email,
            "phone" => $phone,
            "first_name" => $_SESSION['username'],
            "last_name" => "Eazi" // You can modify this as needed
        ];
        $ch = curl_init("https://api.paystack.co/customer");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($customer_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $paystack_secret_key",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $response_data = json_decode($response, true);
        if ($http_code === 200 && isset($response_data['data']['customer_code'])) {
            $customer_code = $response_data['data']['customer_code'];

            // 2. Create DVA
            $dva_data = [
                "customer" => $customer_code,
                "preferred_bank" => "wema-bank",
                "phone" => $phone
            ];
            $ch = curl_init("https://api.paystack.co/dedicated_account");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dva_data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer $paystack_secret_key",
                "Content-Type: application/json"
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $dva_response = curl_exec($ch);
            $dva_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $dva_data = json_decode($dva_response, true);
            if ($dva_http_code === 200 && isset($dva_data['data']['account_number'])) {
                // Save to DB
                $acct_number = $dva_data['data']['account_number'];
                $acct_name = $dva_data['data']['account_name'];
                $bank_name = $dva_data['data']['bank']['name'];

                $stmt2 = $conn->prepare("UPDATE users SET phone=? WHERE user_id=?");
                $stmt2->bind_param("ss", $phone, $_SESSION["user_id"]);
                $stmt2->execute();
                $stmt2->close();

                include '../config.php';
                $stmt = $conn->prepare("UPDATE virtual_accounts SET acct_number=?, acct_name=?, bank_name=? WHERE acct_id=?");
                $stmt->bind_param("ssss", $acct_number, $acct_name, $bank_name, $_SESSION["user_id"]);
                if ($stmt->execute()) {
                    $link_success = "Account linked and virtual account created!";
                    $_SESSION['just_linked_phone'] = true;
                    // echo json_encode([
                    //     'status' => 'success',
                    //     'message' => $link_success,
                    //     'account_number' => $acct_number,
                    //     'bank_name' => $bank_name,
                    //     'account_name' => $acct_name
                    // ]);

                    header("Location: ../dashboard/transfer.php");
                    exit;
                } else {
                    $link_error = "Failed to save account: " . $stmt->error;
                }
                $stmt->close();
                $conn->close();
            } else {
                $link_error = "Failed to create virtual account: " . ($dva_data['message'] ?? 'Unknown error');
            }
        } else {
            $link_error = "Failed to create Paystack customer: " . ($response_data['message'] ?? 'Unknown error');
        }
    }
}

// Always return error if set, or a default error if nothing else matched
if (isset($link_error)) {
    echo json_encode([
        'status' => 'error',
        'message' => $link_error
    ]);
    exit;
}

// If accessed without POST or link_phone, return a message
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['link_phone'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method or missing parameters.'
    ]);
    exit;
}
?>