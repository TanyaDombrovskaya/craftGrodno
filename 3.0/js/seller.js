let currentDeleteProductId = null;

document.addEventListener('DOMContentLoaded', function() {
    // Переключение вкладок
    const navBtns = document.querySelectorAll('.seller-nav-btn');
    const tabs = document.querySelectorAll('.seller-tab');
    
    navBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            navBtns.forEach(b => b.classList.remove('active'));
            tabs.forEach(t => t.classList.remove('active'));
            
            this.classList.add('active');
            document.getElementById(`tab-${tabId}`).classList.add('active');
            
            if (tabId === 'products') {
                loadSellerProducts();
            } else if (tabId === 'sales') {
                loadSales();
            }
        });
    });
    
    // Обновление баланса в навбаре
    updateSellerBalance();
    
    // Модальное окно вывода средств
    initWithdrawModal();
    
    // Загрузка товаров если активна вкладка
    if (document.getElementById('tab-products') && document.getElementById('tab-products').classList.contains('active')) {
        loadSellerProducts();
    }
    if (document.getElementById('tab-sales') && document.getElementById('tab-sales').classList.contains('active')) {
        loadSales();
    }
    
    // Фильтры продаж
    const applyFilterBtn = document.getElementById('applySalesFilter');
    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', loadSales);
    }
    
    const resetFilterBtn = document.getElementById('resetSalesFilter');
    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', resetSalesFilters);
    }
    
    // Загрузка продаж если активна вкладка
    if (document.getElementById('tab-sales') && document.getElementById('tab-sales').classList.contains('active')) {
        loadSales();
    }
});

function updateSellerBalance() {
    fetch('./php/masterData/getSellerBalance.php')
        .then(response => response.json())
        .then(data => {
            const navBalance = document.getElementById('sellerNavBalance');
            const sidebarBalance = document.getElementById('sellerBalanceAmount');
            const availableAmount = document.getElementById('availableAmount');
            
            if (navBalance) navBalance.textContent = data.balance;
            if (sidebarBalance) sidebarBalance.textContent = data.balance + ' руб.';
            if (availableAmount) availableAmount.textContent = data.balance + ' руб.';
        })
        .catch(error => console.error('Ошибка получения баланса:', error));
}

function initWithdrawModal() {
    const modal = document.getElementById('withdrawModal');
    const showBtn = document.getElementById('showWithdrawModal');
    const closeBtns = document.querySelectorAll('#withdrawModal .close-modal');
    const withdrawMethod = document.getElementById('withdrawMethod');
    const cardGroup = document.getElementById('cardNumberGroup');
    const phoneGroup = document.getElementById('phoneNumberGroup');
    const confirmBtn = document.getElementById('confirmWithdraw');
    
    if (showBtn) {
        showBtn.addEventListener('click', () => {
            modal.style.display = 'block';
            resetWithdrawForm();
        });
    }
    
    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            modal.style.display = 'none';
            resetWithdrawForm();
        });
    });
    
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
            resetWithdrawForm();
        }
    });
    
    if (withdrawMethod) {
        withdrawMethod.addEventListener('change', function() {
            if (this.value === 'card') {
                cardGroup.style.display = 'block';
                phoneGroup.style.display = 'none';
                document.getElementById('phoneNumber').value = '';
            } else {
                cardGroup.style.display = 'none';
                phoneGroup.style.display = 'block';
                document.getElementById('cardNumber').value = '';
            }
        });
    }
    
    // Валидация суммы - только числа
    const amountInput = document.getElementById('withdrawAmount');
    if (amountInput) {
        amountInput.addEventListener('input', function(e) {
            let value = this.value;
            value = value.replace(/[^\d]/g, '');
            if (value) {
                let numValue = parseInt(value, 10);
                if (!isNaN(numValue)) {
                    this.value = numValue;
                } else {
                    this.value = '';
                }
            } else {
                this.value = '';
            }
            
            this.classList.remove('error');
            const errorMsg = this.parentNode.querySelector('.error-message');
            if (errorMsg) errorMsg.remove();
        });
        
        amountInput.addEventListener('keypress', function(e) {
            const charCode = e.which ? e.which : e.keyCode;
            if (charCode < 48 || charCode > 57) {
                e.preventDefault();
            }
        });
        
        amountInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const numbersOnly = pastedText.replace(/[^\d]/g, '');
            if (numbersOnly) {
                this.value = numbersOnly;
            }
        });
    }
    
    // Валидация номера карты
    const cardInput = document.getElementById('cardNumber');
    if (cardInput) {
        cardInput.addEventListener('input', function(e) {
            let value = this.value;
            value = value.replace(/[^\d\s]/g, '');
            const cleanValue = value.replace(/\s/g, '');
            
            if (cleanValue.length > 16) {
                const trimmed = cleanValue.slice(0, 16);
                let formatted = trimmed.replace(/(\d{4})(?=\d)/g, '$1 ');
                this.value = formatted;
            } else {
                let formatted = cleanValue.replace(/(\d{4})(?=\d)/g, '$1 ');
                this.value = formatted;
            }
            
            this.classList.remove('error');
            const errorMsg = this.parentNode.querySelector('.error-message');
            if (errorMsg) errorMsg.remove();
        });
        
        cardInput.addEventListener('keypress', function(e) {
            const charCode = e.which ? e.which : e.keyCode;
            if (charCode < 48 || charCode > 57) {
                e.preventDefault();
            }
        });
        
        cardInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const numbersOnly = pastedText.replace(/[^\d]/g, '');
            if (numbersOnly) {
                const trimmed = numbersOnly.slice(0, 16);
                let formatted = trimmed.replace(/(\d{4})(?=\d)/g, '$1 ');
                this.value = formatted;
            }
        });
    }
    
    // Валидация телефона
    const phoneInput = document.getElementById('phoneNumber');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = this.value;
            value = value.replace(/[^\d\+\s]/g, '');
            
            const cleanValue = value.replace(/\s/g, '');
            if (cleanValue && !cleanValue.match(/^\+375\d{9}$/)) {
                this.classList.add('error');
            } else {
                this.classList.remove('error');
                const errorMsg = this.parentNode.querySelector('.error-message');
                if (errorMsg) errorMsg.remove();
            }
        });
        
        phoneInput.addEventListener('keypress', function(e) {
            const charCode = e.which ? e.which : e.keyCode;
            const char = String.fromCharCode(charCode);
            if (!/[\d\+\s]/.test(char) && charCode !== 8 && charCode !== 46) {
                e.preventDefault();
            }
        });
    }
    
    if (confirmBtn) {
        confirmBtn.addEventListener('click', withdrawFunds);
    }
}

