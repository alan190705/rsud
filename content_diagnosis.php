<?php
require_once 'koneksi.php';

$no_kartu = $_GET['no_kartu'] ?? '';

if (!$no_kartu) {
    echo "<div class='alert alert-danger'>No. Kartu tidak valid.</div>";
    exit;
}

// Ambil riwayat diagnosa dari database
try {
    $stmt = $pdo->prepare("SELECT * FROM diagnosa WHERE no_kartu = :no_kartu ORDER BY tanggal_input DESC");
    $stmt->execute([':no_kartu' => $no_kartu]);
    $diagnosa_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $diagnosa_history = [];
}
?>

<div class="diagnosis-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0"><i class="fas fa-diagnoses text-primary me-2"></i> Form Diagnosis Pasien</h5>
        <span class="badge bg-primary-soft text-primary px-3 py-2">No. RM: <?= htmlspecialchars($no_kartu) ?></span>
    </div>

    <form id="formDiagnosis" class="mb-5">
        <input type="hidden" name="no_kartu" value="<?= htmlspecialchars($no_kartu) ?>">
        
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Kode ICD-10</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                    <input type="text" name="kode_icd" class="form-control" placeholder="Contoh: A00.0" required>
                </div>
            </div>
            <div class="col-md-8">
                <label class="form-label">Nama Penyakit / Deskripsi</label>
                <input type="text" name="nama_penyakit" class="form-control" placeholder="Masukkan nama diagnosa..." required>
            </div>
            <div class="col-12">
                <label class="form-label">Keterangan Klinis / Catatan Dokter</label>
                <textarea name="keterangan" class="form-control" rows="3" placeholder="Tambahkan catatan tambahan jika diperlukan..."></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Status Diagnosa</label>
                <select name="status_diagnosa" class="form-select">
                    <option value="Awal">Diagnosa Awal</option>
                    <option value="Banding">Diagnosa Banding</option>
                    <option value="Akhir">Diagnosa Akhir</option>
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">
                    <i class="fas fa-save me-2"></i> Simpan Diagnosis
                </button>
            </div>
        </div>
    </form>

    <hr class="my-4" style="opacity: 0.1;">

    <h6 class="fw-bold mb-3"><i class="fas fa-history text-muted me-2"></i> Riwayat Diagnosa Terakhir</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th class="py-3" style="border-top-left-radius: 8px;">Tanggal</th>
                    <th class="py-3">ICD-10</th>
                    <th class="py-3">Diagnosis</th>
                    <th class="py-3">Status</th>
                    <th class="py-3 text-center" style="border-top-right-radius: 8px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($diagnosa_history)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">Belum ada riwayat diagnosa untuk pasien ini.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($diagnosa_history as $row): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($row['tanggal_input'])) ?></td>
                            <td><span class="badge bg-secondary-soft text-dark"><?= htmlspecialchars($row['kode_icd']) ?></span></td>
                            <td class="fw-medium"><?= htmlspecialchars($row['nama_penyakit']) ?></td>
                            <td>
                                <?php if($row['status_diagnosa'] == 'Akhir'): ?>
                                    <span class="badge bg-success-soft text-success">Akhir</span>
                                <?php else: ?>
                                    <span class="badge bg-info-soft text-info"><?= $row['status_diagnosa'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    /* Tambahan style khusus untuk konten ini */
    .bg-primary-soft { background-color: #eff6ff; }
    .bg-success-soft { background-color: #ecfdf5; }
    .bg-info-soft { background-color: #f0f9ff; }
    .bg-secondary-soft { background-color: #f8fafc; border: 1px solid #e2e8f0; }
    
    .table thead th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 600;
    }
    
    #formDiagnosis .form-control:focus, #formDiagnosis .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
</style>

<script>
document.getElementById('formDiagnosis').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    
    // Efek loading pada tombol
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menyimpan...';

    fetch('proses_simpan_diagnosa.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            // Refresh konten diagnosa saja tanpa refresh halaman utama
            const noKartu = formData.get('no_kartu');
            loadContent(`content_diagnosis.php?no_kartu=${noKartu}`);
        } else {
            alert('Gagal: ' + data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save me-2"></i> Simpan Diagnosis';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan sistem.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save me-2"></i> Simpan Diagnosis';
    });
});
</script>