<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit();
}
include 'db/koneksi.php';
$id_user = $_SESSION['id_user'];

// Logika Pinjam
if (isset($_GET['pinjam'])) {
    $id_buku = $_GET['pinjam'];
    $tgl = date('Y-m-d');
    $cek = mysqli_query($conn, "SELECT * FROM transaksi WHERE id_user='$id_user' AND id_buku='$id_buku' AND (status='pending' OR status='pinjam')");
    if (mysqli_num_rows($cek) == 0) {
        mysqli_query($conn, "INSERT INTO transaksi (id_user, id_buku, tgl_pinjam, status) VALUES ('$id_user', '$id_buku', '$tgl', 'pending')");
        echo "<script>alert('Permintaan terkirim!'); window.location='dashboard_user.php';</script>";
    } else {
        echo "<script>alert('Buku ini sudah dipesan/dipinjam.'); window.location='dashboard_user.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Library | Premium</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #D4AF37;
            --gold-dark: #B8860B;
            --dark: #1a1a1a;
            --light: #ffffff;
            --gray: #f4f4f4;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0; background: var(--gray); color: var(--dark);
        }

        /* Navbar Atas untuk Mobile & Desktop */
        .navbar {
            background: var(--dark);
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--gold);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 3px solid var(--gold);
        }
        .navbar h1 { font-family: 'Playfair Display', serif; margin: 0; font-size: 20px; }

        .container { padding: 20px 5%; max-width: 1200px; margin: auto; }

        .welcome-section { margin-bottom: 30px; }
        .welcome-section h2 { margin: 0; color: var(--dark); }
        .welcome-section p { color: #666; margin-top: 5px; }

        /* Menu Navigasi */
        .nav-menu {
            display: flex; gap: 15px; margin-bottom: 25px;
            overflow-x: auto; padding-bottom: 10px;
        }
        .nav-link {
            text-decoration: none; color: var(--dark); background: white;
            padding: 10px 20px; border-radius: 30px; font-size: 14px;
            font-weight: 600; border: 1px solid #ddd; transition: 0.3s;
            white-space: nowrap;
        }
        .nav-link.active { background: var(--dark); color: var(--gold); border-color: var(--dark); }

        /* Grid Buku */
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .book-card {
            background: white; border-radius: 15px; padding: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border-left: 5px solid var(--gold);
            transition: transform 0.3s;
        }
        .book-card:hover { transform: translateY(-5px); }
        .book-card h3 { margin: 0 0 10px 0; font-family: 'Playfair Display', serif; }
        .book-card p { color: #777; font-size: 14px; margin-bottom: 20px; }
        
        .btn-gold {
            display: block; text-align: center;
            background: var(--dark); color: var(--gold);
            padding: 12px; border-radius: 8px; text-decoration: none;
            font-weight: bold; font-size: 14px; border: 1px solid var(--gold);
            transition: 0.3s;
        }
        .btn-gold:hover { background: var(--gold); color: white; }

        @media (max-width: 600px) {
            .navbar h1 { font-size: 18px; }
            .container { padding: 15px; }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>E-LIBRARY <span style="color:white;">PREMIUM</span></h1>
        <a href="logout.php" style="color: #ff4d4d; text-decoration: none; font-size: 14px; font-weight: bold;">KELUAR</a>
    </div>

    <div class="container">
        <div class="welcome-section">
            <h2>Halo, <?= $_SESSION['nama']; ?>!</h2>
            <p>Eksplorasi koleksi literasi terbaik kami.</p>
        </div>

        <div class="nav-menu">
            <a href="dashboard_user.php" class="nav-link active">📚 Koleksi Buku</a>
            <a href="riwayat_pinjam.php" class="nav-link">⏳ Riwayat Pinjam</a>
        </div>
        
        <div class="book-grid">
            <?php
            $res = mysqli_query($conn, "SELECT * FROM buku WHERE stok > 0");
            while($row = mysqli_fetch_assoc($res)) { ?>
            <div class="book-card">
                <h3><?= $row['judul']; ?></h3>
                <p>Penulis: <strong><?= $row['penulis']; ?></strong><br>
                Tersedia: <?= $row['stok']; ?> pcs</p>
                <a href="dashboard_user.php?pinjam=<?= $row['id_buku']; ?>" class="btn-gold">PINJAM SEKARANG</a>
            </div>
            <?php } ?>
        </div>
    </div>
</body>
</html>