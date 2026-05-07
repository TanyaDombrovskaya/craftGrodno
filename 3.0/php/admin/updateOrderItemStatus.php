<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();
if (getUserRole() !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещён']);
    exit();
}

$orderItemId = isset($_POST['order_item_id']) ? intval($_POST['order_item_id']) : 0;
$newStatus = isset($_POST['status']) ? $_POST['status'] : '';
$comment = isset($_POST['comment']) ? $_POST['comment'] : '';

$allowedStatuses = ['pending', 'approved', 'collecting', 'delivering', 'delivered', 'completed'];

if ($orderItemId <= 0 || !in_array($newStatus, $allowedStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Неверные данные']);
    exit();
}

// Получаем текущий статус
$sql = "SELECT status FROM order_items WHERE order_itemID = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $orderItemId);
$stmt->execute();
$result = $stmt->get_result();
$currentItem = $result->fetch_assoc();
$stmt->close();

if (!$currentItem) {
    echo json_encode(['success' => false, 'message' => 'Товар не найден']);
    exit();
}

$oldStatus = $currentItem['status'];

// Обновляем статус
$updateSql = "UPDATE order_items SET status = ? WHERE order_itemID = ?";
$updateStmt = $connection->prepare($updateSql);
$updateStmt->bind_param("si", $newStatus, $orderItemId);

if ($updateStmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Статус обновлён']);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка обновления: ' . $updateStmt->error]);
}
$updateStmt->close();
?>