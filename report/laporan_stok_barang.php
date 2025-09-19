<script>
    window.print();
</script>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Stok Barang</title>
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
    <h3 style="text-align: center;">LAPORAN STOK BARANG</h3>

    <?php
    include '../koneksi.php';
    $query = mysqli_query($conn, "SELECT * FROM barang ORDER BY id_barang DESC");
    ?>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID Barang</th>
                <th>Nama Barang</th>
                <th>Stok</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            while ($data = mysqli_fetch_array($query)) : ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $data['id_barang']; ?></td>
                    <td><?= $data['nama_barang']; ?></td>
                    <td><?= $data['stok'] == 1 ? $data['stok']  . ' <u>(Tersedia)</u>' : $data['stok'] . ' <u>(Kosong)</u>'; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>

</html>