<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole(['user']);

$title = 'Riwayat Pesanan';

$id_user = $_SESSION['id_user'];
$transaksi = mysqli_query($conn,
    "SELECT * FROM transaksi WHERE id_user = $id_user ORDER BY tanggal DESC"
);

include '../includes/header.php';
?>

<div class="sidebar">
    <a href="dashboard.php" class="btn btn-info">🍽️ Lihat Menu</a>
    <a href="keranjang.php" class="btn btn-info">🛒 Keranjang</a>
    <a href="riwayat.php" class="btn btn-primary">📋 Riwayat Pesanan</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-<?= htmlspecialchars($_GET['type'] ?? 'success') ?>">
        <?= htmlspecialchars($_GET['msg']) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>📋 Riwayat Pesanan Saya</h2>
    </div>

    <?php if (mysqli_num_rows($transaksi) == 0): ?>
        <p style="text-align: center; padding: 2rem;">Belum ada pesanan. <a href="dashboard.php">Pesan sekarang</a></p>
    <?php else: ?>
        <?php while ($trx = mysqli_fetch_assoc($transaksi)): ?>
        <div class="card" style="margin-bottom: 1rem; padding: 1.25rem; border: 1px solid #f0f0f0;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem;">
                <div>
                    <strong>Pesanan #<?= $trx['id_transaksi'] ?></strong><br>
                    <small style="color: #888;"><?= date('d/m/Y H:i', strtotime($trx['tanggal'])) ?></small>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                    <!-- Badge metode -->
                    <?php
                    $metode = strtolower(trim($trx['metode_pembayaran'] ?? ''));
                    if (!in_array($metode, ['cash', 'qris'])) $metode = 'cash';
                    $metode_icon = $metode === 'qris' ? '📱 QRIS' : '💵 Cash';
                    $metode_color = $metode === 'qris' ? '#667eea' : '#28a745';
                    ?>
                    <span style="background: <?= $metode_color ?>20; color: <?= $metode_color ?>; border: 1px solid <?= $metode_color ?>40; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                        <?= $metode_icon ?>
                    </span>
                    <span class="badge badge-<?= $trx['status'] ?>"><?= ucfirst($trx['status']) ?></span>
                </div>
                <div>
                    <strong style="color: #667eea;">Rp <?= number_format($trx['total_bayar'], 0, ',', '.') ?></strong>
                </div>
            </div>

            <!-- Detail items -->
            <?php
            $details = mysqli_query($conn,
                "SELECT dt.*, m.nama_makanan
                 FROM detail_transaksi dt
                 JOIN menu m ON dt.id_menu = m.id_menu
                 WHERE dt.id_transaksi = {$trx['id_transaksi']}"
            );
            ?>
            <table style="margin-top: 0.75rem; font-size: 0.88rem;">
                <thead>
                    <tr><th>Menu</th><th>Qty</th><th>Subtotal</th></tr>
                </thead>
                <tbody>
                    <?php while ($d = mysqli_fetch_assoc($details)): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['nama_makanan']) ?></td>
                        <td><?= $d['qty'] ?></td>
                        <td>Rp <?= number_format($d['subtotal'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Aksi jika QRIS pending -->
            <?php if ($metode === 'qris' && $trx['status'] === 'pending'): ?>
            <div style="margin-top: 0.75rem;">
                <a href="bayar_qris.php?id=<?= $trx['id_transaksi'] ?>" class="btn btn-sm btn-primary">
                    📱 Lanjutkan Pembayaran QRIS
                </a>
            </div>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
