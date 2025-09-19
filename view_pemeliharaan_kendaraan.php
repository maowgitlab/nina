<?php
include 'sidebar.php';
include_once "koneksi.php";

// Get the selected filters, if any
$filter_bulan_dari = isset($_GET['filter_bulan_dari']) ? htmlspecialchars($_GET['filter_bulan_dari']) : 'all';
$filter_bulan_sampai = isset($_GET['filter_bulan_sampai']) ? htmlspecialchars($_GET['filter_bulan_sampai']) : 'all';
$filter_tahun = isset($_GET['filter_tahun']) ? htmlspecialchars($_GET['filter_tahun']) : 'all';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Pemeliharaan Kendaraan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <style>
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

        .table-footer {
            font-weight: bold;
            background-color: #f8f9fa;
        }

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
                <h1 class="mt-4 text-center my-3">Laporan Pemeliharaan Kendaraan</h1>

                <div class="filter-container">
                    <form method="GET" class="form-row">
                        <div class="form-group">
                            <label for="filter_bulan_dari">Dari Bulan:</label>
                            <select name="filter_bulan_dari" id="filter_bulan_dari" class="form-control" onchange="this.form.submit()">
                                <option value="all" <?= ($filter_bulan_dari == 'all') ? 'selected' : ''; ?>>Semua</option>
                                <?php for ($m = 1; $m <= 12; $m++) {
                                    $val = str_pad($m, 2, '0', STR_PAD_LEFT);
                                    echo "<option value='$val'" . ($filter_bulan_dari == $val ? ' selected' : '') . ">" . date("F", mktime(0, 0, 0, $m, 10)) . "</option>";
                                } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="filter_bulan_sampai">Sampai Bulan:</label>
                            <select name="filter_bulan_sampai" id="filter_bulan_sampai" class="form-control" onchange="this.form.submit()">
                                <option value="all" <?= ($filter_bulan_sampai == 'all') ? 'selected' : ''; ?>>Semua</option>
                                <?php for ($m = 1; $m <= 12; $m++) {
                                    $val = str_pad($m, 2, '0', STR_PAD_LEFT);
                                    echo "<option value='$val'" . ($filter_bulan_sampai == $val ? ' selected' : '') . ">" . date("F", mktime(0, 0, 0, $m, 10)) . "</option>";
                                } ?>
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
                                <label> </label>
                                <a href="report/laporan_pemeliharaan_kendaraan.php?filter_bulan_dari=<?= urlencode($filter_bulan_dari) ?>&filter_bulan_sampai=<?= urlencode($filter_bulan_sampai) ?>&filter_tahun=<?= urlencode($filter_tahun) ?>" class="btn btn-print" target="_blank">
                                    <i class="fas fa-print"></i> Cetak Laporan
                                </a>
                            </div>
                    </form>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Pegawai</th>
                                        <th>Nomor Polisi</th>
                                        <th>Tanggal</th>
                                        <th>Keterangan</th>
                                        <th>Biaya</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT pk.*, ik.nomor_polisi, p.nama_pegawai
                                              FROM pemeliharaan_kendaraan pk 
                                              JOIN inventaris_kendaraan ik ON pk.id_inventaris_kendaraan = ik.id_inventaris_kendaraan
                                              JOIN pegawai p ON ik.id_pegawai = p.id_pegawai";
                                    $conditions = [];

                                    if ($filter_bulan_dari != 'all' && $filter_bulan_sampai != 'all') {
                                        $conditions[] = "MONTH(pk.tanggal) BETWEEN '" . mysqli_real_escape_string($conn, $filter_bulan_dari) . "' AND '" . mysqli_real_escape_string($conn, $filter_bulan_sampai) . "'";
                                    } elseif ($filter_bulan_dari != 'all') {
                                        $conditions[] = "MONTH(pk.tanggal) >= '" . mysqli_real_escape_string($conn, $filter_bulan_dari) . "'";
                                    } elseif ($filter_bulan_sampai != 'all') {
                                        $conditions[] = "MONTH(pk.tanggal) <= '" . mysqli_real_escape_string($conn, $filter_bulan_sampai) . "'";
                                    }
                                    if ($filter_tahun != 'all') {
                                        $conditions[] = "YEAR(pk.tanggal) = '" . mysqli_real_escape_string($conn, $filter_tahun) . "'";
                                    }

                                    if (!empty($conditions)) {
                                        $query .= " WHERE " . implode(" AND ", $conditions);
                                    }

                                    $result = mysqli_query($conn, $query);
                                    $i = 1;
                                    $total_biaya = 0;

                                    if (mysqli_num_rows($result) > 0) {
                                        while ($data = mysqli_fetch_array($result)) {
                                            $total_biaya += (float)$data['biaya'];
                                            echo "<tr>
                                                <td>" . htmlspecialchars($i++, ENT_QUOTES, 'UTF-8') . "</td>
                                                <td>" . htmlspecialchars($data['nama_pegawai'], ENT_QUOTES, 'UTF-8') . "</td>
                                                <td>" . htmlspecialchars($data['nomor_polisi'], ENT_QUOTES, 'UTF-8') . "</td>
                                                <td>" . htmlspecialchars(date('d-m-Y', strtotime($data['tanggal'])), ENT_QUOTES, 'UTF-8') . "</td>
                                                <td>" . htmlspecialchars($data['keterangan'], ENT_QUOTES, 'UTF-8') . "</td>
                                                <td>" . htmlspecialchars(number_format($data['biaya'], 2, ',', '.'), ENT_QUOTES, 'UTF-8') . "</td>
                                            </tr>";
                                        }
                                        echo "<tr class='table-footer'>
                                                <td colspan='5' class='text-right'>Total:</td>
                                                <td><strong>" . htmlspecialchars(number_format($total_biaya, 2, ',', '.'), ENT_QUOTES, 'UTF-8') . "</strong></td>
                                              </tr>";
                                    } else {
                                        echo "<tr><td colspan='6' class='text-center'>Tidak ada data pemeliharaan kendaraan.</td></tr>";
                                    }
                                    ?>
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
</body>
</html>