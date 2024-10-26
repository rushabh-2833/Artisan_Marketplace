<?php include '../views/templates/header.php'; ?>


<table>
    <tr>
        <th>Product</th>
        <th>Price</th>
        <th>Image</th>
    </tr>
    <?php
    $result = $conn->query("SELECT * FROM products WHERE status = 'Approved'");
    while ($product = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$product['name']}</td>
                <td>{$product['price']}</td>
                <td><img src='{$product['image']}' width='100'></td>
              </tr>";
    }
    ?>
</table>
