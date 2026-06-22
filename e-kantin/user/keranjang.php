<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole(['user']);

$title = 'Keranjang Belanja';

// Inisialisasi keranjang
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $id_menu = intval($_POST['id_menu'] ?? 0);
        $qty = intval($_POST['qty'] ?? 1);
        
        // BVA untuk qty (1-99)
        if ($qty < 1) $qty = 1;
        if ($qty > 99) $qty = 99;
        
        // Cek stok
        $menu = mysqli_query($conn, "SELECT * FROM menu WHERE id_menu = $id_menu")->fetch_assoc();
        if ($menu && $menu['stok'] >= $qty) {
            if (isset($_SESSION['cart'][$id_menu])) {
                $_SESSION['cart'][$id_menu] += $qty;
            } else {
                $_SESSION['cart'][$id_menu] = $qty;
            }
            // Batasi qty ke stok
            if ($_SESSION['cart'][$id_menu] > $menu['stok']) {
                $_SESSION['cart'][$id_menu] = $menu['stok'];
            }
            header('Location: keranjang.php?msg=Item ditambahkan&type=success');
            exit;
        }
    } elseif ($action === 'update') {
        $id_menu = intval($_POST['id_menu'] ?? 0);
        $qty = intval($_POST['qty'] ?? 1);
        
        if ($qty <= 0) {
            unset($_SESSION['cart'][$id_menu]);
        } else {
            $_SESSION['cart'][$id_menu] = min($qty, 99);
        }
        header('Location: keranjang.php?msg=Keranjang diupdate&type=success');
        exit;
    } elseif ($action === 'remove') {
        $id_menu = intval($_POST['id_menu'] ?? 0);
        unset($_SESSION['cart'][$id_menu]);
        header('Location: keranjang.php?msg=Item dihapus&type=success');
        exit;
    }
}

// Ambil data menu di keranjang
$cart_items = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $result = mysqli_query($conn, "SELECT * FROM menu WHERE id_menu IN ($ids)");
    while ($menu = mysqli_fetch_assoc($result)) {
        $qty = $_SESSION['cart'][$menu['id_menu']];
        $subtotal = $menu['harga'] * $qty;
        $cart_items[] = [
            'menu' => $menu,
            'qty' => $qty,
            'subtotal' => $subtotal
        ];
        $total += $subtotal;
    }
}

include '../includes/header.php';
?>

<div class="sidebar">
    <a href="dashboard.php" class="btn btn-info">🍽️ Lihat Menu</a>
    <a href="keranjang.php" class="btn btn-primary">🛒 Keranjang</a>
    <a href="riwayat.php" class="btn btn-info">📋 Riwayat Pesanan</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-<?= $_GET['type'] ?? 'success' ?>">
        <?= htmlspecialchars($_GET['msg']) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Keranjang Belanja</h2>
        <?php if (!empty($cart_items)): ?>
            <a href="checkout.php" class="btn btn-success">💳 Checkout Sekarang</a>
        <?php endif; ?>
    </div>
    
    <?php if (empty($cart_items)): ?>
        <p style="text-align: center; padding: 2rem;">Keranjang kosong. <a href="dashboard.php">Lihat menu</a></p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Menu</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['menu']['nama_makanan']) ?></td>
                    <td>Rp <?= number_format($item['menu']['harga'], 0, ',', '.') ?></td>
                    <td>
                        <form method="POST" style="display: flex; gap: 0.3rem; align-items: center;">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id_menu" value="<?= $item['menu']['id_menu'] ?>">
                            <input type="number" name="qty" value="<?= $item['qty'] ?>" 
                                   min="1" max="<?= min(99, $item['menu']['stok']) ?>" 
                                   class="form-control" style="width: 70px;">
                            <button type="submit" class="btn btn-sm btn-info">Update</button>
                        </form>
                    </td>
                    <td>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                    <td>
                        <form method="POST" class="inline-form">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="id_menu" value="<?= $item['menu']['id_menu'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger" 
                                    onclick="return confirm('Hapus item ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>Total</strong></td>
                    <td colspan="2"><strong>Rp <?= number_format($total, 0, ',', '.') ?></strong></td>
                </tr>
            </tfoot>
        </table>
        
        <div style="margin-top: 1.5rem; text-align: right;">
            <a href="checkout.php" class="btn btn-success btn-lg">💳 Checkout - Rp <?= number_format($total, 0, ',', '.') ?></a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>