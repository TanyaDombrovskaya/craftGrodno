function addToCart(productId) {
    console.log('Добавление товара ID:', productId);
    
    fetch('./php/userData/addToCart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId + '&quantity=1'
    })
    .then(response => response.json())
    .then(data => {
        console.log('Ответ сервера:', data);
        if (data.success) {
            showMessage('Товар добавлен в корзину', 'success');
            updateCartCounter();
            // Обновляем статус кнопки
            updateCartButtonStatus(productId, true);
        } else {
            showMessage(data.message || 'Ошибка при добавлении', 'error');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showMessage('Ошибка при добавлении в корзину', 'error');
    });
}

// Функция обновления счетчика
function updateCartCounter() {
    fetch('./php/userData/getCartCount.php')
        .then(response => response.json())
        .then(data => {
            console.log('Счетчик корзины:', data);
            let counter = document.querySelector('.cart-counter');
            
            if (counter) {
                if (data.count > 0) {
                    counter.textContent = data.count;
                    counter.style.display = 'inline';
                } else {
                    counter.style.display = 'none';
                }
            }
        })
        .catch(error => console.error('Ошибка загрузки счетчика:', error));
}

// Функция загрузки корзины
function loadCart() {
    console.log('Загрузка корзины...');
    fetch('./php/userData/getCart.php')
        .then(response => response.json())
        .then(items => {
            console.log('Товары в корзине:', items);
            displayCart(items);
        })
        .catch(error => {
            console.error('Ошибка загрузки корзины:', error);
            showCartError();
        });
}

// Функция отображения корзины
function displayCart(items) {
    const container = document.getElementById('cartContainer');
    
    if (!container) return;
    
    if (!items || items.length === 0) {
        container.innerHTML = `
            <div class="cart-empty">
                <h2>Корзина пуста</h2>
                <p>Добавьте товары, чтобы оформить заказ</p>
                <a href="allProducts.php" class="continue-shopping">Перейти к покупкам</a>
            </div>
        `;
        return;
    }
    
    let html = '';
    let total = 0;
    
    items.forEach(item => {
        const itemTotal = parseFloat(item.price) * parseInt(item.quantity);
        total += itemTotal;
        
        // Формируем HTML изображения (как в mainUser)
        let imageHtml = '';
        if (item.has_image && item.image_src) {
            imageHtml = `<img src="${item.image_src}" alt="${escapeHtml(item.productName)}" class="cart-product-image-img">`;
        } else {
            const icon = getCartProductIcon(item.productName);
            imageHtml = `<div class="cart-product-image-icon">${icon}</div>`;
        }
        
        // Создаем карточку товара в стиле mainUser
        html += `
            <div class="cart-product-card" data-cart-id="${item.cartID}">
                <div class="cart-product-image">
                    ${imageHtml}
                </div>
                <div class="cart-product-info">
                    <h3 class="cart-product-title">
                        <a href="productCard.php?id=${item.productID}">${escapeHtml(item.productName)}</a>
                    </h3>
                    <p class="cart-product-description">${escapeHtml(item.aboutProduct ? item.aboutProduct.substring(0, 100) : '')}${item.aboutProduct && item.aboutProduct.length > 100 ? '...' : ''}</p>
                    <div class="cart-product-master">Мастер: ${escapeHtml(item.masterName)}</div>
                    <div class="cart-product-footer">
                        <div class="cart-product-price">${formatPrice(item.price)}</div>
                        <div class="cart-product-quantity">
                            <button class="cart-quantity-btn" onclick="updateCartQuantity(${item.cartID}, ${parseInt(item.quantity) - 1})" ${item.quantity <= 1 ? 'disabled' : ''}>-</button>
                            <span class="cart-quantity-value">${item.quantity}</span>
                            <button class="cart-quantity-btn" onclick="updateCartQuantity(${item.cartID}, ${parseInt(item.quantity) + 1})">+</button>
                            <button class="cart-remove-btn" onclick="removeFromCart(${item.cartID})">Удалить</button>
                        </div>
                        <div class="cart-product-total">${formatPrice(itemTotal)}</div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += `
        <div class="cart-summary">
            <div class="cart-summary-row">
                <span>Итого:</span>
                <span class="cart-total-amount">${formatPrice(total)}</span>
            </div>
            <button class="cart-checkout-btn" onclick="checkout()">Оформить заказ</button>
        </div>
    `;
    
    container.innerHTML = html;
}

// Функция обновления количества
function updateCartQuantity(cartId, newQuantity) {
    if (newQuantity < 1) return;
    
    fetch('./php/userData/updateCart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `cart_id=${cartId}&quantity=${newQuantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCart();
            updateCartCounter();
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showMessage('Ошибка при обновлении', 'error');
    });
}

function removeFromCart(cartId) {
    if (!confirm('Удалить товар из корзины?')) return;
    
    // Получаем productId из карточки
    const cartItem = document.querySelector(`.cart-product-card[data-cart-id="${cartId}"]`);
    let productId = null;
    
    if (cartItem) {
        const titleLink = cartItem.querySelector('.cart-product-title a');
        if (titleLink) {
            const href = titleLink.getAttribute('href');
            const match = href.match(/productCard\.php\?id=(\d+)/);
            if (match) {
                productId = parseInt(match[1]);
            }
        }
    }
    
    fetch('./php/userData/removeFromCart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `cart_id=${cartId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCart();
            updateCartCounter();
            // Обновляем статус кнопки на странице товаров
            if (productId) {
                updateCartButtonStatus(productId, false);
            }
            showMessage('Товар удален из корзины', 'success');
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showMessage('Ошибка при удалении товара', 'error');
    });
}

