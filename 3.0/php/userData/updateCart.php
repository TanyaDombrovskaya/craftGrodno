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
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

if ($cartID <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Неверные данные']);
    exit();
}

$checkStmt = $connection->prepare("SELECT cartID FROM cart WHERE cartID = ? AND userID = ?");
$checkStmt->bind_param("ii", $cartID, $userID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Товар не найден']);
    $checkStmt->close();
    exit();
}
$checkStmt->close();

$updateStmt = $connection->prepare("UPDATE cart SET quantity = ? WHERE cartID = ?");
$updateStmt->bind_param("ii", $quantity, $cartID);
$updateStmt->execute();

echo json_encode(['success' => true, 'message' => 'Количество обновлено']);
$updateStmt->close();