<?php
session_start();
// Proteksi Halaman Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
include 'db/koneksi.php';

// Ambil Statistik Data
$jml_buku = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM buku"));
$jml_user = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role='user'"));
$jml_pinjam = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM transaksi WHERE status='pinjam'"));
$jml_pending = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM transaksi WHERE status='pending'"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | E-Library Premium</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #D4AF37;
            --gold-dark: #B8860B;
            --dark: #121212;
            --dark-accent: #1e1e1e;
            --white: #ffffff;
            --gray: #f8f9fa;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            background-color: var(--gray);
            display: flex;
            color: var(--dark);
        }

        /* SIDEBAR */
        .sidebar {
            width: 260px;
            background: var(--dark);
            height: 100vh;
            color: var(--white);
            position: fixed;
            transition: all 0.3s;
            z-index: 1000;
            border-right: 2px solid var(--gold);
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            font-family: 'Playfair Display', serif;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
        }

        .sidebar-header h2 {
            margin: 0;
            font-size: 22px;
            color: var(--gold);
            letter-spacing: 2px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
            margin: 0;
        }

        .sidebar-menu li a {
            padding: 15px 25px;
            display: block;
            color: #bbb;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: 0.3s;
        }

        .sidebar-menu li a:hover, .sidebar-menu li.active a {
            background: rgba(212, 175, 55, 0.1);
            color: var(--gold);
            border-left: 4px solid var(--gold);
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            padding: 40px;
            box-sizing: border-box;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        header h2 {
            font-family: 'Playfair Display', serif;
            margin: 0;
            font-size: 28px;
        }

        .btn-logout {
            padding: 10px 20px;
            background: transparent;
            border: 1px solid #ff4d4d;
            color: #ff4d4d;
            text-decoration: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-logout:hover {
            background: #ff4d4d;
            color: white;
        }

        /* STATS CARDS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--white);
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            border-bottom: 4px solid var(--gold);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            margin: 0;
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-card p {
            margin: 10px 0 0;
            font-size: 32px;
            font-weight: 700;
            color: var(--dark);
        }

        /* TABLE SECTION */
        .table-container {
            background: var(--white);
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        }

        .table-container h3 {
            font-family: 'Playfair Display', serif;
            margin-top: 0;
            margin-bottom: 25px;
            border-left: 4px solid var(--gold);
            padding-left: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            text-align: left;
            padding: 15px;
            border-bottom: 2px solid #eee;
            font-size: 13px;
            color: #888;
            text-transform: uppercase;
        }

        table td {
            padding: 15px;
            border-bottom: 1px solid #f1f1f1;
            font-size: 14px;
        }

        .badge {
            background: var(--dark);
            color: var(--gold);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
        }

        /* RESPONSIVE MOBILE */
        @media (max-width: 992px) {
            .sidebar { width: 70px; }
            .sidebar-header h2, .sidebar-menu li a span { display: none; }
            .main-content { margin-left: 70px; width: calc(100% - 70px); padding: 20px; }
            .sidebar-menu li a { text-align: center; padding: 20px; }
        }

        @media (max-width: 600px) {
            header h2 { font-size: 20px; }
            .stat-card p { font-size: 24px; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <h2>E-LIB</h2>
        </div>
        <ul class="sidebar-menu">
            <li class="active"><a href="dashboard.php">📊 <span>Dashboard</span></a></li>
            <li><a href="kelola_buku.php">📚 <span>Data Buku</span></a></li>
            <li><a href="kelola_anggota.php">👥 <span>Data Anggota</span></a></li>
            <li><a href="kelola_peminjaman.php">🔔 <span>Konfirmasi (<?= $jml_pending; ?>)</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <div>
                <h2>Dashboard Admin</h2>
                <p style="color: #888; font-size: 14px; margin: 5px 0 0;">Selamat datang kembali, <?= $_SESSION['nama']; ?></p>
            </div>
            <a href="logout.php" class="btn-logout">KELUAR</a>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Buku</h3>
                <p><?= $jml_buku; ?></p>
            </div>
            <div class="stat-card">
                <h3>Anggota</h3>
                <p><?= $jml_user; ?></p>
            </div>
            <div class="stat-card">
                <h3>Dipinjam</h3>
                <p><?= $jml_pinjam; ?></p>
            </div>
            <div class="stat-card">
                <h3>Pending</h3>
                <p style="color: var(--gold-dark);"><?= $jml_pending; ?></p>
            </div>
        </div>

        <div class="table-container">
            <h3>Buku Terbaru</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Judul Buku</th>
                        <th>Penulis</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query_buku = mysqli_query($conn, "SELECT * FROM buku ORDER BY id_buku DESC LIMIT 5");
                    while($row = mysqli_fetch_assoc($query_buku)) {
                        echo "<tr>
                                <td><span class='badge'>#{$row['id_buku']}</span></td>
                                <td><strong>{$row['judul']}</strong></td>
                                <td>{$row['penulis']}</td>
                                <td>{$row['stok']} Unit</td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>