<?php
require_once('./php/checkAuth.php');
checkAuth();

if (getUserRole() !== 'user') {
    header("Location: /craftGrodno/3.0/loginPage.php");
    exit();
}

require_once('./php/init.php');

$userID = getUserId();

// Получаем данные пользователя
$sql = "SELECT login, name, email, balance FROM users WHERE userID = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ГродноАрт - Личный кабинет</title>
    <link rel="stylesheet" href="./styles/userProfileStyle.css">
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
                <a href="allProducts.php" class="nav-link">Товары</a>
                <a href="allMasters.php" class="nav-link">Мастера</a>
                <a href="cart.php" class="nav-link">Корзина <span class="cart-counter" style="display:none;">0</span></a>
            </div>
            <div class="user-section">
                <a href="userProfile.php" class="user-name-link"><?php echo htmlspecialchars($_SESSION['user_login']); ?></a>
            </div>
        </div>
    </nav>

    <div class="container profile-page">
        <div class="profile-sidebar">
            <div class="profile-avatar">
                <div class="avatar-icon">👤</div>
                <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                <p class="profile-login">@<?php echo htmlspecialchars($user['login']); ?></p>
            </div>
            <div class="profile-balance">
                <div class="balance-label">Баланс</div>
                <div class="balance-amount" id="balanceAmount"><?php echo number_format($user['balance'], 2, '.', ' '); ?> руб.</div>
                <button class="topup-btn" id="showTopupModal">Пополнить баланс</button>
            </div>
            <div class="profile-nav">
                <button class="profile-nav-btn active" data-tab="profile">Личные данные</button>
                <button class="profile-nav-btn" data-tab="orders">История заказов</button>
                <a href="./php/logout.php" class="profile-logout-btn">Выйти</a>
            </div>
        </div>

        <div class="profile-content">
            <!-- Вкладка личных данных -->
            <div class="profile-tab active" id="tab-profile">
                <h2 class="tab-title">Личные данные</h2>
                <form id="profileForm" class="profile-form">
                    <div class="form-group">
                        <label for="login">Логин</label>
                        <input type="text" id="login" name="login" value="<?php echo htmlspecialchars($user['login']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="name">Имя</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <button type="submit" class="save-btn">Сохранить изменения</button>
                </form>
            </div>

            <!-- Вкладка истории заказов -->
            <div class="profile-tab" id="tab-orders">
                <h2 class="tab-title">История заказов</h2>
                <div id="ordersContainer" class="orders-container">
                    <div class="loading">Загрузка заказов...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно пополнения баланса -->
    <div id="topupModal" class="modal">
        <div class="modal-content small-modal">
            <div class="modal-header">
                <h3>Пополнение баланса</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="topup-form">
                    <label for="topupAmount">Сумма пополнения (руб.)</label>
                    <input type="number" id="topupAmount" min="1" step="1" placeholder="Введите сумму">
                    <button id="confirmTopup" class="topup-confirm-btn">Пополнить</button>
                </div>
            </div>
        </div>
    </div>

    <script src="./js/cart.js"></script>
    <script src="./js/userProfile.js"></script>
    <script src="./js/toogleMenu.js"></script>
</body>
</html>