<?php
require_once 'koneksi.php';

$no_kartu = $_GET['no_kartu'] ?? '';
$registrasi_list = [];

if ($no_kartu) {
    try {
        // Query tetap sama, kita fokus ke perbaikan UI
        $query = "SELECT no_reg, tgl_registrasi, klinik_tujuan, dokter_tujuan, status_bayar 
                  FROM registrasi 
                  WHERE no_kartu = :no_kartu 
                  ORDER BY tgl_registrasi DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':no_kartu', $no_kartu);
        $stmt->execute();
        $registrasi_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger shadow-sm'><i class='fas fa-exclamation-circle me-2'></i>Error Database: " . $e->getMessage() . "</div>";
    }
}
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--text-color-dark);">
                <i class="fas fa-history me-2 text-primary"></i>Riwayat Registrasi
            </h4>
            <p class="text-muted small mb-0">Menampilkan daftar kunjungan medis pasien sebelumnya.</p>
        </div>
        <button class="btn btn-light border btn-sm fw-medium shadow-sm" onclick="location.reload()">
            <i class="fas fa-sync-alt me-1"></i> Refresh Data
        </button>
    </div>

    <?php if (empty($registrasi_list)): ?>
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <div class="mb-3">
                    <i class="fas fa-folder-open fa-4x text-light"></i>
                </div>
                <h5 class="text-muted">Tidak Ada Data</h5>
                <p class="text-secondary small">Belum ada catatan registrasi untuk nomor kartu <strong><?= htmlspecialchars($no_kartu) ?></strong></p>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 custom-table">
                    <thead>
                        <tr>
                            <th class="ps-4">ID Registrasi</th>
                            <th>Waktu Kunjungan</th>
                            <th>Unit/Klinik</th>
                            <th>Dokter Penanggung Jawab</th>
                            <th>Status Pembayaran</th>
                            <th class="text-center pe-4">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrasi_list as $row): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-primary">#<?= htmlspecialchars($row['no_reg']) ?></span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-medium"><?= date('d M Y', strtotime($row['tgl_registrasi'])) ?></span>
                                        <small class="text-muted mt-1"><i class="far fa-clock me-1"></i><?= date('H:i', strtotime($row['tgl_registrasi'])) ?> WIB</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-shape me-2 bg-blue-light text-primary">
                                            <i class="fas fa-hospital-alt"></i>
                                        </div>
                                        <span class="fw-medium text-dark"><?= htmlspecialchars($row['klinik_tujuan']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary fw-medium"><?= htmlspecialchars($row['dokter_tujuan']) ?></span>
                                </td>
                                <td>
                                    <?php if ($row['status_bayar'] == 'Lunas'): ?>
                                        <span class="badge-status badge-lunas">
                                            <i class="fas fa-check-circle me-1"></i> Lunas
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-status badge-menunggu">
                                            <i class="fas fa-clock me-1"></i> Menunggu
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pe-4">
                                    <button class="btn btn-icon btn-light" title="Lihat Detail Rekam Medis">
                                        <i class="fas fa-chevron-right text-primary"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Custom CSS Variables untuk konsistensi */
    :root {
        --bg-blue-light: #eef2ff;
    }

    /* Styling Tabel Modern */
    .custom-table thead th {
        background-color: #f8fafc;
        color: #64748b;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        padding: 18px 15px;
        border-bottom: 1px solid #e2e8f0;
    }

    .custom-table tbody td {
        padding: 16px 15px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
    }

    .custom-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Efek Icon Shape */
    .icon-shape {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background-color: var(--bg-blue-light);
        font-size: 0.85rem;
    }

    /* Custom Badges */
    .badge-status {
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
    }

    .badge-lunas {
        background-color: #dcfce7;
        color: #15803d;
    }

    .badge-menunggu {
        background-color: #fef9c3;
        color: #a16207;
    }

    /* Action Buttons */
    .btn-icon {
        width: 34px;
        height: 34px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s;
    }

    .btn-icon:hover {
        background-color: var(--primary-color);
        transform: translateX(3px);
    }
    
    .btn-icon:hover i {
        color: white !important;
    }

    /* Animasi Baris */
    .custom-table tbody tr {
        transition: background-color 0.2s ease;
    }
    
    .custom-table tbody tr:hover {
        background-color: #f8fbff;
    }
</style>