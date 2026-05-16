<?php
session_start();

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    if ($_SESSION['user_role'] == 'user') {
        header("Location: /craftGrodno/3.0/mainUser.php");
        exit();
    } else if ($_SESSION['user_role'] == 'seller') {
        header("Location: /craftGrodno/3.0/mainSeller.php");
        exit();
    }
}

$login_error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
$previous_login = isset($_SESSION['previous_login']) ? $_SESSION['previous_login'] : '';

unset($_SESSION['login_error']);
unset($_SESSION['previous_login']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ГродноАрт - Вход</title>
    <link rel="stylesheet" href="./styles/loginStyle.css">
    <link rel="icon" href="./styles/image/icon.png">
</head>
<body>
    <div class="container">
        <div class="image-section"></div>
        
        <div class="form-section">
            <form method="POST" action="./php/login.php">
                <h1 class="logo">Гродно<span>Арт</span></h1>

                <div class="form-group">
                    <label for="login-input">Логин</label>
                    <input type="text" id="login-input" name="login" placeholder="Логин" 
                           value="<?php echo htmlspecialchars($previous_login); ?>">
                </div>

                <div class="form-group">
                    <label for="password-input">Пароль</label>
                    <input type="password" id="password-input" name="password" placeholder="Пароль">
                </div>

                <button id="login-button" type="submit">Войти</button>

                <div class="links-group">
                    <a href="./forgotPage.php" class="link">Забыли пароль?</a>
                    <a href="./registerPage.php" class="link">Регистрация</a>
                </div>
            </form>
        </div>
    </div>

    <script src="./js/commonValidate.js"></script>
    <script src="./js/login/validateLogin.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($login_error === 'password'): ?>
            const passwordInput = document.getElementById('password-input');
            showFieldError(passwordInput, 'Неверный пароль');
            passwordInput.value = '';
        <?php elseif ($login_error === 'login'): ?>
            const loginInput = document.getElementById('login-input');
            showFieldError(loginInput, 'Пользователь не найден');
            loginInput.value = '';
        <?php endif; ?>
    });
    </script>
</body>
</html>