<?php
session_start();
require_once("db.php");

// Обновляем время последней активности при выходе
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $updateSql = "UPDATE users SET last_activity = NULL WHERE userID = ?";
    $updateStmt = $connection->prepare($updateSql);
    $updateStmt->bind_param("i", $userId);
    $updateStmt->execute();
    $updateStmt->close();
}

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

header("Location: /craftGrodno/3.0/loginPage.php");
exit();
?>