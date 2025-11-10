<?php
session_start();
require_once("db.php");

$login = $_POST["login"];
$pass = $_POST["password"];

unset($_SESSION['login_error']);
unset($_SESSION['previous_login']);

$sql = "SELECT * FROM `users` WHERE login = ?";
$stmt = $connection->prepare($sql);

if ($stmt) {
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();
    $hash_pass = md5($pass);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if ($hash_pass == $user["password"]) {
            $_SESSION['user_id'] = $user['userID'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_login'] = $user['login'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['authenticated'] = true;
            
            if ($user["role"] == 'user') {
                header("Location: /craftGrodno/mainUser.php");
            } else if ($user["role"] == "seller") {
                header("Location: /craftGrodno/mainSeller.php");
            }
            exit();
        } else {
            $_SESSION['login_error'] = 'password';
            $_SESSION['previous_login'] = $login;
            header("Location: /craftGrodno/loginPage.php");
            exit();
        }
    } else {
        $_SESSION['login_error'] = 'login';
        header("Location: /craftGrodno/loginPage.php");
        exit();
    }
}

$connection->close();