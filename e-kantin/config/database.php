<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'db_kantin';

// Gunakan MySQLi OOP (agar bisa pakai ->prepare)
$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>