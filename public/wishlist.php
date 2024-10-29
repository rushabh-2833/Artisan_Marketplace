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
        .product-card {
            position: relative;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.2s;
            margin-bottom: 20px;
        }

        .product-image img {
            width: 100%;
            height: auto;
        }

        .heart {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 50px;
            height: 50px;
            cursor: pointer;
            transition: color 0.3s ease;
            background-color: transparent; /* No background color */
            border: none; /* No border */
        }

        .heart.filled {
            color: red;
            font-size: 30px;
        }

        .heart {
            font-size: 30px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Your Wishlist</h2>
    
    <?php if ($result->num_rows > 0): ?>
        <div class="row">
            <?php while ($item = $result->fetch_assoc()): ?>
                <div class="col-md-4">
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
        <p>Your wishlist is empty.</p>
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
</script>

<?php
// Close the database connection
$conn->close();
?>

</body>
</html>
