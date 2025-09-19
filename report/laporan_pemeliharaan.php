<script>
    window.print();
</script>
<?php
include_once "../koneksi.php";

$bulan_indonesia = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

// Get the selected filters, if any
$selected_room = isset($_GET['filter_ruangan']) ? htmlspecialchars($_GET['filter_ruangan']) : '';
$filter_bulan_dari = isset($_GET['filter_bulan_dari']) ? htmlspecialchars($_GET['filter_bulan_dari']) : 'all';
$filter_bulan_sampai = isset($_GET['filter_bulan_sampai']) ? htmlspecialchars($_GET['filter_bulan_sampai']) : 'all';
$filter_tahun = isset($_GET['filter_tahun']) ? htmlspecialchars($_GET['filter_tahun']) : 'all';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Pemeliharaan Barang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            float: left;
            width: 100px;
            height: 100px;
            margin-right: 20px;
        }

        .header h1,
        .header h2,
        .header p {
            margin: 0;
        }

        .line {
            border: 2px double black;
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
            font-size: 0.9rem;
        }

        td {
            font-size: 0.85rem;
        }

        .table-footer {
            font-weight: bold;
            background-color: #f8f9fa;
        }

        .filter-info {
            text-align: center;
            margin: 10px 0;
            font-style: italic;
            font-size: 0.9rem;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
        }

        .footer {
            text-align: right;
            margin-top: 30px;
            font-size: 0.85rem;
        }

        /* Print-specific styles */
        @media print {
            body {
                margin: 0;
            }

            .filter-info {
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="../img/logo disdag.png" alt="Logo Dinas Perdagangan">
        <h1>Dinas Perdagangan</h1>
        <h2>Provinsi Kalimantan Selatan</h2>
        <p>Jl. S. Parman No. 44, Antasan Besar, Kec. Banjarmasin Tengah, Kota Banjarmasin, Kalimantan Selatan, 70114</p>
        <small>Telepon: (0511) 3354219 | Email: dinasperdagangan.kalsel@gmail.com</small>
    </div>
    <hr class="line">
    <h3 style="text-align: center;">LAPORAN PEMELIHARAAN BARANG</h3>

    <?php
    // Tampilkan info filter
    $filter_text = "Semua Data";
    if (!empty($selected_room) || $filter_bulan_dari != 'all' || $filter_bulan_sampai != 'all' || $filter_tahun != 'all') {
        $filter_text = "";
        $parts = [];
        if (!empty($selected_room)) {
            $parts[] = "Ruangan " . htmlspecialchars($selected_room, ENT_QUOTES, 'UTF-8');
        }
        $bulan_nama = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];
        if ($filter_bulan_dari != 'all' && $filter_bulan_sampai != 'all') {
            $parts[] = "Bulan " . $bulan_nama[$filter_bulan_dari] . " sampai " . $bulan_nama[$filter_bulan_sampai];
        } elseif ($filter_bulan_dari != 'all') {
            $parts[] = "Dari Bulan " . $bulan_nama[$filter_bulan_dari];
        } elseif ($filter_bulan_sampai != 'all') {
            $parts[] = "Sampai Bulan " . $bulan_nama[$filter_bulan_sampai];
        }
        if ($filter_tahun != 'all') {
            $parts[] = "Tahun " . htmlspecialchars($filter_tahun, ENT_QUOTES, 'UTF-8');
        }
        $filter_text .= implode(", ", $parts);
    }
    echo '<div class="filter-info">' . $filter_text . '</div>';
    ?>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Lokasi Ruangan</th>
                <th>Keterangan</th>
                <th>Tanggal</th>
                <th>Biaya</th>
                <th>Total Biaya</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Adjust the SQL query based on the selected filters
            $query = "SELECT pb.*, b.nama_barang, r.nama_ruangan, r.id_ruangan 
                      FROM pemeliharaan_barang pb 
                      JOIN barang b ON pb.id_barang = b.id_barang 
                      JOIN ruangan r ON pb.id_ruangan = r.id_ruangan";
            $conditions = [];
            if (!empty($selected_room)) {
                $conditions[] = "r.nama_ruangan = '" . mysqli_real_escape_string($conn, $selected_room) . "'";
            }
            if ($filter_bulan_dari != 'all' && $filter_bulan_sampai != 'all') {
                $conditions[] = "MONTH(pb.tanggal) BETWEEN '" . mysqli_real_escape_string($conn, $filter_bulan_dari) . "' AND '" . mysqli_real_escape_string($conn, $filter_bulan_sampai) . "'";
            } elseif ($filter_bulan_dari != 'all') {
                $conditions[] = "MONTH(pb.tanggal) >= '" . mysqli_real_escape_string($conn, $filter_bulan_dari) . "'";
            } elseif ($filter_bulan_sampai != 'all') {
                $conditions[] = "MONTH(pb.tanggal) <= '" . mysqli_real_escape_string($conn, $filter_bulan_sampai) . "'";
            }
            if ($filter_tahun != 'all') {
                $conditions[] = "YEAR(pb.tanggal) = '" . mysqli_real_escape_string($conn, $filter_tahun) . "'";
            }
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }
            $ambilsemuadatanya = mysqli_query($conn, $query);
            $i = 1;
            $totalBiaya = 0;
            while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                $id_pemeliharaan = $data['id_pemeliharaan'];
                $id_ruangan = $data['id_ruangan'];
                $nama_barang = $data['nama_barang'];
                $keterangan = $data['keterangan'];
                $tanggal = $data['tanggal'];
                $nama_ruangan = $data['nama_ruangan'];
                $biaya = floatval($data['biaya']);
                $jumlah = $data['jumlah'];
                $totalBiayaPerBaris = $biaya * $jumlah;
                $totalBiaya += $totalBiayaPerBaris;
            ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($nama_barang) ?></td>
                    <td><?= htmlspecialchars($jumlah) ?></td>
                    <td><?= htmlspecialchars($nama_ruangan) ?></td>
                    <td><?= htmlspecialchars($keterangan) ?></td>
                    <td><?= htmlspecialchars($tanggal) ?></td>
                    <td><?= number_format($biaya, 2, ',', '.') ?></td>
                    <td><?= number_format($totalBiayaPerBaris, 2, ',', '.') ?></td>
                </tr>
            <?php } ?>
            <tr class="table-footer">
                <td colspan="6"></td>
                <td>Total Biaya Keseluruhan: </td>
                <td><strong><?= number_format($totalBiaya, 2, ',', '.') ?></strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Banjarmasin, <?= date('d ') . $bulan_indonesia[date('n')] . date(' Y'); ?></p>
        <p>Kepala Dinas Perdagangan</p>
        <br><br>
        <p><strong>Sulkan, SH, MM</strong></p>
        <p style="margin-top: -12px; margin-right: 0px;"><strong>NIP.19650801 199503 1 002</strong></p>
    </div>
</body>

</html>