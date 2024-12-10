
<?php
include __DIR__ . '/../../views/templates/header.php';

if (!isset($_SESSION['user_id'])) {
    die('Error: User is not logged in.');
}

$artisan_id = $_SESSION['user_id'];
$conn = new mysqli('artisan-marketplace.cfao628yky31.us-east-1.rds.amazonaws.com', 'admin', 'Cap-Project24', 'artisan_marketplace');
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$sql = "SELECT * FROM products WHERE artisan_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('Error preparing statement: ' . $conn->error);
}

$stmt->bind_param("i", $artisan_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p class='text-warning'>No products found for artisan ID: " . htmlspecialchars($artisan_id) . "</p>";
}
?>

<?php include '../views/templates/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../public/style/style.css" rel="stylesheet"> 
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">


</head>
<body>
<div class="container mt-5">
    <div class="text-center mb-5">
        <h2 class="page-heading">Manage Your Products</h2>
    </div>

    <div class="card p-4 shadow-sm">
        <table class="table table-bordered text-center">
            <thead class="table-dark">
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
                        <td>
                            <span class="badge bg-<?php echo $product['approval_status'] === 'approved' ? 'success' : ($product['approval_status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                <?php echo ucfirst($product['approval_status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($product['approval_status'] == 'approved'): ?>
                                <a href="?view_feedback=true&product_id=<?php echo $product['id']; ?>" class="btn btn-info btn-sm">View Feedback</a>
                            <?php else: ?>
                                <a href="../public/product_management.php?action=edit&product_id=<?php echo $product['id']; ?>" class="btn btn-custom btn-sm">Edit</a>
                                <a href="delete_product.php?product_id=<?php echo $product['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                            <?php endif; ?>
                            <?php if ($product['approval_status'] == 'rejected'): ?>
                                <p class="text-danger mt-2"><strong>Reason:</strong> <?php echo htmlspecialchars($product['rejection_reason']); ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="text-center mt-4">
        <a href="../public/artisan/rejected_products.php" class="btn btn-warning">View Rejected Products</a>
    </div>

    <?php
    if (isset($_GET['view_feedback']) && isset($_GET['product_id'])):
        $product_id = $_GET['product_id'];
        $feedback_query = $conn->prepare("
            SELECT r.rating, r.comment, r.created_at, CONCAT(u.first_name, ' ', u.last_name) AS user_name, p.name AS product_name
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            JOIN products p ON r.product_id = p.id
            WHERE r.product_id = ?
        ");
        $feedback_query->bind_param("i", $product_id);
        $feedback_query->execute();
        $feedback_result = $feedback_query->get_result();
        $product_name = null;
    
        // Fetch product name if there's feedback
        if ($feedback_result->num_rows > 0) {
            $feedback_row = $feedback_result->fetch_assoc();
            $product_name = $feedback_row['product_name'];
            $feedback_result->data_seek(0); // Reset pointer to fetch all rows in the loop
        } else {
            // Fetch product name even if there's no feedback
            $product_query = $conn->prepare("SELECT name FROM products WHERE id = ?");
            $product_query->bind_param("i", $product_id);
            $product_query->execute();
            $product_name_result = $product_query->get_result();
            $product_row = $product_name_result->fetch_assoc();
            $product_name = $product_row['name'] ?? 'Unknown Product';
        }
        ?>
        <div class="card p-4 shadow-sm mt-5">
            <h3 class="text-center mb-4">Feedback for Product: <strong><?php echo htmlspecialchars($product_name); ?></strong></h3>
            <?php if ($feedback_result->num_rows > 0): ?>
                <table class="table table-bordered">
                    <thead class="table-dark">
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
    
</div>

<?php include '../views/templates/footer.php'; ?>
