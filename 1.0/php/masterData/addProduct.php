<?php
require_once(__DIR__ . "/../db.php");
require_once(__DIR__ . "/../checkAuth.php");

// Получаем данные из формы
$product_name = $_POST["product_name"];
$product_about = $_POST["product_about"];
$price = $_POST["price"];
$count = $_POST["count"];

$userID = getUserId();

// Сначала получаем masterID и categoryID из таблицы masters
$masterStmt = mysqli_prepare($connection, "SELECT masterID, categoryID FROM masters WHERE userID = ?");
mysqli_stmt_bind_param($masterStmt, "i", $userID);
mysqli_stmt_execute($masterStmt);
$masterResult = mysqli_stmt_get_result($masterStmt);
$masterData = mysqli_fetch_assoc($masterResult);

if (!$masterData) {
    http_response_code(400);
    echo "Ошибка: сначала заполните личные данные мастера";
    exit();
}

$masterID = $masterData['masterID'];
$categoryID = $masterData['categoryID'];

// Вставляем данные в таблицу products
$stmt = mysqli_prepare($connection, "INSERT INTO products (categoryID, masterID, productName, aboutProduct, price, countOfProduct) VALUES (?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "iissdi", $categoryID, $masterID, $product_name, $product_about, $price, $count);

// Выполняем запрос
if (mysqli_stmt_execute($stmt)) {
    header("Location: /craftGrodno/mainSeller.php?success=product");
} else {
    http_response_code(500);
    echo "Ошибка при добавлении товара: " . mysqli_error($connection);
}

mysqli_stmt_close($stmt);
mysqli_stmt_close($masterStmt);