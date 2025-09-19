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
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT p.*, b.nama_barang, b.gambar AS gambar_barang, b.qrcode AS qrcode_barang, k.nama_kendaraan AS nama_kendaraan, k.gambar AS gambar_kendaraan, k.qrcode AS qrcode_kendaraan, pg.nama_pegawai AS dipinjam_oleh 
                                              FROM peminjaman p 
                                              LEFT JOIN barang b ON p.id_barang = b.id_barang AND p.jenis = 'barang'
                                              LEFT JOIN inventaris_kendaraan k ON p.id_barang = k.id_inventaris_kendaraan AND p.jenis = 'kendaraan'
                                              JOIN pegawai pg ON p.id_user = pg.id_pegawai WHERE p.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
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
    <title>Surat Peminjaman</title>
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
        }

        .footer div {
            width: 45%;
            text-align: center;
        }

        .footer p {
            margin: 5px 0;
            font-size: 10pt;
        }

        .signature {
            margin-top: 60px;
            font-size: 10pt;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 150px;
            margin: 20px auto 10px;
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

        <div class="letter-title">Surat Pengesahan Peminjaman</div>
        <div class="letter-number">Nomor: <?= htmlspecialchars(date('Y/m/d') . '/SP/' . $id, ENT_QUOTES, 'UTF-8') ?></div>
        <div class="letter-date">Banjarmasin, <?= date('d ') . $bulan_indonesia[date('n')] . date(' Y'); ?></div>

        <div class="letter-body">
            <p>Kepada Yth.<br>
               Bapak/Ibu <?= htmlspecialchars($data['dipinjam_oleh'], ENT_QUOTES, 'UTF-8') ?><br>
               di Tempat</p>
            <p>Dengan hormat,</p>
            <p>Bersama ini kami sampaikan bahwa Dinas Perdagangan Provinsi Kalimantan Selatan telah menyetujui permohonan peminjaman sebagai berikut:</p>
            <p>
                <span class="field-label">Jenis Item</span>: <?= htmlspecialchars(ucfirst($data['jenis']), ENT_QUOTES, 'UTF-8') ?><br>
                <span class="field-label">ID Item</span>: <?= htmlspecialchars($data['id_barang'], ENT_QUOTES, 'UTF-8') ?><br>
                <span class="field-label">Nama Item</span>: <?= htmlspecialchars($data['jenis'] === 'barang' ? $data['nama_barang'] : $data['nama_kendaraan'], ENT_QUOTES, 'UTF-8') ?><br>
                <span class="field-label">Jumlah</span>: <?= htmlspecialchars(number_format($data['jumlah'], 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?><br>
                <span class="field-label">Tanggal Pinjam</span>: <?= htmlspecialchars(date('d-m-Y', strtotime($data['tanggal_pinjam'])), ENT_QUOTES, 'UTF-8') ?><br>
                <span class="field-label">Keterangan</span>: <?= htmlspecialchars($data['keterangan'] ?: '-', ENT_QUOTES, 'UTF-8') ?>
            </p>
            <p>Harap barang/kendaraan tersebut digunakan sesuai dengan ketentuan yang berlaku dan dikembalikan dalam kondisi baik pada waktu yang telah disepakati.</p>
            <p>Demikian surat pengesahan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>
        </div>

        <div class="footer">
            <div>
                <p>Pemohon,</p>
                <div class="signature" style="margin-left: 95px;">
                    <div class="signature-line"></div>
                    <p>(<?= htmlspecialchars($data['dipinjam_oleh'], ENT_QUOTES, 'UTF-8') ?>)</p>
                </div>
            </div>
            <div>
                <p>Mengetahui,<br>Kepala Dinas Perdagangan</p>
                <div class="signature" style="margin-left: 80px;">
                    <p>(___________________________)</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
        $stmt->close();
    } else {
        echo "Data peminjaman tidak ditemukan!";
    }
} else {
    echo "ID peminjaman tidak valid!";
}
?>