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

    // Проверяем и блокируем категорию при загрузке
    checkAndLockCategoryOnLoad();

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

    // Функция для проверки и блокировки категории при загрузке
    function checkAndLockCategoryOnLoad() {
        const categoryField = document.getElementById('category');
        if (!categoryField) return;

        // Проверяем localStorage
        const categoryLocked = localStorage.getItem('category_permanently_locked');
        const savedCategoryValue = localStorage.getItem('saved_category_value');
        
        if (categoryLocked === 'true' && savedCategoryValue) {
            // Восстанавливаем значение категории
            categoryField.value = savedCategoryValue;
            // Блокируем поле навсегда
            permanentlyLockCategory(categoryField);
            return;
        }

        // Проверяем, есть ли уже выбранное значение и форма была отправлена
        if (categoryField.value && categoryField.value !== '') {
            // Проверяем, не была ли эта страница загружена после сохранения
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success') || urlParams.has('success_message')) {
                // Сохраняем в localStorage что категория заблокирована
                localStorage.setItem('category_permanently_locked', 'true');
                localStorage.setItem('saved_category_value', categoryField.value);
                permanentlyLockCategory(categoryField);
            } else {
                // Проверяем, есть ли данные о мастере (заполнена ли форма)
                const masterName = document.getElementById('master_name');
                const direction = document.getElementById('direction');
                const phone = document.getElementById('phone');
                
                if (masterName && masterName.value && direction && direction.value && phone && phone.value) {
                    // Форма уже заполнена, значит это редактирование существующего мастера
                    localStorage.setItem('category_permanently_locked', 'true');
                    localStorage.setItem('saved_category_value', categoryField.value);
                    permanentlyLockCategory(categoryField);
                }
            }
        }
    }

    // Функция для перманентной блокировки категории
    function permanentlyLockCategory(categoryField) {
        categoryField.disabled = true;
        categoryField.style.opacity = '0.6';
        categoryField.style.cursor = 'not-allowed';
        categoryField.style.backgroundColor = '#f3f4f6';
        
        // Сохраняем признак блокировки в data-атрибут
        categoryField.setAttribute('data-locked-forever', 'true');
        
        // Добавляем поясняющий текст, если его нет
        if (!categoryField.parentElement.querySelector('.category-lock-hint')) {
            const hint = document.createElement('small');
            hint.className = 'category-lock-hint';
            hint.style.cssText = 'display: block; margin-top: 5px; color: #6c757d; font-size: 12px;';
            hint.innerHTML = '🔒 Категория выбрана и не может быть изменена';
            categoryField.parentElement.appendChild(hint);
        }
    }

    // Функция для обновления состояния поля категории
    function updateCategoryFieldState() {
        const categoryField = document.getElementById('category');
        if (!categoryField) return;

        // Если категория уже заблокирована навсегда, не меняем состояние
        if (categoryField.getAttribute('data-locked-forever') === 'true') {
            return;
        }

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
            // Проверяем, не заблокирована ли категория навсегда
            const isLockedForever = localStorage.getItem('category_permanently_locked') === 'true';
            if (!isLockedForever && !categoryField.value) {
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
            // Проверяем, не заблокирована ли категория
            const isLocked = category.getAttribute('data-locked-forever') === 'true';
            if (!isLocked && !category.value) {
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
            } else {
                // После успешной валидации, сохраняем категорию в localStorage
                const categoryField = document.getElementById('category');
                if (categoryField && categoryField.value && !categoryField.disabled) {
                    localStorage.setItem('category_permanently_locked', 'true');
                    localStorage.setItem('saved_category_value', categoryField.value);
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

// Функция проверки сообщений из URL
function checkUrlMessages() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('success')) {
        showMessage('Операция выполнена успешно!', 'success');
        const newUrl = window.location.pathname + window.location.hash;
        window.history.replaceState({}, document.title, newUrl);
        
        // Дополнительная блокировка категории после успешной операции
        setTimeout(function() {
            const categoryField = document.getElementById('category');
            if (categoryField && categoryField.value && localStorage.getItem('category_permanently_locked') !== 'true') {
                localStorage.setItem('category_permanently_locked', 'true');
                localStorage.setItem('saved_category_value', categoryField.value);
                if (categoryField.getAttribute('data-locked-forever') !== 'true') {
                    categoryField.disabled = true;
                    categoryField.style.opacity = '0.6';
                    categoryField.style.cursor = 'not-allowed';
                    categoryField.style.backgroundColor = '#f3f4f6';
                    categoryField.setAttribute('data-locked-forever', 'true');
                }
            }
        }, 100);
        
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
        
        // Блокируем категорию после успешного сохранения
        setTimeout(function() {
            const categoryField = document.getElementById('category');
            if (categoryField && categoryField.value && localStorage.getItem('category_permanently_locked') !== 'true') {
                localStorage.setItem('category_permanently_locked', 'true');
                localStorage.setItem('saved_category_value', categoryField.value);
                if (categoryField.getAttribute('data-locked-forever') !== 'true') {
                    categoryField.disabled = true;
                    categoryField.style.opacity = '0.6';
                    categoryField.style.cursor = 'not-allowed';
                    categoryField.style.backgroundColor = '#f3f4f6';
                    categoryField.setAttribute('data-locked-forever', 'true');
                    
                    // Добавляем подсказку
                    if (!categoryField.parentElement.querySelector('.category-lock-hint')) {
                        const hint = document.createElement('small');
                        hint.className = 'category-lock-hint';
                        hint.style.cssText = 'display: block; margin-top: 5px; color: #6c757d; font-size: 12px;';
                        hint.innerHTML = '🔒 Категория выбрана и не может быть изменена';
                        categoryField.parentElement.appendChild(hint);
                    }
                }
            }
        }, 100);
    }
    
    if (urlParams.has('error_message')) {
        const message = decodeURIComponent(urlParams.get('error_message'));
        showMessage(message, 'error');
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
}