<?php
require_once 'koneksi.php';

header('Content-Type: application/json');

try {

    $no_kartu = $_POST['no_kartu'] ?? '';
    $tensi = $_POST['tensi'] ?? '';
    $nadi = $_POST['nadi'] ?? '';
    $suhu = $_POST['suhu'] ?? '';
    $respirasi = $_POST['respirasi'] ?? '';

    if (!$no_kartu || !$tensi || !$nadi || !$suhu || !$respirasi) {
        echo json_encode([
            "status" => "error",
            "message" => "Field ada yang kosong!"
        ]);
        exit;
    }

    $query = "INSERT INTO vital_sign 
              (no_kartu, tensi, nadi, suhu, respirasi, tgl_input)
              VALUES (:no_kartu, :tensi, :nadi, :suhu, :respirasi, NOW())";

    $stmt = $pdo->prepare($query);

    $stmt->execute([
        ':no_kartu' => $no_kartu,
        ':tensi' => $tensi,
        ':nadi' => $nadi,
        ':suhu' => $suhu,
        ':respirasi' => $respirasi
    ]);

    echo json_encode([
        "status" => "success",
        "message" => "Berhasil disimpan"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);

}