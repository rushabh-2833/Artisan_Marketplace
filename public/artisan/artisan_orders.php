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

$artisan_id = $_SESSION['user_id'];
include $_SERVER['DOCUMENT_ROOT'] . '/Artisan_Marketplace/src/helpers/db_connect.php';

// Mark notifications as read
$mark_read_query = "UPDATE artisan_notifications SET is_read = 1 WHERE artisan_id = ?";
$mark_read_stmt = $conn->prepare($mark_read_query);
$mark_read_stmt->bind_param("i", $artisan_id);
$mark_read_stmt->execute();

// Fetch orders
$sql = "
    SELECT o.id AS order_id, p.name AS product_name, 
           CONCAT(u.first_name, ' ', u.last_name) AS customer_name, 
           oi.quantity, oi.price * oi.quantity AS total_price, 
           o.status, o.cancellation_reason, o.created_at
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
    <link href="../../styles/artisan_order.css" rel="stylesheet"> <!-- Scoped styles -->
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Manage Your Orders</h2>

    <table class="table artisan-order-table">
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
                    <td><?php echo $order['order_id']; ?></td>
                    <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                    <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                    <td>
                        <?php 
                            if ($order['status'] === 'completed') {
                                echo "<span class='badge bg-success'>Completed</span>";
                            } elseif ($order['status'] === 'cancelled' && !empty($order['cancellation_reason'])) {
                                echo "<span class='badge bg-danger'>Cancelled</span> <small>" . htmlspecialchars($order['cancellation_reason']) . "</small>";
                            } else {
                                echo "<span class='badge bg-secondary'>" . ucfirst(htmlspecialchars($order['status'])) . "</span>";
                            }
                        ?>
                    </td>
                    <td>
                        <?php if ($order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
                            <form method="POST" action="update_order_status.php" class="d-flex align-items-center gap-2">
                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
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
<?php include '../../views/templates/footer.php'; ?>
</body>
</html>
