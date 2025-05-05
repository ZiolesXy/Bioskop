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
            background-image: url('https://images.unsplash.com/photo-1524985069026-dd778a71c7b4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80');
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

        h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            color: white;
            position: relative;
            display: inline-block;
        }

        h2::after {
            content: "";
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border-radius: 2px;
        }

        .movie-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .movie-card {
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

        .movie-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .movie-poster {
            width: 100%;
            height: 300px;
            background-color: rgba(0, 0, 0, 0.2);
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .movie-card:hover .movie-poster {
            transform: scale(1.05);
        }

        .movie-details {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background: rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 1;
        }

        .movie-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: white;
        }

        .movie-schedule {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .movie-price {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--secondary);
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            margin-top: auto;
        }

        .movie-price i {
            font-size: 0.9rem;
        }

        .btn {
            display: inline-block;
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(79, 70, 229, 0.4);
        }

        .btn i {
            margin-right: 0.5rem;
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
            
            .movie-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .movie-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
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
                <a href="tickets.php"><i class="fas fa-ticket-alt"></i> Tiket Saya</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <h2>Film Tayang Saat Ini</h2>
        
        <div class="movie-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="movie-card">
                    <img src="<?= !empty($row['poster']) ? htmlspecialchars($row['poster']) : 'https://via.placeholder.com/300x450.png?text=Film+Bioskop' ?>" 
                         alt="<?= htmlspecialchars($row['title']) ?>" 
                         class="movie-poster"
                         onerror="this.src='https://via.placeholder.com/300x450.png?text=Poster+Tidak+Tersedia'">
                    <div class="movie-details">
                        <h3 class="movie-title"><?= htmlspecialchars($row['title']) ?></h3>
                        <div class="movie-schedule">
                            <i class="far fa-clock"></i>
                            <span><?= $row['schedule'] ?></span>
                        </div>
                        <div class="movie-price">
                            <i class="fas fa-tag"></i>
                            <span>Rp <?= number_format($row['price'], 0, ',', '.') ?></span>
                        </div>
                        <a href="order.php?movie_id=<?= $row['id'] ?>" class="btn">
                            <i class="fas fa-shopping-cart"></i> Pesan Tiket
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>