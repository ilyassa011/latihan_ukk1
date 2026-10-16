<?php
session_start();
require_once 'koneksi.php'; // Hubungkan dengan file koneksi database Anda

// Ambil data alat dari database db_alat
$query = "SELECT alat.*, kategori.nama_kategori 
          FROM alat 
          LEFT JOIN kategori ON alat.id_kategori = kategori.id_kategori";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Peminjaman Alat</title>
    <!-- Menggunakan Bootstrap 5 CDN untuk tampilan simpel dan rapi -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Header / Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">E-Peminjaman Alat</a>
            <div>
                <?php if (isset($_SESSION['user'])): ?>
                    <!-- Tombol Dashboard menyesuaikan role user yang login -->
                    <?php if ($_SESSION['role'] === 'petugas'): ?>
                        <a href="petugas/petugas.php" class="btn btn-light text-primary me-2 fw-semibold">Dashboard Petugas</a>
                    <?php else: ?>
                        <a href="peminjam.php" class="btn btn-light text-primary me-2 fw-semibold">Dashboard Peminjam</a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-outline-light">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-light text-primary fw-semibold">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Daftar Alat -->
    <div class="container mb-5">
        <h3 class="mb-4">Daftar Alat Tersedia</h3>
        <div class="row">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body">
                                <span class="badge bg-info text-dark mb-2"><?= htmlspecialchars($row['nama_kategori'] ?? 'Umum'); ?></span>
                                <h5 class="card-title fw-bold"><?= htmlspecialchars($row['nama_alat']); ?></h5>
                                <p class="card-text mb-1"><strong>Stok:</strong> <?= $row['stok']; ?></p>
                                <p class="card-text"><strong>Kondisi:</strong> <?= htmlspecialchars($row['kondisi']); ?></p>
                            </div>
                            <div class="card-footer bg-white border-0 pb-3">
                                <!-- Pengecekan Login saat ingin meminjam alat -->
                                <?php if (isset($_SESSION['user'])): ?>
                                    <?php if ($_SESSION['role'] === 'peminjam'): ?>
                                        <a href="peminjam.php" class="btn btn-outline-primary btn-sm w-100">Pinjam Alat</a>
                                    <?php else: ?>
                                        <span class="text-muted small d-block text-center">Akun Petugas tidak dapat meminjam alat</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <!-- Jika belum login, langsung diarahkan ke login.php -->
                                    <a href="login.php" class="btn btn-outline-primary btn-sm w-100">Pinjam Alat</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted">
                    <p>Belum ada data alat yang tersedia.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>