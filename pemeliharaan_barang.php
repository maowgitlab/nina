<?php
include 'sidebar.php';
include_once "koneksi.php";

// Mulai transaksi
mysqli_begin_transaction($conn);

// Get the selected room for filtering, if any
$selected_room = isset($_GET['filter_ruangan']) ? htmlspecialchars($_GET['filter_ruangan']) : '';

// Add Functionality
if (isset($_POST['addpemeliharaan_barang'])) {
    $id_barang = htmlspecialchars($_POST['barang']);
    $id_ruangan = htmlspecialchars($_POST['id_ruangan']);
    $keterangan = htmlspecialchars($_POST['keterangan']);
    $tanggal = htmlspecialchars($_POST['tanggal']);
    $biaya = htmlspecialchars($_POST['biaya']);
    $jumlah = htmlspecialchars($_POST['jumlah']);

    // Validate inputs
    if (!is_numeric($jumlah) || $jumlah <= 0) {
        echo '<script>alert("Jumlah harus berupa angka positif!"); window.location.href = window.location.href;</script>';
        exit;
    }
    if (empty($tanggal) || !strtotime($tanggal)) {
        echo '<script>alert("Tanggal tidak valid!"); window.location.href = window.location.href;</script>';
        exit;
    }
    if (!is_numeric($biaya) || $biaya < 0) {
        echo '<script>alert("Biaya harus berupa angka non-negatif!"); window.location.href = window.location.href;</script>';
        exit;
    }

    // Check stock
    $cekStokQuery = "SELECT stok_awal, stok_akhir, stok_dipinjam, stok FROM barang WHERE id_barang = ?";
    $cekStokStmt = $conn->prepare($cekStokQuery);
    $cekStokStmt->bind_param("i", $id_barang);
    $cekStokStmt->execute();
    $cekStokResult = $cekStokStmt->get_result();
    $stokData = $cekStokResult->fetch_assoc();
    $stokAwal = (int)$stokData['stok_awal'];
    $stokAkhirSekarang = (int)$stokData['stok_akhir'];
    $stokDipinjam = (int)$stokData['stok_dipinjam'];
    $stokSekarang = (int)$stokData['stok'];

    if ($jumlah > $stokAkhirSekarang) {
        echo '<script>alert("Stok barang tidak mencukupi! Stok tersedia: ' . $stokAkhirSekarang . '"); window.location.href = window.location.href;</script>';
        $cekStokStmt->close();
        exit;
    }

    $stokAkhirBaru = $stokAkhirSekarang - $jumlah;
    $stokBaru = $stokAkhirBaru - $stokDipinjam;

    $updateStokQuery = "UPDATE barang SET stok_akhir = ?, stok = ? WHERE id_barang = ?";
    $updateStokStmt = $conn->prepare($updateStokQuery);
    $updateStokStmt->bind_param("iii", $stokAkhirBaru, $stokBaru, $id_barang);

    $query = "INSERT INTO pemeliharaan_barang (id_barang, jumlah, id_ruangan, keterangan, tanggal, biaya) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiisss", $id_barang, $jumlah, $id_ruangan, $keterangan, $tanggal, $biaya);

    if ($updateStokStmt->execute() && $stmt->execute()) {
        mysqli_commit($conn);
        echo '<script>alert("Data berhasil ditambahkan!"); window.location.href = window.location.href;</script>';
    } else {
        mysqli_rollback($conn);
        echo '<script>alert("Gagal tambah data: ' . $conn->error . '"); window.location.href = window.location.href;</script>';
    }
    $stmt->close();
    $updateStokStmt->close();
    $cekStokStmt->close();
}

