<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();

if (getUserRole() !== 'user') {
    echo json_encode([]);
    exit();
}

$userID = getUserId();

$sql = "SELECT 
            c.cartID,
            c.quantity,
            p.productID,
            p.productName,
            p.aboutProduct,
            p.price,
            p.countOfProduct as available,
            p.image,
            m.masterName,
            m.masterID
        FROM cart c
        LEFT JOIN products p ON c.productID = p.productID
        LEFT JOIN masters m ON p.masterID = m.masterID
        WHERE c.userID = ?
        ORDER BY c.added_at DESC";

$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
while ($row = $result->fetch_assoc()) {
    // Проверяем наличие изображения
    $image_size = isset($row['image']) ? strlen($row['image']) : 0;
    
    if ($image_size > 100) {
        try {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->buffer($row['image']);
            if (strpos($mime_type, 'image/') === 0) {
                $image_data = base64_encode($row['image']);
                $row['image_src'] = 'data:' . $mime_type . ';base64,' . $image_data;
                $row['has_image'] = true;
            } else {
                $row['has_image'] = false;
                $row['image_src'] = null;
            }
        } catch (Exception $e) {
            $row['has_image'] = false;
            $row['image_src'] = null;
        }
    } else {
        $row['has_image'] = false;
        $row['image_src'] = null;
    }
    
    // Удаляем бинарные данные из объекта, чтобы не засорять JSON
    unset($row['image']);
    
    $cart_items[] = $row;
}

header('Content-Type: application/json');
echo json_encode($cart_items, JSON_UNESCAPED_UNICODE);
$stmt->close();