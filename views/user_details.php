<?php

include '../src/helpers/db_connect.php'; // Ensure the database connection

// Check if user ID is provided
if (!isset($_GET['id'])) {
    echo '<div class="container mt-5 text-center"><h2>User not found.</h2></div>';
    exit;
}

// Fetch user details from the database
$user_id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="container mt-5 text-center"><h2>User not found.</h2></div>';
    exit;
}

$user = $result->fetch_assoc();
?>

<?php include '../views/templates/header.php'; ?>

<div class="container mt-5">
    <h2>User Details</h2>
    <table class="table">
        <tr>
            <th>First Name</th>
            <td><?php echo htmlspecialchars($user['first_name']); ?></td>
        </tr>
        <tr>
            <th>Last Name</th>
            <td><?php echo htmlspecialchars($user['last_name']); ?></td>
        </tr>
        <tr>
            <th>Phone</th>
            <td><?php echo htmlspecialchars($user['phone_number']); ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
        </tr>
        <tr>
            <th>Role</th>
            <td><?php echo htmlspecialchars($user['role']); ?></td>
        </tr>
    </table>
</div>

<?php
$conn->close();
?>


