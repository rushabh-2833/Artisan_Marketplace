<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../views/templates/header.php';

// Check if cart and shipping info exist, then clear them for confirmation display
if (!isset($_SESSION['cart']) || !isset($_SESSION['shipping_info'])) {
    header("Location: checkout.php");
    exit;
}

// Clear cart and shipping info for order confirmation display
unset($_SESSION['cart']);
unset($_SESSION['shipping_info']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
</head>
<body>
    <h2>Thank you for your order!</h2>
    <p>Your order has been successfully placed and is now being processed. You will receive an email confirmation shortly.</p>
    <a href="shop.php">Continue Shopping</a>

    
    <?php include '../views/templates/footer.php'; ?>
