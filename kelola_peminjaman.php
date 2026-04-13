<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
include 'db/koneksi.php';

// --- LOGIKA APPROVAL (SETUJU / TOLAK) ---
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id_transaksi = mysqli_real_escape_string($conn, $_GET['id']);
    $aksi = $_GET['aksi'];

    if ($aksi == 'setuju') {
        // Ambil ID buku untuk cek stok
        $t = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_buku FROM transaksi WHERE id_transaksi = '$id_transaksi'"));
        $id_buku = $t['id_buku'];
        
        $b = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stok FROM buku WHERE id_buku = '$id_buku'"));
        if ($b['stok'] > 0) {
            // Update status jadi pinjam & kurangi stok
            mysqli_query($conn, "UPDATE transaksi SET status = 'pinjam' WHERE id_transaksi = '$id_transaksi'");
            mysqli_query($conn, "UPDATE buku SET stok = stok - 1 WHERE id_buku = '$id_buku'");
            echo "<script>alert('Peminjaman Berhasil Disetujui!'); window.location='kelola_peminjaman.php';</script>";
        } else {
            echo "<script>alert('Maaf, Stok Buku Habis!'); window.location='kelola_peminjaman.php';</script>";
        }
    } elseif ($aksi == 'tolak') {
        mysqli_query($conn, "UPDATE transaksi SET status = 'ditolak' WHERE id_transaksi = '$id_transaksi'");
        echo "<script>alert('Permintaan Pinjam Ditolak.'); window.location='kelola_peminjaman.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pinjam | E-Library Premium</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #D4AF37;
            --dark: #121212;
            --white: #ffffff;
            --gray: #f8f9fa;
            --success: #27ae60;
            --danger: #e74c3c;
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
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            font-family: 'Playfair Display', serif;
            color: var(--gold);
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

        header { margin-bottom: 30px; }
        header h2 { font-family: 'Playfair Display', serif; margin: 0; font-size: 28px; }
        header p { color: #888; font-size: 14px; margin-top: 5px; }

        /* TABLE CARD */
        .table-card {
            background: var(--white);
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
            overflow-x: auto;
        }

        table { width: 100%; border-collapse: collapse; min-width: 700px; }
        table th { text-align: left; padding: 15px; border-bottom: 2px solid #eee; color: #888; font-size: 12px; text-transform: uppercase; }
        table td { padding: 15px; border-bottom: 1px solid #f1f1f1; font-size: 14px; }

        .user-info { display: flex; flex-direction: column; }
        .user-info .name { font-weight: 600; color: var(--dark); }
        .user-info .sub { font-size: 11px; color: #999; text-transform: uppercase; }

        /* ACTION BUTTONS */
        .btn-group { display: flex; gap: 10px; }
        .btn-action {
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            transition: 0.3s;
            text-transform: uppercase;
        }
        .btn-setuju { border: 1px solid var(--success); color: var(--success); }
        .btn-setuju:hover { background: var(--success); color: white; }
        
        .btn-tolak { border: 1px solid var(--danger); color: var(--danger); }
        .btn-tolak:hover { background: var(--danger); color: white; }

        .empty-state { text-align: center; padding: 50px; color: #bbb; }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .sidebar-header, .sidebar-menu span { display: none; }
            .main-content { margin-left: 70px; width: calc(100% - 70px); padding: 20px; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">E-LIBRARY</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">📊 <span>Dashboard</span></a></li>
            <li><a href="kelola_buku.php">📚 <span>Data Buku</span></a></li>
            <li><a href="kelola_anggota.php">👥 <span>Data Anggota</span></a></li>
            <li class="active"><a href="kelola_peminjaman.php">🔔 <span>Peminjaman</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <h2>Konfirmasi Peminjaman</h2>
            <p>Daftar permintaan buku yang menunggu persetujuan Anda.</p>
        </header>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Peminjam</th>
                        <th>Buku yang Dicari</th>
                        <th>Tanggal Request</th>
                        <th>Tindakan Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = mysqli_query($conn, "SELECT transaksi.*, users.nama_lengkap, users.username, buku.judul 
                                                FROM transaksi 
                                                JOIN users ON transaksi.id_user = users.id_user 
                                                JOIN buku ON transaksi.id_buku = buku.id_buku 
                                                WHERE transaksi.status = 'pending'
                                                ORDER BY transaksi.tgl_pinjam DESC");
                    
                    if (mysqli_num_rows($res) > 0) {
                        while($row = mysqli_fetch_assoc($res)) { ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <span class="name"><?= $row['nama_lengkap']; ?></span>
                                    <span class="sub">@<?= $row['username']; ?></span>
                                </div>
                            </td>
                            <td><strong><?= $row['judul']; ?></strong></td>
                            <td><?= date('d M Y', strtotime($row['tgl_pinjam'])); ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="kelola_peminjaman.php?id=<?= $row['id_transaksi']; ?>&aksi=setuju" 
                                       class="btn-action btn-setuju" onclick="return confirm('Setujui peminjaman ini?')">SETUJU</a>
                                    
                                    <a href="kelola_peminjaman.php?id=<?= $row['id_transaksi']; ?>&aksi=tolak" 
                                       class="btn-action btn-tolak" onclick="return confirm('Tolak permintaan ini?')">TOLAK</a>
                                </div>
                            </td>
                        </tr>
                        <?php } 
                    } else { ?>
                        <tr>
                            <td colspan="4" class="empty-state">
                                ✨ Tidak ada permintaan peminjaman saat ini.
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>