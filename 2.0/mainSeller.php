<?php
require_once('./php/checkAuth.php');
checkAuth();

if (getUserRole() !== 'seller') {
    header("Location: /craftGrodno/2.0/loginPage.php");
    exit();
}

require_once('./php/db.php');

$userID = getUserId();

// Первый запрос
$stmt = mysqli_prepare($connection, "SELECT m.masterID, c.categoryName 
                              FROM masters m 
                              JOIN category c ON m.categoryID = c.categoryID 
                              WHERE m.userID = ?");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$categoryData = mysqli_fetch_assoc($result);
$categoryName = $categoryData['categoryName'] ?? '';

// Второй запрос
$stmt2 = mysqli_prepare($connection, "SELECT `direction` FROM `masters` WHERE userID = ?");
mysqli_stmt_bind_param($stmt2, "i", $userID);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);
$directionData = mysqli_fetch_assoc($result2);
$direction = $directionData['direction'] ?? '';

// Третий запрос
$stmt3 = mysqli_prepare($connection, "SELECT `aboutMaster` FROM `masters` WHERE userId = ?");
mysqli_stmt_bind_param($stmt3, "i", $userID);
mysqli_stmt_execute($stmt3);
$result3 = mysqli_stmt_get_result($stmt3);
$aboutData = mysqli_fetch_assoc($result3);
$aboutMaster = $aboutData['aboutMaster'] ?? '';

// Четвертый запрос
$stmt4 = mysqli_prepare($connection, "SELECT `experience` FROM `masters` WHERE userId = ?");
mysqli_stmt_bind_param($stmt4, "i", $userID);
mysqli_stmt_execute($stmt4);
$result4 = mysqli_stmt_get_result($stmt4);
$experienceData = mysqli_fetch_assoc($result4);
$experience = $experienceData['experience'] ?? '';

// Пятый запрос
$stmt5 = mysqli_prepare($connection, "SELECT `phoneNumber` FROM `masters` WHERE userId = ?");
mysqli_stmt_bind_param($stmt5, "i", $userID);
mysqli_stmt_execute($stmt5);
$result5 = mysqli_stmt_get_result($stmt5);
$phoneData = mysqli_fetch_assoc($result5);
$phone = $phoneData['phoneNumber'] ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrodnoCraft - Панель продавца</title>
    <link rel="stylesheet" href="./styles/mainSellerStyle.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">Grodno<span>Craft</span></div>
            <div class="nav-links">
                <a href="#profile" class="nav-link">Личные данные</a>
                <a href="#products" class="nav-link">Мои товары</a>
                <a href="#add-product" class="nav-link">Добавить товар</a>
            </div>
            <div class="user-section">
                <p class="user-name"><?php echo htmlspecialchars($_SESSION['user_login']); ?></p>
                <a href="./php/logout.php" class="logout-button">Выйти</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <!-- Секция личных данных -->
        <section id="profile" class="section">
            <h2>Личные данные мастера</h2>
            <form method="POST" action="./php/masterData/saveMaster.php" class="master-form" id="master-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="login">Логин</label>
                        <input type="text" id="login" name="login" value="<?php echo htmlspecialchars($_SESSION['user_login']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="master_name">Имя мастера *</label>
                        <input type="text" id="master_name" name="master_name" required 
                               value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>">
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
                
                <button type="submit" class="submit-button">Сохранить данные мастера</button>
            </form>
        </section>

        <!-- Секция добавления товара -->
        <section id="add-product" class="section">
            <h2>Добавить новый товар</h2>
            <form method="POST" action="./php/masterData/addProduct.php" class="product-form" id="product-form">
                <div class="form-group">
                    <label for="product_name">Название товара *</label>
                    <input type="text" id="product_name" name="product_name" required>
                </div>
                
                <div class="form-group">
                    <label for="product_about">Описание товара *</label>
                    <textarea id="product_about" name="product_about" rows="4" required></textarea>
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
                
                <button type="submit" class="submit-button">Добавить товар</button>
            </form>
        </section>

        <!-- Секция моих товаров -->
        <section id="products" class="section">
            <h2>Мои товары</h2>
            <div class="product-grid">
                <?php include('./php/masterData/getAllMasterProducts.php'); ?>
            </div>
        </section>
    </div>

    <!-- Модальное окно подтверждения удаления -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Подтверждение удаления</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <p id="deleteMessage">Вы уверены, что хотите удалить товар "<span id="productNameToDelete"></span>"?</p>
            </div>
            <div class="modal-footer">
                <button class="cancel-button">Отмена</button>
                <button class="confirm-delete-button">Удалить</button>
            </div>
        </div>
    </div>              

    <script src="./js/mainSeller/sellerFormValidate.js"></script>
    <script src="./js/mainSeller/sellerFuncDostup.js"></script>
    <script src="./js/mainSeller/deleteProduct.js"></script>
    <script src="./js/commonValidate.js"></script>
    <script src="./js/mainSeller/sellerValidate.js"></script>
</body>
</html>