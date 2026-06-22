<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole(['admin', 'kasir']);

$title = 'Laporan Transaksi';

$filter_status = $_GET['status'] ?? '';
$where = '';
if ($filter_status && in_array($filter_status, ['pending', 'diproses', 'selesai', 'dibatalkan'])) {
    $where = "WHERE t.status = '$filter_status'";
}

$laporan = mysqli_query($conn, 
    "SELECT t.*, u.username 
     FROM transaksi t 
     JOIN user u ON t.id_user = u.id_user 
     $where 
     ORDER BY t.tanggal DESC"
);

// Total statistik
$total_pendapatan = mysqli_query($conn, "SELECT SUM(total_bayar) as total FROM transaksi WHERE status='selesai'")->fetch_assoc()['total'] ?? 0;
$total_transaksi = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi")->fetch_assoc()['total'];

include '../includes/header.php';
?>

<div class="sidebar">
    <a href="dashboard.php" class="btn btn-info">📊 Dashboard</a>
    <a href="kelola_menu.php" class="btn btn-info">🍽️ Kelola Menu</a>
    <a href="kelola_user.php" class="btn btn-info">👥 Kelola User</a>
    <a href="laporan.php" class="btn btn-primary">📋 Laporan</a>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Laporan Transaksi</h2>
        <div>
            <strong>Total Transaksi: <?= $total_transaksi ?></strong> | 
            <strong>Pendapatan: Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></strong>
        </div>
    </div>
    
    <div style="margin-bottom: 1rem;">
        Filter: 
        <a href="laporan.php" class="btn btn-sm <?= !$filter_status ? 'btn-primary' : 'btn-info' ?>">Semua</a>
        <a href="laporan.php?status=pending" class="btn btn-sm <?= $filter_status == 'pending' ? 'btn-primary' : 'btn-info' ?>">Pending</a>
        <a href="laporan.php?status=diproses" class="btn btn-sm <?= $filter_status == 'diproses' ? 'btn-primary' : 'btn-info' ?>">Diproses</a>
        <a href="laporan.php?status=selesai" class="btn btn-sm <?= $filter_status == 'selesai' ? 'btn-primary' : 'btn-info' ?>">Selesai</a>
        <a href="laporan.php?status=dibatalkan" class="btn btn-sm <?= $filter_status == 'dibatalkan' ? 'btn-primary' : 'btn-info' ?>">Dibatalkan</a>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Tanggal</th>
                <th>Total</th>
                <th>Status</th>
                <th>Detail</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($laporan)): ?>
            <tr>
                <td>#<?= $row['id_transaksi'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?></td>
                <td>Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?></td>
                <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                <td>
                    <?php
                    $details = mysqli_query($conn, 
                        "SELECT dt.*, m.nama_makanan 
                         FROM detail_transaksi dt 
                         JOIN menu m ON dt.id_menu = m.id_menu 
                         WHERE dt.id_transaksi = {$row['id_transaksi']}"
                    );
                    $items = [];
                    while ($d = mysqli_fetch_assoc($details)) {
                        $items[] = $d['nama_makanan'] . ' (x' . $d['qty'] . ')';
                    }
                    echo implode(', ', $items);
                    ?>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php if (mysqli_num_rows($laporan) == 0): ?>
            <tr><td colspan="6" style="text-align:center;">Tidak ada data transaksi</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>