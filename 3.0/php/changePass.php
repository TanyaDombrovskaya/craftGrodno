<?php
session_start();
require_once(__DIR__ . "/db.php");
require_once(__DIR__ . "/passwordHash.php");

global $passwordHash, $connection;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /craftGrodno/3.0/forgotPage.php");
    exit();
}

$login = trim($_POST["login"] ?? '');
$email = trim($_POST["email"] ?? '');
$new_password = $_POST["new_password"] ?? '';

$_SESSION['previous_login'] = $login;
$_SESSION['previous_email'] = $email;

if (strlen($new_password) < 8) {
    $_SESSION['forgot_error'] = 'Пароль должен быть не менее 8 символов';
    $_SESSION['error_field'] = 'password';
    header("Location: /craftGrodno/3.0/forgotPage.php");
    exit();
}

$sql = "SELECT * FROM users WHERE login = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("s", $login);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['forgot_error'] = 'Пользователь не найден';
    $_SESSION['error_field'] = 'login';
    header("Location: /craftGrodno/3.0/forgotPage.php");
    exit();
}

if ($email != $user['email']) {
    $_SESSION['forgot_error'] = 'Email не совпадает';
    $_SESSION['error_field'] = 'email';
    header("Location: /craftGrodno/3.0/forgotPage.php");
    exit();
}

$hashed = $passwordHash->hash($new_password);

$update = $connection->prepare("UPDATE users SET password = ? WHERE login = ?");
$update->bind_param("ss", $hashed, $login);
$update->execute();
$update->close();

unset($_SESSION['previous_login']);
unset($_SESSION['previous_email']);
$_SESSION['success_message'] = "Пароль успешно изменен!";
header("Location: /craftGrodno/3.0/loginPage.php");
exit();

$connection->close();
?>