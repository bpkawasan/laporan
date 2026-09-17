<?php
session_start();
require 'koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
// Cek apakah manager sedang meminta cetak laporan milik staf tertentu via URL
if (isset($_GET['user_id']) && $_SESSION['role'] === 'manager') {
    $target_user_id = $_GET['user_id'];
    
    // Ambil data profil staf yang dipilih
    $stmt_user = $pdo->prepare("SELECT nama_karyawan, jabatan FROM users WHERE id = ?");
    $stmt_user->execute([$target_user_id]);
    $user_data = $stmt_user->fetch();
    
    if ($user_data) {
        $user_id = $target_user_id;
        $nama = $user_data['nama_karyawan'];
        $jabatan = $user_data['jabatan'];
    }
} else {
    // Jika diakses oleh staff biasa (menggunakan akun sendiri)
    $user_id = $_SESSION['user_id'];
    $nama = $_SESSION['nama'];       
    $jabatan = $_SESSION['jabatan']; 
}

// Ambil data HANYA milik user yang sedang login
$stmt = $pdo->prepare("SELECT * FROM laporan_pekerjaan WHERE user_id = ? ORDER BY id ASC");
$stmt->execute([$user_id]);
$laporan = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_pekerjaan = count($laporan);
$persentase = 0;

if ($total_pekerjaan > 0) {
    $selesai = 0;
    foreach ($laporan as $row) {
        // Cek jika statusnya bernilai 'Done' (tidak case-sensitive)
        if (strtolower(trim($row['status'])) == 'done') {
            $selesai++;
        }
    }
    // Hitung persentase dan format dengan 2 angka di belakang koma
    $persentase = ($selesai / $total_pekerjaan) * 100;
}
$persentase_format = number_format($persentase, 2, ',', '.') . '%';

// Ambil bulan dari data pertama lalu format menjadi teks (Contoh: September 2026)
$bulan_raw = (count($laporan) > 0) ? $laporan[0]['bulan_laporan'] : '-';
$timestamp = strtotime($bulan_raw . '-01');
$bulan = $timestamp ? date('F Y', $timestamp) : $bulan_raw;

// --- FUNGSI MENGUBAH GAMBAR KE BASE64 ---
function getBase64Image($filename) {
    $possible_paths = [
        __DIR__ . '/' . $filename,
        $_SERVER['DOCUMENT_ROOT'] . '/laporan_pekerjaan/' . $filename,
        $_SERVER['DOCUMENT_ROOT'] . '/' . $filename
    ];

    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
    }
    return '';
}

// Ubah kedua logo ke Base64 agar pasti muncul di Dompdf
$sbm_logo_base64 = getBase64Image('sbm_logo.png');
$atma_logo_base64 = getBase64Image('atma.png');

