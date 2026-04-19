<?php
require_once('./php/checkAuth.php');
checkAuth();

if (getUserRole() !== 'user') {
    header("Location: /craftGrodno/3.0/loginPage.php");
    exit();
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header("Location: /craftGrodno/3.0/mainUser.php");
    exit();
}

// Проверяем, существует ли товар
require_once('./php/init.php');
$checkSql = "SELECT COUNT(*) as count FROM products WHERE productID = ?";
$checkStmt = $connection->prepare($checkSql);
$checkStmt->bind_param("i", $product_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
$productExists = $checkResult->fetch_assoc()['count'] > 0;
$checkStmt->close();

if (!$productExists) {
    // Товар не найден
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GrodnoCraft - Товар не найден</title>
        <link rel="stylesheet" href="./styles/mainUserStyle.css">
        <link rel="icon" href="./styles/image/icon.png">
    </head>
    <body>
        <nav class="navbar">
            <div class="nav-container">
                <button class="menu-toggle" aria-label="Открыть меню">
                    <span></span><span></span><span></span>
                </button>
                <div class="logo">Grodno<span>Craft</span></div>
                <div class="nav-links">
                    <a href="mainUser.php" class="nav-link">Главная</a>
                    <a href="allProducts.php" class="nav-link">Товары</a>
                    <a href="allMasters.php" class="nav-link">Мастера</a>
                    <a href="cart.php" class="nav-link">Корзина <span class="cart-counter" style="display:none;">0</span></a>
                </div>
                <div class="user-section">
                    <div class="balance-display" id="balanceDisplay">
                        <span class="balance-icon">💰</span>
                        <span class="balance-amount-nav" id="navBalance">0</span>
                        <span class="balance-currency">руб.</span>
                    </div>
                    <a href="userProfile.php" class="user-name-link"><?php echo htmlspecialchars($_SESSION['user_login']); ?></a>
                </div>
            </div>
        </nav>
        
        <div class="container" style="text-align: center; padding: 4rem 2rem;">
            <h1 style="color: var(--gray); font-size: 2rem;">📦</h1>
            <h2 style="color: var(--dark); margin-bottom: 1rem;">Товар не найден</h2>
            <p style="color: var(--gray); margin-bottom: 2rem;">К сожалению, этот товар был удален или больше не доступен.</p>
            <a href="allProducts.php" class="continue-shopping">Вернуться к покупкам</a>
        </div>
        
        <script src="./js/cart.js"></script>
        <script src="./js/toogleMenu.js"></script>
    </body>
    </html>
    <?php
    exit();
}

// Если товар существует - показываем обычную страницу
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
            <!-- Кнопка бургер-меню -->
            <button class="menu-toggle" aria-label="Открыть меню">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="logo">Grodno<span>Craft</span></div>

            <div class="nav-links">
                <a href="mainUser.php" class="nav-link">Главная</a>
                <a href="allProducts.php" class="nav-link">Товары</a>
                <a href="allMasters.php" class="nav-link">Мастера</a>
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
        <?php include('./php/userData/getProductDetails.php'); ?>
        
        <!-- Секция отзывов -->
        <div class="reviews-section">
            <h2 class="section-title">Отзывы о товаре</h2>
            
            <!-- Форма добавления отзыва -->
            <div class="add-review-form">
                <h3>Оставить отзыв</h3>
                <form id="reviewForm" method="POST" action="./php/userData/addReview.php">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    
                    <div class="rating-input">
                        <label>Ваша оценка:</label>
                        <div class="stars-rating">
                            <input type="radio" id="star5" name="rating" value="5">
                            <label for="star5">★</label>
                            <input type="radio" id="star4" name="rating" value="4">
                            <label for="star4">★</label>
                            <input type="radio" id="star3" name="rating" value="3">
                            <label for="star3">★</label>
                            <input type="radio" id="star2" name="rating" value="2">
                            <label for="star2">★</label>
                            <input type="radio" id="star1" name="rating" value="1">
                            <label for="star1">★</label>
                        </div>
                    </div>
                    
                    <div class="review-text-input">
                        <label for="reviewText">Ваш отзыв:</label>
                        <textarea id="reviewText" name="review_text" 
                                placeholder="Поделитесь вашим мнением о товаре..." 
                                rows="4"></textarea>
                    </div>
                    
                    <button type="submit" class="submit-review-btn" id="submit-review-btn">Отправить отзыв</button>
                </form>
            </div>
            
            <!-- Список отзывов -->
            <div class="reviews-list" id="reviewsList">
                <?php include('./php/userData/getProductReviews.php'); ?>
            </div>
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
    
    <script src="./js/review/validateReviews.js"></script>
    <script src="./js/commonValidate.js"></script>
    <script src="./js/cart.js"></script>
    <script src="./js/toogleMenu.js"></script>
    <script>
    function hideNotification() {
        const notification = document.getElementById('notification');
        if (notification) {
            notification.style.display = 'none';
        }
    }
    
    setTimeout(hideNotification, 5000);
    </script>
</body>
</html>