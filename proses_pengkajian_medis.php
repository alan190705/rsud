<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

require_once 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $no_kartu = $_POST['no_kartu'] ?? '';
    $keluhan_utama = $_POST['keluhan_utama'] ?? '';
    $riwayat_penyakit = $_POST['riwayat_penyakit'] ?? '';
    $pemeriksaan_fisik = $_POST['pemeriksaan_fisik'] ?? '';
    $diagnosa = $_POST['diagnosa'] ?? '';
    $rencana_terapi = $_POST['rencana_terapi'] ?? '';

    if (empty($no_kartu) || empty($keluhan_utama) || empty($pemeriksaan_fisik) || empty($diagnosa) || empty($rencana_terapi)) {
        $_SESSION['message'] = "Semua kolom yang wajib diisi harus lengkap.";
        $_SESSION['message_type'] = "danger";
        header("Location: pengkajian_medis.php?no_kartu=" . urlencode($no_kartu));
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO pengkajian_medis (no_kartu, keluhan_utama, riwayat_penyakit_dahulu, pemeriksaan_fisik, diagnosa, rencana_terapi) VALUES (:no_kartu, :keluhan_utama, :riwayat_penyakit, :pemeriksaan_fisik, :diagnosa, :rencana_terapi)");

        $stmt->bindParam(':no_kartu', $no_kartu);
        $stmt->bindParam(':keluhan_utama', $keluhan_utama);
        $stmt->bindParam(':riwayat_penyakit', $riwayat_penyakit);
        $stmt->bindParam(':pemeriksaan_fisik', $pemeriksaan_fisik);
        $stmt->bindParam(':diagnosa', $diagnosa);
        $stmt->bindParam(':rencana_terapi', $rencana_terapi);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Pengkajian medis berhasil disimpan.";
            $_SESSION['message_type'] = "success";
            header("Location: data_pasien.php"); // Kembali ke data pasien setelah berhasil
            exit;
        } else {
            $_SESSION['message'] = "Gagal menyimpan pengkajian medis.";
            $_SESSION['message_type'] = "danger";
            header("Location: pengkajian_medis.php?no_kartu=" . urlencode($no_kartu));
            exit;
        }
    } catch (PDOException $e) {
        error_log("Error saving medical assessment: " . $e->getMessage());
        $_SESSION['message'] = "Terjadi kesalahan database saat menyimpan pengkajian medis.";
        $_SESSION['message_type'] = "danger";
        header("Location: pengkajian_medis.php?no_kartu=" . urlencode($no_kartu));
        exit;
    }
} else {
    $_SESSION['message'] = "Metode request tidak valid.";
    $_SESSION['message_type'] = "danger";
    header("Location: data_pasien.php");
    exit;
}
?>