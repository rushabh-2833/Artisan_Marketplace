<?php
// Start session only if it is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_role = $_SESSION['user_role'] ?? null;
$user_logged_in = isset($_SESSION['user_id']);
$initials = $_SESSION['user_initials'] ?? '';

// Include the database connection
$baseDir = $_SERVER['DOCUMENT_ROOT'] . '/../';
include $baseDir . 'src/helpers/db_connect.php'; // Ensure the path is correct

// Fetch notification count only if the user is logged in
$notification_count = 0;
if ($user_logged_in) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $notification_data = $result->fetch_assoc();
            $notification_count = $notification_data['unread_count'] ?? 0;
        }
    }
    $unread_count = 0;
if ($user_logged_in) {
    $sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $notification_data = $result->fetch_assoc();
            $unread_count = $notification_data['unread_count'] ?? 0;
        }
    }
}
    
}
$pending_orders_count = 0;
if ($user_logged_in) {
    $sql = "SELECT COUNT(*) AS pending_count FROM orders WHERE customer_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $data = $result->fetch_assoc();
            $pending_orders_count = $data['pending_count'] ?? 0;
        }
    }
}
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Font Awesome -->
    <title>Artisan Marketplace</title>
    <style>
        /* Profile icon styling */
        .profile-icon {
            width: 40px;
            height: 40px;
            background-color: #007bff;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 15px;
            cursor: pointer;
            font-weight: bold;
            font-size: 18px;
            border: 2px solid #fff;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s;
        }

        .profile-icon:hover {
            transform: scale(1.1);
        }

        /* Navbar customization */
        .navbar {
            background: linear-gradient(90deg, #1c1c1c, #3a3a3a);
            border-bottom: 2px solid #fff;
        }

        .navbar-brand {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .navbar-dark .nav-link {
            font-size: 1rem;
            font-weight: 500;
            margin: 0 10px;
            color: #f8f9fa;
            transition: color 0.3s ease;
        }

        .navbar-dark .nav-link:hover {
            color: #ffc107;
        }

        /* Badge styling for cart count */
        .cart-icon {
            position: relative;
        }

        .cart-icon .badge {
            position: absolute;
            top: -5px;
            right: -10px;
            background-color: #dc3545;
            color: white;
            font-size: 0.8rem;
            padding: 2px 5px;
            border-radius: 50%;
        }

        /* Dropdown styling */
        .dropdown-menu {
            background-color: #343a40;
            border: none;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-10px);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .dropdown-menu.show {
    transform: translateY(0);
    opacity: 1;
}

        .dropdown-item {
            color: #fff;
        }

        .dropdown-item:hover {
            background-color: #ffc107;
            color: #343a40;
        }
    </style>
</head>

<body>

    <!-- Navbar -->

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo getenv('APP_URL'); ?>/index.php">Artisan Marketplace</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($user_role === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link"
                                href="<?php echo getenv('APP_URL'); ?>/admin/admin-dashboard.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo getenv('APP_URL'); ?>/index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo getenv('APP_URL'); ?>/shop.php">Product</a>
                        </li>
                        <li class="nav-item"><a class="nav-link"
                                href="<?php echo getenv('APP_URL'); ?>/admin/admin_approve_products.php">Product Approval</a>
                        </li>
                    <?php elseif ($user_role === 'customer'): ?>
                        <nav class="navbar-expand-lg navbar-dark">
                            <div class="container">
                                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                                    aria-label="Toggle navigation">
                                    <span class="navbar-toggler-icon"></span>
                                </button>
                                <div class="collapse navbar-collapse" id="navbarNav">
                                    <ul class="navbar-nav ms-auto">
                                        <!-- Navigation Links -->
                                        <li class="nav-item"><a class="nav-link"
                                                href="<?php echo getenv('APP_URL'); ?>/index.php">Home</a></li>
                                        <li class="nav-item"><a class="nav-link"
                                                href="<?php echo getenv('APP_URL'); ?>/shop.php">Product</a></li>
                                        <li class="nav-item"><a class="nav-link"
                                                href="<?php echo getenv('APP_URL'); ?>/about.php">About</a></li>
                                        <li class="nav-item"><a class="nav-link"
                                                href="<?php echo getenv('APP_URL'); ?>/contact.php">Contact</a></li>
                                        <li class="nav-item"><a class="nav-link"
                                                href="<?php echo getenv('APP_URL'); ?>/wishlist.php">Wishlist</a></li>

                                        <!-- Cart Icon with Item Count -->
<li class="nav-item position-relative cart-icon">
    <a class="nav-link" href="<?php echo getenv('APP_URL'); ?>/cart.php">
        <i class="fas fa-shopping-cart"></i>
        <?php
        // Display the cart count if items are present
        $cart_count = 0;
        if ($user_logged_in) {
            $sql = "SELECT SUM(quantity) AS cart_count FROM cart WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result) {
                    $cart_data = $result->fetch_assoc();
                    $cart_count = $cart_data['cart_count'] ?? 0;
                }
                $stmt->close();
            }
        }
        ?>
        <?php if ($cart_count > 0): ?>
            <span class="badge bg-danger"><?php echo $cart_count; ?></span>
        <?php endif; ?>
    </a>
