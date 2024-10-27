<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli('artisan-marketplace.cfao628yky31.us-east-1.rds.amazonaws.com', 'admin', 'Cap-Project24', 'artisan_marketplace');
$sql = "SELECT * FROM products WHERE approval_status = 'pending'";
$result = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $action = $_POST['action'];
    $reason = $_POST['rejection_reason'] ?? '';

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE products SET approval_status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $product_id);
    } else {
        $stmt = $conn->prepare("UPDATE products SET approval_status = 'rejected', rejection_reason = ? WHERE id = ?");
        $stmt->bind_param("si", $reason, $product_id);
    }
    $stmt->execute();
    header("Location: admin_approve_products.php");
}

?>

<table>
    <tr><th>Product</th><th>Description</th><th>Action</th></tr>
    <?php while ($product = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $product['name']; ?></td>
            <td><?php echo $product['description']; ?></td>
            <td>
                <form method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <button name="action" value="approve">Approve</button>
                    <button name="action" value="reject">Reject</button>
                    <input type="text" name="rejection_reason" placeholder="Reason for rejection">
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
