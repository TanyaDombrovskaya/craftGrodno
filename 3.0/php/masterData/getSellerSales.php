<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

header('Content-Type: application/json');

checkAuth();

if (getUserRole() !== 'seller') {
    echo json_encode(['error' => 'Доступ запрещен', 'sales' => [], 'stats' => []]);
    exit();
}

$userID = getUserId();

// Получаем masterID
$masterSql = "SELECT masterID FROM masters WHERE userID = ?";
$masterStmt = $connection->prepare($masterSql);
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

// Получаем параметры фильтрации из GET
$period = isset($_GET['period']) ? $_GET['period'] : 'all';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$dateFrom = isset($_GET['date_from']) && !empty($_GET['date_from']) ? $_GET['date_from'] : null;
$dateTo = isset($_GET['date_to']) && !empty($_GET['date_to']) ? $_GET['date_to'] : null;

// Базовый WHERE
$whereConditions = ["oi.masterID = ?"];
$params = [$masterID];
$types = "i";

// Фильтр по статусу
if ($status !== 'all') {
    $whereConditions[] = "oi.status = ?";
    $params[] = $status;
    $types .= "s";
}

// Фильтр по дате от
if ($dateFrom) {
    $whereConditions[] = "DATE(o.order_date) >= ?";
    $params[] = $dateFrom;
    $types .= "s";
}

// Фильтр по дате до
if ($dateTo) {
    $whereConditions[] = "DATE(o.order_date) <= ?";
    $params[] = $dateTo;
    $types .= "s";
}

// Фильтр по периоду (today, week, month, year)
if ($period !== 'all') {
    switch ($period) {
        case 'today':
            $whereConditions[] = "DATE(o.order_date) = CURDATE()";
            break;
        case 'week':
            $whereConditions[] = "o.order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $whereConditions[] = "o.order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
        case 'year':
            $whereConditions[] = "o.order_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            break;
    }
}

$whereClause = implode(" AND ", $whereConditions);

// Запрос для списка продаж
$sql = "SELECT 
            oi.order_itemID,
            oi.quantity,
            oi.price,
            oi.status,
            o.order_date,
            o.orderID,
            p.productID,
            p.productName,
            u.name as buyer_name,
            u.login as buyer_login
        FROM order_items oi
        JOIN orders o ON oi.orderID = o.orderID
        JOIN products p ON oi.productID = p.productID
        JOIN users u ON o.userID = u.userID
        WHERE $whereClause
        ORDER BY o.order_date DESC";

$stmt = $connection->prepare($sql);
if (!empty($params) && count($params) > 1) {
    $stmt->bind_param($types, ...$params);
} elseif (!empty($params)) {
    $stmt->bind_param($types, $params[0]);
}
$stmt->execute();
$result = $stmt->get_result();

$sales = [];
$totalRevenue = 0;

while ($row = $result->fetch_assoc()) {
    $totalRevenue += $row['price'] * $row['quantity'];
    $sales[] = $row;
}

// Статистика
$stats = [
    'total_count' => count($sales),
    'total_revenue' => round($totalRevenue, 2),
    'average_check' => count($sales) > 0 ? round($totalRevenue / count($sales), 2) : 0
];

echo json_encode(['sales' => $sales, 'stats' => $stats]);

$stmt->close();
$connection->close();
?>