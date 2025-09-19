<?php
include 'sidebar.php';
include_once "koneksi.php";

// Mulai transaksi
mysqli_begin_transaction($conn);

if (isset($_POST['addinventaris_barang'])) {
    $id_user = $_POST['id_user'];
    $id_barang = htmlspecialchars($_POST['barang']);
    $id_ruangan = htmlspecialchars($_POST['ruangan']);
    $merk = htmlspecialchars($_POST['merk']);
    $jumlah = htmlspecialchars($_POST['jumlah']);
    $tanggal = htmlspecialchars($_POST['tanggal']);

    // Validasi input
    if (!is_numeric($jumlah) || $jumlah <= 0) {
        echo '<script>alert("Jumlah harus berupa angka positif!"); window.location.href = window.location.href;</script>';
        exit;
    }
    if (empty($tanggal) || !strtotime($tanggal)) {
        echo '<script>alert("Tanggal tidak valid!"); window.location.href = window.location.href;</script>';
        exit;
    }

    // Cek stok di tabel barang
    $cekStokQuery = "SELECT stok_awal, stok_akhir, stok_dipinjam, stok FROM barang WHERE id_barang = ?";
    $cekStokStmt = $conn->prepare($cekStokQuery);
    $cekStokStmt->bind_param("i", $id_barang);
    $cekStokStmt->execute();
    $cekStokResult = $cekStokStmt->get_result();
    $stokData = $cekStokResult->fetch_assoc();
    $stokAwal = (int)$stokData['stok_awal'];
    $stokAkhirSekarang = (int)$stokData['stok_akhir'];
    $stokDipinjamSekarang = (int)$stokData['stok_dipinjam'];
    $stokSekarang = (int)$stokData['stok'];

    if ($stokSekarang < $jumlah) {
        echo '<script>alert("Stok barang tidak cukup! Stok tersedia: ' . $stokSekarang . '"); window.location.href = window.location.href;</script>';
        $cekStokStmt->close();
        exit;
    }

    // Cek apakah data sudah ada
    $cekQuery = "SELECT * FROM inventaris_barang WHERE id_barang = ? AND id_ruangan = ? AND merk = ? AND tanggal = ?";
    $cekStmt = $conn->prepare($cekQuery);
    $cekStmt->bind_param("iiss", $id_barang, $id_ruangan, $merk, $tanggal);
    $cekStmt->execute();
    $cekResult = $cekStmt->get_result();

    if ($cekResult->num_rows == 0) {
        $query = "INSERT INTO inventaris_barang (id_barang, id_ruangan, id_user, merk, jumlah, tanggal) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiisis", $id_barang, $id_ruangan, $id_user, $merk, $jumlah, $tanggal);

        if ($stmt->execute()) {
            // Update stok di tabel barang
            $stokDipinjamBaru = $stokDipinjamSekarang + $jumlah;
            $stokAkhirBaru = $stokAwal - $stokDipinjamBaru; // Stok akhir = stok awal - total dipinjam
            $stokBaru = $stokAkhirBaru; // Stok tersedia = stok akhir

            $updateStokQuery = "UPDATE barang SET stok_dipinjam = ?, stok_akhir = ?, stok = ? WHERE id_barang = ?";
            $updateStokStmt = $conn->prepare($updateStokQuery);
            $updateStokStmt->bind_param("iiii", $stokDipinjamBaru, $stokAkhirBaru, $stokBaru, $id_barang);
            $updateStokStmt->execute();
            $updateStokStmt->close();

            mysqli_commit($conn);
            echo '<script>alert("Data berhasil ditambahkan!"); window.location.href = window.location.href;</script>';
        } else {
            mysqli_rollback($conn);
            echo '<script>alert("Gagal menambahkan data!");</script>';
        }
        $stmt->close();
    } else {
        mysqli_rollback($conn);
        echo '<script>alert("Data sudah ada!");</script>';
    }
    $cekStmt->close();
    $cekStokStmt->close();
}

