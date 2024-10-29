<?php
session_start();
include '../src/helpers/db_connect.php'; // Adjust path as needed

// Check if the product_id was submitted
if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    // Check if the user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Prepare the statement to remove the product from the wishlist
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $stmt->close();

        // Set a success message
        $_SESSION['message'] = "Product removed from wishlist successfully!";
    } else {
        // User is not logged in
        $_SESSION['message'] = "You need to log in to remove items from your wishlist.";
    }

    // Redirect back to the wishlist page
    header("Location: wishlist.php");
    exit;
} else {
    // If no product_id was submitted
    $_SESSION['message'] = "No product specified.";
    header("Location: wishlist.php");
    exit;
}
?>
