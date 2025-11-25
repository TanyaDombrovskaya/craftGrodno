<?php
require_once(__DIR__ . "/../init.php");

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
    
    return '📦';
}

$sql = "SELECT 
            p.productID,
            p.productName,
            p.aboutProduct,
            p.price,
            p.masterID,
            p.image,
            m.masterName,
            m.phoneNumber,
            c.categoryName,
            p.countOfProduct
        FROM products p
        LEFT JOIN masters m ON p.masterID = m.masterID
        LEFT JOIN category c ON p.categoryID = c.categoryID
        WHERE p.productName IS NOT NULL 
        ORDER BY p.countOfProduct DESC 
        LIMIT 6";

$result = $connection->query($sql);

$products_html = '';

if ($result && $result->num_rows > 0) {
    while($product = $result->fetch_assoc()) {
        $icon = getProductIcon($product['productName']);
        $price = number_format($product['price'], 2, '.', ' ') . ' руб.';
        
        // Получаем средний рейтинг товара
        $rating_sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
                       FROM reviews 
                       WHERE productID = ?";
        $rating_stmt = $connection->prepare($rating_sql);
        $rating_stmt->bind_param("i", $product['productID']);
        $rating_stmt->execute();
        $rating_result = $rating_stmt->get_result();
        $rating_data = $rating_result->fetch_assoc();
        
        $avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
        $review_count = $rating_data['review_count'];
        
        // Обработка изображения
        $image_html = '';
        $image_size = isset($product['image']) ? strlen($product['image']) : 0;
        
        if ($image_size > 100) {
            try {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime_type = $finfo->buffer($product['image']);
                
                if (strpos($mime_type, 'image/') === 0) {
                    $image_data = base64_encode($product['image']);
                    $image_src = 'data:' . $mime_type . ';base64,' . $image_data;
                    $image_html = '<img src="' . $image_src . '" alt="' . htmlspecialchars($product['productName']) . '" class="product-image-img" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">';
                    $image_html .= '<div class="product-image-icon" style="display:none;">' . $icon . '</div>';
                } else {
                    throw new Exception("Not an image: " . $mime_type);
                }
            } catch (Exception $e) {
                $image_html = '<div class="product-image-icon">' . $icon . '</div>';
            }
        } else {
            $image_html = '<div class="product-image-icon">' . $icon . '</div>';
        }
        
        $products_html .= '
        <div class="product-card">
            <div class="product-image">
                ' . $image_html . '
            </div>
            <div class="product-info">
                <div class="product-title"><a href="./productCard.php?id=' . $product['productID'] . '">' . htmlspecialchars($product['productName']) . '</a></div>
                
                <!-- Рейтинг товара -->
                <div class="product-rating">
                    <div class="rating-stars">' . displayRatingStars($avg_rating) . '</div>
                    <div class="rating-info">
                        <span class="rating-value">' . $avg_rating . '</span>
                        <span class="rating-count">(' . $review_count . ')</span>
                    </div>
                </div>
                
                <div class="product-description">' . htmlspecialchars(mb_substr($product['aboutProduct'], 0, 100)) . (mb_strlen($product['aboutProduct']) > 100 ? '...' : '') . '</div>
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
        
        // Закрываем statement для рейтинга
        $rating_stmt->close();
    }
}

echo $products_html;