<?php if (isset($_POST['submit'])): ?>
    <?php
        // Tangkap data dari POST
        $nik = $_POST['nik'];
        $kk = $_POST['kk'];
        $nama = $_POST['nama'];
        $tempat = $_POST['tempatLahir'];
        $tgl = $_POST['tglLahir'];
        $jk = $_POST['jenisKelamin'];
        $alamat = $_POST['alamat'];
    ?>

    <h4>Data Pasien</h4>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>NIK</th>
                <th>No KK</th>
                <th>Nama Pasien</th>
                <th>Tempat Lahir</th>
                <th>Tanggal Lahir</th>
                <th>Jenis Kelamin</th>
                <th>Alamat</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= htmlspecialchars($nik) ?></td>
                <td><?= htmlspecialchars($kk) ?></td>
                <td><?= htmlspecialchars($nama) ?></td>
                <td><?= htmlspecialchars($tempat) ?></td>
                <td><?= htmlspecialchars($tgl) ?></td>
                <td><?= htmlspecialchars($jk) ?></td>
                <td><?= htmlspecialchars($alamat) ?></td>
            </tr>
        </tbody>
    </table>
<?php endif; ?>
