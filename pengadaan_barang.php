<?php
include 'sidebar.php';
include_once "koneksi.php";

// Mulai transaksi
mysqli_begin_transaction($conn);

// Ambil filter ruangan dan bulan dari parameter GET
$filter_ruangan = isset($_GET['filter_ruangan']) ? htmlspecialchars($_GET['filter_ruangan']) : 'all';
$filter_bulan = isset($_GET['filter_bulan']) ? htmlspecialchars($_GET['filter_bulan']) : 'all';

// Add Functionality
if (isset($_POST['addpengadaan_barang'])) {
    $id_barang = htmlspecialchars($_POST['barang']);
    $id_supplier = htmlspecialchars($_POST['supplier']);
    $id_ruangan = htmlspecialchars($_POST['ruangan']);
    $jumlah = htmlspecialchars($_POST['jumlah']);
    $tanggal_masuk = htmlspecialchars($_POST['tanggal_masuk']);

    // Validate inputs
    if (!is_numeric($jumlah) || $jumlah <= 0) {
        echo '<script>alert("Jumlah harus berupa angka positif!"); window.location.href = window.location.href;</script>';
        exit;
    }
    if (empty($tanggal_masuk) || !strtotime($tanggal_masuk)) {
        echo '<script>alert("Tanggal masuk tidak valid!"); window.location.href = window.location.href;</script>';
        exit;
    }

    $query = "INSERT INTO pengadaan_barang (id_barang, id_supplier, id_ruangan, jumlah, tanggal_masuk) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiss", $id_barang, $id_supplier, $id_ruangan, $jumlah, $tanggal_masuk);

    if ($stmt->execute()) {
        // Tambah stok di tabel barang
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

        $stokAkhirBaru = $stokAkhirSekarang + $jumlah; // Tambah stok akhir
        $stokBaru = $stokAkhirBaru - $stokDipinjam; // Stok tersedia = stok akhir - stok yang dipinjam

        $updateStokQuery = "UPDATE barang SET stok_akhir = ?, stok = ? WHERE id_barang = ?";
        $updateStokStmt = $conn->prepare($updateStokQuery);
        $updateStokStmt->bind_param("iii", $stokAkhirBaru, $stokBaru, $id_barang);
        $updateStokStmt->execute();
        $updateStokStmt->close();

        mysqli_commit($conn);
        echo '<script>alert("Data pengadaan barang berhasil ditambahkan!"); window.location.href = window.location.href;</script>';
    } else {
        mysqli_rollback($conn);
        echo '<script>alert("Gagal menambahkan data: ' . $conn->error . '"); window.location.href = window.location.href;</script>';
    }
    $stmt->close();
}

