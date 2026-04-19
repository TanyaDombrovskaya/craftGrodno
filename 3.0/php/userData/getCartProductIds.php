<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

if (getUserRole() !== 'user') {
    echo json_encode([]);
    exit();
}

$userID = getUserId();

$sql = "SELECT productID FROM cart WHERE userID = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

$product_ids = [];
while ($row = $result->fetch_assoc()) {
    $product_ids[] = $row['productID'];
}

header('Content-Type: application/json');
echo json_encode($product_ids);
$stmt->close();