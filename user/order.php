<?php
session_start();
include '../config.php';
include '../function.php';

// Pastikan user sudah login
if (!isLoggedIn()) redirect('../auth/login.php');

// Cek apakah ada ID film
if (!isset($_GET['movie_id'])) {
    redirect('dashboard.php');
}

$movie_id = $_GET['movie_id'];
$user_id = $_SESSION['user_id'];

// Ambil data film
$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('dashboard.php');
}

$movie = $result->fetch_assoc();

// Periksa apakah kolom total_price ada di tabel orders
$check_column = $conn->query("SHOW COLUMNS FROM orders LIKE 'total_price'");
$has_total_price = $check_column->num_rows > 0;

// Periksa apakah kolom status ada di tabel orders
$check_status = $conn->query("SHOW COLUMNS FROM orders LIKE 'status'");
$has_status_column = $check_status->num_rows > 0;

// Proses pemesanan tiket
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $seat_number = $_POST['seat_number'];
    $total_price = $movie['price']; // Harga satuan dari film
    
    // Cek ketersediaan kursi
    if ($has_status_column) {
        // Jika kolom status ada, cek kursi dengan status bukan 'cancelled'
        $check_seat = $conn->prepare("SELECT id FROM orders WHERE movie_id = ? AND seat_number = ? AND (status IS NULL OR status != 'cancelled')");
    } else {
        // Jika kolom status tidak ada, cek semua kursi yang dipesan
        $check_seat = $conn->prepare("SELECT id FROM orders WHERE movie_id = ? AND seat_number = ?");
    }
    $check_seat->bind_param("is", $movie_id, $seat_number);
    $check_seat->execute();
    $seat_result = $check_seat->get_result();
    
    if ($seat_result->num_rows > 0) {
        $seat_error = "Maaf, kursi $seat_number sudah dipesan. Silakan pilih kursi lain.";
    } else {
        // Simpan pesanan ke database
        if ($has_total_price) {
            if ($has_status_column) {
                $stmt = $conn->prepare("INSERT INTO orders (user_id, movie_id, seat_number, total_price, order_date, status) 
                                      VALUES (?, ?, ?, ?, NOW(), 'pending')");
            } else {
                $stmt = $conn->prepare("INSERT INTO orders (user_id, movie_id, seat_number, total_price, order_date) 
                                      VALUES (?, ?, ?, ?, NOW())");
            }
            $stmt->bind_param("issd", $user_id, $movie_id, $seat_number, $total_price);
        } else {
            if ($has_status_column) {
                $stmt = $conn->prepare("INSERT INTO orders (user_id, movie_id, seat_number, order_date, status) 
                                      VALUES (?, ?, ?, NOW(), 'pending')");
            } else {
                $stmt = $conn->prepare("INSERT INTO orders (user_id, movie_id, seat_number, order_date) 
                                      VALUES (?, ?, ?, NOW())");
            }
            $stmt->bind_param("iss", $user_id, $movie_id, $seat_number);
        }
        
        if ($stmt->execute()) {
            // Redirect ke halaman tiket setelah berhasil memesan
            $_SESSION['order_success'] = true;
            redirect('tickets.php');
        } else {
            $error = "Gagal memesan tiket: " . $conn->error;
        }
    }
}

// Kursi yang sudah dikonfirmasi (status 'confirmed')
$confirmed_seats = [];
// Kursi yang sedang pending (status 'pending')
$pending_seats = [];

if ($has_status_column) {
    // Ambil kursi yang statusnya confirmed
    $stmt = $conn->prepare("SELECT seat_number FROM orders WHERE movie_id = ? AND status = 'confirmed'");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $confirmed_seats[] = $row['seat_number'];
    }
    
    // Ambil kursi dengan status pending
    $stmt = $conn->prepare("SELECT seat_number FROM orders WHERE movie_id = ? AND status = 'pending'");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $pending_seats[] = $row['seat_number'];
    }
} else {
    // Jika tidak ada kolom status, semua kursi dianggap confirmed
    $stmt = $conn->prepare("SELECT seat_number FROM orders WHERE movie_id = ?");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $confirmed_seats[] = $row['seat_number'];
    }
}

