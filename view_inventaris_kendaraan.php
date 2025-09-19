<?php
include 'sidebar.php';
include_once "koneksi.php";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <title>Laporan Inventaris Kendaraan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4 text-center my-3">Laporan Inventaris Kendaraan</h1>
                <div class="card mb-4">
                    <div class="card-header">
                            <a href="report/laporan_inventaris_kenderaan.php" class="btn btn-secondary" target="_blank">
                                <i class="fa fa-print"></i> Print Laporan
                            </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Pegawai</th>
                                        <th>Nama Kendaraan</th>
                                        <th>Nomor Rangka</th>
                                        <th>Jumlah</th>
                                        <th>Tanggal Masuk</th>
                                        <th>Nomor Polisi</th>
                                        <th>Nomor BPKB</th>
                                        <th>Roda</th>
                                        <th>QR Code</th>
                                        <th>Gambar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT * FROM inventaris_kendaraan JOIN pegawai ON inventaris_kendaraan.id_pegawai = pegawai.id_pegawai ORDER BY id_inventaris_kendaraan DESC";
                                    $result = mysqli_query($conn, $query);
                                    $i = 1;
                                    while ($data = mysqli_fetch_array($result)) {
                                    ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td><?= htmlspecialchars($data['nama_pegawai'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($data['nama_kendaraan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($data['nomor_rangka'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($data['jumlah'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($data['tanggal_masuk'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($data['nomor_polisi'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($data['nomor_bpkb'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($data['roda'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><img src="<?= htmlspecialchars($data['qrcode'], ENT_QUOTES, 'UTF-8'); ?>" alt="QR Code" style="width: 50px; height: 50px;"></td>
                                            <td><img src="uploads/<?= htmlspecialchars($data['gambar'], ENT_QUOTES, 'UTF-8'); ?>" alt="Gambar" style="width: 50px; height: 50px;"></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
                <div class="d-flex align-items-center justify-content-between small">
                    <div class="text-muted">Hak Cipta © Website Anda 2025</div>
                    <div>
                        <a href="#">Kebijakan Privasi</a>
                        ·
                        <a href="#">Syarat & Ketentuan</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/chart-area-demo.js"></script>
    <script src="assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>
</body>
</html>