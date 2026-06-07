// js/admin.js - Полностью обновлённая версия с управлением пользователями

let currentSelectedItems = [];

document.addEventListener('DOMContentLoaded', function() {
    // Переключение вкладок
    const navBtns = document.querySelectorAll('.admin-nav-btn');
    const tabs = document.querySelectorAll('.admin-tab');
    
    navBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            navBtns.forEach(b => b.classList.remove('active'));
            tabs.forEach(t => t.classList.remove('active'));
            
            this.classList.add('active');
            document.getElementById(`tab-${tabId}`).classList.add('active');
            
            if (tabId === 'orders') loadOrders();
            if (tabId === 'products') loadProductsForModeration();
            if (tabId === 'reviews') loadReviews();
            if (tabId === 'users') loadUsers();
        });
    });
    
    // Загрузка заказов
    loadOrders();
    
    // Фильтры
    document.getElementById('applyFilters')?.addEventListener('click', loadOrders);
    document.getElementById('applyProductFilters')?.addEventListener('click', loadProductsForModeration);
    document.getElementById('applyUsersFilter')?.addEventListener('click', loadUsers);
    
    // Выделение всех
    document.getElementById('selectAll')?.addEventListener('click', function(e) {
        const checkboxes = document.querySelectorAll('#ordersTableBody input[type="checkbox"]');
        checkboxes.forEach(cb => {
            cb.checked = e.target.checked;
        });
    });
    
    // Кнопка группового изменения статуса
    document.getElementById('batchStatusBtn')?.addEventListener('click', function() {
        const selected = getSelectedOrderItems();
        if (selected.length === 0) {
            showMessage('Выберите хотя бы одну позицию', 'error');
            return;
        }
        document.getElementById('selectedCount').textContent = selected.length;
        currentSelectedItems = selected;
        document.getElementById('batchStatusModal').style.display = 'flex';
    });
    
    // Подтверждение группового изменения
    document.getElementById('confirmBatchStatus')?.addEventListener('click', function() {
        const newStatus = document.getElementById('batchNewStatus').value;
        const comment = document.getElementById('batchComment').value;
        
        fetch('./php/admin/batchUpdateOrderItemsStatus.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `order_item_ids=${JSON.stringify(currentSelectedItems)}&status=${newStatus}&comment=${encodeURIComponent(comment)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(`Обновлено: ${data.success_count} позиций. Ошибок: ${data.error_count}`, 'success');
                document.getElementById('batchStatusModal').style.display = 'none';
                document.getElementById('batchComment').value = '';
                loadOrders();
            } else {
                showMessage(data.message || 'Ошибка', 'error');
            }
        });
    });
    
    // Закрытие модальных окон
    document.querySelectorAll('.close-modal, .cancel-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.style.display = 'none';
            });
        });
    });
    
    // Обработчик для одиночного изменения статуса (через делегирование)
    document.addEventListener('click', function(e) {
        const targetBtn = e.target.closest('#confirmSingleStatus');
        if (targetBtn) {
            e.preventDefault();
            updateSingleOrderItemStatus();
        }
    });
    
    // Обработчик для подтверждения блокировки
    document.getElementById('confirmBlock')?.addEventListener('click', blockUser);
    
    // Загрузка пользователей если активна вкладка
    if (document.getElementById('tab-users')?.classList.contains('active')) {
        loadUsers();
    }
});

function getSelectedOrderItems() {
    const selected = [];
    document.querySelectorAll('#ordersTableBody input[type="checkbox"]:checked').forEach(cb => {
        const itemId = cb.getAttribute('data-item-id');
        if (itemId) selected.push(parseInt(itemId));
    });
    return selected;
}

