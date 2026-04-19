// Обработка ошибок авторизации
document.addEventListener('DOMContentLoaded', function() {
    const loginInput = document.getElementById('login-input');
    const passwordInput = document.getElementById('password-input');
    
    const body = document.body;
    
    if (body.classList.contains('password-error')) {
        if (passwordInput) {
            showFieldError(passwordInput, 'Неверный пароль');
            passwordInput.value = '';
        }
        
    } else if (body.classList.contains('login-error')) {
        if (loginInput) {
            showFieldError(loginInput, 'Пользователь не найден');
            loginInput.value = '';
        }
    }
    
    if (loginInput) {
        loginInput.addEventListener('input', function() {
            clearFieldError(this);
        });
    }
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            clearFieldError(this);
        });
    }
});