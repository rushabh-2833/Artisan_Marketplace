<?php
include '../src/helpers/db_connect.php';

$userId = $_GET['id'] ?? '';

if ($userId) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    echo json_encode(['success' => $stmt->affected_rows > 0]);
}
?>
