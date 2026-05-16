document.getElementById('replace-password').addEventListener('click', function(e) {
    e.preventDefault();
    clearAllFieldErrors();

    const login = document.getElementById('login-input');
    const email = document.getElementById('email-input');
    const password = document.getElementById('password-input');
    const confirm = document.getElementById('confirm-password-input');

    let hasError = false;

    if (!login.value.trim()) {
        showFieldError(login, 'Заполните поле Логин');
        hasError = true;
    }
    if (!email.value.trim()) {
        showFieldError(email, 'Заполните поле Email');
        hasError = true;
    }
    if (!password.value.trim()) {
        showFieldError(password, 'Заполните поле Пароль');
        hasError = true;
    } else if (password.value.length < 8) {
        showFieldError(password, 'Пароль должен быть не менее 8 символов');
        hasError = true;
    }
    if (password.value !== confirm.value) {
        showFieldError(confirm, 'Пароли не совпадают');
        hasError = true;
    }

    if (!hasError) {
        document.querySelector('form').submit();
    }
});