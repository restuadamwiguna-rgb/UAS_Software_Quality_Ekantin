<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole(['user']);

$title = 'Bayar via QRIS';

if (!isset($_GET['id'])) {
    header('Location: riwayat.php');
    exit;
}

$id_transaksi = intval($_GET['id']);
$id_user = $_SESSION['id_user'];

// Ambil data transaksi (pastikan milik user ini)
$trx = mysqli_query($conn,
    "SELECT t.*, u.username FROM transaksi t
     JOIN user u ON t.id_user = u.id_user
     WHERE t.id_transaksi = $id_transaksi AND t.id_user = $id_user"
)->fetch_assoc();

if (!$trx) {
    header('Location: riwayat.php');
    exit;
}

// Ambil detail items
$details = mysqli_query($conn,
    "SELECT dt.*, m.nama_makanan FROM detail_transaksi dt
     JOIN menu m ON dt.id_menu = m.id_menu
     WHERE dt.id_transaksi = $id_transaksi"
);
$detail_items = [];
while ($d = mysqli_fetch_assoc($details)) {
    $detail_items[] = $d;
}

// Konfirmasi bayar QRIS (simulasi — user klik "Sudah Bayar")
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['konfirmasi_bayar'])) {
    mysqli_query($conn, "UPDATE transaksi SET status='pending' WHERE id_transaksi=$id_transaksi AND id_user=$id_user");
    header("Location: riwayat.php?msg=Pembayaran QRIS berhasil dikonfirmasi! Pesanan #$id_transaksi sedang diproses kasir.&type=success");
    exit;
}

// Generate QRIS menggunakan API qris.id (gratis, no-auth)
// Format: https://api.qris.id/merchant/qris?amount=xxx&merchant_name=xxx&merchant_city=xxx
// Alternatif: gunakan goqr.me untuk generate QR dari string QRIS
$amount = intval($trx['total_bayar']);
$merchant_name = urlencode('E-Kantin');
$merchant_city = urlencode('Bandung');
$order_id = 'EKANTIN-' . str_pad($id_transaksi, 6, '0', STR_PAD_LEFT);

// Gunakan QRIS statis placeholder + overlay nominal menggunakan goQR.me
// String QRIS standar BI untuk demo (bisa diganti QRIS merchant asli)
// Untuk QRIS asli, ganti $qris_string dengan string QRIS dari bank/payment gateway
$qris_string = "00020101021126570011ID.CO.BCA.WWW011893600014000901390502150000000000000000303UME51440014ID.CO.QRIS.WWW0215ID20232303789560303UME5204599953033605802ID5916E-Kantin Bandung6007Bandung610440561622070703A01630401C5";
// Encode untuk URL
$qris_encoded = urlencode($qris_string);

// Generate QR Code via goqr.me (reliable, gratis)
$qr_size = 300;
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size={$qr_size}x{$qr_size}&data=" . $qris_encoded . "&ecc=M&margin=2";

include '../includes/header.php';
?>

<div class="sidebar">
    <a href="dashboard.php" class="btn btn-info">🍽️ Lihat Menu</a>
    <a href="riwayat.php" class="btn btn-info">📋 Riwayat Pesanan</a>
</div>

<div style="max-width: 560px; margin: 0 auto;">

<!-- HEADER CARD -->
<div class="card" style="text-align: center; padding: 1.5rem;">
    <div style="font-size: 2rem; margin-bottom: 0.5rem;">📱</div>
    <h2 style="color: #333; margin-bottom: 0.25rem;">Bayar dengan QRIS</h2>
    <p style="color: #888; font-size: 0.9rem;">Pesanan #<?= $id_transaksi ?></p>
</div>

