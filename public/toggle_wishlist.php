<?php
session_start();
include '../src/helpers/db_connect.php'; // Adjust path as needed

if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $user_id = $_SESSION['user_id']; // Assuming the user is logged in

    // Check if the product is already in the wishlist
    $stmt = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // If the product is in the wishlist, remove it
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $_SESSION['message'] = "Product removed from wishlist!";
    } else {
        // If the product is not in the wishlist, add it
        $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $_SESSION['message'] = "Product added to wishlist!";
    }

    // Redirect back to the shop page or wherever you want
    header("Location: shop.php");
    exit;
}
?>
