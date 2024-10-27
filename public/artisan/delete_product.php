<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'artisan') {
    header("Location: login.php");
    exit;
}

$artisan_id = $_SESSION['user_id'];
$product_id = $_GET['product_id'];

$conn = new mysqli('artisan-marketplace.cfao628yky31.us-east-1.rds.amazonaws.com', 'admin', 'Cap-Project24', 'artisan_marketplace');
$sql = "DELETE FROM products WHERE id = ? AND artisan_id = ? AND approval_status != 'approved'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product_id, $artisan_id);

if ($stmt->execute()) {
    echo "Product deleted successfully.";
} else {
    echo "Error: " . $stmt->error;
}

$conn->close();
?>
