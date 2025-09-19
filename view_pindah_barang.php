<?php
include 'sidebar.php';
include_once "koneksi.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4 text-center my-3">Laporan Perpindahan Barang per Ruangan</h1>
                <div class="card mb-4">
                    <div class="card-header">
                        <a href="report/laporan_pindah_barang.php" class="btn btn-secondary"><i class="fa fa-print"></i> Print Laporan</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>ID Ruangan</th>
                                        <th>Nama Ruangan 1</th>
                                        <th>Total Barang</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT ruangan1.id_ruangan1, ruangan1.nama_ruangan1, count(ruangan1.id_ruangan1) as total FROM ruangan1 JOIN mutasi_barang ON mutasi_barang.id_ruangan1 = ruangan1.id_ruangan1 WHERE mutasi_barang.jumlah != 0 group by id_ruangan1"; 
                                    $result = mysqli_query($conn, $query);
                                    $i = 1;
                                    while ($data = mysqli_fetch_array($result)) {
                                    ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td><?= $data['id_ruangan1']; ?></td>
                                            <td><?= $data['nama_ruangan1']; ?></td>
                                            <td><?= $data['total']; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>