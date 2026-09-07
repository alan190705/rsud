<?php
// Ambil no_kartu dari parameter URL
$no_kartu = $_GET['no_kartu'] ?? '';

// Jika perlu data pasien lagi untuk form ini, lakukan query di sini
?>

<div class="container-fluid">
    <h3 class="mb-4"><i class="fas fa-notes-medical me-2"></i> Form Pengkajian Medis</h3>
    <hr>
    
    <form id="formPengkajian">
        <input type="hidden" name="no_kartu" value="<?= htmlspecialchars($no_kartu) ?>">
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Keluhan Utama</label>
                <textarea class="form-control" name="keluhan" rows="3" placeholder="Masukkan keluhan pasien..."></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Riwayat Penyakit Dahulu</label>
                <textarea class="form-control" name="riwayat_penyakit" rows="3" placeholder="Masukkan riwayat penyakit..."></textarea>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Tinggi Badan (cm)</label>
                <input type="number" class="form-control" name="tinggi_badan">
            </div>
            <div class="col-md-4">
                <label class="form-label">Berat Badan (kg)</label>
                <input type="number" class="form-control" name="berat_badan">
            </div>
            <div class="col-md-4">
                <label class="form-label">Golongan Darah</label>
                <select class="form-select" name="gol_darah">
                    <option value="">- Pilih -</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="AB">AB</option>
                    <option value="O">O</option>
                </select>
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary-custom btn-custom">
                <i class="fas fa-save"></i> Simpan Pengkajian
            </button>
        </div>
    </form>
</div>

<script>
    // Logika simpan data via AJAX bisa diletakkan di sini
    document.getElementById('formPengkajian').onsubmit = function(e) {
        e.preventDefault();
        alert('Data untuk kartu ' + "<?= $no_kartu ?>" + ' akan disimpan!');
        // Tambahkan fetch POST ke simpan_pengkajian.php di sini
    };
</script>