<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();
if (getUserRole() !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещён']);
    exit();
}

$userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

if ($userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Неверный ID пользователя']);
    exit();
}

// Нельзя удалить самого себя
if ($userId == getUserId()) {
    echo json_encode(['success' => false, 'message' => 'Нельзя удалить самого себя']);
    exit();
}

// ======= 1. ПРОВЕРКА: ЕСТЬ ЛИ У ПОЛЬЗОВАТЕЛЯ ЗАКАЗЫ =======
$orderSql = "SELECT COUNT(*) as order_count FROM orders WHERE userID = ?";
$orderStmt = $connection->prepare($orderSql);
$orderStmt->bind_param("i", $userId);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();
$orderData = $orderResult->fetch_assoc();
$orderStmt->close();

// Если у пользователя нет заказов → можно удалять сразу
if ($orderData['order_count'] == 0) {
    $deleteSql = "DELETE FROM users WHERE userID = ?";
    $deleteStmt = $connection->prepare($deleteSql);
    $deleteStmt->bind_param("i", $userId);
    
    if ($deleteStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Пользователь удалён']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка удаления: ' . $deleteStmt->error]);
    }
    $deleteStmt->close();
    exit();
}

// ======= 2. ПРОВЕРКА: ВСЕ ЛИ ПОЗИЦИИ В ЗАКАЗАХ ИМЕЮТ СТАТУС 'completed' =======
$sql = "SELECT 
            COUNT(*) as total_items,
            SUM(CASE WHEN oi.status != 'completed' THEN 1 ELSE 0 END) as not_completed_items
        FROM orders o
        JOIN order_items oi ON o.orderID = oi.orderID
        WHERE o.userID = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

$totalItems = $data['total_items'];
$notCompletedItems = $data['not_completed_items'];

// Если есть хотя бы одна незавершённая позиция → удаление запрещено
if ($notCompletedItems > 0) {
    echo json_encode([
        'success' => false, 
        'message' => "Нельзя удалить пользователя: {$notCompletedItems} из {$totalItems} позиций в заказах имеют незавершённый статус"
    ]);
    exit();
}

// ======= 3. ПРОВЕРКА ДЛЯ МАСТЕРОВ (если пользователь — мастер) =======
$roleSql = "SELECT role FROM users WHERE userID = ?";
$roleStmt = $connection->prepare($roleSql);
$roleStmt->bind_param("i", $userId);
$roleStmt->execute();
$roleResult = $roleStmt->get_result();
$userData = $roleResult->fetch_assoc();
$roleStmt->close();

if ($userData && $userData['role'] === 'seller') {
    // Получаем masterID
    $masterSql = "SELECT masterID FROM masters WHERE userID = ?";
    $masterStmt = $connection->prepare($masterSql);
    $masterStmt->bind_param("i", $userId);
    $masterStmt->execute();
    $masterResult = $masterStmt->get_result();
    $masterData = $masterResult->fetch_assoc();
    $masterStmt->close();
    
    if ($masterData) {
        $masterId = $masterData['masterID'];
        
        // Проверяем, есть ли у мастера товары в НЕЗАВЕРШЁННЫХ заказах
        $productSql = "SELECT 
                           COUNT(*) as total_items,
                           SUM(CASE WHEN oi.status != 'completed' THEN 1 ELSE 0 END) as not_completed_items
                       FROM order_items oi
                       JOIN products p ON oi.productID = p.productID
                       WHERE p.masterID = ?";
        $productStmt = $connection->prepare($productSql);
        $productStmt->bind_param("i", $masterId);
        $productStmt->execute();
        $productResult = $productStmt->get_result();
        $productData = $productResult->fetch_assoc();
        $productStmt->close();
        
        if ($productData['not_completed_items'] > 0) {
            echo json_encode([
                'success' => false, 
                'message' => "Нельзя удалить мастера: {$productData['not_completed_items']} из {$productData['total_items']} позиций с его товарами имеют незавершённый статус"
            ]);
            exit();
        }
    }
}

// ======= 4. ВСЕ ПРОВЕРКИ ПРОЙДЕНЫ → УДАЛЯЕМ =======
$deleteSql = "DELETE FROM users WHERE userID = ?";
$deleteStmt = $connection->prepare($deleteSql);
$deleteStmt->bind_param("i", $userId);

if ($deleteStmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Пользователь удалён']);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка удаления: ' . $deleteStmt->error]);
}
$deleteStmt->close();
?>