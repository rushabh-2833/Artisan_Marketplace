<?php
include '../src/helpers/db_connect.php';

$role = $_GET['role'] ?? '';

if ($role) {
    $stmt = $conn->prepare("SELECT id, first_name, last_name, phone_number, email FROM users WHERE role = ?");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];
    while ($user = $result->fetch_assoc()) {
        $users[] = $user;
    }

    echo json_encode($users);
}
?>
