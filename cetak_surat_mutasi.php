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

if (isset($_GET['id'])) {
    $id_mutasi = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT m.*, b.nama_barang, b.spesifikasi, r1.nama_ruangan AS ruangan_asal, r2.nama_ruangan AS ruangan_tujuan, u.nama AS user_nama,
                     bam.tanggal_berita, bam.unit_asal AS berita_unit_asal, bam.unit_tujuan AS berita_unit_tujuan,
                     bam.penanggung_jawab_asal, bam.penanggung_jawab_tujuan, bam.rincian_barang, bam.catatan,
                     pg.nama_pegawai AS dipinjam_oleh
              FROM mutasi_barang m
              LEFT JOIN pegawai pg ON m.id_user = pg.id_pegawai
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

    if ($data) {
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
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            margin: 2cm;
            color: #333;
        }

        .page {
            width: 21cm; /* A4 width */
            min-height: 29.7cm; /* A4 height */
            margin: 0 auto;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header img {
            float: left;
            width: 80px;
            height: 80px;
            margin-right: 20px;
            margin-top: 10px;
        }

        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }

        .header h2 {
            font-size: 12pt;
            font-weight: normal;
            margin: 5px 0;
        }

        .header p {
            font-size: 10pt;
            margin: 2px 0;
        }

        .header small {
            font-size: 9pt;
            color: #555;
        }

        .line {
            border: 2px double black;
            margin: 20px 0;
        }

        .letter-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 20px 0;
        }

        .letter-number {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 20px;
        }

        .letter-date {
            text-align: right;
            font-size: 10pt;
            margin-bottom: 30px;
        }

        .letter-body {
            font-size: 10pt;
            margin-bottom: 30px;
            text-align: justify;
        }

        .letter-body p {
            margin: 10px 0;
        }

        .letter-body .field-label {
            display: inline-block;
            width: 150px;
            font-weight: bold;
        }

        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .footer div {
            width: 45%;
            text-align: center;
        }

        .footer p {
            margin: 5px 0;
            font-size: 10pt;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10pt;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        .detail-table th {
            background-color: #f5f5f5;
        }

        .signature {
            margin-top: 60px;
            font-size: 10pt;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 150px;
            margin: 10px auto 5px; /* Center the signature line */
        }

        @media print {
            body {
                margin: 0;
            }
            .page {
                width: 100%;
                box-shadow: none;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="page">
        <div class="header">
            <img src="img/logo disdag.png" alt="Logo Dinas Perdagangan">
            <h1>Dinas Perdagangan</h1>
            <h2>Provinsi Kalimantan Selatan</h2>
            <p>Jl. S. Parman No. 44, Antasan Besar, Kec. Banjarmasin Tengah, Kota Banjarmasin, Kalimantan Selatan, 70114</p>
            <small>Telepon: (0511) 3354219 | Email: dinasperdagangan.kalsel@gmail.com</small>
        </div>
        <hr class="line">

        <div class="letter-title">Surat Mutasi Barang</div>
        <div class="letter-number">Nomor: <?= htmlspecialchars(date('Y/m/d') . '/SM/' . $id_mutasi, ENT_QUOTES, 'UTF-8') ?></div>
        <div class="letter-date">Banjarmasin, <?= date('d ') . $bulan_indonesia[date('n')] . date(' Y'); ?></div>

        <div class="letter-body">
            <p>Kepada Yth.<br>
               Bapak/Ibu <?= htmlspecialchars($data['user_nama'], ENT_QUOTES, 'UTF-8') ?><br>
               di Tempat</p>
            <p>Dengan hormat,</p>
            <p>Bersama ini kami sampaikan bahwa Dinas Perdagangan Provinsi Kalimantan Selatan telah menyetujui mutasi barang sebagai berikut:</p>
            <p>
                <span class="field-label">ID Barang</span>: <?= htmlspecialchars($data['id_barang'], ENT_QUOTES, 'UTF-8') ?><br>
                <span class="field-label">Nama Barang</span>: <?= htmlspecialchars($data['nama_barang'], ENT_QUOTES, 'UTF-8') ?><br>
                <span class="field-label">Spesifikasi</span>: <?= htmlspecialchars($data['spesifikasi'] ?? '-', ENT_QUOTES, 'UTF-8') ?><br>
                <span class="field-label">Ruangan Asal</span>: <?= htmlspecialchars($data['ruangan_asal'], ENT_QUOTES, 'UTF-8') ?><br>
                <span class="field-label">Ruangan Tujuan</span>: <?= htmlspecialchars($data['ruangan_tujuan'], ENT_QUOTES, 'UTF-8') ?><br>
                <span class="field-label">Jumlah</span>: <?= htmlspecialchars(number_format($data['jumlah'], 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?><br>
                <span class="field-label">Status Mutasi</span>: <?= htmlspecialchars($data['status'] == 'mutasi' ? 'Mutasi Permanen' : 'Mutasi Sementara', ENT_QUOTES, 'UTF-8') ?><br>
                <span class="field-label">Dimutasi Oleh</span>: <?= htmlspecialchars($data['user_nama'], ENT_QUOTES, 'UTF-8') ?>
            </p>
            <?php if (!empty($data['tanggal_berita'])) : ?>
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>Uraian</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Tanggal Berita Acara</td>
                            <td><?= htmlspecialchars(date('d F Y', strtotime($data['tanggal_berita'])), ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                        <tr>
                            <td>Unit Penyerah</td>
                            <td>
                                <?= htmlspecialchars($data['berita_unit_asal'] ?? $data['ruangan_asal'], ENT_QUOTES, 'UTF-8'); ?><br>
                                <strong>Penanggung Jawab:</strong> <?= htmlspecialchars($data['penanggung_jawab_asal'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Unit Penerima</td>
                            <td>
                                <?= htmlspecialchars($data['berita_unit_tujuan'] ?? $data['ruangan_tujuan'], ENT_QUOTES, 'UTF-8'); ?><br>
                                <strong>Penanggung Jawab:</strong> <?= htmlspecialchars($data['penanggung_jawab_tujuan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Rincian Barang</td>
                            <td><?= nl2br(htmlspecialchars($data['rincian_barang'] ?? '-', ENT_QUOTES, 'UTF-8')); ?></td>
                        </tr>
                        <?php if (!empty($data['catatan'])) : ?>
                            <tr>
                                <td>Catatan</td>
                                <td><?= nl2br(htmlspecialchars($data['catatan'], ENT_QUOTES, 'UTF-8')); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <p>Harap barang tersebut dikelola dengan baik di lokasi tujuan sesuai dengan ketentuan yang berlaku.</p>
            <p>Demikian surat pengesahan mutasi ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>
        </div>

        <div class="footer">
            <div>
                <p>Penanggung Jawab Asal</p>
                <div class="signature">
                    <div class="signature-line"></div>
                    <p>(<?= htmlspecialchars($data['penanggung_jawab_asal'] ?? ($data['dipinjam_oleh'] ?? '........................'), ENT_QUOTES, 'UTF-8'); ?>)</p>
                </div>
            </div>
            <div>
                <p>Penanggung Jawab Tujuan</p>
                <div class="signature">
                    <div class="signature-line"></div>
                    <p>(<?= htmlspecialchars($data['penanggung_jawab_tujuan'] ?? '........................', ENT_QUOTES, 'UTF-8'); ?>)</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
        $stmt->close();
    } else {
        echo "Data mutasi tidak ditemukan!";
    }
} else {
    echo "ID mutasi tidak valid!";
}
?>