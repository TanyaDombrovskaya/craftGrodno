<?php
require_once('./php/checkAuth.php');
checkAuth();

if (getUserRole() !== 'seller') {
    header("Location: /craftGrodno/3.0/loginPage.php");
    exit();
}

require_once('./php/db.php');
require_once('./php/init.php');

$userID = getUserId();

// Получаем данные мастера
$sql = "SELECT m.*, c.categoryName, u.login, u.name as userName 
        FROM masters m 
        JOIN users u ON m.userID = u.userID 
        LEFT JOIN category c ON m.categoryID = c.categoryID 
        WHERE m.userID = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$master = $result->fetch_assoc();
$stmt->close();

$masterID = $master['masterID'] ?? null;
$categoryName = $master['categoryName'] ?? '';
$direction = $master['direction'] ?? '';
$aboutMaster = $master['aboutMaster'] ?? '';
$experience = $master['experience'] ?? '';
$phone = $master['phoneNumber'] ?? '';
$balance = $master['balance'] ?? 0;

// Получаем рейтинг мастера
$masterRating = 0;
if ($masterID) {
    $rating_sql = "SELECT AVG(r.rating) as avg_rating 
                   FROM reviews r 
                   JOIN products p ON r.productID = p.productID 
                   WHERE p.masterID = ?";
    $rating_stmt = $connection->prepare($rating_sql);
    $rating_stmt->bind_param("i", $masterID);
    $rating_stmt->execute();
    $rating_result = $rating_stmt->get_result();
    $rating_data = $rating_result->fetch_assoc();
    $masterRating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
    $rating_stmt->close();
}

// Подсчет количества товаров
$count_sql = "SELECT COUNT(*) as total_products 
              FROM products p 
              LEFT JOIN masters m ON p.masterID = m.masterID 
              WHERE m.userID = ? AND p.productName IS NOT NULL";
