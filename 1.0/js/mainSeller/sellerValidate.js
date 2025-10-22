// Добавляем слушатели событий для всех полей ввода
document.addEventListener('DOMContentLoaded', function() {
    console.log('sellerValidate.js loaded');
    
    // Поля формы личных данных мастера
    const masterFormFields = [
        'master_name', 'direction', 'category', 'phone', 'about', 'experience'
    ];
    
    // Поля формы добавления товара
    const productFormFields = [
        'product_name', 'product_about', 'price', 'count'
    ];
    
    // Добавляем обработчики для полей формы мастера
    masterFormFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', function() {
                clearFieldError(this);
            });
            
            field.addEventListener('change', function() {
                clearFieldError(this);
            });
            
            // Убираем стандартную валидацию HTML5
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
            
            // Убираем стандартную валидацию HTML5
            field.removeAttribute('required');
        }
    });
    
    // Валидация формы личных данных при отправке
    const masterForm = document.getElementById('master-form');
    if (masterForm) {
        console.log('Master form found');
        
        // Отключаем стандартную HTML5 валидацию
        masterForm.setAttribute('novalidate', 'novalidate');
        
        masterForm.addEventListener('submit', function(e) {
            console.log('Master form submit event');
            clearAllFieldErrors();
            
            let hasError = false;
            
            // Проверка обязательных полей
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
                showFieldError(phone, 'Введите корректный номер телефона');
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
            
            if (hasError) {
                e.preventDefault();
                console.log('Form submission prevented due to errors');
                
                // Прокрутка к первой ошибке
                const firstError = document.querySelector('.error-input');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } else {
                console.log('Form validation passed');
            }
        });
    }
    
    // Валидация формы добавления товара при отправке
    const productForm = document.getElementById('product-form');
    if (productForm) {
        console.log('Product form found');
        
        // Отключаем стандартную HTML5 валидацию
        productForm.setAttribute('novalidate', 'novalidate');
        
        productForm.addEventListener('submit', function(e) {
            console.log('Product form submit event');
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
                
                // Прокрутка к первой ошибке
                const firstError = document.querySelector('.error-input');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } else {
                console.log('Product form validation passed');
            }
        });
    }
});

// Функция валидации телефона
function validatePhone(phone) {
    // Простая валидация - можно настроить под нужный формат
    const phoneRegex = /^[\+]?[0-9\s\-\(\)]{7,15}$/;
    return phoneRegex.test(phone);
}