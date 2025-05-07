<?php
// Konfigurasi koneksi database
// Variabel untuk koneksi ke server MySQL/MariaDB
$host = "localhost";     // Nama host database (biasanya localhost)
$username = "root";      // Username database (default: root untuk XAMPP)
$password = "";          // Password database (kosong untuk konfigurasi XAMPP default)
$dbname = "bioskop";     // Nama database yang digunakan aplikasi

// Membuat koneksi ke database menggunakan mysqli
$conn = new mysqli($host, $username, $password, $dbname);

// Cek apakah koneksi berhasil
// Jika terjadi error, hentikan eksekusi dan tampilkan pesan error
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>