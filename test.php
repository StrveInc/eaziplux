<?php

// Import the Postmark Client Class
require_once('./vendor/autoload.php');
use Postmark\PostmarkClient;

// Initialize Postmark client with your API key
$client = new PostmarkClient("5223c201-3d4f-48ed-9675-7348c1b631e7");

// Define email parameters
$fromEmail = "admin@eaziplux.com.ng"; // Ensure this email is verified in Postmark
$toEmail = "support@eaziplux.com.ng"; // Replace with the recipient's email
$subject = "Hello from Postmark";
$htmlBody = "<strong>Hello</strong>, this is a test email from Postmark!";
$textBody = "Hello, this is a test email from Postmark!";
$tag = "example-email-tag";
$trackOpens = true;
$trackLinks = "None";
$messageStream = "outbound"; // Ensure your server allows sending via this stream

try {
    // Send email using Postmark
    $sendResult = $client->sendEmail(
        $fromEmail,
        $toEmail,
        $subject,
        $htmlBody,
        $textBody,
        $tag,
        $trackOpens,
        NULL, // Reply To
        NULL, // CC
        NULL, // BCC
        NULL, // Headers array
        NULL, // Attachments array
        $trackLinks,
        NULL, // Metadata array
        $messageStream
    );

    echo "Email sent successfully!";
} catch (Exception $e) {
    echo "Error sending email: " . $e->getMessage();
}

?>
