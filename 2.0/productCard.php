<?php
require_once('./php/checkAuth.php');
checkAuth();

if (getUserRole() !== 'user') {
    header("Location: /craftGrodno/2.0/loginPage.php");
    exit();
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header("Location: /craftGrodno/2.0/productCard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrodnoCraft - Страница товара</title>
    <link rel="stylesheet" href="./styles/productCardStyle.css">
    <link rel="icon" href="./styles/image/icon.png">
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">Grodno<span>Craft</span></div>
            <div class="nav-links">
                <a href="mainUser.php" class="nav-link">Главная</a>
                <a href="mainUser.php#categories" class="nav-link">Категории</a>
                <a href="mainUser.php#masters" class="nav-link">Мастера</a>
                <a href="mainUser.php#about" class="nav-link">О нас</a>
                <a href="mainUser.php#footer" class="nav-link">Контакты</a>
            </div>
            <div class="user-section">
                <p name="user-name" class="user-name"><?php echo htmlspecialchars($_SESSION['user_login']); ?></p>
                <a href="./php/logout.php" class="logout-button">Выйти</a>
            </div>
        </div>
    </nav>

    <!-- Основной контент -->
    <div class="container">
        <?php include('./php/userData/getProductDetails.php'); ?>
    </div>

    <!-- Футер -->
    <footer class="footer" id="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>GrodnoCraft</h3>
                <p>Платформа для ремесленников и ценителей ручной работы</p>
            </div>
            <div class="footer-section">
                <h3>Категории</h3>
                <ul class="footer-links">
                    <li><a href="./allProducts.php?category=Дерево">Дерево</a></li>
                    <li><a href="./allProducts.php?category=Вязание">Вязание</a></li>
                    <li><a href="./allProducts.php?category=Керамика">Керамика</a></li>
                    <li><a href="./allProducts.php?category=Шитье">Шитье</a></li>
                    <li><a href="./allProducts.php?category=Бижутерия">Бижутерия</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Помощь</h3>
                <ul class="footer-links">
                    <li><a href="#">Доставка и оплата</a></li>
                    <li><a href="#">Возврат</a></li>
                    <li><a href="#">Вопросы и ответы</a></li>
                    <li><a href="#">Контакты</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Контакты</h3>
                <ul class="footer-links">
                    <li>г. Гродно, ул. Советская, 25</li>
                    <li>+375 (29) 123-45-67</li>
                    <li>info@grodnocraft.by</li>
                </ul>
            </div>
        </div>
    </footer>

    <!-- Модальное окно связи -->
    <div id="sellerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Связь с продавцом</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="seller-info">
                    <div class="seller-name" id="modalSellerName"></div>
                    <div class="seller-phone" id="modalSellerPhone"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="close-button">Закрыть</button>
            </div>
        </div>
    </div>
    
    <script src="./js/modalWindow.js"></script>
</body>
</html>