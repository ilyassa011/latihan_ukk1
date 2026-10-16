<?php
session_start();
require_once '../koneksi.php';

// Cek hak akses khusus admin
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil data log beserta informasi user yang melakukan aksi
$query = "SELECT log_aktifitas.*, users.nama_lengkap, users.role 
          FROM log_aktifitas 
          LEFT JOIN users ON log_aktifitas.id_user = users.id_user 
          ORDER BY log_aktifitas.id_log DESC";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Log Aktivitas - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Navbar Admin -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">AdminPanel</a>
            <div class="navbar-nav me-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="peminjaman.php">Peminjaman</a>
                <a class="nav-link" href="pengembalian.php">Pengembalian</a>
                <a class="nav-link" href="alat.php">Alat</a>
                <a class="nav-link" href="kategori.php">Kategori</a>
                <a class="nav-link" href="user.php">User</a>
                <a class="nav-link active" href="log_aktivitas.php">Log Aktivitas</a>
            </div>
            <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </nav>

    <!-- Konten Utama -->
    <div class="container my-4">
        <h3>Log Aktivitas Sistem</h3>
        <p class="text-muted small">Menampilkan seluruh rekam jejak aktivitas user di dalam aplikasi.</p>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama User</th>
                                <th>Role</th>
                                <th>Aktivitas</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama_lengkap'] ?? 'Sistem / Tamu'); ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= ucfirst($row['role'] ?? 'Umum'); ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($row['aktifitas']); ?></td>
                                        <td><?= $row['waktu']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada log aktivitas yang tercatat.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>