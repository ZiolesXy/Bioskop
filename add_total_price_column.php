<?php
session_start();
include 'config.php';
include 'function.php';

// Pastikan user adalah admin
if (!isLoggedIn() || !is_admin()) {
    redirect('auth/login.php');
    exit();
}

// Cek apakah kolom total_price sudah ada
$check_column = $conn->query("SHOW COLUMNS FROM orders LIKE 'total_price'");
$column_exists = $check_column->num_rows > 0;

if (!$column_exists) {
    // Tambahkan kolom total_price ke tabel orders
    $query = "ALTER TABLE orders ADD COLUMN total_price DECIMAL(10,2) DEFAULT 0";
    
    if ($conn->query($query)) {
        // Update nilai total_price dari tabel movies
        $update_query = "UPDATE orders o 
                        JOIN movies m ON o.movie_id = m.id 
                        SET o.total_price = m.price 
                        WHERE o.total_price = 0";
        
        if ($conn->query($update_query)) {
            echo "Kolom total_price berhasil ditambahkan ke tabel orders.<br>";
            echo "Semua pesanan yang ada telah diperbarui dengan harga dari film terkait.<br>";
        } else {
            echo "Kolom total_price berhasil ditambahkan, tetapi gagal memperbarui nilai: " . $conn->error . "<br>";
        }
        
        echo "<a href='admin/dashboard.php'>Kembali ke Dashboard Admin</a>";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Kolom total_price sudah ada di tabel orders.<br>";
    echo "<a href='admin/dashboard.php'>Kembali ke Dashboard Admin</a>";
}
?> 