<?php 
session_start();
$user_role = $_SESSION['user_role'] ?? null; // Check if user_role is set, default to null if not
$initials = $_SESSION['user_initials'] ?? ''; // Retrieve initials from session
$cartItemCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0; // Calculate total items in cart
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <title>Artisan Marketplace</title>
    <style>
        /* Adjust styles for the profile icon */
        .profile-icon {
            width: 40px;  
            height: 40px; 
            background-color: #ffffff; 
            color: #fff; 
            border-radius: 50%; 
            display: flex;
            align-items: center; 
            justify-content: center; 
            margin-left: 15px; 
            cursor: pointer; 
        }
        /* Styles for cart icon */
        .cart-icon {
            color: #fff;
            font-size: 20px;
            position: relative;
            margin-right: 15px;
            cursor: pointer;
        }
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: red;
            color: #fff;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">
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
                    <li class="nav-item"><a class="nav-link" href="../public/shop.php">Product</a></li>
                    <li class="nav-item"><a class="nav-link" href="../public/admin/admin_approve_products.php">Product Approval</a></li>
                
                <?php elseif ($user_role === 'customer') : ?>
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="shop.php">Product</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="checkout.php">Checkout</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    
                    <!-- Cart Icon with Item Count -->
                    <li class="nav-item">
                        <a href="cart.php" class="cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($cartItemCount > 0): ?>
                                <span class="cart-count"><?php echo $cartItemCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- Profile Dropdown for Customers -->
                    <li class="nav-item dropdown">
                        <div class="profile-icon dropdown-toggle" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo $initials; ?>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                            <li><a class="dropdown-item" href="profile.php">Personal Info</a></li>
                            <li><a class="dropdown-item" href="payment_methods.php">Payment Methods</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Sign Out</a></li>
                        </ul>
                    </li>

                <?php elseif ($user_role === 'artisan') : ?>
                    <li class="nav-item"><a class="nav-link" href="../views/home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="../shop.php">Product</a></li>
                    <li class="nav-item"><a class="nav-link" href="product_management.php">Product Management</a></li>
                    <li class="nav-item"><a class="nav-link" href="../views/artisan-dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                
                <?php else : ?>
                    <!-- Default Links for Visitors (Not Logged In) -->
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="shop.php">Product</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
