<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();
if (getUserRole() !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещён']);
    exit();
}

$userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

if ($userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Неверный ID пользователя']);
    exit();
}

// Нельзя заблокировать самого себя
if ($userId == getUserId()) {
    echo json_encode(['success' => false, 'message' => 'Нельзя заблокировать самого себя']);
    exit();
}

// Блокируем пользователя
$sql = "UPDATE users SET is_blocked = 1, block_reason = ?, blocked_at = NOW(), last_activity = NULL WHERE userID = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("si", $reason, $userId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Пользователь заблокирован']);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка блокировки']);
}
$stmt->close();
?>