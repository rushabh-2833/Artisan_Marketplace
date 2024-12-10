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

// Fetch orders
$sql = "
    SELECT 
        o.id AS order_id, 
        p.name AS product_name, 
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
    ORDER BY o.created_at DESC;
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $artisan_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .badge {
            font-size: 0.9rem;
        }
        .badge-pending {
            background-color: #ffc107;
        }
        .badge-completed {
            background-color: #28a745;
        }
        .badge-cancelled {
            background-color: #dc3545;
        }
        .badge-shipped {
            background-color: #17a2b8;
        }
        .badge-accepted {
            background-color: #007bff;
        }
        .badge-rejected {
            background-color: #6c757d;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Manage Your Orders</h2>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-hover artisan-order-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product Name</th>
                    <th>Customer Name</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                        <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                        <td>
                            <?php 
                                // Display appropriate badge for status
                                if ($order['status'] === 'completed') {
                                    echo "<span class='badge badge-completed'>Completed</span>";
                                } elseif ($order['status'] === 'cancelled' && !empty($order['cancellation_reason'])) {
                                    echo "<span class='badge badge-cancelled'>Cancelled</span> <br> <small>" . htmlspecialchars($order['cancellation_reason']) . "</small>";
                                } elseif ($order['status'] === 'pending') {
                                    echo "<span class='badge badge-pending'>Pending</span>";
                                } elseif ($order['status'] === 'shipped') {
                                    echo "<span class='badge badge-shipped'>Shipped</span>";
                                } elseif ($order['status'] === 'accepted') {
                                    echo "<span class='badge badge-accepted'>Accepted</span>";
                                } elseif ($order['status'] === 'rejected') {
                                    echo "<span class='badge badge-rejected'>Rejected</span>";
                                } else {
                                    echo "<span class='badge badge-secondary'>" . ucfirst(htmlspecialchars($order['status'])) . "</span>";
                                }
                            ?>
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
    <?php else: ?>
        <div class="alert alert-info text-center">No orders found for your products.</div>
    <?php endif; ?>
</div>

<?php include '../../views/templates/footer.php'; ?>

</body>
</html>
