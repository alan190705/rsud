<?php
// process_edit_user.php
session_start();
require 'koneksi.php';

// Cek keamanan: Hanya Admin
if (!isset($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}

// Ambil data dari form
$id = $_POST['id'] ?? '';
$username = trim($_POST['username'] ?? '');
$role = $_POST['role'] ?? '';
$password = $_POST['password'] ?? '';

// Validasi dasar
if (empty($id) || empty($username) || empty($role)) {
    $_SESSION['error_message'] = "Form tidak lengkap!";
    header("Location: data_users.php");
    exit();
}

try {
    // Cek apakah username sudah dipakai oleh user lain
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $id]);
    if ($stmt->rowCount() > 0) {
        $_SESSION['error_message'] = "Username '$username' sudah dipakai!";
        header("Location: data_users.php");
        exit();
    }

    // Update query
    if (!empty($password)) {
        // Jika password diubah, hash dulu
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ?, password = ? WHERE id = ?");
        $stmt->execute([$username, $role, $password_hash, $id]);
    } else {
        // Password tidak diubah
        $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
        $stmt->execute([$username, $role, $id]);
    }

    $_SESSION['success_message'] = "User berhasil diperbarui!";
    header("Location: data_users.php");
    exit();

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
    header("Location: data_users.php");
    exit();
}