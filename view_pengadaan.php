<?php
include 'sidebar.php';
include_once "koneksi.php";

if (isset($_POST['addpengadaan_barang'])) {
    $id_barang = htmlspecialchars($_POST['barang']);
    $id_supplier = htmlspecialchars($_POST['supplier']);
    $id_ruangan = htmlspecialchars($_POST['ruangan']);
    $jumlah = htmlspecialchars($_POST['jumlah']);
    $tanggal_masuk = htmlspecialchars($_POST['tanggal_masuk']);

    $query = "INSERT INTO pengadaan_barang (id_barang, id_supplier, id_ruangan, jumlah, tanggal_masuk)
              VALUES ('$id_barang', '$id_supplier', '$id_ruangan', '$jumlah', '$tanggal_masuk')";
    $result = mysqli_query($conn, $query);

    if ($result) {
        echo '<script>alert("Data berhasil ditambahkan!"); window.location.href = window.location.href;</script>';
    } else {
        echo '<script>alert("Gagal menambahkan data!");</script>';
    }
}

// Ambil filter dari parameter GET
$filter_ruangan = isset($_GET['filter_ruangan']) ? htmlspecialchars($_GET['filter_ruangan']) : 'all';
$filter_bulan_dari = isset($_GET['filter_bulan_dari']) ? htmlspecialchars($_GET['filter_bulan_dari']) : 'all';
$filter_bulan_sampai = isset($_GET['filter_bulan_sampai']) ? htmlspecialchars($_GET['filter_bulan_sampai']) : 'all';
$filter_tahun = isset($_GET['filter_tahun']) ? htmlspecialchars($_GET['filter_tahun']) : 'all';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <title>Laporan Pengadaan Barang</title>
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
                <h1 class="mt-4 text-center my-3">Laporan Pengadaan Barang</h1>
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="filter-container">
                            <form method="get" action="">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="filter_ruangan">Filter Ruangan:</label>
                                        <select name="filter_ruangan" id="filter_ruangan" class="form-control" onchange="this.form.submit()">
                                            <option value="all" <?php echo ($filter_ruangan == 'all') ? 'selected' : ''; ?>>Semua Ruangan</option>
                                            <?php
                                            $ruangan_list = mysqli_query($conn, "SELECT * FROM ruangan");
                                            while ($ruangan = mysqli_fetch_array($ruangan_list)) {
                                                $selected = ($filter_ruangan == $ruangan['id_ruangan']) ? 'selected' : '';
                                                echo "<option value='" . htmlspecialchars($ruangan['id_ruangan'], ENT_QUOTES, 'UTF-8') . "' $selected>" . htmlspecialchars($ruangan['nama_ruangan'], ENT_QUOTES, 'UTF-8') . "</option>";
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
                                        <a href="report/laporan_pengadaan.php?filter_ruangan=<?= urlencode($filter_ruangan) ?>&filter_bulan_dari=<?= urlencode($filter_bulan_dari) ?>&filter_bulan_sampai=<?= urlencode($filter_bulan_sampai) ?>&filter_tahun=<?= urlencode($filter_tahun) ?>" class="btn btn-print" target="_blank">
                                            <i class="fa fa-print"></i> Print Laporan
                                        </a>
                                    </div>
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
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th>Nama Supplier</th>
                                        <th>Lokasi Ruangan</th>
                                        <th>Jumlah</th>
                                        <th>Tanggal Masuk</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT pb.*, b.kode, b.nama_barang, s.nama_supplier, r.nama_ruangan 
                                              FROM pengadaan_barang pb 
                                              JOIN barang b ON pb.id_barang = b.id_barang
                                              JOIN supplier s ON pb.id_supplier = s.id_supplier
                                              JOIN ruangan r ON pb.id_ruangan = r.id_ruangan";
                                    $conditions = [];
                                    if ($filter_ruangan != 'all') {
                                        $conditions[] = "pb.id_ruangan = '" . mysqli_real_escape_string($conn, $filter_ruangan) . "'";
                                    }
                                    if ($filter_bulan_dari != 'all' && $filter_bulan_sampai != 'all') {
                                        $conditions[] = "MONTH(pb.tanggal_masuk) BETWEEN '" . mysqli_real_escape_string($conn, $filter_bulan_dari) . "' AND '" . mysqli_real_escape_string($conn, $filter_bulan_sampai) . "'";
                                    } elseif ($filter_bulan_dari != 'all') {
                                        $conditions[] = "MONTH(pb.tanggal_masuk) >= '" . mysqli_real_escape_string($conn, $filter_bulan_dari) . "'";
                                    } elseif ($filter_bulan_sampai != 'all') {
                                        $conditions[] = "MONTH(pb.tanggal_masuk) <= '" . mysqli_real_escape_string($conn, $filter_bulan_sampai) . "'";
                                    }
                                    if ($filter_tahun != 'all') {
                                        $conditions[] = "YEAR(pb.tanggal_masuk) = '" . mysqli_real_escape_string($conn, $filter_tahun) . "'";
                                    }
                                    if (!empty($conditions)) {
                                        $query .= " WHERE " . implode(" AND ", $conditions);
                                    }
                                    $ambilsemuadatanya = mysqli_query($conn, $query);
                                    $i = 1;
                                    while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                        $id_barang = $data['kode'];
                                        $id_pengadaan = $data['id_pengadaan'];
                                        $nama_barang = $data['nama_barang'];
                                        $nama_supplier = $data['nama_supplier'];
                                        $nama_ruangan = $data['nama_ruangan'];
                                        $jumlah = $data['jumlah'];
                                        $tanggal_masuk = date('d-m-Y', strtotime($data['tanggal_masuk']));
                                    ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><?= htmlspecialchars($id_barang, ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($nama_barang, ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($nama_supplier, ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($nama_ruangan, ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($jumlah, ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($tanggal_masuk, ENT_QUOTES, 'UTF-8') ?></td>
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
                    <div class="text-muted">Copyright © Your Website 2025</div>
                    <div>
                        <a href="#">Privacy Policy</a>
                        ·
                        <a href="#">Terms & Conditions</a>
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