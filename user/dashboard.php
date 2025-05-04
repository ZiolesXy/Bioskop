<?php
session_start();
include '../config.php';
include '../function.php';

if (!isLoggedIn()) redirect('../auth/login.php');

$result = $conn->query("SELECT * FROM movies");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - Bioskop</title>
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
        }
        table th, table td {
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
        }
        .btn {
            display: inline-block;
            padding: 5px 10px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard User</h1>
        <div class="nav-links">
            <a href="tickets.php">Tiket Saya</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <h2>Film Tayang</h2>
    <table border="1">
        <tr>
            <th>Judul</th>
            <th>Jadwal</th>
            <th>Harga</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['title'] ?></td>
                <td><?= $row['schedule'] ?></td>
                <td>Rp <?= number_format($row['price'], 2) ?></td>
                <td><a href="order.php?movie_id=<?= $row['id'] ?>" class="btn">Pesan</a></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>