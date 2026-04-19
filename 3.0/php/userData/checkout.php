<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();

if (getUserRole() !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
    exit();
}

$userID = getUserId();

// Получаем товары из корзины
$cartSql = "SELECT c.cartID, c.quantity, p.productID, p.price, p.masterID, p.countOfProduct as available
            FROM cart c 
            JOIN products p ON c.productID = p.productID 
            WHERE c.userID = ?";
$cartStmt = $connection->prepare($cartSql);
$cartStmt->bind_param("i", $userID);
$cartStmt->execute();
$cartResult = $cartStmt->get_result();

$cartItems = [];
$totalAmount = 0;
while ($item = $cartResult->fetch_assoc()) {
    // Проверяем, хватает ли товара на складе
    if ($item['available'] < $item['quantity']) {
        echo json_encode(['success' => false, 'message' => 'Товара "' . $item['productName'] . '" недостаточно на складе. Доступно: ' . $item['available']]);
        exit();
    }
    $itemTotal = $item['price'] * $item['quantity'];
    $totalAmount += $itemTotal;
    $cartItems[] = $item;
}

if (empty($cartItems)) {
    echo json_encode(['success' => false, 'message' => 'Корзина пуста']);
    exit();
}

// Проверяем баланс пользователя
$balanceSql = "SELECT balance FROM users WHERE userID = ?";
$balanceStmt = $connection->prepare($balanceSql);
$balanceStmt->bind_param("i", $userID);
$balanceStmt->execute();
$balanceResult = $balanceStmt->get_result();
$userBalance = $balanceResult->fetch_assoc();

if ($userBalance['balance'] < $totalAmount) {
    echo json_encode(['success' => false, 'message' => 'Недостаточно средств. Пополните баланс.']);
    exit();
}

// Начинаем транзакцию
$connection->begin_transaction();

try {
    // 1. Создаем заказ
    $orderSql = "INSERT INTO orders (userID, total_amount, status) VALUES (?, ?, 'pending')";
    $orderStmt = $connection->prepare($orderSql);
    $orderStmt->bind_param("id", $userID, $totalAmount);
    $orderStmt->execute();
    $orderID = $connection->insert_id;
    $orderStmt->close();
    
    // 2. Добавляем товары в order_items
    $itemSql = "INSERT INTO order_items (orderID, productID, quantity, price, masterID, status) VALUES (?, ?, ?, ?, ?, 'pending')";
    $itemStmt = $connection->prepare($itemSql);
    
    foreach ($cartItems as $item) {
        $itemStmt->bind_param("iiidi", $orderID, $item['productID'], $item['quantity'], $item['price'], $item['masterID']);
        $itemStmt->execute();
    }
    $itemStmt->close();
    
    // 3. Списание средств с баланса пользователя
    $updateUserBalanceSql = "UPDATE users SET balance = balance - ? WHERE userID = ?";
    $updateUserBalanceStmt = $connection->prepare($updateUserBalanceSql);
    $updateUserBalanceStmt->bind_param("di", $totalAmount, $userID);
    $updateUserBalanceStmt->execute();
    $updateUserBalanceStmt->close();
    
    // 4. Начисление средств мастеру
    $masterAmounts = [];
    foreach ($cartItems as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        if (!isset($masterAmounts[$item['masterID']])) {
            $masterAmounts[$item['masterID']] = 0;
        }
        $masterAmounts[$item['masterID']] += $itemTotal;
    }
    
    foreach ($masterAmounts as $masterID => $amount) {
        $updateMasterBalanceSql = "UPDATE masters SET balance = balance + ? WHERE masterID = ?";
        $updateMasterBalanceStmt = $connection->prepare($updateMasterBalanceSql);
        $updateMasterBalanceStmt->bind_param("di", $amount, $masterID);
        $updateMasterBalanceStmt->execute();
        $updateMasterBalanceStmt->close();
    }
    
    // 5. Обновляем количество товаров на складе
    foreach ($cartItems as $item) {
        $updateStockSql = "UPDATE products SET countOfProduct = countOfProduct - ? WHERE productID = ?";
        $updateStockStmt = $connection->prepare($updateStockSql);
        $updateStockStmt->bind_param("ii", $item['quantity'], $item['productID']);
        $updateStockStmt->execute();
        $updateStockStmt->close();
    }
    
    // 6. Очищаем корзину
    $clearCartSql = "DELETE FROM cart WHERE userID = ?";
    $clearCartStmt = $connection->prepare($clearCartSql);
    $clearCartStmt->bind_param("i", $userID);
    $clearCartStmt->execute();
    $clearCartStmt->close();
    
    // Фиксируем транзакцию
    $connection->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Заказ успешно оформлен',
        'order_id' => $orderID,
        'total_amount' => number_format($totalAmount, 2)
    ]);
    
} catch (Exception $e) {
    $connection->rollback();
    echo json_encode(['success' => false, 'message' => 'Ошибка при оформлении заказа: ' . $e->getMessage()]);
}

$cartStmt->close();
?>