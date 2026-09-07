<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}

$success_message = $_SESSION['success_message'] ?? '';
$error_message   = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

$stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Users</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>

body{
font-family:Poppins,sans-serif;
background:#f4f6f9;
}

.navbar{
background:#2c3e50;
}

.table-card{
background:white;
padding:30px;
border-radius:15px;
box-shadow:0 10px 25px rgba(0,0,0,0.08);
}

</style>

</head>

<body>

<nav class="navbar navbar-dark px-4">

<a class="navbar-brand" href="dashboard.php">
<i class="fas fa-hospital me-2"></i>RSUD
</a>

<div>

<a href="dashboard.php" class="btn btn-light btn-sm me-2">Dashboard</a>
<a href="tambah_user.php" class="btn btn-success btn-sm me-2">Tambah User</a>
<a href="logout.php" class="btn btn-danger btn-sm">Logout</a>

</div>

</nav>

<div class="container mt-4">

<div class="table-card">

<h3 class="mb-4">
<i class="fas fa-users me-2"></i>Kelola User
</h3>

<table class="table table-striped table-hover">

<thead class="table-dark">

<tr>
<th>ID</th>
<th>Username</th>
<th>Role</th>
<th>Dibuat</th>
<th>Aksi</th>
</tr>

</thead>

<tbody>

<?php foreach($users as $user): ?>

<tr>

<td><?= $user['id'] ?></td>
<td><?= htmlspecialchars($user['username']) ?></td>
<td><?= $user['role'] ?></td>
<td><?= $user['created_at'] ?></td>

<td>

<button class="btn btn-warning btn-sm"
data-bs-toggle="modal"
data-bs-target="#editModal<?= $user['id'] ?>">
<i class="fas fa-edit"></i>
</button>

<button class="btn btn-danger btn-sm"
onclick="deleteUser(<?= $user['id'] ?>,'<?= $user['username'] ?>')">
<i class="fas fa-trash"></i>
</button>

</td>

</tr>

<!-- Modal Edit -->

<div class="modal fade" id="editModal<?= $user['id'] ?>">

<div class="modal-dialog">

<div class="modal-content">

<form action="process_edit_user.php" method="POST">

<div class="modal-header">
<h5>Edit User</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<input type="hidden" name="id" value="<?= $user['id'] ?>">

<div class="mb-3">
<label>Username</label>
<input type="text" name="username" class="form-control"
value="<?= htmlspecialchars($user['username']) ?>" required>
</div>

<div class="mb-3">
<label>Password Baru (opsional)</label>
<input type="password" name="password" class="form-control">
</div>

<div class="mb-3">
<label>Role</label>
<select name="role" class="form-select">
<option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
<option value="user" <?= $user['role']=='user'?'selected':'' ?>>User</option>
</select>
</div>

</div>

<div class="modal-footer">

<button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
<button class="btn btn-primary">Simpan</button>

</div>

</form>

</div>

</div>

</div>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

function deleteUser(id,username){

Swal.fire({

title: 'Hapus User?',
text: "User "+username+" akan dihapus!",
icon: 'warning',
showCancelButton: true,
confirmButtonColor: '#d33',
confirmButtonText: 'Ya, Hapus',
cancelButtonText:'Batal'

}).then((result)=>{

if(result.isConfirmed){

window.location = "process_delete_user.php?id="+id;

}

})

}

<?php if($success_message): ?>

Swal.fire({
icon:'success',
title:'Berhasil',
text:'<?= addslashes($success_message) ?>'
})

<?php endif; ?>

<?php if($error_message): ?>

Swal.fire({
icon:'error',
title:'Gagal',
text:'<?= addslashes($error_message) ?>'
})

<?php endif; ?>

</script>

</body>
</html>