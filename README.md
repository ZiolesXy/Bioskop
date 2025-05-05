# Sistem Manajemen Bioskop

Sistem Manajemen Bioskop adalah aplikasi berbasis web untuk pengelolaan bioskop yang dibangun dengan PHP dan MySQL. Aplikasi ini memungkinkan admin untuk mengelola film dan pesanan tiket, serta memungkinkan pengguna untuk memesan tiket film.

## Fitur

### Fitur Umum
- Sistem autentikasi (login/register)
- Pemisahan hak akses berdasarkan peran (admin/user)

### Fitur Admin
- Dashboard admin untuk pengelolaan sistem
- Menambah, mengedit, dan menghapus film
- Mengelola pesanan tiket (konfirmasi/batalkan)
- Melihat semua transaksi pesanan tiket
- Mencetak tiket

### Fitur User
- Melihat daftar film yang tersedia
- Memesan tiket dengan memilih kursi
- Melihat riwayat pesanan tiket
- Melihat status tiket (pending/confirmed/cancelled)

## Instalasi

### Prasyarat
- PHP 7.0 atau lebih tinggi
- MySQL 5.6 atau lebih tinggi
- Web server (Apache/Nginx)
- XAMPP/WAMP/LAMP (untuk pengembangan lokal)

### Langkah Instalasi
1. Clone atau download repository ini ke direktori web server Anda:
   ```
   git clone https://github.com/username/bioskop.git
   ```
   atau ekstrak file zip ke direktori web server Anda (misalnya: `xampp/htdocs/`).

2. Buat database MySQL baru dengan nama `bioskop`.

3. Import file database `bioskop.sql` ke dalam database yang baru dibuat.

4. Konfigurasikan koneksi database di file `config.php`:
   ```php
   $host = "localhost";
   $username = "root"; // sesuaikan dengan username MySQL Anda
   $password = ""; // sesuaikan dengan password MySQL Anda
   $dbname = "bioskop";
   ```

5. Akses aplikasi melalui browser:
   ```
   http://localhost/bioskop
   ```

## Struktur Database

Database terdiri dari tabel-tabel berikut:

1. `users` - Menyimpan data pengguna
   - id: int (primary key)
   - username: varchar
   - password: varchar (hashed)
   - role: varchar ('admin' atau 'user')

2. `movies` - Menyimpan data film
   - id: int (primary key)
   - title: varchar
   - description: text
   - schedule: datetime
   - price: decimal

3. `orders` - Menyimpan data pesanan tiket
   - id: int (primary key)
   - user_id: int (foreign key ke users.id)
   - movie_id: int (foreign key ke movies.id)
   - seat_number: varchar
   - order_date: datetime
   - status: varchar ('pending', 'confirmed', 'cancelled')
   - total_price: decimal

## Penggunaan

### Login Admin
- Username: 1
- Password: 1

### Login User
- Daftar akun baru melalui halaman register
- Atau gunakan akun demo:
  - Username: user
  - Password: user123

## Alur Kerja Sistem

1. Admin menambahkan film ke sistem
2. Pengguna melihat film yang tersedia dan memilih untuk memesan tiket
3. Pengguna memilih kursi yang tersedia
4. Pesanan dibuat dengan status 'pending'
5. Admin mengonfirmasi pesanan (status menjadi 'confirmed')
6. Admin dapat mencetak tiket untuk pelanggan

## Troubleshooting

Jika Anda menemukan masalah dengan kolom database yang hilang:
- Sistem secara otomatis akan mendeteksi kolom yang hilang
- Gunakan tombol "Tambahkan Kolom Status" atau "Tambahkan Kolom Total Price" di dashboard admin jika diminta

## Catatan Pengembangan

Proyek ini dirancang dengan fleksibilitas untuk penyesuaian lebih lanjut dan ekstensi fitur tambahan di masa depan, seperti terlihat pada mekanisme pengecekan kolom database dan penyesuaian tampilan.

## Kredit

Sistem Manajemen Bioskop ini dikembangkan sebagai proyek demonstrasi PHP dan MySQL. 
