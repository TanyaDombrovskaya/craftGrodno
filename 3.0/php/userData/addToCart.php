<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();

if (getUserRole() !== 'user') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
    exit();
}

$userID = getUserId();
$productID = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

if ($productID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Неверный ID товара']);
    exit();
}

// Проверяем, есть ли уже товар в корзине
$checkStmt = $connection->prepare("SELECT cartID, quantity FROM cart WHERE userID = ? AND productID = ?");
$checkStmt->bind_param("ii", $userID, $productID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    // Обновляем количество
    $row = $checkResult->fetch_assoc();
    $newQuantity = $row['quantity'] + $quantity;
    $updateStmt = $connection->prepare("UPDATE cart SET quantity = ? WHERE cartID = ?");
    $updateStmt->bind_param("ii", $newQuantity, $row['cartID']);
    $updateStmt->execute();
    $updateStmt->close();
    echo json_encode(['success' => true, 'message' => 'Количество обновлено']);
} else {
    // Добавляем новый товар
    $insertStmt = $connection->prepare("INSERT INTO cart (userID, productID, quantity) VALUES (?, ?, ?)");
    $insertStmt->bind_param("iii", $userID, $productID, $quantity);
    
    if ($insertStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Товар добавлен в корзину']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении: ' . $connection->error]);
    }
    $insertStmt->close();
}

$checkStmt->close();