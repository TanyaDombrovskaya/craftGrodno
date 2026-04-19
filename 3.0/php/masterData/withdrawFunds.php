<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();

if (getUserRole() !== 'seller') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
    exit();
}

$userID = getUserId();
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$method = isset($_POST['method']) ? $_POST['method'] : '';
$details = isset($_POST['details']) ? $_POST['details'] : '';

if ($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Введите корректную сумму']);
    exit();
}

// Получаем текущий баланс
$balanceSql = "SELECT balance, masterID FROM masters WHERE userID = ?";
$balanceStmt = $connection->prepare($balanceSql);
$balanceStmt->bind_param("i", $userID);
$balanceStmt->execute();
$balanceResult = $balanceStmt->get_result();
$master = $balanceResult->fetch_assoc();

if ($master['balance'] < $amount) {
    echo json_encode(['success' => false, 'message' => 'Недостаточно средств для вывода']);
    exit();
}

// Списание средств
$updateSql = "UPDATE masters SET balance = balance - ? WHERE userID = ?";
$updateStmt = $connection->prepare($updateSql);
$updateStmt->bind_param("di", $amount, $userID);

if ($updateStmt->execute()) {
    // Здесь можно добавить запись в таблицу withdrawals для истории выводов
    echo json_encode([
        'success' => true, 
        'message' => "Заявка на вывод {$amount} руб. через {$method} отправлена"
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при выводе средств']);
}

$updateStmt->close();
$balanceStmt->close();
?>