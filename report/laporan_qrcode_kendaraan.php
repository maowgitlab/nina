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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan QR Code Kendaraan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            float: left;
            width: 100px;
            height: 100px;
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
        }

        .qrcode-img {
            width: 100px;
            height: 100px;
        }

        .footer {
            text-align: right;
            margin-top: 30px;
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
    <h3 style="text-align: center;">LAPORAN QR CODE KENDARAAN</h3>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Kendaraan</th>
                <th>QR Code</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT id_inventaris_kendaraan, nama_kendaraan, qrcode FROM inventaris_kendaraan ORDER BY id_inventaris_kendaraan DESC";
            $result = mysqli_query($conn, $query);
            $no = 1;
            while ($data = mysqli_fetch_array($result)) :
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($data['nama_kendaraan'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><img src="../<?= htmlspecialchars($data['qrcode'], ENT_QUOTES, 'UTF-8') ?>" alt="QR Code" class="qrcode-img"></td>
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

    <script>
        window.print();
    </script>
</body>

</html>