<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // ============================================
    // EQUIVALENCE PARTITIONING & BOUNDARY VALUE ANALYSIS
    // ============================================
    
    $errors = [];
    
    // Username validation - EP & BVA (3-20 karakter)
    $username_length = strlen($username);
    if (empty($username)) {
        $errors[] = 'Username wajib diisi';
    } elseif ($username_length < 3) {
        $errors[] = 'Username minimal 3 karakter (BVA: Min-1 invalid)';
    } elseif ($username_length > 20) {
        $errors[] = 'Username maksimal 20 karakter (BVA: Max+1 invalid)';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username hanya boleh huruf, angka, dan underscore';
    }
    
    // Password validation - EP & BVA (6-20 karakter)
    $password_length = strlen($password);
    if (empty($password)) {
        $errors[] = 'Password wajib diisi';
    } elseif ($password_length < 6) {
        $errors[] = 'Password minimal 6 karakter (BVA: Min-1 invalid)';
    } elseif ($password_length > 20) {
        $errors[] = 'Password maksimal 20 karakter (BVA: Max+1 invalid)';
    }
    
    // Confirm password
    if ($password !== $confirm_password) {
        $errors[] = 'Konfirmasi password tidak cocok';
    }
    
    // Check username uniqueness
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id_user FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Username sudah digunakan';
        }
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user'; // Default role for registration
        
        $stmt = $conn->prepare("INSERT INTO user (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashed_password, $role);
        
        if ($stmt->execute()) {
            $success = 'Registrasi berhasil! Silakan login.';
        } else {
            $errors[] = 'Gagal mendaftar, coba lagi';
        }
    }
    
    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - E-Kantin</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">🛒</div>
            <h2>Registrasi E-Kantin</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username (3-20 karakter)*</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?= htmlspecialchars($username ?? '') ?>" 
                           placeholder="Masukkan username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password (6-20 karakter)*</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Masukkan password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password*</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                           placeholder="Konfirmasi password" required>
                </div>
                
                <button type="submit" class="btn-login">
                  Daftar
                </button>
            </form>
            
            <p style="text-align: center; margin-top: 1.5rem; color: #888;">
                Sudah punya akun? <a href="login.php">Login disini</a>
            </p>
        </div>
    </div>
</body>
</html>