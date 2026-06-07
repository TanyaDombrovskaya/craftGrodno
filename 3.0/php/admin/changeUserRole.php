<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();
if (getUserRole() !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещён']);
    exit();
}

$userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$newRole = isset($_POST['new_role']) ? $_POST['new_role'] : '';

$allowedRoles = ['user', 'seller', 'admin'];

if ($userId <= 0 || !in_array($newRole, $allowedRoles)) {
    echo json_encode(['success' => false, 'message' => 'Неверные данные']);
    exit();
}

// Нельзя изменить роль самого себя
if ($userId == getUserId()) {
    echo json_encode(['success' => false, 'message' => 'Нельзя изменить роль самого себя']);
    exit();
}

// Получаем текущую роль пользователя
$sql = "SELECT role FROM users WHERE userID = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Пользователь не найден']);
    exit();
}

$oldRole = $user['role'];

// Если роль не меняется, просто выходим
if ($oldRole === $newRole) {
    echo json_encode(['success' => false, 'message' => 'Роль уже установлена']);
    exit();
}

// Проверки перед сменой роли
try {
    // Если пользователь становится мастером (seller)
    if ($newRole === 'seller') {
        // Проверяем, есть ли у пользователя активные заказы как у покупателя
        $activeOrdersSql = "SELECT COUNT(*) as active_orders FROM orders 
                           WHERE userID = ? AND status NOT IN ('completed', 'cancelled')";
        $activeOrdersStmt = $connection->prepare($activeOrdersSql);
        $activeOrdersStmt->bind_param("i", $userId);
        $activeOrdersStmt->execute();
        $activeOrdersResult = $activeOrdersStmt->get_result();
        $activeOrdersData = $activeOrdersResult->fetch_assoc();
        $activeOrdersStmt->close();
        
        if ($activeOrdersData['active_orders'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Нельзя назначить мастером: у пользователя есть активные заказы как у покупателя. Сначала завершите или отмените заказы.']);
            exit();
        }
        
        // Проверяем, есть ли у пользователя положительный баланс
        $balanceSql = "SELECT balance FROM users WHERE userID = ?";
        $balanceStmt = $connection->prepare($balanceSql);
        $balanceStmt->bind_param("i", $userId);
        $balanceStmt->execute();
        $balanceResult = $balanceStmt->get_result();
        $balanceData = $balanceResult->fetch_assoc();
        $balanceStmt->close();
        
        if ($balanceData['balance'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Нельзя назначить мастером: у пользователя есть средства на балансе (' . $balanceData['balance'] . ' руб.). Сначала выведите средства.']);
            exit();
        }
    }
    
    // Если пользователь перестаёт быть мастером (был seller, стал user или admin)
    if ($oldRole === 'seller' && $newRole !== 'seller') {
        // Получаем masterID
        $masterSql = "SELECT masterID FROM masters WHERE userID = ?";
        $masterStmt = $connection->prepare($masterSql);
        $masterStmt->bind_param("i", $userId);
        $masterStmt->execute();
        $masterResult = $masterStmt->get_result();
        
        if ($masterResult->num_rows === 0) {
            $masterStmt->close();
            echo json_encode(['success' => false, 'message' => 'Запись мастера не найдена']);
            exit();
        }
        
        $masterData = $masterResult->fetch_assoc();
        $masterId = $masterData['masterID'];
        $masterStmt->close();
        
        // Проверяем товары мастера
        $productSql = "SELECT COUNT(*) as product_count FROM products WHERE masterID = ?";
        $productStmt = $connection->prepare($productSql);
        $productStmt->bind_param("i", $masterId);
        $productStmt->execute();
        $productResult = $productStmt->get_result();
        $productData = $productResult->fetch_assoc();
        $productStmt->close();
        
        if ($productData['product_count'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Нельзя снять роль мастера: у пользователя есть активные товары (' . $productData['product_count'] . ' шт.). Сначала удалите все товары.']);
            exit();
        }
        
        // Проверяем, есть ли у мастера активные заказы (как продавца)
        $activeSellerOrdersSql = "SELECT COUNT(*) as active_orders FROM orders o
                                 JOIN products p ON o.productID = p.productID
                                 WHERE p.masterID = ? AND o.status NOT IN ('completed', 'cancelled')";
        $activeSellerOrdersStmt = $connection->prepare($activeSellerOrdersSql);
        $activeSellerOrdersStmt->bind_param("i", $masterId);
        $activeSellerOrdersStmt->execute();
        $activeSellerOrdersResult = $activeSellerOrdersStmt->get_result();
        $activeSellerOrdersData = $activeSellerOrdersResult->fetch_assoc();
        $activeSellerOrdersStmt->close();
        
        if ($activeSellerOrdersData['active_orders'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Нельзя снять роль мастера: есть активные заказы на товары мастера (' . $activeSellerOrdersData['active_orders'] . ' шт.). Сначала завершите или отмените заказы.']);
            exit();
        }
        
        // Проверяем баланс мастера
        $balanceSql = "SELECT balance FROM masters WHERE masterID = ?";
        $balanceStmt = $connection->prepare($balanceSql);
        $balanceStmt->bind_param("i", $masterId);
        $balanceStmt->execute();
        $balanceResult = $balanceStmt->get_result();
        $balanceData = $balanceResult->fetch_assoc();
        $balanceStmt->close();
        
        if ($balanceData['balance'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Нельзя снять роль мастера: на балансе мастера есть средства (' . $balanceData['balance'] . ' руб.). Сначала выведите средства.']);
            exit();
        }
    }
    
    // Если пользователь становится администратором или покупателем из мастера
    if ($newRole === 'admin' || $newRole === 'user') {
        // Дополнительная проверка для покупателей, которые были мастерами
        if ($oldRole === 'seller') {
            // Проверяем, нет ли у пользователя активных заказов как у покупателя
            $activeBuyerOrdersSql = "SELECT COUNT(*) as active_orders FROM orders 
                                    WHERE userID = ? AND status NOT IN ('completed', 'cancelled')";
            $activeBuyerOrdersStmt = $connection->prepare($activeBuyerOrdersSql);
            $activeBuyerOrdersStmt->bind_param("i", $userId);
            $activeBuyerOrdersStmt->execute();
            $activeBuyerOrdersResult = $activeBuyerOrdersStmt->get_result();
            $activeBuyerOrdersData = $activeBuyerOrdersResult->fetch_assoc();
            $activeBuyerOrdersStmt->close();
            
            if ($activeBuyerOrdersData['active_orders'] > 0) {
                echo json_encode(['success' => false, 'message' => 'Нельзя сменить роль: у пользователя есть активные заказы как у покупателя (' . $activeBuyerOrdersData['active_orders'] . ' шт.). Сначала завершите или отмените заказы.']);
                exit();
            }
        }
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка при проверке: ' . $e->getMessage()]);
    exit();
}

// Начинаем транзакцию
$connection->begin_transaction();

try {
    // Обновляем роль пользователя
    $updateSql = "UPDATE users SET role = ? WHERE userID = ?";
    $updateStmt = $connection->prepare($updateSql);
    $updateStmt->bind_param("si", $newRole, $userId);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Ошибка обновления роли: ' . $updateStmt->error);
    }
    $updateStmt->close();
    
    // Если пользователь становится мастером (seller), нужно создать запись в таблице masters
    if ($newRole === 'seller') {
        // Проверяем, есть ли уже запись в masters
        $checkMasterSql = "SELECT masterID FROM masters WHERE userID = ?";
        $checkMasterStmt = $connection->prepare($checkMasterSql);
        $checkMasterStmt->bind_param("i", $userId);
        $checkMasterStmt->execute();
        $checkMasterResult = $checkMasterStmt->get_result();
        
        if ($checkMasterResult->num_rows === 0) {
            // Создаём запись в masters
            $loginSql = "SELECT login, name FROM users WHERE userID = ?";
            $loginStmt = $connection->prepare($loginSql);
            $loginStmt->bind_param("i", $userId);
            $loginStmt->execute();
            $loginResult = $loginStmt->get_result();
            $userData = $loginResult->fetch_assoc();
            $loginStmt->close();
            
            $masterName = $userData['name'] . ' (' . $userData['login'] . ')';
            
            $insertMasterSql = "INSERT INTO masters (userID, masterName, description, balance) VALUES (?, ?, 'Мастер', 0)";
            $insertMasterStmt = $connection->prepare($insertMasterSql);
            $insertMasterStmt->bind_param("is", $userId, $masterName);
            
            if (!$insertMasterStmt->execute()) {
                throw new Exception('Ошибка создания записи мастера: ' . $insertMasterStmt->error);
            }
            $insertMasterStmt->close();
        }
        $checkMasterStmt->close();
    }
    
    // Если пользователь перестаёт быть мастером (был seller, стал user или admin)
    if ($oldRole === 'seller' && $newRole !== 'seller') {
        // Получаем masterID и удаляем запись из masters (или помечаем как неактивного)
        $masterSql = "SELECT masterID FROM masters WHERE userID = ?";
        $masterStmt = $connection->prepare($masterSql);
        $masterStmt->bind_param("i", $userId);
        $masterStmt->execute();
        $masterResult = $masterStmt->get_result();
        
        if ($masterResult->num_rows > 0) {
            $masterData = $masterResult->fetch_assoc();
            $masterId = $masterData['masterID'];
            $masterStmt->close();
            
            // Здесь можно либо удалить запись, либо пометить как неактивную
            // Вариант 1: Удалить запись (если нет внешних ключей с CASCADE)
            $deleteMasterSql = "DELETE FROM masters WHERE masterID = ?";
            $deleteMasterStmt = $connection->prepare($deleteMasterSql);
            $deleteMasterStmt->bind_param("i", $masterId);
            
            if (!$deleteMasterStmt->execute()) {
                throw new Exception('Ошибка удаления записи мастера: ' . $deleteMasterStmt->error);
            }
            $deleteMasterStmt->close();
            
            // Вариант 2: Обновить статус (если есть поле is_active)
            // $updateMasterSql = "UPDATE masters SET is_active = 0 WHERE masterID = ?";
        } else {
            $masterStmt->close();
        }
    }
    
    $connection->commit();
    echo json_encode(['success' => true, 'message' => 'Роль пользователя изменена на "' . getRoleText($newRole) . '"']);
    
} catch (Exception $e) {
    $connection->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function getRoleText($role) {
    $roles = [
        'user' => 'Покупатель',
        'seller' => 'Мастер',
        'admin' => 'Администратор'
    ];
    return $roles[$role] ?? $role;
}
?>