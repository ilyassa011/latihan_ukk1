<?php
session_start();
require_once 'koneksi.php';

$error = '';

if (isset($_POST['login'])) {
    // Trim untuk membuang spasi tidak sengaja di awal/akhir username
    $username = trim(mysqli_real_escape_string($koneksi, $_POST['username']));
    $password = $_POST['password'];

    // Cari user berdasarkan username di tabel users
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($koneksi, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Cek password (mendukung password ter-hash maupun teks biasa)
        if (password_verify($password, $row['password']) || $password === $row['password']) {
            $_SESSION['user'] = $row;
            $_SESSION['role'] = trim(strtolower($row['role'])); // Normalisasi role ke huruf kecil

            // --- CATAT LOG AKTIVITAS LOGIN BERHASIL ---
            catat_log($koneksi, $row['id_user'], "User " . $row['username'] . " berhasil login sebagai " . $row['role']);

            // Arahkan berdasarkan level pengguna (role)
            if ($_SESSION['role'] === 'admin') {
                header("Location: admin/dashboard.php");
                exit;
            } elseif ($_SESSION['role'] === 'petugas') {
                header("Location: petugas/dashboard.php");
                exit;
            } elseif ($_SESSION['role'] === 'peminjam') {
                header("Location: peminjam/dashboard.php");
                exit;
            } else {
                header("Location: index.php");
                exit;
            }
        } else {
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        $error = "Username tidak ditemukan di database!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Peminjaman Alat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">

    <div class="card shadow-sm border-0" style="width: 100%; max-width: 400px;">
        <div class="card-body p-5">
            <h4 class="card-title text-center fw-bold mb-4">Login Sistem</h4>

            <!-- Notifikasi Error jika login gagal -->
            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small" role="alert">
                    <?= $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan username" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required>
                </div>

                <button type="submit" name="login" class="btn btn-primary w-100 fw-bold">Login</button>
            </form>

            <!-- Tautan Navigasi Tambahan -->
            <div class="text-center mt-3">
                <p class="small text-muted mb-1">Belum punya akun? <a href="registrasi.php" class="text-decoration-none fw-bold">Daftar di sini</a></p>
                <a href="index.php" class="text-decoration-none small text-secondary">&larr; Kembali ke Beranda</a>
            </div>
        </div>
    </div>

</body>
</html>
