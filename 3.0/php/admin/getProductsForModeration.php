<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();
if (getUserRole() !== 'admin') {
    echo json_encode(['products' => []]);
    exit();
}

$status = isset($_GET['status']) ? $_GET['status'] : 'pending';

$sql = "SELECT p.productID, p.productName, p.aboutProduct, p.price, p.image, p.approved, p.rejection_reason,
               m.masterName
        FROM products p
        JOIN masters m ON p.masterID = m.masterID
        WHERE p.approved = ?
        ORDER BY p.productID DESC";

$stmt = $connection->prepare($sql);
$stmt->bind_param("s", $status);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    if (!empty($row['image']) && strlen($row['image']) > 100) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->buffer($row['image']);
        $image_data = base64_encode($row['image']);
        $row['image_src'] = 'data:' . $mime_type . ';base64,' . $image_data;
    }
    $products[] = $row;
}

header('Content-Type: application/json');
echo json_encode(['products' => $products]);
$stmt->close();
?>