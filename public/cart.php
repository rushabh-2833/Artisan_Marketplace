    <?php
    include '../src/helpers/db_connect.php';
    include '../views/templates/header.php';

    // If the user is logged in, load the cart from the database
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Fetch cart items from the database
        $stmt = $conn->prepare("SELECT products.id, products.name, products.price, products.image_url, cart.quantity 
                                FROM cart 
                                JOIN products ON cart.product_id = products.id 
                                WHERE cart.user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Load items into the session cart
        $_SESSION['cart'] = [];
        while ($item = $result->fetch_assoc()) {
            $_SESSION['cart'][$item['id']] = [
                'name' => $item['name'],
                'price' => $item['price'],
                'image_url' => $item['image_url'],
                'quantity' => $item['quantity']
            ];
        }
        $stmt->close();
    }

    // Check if the cart is empty
    if (empty($_SESSION['cart'])) {
        echo '<div class="container mt-5 text-center"><h2>Your Cart is Empty</h2></div>';
        exit;
    }

    // Calculate total price
    $totalPrice = 0;
    // Calculate cart count for badge
    $cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));

    ?>
   
   
   
   <style>
        /* General Styles */


/* Table Responsiveness */
.table {
    width: 100%;
    border-collapse: collapse;
}

/* Hide certain columns on smaller devices */
@media (max-width: 768px) {
    .table th:nth-child(2),  
    .table td:nth-child(2),  
    .table th:nth-child(5),  
    .table td:nth-child(5),  
    .table th:nth-child(6),  
    .table td:nth-child(6) {  
        display: none;
    }
}

/* Add horizontal scrolling for table */
.table-responsive {
    overflow-x: auto;
}

/* Image Resizing */
.table img {
    max-width: 40px;
    max-height: 40px;
    object-fit: cover;
}

/* Responsive Buttons */
.btn-custom {
    background-color: #12372A;
    color: #fff;
    font-family: "Poppins", sans-serif;
    padding: 10px 20px;
    border-radius: 5px;
    text-transform: uppercase;
    font-weight: bold;
    border: none;
    transition: 0.3s ease;
}

.btn-custom:hover {
    background-color: #0d2c20;
    color: #fff;
}

@media only screen and (max-width: 576px) {
    .btn-custom {
        width: 100%; 
        text-align: center;
    }

    .d-flex {
        flex-direction: column;
    }

    .justify-content-between {
        justify-content: center;
    }

    .align-items-center {
        align-items: center;
    }
}

/* Quantity Button Styling */
.btn-secondary {
    background-color: #ddd;
    border: none;
    font-size: 14px;
    padding: 6px 10px;
}

.btn-secondary:hover {
    background-color: #ccc;
}

/* Cart Table for Small Devices */
@media only screen and (max-width: 576px) {
    .table {
        font-size: 14px;
    }

    .table th, .table td {
        padding: 5px;
    }

    .table thead {
        display: none;  
    }

    .table tr {
        display: block;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        padding: 10px;
    }

    .table td {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
        text-align: left;
    }

    .table td:last-child {
        margin-bottom: 0;
    }

    .table td:before {
        content: attr(data-label);
        flex-basis: 50%;
        text-align: left;
        font-weight: bold;
    }
}

    </style>

    <div class="container mt-5">
        <h2>Your Shopping Cart</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Image</th>
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
                    </td>
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
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="text-right">Grand Total: $<?php echo number_format($totalPrice, 2); ?></h4>
            <!-- Checkout Button -->
            <a href="checkout.php" class="btn btn-custom">Proceed to Checkout</a>
        </div>
    </div>

    <?php include '../views/templates/footer.php'; ?>
