<?php
require_once(__DIR__ . "/../init.php");

$sql = "SELECT 
            m.masterID,
            m.masterName,
            m.experience,
            m.countOfProducts,
            m.aboutMaster,
            m.direction,
            u.userID
        FROM masters m
        LEFT JOIN users u ON m.userID = u.userID
        WHERE m.masterName IS NOT NULL 
        AND m.countOfProducts > 0
        ORDER BY m.countOfProducts DESC
        LIMIT 3";

$result = $connection->query($sql);

$masters_html = '';

if ($result && $result->num_rows > 0) {
    while($master = $result->fetch_assoc()) {
        $avatar = getMasterAvatar($master['masterName']);
        $experience = formatExperience($master['experience']);
        $productCountText = $master['countOfProducts'] . ' ' . getProductCountText($master['countOfProducts']);
        
        // Получаем средний рейтинг мастера через его продукты
        $rating_sql = "SELECT AVG(r.rating) as avg_rating, COUNT(*) as review_count 
                       FROM reviews r
                       INNER JOIN products p ON r.productID = p.productID
                       WHERE p.masterID = ?";
        $rating_stmt = $connection->prepare($rating_sql);
        $rating_stmt->bind_param("i", $master['masterID']);
        $rating_stmt->execute();
        $rating_result = $rating_stmt->get_result();
        $rating_data = $rating_result->fetch_assoc();
        
        $avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
        $review_count = $rating_data['review_count'];

        $masters_html .= '
        <div class="master-card">
            <div class="master-avatar">' . $avatar . '</div>
            <div class="master-name">' . htmlspecialchars($master['masterName']) . '</div>
            <div class="master-specialty">' . htmlspecialchars($master['direction']) . '</div>
            <div class="master-description">' . htmlspecialchars($master['aboutMaster']) . '</div>

            <div class="master-stats">
                <div class="master-stat">
                    <span class="stat-value">' . $master['countOfProducts'] . '</span>
                    <span class="stat-name">' . getProductCountText($master['countOfProducts']) . '</span>
                </div>
                <!-- Рейтинг мастера -->
                <div class="master-rating">
                    <div class="master-rating-info">
                        <span class="master-rating-value">' . $avg_rating . '</span>
                        <span class="master-rating-count">(' . $review_count . ')</span>
                    </div>
                    <div class="master-rating-stars">' . displayMasterRatingStars($avg_rating) . '</div>
                </div>
                <div class="master-stat">
                    <span class="stat-value">' . $experience . '</span>
                    <span class="stat-name">Опыт</span>
                </div>
            </div>
            
            <a href="masterPage.php?id=' . $master['masterID'] . '" class="view-master-button">Смотреть работы</a>
        </div>';
    }
}

echo $masters_html;

// Закрываем соединение
if (isset($rating_stmt)) {
    $rating_stmt->close();
}