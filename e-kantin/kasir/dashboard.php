<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole(['kasir']);

$title = 'Kasir Dashboard';

// Hitung statistik
$pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE status='pending'")->fetch_assoc()['total'];
$diproses = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE status='diproses'")->fetch_assoc()['total'];
$selesai = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE status='selesai'")->fetch_assoc()['total'];

$today_transactions = mysqli_query($conn, 
    "SELECT SUM(total_bayar) as total FROM transaksi 
     WHERE DATE(tanggal) = CURDATE() AND status='selesai'"
)->fetch_assoc()['total'] ?? 0;

// Pesanan pending
$orders = mysqli_query($conn, 
    "SELECT t.*, u.username 
     FROM transaksi t 
     JOIN user u ON t.id_user = u.id_user 
     WHERE t.status IN ('pending', 'diproses') 
     ORDER BY t.tanggal ASC"
);

include '../includes/header.php';
?>

<div class="sidebar">
    <a href="dashboard.php" class="btn btn-primary">📊 Dashboard</a>
    <a href="proses_pesanan.php" class="btn btn-info">🛒 Proses Pesanan</a>
    <a href="laporan.php" class="btn btn-info">📋 Laporan</a>
</div>

<div class="grid">
    <div class="card stat-card">
        <div class="stat-number" style="color: #ffc107;"><?= $pending ?></div>
        <div class="stat-label">Pending</div>
    </div>
    <div class="card stat-card">
        <div class="stat-number" style="color: #17a2b8;"><?= $diproses ?></div>
        <div class="stat-label">Diproses</div>
    </div>
    <div class="card stat-card">
        <div class="stat-number" style="color: #28a745;"><?= $selesai ?></div>
        <div class="stat-label">Selesai</div>
    </div>
    <div class="card stat-card">
        <div class="stat-number">Rp <?= number_format($today_transactions, 0, ',', '.') ?></div>
        <div class="stat-label">Pendapatan Hari Ini</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Pesanan Aktif</h2>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Pelanggan</th>
                <th>Tanggal</th>
                <th>Total</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = mysqli_fetch_assoc($orders)): ?>
            <tr>
                <td>#<?= $order['id_transaksi'] ?></td>
                <td><?= htmlspecialchars($order['username']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($order['tanggal'])) ?></td>
                <td>Rp <?= number_format($order['total_bayar'], 0, ',', '.') ?></td>
                <td><span class="badge badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                <td>
                    <a href="proses_pesanan.php?id=<?= $order['id_transaksi'] ?>" class="btn btn-sm btn-info">Proses</a>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php if (mysqli_num_rows($orders) == 0): ?>
            <tr><td colspan="6" style="text-align:center;">Tidak ada pesanan aktif</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>