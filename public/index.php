<?php include '../views/templates/header.php'; ?>
<?php

include '../src/helpers/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch unread notifications count
$sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = FALSE";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notification_data = $result->fetch_assoc();
$unread_count = $notification_data['unread_count'] ?? 0;
?>





    <!-- Banner section -->
    <div class="container mt-5">
        <h2>Customer Dashboard</h2>
        <div>
            <a href="notifications.php" class="btn btn-primary">
                Notifications <?php if ($unread_count > 0): ?>
                    <span class="badge bg-danger"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>

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
                                
                <div class="col-md-4">
                    <div class="card">
                        <img src="img/product1.jpeg" class="card-img-top" alt="Product Image">
                        <div class="card-body">
                            <h5 class="card-title">Save
                                Star necklace,ceramic handmade </h5>
                            <p class="card-text">$29.99</p>
                            <a href="#" class="btn btn-primary btn-custom">Add to Cart</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <img src="img/product1.jpeg" class="card-img-top" alt="Product Image">
                        <div class="card-body">
                            <h5 class="card-title">Save
                                Star necklace,ceramic handmade </h5>
                            <p class="card-text">$29.99</p>
                            <a href="#" class="btn btn-primary btn-custom">Add to Cart</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <img src="img/product1.jpeg" class="card-img-top" alt="Product Image">
                        <div class="card-body">
                            <h5 class="card-title">Save
                                Star necklace,ceramic handmade </h5>
                            <p class="card-text">$29.99</p>
                            <a href="#" class="btn btn-primary btn-custom">Add to Cart</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <img src="img/product1.jpeg" class="card-img-top" alt="Product Image">
                        <div class="card-body">
                            <h5 class="card-title">Save
                                Star necklace,ceramic handmade </h5>
                            <p class="card-text">$29.99</p>
                            <a href="#" class="btn btn-primary btn-custom">Add to Cart</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <img src="img/product1.jpeg" class="card-img-top" alt="Product Image">
                        <div class="card-body">
                            <h5 class="card-title">Save
                                Star necklace,ceramic handmade </h5>
                            <p class="card-text">$29.99</p>
                            <a href="#" class="btn btn-primary btn-custom">Add to Cart</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <img src="img/product1.jpeg" class="card-img-top" alt="Product Image">
                        <div class="card-body">
                            <h5 class="card-title">Save
                                Star necklace,ceramic handmade </h5>
                            <p class="card-text">$29.99</p>
                            <a href="#" class="btn btn-primary btn-custom">Add to Cart</a>
                        </div>
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

    <?php include '../views/templates/footer.php'; ?>