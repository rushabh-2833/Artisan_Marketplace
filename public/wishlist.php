<?php
include '../views/templates/header.php';
include '../src/helpers/db_connect.php'; // Adjust path as needed

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo '<div class="container mt-5 text-center"><h2>Please log in to view your wishlist.</h2></div>';
    exit;
}

// Get the current user ID
$user_id = $_SESSION['user_id'];

// Fetch wishlist items from the database
$stmt = $conn->prepare("SELECT p.id as product_id, p.name, p.price, p.image_url FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Wishlist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
/* Wishlist Container */
.wishlist-container h2 {
    text-align: center;
    font-weight: bold;
    margin-bottom: 20px;
    color: #2c3e50;
}

/* Product Card */
.product-card {
    border: 1px solid #e6e6e6;
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background-color: #ffffff;
    position: relative;
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

.product-card.visible {
    opacity: 1;
    transform: translateY(0);
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

/* Product Image */
.product-image img {
    max-height: 300px;
    object-fit: contain;
    border-radius: 8px;
    width: 100%;
}

/* Heart Icon */
.heart-container {
    position: absolute;
    top: 10px;
    right: 10px;
}

.heart {
    background: transparent;
    border: none;
    outline: none;
    cursor: pointer;
    font-size: 1.5rem;
    color: #ccc;
    transition: color 0.3s ease, transform 0.3s ease;
}

.heart.filled {
    color: #ff4d4d;
}

.heart:hover {
    transform: scale(1.2);
    color: #ff6b6b;
}

/* Product Name and Price */
.product-card h5 {
    font-size: 1.2rem;
    font-weight: bold;
    color: #2c3e50;
    margin-top: 15px;
}

.product-card p {
    font-size: 1rem;
    color: #7f8c8d;
}

/* Empty Wishlist Message */
.empty-wishlist {
    text-align: center;
    margin-top: 50px;
    font-size: 1.2rem;
    color: #7f8c8d;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
    60% {
        transform: translateY(-5px);
    }
}

.heart.bounce {
    animation: bounce 0.5s;
}

.loading-spinner {
    border: 3px solid rgba(0, 0, 0, 0.1);
    border-top: 3px solid #3498db;
    border-radius: 50%;
    width: 15px;
    height: 15px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.wishlist-title {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 1rem;
    color: #2c3e50;
}

.wishlist-title + p {
    font-size: 1rem;
    color: #7f8c8d;
    margin-bottom: 2rem;
}

wishlist-divider {
    width: 0;
    height: 4px;
    background-color: #3498db;
    margin: 0.5rem auto 1.5rem auto;
    border-radius: 2px;
    transition: width 0.8s ease-in-out;
}

.wishlist-divider.animated {
    width: 80px;
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
}

    </style>
</head>
<body>
<div class="container mt-5">
<div class="wishlist-container">
<div class="text-center">
    <h2 class="wishlist-title">Your Wishlist</h2>
    <div class="wishlist-divider"></div>
    <p class="text-muted">Review your favorite items and make your purchase decisions!</p>
</div>


    <?php if ($result->num_rows > 0): ?>
        <div class="row">
            <?php while ($item = $result->fetch_assoc()): ?>
                <div class="col-md-3">
                    <div class="product-card text-center">
                        <div class="product-image">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <form action="remove_from_wishlist.php" method="POST" class="heart-container">
                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                <button type="submit" class="heart filled" id="heart-<?php echo $item['product_id']; ?>">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </form>
                        </div>
                        <h5 class="mt-3"><?php echo htmlspecialchars($item['name']); ?></h5>
                        <p class="text-muted">$<?php echo number_format($item['price'], 2); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="empty-wishlist">Your wishlist is empty.</p>
    <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
<script>
    const hearts = document.querySelectorAll('.heart');
    hearts.forEach(heart => {
        heart.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent form submission
            
            // Check if the heart is filled
            const isFilled = this.classList.contains('filled');

            // If it's filled, submit the form to remove from wishlist
            if (isFilled) {
                this.closest('form').submit(); // Submit the form to remove from wishlist
            } else {
                this.classList.toggle('filled'); // Toggle filled state for non-filled hearts
            }
        });
    });

    document.querySelectorAll('.wishlist-button').forEach(button => {
    button.addEventListener('click', (event) => {
        const heartIcon = event.currentTarget.querySelector('.heart');
        heartIcon.classList.add('bounce');
        setTimeout(() => heartIcon.classList.remove('bounce'), 500);
    });
});

window.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.product-card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('visible');
        }, index * 150); // Delay for each card
    });
});

document.querySelectorAll('.wishlist-button').forEach(button => {
    button.addEventListener('click', (event) => {
        const spinner = document.createElement('div');
        spinner.classList.add('loading-spinner');
        event.currentTarget.appendChild(spinner);

        setTimeout(() => {
            spinner.remove(); // Simulate the end of an action
        }, 1000); // Adjust based on actual request time
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const divider = document.querySelector('.wishlist-divider');
    if (divider) {
        divider.classList.add('animated');
    }
});


</script>

<?php
// Close the database connection
$conn->close();
?>

<?php include '../views/templates/footer.php'; ?>
