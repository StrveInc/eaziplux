<?php 
    session_start();

    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
    
        
$servername = "localhost";
$username = "root";
$dbpassword = "";
$database = "eaziplux";

$conn = new mysqli($servername, $username, $dbpassword, $database);
    
        $_SESSION['conn'] = $conn;
    
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    
        $username = $_SESSION['username'];  

        $user_id_query = "SELECT user_id FROM users WHERE username = '$username'";
        $result = $conn->query($user_id_query);
    
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $user_id = $row["user_id"];
    
            $balance_query = "SELECT balance FROM virtual_accounts WHERE acct_id = $user_id";
            $balance_result = $conn->query($balance_query);
    
            $_SESSION["USER_ID"] = $user_id;
    
            if ($balance_result->num_rows == 1) {
                $balance_row = $balance_result->fetch_assoc();
                $account_balance = $balance_row["balance"];
    
                $_SESSION['ACCT'] = $account_balance;
            } else {
                $account_balance = 0;
            }
        } else {
            header("Location: ../home/signup.php");
        }
    } else {
        header("Location: ../home/login.php");
        exit;
    }
?>