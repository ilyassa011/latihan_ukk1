<?php
session_start();
require_once '../koneksi.php';

// Cek hak akses peminjam
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'peminjam') {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['user']['id_user'] ?? $_SESSION['user']['id'] ?? null;
$error = '';
$success = '';

// PROSES PENGEMBALIAN ALAT
if (isset($_POST['kembalikan'])) {
    $id_peminjaman = mysqli_real_escape_string($koneksi, $_POST['id_peminjaman']);
    $tanggal_dikembalikan = date('Y-m-d'); // Tanggal aktual saat dikembalikan
    $status_pengembalian = mysqli_real_escape_string($koneksi, $_POST['status_pengembalian']);

    mysqli_begin_transaction($koneksi);

    try {
        // Ambil detail alat, jumlah, dan rencana tanggal kembali untuk cek denda
        $q_detail = mysqli_query($koneksi, "SELECT dp.id_alat, dp.jumlah, a.nama_alat, p.tanggal_kembali_rencana 
                                             FROM detail_peminjaman dp 
                                             JOIN alat a ON dp.id_alat = a.id_alat 
                                             JOIN peminjaman p ON dp.id_peminjaman = p.id_peminjaman
                                             WHERE dp.id_peminjaman = '$id_peminjaman'");
        $detail = mysqli_fetch_assoc($q_detail);

        if ($detail) {
            $id_alat = $detail['id_alat'];
            $jumlah = $detail['jumlah'];
            $nama_alat = $detail['nama_alat'];
            $tgl_rencana = $detail['tanggal_kembali_rencana'];

            // Hitung denda otomatis jika tanggal pengembalian melebihi rencana (Opsional: Rp 10.000 / hari)
            $denda = 0;
            if ($tanggal_dikembalikan > $tgl_rencana) {
                $datetime1 = new DateTime($tgl_rencana);
                $datetime2 = new DateTime($tanggal_dikembalikan);
                $interval = $datetime1->diff($datetime2);
                $selisih_hari = $interval->days;
                $denda = $selisih_hari * 10000; 
            }

            // 1. Update status peminjaman menjadi 'selesai' (Tanpa tanggal_kembali_real)
            $q_update = "UPDATE peminjaman 
                         SET status = 'selesai' 
                         WHERE id_peminjaman = '$id_peminjaman' AND id_peminjam = '$id_user'";
            mysqli_query($koneksi, $q_update);

            // 2. Kembalikan stok alat ke database
            mysqli_query($koneksi, "UPDATE alat SET stok = stok + $jumlah WHERE id_alat = '$id_alat'");

            // 3. Masukkan data ke tabel pengembalian sesuai struktur database Anda
            $q_pengembalian = "INSERT INTO pengembalian (id_peminjaman, tanggal_dikembalikan, denda, status_pengembalian) 
                              VALUES ('$id_peminjaman', '$tanggal_dikembalikan', '$denda', '$status_pengembalian')";
            mysqli_query($koneksi, $q_pengembalian);

            mysqli_commit($koneksi);

            // --- CATAT LOG AKTIVITAS ---
            if (function_exists('catat_log')) {
                catat_log($koneksi, $id_user, "Mengembalikan alat: " . $nama_alat . " (Status: $status_pengembalian)");
            }

            $_SESSION['flash_success'] = "Alat <strong>" . htmlspecialchars($nama_alat) . "</strong> berhasil dikembalikan!";
            header("Location: pengembalian.php");
            exit;
        } else {
            mysqli_rollback($koneksi);
            $_SESSION['flash_error'] = "Data peminjaman tidak ditemukan.";
            header("Location: pengembalian.php");
            exit;
        }
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        $_SESSION['flash_error'] = "Terjadi kesalahan saat memproses pengembalian: " . $e->getMessage();
        header("Location: pengembalian.php");
        exit;
    }
}

$success = $_SESSION['flash_success'] ?? ''; unset($_SESSION['flash_success']);
$error = $_SESSION['flash_error'] ?? ''; unset($_SESSION['flash_error']);

// Ambil daftar alat yang sedang dipinjam (Status: disetujui) oleh user ini
$query_pinjam = "SELECT p.*, dp.jumlah, dp.id_alat, a.nama_alat, k.nama_kategori 
                 FROM peminjaman p
                 JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
                 JOIN alat a ON dp.id_alat = a.id_alat
                 LEFT JOIN kategori k ON a.id_kategori = k.id_kategori
                 WHERE p.id_peminjam = '$id_user' AND p.status = 'disetujui'
                 ORDER BY p.id_peminjaman DESC";
$result_pinjam = mysqli_query($koneksi, $query_pinjam);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengembalian Alat - Peminjam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Navbar Peminjam -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">Dashboard Peminjam</a>
            <div class="navbar-nav me-auto ms-3">
                <a class="nav-link" href="dashboard.php">Daftar Alat</a>
                <a class="nav-link active" href="pengembalian.php">Pengembalian Alat</a>
                <a class="nav-link" href="riwayat.php">Riwayat Peminjaman</a>
            </div>
            <div class="d-flex align-items-center">
                <span class="text-white me-3 small">Halo, <?= htmlspecialchars($_SESSION['user']['nama_lengkap']); ?></span>
                <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Konten Utama -->
    <div class="container my-5">
        <h3 class="mb-4">Form & Daftar Pengembalian Alat</h3>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                <?= $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                <?= $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Alat</th>
                                <th>Kategori</th>
                                <th>Tanggal Pinjam</th>
                                <th>Rencana Kembali</th>
                                <th>Jumlah</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_pinjam && mysqli_num_rows($result_pinjam) > 0): ?>
                                <?php $no = 1; while ($row = mysqli_fetch_assoc($result_pinjam)): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($row['nama_alat']); ?></td>
                                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars($row['nama_kategori'] ?? 'Umum'); ?></span></td>
                                        <td><?= $row['tanggal_pinjam']; ?></td>
                                        <td><span class="text-danger fw-semibold"><?= $row['tanggal_kembali_rencana']; ?></span></td>
                                        <td><span class="badge bg-secondary"><?= $row['jumlah']; ?></span></td>
                                        <td>
                                            <!-- Tombol Trigger Modal Pengembalian -->
                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalKembali<?= $row['id_peminjaman']; ?>">
                                                Kembalikan Alat
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- MODAL FORM PENGEMBALIAN PER ALAT -->
                                    <div class="modal fade" id="modalKembali<?= $row['id_peminjaman']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="" method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Konfirmasi Pengembalian</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id_peminjaman" value="<?= $row['id_peminjaman']; ?>">
                                                        
                                                        <p class="mb-2">Anda akan mengembalikan alat: <strong><?= htmlspecialchars($row['nama_alat']); ?></strong></p>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label text-secondary small">Batas Rencana Tanggal Kembali</label>
                                                            <input type="text" class="form-control bg-white" value="<?= $row['tanggal_kembali_rencana']; ?>" disabled>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label text-secondary small">Tanggal Pengembalian (Hari Ini)</label>
                                                            <input type="text" class="form-control bg-white" value="<?= date('Y-m-d'); ?>" disabled>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Status / Kondisi Alat</label>
                                                            <select name="status_pengembalian" class="form-select" required>
                                                                <option value="baik" selected>Baik</option>
                                                                <option value="rusak">Rusak</option>
                                                                <option value="hilang">Hilang</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" name="kembalikan" class="btn btn-success btn-sm">Konfirmasi Pengembalian</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Tidak ada alat yang sedang dipinjam saat ini.
                                    </td>
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