<?php 
include 'C:/xampp/htdocs/Artisan_Marketplace/views/templates/header.php'; 



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
    header("Location: admin_approve_products.php"); // Redirect to refresh the page
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Approve Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style/style.css" rel="stylesheet"> 
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4">Pending Product Approvals</h2>

    <div class="table-responsive">
        <table class="table admin-approval-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                        <td>
                            <form method="POST" class="d-flex align-items-center gap-2">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" name="action" value="approve" class="btn btn-outline-success btn-sm">Approve</button>
                                <button type="submit" name="action" value="reject" class="btn btn-outline-danger btn-sm">Reject</button>
                                <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="Rejection reason" aria-label="Rejection reason">
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
