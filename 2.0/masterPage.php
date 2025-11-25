<?php
require_once('./php/checkAuth.php');
checkAuth();

if (getUserRole() !== 'user') {
    header("Location: /craftGrodno/2.0/loginPage.php");
    exit();
}

require_once('./php/init.php');

$masterID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($masterID === 0) {
    header("Location: allMasters.php");
    exit();
}

require_once('./php/userData/getMasterData.php');

$masterData = getMasterData($masterID);
if (!$masterData) {
    header("Location: allMasters.php");
    exit();
}

$masterProducts = getMasterProducts($masterID);

// Получаем рейтинг мастера
$rating_sql = "SELECT AVG(r.rating) as avg_rating, COUNT(*) as review_count 
               FROM reviews r
               INNER JOIN products p ON r.productID = p.productID
               WHERE p.masterID = ?";
$rating_stmt = $connection->prepare($rating_sql);
$rating_stmt->bind_param("i", $masterID);
$rating_stmt->execute();
$rating_result = $rating_stmt->get_result();
$rating_data = $rating_result->fetch_assoc();

$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
$review_count = $rating_data['review_count'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrodnoCraft - <?php echo htmlspecialchars($masterData['masterName']); ?></title>
    <link rel="stylesheet" href="./styles/masterPageStyle.css">
    <link rel="icon" href="./styles/image/icon.png">
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">Grodno<span>Craft</span></div>
            <div class="nav-links">
                <a href="mainUser.php#banner" class="nav-link">Главная</a>
                <a href="mainUser.php#categories" class="nav-link">Категории</a>
                <a href="mainUser.php#masters" class="nav-link">Мастера</a>
                <a href="mainUser.php#about" class="nav-link">О нас</a>
                <a href="mainUser.php#footer" class="nav-link">Контакты</a>
            </div>
            <div class="user-section">
                <p class="user-name"><?php echo htmlspecialchars($_SESSION['user_login']); ?></p>
                <a href="./php/logout.php" class="logout-button">Выйти</a>
            </div>
        </div>
    </nav>

    <!-- Страница профиля мастера -->
    <div class="container master-profile-page">
        
        <!-- Заголовок профиля -->
        <div class="master-profile-header">
            <div class="master-avatar-large">
                <?php echo getMasterAvatar($masterData['masterName']); ?>
            </div>
            <div class="master-info">
                <h1 class="master-name-large"><?php echo htmlspecialchars($masterData['masterName']); ?></h1>
                <div class="master-specialty-large"><?php echo htmlspecialchars($masterData['direction']); ?></div>
                
                <div class="master-rating">
                    <div class="master-rating-info">
                        <span class="master-rating-value"><?php echo $avg_rating; ?></span>
                        <span class="master-rating-count">(<?php echo $review_count; ?> отзывов)</span>
                    </div>
                    <div class="master-rating-stars"><?php echo displayRatingStars($avg_rating); ?></div>
                </div>

                <div class="master-category">Категория: <?php echo htmlspecialchars($masterData['categoryName']); ?></div>
                <div class="master-description-full"><?php echo htmlspecialchars($masterData['aboutMaster']); ?></div>
                
                <div class="master-contact-info">
                    <a href="tel:<?php echo htmlspecialchars($masterData['phoneNumber']); ?>" class="contact-phone">
                        📞 <?php echo htmlspecialchars($masterData['phoneNumber']); ?>
                    </a>
                </div>
                
                <div class="master-stats-profile">
                    <div class="master-stat-profile">
                        <span class="stat-value-profile"><?php echo $masterData['countOfProducts']; ?></span>
                        <span class="stat-name-profile">Товаров</span>
                    </div>
                    <div class="master-stat-profile">
                        <span class="stat-value-profile"><?php echo formatExperience($masterData['experience']); ?></span>
                        <span class="stat-name-profile">Опыт работы</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Товары мастера -->
        <section class="products-section">
            <h2 class="section-title">Товары мастера</h2>
            
            <div class="products-grid">
                <?php include('./php/userData/getMasterProducts.php'); ?>
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
</body>
</html>