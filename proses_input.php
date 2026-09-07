<?php
require 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

$no_kartu = $_POST['no_kartu'];
$nama = $_POST['nama'];
$nik = $_POST['nik'];
$tempat_lahir = $_POST['tempat_lahir'];
$tanggal_lahir = $_POST['tanggal_lahir'];
$jenis_kelamin = $_POST['jenis_kelamin'];
$no_hp = $_POST['no_hp'];
$alamat = $_POST['alamat'];
$no_bpjs = $_POST['no_bpjs'];
$no_kk = $_POST['no_kk'];
$tanggal_registrasi = $_POST['tanggal_registrasi'];
$status_pasien = $_POST['status_pasien'];
$instalasi = $_POST['instalasi'];
$ruangan = $_POST['ruangan'];
$kelas = $_POST['kelas'];
$bed = $_POST['bed'];
$kelompok_pasien = $_POST['kelompok_pasien'];

$sql = "INSERT INTO pasien 
(no_kartu,nama,nik,tempat_lahir,tanggal_lahir,jenis_kelamin,no_hp,alamat,no_bpjs,no_kk,tanggal_registrasi,status_pasien,ruangan,sub_ruangan,kelas,bed,kelompok_pasien)
VALUES 
(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt = $pdo->prepare($sql);

$stmt->execute([
$no_kartu,
$nama,
$nik,
$tempat_lahir,
$tanggal_lahir,
$jenis_kelamin,
$no_hp,
$alamat,
$no_bpjs,
$no_kk,
$tanggal_registrasi,
$status_pasien,
$ruangan,
$sub_ruangan,
$kelas,
$bed,
$kelompok_pasien
]);

header("Location: form_pendaftaran_pasien.php?success=1");
exit();
}
?>