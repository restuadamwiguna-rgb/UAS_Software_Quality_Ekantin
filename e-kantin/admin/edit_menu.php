<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole(['admin']);

$title = 'Edit Menu';
$error = '';

$id_menu = intval($_GET['id'] ?? 0);
$menu = mysqli_query($conn, "SELECT * FROM menu WHERE id_menu = $id_menu")->fetch_assoc();

if (!$menu) { header('Location: kelola_menu.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama  = trim($_POST['nama_makanan'] ?? '');
    $harga = floatval($_POST['harga'] ?? 0);
    $stok  = intval($_POST['stok'] ?? 0);

    if ($harga < 1000 || $harga > 999999) {
        $error = 'Harga harus antara Rp 1.000 – Rp 999.999';
    } else {
        $gambar = $menu['gambar']; // default: gambar lama

        // Hapus gambar?
        if (isset($_POST['hapus_gambar']) && $gambar) {
            @unlink('../assets/uploads/menu/' . $gambar);
            $gambar = null;
        }

        // Upload gambar baru?
        if (!empty($_FILES['gambar']['name'])) {
            $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp','gif'];
            if (!in_array($ext, $allowed)) {
                $error = 'Format gambar harus JPG, PNG, WEBP, atau GIF';
            } elseif ($_FILES['gambar']['size'] > 2 * 1024 * 1024) {
                $error = 'Ukuran gambar maksimal 2MB';
            } else {
                // Hapus gambar lama kalau ada
                if ($menu['gambar']) @unlink('../assets/uploads/menu/' . $menu['gambar']);
                $gambar = 'menu_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                move_uploaded_file($_FILES['gambar']['tmp_name'], '../assets/uploads/menu/' . $gambar);
            }
        }

        if (empty($error)) {
            $stmt = $conn->prepare("UPDATE menu SET nama_makanan=?, harga=?, stok=?, gambar=? WHERE id_menu=?");
            $stmt->bind_param("sdisi", $nama, $harga, $stok, $gambar, $id_menu);
            if ($stmt->execute()) {
                header('Location: kelola_menu.php?msg=Menu berhasil diupdate&type=success');
                exit;
            } else {
                $error = 'Gagal update menu';
            }
        }
    }
}

include '../includes/header.php';
?>
<div class="sidebar">
    <a href="dashboard.php" class="btn btn-info">📊 Dashboard</a>
    <a href="kelola_menu.php" class="btn btn-primary">🍽️ Kelola Menu</a>
    <a href="kelola_user.php" class="btn btn-info">👥 Kelola User</a>
    <a href="laporan.php" class="btn btn-info">📋 Laporan</a>
</div>

<div class="card" style="max-width:520px;">
    <div class="card-header"><h2>✏️ Edit Menu #<?= $menu['id_menu'] ?></h2></div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Nama Makanan / Minuman</label>
            <input type="text" name="nama_makanan" class="form-control" required
                   value="<?= htmlspecialchars($menu['nama_makanan']) ?>">
        </div>
        <div class="form-group">
            <label>Harga (Rp 1.000 – Rp 999.999)</label>
            <input type="number" name="harga" class="form-control" min="1000" max="999999" required
                   value="<?= $menu['harga'] ?>">
        </div>
        <div class="form-group">
            <label>Stok</label>
            <input type="number" name="stok" class="form-control" min="0" max="9999" required
                   value="<?= $menu['stok'] ?>">
        </div>

        <div class="form-group">
            <label>Foto Menu</label>
            <?php if ($menu['gambar'] && !empty($menu['gambar'] ?? null) && file_exists('../assets/uploads/menu/' . $menu['gambar'])): ?>
            <div style="margin-bottom:0.75rem;">
                <img src="../assets/uploads/menu/<?= htmlspecialchars($menu['gambar']) ?>"
                     alt="Foto saat ini" style="max-width:200px;max-height:150px;border-radius:10px;border:2px solid #f0f0f0;">
                <div style="margin-top:0.5rem;">
                    <label style="font-weight:400;font-size:0.85rem;cursor:pointer;color:#dc3545;">
                        <input type="checkbox" name="hapus_gambar" value="1"> Hapus foto ini
                    </label>
                </div>
            </div>
            <?php endif; ?>
            <div class="upload-area" id="uploadArea" onclick="document.getElementById('gambarInput').click()">
                <div id="uploadPreview">
                    <div style="font-size:2rem;">📷</div>
                    <div style="color:#888;font-size:0.85rem;margin-top:0.3rem;">
                        <?= $menu['gambar'] ? 'Klik untuk ganti foto' : 'Klik untuk pilih foto' ?>
                    </div>
                </div>
                <img id="previewImg" src="" alt="" style="display:none;max-width:100%;max-height:180px;border-radius:8px;">
            </div>
            <input type="file" id="gambarInput" name="gambar" accept="image/*" style="display:none" onchange="previewImage(this)">
        </div>

        <div style="display:flex;gap:0.5rem;">
            <button type="submit" class="btn btn-success">💾 Update</button>
            <a href="kelola_menu.php" class="btn btn-danger">Batal</a>
        </div>
    </form>
</div>

<style>
.upload-area {
    border: 2px dashed #ccc; border-radius: 12px; padding: 1.5rem;
    text-align: center; cursor: pointer; transition: all 0.2s;
    background: #fafafa; min-height: 100px;
    display: flex; align-items: center; justify-content: center;
}
.upload-area:hover { border-color: #667eea; background: #f5f0ff; }
</style>
<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('uploadPreview').style.display = 'none';
            var img = document.getElementById('previewImg');
            img.src = e.target.result; img.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include '../includes/footer.php'; ?>