if (isset($_POST['updateinventaris_barang'])) {
    $id_inventaris_barang = htmlspecialchars($_POST['id_inventaris_barang']);
    $id_barang = htmlspecialchars($_POST['barang']);
    $id_ruangan = htmlspecialchars($_POST['ruangan']);
    $merk = htmlspecialchars($_POST['merk']);
    $jumlah = htmlspecialchars($_POST['jumlah']);
    $tanggal = htmlspecialchars($_POST['tanggal']);

    // Validasi input
    if (!is_numeric($jumlah) || $jumlah <= 0) {
        echo '<script>alert("Jumlah harus berupa angka positif!"); window.location.href = window.location.href;</script>';
        exit;
    }
    if (empty($tanggal) || !strtotime($tanggal)) {
        echo '<script>alert("Tanggal tidak valid!"); window.location.href = window.location.href;</script>';
        exit;
    }

    // Ambil jumlah sebelumnya
    $cekSebelumnyaQuery = "SELECT jumlah FROM inventaris_barang WHERE id_inventaris_barang = ?";
    $cekSebelumnyaStmt = $conn->prepare($cekSebelumnyaQuery);
    $cekSebelumnyaStmt->bind_param("i", $id_inventaris_barang);
    $cekSebelumnyaStmt->execute();
    $cekSebelumnyaResult = $cekSebelumnyaStmt->get_result();
    $dataSebelumnya = $cekSebelumnyaResult->fetch_assoc();
    $jumlahSebelumnya = (int)$dataSebelumnya['jumlah'];

    // Cek stok saat ini
    $cekStokQuery = "SELECT stok_awal, stok_akhir, stok_dipinjam, stok FROM barang WHERE id_barang = ?";
    $cekStokStmt = $conn->prepare($cekStokQuery);
    $cekStokStmt->bind_param("i", $id_barang);
    $cekStokStmt->execute();
    $cekStokResult = $cekStokStmt->get_result();
    $stokData = $cekStokResult->fetch_assoc();
    $stokAwal = (int)$stokData['stok_awal'];
    $stokAkhirSekarang = (int)$stokData['stok_akhir'];
    $stokDipinjamSekarang = (int)$stokData['stok_dipinjam'];
    $stokSekarang = (int)$stokData['stok'];

    $selisih = $jumlah - $jumlahSebelumnya;
    if ($selisih > 0 && $stokSekarang < $selisih) {
        echo '<script>alert("Stok barang tidak cukup! Stok tersedia: ' . $stokSekarang . '"); window.location.href = window.location.href;</script>';
        $cekStokStmt->close();
        $cekSebelumnyaStmt->close();
        exit;
    }

    $query = "UPDATE inventaris_barang SET id_barang = ?, id_ruangan = ?, merk = ?, jumlah = ?, tanggal = ? WHERE id_inventaris_barang = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisisi", $id_barang, $id_ruangan, $merk, $jumlah, $tanggal, $id_inventaris_barang);

    if ($stmt->execute()) {
        // Update stok di tabel barang berdasarkan selisih
        $stokDipinjamBaru = $stokDipinjamSekarang + $selisih;
        $stokAkhirBaru = $stokAwal - $stokDipinjamBaru; // Stok akhir = stok awal - total dipinjam
        $stokBaru = $stokAkhirBaru; // Stok tersedia = stok akhir

        $updateStokQuery = "UPDATE barang SET stok_dipinjam = ?, stok_akhir = ?, stok = ? WHERE id_barang = ?";
        $updateStokStmt = $conn->prepare($updateStokQuery);
        $updateStokStmt->bind_param("iiii", $stokDipinjamBaru, $stokAkhirBaru, $stokBaru, $id_barang);
        $updateStokStmt->execute();
        $updateStokStmt->close();

        mysqli_commit($conn);
        echo '<script>alert("Data berhasil diperbarui!"); window.location.href = window.location.href;</script>';
    } else {
        mysqli_rollback($conn);
        echo '<script>alert("Gagal memperbarui data!");</script>';
    }
    $stmt->close();
    $cekStokStmt->close();
    $cekSebelumnyaStmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <title>Inventaris Barang</title>
