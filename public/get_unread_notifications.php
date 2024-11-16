<?php
session_start();
include '../src/helpers/db_connect.php';

$response = ['success' => false, 'unread_count' => 0];

if (isset($_SESSION['user_id'])) {
    $sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $data = $result->fetch_assoc();
            $response['unread_count'] = $data['unread_count'] ?? 0;
            $response['success'] = true;
        }
        $stmt->close();
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
