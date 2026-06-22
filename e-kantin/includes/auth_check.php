<?php
if (!isset($_SESSION['id_user'])) {
    header('Location: ../login.php');
    exit;
}

function checkRole($allowedRoles) {
    if (!in_array($_SESSION['role'], $allowedRoles)) {
        header('Location: ../login.php?error=unauthorized');
        exit;
    }
}
?>