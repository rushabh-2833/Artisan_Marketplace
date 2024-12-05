<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

// Specify the directory containing the .env file
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$servername = "artisan-marketplace.cfao628yky31.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "Cap-Project24";
$dbname = "artisan_marketplace";
 // Use the created database

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


?>
