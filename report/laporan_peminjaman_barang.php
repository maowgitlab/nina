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

// Ambil filter status dari parameter GET
$filter_status = isset($_GET['filter_status']) ? htmlspecialchars($_GET['filter_status']) : 'dipinjam';
$valid_status = ['all', 'dipinjam', 'dikembalikan'];
if (!in_array($filter_status, $valid_status)) {
    $filter_status = 'dipinjam';
}

// Tentukan judul berdasarkan filter
$judul_laporan = 'Laporan Peminjaman Barang/Kendaraan';
if ($filter_status == 'dipinjam') {
    $judul_laporan = 'Laporan Peminjaman Barang/Kendaraan Dipinjam';
} elseif ($filter_status == 'dikembalikan') {
    $judul_laporan = 'Laporan Peminjaman Barang/Kendaraan Dikembalikan';
}

// Query dengan filter status
$query = "SELECT p.*, b.nama_barang, b.gambar AS gambar_barang, b.qrcode AS qrcode_barang, k.nama_kendaraan AS nama_kendaraan, k.gambar AS gambar_kendaraan, k.qrcode AS qrcode_kendaraan, u.nama AS dipinjam_oleh 
          FROM peminjaman p 
          LEFT JOIN barang b ON p.id_barang = b.id_barang AND p.jenis = 'barang'
          LEFT JOIN inventaris_kendaraan k ON p.id_barang = k.id_inventaris_kendaraan AND p.jenis = 'kendaraan'
          JOIN user u ON p.id_user = u.id_user";
if ($filter_status != 'all') {
    $query .= " WHERE p.status = '" . mysqli_real_escape_string($conn, $filter_status) . "'";
}
$query .= " ORDER BY p.tanggal_pinjam DESC";
$result = mysqli_query($conn, $query);
$total_jumlah = 0;
$row_count = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo htmlspecialchars($judul_laporan, ENT_QUOTES, 'UTF-8'); ?></title>
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
    <h3 style="text-align: center;"><?php echo htmlspecialchars($judul_laporan, ENT_QUOTES, 'UTF-8'); ?></h3>

    <?php if ($row_count > 0) : ?>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>ID Item</th>
                    <th>Nama Item</th>
                    <th>Dipinjam Oleh</th>
                    <th>Jumlah</th>
                    <th>Jenis</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Keterangan</th>
                    <th>Gambar</th>
                    <th>QR Code</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                while ($data = mysqli_fetch_array($result)) :
                    $nama = $data['jenis'] === 'barang' ? $data['nama_barang'] : $data['nama_kendaraan'];
                    $gambar = $data['jenis'] === 'barang' ? $data['gambar_barang'] : $data['gambar_kendaraan'];
                    $qrcode = $data['jenis'] === 'barang' ? $data['qrcode_barang'] : $data['qrcode_kendaraan'];
                    $total_jumlah += (int)$data['jumlah'];
                ?>
                    <tr>
                        <td><?= htmlspecialchars($no++, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($data['id_barang'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($nama ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($data['dipinjam_oleh'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars(number_format($data['jumlah'], 0, ',', '.'), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars(ucfirst($data['jenis']), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars(date('d-m-Y', strtotime($data['tanggal_pinjam'])), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= $data['tanggal_kembali'] == null ? 'Belum dikembalikan' : htmlspecialchars(date('d-m-Y', strtotime($data['tanggal_kembali'])), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($data['keterangan'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><img src="../uploads/<?= htmlspecialchars($gambar ?: '', ENT_QUOTES, 'UTF-8'); ?>" alt="Gambar" width="50" style="display: <?= $gambar ? 'block' : 'none' ?>;"></td>
                        <td><img src="../<?= htmlspecialchars($qrcode ?: '', ENT_QUOTES, 'UTF-8'); ?>" alt="QR Code" width="50" style="display: <?= $qrcode ? 'block' : 'none' ?>;"></td>
                    </tr>
                <?php endwhile; ?>
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold;">Total:</td>
                    <td><?= htmlspecialchars(number_format($total_jumlah, 0, ',', '.'), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td colspan="6"></td>
                </tr>
            </tbody>
        </table>
    <?php else : ?>
        <p class="no-data">Tidak ada data peminjaman untuk status yang dipilih.</p>
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