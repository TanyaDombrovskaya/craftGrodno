<?php

require_once("db.php");

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

// Функция для отображения звезд рейтинга
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