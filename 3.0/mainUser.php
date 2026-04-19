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
    <title>GrodnoCraft - Магазин ремесленных изделий</title>
    <link rel="stylesheet" href="./styles/mainUserStyle.css">
    <link rel="icon" href="./styles/image/icon.png">
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar">
        <div class="nav-container">
            <!-- Кнопка бургер-меню -->
            <button class="menu-toggle" aria-label="Открыть меню">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="logo">Grodno<span>Craft</span></div>

            <div class="nav-links">
                <a href="#banner" class="nav-link">Главная</a>
                <a href="#categories" class="nav-link">Категории</a>
                <a href="#masters" class="nav-link">Мастера</a>
                <a href="#about" class="nav-link">О нас</a>
                <a href="#footer" class="nav-link">Контакты</a>
                <a href="cart.php" class="nav-link">Корзина <span class="cart-counter" style="display:none;">0</span></a>
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

    <!-- Основной контент -->
    <div class="container">
        <!-- Баннер -->
        <div class="banner" id="banner">
            <h2>Уникальные изделия ручной работы</h2>
            <p>Откройте для себя мир настоящего ремесленного искусства от мастеров Гродно</p>
            <a href="./allProducts.php" class="banner-button">Смотреть все товары</a>
        </div>

        <!-- Категории -->
        <section class="categories" id="categories">
            <h2>Категории</h2>
            <div class="category-grid">
                <?php include('./php/userData/getCategories.php'); ?>
            </div>
        </section>

        <!-- Все товары -->
        <section class="products" id="products">
            <div class="product-grid">
                <?php include('./php/userData/getProducts.php'); ?>
            </div>
        </section>
    </div>

    <!-- Секция "Лучшие мастера" -->
    <section class="masters-section" id="masters">
        <div class="section-header-center">
            <h2>Наши лучшие мастера</h2>
            <a href="./allMasters.php" class="view-all-center">Все мастера →</a>
        </div>
        
        <div class="masters-grid">
            <?php include('./php/userData/getMasters.php'); ?>
        </div>
    </section>

    <!-- Секция "О нас" -->
    <section class="about-section" id="about">
        <div class="about-content">
            <div class="about-text">
                <h2>О GrodnoCraft</h2>
                <p>Мы - сообщество талантливых ремесленников из Гродно и Гродненской области, объединенные любовью к традиционному искусству и стремлением сохранить культурное наследие нашего региона.</p>
                <p>Наша платформа создана для того, чтобы мастера могли делиться своим творчеством, а ценители ручной работы - находить уникальные изделия, созданные с душой и вниманием к деталям.</p>
                
                <div class="about-stats">
                    <div class="stat-item">
                        <span class="stat-number">150+</span>
                        <span class="stat-label">Мастеров</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">2000+</span>
                        <span class="stat-label">Товаров</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">5 лет</span>
                        <span class="stat-label">На рынке</span>
                    </div>
                </div>
            </div>
            <div class="about-image">
                <img src="./styles/image/about-main-image.jpg" alt="">
            </div>
        </div>
    </section>

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
    
    <script src="./js/toogleMenu.js"></script>
    <script src="./js/cart.js"></script>
</body>
</html>