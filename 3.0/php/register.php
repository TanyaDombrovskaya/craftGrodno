<?php
session_start();
require_once(__DIR__ . "/db.php");
require_once(__DIR__ . "/passwordHash.php");

global $passwordHash, $connection;

$login = trim($_POST['login'] ?? '');
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$pass = $_POST['password'] ?? '';
$confirm = $_POST['confirm-password'] ?? '';
$userType = $_POST['user_type'] ?? 'user';

$errors = [];

if (empty($login)) $errors[] = "Логин обязателен";
if (empty($name)) $errors[] = "Имя обязательно";
if (empty($email)) $errors[] = "Email обязателен";
if (empty($pass)) $errors[] = "Пароль обязателен";
if ($pass !== $confirm) $errors[] = "Пароли не совпадают";
if (strlen($pass) < 8) $errors[] = "Пароль должен быть не менее 8 символов";

if (!empty($errors)) {
    $_SESSION['reg_errors'] = $errors;
    $_SESSION['form_data'] = ['login' => $login, 'name' => $name, 'email' => $email];
    header("Location: /craftGrodno/3.0/registerPage.php");
    exit();
}

// Проверка существования пользователя
$check = $connection->prepare("SELECT userID FROM users WHERE login = ? OR email = ?");
$check->bind_param("ss", $login, $email);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    $_SESSION['reg_errors'] = ["Пользователь с таким логином или email уже существует"];
    $_SESSION['form_data'] = ['login' => $login, 'name' => $name, 'email' => $email];
    header("Location: /craftGrodno/3.0/registerPage.php");
    exit();
}
$check->close();

$hashed = $passwordHash->hash($pass);

$stmt = $connection->prepare("INSERT INTO users (login, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $login, $name, $email, $hashed, $userType);

if ($stmt->execute()) {
    $_SESSION['reg_success'] = "Регистрация успешна! Войдите в систему.";
    header("Location: /craftGrodno/3.0/loginPage.php");
    exit();
} else {
    $_SESSION['reg_errors'] = ["Ошибка регистрации"];
    header("Location: /craftGrodno/3.0/registerPage.php");
    exit();
}

$stmt->close();
$connection->close();
?>