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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #f97316;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-light: #f9fafb;
            --bg-dark: #111827;
            --border-color: #e5e7eb;
            --error: #ef4444;
            --success: #10b981;
            --confirmed: #ef4444;
            --pending: #eab308;
            --available: #10b981;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-dark);
            background-image: url('https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            min-height: 100vh;
        }

        .backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            backdrop-filter: blur(8px);
            background: linear-gradient(145deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.95));
            z-index: -1;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(145deg, var(--primary), var(--secondary));
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
        }

        .logo i {
            font-size: 1.5rem;
            color: white;
        }

        .header h1 {
            margin: 0;
            font-size: 1.8rem;
            background: linear-gradient(to right, #fff, #ccc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .user-info i {
            color: var(--secondary);
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
            transition: all 0.3s;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
        }

        .nav-links a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .content-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .movie-info {
            flex: 1;
            min-width: 300px;
        }

        .movie-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            margin-bottom: 2rem;
        }

        .movie-poster {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .movie-details {
            padding: 1.5rem;
        }

        .movie-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: white;
        }

        .detail-item {
            display: flex;
            margin-bottom: 0.75rem;
            align-items: center;
        }

        .detail-item i {
            width: 24px;
            color: var(--secondary);
            margin-right: 0.75rem;
        }

        .detail-label {
            font-weight: 500;
            color: rgba(255, 255, 255, 0.7);
            width: 100px;
        }

        .seat-selection {
            flex: 1.5;
            min-width: 300px;
        }

        .section-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            color: white;
            position: relative;
            display: inline-block;
        }

        .section-title::after {
            content: "";
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border-radius: 2px;
        }

        .screen-container {
            margin-bottom: 2rem;
        }

        .screen {
            width: 100%;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
            margin-bottom: 0.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .screen::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0.1), transparent);
        }

        .screen-label {
            text-align: center;
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        .seats-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 10px;
            margin-bottom: 2rem;
        }

        .seat {
            width: 100%;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(16, 185, 129, 0.2);
            color: white;
            border: 1px solid var(--available);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .seat.booked {
            background: rgba(239, 68, 68, 0.2);
            border-color: var(--confirmed);
            cursor: not-allowed;
        }

        .seat.pending {
            background: rgba(234, 179, 8, 0.2);
            border-color: var(--pending);
            cursor: not-allowed;
        }

        .seat:hover:not(.booked):not(.pending) {
            background: rgba(16, 185, 129, 0.3);
            transform: translateY(-2px);
        }

        .seat-info {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .seat-type {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .seat-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }

        .seat-color.available {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid var(--available);
        }

        .seat-color.booked {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid var(--confirmed);
        }

        .seat-color.pending {
            background: rgba(234, 179, 8, 0.2);
            border: 1px solid var(--pending);
        }

        .order-form {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .form-title {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            color: white;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.7rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-group select, .form-group input {
            width: 100%;
            padding: 0.9rem 1rem;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            font-size: 1rem;
            color: white;
            font-family: 'Inter', sans-serif;
        }

        .form-group select:focus, .form-group input:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
        }

        .form-group select option {
            background-color: var(--bg-dark);
            color: white;
        }

        .error-message {
            color: var(--error);
            font-size: 0.9rem;
            margin: 1rem 0;
            padding: 0.8rem;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 12px;
            border-left: 4px solid var(--error);
            display: flex;
            align-items: center;
        }

        .error-message i {
            margin-right: 0.5rem;
        }

        .btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            box-shadow: 0 6px 15px rgba(79, 70, 229, 0.3);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            font-family: 'Inter', sans-serif;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.4);
        }

        .btn i {
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .nav-links {
                width: 100%;
                justify-content: flex-end;
            }
            
            .seats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="backdrop"></div>
    <div class="container">
        <div class="header">
            <div class="header-title">
                <div class="logo">
                    <i class="fas fa-film"></i>
                </div>
                <h1>Bioskop Online</h1>
            </div>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span>Halo, <?= $_SESSION['username'] ?? 'Pengguna' ?></span>
            </div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="tickets.php"><i class="fas fa-ticket-alt"></i> Tiket Saya</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="content-wrapper">
            <div class="movie-info">
                <div class="movie-card">
                    <img src="<?= !empty($movie['poster']) ? htmlspecialchars($movie['poster']) : 'https://via.placeholder.com/300x450.png?text=Poster+Tidak+Tersedia' ?>" alt="<?= htmlspecialchars($movie['title']) ?>" class="movie-poster">
                    <div class="movie-details">
                        <h2 class="movie-title"><?= htmlspecialchars($movie['title']) ?></h2>
                        
                        <div class="detail-item">
                            <i class="far fa-clock"></i>
                            <span class="detail-label">Jadwal:</span>
                            <span><?= date('d M Y H:i', strtotime($movie['schedule'])) ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <i class="fas fa-tag"></i>
                            <span class="detail-label">Harga:</span>
                            <span>Rp <?= number_format($movie['price'], 0, ',', '.') ?></span>
                        </div>
                        
                        <?php if (isset($movie['description']) && !empty($movie['description'])): ?>
                        <div class="detail-item" style="margin-top: 0.5rem;">
                            <i class="fas fa-info-circle"></i>
                            <span class="detail-label">Deskripsi:</span>
                        </div>
                        <p style="margin-left: 2rem; margin-top: 0.5rem; color: rgba(255, 255, 255, 0.7);">
                            <?= nl2br(htmlspecialchars($movie['description'])) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="order-form">
                    <h3 class="form-title">Pesan Tiket</h3>
                    
                    <?php if (isset($seat_error)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= $seat_error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label for="seat_number">Pilih Kursi</label>
                            <select id="seat_number" name="seat_number" required>
                                <option value="">-- Pilih Kursi --</option>
                                <?php
                                $rows = ['A', 'B', 'C', 'D', 'E'];
                                $cols = [1, 2, 3, 4, 5, 6, 7, 8];
                                
                                foreach ($rows as $row) {
                                    foreach ($cols as $col) {
                                        $seat = $row . $col;
                                        $disabled = in_array($seat, $booked_seats) ? "disabled" : "";
                                        echo "<option value=\"$seat\" $disabled>$seat" . ($disabled ? " (Sudah dipesan)" : "") . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn">
                            <i class="fas fa-shopping-cart"></i> Pesan Tiket
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="seat-selection">
                <h2 class="section-title">Pilih Kursi</h2>
                
                <div class="screen-container">
                    <div class="screen">LAYAR</div>
                    <div class="screen-label">Semua kursi menghadap ke layar</div>
                </div>
                
                <div class="seats-grid">
                    <?php
                    $rows = ['A', 'B', 'C', 'D', 'E'];
                    $cols = [1, 2, 3, 4, 5, 6, 7, 8];
                    
                    foreach ($rows as $row) {
                        foreach ($cols as $col) {
                            $seat = $row . $col;
                            $class = "seat";
                            
                            if (in_array($seat, $confirmed_seats)) {
                                $class .= " booked";
                            } elseif (in_array($seat, $pending_seats)) {
                                $class .= " pending";
                            }
                            
                            echo "<div class=\"$class\" data-seat=\"$seat\">$seat</div>";
                        }
                    }
                    ?>
                </div>

                <div class="seat-info">
                    <div class="seat-type">
                        <div class="seat-color available"></div>
                        <span>Tersedia</span>
                    </div>
                    <div class="seat-type">
                        <div class="seat-color pending"></div>
                        <span>Menunggu</span>
                    </div>
                    <div class="seat-type">
                        <div class="seat-color booked"></div>
                        <span>Terpesan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sinkronisasi pilihan kursi dengan dropdown
        document.querySelectorAll('.seat:not(.booked):not(.pending)').forEach(seat => {
            seat.addEventListener('click', function() {
                const seatNumber = this.dataset.seat;
                const selectElement = document.getElementById('seat_number');
                
                // Set nilai dalam dropdown
                for (let i = 0; i < selectElement.options.length; i++) {
                    if (selectElement.options[i].value === seatNumber) {
                        selectElement.selectedIndex = i;
                        break;
                    }
                }

                // Hapus kelas selected dari semua kursi
                document.querySelectorAll('.seat').forEach(s => {
                    s.style.boxShadow = 'none';
                    s.style.transform = 'translateY(0)';
                });
                
                // Tambahkan efek visual pada kursi terpilih
                this.style.boxShadow = '0 0 0 2px var(--secondary), 0 0 15px rgba(249, 115, 22, 0.5)';
                this.style.transform = 'translateY(-5px)';
            });
        });
    </script>
</body>
</html>