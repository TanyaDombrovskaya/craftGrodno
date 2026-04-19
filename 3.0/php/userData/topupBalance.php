<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();

if (getUserRole() !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
    exit();
}

$userID = getUserId();
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

if ($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Введите корректную сумму']);
    exit();
}

$sql = "UPDATE users SET balance = balance + ? WHERE userID = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("di", $amount, $userID);

if ($stmt->execute()) {
    // Получаем новый баланс
    $balanceSql = "SELECT balance FROM users WHERE userID = ?";
    $balanceStmt = $connection->prepare($balanceSql);
    $balanceStmt->bind_param("i", $userID);
    $balanceStmt->execute();
    $balanceResult = $balanceStmt->get_result();
    $balanceRow = $balanceResult->fetch_assoc();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Баланс пополнен',
        'amount' => number_format($amount, 2),
        'new_balance' => number_format($balanceRow['balance'], 2)
    ]);
    $balanceStmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при пополнении']);
}

$stmt->close();
?>