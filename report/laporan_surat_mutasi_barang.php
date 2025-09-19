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
// Query untuk mengambil semua data mutasi tanpa filter
$query = "SELECT m.*, b.nama_barang, r1.nama_ruangan AS ruangan_asal, r2.nama_ruangan AS ruangan_tujuan, u.nama AS user_nama 
          FROM mutasi_barang m 
          JOIN barang b ON m.id_barang = b.id_barang 
          JOIN ruangan r1 ON m.id_ruangan = r1.id_ruangan 
          JOIN ruangan r2 ON m.id_ruangan1 = r2.id_ruangan 
          JOIN user u ON m.id_user = u.id_user 
          ORDER BY m.id_mutasi DESC";
$result = mysqli_query($conn, $query);
$total_jumlah = 0;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Surat Mutasi Barang</title>
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

        .content {
            margin-top: 20px;
        }

        .content p {
            margin: 5px 0;
        }

        .mutasi-list {
            margin-top: 20px;
        }

        .mutasi-list ul {
            list-style-type: none;
            padding-left: 0;
        }

        .mutasi-list li {
            margin-bottom: 10px;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
        }

        .signature {
            margin-top: 50px;
            text-align: right;
        }

        .signature p {
            margin: 10px 0;
        }

        @media print {
            .print-button {
                display: none;
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

    <div class="content">
        <h3 style="text-align: center;">Surat Mutasi Barang</h3>
        <p style="text-align: center;">Nomor: 123/Disdag/Kalsel/<?= date('Y') ?></p>

        <p>Kepada Yth,</p>
        <p>Kepala Bagian Umum Dinas Perdagangan Provinsi Kalimantan Selatan</p>
        <p>Di Banjarmasin</p>

        <p>Dengan hormat,</p>
        <p>Berdasarkan hasil inventarisasi, kami sampaikan bahwa telah terjadi mutasi barang sebagai berikut:</p>

        <div class="mutasi-list">
            <ul>
                <?php
                $no = 1;
                $row_count = mysqli_num_rows($result);
                if ($row_count > 0) :
                    while ($data = mysqli_fetch_array($result)) :
                        $total_jumlah += (int)$data['jumlah'];
                ?>
                        <li>
                            <strong>No. <?= $no++ ?></strong><br>
                            - Nama Barang: <?= htmlspecialchars($data['nama_barang'], ENT_QUOTES, 'UTF-8') ?><br>
                            - Ruangan Asal: <?= htmlspecialchars($data['ruangan_asal'], ENT_QUOTES, 'UTF-8') ?><br>
                            - Ruangan Tujuan: <?= htmlspecialchars($data['ruangan_tujuan'], ENT_QUOTES, 'UTF-8') ?><br>
                            - Jumlah: <?= htmlspecialchars(number_format($data['jumlah'], 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?><br>
                            - Status: <?= htmlspecialchars($data['status'] == 'mutasi' ? 'Mutasi Permanen' : 'Mutasi Sementara', ENT_QUOTES, 'UTF-8') ?><br>
                            - Dimutasi Oleh: <?= htmlspecialchars($data['user_nama'], ENT_QUOTES, 'UTF-8') ?><br>
                            - Tanggal Mutasi: <?= htmlspecialchars(date('d-m-Y', strtotime($data['created_at'] ?? date('Y-m-d'))), ENT_QUOTES, 'UTF-8') ?>
                        </li>
                    <?php
                    endwhile;
                else :
                    ?>
                    <li>Tidak ada data mutasi barang.</li>
                <?php endif; ?>
            </ul>
            <?php if ($row_count > 0) : ?>
                <p style="text-align: right; font-weight: bold;">Total Jumlah: <?= htmlspecialchars(number_format($total_jumlah, 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>

        <p>Demikian surat mutasi ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>

        <div class="signature">
            <p>Banjarmasin, <?= date('d ') . $bulan_indonesia[date('n')] . date(' Y'); ?></p>
            <p>Kepala Dinas Perdagangan</p>
            <br><br>
            <p><strong>Sulkan, SH, MM</strong></p>
            <p style="margin-top: -12px; margin-right: 0px;"><strong>NIP.19650801 199503 1 002</strong></p>
        </div>
    </div>

    <script>
        window.print();
    </script>
</body>

</html>