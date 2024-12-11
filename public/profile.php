<?php include '../views/templates/header.php'; ?>

<?php
session_start();
include '../src/helpers/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user information
$stmt = $conn->prepare("SELECT first_name, last_name, email, phone_number, address FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
    .card {
    border: none;
    border-radius: 12px;
    overflow: hidden;
}

.card-header {
    background: linear-gradient(45deg, #007bff, #0056b3);
    font-size: 1.5rem;
    font-weight: bold;
    text-transform: uppercase;
}

.card-body {
    font-family: 'Roboto', sans-serif;
    background-color: #f8f9fa;
    border-top: 5px solid #007bff;
}

.table th {
    width: 25%;
    font-weight: normal;
    font-size: 1rem;
    text-align: left;
    color: #6c757d;
    vertical-align: middle;
}

.table td {
    font-weight: 500;
    font-size: 1.1rem;
    vertical-align: middle;
    color: #212529;
}

.btn-success {
    background-color: #28a745;
    border: none;
    font-size: 1.1rem;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-success:hover {
    background-color: #218838;
    transform: scale(1.05);
}

.btn-success i {
    font-size: 1.2rem;
}

.table-borderless tr:not(:last-child) {
    border-bottom: 1px dashed #dee2e6;
}

.shadow-lg {
    box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.1);
}

</style>
<body>
<?php include '../views/templates/navbar.php'; ?>
<div class="container mt-5">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../views/templates/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="col-md-9">
    <div class="card shadow-lg rounded">
        <div class="card-header bg-gradient-primary text-white text-center py-4">
            <h3 class="mb-0">Your Profile</h3>
        </div>
        <div class="card-body py-4 px-5">
            <table class="table table-borderless mb-4">
                <tbody>
                    <tr>
                        <th class="text-secondary">First Name:</th>
                        <td class="text-dark fw-bold"><?php echo htmlspecialchars($user['first_name']); ?></td>
                    </tr>
                    <tr>
                        <th class="text-secondary">Last Name:</th>
                        <td class="text-dark fw-bold"><?php echo htmlspecialchars($user['last_name']); ?></td>
                    </tr>
                    <tr>
                        <th class="text-secondary">Email:</th>
                        <td class="text-dark fw-bold"><?php echo htmlspecialchars($user['email']); ?></td>
                    </tr>
                    <tr>
                        <th class="text-secondary">Phone:</th>
                        <td class="text-dark fw-bold"><?php echo htmlspecialchars($user['phone_number']); ?></td>
                    </tr>
                    <tr>
                        <th class="text-secondary">Address:</th>
                        <td class="text-dark fw-bold"><?php echo htmlspecialchars($user['address']); ?></td>
                    </tr>
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                <a href="edit_profile.php" class="btn btn-success btn-lg px-4 py-2 shadow-sm">
                    <i class="fas fa-edit me-2"></i>Edit Profile
                </a>
            </div>
        </div>
    </div>
</div>

    </div>
</div>

</body>
</html>
