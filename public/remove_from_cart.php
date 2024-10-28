<?php
session_start();
include '../src/helpers/db_connect.php'; // Adjust path as needed

// Check if the product_id was submitted
if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    // Check if the product exists in the session cart
    if (isset($_SESSION['cart'][$product_id])) {
        // Remove from the session cart
        unset($_SESSION['cart'][$product_id]);
        
        // Check if the user is logged in
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            
            // Remove from the database cart table
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Redirect back to the cart page
    header("Location: cart.php");
    exit;
}
