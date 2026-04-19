// Обработка ошибок восстановления пароля
document.addEventListener('DOMContentLoaded', function() {
    const loginInput = document.getElementById('login-input');
    const emailInput = document.getElementById('email-input');
    
    const forgotError = localStorage.getItem('forgotError');
    const errorMessage = localStorage.getItem('errorMessage');
    
    if (forgotError && errorMessage) {
        if (forgotError === 'login') {
            if (loginInput) {
                showFieldError(loginInput, errorMessage);
                loginInput.value = '';
            }
        } else if (forgotError === 'email') {
            if (emailInput) {
                showFieldError(emailInput, errorMessage);
            }
        }
        
        localStorage.removeItem('forgotError');
        localStorage.removeItem('errorMessage');
    }
    
    if (loginInput) {
        loginInput.addEventListener('input', function() {
            clearFieldError(this);
        });
    }
    
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            clearFieldError(this);
        });
    }
});