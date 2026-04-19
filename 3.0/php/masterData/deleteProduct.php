<?php
require_once(__DIR__ . "/../db.php");
require_once(__DIR__ . "/../checkAuth.php");
checkAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productID = $_POST['product_id'];
    $userID = getUserId();
    
    // Проверяем, что товар принадлежит текущему пользователю
    $checkStmt = mysqli_prepare($connection, 
        "SELECT p.productID 
         FROM products p 
         JOIN masters m ON p.masterID = m.masterID 
         WHERE p.productID = ? AND m.userID = ?");
    mysqli_stmt_bind_param($checkStmt, "ii", $productID, $userID);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    
    if ($checkResult && mysqli_fetch_assoc($checkResult)) {
        // Удаляем товар
        $deleteStmt = mysqli_prepare($connection, "DELETE FROM products WHERE productID = ?");
        mysqli_stmt_bind_param($deleteStmt, "i", $productID);
        
        if (mysqli_stmt_execute($deleteStmt)) {
            echo "success";
        } else {
            echo "error";
        }
        mysqli_stmt_close($deleteStmt);
    } else {
        echo "not_owner";
    }
    mysqli_stmt_close($checkStmt);
} else {
    echo "invalid_request";
}