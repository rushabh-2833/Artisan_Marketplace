<?php
include '../src/helpers/db_connect.php';

$searchTerm = $_GET['term'] ?? '';

// Fetch products that match the search term
$sql = "SELECT name FROM products WHERE name LIKE CONCAT('%', ?, '%') LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $suggestions[] = $row['name'];
}

echo json_encode($suggestions);
?>
