<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include '../src/helpers/db_connect.php'; // Update path if necessary

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get the order ID from the request
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : null;

if (!$order_id) {
    die("Invalid order ID.");
}

// Check if the order is eligible for cancellation
$sql = "SELECT o.status, p.artisan_id, oi.quantity, CONCAT(u.first_name, ' ', u.last_name) AS customer_name 
        FROM orders o
        INNER JOIN order_items oi ON o.id = oi.order_id
        INNER JOIN products p ON oi.product_id = p.id
        INNER JOIN users u ON o.customer_id = u.id
        WHERE o.id = ? AND o.customer_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL preparation failed: " . $conn->error);
}
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

if ($order['status'] !== 'pending') {
    die("This order cannot be canceled as it is already processed.");
}

// Handle cancellation confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cancellation_reason = isset($_POST['cancellation_reason']) ? trim($_POST['cancellation_reason']) : 'No reason provided';
    $cancellation_description = isset($_POST['cancellation_description']) ? trim($_POST['cancellation_description']) : '';
    $cancelled_at = date("Y-m-d H:i:s");

    // Update the order status to "cancelled"
    $sql = "UPDATE orders 
            SET status = 'cancelled', 
                cancellation_reason = ?, 
                cancellation_description = ?, 
                updated_at = ?, 
                cancellation_requested = ? 
            WHERE id = ? AND customer_id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL preparation failed: " . $conn->error);
    }
    $stmt->bind_param("sssiii", $cancellation_reason, $cancellation_description, $cancelled_at, $cancelled_at, $order_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        // Notify the artisan
        $artisan_id = $order['artisan_id'];
        $quantity = $order['quantity'];
        $customer_name = $order['customer_name'];
        $message = "Order #$order_id has been cancelled by $customer_name. Quantity: $quantity. Reason: $cancellation_reason.";
        $notification_sql = "INSERT INTO artisan_notifications (artisan_id, order_id, message, is_read, created_at) 
                             VALUES (?, ?, ?, 0, ?)";
        $notification_stmt = $conn->prepare($notification_sql);
        $notification_stmt->bind_param("iiss", $artisan_id, $order_id, $message, $cancelled_at);
        $notification_stmt->execute();

        header("Location: order_history.php?cancel=success");
        exit;
    } else {
        die("Error processing cancellation: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cancel Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Cancel Order</h2>
    <form method="POST">
        <div class="mb-3">
            <label for="cancellation_reason" class="form-label">Reason for Cancellation:</label>
            <select id="cancellation_reason" name="cancellation_reason" class="form-select" required>
                <option value="">-- Select a reason --</option>
                <option value="Changed my mind">Changed my mind</option>
                <option value="Found a better price">Found a better price</option>
                <option value="Order placed by mistake">Order placed by mistake</option>
                <option value="Shipping cost too high">Shipping cost too high</option>
                <option value="Delivery time too long">Delivery time too long</option>
                <option value="Incorrect product information">Incorrect product information</option>
                <option value="Preferred payment option not available">Preferred payment option not available</option>
                <option value="Wanted to modify the order">Wanted to modify the order</option>
                <option value="Customer support issue">Customer support issue</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="cancellation_description" class="form-label">Additional Details (Optional):</label>
            <textarea id="cancellation_description" name="cancellation_description" class="form-control" rows="3"></textarea>
        </div>
        <p class="text-danger">Note: Cancellations are subject to our policy. Orders cannot be reinstated once canceled.</p>
        <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
        <a href="order_history.php" class="btn btn-secondary">Back</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
