<?php
session_start();
require_once(__DIR__ . "/../db.php");
require_once(__DIR__ . "/../checkAuth.php");

checkAuth();

$userID = getUserId();
$role = getUserRole();

if ($role === 'user') {
    $sql = "UPDATE users SET avatar = NULL, avatar_mime_type = NULL WHERE userID = ?";
} else {
    $sql = "UPDATE masters SET avatar = NULL, avatar_mime_type = NULL WHERE userID = ?";
}

$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $userID);

if ($stmt->execute()) {
    $_SESSION['avatar_success'] = 'Аватар удален';
} else {
    $_SESSION['avatar_error'] = 'Ошибка удаления аватара';
}

$stmt->close();

header("Location: " . ($role === 'user' ? "/craftGrodno/3.0/userProfile.php" : "/craftGrodno/3.0/mainSeller.php"));
exit();
?>