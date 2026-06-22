<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole(['admin']);

$title = 'Kelola User';

$users = mysqli_query($conn, "SELECT id_user, username, role, created_at FROM user ORDER BY id_user DESC");

include '../includes/header.php';
?>

<div class="sidebar">
    <a href="dashboard.php" class="btn btn-info">📊 Dashboard</a>
    <a href="kelola_menu.php" class="btn btn-info">🍽️ Kelola Menu</a>
    <a href="kelola_user.php" class="btn btn-primary">👥 Kelola User</a>
    <a href="laporan.php" class="btn btn-info">📋 Laporan</a>
</div>

<div class="card">
    <div class="card-header">
        <h2>Daftar User</h2>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Tanggal Daftar</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = mysqli_fetch_assoc($users)): ?>
            <tr>
                <td>#<?= $user['id_user'] ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><span class="badge badge-<?= $user['role'] == 'admin' ? 'diproses' : ($user['role'] == 'kasir' ? 'pending' : 'selesai') ?>"><?= ucfirst($user['role']) ?></span></td>
                <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>