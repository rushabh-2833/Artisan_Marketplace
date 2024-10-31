<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../views/templates/header.php';
include '../src/helpers/db_connect.php';

// Ensure cart and user data are available
if (!isset($_SESSION['cart']) || empty($_SESSION['cart']) || !isset($_SESSION['user_id']) || !isset($_SESSION['shipping_info'])) {
    header("Location: checkout.php");
    exit;
}

// Calculate total price
$total_price = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_price += $item['price'] * $item['quantity'];
}

$user_id = $_SESSION['user_id'];
$shipping_info = $_SESSION['shipping_info'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Summary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h2 class="mb-4">Order Summary</h2>
    <div class="row">
        <!-- Left Column: Order Summary -->
        <div class="col-md-8">
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

        <!-- Right Column: Shipping Information and Confirm Order Button -->
        <div class="col-md-4">
            <h4 class="mt-4">Shipping Information</h4>
            <p><?php echo htmlspecialchars($shipping_info); ?></p>
            <button class="btn btn-success mt-4 w-100" onclick="showPaymentForm()">Confirm Order</button>
        </div>
    </div>

    <!-- Payment Form (Initially Hidden) -->
    <div class="row mt-5" id="paymentForm" style="display: none;">
        <div class="col-md-8 offset-md-2">
            <h4>Payment Details</h4>
            <form method="post" action="payment.php" onsubmit="return validatePaymentForm()">
                <div class="mb-3">
                    <label class="form-label">Payment Type</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="debitCard" name="payment_type" value="debit">
                        <label class="form-check-label" for="debitCard">Debit Card</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="creditCard" name="payment_type" value="credit">
                        <label class="form-check-label" for="creditCard">Credit Card</label>
                    </div>
                    <div class="text-danger" id="paymentTypeError"></div>
                </div>

                <div class="mb-3">
                    <label for="nameOnCard" class="form-label">Name on Card</label>
                    <input type="text" id="nameOnCard" name="name_on_card" class="form-control" placeholder="Enter the name on your card">
                    <div class="text-danger" id="nameOnCardError"></div>
                </div>

                <div class="mb-3">
                    <label for="cardNumber" class="form-label">Card Number</label>
                    <input type="text" id="cardNumber" name="card_number" class="form-control" placeholder="Enter your card number">
                    <div class="text-danger" id="cardNumberError"></div>
                </div>

                <div class="mb-3">
                    <label for="cvv" class="form-label">CVV</label>
                    <input type="text" id="cvv" name="cvv" class="form-control" placeholder="Enter CVV">
                    <div class="text-danger" id="cvvError"></div>
                </div>

                <div class="mb-3">
                    <label for="expiryDate" class="form-label">Expiry Date</label>
                    <input type="month" id="expiryDate" name="expiry_date" class="form-control">
                    <div class="text-danger" id="expiryDateError"></div>
                </div>

                <button type="submit" class="btn btn-primary">Complete Payment</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Show the payment form when "Confirm Order" is clicked
    function showPaymentForm() {
        document.getElementById("paymentForm").style.display = "block";
    }

    // JavaScript validation for the payment form
    function validatePaymentForm() {
        let isValid = true;

        document.getElementById("paymentTypeError").textContent = "";
        document.getElementById("nameOnCardError").textContent = "";
        document.getElementById("cardNumberError").textContent = "";
        document.getElementById("cvvError").textContent = "";
        document.getElementById("expiryDateError").textContent = "";

        const paymentType = document.querySelector('input[name="payment_type"]:checked');
        if (!paymentType) {
            document.getElementById("paymentTypeError").textContent = "Please select a payment type.";
            isValid = false;
        }

        const nameOnCard = document.getElementById("nameOnCard").value.trim();
        if (nameOnCard === "") {
            document.getElementById("nameOnCardError").textContent = "Please enter the name on your card.";
            isValid = false;
        }

        const cardNumber = document.getElementById("cardNumber").value.trim();
        if (!/^\d{16}$/.test(cardNumber)) {
            document.getElementById("cardNumberError").textContent = "Please enter a valid 16-digit card number.";
            isValid = false;
        }

        const cvv = document.getElementById("cvv").value.trim();
        if (!/^\d{3}$/.test(cvv)) {
            document.getElementById("cvvError").textContent = "Please enter a valid 3-digit CVV.";
            isValid = false;
        }

        const expiryDate = document.getElementById("expiryDate").value.trim();
        if (expiryDate === "") {
            document.getElementById("expiryDateError").textContent = "Please select an expiry date.";
            isValid = false;
        }

        return isValid;
    }
</script>
</body>
</html>
