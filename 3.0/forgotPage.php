<?php
session_start();

$forgot_error = isset($_SESSION['forgot_error']) ? $_SESSION['forgot_error'] : '';
$error_field = isset($_SESSION['error_field']) ? $_SESSION['error_field'] : '';
$previous_login = isset($_SESSION['previous_login']) ? $_SESSION['previous_login'] : '';
$previous_email = isset($_SESSION['previous_email']) ? $_SESSION['previous_email'] : '';

unset($_SESSION['forgot_error']);
unset($_SESSION['error_field']);
unset($_SESSION['previous_login']);
unset($_SESSION['previous_email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Восстановление пароля</title>
    <link rel="stylesheet" href="./styles/forgotStyle.css">
    <link rel="icon" href="./styles/image/icon.png">
</head>
<body>
    <div class="container">
        <div class="form-section">
            <form method="POST" action="./php/changePass.php">
                <h1 class="logo">Гродно<span>Арт</span></h1>

                <div class="form-group">
                    <label for="login-input">Логин</label>
                    <input type="text" id="login-input" name="login" placeholder="Логин" 
                           value="<?php echo htmlspecialchars($previous_login); ?>">
                </div>

                <div class="form-group">
                    <label for="email-input">E-mail</label>
                    <input type="text" id="email-input" name="email" placeholder="E-mail" 
                           value="<?php echo htmlspecialchars($previous_email); ?>">
                </div>

                <div class="form-group">
                    <label for="password-input">Новый пароль</label>
                    <input type="password" id="password-input" name="new_password" placeholder="Пароль">
                </div>

                <div class="form-group">
                    <label for="confirm-password-input">Повтор пароля</label>
                    <input type="password" id="confirm-password-input" name="confirm_password" placeholder="Повтор пароля">
                </div>

                <button type="submit" id="replace-password">Изменить пароль</button>

                <div class="links-group">
                    <a href="./loginPage.php" class="link">Вернуться к входу</a>
                </div>
            </form>
        </div>
    </div>

    <script src="./js/forgot/forgotValidate.js"></script>
    <script src="./js/commonValidate.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($error_field === 'login'): ?>
            showFieldError(document.getElementById('login-input'), '<?php echo htmlspecialchars($forgot_error); ?>');
        <?php elseif ($error_field === 'email'): ?>
            showFieldError(document.getElementById('email-input'), '<?php echo htmlspecialchars($forgot_error); ?>');
        <?php elseif ($error_field === 'password'): ?>
            showFieldError(document.getElementById('password-input'), '<?php echo htmlspecialchars($forgot_error); ?>');
        <?php endif; ?>
    });
    </script>
</body>
</html>