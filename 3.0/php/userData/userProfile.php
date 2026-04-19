<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();

if (getUserRole() !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
    exit();
}

$userID = getUserId();
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if (empty($name) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Неверный формат email']);
    exit();
}

$sql = "UPDATE users SET name = ?, email = ? WHERE userID = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("ssi", $name, $email, $userID);

if ($stmt->execute()) {
    $_SESSION['user_name'] = $name;
    echo json_encode(['success' => true, 'message' => 'Данные обновлены']);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении']);
}

$stmt->close();
?>