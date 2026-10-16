<?php
session_start();
require_once 'koneksi.php'; // Sesuaikan path jika file logout berada di dalam folder (misal: '../koneksi.php')

// Catat log sebelum session dihancurkan
if (isset($_SESSION['user'])) {
    $id_user = $_SESSION['user']['id_user'];
    $username = $_SESSION['user']['username'];
    
    catat_log($koneksi, $id_user, "User " . $username . " keluar dari sistem.");
}

// Hancurkan session
$_SESSION = array();
session_destroy();

header("location:index.php");
exit;
?>