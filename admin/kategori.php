<?php
session_start();
require_once '../koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['user']['id_user'] ?? $_SESSION['user']['id'] ?? null;

// PROSES TAMBAH KATEGORI
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);

    if (!empty($nama)) {
        mysqli_begin_transaction($koneksi);
        try {
            // Simpan kategori baru
            mysqli_query($koneksi, "INSERT INTO kategori (nama_kategori) VALUES ('$nama')");
            
            // Catat log aktivitas admin
            if (function_exists('catat_log')) {
                catat_log($koneksi, $id_user, "Menambahkan kategori baru: " . $nama);
            }

            mysqli_commit($koneksi);
            $_SESSION['flash_success'] = "Kategori berhasil ditambahkan!";
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $_SESSION['flash_error'] = "Gagal menambahkan kategori.";
        }
    }
    header("Location: kategori.php");
    exit;
}

// PROSES HAPUS KATEGORI
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // Ambil nama kategori untuk log
    $q_kat = mysqli_query($koneksi, "SELECT nama_kategori FROM kategori WHERE id_kategori = '$id'");
    $d_kat = mysqli_fetch_assoc($q_kat);
    $nama_kat = $d_kat['nama_kategori'] ?? 'Kategori';

    mysqli_begin_transaction($koneksi);
    try {
        mysqli_query($koneksi, "DELETE FROM kategori WHERE id_kategori = '$id'");
        
        if (function_exists('catat_log')) {
            catat_log($koneksi, $id_user, "Menghapus kategori: " . $nama_kat);
        }

        mysqli_commit($koneksi);
        $_SESSION['flash_success'] = "Kategori berhasil dihapus!";
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        $_SESSION['flash_error'] = "Gagal menghapus kategori (mungkin masih digunakan oleh alat).";
    }
    header("Location: kategori.php");
    exit;
}

$success = $_SESSION['flash_success'] ?? ''; unset($_SESSION['flash_success']);
$error = $_SESSION['flash_error'] ?? ''; unset($_SESSION['flash_error']);

$result = mysqli_query($koneksi, "SELECT * FROM kategori");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Kategori - Admin</title>
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
                <a class="nav-link active" href="kategori.php">Kategori</a>
                <a class="nav-link" href="user.php">User</a>
                <a class="nav-link" href="log_aktivitas.php">Log aktivitas</a>
            </div>
            <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container my-4">
        <h3>Kelola Kategori</h3>

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
        
        <form action="" method="POST" class="row g-3 mb-4">
            <div class="col-auto">
                <input type="text" name="nama_kategori" class="form-control" placeholder="Nama Kategori Baru" required>
            </div>
            <div class="col-auto">
                <button type="submit" name="tambah" class="btn btn-primary">Tambah Kategori</button>
            </div>
        </form>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="10%">No</th>
                            <th>Nama Kategori</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['nama_kategori']); ?></td>
                                <td>
                                    <a href="kategori.php?hapus=<?= $row['id_kategori']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus kategori ini?')">Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">Belum ada kategori.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
