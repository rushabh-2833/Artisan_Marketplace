<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../../views/templates/header.php'; // Include shared header
include '../../src/helpers/db_connect.php'; // Database connection

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'artisan') {
    header("Location: ../../login.php");
    exit;
}

$artisan_id = $_SESSION['user_id'];

// Fetch notifications for the artisan
$sql = "SELECT id, order_id, message, is_read, created_at 
        FROM artisan_notifications 
        WHERE artisan_id = ? 
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $artisan_id);
$stmt->execute();
$result = $stmt->get_result();

// Mark notifications as read when they are displayed
$update_sql = "UPDATE artisan_notifications SET is_read = 1 WHERE artisan_id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("i", $artisan_id);
$update_stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .notification-unread {
            font-weight: bold;
            background-color: #f8d7da; /* Light red background */
        }
        .notification-read {
            background-color: #ffffff; /* White background */
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Notifications</h2>
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order ID</th>
                        <th>Message</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="<?php echo $row['is_read'] ? 'notification-read' : 'notification-unread'; ?>">
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['message']); ?></td>
                            <td><?php echo $row['created_at']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No notifications available.</p>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