$count_stmt = mysqli_prepare($connection, $count_sql);
mysqli_stmt_bind_param($count_stmt, "i", $userID);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$count_data = mysqli_fetch_assoc($count_result);
$total_products = $count_data['total_products'];
mysqli_stmt_close($count_stmt);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ГроноАрт - Панель продавца</title>
    <link rel="stylesheet" href="./styles/mainSellerStyle.css">
    <link rel="icon" href="./styles/image/icon.png">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <button class="menu-toggle" aria-label="Открыть меню">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="logo">Гродно<span>Арт</span></div>

            <div class="user-section">
                <div class="balance-display">
                    <span class="balance-amount-nav" id="sellerNavBalance"><?php echo number_format($balance, 2); ?></span>
                    <span class="balance-currency">руб.</span>
                </div>
                <a href="mainSeller.php" class="user-name-link"><?php echo htmlspecialchars($_SESSION['user_login']); ?></a>
            </div>
        </div>
    </nav>

    <div class="container seller-page">
        <!-- Боковая панель -->
        <div class="seller-sidebar">
            <div class="seller-avatar">
                <div class="avatar-icon">🎨</div>
                <h2><?php echo htmlspecialchars($master['masterName'] ?? $_SESSION['user_name']); ?></h2>
                <p class="seller-login">@<?php echo htmlspecialchars($_SESSION['user_login']); ?></p>
            </div>

            <div class="seller-rating">
                <div class="rating-label">Рейтинг мастера</div>
                <div class="rating-stars-large">
                    <?php 
                    $fullStars = floor($masterRating);
                    $halfStar = ($masterRating - $fullStars) >= 0.5;
                    for ($i = 1; $i <= 5; $i++):
                        if ($i <= $fullStars):
                    ?>
                            <span class="rating-star filled">★</span>
                    <?php elseif ($i == $fullStars + 1 && $halfStar): ?>
                            <span class="rating-star half">★</span>
                    <?php else: ?>
                            <span class="rating-star">★</span>
                    <?php endif; endfor; ?>
                </div>
                <div class="rating-value"><?php echo number_format($masterRating, 1); ?> / 5</div>
            </div>                                                      

            <div class="seller-balance">
                <div class="balance-label">Доступно для вывода</div>
                <div class="balance-amount" id="sellerBalanceAmount"><?php echo number_format($balance, 2); ?> руб.</div>
                <button class="withdraw-btn" id="showWithdrawModal">Вывести средства</button>
            </div>

            <div class="seller-nav">
                <button class="seller-nav-btn active" data-tab="profile">Личные данные</button>
                <button class="seller-nav-btn" data-tab="add-product">Добавить товар</button>
                <button class="seller-nav-btn" data-tab="products">Мои товары</button>
                <button class="seller-nav-btn" data-tab="sales">Продажи</button>
                <a href="./php/logout.php" class="seller-logout-btn">Выйти</a>
            </div>
        </div>

        <!-- Основной контент -->
        <div class="seller-content">
            <!-- Вкладка профиля -->
            <div class="seller-tab active" id="tab-profile">
                <h2 class="tab-title">Личные данные мастера</h2>
                <form method="POST" action="./php/masterData/saveMaster.php" class="master-form" id="master-form" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="login">Логин</label>
                            <input type="text" id="login" name="login" value="<?php echo htmlspecialchars($_SESSION['user_login']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="master_name">Имя мастера *</label>
                            <input type="text" id="master_name" name="master_name" required 
                                   value="<?php echo htmlspecialchars($master['masterName'] ?? $_SESSION['user_name']); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="direction">Направление деятельности *</label>
                            <input type="text" id="direction" name="direction" value="<?php echo htmlspecialchars($direction); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="category">Категория *</label>
                            <select id="category" name="category" required>
                                <option value="">Выберите категорию</option>
                                <option value="1" <?= $categoryName == 'Дерево' ? 'selected' : '' ?>>Дерево</option>
                                <option value="2" <?= $categoryName == 'Вязание' ? 'selected' : '' ?>>Вязание</option>
                                <option value="3" <?= $categoryName == 'Керамика' ? 'selected' : '' ?>>Керамика</option>
                                <option value="4" <?= $categoryName == 'Шитье' ? 'selected' : '' ?>>Шитье</option>
                                <option value="5" <?= $categoryName == 'Бижутерия' ? 'selected' : '' ?>>Бижутерия</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="phone">Номер телефона *</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="about">О себе *</label>
                        <textarea id="about" name="about" rows="4" required><?php echo htmlspecialchars($aboutMaster); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="experience">Опыт работы (лет) *</label>
                        <input type="number" id="experience" name="experience" min="0" max="50" value="<?php echo htmlspecialchars($experience); ?>" required>
                    </div>

                    <button type="submit" class="save-btn">Сохранить данные мастера</button>
                </form>
            </div>

            <!-- Вкладка добавления товара -->
            <div class="seller-tab" id="tab-add-product">
                <h2 class="tab-title">Добавить новый товар</h2>
                <form method="POST" action="./php/masterData/addProduct.php" class="product-form" id="product-form" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="product_name">Название товара *</label>
                        <input type="text" id="product_name" name="product_name" required>
                    </div>

                    <div class="form-group">
                        <label for="product_about">Описание товара *</label>
                        <textarea id="product_about" name="product_about" rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="product_image">Изображение товара</label>
                        <div class="image-upload-container">
                            <input type="file" id="product_image" name="product_image" accept="image/*" class="image-input">
                            <label for="product_image" class="image-upload-button">Выбрать изображение</label>
                            <div class="image-preview" id="imagePreview">
                                <span class="preview-text">Изображение не выбрано</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Цена (BYN) *</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="count">Количество *</label>
                            <input type="number" id="count" name="count" min="1" required>
                        </div>
                    </div>

                    <button type="submit" class="save-btn">Добавить товар</button>
                </form>
            </div>

            <!-- Вкладка моих товаров -->
            <div class="seller-tab" id="tab-products">
                <h2 class="tab-title">Мои товары</h2>
                <div class="products-header">
                    <div class="products-count">Всего товаров: <span class="count-number"><?php echo $total_products; ?></span></div>
                </div>
                <div class="product-grid" id="productsGrid">
                    <?php include('./php/masterData/getAllMasterProducts.php'); ?>
                </div>
            </div>

            <!-- Вкладка продаж -->
            <div class="seller-tab" id="tab-sales">
                <h2 class="tab-title">Продажи</h2>

                <div class="sales-filters">
                    <div class="filter-group">
                        <label>Период:</label>
                        <select id="periodFilter">
                            <option value="all">Все время</option>
                            <option value="today">Сегодня</option>
                            <option value="week">Эта неделя</option>
                            <option value="month">Этот месяц</option>
                            <option value="year">Этот год</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Статус:</label>
                        <select id="statusFilter">
                            <option value="all">Все</option>
                            <option value="pending">Ожидает</option>
                            <option value="approved">Подтвержден</option>
                            <option value="transferred">Передан</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Дата от:</label>
                        <input type="date" id="dateFrom">
                    </div>
                    <div class="filter-group">
                        <label>Дата до:</label>
                        <input type="date" id="dateTo">
                    </div>
                    <button id="applySalesFilter" class="filter-btn">Применить</button>
                </div>

                <div class="sales-stats" id="salesStats">
                    <div class="stat-card">
                        <div class="stat-label">Всего продаж</div>
                        <div class="stat-value" id="totalSalesCount">0</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Общая выручка</div>
                        <div class="stat-value" id="totalRevenue">0 руб.</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Средний чек</div>
                        <div class="stat-value" id="averageCheck">0 руб.</div>
                    </div>
                </div>

                <div id="salesContainer" class="sales-container">
                    <div class="loading">Загрузка продаж...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно вывода средств -->
    <div id="withdrawModal" class="modal">
        <div class="modal-content small-modal">
            <div class="modal-header">
                <h3>Вывод средств</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="withdraw-form">
                    <div class="info-row">
                        <span>Доступно для вывода:</span>
                        <strong id="availableAmount"><?php echo number_format($balance, 2); ?> руб.</strong>
                    </div>
                    
                    <div class="form-group">
                        <label for="withdrawAmount">Сумма вывода (руб.)</label>
                        <input type="number" id="withdrawAmount" min="1" step="1" placeholder="Введите сумму">
                    </div>
                    
                    <div class="form-group">
                        <label for="withdrawMethod">Способ вывода</label>
                        <select id="withdrawMethod">
                            <option value="card">Банковская карта</option>
                            <option value="phone">На телефон</option>
                        </select>
                    </div>
                    
                    <!-- Поле для номера карты (показывается по умолчанию) -->
                    <div class="form-group" id="cardNumberGroup">
                        <label for="cardNumber">Номер карты</label>
                        <input type="text" id="cardNumber" placeholder="0000 0000 0000 0000" maxlength="19">
                        <small class="input-hint">Введите 16 цифр без пробелов или с пробелами</small>
                    </div>
                    
                    <!-- Поле для номера телефона (скрыто по умолчанию) -->
                    <div class="form-group" id="phoneNumberGroup" style="display: none;">
                        <label for="phoneNumber">Номер телефона</label>
                        <input type="tel" id="phoneNumber" placeholder="+375 XX XXX-XX-XX">
                        <small class="input-hint">Введите номер в международном формате</small>
                    </div>
                    
                    <button id="confirmWithdraw" class="withdraw-confirm-btn">Вывести</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно редактирования товара -->
    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Редактировать товар</h2>
                <span class="close-modal">&times;</span>
            </div>
            <form id="edit-product-form" class="product-form" enctype="multipart/form-data">
                <input type="hidden" id="edit_product_id" name="product_id">
                
                <div class="form-group">
                    <label for="edit_product_name">Название товара *</label>
                    <input type="text" id="edit_product_name" name="product_name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_product_about">Описание товара *</label>
                    <textarea id="edit_product_about" name="product_about" rows="4" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit_product_image">Изображение товара</label>
                    <div class="image-upload-container">
                        <input type="file" id="edit_product_image" name="product_image" accept="image/*" class="image-input">
                        <label for="edit_product_image" class="image-upload-button">
                            <span class="upload-icon">📷</span>
                            <span class="upload-text">Выбрать изображение</span>
                        </label>
                        <div class="image-preview" id="editImagePreview">
                            <span class="preview-text">Текущее изображение</span>
                        </div>
                    </div>
                    <small>Оставьте пустым, чтобы сохранить текущее изображение</small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_price">Цена (BYN) *</label>
                        <input type="number" id="edit_price" name="price" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_count">Количество *</label>
                        <input type="number" id="edit_count" name="count" min="0" required>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="cancel-button">Отмена</button>
                    <button type="submit" class="submit-button">Сохранить изменения</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Модальное окно подтверждения удаления -->
    <div id="deleteModal" class="modal">
        <div class="modal-content small-modal">
            <div class="modal-header">
                <h3>Подтверждение удаления</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить товар "<span id="deleteProductName"></span>"?</p>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn">Отмена</button>
                <button class="confirm-delete-btn">Удалить</button>
            </div>
        </div>
    </div>

    <script src="./js/cart.js"></script>
    <script src="./js/seller.js"></script>
    <script src="./js/mainSeller/sellerFormValidate.js"></script>
    <script src="./js/mainSeller/sellerFuncDostup.js"></script>
    <script src="./js/mainSeller/deleteProduct.js"></script>
    <script src="./js/commonValidate.js"></script>
    <script src="./js/mainSeller/uploadImage.js"></script>
    <script src="./js/mainSeller/productManagment.js"></script>
    <script src="./js/toogleMenu.js"></script>
</body>
</html>