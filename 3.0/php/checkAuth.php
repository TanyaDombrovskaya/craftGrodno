<?php
session_start();
require_once(__DIR__ . "/db.php");

function checkAuth() {
    global $connection;
    
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        header("Location: /craftGrodno/3.0/loginPage.php");
        exit();
    }
    
    // Проверка блокировки при каждом запросе
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $sql = "SELECT is_blocked FROM users WHERE userID = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if ($user && $user['is_blocked'] == 1) {
            session_destroy();
            header("Location: /craftGrodno/3.0/loginPage.php?error=blocked");
            exit();
        }
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
?>