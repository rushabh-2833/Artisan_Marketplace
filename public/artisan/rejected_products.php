<?php include 'C:/xampp/htdocs/Artisan_Marketplace/views/templates/header.php'; ?>
<?php
session_start();

// Check if user is an artisan
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'artisan') {
    header("Location: login.php");
    exit;
}

// Database connection
include 'C:/xampp/htdocs/Artisan_Marketplace/src/helpers/db_connect.php';

$artisan_id = $_SESSION['user_id'];

// Query to get rejected products
$sql = "SELECT * FROM products WHERE artisan_id = ? AND approval_status = 'rejected'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $artisan_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rejected Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Rejected Products</h2>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Rejection Reason</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['price']); ?></td>
                        <td><?php echo htmlspecialchars($product['stock']); ?></td>
                        <td><?php echo htmlspecialchars($product['rejection_reason']); ?></td>
                        <td>
                            <!-- Link to edit product for resubmission -->
                            <a href="product_management.php?action=edit&product_id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">Edit & Resubmit</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center">No rejected products found.</p>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="artisan_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
