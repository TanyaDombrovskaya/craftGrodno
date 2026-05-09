// Функция для показа ошибки поля
function showFieldError(input, message) {
    clearFieldError(input);
    
    input.classList.add('error-input');
    
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.textContent = message;
    errorElement.style.cssText = 'color: #ff4444; font-size: 12px; margin-top: 5px;';
    
    input.parentNode.appendChild(errorElement);
}

// Функция для очистки ВСЕХ ошибок
function clearAllFieldErrors() {
    const errorInputs = document.querySelectorAll('.error-input');
    errorInputs.forEach(input => {
        input.classList.remove('error-input');
    });
    
    const errorMessages = document.querySelectorAll('.field-error');
    errorMessages.forEach(message => {
        message.remove();
    });
}

// Функция для очистки ошибки конкретного поля
function clearFieldError(input) {
    input.classList.remove('error-input');
    const errorElement = input.parentNode.querySelector('.field-error');
    if (errorElement) {
        errorElement.remove();
    }
}

// Слушатели ввода для автоматического скрытия ошибок
document.querySelectorAll('#login-input, #name-input, #email-input, #password-input, #confirm-password-input').forEach(input => {
    input.addEventListener('input', function() {
        clearFieldError(this);
        
        if (this.id === 'email-input' && this.value.trim().length > 0) {
            if (!validateEmail(this.value.trim())) {
                showFieldError(this, 'Введите корректный e-mail');
            }
        }
    });
});

// ========== ЕДИНАЯ ФУНКЦИЯ УВЕДОМЛЕНИЙ ДЛЯ ВСЕЙ СИСТЕМЫ ==========
function showMessage(message, type) {
    // Удаляем существующее уведомление, если есть
    const existingNotification = document.querySelector('.global-notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = `global-notification ${type}`;
    notification.textContent = message;
    
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        padding: 14px 28px;
        border-radius: 12px;
        color: white;
        z-index: 10001;
        font-weight: 600;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        font-size: 14px;
        letter-spacing: 0.3px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        backdrop-filter: blur(4px);
        animation: slideInRight 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: default;
    `;
    
    // НОВАЯ ЦВЕТОВАЯ СХЕМА
    if (type === 'success') {
        notification.style.background = 'linear-gradient(135deg, #E88538, #D16A22)';
    } else if (type === 'error') {
        notification.style.background = 'linear-gradient(135deg, #E88538, #D16A22)';
    } else if (type === 'info') {
        notification.style.background = 'linear-gradient(135deg, #61353B, #4A282D)';
    } else {
        notification.style.background = 'linear-gradient(135deg, #E88538, #D16A22)';
    }
    
    document.body.appendChild(notification);
    
    // Анимация исчезновения
    setTimeout(() => {
        notification.style.animation = 'fadeOutRight 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 3000);
}

// Добавляем CSS анимации, если их нет
if (!document.querySelector('#global-notification-styles')) {
    const style = document.createElement('style');
    style.id = 'global-notification-styles';
    style.textContent = `
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        @keyframes fadeOutRight {
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