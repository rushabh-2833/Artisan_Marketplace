<?php include '../views/templates/header.php'; ?>

<?php
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'artisan') {
    header("Location: login.php");
    exit;
}

$action = $_GET['action'] ?? 'list'; // Default action is to list products
$product_id = $_GET['product_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css"> <!-- Link to custom styles -->
    <title>Product Management</title>
</head>
<body>
<div class="container mt-5">
    <div class="text-center mb-5">
        <h2 class="page-heading">Product Management</h2>
    </div>

    <!-- Display different content based on the action -->
    <div class="card p-4 shadow-sm">
        <?php
        if ($action === 'add') {
            include '../public/artisan/add_product.php'; // Display add product form
        } elseif ($action === 'edit' && $product_id) {
            include '../public/artisan/edit_product.php'; // Display edit product form
        } elseif ($action === 'delete' && $product_id) {
            include '../public/artisan/delete_product.php'; // Handle delete product action
        } else {
            // Default action: List products with Edit and Delete options
            include '../public/artisan/list_products.php';
        }
        ?>
    </div>

    <!-- "Add New Product" button only displayed on product list page -->
    <?php if ($action === 'list'): ?>
        <div class="text-center mt-4">
            <a href="product_management.php?action=add" class="btn btn-custom">Add New Product</a>
        </div>
    <?php endif; ?>
</div>

<?php include '../views/templates/footer.php'; ?>
</body>
</html>
