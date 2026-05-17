// Функция для отображения сообщений
function showMessage(message, type) {
    const msgDiv = document.createElement('div');
    msgDiv.className = `message ${type === 'success' ? 'message-success' : 'message-error'}`;
    msgDiv.textContent = message;
    msgDiv.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 8px;
        z-index: 9999;
        font-size: 14px;
        animation: fadeIn 0.3s ease;
    `;
    
    if (type === 'success') {
        msgDiv.style.backgroundColor = '#d4edda';
        msgDiv.style.color = '#155724';
        msgDiv.style.border = '1px solid #c3e6cb';
    } else {
        msgDiv.style.backgroundColor = '#f8d7da';
        msgDiv.style.color = '#721c24';
        msgDiv.style.border = '1px solid #f5c6cb';
    }
    
    document.body.appendChild(msgDiv);
    
    setTimeout(() => {
        msgDiv.remove();
    }, 3000);
}

// Проверяем URL параметр tab
const urlParams = new URLSearchParams(window.location.search);
const activeTab = urlParams.get('tab');

if (activeTab === 'orders') {
    const ordersBtn = document.querySelector('.profile-nav-btn[data-tab="orders"]');
    if (ordersBtn) {
        ordersBtn.click();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Переключение вкладок
    const navBtns = document.querySelectorAll('.profile-nav-btn');
    const tabs = document.querySelectorAll('.profile-tab');
    
    navBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            navBtns.forEach(b => b.classList.remove('active'));
            tabs.forEach(t => t.classList.remove('active'));
            
            this.classList.add('active');
            document.getElementById(`tab-${tabId}`).classList.add('active');
            
            if (tabId === 'orders') {
                loadOrders();
            }
        });
    });
    
    // Обновление профиля
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            updateProfile();
        });
    }
    
    // Модальное окно пополнения баланса
    const modal = document.getElementById('topupModal');
    const showBtn = document.getElementById('showTopupModal');
    const closeBtns = document.querySelectorAll('.close-modal');
    const confirmBtn = document.getElementById('confirmTopup');
    
    if (showBtn) {
        showBtn.addEventListener('click', () => {
            modal.style.display = 'flex';
        });
    }
    
    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    });
    
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    if (confirmBtn) {
        confirmBtn.addEventListener('click', topupBalance);
    }
    
    // Загружаем заказы если активна вкладка
    if (document.getElementById('tab-orders').classList.contains('active')) {
        loadOrders();
    }

    // Фильтры заказов
    const applyFiltersBtn = document.getElementById('applyOrdersFilter');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', loadOrders);
    }

    const resetFiltersBtn = document.getElementById('resetOrdersFilter');
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', resetOrdersFilter);
    }
});

function updateProfile() {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const address = document.getElementById('address')?.value.trim() || '';
    
    if (!name || !email) {
        showMessage('Заполните все поля', 'error');
        return;
    }
    
    const saveBtn = document.querySelector('#profileForm .save-btn');
    const originalText = saveBtn ? saveBtn.textContent : 'Сохранить изменения';
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.textContent = 'Сохранение...';
    }
    
    fetch('userProfile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&address=${encodeURIComponent(address)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Данные успешно обновлены', 'success');
            const userNameSpan = document.querySelector('.user-name-link');
            if (userNameSpan) {
                userNameSpan.textContent = name;
            }
        } else {
            showMessage(data.message || 'Ошибка при обновлении', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Ошибка при обновлении данных', 'error');
    })
    .finally(() => {
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.textContent = originalText;
        }
    });
}

function topupBalance() {
    const amountInput = document.getElementById('topupAmount');
    const amount = amountInput ? amountInput.value : '';
    
    if (!amount || amount <= 0) {
        showMessage('Введите корректную сумму', 'error');
        return;
    }
    
    // Блокируем кнопку, чтобы избежать повторных запросов
    const confirmBtn = document.getElementById('confirmTopup');
    const originalText = confirmBtn ? confirmBtn.textContent : 'Пополнить';
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Обработка...';
    }
    
    fetch('./php/userData/topupBalance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `amount=${encodeURIComponent(amount)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(`Баланс пополнен на ${data.amount} руб.`, 'success');
            const balanceSpan = document.getElementById('balanceAmount');
            if (balanceSpan) {
                balanceSpan.textContent = data.new_balance + ' руб.';
            }
            const modal = document.getElementById('topupModal');
            if (modal) {
                modal.style.display = 'none';
            }
            const amountInputElem = document.getElementById('topupAmount');
            if (amountInputElem) {
                amountInputElem.value = '';
            }
        } else {
            showMessage(data.message || 'Ошибка при пополнении', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Ошибка при пополнении баланса', 'error');
    })
    .finally(() => {
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.textContent = originalText;
        }
    });
}

