<?php
session_start();

function checkAuth() {
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        header("Location: /craftGrodno/loginPage.php");
        exit();
    }
}

function getUserLogin() {
    return isset($_SESSION['user_login']) ? $_SESSION['user_login'] : 'User';
}

function getUserRole() {
    return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
}

function getUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}