<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../../views/templates/header.php';

// Redirect if not artisan
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'artisan') {
    header("Location: login.php");
    exit;
}

$artisan_id = $_SESSION['user_id'] ?? null; // Get artisan ID from session
if (!$artisan_id) {
    die("Artisan ID is not set. Please log in again.");
}

include $_SERVER['DOCUMENT_ROOT'] . '/Artisan_Marketplace/src/helpers/db_connect.php';

// Pagination settings
$orders_per_page = 6;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max($current_page, 1); // Ensure page is at least 1
$offset = ($current_page - 1) * $orders_per_page;

// Fetch total orders count
$count_sql = "
    SELECT COUNT(*) AS total_orders 
    FROM orders o
    INNER JOIN order_items oi ON o.id = oi.order_id
    INNER JOIN products p ON oi.product_id = p.id
    WHERE p.artisan_id = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $artisan_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_orders = $count_result->fetch_assoc()['total_orders'] ?? 0;
$total_pages = ceil($total_orders / $orders_per_page);

// Fetch orders with sorting and pagination
$sql = "
    SELECT 
        o.id AS order_id, 
        p.name AS product_name, 
        p.image_url, 
        CONCAT(u.first_name, ' ', u.last_name) AS customer_name, 
        oi.quantity, 
        oi.price * oi.quantity AS total_price, 
        o.status, 
        o.cancellation_reason, 
        o.created_at
    FROM orders o
    INNER JOIN order_items oi ON o.id = oi.order_id
    INNER JOIN products p ON oi.product_id = p.id
    INNER JOIN users u ON o.customer_id = u.id
    WHERE p.artisan_id = ?
    ORDER BY 
        FIELD(o.status, 'pending', 'accepted', 'shipped', 'completed', 'cancelled'), 
        o.created_at DESC
    LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $artisan_id, $orders_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .badge {
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .badge-pending {
            background-color: #ffc107;
            color: black;
        }
        .badge-completed {
            background-color: #28a745;
            color: white;
        }
        .badge-cancelled {
            background-color: #dc3545;
            color: white;
        }
        .badge-shipped {
            background-color: #17a2b8;
            color: white;
        }
        .badge-accepted {
            background-color: #007bff;
            color: white;
        }
        .badge-rejected {
            background-color: #6c757d;
            color: white;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        .date-time {
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Manage Your Orders</h2>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered artisan-order-table">
                <thead class="table-dark">
                    <tr>
                        <th>Order ID</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Customer Name</th>
                        <th>Quantity</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Date & Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td>
                                <img src="<?php echo htmlspecialchars($order['image_url']); ?>" alt="Product Image" class="product-image">
                            </td>
                            <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                            <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                            <td>
                                <?php 
                                    // Display appropriate badge with icons for status
                                    if ($order['status'] === 'completed') {
                                        echo "<span class='badge badge-completed'><i class='fas fa-check-circle'></i> Completed</span>";
                                    } elseif ($order['status'] === 'cancelled' && !empty($order['cancellation_reason'])) {
                                        echo "<span class='badge badge-cancelled'><i class='fas fa-times-circle'></i> Cancelled</span> <br> <small>" . htmlspecialchars($order['cancellation_reason']) . "</small>";
                                    } elseif ($order['status'] === 'pending') {
                                        echo "<span class='badge badge-pending'><i class='fas fa-hourglass-half'></i> Pending</span>";
                                    } elseif ($order['status'] === 'shipped') {
                                        echo "<span class='badge badge-shipped'><i class='fas fa-truck'></i> Shipped</span>";
                                    } elseif ($order['status'] === 'accepted') {
                                        echo "<span class='badge badge-accepted'><i class='fas fa-check'></i> Accepted</span>";
                                    } elseif ($order['status'] === 'rejected') {
                                        echo "<span class='badge badge-rejected'><i class='fas fa-ban'></i> Rejected</span>";
                                    } else {
                                        echo "<span class='badge badge-secondary'><i class='fas fa-question-circle'></i> Unknown</span>";
                                    }
                                ?>
                            </td>
                            <td>
                                <div class="date-time">
                                    <i class="fas fa-calendar-alt"></i> <?php echo date('F j, Y', strtotime($order['created_at'])); ?><br>
                                    <i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($order['created_at'])); ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
                                    <form method="POST" action="update_order_status.php" class="d-flex align-items-center gap-2">
                                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                                        <select name="order_status" class="form-select form-select-sm">
                                            <option value="pending" <?php if ($order['status'] === 'pending') echo 'selected'; ?>>Pending</option>
                                            <option value="accepted" <?php if ($order['status'] === 'accepted') echo 'selected'; ?>>Accepted</option>
                                            <option value="rejected" <?php if ($order['status'] === 'rejected') echo 'selected'; ?>>Rejected</option>
                                            <option value="shipped" <?php if ($order['status'] === 'shipped') echo 'selected'; ?>>Shipped</option>
                                            <option value="completed" <?php if ($order['status'] === 'completed') echo 'selected'; ?>>Completed</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">No actions available</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <li class="page-item <?php if ($current_page <= 1) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($i == $current_page) echo 'active'; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php if ($current_page >= $total_pages) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo $current_page + 1; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    <?php else: ?>
        <div class="alert alert-info text-center">No orders found for your products.</div>
    <?php endif; ?>
</div>

<?php include '../../views/templates/footer.php'; ?>

</body>
</html>
