<?php
require_once('./php/checkAuth.php');
checkAuth();

if (getUserRole() !== 'user') {
    header("Location: /craftGrodno/loginPage.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrodnoCraft - Все мастера</title>
    <link rel="stylesheet" href="./styles/allMastersPageStyle.css">
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">Grodno<span>Craft</span></div>
            <div class="nav-links">
                <a href="mainUser.php#banner" class="nav-link">Главная</a>
                <a href="mainUser.php#categories" class="nav-link">Категории</a>
                <a href="allMasters.php" class="nav-link">Мастера</a>
                <a href="mainUser.php#about" class="nav-link">О нас</a>
                <a href="mainUser.php#footer" class="nav-link">Контакты</a>
            </div>
            <div class="user-section">
                <p class="user-name"><?php echo htmlspecialchars($_SESSION['user_login']); ?></p>
                <a href="./php/logout.php" class="logout-button">Выйти</a>
            </div>
        </div>
    </nav>

    <!-- Страница всех мастеров -->
    <div class="container masters-page">        
        <div class="page-header">
            <h1>Все мастера</h1>
            <p>Знакомьтесь с талантливыми ремесленниками нашего сообщества. Каждый мастер - это уникальный стиль и годы опыта.</p>
        </div>

        <div class="masters-grid-all">
            <?php include('./php/userData/getAllMasters.php'); ?>
        </div>
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
                    <li><a href="#">Дерево</a></li>
                    <li><a href="#">Вязание</a></li>
                    <li><a href="#">Керамика</a></li>
                    <li><a href="#">Шитье</a></li>
                    <li><a href="#">Бижутерия</a></li>
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

    <!-- Модальное окно связи с продавцом -->
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