<!-- QR CODE CARD -->
<div class="card" style="text-align: center;">
    <div style="margin-bottom: 1rem;">
        <div style="font-size: 0.85rem; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.75rem;">Scan untuk membayar</div>
        
        <!-- Logo QRIS -->
        <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-bottom: 1rem;">
            <div style="background: linear-gradient(135deg, #e31e24, #f7941d); color:white; font-weight:800; font-size:1rem; padding: 0.3rem 0.8rem; border-radius:6px; letter-spacing:1px;">QRIS</div>
            <span style="color:#888; font-size:0.8rem;">by Bank Indonesia</span>
        </div>

        <!-- QR Code -->
        <div style="display: inline-block; padding: 12px; border: 3px solid #e31e24; border-radius: 16px; background: white; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <img src="<?= $qr_url ?>" 
                 alt="QRIS QR Code" 
                 width="<?= $qr_size ?>" height="<?= $qr_size ?>"
                 style="display: block; border-radius: 8px;"
                 onerror="this.onerror=null; this.src='https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=EKANTIN-<?= $id_transaksi ?>&margin=2';">
        </div>

        <!-- Nominal -->
        <div style="margin-top: 1.25rem; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 12px; padding: 1rem 1.5rem; display: inline-block; min-width: 250px;">
            <div style="font-size: 0.85rem; opacity: 0.85; margin-bottom: 0.25rem;">Total Pembayaran</div>
            <div style="font-size: 1.75rem; font-weight: 700;">Rp <?= number_format($trx['total_bayar'], 0, ',', '.') ?></div>
            <div style="font-size: 0.8rem; opacity: 0.75; margin-top: 0.25rem;"><?= $order_id ?></div>
        </div>
    </div>

    <!-- Panduan -->
    <div style="background: #f8f9fa; border-radius: 10px; padding: 1rem; margin-top: 1rem; text-align: left;">
        <div style="font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem;">📋 Cara Bayar:</div>
        <ol style="margin-left: 1.2rem; font-size: 0.85rem; color: #555; line-height: 1.8;">
            <li>Buka aplikasi e-wallet (GoPay, OVO, Dana, ShopeePay, dll)</li>
            <li>Pilih menu <strong>Scan QR / QRIS</strong></li>
            <li>Arahkan kamera ke QR Code di atas</li>
            <li>Pastikan nominal sesuai: <strong>Rp <?= number_format($trx['total_bayar'], 0, ',', '.') ?></strong></li>
            <li>Konfirmasi pembayaran di aplikasi Anda</li>
            <li>Klik tombol <strong>"Saya Sudah Bayar"</strong> di bawah ini</li>
        </ol>
    </div>

    <!-- Diterima di -->
    <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 1rem; flex-wrap: wrap;">
        <?php
        $wallets = ['GoPay','OVO','Dana','ShopeePay','LinkAja','BSI','BCA','BRI','Mandiri','BNI'];
        foreach ($wallets as $w):
        ?>
        <span style="background:#f0f0f0; border-radius:20px; padding:0.25rem 0.6rem; font-size:0.75rem; color:#555;"><?= $w ?></span>
        <?php endforeach; ?>
    </div>
</div>

<!-- DETAIL PESANAN -->
<div class="card">
    <div class="card-header">
        <h3 style="font-size: 1rem;">🧾 Ringkasan Pesanan</h3>
    </div>
    <table style="font-size: 0.9rem;">
        <thead>
            <tr><th>Menu</th><th>Qty</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
            <?php foreach ($detail_items as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['nama_makanan']) ?></td>
                <td><?= $d['qty'] ?></td>
                <td>Rp <?= number_format($d['subtotal'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align:right;"><strong>Total</strong></td>
                <td><strong>Rp <?= number_format($trx['total_bayar'], 0, ',', '.') ?></strong></td>
            </tr>
        </tfoot>
    </table>
    <p style="margin-top: 0.75rem; font-size: 0.85rem; color: #888;">
        <strong>Pemesan:</strong> <?= htmlspecialchars($trx['username']) ?> &nbsp;|&nbsp;
        <strong>Metode:</strong> QRIS &nbsp;|&nbsp;
        <strong>Status:</strong> <span class="badge badge-pending">Pending</span>
    </p>
</div>

<!-- TOMBOL KONFIRMASI -->
<div class="card" style="text-align: center;">
    <p style="color: #888; font-size: 0.85rem; margin-bottom: 1rem;">
        ⚠️ Klik tombol di bawah setelah pembayaran berhasil dikonfirmasi di aplikasi Anda
    </p>
    <form method="POST">
        <button type="submit" name="konfirmasi_bayar" class="btn btn-success btn-lg"
            style="font-size: 1rem; padding: 0.9rem 2.5rem; width: 100%; margin-bottom: 0.75rem;">
            ✅ Saya Sudah Bayar
        </button>
    </form>
    <a href="riwayat.php" style="color: #888; font-size: 0.85rem; text-decoration: none;">Bayar nanti →</a>
</div>

<!-- COUNTDOWN TIMER -->
<div class="card" style="text-align: center; padding: 1rem;">
    <div style="font-size: 0.85rem; color: #888;">⏱️ Batas waktu pembayaran:</div>
    <div id="countdown" style="font-size: 1.5rem; font-weight: 700; color: #dc3545; margin-top: 0.25rem;">15:00</div>
</div>

</div>

<script>
// Countdown 15 menit
var seconds = 15 * 60;
var el = document.getElementById('countdown');
var interval = setInterval(function() {
    seconds--;
    if (seconds <= 0) {
        clearInterval(interval);
        el.textContent = 'Waktu habis';
        el.style.color = '#dc3545';
        return;
    }
    var m = Math.floor(seconds / 60);
    var s = seconds % 60;
    el.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
    if (seconds <= 60) el.style.color = '#dc3545';
    else if (seconds <= 180) el.style.color = '#ffc107';
    else el.style.color = '#28a745';
}, 1000);
</script>

<?php include '../includes/footer.php'; ?>
