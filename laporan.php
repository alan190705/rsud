<?php
session_start();
// PERBAIKAN: Jika user belum login, arahkan ke halaman dashboard
if (!isset($_SESSION['username'])) {
    header("Location: dashboard.php"); // Atau ke login.php jika Anda ingin user login dulu
    exit;
}

// PERBAIKAN: Pastikan file koneksi.php ada dan menginisialisasi variabel $pdo (untuk PDO)
// Jika koneksi.php Anda menggunakan MySQLi, Anda perlu mengubahnya atau mengadaptasi kode ini
require_once 'koneksi.php'; // Diasumsikan koneksi.php berisi $pdo = new PDO(...)

/**
 * Fungsi untuk mendapatkan data laporan pasien berdasarkan filter menggunakan PDO.
 *
 * @param PDO $pdo Objek koneksi PDO.
 * @param string|null $startDate Tanggal mulai filter (YYYY-MM-DD).
 * @param string|null $endDate Tanggal akhir filter (YYYY-MM-DD).
 * @param string|null $ruanganType Filter jenis ruangan ('Rawat Jalan', 'Rawat Inap', atau null).
 * @return array Data laporan pasien.
 */
function getLaporanData(PDO $pdo, $startDate = null, $endDate = null, $ruanganType = null) {
    $sql = "SELECT * FROM pasien";
    $conditions = [];
    $params = [];

    if ($startDate && $endDate) {
        $conditions[] = "tanggal_registrasi BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $startDate;
        $params[':end_date'] = $endDate;
    }

    if ($ruanganType) {
        if ($ruanganType == 'Rawat Inap') {
            // Asumsi: Rawat Inap adalah ruangan yang tidak 'Rawat Jalan' dan tidak kosong
            $conditions[] = "ruangan IS NOT NULL AND ruangan != '' AND ruangan != 'Rawat Jalan'";
        } elseif ($ruanganType == 'Rawat Jalan') {
            $conditions[] = "ruangan = :ruangan_jalan";
            $params[':ruangan_jalan'] = 'Rawat Jalan';
        }
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    // Tambahkan ORDER BY agar data lebih teratur
    $sql .= " ORDER BY tanggal_registrasi DESC, nama ASC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params); // Eksekusi dengan parameter terikat
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    } catch (PDOException $e) {
        error_log("Error fetching report data: " . $e->getMessage());
        // Anda bisa menampilkan pesan error yang lebih user-friendly di sini
        echo "<div class='alert alert-danger'>Terjadi kesalahan saat mengambil data laporan: " . htmlspecialchars($e->getMessage()) . "</div>";
        return [];
    }
}

// Ambil filter dari request
$filterStartDate = $_GET['start_date'] ?? '';
$filterEndDate = $_GET['end_date'] ?? '';
$filterRuanganType = $_GET['ruangan_type'] ?? '';

// Ambil data laporan berdasarkan filter yang dipilih
// PERBAIKAN: Melewatkan objek $pdo yang sudah terdefinisi dari koneksi.php
$laporanData = getLaporanData($pdo, $filterStartDate, $filterEndDate, $filterRuanganType);

// --- Hitung Statistik Ringkasan dan Data untuk Chart ---
$totalPasien = count($laporanData);
$totalPasienBaru = 0;
$totalRawatJalan = 0;
$totalRawatInap = 0;

$pasienPerJenisKelamin = ['Laki-laki' => 0, 'Perempuan' => 0];
$pasienPerStatus = ['Baru' => 0, 'Lama' => 0]; // Untuk chart status pasien

foreach ($laporanData as $pasien) {
    // Statistik Pasien Baru/Lama
    if (isset($pasien['status_pasien']) && $pasien['status_pasien'] == 'Baru') {
        $totalPasienBaru++;
        $pasienPerStatus['Baru']++;
    } else {
        // Jika status_pasien tidak 'Baru', anggap 'Lama' atau status lain
        $pasienPerStatus['Lama']++;
    }

    // Statistik Jenis Pelayanan
    if (isset($pasien['ruangan'])) {
        if ($pasien['ruangan'] == 'Rawat Jalan') {
            $totalRawatJalan++;
        } elseif (!empty($pasien['ruangan'])) { // Jika ada ruangan dan bukan Rawat Jalan
            $totalRawatInap++;
        }
    }

    // Statistik Jenis Kelamin
    if (isset($pasien['jenis_kelamin'])) {
        if (isset($pasienPerJenisKelamin[$pasien['jenis_kelamin']])) {
            $pasienPerJenisKelamin[$pasien['jenis_kelamin']]++;
        } else {
            // Tangani jika ada jenis kelamin yang tidak terduga
            $pasienPerJenisKelamin[$pasien['jenis_kelamin']] = 1;
        }
    }
}

