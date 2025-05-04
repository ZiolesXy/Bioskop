<?php
session_start();
include '../config.php';
include '../function.php';

// Pastikan user adalah admin
if (!isLoggedIn() || !is_admin()) redirect('../auth/login.php');

// Cek apakah ada ID film
if (!isset($_GET['id'])) {
    redirect('dashboard.php');
}

$movie_id = $_GET['id'];

// Cek apakah ada konfirmasi delete
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    // Hapus film dari database
    $stmt = $conn->prepare("DELETE FROM movies WHERE id = ?");
    $stmt->bind_param("i", $movie_id);
    
    if ($stmt->execute()) {
        // Redirect ke dashboard setelah berhasil hapus
        redirect('dashboard.php');
    } else {
        $error = "Gagal menghapus film: " . $conn->error;
    }
}

// Ambil data film untuk ditampilkan
$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('dashboard.php');
}

$movie = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Film - Admin Bioskop</title>
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
        .confirmation-box {
            max-width: 600px;
            margin: 0 auto;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .movie-info {
            margin: 20px 0;
            padding: 15px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: left;
        }
        .movie-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .warning {
            color: #f44336;
            font-weight: bold;
            margin: 20px 0;
        }
        .btn-group {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }
        .btn-cancel {
            background-color: #ccc;
            color: #333;
        }
        .btn-delete {
            background-color: #f44336;
            color: white;
        }
        .error {
            color: #f44336;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Hapus Film</h1>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="orders.php">Lihat Pesanan</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <div class="confirmation-box">
        <h2>Konfirmasi Penghapusan</h2>
        
        <div class="movie-info">
            <div class="movie-title"><?= htmlspecialchars($movie['title']) ?></div>
            <p><strong>Jadwal:</strong> <?= date('d M Y H:i', strtotime($movie['schedule'])) ?></p>
            <p><strong>Harga:</strong> Rp <?= number_format($movie['price'], 2) ?></p>
        </div>
        
        <p class="warning">Apakah Anda yakin ingin menghapus film ini? Tindakan ini tidak dapat dibatalkan.</p>
        
        <div class="btn-group">
            <a href="dashboard.php" class="btn btn-cancel">Batal</a>
            <a href="delete.php?id=<?= $movie_id ?>&confirm=yes" class="btn btn-delete">Hapus Film</a>
        </div>
    </div>
</body>
</html> 