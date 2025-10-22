<?php
require_once(__DIR__ . "/../init.php");

// Функция для получения иконки продукта на основе названия
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
        'керамик' => '⚱️',
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
    
    return '📦'; // Иконка по умолчанию
}

// Получаем топ-3 товара по количеству
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
        WHERE p.productName IS NOT NULL 
        ORDER BY p.countOfProduct DESC 
        LIMIT 3";

$result = $connection->query($sql);

$products_html = '';

if ($result && $result->num_rows > 0) {
    while($product = $result->fetch_assoc()) {
        $icon = getProductIcon($product['productName']);
        $price = number_format($product['price'], 2, '.', ' ') . ' руб.';
        
        // ОБНОВЛЕННАЯ СТРОКА С КНОПКОЙ - добавлены data-атрибуты
        $products_html .= '
        <div class="product-card">
            <div class="product-image">' . $icon . '</div>
            <div class="product-info">
                <div class="product-title">' . htmlspecialchars($product['productName']) . '</div>
                <div class="product-description">' . htmlspecialchars($product['aboutProduct']) . '</div>
                <div class="product-footer">
                    <div class="product-price">' . $price . '</div>
                    <button class="add-to-cart" 
                            data-product-id="' . $product['productID'] . '"
                            data-seller-name="' . htmlspecialchars($product['masterName']) . '"
                            data-seller-phone="' . htmlspecialchars($product['phoneNumber']) . '">
                        Связаться с продавцом
                    </button>
                </div>
            </div>
        </div>';
    }
}

// Возвращаем HTML с товарами
echo $products_html;