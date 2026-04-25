<?php
require_once('./php/checkAuth.php');
checkAuth();

if (getUserRole() !== 'user') {
    header("Location: /craftGrodno/3.0/loginPage.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ГродноАрт - Корзина</title>
    <link rel="stylesheet" href="./styles/cartStyle.css">
    <link rel="icon" href="./styles/image/icon.png">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <button class="menu-toggle" aria-label="Открыть меню">
                <span></span><span></span><span></span>
            </button>
            <div class="logo">Гродно<span>Арт</span></div>
            <div class="nav-links">
                <a href="mainUser.php" class="nav-link">Главная</a>
                <a href="mainUser.php#categories" class="nav-link">Категории</a>
                <a href="allMasters.php" class="nav-link">Мастера</a>
                <a href="allProducts.php" class="nav-link">Товары</a>
                <a href="mainUser.php#about" class="nav-link">О нас</a>
                <a href="mainUser.php#footer" class="nav-link">Контакты</a>
            </div>
            <div class="user-section">
                <div class="balance-display" id="balanceDisplay">
                    <span class="balance-amount-nav" id="navBalance">0</span>
                    <span class="balance-currency">руб.</span>
                </div>
                <a href="userProfile.php" class="user-name-link"><?php echo htmlspecialchars($_SESSION['user_login']); ?></a>
            </div>
        </div>
    </nav>

    <div class="container cart-page">
        <h1 class="page-title">Корзина</h1>
        <div id="cartContainer">
            <div class="loading">Загрузка...</div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>ГродноАрт</h3>
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

    <script src="./js/cart.js"></script>
    <script src="./js/toogleMenu.js"></script>
</body>
</html>