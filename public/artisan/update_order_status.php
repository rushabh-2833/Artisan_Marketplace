<?php
session_start();
include '../../src/helpers/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $order_status = $_POST['order_status'];

    // Check if the column name in the table is 'id' instead of 'order_id'
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if (!$stmt) {
        die("SQL error: " . $conn->error);
    }

    $stmt->bind_param("si", $order_status, $order_id);
    $stmt->execute();
    $stmt->close();

    // Redirect back to the order management page
    header("Location: artisan_orders.php");
    exit;
}
?>
