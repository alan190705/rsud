<?php
session_start();
// Proteksi Halaman
if (!isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit;
}

require_once 'koneksi.php';

$patient_data = null;
$message = '';
$message_type = '';

// --- 1. PROSES PENGAMBILAN DATA (GET) ---
if (isset($_GET['no_kartu'])) {
    $no_kartu_to_edit = $_GET['no_kartu'];
    try {
        $stmt_select = $pdo->prepare("SELECT * FROM pasien WHERE no_kartu = :no_kartu");
        $stmt_select->execute([':no_kartu' => $no_kartu_to_edit]);
        $patient_data = $stmt_select->fetch(PDO::FETCH_ASSOC);

        if (!$patient_data) {
            header("Location: halaman2.php?error=not_found");
            exit;
        }
    } catch (PDOException $e) {
        error_log("Error Fetch: " . $e->getMessage());
        $message = "Gagal mengambil data dari database.";
        $message_type = "danger";
    }
}

// --- 2. PROSES UPDATE DATA (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['no_kartu'])) {
    $no_kartu = $_POST['no_kartu'];
    
    // Mapping data & Handling Empty to NULL
    $payload = [
        ':nik'                => $_POST['nik'] ?? '',
        ':no_bpjs'            => !empty($_POST['no_bpjs']) ? $_POST['no_bpjs'] : null,
        ':no_kk'                 => !empty($_POST['no_kk']) ? $_POST['no_kk'] : null,
        ':nama'               => $_POST['nama'] ?? '',
        ':jenis_kelamin'      => $_POST['jenisKelamin'] ?? '',
        ':tanggal_lahir'          => $_POST['tanggal_lahir'] ?? '',
        ':tempat_lahir'       => $_POST['tempat_lahir'] ?? '',
        ':alamat'             => $_POST['alamat'] ?? '',
        ':no_hp'              => !empty($_POST['no_hp']) ? $_POST['no_hp'] : null,
        ':tanggal_registrasi' => $_POST['tanggal_registrasi'] ?? date('Y-m-d'),
        ':status_pasien'      => $_POST['status_pasien'] ?? 'Baru',
        ':ruangan'            => $_POST['ruangan'] ?? '',
        ':sub_ruangan'        => !empty($_POST['sub_ruangan']) ? $_POST['sub_ruangan'] : null,
        ':kelas'              => !empty($_POST['kelas']) ? $_POST['kelas'] : null,
        ':bed'                => !empty($_POST['bed']) ? $_POST['bed'] : null,
        ':kelompok_pasien'    => $_POST['kelompok_pasien'] ?? null,
        ':no_kartu'           => $no_kartu
    ];

    // Validasi Sederhana
    if (empty($payload[':nik']) || empty($payload[':nama'])) {
        $message = "Kolom Nama dan NIK wajib diisi!";
        $message_type = "danger";
    } else {
        try {
            $sql_update = "UPDATE pasien SET 
                nik = :nik, no_bpjs = :no_bpjs, no_kk = :no_kk, nama = :nama, 
                jenis_kelamin = :jenis_kelamin, tanggal_lahir = :tanggal_lahir, 
                tempat_lahir = :tempat_lahir, alamat = :alamat, no_hp = :no_hp, 
                tanggal_registrasi = :tanggal_registrasi, status_pasien = :status_pasien, 
                ruangan = :ruangan, sub_ruangan = :sub_ruangan, kelas = :kelas, 
                bed = :bed, kelompok_pasien = :kelompok_pasien 
                WHERE no_kartu = :no_kartu";
            
            $stmt_upd = $pdo->prepare($sql_update);
            if ($stmt_upd->execute($payload)) {
                // Redirect agar data segar kembali
                header("Location: halaman2.php?status=success_update&no_kartu=" . $no_kartu);
                exit;
            }
        } catch (PDOException $e) {
            error_log("Update Error: " . $e->getMessage());
            $message = "Terjadi kesalahan database: " . $e->getCode();
            $message_type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Pasien | SIMRS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #004d99;
            --light-blue-bg: #f0f4f8;
            --dark-text: #2c3e50;
            --accent-green: #2ecc71;
        }
        body { background-color: var(--light-blue-bg); font-family: 'Poppins', sans-serif; }
        .navbar { background-color: var(--primary-blue); padding: 1rem 2rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .navbar a { color: white; text-decoration: none; margin-right: 15px; font-weight: 500; }
        .form-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 25px; border: 1px solid #eee; }
        .form-section-title { color: var(--primary-blue); border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; font-weight: 600; }
        .btn-submit { background-color: var(--accent-green); color: white; font-weight: 600; padding: 12px 30px; border-radius: 10px; border: none; transition: 0.3s; }
        .btn-submit:hover { background-color: #27ae60; transform: translateY(-2px); }
        .required-star { color: #e74c3c; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark">
    <div class="container-fluid">
        <div class="d-flex">
            <a href="dashboard.php"><i class="fas fa-home me-2"></i>Dashboard</a>
            <a href="data_pasien.php"><i class="fas fa-list me-2"></i>Data Pasien</a>
        </div>
        <a href="logout.php" class="ms-auto"><i class="fas fa-sign-out-alt"></i> Keluar</a>
    </div>
</nav>

<div class="container mt-5 mb-5">
    <h2 class="text-center mb-4 fw-bold">Edit Data Pasien</h2>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($patient_data): ?>
    <form method="POST" id="editForm">
        <input type="hidden" name="no_kartu" value="<?= htmlspecialchars($patient_data['no_kartu']) ?>">

        <div class="form-card">
            <h3 class="form-section-title"><i class="fas fa-user-circle me-2"></i>Identitas Pribadi</h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nama Lengkap <span class="required-star">*</span></label>
                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($patient_data['nama']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">NIK <span class="required-star">*</span></label>
                    <input type="text" name="nik" class="form-control" value="<?= htmlspecialchars($patient_data['nik']) ?>" required maxlength="16">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Tempat Lahir <span class="required-star">*</span></label>
                    <input type="text" name="tempat_lahir" class="form-control" value="<?= htmlspecialchars($patient_data['tempat_lahir']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Tanggal Lahir <span class="required-star">*</span></label>
                    <input type="date" name="tanggal_lahir" class="form-control" value="<?= $patient_data['tanggal_lahir'] ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Jenis Kelamin <span class="required-star">*</span></label>
                    <select name="jenisKelamin" class="form-select" required>
                        <option value="Laki-laki" <?= $patient_data['jenis_kelamin'] == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="Perempuan" <?= $patient_data['jenis_kelamin'] == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Alamat Domisili <span class="required-star">*</span></label>
                    <textarea name="alamat" class="form-control" rows="2" required><?= htmlspecialchars($patient_data['alamat']) ?></textarea>
                </div>
            </div>
        </div>

        <div class="form-card">
            <h3 class="form-section-title"><i class="fas fa-file-medical me-2"></i>Jaminan & Pendaftaran</h3>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">No. Kartu (ID)</label>
                    <input type="text" class="form-control bg-light" value="<?= $patient_data['no_kartu'] ?>" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">No. BPJS</label>
                    <input type="text" name="no_bpjs" class="form-control" value="<?= htmlspecialchars($patient_data['no_bpjs'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Kelompok Pasien <span class="required-star">*</span></label>
                    <select name="kelompok_pasien" class="form-select" required>
                        <?php 
                        $groups = ["Umum", "BPJS PBI", "BPJS Non PBI", "Asuransi Lain"];
                        foreach($groups as $g) {
                            $sel = ($patient_data['kelompok_pasien'] == $g) ? 'selected' : '';
                            echo "<option value='$g' $sel>$g</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tanggal Registrasi</label>
                    <input type="date" name="tanggal_registrasi" class="form-control" value="<?= $patient_data['tanggal_registrasi'] ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Status Pasien</label>
                    <select name="status_pasien" class="form-select">
                        <option value="Baru" <?= $patient_data['status_pasien'] == 'Baru' ? 'selected' : '' ?>>Baru</option>
                        <option value="Lama" <?= $patient_data['status_pasien'] == 'Lama' ? 'selected' : '' ?>>Lama</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-card">
            <h3 class="form-section-title"><i class="fas fa-hospital me-2"></i>Penempatan Ruangan</h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Jenis Pelayanan <span class="required-star">*</span></label>
                    <select name="ruangan" id="ruangan" class="form-select" onchange="updateSubRuangan()" required>
                        <option value="Rawat Jalan" <?= $patient_data['ruangan'] == 'Rawat Jalan' ? 'selected' : '' ?>>Rawat Jalan</option>
                        <option value="Rawat Inap" <?= $patient_data['ruangan'] == 'Rawat Inap' ? 'selected' : '' ?>>Rawat Inap</option>
                    </select>
                </div>
                <div class="col-md-6" id="sub_ruangan_container">
                    <label class="form-label fw-bold">Nama Poli/Ruangan <span class="required-star">*</span></label>
                    <select name="sub_ruangan" id="sub_ruangan" class="form-select"></select>
                </div>
                <div class="col-md-6" id="kelas_container">
                    <label class="form-label fw-bold">Kelas</label>
                    <select name="kelas" id="kelas" class="form-select"></select>
                </div>
                <div class="col-md-6" id="bed_container">
                    <label class="form-label fw-bold">No. Bed</label>
                    <input type="text" name="bed" id="bed" class="form-control" value="<?= htmlspecialchars($patient_data['bed'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-center gap-3">
            <a href="data_pasien.php" class="btn btn-secondary px-5 py-2">Batal</a>
            <button type="submit" class="btn btn-submit">Simpan Perubahan</button>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
    // Menyimpan data lama dari PHP ke JS
    const oldData = {
        ruangan: "<?= $patient_data['ruangan'] ?>",
        sub_ruangan: "<?= $patient_data['sub_ruangan'] ?>",
        kelas: "<?= $patient_data['kelas'] ?>"
    };

    function updateSubRuangan() {
        const jenis = document.getElementById('ruangan').value;
        const subSelect = document.getElementById('sub_ruangan');
        const kelasSelect = document.getElementById('kelas');
        
        // Reset
        subSelect.innerHTML = "";
        kelasSelect.innerHTML = "";

        if (jenis === "Rawat Jalan") {
            const polis = ["Poli Umum", "Poli Gigi", "Poli Anak", "Poli Jantung"];
            polis.forEach(p => {
                let opt = new Option(p, p);
                if(p === oldData.sub_ruangan) opt.selected = true;
                subSelect.add(opt);
            });
            
            const kls = ["Umum", "BPJS"];
            kls.forEach(k => {
                let opt = new Option(k, k);
                if(k === oldData.kelas) opt.selected = true;
                kelasSelect.add(opt);
            });
            document.getElementById('bed_container').style.display = "none";
        } else {
            const kamars = ["Mawar", "Melati", "VIP", "ICU"];
            kamars.forEach(k => {
                let opt = new Option(k, k);
                if(k === oldData.sub_ruangan) opt.selected = true;
                subSelect.add(opt);
            });

            const kls = ["Kelas 1", "Kelas 2", "Kelas 3", "VIP"];
            kls.forEach(k => {
                let opt = new Option(k, k);
                if(k === oldData.kelas) opt.selected = true;
                kelasSelect.add(opt);
            });
            document.getElementById('bed_container').style.display = "block";
        }
    }

    // Jalankan saat load pertama kali
    window.onload = updateSubRuangan;
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>