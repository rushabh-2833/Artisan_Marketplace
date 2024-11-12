<?php include '../views/templates/header.php'; ?>
<?php
session_start();
include '../src/helpers/db_connect.php'; // Update the path if necessary

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch completed orders for the user
$sql = "
    SELECT o.id AS order_id, p.name AS product_name, oi.quantity, oi.price, o.status, o.created_at
    FROM orders o
    INNER JOIN order_items oi ON o.id = oi.order_id
    INNER JOIN products p ON oi.product_id = p.id
    WHERE o.customer_id = ? AND o.status = 'completed'
    ORDER BY o.created_at DESC
";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Your Completed Orders</h2>
    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                        <td>$<?php echo htmlspecialchars($row['price']); ?></td>
                        <td><?php echo ucfirst(htmlspecialchars($row['status'])); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You have no completed orders.</p>
    <?php endif; ?>
</div>
</body>
</html>
