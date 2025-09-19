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
                <h1 class="mt-4 text-center my-3">Laporan Stok Barang</h1>
                <div class="card mb-4">
                    <div class="card-header">
                        <a href="report/laporan_stok_barang.php" class="btn btn-secondary"><i class="fa fa-print"></i> Print Laporan</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>ID Barang</th>
                                        <th>Nama Barang</th>
                                        <th>Stok</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT * FROM barang ORDER BY id_barang DESC"; 
                                    $result = mysqli_query($conn, $query);
                                    $i = 1;
                                    while ($data = mysqli_fetch_array($result)) {
                                    ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td><?= $data['id_barang']; ?></td>
                                            <td><?= $data['nama_barang']; ?></td>
                                            <td><?= $data['stok'] == 1 ? $data['stok']  . ' <u>(Tersedia)</u>' : $data['stok'] . ' <u>(Kosong)</u>'; ?></td>
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