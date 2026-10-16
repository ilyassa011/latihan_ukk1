<?php
session_start();
require_once 'koneksi.php';

$error = '';
$success = '';

if (isset($_POST['register'])) {
    $nama_lengkap = trim(mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']));
    $username     = trim(mysqli_real_escape_string($koneksi, $_POST['username']));
    $password     = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    $role         = trim(mysqli_real_escape_string($koneksi, $_POST['role'])); // Default biasanya 'peminjam'

    // Validasi input kosong
    if (empty($nama_lengkap) || empty($username) || empty($password) || empty($role)) {
        $error = "Semua kolom wajib diisi!";
    } elseif ($password !== $konfirmasi_password) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        // Cek apakah username sudah digunakan
        $cek_user = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
        if (mysqli_num_rows($cek_user) > 0) {
            $error = "Username sudah terdaftar, silakan gunakan username lain!";
        } else {
            // Hash password agar aman
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);

            // Simpan ke database
            $query_insert = "INSERT INTO users (nama_lengkap, username, password, role) 
                             VALUES ('$nama_lengkap', '$username', '$password_hashed', '$role')";
            
            if (mysqli_query($koneksi, $query_insert)) {
                $id_baru = mysqli_insert_id($koneksi);
                
                // Catat log aktivitas pendaftaran user baru
                catat_log($koneksi, $id_baru, "Pengguna baru $username mendaftar sebagai $role");
                
                $success = "Registrasi berhasil! Silakan <a href='login.php'>Login di sini</a>.";
            } else {
                $error = "Terjadi kesalahan pada sistem, gagal mendaftar.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Pengguna - Sistem Peminjaman Alat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">

    <div class="card shadow-sm border-0 my-4" style="width: 100%; max-width: 450px;">
        <div class="card-body p-4 p-md-5">
            <h4 class="card-title text-center fw-bold mb-4">Daftar Akun Baru</h4>

            <!-- Notifikasi Pesan -->
            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small" role="alert">
                    <?= $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success py-2 small" role="alert">
                    <?= $success; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-3">
                    <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control" placeholder="Masukkan nama lengkap" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan username unik" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter" required>
                </div>

                <div class="mb-3">
                    <label for="konfirmasi_password" class="form-label">Konfirmasi Password</label>
                    <input type="password" name="konfirmasi_password" id="konfirmasi_password" class="form-control" placeholder="Ulangi password" required>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Daftar Sebagai</label>
                    <select name="role" id="role" class="form-select" required>
                        <option value="peminjam">Peminjam</option>
                        <!-- Jika pendaftaran petugas dibatasi admin, opsi petugas bisa dihapus atau disembunyikan -->
                        <option value="petugas">Petugas</option>
                    </select>
                </div>

                <button type="submit" name="register" class="btn btn-primary w-100 fw-bold mt-2">Daftar Sekarang</button>
            </form>

            <div class="text-center mt-3">
                <p class="small text-muted">Sudah punya akun? <a href="login.php" class="text-decoration-none">Login di sini</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>