<?php
require_once '../koneksi.php';

// Cek hak akses peminjam
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'peminjam') {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['user']['id_user'] ?? $_SESSION['user']['id'] ?? null;

// PROSES MENGAJUKAN PEMINJAMAN
if (isset($_POST['pinjam'])) {
    $id_alat = mysqli_real_escape_string($koneksi, $_POST['id_alat']);
    $tanggal_pinjam = date('Y-m-d');
    $tgl_kembali = mysqli_real_escape_string($koneksi, $_POST['tanggal_kembali_rencana']);
    $jumlah_pinjam = 1;

    mysqli_begin_transaction($koneksi);

    try {
        $q_cek = mysqli_query($koneksi, "SELECT stok, nama_alat FROM alat WHERE id_alat = '$id_alat' FOR UPDATE");
        $data_alat = mysqli_fetch_assoc($q_cek);

        if ($data_alat && $data_alat['stok'] >= $jumlah_pinjam) {
            $q_insert = "INSERT INTO peminjaman (id_peminjam, tanggal_pinjam, tanggal_kembali_rencana, status) 
                         VALUES ('$id_user', '$tanggal_pinjam', '$tgl_kembali', 'pending')";
            mysqli_query($koneksi, $q_insert);
            $id_peminjaman_baru = mysqli_insert_id($koneksi);

            mysqli_query($koneksi, "INSERT INTO detail_peminjaman (id_peminjaman, id_alat, jumlah) VALUES ('$id_peminjaman_baru', '$id_alat', '$jumlah_pinjam')");
            mysqli_query($koneksi, "UPDATE alat SET stok = stok - $jumlah_pinjam WHERE id_alat = '$id_alat'");

            mysqli_commit($koneksi);

            // --- SISIPKAN CATAT LOG DI SINI ---
            catat_log($koneksi, $id_user, "Mengajukan peminjaman alat: " . $data_alat['nama_alat']);

            $_SESSION['flash_success'] = "Pengajuan peminjaman untuk " . htmlspecialchars($data_alat['nama_alat']) . " berhasil dikirim!";
            header("Location: dashboard.php");
            exit;
        } else {
            mysqli_rollback($koneksi);
            $_SESSION['flash_error'] = "Gagal: Stok alat tidak mencukupi.";
            header("Location: dashboard.php");
            exit;
        }
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        $_SESSION['flash_error'] = "Terjadi kesalahan sistem.";
        header("Location: dashboard.php");
        exit;
    }
}

$success = $_SESSION['flash_success'] ?? ''; unset($_SESSION['flash_success']);
$error = $_SESSION['flash_error'] ?? ''; unset($_SESSION['flash_error']);

$alat_result = mysqli_query($koneksi, "SELECT alat.*, kategori.nama_kategori FROM alat LEFT JOIN kategori ON alat.id_kategori = kategori.id_kategori");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Peminjam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
   <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="peminjam.php">Dashboard Peminjam</a>
            <div class="d-flex align-items-center">
                <a href="dashboard.php" class="btn btn-light btn-sm me-2">Daftar Alat</a>
                <a href="riwayat.php" class="btn btn-light btn-sm text-primary me-3 fw-semibold">Riwayat Peminjaman</a>
                <span class="text-white me-3 small">Halo, <?= htmlspecialchars($_SESSION['user']['nama_lengkap']); ?></span>
                <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h3 class="mb-4">Daftar Alat Tersedia</h3>
        <?php if ($success): ?><div class="alert alert-success py-2"><?= $success; ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger py-2"><?= $error; ?></div><?php endif; ?>

        <div class="row">
            <?php while ($alat = mysqli_fetch_assoc($alat_result)): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <span class="badge bg-info text-dark mb-2"><?= htmlspecialchars($alat['nama_kategori'] ?? 'Umum'); ?></span>
                                <h5 class="card-title fw-bold"><?= htmlspecialchars($alat['nama_alat']); ?></h5>
                                <p class="card-text mb-1"><strong>Stok:</strong> <span class="badge bg-secondary"><?= $alat['stok']; ?></span></p>
                                <p class="card-text text-muted small mb-3">Kondisi: <?= htmlspecialchars($alat['kondisi']); ?></p>
                            </div>
                            <form action="dashboard.php" method="POST" class="mt-auto">
                                <input type="hidden" name="id_alat" value="<?= $alat['id_alat']; ?>">
                                <div class="mb-2">
                                    <label class="form-label small text-secondary">Rencana Tanggal Kembali:</label>
                                    <input type="date" name="tanggal_kembali_rencana" class="form-control form-control-sm" required min="<?= date('Y-m-d'); ?>">
                                </div>
                                <button type="submit" name="pinjam" class="btn btn-outline-primary btn-sm w-100" <?= ($alat['stok'] <= 0) ? 'disabled' : ''; ?>>
                                    <?= ($alat['stok'] > 0) ? 'Pinjam Alat' : 'Stok Habis'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>