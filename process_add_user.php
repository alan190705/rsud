<?php
session_start();
require 'koneksi.php'; // Pastikan koneksi PDO di sini

// Hanya admin yang bisa akses
if (!isset($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}

// Ambil data dari form
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirm_password = trim($_POST['confirm_password'] ?? '');
$role = $_POST['role'] ?? 'user';

// Validasi form
if (!$username || !$password || !$confirm_password || !$role) {
    $_SESSION['error_message'] = "Semua field wajib diisi!";
    header("Location: tambah_user.php");
    exit();
}

// Validasi password cocok
if ($password !== $confirm_password) {
    $_SESSION['error_message'] = "Password dan konfirmasi password tidak cocok!";
    header("Location: tambah_user.php");
    exit();
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // Insert user baru
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
    $stmt->execute([
        ':username' => $username,
        ':password' => $hashed_password,
        ':role' => $role
    ]);

    $_SESSION['success_message'] = "User baru berhasil ditambahkan!";
    header("Location: tambah_user.php");
    exit();
} catch (PDOException $e) {
    // Jika username sudah ada (duplicate)
    if ($e->getCode() == 23000) {
        $_SESSION['error_message'] = "Username sudah digunakan!";
    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
    }
    header("Location: tambah_user.php");
    exit();
}