<?php
session_start();
include '../../src/helpers/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $order_status = $_POST['order_status'];

    // Fetch the customer ID associated with the order
    $stmt = $conn->prepare("SELECT customer_id FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $user_id = $order['customer_id'];
    $stmt->close();

    // Update the order status
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $order_status, $order_id);
    $stmt->execute();
    $stmt->close();

    // Insert a notification for the customer
    $message = "Your order #$order_id status has been updated to '$order_status'.";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, order_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $order_id, $message);
    $stmt->execute();
    $stmt->close();

    

    // Redirect back to the order management page
    header("Location: artisan_orders.php");
    exit;
}
?>
