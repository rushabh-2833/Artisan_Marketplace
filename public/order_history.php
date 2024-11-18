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

// Get the total number of orders for pagination
$total_orders_query = "SELECT COUNT(*) AS total_orders FROM orders WHERE customer_id = ?";
$total_stmt = $conn->prepare($total_orders_query);
$total_stmt->bind_param("i", $user_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result()->fetch_assoc();
$total_orders = $total_result['total_orders'];
$total_pages = ceil($total_orders / $orders_per_page);

// Construct the base SQL query
$sql = "
    SELECT 
        o.id AS order_id, 
        p.id AS product_id, 
        p.name AS product_name, 
        oi.quantity, 
        oi.price, 
        o.status, 
        o.created_at, 
        o.cancellation_reason, 
        o.updated_at AS cancellation_requested,
        r.rating AS review_rating 
    FROM orders o
    INNER JOIN order_items oi ON o.id = oi.order_id
    INNER JOIN products p ON oi.product_id = p.id
    LEFT JOIN (
        SELECT user_id, product_id, MAX(created_at) as latest_review, rating
        FROM reviews
        WHERE user_id = ?
        GROUP BY user_id, product_id
    ) r ON o.customer_id = r.user_id AND oi.product_id = r.product_id
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
    $stmt->bind_param("iisii", $user_id, $user_id, $status_filter, $orders_per_page, $offset);
} else {
    $stmt->bind_param("iiii", $user_id, $user_id, $orders_per_page, $offset);
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .star {
            color: #f39c12; /* Gold color for filled stars */
            margin-right: 2px;
        }
        .empty-star {
            color: #ccc; /* Light gray for empty stars */
        }
        .star-rating {
            display: inline-flex;
            gap: 2px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Your Orders</h2>

    <!-- Feedback Messages -->
    <?php if (isset($_GET['review_submitted'])): ?>
        <div class="alert alert-success">Your review was submitted successfully!</div>
    <?php elseif (isset($_GET['review_exists'])): ?>
        <div class="alert alert-warning">You have already reviewed this product!</div>
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
        <table class="table table-bordered">
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
                        <td>
                            <?php 
                                if ($row['status'] === 'cancelled') {
                                    echo '<i class="bi bi-x-circle text-danger" data-bs-toggle="tooltip" title="Order cancelled"></i> Cancelled';
                                } elseif ($row['status'] === 'pending') {
                                    echo '<i class="bi bi-hourglass-split text-warning" data-bs-toggle="tooltip" title="Order pending"></i> Pending';
                                    echo '<a href="cancel_order.php?order_id=' . $row['order_id'] . '" class="btn btn-danger btn-sm ms-2">Cancel Order</a>';
                                } elseif ($row['status'] === 'accepted') {
                                    echo '<i class="bi bi-check-circle text-primary" data-bs-toggle="tooltip" title="Order accepted"></i> Accepted';
                                } elseif ($row['status'] === 'rejected') {
                                    echo '<i class="bi bi-x-circle text-danger" data-bs-toggle="tooltip" title="Order rejected"></i> Rejected';
                                } elseif ($row['status'] === 'shipped') {
                                    echo '<i class="bi bi-truck text-info" data-bs-toggle="tooltip" title="Order shipped"></i> Shipped';
                                } elseif ($row['status'] === 'completed') {
                                    echo '<i class="bi bi-check-circle text-success" data-bs-toggle="tooltip" title="Order completed"></i> Completed';
                                }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td>
                            <?php if ($row['status'] === 'completed' && !empty($row['review_rating'])): ?>
                                <!-- Display Review Stars -->
                                <div class="star-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= $row['review_rating'] ? 'star' : 'empty-star'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            <?php elseif ($row['status'] === 'completed'): ?>
                                <!-- Allow Review for Completed Orders -->
                                <a href="review_product.php?order_id=<?php echo $row['order_id']; ?>&product_id=<?php echo $row['product_id']; ?>" class="btn btn-primary btn-sm">Review Product</a>
                            <?php else: ?>
                                Not Reviewed
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You have no orders matching this status.</p>
    <?php endif; ?>

    <!-- Pagination Controls -->
    <div class="d-flex justify-content-between mt-4">
        <a href="?page=<?php echo max(1, $current_page - 1); ?>&status=<?php echo $status_filter; ?>" class="btn btn-primary" <?= $current_page <= 1 ? 'disabled' : ''; ?>>Previous</a>
        <span>Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></span>
        <a href="?page=<?php echo min($current_page + 1, $total_pages); ?>&status=<?php echo $status_filter; ?>" class="btn btn-primary" <?= $current_page >= $total_pages ? 'disabled' : ''; ?>>Next</a>
    </div>
</div>

<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>
</body>
</html>
