<?php
session_start();


require __DIR__ . '/../../vendor/autoload.php'; // AWS SDK for PHP
use Aws\S3\S3Client;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$bucketName = $_ENV['S3_BUCKET_NAME'];
$s3 = new S3Client([
    'version' => 'latest',
    'region'  => $_ENV['REGION'],
    'credentials' => [
        'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
        'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
    ],
]);


$name = $_POST['name'];
$description = $_POST['description'];
$price = $_POST['price'];
$stock = $_POST['stock'];
$artisan_id = $_SESSION['artisan_id'];  // Artisan logged in

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

$conn = new mysqli('localhost', 'root', '', 'marketplace');
$sql = "INSERT INTO products (artisan_id, name, description, price, stock, image, status)
        VALUES ('$artisan_id', '$name', '$description', '$price', '$stock', '$imageUrl', 'Pending Approval')";

if ($conn->query($sql) === TRUE) {
    echo "Product added successfully!";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
