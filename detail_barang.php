<?php
include_once "koneksi.php";

if (isset($_GET['id'])) {
    $id_barang = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT kode, nama_barang, spesifikasi, stok_awal, gambar FROM barang WHERE id_barang = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_barang);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    if ($data) {
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Barang</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .card {
            max-width: 600px;
            margin: 0 auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #3498db;
            color: #fff;
            font-weight: 600;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .card-body {
            padding: 20px;
        }
        .item-image {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .item-detail {
            font-size: 16px;
            margin-bottom: 10px;
        }
        .item-detail strong {
            display: inline-block;
            width: 150px;
        }
        @media (max-width: 576px) {
            .card {
                margin: 10px;
            }
            .item-detail {
                font-size: 14px;
            }
            .item-detail strong {
                width: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h4>Detail Barang</h4>
        </div>
        <div class="card-body">
            <?php if ($data['gambar'] && file_exists("Uploads/" . $data['gambar'])) : ?>
                <img src="Uploads/<?php echo htmlspecialchars($data['gambar']); ?>" alt="Gambar Barang" class="item-image">
            <?php else : ?>
                <p class="text-muted">Gambar tidak tersedia.</p>
            <?php endif; ?>
            <div class="item-detail">
                <strong>Kode Barang:</strong> <?php echo htmlspecialchars($data['kode']); ?>
            </div>
            <div class="item-detail">
                <strong>Nama Barang:</strong> <?php echo htmlspecialchars($data['nama_barang']); ?>
            </div>
            <div class="item-detail">
                <strong>Spesifikasi:</strong> <?php echo nl2br(htmlspecialchars($data['spesifikasi'])); ?>
            </div>
            <div class="item-detail">
                <strong>Stok Awal:</strong> <?php echo htmlspecialchars($data['stok_awal']); ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php
    } else {
        echo "<p>Data barang tidak ditemukan!</p>";
    }
} else {
    echo "<p>ID barang tidak valid!</p>";
}
?>