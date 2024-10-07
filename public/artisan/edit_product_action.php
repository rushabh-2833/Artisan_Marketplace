<?php
require  __DIR__ . '/../../vendor/autoload.php';
use Aws\S3\S3Client;

$bucketName = $_ENV['S3_BUCKET_NAME'];
$s3 = new S3Client([/*...*/]);

$conn = new mysqli('localhost', 'root', '', 'marketplace');
$product_id = $_POST['product_id'];
$name = $_POST['name'];
$description = $_POST['description'];
$price = $_POST['price'];
$stock = $_POST['stock'];

// Handle image upload
$imageUrl = "";
if ($_FILES['image']['tmp_name']) {
    $image = $_FILES['image']['tmp_name'];
    $imageName = $_FILES['image']['name'];

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

$sql = "UPDATE products SET name='$name', description='$description', price='$price', stock='$stock'";
if ($imageUrl) {
    $sql .= ", image='$imageUrl'";
}
$sql .= " WHERE product_id='$product_id'";

if ($conn->query($sql) === TRUE) {
    echo "Product updated successfully!";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
