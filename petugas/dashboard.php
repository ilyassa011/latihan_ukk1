<?php
session_start();
require_once '../koneksi.php';

// Cek apakah user sudah login dan berstatus 'petugas'
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'petugas') {
    header("Location: ../login.php");
    exit;
}

// Proses Setujui / Tolak Peminjaman
if (isset($_POST['update_status'])) {
    $id_peminjaman = mysqli_real_escape_string($koneksi, $_POST['id_peminjaman']);
    $status = mysqli_real_escape_string($koneksi, $_POST['update_status']); 
    $id_petugas = $_SESSION['user']['id_user'];

    $query_update = "UPDATE peminjaman SET status = '$status', id_petugas = '$id_petugas' WHERE id_peminjaman = '$id_peminjaman'";
    
    if (mysqli_query($koneksi, $query_update)) {
        // Catat log aktivitas verifikasi petugas
        catat_log($koneksi, $_SESSION['user']['id_user'], "Memverifikasi peminjaman ID $id_peminjaman dengan status: $status");
    }

    header("Location: dashboard.php");
    exit;
}

// Ambil data peminjaman (berelasi ke users.id_user)
$query = "SELECT peminjaman.*, users.nama_lengkap AS nama_peminjam 
          FROM peminjaman 
          LEFT JOIN users ON peminjaman.id_peminjam = users.id_user 
          ORDER BY peminjaman.id_peminjaman DESC";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Navbar Petugas -->
    <nav class="navbar navbar-dark bg-secondary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">Dashboard Petugas</a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Halo, <?= htmlspecialchars($_SESSION['user']['nama_lengkap'] ?? 'Petugas'); ?></span>
                <a href="laporan.php" class="btn btn-outline-light btn-sm me-2">Laporan</a>
                <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Konten Utama -->
    <div class="container my-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-bold">
                Persetujuan Peminjaman Alat
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Peminjam</th>
                                <th>Tgl Pinjam</th>
                                <th>Rencana Kembali</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                    <?php 
                                        $status = trim(strtolower($row['status'] ?? 'pending'));
                                    ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama_peminjam'] ?? '-'); ?></td>
                                        <td><?= $row['tanggal_pinjam']; ?></td>
                                        <td><?= $row['tanggal_kembali_rencana']; ?></td>
                                        <td>
                                            <?php 
                                                $badge_bg = 'bg-secondary';
                                                if ($status == 'disetujui') $badge_bg = 'bg-success';
                                                elseif ($status == 'ditolak') $badge_bg = 'bg-danger';
                                                elseif ($status == 'pending') $badge_bg = 'bg-warning text-dark';
                                            ?>
                                            <span class="badge <?= $badge_bg; ?> px-2 py-1">
                                                <?= ucfirst($row['status'] ?? 'Pending'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($status == 'pending'): ?>
                                                <form action="dashboard.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="id_peminjaman" value="<?= $row['id_peminjaman']; ?>">
                                                    <button type="submit" name="update_status" value="disetujui" class="btn btn-success btn-sm" onclick="return confirm('Setujui peminjaman ini?')">Setujui</button>
                                                    <button type="submit" name="update_status" value="ditolak" class="btn btn-danger btn-sm" onclick="return confirm('Tolak peminjaman ini?')">Tolak</button>
                                                </form>
                                            <?php elseif ($status == 'disetujui'): ?>
                                                <span class="text-success small fw-bold">Sedang Dipinjam</span>
                                            <?php elseif ($status == 'ditolak'): ?>
                                                <span class="text-danger small fw-bold">Ditolak</span>
                                            <?php else: ?>
                                                <span class="text-muted small fw-bold">Selesai</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada data peminjaman.</td>
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