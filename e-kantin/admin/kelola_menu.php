<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole(['admin']);

$title = 'Kelola Menu';
$menus = mysqli_query($conn, "SELECT * FROM menu ORDER BY id_menu DESC");

include '../includes/header.php';
?>
<div class="sidebar">
    <a href="dashboard.php" class="btn btn-info">📊 Dashboard</a>
    <a href="kelola_menu.php" class="btn btn-primary">🍽️ Kelola Menu</a>
    <a href="kelola_user.php" class="btn btn-info">👥 Kelola User</a>
    <a href="laporan.php" class="btn btn-info">📋 Laporan</a>
</div>

<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h2>🍽️ Daftar Menu</h2>
        <a href="tambah_menu.php" class="btn btn-success">+ Tambah Menu</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_GET['type'] ?? 'success') ?>">
            <?= htmlspecialchars($_GET['msg']) ?>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th style="width:70px;">Foto</th>
                <th>Nama</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($menu = mysqli_fetch_assoc($menus)): ?>
            <tr>
                <td>
                    <?php if ($menu['gambar'] && !empty($menu['gambar'] ?? null) && file_exists('../assets/uploads/menu/' . $menu['gambar'])): ?>
                        <img src="../assets/uploads/menu/<?= htmlspecialchars($menu['gambar']) ?>"
                             alt="<?= htmlspecialchars($menu['nama_makanan']) ?>"
                             style="width:56px;height:56px;object-fit:cover;border-radius:8px;border:2px solid #f0f0f0;">
                    <?php else: ?>
                        <div style="width:56px;height:56px;background:#f5f5f5;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;border:2px dashed #ddd;">🍽️</div>
                    <?php endif; ?>
                </td>
                <td><strong><?= htmlspecialchars($menu['nama_makanan']) ?></strong></td>
                <td>Rp <?= number_format($menu['harga'], 0, ',', '.') ?></td>
                <td>
                    <span style="color:<?= $menu['stok'] > 0 ? '#28a745' : '#dc3545' ?>;font-weight:600;">
                        <?= $menu['stok'] ?>
                    </span>
                </td>
                <td style="white-space:nowrap;">
                    <a href="edit_menu.php?id=<?= $menu['id_menu'] ?>" class="btn btn-sm btn-warning">✏️ Edit</a>
                    <a href="hapus_menu.php?id=<?= $menu['id_menu'] ?>" class="btn btn-sm btn-danger"
                       onclick="return confirm('Yakin hapus menu ini?')">🗑️ Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