</li>


                                        <!-- Profile Dropdown for Customers -->
                                        <?php if ($user_logged_in): ?>
                                            <li class="nav-item dropdown">
                                               
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                                                    <li><a class="dropdown-item" href="profile.php">Personal Info</a></li>
                                                    
    <a class="dropdown-item" href="<?php echo getenv('APP_URL'); ?>/notifications.php">
        Notifications
        <?php if ($unread_count > 0): ?>
            <span class="badge bg-danger"><?php echo $unread_count; ?></span>
        <?php endif; ?>
    </a>
</li>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li><a class="dropdown-item text-danger" href="<?php echo getenv('APP_URL'); ?>/logout.php">Sign Out</a></li>
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </nav>

                    <?php elseif ($user_role === 'artisan'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo getenv('APP_URL'); ?>/index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo getenv('APP_URL'); ?>/shop.php">Product</a>
                        </li>
                        <li class="nav-item"><a class="nav-link"
                                href="<?php echo getenv('APP_URL'); ?>/product_management.php">Product Management</a></li>
                                    <li class="nav-item">
        <a class="nav-link" href="<?php echo getenv('APP_URL'); ?>/artisan/artisan_orders.php">
            Order Management
            <?php if ($notification_count > 0): ?>
                <span class="badge bg-danger"><?php echo $notification_count; ?></span>
            <?php endif; ?>
        </a>
    </li>

                        <li class="nav-item"><a class="nav-link"
                                href="<?php echo getenv('APP_URL'); ?>/artisan/artisan-dashboard.php">Dashboard</a></li>
                    <?php else: ?>
                        <!-- Default Links for Visitors (Not Logged In) -->
                        <li class="nav-item"><a class="nav-link" href="<?php echo getenv('APP_URL'); ?>/index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo getenv('APP_URL'); ?>/shop.php">Product</a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo getenv('APP_URL'); ?>/login.php">Login</a></li>
                    <?php endif; ?>

                    <?php if ($user_logged_in): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="accountDropdown" role="button"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                >
                                <i class="fas fa-user-circle"></i> Account
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                                <li><a class="dropdown-item" href="<?php echo getenv('APP_URL'); ?>/profile.php">Personal
                                        Information</a></li>
                                
    <a class="dropdown-item" href="<?php echo getenv('APP_URL'); ?>/notifications.php">
        Notifications
        <span id="notification-badge" class="badge bg-danger d-none"><?php echo $unread_count; ?></span>
    </a>
</li>
<li>
    <a class="dropdown-item" href="<?php echo getenv('APP_URL'); ?>/order_history.php">
        Order History
        <?php if ($pending_orders_count > 0): ?>
            <span class="badge bg-warning"><?php echo $pending_orders_count; ?></span>
        <?php endif; ?>
    </a>
</li>

                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="<?php echo getenv('APP_URL'); ?>/logout.php">Sign
                                        Out</a></li>
                            </ul>
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
<script>
    function updateNotificationCount() {
        fetch('/artisan_marketplace/public/get_unread_notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const notificationBadge = document.querySelector('#notification-badge');
                    if (data.unread_count > 0) {
                        notificationBadge.textContent = data.unread_count;
                        notificationBadge.classList.remove('d-none');
                    } else {
                        notificationBadge.classList.add('d-none');
                    }
                }
            })
            .catch(error => console.error('Error fetching notifications:', error));
    }

    // Run the function every 10 seconds
    setInterval(updateNotificationCount, 10000);
    // Call it once on page load
    updateNotificationCount();
</script>

</html>