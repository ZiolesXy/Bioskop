<?php
session_start();
include 'config.php';
include 'function.php';

// Pastikan user adalah admin
if (!isLoggedIn() || !is_admin()) {
    redirect('auth/login.php');
    exit();
}

// Cek apakah kolom status sudah ada
$check_column = $conn->query("SHOW COLUMNS FROM orders LIKE 'status'");
$column_exists = $check_column->num_rows > 0;

if (!$column_exists) {
    // Tambahkan kolom status ke tabel orders
    $query = "ALTER TABLE orders ADD COLUMN status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending'";
    
    if ($conn->query($query)) {
        echo "Kolom status berhasil ditambahkan ke tabel orders.<br>";
        echo "Semua pesanan yang ada telah diberi status 'pending'.<br>";
        echo "<a href='admin/dashboard.php'>Kembali ke Dashboard Admin</a>";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Kolom status sudah ada di tabel orders.<br>";
    echo "<a href='admin/dashboard.php'>Kembali ke Dashboard Admin</a>";
}
?> 