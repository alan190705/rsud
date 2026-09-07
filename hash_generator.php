<?php
$plain_password = "admin"; // Ganti dengan password yang ingin Anda gunakan
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
echo "Password asli: " . $plain_password . "<br>";
echo "Hash password: " . $hashed_password;
?>