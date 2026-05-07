<?php
require_once(__DIR__ . "/../checkAuth.php");
require_once(__DIR__ . "/../init.php");

checkAuth();
if (getUserRole() !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещён']);
    exit();
}

$reviewId = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;

$sql = "DELETE FROM reviews WHERE reviewID = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $reviewId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка удаления']);
}
$stmt->close();
?>