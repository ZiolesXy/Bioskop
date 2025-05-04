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

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $schedule = $_POST['schedule'];
    $price = $_POST['price'];

    // Update data film
    $stmt = $conn->prepare("UPDATE movies SET title = ?, description = ?, schedule = ?, price = ? WHERE id = ?");
    $stmt->bind_param("sssdi", $title, $description, $schedule, $price, $movie_id);
    
    if ($stmt->execute()) {
        // Redirect ke dashboard setelah berhasil update
        redirect('dashboard.php');
    } else {
        $error = "Gagal mengupdate film: " . $conn->error;
    }
}

// Ambil data film dari database
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
    <title>Edit Film - Admin Bioskop</title>
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
            max-width: 600px;
            margin: 0 auto;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        textarea {
            height: 100px;
        }
        .error {
            color: #f44336;
            margin-bottom: 15px;
        }
        .btn-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        button {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        button[type="submit"] {
            background-color: #4CAF50;
            color: white;
        }
        .btn-cancel {
            background-color: #f44336;
            color: white;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Edit Film</h1>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="orders.php">Lihat Pesanan</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="title">Judul Film</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($movie['title']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="description">Deskripsi</label>
            <textarea id="description" name="description"><?= htmlspecialchars($movie['description']) ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="schedule">Jadwal</label>
            <input type="datetime-local" id="schedule" name="schedule" value="<?= date('Y-m-d\TH:i', strtotime($movie['schedule'])) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="price">Harga</label>
            <input type="number" id="price" name="price" value="<?= $movie['price'] ?>" step="0.01" required>
        </div>
        
        <div class="btn-group">
            <a href="dashboard.php" class="btn-cancel">Batal</a>
            <button type="submit">Simpan Perubahan</button>
        </div>
    </form>
</body>
</html> 