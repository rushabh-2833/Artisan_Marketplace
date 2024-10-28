<?php
session_start();
include '../src/helpers/db_connect.php'; // Adjust path as needed

// Check if the product_id and action were submitted
if (isset($_POST['product_id']) && isset($_POST['action'])) {
    $product_id = $_POST['product_id'];
    $action = $_POST['action'];

    // Check if user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Adjust quantity based on action
        if ($action === 'increase') {
            // Increase quantity in database
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $stmt->close();

            // Increase quantity in session
            $_SESSION['cart'][$product_id]['quantity'] += 1;
        } elseif ($action === 'decrease') {
            // Decrease quantity in database
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity - 1 WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $stmt->close();

            // Decrease quantity in session and remove if zero or less
            $_SESSION['cart'][$product_id]['quantity'] -= 1;
            if ($_SESSION['cart'][$product_id]['quantity'] <= 0) {
                unset($_SESSION['cart'][$product_id]);

                // Remove from database if quantity is zero
                $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->bind_param("ii", $user_id, $product_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    } else {
        // For guests, modify session cart only
        if (isset($_SESSION['cart'][$product_id])) {
            if ($action === 'increase') {
                $_SESSION['cart'][$product_id]['quantity'] += 1;
            } elseif ($action === 'decrease') {
                $_SESSION['cart'][$product_id]['quantity'] -= 1;
                if ($_SESSION['cart'][$product_id]['quantity'] <= 0) {
                    unset($_SESSION['cart'][$product_id]);
                }
            }
        }
    }

    // Redirect to cart page
    header("Location: cart.php");
    exit;
}
?>
