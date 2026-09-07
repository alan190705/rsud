<?php
session_start();
require_once 'koneksi.php';

// Pastikan hanya request POST yang diproses
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_kartu = $_POST['no_kartu'] ?? '';
    $kode_icd = $_POST['kode_icd'] ?? '';
    $nama_penyakit = $_POST['nama_penyakit'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    $status_diagnosa = $_POST['status_diagnosa'] ?? 'Awal';
    $dokter_input = $_SESSION['username'] ?? 'System';

    // Validasi sederhana
    if (empty($no_kartu) || empty($kode_icd) || empty($nama_penyakit)) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap!']);
        exit;
    }

    try {
        $sql = "INSERT INTO diagnosa (no_kartu, kode_icd, nama_penyakit, keterangan, status_diagnosa, dokter_input, tanggal_input) 
                VALUES (:no_kartu, :kode_icd, :nama_penyakit, :keterangan, :status_diagnosa, :dokter_input, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':no_kartu' => $no_kartu,
            ':kode_icd' => $kode_icd,
            ':nama_penyakit' => $nama_penyakit,
            ':keterangan' => $keterangan,
            ':status_diagnosa' => $status_diagnosa,
            ':dokter_input' => $dokter_input
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Diagnosis berhasil disimpan!']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
}