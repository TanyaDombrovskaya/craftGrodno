document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('toggle-password');
    const toggleConfirmPassword = document.getElementById('toggle-confirm-password');
    const passwordInput = document.getElementById('password-input');
    const confirmPasswordInput = document.getElementById('confirm-password-input');

    function togglePasswordVisibility(input, toggleElement) {
        if (input.type === 'password') {
            input.type = 'text';
            toggleElement.textContent = 'Скрыть';
        } else {
            input.type = 'password';
            toggleElement.textContent = 'Показать';
        }
    }

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            togglePasswordVisibility(passwordInput, togglePassword);
        });
    }

    if (toggleConfirmPassword && confirmPasswordInput) {
        toggleConfirmPassword.addEventListener('click', function() {
            togglePasswordVisibility(confirmPasswordInput, toggleConfirmPassword);
        });
    }
    
    initUserTypeToggle();
    
    const registerButton = document.getElementById('register-button');
    if (registerButton) {
        registerButton.addEventListener('click', function(event) {    
            event.preventDefault();  // ОБЯЗАТЕЛЬНО!
            
            clearAllFieldErrors();
            
            const login = document.getElementById('login-input');
            const name = document.getElementById('name-input');
            const email = document.getElementById('email-input');
            const password = document.getElementById('password-input');
            const confirmPassword = document.getElementById('confirm-password-input');
            const userType = getSelectedUserType();

            const loginText = login.value.trim();
            const nameText = name.value.trim();
            const emailText = email.value.trim();
            const passwordText = password.value.trim();
            const confirmPasswordText = confirmPassword.value.trim();

            let hasError = false;

            // Валидация логина
            if (!loginText) {
                showFieldError(login, 'Заполните поле Логин');
                hasError = true;
            } else if (!validateLogin(loginText)) {
                showFieldError(login, 'Логин должен быть от 5 символов, без пробелов и специальных символов');
                hasError = true;
            }

            // Валидация имени
            if (!nameText) {
                showFieldError(name, 'Заполните поле Имя');
                hasError = true;
            } else if (!validateName(nameText)) {
                showFieldError(name, 'Имя должно содержать только буквы и дефисы, допускается один пробел между словами');
                hasError = true;
            }

            // Валидация email
            if (!emailText) {
                showFieldError(email, 'Заполните поле Email');
                hasError = true;
            } else if (!validateEmail(emailText)) {
                showFieldError(email, 'Введите корректный e-mail (только английские символы)');
                hasError = true;
            }

            // Валидация пароля (bcrypt требования)
            if (!passwordText) {
                showFieldError(password, 'Заполните поле Пароль');
                hasError = true;
            } else if (!validatePassword(passwordText)) {
                showFieldError(password, 'Пароль должен быть от 8 до 25 символов');
                hasError = true;
            } else if (!validatePasswordStrength(passwordText)) {
                showFieldError(password, 'Пароль должен содержать заглавную букву и цифру');
                hasError = true;
            }

            // Валидация подтверждения пароля
            if (!confirmPasswordText) {
                showFieldError(confirmPassword, 'Заполните поле Повтор пароля');
                hasError = true;
            } else if (passwordText !== confirmPasswordText) {
                showFieldError(confirmPassword, 'Пароли не совпадают');
                hasError = true;
            }

            if (hasError) {
                return;
            }

            document.querySelector('form').submit();
        });
    }
});

// Валидация логина
function validateLogin(login) {
    if (!login) return false;
    if (login.length < 5) return false;
    if (login.includes(' ')) return false;
    const allowedChars = /^[a-zA-Zа-яА-Я0-9_-]+$/;
    return allowedChars.test(login);
}

// Валидация имени
function validateName(name) {
    if (!name) return false;
    const allowedChars = /^[a-zA-Zа-яА-Я- ]+$/;
    if (!allowedChars.test(name)) return false;
    const spaceCount = (name.match(/ /g) || []).length;
    if (spaceCount > 1) return false;
    if (name.startsWith(' ') || name.endsWith(' ')) return false;
    if (name.includes('--')) return false;
    const hasLetters = /[a-zA-Zа-яА-Я]/.test(name);
    return hasLetters;
}

// Валидация email
function validateEmail(email) {
    if (!email) return false;
    if (email.includes(' ')) return false;
    const englishChars = /^[a-zA-Z0-9.@_+-]+$/;
    if (!englishChars.test(email)) return false;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Валидация пароля (длина)
function validatePassword(password) {
    if (!password) return false;
    return password.length >= 8 && password.length <= 25;
}

// Валидация сложности пароля (заглавная буква + цифра)
function validatePasswordStrength(password) {
    return /[A-Z]/.test(password) && /[0-9]/.test(password);
}

// Остальные функции (initUserTypeToggle, getSelectedUserType, updateUserTypeActiveState) остаются без изменений
function initUserTypeToggle() {
    const userTypeOptions = document.querySelectorAll('.user-type-option');
    const radioInputs = document.querySelectorAll('input[name="user_type"]');
    
    if (userTypeOptions.length === 0 || radioInputs.length === 0) {
        return;
    }
    
    userTypeOptions.forEach(option => {
        option.addEventListener('click', function() {
            const radioId = this.getAttribute('for');
            const radio = document.getElementById(radioId);
            if (radio) {
                radio.checked = true;
                updateUserTypeActiveState();
            }
        });
    });
    
    radioInputs.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                updateUserTypeActiveState();
            }
        });
    });
    
    updateUserTypeActiveState();
}

function updateUserTypeActiveState() {
    const userTypeOptions = document.querySelectorAll('.user-type-option');
    const checkedRadio = document.querySelector('input[name="user_type"]:checked');
    
    userTypeOptions.forEach(option => {
        option.classList.remove('active');
    });
    
    if (checkedRadio) {
        const correspondingLabel = document.querySelector(`label[for="${checkedRadio.id}"]`);
        if (correspondingLabel) {
            correspondingLabel.classList.add('active');
        }
    }
}

function getSelectedUserType() {
    const checkedRadio = document.querySelector('input[name="user_type"]:checked');
    return checkedRadio ? checkedRadio.value : 'user';
}