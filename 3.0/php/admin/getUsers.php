<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();
if (getUserRole() !== 'admin') {
    echo json_encode(['users' => []]);
    exit();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$role = isset($_GET['role']) ? $_GET['role'] : 'all';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

$where = [];
$params = [];
$types = "";

if (!empty($search)) {
    $where[] = "(login LIKE ? OR name LIKE ? OR email LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "sss";
}

if ($role !== 'all') {
    $where[] = "role = ?";
    $params[] = $role;
    $types .= "s";
}

if ($status !== 'all') {
    if ($status === 'blocked') {
        $where[] = "is_blocked = 1";
    } elseif ($status === 'online') {
        $where[] = "is_blocked = 0 AND last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE) AND last_activity IS NOT NULL";
    } elseif ($status === 'offline') {
        $where[] = "is_blocked = 0 AND (last_activity <= DATE_SUB(NOW(), INTERVAL 5 MINUTE) OR last_activity IS NULL)";
    }
}

$whereClause = empty($where) ? "" : "WHERE " . implode(" AND ", $where);

$sql = "SELECT userID, login, name, email, role, is_blocked, last_activity 
        FROM users
        $whereClause
        ORDER BY last_activity DESC";

$stmt = $connection->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $isOnline = false;
    
    if ($row['is_blocked'] == 0 && !empty($row['last_activity'])) {
        $lastActivity = strtotime($row['last_activity']);
        $currentTime = time();
        $diffSeconds = $currentTime - $lastActivity;
        
        if ($diffSeconds < 300) {
            $isOnline = true;
        }
    }
    
    $users[] = [
        'userID' => $row['userID'],
        'login' => $row['login'],
        'name' => $row['name'],
        'email' => $row['email'],
        'role' => $row['role'],
        'is_blocked' => (int)$row['is_blocked'],
        'is_online' => $isOnline,
        'last_activity' => $row['last_activity']
    ];
}

header('Content-Type: application/json');
echo json_encode(['users' => $users]);
$stmt->close();
?>