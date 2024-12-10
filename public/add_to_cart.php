<?php
session_start();
include '../src/helpers/db_connect.php'; // Database connection

// Check if a product_id was submitted
if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1; // Default quantity to 1

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
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;

            // Update the quantity in the database
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("iii", $quantity, $user_id, $product_id);
            $stmt->execute();
            $stmt->close();
        } else {
            // Fetch product details from the database
            $stmt = $conn->prepare("SELECT id, name, price, image_url FROM products WHERE id = ? AND approval_status = 'approved'");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();

            if (!$product) {
                $_SESSION['message'] = "Invalid product.";
                header("Location: shop.php");
                exit();
            }

            // Add product to the session cart
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'image_url' => $product['image_url'],
                'quantity' => $quantity
            ];

            // Insert the item into the database
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + ?");
            $stmt->bind_param("iiii", $user_id, $product_id, $quantity, $quantity);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        $_SESSION['message'] = "You need to log in to add items to your cart.";
        header("Location: login.php");
        exit();
    }

    // Set a success message
    $_SESSION['message'] = "Item added to cart successfully!";

    // Redirect back to the referring page
    $referrer = $_SERVER['HTTP_REFERER'] ?? 'shop.php'; // Fallback to shop.php if no referrer
    header("Location: $referrer");
    exit();
} else {
    $_SESSION['message'] = "No product selected.";
    header("Location: shop.php");
    exit();
}
