<?php
session_start();
require_once '../koneksi.php';

// Cek apakah user sudah login dan berstatus 'peminjam'
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'peminjam') {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['user']['id_user'];

// Ambil Riwayat Peminjaman berdasarkan id_peminjam yang merujuk ke id_user
$riwayat_result = mysqli_query($koneksi, "SELECT peminjaman.*, alat.nama_alat 
    FROM peminjaman 
    LEFT JOIN detail_peminjaman ON peminjaman.id_peminjaman = detail_peminjaman.id_peminjaman 
    LEFT JOIN alat ON detail_peminjaman.id_alat = alat.id_alat 
    WHERE peminjaman.id_peminjam = '$id_user' 
    ORDER BY peminjaman.id_peminjaman DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman - Peminjam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Navbar Peminjam -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="peminjam.php">Dashboard Peminjam</a>
            <div class="d-flex align-items-center">
                <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Daftar Alat</a>
                <a href="riwayat.php" class="btn btn-light btn-sm text-primary me-3 fw-semibold">Riwayat Peminjaman</a>
                <span class="text-white me-3 small">Halo, <?= htmlspecialchars($_SESSION['user']['nama_lengkap']); ?></span>
                <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Konten Utama: Riwayat -->
    <div class="container my-5">
        <h3 class="mb-4">Riwayat & Status Peminjaman Anda</h3>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Alat</th>
                                <th>Tgl Pinjam</th>
                                <th>Rencana Kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($riwayat_result && mysqli_num_rows($riwayat_result) > 0): ?>
                                <?php $no = 1; while ($row = mysqli_fetch_assoc($riwayat_result)): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($row['nama_alat'] ?? '-'); ?></td>
                                        <td><?= $row['tanggal_pinjam']; ?></td>
                                        <td><?= $row['tanggal_kembali_rencana']; ?></td>
                                        <td>
                                            <?php 
                                                $status = strtolower(trim($row['status'] ?? 'pending'));
                                                
                                                if ($status == 'disetujui') {
                                                    echo '<span style="background-color: #198754; color: #ffffff; padding: 5px 10px; border-radius: 4px; font-weight: bold; display: inline-block;">Disetujui</span>';
                                                } elseif ($status == 'ditolak') {
                                                    echo '<span style="background-color: #dc3545; color: #ffffff; padding: 5px 10px; border-radius: 4px; font-weight: bold; display: inline-block;">Ditolak</span>';
                                                } elseif ($status == 'pending') {
                                                    echo '<span style="background-color: #ffc107; color: #000000; padding: 5px 10px; border-radius: 4px; font-weight: bold; display: inline-block;">Pending</span>';
                                                } else {
                                                    echo '<span style="background-color: #6c757d; color: #ffffff; padding: 5px 10px; border-radius: 4px; font-weight: bold; display: inline-block;">' . ucfirst($row['status']) . '</span>';
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada riwayat peminjaman.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>