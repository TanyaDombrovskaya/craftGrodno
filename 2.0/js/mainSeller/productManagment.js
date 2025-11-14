// Обработчик кнопки редактирования
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('edit-product-btn')) {
        const productId = e.target.dataset.productId;
        const productName = e.target.dataset.productName;
        const productAbout = e.target.dataset.productAbout;
        const productPrice = e.target.dataset.productPrice;
        const productCount = e.target.dataset.productCount;
        
        openEditModal(productId, productName, productAbout, productPrice, productCount);
    }
});

// Обновленная функция setupModalEvents
function setupModalEvents() {
    const modal = document.getElementById('editProductModal');
    
    if (!modal) return;
    
    // Закрытие при клике на крестик
    const closeBtn = modal.querySelector('.close-modal');
    if (closeBtn) {
        closeBtn.addEventListener('click', closeEditModal);
    }
    
    // Закрытие при клике на кнопку отмены
    const cancelBtn = modal.querySelector('.cancel-button');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeEditModal);
    }
    
    // Закрытие при клике вне модального окна
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeEditModal();
        }
    });
    
    // Закрытие при нажатии Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            closeEditModal();
        }
    });
}

// Обновленная функция closeEditModal
function closeEditModal() {
    const modal = document.getElementById('editProductModal');
    if (modal) {
        modal.style.display = 'none';
        // Сбрасываем форму
        const form = document.getElementById('edit-product-form');
        if (form) {
            form.reset();
        }
        // Сбрасываем превью изображения
        const imageInput = document.getElementById('edit_product_image');
        if (imageInput) {
            imageInput.value = '';
        }
    }
}

// Обновленная функция openEditModal
function openEditModal(productId, productName, productAbout, productPrice, productCount) {
    const modal = document.getElementById('editProductModal');
    if (!modal) return;
    
    // Заполняем форму данными товара
    document.getElementById('edit_product_id').value = productId;
    document.getElementById('edit_product_name').value = productName;
    document.getElementById('edit_product_about').value = productAbout;
    document.getElementById('edit_price').value = productPrice;
    document.getElementById('edit_count').value = productCount;
    
    // Показываем текущее изображение в превью
    const preview = document.getElementById('editImagePreview');
    const productCard = document.querySelector(`[data-product-id="${productId}"]`);
    
    if (productCard) {
        const productImage = productCard.querySelector('.product-image-img');
        if (productImage && productImage.src && !productImage.src.includes('data:,')) {
            preview.innerHTML = `<img src="${productImage.src}" alt="Текущее изображение" style="max-width: 100%; max-height: 200px; border-radius: 6px;">`;
        } else {
            preview.innerHTML = '<span class="preview-text">Текущее изображение не найдено</span>';
        }
    } else {
        preview.innerHTML = '<span class="preview-text">Текущее изображение не найдено</span>';
    }
    
    // Показываем модальное окно
    modal.style.display = 'block';
    
    // Фокусируемся на первом поле формы
    setTimeout(() => {
        const firstInput = document.getElementById('edit_product_name');
        if (firstInput) {
            firstInput.focus();
        }
    }, 100);
}

// Обработка отправки формы редактирования
function setupEditForm() {
    const form = document.getElementById('edit-product-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            updateProduct();
        });
    }
}

function updateProduct() {
    const form = document.getElementById('edit-product-form');
    const formData = new FormData(form);
    const submitBtn = form.querySelector('.submit-button');
    
    // Блокируем кнопку на время отправки
    submitBtn.disabled = true;
    submitBtn.textContent = 'Сохранение...';
    
    fetch('./php/masterData/updateProduct.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Товар успешно обновлен!', 'success');
            closeEditModal();
            // Обновляем список товаров
            loadProducts();
        } else {
            showNotification('Ошибка при обновлении товара: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Произошла ошибка при обновлении товара', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Сохранить изменения';
    });
}

// Обработка превью изображения для формы редактирования
function setupImagePreview() {
    const imageInput = document.getElementById('edit_product_image');
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('editImagePreview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Новое изображение" style="max-width: 100%; max-height: 150px;">`;
                }
                reader.readAsDataURL(file);
            } else {
                // Возвращаем оригинальное превью
                const productId = document.getElementById('edit_product_id').value;
                const productCard = document.querySelector(`[data-product-id="${productId}"]`);
                const productImage = productCard.querySelector('.product-image-img');
                
                if (productImage && productImage.src) {
                    preview.innerHTML = `<img src="${productImage.src}" alt="Текущее изображение" style="max-width: 100%; max-height: 150px;">`;
                } else {
                    preview.innerHTML = '<span class="preview-text">Текущее изображение</span>';
                }
            }
        });
    }
}

// Функция для показа уведомлений
function showNotification(message, type = 'info') {
    // Создаем элемент уведомления
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Добавляем стили
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        z-index: 1001;
        font-weight: 500;
        max-width: 300px;
        animation: slideIn 0.3s ease;
    `;
    
    if (type === 'success') {
        notification.style.backgroundColor = '#27ae60';
    } else if (type === 'error') {
        notification.style.backgroundColor = '#e74c3c';
    } else {
        notification.style.backgroundColor = '#3498db';
    }
    
    document.body.appendChild(notification);
    
    // Удаляем уведомление через 3 секунды
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Функция для загрузки товаров (если нужно обновлять без перезагрузки страницы)
function loadProducts() {
    fetch('./php/masterData/getAllMasterProducts.php')
        .then(response => response.text())
        .then(html => {
            const productGrid = document.querySelector('.product-grid');
            if (productGrid) {
                productGrid.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Error loading products:', error);
        });
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    setupModalEvents();
    setupEditForm();
    setupImagePreview();
});

// Добавляем CSS анимации для уведомлений
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);