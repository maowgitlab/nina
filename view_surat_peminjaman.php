<?php
include 'sidebar.php';
include_once "koneksi.php";

// Ambil filter status dari parameter GET
$filter_status = isset($_GET['filter_status']) ? htmlspecialchars($_GET['filter_status']) : 'dipinjam';
$valid_status = ['all', 'dipinjam', 'dikembalikan'];
if (!in_array($filter_status, $valid_status)) {
    $filter_status = 'dipinjam';
}
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
    <title>Surat Peminjaman Barang/Kendaraan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Surat Peminjaman Barang/Kendaraan</h1>
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                    <a href="report/laporan_surat_peminjaman.php?filter_status=<?php echo urlencode($filter_status); ?>" class="btn btn-secondary" target="_blank">
                                        <i class="fa fa-print"></i> Cetak Surat Peminjaman
                                    </a>
                            </div>
                            <div class="col-md-6 text-right">
                                <form method="get" action="">
                                    <label for="filter_status">Filter Status:</label>
                                    <select name="filter_status" id="filter_status" class="form-control d-inline-block w-auto" onchange="this.form.submit()">
                                        <option value="all" <?php echo ($filter_status == 'all') ? 'selected' : ''; ?>>Semua</option>
                                        <option value="dipinjam" <?php echo ($filter_status == 'dipinjam') ? 'selected' : ''; ?>>Dipinjam</option>
                                        <option value="dikembalikan" <?php echo ($filter_status == 'dikembalikan') ? 'selected' : ''; ?>>Dikembalikan</option>
                                    </select>
                                </form>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/chart-area-demo.js"></script>
    <script src="assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>
</body>

</html>