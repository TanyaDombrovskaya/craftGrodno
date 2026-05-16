<?php
session_start();
require_once(__DIR__ . "/db.php");
require_once(__DIR__ . "/passwordHash.php");

global $passwordHash, $connection;

$login = trim($_POST["login"] ?? '');
$pass = $_POST["password"] ?? '';

$sql = "SELECT * FROM `users` WHERE login = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("s", $login);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    if ($passwordHash->verify($pass, $user["password"])) {
        $_SESSION['user_id'] = $user['userID'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_login'] = $user['login'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['authenticated'] = true;
        
        if ($user["role"] == 'user') {
            header("Location: /craftGrodno/3.0/mainUser.php");
            exit();
        } else if ($user["role"] == "seller") {
            header("Location: /craftGrodno/3.0/mainSeller.php");
            exit();
        } else if ($user["role"] == "admin") {
            header("Location: /craftGrodno/3.0/admin.php");
            exit();
        }
    } else {
        $_SESSION['login_error'] = 'password';
        $_SESSION['previous_login'] = $login;
        header("Location: /craftGrodno/3.0/loginPage.php");
        exit();
    }
} else {
    $_SESSION['login_error'] = 'login';
    header("Location: /craftGrodno/3.0/loginPage.php");
    exit();
}

$stmt->close();
$connection->close();
?>