<?php
session_start();
include '../config.php';
include '../function.php';

// Pastikan user adalah admin
if (!isLoggedIn() || !is_admin()) redirect('../auth/login.php');

// Cek apakah tabel tickets ada
$table_check = $conn->query("SHOW TABLES LIKE 'tickets'");
if ($table_check->num_rows === 0) {
    echo "Tabel tickets belum dibuat. Silakan konfirmasi pesanan terlebih dahulu untuk membuat tabel ini.";
    echo "<p><a href='orders.php'>Kembali ke daftar pesanan</a></p>";
    exit;
}

// Cek apakah ada ID pesanan
if (!isset($_GET['id'])) {
    redirect('orders.php');
}

$order_id = $_GET['id'];

// Ambil data tiket dari database
$query = "SELECT t.id, t.booking_code, t.seat_number, t.purchase_date, 
          m.title, m.description, m.schedule, m.price,
          u.username, u.email, o.status, o.total_price 
          FROM tickets t
          JOIN users u ON t.user_id = u.id
          JOIN movies m ON t.movie_id = m.id
          JOIN orders o ON t.order_id = o.id
          WHERE t.order_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Tiket untuk pesanan ini belum dibuat. Pastikan pesanan sudah dikonfirmasi.";
    echo "<p><a href='orders.php'>Kembali ke daftar pesanan</a></p>";
    exit;
}

$ticket = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Tiket #<?= $ticket['booking_code'] ?></title>
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
        .ticket-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 2px solid #333;
            border-radius: 10px;
            position: relative;
        }
        .ticket-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #333;
        }
        .cinema-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .booking-code {
            display: inline-block;
            background-color: #f0f0f0;
            padding: 5px 10px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 18px;
            margin-bottom: 5px;
        }
        .ticket-details {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .ticket-col {
            flex: 1;
            min-width: 250px;
            margin-bottom: 15px;
        }
        .detail-item {
            margin-bottom: 8px;
        }
        .detail-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        .movie-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .ticket-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #333;
            font-size: 0.9em;
            color: #555;
        }
        .print-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 20px;
            display: block;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 80px;
            color: rgba(0, 0, 0, 0.1);
            font-weight: bold;
            pointer-events: none;
            z-index: -1;
        }
        @media print {
            .no-print {
                display: none;
            }
            .ticket-container {
                border: 1px solid #333;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="header no-print">
        <h1>Cetak Tiket</h1>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="orders.php">Kembali ke Daftar Pesanan</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="ticket-container">
        <div class="watermark">TIKET BIOSKOP</div>
        <div class="ticket-header">
            <div class="cinema-name">BIOSKOP CINEMA XXI</div>
            <div>Kode Booking: <span class="booking-code"><?= htmlspecialchars($ticket['booking_code']) ?></span></div>
            <?php if (isset($ticket['status'])): ?>
            <div>Status: <strong><?= ucfirst($ticket['status']) ?></strong></div>
            <?php endif; ?>
        </div>
        
        <div class="movie-title"><?= htmlspecialchars($ticket['title']) ?></div>
        
        <div class="ticket-details">
            <div class="ticket-col">
                <div class="detail-item">
                    <span class="detail-label">Jadwal:</span> 
                    <span><?= date('d M Y', strtotime($ticket['schedule'])) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Jam:</span> 
                    <span><?= date('H:i', strtotime($ticket['schedule'])) ?> WIB</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Kursi:</span> 
                    <span><?= htmlspecialchars($ticket['seat_number']) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Harga:</span> 
                    <span>Rp <?= number_format($ticket['price'], 2) ?></span>
                </div>
            </div>
            
            <div class="ticket-col">
                <div class="detail-item">
                    <span class="detail-label">Pelanggan:</span> 
                    <span><?= htmlspecialchars($ticket['username']) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Email:</span> 
                    <span><?= htmlspecialchars($ticket['email'] ?? '-') ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Tanggal Beli:</span> 
                    <span><?= date('d M Y H:i', strtotime($ticket['purchase_date'])) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Total Bayar:</span> 
                    <span>Rp <?= number_format($ticket['total_price'], 2) ?></span>
                </div>
            </div>
        </div>
        
        <div class="ticket-footer">
            <p>Tiket ini harus ditunjukkan pada petugas bioskop sebelum memasuki studio.</p>
            <p>Terima kasih telah memilih Bioskop Cinema XXI.</p>
        </div>
    </div>
    
    <button class="print-btn no-print" onclick="window.print()">Cetak Tiket</button>
</body>
</html> 