<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

// Включаем отображение ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

checkAuth();

if (getUserRole() !== 'seller') {
    echo json_encode(['error' => 'Доступ запрещен', 'sales' => [], 'stats' => []]);
    exit();
}

$userID = getUserId();

// Получаем masterID
$masterSql = "SELECT masterID FROM masters WHERE userID = ?";
$masterStmt = $connection->prepare($masterSql);
if (!$masterStmt) {
    echo json_encode(['error' => 'Ошибка подготовки запроса master: ' . $connection->error]);
    exit();
}

$masterStmt->bind_param("i", $userID);
$masterStmt->execute();
$masterResult = $masterStmt->get_result();
$master = $masterResult->fetch_assoc();

if (!$master) {
    echo json_encode(['error' => 'Мастер не найден', 'sales' => [], 'stats' => []]);
    exit();
}

$masterID = $master['masterID'];
$masterStmt->close();

// Простой запрос без фильтров для проверки
$sql = "SELECT 
            oi.order_itemID,
            oi.quantity,
            oi.price,
            oi.status,
            o.order_date,
            p.productID,
            p.productName,
            u.name as buyer_name
        FROM order_items oi
        JOIN orders o ON oi.orderID = o.orderID
        JOIN products p ON oi.productID = p.productID
        JOIN users u ON o.userID = u.userID
        WHERE oi.masterID = ?
        ORDER BY o.order_date DESC
        LIMIT 10";

$stmt = $connection->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Ошибка подготовки запроса: ' . $connection->error]);
    exit();
}

$stmt->bind_param("i", $masterID);
$stmt->execute();
$result = $stmt->get_result();

$sales = [];
$totalRevenue = 0;
while ($row = $result->fetch_assoc()) {
    $totalRevenue += $row['price'] * $row['quantity'];
    $sales[] = $row;
}

$stats = [
    'total_count' => count($sales),
    'total_revenue' => $totalRevenue,
    'average_check' => count($sales) > 0 ? $totalRevenue / count($sales) : 0
];

header('Content-Type: application/json');
echo json_encode(['sales' => $sales, 'stats' => $stats]);

$stmt->close();
?>