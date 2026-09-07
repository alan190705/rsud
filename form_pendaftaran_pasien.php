<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// Koneksi database
require_once 'koneksi.php';

// Default nomor kartu pertama
$next_no_kartu = '(001)';

try {
    // Ambil nomor kartu terbesar dari tabel pasien
    $stmt = $pdo->query("SELECT MAX(no_kartu) AS max_no_kartu FROM pasien");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && $result['max_no_kartu']) {
        $last_no_kartu = $result['max_no_kartu'];
        if (preg_match('/\((\d+)\)/', $last_no_kartu, $matches)) {
            $number = (int)$matches[1];
            $number++;
            $next_no_kartu = '(' . str_pad($number, 3, '0', STR_PAD_LEFT) . ')';
        }
    }
} catch (PDOException $e) {
    error_log("Error mengambil no_kartu: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Pasien | SIMRS Digital</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0f172a;    /* Slate Dark */
            --accent: #3b82f6;     /* Medical Blue */
            --bg-light: #f1f5f9;
            --text-muted: #64748b;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--primary);
            min-height: 100vh;
        }

        /* --- NAVBAR --- */
        .navbar-custom {
            background-color: var(--primary);
            padding: 15px 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .navbar-custom .nav-link {
            color: #cbd5e1 !important;
            font-weight: 600;
            margin-right: 15px;
            transition: 0.3s;
        }

        .navbar-custom .nav-link:hover, .navbar-custom .nav-link.active {
            color: white !important;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
        }

        /* --- MAIN CONTENT --- */
        .main-header {
            background: white;
            padding: 30px 0;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 40px;
        }

        .form-card {
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 10px 15px -3px rgba(0,0,0,0.03);
            border: 1px solid #f1f5f9;
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--accent);
            background: #eff6ff;
            padding: 10px;
            border-radius: 10px;
        }

        /* --- INPUT STYLING --- */
        .form-label {
            font-weight: 600;
            font-size: 14px;
            color: #334155;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border-radius: 12px;
            padding: 12px 16px;
            border: 2px solid #f1f5f9;
            background: #f8fafc;
            font-size: 15px;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            background: white;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .input-group-text {
            background: #f8fafc;
            border: 2px solid #f1f5f9;
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: #94a3b8;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }

        /* --- BUTTON --- */
        .btn-submit {
            background: var(--primary);
            color: white;
            border: none;
            padding: 16px 40px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-submit:hover {
            background: #1e293b;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.15);
            color: white;
        }

        .required-star { color: #ef4444; }

        .footer {
            padding: 30px;
            text-align: center;
            color: var(--text-muted);
            font-size: 14px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="#"><i class="fas fa-hospital-symbol me-2"></i> SIMRS DIGITAL</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link active" href="form_pendaftaran_pasien.php">Registrasi</a>
                <a class="nav-link" href="data_pasien.php">Data Pasien</a>
                <a class="nav-link" href="logout.php text-danger"><i class="fas fa-power-off"></i></a>
            </div>
        </div>
    </div>
</nav>

<div class="main-header">
    <div class="container">
        <h2 class="fw-bold mb-1">Registrasi Pasien Baru</h2>
        <p class="text-muted mb-0">Lengkapi formulir di bawah untuk mendaftarkan pasien ke sistem.</p>
    </div>
</div>

<div class="container pb-5">
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success border-0 shadow-sm rounded-4 p-3 mb-4">
            <i class="fas fa-check-circle me-2"></i> <strong>Berhasil!</strong> Data pasien baru telah disimpan.
        </div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger border-0 shadow-sm rounded-4 p-3 mb-4">
            <i class="fas fa-exclamation-circle me-2"></i> <strong>Gagal!</strong> Terjadi kendala pada sistem.
        </div>
    <?php endif; ?>

    <form method="POST" action="proses_input.php">
        
        <div class="form-card">
            <h3 class="section-title">
                <i class="fas fa-user"></i> Informasi Pribadi
            </h3>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">Nama Lengkap Pasien <span class="required-star">*</span></label>
                    <input type="text" class="form-control" name="nama" required placeholder="Contoh: Budi Santoso">
                </div>
                <div class="col-md-6">
                    <label class="form-label">NIK (Nomor Induk Kependudukan) <span class="required-star">*</span></label>
                    <input type="text" class="form-control" name="nik" required placeholder="16 digit angka">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tempat Lahir <span class="required-star">*</span></label>
                    <select class="form-select" name="tempat_lahir" required>
                        <option value="">-- Pilih Kota --</option>
                        <option value="Jakarta">Jakarta</option>
                        <option value="Surabaya">Surabaya</option>
                        <option value="Bandung">Bandung</option>
                        <option value="Bogor">Bogor</option>
                        <option value="Ciawi">Ciawi</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Lahir <span class="required-star">*</span></label>
                    <input type="date" class="form-control" name="tanggal_lahir" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jenis Kelamin <span class="required-star">*</span></label>
                    <select class="form-select" name="jenis_kelamin" required>
                        <option value="">-- Pilih --</option>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nomor WhatsApp/HP</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fab fa-whatsapp"></i></span>
                        <input type="text" class="form-control" name="no_hp" placeholder="08xxxxxxxxxx">
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Alamat Lengkap <span class="required-star">*</span></label>
                    <textarea class="form-control" name="alamat" rows="3" required placeholder="Nama jalan, RT/RW, Kec, Kota"></textarea>
                </div>
            </div>
        </div>

        <div class="form-card">
            <h3 class="section-title">
                <i class="fas fa-hospital-user"></i> Administrasi & Medis
            </h3>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">No Kartu Pasien (Auto)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-id-card-clip"></i></span>
                        <input type="text" class="form-control fw-bold text-primary" name="no_kartu" value="<?= htmlspecialchars($next_no_kartu) ?>" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">No BPJS (Jika ada)</label>
                    <input type="text" class="form-control" name="no_bpjs" placeholder="Masukkan nomor kartu BPJS">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kelompok Pasien <span class="required-star">*</span></label>
                    <select name="kelompok_pasien" id="kelompok_pasien" class="form-select" required>
                        <option value="">-- Pilih Kelompok --</option>
                        <option value="Umum">Umum (Mandiri)</option>
                        <option value="BPJS PBI">BPJS PBI</option>
                        <option value="BPJS Non PBI">BPJS Non PBI</option>
                        <option value="Asuransi Lain">Asuransi Swasta</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Registrasi</label>
                    <input type="date" class="form-control" id="tanggal_registrasi" name="tanggal_registrasi" readonly>
                </div>
            </div>
        </div>

        <div class="form-card">
            <h3 class="section-title">
                <i class="fas fa-stethoscope"></i> Detail Kunjungan
            </h3>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">Jenis Pelayanan <span class="required-star">*</span></label>
                    <select name="ruangan" id="ruangan" class="form-select" onchange="tampilkanSubRuangan(this.value)" required>
                        <option value="">-- Pilih Pelayanan --</option>
                        <option value="Rawat Jalan">Rawat Jalan (Poliklinik)</option>
                        <option value="Rawat Inap">Rawat Inap (Gedung)</option>
                    </select>
                </div>

                <div class="col-md-6" id="sub_ruangan_group" style="display: none;">
                    <label for="sub_ruangan" class="form-label">Unit/Poli <span class="required-star">*</span></label>
                    <select name="sub_ruangan" id="sub_ruangan" class="form-select"></select>
                </div>

                <div class="col-md-4" id="kelas_group" style="display: none;">
                    <label for="kelas" class="form-label">Kelas Kamar <span class="required-star">*</span></label>
                    <select name="kelas" id="kelas" class="form-select"></select>
                </div>

                <div class="col-md-4" id="bed_group" style="display: none;">
                    <label for="bed" class="form-label">Nomor Bed <span class="required-star">*</span></label>
                    <input type="number" class="form-control" name="bed" id="bed" placeholder="Contoh: 01">
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <button type="submit" class="btn-submit">
                Daftarkan Pasien <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </form>
</div>

<footer class="footer">
    &copy; <?= date('Y') ?> <strong>SIMRS Digital</strong> | RSUD Kita Bersama.
</footer>

<script>
function tampilkanSubRuangan(jenis) {
    const subGroup = document.getElementById('sub_ruangan_group');
    const kelasGroup = document.getElementById('kelas_group');
    const bedGroup = document.getElementById('bed_group');
    const subSelect = document.getElementById('sub_ruangan');
    const kelasSelect = document.getElementById('kelas');

    subSelect.innerHTML = '<option value="">-- Pilih --</option>';
    kelasSelect.innerHTML = '<option value="">-- Pilih --</option>';
    
    subGroup.style.display = 'none';
    kelasGroup.style.display = 'none';
    bedGroup.style.display = 'none';

    if (jenis === 'Rawat Jalan') {
        const polis = ["Poli Umum", "Poli Gigi", "Poli Anak", "Poli Jantung"];
        polis.forEach(p => subSelect.innerHTML += `<option value="${p}">${p}</option>`);
        subGroup.style.display = 'block';
    } else if (jenis === 'Rawat Inap') {
        const gedungs = ["Gedung Soekarno", "Gedung Hatta", "Gedung Kartini"];
        gedungs.forEach(g => subSelect.innerHTML += `<option value="${g}">${g}</option>`);
        const kelass = ["VIP", "VVIP", "Kelas 1", "Kelas 2", "Kelas 3"];
        kelass.forEach(k => kelasSelect.innerHTML += `<option value="${k}">${k}</option>`);
        
        subGroup.style.display = 'block';
        kelasGroup.style.display = 'block';
        bedGroup.style.display = 'block';
    }
}

window.onload = function () {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('tanggal_registrasi').value = today;
};
</script>

</body>
</html>