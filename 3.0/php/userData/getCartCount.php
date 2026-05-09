<?php
session_start();
require_once(__DIR__ . "/../db.php");

// Проверяем авторизацию пользователя
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    echo json_encode(['count' => 0]);
    exit();
}

$userID = $_SESSION['user_id'];

if (!$userID) {
    echo json_encode(['count' => 0]);
    exit();
}

$sql = "SELECT COUNT(*) as count FROM cart WHERE userID = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

header('Content-Type: application/json');
echo json_encode(['count' => $row['count']]);

$stmt->close();
$connection->close();
?>