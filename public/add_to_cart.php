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
        // Increment quantity if already in the cart
        $_SESSION['cart'][$product_id]['quantity'] += 1;
    } else {
        // Fetch product details for the cart item from the database
        $stmt = $conn->prepare("SELECT id, name, price, image_url FROM products WHERE id = ? AND approval_status = 'approved'");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        // Add product to cart with quantity set to 1
        $_SESSION['cart'][$product_id] = [
            'name' => $product['name'],
            'price' => $product['price'],
            'image_url' => $product['image_url'],
            'quantity' => 1
        ];
    }

    // Set a success message in session
    $_SESSION['message'] = "Item added to cart successfully!";
    
    // Redirect back to the shop page
    header("Location: shop.php");
    exit();
}
?>
