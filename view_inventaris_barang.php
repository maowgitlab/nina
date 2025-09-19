<?php
include 'sidebar.php';
include_once "koneksi.php";

// Get the selected filters, if any
$selected_room = isset($_GET['filter_ruangan']) ? htmlspecialchars($_GET['filter_ruangan']) : '';
$filter_bulan_dari = isset($_GET['filter_bulan_dari']) ? htmlspecialchars($_GET['filter_bulan_dari']) : 'all';
$filter_bulan_sampai = isset($_GET['filter_bulan_sampai']) ? htmlspecialchars($_GET['filter_bulan_sampai']) : 'all';
$filter_tahun = isset($_GET['filter_tahun']) ? htmlspecialchars($_GET['filter_tahun']) : 'all';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <title>Laporan Inventaris Barang</title>
    <style>
        /* Custom Filter Styling */
        .filter-container {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .filter-container:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .filter-container .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }

        .filter-container .form-group {
            flex: 1;
            min-width: 150px;
        }

        .filter-container label {
            font-size: 0.9rem;
            color: #343a40;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .filter-container select,
        .filter-container .btn-print {
            font-size: 0.85rem;
            padding: 8px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .filter-container select {
            border: 1px solid #ced4da;
            background-color: #fff;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .filter-container select:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
            outline: none;
        }

        .filter-container select:hover {
            background-color: #f1f3f5;
        }

        .filter-container .btn-print {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 8px 15px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .filter-container .btn-print:hover {
            background-color: #218838;
            transform: scale(1.05);
        }

        .filter-container .btn-print i {
            font-size: 0.9rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .filter-container .form-row {
                flex-direction: column;
                gap: 10px;
            }

            .filter-container .form-group {
                min-width: 100%;
            }

            .filter-container select,
            .filter-container .btn-print {
                font-size: 0.8rem;
                padding: 6px;
            }
        }

        @media (max-width: 576px) {
            .filter-container {
                padding: 10px;
            }

            .filter-container label {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4 text-center my-3">Laporan Inventaris Barang</h1>
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="filter-container">
                            <form method="GET" class="form-row">
                                <div class="form-group">
                                    <label for="filter_ruangan">Filter Ruangan:</label>
                                    <select name="filter_ruangan" id="filter_ruangan" class="form-control" onchange="this.form.submit()">
                                        <option value="">Semua Ruangan</option>
                                        <?php
                                        $ruangan_list = mysqli_query($conn, "SELECT * FROM ruangan");
                                        while ($ruangan = mysqli_fetch_array($ruangan_list)) {
                                            $selected = ($ruangan['nama_ruangan'] == $selected_room) ? "selected" : "";
                                            echo "<option value='" . htmlspecialchars($ruangan['nama_ruangan']) . "' $selected>" . htmlspecialchars($ruangan['nama_ruangan']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="filter_bulan_dari">Dari Bulan:</label>
                                    <select name="filter_bulan_dari" id="filter_bulan_dari" class="form-control" onchange="this.form.submit()">
                                        <option value="all" <?php echo ($filter_bulan_dari == 'all') ? 'selected' : ''; ?>>Semua</option>
                                        <option value="01" <?php echo ($filter_bulan_dari == '01') ? 'selected' : ''; ?>>Januari</option>
                                        <option value="02" <?php echo ($filter_bulan_dari == '02') ? 'selected' : ''; ?>>Februari</option>
                                        <option value="03" <?php echo ($filter_bulan_dari == '03') ? 'selected' : ''; ?>>Maret</option>
                                        <option value="04" <?php echo ($filter_bulan_dari == '04') ? 'selected' : ''; ?>>April</option>
                                        <option value="05" <?php echo ($filter_bulan_dari == '05') ? 'selected' : ''; ?>>Mei</option>
                                        <option value="06" <?php echo ($filter_bulan_dari == '06') ? 'selected' : ''; ?>>Juni</option>
                                        <option value="07" <?php echo ($filter_bulan_dari == '07') ? 'selected' : ''; ?>>Juli</option>
                                        <option value="08" <?php echo ($filter_bulan_dari == '08') ? 'selected' : ''; ?>>Agustus</option>
                                        <option value="09" <?php echo ($filter_bulan_dari == '09') ? 'selected' : ''; ?>>September</option>
                                        <option value="10" <?php echo ($filter_bulan_dari == '10') ? 'selected' : ''; ?>>Oktober</option>
                                        <option value="11" <?php echo ($filter_bulan_dari == '11') ? 'selected' : ''; ?>>November</option>
                                        <option value="12" <?php echo ($filter_bulan_dari == '12') ? 'selected' : ''; ?>>Desember</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="filter_bulan_sampai">Sampai Bulan:</label>
                                    <select name="filter_bulan_sampai" id="filter_bulan_sampai" class="form-control" onchange="this.form.submit()">
                                        <option value="all" <?php echo ($filter_bulan_sampai == 'all') ? 'selected' : ''; ?>>Semua</option>
                                        <option value="01" <?php echo ($filter_bulan_sampai == '01') ? 'selected' : ''; ?>>Januari</option>
                                        <option value="02" <?php echo ($filter_bulan_sampai == '02') ? 'selected' : ''; ?>>Februari</option>
                                        <option value="03" <?php echo ($filter_bulan_sampai == '03') ? 'selected' : ''; ?>>Maret</option>
                                        <option value="04" <?php echo ($filter_bulan_sampai == '04') ? 'selected' : ''; ?>>April</option>
                                        <option value="05" <?php echo ($filter_bulan_sampai == '05') ? 'selected' : ''; ?>>Mei</option>
                                        <option value="06" <?php echo ($filter_bulan_sampai == '06') ? 'selected' : ''; ?>>Juni</option>
                                        <option value="07" <?php echo ($filter_bulan_sampai == '07') ? 'selected' : ''; ?>>Juli</option>
                                        <option value="08" <?php echo ($filter_bulan_sampai == '08') ? 'selected' : ''; ?>>Agustus</option>
                                        <option value="09" <?php echo ($filter_bulan_sampai == '09') ? 'selected' : ''; ?>>September</option>
                                        <option value="10" <?php echo ($filter_bulan_sampai == '10') ? 'selected' : ''; ?>>Oktober</option>
                                        <option value="11" <?php echo ($filter_bulan_sampai == '11') ? 'selected' : ''; ?>>November</option>
                                        <option value="12" <?php echo ($filter_bulan_sampai == '12') ? 'selected' : ''; ?>>Desember</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="filter_tahun">Tahun:</label>
                                    <select name="filter_tahun" id="filter_tahun" class="form-control" onchange="this.form.submit()">
                                        <option value="all" <?php echo ($filter_tahun == 'all') ? 'selected' : ''; ?>>Semua</option>
                                        <?php
                                        $current_year = date('Y');
                                        for ($year = 2020; $year <= $current_year; $year++) {
                                            $selected = ($filter_tahun == $year) ? 'selected' : '';
                                            echo "<option value='$year' $selected>$year</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                    <div class="form-group">
                                        <a href="report/laporan_inventaris_barang.php?filter_ruangan=<?= urlencode($selected_room) ?>&filter_bulan_dari=<?= urlencode($filter_bulan_dari) ?>&filter_bulan_sampai=<?= urlencode($filter_bulan_sampai) ?>&filter_tahun=<?= urlencode($filter_tahun) ?>" class="btn btn-print" target="_blank">
                                            <i class="fa fa-print"></i> Print Laporan
                                        </a>
                                    </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode</th>
                                        <th>Gambar</th>
                                        <th>QR Code</th>
                                        <th>Nama Barang</th>
                                        <th>Lokasi Ruangan</th>
                                        <th>Merk</th>
                                        <th>Jumlah</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Adjust the SQL query based on user level and filters
                                    if ($data_level == 'pegawai') {
                                        $query = "SELECT ib.id_inventaris_barang, ib.status, b.id_barang, b.kode, b.nama_barang, b.gambar, b.qrcode, r.id_ruangan, r.nama_ruangan, ib.merk, ib.jumlah, ib.tanggal 
                                                  FROM inventaris_barang ib
                                                  JOIN barang b ON ib.id_barang = b.id_barang
                                                  JOIN ruangan r ON ib.id_ruangan = r.id_ruangan 
                                                  WHERE ib.id_user = '$data_id'";
                                    } else {
                                        $query = "SELECT ib.id_inventaris_barang, ib.status, b.id_barang, b.kode, b.nama_barang, b.gambar, b.qrcode, r.id_ruangan, r.nama_ruangan, ib.merk, ib.jumlah, ib.tanggal 
                                                  FROM inventaris_barang ib
                                                  JOIN barang b ON ib.id_barang = b.id_barang
                                                  JOIN ruangan r ON ib.id_ruangan = r.id_ruangan";
                                    }
                                    $conditions = [];
                                    if (!empty($selected_room)) {
                                        $conditions[] = "r.nama_ruangan = '" . mysqli_real_escape_string($conn, $selected_room) . "'";
                                    }
                                    if ($filter_bulan_dari != 'all' && $filter_bulan_sampai != 'all') {
                                        $conditions[] = "MONTH(ib.tanggal) BETWEEN '" . mysqli_real_escape_string($conn, $filter_bulan_dari) . "' AND '" . mysqli_real_escape_string($conn, $filter_bulan_sampai) . "'";
                                    } elseif ($filter_bulan_dari != 'all') {
                                        $conditions[] = "MONTH(ib.tanggal) >= '" . mysqli_real_escape_string($conn, $filter_bulan_dari) . "'";
                                    } elseif ($filter_bulan_sampai != 'all') {
                                        $conditions[] = "MONTH(ib.tanggal) <= '" . mysqli_real_escape_string($conn, $filter_bulan_sampai) . "'";
                                    }
                                    if ($filter_tahun != 'all') {
                                        $conditions[] = "YEAR(ib.tanggal) = '" . mysqli_real_escape_string($conn, $filter_tahun) . "'";
                                    }
                                    if (!empty($conditions)) {
                                        $query .= " WHERE " . implode(" AND ", $conditions);
                                    }
                                    $result = mysqli_query($conn, $query);
                                    $i = 1;
                                    $total_jumlah = 0;
                                    while ($data = mysqli_fetch_assoc($result)) {
                                        $total_jumlah += (int)$data['jumlah'];
                                        $gambar = $data['gambar'] ? $data['gambar'] : 'default.jpg';
                                        $qrcode = $data['qrcode'] ? $data['qrcode'] : 'default_qr.png';
                                    ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td><?= htmlspecialchars($data['kode'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><img src="Uploads/<?= htmlspecialchars($gambar, ENT_QUOTES, 'UTF-8'); ?>" alt="Gambar Barang" style="width: 50px; height: 50px;"></td>
                                            <td><img src="<?= htmlspecialchars($qrcode, ENT_QUOTES, 'UTF-8'); ?>" alt="QR Code" style="width: 50px; height: 50px;"></td>
                                            <td><?= htmlspecialchars($data['nama_barang'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($data['nama_ruangan'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($data['merk'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($data['jumlah'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($data['tanggal'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <td colspan="7" style="text-align: right; font-weight: bold;">Total:</td>
                                        <td><?= htmlspecialchars(number_format($total_jumlah, 0, ',', '.'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td></td>
                                    </tr>
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