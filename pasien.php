<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: index.php"); // Redirect ke login jika belum login
    exit;
}
?>

<!DOCTYPE html>
<html>
<head><title>Data Pasien</title></head>
<body>
    <h2>Halo, <?= $_SESSION['username'] ?>! Ini Halaman Data Pasien.</h2>

    <!-- Isi halaman -->
    <p>Daftar pasien akan ditampilkan di sini.</p>

    <!-- Navigasi -->
    <a href="dashboard.php">Kembali ke Dashboard</a><br>
    <a href="logout.php">Logout</a>
</body>
</html>