function loadOrders() {
    const searchUser = document.getElementById('searchUser')?.value || '';
    const masterFilter = document.getElementById('masterFilter')?.value || 'all';
    const statusFilter = document.getElementById('statusFilter')?.value || 'all';
    const orderIdFilter = document.getElementById('orderIdFilter')?.value || '';
    
    let url = `./php/admin/getOrders.php?search=${encodeURIComponent(searchUser)}&status=${statusFilter}`;
    if (masterFilter !== 'all') url += `&master_id=${masterFilter}`;
    if (orderIdFilter) url += `&order_id=${orderIdFilter}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            displayOrders(data.orders || []);
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('ordersTableBody').innerHTML = '<tr><td colspan="10" class="no-products">Ошибка загрузки заказов</td></tr>';
        });
}

function displayOrders(orders) {
    const tbody = document.getElementById('ordersTableBody');
    
    if (!orders || orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="no-products">Заказы не найдены</td></tr>';
        return;
    }
    
    let html = '';
    orders.forEach(order => {
        const statusClass = getStatusClass(order.item_status);
        const statusText = getStatusText(order.item_status);
        
        html += `
            <tr>
                <td><input type="checkbox" data-item-id="${order.order_itemID}"></td>
                <td>#${order.orderID}</td>
                <td>${formatDateTime(order.order_date)}</td>
                <td>
                    ${escapeHtml(order.buyer_name || '')}<br>
                    <small>${escapeHtml(order.buyer_email || '')}</small>
                </td>
                <td>${escapeHtml(order.masterName)}</td>
                <td>
                    <a href="productCard.php?id=${order.productID}" target="_blank">${escapeHtml(order.productName)}</a>
                </td>
                <td>${order.quantity}</td>
                <td>${parseFloat(order.price).toFixed(2)} руб.</td>
                <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                <td>
                    <button class="change-status-btn" onclick="openSingleStatusModal(${order.order_itemID}, '${escapeHtml(order.productName)}', '${escapeHtml(order.masterName)}')">✏️ Изменить</button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function openSingleStatusModal(orderItemId, productName, masterName) {
    document.getElementById('singleOrderItemId').value = orderItemId;
    document.getElementById('singleProductName').textContent = productName;
    document.getElementById('singleMasterName').textContent = masterName;
    document.getElementById('singleNewStatus').value = 'approved';
    document.getElementById('singleComment').value = '';
    document.getElementById('singleStatusModal').style.display = 'flex';
}

function updateSingleOrderItemStatus() {
    const orderItemId = document.getElementById('singleOrderItemId').value;
    const newStatus = document.getElementById('singleNewStatus').value;
    const comment = document.getElementById('singleComment').value;
    
    if (!orderItemId) {
        showMessage('Ошибка: ID товара не найден', 'error');
        return;
    }
    
    const btn = document.getElementById('confirmSingleStatus');
    const originalText = btn ? btn.textContent : 'Изменить';
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Сохранение...';
    }
    
    fetch('./php/admin/updateOrderItemStatus.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `order_item_id=${orderItemId}&status=${newStatus}&comment=${encodeURIComponent(comment)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Статус обновлён', 'success');
            document.getElementById('singleStatusModal').style.display = 'none';
            document.getElementById('singleComment').value = '';
            loadOrders();
        } else {
            showMessage(data.message || 'Ошибка', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Ошибка при обновлении статуса', 'error');
    })
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });
}

// ========== ФУНКЦИИ ДЛЯ МОДЕРАЦИИ ОТЗЫВОВ ==========

function loadReviews() {
    fetch('./php/admin/getReviews.php')
        .then(response => response.json())
        .then(data => {
            displayReviews(data.reviews || []);
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('reviewsContainer').innerHTML = '<div class="no-products">Ошибка загрузки отзывов</div>';
        });
}

function displayReviews(reviews) {
    const container = document.getElementById('reviewsContainer');
    
    if (!reviews || reviews.length === 0) {
        container.innerHTML = '<div class="no-products">Отзывы не найдены</div>';
        return;
    }
    
    let html = '';
    reviews.forEach(review => {
        html += `
            <div class="review-card" data-review-id="${review.reviewID}">
                <div class="review-header">
                    <span class="review-product">${escapeHtml(review.productName)}</span>
                    <span class="review-author">${escapeHtml(review.userName)}</span>
                    <span class="review-date">${review.created_at}</span>
                </div>
                <div class="review-text">${escapeHtml(review.review)}</div>
                <div class="review-actions">
                    <button class="edit-review-btn" onclick="openEditReviewModal(${review.reviewID}, '${escapeHtml(review.review)}')">✏️ Редактировать</button>
                    <button class="delete-review-btn" onclick="deleteReview(${review.reviewID})">🗑️ Удалить</button>
                </div>
            </div>
        `;
    });
    container.innerHTML = html;
}

