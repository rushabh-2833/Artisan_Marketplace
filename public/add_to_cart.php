<?php
session_start();
include '../src/helpers/db_connect.php'; // Database connection

// Check if a product_id was submitted
if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    // Check if cart exists in session, if not, create it
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if the product is already in the cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += 1; // Increment quantity
    } else {
        // Fetch product details for the cart item
        $stmt = $conn->prepare("SELECT id, name, price, image_url FROM products WHERE id = ? AND approval_status = 'approved'");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        // Add product to cart
        $_SESSION['cart'][$product_id] = [
            'name' => $product['name'],
            'price' => $product['price'],
            'image_url' => $product['image_url'],
            'quantity' => 1
        ];
    }

    // Redirect back to the shop page with a success message
    header("Location: shop.php?added_to_cart=true");
    exit;
}
?>