function resetWithdrawForm() {
    const amountInput = document.getElementById('withdrawAmount');
    const cardInput = document.getElementById('cardNumber');
    const phoneInput = document.getElementById('phoneNumber');
    const methodSelect = document.getElementById('withdrawMethod');
    
    if (amountInput) amountInput.value = '';
    if (cardInput) {
        cardInput.value = '';
        cardInput.classList.remove('error');
    }
    if (phoneInput) {
        phoneInput.value = '';
        phoneInput.classList.remove('error');
    }
    if (methodSelect) methodSelect.value = 'card';
    
    const cardGroup = document.getElementById('cardNumberGroup');
    const phoneGroup = document.getElementById('phoneNumberGroup');
    if (cardGroup) cardGroup.style.display = 'block';
    if (phoneGroup) phoneGroup.style.display = 'none';
    
    document.querySelectorAll('.error-message').forEach(msg => msg.remove());
}

function validateCardNumber(cardNumber) {
    const cleanNumber = cardNumber.replace(/\s/g, '');
    return /^\d{16}$/.test(cleanNumber);
}

function validatePhoneNumber(phoneNumber) {
    const cleanNumber = phoneNumber.replace(/\s/g, '');
    return /^\+375\d{9}$/.test(cleanNumber);
}

function withdrawFunds() {
    const amount = document.getElementById('withdrawAmount').value;
    const method = document.getElementById('withdrawMethod').value;
    let accountDetails = '';
    let isValid = true;
    
    document.querySelectorAll('.error-message').forEach(msg => msg.remove());
    
    if (!amount || amount <= 0) {
        showMessage('Введите корректную сумму', 'error');
        return;
    }
    
    const availableAmount = parseFloat(document.getElementById('availableAmount').textContent);
    if (parseFloat(amount) > availableAmount) {
        showMessage('Сумма вывода не может превышать доступный баланс', 'error');
        return;
    }
    
    if (method === 'card') {
        const cardInput = document.getElementById('cardNumber');
        const cardNumber = cardInput.value;
        
        if (!cardNumber) {
            showFieldError(cardInput, 'Введите номер карты');
            isValid = false;
        } else if (!validateCardNumber(cardNumber)) {
            showFieldError(cardInput, 'Введите корректный номер карты (16 цифр)');
            isValid = false;
        } else {
            accountDetails = cardNumber.replace(/\s/g, '');
        }
    } else {
        const phoneInput = document.getElementById('phoneNumber');
        const phoneNumber = phoneInput.value;
        
        if (!phoneNumber) {
            showFieldError(phoneInput, 'Введите номер телефона');
            isValid = false;
        } else if (!validatePhoneNumber(phoneNumber)) {
            showFieldError(phoneInput, 'Введите корректный номер телефона в формате +375 XX XXX-XX-XX');
            isValid = false;
        } else {
            accountDetails = phoneNumber.replace(/\s/g, '');
        }
    }
    
    if (!isValid) {
        return;
    }
    
    fetch('./php/masterData/withdrawFunds.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `amount=${amount}&method=${method}&details=${encodeURIComponent(accountDetails)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            updateSellerBalance();
            const modal = document.getElementById('withdrawModal');
            if (modal) modal.style.display = 'none';
            resetWithdrawForm();
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Ошибка при выводе средств', 'error');
    });
}

