<?php
session_start();
include '../config.php';
include '../function.php';

// Pastikan user sudah login
if (!isLoggedIn()) redirect('../auth/login.php');

// Cek apakah tabel tickets ada
$table_check = $conn->query("SHOW TABLES LIKE 'tickets'");
if ($table_check->num_rows === 0) {
    // Tampilkan pesan yang informatif jika tabel belum ada
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tiket Saya - Bioskop</title>
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
            .no-tickets {
                text-align: center;
                padding: 30px;
                background-color: #f9f9f9;
                border-radius: 8px;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Tiket Saya</h1>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="../logout.php">Logout</a>
            </div>
        </div>

        <div class="no-tickets">
            <h2>Sistem Tiket Belum Tersedia</h2>
            <p>Sistem tiket saat ini sedang dalam pengembangan.</p>
            <p>Anda tetap dapat memesan film melalui dashboard.</p>
            <p><a href="dashboard.php" style="color: #2196F3;">Kembali ke Dashboard</a></p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Ambil tiket user dari database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT t.id, t.booking_code, t.seat_number, t.purchase_date, m.title, m.schedule, m.price 
                        FROM tickets t 
                        JOIN movies m ON t.movie_id = m.id 
                        WHERE t.user_id = ?
                        ORDER BY t.purchase_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Saya - Bioskop</title>
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
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .ticket {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            width: 300px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            background-color: #fff;
        }
        .ticket-header {
            border-bottom: 1px dashed #ddd;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .ticket-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .booking-code {
            display: inline-block;
            background-color: #f0f0f0;
            padding: 3px 8px;
            border-radius: 4px;
            font-family: monospace;
            margin-top: 5px;
        }
        .ticket-details {
            margin-top: 10px;
        }
        .detail-item {
            margin-bottom: 5px;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
        }
        .no-tickets {
            text-align: center;
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Tiket Saya</h1>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <?php if (isset($_SESSION['order_success'])): ?>
        <div class="alert">
            <strong>Sukses!</strong> Pesanan Anda telah berhasil dibuat. Tiket akan tersedia setelah dikonfirmasi oleh admin.
        </div>
        <?php unset($_SESSION['order_success']); ?>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
        <div class="ticket-container">
            <?php while ($ticket = $result->fetch_assoc()): ?>
                <div class="ticket">
                    <div class="ticket-header">
                        <div class="ticket-title"><?= htmlspecialchars($ticket['title']) ?></div>
                        <div>Kode Booking: <span class="booking-code"><?= htmlspecialchars($ticket['booking_code']) ?></span></div>
                    </div>
                    <div class="ticket-details">
                        <div class="detail-item">
                            <span class="label">Kursi:</span> 
                            <span><?= htmlspecialchars($ticket['seat_number']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Jadwal:</span> 
                            <span><?= date('d M Y H:i', strtotime($ticket['schedule'])) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Harga:</span> 
                            <span>Rp <?= number_format($ticket['price'], 2) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Tanggal Beli:</span> 
                            <span><?= date('d M Y', strtotime($ticket['purchase_date'])) ?></span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-tickets">
            <h2>Anda belum memiliki tiket</h2>
            <p>Tiket akan tersedia di sini setelah pesanan Anda dikonfirmasi oleh admin.</p>
            <p><a href="dashboard.php" style="color: #2196F3;">Kembali ke Dashboard</a></p>
        </div>
    <?php endif; ?>
</body>
</html>
