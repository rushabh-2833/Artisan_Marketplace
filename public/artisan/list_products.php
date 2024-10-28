<?php

$conn = new mysqli('artisan-marketplace.cfao628yky31.us-east-1.rds.amazonaws.com', 'admin', 'Cap-Project24', 'artisan_marketplace');

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch products for the logged-in artisan
$sql = "SELECT * FROM products WHERE artisan_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4">Your Products</h2>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Product Name</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($product = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td><?php echo htmlspecialchars($product['price']); ?></td>
                    <td><?php echo ucfirst(htmlspecialchars($product['approval_status'])); ?></td>
                    <td>
                        <a href="product_management.php?action=edit&product_id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm me-2">Edit</a>
                        <a href="product_management.php?action=delete&product_id=<?php echo $product['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
