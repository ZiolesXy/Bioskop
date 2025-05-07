<?php
// Memulai sesi untuk mengakses data sesi yang ada
session_start();
// Include file fungsi utilitas
include 'function.php';

// Menghapus semua data sesi yang tersimpan
session_unset();  // Menghapus semua variabel sesi
session_destroy(); // Menghancurkan sesi yang aktif

// Mengarahkan pengguna kembali ke halaman login
redirect('auth/login.php');
?>
