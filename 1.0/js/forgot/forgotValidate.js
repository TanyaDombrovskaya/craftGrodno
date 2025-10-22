document.getElementById('replace-password').addEventListener('click', function(event) {
    event.preventDefault();
    
    clearAllFieldErrors();

    const login = document.getElementById('login-input');
    const email = document.getElementById('email-input');
    const password = document.getElementById('password-input');
    const confirmPassword = document.getElementById('confirm-password-input');

    const loginText = login.value.trim();
    const emailText = email.value.trim();
    const passwordText = password.value.trim();
    const confirmPasswordText = confirmPassword.value.trim();

    let hasError = false;

    if (!loginText) {
        showFieldError(login, 'Заполните поле Логин');
        hasError = true;
    }

    if (!emailText) {
        showFieldError(email, 'Заполните поле Email');
        hasError = true;
    } else if (!validateEmail(emailText)) {
        showFieldError(email, 'Введите корректный e-mail (только английские символы)');
        hasError = true;
    }

    if (!passwordText) {
        showFieldError(password, 'Заполните поле Пароль');
        hasError = true;
    } else if (!validatePassword(passwordText)) {
        showFieldError(password, 'Пароль должен быть от 8 до 25 символов');
        hasError = true;
    }

    if (!confirmPasswordText) {
        showFieldError(confirmPassword, 'Заполните поле Повтор пароля');
        hasError = true;
    } else if (passwordText !== confirmPasswordText) {
        showFieldError(confirmPassword, 'Пароли не совпадают');
        hasError = true;
    }

    if (!hasError) {
        document.querySelector('form').submit();
    }
});

function validatePassword(password) {
    return password && password.length >= 8 && password.length <= 25;
}

function validateEmail(email) {
    if (!email) return false;
    if (email.includes(' ')) return false;
    const englishChars = /^[a-zA-Z0-9.@_+-]+$/;
    if (!englishChars.test(email)) return false;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}