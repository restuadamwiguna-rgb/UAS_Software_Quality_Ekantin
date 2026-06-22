<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole(['admin']);

$id_menu = intval($_GET['id'] ?? 0);
mysqli_query($conn, "DELETE FROM menu WHERE id_menu = $id_menu");
header('Location: kelola_menu.php?msg=Menu berhasil dihapus&type=success');
exit;
?>