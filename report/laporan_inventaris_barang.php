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
    <title>Laporan Inventaris Barang</title>
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
    <h3 style="text-align: center;">LAPORAN INVENTARIS BARANG</h3>

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

    // Adjust the SQL query based on filters
    $query = "SELECT ib.id_inventaris_barang, ib.status, b.id_barang, b.kode, b.nama_barang, b.gambar, b.qrcode, r.id_ruangan, r.nama_ruangan, ib.merk, ib.jumlah, ib.tanggal 
              FROM inventaris_barang ib
              JOIN barang b ON ib.id_barang = b.id_barang
              JOIN ruangan r ON ib.id_ruangan = r.id_ruangan";
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
    $total_jumlah = 0;
    $row_count = mysqli_num_rows($result);
    ?>

    <?php if ($row_count > 0) : ?>
        <table>
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
                <?php $no = 1;
                while ($data = mysqli_fetch_assoc($result)) :
                    $total_jumlah += (int)$data['jumlah'];
                    $gambar = $data['gambar'] ? $data['gambar'] : 'default.jpg';
                    $qrcode = $data['qrcode'] ? $data['qrcode'] : 'default_qr.png';
                ?>
                    <tr>
                        <td><?= htmlspecialchars($no++, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($data['kode'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><img src="../Uploads/<?= htmlspecialchars($gambar, ENT_QUOTES, 'UTF-8'); ?>" alt="Gambar Barang" style="width: 50px; height: 50px;"></td>
                        <td><img src="../<?= htmlspecialchars($qrcode, ENT_QUOTES, 'UTF-8'); ?>" alt="QR Code" style="width: 50px; height: 50px;"></td>
                        <td><?= htmlspecialchars($data['nama_barang'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($data['nama_ruangan'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($data['merk'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($data['jumlah'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($data['tanggal'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endwhile; ?>
                <tr>
                    <td colspan="7" style="text-align: right; font-weight: bold;">Total:</td>
                    <td><?= htmlspecialchars(number_format($total_jumlah, 0, ',', '.'), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    <?php else : ?>
        <p style="text-align: center; color: #555;">Tidak ada data inventaris barang.</p>
    <?php endif; ?>

    <div class="footer">
        <p>Banjarmasin, <?= date('d ') . $bulan_indonesia[date('n')] . date(' Y'); ?></p>
        <p>Kepala Dinas Perdagangan</p>
        <br><br>
        p><strong>Sulkan, SH, MM</strong></p>
        <p style="margin-top: -12px; margin-right: 0px;"><strong>NIP.19650801 199503 1 002</strong></p>
    </div>
</body>

</html>