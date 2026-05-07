<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();
if (getUserRole() !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещён']);
    exit();
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
        continue;
    }
    
    $oldStatus = $currentItem['status'];
    
    $updateSql = "UPDATE order_items SET status = ? WHERE order_itemID = ?";
    $updateStmt = $connection->prepare($updateSql);
    $updateStmt->bind_param("si", $newStatus, $orderItemId);
    
    if ($updateStmt->execute()) {
        $successCount++;
        
        // Записываем историю
        $historySql = "INSERT INTO order_item_status_history (order_itemID, old_status, new_status, changed_by, comment) 
                       VALUES (?, ?, ?, 'admin', ?)";
        $historyStmt = $connection->prepare($historySql);
        $historyStmt->bind_param("isss", $orderItemId, $oldStatus, $newStatus, $comment);
        $historyStmt->execute();
        $historyStmt->close();
    } else {
        $errorCount++;
    }
    $updateStmt->close();
}

echo json_encode(['success' => true, 'success_count' => $successCount, 'error_count' => $errorCount]);
?>