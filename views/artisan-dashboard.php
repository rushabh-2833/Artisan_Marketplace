<?php
include '../views/templates/header.php';

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
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo ucfirst($product['approval_status']); ?></td>
                        <td>
                            <?php if ($product['approval_status'] == 'approved'): ?>
                                <a href="?view_feedback=true&product_id=<?php echo $product['id']; ?>" class="btn btn-info btn-sm">View Feedback</a>
                            <?php else: ?>
                                <a href="../public/product_management.php?action=edit&product_id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="delete_product.php?product_id=<?php echo $product['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                            <?php endif; ?>
                            <?php if ($product['approval_status'] == 'rejected'): ?>
                                <p class="text-danger">Rejected: <?php echo htmlspecialchars($product['rejection_reason']); ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php
    // Display feedback for a product
    if (isset($_GET['view_feedback']) && isset($_GET['product_id'])):
        $product_id = $_GET['product_id'];
        $feedback_query = $conn->prepare("
            SELECT r.rating, r.comment, r.created_at, CONCAT(u.first_name, ' ', u.last_name) AS user_name
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.product_id = ?
        ");
        $feedback_query->bind_param("i", $product_id);
        $feedback_query->execute();
        $feedback_result = $feedback_query->get_result();
        ?>
        <div class="container mt-5">
            <h3>Feedback for Product: <?php echo htmlspecialchars($product['name']); ?></h3>
            <?php if ($feedback_result->num_rows > 0): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($feedback = $feedback_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($feedback['user_name']); ?></td>
                                <td>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $feedback['rating'] ? 'text-warning' : 'text-secondary'; ?>"></i>
                                    <?php endfor; ?>
                                </td>
                                <td><?php echo htmlspecialchars($feedback['comment']); ?></td>
                                <td><?php echo htmlspecialchars($feedback['created_at']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No feedback available for this product.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <a href="../public/artisan/rejected_products.php" class="btn btn-warning">View Rejected Products</a>
    <?php include '../views/templates/footer.php'; ?>
