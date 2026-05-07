<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();
if (getUserRole() !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещён']);
    exit();
}

$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

if (empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'Укажите причину отклонения']);
    exit();
}

$sql = "UPDATE products SET approved = 'rejected', rejection_reason = ? WHERE productID = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("si", $reason, $productId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка отклонения']);
}
$stmt->close();
?>