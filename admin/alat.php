<?php
session_start();
require_once '../koneksi.php';

// Cek hak akses admin
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['user']['id_user'] ?? $_SESSION['user']['id'] ?? null;
$error = '';

// PROSES TAMBAH ALAT
if (isset($_POST['simpan'])) {
    $nama_alat = mysqli_real_escape_string($koneksi, $_POST['nama_alat']);
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $stok = mysqli_real_escape_string($koneksi, $_POST['stok']);
    $kondisi = mysqli_real_escape_string($koneksi, $_POST['kondisi']);

    if (!empty($nama_alat) && $stok !== '' && !empty($kondisi)) {
        mysqli_begin_transaction($koneksi);

        try {
            // Query insert data alat baru
            $query = "INSERT INTO alat (nama_alat, id_kategori, stok, kondisi) 
                      VALUES ('$nama_alat', '$id_kategori', '$stok', '$kondisi')";
            mysqli_query($koneksi, $query);

            // Catat log aktivitas
            if (function_exists('catat_log')) {
                catat_log($koneksi, $id_user, "Menambahkan alat baru: " . $nama_alat . " (Stok: $stok)");
            }

            mysqli_commit($koneksi);
            $_SESSION['flash_success'] = "Data alat berhasil ditambahkan!";
            header("Location: alat.php");
            exit;
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $error = "Gagal menyimpan data alat: " . $e->getMessage();
        }
    } else {
        $error = "Semua field wajib diisi!";
    }
}

// Ambil daftar kategori untuk dropdown
$kategori_result = mysqli_query($koneksi, "SELECT * FROM kategori");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Alat - Admin</title>
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
                <a class="nav-link active" href="alat.php">Alat</a>
                <a class="nav-link" href="kategori.php">Kategori</a>
                <a class="nav-link" href="user.php">User</a>
                <a class="nav-link" href="log_aktivitas.php">Log aktivitas</a>
            </div>
            <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </nav>

    <!-- Konten Utama -->
    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h4 class="mb-0 fw-bold">Tambah Data Alat</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger py-2" role="alert">
                                <?= $error; ?>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Alat</label>
                                <input type="text" name="nama_alat" class="form-control" placeholder="Contoh: Proyektor Epson" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Kategori</label>
                                <select name="id_kategori" class="form-select" required>
                                    <option value="" disabled selected>Pilih Kategori</option>
                                    <?php while ($kat = mysqli_fetch_assoc($kategori_result)): ?>
                                        <option value="<?= $kat['id_kategori']; ?>"><?= htmlspecialchars($kat['nama_kategori']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Stok Awal</label>
                                <input type="number" name="stok" class="form-control" placeholder="Contoh: 10" min="0" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Kondisi Alat</label>
                                <select name="kondisi" class="form-select" required>
                                    <option value="Baik" selected>Baik</option>
                                    <option value="Rusak Ringan">Rusak Ringan</option>
                                    <option value="Rusak Berat">Rusak Berat</option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="alat.php" class="btn btn-secondary btn-sm">Kembali</a>
                                <button type="submit" name="simpan" class="btn btn-primary btn-sm">Simpan Alat</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
