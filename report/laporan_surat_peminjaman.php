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
$judul_laporan = 'Surat Peminjaman Barang/Kendaraan';
if ($filter_status == 'dipinjam') {
    $judul_laporan = 'Surat Peminjaman Barang/Kendaraan Dipinjam';
} elseif ($filter_status == 'dikembalikan') {
    $judul_laporan = 'Surat Peminjaman Barang/Kendaraan Dikembalikan';
}

// Query dengan filter status
$query = "SELECT p.*, b.nama_barang, k.nama_kendaraan, u.nama AS dipinjam_oleh 
          FROM peminjaman p 
          LEFT JOIN barang b ON p.id_barang = b.id_barang AND p.jenis = 'barang'
          LEFT JOIN inventaris_kendaraan k ON p.id_barang = k.id_inventaris_kendaraan AND p.jenis = 'kendaraan'
          JOIN user u ON p.id_user = u.id_user";
if ($filter_status != 'all') {
    $query .= " WHERE p.status = '" . mysqli_real_escape_string($conn, $filter_status) . "'";
}
$result = mysqli_query($conn, $query);
$total_jumlah = 0;
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

        .content {
            margin-top: 20px;
        }

        .content p {
            margin: 5px 0;
        }

        .peminjaman-list {
            margin-top: 20px;
        }

        .peminjaman-list ul {
            list-style-type: none;
            padding-left: 0;
        }

        .peminjaman-list li {
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
        <h3 style="text-align: center;"><?php echo htmlspecialchars($judul_laporan, ENT_QUOTES, 'UTF-8'); ?></h3>
        <p style="text-align: center;">Nomor: 124/Disdag/Kalsel/<?= date('Y') ?></p>

        <p>Kepada Yth,</p>
        <p>Kepala Bagian Umum Dinas Perdagangan Provinsi Kalimantan Selatan</p>
        <p>Di Banjarmasin</p>

        <p>Dengan hormat,</p>
        <p>Berdasarkan hasil pencatatan, kami sampaikan bahwa telah terjadi peminjaman barang/kendaraan sebagai berikut:</p>

        <div class="peminjaman-list">
            <ul>
                <?php
                $no = 1;
                $row_count = mysqli_num_rows($result);
                if ($row_count > 0) :
                    while ($data = mysqli_fetch_array($result)) :
                        $nama = $data['jenis'] === 'barang' ? $data['nama_barang'] : $data['nama_kendaraan'];
                        $total_jumlah += (int)$data['jumlah'];
                ?>
                        <li>
                            <strong>No. <?= $no++ ?></strong><br>
                            - ID Item: <?= htmlspecialchars($data['id_barang'], ENT_QUOTES, 'UTF-8') ?><br>
                            - Nama Item: <?= htmlspecialchars($nama ?: '-', ENT_QUOTES, 'UTF-8') ?><br>
                            - Dipinjam Oleh: <?= htmlspecialchars($data['dipinjam_oleh'], ENT_QUOTES, 'UTF-8') ?><br>
                            - Jumlah: <?= htmlspecialchars(number_format($data['jumlah'], 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?><br>
                            - Jenis: <?= htmlspecialchars(ucfirst($data['jenis']), ENT_QUOTES, 'UTF-8') ?><br>
                            - Tanggal Pinjam: <?= htmlspecialchars(date('d-m-Y', strtotime($data['tanggal_pinjam'])), ENT_QUOTES, 'UTF-8') ?><br>
                            - Tanggal Kembali: <?= $data['tanggal_kembali'] == null ? 'Belum dikembalikan' : htmlspecialchars(date('d-m-Y', strtotime($data['tanggal_kembali'])), ENT_QUOTES, 'UTF-8') ?><br>
                            - Keterangan: <?= htmlspecialchars($data['keterangan'] ?: '-', ENT_QUOTES, 'UTF-8') ?><br>
                            - Status: <?= htmlspecialchars($data['status'] == 'dipinjam' ? 'Dipinjam' : 'Dikembalikan', ENT_QUOTES, 'UTF-8') ?>
                        </li>
                    <?php
                    endwhile;
                else :
                    ?>
                    <li>Tidak ada data peminjaman untuk status yang dipilih.</li>
                <?php endif; ?>
            </ul>
            <?php if ($row_count > 0) : ?>
                <p style="text-align: right; font-weight: bold;">Total Jumlah: <?= htmlspecialchars(number_format($total_jumlah, 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>

        <p>Demikian surat peminjaman ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>

        <div class="signature">
            <p>Banjarmasin, <?= date('d ') . $bulan_indonesia[date('n')] . date(' Y'); ?></p>
            <p>Kepala Dinas Perdagangan</p>
            <br><br>
            <<p><strong>Sulkan, SH, MM</strong></p>
                <p style="margin-top: -12px; margin-right: 0px;"><strong>NIP.19650801 199503 1 002</strong></p>
        </div>
    </div>

    <script>
        window.print();
    </script>
</body>

</html>