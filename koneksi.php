<?php
$host = 'localhost';
$dbname = 'db_laporan'; 
$user = 'root';
$pass = '#Sariater@2026'; // Password bawaan Laragon adalah kosong

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    // Mengatur mode error PDO menjadi Exception agar mudah dilacak jika ada masalah
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Koneksi Database Gagal: " . $e->getMessage();
    die();
}
?>