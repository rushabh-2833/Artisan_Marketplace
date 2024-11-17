<?php
session_start();
require_once '../src/helpers/db_connect.php';
require_once '../views/templates/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $order_id = $_POST['order_id'];
    $product_id = $_POST['product_id'];
    $rating = $_POST['rating']; // The selected star value
    $comment = $_POST['comment'];

    // Check if the user has already reviewed this product
    $check_review_query = "SELECT * FROM reviews WHERE user_id = ? AND product_id = ?";
    $check_stmt = $conn->prepare($check_review_query);
    $check_stmt->bind_param("ii", $user_id, $product_id);
    $check_stmt->execute();
    $existing_review = $check_stmt->get_result()->fetch_assoc();

    if ($existing_review) {
        // Redirect to order history with a message
        header('Location: order_history.php?review_exists=true');
        exit();
    }

    // Insert the review into the database
    $query = "INSERT INTO reviews (user_id, product_id, order_id, rating, comment) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("SQL preparation failed: " . $conn->error);
    }
    $stmt->bind_param("iiiss", $user_id, $product_id, $order_id, $rating, $comment);
    if ($stmt->execute()) {
        // Redirect to order history with a success message
        header('Location: order_history.php?review_submitted=true');
        exit();
    } else {
        die("Failed to submit review: " . $stmt->error);
    }
}

// Fetch product details
if (!isset($_GET['order_id']) || !isset($_GET['product_id'])) {
    die("Invalid request. Missing order_id or product_id.");
}

$order_id = $_GET['order_id'];
$product_id = $_GET['product_id'];

// Check if the user has already reviewed this product
$check_review_query = "SELECT * FROM reviews WHERE user_id = ? AND product_id = ?";
$check_stmt = $conn->prepare($check_review_query);
$check_stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
$check_stmt->execute();
$existing_review = $check_stmt->get_result()->fetch_assoc();

$query = "SELECT name FROM products WHERE id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("SQL preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Product not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        .star-rating {
            display: flex;
            justify-content: start;
            gap: 10px;
            font-size: 24px;
        }
        .star {
            color: #ccc;
            cursor: pointer;
            transition: color 0.2s ease;
        }
        .star:hover,
        .star.selected {
            color: #f39c12;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1>Review Product: <?= htmlspecialchars($product['name']) ?></h1>

    <?php if ($existing_review): ?>
        <h3 class="text-success">You have already reviewed this product!</h3>
        <p><strong>Your Rating:</strong></p>
        <div class="star-rating">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="fas fa-star <?= $i <= $existing_review['rating'] ? 'text-warning' : 'text-secondary'; ?>"></i>
            <?php endfor; ?>
        </div>
        <p><strong>Your Comment:</strong> <?= htmlspecialchars($existing_review['comment']); ?></p>
        <p><strong>Reviewed On:</strong> <?= htmlspecialchars($existing_review['created_at']); ?></p>
    <?php else: ?>
        <form method="POST" action="">
            <input type="hidden" name="order_id" value="<?= htmlspecialchars($order_id) ?>">
            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_id) ?>">
            
            <!-- Star Rating -->
            <div class="mb-3">
                <label for="rating" class="form-label">Rating:</label>
                <div class="star-rating" id="star-rating">
                    <i class="fas fa-star star" data-value="1"></i>
                    <i class="fas fa-star star" data-value="2"></i>
                    <i class="fas fa-star star" data-value="3"></i>
                    <i class="fas fa-star star" data-value="4"></i>
                    <i class="fas fa-star star" data-value="5"></i>
                </div>
                <input type="hidden" id="rating" name="rating" required>
            </div>

            <!-- Comment -->
            <div class="mb-3">
                <label for="comment" class="form-label">Comment:</label>
                <textarea id="comment" name="comment" class="form-control" rows="4" required></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">Submit Review</button>
        </form>
    <?php endif; ?>
</div>

<script>
    // JavaScript for Star Rating System
    const stars = document.querySelectorAll('.star');
    const ratingInput = document.getElementById('rating');

    stars.forEach(star => {
        star.addEventListener('click', () => {
            // Remove 'selected' class from all stars
            stars.forEach(s => s.classList.remove('selected'));

            // Add 'selected' class to clicked star and all previous ones
            star.classList.add('selected');
            const value = star.getAttribute('data-value');
            ratingInput.value = value;

            for (let i = 0; i < value; i++) {
                stars[i].classList.add('selected');
            }
        });
    });
</script>
</body>
</html>
