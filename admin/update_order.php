<?php
session_start();
include '../config.php';
include '../function.php';

// Pastikan user adalah admin
if (!isLoggedIn() || !is_admin()) redirect('../auth/login.php');

// Cek apakah kolom status ada di tabel orders
$check_column = $conn->query("SHOW COLUMNS FROM orders LIKE 'status'");
if ($check_column->num_rows === 0) {
    echo "<div style='padding: 20px; background-color: #fcf8e3; border: 1px solid #faebcc; border-radius: 4px; color: #8a6d3b;'>";
    echo "<strong>Perhatian!</strong> Kolom status tidak ditemukan di tabel orders.<br>";
    echo "Silakan <a href='../add_status_column.php'>klik di sini</a> untuk menambahkan kolom status terlebih dahulu.";
    echo "</div>";
    echo "<p><a href='orders.php'>Kembali ke daftar pesanan</a></p>";
    exit;
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $order_id = $_GET['id'];
    $action = $_GET['action'];
    
    // Validasi action
    if ($action == 'confirm' || $action == 'cancel') {
        $status = ($action == 'confirm') ? 'confirmed' : 'cancelled';
        
        // Update status pesanan di database
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $order_id);
        
        if ($stmt->execute()) {
            // Jika status dikonfirmasi, buat tiket
            if ($status == 'confirmed') {
                // Ambil data pesanan
                $query = "SELECT o.user_id, o.movie_id, o.seat_number, o.total_price 
                          FROM orders o 
                          WHERE o.id = ?";
                $stmt_select = $conn->prepare($query);
                $stmt_select->bind_param("i", $order_id);
                $stmt_select->execute();
                $result = $stmt_select->get_result();
                
                if ($order = $result->fetch_assoc()) {
                    // Cek kolom total_price
                    $check_total_price = $conn->query("SHOW COLUMNS FROM orders LIKE 'total_price'");
                    $has_total_price = $check_total_price->num_rows > 0;
                    
                    // Jika total_price tidak ada, ambil dari tabel movies
                    if (!$has_total_price || !isset($order['total_price']) || $order['total_price'] == 0) {
                        $movie_query = "SELECT price FROM movies WHERE id = ?";
                        $stmt_movie = $conn->prepare($movie_query);
                        $stmt_movie->bind_param("i", $order['movie_id']);
                        $stmt_movie->execute();
                        $movie_result = $stmt_movie->get_result();
                        $movie = $movie_result->fetch_assoc();
                        $order['total_price'] = $movie['price'];
                    }
                    
                    // Cek apakah tabel tickets ada
                    $table_check = $conn->query("SHOW TABLES LIKE 'tickets'");
                    
                    if ($table_check->num_rows === 0) {
                        // Buat tabel tickets jika belum ada
                        $create_table = "CREATE TABLE tickets (
                            id INT(11) NOT NULL AUTO_INCREMENT,
                            user_id INT(11) NOT NULL,
                            movie_id INT(11) NOT NULL,
                            order_id INT(11) NOT NULL,
                            booking_code VARCHAR(20) NOT NULL,
                            seat_number VARCHAR(10) NOT NULL,
                            purchase_date DATETIME NOT NULL,
                            PRIMARY KEY (id),
                            UNIQUE KEY unique_order (order_id)
                        )";
                        $conn->query($create_table);
                    }
                    
                    // Generate booking code
                    $booking_code = 'TKT' . strtoupper(substr(md5(uniqid()), 0, 8));
                    
                    // Buat tiket di database
                    $stmt_ticket = $conn->prepare("INSERT INTO tickets (user_id, movie_id, order_id, booking_code, seat_number, purchase_date) 
                                                 VALUES (?, ?, ?, ?, ?, NOW())");
                    $stmt_ticket->bind_param("iiiss", 
                                          $order['user_id'], 
                                          $order['movie_id'], 
                                          $order_id, 
                                          $booking_code, 
                                          $order['seat_number']);
                    $stmt_ticket->execute();
                }
            }
            // Jika status dibatalkan, kursi akan otomatis tersedia kembali
            // karena kita sudah memodifikasi query untuk mengabaikan kursi dengan status 'cancelled'
            
            // Redirect kembali ke halaman orders
            redirect('orders.php');
        } else {
            echo "Terjadi kesalahan: " . $conn->error;
        }
    } else {
        echo "Action tidak valid!";
    }
} else {
    echo "Parameter tidak lengkap!";
}
?> 