<?php



$servername = "artisan-marketplace.cfao628yky31.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "Cap-Project24";


$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";

$sql = "CREATE DATABASE IF NOT EXISTS artisan_marketplace";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Database 'artisan_marketplace' created successfully.<br>";
} else {
    echo "Error creating database: " . $conn->error;
}



?>
