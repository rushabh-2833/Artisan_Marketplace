<?php include '../views/templates/header.php'; ?>
<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../src/helpers/db_connect.php'; // Update the path if necessary

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Define the number of orders per page
$orders_per_page = 5;

// Get the current page number, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the offset for the SQL query
$offset = ($current_page - 1) * $orders_per_page;

// Get the status filter from the request
$status_filter = isset($_GET['status']) && !empty($_GET['status']) ? $_GET['status'] : null;

// Construct the base SQL query
$sql = "
    SELECT 
        o.id AS order_id, 
        p.id AS product_id, 
        p.name AS product_name, 
        oi.quantity, 
        oi.price, 
        o.status, 
        o.created_at
    FROM orders o
    INNER JOIN order_items oi ON o.id = oi.order_id
    INNER JOIN products p ON oi.product_id = p.id
    WHERE o.customer_id = ?
";

// Append the status filter condition if applicable
if ($status_filter) {
    $sql .= " AND o.status = ?";
}
$sql .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL preparation failed: " . $conn->error);
}

// Bind parameters based on whether a filter is applied
if ($status_filter) {
    $stmt->bind_param("isii", $user_id, $status_filter, $orders_per_page, $offset);
} else {
    $stmt->bind_param("iii", $user_id, $orders_per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles/order_history.css" rel="stylesheet"> <!-- Link to specific CSS -->
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Your Order History</h2>

    <!-- Feedback Messages -->
    <?php if (isset($_GET['cancel'])): ?>
        <?php if ($_GET['cancel'] === 'success'): ?>
            <div class="alert alert-success">Order was canceled successfully.</div>
        <?php elseif ($_GET['cancel'] === 'error'): ?>
            <div class="alert alert-danger">There was an error processing your cancellation. Please try again.</div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Status Filter Dropdown -->
    <form method="GET" class="mb-3">
        <label for="status-filter" class="form-label">Filter by Status:</label>
        <select id="status-filter" name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All</option>
            <option value="pending" <?php if ($status_filter === 'pending') echo 'selected'; ?>>Pending</option>
            <option value="accepted" <?php if ($status_filter === 'accepted') echo 'selected'; ?>>Accepted</option>
            <option value="rejected" <?php if ($status_filter === 'rejected') echo 'selected'; ?>>Rejected</option>
            <option value="shipped" <?php if ($status_filter === 'shipped') echo 'selected'; ?>>Shipped</option>
            <option value="completed" <?php if ($status_filter === 'completed') echo 'selected'; ?>>Completed</option>
            <option value="cancelled" <?php if ($status_filter === 'cancelled') echo 'selected'; ?>>Cancelled</option>
        </select>
    </form>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered order-history-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
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
                        <td>
                            <?php if ($row['status'] === 'pending'): ?>
                                <a href="cancel_order.php?order_id=<?php echo $row['order_id']; ?>" class="btn btn-danger btn-sm">Cancel Order</a>
                            <?php elseif ($row['status'] === 'completed'): ?>
                                <a href="review_product.php?order_id=<?php echo $row['order_id']; ?>" class="btn btn-primary btn-sm">Review Product</a>
                            <?php else: ?>
                                No Actions
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You have no orders matching this status.</p>
    <?php endif; ?>
</div>
</body>
</html>

<?php include '../views/templates/footer.php'; ?>
