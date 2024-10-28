<?php
session_start();
include '../views/templates/header.php'; // Include header

// Check if the cart is empty
if (empty($_SESSION['cart'])) {
    echo '<div class="container mt-5 text-center"><h2>Your Cart is Empty</h2></div>';
    exit;
}

// Calculate total price
$totalPrice = 0;
?>

<div class="container mt-5">
    <h2>Your Shopping Cart</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Image</th> <!-- Added Image column -->
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_SESSION['cart'] as $product_id => $item): 
                $itemTotal = $item['price'] * $item['quantity'];
                $totalPrice += $itemTotal;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td>
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 50px; height: auto;">
                </td> <!-- Display product image -->
                <td>$<?php echo number_format($item['price'], 2); ?></td>
                <td>
                    <form action="update_cart.php" method="POST" class="d-flex align-items-center">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <button type="submit" name="action" value="decrease" class="btn btn-secondary btn-sm">-</button>
                        <span class="mx-2"><?php echo $item['quantity']; ?></span>
                        <button type="submit" name="action" value="increase" class="btn btn-secondary btn-sm">+</button>
                    </form>
                </td>
                <td>$<?php echo number_format($itemTotal, 2); ?></td>
                <td>
                    <form action="remove_from_cart.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <h4 class="text-right">Grand Total: $<?php echo number_format($totalPrice, 2); ?></h4>
</div>

<?php include '../views/templates/footer.php'; // Include footer if applicable ?>
