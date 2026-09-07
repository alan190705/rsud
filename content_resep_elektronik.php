<?php
require_once 'koneksi.php';
$no_kartu = $_GET['no_kartu'] ?? '';
?>

<style>
    .resep-wrapper { font-size: 12px; color: #333; }
    .header-blue { background-color: #3498db; color: white; padding: 8px 15px; font-weight: bold; border-radius: 4px 4px 0 0; }
    .section-box { border: 1px solid #ddd; margin-bottom: 15px; background: #fff; }
    .row-filter { background: #f9f9f9; padding: 10px; border-bottom: 1px solid #ddd; }
    
    /* Styling Tabel ala SIMRS */
    .table-simrs { width: 100%; border-collapse: collapse; font-size: 11px; }
    .table-simrs th { background-color: #444; color: #fff; padding: 8px; text-align: left; border: 1px solid #555; }
    .table-simrs td { padding: 6px; border: 1px solid #ddd; }
    .table-simrs tr:nth-child(even) { background-color: #f2f2f2; }
    
    /* Form Input Area */
    .input-group-custom { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .input-group-custom label { min-width: 80px; font-weight: bold; }
    .form-control-sm-custom { padding: 4px 8px; border: 1px solid #3498db; border-radius: 3px; font-size: 12px; }
    
    .btn-action { padding: 5px 15px; border-radius: 4px; border: none; color: white; cursor: pointer; font-weight: bold; }
    .btn-blue { background-color: #3498db; }
    .btn-red { background-color: #e74c3c; }
    .btn-green { background-color: #2ecc71; }
    .btn-orange { background-color: #f39c12; }
</style>

<div class="resep-wrapper">
    
    <div class="header-blue">Resep Elektronik</div>
    <div class="section-box">
        <div class="row row-filter">
            <div class="row w-100 g-2">
                <div class="col-md-3">
                    <label>Tanggal resep</label>
                    <div class="d-flex gap-1">
                        <input type="date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                        <span class="align-self-center">s/d</span>
                        <input type="date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <label>No Order</label>
                    <input type="text" class="form-control form-control-sm" placeholder="No. Order">
                </div>
                <div class="col-md-2">
                    <label>Penulis resep</label>
                    <select class="form-select form-select-sm">
                        <option>Semua Dokter</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Ruangan</label>
                    <select class="form-select form-select-sm">
                        <option>DEPO RAWAT INAP</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-simrs">
                <thead>
                    <tr>
                        <th>No.Resep</th>
                        <th>Tgl Resep</th>
                        <th>No.Registrasi</th>
                        <th>Penulis Resep</th>
                        <th>Ruangan</th>
                        <th>Depo</th>
                        <th>Aksi</th>
                    
        </div>
    </div>

    <div class="header-blue" style="background-color: #2980b9;">Input Obat dan Alkes</div>
    <div class="section-box p-3">
        <div class="row g-3">
            <div class="col-md-2">
                <label class="fw-bold">R/ ke</label>
                <input type="number" class="form-control form-control-sm" value="1">
            </div>
            <div class="col-md-3">
                <label class="fw-bold">Jenis kemasan</label>
                <select class="form-select form-select-sm">
                    <option>Non Racikan</option>
                    <option>Racikan</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="fw-bold">Kekuatan</label>
                <input type="text" class="form-control form-control-sm">
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-md-4">
                <label class="fw-bold">Produk</label>
                <input type="text" class="form-control form-control-sm" placeholder="Ketik nama obat...">
            </div>
            <div class="col-md-1">
                <label class="fw-bold">Qty</label>
                <input type="number" class="form-control form-control-sm" value="1">
            </div>
            <div class="col-md-2">
                <label class="fw-bold">Satuan</label>
                <select class="form-select form-select-sm">
                    <option>TABLET</option>
                    <option>BOTOL</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="fw-bold">Aturan pakai</label>
                <input type="text" class="form-control form-control-sm" placeholder="3 x 1">
            </div>
            <div class="col-md-3">
                <label class="fw-bold">Keterangan</label>
                <input type="text" class="form-control form-control-sm">
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button class="btn-action btn-blue"><i class="fas fa-plus"></i> Paket Obat</button>
            <button class="btn-action btn-blue"><i class="fas fa-plus"></i> Tambah</button>
            <button class="btn-action btn-red"><i class="fas fa-trash"></i> Hapus</button>
            <button class="btn-action btn-orange"><i class="fas fa-undo"></i> Batal</button>
        </div>
    </div>

    <div class="section-box">
        <div class="table-responsive">
            <table class="table-simrs">
                <thead style="background-color: #2c3e50;">
                    <tr>
                        <th>No</th>
                        <th>R/ke</th>
                        <th>Kemasan</th>
                        <th>Jml/Dosis</th>
                        <th>Aturan Pakai</th>
                        <th>Keterangan</th>
                        <th>Stok</th>
                        <th>Harga</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="9" class="text-center py-3 text-muted">Tidak ada data obat diinput</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="p-2 bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-primary">Sub Total : 0.00</h6>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-primary px-4"><i class="fas fa-save me-1"></i> Simpan</button>
                <button class="btn btn-sm btn-info text-white"><i class="fas fa-history me-1"></i> Riwayat Order</button>
            </div>
        </div>
    </div>
</div>