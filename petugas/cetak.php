<?php
session_start();
require_once '../koneksi.php';

// Cek apakah user sudah login dan berstatus 'petugas'
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'petugas') {
    header("Location: login.php");
    exit;
}

// Ambil data lengkap untuk cetak laporan
$query_laporan = "SELECT 
                    peminjaman.*, 
                    users.nama_lengkap AS nama_peminjam,
                    alat.nama_alat,
                    detail_peminjaman.jumlah
                  FROM peminjaman 
                  LEFT JOIN users ON peminjaman.id_peminjam = users.id_user
                  LEFT JOIN detail_peminjaman ON peminjaman.id_peminjaman = detail_peminjaman.id_peminjaman
                  LEFT JOIN alat ON detail_peminjaman.id_alat = alat.id_alat
                  ORDER BY peminjaman.id_peminjaman DESC";
$result_laporan = mysqli_query($koneksi, $query_laporan);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Peminjaman Alat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: white !important;
            color: black;
        }
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="container my-4">
        <!-- Kop Laporan -->
        <div class="text-center mb-4">
            <h3 class="fw-bold">LAPORAN PEMINJAMAN ALAT</h3>
            <p class="text-muted">Sistem Peminjaman Alat</p>
            <hr>
        </div>

        <!-- Info Petugas & Tanggal Cetak -->
        <div class="d-flex justify-content-between mb-3">
            <div>
                <span class="fw-semibold">Dicetak oleh Petugas:</span> <?= htmlspecialchars($_SESSION['user']['nama_lengkap']); ?>
            </div>
            <div>
                <span class="fw-semibold">Tanggal:</span> <?= date('d-m-Y'); ?>
            </div>
        </div>

        <!-- Tabel Data Laporan -->
        <div class="table-responsive">
            <table class="table table-bordered border-dark align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th>No</th>
                        <th>Nama Peminjam</th>
                        <th>Nama Alat</th>
                        <th>Jumlah</th>
                        <th>Tgl Pinjam</th>
                        <th>Rencana Kembali</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_laporan && mysqli_num_rows($result_laporan) > 0): ?>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result_laporan)): ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['nama_peminjam'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($row['nama_alat'] ?? '-'); ?></td>
                                <td class="text-center"><?= $row['jumlah'] ?? 0; ?> Unit</td>
                                <td class="text-center"><?= $row['tanggal_pinjam']; ?></td>
                                <td class="text-center"><?= $row['tanggal_kembali_rencana']; ?></td>
                                <td class="text-center fw-semibold"><?= ucfirst($row['status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada data laporan peminjaman.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Tombol Navigasi (Hilang saat diprint) -->
        <div class="mt-4 no-print text-center">
            <a href="laporan.php" class="btn btn-secondary btn-sm">← Kembali ke Laporan</a>
            <button onclick="window.print()" class="btn btn-primary btn-sm">🖨️ Cetak Ulang</button>
        </div>
    </div>

</body>
</html>