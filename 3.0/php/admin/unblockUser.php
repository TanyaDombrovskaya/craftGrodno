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

$sql = "UPDATE users SET is_blocked = 0, block_reason = NULL, blocked_at = NULL WHERE userID = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Пользователь разблокирован']);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка разблокировки']);
}
$stmt->close();
?>