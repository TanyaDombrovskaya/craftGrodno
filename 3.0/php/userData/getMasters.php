<?php
require_once(__DIR__ . "/../init.php");

$sql = "SELECT 
            m.masterID,
            m.masterName,
            m.experience,
            m.aboutMaster,
            m.direction,
            u.avatar,        
            u.avatar_mime_type, 
            u.userID,
            COUNT(p.productID) as actual_products_count
        FROM masters m
        LEFT JOIN users u ON m.userID = u.userID
        LEFT JOIN products p ON m.masterID = p.masterID
        WHERE m.masterName IS NOT NULL 
        GROUP BY m.masterID
        HAVING actual_products_count > 0
        ORDER BY actual_products_count DESC
        LIMIT 3";

$result = $connection->query($sql);

$masters_html = '';

if ($result && $result->num_rows > 0) {
    while($master = $result->fetch_assoc()) {
        // Формируем аватар (фиксированный размер 80x80)
        if (!empty($master['avatar'])) {
            $avatarData = base64_encode($master['avatar']);
            $avatarMime = $master['avatar_mime_type'];
            $avatarHtml = '<img src="data:' . $avatarMime . ';base64,' . $avatarData . '" alt="аватар" class="master-avatar-img">';
        } else {
            $avatarHtml = '<div class="master-avatar">' . getMasterAvatar($master['masterName']) . '</div>';
        }
        
        $experience = formatExperience($master['experience']);
        $actualCount = $master['actual_products_count'];
        
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
            <div class="master-avatar-wrapper">
                ' . $avatarHtml . '
            </div>
            <div class="master-name">' . htmlspecialchars($master['masterName']) . '</div>
            <div class="master-specialty">' . htmlspecialchars($master['direction']) . '</div>
            <div class="master-description">' . htmlspecialchars($master['aboutMaster']) . '</div>

            <div class="master-stats">
                <div class="master-stat">
                    <span class="stat-value">' . $actualCount . '</span>
                    <span class="stat-name">' . getProductCountText($actualCount) . '</span>
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
?>