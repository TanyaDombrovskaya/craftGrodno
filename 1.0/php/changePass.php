<?php
session_start();
require_once("db.php");

unset($_SESSION['forgot_error']);
unset($_SESSION['error_field']);
unset($_SESSION['previous_login']);
unset($_SESSION['previous_email']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST["login"] ?? '';
    $email = $_POST["email"] ?? '';
    $new_password = $_POST["new_password"] ?? '';

    $_SESSION['previous_login'] = $login;
    $_SESSION['previous_email'] = $email;

    $sql = "SELECT * FROM `users` WHERE login = ?";
    $stmt = $connection->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if ($email == $user["email"]) {
                $hashed_password = md5($new_password);
                $update_sql = "UPDATE `users` SET password = ? WHERE login = ?";
                $update_stmt = $connection->prepare($update_sql);
                
                if ($update_stmt) {
                    $update_stmt->bind_param("ss", $hashed_password, $login);
                    if ($update_stmt->execute()) {
                        unset($_SESSION['previous_login']);
                        unset($_SESSION['previous_email']);
                        $_SESSION['success_message'] = "Пароль успешно изменен!";
                        echo "<script>
                            alert('Пароль успешно изменен!');
                            window.location.href = '../loginPage.php';
                        </script>";
                        exit();
                    }
                    $update_stmt->close();
                }
            } else {
                $_SESSION['forgot_error'] = 'Неверная почта';
                $_SESSION['error_field'] = 'email';
                echo "<script>
                    localStorage.setItem('forgotError', 'email');
                    localStorage.setItem('errorMessage', 'Неверная почта');
                    window.location.href = '../forgotPage.php';
                </script>";
                exit();
            }
        } else {
            $_SESSION['forgot_error'] = 'Пользователь не найден';
            $_SESSION['error_field'] = 'login';
            echo "<script>
                localStorage.setItem('forgotError', 'login');
                localStorage.setItem('errorMessage', 'Пользователь не найден');
                window.location.href = '../forgotPage.php';
            </script>";
            exit();
        }
        
        $stmt->close();
    }

    $connection->close();
}