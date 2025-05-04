<?php
session_start();
include '../config.php';
include '../function.php';

// Pastikan user adalah admin
if (!isLoggedIn() || !is_admin()) redirect('../auth/login.php');

// Cek apakah kolom total_price ada di tabel orders
$check_total_price = $conn->query("SHOW COLUMNS FROM orders LIKE 'total_price'");
$has_total_price = $check_total_price->num_rows > 0;

// Cek apakah tabel orders memiliki kolom status
$table_info = $conn->query("SHOW COLUMNS FROM orders LIKE 'status'");
$has_status_column = $table_info->num_rows > 0;

// Ambil semua pesanan dari database
if ($has_total_price) {
    if ($has_status_column) {
        $query = "SELECT o.id, o.order_date, u.username, m.title, o.seat_number, o.status, o.total_price 
                  FROM orders o
                  JOIN users u ON o.user_id = u.id
                  JOIN movies m ON o.movie_id = m.id
                  ORDER BY o.order_date DESC";
    } else {
        $query = "SELECT o.id, o.order_date, u.username, m.title, o.seat_number, o.total_price 
                  FROM orders o
                  JOIN users u ON o.user_id = u.id
                  JOIN movies m ON o.movie_id = m.id
                  ORDER BY o.order_date DESC";
    }
} else {
    if ($has_status_column) {
        $query = "SELECT o.id, o.order_date, u.username, m.title, o.seat_number, o.status, m.price as total_price 
                  FROM orders o
                  JOIN users u ON o.user_id = u.id
                  JOIN movies m ON o.movie_id = m.id
                  ORDER BY o.order_date DESC";
    } else {
        $query = "SELECT o.id, o.order_date, u.username, m.title, o.seat_number, m.price as total_price 
                  FROM orders o
                  JOIN users u ON o.user_id = u.id
                  JOIN movies m ON o.movie_id = m.id
                  ORDER BY o.order_date DESC";
    }
}
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesanan - Admin Bioskop</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .status-pending {
            background-color: #FFD700;
            color: #333;
        }
        .status-confirmed {
            background-color: #4CAF50;
            color: white;
        }
        .status-cancelled {
            background-color: #f44336;
            color: white;
        }
        .action-links a {
            margin-right: 10px;
            text-decoration: none;
        }
        .btn-confirm {
            color: #4CAF50;
        }
        .btn-cancel {
            color: #f44336;
        }
        .no-orders {
            text-align: center;
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 8px;
            margin-top: 20px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
            color: #8a6d3b;
            background-color: #fcf8e3;
            border-color: #faebcc;
        }
        .status-explanation {
            margin: 20px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .status-explanation h3 {
            margin-top: 0;
        }
        .status-details {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .status-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .status-color {
            width: 40px;
            height: 20px;
            margin-right: 10px;
            border-radius: 3px;
        }
        .color-pending {
            background-color: #FFD700;
        }
        .color-confirmed {
            background-color: #4CAF50;
        }
        .color-cancelled {
            background-color: #f44336;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daftar Pesanan</h1>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <?php if (!$has_status_column): ?>
    <div class="alert">
        <strong>Perhatian!</strong> Kolom status tidak ditemukan di tabel pesanan. 
        <a href="../add_status_column.php">Klik di sini</a> untuk menambahkan kolom status.
    </div>
    <?php endif; ?>

    <?php if (!$has_total_price): ?>
    <div class="alert">
        <strong>Perhatian!</strong> Kolom total_price tidak ditemukan di tabel pesanan. 
        <a href="../add_total_price_column.php">Klik di sini</a> untuk menambahkan kolom total_price.
    </div>
    <?php endif; ?>

    <?php if ($has_status_column): ?>
    <div class="status-explanation">
        <h3>Keterangan Status Pesanan</h3>
        <div class="status-details">
            <div class="status-item">
                <div class="status-color color-pending"></div>
                <div>
                    <strong>Menunggu</strong> - Pesanan belum diproses, kursi dicadangkan tetapi belum dikonfirmasi
                </div>
            </div>
            <div class="status-item">
                <div class="status-color color-confirmed"></div>
                <div>
                    <strong>Terkonfirmasi</strong> - Pesanan sudah dikonfirmasi, tiket sudah diterbitkan
                </div>
            </div>
            <div class="status-item">
                <div class="status-color color-cancelled"></div>
                <div>
                    <strong>Dibatalkan</strong> - Pesanan dibatalkan, kursi kembali tersedia untuk pemesanan
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Username</th>
                <th>Film</th>
                <th>Kursi</th>
                <th>Total Harga</th>
                <?php if ($has_status_column): ?>
                    <th>Status</th>
                    <th>Aksi</th>
                <?php endif; ?>
            </tr>
            <?php while ($order = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $order['id'] ?></td>
                    <td><?= date('d M Y H:i', strtotime($order['order_date'])) ?></td>
                    <td><?= htmlspecialchars($order['username']) ?></td>
                    <td><?= htmlspecialchars($order['title']) ?></td>
                    <td><?= htmlspecialchars($order['seat_number']) ?></td>
                    <td>Rp <?= number_format($order['total_price'], 2) ?></td>
                    <?php if ($has_status_column): ?>
                    <td>
                        <?php if (isset($order['status'])): ?>
                        <span class="status status-<?= strtolower($order['status']) ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                        <?php else: ?>
                        <span class="status status-pending">
                            Pending
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="action-links">
                        <?php if (!isset($order['status']) || $order['status'] == 'pending'): ?>
                            <a href="update_order.php?id=<?= $order['id'] ?>&action=confirm" class="btn-confirm">Konfirmasi</a>
                            <a href="update_order.php?id=<?= $order['id'] ?>&action=cancel" class="btn-cancel">Batalkan</a>
                        <?php elseif (isset($order['status']) && $order['status'] == 'confirmed'): ?>
                            <a href="print_ticket.php?id=<?= $order['id'] ?>">Cetak Tiket</a>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <div class="no-orders">
            <h2>Belum ada pesanan</h2>
            <p>Saat ini belum ada pesanan yang dibuat.</p>
        </div>
    <?php endif; ?>
</body>
</html>