function showFieldError(input, message) {
    const existingError = input.parentNode.querySelector('.error-message');
    if (existingError) existingError.remove();
    
    input.classList.add('error');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    input.parentNode.appendChild(errorDiv);
    
    input.addEventListener('input', function onInput() {
        input.classList.remove('error');
        const msg = input.parentNode.querySelector('.error-message');
        if (msg) msg.remove();
        input.removeEventListener('input', onInput);
    });
}

function loadSellerProducts() {
    const container = document.getElementById('productsGrid');
    if (!container) return;
    
    container.innerHTML = '<div class="loading">Загрузка товаров...</div>';
    
    fetch('./php/masterData/getAllMasterProducts.php')
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<div class="no-products">Ошибка загрузки товаров</div>';
        });
}

function loadSales() {
    const container = document.getElementById('salesContainer');
    if (!container) return;
    
    // Получаем значения фильтров (убран период)
    const statusSelect = document.getElementById('statusFilter');
    const dateFromInput = document.getElementById('dateFrom');
    const dateToInput = document.getElementById('dateTo');
    
    const status = statusSelect ? statusSelect.value : 'all';
    const dateFrom = dateFromInput && dateFromInput.value ? dateFromInput.value : '';
    const dateTo = dateToInput && dateToInput.value ? dateToInput.value : '';
    
    console.log('Фильтры:', { status, dateFrom, dateTo });
    
    container.innerHTML = '<div class="loading">Загрузка продаж...</div>';
    
    // Формируем URL с параметрами (убран period)
    let url = './php/masterData/getSellerSales.php?';
    url += `&status=${encodeURIComponent(status)}`;
    if (dateFrom) url += `&date_from=${encodeURIComponent(dateFrom)}`;
    if (dateTo) url += `&date_to=${encodeURIComponent(dateTo)}`;
    
    console.log('Запрос к:', url);
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Ответ сервера:', data);
            if (data.error) {
                container.innerHTML = `<div class="no-products">Ошибка: ${data.error}</div>`;
                return;
            }
            if (data.stats) {
                updateSalesStats(data.stats);
            }
            if (data.sales && data.sales.length > 0) {
                displaySales(data.sales);
            } else {
                container.innerHTML = '<div class="no-products">Нет продаж по выбранным фильтрам</div>';
            }
        })
        .catch(error => {
            console.error('Ошибка загрузки продаж:', error);
            container.innerHTML = '<div class="no-products">Ошибка загрузки продаж: ' + error.message + '</div>';
        });
}

// Функция сброса фильтров
function resetSalesFilters() {
    const statusSelect = document.getElementById('statusFilter');
    const dateFromInput = document.getElementById('dateFrom');
    const dateToInput = document.getElementById('dateTo');
    
    if (statusSelect) statusSelect.value = 'all';
    if (dateFromInput) dateFromInput.value = '';
    if (dateToInput) dateToInput.value = '';
    
    loadSales();
}

function updateSalesStats(stats) {
    const totalCount = document.getElementById('totalSalesCount');
    const totalRevenue = document.getElementById('totalRevenue');
    const averageCheck = document.getElementById('averageCheck');
    
    if (totalCount) totalCount.textContent = stats.total_count || 0;
    if (totalRevenue) totalRevenue.textContent = (stats.total_revenue || 0).toFixed(2) + ' руб.';
    if (averageCheck) averageCheck.textContent = (stats.average_check || 0).toFixed(2) + ' руб.';
}

