<?php
include '../../views/templates/header.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../../src/helpers/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'artisan') {
    header("Location: ../login.php");
    exit;
}

$artisan_id = $_SESSION['user_id'];

// Define the number of notifications per page
$notifications_per_page = 6;

// Get the current page number, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the offset for the SQL query
$offset = ($current_page - 1) * $notifications_per_page;

// Fetch total number of notifications for pagination
$total_notifications_query = "SELECT COUNT(*) AS total_notifications FROM artisan_notifications WHERE artisan_id = ?";
$total_stmt = $conn->prepare($total_notifications_query);
$total_stmt->bind_param("i", $artisan_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result()->fetch_assoc();
$total_notifications = $total_result['total_notifications'];
$total_pages = ceil($total_notifications / $notifications_per_page);

// Fetch notifications with LIMIT and OFFSET for pagination
$sql = "
    SELECT an.id AS notification_id, an.message, an.created_at, an.is_read, an.cancellation_reason,
           p.name AS product_name, p.image_url, o.status
    FROM artisan_notifications an
    LEFT JOIN orders o ON an.order_id = o.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE an.artisan_id = ?
    ORDER BY an.created_at DESC
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $artisan_id, $notifications_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Artisan Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .notification-item {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f9f9f9;
        }
        .notification-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }
        .notification-details {
            flex-grow: 1;
        }
        .notification-status {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .notification-time {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .product-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .badge {
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .badge-pending {
            background-color: #ffc107;
            color: #000;
        }
        .badge-completed {
            background-color: #28a745;
            color: #fff;
        }
        .badge-cancelled {
            background-color: #dc3545;
            color: #fff;
        }
        .badge-shipped {
            background-color: #17a2b8;
            color: #fff;
        }
        .badge-accepted {
            background-color: #007bff;
            color: #fff;
        }
        .badge-rejected {
            background-color: #6c757d;
            color: #fff;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../../views/templates/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Notifications</h3>
                </div>
                <div class="card-body">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($notification = $result->fetch_assoc()): ?>
                            <div class="notification-item">
                                <img src="<?php echo htmlspecialchars($notification['image_url'] ?? '/default_image.jpg'); ?>" alt="Product Image" class="notification-image">
                                <div class="notification-details">
                                    <p class="product-title"><?php echo htmlspecialchars($notification['product_name'] ?? 'Notification'); ?></p>
                                    <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                    <?php if (!empty($notification['cancellation_reason'])): ?>
                                        <p><strong>Cancellation Reason:</strong> <?php echo htmlspecialchars($notification['cancellation_reason']); ?></p>
                                    <?php endif; ?>
                                    <p class="notification-time">
                                        <i class="fas fa-clock"></i> <?php echo date('F j, Y g:i A', strtotime($notification['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="notification-status">
                                    <?php
                                    // Display status badge with icons
                                    if ($notification['status'] === 'cancelled') {
                                        echo "<span class='badge badge-cancelled'><i class='fas fa-times-circle'></i> Cancelled</span>";
                                    } elseif ($notification['status'] === 'pending') {
                                        echo "<span class='badge badge-pending'><i class='fas fa-hourglass-half'></i> Pending</span>";
                                    } elseif ($notification['status'] === 'accepted') {
                                        echo "<span class='badge badge-accepted'><i class='fas fa-check-circle'></i> Accepted</span>";
                                    } elseif ($notification['status'] === 'shipped') {
                                        echo "<span class='badge badge-shipped'><i class='fas fa-truck'></i> Shipped</span>";
                                    } elseif ($notification['status'] === 'completed') {
                                        echo "<span class='badge badge-completed'><i class='fas fa-check-circle'></i> Completed</span>";
                                    } elseif ($notification['status'] === 'rejected') {
                                        echo "<span class='badge badge-rejected'><i class='fas fa-ban'></i> Rejected</span>";
                                    }
                                    ?>
                                    <span class="badge bg-secondary"><?php echo $notification['is_read'] ? 'Read' : 'Unread'; ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>

                        <!-- Pagination Controls -->
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo max(1, $current_page - 1); ?>">Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $current_page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo min($current_page + 1, $total_pages); ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    <?php else: ?>
                        <p>No notifications found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
