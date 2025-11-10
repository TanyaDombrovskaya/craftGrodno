<?php
require_once(__DIR__ . "/../init.php");
require_once(__DIR__ . "/../checkAuth.php");
checkAuth();

function getProductIcon($productName) {
    $icons = [
        'салфетка' => '🧵',
        'вышив' => '🧵',
        'деревянн' => '🔨',
        'доска' => '🔨',
        'резьб' => '🔨',
        'варежк' => '🧶',
        'вязан' => '🧶',
        'шерст' => '🧶',
        'кружка' => '⚱️',
        'ваза' => '⚱️',
        'блюдо' => '⚱️',
        'кера' => '⚱️',
        'глин' => '⚱️',
        'колье' => '💎',
        'бижутери' => '💎',
        'камен' => '💎',
        'сумка' => '🪡',
        'льнян' => '🪡',
        'шить' => '🪡'
    ];
    
    $productNameLower = mb_strtolower($productName, 'UTF-8');
    
    foreach ($icons as $keyword => $icon) {
        if (mb_strpos($productNameLower, $keyword, 0, 'UTF-8') !== false) {
            return $icon;
        }
    }
    
    return '📦';
}

// Получаем userID текущего пользователя
$userID = getUserId();

$sql = "SELECT 
            p.productID,
            p.productName,
            p.aboutProduct,
            p.price,
            p.masterID,
            m.masterName,
            m.phoneNumber,
            c.categoryName,
            p.countOfProduct
        FROM products p
        LEFT JOIN masters m ON p.masterID = m.masterID
        LEFT JOIN category c ON p.categoryID = c.categoryID
        WHERE m.userID = ? AND p.productName IS NOT NULL 
        ORDER BY p.countOfProduct DESC";

$stmt = mysqli_prepare($connection, $sql);
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$products_html = '';

if ($result && $result->num_rows > 0) {
    while($product = $result->fetch_assoc()) {
        $icon = getProductIcon($product['productName']);
        $price = number_format($product['price'], 2, '.', ' ') . ' BYN';
        
        $products_html .= '
        <div class="product-card" data-product-id="' . $product['productID'] . '">
            <div class="product-image">' . $icon . '</div>
            <div class="product-info">
                <h3 class="product-title">' . htmlspecialchars($product['productName']) . '</h3>
                <p class="product-description">' . htmlspecialchars($product['aboutProduct']) . '</p>
                <div class="product-footer">
                    <span class="product-price">' . $price . '</span>
                    <span class="product-count">Осталось: ' . $product['countOfProduct'] . ' шт.</span>
                </div>
                <button class="delete-product-btn" data-product-id="' . $product['productID'] . '" data-product-name="' . htmlspecialchars($product['productName']) . '">
                    Удалить товар
                </button>
            </div>
        </div>';
    }
} else {
    $products_html = '<div class="no-products">У вас пока нет товаров</div>';
}

echo $products_html;