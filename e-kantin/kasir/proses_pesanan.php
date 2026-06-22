<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole(['kasir']);

$title = 'Proses Pesanan';

// Handle status update
if (isset($_POST['update_status']) && isset($_POST['id_transaksi']) && isset($_POST['status'])) {
    $id = intval($_POST['id_transaksi']);
    $status = $_POST['status'];
    $allowed = ['pending', 'diproses', 'selesai', 'dibatalkan'];

    if (in_array($status, $allowed)) {
        mysqli_query($conn, "UPDATE transaksi SET status='$status' WHERE id_transaksi=$id");
        $msg = 'Status pesanan #' . $id . ' berhasil diupdate ke: ' . ucfirst($status);
        $msgType = 'success';
    }
}

// Ambil detail pesanan jika ada ID
$detail_order = null;
$detail_items = [];
if (isset($_GET['id'])) {
    $id_transaksi = intval($_GET['id']);
    $detail_order = mysqli_query($conn,
        "SELECT t.*, u.username
         FROM transaksi t
         JOIN user u ON t.id_user = u.id_user
         WHERE t.id_transaksi = $id_transaksi"
    )->fetch_assoc();

    if ($detail_order) {
        $result_items = mysqli_query($conn,
            "SELECT dt.*, m.nama_makanan
             FROM detail_transaksi dt
             JOIN menu m ON dt.id_menu = m.id_menu
             WHERE dt.id_transaksi = $id_transaksi"
        );
        while ($row = mysqli_fetch_assoc($result_items)) {
            $detail_items[] = $row;
        }
    }
}

// Semua pesanan
$all_orders = mysqli_query($conn,
    "SELECT t.*, u.username
     FROM transaksi t
     JOIN user u ON t.id_user = u.id_user
     ORDER BY
        CASE t.status
            WHEN 'pending' THEN 1
            WHEN 'diproses' THEN 2
            WHEN 'selesai' THEN 3
            WHEN 'dibatalkan' THEN 4
        END,
        t.tanggal DESC"
);

include '../includes/header.php';
?>

<div class="sidebar">
    <a href="dashboard.php" class="btn btn-info">📊 Dashboard</a>
    <a href="proses_pesanan.php" class="btn btn-primary">🛒 Proses Pesanan</a>
    <a href="laporan.php" class="btn btn-info">📋 Laporan</a>
</div>

