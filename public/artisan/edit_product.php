
<?php
require __DIR__ . '/../../vendor/autoload.php'; // AWS SDK for PHP
use Aws\S3\S3Client;

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'artisan') {
    header("Location: login.php");
    exit;
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// AWS S3 configuration
$bucketName = $_ENV['S3_BUCKET_NAME'];
$s3 = new S3Client([
    'version' => 'latest',
    'region'  => $_ENV['REGION'],
    'credentials' => [
        'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
        'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
    ],
]);

// Fetch product data to edit
$artisan_id = $_SESSION['user_id'];
$product_id = $_GET['product_id'];

$conn = new mysqli('artisan-marketplace.cfao628yky31.us-east-1.rds.amazonaws.com', 'admin', 'Cap-Project24', 'artisan_marketplace');
$sql = "SELECT * FROM products WHERE id = ? AND artisan_id = ? AND approval_status IN ('pending', 'rejected')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product_id, $artisan_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die("Product not found or cannot be edited.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $imageUrl = $product['image_url']; // Default to current image

    if (!empty($_FILES['image']['tmp_name'])) {
        $image = $_FILES['image']['tmp_name'];
        $imageName = $_FILES['image']['name'];

        // Upload new image to S3
        try {
            $result = $s3->putObject([
                'Bucket' => $bucketName,
                'Key'    => 'products/' . $imageName,
                'SourceFile' => $image,
                'ACL'    => 'public-read',
            ]);
            $imageUrl = $result['ObjectURL'];
        } catch (Exception $e) {
            die("Error uploading image: " . $e->getMessage());
        }
    }

    // Update product details in the database
    $sql = "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image_url = ?, approval_status = 'pending' WHERE id = ? AND artisan_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdisii", $name, $description, $price, $stock, $imageUrl, $product_id, $artisan_id);

    if ($stmt->execute()) {
        echo "Product updated successfully and is pending approval!";
    } else {
        echo "Error: " . $stmt->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Edit Product</h2>

        <form action="product_management.php?action=edit&product_id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo $product['name']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" required><?php echo $product['description']; ?></textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="stock" class="form-label">Stock Quantity</label>
                <input type="number" class="form-control" id="stock" name="stock" value="<?php echo $product['stock']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Product Image</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                <p>Current Image: <a href="<?php echo $product['image_url']; ?>" target="_blank">View Image</a></p>
            </div>
            <button type="submit" class="btn btn-primary w-100">Update Product</button>
        </form>
    </div>
</body>
</html>
