<?php
session_start();
// Koneksi ke database
$conn = mysqli_connect("127.0.0.1", "root", "root", "db_perpustakaan_digital");

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    // Mencari user di database
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        
        // Menyimpan data ke session
        $_SESSION['id_user']  = $data['id_user'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['role']     = $data['role']; 
        $_SESSION['nama']     = $data['nama_lengkap'];
        
        // Redirect berdasarkan role
        if ($data['role'] == 'admin') {
            header("Location: dashboard.php");
        } else {
            header("Location: dashboard_user.php");
        }
        exit();
    } else {
        $error = "Username atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | E-Library Premium</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #D4AF37;
            --gold-dark: #B8860B;
            --dark: #121212;
            --white: #ffffff;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--dark);
            background-image: radial-gradient(circle at 50% 50%, #1e1e1e 0%, #121212 100%);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: var(--white);
        }

        .login-container {
            background: var(--white);
            padding: 40px;
            border-radius: 24px;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            position: relative;
            border: 1px solid rgba(212, 175, 55, 0.2);
        }

        /* Garis Emas di Atas */
        .login-container::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--gold-dark), var(--gold), var(--gold-dark));
            border-radius: 24px 24px 0 0;
        }

        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .login-header h1 {
            font-family: 'Playfair Display', serif;
            color: var(--dark);
            font-size: 30px;
            margin: 0;
            letter-spacing: 1px;
        }

        .login-header p {
            color: #777;
            font-size: 13px;
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-group input {
            width: 100%;
            padding: 14px;
            border: 1.5px solid #eee;
            border-radius: 12px;
            font-size: 15px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: var(--dark);
            color: var(--gold);
            border: 1px solid var(--gold);
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 2px;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: var(--gold);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.2);
        }

        .error-msg {
            background-color: #fff5f5;
            color: #c53030;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
            text-align: center;
            border: 1px solid #feb2b2;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #bbb;
            letter-spacing: 1px;
        }

        /* Responsif HP */
        @media (max-width: 480px) {
            .login-container {
                width: 85%;
                padding: 35px 25px;
            }
            .login-header h1 { font-size: 26px; }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-header">
            <h1>E-LIBRARY</h1>
            <p>Premium Digital</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Username Anda" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" name="login" class="btn-login">MASUK</button>
        </form>

        <div class="footer">
            &copy; <?php echo date('Y'); ?> Digital Library System
        </div>
    </div>

</body>
</html>