<?php
$host = 'sql105.infinityfree.com';
$dbname = 'if0_42937086_laporan';
$username = 'if0_42937086';
$password = '4IqrbCBko7B'; // Password vPanel/akun Anda

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>
