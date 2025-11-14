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

// Обработка загруженного изображения
$image = null;
$hasImage = false;

if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
    // Проверяем тип файла
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = $_FILES['product_image']['type'];
    
    if (!in_array($file_type, $allowed_types)) {
        http_response_code(400);
        echo "Ошибка: разрешены только изображения JPEG, JPG, PNG и GIF";
        exit();
    }
    
    // Проверяем размер файла (максимум 16MB)
    if ($_FILES['product_image']['size'] > 16 * 1024 * 1024) {
        http_response_code(400);
        echo "Ошибка: размер файла не должен превышать 16MB";
        exit();
    }
    
    // Читаем содержимое файла
    $image = file_get_contents($_FILES['product_image']['tmp_name']);
    if ($image === false) {
        http_response_code(500);
        echo "Ошибка при чтении файла изображения";
        exit();
    }
    
    $hasImage = true;
}

// Вставляем данные в таблицу products
try {
    if ($hasImage) {
        // Способ 1: Используем HEX() функцию для BLOB данных
        $image_hex = bin2hex($image);
        $product_name_escaped = mysqli_real_escape_string($connection, $product_name);
        $product_about_escaped = mysqli_real_escape_string($connection, $product_about);
        
        $sql = "INSERT INTO products (categoryID, masterID, productName, aboutProduct, price, countOfProduct, image) 
                VALUES ($categoryID, $masterID, '$product_name_escaped', '$product_about_escaped', $price, $count, 0x$image_hex)";
        
        if (mysqli_query($connection, $sql)) {
            header("Location: /craftGrodno/2.0/mainSeller.php?success=product");
            exit();
        } else {
            throw new Exception("Ошибка SQL: " . mysqli_error($connection));
        }
        
    } else {
        // Если изображение не загружено
        $stmt = mysqli_prepare($connection, "INSERT INTO products (categoryID, masterID, productName, aboutProduct, price, countOfProduct) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iissdi", $categoryID, $masterID, $product_name, $product_about, $price, $count);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: /craftGrodno/2.0/mainSeller.php?success=product");
            exit();
        } else {
            throw new Exception("Ошибка выполнения запроса: " . mysqli_stmt_error($stmt));
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo "Ошибка при добавлении товара: " . $e->getMessage();
    error_log("Product addition error: " . $e->getMessage());
}

if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}
mysqli_stmt_close($masterStmt);