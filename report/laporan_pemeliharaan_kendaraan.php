<?php
include '../koneksi.php';

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
    <title>Laporan Pemeliharaan Kendaraan</title>
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
            font-size: 0.85rem;
        }

        th {
            background-color: #f2f2f2;
            font-size: 0.9rem;
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

        .no-data {
            text-align: center;
            margin-top: 20px;
            color: #555;
            font-size: 0.9rem;
        }

        .footer {
            text-align: right;
            margin-top: 30px;
            font-size: 0.85rem;
        }

        .footer .signature {
            margin-top: 50px;
        }

        @media print {
            body {
                margin: 0;
            }

            .header img {
                width: 80px;
                height: 80px;
            }

            .filter-info {
                box-shadow: none;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="header">
        <img src="../img/logo disdag.png" alt="Logo Dinas Perdagangan">
        <h1>Dinas Perdagangan</h1>
        <h2>Provinsi Kalimantan Selatan</h2>
        <p>Jl. S. Parman No. 44, Antasan Besar, Kec. Banjarmasin Tengah, Kota Banjarmasin, Kalimantan Selatan, 70114</p>
        <small>Telepon: (0511) 3354219 | Email: dinasperdagangan.kalsel@gmail.com</small>
    </div>
    <hr class="line">

    <h3 style="text-align: center;">Laporan Pemeliharaan Kendaraan</h3>

    <?php
    // Tampilkan info filter
    $filter_text = "Semua Data";
    if ($filter_bulan_dari != 'all' || $filter_bulan_sampai != 'all' || $filter_tahun != 'all') {
        $filter_text = "";
        $parts = [];
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

    // Adjust the SQL query based on filters
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
    $total_biaya = 0;
    $row_count = mysqli_num_rows($result);
    ?>

    <?php if ($row_count > 0) : ?>
        <table>
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
                $no = 1;
                while ($data = mysqli_fetch_array($result)) :
                    $total_biaya += (float)$data['biaya'];
                ?>
                    <tr>
                        <td><?= htmlspecialchars($no++, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($data['nama_pegawai'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($data['nomor_polisi'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars(date('d-m-Y', strtotime($data['tanggal'])), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($data['keterangan'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars(number_format($data['biaya'], 2, ',', '.'), ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endwhile; ?>
                <tr class="table-footer">
                    <td colspan="5" style="text-align: right; font-weight: bold;">Total:</td>
                    <td><?= htmlspecialchars(number_format($total_biaya, 2, ',', '.'), ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            </tbody>
        </table>
    <?php else : ?>
        <p class="no-data">Tidak ada data pemeliharaan kendaraan.</p>
    <?php endif; ?>

    <div class="footer">
        <p>Banjarmasin, <?= date('d ') . $bulan_indonesia[date('n')] . date(' Y'); ?></p>
        <p>Kepala Dinas Perdagangan</p>
        <br><br>
        <p><strong>Sulkan, SH, MM</strong></p>
        <p style="margin-top: -12px; margin-right: 0px;"><strong>NIP.19650801 199503 1 002</strong></p>
    </div>

</body>

</html>