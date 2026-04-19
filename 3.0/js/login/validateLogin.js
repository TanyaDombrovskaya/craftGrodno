document.getElementById('login-button').addEventListener('click', function() {    
    clearAllFieldErrors();

    const login = document.getElementById('login-input');
    const password = document.getElementById('password-input');
    const loginText = login.value.trim();
    const passwordText = password.value.trim();

    let hasError = false;

    if (!loginText) {
        showFieldError(login, 'Заполните поле Логин');
        hasError = true;
    }

    if (!passwordText) {
        showFieldError(password, 'Заполните поле Пароль');
        hasError = true;
    }

    if (hasError) {
        return;
    }
});