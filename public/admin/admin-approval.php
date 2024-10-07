
<table>
    <tr>
        <th>Product</th>
        <th>Artisan</th>
        <th>Actions</th>
    </tr>
    <?php
    $result = $conn->query("SELECT * FROM products WHERE status = 'Pending Approval'");
    while ($product = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$product['name']}</td>
                <td>{$product['artisan_id']}</td>
                <td>
                    <a href='approve_product.php?id={$product['product_id']}'>Approve</a>
                    <a href='reject_product.php?id={$product['product_id']}'>Reject</a>
                </td>
              </tr>";
    }
    ?>
</table>
