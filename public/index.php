<?php
include '../views/templates/header.php';
include '../src/helpers/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch products from the database
$sql = "
    SELECT 
        p.*, 
        COALESCE(AVG(r.rating), 0) AS average_rating, 
        COUNT(r.rating) AS total_reviews 
    FROM products p
    LEFT JOIN reviews r ON p.id = r.product_id
    WHERE p.approval_status = 'approved'
    GROUP BY p.id
    LIMIT 6
";
$result = $conn->query($sql);
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">  
    <style>
        /* Product Cards */
        .card {
            height: 550px;  
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);  
            margin: 10px;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .card-img-top {
            height: 300px;  
            object-fit: cover;  
            border-bottom: 2px solid #ddd;  
        }
    </style>
</head>

<section class="banner text-center my-5">
    <div class="container">
        <h1>Discover Unique Handmade Treasures</h1>
        <p>Join our community and find your favorite artisans today!</p>
        <a href="#" class="btn btn-primary btn-custom">Explore</a>
        <a href="register.php" class="btn btn-outline-secondary">Sign Up</a>
    </div>
</section>

<!-- Featured Products -->
<section class="featured-products my-5">
    <div class="container">
        <h2 class="text-center mb-4">Featured Products</h2>
        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($product = $result->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="card">
                            <!-- Clickable product card redirecting to product_details.php -->
                            <a href="product_details.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark">
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <p class="card-text">$<?php echo number_format($product['price'], 2); ?></p>
                                    <p>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $product['average_rating'] ? 'star' : 'empty-star'; ?>"></i>
                                        <?php endfor; ?>
                                        (<?php echo number_format($product['average_rating'], 1); ?>)
                                    </p>
                                </div>
                            </a>
                            <!-- Add to Cart Button -->
                            <div class="card-footer text-center">
                                <a href="add_to_cart.php?product_id=<?php echo $product['id']; ?>" class="btn btn-primary btn-custom">Add to Cart</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center">No products available at the moment.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Newsletter Subscription -->
<section class="newsletter my-5 text-center">
    <div class="container">
        <h2>Subscribe to our newsletter</h2>
        <p>Get the latest updates on features and releases</p>
        <form>
            <div class="input-group mb-3">
                <input type="email" class="form-control" placeholder="Enter your email">
                <button class="btn btn-primary btn-custom" type="submit">Join</button>
            </div>
        </form>
    </div>
</section>

<?php include '../views/templates/footer.php'; ?>
