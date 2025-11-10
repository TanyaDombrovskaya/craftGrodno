<?php
require_once(__DIR__ . "/../init.php");

// Получаем категории из базы данных
$sql = "SELECT 
            c.categoryID, 
            c.categoryName, 
            COUNT(p.productID) as product_count 
        FROM category c 
        LEFT JOIN products p ON c.categoryID = p.categoryID 
        WHERE c.categoryID IS NOT NULL 
        GROUP BY c.categoryID, c.categoryName 
        ORDER BY product_count DESC";

$result = $connection->query($sql);

$categories_html = '';

if ($result && $result->num_rows > 0) {
    while($category = $result->fetch_assoc()) {
        $icon = getCategoryIcon($category['categoryName']);
        
        $categories_html .= '
        <div class="category-card">
            <div class="category-icon">' . $icon . '</div>
            <div class="category-name">' . htmlspecialchars($category['categoryName']) . '</div>
            <div class="category-count">' . $category['product_count'] . ' ' . getProductCountText($category['product_count']) . '</div>
        </div>';
    }
}

// Возвращаем HTML с категориями
echo $categories_html;