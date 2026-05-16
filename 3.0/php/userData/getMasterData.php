<?php

function getMasterData($masterID) {
    global $connection;
    
    $sql = "SELECT 
                m.masterID,
                m.masterName,
                m.direction,
                m.aboutMaster,
                m.experience,
                m.phoneNumber,
                m.balance,
                m.countOfProducts,
                c.categoryName,
                u.login,
                u.name as userName,
                u.avatar,            
                u.avatar_mime_type 
            FROM masters m 
            JOIN users u ON m.userID = u.userID 
            LEFT JOIN category c ON m.categoryID = c.categoryID 
            WHERE m.masterID = ?";
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $masterID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

function getMasterProducts($masterID) {
    global $connection;
    
    $sql = "SELECT 
                productID,
                productName,
                aboutProduct,
                price,
                countOfProduct,
                image     
            FROM products 
            WHERE masterID = ? 
            AND countOfProduct > 0
            ORDER BY productID DESC";
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $masterID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

function getProductImage($product) {
    if (!empty($product['image'])) {
        if (strpos($product['image'], 'data:image') === 0) {
            return $product['image'];
        }
        $imageData = base64_encode($product['image']);
        return 'data:image/jpeg;base64,' . $imageData;
    }
    return './styles/image/placeholder.png';
}

function getMasterRating($masterID) {
    global $connection;
    
    $sql = "SELECT AVG(r.rating) as avg_rating, COUNT(*) as review_count 
            FROM reviews r
            INNER JOIN products p ON r.productID = p.productID
            WHERE p.masterID = ?";
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $masterID);
    $stmt->execute();
    $result = $stmt->get_result();
    $rating_data = $result->fetch_assoc();
    $stmt->close();
    
    return [
        'avg_rating' => $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0,
        'review_count' => $rating_data['review_count'] ?? 0
    ];
}
?>