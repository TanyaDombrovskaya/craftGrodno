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
        WHERE p.masterID = ? AND p.productName IS NOT NULL 
        ORDER BY p.countOfProduct DESC";

$stmt = mysqli_prepare($connection, $sql);
mysqli_stmt_bind_param($stmt, "i", $masterID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && $result->num_rows > 0): 
    while($product = $result->fetch_assoc()): 
        $icon = getProductIcon($product['productName']);
        $price = number_format($product['price'], 2, '.', ' ') . ' руб.';
        
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
?>
        <div class="product-card">
            <div class="product-image"><?php echo $image_html; ?></div>
            <div class="product-info">
                <h3 class="product-title"><a href="./productCard.php?id=<?php echo $product['productID']; ?>"><?php echo htmlspecialchars($product['productName']); ?></a></h3>
                
                <!-- Рейтинг товара -->
                <div class="product-rating">
                    <div class="product-rating-stars"><?php echo displayRatingStars($avg_rating); ?></div>
                    <div class="product-rating-info">
                        <span class="product-rating-value"><?php echo $avg_rating; ?></span>
                        <span class="product-rating-count">(<?php echo $review_count; ?>)</span>
                    </div>
                </div>
                
                <p class="product-description"><?php echo htmlspecialchars($product['aboutProduct']); ?></p>
                <div class="product-footer">
                    <div class="product-price"><?php echo $price; ?></div>
                    <div class="product-stock">В наличии: <?php echo $product['countOfProduct']; ?> шт.</div>
                    <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['productID']; ?>)">
                        В корзину 🛒
                    </button>
                </div>
            </div>
        </div>
<?php 
    endwhile; 
else: 
?>
    <div class="no-products">
        <h3>Товары отсутствуют</h3>
        <p>У этого мастера пока нет товаров в продаже.</p>
    </div>
<?php endif; 
mysqli_stmt_close($stmt);