// Edit Functionality
if (isset($_POST['SimpanEditpemeliharaan'])) {
    $id_pemeliharaan = htmlspecialchars($_POST['Edit_Id_pemeliharaan']);
    $id_barang = htmlspecialchars($_POST['Edit_nama_barang']);
    $id_ruangan = htmlspecialchars($_POST['Edit_ruangan']);
    $keterangan = htmlspecialchars($_POST['Edit_keterangan']);
    $tanggal = htmlspecialchars($_POST['Edit_tanggal']);
    $biaya = htmlspecialchars($_POST['Edit_biaya']);
    $jumlah = htmlspecialchars($_POST['Edit_jumlah']);

    // Validate inputs
    if (!is_numeric($jumlah) || $jumlah <= 0) {
        echo '<script>alert("Jumlah harus berupa angka positif!"); window.location.href = window.location.href;</script>';
        exit;
    }
    if (empty($tanggal) || !strtotime($tanggal)) {
        echo '<script>alert("Tanggal tidak valid!"); window.location.href = window.location.href;</script>';
        exit;
    }
    if (!is_numeric($biaya) || $biaya < 0) {
        echo '<script>alert("Biaya harus berupa angka non-negatif!"); window.location.href = window.location.href;</script>';
        exit;
    }

    // Get current stock and previous quantity
    $cekStokQuery = "SELECT stok_awal, stok_akhir, stok_dipinjam, stok FROM barang WHERE id_barang = ?";
    $cekStokStmt = $conn->prepare($cekStokQuery);
    $cekStokStmt->bind_param("i", $id_barang);
    $cekStokStmt->execute();
    $cekStokResult = $cekStokStmt->get_result();
    $stokData = $cekStokResult->fetch_assoc();
    $stokAwal = (int)$stokData['stok_awal'];
    $stokAkhirSekarang = (int)$stokData['stok_akhir'];
    $stokDipinjam = (int)$stokData['stok_dipinjam'];
    $stokSekarang = (int)$stokData['stok'];

    $cekPemeliharaanQuery = "SELECT jumlah FROM pemeliharaan_barang WHERE id_pemeliharaan = ?";
    $cekPemeliharaanStmt = $conn->prepare($cekPemeliharaanQuery);
    $cekPemeliharaanStmt->bind_param("i", $id_pemeliharaan);
    $cekPemeliharaanStmt->execute();
    $cekPemeliharaanResult = $cekPemeliharaanStmt->get_result();
    $pemeliharaanData = $cekPemeliharaanResult->fetch_assoc();
    $jumlahAwal = (int)$pemeliharaanData['jumlah'];

    $stokTersediaSebelum = $stokAkhirSekarang + $jumlahAwal; // Stok sebelum edit + jumlah lama
    $selisih = $jumlah - $jumlahAwal;
    if ($selisih > 0 && $stokTersediaSebelum < $selisih) {
        echo '<script>alert("Jumlah melebihi stok yang tersedia! Stok tersedia: ' . $stokTersediaSebelum . '"); window.location.href = window.location.href;</script>';
        $cekStokStmt->close();
        $cekPemeliharaanStmt->close();
        exit;
    }

    $stokAkhirBaru = $stokTersediaSebelum - $jumlah; // Kurangi stok akhir berdasarkan jumlah baru
    $stokBaru = $stokAkhirBaru - $stokDipinjam;

    $updateStokQuery = "UPDATE barang SET stok_akhir = ?, stok = ? WHERE id_barang = ?";
    $updateStokStmt = $conn->prepare($updateStokQuery);
    $updateStokStmt->bind_param("iii", $stokAkhirBaru, $stokBaru, $id_barang);

    $query = "UPDATE pemeliharaan_barang SET id_barang = ?, jumlah = ?, id_ruangan = ?, keterangan = ?, tanggal = ?, biaya = ? WHERE id_pemeliharaan = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiisssi", $id_barang, $jumlah, $id_ruangan, $keterangan, $tanggal, $biaya, $id_pemeliharaan);

    if ($updateStokStmt->execute() && $stmt->execute()) {
        mysqli_commit($conn);
        echo '<script>alert("Data berhasil dirubah!"); window.location.href = window.location.href;</script>';
    } else {
        mysqli_rollback($conn);
        echo '<script>alert("Gagal edit data: ' . $conn->error . '"); window.location.href = window.location.href;</script>';
    }
    $stmt->close();
    $updateStokStmt->close();
    $cekStokStmt->close();
    $cekPemeliharaanStmt->close();
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
    <title>Pemeliharaan Barang</title>
    <style>
        .table-footer {
            font-weight: bold;
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Pemeliharaan Barang</h1>
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between">
                        <button type="button" class="btn btn-primary mr-2" data-toggle="modal" data-target="#myModal">
                            Tambah Data
                        </button>
                        <!-- <form method="GET" class="form-inline">
                            <label for="filter_ruangan" class="mr-2">Filter Ruangan:</label>
                            <select name="filter_ruangan" id="filter_ruangan" class="form-control" onchange="this.form.submit()">
                                <option value="">Semua Ruangan</option>
                                <?php
                                $ruangan_list = mysqli_query($conn, "SELECT * FROM ruangan");
                                while ($ruangan = mysqli_fetch_array($ruangan_list)) {
                                    $selected = ($ruangan['nama_ruangan'] == $selected_room) ? "selected" : "";
                                    echo "<option value='" . htmlspecialchars($ruangan['nama_ruangan']) . "' $selected>" . htmlspecialchars($ruangan['nama_ruangan']) . "</option>";
                                }
                                ?>
                            </select>
                        </form> -->
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Barang</th>
                                        <th>Jumlah</th>
                                        <th>Lokasi Ruangan</th>
                                        <th>Keterangan</th>
                                        <th>Tanggal</th>
                                        <th>Biaya</th>
                                        <th>Total Biaya</th>
                                        <th>Opsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Adjust the SQL query based on the selected room
                                    $query = "SELECT pb.*, b.nama_barang, r.nama_ruangan, r.id_ruangan 
                                              FROM pemeliharaan_barang pb 
                                              JOIN barang b ON pb.id_barang = b.id_barang 
                                              JOIN ruangan r ON pb.id_ruangan = r.id_ruangan";
                                    if (!empty($selected_room)) {
                                        $query .= " WHERE r.nama_ruangan = '" . mysqli_real_escape_string($conn, $selected_room) . "'";
                                    }
                                    $ambilsemuadatanya = mysqli_query($conn, $query);
                                    $i = 1;
                                    while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                        $id_pemeliharaan = $data['id_pemeliharaan'];
                                        $id_ruangan = $data['id_ruangan'];
                                        $nama_barang = $data['nama_barang'];
                                        $keterangan = $data['keterangan'];
                                        $tanggal = $data['tanggal'];
                                        $nama_ruangan = $data['nama_ruangan'];
                                        $biaya = floatval($data['biaya']);
                                        $jumlah = $data['jumlah'];
                                        $totalBiayaPerBaris = $biaya * $jumlah; // Hitung total biaya per baris
                                    ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><?= htmlspecialchars($nama_barang) ?></td>
                                            <td><?= htmlspecialchars($jumlah) ?></td>
                                            <td><?= htmlspecialchars($nama_ruangan) ?></td>
                                            <td><?= htmlspecialchars($keterangan) ?></td>
                                            <td><?= htmlspecialchars($tanggal) ?></td>
                                            <td><?= htmlspecialchars(number_format($biaya, 2, ',', '.')) ?></td>
                                            <td><?= htmlspecialchars(number_format($totalBiayaPerBaris, 2, ',', '.')) ?></td>
                                            <td>
                                                <button type="button" data-toggle="modal" data-target="#Edit_pemeliharaan_barang<?php echo $id_pemeliharaan; ?>" class="btn btn-success btn-sm mr-2">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <a href="proses_hapus/pemeliharaan.php?hapus=<?= $id_pemeliharaan; ?>" class="btn btn-danger btn-sm hapus-btn" onclick="return confirm('Yakin ingin menghapus?')">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <!-- Modal Edit pemeliharaan Barang -->
                                        <div class="modal fade" id="Edit_pemeliharaan_barang<?php echo $id_pemeliharaan; ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <form method="post">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Pemeliharaan Barang</h5>
                                                            <button type="button" class="close" data-dismiss="modal">×</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="Edit_Id_pemeliharaan" value="<?php echo $id_pemeliharaan; ?>">
                                                            <label>Nama Barang:</label>
                                                            <select name="Edit_nama_barang" class="form-control" required>
                                                                <?php
                                                                $barang_list = mysqli_query($conn, "SELECT * FROM barang");
                                                                while ($barang = mysqli_fetch_array($barang_list)) {
                                                                    $selected = ($barang['id_barang'] == $data['id_barang']) ? "selected" : "";
                                                                    echo "<option value='" . $barang['id_barang'] . "' $selected>" . htmlspecialchars($barang['nama_barang']) . "</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                            <br>
                                                            <label>Jumlah:</label>
                                                            <input type="number" name="Edit_jumlah" value="<?php echo htmlspecialchars($jumlah); ?>" class="form-control" min="1" required>
                                                            <br>
                                                            <label>Lokasi Ruangan:</label>
                                                            <select name="Edit_ruangan" class="form-control" required>
                                                                <?php
                                                                $ruangan_list = mysqli_query($conn, "SELECT * FROM ruangan");
                                                                while ($ruangan = mysqli_fetch_array($ruangan_list)) {
                                                                    $selected = ($ruangan['id_ruangan'] == $data['id_ruangan']) ? "selected" : "";
                                                                    echo "<option value='" . $ruangan['id_ruangan'] . "' $selected>" . htmlspecialchars($ruangan['nama_ruangan']) . "</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                            <br>
                                                            <label>Keterangan:</label>
                                                            <input type="text" name="Edit_keterangan" value="<?php echo htmlspecialchars($keterangan); ?>" class="form-control" required>
                                                            <br>
                                                            <label>Tanggal:</label>
                                                            <input type="date" name="Edit_tanggal" value="<?php echo htmlspecialchars($tanggal); ?>" class="form-control" required>
                                                            <br>
                                                            <label>Biaya:</label>
                                                            <input type="text" name="Edit_biaya" value="<?php echo htmlspecialchars($biaya); ?>" class="form-control" required>
                                                            <br>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                            <button type="submit" name="SimpanEditpemeliharaan" class="btn btn-primary">Simpan</button>
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
                    <h4 class="modal-title">Tambah Pemeliharaan Barang</h4>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <!-- Modal body -->
                <form method="post">
                    <div class="modal-body">
                        <select name="barang" class="form-control" required>
                            <?php
                            $ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM barang");
                            while ($fetcharray = mysqli_fetch_array($ambilsemuadatanya)) {
                                $nama_barang = $fetcharray['nama_barang'];
                                $id_barang = $fetcharray['id_barang'];
                            ?>
                                <option value="<?= $id_barang; ?>"><?= htmlspecialchars($nama_barang); ?></option>
                            <?php
                            }
                            ?>
                        </select>
                        <br>
                        <input type="number" name="jumlah" placeholder="Jumlah" class="form-control" min="1" required>
                        <br>
                        <select name="id_ruangan" class="form-control" required>
                            <?php
                            $ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM ruangan");
                            while ($fetcharray = mysqli_fetch_array($ambilsemuadatanya)) {
                                $nama_ruangan = $fetcharray['nama_ruangan'];
                                $id_ruangan = $fetcharray['id_ruangan'];
                            ?>
                                <option value="<?= $id_ruangan; ?>"><?= htmlspecialchars($nama_ruangan); ?></option>
                            <?php
                            }
                            ?>
                        </select>
                        <br>
                        <input type="text" name="keterangan" placeholder="Keterangan" class="form-control" required>
                        <br>
                        <input type="date" name="tanggal" placeholder="Tanggal" class="form-control" required>
                        <br>
                        <input type="text" name="biaya" placeholder="Biaya" class="form-control" required>
                        <br>
                        <button type="submit" class="btn btn-primary" name="addpemeliharaan_barang">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>