<?php
session_start(); // Memulai sesi agar bisa dihapus

// Menghapus semua variabel session
$_SESSION = array();

// Menghapus cookie session jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Menghancurkan session secara total
session_destroy();

// Mengarahkan kembali ke halaman login (index.php)
header("Location: index.php");
exit();
?>