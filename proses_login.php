<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username_input = trim($_POST['username'] ?? '');
    $password_input = trim($_POST['password'] ?? '');

    if (!$username_input || !$password_input) {
        $_SESSION['error'] = "Username dan password harus diisi.";
        header("Location: login.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username_input]);
        $user = $stmt->fetch();

        if ($user && password_verify($password_input, $user['password'])) {

            session_regenerate_id(true);
            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header($user['role'] === 'admin' ? "Location: dashboard.php" : "Location: dashboard.php");
            exit();

        } else {
            $_SESSION['error'] = "Username atau password salah.";
            header("Location: login.php");
            exit();
        }

    } catch (PDOException $e) {
        $_SESSION['error'] = "Terjadi kesalahan database: " . $e->getMessage();
        header("Location: login.php");
        exit();
    }

} else {
    header("Location: login.php");
    exit();
}
