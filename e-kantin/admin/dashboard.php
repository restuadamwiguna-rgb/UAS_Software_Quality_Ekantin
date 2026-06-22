<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole(['admin']);

$title = 'Admin Dashboard';

// Get statistics
$total_menu = mysqli_query($conn, "SELECT COUNT(*) as total FROM menu")->fetch_assoc()['total'];
$total_user = mysqli_query($conn, "SELECT COUNT(*) as total FROM user")->fetch_assoc()['total'];
$total_transaksi = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi")->fetch_assoc()['total'];
$total_pendapatan = mysqli_query($conn, "SELECT SUM(total_bayar) as total FROM transaksi WHERE status='selesai'")->fetch_assoc()['total'] ?? 0;

$recent_transactions = mysqli_query($conn, 
    "SELECT t.id_transaksi, u.username, t.tanggal, t.total_bayar, t.status 
     FROM transaksi t JOIN user u ON t.id_user = u.id_user 
     ORDER BY t.tanggal DESC LIMIT 5"
);

include '../includes/header.php';
?>

<div class="sidebar">
    <a href="dashboard.php" class="btn btn-primary">📊 Dashboard</a>
    <a href="kelola_menu.php" class="btn btn-info">🍽️ Kelola Menu</a>
    <a href="kelola_user.php" class="btn btn-info">👥 Kelola User</a>
    <a href="laporan.php" class="btn btn-info">📋 Laporan</a>
</div>

<div class="grid">
    <div class="card stat-card">
        <div class="stat-number"><?= $total_menu ?></div>
        <div class="stat-label">Total Menu</div>
    </div>
    <div class="card stat-card">
        <div class="stat-number"><?= $total_user ?></div>
        <div class="stat-label">Total User</div>
    </div>
    <div class="card stat-card">
        <div class="stat-number"><?= $total_transaksi ?></div>
        <div class="stat-label">Total Transaksi</div>
    </div>
    <div class="card stat-card">
        <div class="stat-number">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></div>
        <div class="stat-label">Total Pendapatan</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Transaksi Terbaru</h2>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Tanggal</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($recent_transactions)): ?>
            <tr>
                <td>#<?= $row['id_transaksi'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?></td>
                <td>Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?></td>
                <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
            </tr>
            <?php endwhile; ?>
            <?php if (mysqli_num_rows($recent_transactions) == 0): ?>
            <tr><td colspan="5" style="text-align:center;">Belum ada transaksi</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>