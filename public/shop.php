<?php

include '../views/templates/header.php';
include '../src/helpers/db_connect.php'; // Adjust path if needed


if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = []; // Initialize as an empty array
}

// Get the current user ID


// Fetch wishlist items from the database
$wishlist_stmt = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
$wishlist_stmt->bind_param("i", $user_id);
$wishlist_stmt->execute();
$wishlist_result = $wishlist_stmt->get_result();
$wishlist_items = $wishlist_result->fetch_all(MYSQLI_ASSOC);
$wishlist_product_ids = array_column($wishlist_items, 'product_id');

// Check if connection is established
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle filtering (e.g., by category and price range)
$price_min = $_GET['price_min'] ?? 0;
$price_max = $_GET['price_max'] ?? 1000;

// Fetch products with approval status 'approved' and within the price range, including average rating and review count
$sql = "
    SELECT 
        p.*, 
        COALESCE(AVG(r.rating), 0) AS average_rating, 
        COUNT(r.rating) AS total_reviews 
    FROM products p
    LEFT JOIN reviews r ON p.id = r.product_id
    WHERE p.approval_status = 'approved' AND p.price BETWEEN ? AND ?
    GROUP BY p.id
";
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

        .star {
    color: #f39c12;
}
.empty-star {
    color: #ccc;
}

    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Our Products</h2>

      <!-- Search Box -->
      <div class="mb-4">
        <input type="text" id="searchBox" class="form-control" placeholder="Search products..." oninput="fetchSuggestions()">
        <div id="suggestions" class="list-group" style="display: none;"></div>
    </div>

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

    <div class="row">
<?php if ($result->num_rows > 0): ?>
    <?php while ($product = $result->fetch_assoc()): ?>
        <div class="col-md-4">
            <!-- Wrap the entire card with a clickable link -->
            <a href="product_details.php?id=<?php echo $product['id']; ?>" class="text-decoration-none">
                <div class="product-card text-center" style="cursor: pointer;">
                    <!-- Product Image with Heart Icon -->
                    <div class="product-image position-relative">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid">
                        <form action="toggle_wishlist.php" method="POST" class="wishlist-form position-absolute top-0 end-0 p-2">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="wishlist-button bg-transparent border-0">
                                <i class="fas fa-heart heart-icon <?php echo in_array($product['id'], $wishlist_product_ids) ? 'filled' : ''; ?>"></i>
                            </button>
                        </form>
                    </div>
                    <h5 class="mt-3 text-dark"><?php echo htmlspecialchars($product['name']); ?></h5>
                    <p class="text-muted">$<?php echo number_format($product['price'], 2); ?></p>

                    <p>
    <?php for ($i = 1; $i <= 5; $i++): ?>
        <i class="fas fa-star <?php echo $i <= $product['average_rating'] ? 'star' : 'empty-star'; ?>"></i>
    <?php endfor; ?>
    (<?php echo number_format($product['average_rating'], 1); ?>)
</p>


                    <form action="add_to_cart.php" method="POST" class="mt-2">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <button type="submit" class="btn btn-success">Add to Cart</button>
            </form>
                </div>
            </a>
            <!-- Keep the "Add to Cart" button outside the clickable card -->
            
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="col-12 text-center">
        <p>No products found in this price range. Please adjust your filter.</p>
    </div>
<?php endif; ?>
    </div>


<!-- Font Awesome for Icons -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<!-- Styles for the Product Card and Heart Icon -->
<style>
    .product-card {
        position: relative;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        transition: transform 0.3s ease;
        margin-bottom: 20px;
        padding-top: 10px;
    }

    .product-image {
        position: relative;
        overflow: hidden;
    }

    /* Heart Icon Style */
    .wishlist-button {
        position: absolute;
        top: 10px;
        right: 10px;
        background: none;
        border: none;
        cursor: pointer;
        outline: none;
    }

    .heart-icon {
        font-size: 24px;
        color: #ccc; /* Default color for empty heart */
        transition: color 0.3s ease, transform 0.3s ease;
    }

    .heart-icon.filled {
        color: red; /* Color when filled */
    }

    /* Hover effect for the card */
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    /* Heartbeat animation on hover */
    .wishlist-button:hover .heart-icon {
        animation: heartbeat 0.6s ease infinite; /* Beat animation */
    }

    @keyframes heartbeat {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.2);
        }
    }
</style>

<!-- Script for Wishlist Toggle Animation -->
<script>
    // Wishlist Toggle Animation
    document.querySelectorAll('.wishlist-button').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent form submission
            const heartIcon = this.querySelector('.heart-icon');
            heartIcon.classList.toggle('filled'); // Toggle the filled class
            this.closest('form').submit(); // Submit the form
        });
    });

    // AJAX Search Suggestions
    function fetchSuggestions() {
        const searchBox = document.getElementById('searchBox');
        const suggestionsDiv = document.getElementById('suggestions');
        const searchTerm = searchBox.value.trim();

        if (searchTerm.length === 0) {
            suggestionsDiv.style.display = 'none';
            return;
        }

        fetch(`search_suggestions.php?term=${searchTerm}`)
            .then(response => response.json())
            .then(suggestions => {
                suggestionsDiv.innerHTML = '';
                if (suggestions.length > 0) {
                    suggestions.forEach(suggestion => {
                        const item = document.createElement('a');
                        item.href = `shop.php?search=${encodeURIComponent(suggestion)}`;
                        item.classList.add('list-group-item', 'list-group-item-action');
                        item.textContent = suggestion;
                        suggestionsDiv.appendChild(item);
                    });
                    suggestionsDiv.style.display = 'block';
                } else {
                    suggestionsDiv.style.display = 'none';
                }
            })
            .catch(error => console.error('Error fetching suggestions:', error));
    }
</script>



<?php
// Close the statement and connection at the end
$stmt->close();
$conn->close();
?>
<?php include '../views/templates/footer.php'; ?>
            