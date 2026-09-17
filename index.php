<?php
session_start();
require 'koneksi.php';

// Cek apakah sudah login dan rolenya staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nama_user = $_SESSION['nama'];
$jabatan_user = $_SESSION['jabatan'];

// Ambil laporan hanya milik user yang sedang login
$stmt = $pdo->prepare("SELECT * FROM laporan_pekerjaan WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$laporan = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard <?= $jabatan_user ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid mt-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 shadow-sm rounded">
        <div>
            <h3 class="mb-0">Laporan Pekerjaan: <?= $jabatan_user ?></h3>
            <p class="text-muted mb-0">Petugas: <strong><?= $nama_user ?></strong></p>
        </div>
        <div>
            <a href="input.php" class="btn btn-primary">+ Tambah Laporan</a>
            <a href="cetak_pdf.php" target="_blank" class="btn btn-danger shadow-sm">Cetak Laporan (PDF)</a>
            <a href="logout.php" class="btn btn-outline-secondary">Logout</a>
        </div>
    </div>
    
    <!-- Tabel Data Staf -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Bulan</th>
                            <th>Minggu</th>
                            <th>Uraian Pekerjaan</th>
                            <th>Kendala</th>
                            <th>Progres</th>
                            <th>Solusi</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($laporan) > 0): $no=1; foreach($laporan as $row): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <?php 
                                $timestamp = strtotime($row['bulan_laporan'] . '-01');
                                echo $timestamp ? date('F Y', $timestamp) : htmlspecialchars($row['bulan_laporan']); 
                                ?>
                            </td>
                            <td><?= htmlspecialchars($row['minggu_ke']) ?></td>
                            <td><?= htmlspecialchars($row['uraian_pekerjaan']) ?></td>
                            <td><?= htmlspecialchars($row['kendala']) ?></td>
                            <td><?= htmlspecialchars($row['pelaksanaan_progres']) ?></td>
                            <td><?= htmlspecialchars($row['tindak_lanjut']) ?></td>
                            <td><span class="badge bg-<?= $row['status'] == 'Done' ? 'success' : 'warning' ?>"><?= $row['status'] ?></span></td>
                            <td class="text-center">
                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="hapus.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus laporan ini?');">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="9" class="text-center py-4 text-muted">Belum ada laporan yang Anda input.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>