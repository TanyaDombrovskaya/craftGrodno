<?php
session_start();

$reg_errors = isset($_SESSION['reg_errors']) ? $_SESSION['reg_errors'] : [];
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
$selected_type = $form_data['user_type'] ?? 'user';

// Очищаем ошибки после чтения
unset($_SESSION['reg_errors']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ГродноАрт - Регистрация</title>
    <link rel="stylesheet" href="./styles/registerStyle.css">
    <link rel="icon" href="./styles/image/icon.png">
</head>
<body>
    <div class="container">        
        <div class="form-section">
            <form method="POST" action="./php/register.php">
                <h1 class="logo">Гродно<span>Арт</span></h1>
                
                <?php if (!empty($reg_errors)): ?>
                <div class="error-list" style="background: #FEE2E2; color: #DC2626; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                    <?php foreach ($reg_errors as $error): ?>
                        <div>⚠️ <?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="user-type-toggle">
                    <input type="radio" id="user-type-user" name="user_type" value="user" <?php echo $selected_type === 'user' ? 'checked' : ''; ?>>
                    <label for="user-type-user" class="user-type-option <?php echo $selected_type === 'user' ? 'active' : ''; ?>">Покупатель</label>
                    
                    <input type="radio" id="user-type-seller" name="user_type" value="seller" <?php echo $selected_type === 'seller' ? 'checked' : ''; ?>>
                    <label for="user-type-seller" class="user-type-option <?php echo $selected_type === 'seller' ? 'active' : ''; ?>">Продавец</label>
                </div>
                
                <div class="form-group">
                    <label for="login-input">Логин</label>
                    <input type="text" id="login-input" name="login" placeholder="Придумайте логин" 
                           value="<?php echo htmlspecialchars($form_data['login'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="name-input">Имя</label>
                    <input type="text" id="name-input" name="name" placeholder="Ваше имя" 
                           value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email-input">E-mail</label>
                    <input type="email" id="email-input" name="email" placeholder="example@mail.com" 
                           value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password-input">Пароль</label>
                    <input type="password" id="password-input" name="password" placeholder="Придумайте пароль" required>
                    <span class="password-toggle" id="toggle-password">Показать</span>
                    <small style="display: block; margin-top: 5px; color: #666;">Минимум 8 символов, заглавная буква и цифра</small>
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