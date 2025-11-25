<?php
require_once(__DIR__ . "/../init.php");

// Получаем ID продукта из GET параметра
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    echo '
    <div class="product-details">
        <div class="product-not-found">
            <h2>Товар не найден</h2>
            <p>Извините, запрашиваемый товар не существует или был удален.</p>
            <a href="mainUserPage.php" class="back-button">Вернуться на главную</a>
        </div>
    </div>';
    exit();
}

// Функция для получения иконки продукта
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

// Запрос для получения информации о продукте и мастере
$sql = "SELECT 
            p.productID,
            p.productName,
            p.aboutProduct,
            p.price,
            p.countOfProduct,
            p.image,
            p.categoryID,
            p.masterID,
            m.masterName,
            m.phoneNumber,
            m.direction,
            m.aboutMaster,
            m.experience,
            m.countOfProducts as masterProductCount,
            c.categoryName
        FROM products p
        LEFT JOIN masters m ON p.masterID = m.masterID
        LEFT JOIN category c ON p.categoryID = c.categoryID
        WHERE p.productID = ?";

$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $product = $result->fetch_assoc();
    
    // Получаем средний рейтинг товара
    $rating_sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
                   FROM reviews 
                   WHERE productID = ?";
    $rating_stmt = $connection->prepare($rating_sql);
    $rating_stmt->bind_param("i", $product_id);
    $rating_stmt->execute();
    $rating_result = $rating_stmt->get_result();
    $rating_data = $rating_result->fetch_assoc();
    
    $avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
    $review_count = $rating_data['review_count'];
    
    // Получаем средний рейтинг мастера
    $master_rating_sql = "SELECT AVG(r.rating) as avg_rating, COUNT(r.reviewID) as review_count
                          FROM reviews r
                          JOIN products p ON r.productID = p.productID
                          WHERE p.masterID = ?";
    $master_rating_stmt = $connection->prepare($master_rating_sql);
    $master_rating_stmt->bind_param("i", $product['masterID']);
    $master_rating_stmt->execute();
    $master_rating_result = $master_rating_stmt->get_result();
    $master_rating_data = $master_rating_result->fetch_assoc();
    
    $master_avg_rating = $master_rating_data['avg_rating'] ? round($master_rating_data['avg_rating'], 1) : 0;
    $master_review_count = $master_rating_data['review_count'];
    
    // Форматирование цены
    $price = number_format($product['price'], 2, '.', ' ') . ' руб.';
    
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
                $image_html = '<img src="' . $image_src . '" alt="' . htmlspecialchars($product['productName']) . '" class="product-main-image" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">';
                $image_html .= '<div class="product-image-icon" style="display:none;">' . getProductIcon($product['productName']) . '</div>';
            } else {
                throw new Exception("Not an image: " . $mime_type);
            }
        } catch (Exception $e) {
            $image_html = '<div class="product-image-icon">' . getProductIcon($product['productName']) . '</div>';
        }
    } else {
        $image_html = '<div class="product-image-icon">' . getProductIcon($product['productName']) . '</div>';
    }
    
    // Определение статуса наличия с указанием количества
    $stock_status = '';
    $stock_class = '';
    $stock_count = '';
    
    if ($product['countOfProduct'] > 10) {
        $stock_status = 'В наличии: ';
        $stock_class = 'stock-available';
        $stock_count = $product['countOfProduct'] . ' шт.';
    } elseif ($product['countOfProduct'] > 0) {
        $stock_status = 'Мало ';
        $stock_class = 'stock-low';
        $stock_count = $product['countOfProduct'] . ' шт.';
    } else {
        $stock_status = 'Нет в наличии ';
        $stock_class = 'stock-out';
        $stock_count = '';
    }
    
    // Вывод HTML
    echo '
    <div class="product-details">
        <div class="product-main-container">
            <!-- Основной контейнер с двумя колонками -->
            <div class="product-content">
                <!-- Левая колонка - изображение -->
                <div class="product-image-section">
                    <div class="product-image-container">
                        ' . $image_html . '
                    </div>
                </div>
                
                <!-- Правая колонка - информация -->
                <div class="product-info-section">
                    <h1 class="product-title">' . htmlspecialchars($product['productName']) . '</h1>
                    <div class="product-category">' . htmlspecialchars($product['categoryName']) . '</div>
                    
                    <!-- Средний рейтинг товара -->
                    <div class="product-rating-section">
                        <div class="rating-stars">' . displayRatingStars($avg_rating) . '</div>
                        <div class="rating-info">
                            <span class="rating-value">' . $avg_rating . '</span>
                            <span class="rating-count">(' . $review_count . ' отзывов)</span>
                        </div>
                    </div>
                    
                    <div class="product-price">' . $price . '</div>
                    
                    <div class="product-stock">
                        <span class="' . $stock_class . '">' . $stock_status . $stock_count . '</span>
                    </div>
                    
                    <div class="product-description">
                        <div class="description-title">Описание товара</div>
                        <div class="description-text">' . nl2br(htmlspecialchars($product['aboutProduct'])) . '</div>
                    </div>
                    
                    <div class="product-actions">
                        <button class="add-to-cart" 
                                data-seller-name="' . htmlspecialchars($product['masterName']) . '"
                                data-seller-phone="' . htmlspecialchars($product['phoneNumber']) . '">
                            Связаться с продавцом
                        </button>
                        <a href="allProducts.php" class="back-to-products">Все товары</a>
                    </div>
                </div>
            </div>
            
            <!-- Информация о мастере -->
            <div class="master-section">
                <h2 class="section-title">О мастере</h2>
                <div class="master-info">
                    <div class="master-avatar-large">' . getMasterAvatar($product['masterName']) . '</div>
                    <div class="master-details">
                        <div class="master-name"><a href="./masterPage.php?id=' . $product['masterID'] . '">' . htmlspecialchars($product['masterName']) . '</a></div>
                        
                        <!-- Рейтинг мастера -->
                        <div class="master-rating">
                            <div class="rating-stars small">' . displayRatingStars($master_avg_rating) . '</div>
                            <div class="rating-info">
                                <span class="rating-value">' . $master_avg_rating . '</span>
                                <span class="rating-count">(' . $master_review_count . ' отзывов)</span>
                            </div>
                        </div>
                        
                        <div class="master-direction">' . htmlspecialchars($product['direction']) . '</div>
                        <div class="master-about">' . nl2br(htmlspecialchars($product['aboutMaster'])) . '</div>
                    </div>
                </div>
                
                <div class="master-stats-detailed">
                    <div class="master-stat-detailed">
                        <span class="stat-value-detailed">' . $product['masterProductCount'] . '</span>
                        <span class="stat-name-detailed">Товаров в магазине</span>
                    </div>
                    <div class="master-stat-detailed">
                        <span class="stat-value-detailed">' . formatExperience($product['experience']) . '</span>
                        <span class="stat-name-detailed">Опыт работы</span>
                    </div>
                </div>
            </div>
        </div>
    </div>';
    
} else {
    echo '
    <div class="product-details">
        <div class="product-not-found">
            <h2>Товар не найден</h2>
            <p>Извините, запрашиваемый товар не существует или был удален.</p>
            <a href="mainUserPage.php" class="back-button">Вернуться на главную</a>
        </div>
    </div>';
}

$stmt->close();
// Закрываем дополнительные statement'ы если они были созданы
if (isset($rating_stmt)) $rating_stmt->close();
if (isset($master_rating_stmt)) $master_rating_stmt->close();