<?php
// Функция для получения данных мастера
function getMasterData($masterID) {
    global $connection;
    
    $sql = "SELECT 
                m.masterID,
                m.masterName,
                m.phoneNumber,
                m.aboutMaster,
                m.direction,
                m.experience,
                m.countOfProducts,
                c.categoryName
            FROM masters m
            LEFT JOIN category c ON m.categoryID = c.categoryID
            WHERE m.masterID = ?";
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $masterID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

// Функция для получения товаров мастера
function getMasterProducts($masterID) {
    global $connection;
    
    $sql = "SELECT 
                productID,
                productName,
                aboutProduct,
                price,
                countOfProduct
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