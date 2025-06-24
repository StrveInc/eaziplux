<?php 
 session_start();
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
            border-bottom: .4px solid #ccc;
            width: 100%;
            left: -1px;
            background: rgb(0, 0, 0);
        }
    </style>
</head

<body>
    <header>
            Settings
    </header>

    <main>
        <div class="container">
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
</body>

</html>