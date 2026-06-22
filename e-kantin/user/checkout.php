<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole(['user']);

$title = 'Checkout';

if (empty($_SESSION['cart'])) {
    header('Location: keranjang.php');
    exit;
}

$error = '';

// Hitung total & item keranjang
$cart_items = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
    $result = mysqli_query($conn, "SELECT * FROM menu WHERE id_menu IN ($ids)");
    while ($menu = mysqli_fetch_assoc($result)) {
        $qty = $_SESSION['cart'][$menu['id_menu']];
        $subtotal = $menu['harga'] * $qty;
        $cart_items[] = ['menu' => $menu, 'qty' => $qty, 'subtotal' => $subtotal];
        $total += $subtotal;
    }
}

// STEP 1: Konfirmasi pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_checkout'])) {
    $metode = strtolower(trim($_POST['metode_pembayaran'] ?? ''));
    if (!in_array($metode, ['cash', 'qris'])) {
        $error = 'Pilih metode pembayaran terlebih dahulu.';
    } else {
        $id_user = $_SESSION['id_user'];
        $total_bayar = 0;
        $cart_details = [];

        foreach ($_SESSION['cart'] as $id_menu => $qty) {
            $menu_row = mysqli_query($conn, "SELECT * FROM menu WHERE id_menu = " . intval($id_menu))->fetch_assoc();
            if (!$menu_row || $menu_row['stok'] < $qty) {
                $error = "Stok tidak mencukupi untuk " . ($menu_row['nama_makanan'] ?? 'menu #' . $id_menu);
                break;
            }
            $subtotal = $menu_row['harga'] * $qty;
            $total_bayar += $subtotal;
            $cart_details[] = ['id_menu' => $id_menu, 'qty' => $qty, 'subtotal' => $subtotal];
        }

        if (empty($error)) {
            mysqli_begin_transaction($conn);
            try {
                $stmt = $conn->prepare("INSERT INTO transaksi (id_user, total_bayar, status, metode_pembayaran) VALUES (?, ?, 'pending', ?)");
                $stmt->bind_param("iis", $id_user, $total_bayar, $metode);
                $stmt->execute();
                $id_transaksi = $conn->insert_id;

                foreach ($cart_details as $detail) {
                    $stmt2 = $conn->prepare("INSERT INTO detail_transaksi (id_transaksi, id_menu, qty, subtotal) VALUES (?, ?, ?, ?)");
                    $stmt2->bind_param("iiid", $id_transaksi, $detail['id_menu'], $detail['qty'], $detail['subtotal']);
                    $stmt2->execute();
                    mysqli_query($conn, "UPDATE menu SET stok = stok - {$detail['qty']} WHERE id_menu = {$detail['id_menu']}");
                }

                mysqli_commit($conn);
                $_SESSION['cart'] = [];

                if ($metode === 'qris') {
                    header("Location: bayar_qris.php?id=$id_transaksi");
                } else {
                    header("Location: riwayat.php?msg=Pesanan berhasil! Silakan bayar ke kasir. ID: #$id_transaksi&type=success");
                }
                exit;
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = 'Gagal memproses pesanan: ' . $e->getMessage();
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="sidebar">
    <a href="dashboard.php" class="btn btn-info">🍽️ Lihat Menu</a>
    <a href="keranjang.php" class="btn btn-info">🛒 Keranjang</a>
    <a href="riwayat.php" class="btn btn-info">📋 Riwayat Pesanan</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-header">
        <h2>🧾 Konfirmasi Pesanan</h2>
    </div>

    <p><strong>Pelanggan:</strong> <?= htmlspecialchars($_SESSION['username']) ?></p>

    <h4 style="margin-top: 1rem; margin-bottom: 0.75rem;">Detail Pesanan:</h4>
    <table>
        <thead>
            <tr><th>Menu</th><th>Qty</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
            <?php foreach ($cart_items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['menu']['nama_makanan']) ?></td>
                <td><?= $item['qty'] ?></td>
                <td>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align: right;"><strong>Total Bayar</strong></td>
                <td><strong style="color: #667eea; font-size: 1.1rem;">Rp <?= number_format($total, 0, ',', '.') ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 2rem;">
        <h4 style="margin-bottom: 1rem;">💳 Pilih Metode Pembayaran</h4>

        <form method="POST">
            <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
                <label id="label-cash" style="
                    flex: 1; border: 2px solid #e0e0e0; border-radius: 12px;
                    padding: 1.2rem; cursor: pointer; text-align: center; transition: all 0.3s;
                ">
                    <input type="radio" name="metode_pembayaran" value="cash" style="display:none;" onchange="selectMethod('cash')">
                    <div style="font-size: 2.5rem;">💵</div>
                    <div style="font-weight: 600; margin-top: 0.5rem; font-size: 1rem;">Cash</div>
                    <div style="font-size: 0.8rem; color: #888; margin-top: 0.3rem;">Bayar langsung ke kasir</div>
                </label>

                <label id="label-qris" style="
                    flex: 1; border: 2px solid #e0e0e0; border-radius: 12px;
                    padding: 1.2rem; cursor: pointer; text-align: center; transition: all 0.3s;
                ">
                    <input type="radio" name="metode_pembayaran" value="qris" style="display:none;" onchange="selectMethod('qris')">
                    <div style="font-size: 2.5rem;">📱</div>
                    <div style="font-weight: 600; margin-top: 0.5rem; font-size: 1rem;">QRIS</div>
                    <div style="font-size: 0.8rem; color: #888; margin-top: 0.3rem;">GoPay, OVO, Dana, dll</div>
                </label>
            </div>

            <div id="info-cash" style="display:none; background:#f0f9ff; border-left:4px solid #17a2b8; padding:1rem; border-radius:8px; margin-bottom:1rem;">
                <strong>ℹ️ Pembayaran Cash</strong><br>
                Pesanan akan dikirim ke kasir. Siapkan uang tunai sebesar <strong>Rp <?= number_format($total, 0, ',', '.') ?></strong> dan bayar saat mengambil pesanan.
            </div>
            <div id="info-qris" style="display:none; background:#f5f0ff; border-left:4px solid #667eea; padding:1rem; border-radius:8px; margin-bottom:1rem;">
                <strong>📱 Pembayaran QRIS</strong><br>
                Setelah konfirmasi, Anda akan diarahkan ke halaman QR Code untuk scan dan bayar sebesar <strong>Rp <?= number_format($total, 0, ',', '.') ?></strong>. Gunakan e-wallet apapun yang mendukung QRIS.
            </div>

            <div style="display:flex; gap:1rem; justify-content:center; margin-top:1rem;">
                <button type="submit" name="confirm_checkout" id="btn-confirm"
                    class="btn btn-success btn-lg" disabled
                    style="opacity:0.5; cursor:not-allowed; font-size:1rem; padding:0.75rem 2rem;">
                    ✅ Konfirmasi Pesanan
                </button>
                <a href="keranjang.php" class="btn btn-danger">Kembali</a>
            </div>
        </form>
    </div>
</div>

<style>
.label-selected { border-color: #667eea !important; background: #f5f0ff; box-shadow: 0 0 0 3px rgba(102,126,234,0.2); }
</style>
<script>
function selectMethod(method) {
    ['cash','qris'].forEach(m => {
        document.getElementById('label-'+m).classList.remove('label-selected');
        document.getElementById('info-'+m).style.display = 'none';
    });
    document.getElementById('label-'+method).classList.add('label-selected');
    document.getElementById('info-'+method).style.display = 'block';
    var btn = document.getElementById('btn-confirm');
    btn.disabled = false; btn.style.opacity = '1'; btn.style.cursor = 'pointer';
}
</script>

<?php include '../includes/footer.php'; ?>
