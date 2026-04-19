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
            p.image,
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
        
        // Получаем рейтинг товара
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
        
        // Определяем класс для товаров без отзывов
        $rating_class = $review_count == 0 ? 'no-reviews' : '';
        
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
        <div class="product-card" data-product-id="' . $product['productID'] . '">
            <div class="product-image">
                ' . $image_html . '
            </div>
            <div class="product-info">
                <h3 class="product-title">' . htmlspecialchars($product['productName']) . '</h3>
                
                <p class="product-description">' . htmlspecialchars($product['aboutProduct']) . '</p>

                <!-- Рейтинг товара -->
                <div class="product-rating ' . $rating_class . '">
                    <div class="rating-stars">' . displayRatingStars($avg_rating) . '</div>
                    <div class="rating-info">
                        <span class="rating-value">' . $avg_rating . '</span>
                        <span class="rating-count">(' . $review_count . ')</span>
                    </div>
                </div>

                <div class="product-footer">
                    <span class="product-price">' . $price . '</span>
                    <span class="product-count">Осталось: ' . $product['countOfProduct'] . ' шт.</span>
                </div>
                <div class="product-actions">
                    <button class="edit-product-btn" data-product-id="' . $product['productID'] . '" 
                            data-product-name="' . htmlspecialchars($product['productName']) . '" 
                            data-product-about="' . htmlspecialchars($product['aboutProduct']) . '" 
                            data-product-price="' . $product['price'] . '" 
                            data-product-count="' . $product['countOfProduct'] . '">
                        Редактировать
                    </button>
                    <button class="delete-product-btn" data-product-id="' . $product['productID'] . '" data-product-name="' . htmlspecialchars($product['productName']) . '">
                        Удалить
                    </button>
                </div>
            </div>
        </div>';
    }
} else {
    $products_html = '<div class="no-products">У вас пока нет товаров</div>';
}

echo $products_html;

// // Закрываем соединения
// if (isset($rating_stmt)) {
//     $rating_stmt->close();
// }
mysqli_stmt_close($stmt);