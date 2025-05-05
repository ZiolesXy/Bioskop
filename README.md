# Sistem Manajemen Bioskop

Sistem Manajemen Bioskop adalah aplikasi berbasis web untuk pengelolaan bioskop yang dibangun dengan PHP dan MySQL. Aplikasi ini memungkinkan admin untuk mengelola film dan pesanan tiket, serta memungkinkan pengguna untuk memesan tiket film.

![Screenshot Dashboard](SS%20Hasil/dashboard.png)

## Fitur

### Fitur Umum
- Sistem autentikasi (login/register) dengan desain glassmorphism modern
- Pemisahan hak akses berdasarkan peran (admin/user)
- Antarmuka responsif yang bekerja di berbagai ukuran layar

### Fitur Admin
- Dashboard admin untuk pengelolaan sistem
- Menambah, mengedit, dan menghapus film
- Mengelola link poster film untuk tampilan visual
- Mengelola pesanan tiket (konfirmasi/batalkan)
- Melihat semua transaksi pesanan tiket
- Mencetak tiket

### Fitur User
- Tampilan dashboard modern dengan kartu film yang visual
- Tampilan poster film untuk setiap film yang tersedia
- Memesan tiket dengan pemilihan kursi interaktif
- Melihat riwayat pesanan tiket dengan tampilan kartu modern
- Melihat status tiket (pending/confirmed/cancelled)

## Desain UI/UX

Aplikasi ini menggunakan pendekatan desain modern dengan tema berikut:
- **Glassmorphism**: Efek transparan dengan blur untuk elemen kartu dan form
- **Gradient**: Warna gradient untuk header dan tombol aksi
- **Animasi**: Efek hover dan transisi halus untuk interaksi pengguna
- **Font**: Menggunakan font Inter yang modern dan mudah dibaca
- **Ikon**: Font Awesome untuk ikon yang konsisten dan berkualitas tinggi
- **Responsif**: Layout yang menyesuaikan dengan berbagai ukuran perangkat

## Instalasi

### Prasyarat
- PHP 7.0 atau lebih tinggi
- MySQL 5.6 atau lebih tinggi
- Web server (Apache/Nginx)
- XAMPP/WAMP/LAMP (untuk pengembangan lokal)
- Koneksi internet (untuk memuat font dan ikon eksternal)

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

5. (Opsional) Jalankan `update_movies_table.php` untuk menambahkan kolom poster ke database dan mengisi data poster contoh.

6. Akses aplikasi melalui browser:
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
   - poster: varchar (URL gambar poster film)

3. `orders` - Menyimpan data pesanan tiket
   - id: int (primary key)
   - user_id: int (foreign key ke users.id)
   - movie_id: int (foreign key ke movies.id)
   - seat_number: varchar
   - order_date: datetime
   - status: varchar ('pending', 'confirmed', 'cancelled')
   - total_price: decimal

4. `tickets` - Menyimpan data tiket yang telah dikonfirmasi
   - id: int (primary key)
   - booking_code: varchar (kode unik tiket)
   - user_id: int (foreign key ke users.id)
   - movie_id: int (foreign key ke movies.id)
   - seat_number: varchar
   - purchase_date: datetime

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

1. Admin menambahkan film ke sistem beserta link poster
2. Pengguna melihat film yang tersedia dengan poster di dashboard
3. Pengguna memilih film dan memilih kursi dari tampilan visual
4. Pesanan dibuat dengan status 'pending'
5. Admin mengonfirmasi pesanan (status menjadi 'confirmed')
6. Tiket pengguna ditampilkan dengan desain kartu modern di halaman tiket

## Update dan Fitur Tambahan

### Poster Film
- Kolom `poster` ditambahkan ke tabel `movies` untuk URL gambar
- Halaman admin memiliki field untuk menambah/edit URL poster
- Dashboard pengguna menampilkan poster untuk pengalaman visual yang lebih baik

### Desain Modern
- Halaman login dan register dengan efek glassmorphism
- Dashboard dengan tampilan grid kartu film
- Halaman pemesanan tiket dengan visualisasi pemilihan kursi
- Halaman tiket dengan tampilan kartu modern

## Troubleshooting

Jika Anda menemukan masalah dengan kolom database yang hilang:
- Sistem secara otomatis akan mendeteksi kolom yang hilang
- Gunakan skrip `update_movies_table.php` untuk menambahkan kolom poster
- Gunakan tombol "Tambahkan Kolom Status" atau "Tambahkan Kolom Total Price" di dashboard admin jika diminta

## Catatan Pengembangan

Proyek ini dirancang dengan pendekatan desain modern dan UX yang user-friendly. Pemisahan yang jelas antara tampilan admin dan pengguna, serta konsistensi desain membuat aplikasi ini mudah digunakan dan dikembangkan lebih lanjut.

## Kredit

Sistem Manajemen Bioskop ini dikembangkan dengan:
- PHP & MySQL untuk backend
- HTML, CSS, JavaScript untuk frontend
- Font Awesome untuk ikon
- Google Fonts (Inter) untuk tipografi
- Unsplash untuk gambar background 
