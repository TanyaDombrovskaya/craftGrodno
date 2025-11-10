<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrodnoCraft - Все товары</title>
    <link rel="stylesheet" href="./styles/allProductsPageStyle.css">
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">Grodno<span>Craft</span></div>
            <div class="nav-links">
                <a href="./mainUser.php" class="nav-link">Главная</a>
                <a href="./mainUser.php#categories" class="nav-link">Категории</a>
                <a href="./mainUser.php#masters" class="nav-link">Мастера</a>
                <a href="./mainUser.php#about" class="nav-link">О нас</a>
                <a href="./mainUser.php#footer" class="nav-link">Контакты</a>
            </div>
        </div>
    </nav>

    <!-- Основной контент -->
    <div class="container">
        <!-- Заголовок и поиск -->
        <div class="products-header">
            <h1 class="page-title">Все изделия ручной работы</h1>
            <div class="search-section">
                <div class="search-container">
                    <input type="text" id="searchInput" placeholder="Найти изделие" class="search-input">
                    <button class="search-button">
                        <span class="search-icon">🔍</span>
                        Найти
                    </button>
                </div>
            </div>
        </div>

        <!-- Все товары -->
        <section class="products" id="products">
            <div class="product-grid" id="productGrid">
                <?php include('./php/userData/getAllProducts.php'); ?>
            </div>
        </section>
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
    <script src="./js/allProducts/searchProducts.js"></script>
</body>
</html>