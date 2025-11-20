<?php
session_start();

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    if ($_SESSION['user_role'] == 'user') {
        header("Location: /craftGrodno/2.0/mainUser.php");
    } else if ($_SESSION['user_role'] == 'seller') {
        header("Location: /craftGrodno/2.0/mainSeller.php");
    }
    exit();
}

$login_error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
$previous_login = isset($_SESSION['previous_login']) ? $_SESSION['previous_login'] : '';

$body_class = '';
if ($login_error === 'password') {
    $body_class = 'password-error';
} elseif ($login_error === 'login') {
    $body_class = 'login-error';
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrodnoCraft - Вход</title>
    <link rel="stylesheet" href="./styles/loginStyle.css">
    <link rel="icon" href="./styles/image/icon.png">
</head>
<body class="<?php echo $body_class; ?>">
    <div class="container">
        <div class="image-section"></div>
        
        <div class="form-section">
            <form method="POST" action="./php/login.php">
                <h1 class="logo">Grodno<span>Craft</span></h1>

                <div class="form-group">
                    <label for="login-input">Логин</label>
                    <input type="text" id="login-input" name="login" placeholder="Логин" 
                           value="<?php echo isset($_SESSION['previous_login']) ? htmlspecialchars($_SESSION['previous_login']) : ''; ?>">
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
    <script src="./js/login/authError.js"></script>
    <script src="./js/login/validateLogin.js"></script>
</body>
</html>
<?php
unset($_SESSION['login_error']);
unset($_SESSION['previous_login']);
?>