<?php
session_start();
require_once '../koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Proses Hapus Alat
if (isset($_GET['hapus'])) {
    $id_alat = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM alat WHERE id_alat = '$id_alat'");
    header("Location: alat.php");
    exit;
}

// Ambil data alat & kategori
$query = "SELECT alat.*, kategori.nama_kategori FROM alat LEFT JOIN kategori ON alat.id_kategori = kategori.id_kategori";
$result = mysqli_query($koneksi, $query);
$kategori_result = mysqli_query($koneksi, "SELECT * FROM kategori");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Alat - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">AdminPanel</a>
            <div class="navbar-nav me-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="peminjaman.php">Peminjaman</a>
                <a class="nav-link" href="pengembalian.php">Pengembalian</a>
                <a class="nav-link active" href="alat.php">Alat</a>
                <a class="nav-link" href="kategori.php">Kategori</a>
                <a class="nav-link" href="user.php">User</a>
                <a class="nav-link" href="log_aktivitas.php">Log aktivitas</a>
            </div>
            <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container my-4">
        <h3>Kelola Data Alat</h3>
        <a href="tambah_alat.php" class="btn btn-primary btn-sm mb-3">+ Tambah Alat</a>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Alat</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Kondisi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($row['nama_alat']); ?></td>
                                    <td><?= htmlspecialchars($row['nama_kategori'] ?? 'Umum'); ?></td>
                                    <td><?= $row['stok']; ?></td>
                                    <td><?= htmlspecialchars($row['kondisi']); ?></td>
                                    <td>
                                        <a href="edit_alat.php?id=<?= $row['id_alat']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="alat.php?hapus=<?= $row['id_alat']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus alat ini?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted">Belum ada data alat.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>