<?php
session_start();
// Proteksi: Hanya user/siswa yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit();
}
include 'db/koneksi.php';
$id_user = $_SESSION['id_user'];

// --- LOGIKA PENGEMBALIAN ---
if (isset($_GET['kembali'])) {
    $id_transaksi = mysqli_real_escape_string($conn, $_GET['kembali']);
    
    // 1. Ambil ID buku untuk mengembalikan stok
    $query_t = mysqli_query($conn, "SELECT id_buku FROM transaksi WHERE id_transaksi='$id_transaksi' AND id_user='$id_user' AND status='pinjam'");
    $data_t = mysqli_fetch_assoc($query_t);
    
    if ($data_t) {
        $id_buku = $data_t['id_buku'];
        
        // 2. Update status transaksi jadi 'kembali'
        mysqli_query($conn, "UPDATE transaksi SET status='kembali' WHERE id_transaksi='$id_transaksi'");
        
        // 3. Tambahkan stok buku kembali
        mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id_buku='$id_buku'");
        
        echo "<script>alert('Buku berhasil dikembalikan!'); window.location='riwayat_pinjam.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pinjam | E-Library Premium</title>
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
            color: var(--dark);
        }

        /* Navbar (Konsisten dengan Dashboard User) */
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
        .navbar a { color: #ff4d4d; text-decoration: none; font-size: 14px; font-weight: bold; }

        .container { padding: 20px 5%; max-width: 1000px; margin: auto; }

        .header-section { margin-bottom: 25px; }
        .header-section h2 { margin: 0; font-family: 'Playfair Display', serif; font-size: 24px; }
        
        /* Menu Navigasi Pill */
        .nav-menu {
            display: flex; gap: 10px; margin-bottom: 25px;
            overflow-x: auto; padding-bottom: 5px;
        }
        .nav-link {
            text-decoration: none; color: #666; background: white;
            padding: 10px 20px; border-radius: 30px; font-size: 13px;
            font-weight: 600; border: 1px solid #ddd; transition: 0.3s;
            white-space: nowrap;
        }
        .nav-link.active { background: var(--dark); color: var(--gold); border-color: var(--dark); }

        /* Tabel Riwayat */
        .table-card {
            background: white; border-radius: 15px; padding: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            overflow-x: auto;
        }
        table { width: 100%; border-collapse: collapse; min-width: 500px; }
        table th { text-align: left; padding: 15px; color: #888; font-size: 11px; text-transform: uppercase; border-bottom: 2px solid #f4f4f4; }
        table td { padding: 15px; border-bottom: 1px solid #f9f9f9; font-size: 14px; }

        /* Status Badges */
        .badge {
            padding: 5px 12px; border-radius: 6px; font-size: 11px; font-weight: 700;
            text-transform: uppercase; display: inline-block;
        }
        .bg-pending { background: #fff8e1; color: #f57f17; } /* Kuning Muda */
        .bg-pinjam  { background: #e8f5e9; color: #2e7d32; } /* Hijau */
        .bg-kembali { background: #f5f5f5; color: #9e9e9e; } /* Abu-abu */
        .bg-ditolak { background: #ffebee; color: #c62828; } /* Merah */

        .btn-kembali {
            color: var(--gold); text-decoration: none; font-weight: bold;
            font-size: 12px; border: 1px solid var(--gold); padding: 5px 10px;
            border-radius: 5px; transition: 0.3s;
        }
        .btn-kembali:hover { background: var(--gold); color: white; }

        .empty-state { text-align: center; padding: 40px; color: #bbb; }

        @media (max-width: 600px) {
            .container { padding: 15px; }
            .header-section h2 { font-size: 20px; }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>E-LIBRARY <span style="color:white;">PREMIUM</span></h1>
        <a href="logout.php">KELUAR</a>
    </div>

    <div class="container">
        <div class="header-section">
            <h2>Aktivitas Saya</h2>
        </div>

        <div class="nav-menu">
            <a href="dashboard_user.php" class="nav-link">📚 Koleksi Buku</a>
            <a href="riwayat_pinjam.php" class="nav-link active">⏳ Riwayat Pinjam</a>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Buku</th>
                        <th>Tgl Pinjam</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = mysqli_query($conn, "SELECT transaksi.*, buku.judul 
                                                FROM transaksi 
                                                JOIN buku ON transaksi.id_buku = buku.id_buku 
                                                WHERE transaksi.id_user = '$id_user' 
                                                ORDER BY transaksi.id_transaksi DESC");
                    
                    if (mysqli_num_rows($res) > 0) {
                        while($row = mysqli_fetch_assoc($res)) { 
                            $st = $row['status'];
                            $badge_class = "bg-pending";
                            if($st == 'pinjam') $badge_class = "bg-pinjam";
                            if($st == 'kembali') $badge_class = "bg-kembali";
                            if($st == 'ditolak') $badge_class = "bg-ditolak";
                        ?>
                        <tr>
                            <td><strong><?= $row['judul']; ?></strong></td>
                            <td><?= date('d M Y', strtotime($row['tgl_pinjam'])); ?></td>
                            <td><span class="badge <?= $badge_class; ?>"><?= $st; ?></span></td>
                            <td>
                                <?php if($st == 'pinjam'): ?>
                                    <a href="riwayat_pinjam.php?kembali=<?= $row['id_transaksi']; ?>" 
                                       class="btn-kembali" 
                                       onclick="return confirm('Kembalikan buku ini?')">Kembalikan</a>
                                <?php else: ?>
                                    <span style="color: #ddd;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php } 
                    } else { ?>
                        <tr>
                            <td colspan="4" class="empty-state">
                                Belum ada riwayat peminjaman.
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>