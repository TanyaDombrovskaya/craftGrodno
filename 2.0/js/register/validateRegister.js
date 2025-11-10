document.addEventListener('DOMContentLoaded', function() {
    // Элементы для переключения видимости пароля
    const togglePassword = document.getElementById('toggle-password');
    const toggleConfirmPassword = document.getElementById('toggle-confirm-password');
    const passwordInput = document.getElementById('password-input');
    const confirmPasswordInput = document.getElementById('confirm-password-input');

    // Функция для переключения видимости пароля
    function togglePasswordVisibility(input, toggleElement) {
        if (input.type === 'password') {
            input.type = 'text';
            toggleElement.textContent = 'Скрыть';
        } else {
            input.type = 'password';
            toggleElement.textContent = 'Показать';
        }
    }

    // Обработчики для переключения видимости основного пароля
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            togglePasswordVisibility(passwordInput, togglePassword);
        });
    }

    // Обработчики для переключения видимости подтверждения пароля
    if (toggleConfirmPassword && confirmPasswordInput) {
        toggleConfirmPassword.addEventListener('click', function() {
            togglePasswordVisibility(confirmPasswordInput, toggleConfirmPassword);
        });
    }
    
    // Инициализация переключателя типа пользователя
    initUserTypeToggle();
    
    const registerButton = document.getElementById('register-button');
    if (registerButton) {
        registerButton.addEventListener('click', function(event) {    
            event.preventDefault();
            
            clearAllFieldErrors();
            
            const login = document.getElementById('login-input');
            const name = document.getElementById('name-input');
            const email = document.getElementById('email-input');
            const password = document.getElementById('password-input');
            const confirmPassword = document.getElementById('confirm-password-input');
            const userType = getSelectedUserType();

            console.log('Выбранный тип пользователя:', userType);

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

            // Валидация пароля
            if (!passwordText) {
                showFieldError(password, 'Заполните поле Пароль');
                hasError = true;
            } else if (!validatePassword(passwordText)) {
                showFieldError(password, 'Пароль должен быть от 8 до 25 символов');
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

            // Дополнительная проверка для продавцов
            if (userType === 'seller') {
                console.log('Проверка для продавца...');
            }

            if (hasError) {
                console.log('Есть ошибки валидации');
                return;
            }

            // Если все проверки пройдены, отправляем форму
            console.log('Регистрация с типом пользователя:', userType);
            submitRegistrationForm();
        });
    } else {
        console.error('Кнопка register-button не найдена!');
    }
});

// Функция для инициализации переключателя типа пользователя
function initUserTypeToggle() {
    const userTypeOptions = document.querySelectorAll('.user-type-option');
    const radioInputs = document.querySelectorAll('input[name="user_type"]');
    
    console.log('Найдено опций пользователя:', userTypeOptions.length);
    console.log('Найдено radio кнопок:', radioInputs.length);
    
    if (userTypeOptions.length === 0 || radioInputs.length === 0) {
        console.error('Элементы переключателя пользователя не найдены!');
        return;
    }
    
    // Обработчики для клика по labels
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
    
    // Обработчики для изменения radio кнопок
    radioInputs.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                updateUserTypeActiveState();
            }
        });
    });
    
    // Инициализируем начальное состояние
    updateUserTypeActiveState();
}

// Функция для обновления активного состояния переключателя
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

// Функция для получения выбранного типа пользователя
function getSelectedUserType() {
    const checkedRadio = document.querySelector('input[name="user_type"]:checked');
    return checkedRadio ? checkedRadio.value : 'user';
}

// Функция для отправки формы
function submitRegistrationForm() {
    const form = document.querySelector('form');
    if (form) {
        console.log('Отправка формы регистрации...');
        form.submit();
    } else {
        console.error('Форма не найдена!');
    }
}

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
    
    // Проверяем, что имя содержит только буквы, дефисы и максимум один пробел
    const allowedChars = /^[a-zA-Zа-яА-Я- ]+$/;
    if (!allowedChars.test(name)) return false;
    
    // Проверяем, что пробелов не больше одного
    const spaceCount = (name.match(/ /g) || []).length;
    if (spaceCount > 1) return false;
    
    // Проверяем, что пробел не в начале и не в конце
    if (name.startsWith(' ') || name.endsWith(' ')) return false;
    
    // Проверяем, что если есть пробел, то с обеих сторон есть другие символы
    if (name.includes(' ')) {
        const parts = name.split(' ');
        if (parts[0].length === 0 || parts[1].length === 0) {
            return false; // Пробел не может быть без символов с обеих сторон
        }
    }
    
    // Проверяем, что нет двух дефисов подряд или дефисов в начале/конце
    if (name.startsWith('-') || name.endsWith('-')) return false;
    if (name.includes('--')) return false;
    
    // Проверяем, что имя содержит хотя бы один буквенный символ
    const hasLetters = /[a-zA-Zа-яА-Я]/.test(name);
    if (!hasLetters) return false;
    
    return true;
}

// Улучшенная валидация почты
function validateEmail(email) {
    if (!email) return false;
    if (email.includes(' ')) return false;
    const englishChars = /^[a-zA-Z0-9.@_+-]+$/;
    if (!englishChars.test(email)) return false;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Валидация пароля
function validatePassword(password) {
    if (!password) return false;
    return password.length >= 8 && password.length <= 25;
}