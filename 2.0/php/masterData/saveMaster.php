<?php
require_once(__DIR__ . "/../db.php");
require_once(__DIR__ . "/../checkAuth.php");

// Получаем данные из формы
$name = $_POST["master_name"];
$direction = $_POST["direction"];
$categoryID = $_POST["category"];
$phone = $_POST["phone"];
$about = $_POST["about"];
$experience = $_POST["experience"];
$userID = getUserId();

// Обновляем существующую запись
$stmt = mysqli_prepare($connection, "UPDATE masters SET masterName = ?, direction = ?, categoryID = ?, phoneNumber = ?, aboutMaster = ?, experience = ? WHERE userID = ?");
mysqli_stmt_bind_param($stmt, "ssissii", $name, $direction, $categoryID, $phone, $about, $experience, $userID);

// Выполняем запрос
if (mysqli_stmt_execute($stmt)) {
    // Обновляем имя пользователя в сессии
    $_SESSION['user_name'] = $name;
    header("Location: /craftGrodno/2.0/mainSeller.php?success=master");
} else {
    http_response_code(500);
    echo "Ошибка при сохранении данных: " . mysqli_error($connection);
}

mysqli_stmt_close($stmt);