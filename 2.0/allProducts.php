<?php
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';
?>
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
    <script src="./js/allProducts/filterProduct.js"></script>
</body>
</html>