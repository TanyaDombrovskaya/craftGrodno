<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();

if (getUserRole() !== 'seller') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
    exit();
}

$userID = getUserId();
$orderItemID = isset($_POST['order_item_id']) ? intval($_POST['order_item_id']) : 0;
$newStatus = isset($_POST['status']) ? $_POST['status'] : '';

if ($orderItemID <= 0 || !in_array($newStatus, ['transferred'])) {
    echo json_encode(['success' => false, 'message' => 'Неверные данные']);
    exit();
}

// Проверяем, что товар принадлежит мастеру
$checkSql = "SELECT oi.order_itemID 
             FROM order_items oi
             JOIN products p ON oi.productID = p.productID
             JOIN masters m ON p.masterID = m.masterID
             WHERE oi.order_itemID = ? AND m.userID = ?";
$checkStmt = $connection->prepare($checkSql);
$checkStmt->bind_param("ii", $orderItemID, $userID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
    $checkStmt->close();
    exit();
}
$checkStmt->close();

// Обновляем статус
$updateSql = "UPDATE order_items SET status = ? WHERE order_itemID = ?";
$updateStmt = $connection->prepare($updateSql);
$updateStmt->bind_param("si", $newStatus, $orderItemID);

if ($updateStmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Статус обновлен']);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении']);
}

$updateStmt->close();
?>