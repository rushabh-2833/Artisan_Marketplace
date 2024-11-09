<?php
include '../views/templates/header.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'artisan') {
    header("Location: login.php");
    exit;
}

$artisan_id = $_SESSION['user_id'];
$conn = new mysqli('artisan-marketplace.cfao628yky31.us-east-1.rds.amazonaws.com', 'admin', 'Cap-Project24', 'artisan_marketplace');

// Fetch orders for this artisan
$sql = "
    SELECT o.order_id, p.name AS product_name, u.name AS customer_name, o.quantity, 
           o.total_price, o.order_status, o.created_at 
    FROM orders o
    INNER JOIN products p ON o.product_id = p.id
    INNER JOIN users u ON o.customer_id = u.id
    WHERE o.artisan_id = ?
    ORDER BY o.created_at DESC
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
                        <td><?php echo ucfirst($order['order_status']); ?></td>
                        <td>
                            <form method="POST" action="update_order_status.php">
                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                <select name="order_status" class="form-select form-select-sm">
                                    <option value="Pending" <?php if ($order['order_status'] === 'Pending') echo 'selected'; ?>>Pending</option>
                                    <option value="Accepted" <?php if ($order['order_status'] === 'Accepted') echo 'selected'; ?>>Accepted</option>
                                    <option value="Rejected" <?php if ($order['order_status'] === 'Rejected') echo 'selected'; ?>>Rejected</option>
                                    <option value="Shipped" <?php if ($order['order_status'] === 'Shipped') echo 'selected'; ?>>Shipped</option>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm mt-1">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