// Gabungkan semua kursi yang tidak tersedia (untuk kompatibilitas dengan kode lama)
$booked_seats = array_merge($confirmed_seats, $pending_seats);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Tiket - <?= htmlspecialchars($movie['title']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .header h1 {
            margin: 0;
        }
        .nav-links a {
            margin-left: 15px;
            text-decoration: none;
            color: #2196F3;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }
        .movie-info {
            flex: 1;
            min-width: 300px;
        }
        .movie-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .movie-details {
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .detail-item {
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
        }
        .seat-selection {
            flex: 1;
            min-width: 300px;
        }
        .screen {
            width: 100%;
            height: 30px;
            background-color: #ddd;
            text-align: center;
            line-height: 30px;
            border-radius: 5px;
            margin-bottom: 30px;
            color: #555;
        }
        .seats-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .seat {
            width: 100%;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .seat.booked {
            background-color: #f44336;
            cursor: not-allowed;
        }
        .seat.pending {
            background-color: #FFD700;
            cursor: not-allowed;
        }
        .seat:hover:not(.booked):not(.pending) {
            background-color: #45a049;
        }
        form {
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        select, input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .error {
            color: #f44336;
            margin-bottom: 15px;
        }
        .seat-legend {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .legend-item {
            display: flex;
            align-items: center;
        }
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 3px;
            margin-right: 5px;
        }
        .legend-available {
            background-color: #4CAF50;
        }
        .legend-pending {
            background-color: #FFD700;
        }
        .legend-booked {
            background-color: #f44336;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pesan Tiket</h1>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="tickets.php">Tiket Saya</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <?php if (isset($error) || isset($seat_error)): ?>
        <div class="error"><?= $error ?? $seat_error ?></div>
    <?php endif; ?>

    <div class="container">
        <div class="movie-info">
            <div class="movie-title"><?= htmlspecialchars($movie['title']) ?></div>
            
            <div class="movie-details">
                <div class="detail-item">
                    <span class="detail-label">Jadwal:</span> 
                    <span><?= date('d M Y H:i', strtotime($movie['schedule'])) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Harga:</span> 
                    <span>Rp <?= number_format($movie['price'], 2) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Deskripsi:</span> 
                    <p><?= nl2br(htmlspecialchars($movie['description'])) ?></p>
                </div>
            </div>
        </div>
        
        <div class="seat-selection">
            <h2>Pilih Kursi</h2>
            <div class="screen">LAYAR</div>
            
            <div class="seat-legend">
                <div class="legend-item">
                    <div class="legend-color legend-available"></div>
                    <span>Tersedia</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-pending"></div>
                    <span>Menunggu Konfirmasi</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-booked"></div>
                    <span>Sudah Dipesan</span>
                </div>
            </div>
            
            <div class="seats-grid">
                <?php for ($i = 1; $i <= 64; $i++): ?>
                    <?php 
                    $seat_label = chr(64 + ceil($i/8)) . ($i % 8 == 0 ? 8 : $i % 8); 
                    $is_confirmed = in_array($seat_label, $confirmed_seats);
                    $is_pending = in_array($seat_label, $pending_seats);
                    $seat_class = '';
                    
                    if ($is_confirmed) {
                        $seat_class = 'booked';
                    } elseif ($is_pending) {
                        $seat_class = 'pending';
                    }
                    ?>
                    <div class="seat <?= $seat_class ?>" 
                         data-seat="<?= $seat_label ?>"
                         onclick="selectSeat('<?= $seat_label ?>', '<?= $seat_class ?>')">
                        <?= $seat_label ?>
                    </div>
                <?php endfor; ?>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="seat_number">Kursi yang dipilih:</label>
                    <input type="text" id="seat_number" name="seat_number" readonly required>
                </div>
                
                <div class="form-group">
                    <label for="total">Total Bayar:</label>
                    <input type="text" id="total" value="Rp <?= number_format($movie['price'], 2) ?>" readonly>
                </div>
                
                <button type="submit">Pesan Tiket Sekarang</button>
            </form>
        </div>
    </div>

    <script>
        function selectSeat(seat, status) {
            if (status === 'booked') {
                alert('Maaf, kursi ' + seat + ' sudah dipesan.');
                return;
            }
            
            if (status === 'pending') {
                alert('Maaf, kursi ' + seat + ' sedang menunggu konfirmasi.');
                return;
            }
            
            document.getElementById('seat_number').value = seat;
        }
    </script>
</body>
</html>