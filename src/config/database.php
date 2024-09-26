<?php



$servername = "artisan-marketplace.cfao628yky31.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "Cap-Project24";


$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";

// Close the connection explicitly
$conn->close();
echo "Connection closed";

?>
