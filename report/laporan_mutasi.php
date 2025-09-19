<script>window.print();</script>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Mutasi Barang</title>
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

        .print-button {
            text-align: center;
            margin-bottom: 20px;
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
    $filter_status = isset($_GET['filter_status']) ? htmlspecialchars($_GET['filter_status']) : 'mutasi';
    $valid_status = ['all', 'mutasi', 'temporary'];
    if (!in_array($filter_status, $valid_status)) {
        $filter_status = 'mutasi';
    }

    // Tentukan judul berdasarkan filter
    $judul_laporan = 'Laporan Mutasi Barang';
    if ($filter_status == 'mutasi') {
        $judul_laporan = 'Laporan Mutasi Barang Permanen';
    } elseif ($filter_status == 'temporary') {
        $judul_laporan = 'Laporan Mutasi Barang Sementara';
    }

    $query = "SELECT m.*, b.kode, b.nama_barang, r1.nama_ruangan AS ruangan_asal, r2.nama_ruangan AS ruangan_tujuan, u.nama AS user_nama,
            bam.tanggal_berita, bam.penanggung_jawab_asal, bam.penanggung_jawab_tujuan
            FROM mutasi_barang m
            JOIN barang b ON m.id_barang = b.id_barang
            JOIN ruangan r1 ON m.id_ruangan = r1.id_ruangan
            JOIN ruangan r2 ON m.id_ruangan1 = r2.id_ruangan
            JOIN user u ON m.id_user = u.id_user
            LEFT JOIN berita_acara_mutasi bam ON m.id_mutasi = bam.id_mutasi";

    if ($filter_status != 'all') {
        $query .= " WHERE m.status = '" . mysqli_real_escape_string($conn, $filter_status) . "'";
    }

    $query .= " ORDER BY m.id_mutasi DESC";

    $result = mysqli_query($conn, $query);
    $total_jumlah = 0;
    $row_count = mysqli_num_rows($result);
    ?>

    <h3 style="text-align: center;"><?php echo htmlspecialchars($judul_laporan, ENT_QUOTES, 'UTF-8'); ?></h3>
    <p style="text-align: center;">Tanggal: <?php echo date('d ') . $bulan_indonesia[date('n')] . date(' Y'); ?></p>

    <?php if ($row_count > 0) : ?>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Ruangan Asal</th>
                    <th>Ruangan Tujuan</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Dimutasi Oleh</th>
                    <th>Tanggal Berita</th>
                    <th>Penanggung Jawab Asal</th>
                    <th>Penanggung Jawab Tujuan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                while ($data = mysqli_fetch_array($result)) :
                    $total_jumlah += (int)$data['jumlah'];
                ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($data['kode'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($data['nama_barang'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($data['ruangan_asal'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($data['ruangan_tujuan'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars(number_format($data['jumlah'], 0, ',', '.'), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($data['status'] == 'mutasi' ? 'Mutasi Permanen' : 'Mutasi Sementara', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($data['user_nama'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo !empty($data['tanggal_berita']) ? htmlspecialchars(date('d/m/Y', strtotime($data['tanggal_berita'])), ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                        <td><?php echo htmlspecialchars($data['penanggung_jawab_asal'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($data['penanggung_jawab_tujuan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endwhile; ?>
                <tr>
                    <td colspan="5" style="text-align: right; font-weight: bold;">Total:</td>
                    <td><?php echo htmlspecialchars(number_format($total_jumlah, 0, ',', '.'), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td colspan="5"></td>
                </tr>
            </tbody>
        </table>
    <?php else : ?>
        <p class="no-data">Tidak ada data mutasi barang untuk status yang dipilih.</p>
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