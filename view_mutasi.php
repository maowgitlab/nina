<?php
include 'sidebar.php';
include_once "koneksi.php";

// Ambil filter status dari parameter GET
$filter_status = isset($_GET['filter_status']) ? htmlspecialchars($_GET['filter_status']) : 'mutasi';
$valid_status = ['all', 'mutasi', 'temporary'];
if (!in_array($filter_status, $valid_status)) {
    $filter_status = 'mutasi';
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <title>Laporan Mutasi Barang</title>
</head>

<body>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4 text-center my-3">Laporan Mutasi Barang</h1>
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                    <a href="report/laporan_mutasi.php?filter_status=<?php echo urlencode($filter_status); ?>" class="btn btn-secondary" target="_blank">
                                        <i class="fa fa-print"></i> Cetak Laporan
                                    </a>
                            </div>
                            <div class="col-md-6 text-right">
                                <form method="get" action="">
                                    <label for="filter_status">Filter Status:</label>
                                    <select name="filter_status" id="filter_status" class="form-control d-inline-block w-auto" onchange="this.form.submit()">
                                        <option value="all" <?php echo ($filter_status == 'all') ? 'selected' : ''; ?>>Semua</option>
                                        <option value="mutasi" <?php echo ($filter_status == 'mutasi') ? 'selected' : ''; ?>>Mutasi Permanen</option>
                                        <option value="temporary" <?php echo ($filter_status == 'temporary') ? 'selected' : ''; ?>>Mutasi Sementara</option>
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
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th>Ruangan Asal</th>
                                        <th>Ruangan Tujuan</th>
                                        <th>Jumlah</th>
                                        <th>Status</th>
                                        <th>Dimutasi Oleh</th>
                                        <th>Tanggal Berita</th>
                                        <th>Penanggung Jawab Asal</th>
                                        <th>Penanggung Jawab Tujuan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT m.*,
                                            b.kode,
                                             b.id_barang,
                                             b.nama_barang,
                                             u.nama,
                                             r1.nama_ruangan AS ruangan_asal,
                                             r2.nama_ruangan AS ruangan_tujuan,
                                             bam.tanggal_berita,
                                             bam.penanggung_jawab_asal,
                                             bam.penanggung_jawab_tujuan
                                      FROM mutasi_barang m
                                      JOIN barang b ON m.id_barang = b.id_barang
                                      JOIN ruangan r1 ON m.id_ruangan = r1.id_ruangan
                                      JOIN ruangan r2 ON m.id_ruangan1 = r2.id_ruangan
                                      JOIN user u ON m.id_user = u.id_user
                                      LEFT JOIN berita_acara_mutasi bam ON m.id_mutasi = bam.id_mutasi";
                                    if ($filter_status != 'all') {
                                        $query .= " WHERE m.status = '" . mysqli_real_escape_string($conn, $filter_status) . "'";
                                    }
                                    $result = mysqli_query($conn, $query);
                                    $i = 1;
                                    $total_jumlah = 0;
                                    $row_count = mysqli_num_rows($result);

                                    if ($row_count > 0) :
                                        while ($data = mysqli_fetch_array($result)) :
                                            $total_jumlah += (int)$data['jumlah'];
                                    ?>
                                            <tr>
                                                <td><?php echo $i++; ?></td>
                                                <td><?php echo htmlspecialchars($data['kode'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($data['nama_barang'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($data['ruangan_asal'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($data['ruangan_tujuan'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars(number_format($data['jumlah'], 0, ',', '.'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($data['status'] == 'mutasi' ? 'Mutasi Permanen' : 'Mutasi Sementara', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($data['nama'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo !empty($data['tanggal_berita']) ? htmlspecialchars(date('d/m/Y', strtotime($data['tanggal_berita'])), ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                                <td><?php echo htmlspecialchars($data['penanggung_jawab_asal'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($data['penanggung_jawab_tujuan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <tr>
                                            <td colspan="5" class="text-right font-weight-bold">Total:</td>
                                            <td><?php echo htmlspecialchars(number_format($total_jumlah, 0, ',', '.'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td colspan="5"></td>
                                        </tr>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="11" class="text-center">Tidak ada data mutasi barang untuk status yang dipilih.</td>
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
                    <div class="text-muted">Hak Cipta &copy; Website Anda 2023</div>
                    <div>
                        <a href="#">Kebijakan Privasi</a>
                        &middot;
                        <a href="#">Syarat &amp; Ketentuan</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>

</html>