<?php
session_start();
require 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Proses saat tombol simpan ditekan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id']; // Ambil ID user yang sedang login secara otomatis
    $bulan_laporan = $_POST['bulan_laporan'];
    $minggu_ke = $_POST['minggu_ke'];
    $uraian_pekerjaan = $_POST['uraian_pekerjaan'];
    $kendala = $_POST['kendala'];
    $pelaksanaan_progres = $_POST['pelaksanaan_progres'];
    $tindak_lanjut = $_POST['tindak_lanjut'];
    $keterangan = $_POST['keterangan'];
    $status = $_POST['status'];

    // Query untuk menyimpan data dengan menyertakan user_id
    $sql = "INSERT INTO laporan_pekerjaan (user_id, bulan_laporan, minggu_ke, uraian_pekerjaan, kendala, pelaksanaan_progres, tindak_lanjut, keterangan, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $bulan_laporan, $minggu_ke, $uraian_pekerjaan, $kendala, $pelaksanaan_progres, $tindak_lanjut, $keterangan, $status]);
        
        echo "<script>alert('Data Laporan Berhasil Disimpan!'); window.location='index.php';</script>";
    } catch(PDOException $e) {
        echo "<script>alert('Gagal menyimpan data: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Laporan Pekerjaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5 mb-5" style="max-width: 800px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Form Input Laporan Pekerjaan</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <!-- Info Pegawai (Otomatis dari Session Login) -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Bulan Laporan</label>
                        <input type="month" name="bulan_laporan" class="form-control" required>
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
                        <option value="" disabled selected>Pilih Minggu...</option>
                        <option value="Minggu I">Minggu I</option>
                        <option value="Minggu II">Minggu II</option>
                        <option value="Minggu III">Minggu III</option>
                        <option value="Minggu IV">Minggu IV</option>
                    </select>
                </div>

                <!-- Detail Pekerjaan -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Uraian Pekerjaan / Kegiatan</label>
                    <textarea name="uraian_pekerjaan" class="form-control" rows="2" placeholder="Contoh: Perbaikan dan perubahan tiang Atma" required></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Kendala / Hambatan</label>
                    <textarea name="kendala" class="form-control" rows="2" placeholder="Tuliskan jika ada kendala di lapangan..."></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Pelaksanaan & Progres</label>
                    <textarea name="pelaksanaan_progres" class="form-control" rows="2" placeholder="Contoh: -" required></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Tindak Lanjut / Solusi</label>
                    <textarea name="tindak_lanjut" class="form-control" rows="2" placeholder="Contoh: Penambahan kaki untuk meninggikan tulisan"></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="Done">Done</option>
                        <option value="In Progress">In Progress</option>
                    </select>
                </div>
                
                <hr>
                <button type="submit" class="btn btn-success px-4">Simpan Laporan</button>
                <a href="index.php" class="btn btn-outline-secondary ms-2">Batal & Kembali</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>