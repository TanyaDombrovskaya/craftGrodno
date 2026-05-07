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

// Проверяем, есть ли у пользователя заказы
$checkSql = "SELECT COUNT(*) as order_count FROM orders WHERE userID = ?";
$checkStmt = $connection->prepare($checkSql);
$checkStmt->bind_param("i", $userId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
$checkData = $checkResult->fetch_assoc();
$checkStmt->close();

if ($checkData['order_count'] > 0) {
    echo json_encode(['success' => false, 'message' => 'Нельзя удалить пользователя с заказами']);
    exit();
}

$sql = "DELETE FROM users WHERE userID = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Пользователь удалён']);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка удаления']);
}
$stmt->close();
?>