function displaySales(sales) {
    const container = document.getElementById('salesContainer');
    
    if (!sales || sales.length === 0) {
        container.innerHTML = '<div class="no-products">Нет продаж</div>';
        return;
    }
    
    let html = '<div class="sales-table-wrapper"><table class="sales-table"><thead><tr>';
    html += '<th>Дата</th><th>Товар</th><th>Кол-во</th><th>Цена</th><th>Сумма</th><th>Покупатель</th><th>Адрес доставки</th><th>Статус</th><th>Действие</th>';
    html += '</tr></thead><tbody>';
    
    sales.forEach(sale => {
        const statusClass = getStatusClass(sale.status);
        const statusText = getStatusText(sale.status);
        const totalAmount = parseFloat(sale.price) * parseInt(sale.quantity);
        const buyerAddress = sale.buyer_address || 'Адрес не указан';
        
        html += `<tr data-order-item-id="${sale.order_itemID}">`;
        html += `<td>${formatDateTime(sale.order_date)}</td>`;
        html += `<td><a href="productCard.php?id=${sale.productID}" target="_blank" class="sale-product-link">${escapeHtml(sale.productName)}</a></td>`;
        html += `<td>${sale.quantity}</td>`;
        html += `<td>${parseFloat(sale.price).toFixed(2)} руб.</td>`;
        html += `<td>${totalAmount.toFixed(2)} руб.</td>`;
        html += `<td>${escapeHtml(sale.buyer_name || 'Неизвестно')}<br><small>@${escapeHtml(sale.buyer_login || '')}</small></td>`;
        html += `<td class="address-cell">${escapeHtml(buyerAddress)}</td>`;
        html += `<td><span class="status-badge ${statusClass}">${statusText}</span></td>`;
        html += `<td>`;
        
        if (sale.status === 'approved') {
            html += `<button class="change-status-btn" onclick="updateOrderItemStatus(${sale.order_itemID}, 'collecting')">Начать сборку</button>`;
        } else if (sale.status === 'collecting') {
            html += `<button class="change-status-btn" onclick="updateOrderItemStatus(${sale.order_itemID}, 'delivering')">Отправить</button>`;
        } else if (sale.status === 'delivering') {
            html += `<button class="change-status-btn" onclick="updateOrderItemStatus(${sale.order_itemID}, 'delivered')">Подтвердить доставку</button>`;
        } else if (sale.status === 'delivered') {
            html += `<span class="status-completed">✓ Ожидает подтверждения</span>`;
        } else if (sale.status === 'completed') {
            html += `<span class="status-completed">✓ Заказ завершён</span>`;
        } else if (sale.status === 'pending') {
            html += `<span class="status-waiting">Ожидает одобрения менеджера</span>`;
        } else {
            html += `<span class="status-waiting">${statusText}</span>`;
        }
        
        html += `</td>`;
        html += `</tr>`;
    });
    
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

function updateOrderItemStatus(orderItemId, newStatus) {
    const btn = document.querySelector(`button[onclick*="updateOrderItemStatus(${orderItemId},"]`);
    let originalText = '';
    if (btn) {
        originalText = btn.textContent;
        btn.textContent = '⏳ Обновление...';
        btn.disabled = true;
    }
    
    fetch('./php/masterData/updateOrderItemStatus.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `order_item_id=${orderItemId}&status=${newStatus}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            loadSales();
        } else {
            showMessage(data.message || 'Ошибка при обновлении статуса', 'error');
            if (btn) {
                btn.textContent = originalText;
                btn.disabled = false;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Ошибка при обновлении статуса', 'error');
        if (btn) {
            btn.textContent = originalText;
            btn.disabled = false;
        }
    });
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

function getStatusText(status) {
    const texts = {
        'pending': 'Ожидает одобрения',
        'approved': 'Одобрен',
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

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Функции для модального окна удаления
window.confirmDelete = function() {
    if (currentDeleteProductId) {
        fetch('./php/masterData/deleteProduct.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_id=' + currentDeleteProductId
        })
        .then(response => response.text())
        .then(result => {
            if (result === 'success') {
                showMessage('Товар успешно удален', 'success');
                loadSellerProducts();
                const modal = document.getElementById('deleteModal');
                if (modal) modal.style.display = 'none';
                currentDeleteProductId = null;
            } else {
                showMessage('Ошибка при удалении товара', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Ошибка при удалении товара', 'error');
        });
    }
};

// Обработчики для модального окна удаления
document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        const confirmBtn = deleteModal.querySelector('.confirm-delete-btn');
        const cancelBtns = deleteModal.querySelectorAll('.cancel-btn, .close-modal');
        
        if (confirmBtn) {
            confirmBtn.addEventListener('click', window.confirmDelete);
        }
        
        cancelBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                deleteModal.style.display = 'none';
                currentDeleteProductId = null;
            });
        });
        
        window.addEventListener('click', (e) => {
            if (e.target === deleteModal) {
                deleteModal.style.display = 'none';
                currentDeleteProductId = null;
            }
        });
    }
});