<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}

// Ambil data berdasarkan ID dan pastikan milik user yang login
$stmt = $pdo->prepare("SELECT * FROM laporan_pekerjaan WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$laporan = $stmt->fetch();

if (!$laporan) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

// Proses Update Data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bulan_laporan = $_POST['bulan_laporan'];
    $minggu_ke = $_POST['minggu_ke'];
    $uraian_pekerjaan = $_POST['uraian_pekerjaan'];
    $kendala = $_POST['kendala'];
    $pelaksanaan_progres = $_POST['pelaksanaan_progres'];
    $tindak_lanjut = $_POST['tindak_lanjut'];
    $keterangan = $_POST['keterangan'];
    $status = $_POST['status'];

    $sql = "UPDATE laporan_pekerjaan SET bulan_laporan=?, minggu_ke=?, uraian_pekerjaan=?, kendala=?, pelaksanaan_progres=?, tindak_lanjut=?, keterangan=?, status=? WHERE id=? AND user_id=?";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$bulan_laporan, $minggu_ke, $uraian_pekerjaan, $kendala, $pelaksanaan_progres, $tindak_lanjut, $keterangan, $status, $id, $_SESSION['user_id']]);
        
        echo "<script>alert('Laporan Berhasil Diperbarui!'); window.location='index.php';</script>";
    } catch(PDOException $e) {
        echo "<script>alert('Gagal memperbarui data: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Laporan Pekerjaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5 mb-5" style="max-width: 800px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0">Form Edit Laporan Pekerjaan</h4>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row mb-3">
                    <div class="col-md-4">
                    <label class="form-label fw-bold">Bulan Laporan</label>
                    <input type="month" name="bulan_laporan" class="form-control" value="<?= htmlspecialchars($laporan['bulan_laporan']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Nama Karyawan</label>
                        <input type="text" class="form-control" value="<?= $_SESSION['nama']; ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Jabatan</label>
                        <input type="text" class="form-control" value="<?= $_SESSION['jabatan']; ?>" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Minggu Ke</label>
                    <select name="minggu_ke" class="form-select" required>
                        <?php 
                        $minggus = ["Minggu I", "Minggu II", "Minggu III", "Minggu IV"];
                        foreach($minggus as $m) {
                            $selected = ($laporan['minggu_ke'] == $m) ? 'selected' : '';
                            echo "<option value='$m' $selected>$m</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Uraian Pekerjaan / Kegiatan</label>
                    <textarea name="uraian_pekerjaan" class="form-control" rows="2" required><?= htmlspecialchars($laporan['uraian_pekerjaan']) ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Kendala / Hambatan</label>
                    <textarea name="kendala" class="form-control" rows="2"><?= htmlspecialchars($laporan['kendala']) ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Pelaksanaan & Progres</label>
                    <textarea name="pelaksanaan_progres" class="form-control" rows="2" required><?= htmlspecialchars($laporan['pelaksanaan_progres']) ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Tindak Lanjut / Solusi</label>
                    <textarea name="tindak_lanjut" class="form-control" rows="2"><?= htmlspecialchars($laporan['tindak_lanjut']) ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="2"><?= htmlspecialchars($laporan['keterangan']) ?></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="Done" <?= ($laporan['status'] == 'Done') ? 'selected' : '' ?>>Done</option>
                        <option value="In Progress" <?= ($laporan['status'] == 'In Progress') ? 'selected' : '' ?>>In Progress</option>
                    </select>
                </div>
                
                <hr>
                <button type="submit" class="btn btn-warning px-4 fw-semibold">Perbarui Laporan</button>
                <a href="index.php" class="btn btn-outline-secondary ms-2">Batal</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>