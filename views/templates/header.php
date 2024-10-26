<?php
session_start();
$user_role = $_SESSION['user_role'] ?? null; // Check if user_role is set, default to null if not
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <li class="nav-item"><a class="nav-link" href="../views/admin-dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="product.php">Product</a></li>
                
                <?php elseif ($user_role === 'customer') : ?>
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="shop.php">Product</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="checkout.php">Checkout</a></li>
                    <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>

                <?php elseif ($user_role === 'artisan') : ?>
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="shop.php">Product</a></li>
                    <li class="nav-item"><a class="nav-link" href="product_management.php">Product Management</a></li>
                    <li class="nav-item"><a class="nav-link" href="../views/artisan-dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                
                <?php else : ?>
                    <!-- Default Links for Visitors (Not Logged In) -->
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="shop.php">Product</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                <?php endif; ?>

                <?php if ($user_role) : ?>
                    <!-- Sign Out Link for Logged-In Users -->
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Sign Out</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
