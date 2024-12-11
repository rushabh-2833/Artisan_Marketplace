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
        p.image_url, 
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .table {
    border-collapse: separate;
    border-spacing: 0 10px;
}

.table th {
    text-transform: uppercase;
    font-size: 0.9rem;
    letter-spacing: 0.05rem;
}

.table tbody tr {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

.badge {
    font-size: 0.8rem;
    text-transform: capitalize;
    padding: 5px 10px;
}

.badge-warning {
    background-color: #ffc107;
    color: #fff;
}

.badge-success {
    background-color: #28a745;
    color: #fff;
}

.badge-danger {
    background-color: #dc3545;
    color: #fff;
}

.table tbody td {
    vertical-align: middle;
}

.img-thumbnail {
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.pagination .page-item.disabled .page-link {
    color: #ccc;
}

.pagination .page-link {
    color: #007bff;
    font-weight: bold;
    border: none;
}

    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../views/templates/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="col-md-9">
    <h2 class="mb-4">Your Orders</h2>

    <!-- Feedback Messages -->
    <?php if (isset($_GET['review_submitted'])): ?>
        <div class="alert alert-success">Your review was submitted successfully!</div>
    <?php elseif (isset($_GET['review_exists'])): ?>
        <div class="alert alert-warning">You have already reviewed this product!</div>
    <?php endif; ?>

    <!-- Status Filter Dropdown -->
    <form method="GET" class="mb-4">
        <label for="status-filter" class="form-label fw-bold">Filter by Status:</label>
        <select id="status-filter" name="status" class="form-select w-25" onchange="this.form.submit()">
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
        <table class="table table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Image</th>
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
                        <td>#<?php echo htmlspecialchars($row['order_id']); ?></td>
                        <td>
                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="Product" class="img-thumbnail" style="width: 60px; height: auto;">
                        </td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                        <td class="fw-bold">$<?php echo htmlspecialchars($row['price']); ?></td>
                        <td>
                            <?php
                                $status = $row['status'];
                                $badgeClass = '';
                                if ($status === 'pending') $badgeClass = 'badge-warning';
                                elseif ($status === 'completed') $badgeClass = 'badge-success';
                                elseif ($status === 'cancelled') $badgeClass = 'badge-danger';
                                echo "<span class='badge $badgeClass px-3 py-2'>" . ucfirst($status) . "</span>";
                            ?>
                            <?php if ($status === 'pending'): ?>
                                <a href="cancel_order.php?order_id=<?php echo $row['order_id']; ?>" class="btn btn-sm btn-danger">Cancel</a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="text-muted">
                                <i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($row['created_at'])); ?><br>
                                <i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($row['created_at'])); ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($status === 'completed' && !empty($row['review_rating'])): ?>
                                <div class="star-rating">
                                    <?php for ($i = 1; $i <= $row['review_rating']; $i++): ?>
                                        <i class="fas fa-star text-warning"></i>
                                    <?php endfor; ?>
                                </div>
                            <?php elseif ($status === 'completed'): ?>
                                <a href="review_product.php?order_id=<?php echo $row['order_id']; ?>&product_id=<?php echo $row['product_id']; ?>" class="btn btn-sm btn-primary">Review</a>
                            <?php else: ?>
                                <span class="text-muted">Not Reviewed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center text-muted">No orders found for the selected status.</p>
    <?php endif; ?>

    <!-- Pagination -->
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&status=<?php echo $status_filter; ?>">Previous</a>
            </li>
            <li class="page-item disabled">
                <span class="page-link">Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></span>
            </li>
            <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&status=<?php echo $status_filter; ?>">Next</a>
            </li>
        </ul>
    </nav>
</div>

    </div>
</div>

<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>

</body><br>
<br> <br>
<?php include '../views/templates/footer.php'; ?>

</html>
