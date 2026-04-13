<?php
session_start();
// Proteksi Halaman Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
include 'db/koneksi.php';

// --- LOGIKA SIMPAN ANGGOTA ---
if (isset($_POST['simpan_anggota'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $nama     = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $role     = 'user'; 

    $query = mysqli_query($conn, "INSERT INTO users (username, password, nama_lengkap, role) 
                                  VALUES ('$username', '$password', '$nama', '$role')");
    if ($query) {
        header("Location: kelola_anggota.php");
        exit();
    }
}

// --- LOGIKA HAPUS ANGGOTA ---
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    // Cegah menghapus akun admin sistem
    mysqli_query($conn, "DELETE FROM users WHERE id_user = '$id' AND role != 'admin'");
    header("Location: kelola_anggota.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Anggota | E-Library Premium</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #D4AF37;
            --gold-dark: #B8860B;
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

        /* SIDEBAR (Sama dengan Dashboard) */
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

        /* TABLE */
        .table-card {
            background: var(--white);
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        }

        table { width: 100%; border-collapse: collapse; }
        table th { text-align: left; padding: 15px; border-bottom: 2px solid #eee; color: #888; font-size: 12px; text-transform: uppercase; }
        table td { padding: 15px; border-bottom: 1px solid #f1f1f1; font-size: 14px; }

        .btn-delete {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 600;
            font-size: 12px;
            padding: 5px 10px;
            border: 1px solid #e74c3c;
            border-radius: 5px;
            transition: 0.3s;
        }

        .btn-delete:hover { background: #e74c3c; color: white; }

        /* MODAL */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }

        .modal-overlay:target { display: flex; }

        .modal-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            width: 90%;
            max-width: 400px;
            position: relative;
            border-top: 8px solid var(--gold);
        }

        .modal-content h2 { font-family: 'Playfair Display', serif; margin-bottom: 20px; }
        
        .close-modal {
            position: absolute; top: 15px; right: 20px;
            text-decoration: none; color: #aaa; font-size: 24px;
        }

        form input {
            width: 100%; padding: 12px; margin-bottom: 15px;
            border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box;
        }

        .btn-save {
            width: 100%; padding: 12px; background: var(--dark);
            color: var(--gold); border: 1px solid var(--gold);
            border-radius: 8px; font-weight: bold; cursor: pointer;
        }

        /* Responsive */
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
            <li class="active"><a href="kelola_anggota.php">👥 <span>Data Anggota</span></a></li>
            <li><a href="kelola_peminjaman.php">🔔 <span>Peminjaman</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <h2>Kelola Anggota</h2>
            <a href="#tambah-anggota" class="btn-add">+ ANGGOTA BARU</a>
        </header>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = mysqli_query($conn, "SELECT * FROM users ORDER BY role ASC, nama_lengkap ASC");
                    while($row = mysqli_fetch_assoc($res)) { ?>
                    <tr>
                        <td><span style="color: #999;">#<?= $row['id_user']; ?></span></td>
                        <td><strong><?= $row['nama_lengkap']; ?></strong></td>
                        <td><?= $row['username']; ?></td>
                        <td>
                            <span style="font-size: 11px; font-weight: bold; padding: 3px 8px; border-radius: 4px; background: <?= $row['role'] == 'admin' ? '#121212; color: #D4AF37;' : '#eee; color: #666;'; ?>">
                                <?= strtoupper($row['role']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if($row['role'] !== 'admin'): ?>
                                <a href="kelola_anggota.php?hapus=<?= $row['id_user']; ?>" 
                                   class="btn-delete" onclick="return confirm('Hapus anggota ini?')">HAPUS</a>
                            <?php else: ?>
                                <small style="color: #ccc;">Sistem</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="tambah-anggota" class="modal-overlay">
        <div class="modal-content">
            <a href="#" class="close-modal">&times;</a>
            <h2>Tambah Anggota</h2>
            <form action="kelola_anggota.php" method="POST">
                <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" required>
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="simpan_anggota" class="btn-save">SIMPAN DATA</button>
            </form>
        </div>
    </div>

</body>
</html>