function loadOrders() {
    const container = document.getElementById('ordersContainer');
    
    if (!container) return;
    
    // Получаем значения фильтров
    const status = document.getElementById('orderStatusFilter')?.value || 'all';
    const dateFrom = document.getElementById('dateFrom')?.value || '';
    const dateTo = document.getElementById('dateTo')?.value || '';
    
    container.innerHTML = '<div class="loading">Загрузка заказов...</div>';
    
    // Формируем URL с параметрами
    let url = `./php/userData/getUserOrders.php?status=${encodeURIComponent(status)}`;
    if (dateFrom) url += `&date_from=${encodeURIComponent(dateFrom)}`;
    if (dateTo) url += `&date_to=${encodeURIComponent(dateTo)}`;
    
    fetch(url)
        .then(response => response.json())
        .then(orders => {
            if (!orders || orders.length === 0) {
                container.innerHTML = `
                    <div class="empty-orders">
                        <h3>Заказы не найдены</h3>
                        <p>Попробуйте изменить параметры фильтрации</p>
                        <button onclick="resetOrdersFilter()" class="continue-shopping">Сбросить фильтры</button>
                    </div>
                `;
                return;
            }
            
            let html = '';
            orders.forEach(order => {
                html += `
                    <div class="order-card">
                        <div class="order-header">
                            <span class="order-number">Заказ №${order.orderID}</span>
                            <span class="order-date">${formatDate(order.order_date)}</span>
                        </div>
                        <div class="order-items">
                            ${order.items.map(item => {
                                const itemStatusClass = getStatusClass(item.item_status);
                                const itemStatusText = getStatusText(item.item_status);
                                const totalPrice = (parseFloat(item.price) * parseInt(item.quantity)).toFixed(2);
                                
                                return `
                                    <div class="order-item">
                                        <div class="order-item-name">
                                            ${item.product_exists ? `
                                                <a href="productCard.php?id=${item.productID}" class="order-item-link">
                                                    ${escapeHtml(item.productName)}
                                                </a>
                                            ` : `
                                                <span class="order-item-deleted">
                                                    ${escapeHtml(item.productName)} 
                                                    <span class="deleted-badge">(товар удален)</span>
                                                </span>
                                            `}
                                            <span class="order-item-quantity">× ${item.quantity}</span>
                                        </div>
                                        <div class="order-item-right">
                                            <div class="order-item-price">${totalPrice} руб.</div>
                                            <div class="order-item-status">
                                                <span class="status-badge ${itemStatusClass}">${itemStatusText}</span>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                        <div class="order-total">
                            Итого: <span>${parseFloat(order.total_amount).toFixed(2)} руб.</span>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<div class="empty-orders"><h3>Ошибка загрузки заказов</h3></div>';
        });
}

function resetOrdersFilter() {
    const statusSelect = document.getElementById('orderStatusFilter');
    const dateFromInput = document.getElementById('dateFrom');
    const dateToInput = document.getElementById('dateTo');
    
    if (statusSelect) statusSelect.value = 'all';
    if (dateFromInput) dateFromInput.value = '';
    if (dateToInput) dateToInput.value = '';
    
    loadOrders();
}

function getStatusText(status) {
    const texts = {
        'pending': 'Ожидает одобрения',
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

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('ru-RU', {
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