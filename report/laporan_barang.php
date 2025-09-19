<?php
include '../koneksi.php';

// Array untuk nama bulan dalam bahasa Indonesia
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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Stok Barang</title>
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
        }

        .footer {
            text-align: right;
            margin-top: 50px;
        }

        .footer .signature {
            margin-top: 80px;
        }

        .no-data {
            text-align: center;
            margin-top: 20px;
            color: #555;
        }

        @media print {
            body {
                margin: 0;
            }

            .header img {
                width: 80px;
                height: 80px;
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
    <h3 style="text-align: center;">LAPORAN STOK BARANG</h3>
    <p style="text-align: center;">Tanggal: <?php echo date('d ') . $bulan_indonesia[date('n')] . date(' Y'); ?></p>

    <?php
    $query = "SELECT * FROM barang ORDER BY id_barang DESC";
    $result = mysqli_query($conn, $query);
    $total_stok = 0;
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
                    <th>Spesifikasi</th>
                    <th>Stok Awal</th>
                    <th>Stok Dipinjam</th>
                    <th>Stok Tersedia</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                while ($data = mysqli_fetch_array($result)) :
                    $gambar = $data['gambar'] ? $data['gambar'] : 'default.jpg';
                    $qrcode = $data['qrcode'] ? $data['qrcode'] : 'default_qr.png';
                    $total_stok += (int)$data['stok'];
                ?>
                    <tr>
                        <td><?= htmlspecialchars($no++, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($data['kode'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><img src="../Uploads/<?= htmlspecialchars($gambar, ENT_QUOTES, 'UTF-8'); ?>" alt="Gambar Barang" style="width: 50px; height: 50px;"></td>
                        <td><img src="../<?= htmlspecialchars($qrcode, ENT_QUOTES, 'UTF-8'); ?>" alt="QR Code" style="width: 50px; height: 50px;"></td>
                        <td><?= htmlspecialchars($data['nama_barang'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="text-align: left;"><?= nl2br(htmlspecialchars($data['spesifikasi'], ENT_QUOTES, 'UTF-8')); ?></td>
                        <td><?= htmlspecialchars($data['stok_awal'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($data['stok_dipinjam'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($data['stok'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endwhile; ?>
                <tr>
                    <td colspan="8" style="text-align: right; font-weight: bold;">Total Stok Tersedia:</td>
                    <td><?= htmlspecialchars(number_format($total_stok, 0, ',', '.'), ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            </tbody>
        </table>
    <?php else : ?>
        <p class="no-data">Tidak ada data barang.</p>
    <?php endif; ?>

    <div class="footer">
        <p>Banjarmasin, <?php echo date('d ') . $bulan_indonesia[date('n')] . date(' Y'); ?></p>
        <p>Kepala Dinas Perdagangan</p>
        <div class="signature">
            <p><strong>Sulkan, SH, MM</strong></p>
            <p style="margin-top: -12px; margin-right: 0px;"><strong>NIP.19650801 199503 1 002</strong></p>
        </div>
    </div>
</body>
</html>