// Data untuk chart per jenis pelayanan
$dataPelayanan = [
    'Rawat Jalan' => $totalRawatJalan,
    'Rawat Inap' => $totalRawatInap
];

// Data untuk chart per status pasien
$dataStatusPasien = [
    'Baru' => $pasienPerStatus['Baru'],
    'Lama' => $pasienPerStatus['Lama']
];

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pasien - RSUD KITA BERSAMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary-blue:  #0f172a; /* Darker, more elegant blue */
            --light-blue-bg: #eef5f9; /* Softer, muted background */
            --dark-text: #34495e; /* Richer dark text */
            --light-gray-border: #dde8f0; /* Softer border */
            --card-bg: #ffffff;
            --hover-bg: #f8fcfd; /* Very light hover for cards/rows */
            --shadow-subtle: rgba(0, 0, 0, 0.04); /* Lighter shadow */
            --shadow-medium: rgba(0, 0, 0, 0.08); /* More diffuse shadow */
            --green-success: #27ae60; /* Vibrant green */
            --red-danger: #e74c3c; /* Strong red */
            --blue-info: #3498db; /* Clear blue for info/edit */
            --accent-green: #2ecc71; /* Accent green for add button */
            --text-muted: #7f8c8d; /* Muted gray for secondary text */
            --warning-orange: #f39c12; /* Adjusted orange for warning/rawat jalan */
            --purple-status: #8e44ad; /* Custom purple for 'Lama' status */
        }

        body {
            background-color: var(--light-blue-bg);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: var(--dark-text);
            line-height: 1.6;
        }

        /* Navbar Styling */
        .navbar {
            background-color: var(--primary-blue);
            padding: 1.2rem 3rem; /* More padding */
            box-shadow: 0 8px 25px var(--shadow-medium); /* Deeper, softer shadow */
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar a {
            color: white;
            padding: 0.9rem 1.4rem; /* More padding */
            text-decoration: none;
            border-radius: 0.75rem; /* More rounded */
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        .navbar a:hover {
            background-color: #3b506b; /* Darker on hover */
            transform: translateY(-3px); /* More pronounced lift */
            box-shadow: 0 6px 15px rgba(0,0,0,0.25);
        }
        .navbar .nav-links {
            display: flex;
            gap: 25px; /* More space between links */
        }
        .navbar .fas {
            margin-right: 12px; /* More space for icon */
            font-size: 1.1em;
        }
        /* Styling untuk brand/judul di navbar */
        .navbar-brand {
            color: white !important; /* Penting untuk override */
            font-weight: 600;
            font-size: 1.3em;
            display: flex;
            align-items: center;
        }
        .navbar-brand .fas {
            margin-right: 10px;
            font-size: 1.5em;
        }
        /* Styling untuk tombol logout */
        .navbar .btn-outline-light {
            border: 2px solid white;
            padding: 0.7rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .navbar .btn-outline-light:hover {
            background-color: white;
            color: var(--primary-blue) !important;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(255,255,255,0.25);
        }

        /* Main Content Area */
        .main-content {
            flex-grow: 1;
            padding: 60px 20px;
            width: 100%;
        }

        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 50px; /* More space below header */
        }
        .page-header h3 {
            color: var(--primary-blue);
            font-size: 3.2em; /* Larger title */
            font-weight: 700;
            position: relative;
            display: inline-block;
            padding-bottom: 18px; /* Space for elegant underline */
            letter-spacing: -0.03em; /* Tighter letter spacing for elegance */
        }
        .page-header h3::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            width: 120px; /* Wider underline */
            height: 6px; /* Thicker underline */
            background-color: var(--accent-green); /* Accent green underline */
            border-radius: 4px;
        }

        /* Card Container - General styling for content cards */
        .card-container {
            background-color: var(--card-bg);
            border-radius: 25px; /* Significantly more rounded */
            box-shadow: 0 20px 50px var(--shadow-subtle); /* Soft, prominent shadow */
            padding: 40px; /* Generous padding */
            margin-bottom: 40px; /* More space below cards */
            border: 1px solid var(--light-gray-border);
        }
        .card-container h5 {
            color: var(--primary-blue);
            font-weight: 600;
            font-size: 1.5em;
            margin-bottom: 25px; /* More space below sub-headings */
        }


        /* Summary Cards - Specific styling for small info cards */
        .summary-card {
            border-radius: 18px; /* More rounded */
            padding: 25px; /* More padding */
            box-shadow: 0 8px 25px rgba(0,0,0,0.08); /* More prominent shadow */
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border: 1px solid var(--light-gray-border);
        }
        .summary-card:hover {
            transform: translateY(-8px); /* More pronounced lift */
            box-shadow: 0 12px 35px rgba(0,0,0,0.15); /* Stronger shadow on hover */
        }
        .summary-card .icon {
            font-size: 3.5em; /* Larger icon */
            margin-bottom: 20px; /* More space below icon */
        }
        .summary-card .value {
            font-size: 2.8em; /* Larger value */
            font-weight: 700;
            margin-bottom: 8px; /* More space below value */
            line-height: 1.2;
        }
        .summary-card .label {
            font-size: 1.1em; /* Larger label */
            color: var(--text-muted);
            font-weight: 500;
        }
        /* Specific colors for summary cards */
        .summary-card.total .icon, .summary-card.total .value { color: var(--primary-blue); }
        .summary-card.new .icon, .summary-card.new .value { color: var(--accent-green); }
        .summary-card.rawat-jalan .icon, .summary-card.rawat-jalan .value { color: var(--warning-orange); }
        .summary-card.rawat-inap .icon, .summary-card.rawat-inap .value { color: var(--red-danger); }


        /* Filter Bar */
        .filter-bar {
            margin-bottom: 35px; /* More space below filter bar */
            padding: 25px; /* Padding for the filter bar */
            background-color: var(--light-blue-bg); /* Slightly different background */
            border-radius: 18px; /* Rounded corners for filter bar */
            border: 1px solid var(--light-gray-border);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center; /* Center filter elements */
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); /* Subtle shadow */
        }
        .filter-bar .form-control,
        .filter-bar .form-select {
            border-radius: 10px; /* More rounded */
            border: 1px solid #c9d4e0; /* Softer border */
            padding: 0.85rem 1.2rem; /* More padding */
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            font-size: 0.95em;
        }
        .filter-bar .form-control:focus,
        .filter-bar .form-select:focus {
            border-color: var(--blue-info);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.2);
            outline: none;
        }
        .filter-bar .input-group {
            flex-grow: 1;
            max-width: 280px; /* Wider input fields */
        }
        .filter-bar .input-group-text {
            background-color: white;
            border: 1px solid #c9d4e0;
            border-right: none;
            border-radius: 10px 0 0 10px;
            color: var(--primary-blue);
        }
        .filter-bar .btn {
            border-radius: 10px; /* More rounded */
            padding: 0.85rem 1.8rem; /* More padding */
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            font-size: 0.95em;
        }
        .filter-bar .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        .filter-bar .btn-primary:hover {
            background-color: #23313d;
            border-color: #23313d;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }
        .filter-bar .btn-outline-secondary {
            border-color: var(--text-muted);
            color: var(--text-muted);
        }
        .filter-bar .btn-outline-secondary:hover {
            background-color: var(--text-muted);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }
        .filter-bar .form-select.w-auto {
            flex-grow: 0;
            max-width: 250px;
        }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 380px; /* Slightly taller charts */
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .chart-container canvas {
            max-height: 100%;
            max-width: 100%;
        }
        .chart-container .no-data-message {
            color: var(--text-muted);
            font-size: 1.2em;
            text-align: center;
            padding: 30px;
        }

        /* Table Styling */
        .table-responsive {
            margin-top: 25px;
            border-radius: 15px; /* Rounded corners for the responsive container */
            overflow: hidden; /* Ensures table corners are respected */
        }
        .table {
            --bs-table-bg: transparent; /* Remove default table background */
            border-collapse: separate;
            border-spacing: 0 12px; /* Space between rows */
            width: 100%;
        }
        .table thead {
            background-color: var(--primary-blue);
            color: white;
        }
        .table th {
            padding: 18px 25px; /* More padding */
            font-weight: 600;
            border: none;
            text-align: left;
            white-space: nowrap;
            font-size: 0.95em;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .table thead tr:first-child th:first-child { border-top-left-radius: 15px; }
        .table thead tr:first-child th:last-child { border-top-right-radius: 15px; }

        .table tbody tr {
            background-color: var(--card-bg);
            border: 1px solid var(--light-gray-border);
            border-radius: 15px; /* Rounded corners for rows */
            transition: all 0.2s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); /* Subtle shadow for rows */
        }
        .table tbody tr:hover {
            background-color: var(--hover-bg);
            transform: translateY(-5px); /* More pronounced lift */
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); /* Stronger shadow on hover */
        }
        .table tbody tr td {
            padding: 15px 25px; /* More padding */
            vertical-align: middle;
            font-size: 0.9em;
            border-top: none;
            border-bottom: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 180px; /* Limit width to enforce ellipsis */
            color: var(--dark-text);
        }
        .table tbody tr td:first-child {
            border-left: 1px solid var(--light-gray-border);
            border-top-left-radius: 15px;
            border-bottom-left-radius: 15px;
        }
        .table tbody tr td:last-child {
            border-right: 1px solid var(--light-gray-border);
            border-top-right-radius: 15px;
            border-bottom-right-radius: 15px;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #fcfdff; /* Lighter stripe */
        }
        .table-striped tbody tr:nth-of-type(odd):hover {
            background-color: var(--hover-bg);
        }

        /* Badge Status */
        .badge {
            font-size: 0.8em;
            padding: 0.7em 1.2em; /* More padding */
            border-radius: 0.6em; /* More rounded */
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            line-height: 1;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .badge.bg-success { background-color: var(--accent-green) !important; } /* For 'Baru' */
        .badge.bg-primary { background-color: var(--purple-status) !important; } /* For 'Lama' */


        /* Footer Styling */
        .footer {
            background-color: var(--primary-blue);
            color: white;
            padding: 35px; /* More padding */
            text-align: center;
            font-size: 0.95em;
            box-shadow: 0 -6px 20px rgba(0,0,0,0.08);
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .navbar { padding: 1rem 2rem; flex-direction: column; align-items: flex-start;}
            .navbar .nav-links { gap: 15px; margin-top: 15px; width: 100%; flex-wrap: wrap;}
            .navbar a { padding: 0.8rem 1.2rem; font-size: 0.9em; }
            .navbar .fas { margin-right: 10px; font-size: 1em; }
            .navbar-brand { margin-bottom: 10px; }
            .navbar .btn-outline-light { margin-top: 15px; width: 100%; }


            .main-content { padding: 40px 0; }
            .page-header h3 { font-size: 2.5em; margin-bottom: 40px; padding-bottom: 15px; }
            .page-header h3::after { width: 90px; height: 5px; }
            .card-container { padding: 30px; border-radius: 20px; }
            .card-container h5 { font-size: 1.3em; margin-bottom: 20px; }
            .summary-card { padding: 20px; border-radius: 15px; }
            .summary-card .icon { font-size: 3em; margin-bottom: 15px; }
            .summary-card .value { font-size: 2.2em; margin-bottom: 5px; }
            .summary-card .label { font-size: 1em; }

            .filter-bar { flex-direction: column; align-items: stretch; padding: 20px; border-radius: 15px; }
            .filter-bar > *, .filter-bar .input-group {
                width: 100% !important;
                max-width: none !important;
            }
            .filter-bar .form-select.w-auto {
                width: 100% !important;
                max-width: none !important;
            }
            .filter-bar .btn { padding: 0.75rem 1.5rem; font-size: 0.9em; }
            .filter-bar .input-group-text { border-radius: 8px 0 0 8px; }
            .filter-bar .form-control, .filter-bar .form-select { border-radius: 8px; padding: 0.7rem 1rem;}


            .chart-container { height: 300px; }
            .table th, .table td { padding: 12px 18px; font-size: 0.85em; }
            .table tbody tr { border-radius: 12px; }
            .table tbody tr td:first-child { border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
            .table tbody tr td:last-child { border-top-right-radius: 12px; border-bottom-right-radius: 12px; }
            .footer { padding: 30px; font-size: 0.9em; }
        }

        @media (max-width: 768px) {
            .navbar { flex-direction: column; align-items: flex-start; padding: 0.8rem 1rem; }
            .navbar .nav-links { flex-direction: column; align-items: flex-start; gap: 8px; width: 100%; }
            .navbar a { width: 100%; text-align: left; padding: 0.7rem 1rem; font-size: 0.85em; }
            .navbar .ms-auto { margin-top: 15px; width: 100%; }

            .main-content { padding: 30px 0; }
            .page-header h3 { font-size: 2em; margin-bottom: 30px; padding-bottom: 12px; }
            .page-header h3::after { width: 80px; height: 4px; }
            .card-container { padding: 20px; border-radius: 18px; }
            .card-container h5 { font-size: 1.2em; margin-bottom: 15px; }

            .summary-card { padding: 15px; border-radius: 10px; }
            .summary-card .icon { font-size: 2.8em; margin-bottom: 10px; }
            .summary-card .value { font-size: 2em; margin-bottom: 3px; }
            .summary-card .label { font-size: 0.9em; }

            .filter-bar { padding: 15px; border-radius: 12px; }
            .filter-bar .btn { padding: 0.6em 1.2em; font-size: 0.85em; }
            .filter-bar .form-control, .filter-bar .form-select { padding: 0.6em 1em; font-size: 0.9em; border-radius: 6px; }
            .filter-bar .input-group-text { border-radius: 6px 0 0 6px; }

            .chart-container { height: 250px; }
            .table th, .table td { padding: 10px 15px; font-size: 0.75em; }
            .badge { font-size: 0.7em; padding: 0.5em 1em; border-radius: 0.4em; }
            .footer { padding: 25px; font-size: 0.85em; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="navbar-brand">
        <i class="fas fa-hospital-alt"></i> RSUD SI
    </a>
    <div class="nav-links">
        <a href="dashboard.php" title="Dashboard"><i class="fas fa-home"></i>Dashboard</a>
        <a href="form_pendaftaran_pasien.php" title="Registrasi Pasien"><i class="fas fa-user-plus"></i>Registrasi Pasien</a>
        <a href="data_pasien.php" title="Data Pasien"><i class="fas fa-notes-medical"></i>Data Pasien</a>
        <a href="laporan.php" title="Laporan & Statistik"><i class="fas fa-chart-bar"></i>Laporan</a>
    </div>
    <a href="logout.php" class="btn btn-outline-light d-flex align-items-center" title="Logout">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</nav>

<div class="main-content container-fluid">
    <div class="page-header">
        <h3>Laporan Data Pasien</h3>
    </div>

    <div class="card-container">
        <div class="row row-cols-1 row-cols-md-4 g-4 text-center">
            <div class="col">
                <div class="summary-card total">
                    <i class="fas fa-users icon"></i>
                    <div class="value"><?= $totalPasien; ?></div>
                    <div class="label">Total Pasien (Filter)</div>
                </div>
            </div>
            <div class="col">
                <div class="summary-card new">
                    <i class="fas fa-user-tag icon"></i>
                    <div class="value"><?= $totalPasienBaru; ?></div>
                    <div class="label">Pasien Baru (Filter)</div>
                </div>
            </div>
            <div class="col">
                <div class="summary-card rawat-jalan">
                    <i class="fas fa-walking icon"></i>
                    <div class="value"><?= $totalRawatJalan; ?></div>
                    <div class="label">Total Rawat Jalan (Filter)</div>
                </div>
            </div>
            <div class="col">
                <div class="summary-card rawat-inap">
                    <i class="fas fa-bed icon"></i>
                    <div class="value"><?= $totalRawatInap; ?></div>
                    <div class="label">Total Rawat Inap (Filter)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-container">
        <h5 class="mb-3">Filter Laporan</h5>
        <form method="GET" action="laporan.php" class="filter-bar">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                <input type="date" class="form-control" name="start_date" value="<?= htmlspecialchars($filterStartDate); ?>" title="Tanggal Mulai">
            </div>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                <input type="date" class="form-control" name="end_date" value="<?= htmlspecialchars($filterEndDate); ?>" title="Tanggal Akhir">
            </div>
            <select name="ruangan_type" class="form-select w-auto" title="Jenis Pelayanan">
                <option value="">Semua Pelayanan</option>
                <option value="Rawat Jalan" <?= ($filterRuanganType == 'Rawat Jalan') ? 'selected' : ''; ?>>Rawat Jalan</option>
                <option value="Rawat Inap" <?= ($filterRuanganType == 'Rawat Inap') ? 'selected' : ''; ?>>Rawat Inap</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-2"></i>Terapkan Filter</button>
            <a href="laporan.php" class="btn btn-outline-secondary"><i class="fas fa-sync-alt me-2"></i>Reset Filter</a>
        </form>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card-container h-100">
                <h5 class="mb-3">Distribusi Pasien Berdasarkan Jenis Kelamin (Data Terfilter)</h5>
                <div class="chart-container">
                    <?php if ($totalPasien > 0): ?>
                        <canvas id="genderChart"></canvas>
                    <?php else: ?>
                        <p class="no-data-message">Tidak ada data pasien untuk ditampilkan pada grafik jenis kelamin.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-container h-100">
                <h5 class="mb-3">Jumlah Pasien Berdasarkan Jenis Pelayanan (Data Terfilter)</h5>
                <div class="chart-container">
                    <?php if ($totalPasien > 0): ?>
                        <canvas id="pelayananChart"></canvas>
                    <?php else: ?>
                        <p class="no-data-message">Tidak ada data pasien untuk ditampilkan pada grafik jenis pelayanan.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
           <div class="col-md-6">
            <div class="card-container h-100">
                <h5 class="mb-3">Distribusi Pasien Berdasarkan Status (Data Terfilter)</h5>
                <div class="chart-container">
                    <?php if ($totalPasien > 0): ?>
                        <canvas id="statusChart"></canvas>
                    <?php else: ?>
                        <p class="no-data-message">Tidak ada data pasien untuk ditampilkan pada grafik status pasien.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card-container">
        <h5 class="mb-3">Rincian Data Pasien Terdaftar (Berdasarkan Filter)</h5>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle" id="laporanPasienTable">
                <thead class="table-primary">
                    <tr>
                        <th>No Kartu</th>
                        <th>NIK</th>
                        <th>Nama Pasien</th>
                        <th>Jenis Kelamin</th>
                        <th>Tgl Lahir</th>
                        <th>Tempat Lahir</th>
                        <th>Alamat</th>
                        <th>No HP</th>
                        <th>Status</th>
                        <th>Jenis Pelayanan</th>
                        <th>Ruangan Spesifik</th>
                        <th>Kelas</th>
                        <th>Bed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($laporanData)) {
                        foreach ($laporanData as $row) :
                            $jenisPelayanan = '';
                            $ruanganSpesifik = htmlspecialchars($row['ruangan'] ?: '-');
                            // Determine Jenis Pelayanan based on 'ruangan' column
                            if ($row['ruangan'] == 'Rawat Jalan') {
                                $jenisPelayanan = 'Rawat Jalan';
                            } elseif (!empty($row['ruangan'])) {
                                $jenisPelayanan = 'Rawat Inap';
                            } else {
                                $jenisPelayanan = 'Belum Ditentukan';
                            }
                    ?>
                            <tr>
                                <td><?= htmlspecialchars($row['no_kartu']); ?></td>
                                <td><?= htmlspecialchars($row['nik']); ?></td>
                                <td><?= htmlspecialchars($row['nama']); ?></td>
                                <td><?= htmlspecialchars($row['jenis_kelamin']); ?></td>
                                <td><?= htmlspecialchars(date('d-M-Y', strtotime($row['tgl_lahir']))); ?></td>
                                <td><?= htmlspecialchars($row['tempat_lahir']); ?></td>
                                <td><?= htmlspecialchars($row['alamat']); ?></td>
                                <td><?= htmlspecialchars($row['no_hp'] ?: '-'); ?></td>
                                <td>
                                    <?php
                                        // Use custom colors for 'Baru' (success green) and 'Lama' (purple)
                                        $statusClass = ($row['status_pasien'] == 'Baru') ? 'bg-success' : 'bg-primary';
                                    ?>
                                    <span class="badge <?= $statusClass; ?>"><?= htmlspecialchars($row['status_pasien']); ?></span>
                                </td>
                                <td><?= $jenisPelayanan; ?></td>
                                <td><?= $ruanganSpesifik; ?></td>
                                <td><?= htmlspecialchars($row['kelas'] ?: '-'); ?></td>
                                <td><?= htmlspecialchars($row['bed'] ?: '-'); ?></td>
                            </tr>
                    <?php
                        endforeach;
                    } else {
                        echo '<tr><td colspan="13" class="text-center py-4 text-muted">Tidak ada data pasien yang cocok dengan filter yang diterapkan.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="container">
        &copy; <?= date('Y') ?> Sistem Informasi Rumah Sakit. All rights reserved.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Pastikan ada data sebelum mencoba menggambar chart
    const totalPasien = <?= $totalPasien; ?>;

    // --- Chart: Jenis Kelamin ---
    if (totalPasien > 0) {
        const pasienPerJenisKelamin = <?= json_encode($pasienPerJenisKelamin); ?>;
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: ['Laki-laki', 'Perempuan'],
                datasets: [{
                    label: 'Jumlah Pasien',
                    data: [
                        pasienPerJenisKelamin['Laki-laki'] || 0,
                        pasienPerJenisKelamin['Perempuan'] || 0
                    ],
                    backgroundColor: [
                        'rgba(44, 62, 80, 0.8)', // Primary blue (Laki-laki)
                        'rgba(52, 152, 219, 0.8)' // Info blue (Perempuan)
                    ],
                    borderColor: [ '#fff', '#fff' ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: 'Poppins' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                const label = tooltipItem.label;
                                const value = tooltipItem.raw;
                                const total = tooltipItem.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(2) + '%' : '0%';
                                return `${label}: ${value} (${percentage})`;
                            }
                        }
                    }
                }
            }
        });
    }

    // --- Chart: Jenis Pelayanan ---
    if (totalPasien > 0) {
        const dataPelayanan = <?= json_encode($dataPelayanan); ?>;
        const pelayananCtx = document.getElementById('pelayananChart').getContext('2d');
        new Chart(pelayananCtx, {
            type: 'bar',
            data: {
                labels: ['Rawat Jalan', 'Rawat Inap'],
                datasets: [{
                    label: 'Jumlah Pasien',
                    data: [
                        dataPelayanan['Rawat Jalan'] || 0,
                        dataPelayanan['Rawat Inap'] || 0
                    ],
                    backgroundColor: [
                        'rgba(243, 156, 18, 0.8)', // Warning orange (Rawat Jalan)
                        'rgba(231, 76, 60, 0.8)' // Danger red (Rawat Inap)
                    ],
                    borderColor: [ '#fff', '#fff' ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return `${tooltipItem.label}: ${tooltipItem.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah Pasien',
                            font: { family: 'Poppins' }
                        },
                        ticks: {
                            callback: function(value) { if (Number.isInteger(value)) { return value; } },
                            font: { family: 'Poppins' }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Jenis Pelayanan',
                            font: { family: 'Poppins' }
                        },
                        ticks: {
                            font: { family: 'Poppins' }
                        }
                    }
                }
            }
        });
    }

    // --- Chart: Status Pasien (Baru/Lama) ---
    if (totalPasien > 0) {
        const dataStatusPasien = <?= json_encode($dataStatusPasien); ?>;
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Baru', 'Lama'],
                datasets: [{
                    label: 'Jumlah Pasien',
                    data: [
                        dataStatusPasien['Baru'] || 0,
                        dataStatusPasien['Lama'] || 0
                    ],
                    backgroundColor: [
                        'rgba(46, 204, 113, 0.8)', // Accent green (Baru)
                        'rgba(142, 68, 173, 0.8)' // Custom purple (Lama)
                    ],
                    borderColor: [ '#fff', '#fff' ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: 'Poppins' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                const label = tooltipItem.label;
                                const value = tooltipItem.raw;
                                const total = tooltipItem.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(2) + '%' : '0%';
                                return `${label}: ${value} (${percentage})`;
                            }
                        }
                    }
                }
            }
        });
    }
</script>

</body>
</html>