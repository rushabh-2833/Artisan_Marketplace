<?php
include '../views/templates/header.php';
include '../src/helpers/db_connect.php'; // Adjust path if needed

// Get the product ID from the query parameter
$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    echo "Product not found.";
    exit;
}

// Fetch product details, average rating, and total reviews
$stmt = $conn->prepare("
    SELECT 
        p.*, 
        COALESCE(AVG(r.rating), 0) AS average_rating, 
        COUNT(r.rating) AS total_reviews 
    FROM products p
    LEFT JOIN reviews r ON p.id = r.product_id
    WHERE p.id = ? AND p.approval_status = 'approved'
    GROUP BY p.id
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo "Product not found.";
    exit;
}

// Fetch product reviews with user names
$review_stmt = $conn->prepare("
    SELECT r.rating, r.comment, r.created_at, CONCAT(u.first_name, ' ', u.last_name) AS full_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.product_id = ?
");
$review_stmt->bind_param("i", $product_id);
$review_stmt->execute();
$reviews = $review_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        .star {
            color: #f39c12;
        }
        .empty-star {
            color: #ccc;
        }
        .review {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="col-md-6">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <p><?php echo htmlspecialchars_decode($product['description']); ?></p>
            <p><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></p>
            <p><strong>Stock:</strong> <?php echo $product['stock']; ?> available</p>

            <!-- Average Rating Display -->
            <p>
                <strong>Average Rating:</strong> 
                <?php if ($product['total_reviews'] > 0): ?>
                    <span>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= $i <= round($product['average_rating']) ? 'star' : 'empty-star'; ?>"></i>
                        <?php endfor; ?>
                    </span>
                    (<?php echo number_format($product['average_rating'], 1); ?> based on <?php echo $product['total_reviews']; ?> reviews)
                <?php else: ?>
                    No ratings yet
                <?php endif; ?>
            </p>

            <form action="add_to_cart.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1" max="<?php echo $product['stock']; ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Add to Cart</button>
            </form>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="mt-5">
        <h3>Product Reviews</h3>
        <?php if ($reviews->num_rows > 0): ?>
            <?php while ($review = $reviews->fetch_assoc()): ?>
                <div class="review mb-4 p-3 border rounded">
                    <strong><?php echo htmlspecialchars($review['full_name']); ?></strong>
                    <div class="mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= $i <= $review['rating'] ? 'star' : 'empty-star'; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="mb-1"><?php echo htmlspecialchars($review['comment']); ?></p>
                    <small class="text-muted">Reviewed on: <?php echo htmlspecialchars($review['created_at']); ?></small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No reviews yet for this product.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
