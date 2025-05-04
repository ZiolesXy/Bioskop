<?php
session_start();
include '../config.php';
include '../function.php';

if (!isLoggedIn() || !is_admin()) redirect('../auth/login.php');

// Cek apakah kolom status dan total_price ada
$check_status = $conn->query("SHOW COLUMNS FROM orders LIKE 'status'");
$has_status_column = $check_status->num_rows > 0;

$check_total_price = $conn->query("SHOW COLUMNS FROM orders LIKE 'total_price'");
$has_total_price = $check_total_price->num_rows > 0;

// Tambah Film
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $schedule = $_POST['schedule'];
    $price = $_POST['price'];

    $stmt = $conn->prepare("INSERT INTO movies (title, description, schedule, price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssd", $title, $description, $schedule, $price);
    $stmt->execute();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Bioskop</title>
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
        form {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        form input, form textarea {
            display: block;
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #f2f2f2;
        }
        .action-links a {
            margin-right: 10px;
            color: #2196F3;
            text-decoration: none;
        }
        .action-links a:hover {
            text-decoration: underline;
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
        .database-tools {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .database-tools h3 {
            margin-top: 0;
        }
        .database-tools .tool-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .tool-button {
            display: inline-block;
            padding: 8px 12px;
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        .tool-button:hover {
            background-color: #0b7dda;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard Admin</h1>
        <div class="nav-links">
            <a href="orders.php">Lihat Pesanan</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <?php if (!$has_status_column || !$has_total_price): ?>
    <div class="database-tools">
        <h3>Alat Database</h3>
        <p>Beberapa fitur memerlukan kolom tambahan pada tabel database. Gunakan alat di bawah ini untuk menambahkannya:</p>
        <div class="tool-buttons">
            <?php if (!$has_status_column): ?>
            <a href="../add_status_column.php" class="tool-button">Tambahkan Kolom Status</a>
            <?php endif; ?>
            
            <?php if (!$has_total_price): ?>
            <a href="../add_total_price_column.php" class="tool-button">Tambahkan Kolom Total Price</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <h2>Tambah Film Baru</h2>
    <form method="POST">
        <input type="text" name="title" placeholder="Judul Film" required>
        <textarea name="description" placeholder="Deskripsi" rows="4"></textarea>
        <input type="datetime-local" name="schedule" required>
        <input type="number" name="price" placeholder="Harga" step="0.01" required>
        <button type="submit">Tambah Film</button>
    </form>

    <h2>Daftar Film</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Judul</th>
            <th>Jadwal</th>
            <th>Aksi</th>
        </tr>
        <?php 
        $result = $conn->query("SELECT * FROM movies");
        while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['title'] ?></td>
                <td><?= $row['schedule'] ?></td>
                <td class="action-links">
                    <a href="edit.php?id=<?= $row['id'] ?>">Edit</a> | 
                    <a href="delete.php?id=<?= $row['id'] ?>">Hapus</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>