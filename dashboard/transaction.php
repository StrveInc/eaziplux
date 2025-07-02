<?php
session_start();

include '../config.php';



if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];


    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get product from GET parameter, default to all
    $product = isset($_GET['product']) ? $conn->real_escape_string($_GET['product']) : '';

    // Get user_id
    $user_id_query = "SELECT user_id FROM users WHERE username = '$username'";
    $result = $conn->query($user_id_query);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $user_id = $row["user_id"];

        // Build WHERE clause for product filter
        $product_filter = "";
        if ($product !== '') {
            $product_filter = "AND transaction_type = '$product'";
        }

        // If product is giftcard, fetch from giftcard_requests
        if ($product === 'giftcard') {
            // Fetch ALL giftcard transactions for the user, regardless of status
            $giftcard_query = "SELECT * FROM giftcard_requests WHERE user_id = '$user_id' ORDER BY created_at DESC";
            $giftcard_result = $conn->query($giftcard_query);

            if ($giftcard_result && $giftcard_result->num_rows > 0) {
                $transaction_html = '';
                while ($row = $giftcard_result->fetch_assoc()) {
                    $icon = '<img src="../css/icon/giftcard.svg" alt="Giftcard" class="txn-icon">';
                    $status_class = strtolower($row["status"]) === 'failed' ? 'failed' : (strtolower($row["status"]) === 'completed' ? 'success' : (strtolower($row["status"]) === 'processing' ? 'pending' : 'pending'));
                    $amount_sign = '+';
                    $amount_color = '#2ecc40';

                    $data_details = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');

                    $transaction_html .= '<div class="txn-card" data-details="' . $data_details . '">';
                    $transaction_html .= '<div class="txn-icon-wrap">' . $icon . '</div>';
                    $transaction_html .= '<div class="txn-details">';
                    $transaction_html .= '<div class="top-row">';
                    $transaction_html .= '<div class="txn-title">'.htmlspecialchars($row["card_type"]).'</div>';
                    $transaction_html .= '<div class="txn-amount" style="color:'.$amount_color.';">'.$amount_sign.'₦'.number_format($row["converted_amount"],2).'</div>';
                    $transaction_html .= '</div>';
                    $transaction_html .= '<div class="txn-meta">';
                    $transaction_html .= '<span class="txn-date">'.date('d M, Y h:i A', strtotime($row["created_at"])).'</span>';
                    $transaction_html .= '<span class="txn-status '.$status_class.'">'.ucfirst($row["status"]).'</span>';
                    $transaction_html .= '</div>';
                    $transaction_html .= '</div>';
                    $transaction_html .= '</div>';
                }
            } else {
                $transaction_html = "<div class='no-txn'>No giftcard transactions found.</div>";
            }
            // Skip the rest of the normal transaction_history logic for giftcard
        } else {
            // Fetch distinct months for the filtered transactions
            $months_query = "SELECT DISTINCT MONTHNAME(transaction_time) AS transaction_month 
                             FROM transaction_history 
                             WHERE user_id = '$user_id' $product_filter 
                             ORDER BY transaction_month DESC";
            $months_result = $conn->query($months_query);

            // Fetch filtered transaction history
            $transaction_query = "SELECT * FROM transaction_history 
                                  WHERE user_id = '$user_id' $product_filter 
                                  ORDER BY transaction_time DESC";
            $transaction_result = $conn->query($transaction_query);

            if ($months_result->num_rows > 0 && $transaction_result->num_rows > 0) {
                $month_options = '';
                while ($month_row = $months_result->fetch_assoc()) {
                    $month_options .= '<option value="' . $month_row["transaction_month"] . '">' . $month_row["transaction_month"] . '</option>';
                }

                // SVG icon map
                $icon_map = [
                    'airtime' => '<img src="../css/icon/signal.svg" alt="Airtime" class="txn-icon">',
                    'credit' => '<img src="../css/icon/fund.svg" style="width: 30px; height: 30px;" alt="Credit" class="txn-icon">',
                    'data' => '<img src="../css/icon/data.svg" alt="Data" class="txn-icon">',
                    'giftcard' => '<img src="../css/icon/giftcard.svg" alt="Giftcard" class="txn-icon">',
                    'electricity' => '<img src="../css/icon/electric.svg" alt="Electricity" class="txn-icon">',
                    'cable' => '<img src="../css/icon/satellite.svg" alt="Cable" class="txn-icon">',
                    'betting' => '<img src="../css/icon/bet.svg" alt="Betting" class="txn-icon">',
                    'withdrawal' => '<img src="../css/icon/withdrawal.svg" style="width: 25px; height: 25px;" alt="Withdraw" class="txn-icon">',
                    // Add more as needed
                ];

                $transaction_html = '';
                while ($row = $transaction_result->fetch_assoc()) {
                    $type = strtolower($row["transaction_type"]);
                    $icon = isset($icon_map[$type]) ? $icon_map[$type] : '<img src="../css/svg/other.svg" alt="Other" class="txn-icon">';
                    $status_class = strtolower($row["status"]) === 'failed' ? 'failed' : 'success';
                    $amount_sign = (strtolower($row["transaction_type"]) === 'credit' || strtolower($row["transaction_type"]) === 'giftcard') ? '+' : '-';
                    $amount_color = ($row["direction"] ?? 'debit') === 'credit' ? '#2ecc40' : '#e74c3c';

                    // Add data-details attribute for receipt
                    $data_details = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');

                    $transaction_html .= '<div class="txn-card" data-details="' . $data_details . '">';
                    $transaction_html .= '<div class="txn-icon-wrap">' . $icon . '</div>';
                    $transaction_html .= '<div class="txn-details">';
                    $transaction_html .= '<div class="top-row">';
                    $transaction_html .= '<div class="txn-title">'.ucfirst($type).'</div>';
                    $transaction_html .= '<div class="txn-amount" style="color:'.$amount_color.';">'.$amount_sign.'₦'.number_format($row["amount"],2).'</div>';
                    $transaction_html .= '</div>';
                    $transaction_html .= '<div class="txn-meta">';
                    $transaction_html .= '<span class="txn-date">'.date('d M, Y h:i A', strtotime($row["transaction_time"])).'</span>';
                    $transaction_html .= '<span class="txn-status '.$status_class.'">'.ucfirst($row["status"]).'</span>';
                    $transaction_html .= '</div>';
                    $transaction_html .= '</div>';
                    $transaction_html .= '</div>';
                }
            } else {
                $month_options = '<option value="">No transactions found.</option>';
                $transaction_html = "<div class='no-txn'>No transactions found.</div>";
            }
        }
    } else {
        $month_options = '<option value="">User not found.</option>';
        $transaction_html = "<div class='no-txn'>User not found.</div>";
    }
} else {
    header("Location: ../home/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../css/transaction.css">

    <meta charset="UTF-8">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Audiowide&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" size="662x662" href="../css/imgs/eazipluxpure.png">
    <script src="https://kit.fontawesome.com/49c5823e25.js" crossorigin="anonymous"></script>
    <title>Transaction History</title>
    <style>
        body {
            background: black;
            font-family: 'Poppins', Arial, sans-serif;
        }
        
        .back {
            display: inline-block;
            margin-left: 10px;
        }

        header {
            color: #fff;
            font-size: 16px;
            padding-block: 10px;
            text-align: center;
            position: fixed;
            top: -5px;
            /* border-bottom: .4px solid #ccc; */
            width: 100%;
            left: -1px;
            background: rgb(0, 0, 0);
        }

        .head {
            display: inline-block;
            font-weight: 600;
            font-size: 20px;
            margin-left: 20px;
            color: #222;
        }
        .date {
            float: right;
            margin-right: 20px;
            margin-top: -35px;
        }
        #monthSelector {
            padding: 6px 14px;
            border-radius: 6px;
            border: 1px solid #eee;
            font-size: 15px;
            background: #f7f8fa;
        }
        .container {
            max-width: 550px;
            margin: 0 auto;
            padding: 0 10px;
        }
        .txn-card {
            display: flex;
            align-items: center;
            background: rgba(0,0,0,0.04);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            margin-bottom: 9px;
            padding: 16px 18px;
            transition: box-shadow 0.2s;
        }
        .txn-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }
        .txn-icon-wrap {
            width: 38px;
            height: 38px;
            background:rgb(39, 39, 39);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
        }
        .txn-icon {
            width: 20px;
            height: 20px;
            display: block;
        }
        .txn-details {
            flex: 1;
        }
        .txn-title {
            font-weight: 600;
            font-size: 16px;
            color: #ccc;
        }
        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .txn-meta {
            font-size: 12px;
            color: #888;
            margin-top: 2px;
            border: 0px solid black;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .txn-meta .txn-date {
            margin-right: 10px;
        }

        .txn-meta .txn-status {
            font-size: 10px;
            /* border: 1px solid #eee; */
            padding-inline: 2px;
            border-radius: 4px;
            text-transform: capitalize;
        }

        .txn-status.success {
            color: #2ecc40;
            font-weight: 500;
            background:rgba(46, 204, 64, 0.13);
        }
        .txn-status.failed {
            color: #e74c3c;
            font-weight: 500;
            background:rgba(231, 77, 60, 0.13);
        }
        .txn-amount {
            font-weight: 600;
            font-size: 17px;
            min-width: 100px;
            text-align: right;
        }
        .no-txn {
            text-align: center;
            color: #aaa;
            margin: 40px 0;
            font-size: 18px;
        }
        #receiptContent #receiptDetails div {
            display: flex;
            justify-content: space-between;
            padding: 7px 0;
            border-bottom: 1px dashed #eee;
            font-size: 15px;
        }
        #receiptContent #receiptDetails div:last-child {
            border-bottom: none;
        }
        #receiptContent .icon {
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @media (max-width: 600px) {
            .container { max-width: 100%; }
            .txn-card { padding: 12px 8px; }
            .txn-title { font-size: 15px; }
            .txn-amount { font-size: 15px; }
        }
    </style>
