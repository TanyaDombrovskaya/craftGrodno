<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();

if (getUserRole() !== 'seller') {
    echo json_encode(['balance' => 0]);
    exit();
}

$userID = getUserId();

$sql = "SELECT balance FROM masters WHERE userID = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$master = $result->fetch_assoc();

header('Content-Type: application/json');
echo json_encode(['balance' => number_format($master['balance'] ?? 0, 2)]);

$stmt->close();
?>