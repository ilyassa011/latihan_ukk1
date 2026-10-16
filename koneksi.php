<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_alat"; // Sesuaikan dengan nama database Anda

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// ---------------------------------------------------------
// HELPER: Fungsi Global untuk Mencatat Log Aktivitas
// ---------------------------------------------------------
if (!function_exists('catat_log')) {
    function catat_log($koneksi, $id_user, $aktivitas) {
        $id_user = $id_user ? intval($id_user) : "NULL";
        $aktivitas = mysqli_real_escape_string($koneksi, $aktivitas);
        $waktu = date('Y-m-d H:s:i'); // Atau format 'Y-m-d H:i:s'
        
        mysqli_query($koneksi, "INSERT INTO log_aktifitas (id_user, aktifitas, waktu) VALUES ($id_user, '$aktivitas', NOW())");
    }
}
?>