// Panggil library Dompdf
require 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$html = '
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Bulanan</title>
    <style>
        @page { margin: 20px 30px; }
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header-title { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .header-subtitle { font-size: 16px; font-weight: bold; margin-bottom: 15px; }
        .info-table { width: 40%; font-weight: bold; font-size: 12px; margin-bottom: 15px; float: left; }
        .logo-right { float: right; text-align: right; }
        .logo-text { font-family: "Times New Roman", serif; font-size: 24px; color: #228B22; font-weight: bold; }
        .persen-box { border: 1px solid #000; padding: 4px 20px; font-weight: bold; display: inline-block; margin-top: 5px; }
        .clear { clear: both; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data th, table.data td { border: 1px solid #000; padding: 5px; vertical-align: top; }
        table.data th { background-color: #00BFFF; text-align: center; }
        .bg-minggu { background-color: #90EE90; font-weight: bold; }
        table.ttd { 
            width: 100%; 
            border-collapse: collapse; 
            text-align: center; 
            margin-top: 20px; 
        }
        table.ttd th, table.ttd td { 
            border: 1px solid #000; 
            padding: 4px 6px; 
            font-size: 10px;
        }
        table.ttd td { 
            height: 30px; 
            vertical-align: center; 
        }
    </style>
</head>
<body>
    <!-- Header Layout Tabel -->
    <table width="100%" style="border-collapse: collapse; margin-bottom: 15px;">
        <tr>
            <!-- SEBELAH KIRI: Logo Sari Bumi Mas & Identitas -->
            <td width="65%" style="vertical-align: top; border: none;">
                <div style="margin-bottom: 10px;">
                    <img src="' . $sbm_logo_base64 . '" style="max-height: 50px; width: auto; object-fit: contain;">
                </div>
                
                <div style="font-size: 16px; font-weight: bold; margin-bottom: 3px;">PT. SARI BUMI MAS</div>
                <div style="font-size: 14px; font-weight: bold; margin-bottom: 10px;">Laporan Bulanan Pelaksanaan Bidang Engenering Kawasan</div>
                
                <table style="font-size: 11px; font-weight: bold; border: none;">
                    <tr><td style="border: none; padding: 1px 0;" width="60">Bulan</td><td style="border: none; padding: 1px 0;">: '.$bulan.'</td></tr>
                    <tr><td style="border: none; padding: 1px 0;">Nama</td><td style="border: none; padding: 1px 0;">: '.$nama.'</td></tr>
                    <tr><td style="border: none; padding: 1px 0;">Jabatan</td><td style="border: none; padding: 1px 0;">: '.$jabatan.'</td></tr>
                </table>
            </td>
            
            <!-- SEBELAH KANAN: Logo Atma & Persentase -->
            <td width="35%" style="vertical-align: top; text-align: right; border: none;">
                <div style="margin-bottom: 8px;">
                    <img src="' . $atma_logo_base64 . '" style="max-height: 50px; width: auto; object-fit: contain;">
                </div>
                <div>
                    <div style="border: 1px solid #000; padding: 3px 15px; font-weight: bold; display: inline-block; font-size: 11px;">'.$persentase_format.'</div>
                </div>
            </td>
        </tr>
    </table>
    <div class="clear"></div>

    <table class="data">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="20%">Uraian Pekerjaan / Kegiatan</th>
                <th width="20%">Kendala / Hambatan</th>
                <th width="20%">Pelaksanaan & Progres</th>
                <th width="17%">Tindak Lanjut / Solusi</th>
                <th width="12%">Keterangan</th>
                <th width="8%">Status</th>
            </tr>
        </thead>
        <tbody>';

// Mengelompokkan data berdasarkan Minggu agar rapi
$minggu_sekarang = "";
$no = 1;

if (count($laporan) > 0) {
    foreach ($laporan as $row) {
        if ($row['minggu_ke'] != $minggu_sekarang) {
            $minggu_sekarang = $row['minggu_ke'];
            $html .= '<tr><td colspan="7" class="bg-minggu">' . $minggu_sekarang . '</td></tr>';
            $no = 1; 
        }
        
        $html .= '<tr>
            <td style="text-align:center;">'.$no++.'</td>
            <td>'.htmlspecialchars($row['uraian_pekerjaan']).'</td>
            <td>'.htmlspecialchars($row['kendala']).'</td>
            <td>'.htmlspecialchars($row['pelaksanaan_progres']).'</td>
            <td>'.htmlspecialchars($row['tindak_lanjut']).'</td>
            <td>'.htmlspecialchars($row['keterangan']).'</td>
            <td style="text-align:center;">'.htmlspecialchars($row['status']).'</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="7" align="center">Belum ada data laporan.</td></tr>';
}

$html .= '
        </tbody>
    </table>

    <table class="ttd">
        <thead>
            <tr>
                <th width="20%">Pihak</th>
                <th width="30%">Nama</th>
                <th width="30%">Jabatan</th>
                <th width="20%">Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Penyusun,</td>
                <td>'.$nama.'</td>
                <td>'.$jabatan.'</td>
                <td></td>
            </tr>
            <tr>
                <td>Mengetahui,</td>
                <td>'.$nama.'</td>
                <td>Koordinator Engenering</td>
                <td></td>
            </tr>
            <tr>
                <td>Menyetujui,</td>
                <td>Gilar M Nugraha.,SH</td>
                <td>Manager Badan Pengelola Kawasan</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>';

// Setup dan Render PDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); // Format Kertas A4 Mendatar
$dompdf->render();

// Output file ke browser
$dompdf->stream("Laporan_Pekerjaan_".$bulan.".pdf", array("Attachment" => false));
?>