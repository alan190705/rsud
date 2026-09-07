<?php
session_start();
// PERBAIKAN: Jika user belum login, arahkan ke halaman login
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Arahkan ke halaman login
    exit;
}

// Include your database connection file (must provide $pdo object)
require_once 'koneksi.php';

$patients = []; // Array untuk menyimpan data pasien
$message = '';
$message_type = '';

// Handle delete operation if requested
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['no_kartu'])) {
    $no_kartu_to_delete = $_GET['no_kartu'];
    try {
        $stmt_delete = $pdo->prepare("DELETE FROM pasien WHERE no_kartu = :no_kartu");
        $stmt_delete->bindParam(':no_kartu', $no_kartu_to_delete);
        if ($stmt_delete->execute()) {
            $message = "Data pasien dengan No Kartu " . htmlspecialchars($no_kartu_to_delete) . " berhasil dihapus.";
            $message_type = "success";
        } else {
            $message = "Gagal menghapus data pasien.";
            $message_type = "danger";
        }
    } catch (PDOException $e) {
        error_log("Error deleting patient: " . $e->getMessage());
        $message = "Terjadi kesalahan database saat menghapus data.";
        $message_type = "danger";
    }
}

// Fetch all patient data from the database using PDO
try {
    // SELECT semua kolom yang mungkin dibutuhkan
    $stmt_select_all = $pdo->query("SELECT * FROM pasien ORDER BY tanggal_registrasi DESC, nama ASC");
    $patients = $stmt_select_all->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching patient data: " . $e->getMessage());
    $message = "Terjadi kesalahan saat mengambil data pasien dari database.";
    $message_type = "danger";
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pasien</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">

    <style>
        :root {
            --primary-blue: #0f172a; /* Darker, more elegant blue */
            --light-blue-bg: #eef5f9; /* Softer, muted background */
            --dark-text:rgb(9, 13, 18); /* Richer dark text */
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
        .navbar {
            background-color: var(--primary-blue);
            padding: 1.2rem 3rem; /* More padding */
            box-shadow: 0 8px 25px var(--shadow-medium); /* Deeper, softer shadow */
            z-index: 1000;
            /* Tambahan: Menggunakan flexbox dari Bootstrap untuk penataan */
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
            background-color:rgb(83, 146, 228); /* Darker on hover */
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


        .main-content {
            flex-grow: 1;
            padding: 60px 0; /* More vertical padding */
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .data-card {
            background-color: var(--card-bg);
            padding: 45px 55px; /* Generous padding */
            border-radius: 25px; /* Significantly more rounded */
            box-shadow: 0 20px 50px var(--shadow-subtle); /* Soft, prominent shadow */
            margin-bottom: 50px;
            border: 1px solid var(--light-gray-border);
            width: 95%;
            overflow-x: auto; /* Ensures horizontal scrolling if content overflows */
        }

        /* Custom scrollbar for data-card (Webkit browsers only) */
        .data-card::-webkit-scrollbar {
            height: 10px; /* Thicker scrollbar */
            background-color: #f5f5f5;
        }

        .data-card::-webkit-scrollbar-thumb {
            background-color: #b0b0b0; /* Grayish thumb */
            border-radius: 10px;
            border: 2px solid #f5f5f5;
        }

        .data-card::-webkit-scrollbar-thumb:hover {
            background-color: #999;
        }

        h2 {
            color: var(--primary-blue); /* Use primary blue for title */
            font-size: 3.2em; /* Larger title */
            font-weight: 700;
            text-align: center;
            margin-bottom: 60px; /* More space below title */
            position: relative;
            display: inline-block;
            padding-bottom: 18px; /* Space for elegant underline */
            width: 100%;
            letter-spacing: -0.03em; /* Tighter letter spacing for elegance */
        }
        h2::after {
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

        .alert {
            margin-bottom: 40px;
            border-radius: 12px;
            max-width: 90%;
            width: 100%;
            text-align: center;
            font-weight: 500;
            font-size: 1.05em;
            padding: 15px 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .alert-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }


        /* DataTables Custom Style Enhancements */
        table.dataTable {
            width: 100% !important;
            margin-bottom: 2em !important; /* More space below table */
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 15px; /* Rounded corners for the whole table */
            overflow: hidden; /* Ensures rounded corners are applied */
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); /* Subtle shadow for the table itself */
        }

        table.dataTable thead th {
            background-color: var(--primary-blue);
            color: white;
            padding: 15px 22px; /* Generous padding */
            border-bottom: 1px solid #ddd;
            text-align: left;
            font-weight: 600;
            font-size: 0.95em;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            white-space: nowrap; /* Prevent wrapping in headers */
        }
        /* Rounded corners for table header */
        table.dataTable thead tr:first-child th:first-child { border-top-left-radius: 15px; }
        table.dataTable thead tr:first-child th:last-child { border-top-right-radius: 15px; }


        table.dataTable tbody td {
            padding: 12px 22px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
            font-size: 0.9em;
            white-space: nowrap; /* Prevent text wrapping in cells by default */
            color: var(--dark-text);
            transition: background-color 0.2s ease;
        }
        table.dataTable tbody tr:hover {
            background-color: var(--hover-bg);
            cursor: pointer;
            transform: translateY(-2px); /* More pronounced lift effect */
            box-shadow: 0 6px 15px rgba(0,0,0,0.1); /* Subtle shadow on hover */
        }
        table.dataTable tbody tr:last-child td {
            border-bottom: none; /* No border on last row's cells */
        }
        /* Mengomentari ini karena bisa konflik dengan DataTables Responsive/ScrollX */
        /* table.dataTable tbody tr:last-child td:first-child { border-bottom-left-radius: 15px; } */
        /* table.dataTable tbody tr:last-child td:last-child { border-bottom-right-radius: 15px; } */


        /* DataTables Pagination & Search */
        .dataTables_wrapper .dataTables_filter label,
        .dataTables_wrapper .dataTables_length label {
            color: var(--dark-text);
            font-weight: 500;
            font-size: 1em;
            margin-bottom: 10px; /* Space below labels */
        }
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid var(--light-gray-border);
            border-radius: 10px; /* More rounded */
            padding: 10px 18px; /* More padding */
            margin-left: 0.8em; /* More spacing */
            font-size: 0.95em;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            width: 250px; /* Fixed width for search input */
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.25rem rgba(44, 62, 80, 0.2);
            outline: none;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 1px solid var(--light-gray-border);
            border-radius: 10px;
            padding: 10px 18px;
            font-size: 0.95em;
            margin-left: 0.5em;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.8em 1.6em; /* Larger, more elegant buttons */
            border-radius: 10px;
            margin: 0 8px; /* More spacing */
            background-color: var(--blue-info);
            color: white !important;
            border: none;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 4px 12px var(--shadow-subtle);
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
            background-color: #2980b9; /* Darker on hover */
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background-color: var(--primary-blue) !important; /* Use primary blue for current */
            color: white !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            background-color: #eef1f4; /* Lighter disabled state */
            color: #b9c1cb !important; /* Lighter disabled text */
            cursor: not-allowed;
            box-shadow: none;
        }

        .btn-action {
            margin-right: 10px; /* More space between buttons */
            font-size: 0.85em; /* Slightly larger for clarity */
            padding: 0.7em 1.4em; /* More generous padding */
            border-radius: 12px; /* More rounded */
            transition: all 0.2s ease;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
            display: inline-flex; /* For icon alignment */
            align-items: center;
            justify-content: center;
        }
        .btn-edit {
            background-color: var(--blue-info);
            color: white;
            border-color: var(--blue-info);
        }
        .btn-edit:hover {
            background-color: #2980b9;
            border-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }
        .btn-delete {
            background-color: var(--red-danger);
            color: white;
            border-color: var(--red-danger);
        }
        .btn-delete:hover {
            background-color: #c0392b;
            border-color: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }
        /* Styling for the new "Pengkajian Medis" button */
        .btn-medis {
            background-color: #f39c12; /* Orange color for medical assessment */
            color: white;
            border-color: #f39c12;
            margin-right: 10px; /* Keep consistent spacing */
        }
        .btn-medis:hover {
            background-color: #e67e22; /* Darker orange on hover */
            border-color: #e67e22;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }

        .btn-add-patient {
            background-color: var(--accent-green);
            border-color: var(--accent-green);
            color: white;
            padding: 1em 2.5em; /* Larger and more prominent */
            font-size: 1.1em;
            font-weight: 600;
            border-radius: 15px; /* Even more rounded */
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 15px rgba(46, 204, 113, 0.3);
        }
        .btn-add-patient:hover {
            background-color: #27ae60;
            border-color: #27ae60;
            transform: translateY(-4px); /* More pronounced lift */
            box-shadow: 0 10px 25px rgba(46, 204, 113, 0.5);
        }
        .btn-add-patient i {
            margin-right: 12px;
            font-size: 1.2em;
        }

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

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .navbar { padding: 1rem 2rem; flex-direction: column; align-items: flex-start;}
            .navbar .nav-links { gap: 15px; margin-top: 15px; width: 100%; flex-wrap: wrap;}
            .navbar a { padding: 0.8rem 1.2rem; font-size: 0.9em; }
            .navbar .fas { margin-right: 10px; font-size: 1em; }
            .navbar-brand { margin-bottom: 10px; }
            .navbar .btn-outline-light { margin-top: 15px; width: 100%; }


            .main-content { padding: 40px 0; }
            .data-card { padding: 30px 40px; border-radius: 20px; }
            h2 { font-size: 2.5em; margin-bottom: 40px; padding-bottom: 15px; }
            h2::after { width: 90px; height: 5px; }
            .alert { font-size: 0.95em; padding: 12px 20px; }

            table.dataTable thead th { padding: 12px 18px; font-size: 0.85em; }
            table.dataTable tbody td { padding: 10px 18px; font-size: 0.8em; }
            .dataTables_wrapper .dataTables_filter input { width: 200px; padding: 8px 15px; font-size: 0.9em; }
            .dataTables_wrapper .dataTables_length select { padding: 8px 15px; font-size: 0.9em; }
            .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0.6em 1.2em; font-size: 0.9em; margin: 0 6px; border-radius: 8px; }
            .btn-action { font-size: 0.75em; padding: 0.5em 1em; margin-right: 8px; border-radius: 10px; }
            .btn-add-patient { padding: 0.9em 2.2em; font-size: 1em; border-radius: 12px; }
            .btn-add-patient i { margin-right: 10px; font-size: 1.1em; }
            .footer { padding: 30px; font-size: 0.9em; }
        }

        @media (max-width: 768px) {
            .navbar { flex-direction: column; align-items: flex-start; padding: 0.8rem 1rem; }
            .navbar .nav-links { flex-direction: column; align-items: flex-start; gap: 8px; width: 100%; }
            .navbar a { width: 100%; text-align: left; padding: 0.7rem 1rem; font-size: 0.85em; }
            .navbar .ms-auto { margin-top: 15px; width: 100%; }

            .main-content { padding: 30px 0; }
            .data-card { padding: 20px 25px; border-radius: 15px; width: 98%; }
            h2 { font-size: 2em; margin-bottom: 30px; padding-bottom: 12px; }
            h2::after { width: 80px; height: 4px; }
            .alert { font-size: 0.9em; padding: 10px 15px; margin-bottom: 25px; }

            table.dataTable thead th,
            table.dataTable tbody td {
                padding: 8px 12px;
                font-size: 0.8em;
            }
            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_length {
                display: block; /* Stack search and entries */
                text-align: center;
                margin-bottom: 15px;
            }
            .dataTables_wrapper .dataTables_filter label,
            .dataTables_wrapper .dataTables_length label {
                display: block; /* Ensure label is on its own line */
                margin-bottom: 5px;
            }
            .dataTables_wrapper .dataTables_filter input,
            .dataTables_wrapper .dataTables_length select {
                width: 80%; /* Make them wider on small screens */
                margin: 0 auto 10px auto; /* Center and add bottom margin */
                display: block;
                padding: 8px 12px;
            }
            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate {
                text-align: center;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button {
                padding: 0.4em 0.8em;
                font-size: 0.8em;
                margin: 0 4px;
                border-radius: 6px;
            }
            .btn-action {
                font-size: 0.65em;
                padding: 0.3em 0.7em;
                margin-right: 5px;
                border-radius: 8px;
            }
            .btn-add-patient {
                padding: 0.8em 2em;
                font-size: 0.9em;
                border-radius: 10px;
                width: 100%; /* Full width on mobile */
                margin-top: 15px; /* Adjust top margin for mobile */
            }
            .btn-add-patient i { margin-right: 8px; font-size: 1em; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="navbar-brand">
        <i class="fas fa-hospital-alt"></i> RSUD
    </a>
    <div class="nav-links">
        <a href="dashboard.php" title="Dashboard"><i class="fas fa-home"></i>Dashboard</a>
        <a href="form_pendaftaran_pasien.php" title="Registrasi Pasien"><i class="fas fa-user-plus"></i>Registrasi Pasien</a>
        <a href="data_pasien.php" title="Data Pasien"><i class="fas fa-notes-medical"></i>Data Pasien</a>
        <a href="laporan.php" title="Laporan"><i class="fas fa-chart-bar"></i>Laporan</a>
    </div>
    <a href="logout.php" class="btn btn-outline-light d-flex align-items-center" title="Logout">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</nav>

<div class="main-content container-fluid">
    <h2 class="text-center">Data Pasien Terdaftar</h2>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show mx-auto" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="data-card">
        <div class="d-flex justify-content-end mb-4">
            <a href="form_pendaftaran_pasien.php" class="btn btn-add-patient">
                <i class="fas fa-plus-circle"></i> Tambah Pasien Baru
            </a>
        </div>
        <table id="patientTable" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>No. Kartu</th>
                    <th>Nama</th>
                    <th>NIK</th>
                    <th>Tanggal Lahir</th>
                    <th>Jenis Kelamin</th>
                    <th>No. HP</th>
                    <th>No. BPJS</th>
                    <th>No. KK</th>
                    <th>Tanggal Registrasi</th>
                    <th>Status Pasien</th>
                    <th>Instalasi</th>
                    <th>Ruangan</th>
                    <th>Kelas</th>
                    <th>Bed</th>
                    <th>Kelompok Pasien</th>
                    <th class="text-center">Aksi</th>
                    <th class="text-center">Pengkajian</th> </tr>
            </thead>
            <tbody>
<?php if (!empty($patients)): ?>
<?php foreach ($patients as $patient): ?>
<tr>
    <td><?= htmlspecialchars($patient['no_kartu']) ?></td>
    <td><?= htmlspecialchars($patient['nama']) ?></td>
    <td><?= htmlspecialchars($patient['nik']) ?></td>
    <td><?= date('d-m-Y', strtotime($patient['tanggal_lahir'])) ?></td>
    <td><?= htmlspecialchars($patient['jenis_kelamin']) ?></td>
    <td><?= htmlspecialchars($patient['no_hp'] ?? '-') ?></td>
    <td><?= htmlspecialchars($patient['no_bpjs'] ?? '-') ?></td>
    <td><?= htmlspecialchars($patient['no_kk'] ?? '-') ?></td>
    <td><?= date('d-m-Y', strtotime($patient['tanggal_registrasi'])) ?></td>
    <td><?= htmlspecialchars($patient['status_pasien'] ?? '-') ?></td>
    <td><?= htmlspecialchars($patient['instalasi']) ?></td>
    <td><?= htmlspecialchars($patient['ruangan'] ?? '-') ?></td>
    <td><?= htmlspecialchars($patient['kelas'] ?? '-') ?></td>
    <td><?= htmlspecialchars($patient['bed'] ?? '-') ?></td>
    <td><?= htmlspecialchars($patient['kelompok_pasien'] ?? '-') ?></td>

    <td class="text-center">
        <a href="edit_pasien.php?no_kartu=<?= urlencode($patient['no_kartu']) ?>" class="btn btn-edit btn-action">
            <i class="fas fa-edit"></i> Edit
        </a>

        <a href="#" onclick="confirmDelete('<?= urlencode($patient['no_kartu']) ?>','<?= htmlspecialchars($patient['nama']) ?>')" class="btn btn-delete btn-action">
            <i class="fas fa-trash-alt"></i> Hapus
        </a>
    </td>

    <td class="text-center">
        <a href="pengkajian_medis.php?no_kartu=<?= urlencode($patient['no_kartu']) ?>" class="btn btn-medis btn-action">
            <i class="fas fa-notes-medical"></i> Pengkajian
        </a>
    </td>

    </tr>
    <?php endforeach; ?>

    <?php else: ?>
    <tr>
    <td colspan="17" class="text-center">Tidak ada data pasien ditemukan.</td>
    </tr>
    <?php endif; ?>
    </tbody>
            </table>
        </div>
    </div>

<footer class="footer">
    <div class="container">
        &copy; <?= date('Y') ?> Sistem Informasi Rumah Sakit. All rights reserved.
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready( function () {
        // Initialize DataTable with horizontal scrolling and no responsive collapsing
        $('#patientTable').DataTable({
            "responsive": false, // Disable DataTables responsive collapsing
            "scrollX": true,   // Enable horizontal scrolling for the table
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.3/i18n/id.json" // Indonesian language pack
            },
            // Anda bisa mengaktifkan columnDefs jika perlu mengatur lebar kolom tertentu secara eksplisit
            /* "columnDefs": [
                { "width": "100px", "targets": 0 }, // No. Kartu
                { "width": "150px", "targets": 1 }, // Nama
                { "width": "120px", "targets": 2 }, // NIK
                { "width": "120px", "targets": 3 }, // Tgl. Lahir
                { "width": "80px", "targets": 4 },  // JK
                { "width": "120px", "targets": 5 }, // No. HP
                { "width": "120px", "targets": 6 }, // No. BPJS
                { "width": "120px", "targets": 7 }, // No. KK
                { "width": "120px", "targets": 8 }, // Tgl. Reg.
                { "width": "100px", "targets": 9 }, // Status Pasien
                { "width": "100px", "targets": 10 }, // Ruangan
                { "width": "120px", "targets": 11 }, // Sub Ruangan
                { "width": "80px", "targets": 12 }, // Kelas
                { "width": "80px", "targets": 13 }, // Bed
                { "width": "120px", "targets": 14 }, // Kelompok Pasien
                { "width": "180px", "targets": 15, "orderable": false, "searchable": false }, // Aksi
                { "width": "120px", "targets": 16, "orderable": false, "searchable": false } // Pengkajian (New)
            ] */
        });

        // Handle alert dismiss
        const alertElement = document.querySelector('.alert');
        if (alertElement) {
            new bootstrap.Alert(alertElement);
        }
    });

    function confirmDelete(no_kartu, nama_pasien) {
        const userConfirmed = window.confirm(`Apakah Anda yakin ingin menghapus data pasien ${nama_pasien} (No. Kartu: ${no_kartu})?`);
        if (userConfirmed) {
            // Pastikan URL action mengarah ke file ini sendiri (data_pasien.php)
            window.location.href = `data_pasien.php?action=delete&no_kartu=${encodeURIComponent(no_kartu)}`;
        }
    }
</script>

</body>
</html>