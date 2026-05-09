<?php
require_once(__DIR__ . "/../init.php");

// Пути к изображениям для категорий
$categoryImages = [
    'Дерево' => './styles/image/categories/wood.jpg',
    'Вязание' => './styles/image/categories/knitting.jpg',
    'Керамика' => './styles/image/categories/ceramics.jpg',
    'Шитье' => './styles/image/categories/sewing.jpg',
    'Бижутерия' => './styles/image/categories/jewelry.jpg',
];

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

$categories = [];
if ($result && $result->num_rows > 0) {
    while($category = $result->fetch_assoc()) {
        $categoryName = $category['categoryName'];
        $category['image'] = $categoryImages[$categoryName] ?? './styles/image/icon.png';
        $categories[] = $category;
    }
}

header('Content-Type: application/json');
echo json_encode($categories);
?>