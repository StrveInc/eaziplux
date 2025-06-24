<?php
session_start();
require '../vendor/autoload.php';

include '../config.php'; // Include your database connection details

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$reference = $_GET['reference'] ?? '';
$success = false;
$error = '';
$giftcard = null;

// Fetch giftcard request details
if ($reference) {
    $stmt = $conn->prepare("SELECT * FROM giftcard_requests WHERE reference = ?");
    $stmt->bind_param("s", $reference);
    $stmt->execute();
    $result = $stmt->get_result();
    $giftcard = $result->fetch_assoc();
    $stmt->close();
}

$deny_update = false;
if ($giftcard && strtolower($giftcard['status']) !== 'processing') {
    $deny_update = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $giftcard && !$deny_update) {
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? $giftcard['status'];

    $stmt = $conn->prepare("UPDATE giftcard_requests SET description = ?, status = ? WHERE reference = ?");
    $stmt->bind_param("sss", $description, $status, $reference);
    if ($stmt->execute()) {
        $success = true;
        // Refresh giftcard data
        $giftcard['description'] = $description;
        $giftcard['status'] = $status;
        if (strtolower($status) !== 'processing') {
            $deny_update = true;
        }
    } else {
        $error = "Failed to update review.";
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Giftcard Review</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; }
        .review-container { max-width: 500px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px #0001; }
        h2 { text-align: center; }
        label { display: block; margin-top: 18px; }
        textarea { width: 100%; min-height: 80px; margin-top: 8px; border-radius: 4px; border: 1px solid #ccc; padding: 8px; }
        select, button { width: 100%; padding: 10px; margin-top: 8px; border-radius: 4px; border: 1px solid #ccc; }
        .success { color: green; text-align: center; }
        .error { color: red; text-align: center; }
        .readonly { background: #f0f0f0; }
        .overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.7);
            color: #fff;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            flex-direction: column;
        }
    </style>
</head>
<body>
    <?php if ($deny_update): ?>
        <div class="overlay">
            <div>
                <strong>Order has been completed or is no longer processing.</strong>
            </div>
        </div>
    <?php endif; ?>
    <div class="review-container">
        <h2>Giftcard Order Review</h2>
        <?php if (!$giftcard): ?>
            <div class="error">Invalid or missing reference.</div>
        <?php else: ?>
            <?php if ($success): ?>
                <div class="success">Review updated successfully!</div>
            <?php elseif ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" <?= $deny_update ? 'style="pointer-events:none;opacity:0.5;"' : '' ?>>
                <label>Reference:</label>
                <input type="text" class="readonly" value="<?= htmlspecialchars($giftcard['reference']) ?>" readonly>
                <label>Status:</label>
                <select name="status" required <?= $deny_update ? 'disabled' : '' ?>>
                    <?php
                    $statuses = ['processing', 'completed', 'failed', 'pending'];
                    foreach ($statuses as $s):
                    ?>
                        <option value="<?= $s ?>" <?= $giftcard['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Description / Review:</label>
                <textarea name="description" placeholder="Write your review here..." <?= $deny_update ? 'readonly' : '' ?>><?= htmlspecialchars($giftcard['description'] ?? '') ?></textarea>
                <button type="submit" <?= $deny_update ? 'disabled' : '' ?>>Update Review</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>