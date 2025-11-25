<?php
require_once(__DIR__ . "/../init.php");

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    echo '<div class="no-reviews"><p>Товар не найден</p></div>';
    exit();
}

// Используем столбец created_at для сортировки и вывода даты
$sql = "SELECT 
            r.reviewID,
            r.rating,
            r.review,
            r.created_at,
            u.name as userName
        FROM reviews r
        LEFT JOIN users u ON r.userID = u.userID
        WHERE r.productID = ?
        ORDER BY r.created_at DESC"; // Сортируем по дате создания

$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

$reviews_html = '';

if ($result && $result->num_rows > 0) {
    while($review = $result->fetch_assoc()) {
        // Форматируем дату
        $date = date('d.m.Y H:i', strtotime($review['created_at']));
        
        // Создаем звезды рейтинга
        $stars_html = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $review['rating']) {
                $stars_html .= '<span class="rating-star filled">★</span>';
            } else {
                $stars_html .= '<span class="rating-star">★</span>';
            }
        }
        
        $reviews_html .= '
        <div class="review-item">
            <div class="review-header">
                <div class="reviewer-info">
                    <div class="reviewer-name">' . htmlspecialchars($review['userName']) . '</div>
                    <div class="review-date">' . $date . '</div>
                </div>
                <div class="review-rating">' . $stars_html . '</div>
            </div>
            <div class="review-text">' . nl2br(htmlspecialchars($review['review'])) . '</div>
        </div>';
    }
} else {
    $reviews_html = '<div class="no-reviews"><p>Пока нет отзывов. Будьте первым!</p></div>';
}

echo $reviews_html;
$stmt->close();