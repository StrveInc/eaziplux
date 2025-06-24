<?php
header('Content-Type: application/json');

if (!isset($_GET['service'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing service']);
    exit;
}

$service = $_GET['service'];
$apiUrl = "https://api.gsubz.com/api/plans?service=" . urlencode($service);

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);

if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch plans']);
    exit;
}

echo $response;