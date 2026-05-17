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
$comment = isset($_POST['comment']) ? $_POST['comment'] : '';

// Получаем логин мастера для записи в историю
$currentUserLogin = $_SESSION['user_login'] ?? null;
if (!$currentUserLogin) {
    $userSql = "SELECT login FROM users WHERE userID = ?";
    $userStmt = $connection->prepare($userSql);
    $userStmt->bind_param("i", $userID);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $userData = $userResult->fetch_assoc();
    $currentUserLogin = $userData['login'] ?? 'master';
    $userStmt->close();
}

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
    echo json_encode(['success' => false, 'message' => 'Невозможно изменить статус. Текущий статус: ' . getStatusText($currentStatus)]);
    exit();
}

// Начинаем транзакцию
$connection->begin_transaction();

try {
    // Обновляем статус
    $updateSql = "UPDATE order_items SET status = ? WHERE order_itemID = ?";
    $updateStmt = $connection->prepare($updateSql);
    $updateStmt->bind_param("si", $newStatus, $orderItemID);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Ошибка при обновлении: ' . $updateStmt->error);
    }
    $updateStmt->close();
    
    // Записываем историю
    $historySql = "INSERT INTO order_item_status_history (order_itemID, old_status, new_status, changed_by, comment) 
                   VALUES (?, ?, ?, ?, ?)";
    $historyStmt = $connection->prepare($historySql);
    $historyStmt->bind_param("issss", $orderItemID, $currentStatus, $newStatus, $currentUserLogin, $comment);
    
    if (!$historyStmt->execute()) {
        throw new Exception('Ошибка записи истории: ' . $historyStmt->error);
    }
    $historyStmt->close();
    
    $connection->commit();
    echo json_encode(['success' => true, 'message' => 'Статус обновлен']);
    
} catch (Exception $e) {
    $connection->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

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