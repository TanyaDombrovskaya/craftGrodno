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

