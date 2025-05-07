<?php
// Memulai sesi untuk mengakses informasi login
session_start();
// Include file konfigurasi database dan fungsi utilitas
include 'config.php';
include 'function.php';

// Verifikasi bahwa pengguna adalah admin
// Ini mencegah pengguna biasa mengakses halaman pengelolaan database
if (!isLoggedIn() || !is_admin()) {
    redirect('auth/login.php');
    exit();
}

// Memeriksa apakah kolom status sudah ada di tabel orders
// Ini mencegah penambahan kolom duplikat
$check_column = $conn->query("SHOW COLUMNS FROM orders LIKE 'status'");
$column_exists = $check_column->num_rows > 0;

if (!$column_exists) {
    // Jika kolom belum ada, tambahkan kolom status dengan tipe ENUM
    // Enum membatasi nilai status hanya untuk: pending, confirmed, cancelled
    // Default value 'pending' akan diberikan untuk semua pesanan baru
    $query = "ALTER TABLE orders ADD COLUMN status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending'";
    
    if ($conn->query($query)) {
        // Tampilkan pesan sukses dan link untuk kembali ke dashboard
        echo "Kolom status berhasil ditambahkan ke tabel orders.<br>";
        echo "Semua pesanan yang ada telah diberi status 'pending'.<br>";
        echo "<a href='admin/dashboard.php'>Kembali ke Dashboard Admin</a>";
    } else {
        // Tampilkan pesan error jika query gagal
        echo "Error: " . $conn->error;
    }
} else {
    // Tampilkan pesan jika kolom sudah ada dan link untuk kembali
    echo "Kolom status sudah ada di tabel orders.<br>";
    echo "<a href='admin/dashboard.php'>Kembali ke Dashboard Admin</a>";
}
?> 