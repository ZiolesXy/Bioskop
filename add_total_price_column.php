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

// Memeriksa apakah kolom total_price sudah ada di tabel orders
// Ini mencegah penambahan kolom duplikat
$check_column = $conn->query("SHOW COLUMNS FROM orders LIKE 'total_price'");
$column_exists = $check_column->num_rows > 0;

if (!$column_exists) {
    // Jika kolom belum ada, tambahkan kolom total_price dengan tipe DECIMAL
    // Ini akan menyimpan harga total pesanan dengan format 10 digit dan 2 desimal
    $query = "ALTER TABLE orders ADD COLUMN total_price DECIMAL(10,2) DEFAULT 0";
    
    if ($conn->query($query)) {
        // Update nilai total_price di semua pesanan yang ada
        // Mengambil harga dari tabel movies untuk setiap pesanan
        // Ini menggunakan JOIN untuk menghubungkan tabel orders dan movies
        $update_query = "UPDATE orders o 
                        JOIN movies m ON o.movie_id = m.id 
                        SET o.total_price = m.price 
                        WHERE o.total_price = 0";
        
        if ($conn->query($update_query)) {
            // Tampilkan pesan sukses
            echo "Kolom total_price berhasil ditambahkan ke tabel orders.<br>";
            echo "Semua pesanan yang ada telah diperbarui dengan harga dari film terkait.<br>";
        } else {
            // Tampilkan pesan error jika query update gagal
            echo "Kolom total_price berhasil ditambahkan, tetapi gagal memperbarui nilai: " . $conn->error . "<br>";
        }
        
        // Link kembali ke dashboard admin
        echo "<a href='admin/dashboard.php'>Kembali ke Dashboard Admin</a>";
    } else {
        // Tampilkan pesan error jika query alter table gagal
        echo "Error: " . $conn->error;
    }
} else {
    // Tampilkan pesan jika kolom sudah ada dan link untuk kembali
    echo "Kolom total_price sudah ada di tabel orders.<br>";
    echo "<a href='admin/dashboard.php'>Kembali ke Dashboard Admin</a>";
}
?> 