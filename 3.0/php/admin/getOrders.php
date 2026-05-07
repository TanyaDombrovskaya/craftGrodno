<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();
if (getUserRole() !== 'admin') {
    echo json_encode(['orders' => []]);
    exit();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$masterId = isset($_GET['master_id']) ? intval($_GET['master_id']) : 0;
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

$where = [];
$params = [];
$types = "";

if (!empty($search)) {
    $where[] = "(u.name LIKE ? OR u.email LIKE ? OR u.login LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "sss";
}

if ($masterId > 0) {
    $where[] = "oi.masterID = ?";
    $params[] = $masterId;
    $types .= "i";
}

if ($status !== 'all') {
    $where[] = "oi.status = ?";
    $params[] = $status;
    $types .= "s";
}

if ($orderId > 0) {
    $where[] = "o.orderID = ?";
    $params[] = $orderId;
    $types .= "i";
}

$whereClause = empty($where) ? "" : "WHERE " . implode(" AND ", $where);

$sql = "SELECT 
            oi.order_itemID,
            oi.quantity,
            oi.price,
            oi.status as item_status,
            o.orderID,
            o.order_date,
            o.total_amount,
            u.name as buyer_name,
            u.email as buyer_email,
            u.login as buyer_login,
            p.productID,
            p.productName,
            m.masterID,
            m.masterName
        FROM order_items oi
        JOIN orders o ON oi.orderID = o.orderID
        JOIN products p ON oi.productID = p.productID
        JOIN masters m ON oi.masterID = m.masterID
        JOIN users u ON o.userID = u.userID
        $whereClause
        ORDER BY o.order_date DESC, o.orderID, m.masterName";

$stmt = $connection->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

header('Content-Type: application/json');
echo json_encode(['orders' => $orders]);
$stmt->close();
?>