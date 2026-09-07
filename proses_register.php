<?php
require 'koneksi.php';

$username = $_POST['username'];
$password = $_POST['password'];

// Menyimpan ke database
$query = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
$result = mysqli_query($conn, $query);

if ($result) {
    echo "Registrasi berhasil. <a href='login.php'>Login di sini</a>";
} else {
    echo "Gagal menyimpan user: " . mysqli_error($conn);
}
?>
