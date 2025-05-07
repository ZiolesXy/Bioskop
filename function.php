<?php
/**
 * File fungsi utilitas untuk aplikasi Bioskop
 * Berisi fungsi-fungsi umum yang digunakan di seluruh aplikasi
 */

/**
 * Fungsi untuk melakukan pengalihan ke halaman lain
 * @param string $url URL tujuan pengalihan
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Memeriksa apakah pengguna sudah login
 * @return bool True jika pengguna sudah login, false jika belum
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Memeriksa apakah pengguna yang login memiliki hak akses admin
 * @return bool True jika pengguna adalah admin, false jika bukan
 */
function is_admin() {
    return $_SESSION['role'] === 'admin';
}
?>