<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();
if (getUserRole() !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещён']);
    exit();
}

// Получаем логин текущего администратора
$currentUserLogin = $_SESSION['user_login'] ?? null;
if (!$currentUserLogin) {
    $userId = $_SESSION['user_id'] ?? 0;
    if ($userId) {
        $userSql = "SELECT login FROM users WHERE userID = ?";
        $userStmt = $connection->prepare($userSql);
        $userStmt->bind_param("i", $userId);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $userData = $userResult->fetch_assoc();
        $currentUserLogin = $userData['login'] ?? 'admin';
        $userStmt->close();
    } else {
        $currentUserLogin = 'admin';
    }
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

// Начинаем транзакцию
$connection->begin_transaction();

try {
    // Обновляем статус
    $updateSql = "UPDATE order_items SET status = ? WHERE order_itemID = ?";
    $updateStmt = $connection->prepare($updateSql);
    $updateStmt->bind_param("si", $newStatus, $orderItemId);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Ошибка обновления: ' . $updateStmt->error);
    }
    $updateStmt->close();
    
    // Записываем историю
    $historySql = "INSERT INTO order_item_status_history (order_itemID, old_status, new_status, changed_by, comment) 
                   VALUES (?, ?, ?, ?, ?)";
    $historyStmt = $connection->prepare($historySql);
    $historyStmt->bind_param("issss", $orderItemId, $oldStatus, $newStatus, $currentUserLogin, $comment);
    
    if (!$historyStmt->execute()) {
        throw new Exception('Ошибка записи истории: ' . $historyStmt->error);
    }
    $historyStmt->close();
    
    $connection->commit();
    echo json_encode(['success' => true, 'message' => 'Статус обновлён']);
    
} catch (Exception $e) {
    $connection->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>