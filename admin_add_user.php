<?php
session_start();

// Proteksi Halaman Admin
if (!isset($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User Baru - SIM RSUD</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --sidebar-color:  #0f172a;
            --topbar-color: #ffffff;
            --bg-body: #f2f5f9;
            --accent-purple: #6f42c1;
        }

        body { 
            font-family: 'Poppins', sans-serif; 
            background-color: var(--bg-body);
            margin: 0;
            display: flex;
        }

        /* Sidebar Navigasi */
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-color);
            min-height: 100vh;
            color: white;
            position: fixed;
            box-shadow: 4px 0 10px rgba(0,0,0,0.05);
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 25px;
            font-weight: 700;
            font-size: 1.2rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 15px 25px;
            font-size: 0.95rem;
            transition: 0.3s;
        }

        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.15);
            border-left: 4px solid white;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            width: 100%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .top-navbar {
            background: var(--topbar-color);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .page-body { padding: 40px; }

        .form-card { 
            background: white; 
            border-radius: 15px; 
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }

        .card-header-custom {
            background: linear-gradient(135deg,  #0f172a 0%,  #0f172a 100%);
            padding: 25px;
            color: white;
            border-radius: 15px 15px 0 0;
        }

        .form-label {
            font-weight: 600;
            color: #4e73df;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #d1d3e2;
            background-color: #f8f9fc;
        }

        .btn-purple {
            background-color: var(--accent-purple);
            border: none;
            color: white;
            padding: 14px;
            font-weight: 600;
            border-radius: 10px;
            transition: 0.3s;
        }

        .btn-purple:hover {
            background-color: #59359a;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(111, 66, 193, 0.3);
            color: white;
        }

        footer {
            margin-top: auto;
            background: white;
            padding: 20px;
            text-align: center;
            color: #858796;
            font-size: 0.85rem;
            border-top: 1px solid #e3e6f0;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-hospital-user me-3"></i> SIM RSUD
    </div>
    <nav class="nav flex-column mt-3">
        <a class="nav-link" href="dashboard.php"><i class="fas fa-chart-line me-2"></i> Dashboard</a>
        <a class="nav-link" href="registrasi.php"><i class="fas fa-user-plus me-2"></i> Registrasi Pasien</a>
        <a class="nav-link" href="data_pasien.php"><i class="fas fa-notes-medical me-2"></i> Data Pasien</a>
        <a class="nav-link active" href="data_users.php"><i class="fas fa-users-cog me-2"></i> System admin</a>
        <a class="nav-link" href="laporan.php"><i class="fas fa-file-invoice me-2"></i> Laporan</a>
        <a class="nav-link" href="data_users.php"><i class="fas fa-chart-line me-2"></i> Kelola User</a>
    </nav>
</div>

<div class="main-content">
    <div class="top-navbar">
        <div class="fw-bold text-primary">Sistem Informasi Rumah Sakit</div>
        <div class="dropdown">
            <span class="small text-muted"><i class="fas fa-user-circle me-1"></i> <?= $_SESSION['username'] ?? 'Admin' ?> (Admin)</span>
            <a href="logout.php" class="btn btn-sm btn-outline-danger ms-3">Logout</a>
        </div>
    </div>

    <div class="page-body">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <div class="form-card">
                    <div class="card-header-custom d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-user-shield fa-2x text-white-50"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold">Tambah Akun User Baru</h4>
                            <p class="small mb-0 opacity-75">Gunakan form ini untuk menambah admin atau staf operasional baru.</p>
                        </div>
                    </div>

                    <div class="p-4 p-md-5">
                        <?php if (isset($_SESSION['success_message'])): ?>
                            <script>Swal.fire('Berhasil', '<?= $_SESSION['success_message'] ?>', 'success');</script>
                            <?php unset($_SESSION['success_message']); ?>
                        <?php endif; ?>

                        <form action="process_add_user.php" method="POST" id="userForm">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="username" placeholder="Masukkan username unik" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Level Akses (Role) <span class="text-danger">*</span></label>
                                    <select class="form-select" name="role" required>
                                        <option value="" selected disabled>-- Pilih Hak Akses --</option>
                                        <option value="admin">Administrator</option>
                                        <option value="user">Staff Operasional</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="password" id="password" placeholder="Minimal 6 karakter" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Ulangi Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Sama dengan password" required>
                                </div>
                            </div>

                            <hr class="my-5">

                            <div class="row align-items-center">
                                <div class="col-md-6 text-muted small">
                                    <i class="fas fa-info-circle me-1"></i> Pastikan data sudah benar sebelum menyimpan.
                                </div>
                                <div class="col-md-6 text-end">
                                    <a href="data_users.php" class="btn btn-light px-4 me-2">Batal</a>
                                    <button type="submit" class="btn btn-purple px-5">
                                        <i class="fas fa-plus-circle me-2"></i> Buat Akun User
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <footer>
        &copy; <?= date('Y') ?> <b>RSUD KITA BERSAMA</b> | Sistem Informasi Rumah Sakit Terintegrasi.
    </footer>
</div>

<script>
    const form = document.getElementById('userForm');
    const password = document.getElementById('password');
    const confirm_password = document.getElementById('confirm_password');

    function validate() {
        if (password.value.length < 6) {
            password.setCustomValidity("Password minimal 6 karakter.");
        } else {
            password.setCustomValidity('');
        }

        if (confirm_password.value !== password.value) {
            confirm_password.setCustomValidity("Password tidak cocok.");
        } else {
            confirm_password.setCustomValidity('');
        }
    }

    password.onchange = validate;
    confirm_password.onkeyup = validate;
</script>

</body>
</html>