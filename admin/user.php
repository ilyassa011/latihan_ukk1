<?php
session_start();
require_once '../koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Hapus User
if (isset($_GET['hapus'])) {
    $id_user = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM users WHERE id_user = '$id_user'");
    header("Location: user.php");
    exit;
}

$result = mysqli_query($koneksi, "SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola User - Admin</title>
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
                <a class="nav-link" href="alat.php">Alat</a>
                <a class="nav-link" href="kategori.php">Kategori</a>
                <a class="nav-link active" href="user.php">User</a>
                <a class="nav-link" href="log_aktivitas.php">Log aktivitas</a>
            </div>
            <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container my-4">
        <h3>Kelola Pengguna Sistem</h3>
        <a href="tambah_user.php" class="btn btn-primary btn-sm mb-3">+ Tambah User</a>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['username']); ?></td>
                            <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><span class="badge bg-secondary"><?= ucfirst($row['role']); ?></span></td>
                            <td>
                                <a href="user.php?hapus=<?= $row['id_user']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus user ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>