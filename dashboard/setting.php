<?php 
 session_start();

 include '../config.php';
if (!isset($_SESSION['username'])) {
    header("Location: ../home/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Audiowide&display=swap" rel="stylesheet">

    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&family=Truculenta:opsz,wght@12..72,100..900&display=swap"
        rel="stylesheet">
    <script src="https://kit.fontawesome.com/49c5823e25.js" crossorigin="anonymous"></script>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/setting.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" size="662x662" href="../css/imgs/eaziplux.png">
    <title>Settings</title>
    <style>
        
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

        .referral {
            /* display: flex; */
            /* flex-direction: column; */
            align-items: center;
            justify-content: center;
            /* margin: 20px 0; */
            padding: 10px;
            /* border: 1px solid #ffbf00; */
            border-radius: 10px;
            background-color: #1E1E1E;
            color: #fff;
            /* text-align: center; */
        }

        .referral-text, .referral-link {
            /* margin-bottom: 10px; */
        }

        #referralCode, #referralLink {
            font-size: 15px;
            color: #ffbf00;
            text-align: center;
            white-space: nowrap; /* Prevent text from wrapping */
            overflow: hidden; /* Hide overflow content */
            text-overflow: ellipsis; /* Add ellipsis for overflow */
            width: 100%; /* Make it extend the full width of the container */
            border: none; /* Remove border for a cleaner look */
        }

        .referral-copy button {
            padding: 8px 15px;
            font-size: 10px;
            color: #fff;
            background-color:rgb(80, 80, 80);
            border: none;
            /* border-radius: 5px; */
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100px;
            font-family: 'Poppins', sans-serif;
        }

        .referral-copy button:hover {
            background-color: #e0a800;
        }

        .invite{
            margin: 10px auto;
            width: 90%;
            /* border: 1px solid #ffbf00; */
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-left: 10px;
            background-color:rgb(118, 118, 118);
            font-family: 'poppins', sans-serif;
        }
    </style>
</head

<body>
    <header>
            Settings
    </header>

    <main>
        <div class="container">
            <div class="referral">
                <div style="font-size: 10px; font-weight: 500; border: 0px solid black; width: 94%; text-align: left; margin: auto;">Invite friends and earn while they purchase.</div>
                <div class="referral-text">
                    <div class="invite">
                        <div id="referralCode"><?php echo $_SESSION['referral_code'] ?? 'N/A'; ?></div>         
                        <div class="referral-copy">
                            <button id="copyCodeButton" onclick="copyToClipboard('referralCode', 'copyCodeButton')">Copy code</button>                
                        </div>
                    </div>
                    </div>
                
                <div class="referral-link">
                    <div class="invite">
                        <div id="referralLink"><?php echo 'https://eaziplux.com.ng/home/signup.php?ref=' . ($_SESSION['referral_code'] ?? ''); ?></div>
                        <div class="referral-copy">
                            <button id="copyLinkButton" onclick="copyToClipboard('referralLink', 'copyLinkButton')">Copy link</button>
                        </div>
                    </div>
                </div>
                <div style="font-size: 10px; color: #ffbf00;">EaziFlow balance: &#8358;<?php 
                // Fetch referral earnings from the virtual_accounts table
                $referral_earnings_query = "SELECT referral_earnings FROM virtual_accounts WHERE acct_id = ?";
                $stmt3 = $conn->prepare($referral_earnings_query);
                $stmt3->bind_param("s", $_SESSION['user_id']);
                $stmt3->execute();
                $referral_earnings_result = $stmt3->get_result();
                if ($referral_earnings_result->num_rows == 1) {
                    $referral_earnings_row = $referral_earnings_result->fetch_assoc();
                    $referral_earnings = $referral_earnings_row["referral_earnings"];
                    echo number_format($referral_earnings, 2);
                } else {
                    echo "0.00"; // Default value if no referral earnings found
                }
                $stmt3->close();
            ?></div>
                <div style="font-size: 10px; margin: auto; width: 90%; text-align: right; font-weight: 600;">Refer and Relax by <span style="font-family: audiowide; color: #ffbf00">EaziFlow⚡</span></div>
            </div>


            <a href="transfer.php">
                <div class="tab">
                    <p> <i class="fa fa-pencil" aria-hidden="true"></i> Account details</p>
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>

            <a href="transaction.php">
                <div class="tab">
                    <p> <i class="fas fa-scroll"></i> Transaction History </p><i class="fas fa-chevron-right"></i>
                </div>
            </a>

            <a href="../home/resetpassword.php">
                <div class="tab">
                    <p> <i class="fa fa-key" aria-hidden="true"></i> Login Settings</p><i class="fas fa-chevron-right"></i>
                </div>
            </a>

            <a href="https://wa.link/jfjvef">
                <div class="tab">
                    <p> <i class="fas fa-headset"></i> Customer Service</p><i class="fas fa-chevron-right"></i>
                </div>
            </a>

            <a href="#">
                <div class="tab">
                    <p> <i class="fa fa-star" aria-hidden="true"></i> Rate US </p><i class="fas fa-chevron-right"></i>
                </div>
        </div>

        <div class="logout">
            <a href="../home/logout.php">
                <p>Sign out</p>
            </a>
        </div>
    </main>
    <div>
        <div class="tag1">
            <p>Version 1.1.0

            </p>
        </div>
    </div>
    <div class="tag">
        <p>&copy 2024 EaziPlux copyright. All right reseverd</p>
    </div>

    <script>
        function copyToClipboard(elementId, buttonId) {
            const textToCopy = document.getElementById(elementId).textContent;
            const button = document.getElementById(buttonId);

            if (navigator.clipboard && navigator.clipboard.writeText) {
                // Use Clipboard API if available
                navigator.clipboard.writeText(textToCopy).then(() => {
                    button.textContent = "Copied!";
                    button.style.backgroundColor = "#ffbf00"; // Optional: Change button color to indicate success

                    // Reset the button text after 2 seconds
                    setTimeout(() => {
                        button.textContent = buttonId === "copyCodeButton" ? "Copy code" : "Copy link";
                        button.style.backgroundColor = "rgb(80, 80, 80)"; // Reset to original color
                    }, 2000);
                }).catch(err => {
                    alert('Failed to copy: ' + err);
                });
            } else {
                // Fallback for older browsers or restricted environments
                const tempInput = document.createElement("textarea");
                tempInput.value = textToCopy;
                document.body.appendChild(tempInput);
                tempInput.select();
                tempInput.setSelectionRange(0, 99999); // For mobile devices

                try {
                    document.execCommand("copy");
                    button.textContent = "Copied!";
                    button.style.backgroundColor = "#ffbf00"; // Optional: Change button color to indicate success

                    // Reset the button text after 2 seconds
                    setTimeout(() => {
                        button.textContent = buttonId === "copyCodeButton" ? "Copy code" : "Copy link";
                        button.style.backgroundColor = "rgb(80, 80, 80)"; // Reset to original color
                    }, 2000);
                } catch (err) {
                    alert("Failed to copy: " + err);
                }

                document.body.removeChild(tempInput);
            }
        }
    </script>
</body>

</html>