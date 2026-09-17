<?php
session_start();
require 'koneksi.php';

// Pastikan hanya manager yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit;
}

// Ambil daftar semua user staff untuk opsi dropdown filter
$stmt_users = $pdo->query("SELECT id, nama_karyawan, jabatan FROM users WHERE role = 'staff' ORDER BY nama_karyawan ASC");
$list_staff = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// Cek apakah ada filter staf yang dipilih
$filter_user = $_GET['user_id'] ?? '';

// Buat query untuk mengambil laporan berdasarkan filter
if (!empty($filter_user)) {
    $stmt = $pdo->prepare("SELECT l.*, u.nama_karyawan, u.jabatan FROM laporan_pekerjaan l JOIN users u ON l.user_id = u.id WHERE l.user_id = ? ORDER BY l.id ASC");
    $stmt->execute([$filter_user]);
} else {
    // Jika tidak memilih (tampilkan semua)
    $stmt = $pdo->query("SELECT l.*, u.nama_karyawan, u.jabatan FROM laporan_pekerjaan l JOIN users u ON l.user_id = u.id ORDER BY l.id ASC");
}
$laporan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fungsi untuk menghitung persentase per user_id
function getPersentaseUser($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT status FROM laporan_pekerjaan WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total = count($tasks);
    if ($total == 0) return '0,00%';
    
    $done = 0;
    foreach ($tasks as $t) {
        if (strtolower(trim($t['status'])) == 'done') {
            $done++;
        }
    }
    return number_format(($done / $total) * 100, 2, ',', '.') . '%';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4 mb-5">
    <!-- Header Dashboard -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1">Dashboard Utama (Manager)</h3>
                <p class="text-muted mb-0">Pemantauan Seluruh Bidang Engineering & Kawasan</p>
            </div>
            <a href="logout.php" class="btn btn-outline-danger px-4">Logout</a>
        </div>
    </div>

    <!-- Filter Berdasarkan Staf & Tombol Cetak -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-center justify-content-between">
                <div class="col-auto">
                    <div class="row g-2 align-items-center">
                        <div class="col-auto">
                            <label class="fw-bold form-label mb-0">Filter Staf:</label>
                        </div>
                        <div class="col-auto">
                            <select name="user_id" class="form-select">
                                <option value="">-- Tampilkan Semua Staf --</option>
                                <?php foreach ($list_staff as $staff): ?>
                                    <option value="<?= $staff['id']; ?>" <?= ($filter_user == $staff['id']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($staff['nama_karyawan']); ?> (<?= htmlspecialchars($staff['jabatan']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary px-3">Tampilkan</button>
                            <?php if (!empty($filter_user)): ?>
                                <a href="dashboard_manager.php" class="btn btn-secondary ms-1">Reset</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tombol Cetak PDF -->
                <div class="col-auto">
                    <?php if (!empty($filter_user)): ?>
                        <!-- Jika staf dipilih, cetak laporan milik staf tersebut -->
                        <a href="cetak_pdf.php?user_id=<?= $filter_user; ?>" target="_blank" class="btn btn-danger px-4 fw-semibold">
                            Cetak Laporan (PDF)
                        </a>
                    <?php else: ?>
                        <!-- Jika tidak memilih, beri peringatan atau tombol disable / cetak umum -->
                        <button type="button" class="btn btn-secondary px-4 fw-semibold" disabled title="Pilih staf terlebih dahulu untuk mencetak laporan PDF">
                            Cetak Laporan (PDF)
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Data Laporan -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th width="16%">Bidang / Jabatan</th>
                            <th width="16%">Nama Petugas</th>
                            <th width="14%">Bulan & Minggu</th>
                            <th width="18%">Uraian Pekerjaan</th>
                            <th width="16%">Progres & Solusi</th>
                            <th width="10%">Status</th>
                            <th width="10%">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($laporan) > 0): ?>
                            <?php $no = 1; foreach ($laporan as $row): ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($row['jabatan']); ?></span></td>
                                    <td><?= htmlspecialchars($row['nama_karyawan']); ?></td>
                                    <td>
                                        <?php 
                                        $timestamp = strtotime($row['bulan_laporan'] . '-01');
                                        echo $timestamp ? date('F Y', $timestamp) : htmlspecialchars($row['bulan_laporan']); 
                                        ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($row['minggu_ke']); ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($row['uraian_pekerjaan']); ?></td>
                                    <td>
                                        <small><b>Progres:</b> <?= htmlspecialchars($row['pelaksanaan_progres']); ?></small><br>
                                        <small><b>Solusi:</b> <?= htmlspecialchars($row['tindak_lanjut']); ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= ($row['status'] == 'Done') ? 'success' : 'warning'; ?>">
                                            <?= htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center fw-bold text-primary">
                                        <?= getPersentaseUser($pdo, $row['user_id']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Belum ada data laporan yang tersedia.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>