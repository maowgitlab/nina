<?php
include_once "koneksi.php";

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

if (!isset($_GET['id'])) {
    echo "ID mutasi tidak valid!";
    exit;
}

$id_mutasi = mysqli_real_escape_string($conn, $_GET['id']);
$query = "SELECT m.*, b.kode, b.nama_barang, b.spesifikasi, r1.nama_ruangan AS ruangan_asal, r2.nama_ruangan AS ruangan_tujuan,
                 bam.tanggal_berita, bam.unit_asal, bam.unit_tujuan, bam.penanggung_jawab_asal, bam.penanggung_jawab_tujuan,
                 bam.rincian_barang, bam.catatan,
                 u.nama AS user_pencatat
          FROM mutasi_barang m
          JOIN barang b ON m.id_barang = b.id_barang
          JOIN ruangan r1 ON m.id_ruangan = r1.id_ruangan
          JOIN ruangan r2 ON m.id_ruangan1 = r2.id_ruangan
          JOIN user u ON m.id_user = u.id_user
          LEFT JOIN berita_acara_mutasi bam ON m.id_mutasi = bam.id_mutasi
          WHERE m.id_mutasi = ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_mutasi);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    echo "Data mutasi tidak ditemukan!";
    exit;
}

$tanggalBerita = $data['tanggal_berita'] ?? date('Y-m-d');
$timestampBerita = strtotime($tanggalBerita);

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Serah Terima Barang</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.6;
            margin: 20px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            width: 80px;
            height: 80px;
            float: left;
            margin-right: 20px;
        }

        .header h1 {
            font-size: 15pt;
            margin: 0;
        }

        .header h2 {
            font-size: 13pt;
            margin: 0;
        }

        .header p {
            font-size: 10pt;
            margin: 2px 0;
        }

        .line {
            border: 2px double #000;
            margin: 15px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10pt;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f3f3f3;
        }

        .signature {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }

        .signature div {
            width: 45%;
            text-align: center;
        }

        .signature .line-sign {
            margin: 60px auto 0;
            border-top: 1px solid #000;
            width: 200px;
        }

        @media print {
            body {
                margin: 0;
            }

            .header img {
                width: 70px;
                height: 70px;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="header">
        <img src="img/logo disdag.png" alt="Logo Dinas Perdagangan">
        <h1>Dinas Perdagangan</h1>
        <h2>Provinsi Kalimantan Selatan</h2>
        <p>Jl. S. Parman No. 44, Antasan Besar, Kec. Banjarmasin Tengah, Kota Banjarmasin, Kalimantan Selatan, 70114</p>
        <small>Telepon: (0511) 3354219 | Email: dinasperdagangan.kalsel@gmail.com</small>
    </div>
    <hr class="line">

    <h3 style="text-align:center; text-transform: uppercase;">Berita Acara Serah Terima Barang</h3>
    <p style="text-align:center;">Nomor: <?= htmlspecialchars(date('Y/m', $timestampBerita) . '/BAST/' . $id_mutasi, ENT_QUOTES, 'UTF-8'); ?></p>
    <p>Pada hari ini, <?= htmlspecialchars($bulan_indonesia[date('n', $timestampBerita)] . ' tanggal ' . date('d', $timestampBerita) . ' tahun ' . date('Y', $timestampBerita), ENT_QUOTES, 'UTF-8'); ?>, telah dilakukan serah terima barang dengan rincian sebagai berikut:</p>

    <table>
        <tbody>
            <tr>
                <th style="width: 25%;">Tanggal Berita Acara</th>
                <td><?= htmlspecialchars(date('d ', $timestampBerita) . $bulan_indonesia[date('n', $timestampBerita)] . date(' Y', $timestampBerita), ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
            <tr>
                <th>Unit Penyerah</th>
                <td>
                    <?= htmlspecialchars($data['unit_asal'] ?? $data['ruangan_asal'], ENT_QUOTES, 'UTF-8'); ?><br>
                    <strong>Penanggung Jawab:</strong> <?= htmlspecialchars($data['penanggung_jawab_asal'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                </td>
            </tr>
            <tr>
                <th>Unit Penerima</th>
                <td>
                    <?= htmlspecialchars($data['unit_tujuan'] ?? $data['ruangan_tujuan'], ENT_QUOTES, 'UTF-8'); ?><br>
                    <strong>Penanggung Jawab:</strong> <?= htmlspecialchars($data['penanggung_jawab_tujuan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                </td>
            </tr>
            <tr>
                <th>Barang yang Diserahkan</th>
                <td>
                    <strong><?= htmlspecialchars($data['nama_barang'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                    <em><?= htmlspecialchars($data['spesifikasi'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></em><br>
                    Jumlah: <?= htmlspecialchars(number_format($data['jumlah'], 0, ',', '.'), ENT_QUOTES, 'UTF-8'); ?> unit
                </td>
            </tr>
            <tr>
                <th>Rincian Tambahan</th>
                <td><?= nl2br(htmlspecialchars($data['rincian_barang'] ?? '-', ENT_QUOTES, 'UTF-8')); ?></td>
            </tr>
            <?php if (!empty($data['catatan'])) : ?>
                <tr>
                    <th>Catatan</th>
                    <td><?= nl2br(htmlspecialchars($data['catatan'], ENT_QUOTES, 'UTF-8')); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p>Dengan ini kedua belah pihak menyatakan bahwa barang telah diterima dalam kondisi baik dan selanjutnya menjadi tanggung jawab pihak penerima.</p>

    <div class="signature">
        <div>
            <p>Penyerah Barang</p>
            <div class="line-sign"></div>
            <p><strong><?= htmlspecialchars($data['penanggung_jawab_asal'] ?? '........................', ENT_QUOTES, 'UTF-8'); ?></strong></p>
        </div>
        <div>
            <p>Penerima Barang</p>
            <div class="line-sign"></div>
            <p><strong><?= htmlspecialchars($data['penanggung_jawab_tujuan'] ?? '........................', ENT_QUOTES, 'UTF-8'); ?></strong></p>
        </div>
    </div>

    <p style="margin-top:40px;">Disusun oleh: <?= htmlspecialchars($data['user_pencatat'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></p>
</body>

</html>
