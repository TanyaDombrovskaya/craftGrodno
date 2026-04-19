<?php
require_once(__DIR__ . "/../init.php");
require_once(__DIR__ . "/../checkAuth.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /craftGrodno/2.0/productCard.php");
    exit();
}

checkAuth();

if (getUserRole() !== 'user') {
    header("Location: /craftGrodno/2.0/loginPage.php");
    exit();
}

$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';
$user_id = $_SESSION['user_id'];

if ($product_id <= 0) {
    $_SESSION['review_error'] = "Ошибка товара";
    header("Location: /craftGrodno/2.0/productCard.php?id=" . $product_id);
    exit();
}

// Добавляем отзыв
$sql = "INSERT INTO reviews (productID, userID, rating, review) VALUES (?, ?, ?, ?)";
$stmt = $connection->prepare($sql);
$stmt->bind_param("iiis", $product_id, $user_id, $rating, $review_text);

if ($stmt->execute()) {
    $_SESSION['review_success'] = "Ваш отзыв успешно добавлен";
} else {
    $_SESSION['review_error'] = "Произошла ошибка при добавлении отзыва";
}

header("Location: /craftGrodno/2.0/productCard.php?id=" . $product_id);
exit();