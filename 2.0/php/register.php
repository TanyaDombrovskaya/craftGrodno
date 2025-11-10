<?php
require_once("db.php");

$login = $_POST['login'];
$name = $_POST['name'];
$email = $_POST['email'];
$pass = $_POST['password'];
$userType = $_POST['user_type'] ?? 'user';

$hashPass = md5($pass);

$sql = "INSERT INTO `users` (login, name, email, password, role) VALUES ('$login', '$name', '$email', '$hashPass', '$userType')";

if ($connection->query($sql)) {
    header("Location: /craftGrodno/2.0/loginPage.php");
} else {
    echo "Ошибка при регистрации: " . $connection->error;
}

$connection->close();