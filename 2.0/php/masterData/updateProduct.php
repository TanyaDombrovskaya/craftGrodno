<?php
require_once(__DIR__ . "/../db.php");
require_once(__DIR__ . "/../checkAuth.php");

header('Content-Type: application/json');

// Проверяем авторизацию
checkAuth();

// Получаем данные из формы
$product_id = $_POST["product_id"] ?? '';
$product_name = $_POST["product_name"] ?? '';
$product_about = $_POST["product_about"] ?? '';
$price = $_POST["price"] ?? 0;
$count = $_POST["count"] ?? 0;

// Валидация данных
if (empty($product_id) || empty($product_name) || empty($product_about)) {
    echo json_encode(['success' => false, 'message' => 'Все обязательные поля должны быть заполнены']);
    exit();
}

if (!is_numeric($price) || $price < 0) {
    echo json_encode(['success' => false, 'message' => 'Некорректная цена']);
    exit();
}

if (!is_numeric($count) || $count < 0) {
    echo json_encode(['success' => false, 'message' => 'Некорректное количество']);
    exit();
}

// Проверяем, существует ли товар и принадлежит ли он текущему пользователю
$userID = getUserId();
$checkStmt = mysqli_prepare($connection, 
    "SELECT p.productID 
     FROM products p 
     LEFT JOIN masters m ON p.masterID = m.masterID 
     WHERE p.productID = ? AND m.userID = ?");
mysqli_stmt_bind_param($checkStmt, "ii", $product_id, $userID);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_get_result($checkStmt);

if (mysqli_num_rows($checkResult) === 0) {
    echo json_encode(['success' => false, 'message' => 'Товар не найден или у вас нет прав для его редактирования']);
    mysqli_stmt_close($checkStmt);
    exit();
}
mysqli_stmt_close($checkStmt);

// Обработка загруженного изображения
$image_update = '';
$hasNewImage = false;
$image = null;

if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
    // Проверяем тип файла
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = $_FILES['product_image']['type'];
    
    if (!in_array($file_type, $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Разрешены только изображения JPEG, JPG, PNG, GIF и WebP']);
        exit();
    }
    
    // Проверяем размер файла (максимум 16MB)
    if ($_FILES['product_image']['size'] > 16 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Размер файла не должен превышать 16MB']);
        exit();
    }
    
    // Читаем содержимое файла
    $image = file_get_contents($_FILES['product_image']['tmp_name']);
    if ($image !== false && strlen($image) > 100) {
        $hasNewImage = true;
        $image_update = ", image = ?";
    }
}

// Подготавливаем запрос в зависимости от наличия нового изображения
if ($hasNewImage) {
    $stmt = mysqli_prepare($connection, 
        "UPDATE products SET productName = ?, aboutProduct = ?, price = ?, countOfProduct = ? $image_update WHERE productID = ?");
    
    if ($stmt) {
        $null = NULL;
        mysqli_stmt_bind_param($stmt, "ssdibi", $product_name, $product_about, $price, $count, $null, $product_id);
        mysqli_stmt_send_long_data($stmt, 4, $image);
    }
} else {
    $stmt = mysqli_prepare($connection, 
        "UPDATE products SET productName = ?, aboutProduct = ?, price = ?, countOfProduct = ? WHERE productID = ?");
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssdii", $product_name, $product_about, $price, $count, $product_id);
    }
}

// Выполняем запрос
if ($stmt && mysqli_stmt_execute($stmt)) {
    if (mysqli_affected_rows($connection) > 0) {
        echo json_encode(['success' => true, 'message' => 'Товар успешно обновлен']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Не удалось обновить товар. Возможно, данные не изменились.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении товара: ' . mysqli_error($connection)]);
}

if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}