function checkout() {
    // Проверяем, есть ли товары в корзине
    const cartItems = document.querySelectorAll('.cart-product-card');
    if (cartItems.length === 0) {
        showMessage('Корзина пуста', 'error');
        return;
    }
    
    if (!confirm('Оформить заказ? Средства будут списаны с вашего баланса.')) {
        return;
    }
    
    showMessage('Оформление заказа...', 'info');
    
    fetch('./php/userData/checkout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=checkout'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            // Обновляем баланс
            updateBalanceDisplay();
            // Очищаем корзину
            loadCart();
            updateCartCounter();
            // Перенаправляем на страницу заказов через 2 секунды
            setTimeout(() => {
                window.location.href = 'userProfile.php?tab=orders';
            }, 2000);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Ошибка при оформлении заказа', 'error');
    });
}

// Функция показа уведомлений
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

// Функция отображения ошибки
function showCartError() {
    const container = document.getElementById('cartContainer');
    if (container) {
        container.innerHTML = `
            <div class="cart-empty">
                <h2>Ошибка загрузки</h2>
                <p>Не удалось загрузить корзину. Проверьте подключение к интернету.</p>
                <button onclick="loadCart()" class="continue-shopping">Повторить</button>
            </div>
        `;
    }
}

// Вспомогательные функции
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatPrice(price) {
    return parseFloat(price).toFixed(2) + ' руб.';
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    updateCartCounter();
    if (document.getElementById('cartContainer')) {
        loadCart();
    }
});

// Функция для получения списка товаров в корзине
function getCartProductIds() {
    fetch('./php/userData/getCartProductIds.php')
        .then(response => response.json())
        .then(productIds => {
            updateAllCartButtons(productIds);
        })
        .catch(error => {
            console.error('Ошибка получения списка корзины:', error);
        });
}

// Функция для обновления всех кнопок на странице
function updateAllCartButtons(cartProductIds) {
    // Находим все кнопки добавления в корзину
    const addButtons = document.querySelectorAll('.add-to-cart-btn');
    
    addButtons.forEach(button => {
        // Извлекаем ID товара из атрибута onclick
        const onclickAttr = button.getAttribute('onclick');
        if (onclickAttr) {
            const match = onclickAttr.match(/addToCart\((\d+)\)/);
            if (match) {
                const productId = parseInt(match[1]);
                
                if (cartProductIds.includes(productId)) {
                    // Товар уже в корзине
                    button.innerHTML = '✓ В корзине';
                    button.disabled = true;
                    button.style.opacity = '0.7';
                    button.style.cursor = 'default';
                } else {
                    // Товар не в корзине
                    button.innerHTML = '🛒 В корзину';
                    button.disabled = false;
                    button.style.opacity = '1';
                    button.style.cursor = 'pointer';
                }
            }
        }
    });
}

// Функция обновления статуса конкретного товара (после добавления)
function updateCartButtonStatus(productId, isInCart) {
    const buttons = document.querySelectorAll('.add-to-cart-btn');
    
    buttons.forEach(button => {
        const onclickAttr = button.getAttribute('onclick');
        if (onclickAttr) {
            const match = onclickAttr.match(/addToCart\((\d+)\)/);
            if (match && parseInt(match[1]) === productId) {
                if (isInCart) {
                    button.innerHTML = '✓ В корзине';
                    button.disabled = true;
                    button.style.opacity = '0.7';
                    button.style.cursor = 'default';
                } else {
                    button.innerHTML = '🛒 В корзину';
                    button.disabled = false;
                    button.style.opacity = '1';
                    button.style.cursor = 'pointer';
                }
            }
        }
    });
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    updateCartCounter();
    
    // Проверяем, какие товары в корзине (для всех страниц)
    if (typeof getCartProductIds === 'function') {
        getCartProductIds();
    }
    
    // Если это страница корзины - загружаем корзину
    if (document.getElementById('cartContainer')) {
        loadCart();
    }
});

// Функция получения баланса пользователя
function updateBalanceDisplay() {
    fetch('./php/userData/getBalance.php')
        .then(response => response.json())
        .then(data => {
            const balanceSpan = document.getElementById('navBalance');
            if (balanceSpan) {
                balanceSpan.textContent = data.balance;
            }
        })
        .catch(error => console.error('Ошибка получения баланса:', error));
}

// Обновляем инициализацию
document.addEventListener('DOMContentLoaded', function() {
    updateCartCounter();
    updateBalanceDisplay();
    
    if (typeof getCartProductIds === 'function') {
        getCartProductIds();
    }
    
    if (document.getElementById('cartContainer')) {
        loadCart();
    }
});