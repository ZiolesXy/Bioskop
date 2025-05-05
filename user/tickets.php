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
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Inter', sans-serif;
                background-color: var(--bg-dark);
                background-image: url('https://images.unsplash.com/photo-1542204165-65bf26472b9b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80');
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

            .no-tickets {
                text-align: center;
                padding: 3rem;
                background: rgba(255, 255, 255, 0.08);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 16px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
                animation: float 0.8s ease-out;
            }

            .no-tickets h2 {
                font-size: 1.5rem;
                font-weight: 600;
                margin-bottom: 1rem;
                color: white;
            }

            .no-tickets p {
                color: rgba(255, 255, 255, 0.7);
                margin-bottom: 1rem;
                max-width: 600px;
                margin: 0 auto 1rem;
            }

            .no-tickets a {
                display: inline-block;
                color: var(--secondary);
                text-decoration: none;
                font-weight: 500;
                margin-top: 1rem;
                padding: 0.5rem 1.5rem;
                border: 1px solid var(--secondary);
                border-radius: 8px;
                transition: all 0.3s;
            }

            .no-tickets a:hover {
                background: var(--secondary);
                color: white;
            }

            @keyframes float {
                0% { opacity: 0; transform: translateY(30px); }
                100% { opacity: 1; transform: translateY(0); }
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
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>

            <div class="no-tickets">
                <h2>Sistem Tiket Belum Tersedia</h2>
                <p>Sistem tiket saat ini sedang dalam pengembangan.</p>
                <p>Anda tetap dapat memesan film melalui dashboard.</p>
                <a href="dashboard.php">Kembali ke Dashboard</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Ambil tiket user dari database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT t.id, t.booking_code, t.seat_number, t.purchase_date, m.title, m.schedule, m.price, m.poster
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
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-dark);
            background-image: url('https://images.unsplash.com/photo-1542204165-65bf26472b9b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80');
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

        .ticket-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .ticket {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .ticket:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .ticket-header {
            position: relative;
            width: 100%;
            height: 0;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            overflow: hidden;
            background: rgba(0, 0, 0, 0.2);
        }

        .ticket-poster {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .ticket-content {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .ticket-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: white;
        }

        .booking-code {
            display: inline-block;
            background: rgba(255, 255, 255, 0.1);
            padding: 0.3rem 0.7rem;
            border-radius: 6px;
            font-family: monospace;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            color: var(--secondary);
            border: 1px solid rgba(249, 115, 22, 0.3);
        }

        .ticket-details {
            margin-top: 0.5rem;
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
            width: 120px;
        }

        .detail-value {
            color: white;
        }

        .alert {
            margin-bottom: 2rem;
            padding: 1rem;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 12px;
            border-left: 4px solid var(--success);
            display: flex;
            align-items: center;
        }

        .alert i {
            color: var(--success);
            font-size: 1.2rem;
            margin-right: 1rem;
        }

        .alert-content {
            color: rgba(255, 255, 255, 0.9);
        }

        .no-tickets {
            text-align: center;
            padding: 3rem;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            animation: float 0.8s ease-out;
        }

        .no-tickets h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: white;
        }

        .no-tickets p {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 1rem;
            max-width: 600px;
            margin: 0 auto 1rem;
        }

        .no-tickets a {
            display: inline-block;
            color: var(--secondary);
            text-decoration: none;
            font-weight: 500;
            margin-top: 1rem;
            padding: 0.5rem 1.5rem;
            border: 1px solid var(--secondary);
            border-radius: 8px;
            transition: all 0.3s;
        }

        .no-tickets a:hover {
            background: var(--secondary);
            color: white;
        }

        @keyframes float {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
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
            
            .ticket-container {
                grid-template-columns: 1fr;
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
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <?php if (isset($_SESSION['order_success'])): ?>
            <div class="alert">
                <i class="fas fa-check-circle"></i>
                <div class="alert-content">
                    <strong>Sukses!</strong> Pesanan Anda telah berhasil dibuat. Tiket akan tersedia setelah dikonfirmasi oleh admin.
                </div>
            </div>
            <?php unset($_SESSION['order_success']); ?>
        <?php endif; ?>

        <h2 class="section-title">Tiket Saya</h2>

        <?php if ($result->num_rows > 0): ?>
            <div class="ticket-container">
                <?php while ($ticket = $result->fetch_assoc()): ?>
                    <div class="ticket">
                        <div class="ticket-header">
                            <img src="<?= !empty($ticket['poster']) ? htmlspecialchars($ticket['poster']) : 'https://via.placeholder.com/350x197.png?text=Poster+Tidak+Tersedia' ?>" 
                                alt="<?= htmlspecialchars($ticket['title']) ?>" 
                                class="ticket-poster">
                        </div>
                        <div class="ticket-content">
                            <h3 class="ticket-title"><?= htmlspecialchars($ticket['title']) ?></h3>
                            <div class="booking-code">
                                <i class="fas fa-ticket-alt"></i> <?= htmlspecialchars($ticket['booking_code']) ?>
                            </div>
                            
                            <div class="ticket-details">
                                <div class="detail-item">
                                    <i class="fas fa-chair"></i>
                                    <span class="detail-label">Kursi:</span> 
                                    <span class="detail-value"><?= htmlspecialchars($ticket['seat_number']) ?></span>
                                </div>
                                
                                <div class="detail-item">
                                    <i class="far fa-calendar-alt"></i>
                                    <span class="detail-label">Jadwal:</span> 
                                    <span class="detail-value"><?= date('d M Y H:i', strtotime($ticket['schedule'])) ?></span>
                                </div>
                                
                                <div class="detail-item">
                                    <i class="fas fa-tag"></i>
                                    <span class="detail-label">Harga:</span> 
                                    <span class="detail-value">Rp <?= number_format($ticket['price'], 0, ',', '.') ?></span>
                                </div>
                                
                                <div class="detail-item">
                                    <i class="fas fa-shopping-cart"></i>
                                    <span class="detail-label">Tanggal Beli:</span> 
                                    <span class="detail-value"><?= date('d M Y', strtotime($ticket['purchase_date'])) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-tickets">
                <h2>Anda belum memiliki tiket</h2>
                <p>Tiket akan tersedia di sini setelah pesanan Anda dikonfirmasi oleh admin.</p>
                <a href="dashboard.php">Kembali ke Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
