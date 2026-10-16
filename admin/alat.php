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

// 1. PROSES TAMBAH ALAT
if (isset($_POST['tambah'])) {
    $nama_alat = mysqli_real_escape_string($koneksi, $_POST['nama_alat']);
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $stok = mysqli_real_escape_string($koneksi, $_POST['stok']);
    $kondisi = mysqli_real_escape_string($koneksi, $_POST['kondisi']);

    if (!empty($nama_alat) && $stok !== '' && !empty($kondisi)) {
        mysqli_begin_transaction($koneksi);
        try {
            $query = "INSERT INTO alat (nama_alat, id_kategori, stok, kondisi) 
                      VALUES ('$nama_alat', '$id_kategori', '$stok', '$kondisi')";
            mysqli_query($koneksi, $query);

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

// 2. PROSES UPDATE / EDIT ALAT
if (isset($_POST['update'])) {
    $id_alat = mysqli_real_escape_string($koneksi, $_POST['id_alat']);
    $nama_alat = mysqli_real_escape_string($koneksi, $_POST['nama_alat']);
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $stok = mysqli_real_escape_string($koneksi, $_POST['stok']);
    $kondisi = mysqli_real_escape_string($koneksi, $_POST['kondisi']);

    if (!empty($nama_alat) && $stok !== '' && !empty($kondisi)) {
        mysqli_begin_transaction($koneksi);
        try {
            $query = "UPDATE alat SET 
                      nama_alat = '$nama_alat', 
                      id_kategori = '$id_kategori', 
                      stok = '$stok', 
                      kondisi = '$kondisi' 
                      WHERE id_alat = '$id_alat'";
            mysqli_query($koneksi, $query);

            if (function_exists('catat_log')) {
                catat_log($koneksi, $id_user, "Mengubah data alat: " . $nama_alat);
            }

            mysqli_commit($koneksi);
            $_SESSION['flash_success'] = "Data alat berhasil diperbarui!";
            header("Location: alat.php");
            exit;
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $error = "Gagal memperbarui data alat: " . $e->getMessage();
        }
    } else {
        $error = "Semua field wajib diisi!";
    }
}

// 3. PROSES HAPUS ALAT
if (isset($_GET['hapus'])) {
    $id_alat = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    $q_alat = mysqli_query($koneksi, "SELECT nama_alat FROM alat WHERE id_alat = '$id_alat'");
    $d_alat = mysqli_fetch_assoc($q_alat);
    $nama_alat = $d_alat['nama_alat'] ?? 'Alat';

    mysqli_begin_transaction($koneksi);
    try {
        mysqli_query($koneksi, "DELETE FROM alat WHERE id_alat = '$id_alat'");

        if (function_exists('catat_log')) {
            catat_log($koneksi, $id_user, "Menghapus alat: " . $nama_alat);
        }

        mysqli_commit($koneksi);
        $_SESSION['flash_success'] = "Alat berhasil dihapus!";
        header("Location: alat.php");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        $_SESSION['flash_error'] = "Gagal menghapus alat (mungkin sedang digunakan dalam peminjaman aktif).";
        header("Location: alat.php");
        exit;
    }
}

// Ambil data untuk mode edit jika parameter ?edit=id tersedia
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_edit = mysqli_real_escape_string($koneksi, $_GET['edit']);
    $q_edit = mysqli_query($koneksi, "SELECT * FROM alat WHERE id_alat = '$id_edit'");
    $edit_data = mysqli_fetch_assoc($q_edit);
}

$success = $_SESSION['flash_success'] ?? ''; unset($_SESSION['flash_success']);
$flash_error = $_SESSION['flash_error'] ?? ''; unset($_SESSION['flash_error']);

// Ambil data alat & kategori untuk tabel
$query = "SELECT alat.*, kategori.nama_kategori FROM alat LEFT JOIN kategori ON alat.id_kategori = kategori.id_kategori ORDER BY alat.id_alat DESC";
$result = mysqli_query($koneksi, $query);
$kategori_result = mysqli_query($koneksi, "SELECT * FROM kategori");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Alat - AdminPanel</title>
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
        <h3 class="mb-4">Kelola Data Alat</h3>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                <?= $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($flash_error || $error): ?>
            <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                <?= $flash_error ?: $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Kolom Form (Dinamis: Tambah atau Edit) -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header <?= $edit_data ? 'bg-warning text-dark' : 'bg-primary text-white'; ?> py-3">
                        <h5 class="mb-0 fw-bold"><?= $edit_data ? 'Edit Data Alat' : 'Tambah Alat Baru'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <?php if ($edit_data): ?>
                                <input type="hidden" name="id_alat" value="<?= $edit_data['id_alat']; ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Nama Alat</label>
                                <input type="text" name="nama_alat" class="form-control form-control-sm" placeholder="Contoh: Proyektor" value="<?= $edit_data['nama_alat'] ?? ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Kategori</label>
                                <select name="id_kategori" class="form-select form-select-sm" required>
                                    <option value="" disabled>Pilih Kategori</option>
                                    <?php 
                                    // Reset pointer kategori result
                                    mysqli_data_seek($kategori_result, 0);
                                    while ($kat = mysqli_fetch_assoc($kategori_result)): 
                                        $selected = ($edit_data && $edit_data['id_kategori'] == $kat['id_kategori']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $kat['id_kategori']; ?>" <?= $selected; ?>><?= htmlspecialchars($kat['nama_kategori']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Stok</label>
                                <input type="number" name="stok" class="form-control form-control-sm" placeholder="0" min="0" value="<?= $edit_data['stok'] ?? ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Kondisi</label>
                                <select name="kondisi" class="form-select form-select-sm" required>
                                    <?php $kondisi_val = $edit_data['kondisi'] ?? 'Baik'; ?>
                                    <option value="Baik" <?= ($kondisi_val == 'Baik') ? 'selected' : ''; ?>>Baik</option>
                                    <option value="Rusak Ringan" <?= ($kondisi_val == 'Rusak Ringan') ? 'selected' : ''; ?>>Rusak Ringan</option>
                                    <option value="Rusak Berat" <?= ($kondisi_val == 'Rusak Berat') ? 'selected' : ''; ?>>Rusak Berat</option>
                                </select>
                            </div>

                            <div class="d-flex gap-2">
                                <?php if ($edit_data): ?>
                                    <a href="alat.php" class="btn btn-secondary btn-sm w-50">Batal</a>
                                    <button type="submit" name="update" class="btn btn-warning btn-sm w-50 fw-semibold">Perbarui</button>
                                <?php else: ?>
                                    <button type="submit" name="tambah" class="btn btn-primary btn-sm w-100">Simpan Alat</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Kolom Tabel Daftar Alat (Read) -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">Daftar Alat Tersedia</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Alat</th>
                                        <th>Kategori</th>
                                        <th>Stok</th>
                                        <th>Kondisi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td class="fw-bold"><?= htmlspecialchars($row['nama_alat']); ?></td>
                                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($row['nama_kategori'] ?? 'Umum'); ?></span></td>
                                                <td><span class="badge bg-secondary"><?= $row['stok']; ?></span></td>
                                                <td><?= htmlspecialchars($row['kondisi']); ?></td>
                                                <td>
                                                    <a href="alat.php?edit=<?= $row['id_alat']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                                    <a href="alat.php?hapus=<?= $row['id_alat']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus alat ini?')">Hapus</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">Belum ada data alat.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
