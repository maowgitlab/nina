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
    <title>Peminjaman Barang/Kendaraan - Laporan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4 text-center my-3">Laporan Peminjaman Barang/Kendaraan</h1>
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                    <a href="report/laporan_peminjaman_barang.php?filter_status=<?php echo urlencode($filter_status); ?>" class="btn btn-secondary" target="_blank">
                                        <i class="fa fa-print"></i> Cetak Laporan
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
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>ID Item</th>
                                        <th>Nama Item</th>
                                        <th>Dipinjam Oleh</th>
                                        <th>Jumlah</th>
                                        <th>Jenis</th>
                                        <th>Tanggal Pinjam</th>
                                        <th>Tanggal Kembali</th>
                                        <th>Keterangan</th>
                                        <th>Gambar</th>
                                        <th>QR Code</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT p.*, b.nama_barang, b.gambar AS gambar_barang, b.qrcode AS qrcode_barang, k.nama_kendaraan AS nama_kendaraan, k.gambar AS gambar_kendaraan, k.qrcode AS qrcode_kendaraan, u.nama AS dipinjam_oleh 
                                              FROM peminjaman p 
                                              LEFT JOIN barang b ON p.id_barang = b.id_barang AND p.jenis = 'barang'
                                              LEFT JOIN inventaris_kendaraan k ON p.id_barang = k.id_inventaris_kendaraan AND p.jenis = 'kendaraan'
                                              JOIN user u ON p.id_user = u.id_user";
                                    if ($filter_status != 'all') {
                                        $query .= " WHERE p.status = '" . mysqli_real_escape_string($conn, $filter_status) . "'";
                                    }
                                    $query .= " ORDER BY p.tanggal_pinjam DESC";
                                    $result = mysqli_query($conn, $query);
                                    $i = 1;
                                    $total_jumlah = 0;
                                    $row_count = mysqli_num_rows($result);

                                    if ($row_count > 0) :
                                        while ($data = mysqli_fetch_array($result)) :
                                            $nama = $data['jenis'] === 'barang' ? $data['nama_barang'] : $data['nama_kendaraan'];
                                            $gambar = $data['jenis'] === 'barang' ? $data['gambar_barang'] : $data['gambar_kendaraan'];
                                            $qrcode = $data['jenis'] === 'barang' ? $data['qrcode_barang'] : $data['qrcode_kendaraan'];
                                            $total_jumlah += (int)$data['jumlah'];
                                    ?>
                                            <tr>
                                                <td><?= $i++; ?></td>
                                                <td><?= htmlspecialchars($data['id_barang'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($nama ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($data['dipinjam_oleh'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars(number_format($data['jumlah'], 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars(ucfirst($data['jenis']), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars(date('d-m-Y', strtotime($data['tanggal_pinjam'])), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= $data['tanggal_kembali'] == null ? 'Belum dikembalikan' : htmlspecialchars(date('d-m-Y', strtotime($data['tanggal_kembali'])), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($data['keterangan'] ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><img src="uploads/<?= htmlspecialchars($gambar ?: '', ENT_QUOTES, 'UTF-8') ?>" alt="Gambar" width="50" style="display: <?= $gambar ? 'block' : 'none' ?>;"></td>
                                                <td><img src="<?= htmlspecialchars($qrcode ?: '', ENT_QUOTES, 'UTF-8') ?>" alt="QR Code" width="50" style="display: <?= $qrcode ? 'block' : 'none' ?>;"></td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <tr>
                                            <td colspan="4" class="text-right font-weight-bold">Total:</td>
                                            <td><?= htmlspecialchars(number_format($total_jumlah, 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td colspan="6"></td>
                                        </tr>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="11" class="text-center">Tidak ada data peminjaman untuk status yang dipilih.</td>
                                        </tr>
                                    <?php endif; ?>
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