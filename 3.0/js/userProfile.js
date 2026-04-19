// Проверяем URL параметр tab
const urlParams = new URLSearchParams(window.location.search);
const activeTab = urlParams.get('tab');

if (activeTab === 'orders') {
    // Активируем вкладку заказов
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
            modal.style.display = 'block';
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
});

function updateProfile() {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    
    if (!name || !email) {
        showMessage('Заполните все поля', 'error');
        return;
    }
    
    fetch('./php/userData/updateProfile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Данные успешно обновлены', 'success');
            // Обновляем имя в навбаре
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
    });
}

function topupBalance() {
    const amount = document.getElementById('topupAmount').value;
    
    if (!amount || amount <= 0) {
        showMessage('Введите корректную сумму', 'error');
        return;
    }
    
    fetch('./php/userData/topupBalance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `amount=${amount}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(`Баланс пополнен на ${data.amount} руб.`, 'success');
            // Обновляем отображение баланса
            const balanceSpan = document.getElementById('balanceAmount');
            if (balanceSpan) {
                balanceSpan.textContent = data.new_balance + ' руб.';
            }
            // Закрываем модальное окно
            document.getElementById('topupModal').style.display = 'none';
            document.getElementById('topupAmount').value = '';
        } else {
            showMessage(data.message || 'Ошибка при пополнении', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Ошибка при пополнении баланса', 'error');
    });
}

function loadOrders() {
    const container = document.getElementById('ordersContainer');
    container.innerHTML = '<div class="loading">Загрузка заказов...</div>';
    
    fetch('./php/userData/getUserOrders.php')
        .then(response => response.json())
        .then(orders => {
            if (!orders || orders.length === 0) {
                container.innerHTML = `
                    <div class="empty-orders">
                        <h3>У вас пока нет заказов</h3>
                        <p>Перейдите в каталог и выберите товары для покупки</p>
                        <a href="allProducts.php" class="continue-shopping">Перейти к покупкам</a>
                    </div>
                `;
                return;
            }
            
            let html = '';
            orders.forEach(order => {
                const statusClass = getStatusClass(order.status);
                const statusText = getStatusText(order.status);
                
                html += `
                    <div class="order-card">
                        <div class="order-header">
                            <span class="order-number">Заказ №${order.orderID}</span>
                            <span class="order-date">${formatDate(order.order_date)}</span>
                            <span class="order-status ${statusClass}">${statusText}</span>
                        </div>
                        <div class="order-items">
                            ${order.items.map(item => `
                                <div class="order-item">
                                    <div class="order-item-info">
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
                                    <div class="order-item-price">${parseFloat(item.price).toFixed(2)} руб.</div>
                                </div>
                            `).join('')}
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

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getStatusClass(status) {
    const classes = {
        'pending': 'status-pending',
        'approved': 'status-approved',
        'transferred': 'status-transferred',
        'completed': 'status-completed'
    };
    return classes[status] || 'status-pending';
}

function getStatusText(status) {
    const texts = {
        'pending': 'Ожидает подтверждения',
        'approved': 'Подтвержден',
        'transferred': 'Передан мастеру',
        'completed': 'Завершен'
    };
    return texts[status] || status;
}

function showMessage(message, type) {
    const msgDiv = document.createElement('div');
    msgDiv.className = `notification ${type}`;
    msgDiv.textContent = message;
    msgDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    
    if (type === 'success') {
        msgDiv.style.backgroundColor = '#10b981';
    } else if (type === 'error') {
        msgDiv.style.backgroundColor = '#ef4444';
    } else {
        msgDiv.style.backgroundColor = '#3b82f6';
    }
    
    document.body.appendChild(msgDiv);
    
    setTimeout(() => {
        msgDiv.remove();
    }, 3000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}