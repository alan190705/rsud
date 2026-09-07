<?php
session_start();
// Perbaikan: Menggunakan null coalescing operator agar tidak muncul error 'Undefined variable'
$error = $_SESSION['error'] ?? ''; 
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi RSUD | Login Modern</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #0f172a; 
            --accent: #3b82f6;  
            --bg-light: #f8fafc;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #e2e8f0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            height: 650px;
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            animation: fadeIn 0.6s ease-out;
        }

        /* Sisi Kiri: Background Gambar dengan Overlay */
        .brand-side {
            flex: 1.2;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.85), rgba(15, 23, 42, 0.9)), 
                        url('gambar/gambar.jpg') no-repeat center center;
            background-size: cover;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: white;
            position: relative;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .brand-logo i {
            background: rgba(255, 255, 255, 0.2);
            padding: 12px;
            border-radius: 12px;
            backdrop-filter: blur(4px);
        }

        .welcome-text h1 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 20px;
            line-height: 1.1;
        }

        .welcome-text p {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.6;
            max-width: 400px;
        }

        /* Sisi Kanan: Form Login */
        .form-side {
            flex: 1;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
        }

        .form-header {
            margin-bottom: 35px;
        }

        .form-header h2 {
            font-size: 32px;
            color: var(--primary);
            font-weight: 700;
        }

        .form-header p {
            color: #64748b;
            margin-top: 8px;
        }

        /* Error Alert Modern */
        .error-msg {
            background: #fff1f2;
            color: #e11d48;
            padding: 14px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 5px solid #e11d48;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .input-wrapper input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #f1f5f9;
            border-radius: 12px;
            font-family: inherit;
            background: #f8fafc;
            transition: all 0.3s;
        }

        .input-wrapper input:focus {
            background: #fff;
            border-color: var(--accent);
            outline: none;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .options-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 25px 0;
            font-size: 14px;
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .btn-login:hover {
            background: #1e293b;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.2);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 900px) {
            .brand-side { display: none; }
            .container { max-width: 450px; height: auto; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="brand-side">
        <div class="brand-logo">
            <i class="fas fa-hospital-symbol"></i>
            <span>SIMRS DIGITAL</span>
        </div>
        
        <div class="welcome-text">
            <h1>Melayani dengan <br>Hati & Teknologi.</h1>
            <p>Sistem Informasi Manajemen Rumah Sakit terintegrasi untuk efisiensi layanan kesehatan masa depan.</p>
        </div>

        <div style="font-size: 12px; opacity: 0.7;">
            &copy; 2026 RSUD Kita Bersama. All rights reserved.
        </div>
    </div>

    <div class="form-side">
        <div class="form-header">
            <h2>Selamat Datang</h2>
            <p>Silakan masuk ke akun Anda</p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="error-msg">
            <i class="fas fa-circle-exclamation"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form action="proses_login.php" method="post">
            <div class="input-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" id="username" placeholder="Masukkan ID Pegawai" required>
                </div>
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" placeholder="••••••••" required>
                </div>
            </div>

            <div class="options-row">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #64748b;">
                    <input type="checkbox" id="togglePassword"> Tampilkan Password
                </label>
                <a href="#" style="color: var(--accent); text-decoration: none; font-weight: 600;">Lupa Sandi?</a>
            </div>

            <button type="submit" class="btn-login">
                Masuk ke Sistem <i class="fas fa-arrow-right"></i>
            </button>
        </form>
    </div>
</div>

<script>
    // Fungsi Show/Hide Password
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput = document.getElementById("password");
