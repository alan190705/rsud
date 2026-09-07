<?php
// process_delete_user.php
session_start();
require 'koneksi.php';

// Cek keamanan: Hanya Admin
if (!isset($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}

// Ambil ID dari URL
$id = $_GET['id'] ?? '';

if (empty($id)) {
    $_SESSION['error_message'] = "ID user tidak valid!";
    header("Location: data_users.php");
    exit();
}

try {
    // Optional: Cek dulu user ada atau tidak
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    if ($stmt->rowCount() === 0) {
        $_SESSION['error_message'] = "User tidak ditemukan!";
        header("Location: data_users.php");
        exit();
    }

    // Hapus user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['success_message'] = "User berhasil dihapus!";
    header("Location: data_users.php");
    exit();

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
    header("Location: data_users.php");
    exit();
}