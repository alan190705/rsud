<?php
session_start();

// 1. Keamanan: Cek status login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// 2. Variabel Username
$username = $_SESSION['username'] ?? 'Admin Staf';

// --- BAGIAN TAMBAHAN UNTUK DATA REAL-TIME ---
// Idealnya Anda melakukan query ke database di sini, contoh:
// $count_pasien = mysqli_query($conn, "SELECT COUNT(*) as total FROM pasien WHERE DATE(created_at) = CURDATE()");
// $data = mysqli_fetch_assoc($count_pasien);
// $total_hari_ini = $data['total'] ?? 0;

$total_hari_ini = 24; // Contoh data statis jika belum connect database
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard SIMRS | RSUD Kita Bersama</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #0f172a;
            --accent: #3b82f6;
            --sidebar-bg: #1e293b; 
            --bg-light: #f1f5f9;
            --text-muted: #64748b;
            --sidebar-width: 260px;
            --topbar-height: 70px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-light);
            color: var(--primary);
            min-height: 100vh;
            display: flex;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--primary);
            color: white;
            position: fixed;
            top: 0; bottom: 0; left: 0;
            z-index: 1040;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 10px rgba(0,0,0,0.05);
        }

        .sidebar.collapsed { transform: translateX(-var(--sidebar-width)); }

        .sidebar-header {
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.03);
        }

        .sidebar-header i { font-size: 24px; color: var(--accent); }

        .sidebar-nav { padding: 20px 15px; flex-grow: 1; }

        .nav-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            margin: 20px 0 10px 15px;
            font-weight: 700;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }

        .nav-link i { width: 25px; font-size: 18px; margin-right: 12px; }

        .nav-link:hover, .nav-link.active {
            background: var(--accent);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        /* --- TOPBAR --- */
        .topbar {
            height: var(--topbar-height);
            background: white;
            position: fixed;
            top: 0; right: 0; left: var(--sidebar-width);
            z-index: 1030;
            display: flex;
            align-items: center;
            padding: 0 30px;
            box-shadow: 0 1px 0 #e2e8f0;
            transition: all 0.3s ease;
        }

        .btn-toggle {
            background: #f1f5f9;
            border: none;
            width: 40px; height: 40px;
            border-radius: 10px;
            color: var(--primary);
            cursor: pointer;
        }

        /* --- MAIN CONTENT --- */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            padding-top: var(--topbar-height);
            width: 100%;
            transition: all 0.3s ease;
        }

        .content-body { padding: 40px; }

        .welcome-banner {
            background: linear-gradient(135deg, var(--primary), #1e293b);
            border-radius: 24px;
            padding: 40px;
            color: white;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
        }

        /* --- ACTION CARDS --- */
        .card-menu {
            background: white;
            border: none;
            border-radius: 20px;
            padding: 25px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            height: 100%;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        }

        .card-menu:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 25px -5px rgba(0,0,0,0.05);
        }

        .icon-box {
            width: 60px; height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .bg-registrasi { background: #ecfdf5; color: #10b981; }
        .bg-data { background: #eff6ff; color: #3b82f6; }
        .bg-laporan { background: #fffbeb; color: #f59e0b; }
        .bg-admin { background: #f5f3ff; color: #8b5cf6; }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .topbar, .main-wrapper { left: 0; margin-left: 0; }
        }
    </style>
</head>
<body>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-hand-holding-medical"></i>
            <span class="fw-bold fs-5">SIMRS V.2</span>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-label">Main Menu</div>
            <a href="dashboard.php" class="nav-link active"><i class="fas fa-table-cells-large"></i> Dashboard</a>
            <a href="form_pendaftaran_pasien.php" class="nav-link"><i class="fas fa-user-plus"></i> Registrasi</a>
            <a href="data_pasien.php" class="nav-link"><i class="fas fa-address-book"></i> Data Pasien</a>
            <a href="laporan.php" class="nav-link"><i class="fas fa-chart-bar"></i> Laporan</a>
            
            <div class="nav-label">Administrator</div>
            <a href="admin_add_user.php" class="nav-link"><i class="fas fa-user-gear"></i> Sistem Admin</a>
        </nav>

        <div class="p-3">
            <a href="logout.php" class="nav-link text-danger"><i class="fas fa-right-from-bracket"></i> Keluar</a>
        </div>
    </aside>

    <div class="main-wrapper">
        
        <header class="topbar">
            <button class="btn-toggle me-3" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <h5 class="mb-0 fw-bold d-none d-md-block">NAVIGASI</h5>
            
            <div class="ms-auto dropdown">
                <div class="d-flex align-items-center gap-3" data-bs-toggle="dropdown" style="cursor: pointer;">
                    <div class="text-end d-none d-sm-block">
                        <p class="mb-0 fw-bold" style="font-size: 14px;"><?= htmlspecialchars($username); ?></p>
                        <p class="mb-0 text-muted" style="font-size: 12px;">Administrator</p>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=<?= $username ?>&background=3b82f6&color=fff" class="rounded-circle" width="40" height="40">
                </div>
            </div>
        </header>

        <main class="content-body">
            <div class="welcome-banner">
                <h1 class="fw-bold">Halo, <?= htmlspecialchars($username); ?>!</h1>
                <p class="mb-0 opacity-75">Selamat datang kembali di Sistem Informasi RSUD Kita Bersama. Pantau dan kelola data rumah sakit dengan mudah.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card-menu">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-box bg-registrasi">
                                <i class="fas fa-users"></i>
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 10px;">Hari Ini</span>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">Total Pasien Baru</p>
                            <h3 class="fw-bold mb-0"><?= $total_hari_ini; ?></h3>
                            <p class="mb-0 mt-2" style="font-size: 11px;">
                                <span class="text-success fw-bold"><i class="fas fa-arrow-up"></i> 12%</span> 
                                <span class="text-muted ms-1">dari kemarin</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card-menu">
                        <div class="icon-box bg-data mb-3">
                            <i class="fas fa-address-book"></i>
                        </div>
                        <h6 class="fw-bold">Data Pasien</h6>
                        <p class="text-muted small">Kelola dan cari rekam medis pasien.</p>
                        <a href="data_pasien.php" class="btn btn-sm btn-outline-primary border-0 fw-bold p-0">Buka Data <i class="fas fa-chevron-right ms-1"></i></a>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card-menu">
                        <div class="icon-box bg-laporan mb-3">
                            <i class="fas fa-file-lines"></i>
                        </div>
                        <h6 class="fw-bold">Laporan</h6>
                        <p class="text-muted small">Rekapitulasi kunjungan bulanan.</p>
                        <a href="laporan.php" class="btn btn-sm btn-outline-warning border-0 fw-bold p-0">Lihat Laporan <i class="fas fa-chevron-right ms-1"></i></a>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card-menu">
                        <div class="icon-box bg-admin mb-3">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <h6 class="fw-bold">Sistem Admin</h6>
                        <p class="text-muted small">Pengaturan user dan hak akses.</p>
                        <a href="admin_add_user.php" class="btn btn-sm btn-outline-secondary border-0 fw-bold p-0">Kelola <i class="fas fa-chevron-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script untuk toggle sidebar (Mobile Friendly)
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });
    </script>
</body>
</html>