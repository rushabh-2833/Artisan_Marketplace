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
/* Section Title */
.section-title {
    font-size: 2.5rem;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 2rem;
}

/* Search Box */
.search-input {
    padding: 0.8rem 1rem;
    font-size: 1.1rem;
    border-radius: 50px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease;
}

.search-input:focus {
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    outline: none;
}

/* Suggestions Dropdown */
.suggestions-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    z-index: 1000;
    background: #ffffff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    overflow: hidden;
}

/* Success Alert */
.fade-out-alert {
    animation: fadeOut 3s forwards;
}

@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
        visibility: hidden;
    }
}

/* Filter Form */
.filter-form .form-label {
    font-size: 1rem;
    font-weight: bold;
    color: #2c3e50;
}

.filter-form .form-control {
    padding: 0.8rem 1rem;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease;
}

.filter-form .form-control:focus {
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    outline: none;
}

.btn-filter {
    background: #2c3e50;
    color: #ffffff;
    font-weight: bold;
    border-radius: 10px;
    padding: 0.8rem;
    transition: background 0.3s ease, transform 0.2s ease;
}

.btn-filter:hover {
    background: #1a252f;
    transform: translateY(-2px);
}

/* Product Card */
.product-card {
    border: 1px solid #e6e6e6;
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background-color: #ffffff;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

/* Product Image */
.product-image img {
    max-height: 200px;
    object-fit: cover;
    border-radius: 8px;
}

/* Heart Icon */
.heart-icon {
    font-size: 1.5rem;
    color: #ccc;
    transition: color 0.3s ease, transform 0.3s ease;
}

.heart-icon.filled {
    color: #ff4d4d;
}

.wishlist-button:hover .heart-icon {
    transform: scale(1.2);
    color: #ff4d4d;
}

/* Product Title */
.product-card h5 {
    font-size: 1.2rem;
    font-weight: bold;
    margin-top: 10px;
    color: #2c3e50;
}

/* Product Price */
.product-card p {
    margin: 0;
    font-size: 1rem;
    color: #7f8c8d;
}

/* Rating Stars */
.fa-star {
    color: #ffd700;
}

.empty-star {
    color: #e0e0e0;
}

/* Add to Cart Button */
.btn-success {
    background-color: #28a745;
    border: none;
    color: white;
    font-weight: bold;
    padding: 10px 20px;
    border-radius: 50px;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

.btn-success:hover {
    background-color: #218838;
    transform: translateY(-2px);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .product-card {
        padding: 10px;
    }

    .product-image img {
        max-height: 150px;
    }

    .product-card h5 {
        font-size: 1rem;
    }

    .btn-success {
        padding: 8px 15px;
    }
}



    </style>
</head>
<body>
<div class="container mt-5">
<h2 class="text-center mb-4 section-title">Our Products</h2>

<!-- Search Box -->
<div class="mb-4 position-relative">
    <input type="text" id="searchBox" class="form-control search-input" placeholder="Search products..." oninput="fetchSuggestions()">
    <div id="suggestions" class="list-group suggestions-dropdown" style="display: none;"></div>
</div>

<!-- Display success message if product was added -->
<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success text-center fade-out-alert">
        <?php
        echo $_SESSION['message'];
        unset($_SESSION['message']); // Clear the message after displaying
        ?>
    </div>
<?php endif; ?>

<!-- Filter Form -->
<form method="GET" action="shop.php" class="mb-4 filter-form">
    <div class="row g-3">
        <div class="col-md-4">
            <label for="price_min" class="form-label">Min Price</label>
            <input type="number" name="price_min" class="form-control" value="<?php echo htmlspecialchars($price_min); ?>" min="0">
        </div>
        <div class="col-md-4">
            <label for="price_max" class="form-label">Max Price</label>
            <input type="number" name="price_max" class="form-control" value="<?php echo htmlspecialchars($price_max); ?>" min="0">
        </div>
        <div class="col-md-4 align-self-end">
            <button type="submit" class="btn btn-filter w-100">Filter</button>
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
            