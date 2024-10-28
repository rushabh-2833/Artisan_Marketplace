<?php
session_start();
include '../src/helpers/db_connect.php'; // Database connection

// Check if a product_id was submitted
if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    // Ensure the session cart exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if the user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Check if the product already exists in the session cart
        if (isset($_SESSION['cart'][$product_id])) {
            // Increment quantity if already in the cart
            $_SESSION['cart'][$product_id]['quantity'] += 1;

            // Update the quantity in the database
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $stmt->close();
        } else {
            // Fetch product details from the database
            $stmt = $conn->prepare("SELECT id, name, price, image_url FROM products WHERE id = ? AND approval_status = 'approved'");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();

            // Add product to the session cart
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'image_url' => $product['image_url'],
                'quantity' => 1
            ];

            // Insert the item into the database
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Set a success message
    $_SESSION['message'] = "Item added to cart successfully!";
    
    // Redirect back to the shop page
    header("Location: shop.php");
    exit();
}
