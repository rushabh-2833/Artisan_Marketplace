<?php
include '../src/helpers/db_connect.php';

$product_id = $_GET['product_id'];
$artisan_id = $_SESSION['user_id'];

// Delete query with condition to ensure the product belongs to the artisan
$sql = "DELETE FROM products WHERE id = ? AND artisan_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product_id, $artisan_id);

if ($stmt->execute()) {
    echo "Product deleted successfully.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();

exit;
?>
