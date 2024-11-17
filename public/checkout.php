<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once '../src/helpers/db_connect.php';

// Check if the user is logged in
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: login.php");
    exit;
}

// Check if the cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<div class='text-center my-5'><h1 style='font-size: 2.5em; color: #333;'>Your cart is empty.</h1></div>";
    exit;
}

// Fetch the user's saved address
$saved_address = '';
$stmt = $conn->prepare("SELECT address FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $saved_address = $row['address'];
}
$stmt->close();

// Calculate the total price of the cart
$total_price = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_price += $item['price'] * $item['quantity'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['shipping_info'])) {
        $shipping_info = $_POST['shipping_info'];

        // Insert the order into the orders table
        $stmt = $conn->prepare("INSERT INTO orders (customer_id, total_price, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("id", $user_id, $total_price);
        $stmt->execute();
        $order_id = $stmt->insert_id; // Get the generated order ID
        $stmt->close();

        // Insert each cart item into the order_items table
        foreach ($_SESSION['cart'] as $product_id => $item) {
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $order_id, $product_id, $item['quantity'], $item['price']);
            $stmt->execute();
            $stmt->close();
        }

        // Clear the cart in session and database
        unset($_SESSION['cart']);
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        // Redirect to confirmation page
        header("Location: checkout_confirmation.php");
        exit;
    } else {
        echo "<div class='text-center my-5'><h1 style='font-size: 2em; color: red;'>Shipping information is required!</h1></div>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h2 class="mb-4">Checkout</h2>
    <div class="row">
        <!-- Left Column: Order Summary -->
        <div class="col-md-6">
            <h4>What You've Ordered</h4>
            <div class="mb-4">
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="card mb-3">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" class="img-fluid rounded-start" style="max-height: 120px; object-fit: contain;" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                                    <p class="card-text">Quantity: <?php echo $item['quantity']; ?></p>
                                    <p class="card-text">Price: $<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <p><strong>Total Price:</strong> $<?php echo number_format($total_price, 2); ?></p>
        </div>

        <!-- Right Column: Shipping Information -->
        <div class="col-md-6">
            <form method="post" class="needs-validation" novalidate>
                <h4>Shipping Information</h4>
                <div class="mb-3">
                    <label for="shipping_info" class="form-label"><strong>Your Default Address</strong></label>
                    <textarea name="shipping_info" id="shipping_info" class="form-control" placeholder="Enter your address here..." required><?php echo htmlspecialchars($saved_address); ?></textarea>
                    <div class="invalid-feedback">
                        Please enter your shipping address.
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Place Order</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Add client-side validation
    (function () {
        'use strict';
        const forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>
<?php include '../views/templates/footer.php'; ?>
</body>
</html>
