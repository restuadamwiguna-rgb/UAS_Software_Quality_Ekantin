<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole(['user']);

$title = 'Menu Kantin';
$menus = mysqli_query($conn, "SELECT * FROM menu WHERE stok > 0 ORDER BY nama_makanan ASC");

include '../includes/header.php';
?>

<div class="sidebar">
    <a href="dashboard.php" class="btn btn-primary">🍽️ Lihat Menu</a>
    <a href="keranjang.php" class="btn btn-info">🛒 Keranjang
        <?php
        $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
        if ($cart_count > 0) echo "<span style='background:red;color:white;padding:2px 8px;border-radius:50%;margin-left:5px;'>$cart_count</span>";
        ?>
    </a>
    <a href="riwayat.php" class="btn btn-info">📋 Riwayat Pesanan</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-<?= htmlspecialchars($_GET['type'] ?? 'success') ?>">
        <?= htmlspecialchars($_GET['msg']) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>🍽️ Daftar Menu Kantin</h2>
    </div>

    <div class="menu-grid">
        <?php while ($menu = mysqli_fetch_assoc($menus)): ?>
        <div class="menu-card">
            <!-- Gambar produk -->
            <div class="menu-img-wrap">
                <?php
                $gambar_file = $menu['gambar'] ?? null;
                $img_path = '../assets/uploads/menu/' . $gambar_file;
                if (!empty($gambar_file) && file_exists($img_path)):
                ?>
                    <img src="../assets/uploads/menu/<?= htmlspecialchars($gambar_file) ?>"
                         alt="<?= htmlspecialchars($menu['nama_makanan']) ?>"
                         class="menu-img">
                <?php else: ?>
                    <div class="menu-img-placeholder">🍽️</div>
                <?php endif; ?>

                <?php if ($menu['stok'] <= 5): ?>
                    <div class="stok-badge">Sisa <?= $menu['stok'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="menu-body">
                <h3 class="menu-name"><?= htmlspecialchars($menu['nama_makanan']) ?></h3>
                <div class="menu-price">Rp <?= number_format($menu['harga'], 0, ',', '.') ?></div>
                <div class="menu-stok">Stok: <?= $menu['stok'] ?></div>

                <form method="POST" action="keranjang.php">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id_menu" value="<?= $menu['id_menu'] ?>">
                    <div style="display:flex;gap:0.5rem;align-items:center;margin-top:0.75rem;">
                        <input type="number" name="qty" class="form-control qty-input"
                               value="1" min="1" max="<?= min(99, $menu['stok']) ?>">
                        <button type="submit" class="btn btn-primary" style="flex:1;white-space:nowrap;">
                            + Keranjang
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endwhile; ?>

        <?php if (mysqli_num_rows($menus) == 0): ?>
            <p style="text-align:center;grid-column:1/-1;padding:3rem;color:#888;">
                Belum ada menu tersedia saat ini.
            </p>
        <?php endif; ?>
    </div>
</div>

<style>
.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.25rem;
    padding: 0.25rem;
}
.menu-card {
    border: 1.5px solid #f0f0f0;
    border-radius: 14px;
    overflow: hidden;
    background: #fff;
    transition: all 0.25s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.menu-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(102,126,234,0.15);
    border-color: #c5caff;
}
.menu-img-wrap {
    position: relative;
    width: 100%;
    height: 150px;
    background: #f5f5f5;
    overflow: hidden;
}
.menu-img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.3s;
}
.menu-card:hover .menu-img { transform: scale(1.05); }
.menu-img-placeholder {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    font-size: 3.5rem;
    background: linear-gradient(135deg, #f5f5f5, #ececec);
}
.stok-badge {
    position: absolute; top: 8px; right: 8px;
    background: #dc3545; color: white;
    font-size: 0.7rem; font-weight: 700;
    padding: 0.2rem 0.5rem; border-radius: 20px;
}
.menu-body { padding: 0.9rem; }
.menu-name {
    font-size: 0.95rem; font-weight: 700;
    color: #333; margin-bottom: 0.3rem;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.menu-price {
    font-size: 1.05rem; font-weight: 700;
    color: #667eea; margin-bottom: 0.2rem;
}
.menu-stok { font-size: 0.78rem; color: #aaa; margin-bottom: 0.25rem; }
.qty-input {
    width: 56px !important; text-align: center;
    padding: 0.45rem 0.3rem; font-size: 0.9rem;
}
</style>

<?php include '../includes/footer.php'; ?>
