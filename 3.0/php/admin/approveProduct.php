<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();
if (getUserRole() !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещён']);
    exit();
}

$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

$sql = "UPDATE products SET approved = 'approved', rejection_reason = NULL WHERE productID = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $productId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка одобрения']);
}
$stmt->close();
?>