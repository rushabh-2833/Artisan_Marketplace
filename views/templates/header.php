<?php
session_start();
$user_role = $_SESSION['user_role'] ?? null; // Check if user_role is set, default to null if not
$user_logged_in = isset($_SESSION['user_id']); // Check if the user is logged in
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style/style.css">
    <title>Artisan Marketplace</title>
</head>
<body>

<!-- Navbar -->

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="path/to/logo.png" alt="Logo" width="40" height="40">
            Artisan Marketplace
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if ($user_role === 'admin') : ?>
                    <li class="nav-item"><a class="nav-link" href="/artisan_marketplace/views/admin-dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/artisan_marketplace/public/index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/artisan_marketplace/public/shop.php">Product</a></li>
                    <li class="nav-item"><a class="nav-link" href="/artisan_marketplace/public/admin/admin_approve_products.php">Product Approval</a></li>
                <?php elseif ($user_role === 'customer') : ?>
                    <li class="nav-item"><a class="nav-link" href="/artisan_marketplace/public/index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/artisan_marketplace/public/shop.php">Product</a></li>
                    <li class="nav-item"><a class="nav-link" href="/artisan_marketplace/public/about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="/artisan_marketplace/public/checkout.php">Checkout</a></li>
                    <li class="nav-item"><a class="nav-link" href="/artisan_marketplace/public/cart.php">Cart</a></li>
                    <li class="nav-item"><a class="nav-link" href="/artisan_marketplace/public/contact.php">Contact</a></li>
                <?php elseif ($user_role === 'artisan') : ?>
                    <li class="nav-item"><a class="nav-link" href="/artisan_marketplace/public/index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/artisan_marketplace/public/shop.php">Product</a></li>
                    <li class="nav-item"><a class="nav-link" href="/artisan_marketplace/public/product_management.php">Product Management</a></li>
                    <li class="nav-item"><a class="nav-link" href="/artisan_marketplace/views/artisan-dashboard.php">Dashboard</a></li>
                <?php else : ?>
                    <!-- Default Links for Visitors (Not Logged In) -->
                    <li class="nav-item"><a class="nav-link" href="/artisan_marketplace/public/index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/artisan_marketplace/public/shop.php">Product</a></li>
                    <li class="nav-item"><a class="nav-link" href="/artisan_marketplace/public/login.php">Login</a></li>
                <?php endif; ?>

                <!-- Profile Dropdown for Logged-In Users -->
                <?php if ($user_logged_in) : ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> Account
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                            <a class="dropdown-item" href="personal_info.php">Personal Information</a>
                            <a class="dropdown-item" href="payment_methods.php">Payment Methods</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="/artisan_marketplace/public/logout.php">Sign Out</a>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>



<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>