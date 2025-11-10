<?php
require_once(__DIR__ . "/../init.php");

// Получаем всех мастеров с товарами
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
        ORDER BY m.countOfProducts DESC";

$result = $connection->query($sql);

$masters_html = '';

if ($result && $result->num_rows > 0) {
    while($master = $result->fetch_assoc()) {
        $avatar = getMasterAvatar($master['masterName']);
        $experience = formatExperience($master['experience']);
        $productCountText = $master['countOfProducts'] . ' ' . getProductCountText($master['countOfProducts']);
        
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
                <div class="master-stat">
                    <span class="stat-value">' . $experience . '</span>
                    <span class="stat-name">Опыт</span>
                </div>
            </div>
            
            <a href="masterPage.php?id=' . $master['masterID'] . '" class="view-master-button">Смотреть работы</a>
        </div>';
    }
} else {
    $masters_html = '
    <div class="no-masters">
        <h3>Мастера не найдены</h3>
        <p>В данный момент нет активных мастеров на платформе.</p>
    </div>';
}

// Возвращаем HTML с мастерами
echo $masters_html;