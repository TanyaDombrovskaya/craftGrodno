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
    // Альтернативный способ: получить из БД по userID
    $userId = $_SESSION['user_id'] ?? 0;
    if ($userId) {
        $userSql = "SELECT login FROM users WHERE userID = ?";
        $userStmt = $connection->prepare($userSql);
        $userStmt->bind_param("i", $userId);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $userData = $userResult->fetch_assoc();
        $currentUserLogin = $userData['login'] ?? 'unknown';
        $userStmt->close();
    } else {
        $currentUserLogin = 'unknown';
    }
}

$orderItemIds = isset($_POST['order_item_ids']) ? json_decode($_POST['order_item_ids'], true) : [];
$newStatus = isset($_POST['status']) ? $_POST['status'] : '';
$comment = isset($_POST['comment']) ? $_POST['comment'] : '';

$allowedStatuses = ['approved', 'collecting', 'delivering', 'delivered', 'completed'];

if (empty($orderItemIds) || !in_array($newStatus, $allowedStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Неверные данные']);
    exit();
}

$successCount = 0;
$errorCount = 0;
$errors = [];

// Начинаем транзакцию для целостности данных
$connection->begin_transaction();

try {
    foreach ($orderItemIds as $orderItemId) {
        // Получаем текущий статус
        $sql = "SELECT status FROM order_items WHERE order_itemID = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $orderItemId);
        $stmt->execute();
        $result = $stmt->get_result();
        $currentItem = $result->fetch_assoc();
        $stmt->close();
        
        if (!$currentItem) {
            $errorCount++;
            $errors[] = "ID $orderItemId: не найден";
            continue;
        }
        
        $oldStatus = $currentItem['status'];
        
        // Обновляем статус
        $updateSql = "UPDATE order_items SET status = ? WHERE order_itemID = ?";
        $updateStmt = $connection->prepare($updateSql);
        $updateStmt->bind_param("si", $newStatus, $orderItemId);
        
        if (!$updateStmt->execute()) {
            $errorCount++;
            $errors[] = "ID $orderItemId: ошибка UPDATE - " . $updateStmt->error;
            $updateStmt->close();
            continue;
        }
        $updateStmt->close();
        
        // Записываем историю (используем login текущего пользователя)
        $historySql = "INSERT INTO order_item_status_history (order_itemID, old_status, new_status, changed_by, comment) 
                       VALUES (?, ?, ?, ?, ?)";
        $historyStmt = $connection->prepare($historySql);
        $historyStmt->bind_param("issss", $orderItemId, $oldStatus, $newStatus, $currentUserLogin, $comment);
        
        if ($historyStmt->execute()) {
            $successCount++;
        } else {
            // Ошибка при записи истории — откатываем всё
            $errorCount++;
            $errors[] = "ID $orderItemId: ошибка INSERT в историю - " . $historyStmt->error;
            $historyStmt->close();
            throw new Exception("Ошибка записи истории для ID $orderItemId");
        }
        $historyStmt->close();
    }
    
    // Если есть ошибки, откатываем транзакцию
    if ($errorCount > 0) {
        $connection->rollback();
        echo json_encode([
            'success' => false, 
            'message' => 'Ошибки при обновлении: ' . implode('; ', $errors),
            'success_count' => $successCount, 
            'error_count' => $errorCount
        ]);
    } else {
        $connection->commit();
        echo json_encode([
            'success' => true, 
            'success_count' => $successCount, 
            'error_count' => $errorCount
        ]);
    }
    
} catch (Exception $e) {
    $connection->rollback();
    echo json_encode([
        'success' => false, 
        'message' => 'Ошибка: ' . $e->getMessage(),
        'success_count' => $successCount, 
        'error_count' => $errorCount
    ]);
}
?>