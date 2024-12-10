<?php
include __DIR__ . '/../../views/templates/header.php';
include __DIR__ . '/../../src/helpers/db_connect.php';




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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css">
    <title>Artisan Marketplace</title>
</head>
<body>

<!-- Hero Banner -->
<section class="hero-banner">
    <div class="container-fluid p-0">
    <img src="<?php echo getenv('APP_URL'); ?>/img/banner.jpg" class="img-fluid w-100" alt="Hero Banner">
    <div class="banner-content text-center">
            <h1 class="text-white">Welcome to Artisan Marketplace</h1>
            <p>Explore a world of handmade treasures crafted by passionate artisans.</p>
            <a href="shop.php" class="btn btn-primary btn-custom">Shop Now</a>
            <a href="register.php" class="btn btn-outline-secondary btn-custom">Sign Up</a>
        </div>
    </div>
</section>

<!-- Spacing -->
<div class="spacer"></div>

<!-- Featured Products Section -->
<section class="featured-products my-5">
    <div class="container">
        <h2 class="text-center mb-4">Featured Products</h2>
        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($product = $result->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="card">
                            <!-- Product link -->
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

<section class="promo-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <!-- Image Column -->
            <div class="col-md-6 promo-img">
                <img src="/img/sale-banner.jpg" alt="Sale Banner" class="img-fluid rounded">
            </div>
            <!-- Text Column -->
            <div class="col-md-6">
                <h2>Biggest Sale of the Season!</h2>
                <p>
                    Don’t miss out on our exclusive discounts! Shop your favorite handmade products now and enjoy up to <strong>50% off</strong> on selected items. Offer valid while supplies last!
                </p>
                <a href="shop.php" class="btn btn-primary btn-custom">Shop Now</a>
            </div>
        </div>
    </div>
</section>


<section class="why-choose-us">
    <div class="container text-center">
        <h2>Why Choose Artisan Marketplace?</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="feature-box">
                    <i class="fas fa-gem"></i>
                    <h4>Unique Products</h4>
                    <p>Discover one-of-a-kind, handcrafted items you won't find elsewhere.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <i class="fas fa-handshake"></i>
                    <h4>Support Artisans</h4>
                    <p>Empower local artisans and help them grow their businesses.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <i class="fas fa-shield-alt"></i>
                    <h4>Secure Transactions</h4>
                    <p>Shop with confidence knowing your data is protected.</p>
                </div>
            </div>
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

<section class="testimonials">
    <div class="container text-center">
        <h2>What Our Customers Say</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="testimonial-card">
                    <p class="testimonial-text">"Amazing products! The quality and craftsmanship are exceptional."</p>
                    <h5>- Sarah M.</h5>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <p class="testimonial-text">"This platform helped me connect with local artisans and find unique gifts."</p>
                    <h5>- Alex J.</h5>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <p class="testimonial-text">"Easy to use and such a wide variety of products. Highly recommended!"</p>
                    <h5>- Emily K.</h5>
                </div>
            </div>
        </div>
    </div>
</section>


<?php include '../views/templates/footer.php'; ?>
</body>
</html>
