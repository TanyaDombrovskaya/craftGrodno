<?php
session_start();

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    if ($_SESSION['user_role'] == 'user') {
        header("Location: /craftGrodno/2.0/mainUser.php");
    } else if ($_SESSION['user_role'] == 'seller') {
        header("Location: /craftGrodno/2.0/mainSeller.php");
    } else {
        header("Location: /craftGrodno/2.0/loginPage.php");
    }
} else {
    header("Location: /craftGrodno/2.0/loginPage.php");
}
exit();