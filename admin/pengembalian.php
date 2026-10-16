<?php
session_start();
require_once '../koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$query = "SELECT pengembalian.*, peminjaman.tanggal_pinjam, users.nama_lengkap AS nama_peminjam 
          FROM pengembalian 
          LEFT JOIN peminjaman ON pengembalian.id_peminjaman = peminjaman.id_peminjaman 
          LEFT JOIN users ON peminjaman.id_peminjam = users.id_user 
          ORDER BY pengembalian.id_pengembalian DESC";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pengembalian - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">AdminPanel</a>
            <div class="navbar-nav me-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="peminjaman.php">Peminjaman</a>
                <a class="nav-link active" href="pengembalian.php">Pengembalian</a>
                <a class="nav-link" href="alat.php">Alat</a>
                <a class="nav-link" href="kategori.php">Kategori</a>
                <a class="nav-link" href="user.php">User</a>
                <a class="nav-link" href="log_aktivitas.php">Log aktivitas</a>
            </div>
            <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container my-4">
        <h3>Daftar Pengembalian Alat</h3>
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-body">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Peminjam</th>
                            <th>Tgl Dikembalikan</th>
                            <th>Denda</th>
                            <th>Status Pengembalian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($row['nama_peminjam'] ?? '-'); ?></td>
                                    <td><?= $row['tanggal_dikembalikan']; ?></td>
                                    <td>Rp <?= number_format($row['denda'], 0, ',', '.'); ?></td>
                                    <td><span class="badge bg-success"><?= ucfirst($row['status_pengembalian']); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted">Belum ada data pengembalian.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>