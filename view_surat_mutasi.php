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
</head>

<body>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4 text-center my-3">Laporan Surat Mutasi Barang</h1>
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-12">
                                    <a href="report/laporan_surat_mutasi_barang.php" class="btn btn-secondary" target="_blank">
                                        <i class="fa fa-print"></i> Cetak Surat Mutasi
                                    </a>
                            </div>
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
</body>

</html>