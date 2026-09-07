<?php
require_once 'koneksi.php';

$no_kartu = $_GET['no_kartu'] ?? '';

if (!$no_kartu) {
    echo "<div class='alert alert-danger'>No kartu tidak ditemukan!</div>";
    exit;
}

$vital_sign_history = [];

$query = "SELECT * FROM vital_sign 
          WHERE no_kartu = :no_kartu 
          ORDER BY tgl_input DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([':no_kartu' => $no_kartu]);
$vital_sign_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
<div class="row">

<!-- FORM -->
<div class="col-lg-4">
<div class="card shadow-sm">
<div class="card-header"><b>Input Vital Sign</b></div>

<div class="card-body">

<form id="formVitalSign">
<input type="hidden" name="no_kartu" value="<?= $no_kartu ?>">

<div class="mb-3">
<label>Tensi</label>
<input type="text" name="tensi" class="form-control" required>
</div>

<div class="mb-3">
<label>Nadi</label>
<input type="number" name="nadi" class="form-control" required>
</div>

<div class="mb-3">
<label>Suhu</label>
<input type="number" step="0.1" name="suhu" class="form-control" required>
</div>

<div class="mb-3">
<label>Respirasi</label>
<input type="number" name="respirasi" class="form-control" required>
</div>

<button type="button" id="btnSimpanVital" class="btn btn-primary w-100">
Simpan Data
</button>

</form>

</div>
</div>
</div>

<!-- TABLE -->
<div class="col-lg-8">
<div class="card shadow-sm">
<div class="card-header"><b>Riwayat Vital Sign</b></div>

<div class="table-responsive">
<table class="table table-hover">
<thead>
<tr>
<th>Tanggal</th>
<th>Tensi</th>
<th>Nadi</th>
<th>Suhu</th>
<th>RR</th>
</tr>
</thead>

<tbody>
<?php if(empty($vital_sign_history)): ?>
<tr><td colspan="5" class="text-center">Belum ada data</td></tr>
<?php else: ?>
<?php foreach($vital_sign_history as $vs): ?>
<tr>
<td><?= $vs['tgl_input'] ?></td>
<td><?= $vs['tensi'] ?></td>
<td><?= $vs['nadi'] ?></td>
<td><?= $vs['suhu'] ?></td>
<td><?= $vs['respirasi'] ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>

</table>
</div>

</div>
</div>

</div>
</div>