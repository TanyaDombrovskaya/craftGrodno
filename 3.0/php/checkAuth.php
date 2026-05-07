<?php
session_start();
require_once("db.php");

function checkAuth() {
    global $connection;
    
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        header("Location: /craftGrodno/3.0/loginPage.php");
        exit();
    }
    
    // Проверяем, не заблокирован ли пользователь
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
            // Пользователь заблокирован - завершаем сессию
            session_destroy();
            header("Location: /craftGrodno/3.0/loginPage.php?error=blocked");
            exit();
        }
        
        // Обновляем время активности при каждом запросе
        $updateSql = "UPDATE users SET last_activity = NOW() WHERE userID = ?";
        $updateStmt = $connection->prepare($updateSql);
        $updateStmt->bind_param("i", $userId);
        $updateStmt->execute();
        $updateStmt->close();
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