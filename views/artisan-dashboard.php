<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'artisan') {
    header("Location: login.php");
    exit;
}

$artisan_id = $_SESSION['user_id'];
$conn = new mysqli('artisan-marketplace.cfao628yky31.us-east-1.rds.amazonaws.com', 'admin', 'Cap-Project24', 'artisan_marketplace');
$sql = "SELECT * FROM products WHERE artisan_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $artisan_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Manage Your Products</h2>
        
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $product['name']; ?></td>
                        <td><?php echo ucfirst($product['approval_status']); ?></td>
                        <td>
                            <?php if (in_array($product['approval_status'], ['pending', 'rejected'])): ?>
                                <a href="edit_product.php?product_id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="delete_product.php?product_id=<?php echo $product['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                            <?php endif; ?>
                            <?php if ($product['approval_status'] == 'rejected'): ?>
                                <p class="text-danger">Rejected: <?php echo $product['rejection_reason']; ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <a href="../public/artisan/rejected_products.php" class="btn btn-warning">View Rejected Products</a>

</body>
</html>
