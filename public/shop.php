<?php
// Include header and database connection
include '../views/templates/header.php';
include '../src/helpers/db_connect.php'; // Adjust path if needed

// Check if connection is established
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>

<table>
    <tr>
        <th>Product</th>
        <th>Price</th>
        <th>Image</th>
    </tr>
    <?php
    // Fetch products with approval status 'approved'
    $result = $conn->query("SELECT * FROM products WHERE approval_status = 'approved'");
    
    while ($product = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$product['name']}</td>
                <td>{$product['price']}</td>
                <td><img src='{$product['image_url']}' width='100'></td>
              </tr>";
    }

    // Close the database connection
    $conn->close();
    ?>
</table>