function openEditReviewModal(reviewId, reviewText) {
    document.getElementById('editReviewId').value = reviewId;
    document.getElementById('editReviewText').value = reviewText;
    document.getElementById('editReviewModal').style.display = 'flex';
}

function saveEditedReview() {
    const reviewId = document.getElementById('editReviewId').value;
    const newText = document.getElementById('editReviewText').value;
    
    fetch('./php/admin/editReview.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `review_id=${reviewId}&review_text=${encodeURIComponent(newText)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Отзыв отредактирован', 'success');
            document.getElementById('editReviewModal').style.display = 'none';
            loadReviews();
        } else {
            showMessage(data.message || 'Ошибка', 'error');
        }
    });
}

function deleteReview(reviewId) {
    if (!confirm('Удалить этот отзыв?')) return;
    
    fetch('./php/admin/deleteReview.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `review_id=${reviewId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Отзыв удалён', 'success');
            loadReviews();
        } else {
            showMessage(data.message || 'Ошибка', 'error');
        }
    });
}

document.getElementById('saveReview')?.addEventListener('click', saveEditedReview);

// ========== ФУНКЦИИ ДЛЯ УПРАВЛЕНИЯ ПОЛЬЗОВАТЕЛЯМИ ==========

function loadUsers() {
    const search = document.getElementById('searchUserInput')?.value || '';
    const role = document.getElementById('roleFilter')?.value || 'all';
    const status = document.getElementById('statusFilter')?.value || 'all';
    
    let url = `./php/admin/getUsers.php?search=${encodeURIComponent(search)}&role=${role}&status=${status}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            displayUsers(data.users || []);
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('usersContainer').innerHTML = '<div class="no-products">Ошибка загрузки пользователей</div>';
        });
}

// ========== ФУНКЦИИ ДЛЯ СМЕНЫ РОЛИ ПОЛЬЗОВАТЕЛЯ ==========

function openChangeRoleModal(userId, userLogin, currentRole) {
    document.getElementById('changeRoleUserId').value = userId;
    document.getElementById('changeRoleUserLogin').textContent = userLogin;
    
    // Устанавливаем текущую роль в select
    const roleSelect = document.getElementById('newRole');
    if (roleSelect) {
        roleSelect.value = currentRole;
    }
    
    document.getElementById('changeRoleModal').style.display = 'flex';
}

function changeUserRole() {
    const userId = document.getElementById('changeRoleUserId').value;
    const newRole = document.getElementById('newRole').value;
    
    if (!userId) {
        showMessage('Ошибка: ID пользователя не найден', 'error');
        return;
    }
    
    // Блокируем кнопку
    const confirmBtn = document.getElementById('confirmChangeRole');
    const originalText = confirmBtn ? confirmBtn.textContent : 'Сменить роль';
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Сохранение...';
    }
    
    fetch('./php/admin/changeUserRole.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `user_id=${userId}&new_role=${newRole}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            document.getElementById('changeRoleModal').style.display = 'none';
            loadUsers(); // Перезагружаем список пользователей
        } else {
            showMessage(data.message || 'Ошибка при смене роли', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Ошибка при смене роли', 'error');
    })
    .finally(() => {
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.textContent = originalText;
        }
    });
}

// Добавляем обработчик для кнопки подтверждения смены роли
document.addEventListener('DOMContentLoaded', function() {
    // Обработчик для смены роли
    const confirmChangeRoleBtn = document.getElementById('confirmChangeRole');
    if (confirmChangeRoleBtn) {
        confirmChangeRoleBtn.addEventListener('click', changeUserRole);
    }
});

