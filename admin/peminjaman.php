<?php
session_start();
require_once '../koneksi.php';

// Cek hak akses admin
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// 1. PROSES TAMBAH PEMINJAMAN
if (isset($_POST['tambah_peminjaman'])) {
    $id_peminjam = mysqli_real_escape_string($koneksi, $_POST['id_peminjam']);
    $tanggal_pinjam = mysqli_real_escape_string($koneksi, $_POST['tanggal_pinjam']);
    $tanggal_kembali_rencana = mysqli_real_escape_string($koneksi, $_POST['tanggal_kembali_rencana']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    $id_admin = $_SESSION['user']['id_user'];

    $query_tambah = "INSERT INTO peminjaman (id_peminjam, id_petugas, tanggal_pinjam, tanggal_kembali_rencana, status) 
                     VALUES ('$id_peminjam', '$id_admin', '$tanggal_pinjam', '$tanggal_kembali_rencana', '$status')";
    
    if (mysqli_query($koneksi, $query_tambah)) {
        catat_log($koneksi, $id_admin, "Admin menambahkan data peminjaman baru secara manual.");
        header("Location: peminjaman.php?status=sukses_tambah");
        exit;
    }
}

// 2. PROSES EDIT PEMINJAMAN
if (isset($_POST['edit_peminjaman'])) {
    $id_peminjaman = mysqli_real_escape_string($koneksi, $_POST['id_peminjaman']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    $tanggal_kembali_rencana = mysqli_real_escape_string($koneksi, $_POST['tanggal_kembali_rencana']);
    $id_admin = $_SESSION['user']['id_user'];

    $query_edit = "UPDATE peminjaman SET status = '$status', tanggal_kembali_rencana = '$tanggal_kembali_rencana' WHERE id_peminjaman = '$id_peminjaman'";
    
    if (mysqli_query($koneksi, $query_edit)) {
        catat_log($koneksi, $id_admin, "Mengubah data/status peminjaman ID: $id_peminjaman menjadi $status");
        header("Location: peminjaman.php?status=sukses_edit");
        exit;
    }
}

// 3. PROSES HAPUS PEMINJAMAN
if (isset($_GET['hapus'])) {
    $id_peminjaman = $_GET['hapus'];
    
    mysqli_query($koneksi, "DELETE FROM detail_peminjaman WHERE id_peminjaman = '$id_peminjaman'");
    mysqli_query($koneksi, "DELETE FROM peminjaman WHERE id_peminjaman = '$id_peminjaman'");
    
    // --- CATAT LOG HAPUS PEMINJAMAN ---
    catat_log($koneksi, $_SESSION['user']['id_user'], "Menghapus data peminjaman ID: $id_peminjaman");
    
    header("Location: peminjaman.php?status=sukses_hapus");
    exit;
}

// Ambil data peminjaman untuk ditampilkan
$query = "SELECT p.*, u.nama_lengkap AS nama_peminjam, petugas.nama_lengkap AS nama_petugas 
          FROM peminjaman p
          LEFT JOIN users u ON p.id_peminjam = u.id_user 
          LEFT JOIN users petugas ON p.id_petugas = petugas.id_user 
          ORDER BY p.id_peminjaman DESC";
$result = mysqli_query($koneksi, $query);

// Ambil data user yang berstatus 'peminjam' untuk form tambah
$data_peminjam = mysqli_query($koneksi, "SELECT * FROM users WHERE role = 'peminjam'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Peminjaman - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navbar Admin -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">AdminPanel</a>
            <div class="navbar-nav me-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link active" href="peminjaman.php">Peminjaman</a>
                <a class="nav-link" href="pengembalian.php">Pengembalian</a>
                <a class="nav-link" href="alat.php">Alat</a>
                <a class="nav-link" href="kategori.php">Kategori</a>
                <a class="nav-link" href="user.php">User</a>
                <a class="nav-link" href="log_aktivitas.php">Log Aktivitas</a>
            </div>
            <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Kelola Data Peminjaman</h3>
            <!-- Tombol Trigger Modal Tambah -->
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                + Tambah Peminjaman
            </button>
        </div>

        <div class="card shadow-sm border-0 mt-3">
            <div class="card-body">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Peminjam</th>
                            <th>Tgl Pinjam</th>
                            <th>Rencana Kembali</th>
                            <th>Petugas / Admin</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                <?php 
                                    $status = trim(strtolower($row['status']));
                                    $badge_bg = 'bg-secondary';
                                    if ($status == 'disetujui') $badge_bg = 'bg-success';
                                    elseif ($status == 'ditolak') $badge_bg = 'bg-danger';
                                    elseif ($status == 'pending') $badge_bg = 'bg-warning text-dark';
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($row['nama_peminjam'] ?? '-'); ?></td>
                                    <td><?= $row['tanggal_pinjam']; ?></td>
                                    <td><?= $row['tanggal_kembali_rencana']; ?></td>
                                    <td><?= htmlspecialchars($row['nama_petugas'] ?? 'Belum Diverifikasi'); ?></td>
                                    <td>
                                        <span class="badge <?= $badge_bg; ?>">
                                            <?= ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <!-- Tombol Edit (Trigger Modal) -->
                                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_peminjaman']; ?>">
                                            Edit
                                        </button>
                                        <!-- Tombol Hapus -->
                                        <a href="peminjaman.php?hapus=<?= $row['id_peminjaman']; ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Yakin ingin menghapus data peminjaman ini?')">
                                           Hapus
                                        </a>
                                    </td>
                                </tr>

                                <!-- MODAL EDIT PER BARIS -->
                                <div class="modal fade" id="modalEdit<?= $row['id_peminjaman']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="" method="POST">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Peminjaman (ID: <?= $row['id_peminjaman']; ?>)</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="id_peminjaman" value="<?= $row['id_peminjaman']; ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Rencana Tanggal Kembali</label>
                                                        <input type="date" name="tanggal_kembali_rencana" class="form-control" value="<?= $row['tanggal_kembali_rencana']; ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Status Peminjaman</label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="pending" <?= ($status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                            <option value="disetujui" <?= ($status == 'disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                                                            <option value="ditolak" <?= ($status == 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" name="edit_peminjaman" class="btn btn-primary btn-sm">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Belum ada data peminjaman.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH PEMINJAMAN -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Peminjaman Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Pilih Peminjam</label>
                            <select name="id_peminjam" class="form-select" required>
                                <option value="">-- Pilih User Peminjam --</option>
                                <?php while ($p = mysqli_fetch_assoc($data_peminjam)): ?>
                                    <option value="<?= $p['id_user']; ?>"><?= htmlspecialchars($p['nama_lengkap']); ?> (<?= $p['username']; ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Pinjam</label>
                            <input type="date" name="tanggal_pinjam" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rencana Tanggal Kembali</label>
                            <input type="date" name="tanggal_kembali_rencana" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status Awal</label>
                            <select name="status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="disetujui">Disetujui</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_peminjaman" class="btn btn-primary btn-sm">Tambah Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (Diperlukan untuk Modal) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>