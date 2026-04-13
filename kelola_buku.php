<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
include 'db/koneksi.php';

// --- LOGIKA SIMPAN DATA ---
if (isset($_POST['simpan'])) {
    $judul    = mysqli_real_escape_string($conn, $_POST['judul']);
    $penulis  = mysqli_real_escape_string($conn, $_POST['penulis']);
    $penerbit = mysqli_real_escape_string($conn, $_POST['penerbit']);
    $stok     = $_POST['stok'];

    $insert = mysqli_query($conn, "INSERT INTO buku (judul, penulis, penerbit, stok) VALUES ('$judul', '$penulis', '$penerbit', '$stok')");
    if($insert) { header("Location: kelola_buku.php"); exit(); }
}

// --- LOGIKA HAPUS DATA ---
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM buku WHERE id_buku = '$id'");
    header("Location: kelola_buku.php"); exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Buku | E-Library Premium</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #D4AF37;
            --dark: #121212;
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
            position: fixed;
            border-right: 2px solid var(--gold);
            z-index: 100;
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            font-family: 'Playfair Display', serif;
            color: var(--gold);
            letter-spacing: 2px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.1);
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
            transition: 0.3s;
        }

        .sidebar-menu li.active a {
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
            margin-bottom: 30px;
        }

        header h2 { font-family: 'Playfair Display', serif; margin: 0; font-size: 28px; }

        .btn-add {
            padding: 12px 20px;
            background: var(--dark);
            color: var(--gold);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            border: 1px solid var(--gold);
            transition: 0.3s;
        }

        .btn-add:hover { background: var(--gold); color: white; }

        /* TABLE CARD */
        .table-card {
            background: var(--white);
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
            overflow-x: auto; /* Agar tabel bisa scroll di HP */
        }

        table { width: 100%; border-collapse: collapse; min-width: 600px; }
        table th { text-align: left; padding: 15px; border-bottom: 2px solid #eee; color: #888; font-size: 12px; text-transform: uppercase; }
        table td { padding: 15px; border-bottom: 1px solid #f1f1f1; font-size: 14px; }

        .badge-id { background: #f0f0f0; color: #666; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        
        .btn-delete {
            color: #ff4d4d;
            text-decoration: none;
            font-weight: 600;
            font-size: 12px;
            padding: 6px 12px;
            border: 1px solid #ff4d4d;
            border-radius: 6px;
            transition: 0.3s;
        }

        .btn-delete:hover { background: #ff4d4d; color: white; }

        /* MODAL */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.8);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-overlay:target { display: flex; }

        .modal-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            width: 90%;
            max-width: 450px;
            position: relative;
            border-top: 8px solid var(--gold);
        }

        .modal-content h2 { font-family: 'Playfair Display', serif; margin-top: 0; }
        .close-modal { position: absolute; top: 20px; right: 20px; text-decoration: none; color: #999; font-size: 24px; }

        form label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 5px; color: #555; }
        form input { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        
        .btn-save {
            width: 100%; padding: 14px; background: var(--dark);
            color: var(--gold); border: 1px solid var(--gold);
            border-radius: 8px; font-weight: bold; cursor: pointer;
            transition: 0.3s;
        }
        .btn-save:hover { background: var(--gold); color: white; }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .sidebar-header, .sidebar-menu span { display: none; }
            .main-content { margin-left: 70px; width: calc(100% - 70px); padding: 20px; }
            header h2 { font-size: 22px; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">E-LIBRARY</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">📊 <span>Dashboard</span></a></li>
            <li class="active"><a href="kelola_buku.php">📚 <span>Data Buku</span></a></li>
            <li><a href="kelola_anggota.php">👥 <span>Data Anggota</span></a></li>
            <li><a href="kelola_peminjaman.php">🔔 <span>Peminjaman</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <h2>Kelola Koleksi Buku</h2>
            <a href="#tambah-buku" class="btn-add">+ TAMBAH BUKU</a>
        </header>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Judul Buku</th>
                        <th>Penulis</th>
                        <th>Penerbit</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = mysqli_query($conn, "SELECT * FROM buku ORDER BY id_buku DESC");
                    while($row = mysqli_fetch_assoc($query)) { ?>
                    <tr>
                        <td><span class="badge-id">#<?= $row['id_buku']; ?></span></td>
                        <td><strong><?= $row['judul']; ?></strong></td>
                        <td><?= $row['penulis']; ?></td>
                        <td><?= $row['penerbit']; ?></td>
                        <td><span style="font-weight: 600;"><?= $row['stok']; ?></span> <small>Unit</small></td>
                        <td>
                            <a href="kelola_buku.php?hapus=<?= $row['id_buku']; ?>" 
                               class="btn-delete" onclick="return confirm('Hapus buku ini?')">HAPUS</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="tambah-buku" class="modal-overlay">
        <div class="modal-content">
            <a href="#" class="close-modal">&times;</a>
            <h2>Tambah Buku Baru</h2>
            <form action="kelola_buku.php" method="POST">
                <label>Judul Buku</label>
                <input type="text" name="judul" placeholder="Contoh: Belajar PHP Dasar" required>
                
                <label>Penulis</label>
                <input type="text" name="penulis" placeholder="Nama penulis" required>
                
                <label>Penerbit</label>
                <input type="text" name="penerbit" placeholder="Nama penerbit" required>
                
                <label>Jumlah Stok</label>
                <input type="number" name="stok" placeholder="0" required>
                
                <button type="submit" name="simpan" class="btn-save">SIMPAN KOLEKSI</button>
            </form>
        </div>
    </div>

</body>
</html>