</head>
<body>
    <header>
            Transactions
    </header>
    <main style="margin-top: 45px;">
        <div class="container" id="transactionContainer">
            <?php echo $transaction_html; ?>
        </div>
    </main>
    <div id="receiptOverlay" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.7); z-index:99999; justify-content:center; align-items:center;">
        <div id="receiptContent" style="border: 0px solid white; background:black; max-width:370px; width:95%; padding:0 0 24px 0; box-shadow:0 8px 32px rgba(0,0,0,0.18); text-align:center; position:relative; font-family:'Poppins',Arial,sans-serif;">
            <div style="background: black; border-radius:18px 18px 0 0; padding-top: 10px;">
                <div style="border: 0px solid white; display: flex; align-items: center; justify-content: space-between;">
                    <div style="width: 20%; display: flex; align-items: center; font-size: 17px; font-family: 'Audiowide', sans-serif; color: #ffbf00; padding: 5px;">   
                      <img src="../css/imgs/eazipluxpure.png" alt="Eaziplux" style="width:25px; padding-right: 5px;">
                        EAZIPLUX
                    </div>
                    <div style="width: 60%; text-align: right; padding: 5px; font-size: 14px; border: 0px solid white;">
                        Transaction Receipt
                    </div>
                
                <!-- <div style="font-size:18px; font-weight:700; color:#fff; letter-spacing:1px;">Transaction receipt</div> -->
            </div>
            <div style="padding-top: 10px;">
                <div class="icon" style="margin:0 auto 2px auto; font-size:44px; width:52px; height:52px; display:flex; align-items:center; justify-content:center;"></div>
                <div id="receiptStatus" style="font-size:10px; color:grey; margin-bottom:10px;"></div>
                <div id="receiptAmount" style="font-size: 25px; font-weight:700; color:white; margin-bottom:8px;"></div>
                <div id="receiptDetails" style="text-align:left; font-size:15px; color: whire; margin-bottom:18px;">
                    <!-- Filled by JS -->
                </div>
                <div style="border-top:1px dashed #ffbf00; margin:18px 0 5px 0;"></div>
                <div style="font-size: 11px; color: grey; text-align: center; padding-block: 10px">For any complaint reach us at support@eaziplux.com.ng</div>
            </div>
        </div>
            <div style="display:flex; justify-content:center; gap:10px;">
                    <button id="downloadPDF" style="background:#0aa83f; color:#fff; border:none; border-radius:6px; padding:8px 18px; font-size:15px; cursor:pointer;">Share PDF</button>
                    <button id="downloadIMG" style="background:#ffbf00; color:#222; border:none; border-radius:6px; padding:8px 18px; font-size:15px; cursor:pointer;">Share Image</button>
                    <button id="closeReceipt" style="background:#eee; color:#222; border:none; border-radius:6px; padding:8px 18px; font-size:15px; cursor:pointer;">Close</button>
            </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Attach click event to each transaction card
        document.querySelectorAll('.txn-card').forEach(function(card) {
            card.style.cursor = "pointer";
            card.addEventListener('click', function() {
                const details = JSON.parse(card.getAttribute('data-details') || '{}');
                let html = '';
                let amount = '';
                let statusText = '';
                let cardImageHtml = '';
                let cardCodeHtml = '';

                // Card image logic
                if (details.image_path && details.image_path !== "null" && details.image_path !== "") {
                    cardImageHtml = `<div><span style="color:#888; font-size: 14px;">Card Image</span>
                        <a href="#" style="font-weight:500; font-size: 13px; color:#ffbf00;" onclick="event.preventDefault(); showFullImage('${details.image_path}');">Click here to view image</a></div>`;
                }

                // Card code logic (only show if image is not set)
                if ((!details.image_path || details.image_path === "null" || details.image_path === "") && details.card_code) {
                    cardCodeHtml = `<div><span style="color:#888; font-size: 14px;">Card Code</span>
                        <span style="font-weight:500; font-size: 13px;">${details.card_code}</span></div>`;
                }

                // Determine if this is a giftcard and if status is 'success'
                const isGiftcard = (details.card_type !== undefined);
                const isGiftcardSuccess = isGiftcard && (String(details.status).toLowerCase() === 'success' || String(details.status).toLowerCase() === 'completed');

                for (const key in details) {
                    if (
                        details.hasOwnProperty(key) &&
                        key.toLowerCase() !== 'user_id' &&
                        key.toLowerCase() !== 'image_path' &&
                        key.toLowerCase() !== 'card_code'
                    ) {
                        if (key.toLowerCase() === 'amount' || key.toLowerCase() === 'converted_amount') {
                            amount = '₦' + Number(details[key]).toLocaleString();
                        } else if (key.toLowerCase() === 'status') {
                            statusText = details[key];
                        } else if (
                            // Hide description if giftcard and status is success/completed
                            !(isGiftcardSuccess && key.toLowerCase() === 'description') &&
                            // Hide balance for giftcard
                            !(isGiftcard && key.toLowerCase() === 'balance')
                        ) {
                            html += `<div><span style="color:#888; font-size: 14px;">${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</span><span style="font-weight:500; font-size: 13px;">${details[key]}</span></div>`;
                        }
                    }
                }

                // Place card image or card code at the top of the details
                html = cardImageHtml + cardCodeHtml + html;

                document.getElementById('receiptAmount').textContent = amount;
                document.getElementById('receiptStatus').textContent = statusText;
                document.getElementById('receiptDetails').innerHTML = html;
                document.getElementById('receiptOverlay').style.display = 'flex';
                document.getElementById('receiptOverlay').setAttribute('data-ref', details.reference || details.transaction_id || 'receipt');

                // Set status icon
                const iconDiv = document.querySelector('#receiptContent .icon');
                if (iconDiv) {
                    let status = (details.status || '').toLowerCase();
                    if (status === 'successful' || status === 'success' || status === 'completed') {
                        iconDiv.innerHTML = '<span style="font-size:44px;color:#0aa83f;">&#10004;</span>';
                    } else if (status === 'failed' || status === 'failure') {
                        iconDiv.innerHTML = '<span style="font-size:44px;color:#e74c3c;">&#10008;</span>';
                    } else if (status === 'processing' || status === 'pending') {
                        iconDiv.innerHTML = '<svg width="44" height="44" viewBox="0 0 50 50" style="vertical-align:middle;"><circle cx="25" cy="25" r="20" fill="none" stroke="#ffbf00" stroke-width="5" stroke-linecap="round" stroke-dasharray="31.4 31.4" transform="rotate(-90 25 25)"><animateTransform attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="1s" repeatCount="indefinite"/></circle></svg>';
                    } else {
                        iconDiv.innerHTML = '';
                    }
                }
            });
        });

        // Close overlay
        document.getElementById('closeReceipt').onclick = function() {
            document.getElementById('receiptOverlay').style.display = 'none';
        };

        // Download as PDF (hide buttons before capture)
        document.getElementById('downloadPDF').onclick = function() {
            const { jsPDF } = window.jspdf;
            // Hide the button row before capture
            const buttonRow = document.querySelector('#receiptContent > div[style*="display:flex"]');
            if (buttonRow) buttonRow.style.display = 'none';
            html2canvas(document.getElementById('receiptContent')).then(function(canvas) {
                if (buttonRow) buttonRow.style.display = '';
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF();
                const imgProps = pdf.getImageProperties(imgData);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                // Get reference for filename
                const ref = document.getElementById('receiptOverlay').getAttribute('data-ref') || 'receipt';
                pdf.save('eaziplux-' + ref + '.pdf');
            });
        };

        document.getElementById('downloadIMG').onclick = function() {
            // Hide the button row before capture
            const buttonRow = document.querySelector('#receiptContent > div[style*="display:flex"]');
            if (buttonRow) buttonRow.style.display = 'none';
            html2canvas(document.getElementById('receiptContent')).then(function(canvas) {
                if (buttonRow) buttonRow.style.display = '';
                const ref = document.getElementById('receiptOverlay').getAttribute('data-ref') || 'receipt';
                const link = document.createElement('a');
                link.download = 'eaziplux-' + ref + '.png';
                link.href = canvas.toDataURL();
                link.click();
            });
        };
    });

    // Function to show full image in overlay
    function showFullImage(imgPath) {
        // Create overlay
        let overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.top = 0;
        overlay.style.left = 0;
        overlay.style.width = '100vw';
        overlay.style.height = '100vh';
        overlay.style.background = 'rgba(0,0,0,0.95)';
        overlay.style.zIndex = 100000;
        overlay.style.display = 'flex';
        overlay.style.justifyContent = 'center';
        overlay.style.alignItems = 'center';
        overlay.onclick = function() { document.body.removeChild(overlay); };

        // Create image
        let img = document.createElement('img');
        img.src = imgPath;
        img.style.maxWidth = '95vw';
        img.style.maxHeight = '95vh';
        img.style.borderRadius = '10px';
        img.style.boxShadow = '0 0 20px #000';

        overlay.appendChild(img);
        document.body.appendChild(overlay);
    }
    </script>
</body>
</html>