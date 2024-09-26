<?php
$servername = "artisan-marketplace.cfao628yky31.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "Cap-Project24";
$dbname = "artisan-marketplace";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
?>
