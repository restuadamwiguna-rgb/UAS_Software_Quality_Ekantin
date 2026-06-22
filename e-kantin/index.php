<?php
session_start();
if (isset($_SESSION['id_user'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        case 'kasir':
            header('Location: kasir/dashboard.php');
            break;
        case 'user':
            header('Location: user/dashboard.php');
            break;
        default:
            header('Location: login.php');
    }
    exit;
} else {
    header('Location: login.php');
    exit;
}
?>