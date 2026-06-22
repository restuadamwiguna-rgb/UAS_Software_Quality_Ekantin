<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole(['admin']);

$title = 'Tambah Menu';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama  = trim($_POST['nama_makanan'] ?? '');
    $harga = floatval($_POST['harga'] ?? 0);
    $stok  = intval($_POST['stok'] ?? 0);

    if ($harga < 1000)   $error = 'Harga minimal Rp 1.000';
    elseif ($harga > 999999) $error = 'Harga maksimal Rp 999.999';
    elseif ($stok < 0)   $error = 'Stok tidak boleh negatif';
    elseif ($stok > 9999) $error = 'Stok maksimal 9999';
    else {
        // Upload gambar
        $gambar = null;
        if (!empty($_FILES['gambar']['name'])) {
            $ext    = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp','gif'];
            if (!in_array($ext, $allowed)) {
                $error = 'Format gambar harus JPG, PNG, WEBP, atau GIF';
            } elseif ($_FILES['gambar']['size'] > 2 * 1024 * 1024) {
                $error = 'Ukuran gambar maksimal 2MB';
            } else {
                $gambar = 'menu_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                move_uploaded_file($_FILES['gambar']['tmp_name'], '../assets/uploads/menu/' . $gambar);
            }
        }

        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO menu (nama_makanan, harga, stok, gambar) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sdis", $nama, $harga, $stok, $gambar);
            if ($stmt->execute()) {
                header('Location: kelola_menu.php?msg=Menu berhasil ditambahkan&type=success');
                exit;
            } else {
                $error = 'Gagal menambahkan menu';
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
    <div class="card-header"><h2>➕ Tambah Menu Baru</h2></div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Nama Makanan / Minuman</label>
            <input type="text" name="nama_makanan" class="form-control" required
                   value="<?= htmlspecialchars($_POST['nama_makanan'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Harga (Rp 1.000 – Rp 999.999)</label>
            <input type="number" name="harga" class="form-control" min="1000" max="999999" required
                   value="<?= htmlspecialchars($_POST['harga'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Stok</label>
            <input type="number" name="stok" class="form-control" min="0" max="9999" required
                   value="<?= htmlspecialchars($_POST['stok'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Foto Menu <span style="color:#aaa;font-weight:400;">(opsional, maks 2MB)</span></label>
            <div class="upload-area" id="uploadArea" onclick="document.getElementById('gambarInput').click()">
                <div id="uploadPreview">
                    <div style="font-size:2.5rem;">🖼️</div>
                    <div style="margin-top:0.5rem;color:#888;font-size:0.9rem;">Klik untuk pilih foto</div>
                    <div style="font-size:0.75rem;color:#bbb;margin-top:0.25rem;">JPG, PNG, WEBP (maks 2MB)</div>
                </div>
                <img id="previewImg" src="" alt="" style="display:none;max-width:100%;max-height:200px;border-radius:8px;">
            </div>
            <input type="file" id="gambarInput" name="gambar" accept="image/*" style="display:none" onchange="previewImage(this)">
        </div>
        <div style="display:flex;gap:0.5rem;">
            <button type="submit" class="btn btn-success">💾 Simpan</button>
            <a href="kelola_menu.php" class="btn btn-danger">Batal</a>
        </div>
    </form>
</div>

<style>
.upload-area {
    border: 2px dashed #ccc; border-radius: 12px; padding: 1.5rem;
    text-align: center; cursor: pointer; transition: all 0.2s;
    background: #fafafa; min-height: 120px;
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
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include '../includes/footer.php'; ?>
