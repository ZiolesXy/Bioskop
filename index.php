<?php
// Memulai sesi PHP untuk menyimpan data login pengguna
session_start();

// Cek apakah pengguna belum login (tidak memiliki session user_id)
if (!isset($_SESSION['user_id'])) {
    // Jika belum login, arahkan ke halaman login
    header("Location: auth/login.php");
} else {
    // Jika sudah login, arahkan ke dashboard sesuai role pengguna
    if ($_SESSION['role'] == 'admin') {
        // Jika role admin, arahkan ke dashboard admin
        header("Location: admin/dashboard.php");
    } else {
        // Jika role user, arahkan ke dashboard user
        header("Location: user/dashboard.php");
    }
}
?>