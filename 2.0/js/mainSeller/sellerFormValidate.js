// Объединенный файл валидации для продавца
document.addEventListener('DOMContentLoaded', function() {
    // Навигация
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            if (targetSection) {
                targetSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    const masterFormFields = [
        'master_name', 'direction', 'category', 'phone', 'about', 'experience'
    ];
    
    const productFormFields = [
        'product_name', 'product_about', 'price', 'count'
    ];

    // Добавляем обработчики для полей формы мастера
    masterFormFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', function() {
                clearFieldError(this);
                setTimeout(updateCategoryFieldState, 10);
            });
            
            field.addEventListener('change', function() {
                clearFieldError(this);
                updateCategoryFieldState();
            });
            
            field.removeAttribute('required');
        }
    });
    
    // Добавляем обработчики для полей формы товара
    productFormFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', function() {
                clearFieldError(this);
            });
            
            field.addEventListener('change', function() {
                clearFieldError(this);
            });
            
            field.removeAttribute('required');
        }
    });

    // Функция для обновления состояния поля категории
    function updateCategoryFieldState() {
        const categoryField = document.getElementById('category');
        if (!categoryField) return;

        const masterName = document.getElementById('master_name');
        const direction = document.getElementById('direction');
        const phone = document.getElementById('phone');
        const about = document.getElementById('about');
        const experience = document.getElementById('experience');

        const hasErrors = 
            !masterName.value.trim() ||
            !direction.value.trim() ||
            !phone.value.trim() ||
            !validatePhone(phone.value.trim()) ||
            !about.value.trim() ||
            isNaN(parseInt(experience.value)) || 
            parseInt(experience.value) < 0 || 
            parseInt(experience.value) > 50;

        if (hasErrors) {
            categoryField.disabled = true;
            categoryField.style.opacity = '0.6';
            categoryField.style.cursor = 'not-allowed';
        } else {
            if (!categoryField.value) {
                categoryField.disabled = false;
                categoryField.style.opacity = '1';
                categoryField.style.cursor = 'default';
            }
        }
    }

    // Обработчик для поля телефона
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        if (!phoneInput.value) {
            phoneInput.value = '+375';
        }
        
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value;
            let cleanValue = value.replace(/\D/g, '');
            
            if (cleanValue.startsWith('375')) {
                cleanValue = cleanValue.substring(3);
            }
            
            cleanValue = cleanValue.substring(0, 9);
            
            let formattedValue = '+375 (';
            
            if (cleanValue.length > 0) {
                formattedValue += cleanValue.substring(0, 2);
                
                if (cleanValue.length > 2) {
                    formattedValue += ') ' + cleanValue.substring(2, 5);
                    
                    if (cleanValue.length > 5) {
                        formattedValue += '-' + cleanValue.substring(5, 7);
                        
                        if (cleanValue.length > 7) {
                            formattedValue += '-' + cleanValue.substring(7, 9);
                        }
                    }
                }
            }
            
            e.target.value = formattedValue;
            
            const cursorPosition = formattedValue.length;
            e.target.setSelectionRange(cursorPosition, cursorPosition);
        });

        phoneInput.addEventListener('keydown', function(e) {
            const cursorPosition = e.target.selectionStart;
            
            if (cursorPosition <= 6 && e.key !== 'Tab' && !e.key.startsWith('Arrow')) {
                e.preventDefault();
                
                if (/\d/.test(e.key)) {
                    const currentValue = e.target.value;
                    const cleanValue = currentValue.replace(/\D/g, '').substring(3);
                    const newValue = '+375 (' + (cleanValue + e.key).substring(0, 9);
                    e.target.value = formatPhoneNumber(newValue.replace(/\D/g, ''));
                    
                    const newCursorPosition = e.target.value.length;
                    e.target.setSelectionRange(newCursorPosition, newCursorPosition);
                }
            }
        });

        function formatPhoneNumber(cleanValue) {
            if (cleanValue.startsWith('375')) {
                cleanValue = cleanValue.substring(3);
            }
            cleanValue = cleanValue.substring(0, 9);
            
            let formatted = '+375 (';
            if (cleanValue.length > 0) {
                formatted += cleanValue.substring(0, 2);
                if (cleanValue.length > 2) {
                    formatted += ') ' + cleanValue.substring(2, 5);
                    if (cleanValue.length > 5) {
                        formatted += '-' + cleanValue.substring(5, 7);
                        if (cleanValue.length > 7) {
                            formatted += '-' + cleanValue.substring(7, 9);
                        }
                    }
                }
            }
            return formatted;
        }
    }

    // Обработчики для числовых полей
    const priceInput = document.getElementById('price');
    if (priceInput) {
        priceInput.addEventListener('input', function(e) {
            let value = parseFloat(e.target.value);
            if (value < 0 || isNaN(value)) {
                e.target.value = 0;
            }
        });

        priceInput.addEventListener('blur', function(e) {
            let value = parseFloat(e.target.value);
            if (value < 0 || isNaN(value)) {
                e.target.value = 0;
            } else {
                e.target.value = value.toFixed(2);
            }
        });
    }

    const countInput = document.getElementById('count');
    if (countInput) {
        countInput.addEventListener('input', function(e) {
            let value = parseInt(e.target.value);
            if (value < 1 || isNaN(value)) {
                e.target.value = 1;
            }
        });
    }

    const experienceInput = document.getElementById('experience');
    if (experienceInput) {
        experienceInput.addEventListener('input', function(e) {
            let value = parseInt(e.target.value);
            if (value < 0 || isNaN(value)) {
                e.target.value = 0;
            }
            if (value > 50) {
                e.target.value = 50;
            }
        });
    }

    // Валидация формы личных данных при отправке
    const masterForm = document.getElementById('master-form');
    if (masterForm) {
        
        masterForm.setAttribute('novalidate', 'novalidate');
        
        masterForm.addEventListener('submit', function(e) {
            clearAllFieldErrors();
            
            let hasError = false;
            
            const masterName = document.getElementById('master_name');
            if (!masterName.value.trim()) {
                showFieldError(masterName, 'Заполните поле Имя мастера');
                hasError = true;
            }
            
            const direction = document.getElementById('direction');
            if (!direction.value.trim()) {
                showFieldError(direction, 'Заполните поле Направление деятельности');
                hasError = true;
            }
            
            const category = document.getElementById('category');
            if (!category.value) {
                showFieldError(category, 'Выберите категорию');
                hasError = true;
            }
            
            const phone = document.getElementById('phone');
            const phoneValue = phone.value.trim();
            if (!phoneValue) {
                showFieldError(phone, 'Заполните поле Номер телефона');
                hasError = true;
            } else if (!validatePhone(phoneValue)) {
                showFieldError(phone, 'Введите корректный номер телефона. Допустимые коды: 29, 33, 44. Номер должен содержать 7 цифр после кода');
                hasError = true;
            }
            
            const about = document.getElementById('about');
            if (!about.value.trim()) {
                showFieldError(about, 'Заполните поле О себе');
                hasError = true;
            }
            
            const experience = document.getElementById('experience');
            const experienceValue = parseInt(experience.value);
            if (isNaN(experienceValue) || experienceValue < 0 || experienceValue > 50) {
                showFieldError(experience, 'Опыт работы должен быть от 0 до 50 лет');
                hasError = true;
            }
            
            updateCategoryFieldState();
            
            if (hasError) {
                e.preventDefault();
                console.log('Form submission prevented due to errors');
                
                const firstError = document.querySelector('.error-input');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }
    
    // Валидация формы добавления товара при отправке
    const productForm = document.getElementById('product-form');
    if (productForm) {
        productForm.setAttribute('novalidate', 'novalidate');
        
        productForm.addEventListener('submit', function(e) {
            clearAllFieldErrors();
            
            let hasError = false;
            
            const productName = document.getElementById('product_name');
            if (!productName.value.trim()) {
                showFieldError(productName, 'Заполните поле Название товара');
                hasError = true;
            }
            
            const productAbout = document.getElementById('product_about');
            if (!productAbout.value.trim()) {
                showFieldError(productAbout, 'Заполните поле Описание товара');
                hasError = true;
            }
            
            const price = document.getElementById('price');
            const priceValue = parseFloat(price.value);
            if (isNaN(priceValue) || priceValue <= 0) {
                showFieldError(price, 'Цена должна быть больше 0');
                hasError = true;
            }
            
            const count = document.getElementById('count');
            const countValue = parseInt(count.value);
            if (isNaN(countValue) || countValue < 1) {
                showFieldError(count, 'Количество должно быть не менее 1');
                hasError = true;
            }
            
            if (hasError) {
                e.preventDefault();
                console.log('Product form submission prevented due to errors');
                
                const firstError = document.querySelector('.error-input');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }

    // Проверка сообщений из URL
    checkUrlMessages();

    // Инициализация состояния поля категории
    updateCategoryFieldState();
});

// Обновленная функция валидации телефона
function validatePhone(phone) {
    const cleanPhone = phone.replace(/\D/g, '');
    
    if (cleanPhone.length !== 12) {
        return false;
    }
    
    if (!cleanPhone.startsWith('375')) {
        return false;
    }
    
    const operatorCode = cleanPhone.substring(3, 5);
    const validCodes = ['29', '33', '44'];
    if (!validCodes.includes(operatorCode)) {
        return false;
    }
    
    const restDigits = cleanPhone.substring(5);
    if (restDigits.length !== 7 || !/^\d{7}$/.test(restDigits)) {
        return false;
    }
    
    return true;
}

// Функция для показа сообщений (уникальная для этого файла)
function showMessage(text, type) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}-message`;
    messageDiv.textContent = text;
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        animation: slideIn 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    if (type === 'success') {
        messageDiv.style.background = 'linear-gradient(135deg, #10b981, #34d399)';
    } else {
        messageDiv.style.background = 'linear-gradient(135deg, #ff6b6b, #ee5a52)';
    }

    document.body.appendChild(messageDiv);
    
    if (!document.querySelector('#message-styles')) {
        const style = document.createElement('style');
        style.id = 'message-styles';
        style.textContent = `
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateX(100%);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            @keyframes slideOut {
                from {
                    opacity: 1;
                    transform: translateX(0);
                }
                to {
                    opacity: 0;
                    transform: translateX(100%);
                }
            }
        `;
        document.head.appendChild(style);
    }

    setTimeout(() => {
        messageDiv.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.remove();
            }
        }, 300);
    }, 3000);
}

// Функция проверки сообщений из URL
function checkUrlMessages() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('success')) {
        showMessage('Операция выполнена успешно!', 'success');
        const newUrl = window.location.pathname + window.location.hash;
        window.history.replaceState({}, document.title, newUrl);
    } else if (urlParams.has('error')) {
        showMessage('Произошла ошибка. Попробуйте еще раз.', 'error');
        const newUrl = window.location.pathname + window.location.hash;
        window.history.replaceState({}, document.title, newUrl);
    }
    
    if (urlParams.has('success_message')) {
        const message = decodeURIComponent(urlParams.get('success_message'));
        showMessage(message, 'success');
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
    
    if (urlParams.has('error_message')) {
        const message = decodeURIComponent(urlParams.get('error_message'));
        showMessage(message, 'error');
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
}