<script>
    window.print();
</script>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Pengadaan Barang</title>
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
    <h3 style="text-align: center;">LAPORAN PENGADAAN BARANG</h3>

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

    // Ambil filter dari parameter GET
    $filter_ruangan = isset($_GET['filter_ruangan']) ? htmlspecialchars($_GET['filter_ruangan']) : 'all';
    $filter_bulan_dari = isset($_GET['filter_bulan_dari']) ? htmlspecialchars($_GET['filter_bulan_dari']) : 'all';
    $filter_bulan_sampai = isset($_GET['filter_bulan_sampai']) ? htmlspecialchars($_GET['filter_bulan_sampai']) : 'all';
    $filter_tahun = isset($_GET['filter_tahun']) ? htmlspecialchars($_GET['filter_tahun']) : 'all';

    // Tampilkan info filter
    $filter_text = "Semua Data";
    if ($filter_ruangan != 'all' || $filter_bulan_dari != 'all' || $filter_bulan_sampai != 'all' || $filter_tahun != 'all') {
        $filter_text = "";
        $parts = [];
        if ($filter_ruangan != 'all') {
            $ruangan = mysqli_fetch_array(mysqli_query($conn, "SELECT nama_ruangan FROM ruangan WHERE id_ruangan = '" . mysqli_real_escape_string($conn, $filter_ruangan) . "'"));
            $parts[] = "Ruangan " . htmlspecialchars($ruangan['nama_ruangan'], ENT_QUOTES, 'UTF-8');
        }
        $bulan_nama = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
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
    $query = mysqli_query($conn, $query);
    ?>

    <table>
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
            $no = 1;
            while ($data = mysqli_fetch_array($query)) : ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($data['kode'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars($data['nama_barang'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars($data['nama_supplier'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars($data['nama_ruangan'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars($data['jumlah'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= date('d-m-Y', strtotime($data['tanggal_masuk'])); ?></td>
                </tr>
            <?php endwhile; ?>
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