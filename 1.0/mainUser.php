<?php
require_once('./php/checkAuth.php');
checkAuth();

// Проверяем, что страница доступна только пользователям
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
    <title>GrodnoCraft - Магазин ремесленных изделий</title>
    <link rel="stylesheet" href="./styles/mainUserStyle.css">
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">Grodno<span>Craft</span></div>
            <div class="nav-links">
                <a href="#banner" class="nav-link">Главная</a>
                <a href="#categories" class="nav-link">Категории</a>
                <a href="#masters" class="nav-link">Мастера</a>
                <a href="#about" class="nav-link">О нас</a>
                <a href="#footer" class="nav-link">Контакты</a>
            </div>
            <div class="user-section">
                <p name="user-name" class="user-name"><?php echo htmlspecialchars($_SESSION['user_login']); ?></p>
                <a href="./php/logout.php" class="logout-button">Выйти</a>
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
        
        <div class="masters-cta">
            <h3>Станьте частью нашего сообщества</h3>
            <p>Присоединяйтесь к платформе GrodnoCraft и начните делиться своим творчеством с ценителями ручной работы</p>
            <button class="cta-button">Стать мастером</button>
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
                🎨
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
                    <li><a href="#">Дерево</a></li>
                    <li><a href="#">Вязание</a></li>
                    <li><a href="#">Керамика</a></li>
                    <li><a href="#">Шытье</a></li>
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