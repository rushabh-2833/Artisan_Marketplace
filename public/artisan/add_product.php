<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../../vendor/autoload.php';
use Aws\S3\S3Client;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bucketName = $_ENV['S3_BUCKET_NAME'];
    $s3 = new S3Client([
        'version' => 'latest',
        'region'  => $_ENV['REGION'],
        'credentials' => [
            'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
            'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
        ],
    ]);

    // Use null coalescing to handle undefined array keys
    $name = $_POST['name'] ?? null;
    $description = $_POST['description'] ?? null;
    $price = $_POST['price'] ?? null;
    $stock = $_POST['stock'] ?? null;
    $image = $_FILES['image']['tmp_name'] ?? null;
    $imageName = $_FILES['image']['name'] ?? null;
    $artisan_id = $_SESSION['user_id'] ?? null;

    // Check required fields
    if ($name && $description && $price && $stock && $image) {
        try {
            // Upload image to S3 without ACL
            $result = $s3->putObject([
                'Bucket' => $bucketName,
                'Key'    => 'products/' . $imageName,
                'SourceFile' => $image,
            ]);
            $imageUrl = $result['ObjectURL'];
        } catch (Exception $e) {
            die("Error uploading image: " . $e->getMessage());
        }

        // Database connection and insertion
        $conn = new mysqli('artisan-marketplace.cfao628yky31.us-east-1.rds.amazonaws.com', 'admin', 'Cap-Project24', 'artisan_marketplace');
        $sql = "INSERT INTO products (artisan_id, name, description, price, stock, image_url, approval_status)
                VALUES (?, ?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issdis", $artisan_id, $name, $description, $price, $stock, $imageUrl);

        if ($stmt->execute()) {
            echo "Product added successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
        $conn->close();
    } else {
        echo "Please fill in all required fields and upload an image.";
    }
}
?>

<!-- Add Product Form -->
<form action="product_management.php?action=add" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="name" class="form-label">Product Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" required></textarea>
    </div>
    <div class="mb-3">
        <label for="price" class="form-label">Price</label>
        <input type="number" class="form-control" id="price" name="price" step="0.01" required>
    </div>
    <div class="mb-3">
        <label for="stock" class="form-label">Stock Quantity</label>
        <input type="number" class="form-control" id="stock" name="stock" required>
    </div>
    <div class="mb-3">
        <label for="image" class="form-label">Product Image</label>
        <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Add Product</button>
</form>
