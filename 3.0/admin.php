<?php
require_once('./php/checkAuth.php');
checkAuth();

if (getUserRole() !== 'admin') {
    header("Location: /craftGrodno/3.0/loginPage.php");
    exit();
}

require_once('./php/init.php');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ГродноАрт - Панель администратора</title>
    <link rel="stylesheet" href="./styles/adminStyle.css">
    <link rel="icon" href="./styles/image/icon.png">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <button class="menu-toggle" aria-label="Открыть меню">
                <span></span><span></span><span></span>
            </button>
            <div class="logo">Гродно<span>Арт</span></div>
            <div class="user-section">
                <a href="admin.php" class="user-name-link">Администратор</a>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <div class="admin-sidebar">
            <div class="admin-avatar">
                <h2>Панель управления</h2>
                <p class="admin-role">Администратор</p>
            </div>
            <div class="admin-nav">
                <button class="admin-nav-btn active" data-tab="orders">Заказы</button>
                <button class="admin-nav-btn" data-tab="reviews">Отзывы</button>
                <button class="admin-nav-btn" data-tab="users">Пользователи</button>
                <a href="./php/logout.php" class="admin-logout-btn">Выйти</a>
            </div>
        </div>

        <div class="admin-content">
            <!-- Вкладка заказов -->
            <div class="admin-tab active" id="tab-orders">
                <h2 class="tab-title">Управление заказами</h2>
                
                <div class="orders-filters">
                    <div class="filter-group">
                        <label>Поиск по пользователю:</label>
                        <input type="text" id="searchUser" placeholder="Имя или email">
                    </div>
                    <div class="filter-group">
                        <label>Продавец (мастер):</label>
                        <select id="masterFilter">
                            <option value="all">Все мастера</option>
                            <?php
                            $mastersSql = "SELECT masterID, masterName FROM masters ORDER BY masterName";
                            $mastersResult = $connection->query($mastersSql);
                            while ($master = $mastersResult->fetch_assoc()) {
                                echo '<option value="' . $master['masterID'] . '">' . htmlspecialchars($master['masterName']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Статус:</label>
                        <select id="statusFilter">
                            <option value="all">Все</option>
                            <option value="pending">Ожидает</option>
                            <option value="approved">Подтверждён</option>
                            <option value="collecting">Собирается</option>
                            <option value="delivering">Доставляется</option>
                            <option value="delivered">Доставлен</option>
                            <option value="completed">Завершён</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Номер заказа:</label>
                        <input type="text" id="orderIdFilter" placeholder="№ заказа">
                    </div>
                    <button id="applyFilters" class="filter-btn">Применить</button>
                    <button id="batchStatusBtn" class="batch-status-btn">Изменить статус для выбранных</button>
                </div>

                <div class="orders-table-wrapper">
                    <table class="orders-table" id="ordersTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>№ заказа</th>
                                <th>Дата</th>
                                <th>Покупатель</th>
                                <th>Продавец (мастер)</th>
                                <th>Товар</th>
                                <th>Кол-во</th>
                                <th>Сумма</th>
                                <th>Статус</th>
                                <th>Действие</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            <tr><td colspan="10" class="loading">Загрузка заказов...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Вкладка модерации отзывов -->
            <div class="admin-tab" id="tab-reviews">
                <h2 class="tab-title">Модерация отзывов</h2>
                <div id="reviewsContainer" class="reviews-container">
                    <div class="loading">Загрузка отзывов...</div>
                </div>
            </div>

            <!-- Вкладка управления пользователями -->
            <div class="admin-tab" id="tab-users">
                <h2 class="tab-title">Управление пользователями</h2>
                <div class="users-filters">
                    <div class="filter-group">
                        <label>Поиск:</label>
                        <input type="text" id="searchUserInput" placeholder="Логин, имя или email">
                    </div>
                    <div class="filter-group">
                        <label>Роль:</label>
                        <select id="roleFilter">
                            <option value="all">Все</option>
                            <option value="user">Покупатели</option>
                            <option value="seller">Мастера</option>
                            <option value="admin">Администраторы</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Статус:</label>
                        <select id="statusFilter">
                            <option value="all">Все</option>
                            <option value="online">В сети</option>
                            <option value="offline">Не в сети</option>
                            <option value="blocked">Заблокированы</option>
                        </select>
                    </div>
                    <button id="applyUsersFilter" class="filter-btn">Применить</button>
                </div>

                <div id="usersContainer" class="users-container">
                    <div class="loading">Загрузка пользователей...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно группового изменения статуса -->
    <div id="batchStatusModal" class="modal">
        <div class="modal-content small-modal">
            <div class="modal-header">
                <h3>Изменение статуса для выбранных позиций</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="status-form">
                    <label>Выбрано позиций: <span id="selectedCount">0</span></label>
                    <label for="batchNewStatus">Новый статус:</label>
                    <select id="batchNewStatus">
                        <option value="pending">Ожидает</option>
                        <option value="approved">Подтверждён</option>
                        <option value="collecting">Собирается</option>
                        <option value="delivering">Доставляется</option>
                        <option value="delivered">Доставлен</option>
                        <option value="completed">Завершён</option>
                    </select>
                    <label for="batchComment">Комментарий (опционально):</label>
                    <textarea id="batchComment" rows="3" placeholder="Дополнительная информация..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn">Отмена</button>
                <button id="confirmBatchStatus" class="status-confirm-btn">Изменить</button>
            </div>
        </div>
    </div>

    <!-- Модальное окно редактирования статуса одного товара -->
    <div id="singleStatusModal" class="modal">
        <div class="modal-content small-modal">
            <div class="modal-header">
                <h3>Изменение статуса товара</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="status-form">
                    <label>Товар: <span id="singleProductName"></span></label>
                    <label>Мастер: <span id="singleMasterName"></span></label>
                    <label for="singleNewStatus">Новый статус:</label>
                    <select id="singleNewStatus">
                        <option value="pending">Ожидает</option>
                        <option value="approved">Подтверждён</option>
                        <option value="collecting">Собирается</option>
                        <option value="delivering">Доставляется</option>
                        <option value="delivered">Доставлен</option>
                        <option value="completed">Завершён</option>
                    </select>
                    <label for="singleComment">Комментарий (опционально):</label>
                    <textarea id="singleComment" rows="3" placeholder="Дополнительная информация..."></textarea>
                    <input type="hidden" id="singleOrderItemId">
                </div>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn">Отмена</button>
                <button id="confirmSingleStatus" class="status-confirm-btn">Изменить</button>
            </div>
        </div>
    </div>

    <!-- Модальное окно редактирования отзыва -->
    <div id="editReviewModal" class="modal">
        <div class="modal-content small-modal">
            <div class="modal-header">
                <h3>Редактирование отзыва</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="edit-review-form">
                    <label for="editReviewText">Текст отзыва:</label>
                    <textarea id="editReviewText" rows="5"></textarea>
                    <input type="hidden" id="editReviewId">
                </div>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn">Отмена</button>
                <button id="saveReview" class="save-review-btn">Сохранить</button>
            </div>
        </div>
    </div>

    <!-- Модальное окно блокировки пользователя -->
    <div id="blockUserModal" class="modal">
        <div class="modal-content small-modal">
            <div class="modal-header">
                <h3>Блокировка пользователя</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="block-form">
                    <p>Вы уверены, что хотите заблокировать пользователя <strong id="blockUserLogin"></strong>?</p>
                    <label for="blockReason">Причина блокировки:</label>
                    <textarea id="blockReason" rows="3" placeholder="Укажите причину блокировки..."></textarea>
                    <input type="hidden" id="blockUserId">
                </div>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn">Отмена</button>
                <button id="confirmBlock" class="block-confirm-btn">Заблокировать</button>
            </div>
        </div>
    </div>

    <!-- Модальное окно смены роли пользователя -->
    <div id="changeRoleModal" class="modal">
        <div class="modal-content small-modal">
            <div class="modal-header">
                <h3>Смена роли пользователя</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="change-role-form">
                    <p>Пользователь: <strong id="changeRoleUserLogin"></strong></p>
                    <label for="newRole">Новая роль:</label>
                    <select id="newRole">
                        <option value="user">Покупатель</option>
                        <option value="seller">Мастер</option>
                        <option value="admin">Администратор</option>
                    </select>
                    <input type="hidden" id="changeRoleUserId">
                </div>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn">Отмена</button>
                <button id="confirmChangeRole" class="role-confirm-btn">Сменить роль</button>
            </div>
        </div>
    </div>

    <script src="./js/commonValidate.js"></script>
    <script>
        const currentAdminId = <?php echo getUserId(); ?>;
    </script>
    <script src="./js/admin.js"></script>
    <script src="./js/toogleMenu.js"></script>
</body>
</html>