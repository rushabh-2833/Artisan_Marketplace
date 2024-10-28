<?php
session_start();
include '../views/templates/header.php'; // Include header
include '../src/helpers/db_connect.php'; // Database connection

// Check if connection is established
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle filtering (e.g., by category and price range)
$price_min = $_GET['price_min'] ?? 0;
$price_max = $_GET['price_max'] ?? 1000;

// Fetch products with approval status 'approved' and within the price range
$sql = "SELECT * FROM products WHERE approval_status = 'approved' AND price BETWEEN ? AND ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("dd", $price_min, $price_max);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-card { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; }
        .product-image img { max-width: 100%; height: auto; }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Our Products</h2>

    <!-- Display success message if product was added -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success text-center">
            <?php
            echo $_SESSION['message'];
            unset($_SESSION['message']); // Clear the message after displaying
            ?>
        </div>
    <?php endif; ?>

    <!-- Filter Form -->
    <form method="GET" action="shop.php" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="price_min">Min Price</label>
                <input type="number" name="price_min" class="form-control" value="<?php echo htmlspecialchars($price_min); ?>" min="0">
            </div>
            <div class="col-md-4">
                <label for="price_max">Max Price</label>
                <input type="number" name="price_max" class="form-control" value="<?php echo htmlspecialchars($price_max); ?>" min="0">
            </div>
            <div class="col-md-4 align-self-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </div>
    </form>

    <!-- Product Grid -->
    <div class="row">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($product = $result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="product-card text-center">
                        <div class="product-image">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        <h5 class="mt-3"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="text-muted">$<?php echo number_format($product['price'], 2); ?></p>
                        <form action="add_to_cart.php" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="btn btn-success">Add to Cart</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <p>No products found in this price range. Please adjust your filter.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Close the statement and connection at the end
$stmt->close();
$conn->close();
?>
</body>
</html>
            