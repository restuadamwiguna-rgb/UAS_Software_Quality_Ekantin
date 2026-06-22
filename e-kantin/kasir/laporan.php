<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole(['kasir']);

$title = 'Laporan';

$tanggal_dari = $_GET['dari'] ?? date('Y-m-01');
$tanggal_sampai = $_GET['sampai'] ?? date('Y-m-d');

$transaksi = mysqli_query($conn,
    "SELECT t.*, u.username
     FROM transaksi t
     JOIN user u ON t.id_user = u.id_user
     WHERE DATE(t.tanggal) BETWEEN '$tanggal_dari' AND '$tanggal_sampai'
     AND t.status = 'selesai'
     ORDER BY t.tanggal DESC"
);

$total_pendapatan = mysqli_query($conn,
    "SELECT SUM(total_bayar) as total FROM transaksi
     WHERE DATE(tanggal) BETWEEN '$tanggal_dari' AND '$tanggal_sampai'
     AND status = 'selesai'"
)->fetch_assoc()['total'] ?? 0;

$total_cash = mysqli_query($conn,
    "SELECT SUM(total_bayar) as total FROM transaksi
     WHERE DATE(tanggal) BETWEEN '$tanggal_dari' AND '$tanggal_sampai'
     AND status = 'selesai' AND metode_pembayaran = 'cash'"
)->fetch_assoc()['total'] ?? 0;

$total_qris = mysqli_query($conn,
    "SELECT SUM(total_bayar) as total FROM transaksi
     WHERE DATE(tanggal) BETWEEN '$tanggal_dari' AND '$tanggal_sampai'
     AND status = 'selesai' AND metode_pembayaran = 'qris'"
)->fetch_assoc()['total'] ?? 0;

include '../includes/header.php';
?>

<div class="sidebar">
    <a href="dashboard.php" class="btn btn-info">📊 Dashboard</a>
    <a href="proses_pesanan.php" class="btn btn-info">🛒 Proses Pesanan</a>
    <a href="laporan.php" class="btn btn-primary">📋 Laporan</a>
</div>

<!-- Filter -->
<div class="card">
    <div class="card-header"><h2>📋 Laporan Transaksi</h2></div>
    <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div>
            <label style="display:block; font-size:0.85rem; color:#555; margin-bottom:0.3rem;">Dari Tanggal</label>
            <input type="date" name="dari" value="<?= $tanggal_dari ?>" class="form-control" style="width:auto;">
        </div>
        <div>
            <label style="display:block; font-size:0.85rem; color:#555; margin-bottom:0.3rem;">Sampai Tanggal</label>
            <input type="date" name="sampai" value="<?= $tanggal_sampai ?>" class="form-control" style="width:auto;">
        </div>
        <button type="submit" class="btn btn-primary">🔍 Filter</button>
    </form>
</div>

<!-- Ringkasan -->
<div class="grid">
    <div class="card stat-card">
        <div class="stat-number" style="color:#28a745;">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></div>
        <div class="stat-label">Total Pendapatan</div>
    </div>
    <div class="card stat-card">
        <div class="stat-number" style="color:#28a745; font-size:1.5rem;">Rp <?= number_format($total_cash, 0, ',', '.') ?></div>
        <div class="stat-label">💵 Cash</div>
    </div>
    <div class="card stat-card">
        <div class="stat-number" style="color:#667eea; font-size:1.5rem;">Rp <?= number_format($total_qris, 0, ',', '.') ?></div>
        <div class="stat-label">📱 QRIS</div>
    </div>
</div>

<!-- Tabel -->
<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Pelanggan</th>
                <th>Tanggal</th>
                <th>Metode</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $count = 0;
            while ($trx = mysqli_fetch_assoc($transaksi)):
                $count++;
                $m = strtolower(trim($trx['metode_pembayaran'] ?? ''));
                if (!in_array($m, ['cash', 'qris'])) $m = 'cash';
                $m_icon = $m === 'qris' ? '📱' : '💵';
                $m_color = $m === 'qris' ? '#667eea' : '#28a745';
            ?>
            <tr>
                <td>#<?= $trx['id_transaksi'] ?></td>
                <td><?= htmlspecialchars($trx['username']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($trx['tanggal'])) ?></td>
                <td><span style="color:<?= $m_color ?>; font-weight:600;"><?= $m_icon ?> <?= strtoupper($m) ?></span></td>
                <td>Rp <?= number_format($trx['total_bayar'], 0, ',', '.') ?></td>
            </tr>
            <?php endwhile; ?>
            <?php if ($count === 0): ?>
            <tr><td colspan="5" style="text-align:center; color:#888; padding:2rem;">Tidak ada transaksi selesai pada periode ini</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
