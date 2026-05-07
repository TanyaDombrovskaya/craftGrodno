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

// Разрешенные статусы для мастера
$allowedStatuses = ['collecting', 'delivering', 'delivered'];

if ($orderItemID <= 0 || !in_array($newStatus, $allowedStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Неверные данные: статус=' . $newStatus]);
    exit();
}

// Проверяем, что товар принадлежит мастеру И получаем текущий статус
$checkSql = "SELECT oi.order_itemID, oi.status 
             FROM order_items oi
             JOIN products p ON oi.productID = p.productID
             JOIN masters m ON p.masterID = m.masterID
             WHERE oi.order_itemID = ? AND m.userID = ?";
$checkStmt = $connection->prepare($checkSql);
$checkStmt->bind_param("ii", $orderItemID, $userID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
$orderItem = $checkResult->fetch_assoc();

if (!$orderItem) {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен или товар не найден']);
    $checkStmt->close();
    exit();
}
$checkStmt->close();

$currentStatus = $orderItem['status'];

// Логика смены статусов для мастера:
// approved -> collecting (мастер начинает сборку)
// collecting -> delivering (мастер отправляет)
// delivering -> delivered (мастер подтверждает доставку)
$allowedTransitions = [
    'approved' => ['collecting'],
    'collecting' => ['delivering'],
    'delivering' => ['delivered']
];

if (!isset($allowedTransitions[$currentStatus]) || !in_array($newStatus, $allowedTransitions[$currentStatus])) {
    echo json_encode(['success' => false, 'message' => 'Невозможно изменить статус. Текущий статус: ' . $this->getStatusText($currentStatus)]);
    exit();
}

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

function getStatusText($status) {
    $texts = [
        'pending' => 'Ожидает',
        'approved' => 'Подтверждён',
        'collecting' => 'Собирается',
        'delivering' => 'Доставляется',
        'delivered' => 'Доставлен',
        'completed' => 'Завершён'
    ];
    return $texts[$status] ?? $status;
}
?>