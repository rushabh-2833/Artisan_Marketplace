<?php
session_start();

// Check if the product_id was submitted
if (isset($_POST['product_id']) && isset($_POST['action'])) {
    $product_id = $_POST['product_id'];
    $action = $_POST['action'];

    // Check if cart exists in session
    if (isset($_SESSION['cart'][$product_id])) {
        if ($action === 'increase') {
            $_SESSION['cart'][$product_id]['quantity'] += 1; // Increase quantity
        } elseif ($action === 'decrease') {
            $_SESSION['cart'][$product_id]['quantity'] -= 1; // Decrease quantity
            // Remove item from cart if quantity is zero or less
            if ($_SESSION['cart'][$product_id]['quantity'] <= 0) {
                unset($_SESSION['cart'][$product_id]);
            }
        }
    }

    // Redirect to cart page
    header("Location: cart.php");
    exit;
}
?>
