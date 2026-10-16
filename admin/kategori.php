<?php
session_start();
require_once '../koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Tambah Kategori
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);
    mysqli_query($koneksi, "INSERT INTO kategori (nama_kategori) VALUES ('$nama')");
    header("Location: kategori.php");
    exit;
}

// Hapus Kategori
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM kategori WHERE id_kategori = '$id'");
    header("Location: kategori.php");
    exit;
}

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
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['nama_kategori']); ?></td>
                            <td>
                                <a href="kategori.php?hapus=<?= $row['id_kategori']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus kategori ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>