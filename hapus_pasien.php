<?php
include 'koneksi.php';

$no_kartu = $_GET['no_kartu'];
mysqli_query($conn, "DELETE FROM pasien WHERE no_kartu = '$no_kartu'");

header("Location: halaman2.php");
exit;
?>
