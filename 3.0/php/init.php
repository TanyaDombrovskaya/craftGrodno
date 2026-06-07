<?php
require_once(__DIR__ . "/db.php");

// Обновление последней активности пользователя (только один раз)
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    
    // Проверяем, существует ли поле last_activity
    $checkColumn = $connection->query("SHOW COLUMNS FROM users LIKE 'last_activity'");
    if ($checkColumn->num_rows > 0) {
        $columnInfo = $checkColumn->fetch_assoc();
        
        // Убираем ON UPDATE CURRENT_TIMESTAMP если он есть
        if (strpos($columnInfo['Extra'], 'on update CURRENT_TIMESTAMP') !== false) {
            $connection->query("ALTER TABLE users MODIFY last_activity TIMESTAMP NULL DEFAULT NULL");
        }
    }
    
    // Обновляем last_activity
    $updateActivitySql = "UPDATE users SET last_activity = NOW() WHERE userID = ?";
    $updateStmt = $connection->prepare($updateActivitySql);
    $updateStmt->bind_param("i", $userId);
    $updateStmt->execute();
    $updateStmt->close();
}

// Общие функции
function getProductCountText($count) {
    if ($count % 10 == 1 && $count % 100 != 11) {
        return 'товар';
    } elseif (in_array($count % 10, [2, 3, 4]) && !in_array($count % 100, [12, 13, 14])) {
        return 'товара';
    } else {
        return 'товаров';
    }
}

function formatExperience($experience) {
    if ($experience == 1) {
        return '1 год';
    } elseif ($experience >= 2 && $experience <= 4) {
        return $experience . ' года';
    } else {
        return $experience . ' лет';
    }
}

function getMasterAvatar($masterName) {
    $words = explode(' ', $masterName);
    $avatar = '';
    
    foreach ($words as $word) {
        if (!empty($word)) {
            $avatar .= mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8');
            if (mb_strlen($avatar, 'UTF-8') >= 2) {
                break;
            }
        }
    }
    
    return $avatar ?: 'МС';
}

function getCategoryIcon($categoryName) {
    $icons = [
        'Дерево' => '🔨',
        'Вязание' => '🧶',
        'Керамика' => '⚱️',
        'Шитье' => '🧵',
        'Бижутерия' => '💎'
    ];
    
    return $icons[$categoryName] ?? '📦';
}

function displayRatingStars($rating) {
    $stars = '';
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
    
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $fullStars) {
            $stars .= '<span class="rating-star filled">★</span>';
        } elseif ($i == $fullStars + 1 && $hasHalfStar) {
            $stars .= '<span class="rating-star half">★</span>';
        } else {
            $stars .= '<span class="rating-star">★</span>';
        }
    }
    return $stars;
}

function displayMasterRatingStars($rating) {
    $stars = '';
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
    
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $fullStars) {
            $stars .= '<span class="master-rating-star filled">★</span>';
        } elseif ($i == $fullStars + 1 && $hasHalfStar) {
            $stars .= '<span class="master-rating-star half">★</span>';
        } else {
            $stars .= '<span class="master-rating-star">★</span>';
        }
    }
    return $stars;
}
?>