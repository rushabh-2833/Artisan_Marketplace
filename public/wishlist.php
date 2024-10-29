<?php
session_start();
include '../views/templates/header.php'; // Include header

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo '<div class="container mt-5 text-center"><h2>Please log in to view your wishlist.</h2></div>';
    exit;
}

// Fetch wishlist items
$user_id = $_SESSION['user_id'];
$sql = "SELECT p.id, p.name, p.price, p.image_url FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mt-5">
    <h2>Your Wishlist</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Image</th>
                <th>Price</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 50px; height: auto;"></td>
                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                    <td>
                        <form action="toggle_wishlist.php" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Remove from Wishlist</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../views/templates/footer.php'; // Include footer if applicable ?>
