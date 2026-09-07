<?php
require 'koneksi.php';
// Ganti 'admin' dengan username yang Anda gunakan
$username = 'admin'; 
$password_baru = '12345'; // Password yang ingin Anda set

// Hash password baru
$hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);

// Update database
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
$stmt->execute([$hashed_password, $username]);

echo "Password berhasil di-hash untuk user: $username <br>";
echo "Password sekarang: $password_baru";
?>