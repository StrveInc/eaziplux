<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;


// Database connection
include '../config.php';
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
// --- END DB CONNECTION ---


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        header("Location: ../home/login.php");
        exit;
    }

    $reference = 'GC-' . strtoupper(bin2hex(random_bytes(5))) . time();
    $card_type = $_POST['network'] ?? '';
    $card_code = trim($_POST['card_code'] ?? '');
    $card_amount = $_POST['number'] ?? null;
    $status = 'processing';
    $currency = $_POST['currency'] ?? 'USD';
    $converted_amount = $_POST['converted_amount'] ?? null;
    $original_amount = $_POST['original_amount'] ?? null;  
    $created_at = date('Y-m-d H:i:s');

    // Convert created_at to Africa/Lagos time for email
    $dt = new DateTime($created_at, new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('Africa/Lagos'));
    $created_at_local = $dt->format('Y-m-d H:i:s');

    // Handle file upload if image is provided
    if (isset($_FILES['card_image']) && $_FILES['card_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/giftcards/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $ext = pathinfo($_FILES['card_image']['name'], PATHINFO_EXTENSION);
        $filename = $reference . '.' . $ext;
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['card_image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        } else {
            die('Failed to upload image.');
        }
    }

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO giftcard_requests 
        (reference, user_id, card_type, card_code, currency, card_amount, converted_amount, balance, image_path, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "sssssddsss",
        $reference,
        $user_id,
        $card_type,
        $card_code,
        $currency,
        $card_amount,
        $converted_amount,
        $original_amount, // balance
        $image_path,
        $status,
    );

    if ($stmt->execute()) {
        $_SESSION['status_type'] = 'processing';
        $_SESSION['status_message'] = 'Your gift card request is being processed. You will be notified once completed.';

        // Format original amount with comma for display
        $original_amount_formatted = number_format((float)$original_amount, 2);

        // --- Send email to vendor using Symfony Mailer ---
        $vendor_email = "strveinc@gmail.com"; // Replace with your vendor's email
        $subject = "New Gift Card Order - $reference";
        $message = "A new gift card order has been placed:\n\n"
            . "Reference: $reference\n"
            . "Naira amount: ₦ $original_amount_formatted\n"
            . "Card Type: $card_type\n"
            . "Card Code: $card_code\n"
            . "Amount: $card_amount $currency\n"
            . "Status: $status\n"
            . (!empty($image_path) ? "Image: " . realpath($image_path) . "\n" : "")
            . "Date: $created_at_local\n\n";

        $update_link = "https://eaziplux.com.ng/giftcard/updateOrder.php?reference=" . urlencode($reference);

        $message .= "To update or review this order, visit: $update_link\n";

        // Add HTML version with a button
        $htmlMessage = "
            <p>A new gift card order has been placed:</p>
            <ul>
                <li><strong>Reference:</strong> $reference</li>
                <li><strong>Naira amount:</strong> $original_amount_formatted</li>
                <li><strong>Card Type:</strong> $card_type</li>
                <li><strong>Card Code:</strong> $card_code</li>
                <li><strong>Amount:</strong> $card_amount $currency</li>
                <li><strong>Status:</strong> $status</li>"
                . (!empty($image_path) ? "<li><strong>Image:</strong> " . realpath($image_path) . "</li>" : "") . "
                <li><strong>Date:</strong> $created_at_local</li>
            </ul>
            <p>
                <a href=\"$update_link\" 
                   style=\"display:inline-block;padding:10px 20px;background:#ffbf00;color:#222;text-decoration:none;border-radius:5px;font-weight:bold;\">
                    Update/Review Order
                </a>
            </p>
        ";

        // Paystack inline payment button (using reference and original_amount)
        $paystack_public_key = $_ENV['PK_PUBLIC']; // Replace with your Paystack public key
        $paystack_secret_key = $_ENV['PK_SECRET']; // Your Paystack secret key
        $paystack_amount_kobo = intval(floatval($original_amount) * 100);

        // Initialize Paystack transaction
        $fields = [
            'email' => $vendor_email, // or the payer's email
            'amount' => intval(floatval($original_amount) * 100), // amount in kobo
            'reference' => $reference, // your order reference
        ];

        $ch = curl_init('https://api.paystack.co/transaction/initialize');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $paystack_secret_key,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        if (isset($result['status']) && $result['status'] && isset($result['data']['authorization_url'])) {
            $paystack_payment_link = $result['data']['authorization_url'];

            $htmlMessage .= "
                <p>
                    <a href=\"$paystack_payment_link\" 
                       style=\"display:inline-block;padding:10px 20px;background:#0aa83f;color:#fff;text-decoration:none;border-radius:5px;font-weight:bold;\">
                        Pay with Paystack
                    </a>
                </p>
            ";
        } else {
            // Handle error
            $paystack_payment_link = "https://paystack.shop/pay/bs305tl-v1";
        }

        // Use SMTP DSN for Gmail (replace with your credentials)
        $smtpDsn = $_ENV['MAILER_DSN'];

        $transport = Transport::fromDsn($smtpDsn);
        $mailer = new Mailer($transport);

        $email = (new Email())
            ->from('no-reply@eaziplux.com.ng')
            ->to($vendor_email)
            ->subject($subject)
            ->text($message)
            ->html($htmlMessage);

        // Attach image if available
        if (!empty($image_path) && file_exists($image_path)) {
            $email->attachFromPath($image_path);
        }

        try {
            $mailer->send($email);
        } catch (\Exception $e) {
            file_put_contents(
                __DIR__ . '/email_error.log',
                "[" . date('Y-m-d H:i:s') . "] Symfony Mailer Error: " . $e->getMessage() . "\n",
                FILE_APPEND
            );
            error_log("Symfony Mailer Error: " . $e->getMessage());
        }

        header("Location: ../success.php");
        exit;
    } else {
        die('Database error: ' . $stmt->error);
    }
}
?>