</head>

<body>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Inventaris Barang</h1>
                <div class="card mb-4">
                    <div class="card-header">
                        <?php if ($data_level == 'auditor' || $data_level == 'admin') : ?>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                                Tambah Data
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode Barang</th>
                                        <th>Gambar</th>
                                        <th>QR Code</th>
                                        <th>Nama Barang</th>
                                        <th>Lokasi Ruangan</th>
                                        <th>Merk</th>
                                        <th>Jumlah</th>
                                        <th>Tanggal</th>
                                        <!-- <th>Status</th> -->
                                        <th>Opsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($data_level == 'pegawai') {
                                        $query = "SELECT ib.id_inventaris_barang, ib.status, b.id_barang, b.kode, b.nama_barang, b.gambar, b.qrcode, r.id_ruangan, r.nama_ruangan, ib.merk, ib.jumlah, ib.tanggal 
                                          FROM inventaris_barang ib
                                          JOIN barang b ON ib.id_barang = b.id_barang
                                          JOIN ruangan r ON ib.id_ruangan = r.id_ruangan WHERE ib.id_user = '$data_id'";
                                    } else {
                                        $query = "SELECT ib.id_inventaris_barang, ib.status, b.id_barang, b.kode, b.nama_barang, b.gambar, b.qrcode, r.id_ruangan, r.nama_ruangan, ib.merk, ib.jumlah, ib.tanggal 
                                          FROM inventaris_barang ib
                                          JOIN barang b ON ib.id_barang = b.id_barang
                                          JOIN ruangan r ON ib.id_ruangan = r.id_ruangan";
                                    }
                                    $result = mysqli_query($conn, $query);
                                    $i = 1;

                                    while ($data = mysqli_fetch_assoc($result)) {
                                        $gambar = $data['gambar'] ? $data['gambar'] : 'default.jpg';
                                        $qrcode = $data['qrcode'] ? $data['qrcode'] : 'default_qr.png';
                                    ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td><?= htmlspecialchars($data['kode'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><img src="uploads/<?= $gambar; ?>" alt="Gambar Barang" style="width: 50px; height: 50px;"></td>
                                            <td><img src="<?= $qrcode; ?>" alt="QR Code" style="width: 50px; height: 50px;"></td>
                                            <td><?= htmlspecialchars($data['nama_barang'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($data['nama_ruangan'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($data['merk'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($data['jumlah'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($data['tanggal'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <!-- <td>
                                                <?php
                                                if ($data['status'] == 'Y') {
                                                    echo '<span class="badge badge-success">Disetujui</span>';
                                                } elseif ($data['status'] == 'N') {
                                                    echo '<span class="badge badge-danger">Ditolak</span>';
                                                } else {
                                                    echo '<span class="badge badge-warning">Pending</span>';
                                                }
                                                ?>
                                            </td> -->
                                            <td>
                                                <?php if ($data_level == 'admin' || $data_level == 'auditor') : ?>
                                                    <button type="button" data-toggle="modal" data-target="#Edit_inventaris_barang<?= $data['id_inventaris_barang']; ?>" class="btn btn-success btn-sm">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                    <a href="proses_hapus/inventaris_barang.php?hapus=<?= $data['id_inventaris_barang']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="Edit_inventaris_barang<?= $data['id_inventaris_barang']; ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <!-- Modal Header -->
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Edit Inventaris Barang</h4>
                                                        <button type="button" class="close" data-dismiss="modal">×</button>
                                                    </div>
                                                    <!-- Modal body -->
                                                    <form method="post" action="">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id_inventaris_barang" value="<?= $data['id_inventaris_barang']; ?>">
                                                            <select name="barang" class="form-control" required>
                                                                <?php
                                                                $ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM barang");
                                                                while ($fetcharray = mysqli_fetch_assoc($ambilsemuadatanya)) {
                                                                    $nama_barang = htmlspecialchars($fetcharray['nama_barang'], ENT_QUOTES, 'UTF-8');
                                                                    $id_barang = htmlspecialchars($fetcharray['id_barang'], ENT_QUOTES, 'UTF-8');
                                                                ?>
                                                                    <option value="<?= $id_barang; ?>" <?= $id_barang == $data['id_barang'] ? 'selected' : ''; ?>><?= $nama_barang; ?></option>
                                                                <?php
                                                                }
                                                                ?>
                                                            </select>
                                                            <br>
                                                            <select name="ruangan" class="form-control" required>
                                                                <?php
                                                                $ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM ruangan");
                                                                while ($fetcharray = mysqli_fetch_assoc($ambilsemuadatanya)) {
                                                                    $nama_ruangan = htmlspecialchars($fetcharray['nama_ruangan'], ENT_QUOTES, 'UTF-8');
                                                                    $id_ruangan = htmlspecialchars($fetcharray['id_ruangan'], ENT_QUOTES, 'UTF-8');
                                                                ?>
                                                                    <option value="<?= $id_ruangan; ?>" <?= $id_ruangan == $data['id_ruangan'] ? 'selected' : ''; ?>><?= $nama_ruangan; ?></option>
                                                                <?php
                                                                }
                                                                ?>
                                                            </select>
                                                            <br>
                                                            <input type="text" name="merk" placeholder="Merk" value="<?= htmlspecialchars($data['merk'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
                                                            <br>
                                                            <input type="number" name="jumlah" placeholder="Jumlah" value="<?= htmlspecialchars($data['jumlah'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" min="1" required>
                                                            <br>
                                                            <input type="date" name="tanggal" placeholder="Tanggal" value="<?= htmlspecialchars($data['tanggal'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
                                                            <br>
                                                            <button type="submit" class="btn btn-primary" name="updateinventaris_barang">Submit</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
                <div class="d-flex align-items-center justify-content-between small">
                    <div class="text-muted">Hak Cipta © Website Anda 2025</div>
                    <div>
                        <a href="#">Kebijakan Privasi</a>
                        ·
                        <a href="#">Syarat & Ketentuan</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- The Modal -->
    <div class="modal fade" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Inventaris Barang</h4>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <!-- Modal body -->
                <form method="post" action="">
                    <input type="hidden" name="id_user" value="<?= $data_id; ?>">
                    <div class="modal-body">
                        <select name="barang" class="form-control" required>
                            <?php
                            $ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM barang");
                            while ($fetcharray = mysqli_fetch_assoc($ambilsemuadatanya)) {
                                $nama_barang = htmlspecialchars($fetcharray['nama_barang'], ENT_QUOTES, 'UTF-8');
                                $id_barang = htmlspecialchars($fetcharray['id_barang'], ENT_QUOTES, 'UTF-8');
                            ?>
                                <option value="<?= $id_barang; ?>"><?= $nama_barang; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                        <br>
                        <select name="ruangan" class="form-control" required>
                            <?php
                            $ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM ruangan");
                            while ($fetcharray = mysqli_fetch_assoc($ambilsemuadatanya)) {
                                $nama_ruangan = htmlspecialchars($fetcharray['nama_ruangan'], ENT_QUOTES, 'UTF-8');
                                $id_ruangan = htmlspecialchars($fetcharray['id_ruangan'], ENT_QUOTES, 'UTF-8');
                            ?>
                                <option value="<?= $id_ruangan; ?>"><?= $nama_ruangan; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                        <br>
                        <input type="text" name="merk" placeholder="Merk" class="form-control" required>
                        <br>
                        <input type="number" name="jumlah" placeholder="Jumlah" class="form-control" min="1" required>
                        <br>
                        <input type="date" name="tanggal" placeholder="Tanggal" class="form-control" required>
                        <br>
                        <button type="submit" class="btn btn-primary" name="addinventaris_barang">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $(document).on('click', '.hapus-btn', function(e) {
                e.preventDefault();
                var id_inventaris_barang = $(this).data('id');
                if (confirm('Apakah anda yakin ingin menghapus data ini?')) {
                    window.location.href = '?hapus=' + id_inventaris_barang;
                }
            });
        });
    </script>
</body>

</html>