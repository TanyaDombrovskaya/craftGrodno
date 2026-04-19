<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();

if (getUserRole() !== 'user') {
    echo json_encode([]);
    exit();
}

$userID = getUserId();

$sql = "SELECT orderID, order_date, total_amount, status FROM orders WHERE userID = ? ORDER BY order_date DESC";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($order = $result->fetch_assoc()) {
    // Получаем товары в заказе
    $itemsSql = "SELECT oi.quantity, oi.price, p.productID, p.productName, p.countOfProduct
                 FROM order_items oi 
                 LEFT JOIN products p ON oi.productID = p.productID 
                 WHERE oi.orderID = ?";
    $itemsStmt = $connection->prepare($itemsSql);
    $itemsStmt->bind_param("i", $order['orderID']);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();
    
    $items = [];
    while ($item = $itemsResult->fetch_assoc()) {
        // Проверяем, существует ли товар
        $item['product_exists'] = !is_null($item['productID']);
        $items[] = $item;
    }
    
    $order['items'] = $items;
    $orders[] = $order;
    
    $itemsStmt->close();
}

header('Content-Type: application/json');
echo json_encode($orders);
$stmt->close();
?>