<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();
if (getUserRole() !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
    exit();
}

$userID = getUserId();
$cartID = isset($_POST['cart_id']) ? intval($_POST['cart_id']) : 0;

if ($cartID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Неверный ID']);
    exit();
}

$deleteStmt = $connection->prepare("DELETE FROM cart WHERE cartID = ? AND userID = ?");
$deleteStmt->bind_param("ii", $cartID, $userID);
$deleteStmt->execute();

if ($deleteStmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Товар удален из корзины']);
} else {
    echo json_encode(['success' => false, 'message' => 'Товар не найден']);
}
$deleteStmt->close();