<script>
    window.print();
</script>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Perpindahan Barang Per Ruangan</title>
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

        .footer {
            text-align: right;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="../img/logo disdag.png">
        <h1>Dinas Perdagangan</h1>
        <h2>Provinsi Kalimantan Selatan</h2>
        <p>Jl. S. Parman No. 44, Antasan Besar, Kec. Banjarmasin Tengah, Kota Banjarmasin, Kalimantan Selatan, 70114</p>
        <small>Telepon: (0511) 3354219 | Email: dinasperdagangan.kalsel@gmail.com</small>
    </div>
    <hr class="line">
    <h3 style="text-align: center;">LAPORAN PERPINDAHAN BARANG PER RUANGAN</h3>

    <?php
    include '../koneksi.php';

    $query = mysqli_query($conn, "SELECT ruangan1.id_ruangan1, ruangan1.nama_ruangan1, count(ruangan1.id_ruangan1) as total FROM ruangan1 JOIN mutasi_barang ON mutasi_barang.id_ruangan1 = ruangan1.id_ruangan1 WHERE mutasi_barang.jumlah != 0 group by id_ruangan1");
    ?>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID Ruangan</th>
                <th>Nama Ruangan 1</th>
                <th>Total Barang</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            while ($data = mysqli_fetch_array($query)) : ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $data['id_ruangan1']; ?></td>
                    <td><?= $data['nama_ruangan1']; ?></td>
                    <td><?= $data['total']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>

</html>