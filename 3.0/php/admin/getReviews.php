<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();
if (getUserRole() !== 'admin') {
    echo json_encode(['reviews' => []]);
    exit();
}

$sql = "SELECT r.reviewID, r.rating, r.review, r.created_at,
               p.productName, u.name as userName
        FROM reviews r
        JOIN products p ON r.productID = p.productID
        JOIN users u ON r.userID = u.userID
        ORDER BY r.created_at DESC";

$result = $connection->query($sql);

$reviews = [];
while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
}

header('Content-Type: application/json');
echo json_encode(['reviews' => $reviews]);
?>