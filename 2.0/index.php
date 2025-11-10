<?php
// index.php - автоматическое перенаправление на нужную страницу
session_start();

// Проверяем, авторизован ли пользователь
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    // Пользователь авторизован - перенаправляем в зависимости от роли
    if ($_SESSION['user_role'] == 'user') {
        header("Location: /craftGrodno/mainUser.php");
    } else if ($_SESSION['user_role'] == 'seller') {
        header("Location: /craftGrodno/mainSeller.php");
    } else {
        // Если роль не определена, на страницу входа
        header("Location: /craftGrodno/loginPage.php");
    }
} else {
    // Пользователь не авторизован - на страницу входа
    header("Location: /craftGrodno/loginPage.php");
}
exit();