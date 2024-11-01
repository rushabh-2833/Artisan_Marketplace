<?php 
include 'C:/xampp/htdocs/Artisan_Marketplace/views/templates/header.php'; 

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
   
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Approve Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4">Pending Product Approvals</h2>

    <table class="table table-striped table-bordered">
        <thead class="table-dark">
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
                        <form method="POST" class="d-flex align-items-center">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm me-2">Approve</button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm me-2">Reject</button>
                            <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="Reason for rejection" style="width: 200px;">
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
