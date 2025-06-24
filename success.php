<?php
session_start();
$type = isset($_SESSION['status_type']) ? $_SESSION['status_type'] : 'success'; // 'success', 'failure', or 'processing'
$message = isset($_SESSION['status_message']) ? $_SESSION['status_message'] : 'Transaction completed.';
$processing = ($type === 'processing');
unset($_SESSION['status_type'], $_SESSION['status_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($type); ?></title>
    <style>
        body { font-family: Poppins, Arial, sans-serif; background: black; text-align: center; }
        .box { margin: 200px auto; padding: 40px 30px; background: #000; border-radius: 10px; box-shadow: 0 4px 24px rgba(0,0,0,0.07); max-width: 400px; }
        .icon { font-size: 46px; margin-bottom: 50px; }
        .icon.success { color: #2ecc40; }
        .icon.failure { color: #e74c3c; }
        .icon.processing { color: #ffbf00; animation: spin 1.2s linear infinite; }
        .msg { font-size: 16px; color: #ccc; }
        a { display: inline-block; margin-top: 30px; color: #2ecc40; text-decoration: none; font-weight: 500; }
        .failure-link { color: #e74c3c; }
        @keyframes spin {
            100% { transform: rotate(360deg);}
        }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon <?php echo $type; ?>">
            <?php
            if ($type === 'success') {
                echo '&#10004;';
            } elseif ($type === 'failure') {
                echo '&#10008;';
            } elseif ($type === 'processing') {
                // Spinner icon (Unicode or SVG)
                echo '<svg width="48" height="48" viewBox="0 0 50 50" style="vertical-align:middle;"><circle cx="25" cy="25" r="20" fill="none" stroke="#ffbf00" stroke-width="5" stroke-linecap="round" stroke-dasharray="31.4 31.4" transform="rotate(-90 25 25)"><animateTransform attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="1s" repeatCount="indefinite"/></circle></svg>';
            }
            ?>
        </div>
        <div class="msg">
            <?php
            if ($processing) {
                echo "Your giftcard transaction is processing. You will be notified once completed.";
            } else {
                echo htmlspecialchars($message);
            }
            ?>
        </div>
        <a href="home/dashboard.php" style="font-size: 18px;" class="<?php echo $type === 'failure' ? 'failure-link' : ''; ?>">Back to Dashboard</a>
    </div>
</body>
</html>