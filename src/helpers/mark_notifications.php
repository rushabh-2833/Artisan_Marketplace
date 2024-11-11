<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Update notifications to mark as read
$sql = "UPDATE notifications SET is_read = TRUE WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Notifications marked as read.']);
} else {
    echo json_encode(['success' => false, 'message' => 'No notifications to update.']);
}

$stmt->close();
$conn->close();
?>
