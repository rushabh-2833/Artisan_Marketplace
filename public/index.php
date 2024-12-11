<?php
// Include the header and database connection
include __DIR__ . '/../views/templates/header.php'; // Adjusted file path
include __DIR__ . '/../src/helpers/db_connect.php'; // Adjusted file path

// Ensure the database connection is initialized
if (!isset($conn) || !$conn) {
    die('Error: Database connection is not established.');
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

if (!$result) {
    die('Error executing query: ' . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- FontAwesome -->
    <title>Artisan Marketplace</title>
</head>
<style>
    /* Hero Banner */
.hero-banner {
    position: relative;
}

.hero-banner .banner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5); /* Semi-transparent overlay */
    z-index: 1;
}

.hero-banner img {
    position: relative;
    z-index: 0; /* Place the image below the overlay */
}

.hero-banner .banner-content {
    position: absolute;
    top: 20%;
    left: 20%;
    right: 20%;
    transform: translate(-50%, -50%);
    z-index: 2; /* Place the content above the overlay */
}

.hero-banner .banner-content h1 {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 3rem;
    letter-spacing: 1px;
}

.hero-banner .banner-content p {
    font-size: 1.2rem;
    color: #e8e8e8;
}

.btn-custom {
    padding: 0.8rem 1.5rem;
    border-radius: 25px;
    font-size: 1rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-custom:hover {
    transform: scale(1.1);
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

.btn-outline-light:hover {
    background-color: #ffffff;
    color: #000000;
}

.banner-content {
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.8s ease;
}

.banner-content.active {
    opacity: 1;
    transform: translateY(0);
}

/* Section Title */
.featured-products h2 {
    font-weight: bold;
    color: #2c3e50;
    letter-spacing: 1px;
}

/* Card Styling */
.product-card {
    border: none;
    border-radius: 15px;
    overflow: hidden;
    background-color: #ffffff;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
}

.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.2);
}

/* Image Styling */
.card-img-wrapper {
    overflow: hidden;
}

.card-img-top {
    height: 180px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.card-img-wrapper:hover .card-img-top {
    transform: scale(1.1); /* Zoom-in effect */
}

/* Typography */
.card-title {
    font-size: 1.1rem;
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.card-text {
    font-size: 1rem;
    color: #555;
    margin-bottom: 10px;
}

/* Buttons */
.btn-add-to-cart {
    padding: 0.6rem 1.2rem;
    font-size: 0.9rem;
    font-weight: 600;
    border-radius: 25px;
    background-color: #007bff;
    color: #ffffff;
    transition: all 0.3s ease;
    width: 100%;
}

.btn-add-to-cart:hover {
    background-color: #0056b3;
    color: #ffffff;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
}


</style>
<body>

<!-- Hero Banner -->
<section class="hero-banner position-relative">
    <div class="container-fluid p-0">
        <div class="banner-overlay"></div> <!-- Overlay added -->
        <img src="<?php echo getenv('APP_URL'); ?>/img/banner.jpg" class="img-fluid w-100" alt="Hero Banner">
        <div class="banner-content text-center">
            <h1 class="text-white display-4 fw-bold mb-3">Welcome to Artisan Marketplace</h1>
            <p class="text-light mb-4">Explore a world of handmade treasures crafted by passionate artisans.</p>
            <a href="shop.php" class="btn btn-primary btn-custom me-2">Shop Now</a>
            <a href="register.php" class="btn btn-outline-light btn-custom">Sign Up</a>
        </div>
    </div>
</section>


<!-- Spacing -->
<div class="spacer"></div>

<!-- Featured Products Section -->
<section class="featured-products my-5">
    <div class="container">
        <h2 class="text-center mb-4">Featured Products</h2>
        <div class="row row-cols-1 row-cols-md-4 g-4">
            <?php 
            $sql = "SELECT * FROM products LIMIT 4"; // Fetch only 4 products
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0): ?>
                <?php while ($product = $result->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card product-card h-100">
                            <div class="card-img-wrapper">
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="card-img-top">
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text text-muted">$<?php echo number_format($product['price'], 2); ?></p>
                                
                            </div>
                            <div class="card-footer text-center">
                                <a href="add_to_cart.php?product_id=<?php echo $product['id']; ?>" class="btn btn-primary btn-add-to-cart">Add to Cart</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center">No products found.</p>
            <?php endif; ?>
        </div>
    </div>
</section>


<section class="promo-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 promo-img">
                <img src="<?php echo getenv('APP_URL'); ?>/img/sale-banner.jpg" alt="Sale Banner" class="img-fluid rounded">
            </div>
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

<?php include __DIR__ . '/../views/templates/footer.php'; ?>
</body>
<script>
    document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('.banner-content').classList.add('active');
});

</script>
</html>
