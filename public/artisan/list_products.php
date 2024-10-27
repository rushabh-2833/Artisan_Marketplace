<?php
$conn = new mysqli('artisan-marketplace.cfao628yky31.us-east-1.rds.amazonaws.com', 'admin', 'Cap-Project24', 'artisan_marketplace');
$sql = "SELECT * FROM products WHERE artisan_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<table>
    <tr>
        <th>Product Name</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    <?php while ($product = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $product['name']; ?></td>
            <td><?php echo ucfirst($product['approval_status']); ?></td>
            <td>
                <a href="product_management.php?action=edit&product_id=<?php echo $product['id']; ?>">Edit</a>
                <a href="product_management.php?action=delete&product_id=<?php echo $product['id']; ?>" onclick="return confirm('Delete this product?');">Delete</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
