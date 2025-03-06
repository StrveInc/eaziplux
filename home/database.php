<?php


$servername = "localhost";  // Replace with your database server name
$username = "qd_global";        // Replace with your database username
$dbpassword = "";            // Replace with your database password
$database = "eaziplux"; // Replace with your database name



$mysqli = new mysqli(
    hostname: $servername,
    username: $username,
    password: $dbpassword,
    database: $database,
);

return $mysqli;