<?php include '../views/templates/header.php'; ?>
<?php
session_start();
include '../src/helpers/db_connect.php'; // Adjust the path if needed

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch notifications for the logged-in user
$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Notifications</h2>
        <?php if ($result->num_rows > 0): ?>
            <ul class="list-group">
                <?php while ($notification = $result->fetch_assoc()): ?>
                    <li class="list-group-item <?php echo $notification['is_read'] ? 'text-muted' : 'fw-bold'; ?>">
                        <?php echo htmlspecialchars($notification['message']); ?>
                        <span class="text-secondary small d-block"><?php echo $notification['created_at']; ?></span>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No notifications found.</p>
        <?php endif; ?>
    </div>

    <!-- Add JavaScript to Mark Notifications as Read -->
    <script>
        // Mark notifications as read when the page loads
        fetch('../src/helpers/mark_notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log("Notifications marked as read.");
                }
            })
            .catch(error => console.error('Error:', error));
    </script>
</body>
</html>
