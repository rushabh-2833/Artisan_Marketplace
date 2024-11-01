<?php

$conn = new mysqli('artisan-marketplace.cfao628yky31.us-east-1.rds.amazonaws.com', 'admin', 'Cap-Project24', 'artisan_marketplace');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product_id'])) {
    $product_id = $_POST['delete_product_id'];

    // Prepare and execute deletion
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND artisan_id = ?");
    $stmt->bind_param("ii", $product_id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Product deleted successfully.";
    } else {
        $_SESSION['message'] = "Error deleting product: " . $stmt->error;
    }
    $stmt->close();
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

    <!-- Display success or error message -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success text-center">
            <?php echo $_SESSION['message']; ?>
            <?php unset($_SESSION['message']); // Clear the message after displaying ?>
        </div>
    <?php endif; ?>

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
                        <form action="product_management.php" method="POST" style="display:inline;">
                            <input type="hidden" name="delete_product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
