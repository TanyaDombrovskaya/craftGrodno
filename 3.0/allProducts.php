<?php
require_once('./php/checkAuth.php');
checkAuth();

if (getUserRole() !== 'user') {
    header("Location: /craftGrodno/3.0/loginPage.php");
    exit();
}

$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ГродноАрт - Все товары</title>
    <link rel="stylesheet" href="./styles/allProductsPageStyle.css">
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

            <div class="logo">Гродно<span>Арт</span></div>
            <div class="nav-links">
                <a href="./mainUser.php#banner" class="nav-link">Главная</a>
                <a href="./mainUser.php#categories" class="nav-link">Категории</a>
                <a href="./allMasters.php" class="nav-link">Мастера</a>
                <a href="./mainUser.php#about" class="nav-link">О нас</a>
                <a href="./mainUser.php#footer" class="nav-link">Контакты</a>
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
        
        <!-- Фильтры -->
        <section class="filters-section">
            <div class="filters-header">
                <h3 class="filters-title">Фильтры</h3>
                <button class="reset-filters" id="resetFilters">Сбросить все</button>
            </div>
            
            <div class="filters-grid">
                <div class="filter-group">
                    <label class="filter-label">Категория</label>
                    <select class="filter-select" id="categoryFilter">
                        <option value="">Все категории</option>
                        <option value="Дерево" <?php echo $selectedCategory === 'Дерево' ? 'selected' : ''; ?>>Дерево</option>
                        <option value="Вязание" <?php echo $selectedCategory === 'Вязание' ? 'selected' : ''; ?>>Вязание</option>
                        <option value="Керамика" <?php echo $selectedCategory === 'Керамика' ? 'selected' : ''; ?>>Керамика</option>
                        <option value="Шитье" <?php echo $selectedCategory === 'Шитье' ? 'selected' : ''; ?>>Шитье</option>
                        <option value="Бижутерия" <?php echo $selectedCategory === 'Бижутерия' ? 'selected' : ''; ?>>Бижутерия</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">Цена, руб.</label>
                    <div class="price-range">
                        <input type="number" class="filter-input" id="priceMin" placeholder="От" min="0">
                        <span class="price-separator">—</span>
                        <input type="number" class="filter-input" id="priceMax" placeholder="До" min="0">
                    </div>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">Сортировка</label>
                    <select class="filter-select" id="sortFilter">
                        <option value="name_asc">По названию (А-Я)</option>
                        <option value="name_desc">По названию (Я-А)</option>
                        <option value="price_asc">По цене (сначала дешевые)</option>
                        <option value="price_desc">По цене (сначала дорогие)</option>
                        <option value="popular">По популярности</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">&nbsp;</label>
                    <button class="filter-button" id="applyFilters">Применить</button>
                </div>
            </div>
            
            <div class="active-filters" id="activeFilters">
                <!-- Активные фильтры будут добавляться здесь -->
            </div>
        </section>

        <!-- Результаты -->
        <div class="results-count" id="resultsCount"></div>

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

    <script src="./js/allProducts/searchProducts.js"></script>
    <script src="./js/allProducts/filterProduct.js"></script>
    <script src="./js/toogleMenu.js"></script>
    <script src="./js/cart.js"></script>
</body>
</html>