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

// Fetch notification count
$notification_count = 0;
$notification_query = "SELECT COUNT(*) AS unread_count FROM artisan_notifications WHERE artisan_id = ? AND is_read = 0";
$notification_stmt = $conn->prepare($notification_query);
$notification_stmt->bind_param("i", $artisan_id);
$notification_stmt->execute();
$notification_result = $notification_stmt->get_result();
if ($notification_result) {
    $notification_data = $notification_result->fetch_assoc();
    $notification_count = $notification_data['unread_count'] ?? 0;
}

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
if (!$stmt) {
    die("SQL preparation failed: " . $conn->error);
}
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
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Manage Your Orders</h2>
        
        <table class="table table-bordered">
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
                        <td><?php echo $order['product_name']; ?></td>
                        <td><?php echo $order['customer_name']; ?></td>
                        <td><?php echo $order['quantity']; ?></td>
                        <td>$<?php echo $order['total_price']; ?></td>
                        <td>
                            <?php 
                                echo ucfirst($order['status']);
                                if ($order['status'] === 'cancelled' && !empty($order['cancellation_reason'])) {
                                    echo " - Reason: " . htmlspecialchars($order['cancellation_reason']);
                                }
                            ?>
                        </td>
                        <td>
                            <?php if ($order['status'] !== 'cancelled'): ?>
                                <form method="POST" action="update_order_status.php">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <select name="order_status" class="form-select form-select-sm">
                                        <option value="pending" <?php if ($order['status'] === 'pending') echo 'selected'; ?>>Pending</option>
                                        <option value="accepted" <?php if ($order['status'] === 'accepted') echo 'selected'; ?>>Accepted</option>
                                        <option value="rejected" <?php if ($order['status'] === 'rejected') echo 'selected'; ?>>Rejected</option>
                                        <option value="shipped" <?php if ($order['status'] === 'shipped') echo 'selected'; ?>>Shipped</option>
                                        <option value="completed" <?php if ($order['status'] === 'completed') echo 'selected'; ?>>Completed</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm mt-1">Update</button>
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
</body>
</html>