// Edit Functionality
if (isset($_POST['SimpanEditpengadaan'])) {
    $id_pengadaan = htmlspecialchars($_POST['Edit_Id_pengadaan']);
    $id_barang = htmlspecialchars($_POST['Edit_nama_barang']);
    $id_supplier = htmlspecialchars($_POST['Edit_nama_supplier']);
    $id_ruangan = htmlspecialchars($_POST['Edit_nama_ruangan']);
    $jumlah = htmlspecialchars($_POST['Edit_jumlah']);
    $tanggal_masuk = htmlspecialchars($_POST['Edit_tanggal_masuk']);

    // Validate inputs
    if (!is_numeric($jumlah) || $jumlah <= 0) {
        echo '<script>alert("Jumlah harus berupa angka positif!"); window.location.href = window.location.href;</script>';
        exit;
    }
    if (empty($tanggal_masuk) || !strtotime($tanggal_masuk)) {
        echo '<script>alert("Tanggal masuk tidak valid!"); window.location.href = window.location.href;</script>';
        exit;
    }

    // Ambil jumlah sebelumnya
    $cekSebelumnyaQuery = "SELECT jumlah FROM pengadaan_barang WHERE id_pengadaan = ?";
    $cekSebelumnyaStmt = $conn->prepare($cekSebelumnyaQuery);
    $cekSebelumnyaStmt->bind_param("i", $id_pengadaan);
    $cekSebelumnyaStmt->execute();
    $cekSebelumnyaResult = $cekSebelumnyaStmt->get_result();
    $dataSebelumnya = $cekSebelumnyaResult->fetch_assoc();
    $jumlahSebelumnya = (int)$dataSebelumnya['jumlah'];

    $query = "UPDATE pengadaan_barang 
              SET id_barang = ?, id_supplier = ?, id_ruangan = ?, jumlah = ?, tanggal_masuk = ? 
              WHERE id_pengadaan = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiissi", $id_barang, $id_supplier, $id_ruangan, $jumlah, $tanggal_masuk, $id_pengadaan);

    if ($stmt->execute()) {
        // Sesuaikan stok berdasarkan selisih
        $selisih = $jumlah - $jumlahSebelumnya;
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

        $stokAkhirBaru = $stokAkhirSekarang + $selisih; // Tambah atau kurangi stok akhir
        $stokBaru = $stokAkhirBaru - $stokDipinjam; // Stok tersedia = stok akhir - stok yang dipinjam

        $updateStokQuery = "UPDATE barang SET stok_akhir = ?, stok = ? WHERE id_barang = ?";
        $updateStokStmt = $conn->prepare($updateStokQuery);
        $updateStokStmt->bind_param("iii", $stokAkhirBaru, $stokBaru, $id_barang);
        $updateStokStmt->execute();
        $updateStokStmt->close();

        mysqli_commit($conn);
        echo '<script>alert("Data pengadaan barang berhasil diperbarui!"); window.location.href = window.location.href;</script>';
    } else {
        mysqli_rollback($conn);
        echo '<script>alert("Gagal memperbarui data: ' . $conn->error . '"); window.location.href = window.location.href;</script>';
    }
    $stmt->close();
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
    <title>Pengadaan Barang</title>
</head>

<body>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Pengadaan Barang</h1>
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <?php if (isset($data_level) && $data_level == 'admin') : ?>
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                                        Tambah Data
                                    </button>
                                <?php endif; ?>
                            </div>
                            <!-- <div class="col-md-6 text-right">
                                <form method="get" action="">
                                    <div class="form-row align-items-end">
                                        <div class="col-auto">
                                            <label for="filter_ruangan">Filter Ruangan:</label>
                                            <select name="filter_ruangan" id="filter_ruangan" class="form-control" onchange="this.form.submit()">
                                                <option value="all" <?php echo ($filter_ruangan == 'all') ? 'selected' : ''; ?>>Semua Ruangan</option>
                                                <?php
                                                $ruangan_list = mysqli_query($conn, "SELECT * FROM ruangan");
                                                while ($ruangan = mysqli_fetch_array($ruangan_list)) {
                                                    $selected = ($filter_ruangan == $ruangan['id_ruangan']) ? 'selected' : '';
                                                    echo "<option value='" . htmlspecialchars($ruangan['id_ruangan'], ENT_QUOTES, 'UTF-8') . "' $selected>" . htmlspecialchars($ruangan['nama_ruangan'], ENT_QUOTES, 'UTF-8') . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-auto">
                                            <label for="filter_bulan">Filter Bulan:</label>
                                            <select name="filter_bulan" id="filter_bulan" class="form-control" onchange="this.form.submit()">
                                                <option value="all" <?php echo ($filter_bulan == 'all') ? 'selected' : ''; ?>>Semua Bulan</option>
                                                <option value="01" <?php echo ($filter_bulan == '01') ? 'selected' : ''; ?>>Januari</option>
                                                <option value="02" <?php echo ($filter_bulan == '02') ? 'selected' : ''; ?>>Februari</option>
                                                <option value="03" <?php echo ($filter_bulan == '03') ? 'selected' : ''; ?>>Maret</option>
                                                <option value="04" <?php echo ($filter_bulan == '04') ? 'selected' : ''; ?>>April</option>
                                                <option value="05" <?php echo ($filter_bulan == '05') ? 'selected' : ''; ?>>Mei</option>
                                                <option value="06" <?php echo ($filter_bulan == '06') ? 'selected' : ''; ?>>Juni</option>
                                                <option value="07" <?php echo ($filter_bulan == '07') ? 'selected' : ''; ?>>Juli</option>
                                                <option value="08" <?php echo ($filter_bulan == '08') ? 'selected' : ''; ?>>Agustus</option>
                                                <option value="09" <?php echo ($filter_bulan == '09') ? 'selected' : ''; ?>>September</option>
                                                <option value="10" <?php echo ($filter_bulan == '10') ? 'selected' : ''; ?>>Oktober</option>
                                                <option value="11" <?php echo ($filter_bulan == '11') ? 'selected' : ''; ?>>November</option>
                                                <option value="12" <?php echo ($filter_bulan == '12') ? 'selected' : ''; ?>>Desember</option>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                            </div> -->
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th>Nama Supplier</th>
                                        <th>Lokasi Ruangan</th>
                                        <th>Jumlah</th>
                                        <th>Tanggal Masuk</th>
                                        <th>Opsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT pb.*, b.kode, b.nama_barang, s.nama_supplier, r.nama_ruangan 
                                              FROM pengadaan_barang pb 
                                              JOIN barang b ON pb.id_barang = b.id_barang
                                              JOIN supplier s ON pb.id_supplier = s.id_supplier
                                              JOIN ruangan r ON pb.id_ruangan = r.id_ruangan";
                                    $conditions = [];
                                    if ($filter_ruangan != 'all') {
                                        $conditions[] = "pb.id_ruangan = '" . mysqli_real_escape_string($conn, $filter_ruangan) . "'";
                                    }
                                    if ($filter_bulan != 'all') {
                                        $conditions[] = "MONTH(pb.tanggal_masuk) = '" . mysqli_real_escape_string($conn, $filter_bulan) . "'";
                                    }
                                    if (!empty($conditions)) {
                                        $query .= " WHERE " . implode(" AND ", $conditions);
                                    }
                                    $ambilsemuadatanya = mysqli_query($conn, $query);
                                    $i = 1;
                                    while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                        $id_pengadaan = $data['id_pengadaan'];
                                        $id_barang = $data['kode'];
                                        $nama_barang = $data['nama_barang'];
                                        $nama_supplier = $data['nama_supplier'];
                                        $nama_ruangan = $data['nama_ruangan'];
                                        $jumlah = $data['jumlah'];
                                        $tanggal_masuk = date('d-m-Y', strtotime($data['tanggal_masuk']));
                                    ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td><?= htmlspecialchars($id_barang, ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($nama_barang, ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($nama_supplier, ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($nama_ruangan, ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars(number_format($jumlah, 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($tanggal_masuk, ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <?php if (isset($data_level) && $data_level == 'admin') : ?>
                                                    <button type="button" data-toggle="modal" data-target="#Edit_pengadaan_barang<?php echo $id_pengadaan; ?>" class="btn btn-success btn-sm mr-2">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                    <a href="proses_hapus/pengadaan.php?hapus=<?= $id_pengadaan; ?>" class="btn btn-danger btn-sm hapus-btn" onclick="return confirm('Yakin ingin menghapus?')">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <!-- Modal Edit Pengadaan Barang -->
                                        <div class="modal fade" id="Edit_pengadaan_barang<?php echo $id_pengadaan; ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <form method="post">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Pengadaan Barang</h5>
                                                            <button type="button" class="close" data-dismiss="modal">×</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="Edit_Id_pengadaan" value="<?php echo htmlspecialchars($id_pengadaan, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <label>Nama Barang:</label>
                                                            <select name="Edit_nama_barang" class="form-control" required>
                                                                <?php
                                                                $barang_list = mysqli_query($conn, "SELECT * FROM barang");
                                                                while ($barang = mysqli_fetch_array($barang_list)) {
                                                                    $selected = ($barang['id_barang'] == $id_barang) ? "selected" : "";
                                                                    echo "<option value='" . htmlspecialchars($barang['id_barang'], ENT_QUOTES, 'UTF-8') . "' $selected>" . htmlspecialchars($barang['nama_barang'], ENT_QUOTES, 'UTF-8') . "</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                            <br>
                                                            <label>Nama Supplier:</label>
                                                            <select name="Edit_nama_supplier" class="form-control" required>
                                                                <?php
                                                                $supplier_list = mysqli_query($conn, "SELECT * FROM supplier");
                                                                while ($supplier = mysqli_fetch_array($supplier_list)) {
                                                                    $selected = ($supplier['id_supplier'] == $data['id_supplier']) ? "selected" : "";
                                                                    echo "<option value='" . htmlspecialchars($supplier['id_supplier'], ENT_QUOTES, 'UTF-8') . "' $selected>" . htmlspecialchars($supplier['nama_supplier'], ENT_QUOTES, 'UTF-8') . "</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                            <br>
                                                            <label>Lokasi Ruangan:</label>
                                                            <select name="Edit_nama_ruangan" class="form-control" required>
                                                                <?php
                                                                $ruangan_list = mysqli_query($conn, "SELECT * FROM ruangan");
                                                                while ($ruangan = mysqli_fetch_array($ruangan_list)) {
                                                                    $selected = ($ruangan['id_ruangan'] == $data['id_ruangan']) ? "selected" : "";
                                                                    echo "<option value='" . htmlspecialchars($ruangan['id_ruangan'], ENT_QUOTES, 'UTF-8') . "' $selected>" . htmlspecialchars($ruangan['nama_ruangan'], ENT_QUOTES, 'UTF-8') . "</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                            <br>
                                                            <label>Jumlah:</label>
                                                            <input type="number" name="Edit_jumlah" value="<?php echo htmlspecialchars($jumlah, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" min="1" required>
                                                            <br>
                                                            <label>Tanggal Masuk:</label>
                                                            <input type="date" name="Edit_tanggal_masuk" value="<?php echo htmlspecialchars($data['tanggal_masuk'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                            <button type="submit" name="SimpanEditpengadaan" class="btn btn-primary">Simpan</button>
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

    <!-- Add Modal -->
    <div class="modal fade" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Pengadaan Barang</h4>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <label>Nama Barang:</label>
                        <select name="barang" class="form-control" required>
                            <?php
                            $ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM barang");
                            while ($fetcharray = mysqli_fetch_array($ambilsemuadatanya)) {
                                $nama_barang = htmlspecialchars($fetcharray['nama_barang'], ENT_QUOTES, 'UTF-8');
                                $id_barang = htmlspecialchars($fetcharray['id_barang'], ENT_QUOTES, 'UTF-8');
                            ?>
                                <option value="<?= $id_barang; ?>"><?= $nama_barang; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                        <br>
                        <label>Nama Supplier:</label>
                        <select name="supplier" class="form-control" required>
                            <?php
                            $ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM supplier");
                            while ($fetcharray = mysqli_fetch_array($ambilsemuadatanya)) {
                                $nama_supplier = htmlspecialchars($fetcharray['nama_supplier'], ENT_QUOTES, 'UTF-8');
                                $id_supplier = htmlspecialchars($fetcharray['id_supplier'], ENT_QUOTES, 'UTF-8');
                            ?>
                                <option value="<?= $id_supplier; ?>"><?= $nama_supplier; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                        <br>
                        <label>Lokasi Ruangan:</label>
                        <select name="ruangan" class="form-control" required>
                            <?php
                            $ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM ruangan");
                            while ($fetcharray = mysqli_fetch_array($ambilsemuadatanya)) {
                                $nama_ruangan = htmlspecialchars($fetcharray['nama_ruangan'], ENT_QUOTES, 'UTF-8');
                                $id_ruangan = htmlspecialchars($fetcharray['id_ruangan'], ENT_QUOTES, 'UTF-8');
                            ?>
                                <option value="<?= $id_ruangan; ?>"><?= $nama_ruangan; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                        <br>
                        <label>Jumlah:</label>
                        <input type="number" name="jumlah" placeholder="Masukkan jumlah" class="form-control" min="1" required>
                        <br>
                        <label>Tanggal Masuk:</label>
                        <input type="date" name="tanggal_masuk" placeholder="Pilih tanggal masuk" class="form-control" required>
                        <br>
                        <button type="submit" class="btn btn-primary" name="addpengadaan_barang">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/chart-area-demo.js"></script>
    <script src="assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>
</body>

</html>