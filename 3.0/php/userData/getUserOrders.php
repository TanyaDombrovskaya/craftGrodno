<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();

if (getUserRole() !== 'user') {
    echo json_encode([]);
    exit();
}

$userID = getUserId();

// Получаем параметры фильтрации
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$dateFrom = isset($_GET['date_from']) && !empty($_GET['date_from']) ? $_GET['date_from'] : null;
$dateTo = isset($_GET['date_to']) && !empty($_GET['date_to']) ? $_GET['date_to'] : null;

// Формируем WHERE условия
$whereConditions = ["o.userID = ?"];
$params = [$userID];
$types = "i";

if ($status !== 'all') {
    $whereConditions[] = "oi.status = ?";
    $params[] = $status;
    $types .= "s";
}

if ($dateFrom) {
    $whereConditions[] = "DATE(o.order_date) >= ?";
    $params[] = $dateFrom;
    $types .= "s";
}

if ($dateTo) {
    $whereConditions[] = "DATE(o.order_date) <= ?";
    $params[] = $dateTo;
    $types .= "s";
}

$whereClause = implode(" AND ", $whereConditions);

// Получаем заказы с учетом фильтров
$sql = "SELECT DISTINCT o.orderID, o.order_date, o.total_amount 
        FROM orders o
        JOIN order_items oi ON o.orderID = oi.orderID
        WHERE $whereClause
        ORDER BY o.order_date DESC";

$stmt = $connection->prepare($sql);
if (count($params) > 1) {
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param($types, $params[0]);
}
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($order = $result->fetch_assoc()) {
    // Получаем товары в заказе с их статусами
    $itemsSql = "SELECT oi.order_itemID, oi.quantity, oi.price, oi.status as item_status,
                        p.productID, p.productName, p.countOfProduct
                 FROM order_items oi 
                 LEFT JOIN products p ON oi.productID = p.productID 
                 WHERE oi.orderID = ?";
    $itemsStmt = $connection->prepare($itemsSql);
    $itemsStmt->bind_param("i", $order['orderID']);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();
    
    $items = [];
    while ($item = $itemsResult->fetch_assoc()) {
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