function displayUsers(users) {
    const container = document.getElementById('usersContainer');
    
    if (!users || users.length === 0) {
        container.innerHTML = '<div class="no-products">Пользователи не найдены</div>';
        return;
    }
    
    let html = `
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Логин</th>
                    <th>Имя</th>
                    <th>Email</th>
                    <th>Роль</th>
                    <th>Статус</th>
                    <th>Действие</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    users.forEach(user => {
        const roleText = user.role === 'user' ? 'Покупатель' : (user.role === 'seller' ? 'Мастер' : 'Администратор');
        const roleClass = user.role === 'user' ? 'role-user' : (user.role === 'seller' ? 'role-seller' : 'role-admin');
        
        let statusHtml = '';
        
        if (user.is_blocked == 1) {
            statusHtml = '<span class="status-badge status-blocked">🔒 Заблокирован</span>';
        } else if (user.is_online) {
            statusHtml = '<span class="status-badge status-online">🟢 В сети</span>';
        } else {
            statusHtml = '<span class="status-badge status-offline">⚫ Не в сети</span>';
        }
        
        // Используем глобальную переменную currentAdminId
        const roleButton = (user.userID != currentAdminId) ? 
            `<button class="change-role-btn" onclick="openChangeRoleModal(${user.userID}, '${escapeHtml(user.login)}', '${user.role}')">👥 Сменить роль</button>` : 
            '';
        
        html += `
            <tr>
                <td>${user.userID}</td>
                <td>${escapeHtml(user.login)}</td>
                <td>${escapeHtml(user.name)}</td>
                <td>${escapeHtml(user.email)}</td>
                <td><span class="role-badge ${roleClass}">${roleText}</span></td>
                <td>${statusHtml}</td>
                <td>
                    ${roleButton}
                    ${user.is_blocked == 0 ? 
                        `<button class="block-user-btn" onclick="openBlockModal(${user.userID}, '${escapeHtml(user.login)}')">🔨 Блокировать</button>` :
                        `<button class="unblock-user-btn" onclick="unblockUser(${user.userID})">🔓 Разблокировать</button>`
                    }
                    <button class="delete-user-btn" onclick="deleteUser(${user.userID}, '${escapeHtml(user.login)}')">🗑️ Удалить</button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

function openBlockModal(userId, userLogin) {
    document.getElementById('blockUserId').value = userId;
    document.getElementById('blockUserLogin').textContent = userLogin;
    document.getElementById('blockReason').value = '';
    document.getElementById('blockUserModal').style.display = 'flex';
}

function blockUser() {
    const userId = document.getElementById('blockUserId').value;
    const reason = document.getElementById('blockReason').value;
    
    if (!reason.trim()) {
        showMessage('Укажите причину блокировки', 'error');
        return;
    }
    
    fetch('./php/admin/blockUser.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `user_id=${userId}&reason=${encodeURIComponent(reason)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            document.getElementById('blockUserModal').style.display = 'none';
            loadUsers();
        } else {
            showMessage(data.message, 'error');
        }
    });
}

function unblockUser(userId) {
    if (!confirm('Разблокировать этого пользователя?')) return;
    
    fetch('./php/admin/unblockUser.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `user_id=${userId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            loadUsers();
        } else {
            showMessage(data.message, 'error');
        }
    });
}

function deleteUser(userId, userLogin) {
    if (!confirm(`Удалить пользователя "${userLogin}"? Это действие нельзя отменить.`)) return;
    
    fetch('./php/admin/deleteUser.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `user_id=${userId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            loadUsers();
        } else {
            showMessage(data.message, 'error');
        }
    });
}

// ========== ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ==========

function getStatusText(status) {
    const texts = {
        'pending': 'Ожидает',
        'approved': 'Подтверждён',
        'collecting': 'Собирается',
        'delivering': 'Доставляется',
        'delivered': 'Доставлен',
        'completed': 'Завершён'
    };
    return texts[status] || status;
}

function getStatusClass(status) {
    const classes = {
        'pending': 'status-pending',
        'approved': 'status-approved',
        'collecting': 'status-collecting',
        'delivering': 'status-delivering',
        'delivered': 'status-delivered',
        'completed': 'status-completed'
    };
    return classes[status] || 'status-pending';
}

function formatDateTime(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}