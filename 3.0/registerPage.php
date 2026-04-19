<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrodnoCraft - Регистрация</title>
    <link rel="stylesheet" href="./styles/registerStyle.css">
    <link rel="icon" href="./styles/image/icon.png">
</head>
<body>
    <div class="container">        
        <div class="form-section">
            <form method="POST" action="./php/register.php">
                <h1 class="logo">Grodno<span>Craft</span></h1>
                
                <div class="user-type-toggle">
                    <input type="radio" id="user-type-user" name="user_type" value="user" checked>
                    <label for="user-type-user" class="user-type-option active">Покупатель</label>
                    
                    <input type="radio" id="user-type-seller" name="user_type" value="seller">
                    <label for="user-type-seller" class="user-type-option">Продавец</label>
                </div>
                
                <div class="form-group">
                    <label for="login-input">Логин</label>
                    <input type="text" id="login-input" name="login" placeholder="Придумайте логин" required>
                </div>
                
                <div class="form-group">
                    <label for="name-input">Имя</label>
                    <input type="text" id="name-input" name="name" placeholder="Ваше имя" required>
                </div>
                
                <div class="form-group">
                    <label for="email-input">E-mail</label>
                    <input type="email" id="email-input" name="email" placeholder="example@mail.com" required>
                </div>
                
                <div class="form-group">
                    <label for="password-input">Пароль</label>
                    <input type="password" id="password-input" name="password" placeholder="Придумайте пароль" required>
                    <span class="password-toggle" id="toggle-password">Показать</span>
                </div>
                
                <div class="form-group">
                    <label for="confirm-password-input">Повтор пароля</label>
                    <input type="password" id="confirm-password-input" name="confirm-password" placeholder="Повторите пароль" required>
                    <span class="password-toggle" id="toggle-confirm-password">Показать</span>
                </div>

                <button type="submit" id="register-button">Зарегистрироваться</button>

                <div class="links-group">
                    <a href="./loginPage.php" class="link">Уже есть аккаунт? Войти</a>
                </div>
            </form>
        </div>
    </div>

    <script src="./js/register/validateRegister.js"></script>
    <script src="./js/commonValidate.js"></script>
</body>
</html>