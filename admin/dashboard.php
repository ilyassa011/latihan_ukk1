<?php
session_start();
require_once '../koneksi.php';

// Cek hak akses admin
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil statistik ringkas
$total_alat = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM alat"))['total'] ?? 0;
$total_user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM users"))['total'] ?? 0;
$total_peminjaman = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM peminjaman"))['total'] ?? 0;

// Ambil data peminjaman
$query = "SELECT p.*, u.nama_lengkap AS nama_peminjam, petugas.nama_lengkap AS nama_petugas 
          FROM peminjaman p
          LEFT JOIN users u ON p.id_peminjam = u.id_user 
          LEFT JOIN users petugas ON p.id_petugas = petugas.id_user 
          ORDER BY p.id_peminjaman DESC";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Navbar Menu Lengkap -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">AdminPanel</a>
            <div class="navbar-nav me-auto">
                <a class="nav-link active" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="peminjaman.php">Peminjaman</a>
                <a class="nav-link" href="pengembalian.php">Pengembalian</a>
                <a class="nav-link" href="alat.php">Alat</a>
                <a class="nav-link" href="kategori.php">Kategori</a>
                <a class="nav-link" href="user.php">User</a>
                <a class="nav-link" href="log_aktivitas.php">Log Aktivitas</a>
            </div>
            <div class="d-flex align-items-center">
                <span class="text-white small me-3"><?= htmlspecialchars($_SESSION['user']['nama_lengkap']); ?></span>
                <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Konten -->
    <div class="container my-4">
        <h4 class="mb-4">Dashboard Admin</h4>

        <!-- Statistik -->
        <div class="row text-white mb-4">
            <div class="col-md-4">
                <div class="card bg-primary p-3">
                    <h6>Total Alat</h6>
                    <h3><?= $total_alat; ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success p-3">
                    <h6>Total User</h6>
                    <h3><?= $total_user; ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-dark p-3">
                    <h6>Total Peminjaman</h6>
                    <h3><?= $total_peminjaman; ?></h3>
                </div>
            </div>
        </div>

        <!-- Tabel Peminjaman -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white fw-bold">Riwayat Peminjaman</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Peminjam</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Petugas</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama_peminjam'] ?? '-'); ?></td>
                                        <td><?= $row['tanggal_pinjam']; ?></td>
                                        <td><?= $row['tanggal_kembali_rencana']; ?></td>
                                        <td><?= htmlspecialchars($row['nama_petugas'] ?? '-'); ?></td>
                                        <td>
                                            <span class="badge bg-secondary"><?= ucfirst($row['status']); ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada data.</td>
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