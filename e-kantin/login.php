<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config/database.php';

$error = '';
$username_error = '';
$password_error = '';

if (isset($_SESSION['id_user'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // ============================================
    // EQUIVALENCE PARTITIONING & BOUNDARY VALUE ANALYSIS
    // ============================================
    
    // Username validation - EP & BVA
    // Valid class: 3-20 karakter
    // Invalid class: < 3 karakter, > 20 karakter
    $username_length = strlen($username);
    
    if (empty($username)) {
        $username_error = 'Username wajib diisi';
    } elseif ($username_length < 3) {
        // BVA: Min-1 (2 karakter) -> Invalid
        $username_error = 'Username minimal 3 karakter (Boundary Value: Min-1 invalid)';
    } elseif ($username_length > 20) {
        // BVA: Max+1 (21 karakter) -> Invalid
        $username_error = 'Username maksimal 20 karakter (Boundary Value: Max+1 invalid)';
    }
    
    // Password validation - EP & BVA  
    // Valid class: 6-20 karakter
    // Invalid class: < 6 karakter, > 20 karakter
    $password_length = strlen($password);
    
    if (empty($password)) {
        $password_error = 'Password wajib diisi';
    } elseif ($password_length < 6) {
        // BVA: Min-1 (5 karakter) -> Invalid
        $password_error = 'Password minimal 6 karakter (Boundary Value: Min-1 invalid)';
    } elseif ($password_length > 20) {
        // BVA: Max+1 (21 karakter) -> Invalid
        $password_error = 'Password maksimal 20 karakter (Boundary Value: Max+1 invalid)';
    }
    
    // Jika validasi sukses, lanjutkan autentikasi
    if (empty($username_error) && empty($password_error)) {
        // Check credentials
        $stmt = $conn->prepare("SELECT id_user, username, password, role FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Login sukses
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect sesuai role (use case testing scenario)
                switch ($user['role']) {
                    case 'admin':
                        header('Location: admin/dashboard.php');
                        break;
                    case 'kasir':
                        header('Location: kasir/dashboard.php');
                        break;
                    case 'user':
                        header('Location: user/dashboard.php');
                        break;
                }
                exit;
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'Username tidak ditemukan!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Kantin</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">🛒</div>
            <h2>Login E-Kantin</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username (3-20 karakter)</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?= htmlspecialchars($username ?? '') ?>" 
                           placeholder="Masukkan username">
                    <?php if ($username_error): ?>
                        <small style="color: #dc3545;"><?= $username_error ?></small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="password">Password (6-20 karakter)</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Masukkan password">
                    <?php if ($password_error): ?>
                        <small style="color: #dc3545;"><?= $password_error ?></small>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn-login">
    Masuk
</button>
            </form>
            
            <p style="text-align: center; margin-top: 1.5rem; color: #888;">
                Belum punya akun? <a href="register.php">Daftar disini</a>
            </p>
            
        </div>
    </div>
</body>
</html>