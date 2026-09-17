<?php
session_start();
require 'koneksi.php';

// Proses saat tombol Masuk ditekan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
        $stmt->execute([$username, $password]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama_karyawan'];
            $_SESSION['jabatan'] = $user['jabatan'];
            $_SESSION['role'] = $user['role'];

            // Arahkan berdasarkan peran (role)
            if ($user['role'] == 'manager') {
                header("Location: dashboard_manager.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "Username atau Password salah!";
        }
    } catch(PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Report Atma Highpoint</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
                        url('background.jpeg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(5px);
        }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center py-4">

<!-- Pembungkus container agar lebar kotak login rapi -->
<div class="container" style="max-width: 400px;">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-4 text-center">
            
            <!-- BAGIAN GAMBAR / LOGO -->
            <div class="mb-3">
                <img src="background.jpeg" alt="Logo Perusahaan" style="max-height: 60px; width: auto; object-fit: contain;" class="img-fluid">
            </div>
                
            <h3 class="fw-bold mb-1">E-Report</h3>
            <div class="text-muted mb-2" style="font-size: 14px;">(Sistem Pelaporan Online)</div>
            <h5 class="text-secondary mb-4">Atma Highpoint</h5>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger py-2 text-start"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" class="text-start">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <input type="text" name="username" class="form-control" required placeholder="Masukkan username...">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Masukkan password...">
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Masuk</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>