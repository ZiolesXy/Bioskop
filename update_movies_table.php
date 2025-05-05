<?php
// Koneksi ke database
include 'config.php';

// Cek apakah kolom poster sudah ada
$result = $conn->query("SHOW COLUMNS FROM movies LIKE 'poster'");
$exists = $result->num_rows > 0;

// Jika kolom poster belum ada, tambahkan
if (!$exists) {
    $sql = "ALTER TABLE movies ADD COLUMN poster VARCHAR(255) AFTER price";
    if ($conn->query($sql) === TRUE) {
        echo "Kolom poster berhasil ditambahkan ke tabel movies<br>";
    } else {
        echo "Error menambahkan kolom poster: " . $conn->error . "<br>";
    }
}

// Array film dengan URL poster
$movies = [
    [
        'title' => 'The Avengers',
        'poster' => 'https://m.media-amazon.com/images/M/MV5BNDYxNjQyMjAtNTdiOS00NGYwLWFmNTAtNThmYjU5ZGI2YTI1XkEyXkFqcGdeQXVyMTMxODk2OTU@._V1_.jpg'
    ],
    [
        'title' => 'Spider-Man: No Way Home',
        'poster' => 'https://m.media-amazon.com/images/M/MV5BZWMyYzFjYTYtNTRjYi00OGExLWE2YzgtOGRmYjAxZTU3NzBiXkEyXkFqcGdeQXVyMzQ0MzA0NTM@._V1_.jpg'
    ],
    [
        'title' => 'Doctor Strange',
        'poster' => 'https://m.media-amazon.com/images/M/MV5BNjgwNzAzNjk1Nl5BMl5BanBnXkFtZTgwMzQ2NjI1OTE@._V1_.jpg'
    ],
    [
        'title' => 'Black Panther',
        'poster' => 'https://m.media-amazon.com/images/M/MV5BMTg1MTY2MjYzNV5BMl5BanBnXkFtZTgwMTc4NTMwNDI@._V1_.jpg'
    ],
    [
        'title' => 'Thor: Ragnarok',
        'poster' => 'https://m.media-amazon.com/images/M/MV5BMjMyNDkzMzI1OF5BMl5BanBnXkFtZTgwODcxODg5MjI@._V1_.jpg'
    ]
];

// Update film dengan poster
$updated = 0;
foreach ($movies as $movie) {
    $title = $movie['title'];
    $poster = $movie['poster'];
    
    // Cek apakah film dengan judul tersebut ada
    $stmt = $conn->prepare("SELECT id FROM movies WHERE title LIKE ?");
    $search = "%$title%";
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id = $row['id'];
        
        // Update poster
        $updateStmt = $conn->prepare("UPDATE movies SET poster = ? WHERE id = ?");
        $updateStmt->bind_param("si", $poster, $id);
        
        if ($updateStmt->execute()) {
            echo "Berhasil mengupdate poster untuk film: $title<br>";
            $updated++;
        } else {
            echo "Error mengupdate poster untuk film $title: " . $conn->error . "<br>";
        }
    } else {
        echo "Film dengan judul yang mirip '$title' tidak ditemukan dalam database<br>";
    }
}

// Tambahkan film baru jika belum ada film
if ($updated == 0) {
    echo "Tidak ada film yang cocok untuk diupdate. Menambahkan film baru...<br>";
    
    foreach ($movies as $index => $movie) {
        $title = $movie['title'];
        $poster = $movie['poster'];
        $schedule = date('Y-m-d H:i:s', strtotime('+' . ($index + 1) . ' days'));
        $price = 50000 + ($index * 10000);
        
        $stmt = $conn->prepare("INSERT INTO movies (title, schedule, price, poster) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $title, $schedule, $price, $poster);
        
        if ($stmt->execute()) {
            echo "Berhasil menambahkan film baru: $title<br>";
        } else {
            echo "Error menambahkan film baru $title: " . $conn->error . "<br>";
        }
    }
}

echo "<br>Proses update selesai! <a href='user/dashboard.php'>Kembali ke Dashboard</a>";
?> 