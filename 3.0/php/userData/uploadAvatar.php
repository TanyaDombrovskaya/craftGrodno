<?php
session_start();
require_once(__DIR__ . "/../db.php");
require_once(__DIR__ . "/../checkAuth.php");

checkAuth();

$userID = getUserId();
$role = getUserRole();

// Разрешенные типы файлов
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 2 * 1024 * 1024; // 2MB

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['avatar_error'] = 'Ошибка загрузки файла';
        header("Location: " . ($role === 'user' ? "/craftGrodno/3.0/userProfile.php" : "/craftGrodno/3.0/mainSeller.php"));
        exit();
    }
    
    // Проверка типа файла
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $fileType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($fileType, $allowedTypes)) {
        $_SESSION['avatar_error'] = 'Разрешены только изображения (JPG, PNG, GIF, WEBP)';
        header("Location: " . ($role === 'user' ? "/craftGrodno/3.0/userProfile.php" : "/craftGrodno/3.0/mainSeller.php"));
        exit();
    }
    
    // Проверка размера
    if ($file['size'] > $maxSize) {
        $_SESSION['avatar_error'] = 'Размер файла не должен превышать 2MB';
        header("Location: " . ($role === 'user' ? "/craftGrodno/3.0/userProfile.php" : "/craftGrodno/3.0/mainSeller.php"));
        exit();
    }
    
    // Читаем содержимое файла
    $avatarData = file_get_contents($file['tmp_name']);
    
    if ($avatarData === false) {
        $_SESSION['avatar_error'] = 'Ошибка чтения файла';
        header("Location: " . ($role === 'user' ? "/craftGrodno/3.0/userProfile.php" : "/craftGrodno/3.0/mainSeller.php"));
        exit();
    }
    
    // Обновляем БД
    if ($role === 'user') {
        $sql = "UPDATE users SET avatar = ?, avatar_mime_type = ? WHERE userID = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ssi", $avatarData, $fileType, $userID);
    } else {
        $sql = "UPDATE masters SET avatar = ?, avatar_mime_type = ? WHERE userID = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ssi", $avatarData, $fileType, $userID);
    }
    
    if ($stmt->execute()) {
        $_SESSION['avatar_success'] = 'Аватар успешно обновлен';
    } else {
        $_SESSION['avatar_error'] = 'Ошибка сохранения в базе данных';
    }
    
    $stmt->close();
    
    header("Location: " . ($role === 'user' ? "/craftGrodno/3.0/userProfile.php" : "/craftGrodno/3.0/mainSeller.php"));
    exit();
}
?>