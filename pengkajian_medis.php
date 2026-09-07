<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

require_once 'koneksi.php';

$no_kartu = $_GET['no_kartu'] ?? '';
$patient_data = null;

if ($no_kartu) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM pasien WHERE no_kartu = :no_kartu");
        $stmt->execute([':no_kartu' => $no_kartu]);
        $patient_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
    }
}

$flash_message = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Medical Record | <?= $patient_data ? htmlspecialchars($patient_data['nama']) : 'Pasien' ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #2563eb;
            --primary-soft: #eff6ff;
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --body-bg: #f8fafc;
            --text-dark: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
            --radius: 12px;
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--body-bg);
            color: var(--text-dark);
            margin: 0;
            display: flex;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: 280px;
            background-color: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            color: white;
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 1.5rem;
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
            background: #3b82f6; /* Menyamakan dengan warna header navigasi di gambar */
        }

        .nav-container {
            flex-grow: 1;
            padding: 0.5rem 0;
            overflow-y: auto; /* Mengaktifkan scroll untuk menu yang banyak */
        }

        /* Custom Scrollbar untuk Sidebar */
        .nav-container::-webkit-scrollbar { width: 5px; }
        .nav-container::-webkit-scrollbar-track { background: var(--sidebar-bg); }
        .nav-container::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }

        .nav-label {
            padding: 1rem 1.5rem 0.5rem;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.6rem 1.5rem;
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 0.85rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .nav-link:hover {
            background-color: var(--sidebar-hover);
            color: white;
        }

        .nav-link.active {
            background-color: rgba(37, 99, 235, 0.2);
            color: #3b82f6;
            font-weight: 500;
        }

        .nav-link i {
            width: 20px;
            margin-right: 12px;
            font-size: 1rem;
            opacity: 0.7;
        }

        .badge-nav {
            font-size: 0.6rem;
            padding: 2px 6px;
            margin-left: auto;
            border-radius: 4px;
            font-weight: 700;
        }

        /* --- CONTENT --- */
        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
            padding: 2rem;
            min-height: 100vh;
        }

        .patient-header {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .avatar-box {
            width: 64px;
            height: 64px;
            background: var(--primary-soft);
            color: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            flex-grow: 1;
            gap: 1rem;
        }

        .info-item label {
            display: block;
            font-size: 0.7rem;
            color: var(--text-light);
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .info-item span {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .content-card {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            min-height: 500px;
        }

        .badge-sex {
            background: #f1f5f9;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            color: #475569;
        }

        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar-brand span, .nav-label, .nav-link span, .badge-nav { display: none; }
            .main-content { margin-left: 80px; width: calc(100% - 80px); }
            .nav-link { justify-content: center; padding: 1rem; }
            .nav-link i { margin: 0; }
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-th-list"></i>
            <span>Navigasi</span>
        </div>
        
        <div class="nav-container">
            <div class="nav-label">EMR Terintegrasi</div>
            
            <a href="#" class="nav-link" data-url="content_riwayat_registrasi.php?no_kartu=<?= $no_kartu ?>">
                <i class="fas fa-bars"></i> <span>Riwayat Registrasi</span>
            </a>
            <a href="#" class="nav-link" data-url="content_emr.php?no_kartu=<?= $no_kartu ?>">
                <i class="fas fa-bars text-danger"></i> <span>EMR</span>
                <span class="badge-nav bg-danger">EMR</span>
            </a>
            <a href="#" class="nav-link active" data-url="content_pengkajian_medis.php?no_kartu=<?= $no_kartu ?>">
                <i class="fas fa-bars"></i> <span>Pengkajian Medis</span>
            </a>
            <a href="#" class="nav-link" data-url="conten_vital_sign.php?no_kartu=<?= $no_kartu ?>">
                <i class="fas fa-bars text-danger"></i> <span>Vital Sign</span>
                <span class="badge-nav bg-danger">Vital Sign</span>
            </a>
            <a href="#" class="nav-link" data-url="content_askep.php?no_kartu=<?= $no_kartu ?>">
                <i class="fas fa-bars"></i> <span>Askep</span>
            </a>
            <a href="#" class="nav-link" data-url="content_catatan_klinik.php?no_kartu=<?= $no_kartu ?>">
                <i class="fas fa-bars"></i> <span>Catatan Klinik</span>
            </a>
            <a href="#" class="nav-link" data-url="content_diagnosis.php?no_kartu=<?= $no_kartu ?>">
                <i class="fas fa-bars"></i> <span>Diagnosis</span>
            </a>
            <a href="#" class="nav-link" data-url="conten_vital_sign.php?no_kartu=<?= $no_kartu ?>">
                <i class="fas fa-bars"></i> <span>Vital Sign</span>
            </a>
            <a href="#" class="nav-link" data-url="content_resep_elektronik.php?no_kartu=<?= $no_kartu ?>">
                <i class="fas fa-bars text-info"></i> <span>Resep Elektronik</span>
                <span class="badge-nav bg-info text-dark">Resep Elektronik</span>
            </a>
            <a href="#" class="nav-link" data-url="content_asesmen_forensik.php?no_kartu=<?= $no_kartu ?>">
                <i class="fas fa-bars"></i> <span>Asesmen Forensik</span>
            </a>
            <a href="#" class="nav-link" data-url="content_laboratorium.php?no_kartu=<?= $no_kartu ?>">
                <i class="fas fa-bars"></i> <span>Laboratorium</span>
            </a>
            <a href="#" class="nav-link" data-url="content_radiologi.php?no_kartu=<?= $no_kartu ?>">
                <i class="fas fa-bars"></i> <span>Radiologi</span>
            </a>
            <a href="#" class="nav-link" data-url="content_odontogram.php?no_kartu=<?= $no_kartu ?>">
                <i class="fas fa-bars"></i> <span>Odontogram</span>
            </a>
            <a href="#" class="nav-link" data-url="content_surveilans.php?no_kartu=<?= $no_kartu ?>">
                <i class="fas fa-bars"></i> <span>Surveilans</span>
            </a>
            <a href="#" class="nav-link" data-url="content_summary_list.php?no_kartu=<?= $no_kartu ?>">
                <i class="fas fa-bars"></i> <span>Summary List</span>
            </a>
            <a href="#" class="nav-link" data-url="content_resume_medis.php?no_kartu=<?= $no_kartu ?>">
                <i class="fas fa-bars"></i> <span>Resume Medis</span>
            </a>

            <div class="nav-label mt-3">Lainnya</div>
            <a href="data_pasien.php" class="nav-link text-warning">
                <i class="fas fa-circle-chevron-left"></i> <span>Tutup Rekam Medis</span>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <?php if ($patient_data): ?>
            <header class="patient-header">
                <div class="avatar-box">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <label>No. Registrasi</label>
                        <span class="text-primary">#<?= htmlspecialchars($patient_data['no_kartu']) ?></span>
                    </div>
                    <div class="info-item">
                        <label>Nama Lengkap</label>
                        <span><?= htmlspecialchars($patient_data['nama']) ?></span>
                    </div>
                    <div class="info-item">
                        <label>Umur/Jenis Kelamin</label>
                        <span>
                            <?php
                                if (!empty($patient_data['tgl_lahir'])) {
                                    $birthDate = new DateTime($patient_data['tgl_lahir']);
                                    $age = $birthDate->diff(new DateTime('today'));
                                    echo $age->y . " Thn";
                                } else {
                                    echo "-";
                                }
                            ?>
                            <span class="badge-sex">
                                <?= htmlspecialchars($patient_data['jenis_kelamin'] ?? '-') ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <label>NIK</label>
                        <span><?= htmlspecialchars($patient_data['nik']) ?></span>
                    </div>
                </div>
            </header>

            <div class="content-card" id="content-loader">
                <div class="d-flex flex-column align-items-center justify-content-center h-100 py-5">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p class="text-muted">Mengambil data medis...</p>
                </div>
            </div>

        <?php else: ?>
            <div class="content-card d-flex flex-column align-items-center justify-content-center text-center">
                <i class="fas fa-user-slash fa-4x text-light mb-4"></i>
                <h4 class="fw-bold">Pasien Tidak Ditemukan</h4>
                <p class="text-muted mb-4">Silakan kembali dan pilih pasien dengan benar.</p>
                <a href="data_pasien.php" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                    <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
                </a>
            </div>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('content-loader');
            const links = document.querySelectorAll('.nav-link[data-url]');
            const noKartu = "<?= $no_kartu ?>";

            function loadPage(url) {
                if (!url || noKartu === "") return;
                
                container.innerHTML = `
                    <div class="d-flex flex-column align-items-center justify-content-center h-100 py-5">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p class="text-muted">Memuat halaman...</p>
                    </div>`;

                fetch(url)
                    .then(res => res.text())
                    .then(data => {
                        container.innerHTML = data;
                    })
                    .catch(err => {
                        container.innerHTML = `<div class="alert alert-danger">Gagal memuat: ${err}</div>`;
                    });
            }

            if(noKartu) loadPage(`content_pengkajian_medis.php?no_kartu=${noKartu}`);

            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    links.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                    loadPage(this.dataset.url);
                });
            });
        });
    </script>
</body><script>
document.addEventListener("click", function(e){

    if(e.target && e.target.id === "btnSimpanVital"){

        console.log("KLIK KEDETECT ✅");

        const form = document.getElementById("formVitalSign");

        if(!form){
            console.log("FORM TIDAK KETEMU ❌");
            return;
        }

        const formData = new FormData(form);

        fetch("simpan_vital_sign.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(res => {
            console.log("RESPONSE:", res);
            alert(res);
            location.reload();
        })
        .catch(err => {
            console.log("ERROR:", err);
            alert("Gagal koneksi");
        });

    }

});
</script>
</html>