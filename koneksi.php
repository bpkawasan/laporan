<?php
$host = 'if0_42937086_laporan';
$dbname = 'if0_42937086_laporan'; 
$user = 'if0_42937086';
$pass = '4IqrbCBko7B'; // Password bawaan Laragon adalah kosong

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    // Mengatur mode error PDO menjadi Exception agar mudah dilacak jika ada masalah
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Koneksi Database Gagal: " . $e->getMessage();
    die();
}
?>
