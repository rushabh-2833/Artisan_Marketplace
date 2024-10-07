
$conn = new mysqli('localhost', 'root', '', 'marketplace');
$product_id = $_GET['id'];

$sql = "UPDATE products SET status='Approved' WHERE product_id='$product_id'";
if ($conn->query($sql) === TRUE) {
    echo "Product approved!";
} else {
    echo "Error: " . $conn->error;
}
$conn->close();

// reject_product.php
$conn = new mysqli('localhost', 'root', '', 'marketplace');
$product_id = $_GET['id'];
$rejection_reason = $_POST['rejection_reason'];

$sql = "UPDATE products SET status='Rejected', rejection_reason='$rejection_reason' WHERE product_id='$product_id'";
if ($conn->query($sql) === TRUE) {
    echo "Product rejected!";
} else {
    echo "Error: " . $conn->error;
}
$conn->close();