<?php if (isset($msg)): ?>
    <div class="alert alert-<?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<?php if ($detail_order): ?>
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <h2>Detail Pesanan #<?= $detail_order['id_transaksi'] ?></h2>
        <span class="badge badge-<?= $detail_order['status'] ?>"><?= ucfirst($detail_order['status']) ?></span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <div style="font-size: 0.8rem; color: #888; text-transform: uppercase; letter-spacing: 0.5px;">Pelanggan</div>
            <div style="font-weight: 600; margin-top: 0.2rem;">👤 <?= htmlspecialchars($detail_order['username']) ?></div>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: #888; text-transform: uppercase; letter-spacing: 0.5px;">Tanggal</div>
            <div style="font-weight: 600; margin-top: 0.2rem;">📅 <?= date('d/m/Y H:i', strtotime($detail_order['tanggal'])) ?></div>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: #888; text-transform: uppercase; letter-spacing: 0.5px;">Metode Bayar</div>
            <?php
            $metode = strtolower(trim($detail_order['metode_pembayaran'] ?? ''));
            if (!in_array($metode, ['cash', 'qris'])) $metode = 'cash';
            $metode_label = $metode === 'qris' ? '📱 QRIS' : '💵 Cash';
            $metode_color = $metode === 'qris' ? '#667eea' : '#28a745';
            ?>
            <div style="margin-top: 0.2rem;">
                <span style="background:<?= $metode_color ?>20; color:<?= $metode_color ?>; border:1px solid <?= $metode_color ?>40; padding:0.2rem 0.7rem; border-radius:20px; font-weight:600; font-size:0.9rem;">
                    <?= $metode_label ?>
                </span>
            </div>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: #888; text-transform: uppercase; letter-spacing: 0.5px;">Total Bayar</div>
            <div style="font-weight: 700; font-size: 1.2rem; color: #667eea; margin-top: 0.2rem;">
                Rp <?= number_format($detail_order['total_bayar'], 0, ',', '.') ?>
            </div>
        </div>
    </div>

    <h4 style="margin-bottom: 0.75rem;">Item Pesanan:</h4>
    <table>
        <thead>
            <tr><th>Menu</th><th>Qty</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
            <?php foreach ($detail_items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['nama_makanan']) ?></td>
                <td><?= $item['qty'] ?></td>
                <td>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align:right;"><strong>Total</strong></td>
                <td><strong>Rp <?= number_format($detail_order['total_bayar'], 0, ',', '.') ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <!-- Instruksi sesuai metode bayar -->
    <?php if ($metode === 'cash' && in_array($detail_order['status'], ['pending', 'diproses'])): ?>
    <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 0.75rem 1rem; border-radius: 8px; margin-top: 1rem; font-size: 0.9rem;">
        💵 <strong>Pembayaran Cash</strong> — Pastikan pelanggan membayar tunai <strong>Rp <?= number_format($detail_order['total_bayar'], 0, ',', '.') ?></strong> sebelum menyerahkan pesanan.
    </div>
    <?php elseif ($metode === 'qris' && in_array($detail_order['status'], ['pending', 'diproses'])): ?>
    <div style="background: #f0e8ff; border-left: 4px solid #667eea; padding: 0.75rem 1rem; border-radius: 8px; margin-top: 1rem; font-size: 0.9rem;">
        📱 <strong>Pembayaran QRIS</strong> — Verifikasi pembayaran di aplikasi/dashboard QRIS sebelum memproses pesanan ini.
    </div>
    <?php endif; ?>

    <div style="margin-top: 1.5rem;">
        <strong>Update Status:</strong>
        <form method="POST" style="display: flex; gap: 0.5rem; margin-top: 0.5rem; flex-wrap: wrap;">
            <input type="hidden" name="id_transaksi" value="<?= $detail_order['id_transaksi'] ?>">
            <input type="hidden" name="update_status" value="1">

            <?php if ($detail_order['status'] == 'pending'): ?>
                <button type="submit" name="status" value="diproses" class="btn btn-info">🍳 Proses Pesanan</button>
                <button type="submit" name="status" value="dibatalkan" class="btn btn-danger">❌ Batalkan</button>
            <?php elseif ($detail_order['status'] == 'diproses'): ?>
                <button type="submit" name="status" value="selesai" class="btn btn-success">✅ Tandai Selesai</button>
                <button type="submit" name="status" value="dibatalkan" class="btn btn-danger">❌ Batalkan</button>
            <?php else: ?>
                <p style="color: #888; font-style: italic;">Pesanan ini sudah <?= $detail_order['status'] ?>.</p>
            <?php endif; ?>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>Semua Pesanan</h2>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Pelanggan</th>
                <th>Tanggal</th>
                <th>Total</th>
                <th>Metode</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = mysqli_fetch_assoc($all_orders)): ?>
            <?php
            $m = strtolower(trim($order['metode_pembayaran'] ?? ''));
            if (!in_array($m, ['cash', 'qris'])) $m = 'cash';
            $m_icon = $m === 'qris' ? '📱' : '💵';
            $m_color = $m === 'qris' ? '#667eea' : '#28a745';
            ?>
            <tr>
                <td>#<?= $order['id_transaksi'] ?></td>
                <td><?= htmlspecialchars($order['username']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($order['tanggal'])) ?></td>
                <td>Rp <?= number_format($order['total_bayar'], 0, ',', '.') ?></td>
                <td>
                    <span style="color:<?= $m_color ?>; font-weight:600; font-size:0.85rem;"><?= $m_icon ?> <?= strtoupper($m) ?></span>
                </td>
                <td><span class="badge badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                <td>
                    <a href="proses_pesanan.php?id=<?= $order['id_transaksi'] ?>" class="btn btn-sm btn-info">Lihat</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
