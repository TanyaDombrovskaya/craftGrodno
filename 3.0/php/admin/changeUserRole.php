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
            
            $insertMasterSql = "INSERT INTO masters (userID, masterName, description) VALUES (?, ?, 'Мастер')";
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
        // Проверяем, есть ли у мастера товары или незавершённые заказы
        $masterSql = "SELECT masterID FROM masters WHERE userID = ?";
        $masterStmt = $connection->prepare($masterSql);
        $masterStmt->bind_param("i", $userId);
        $masterStmt->execute();
        $masterResult = $masterStmt->get_result();
        
        if ($masterResult->num_rows > 0) {
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
                throw new Exception('Нельзя снять роль мастера: у пользователя есть активные товары');
            }
            
            // Опционально: удаляем запись из masters или просто оставляем
            // $deleteMasterSql = "DELETE FROM masters WHERE masterID = ?";
            // $deleteMasterStmt = $connection->prepare($deleteMasterSql);
            // $deleteMasterStmt->bind_param("i", $masterId);
            // $deleteMasterStmt->execute();
            // $deleteMasterStmt->close();
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