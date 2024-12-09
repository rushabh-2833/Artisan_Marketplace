<?php
include '../views/templates/header.php';
include '../src/helpers/db_connect.php';  

// Check if a user type is selected
$userType = $_GET['userType'] ?? '';  

// Fetch users based on user type
if ($userType === 'artisan') {
    $sql = "SELECT * FROM users WHERE role = 'artisan'";
} elseif ($userType === 'customer') {
    $sql = "SELECT * FROM users WHERE role = 'customer'";
} else {
    // Default: Fetch all users
    $sql = "SELECT * FROM users";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/artisan_marketplace/public/style/style.css" rel="stylesheet"> <!-- Scoped CSS -->
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Admin Dashboard</h2>

    <!-- Dropdown for selecting user type -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <form method="GET" action="" class="d-flex align-items-center gap-2">
            <label for="userType" class="form-label mb-0">Filter by Role:</label>
            <select class="form-select w-auto" id="userType" name="userType" onchange="this.form.submit()">
                <option value="">All Users</option>
                <option value="artisan" <?php if ($userType === 'artisan') echo 'selected'; ?>>Artisan</option>
                <option value="customer" <?php if ($userType === 'customer') echo 'selected'; ?>>Customer</option>
            </select>
        </form>
    </div>

    <!-- Table to display users -->
    <div class="table-responsive">
        <table class="table admin-user-table">
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <a href="user_details.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-info btn-sm">View Details</a>
                            <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="btn btn-outline-danger btn-sm">Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Function to delete user
function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch(`delete_user.php?id=${userId}`, { method: 'GET' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User deleted successfully');
                    location.reload(); // Refresh the page to see changes
                } else {
                    alert('Failed to delete user');
                }
            })
            .catch(error => console.error('Error deleting user:', error));
    }
}
</script>

<?php
// Close the database connection
$conn->close();
?>

<?php include '../views/templates/footer.php'; ?>
