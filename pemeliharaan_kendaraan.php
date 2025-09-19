<?php
include 'sidebar.php';
include_once "koneksi.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Pemeliharaan Kendaraan</h1>
                <div class="card mb-4">
                    <div class="card-header">
                        <?php if ($data_level == 'admin') : ?>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addModal">
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
                                        <th>Nama Pegawai</th>
                                        <th>Nomor Polisi</th>
                                        <th>Tanggal</th>
                                        <th>Keterangan</th>
                                        <th>Biaya</th>
                                        <th>Opsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT pk.*, ik.nomor_polisi, p.nama_pegawai
                                              FROM pemeliharaan_kendaraan pk 
                                              JOIN inventaris_kendaraan ik ON pk.id_inventaris_kendaraan = ik.id_inventaris_kendaraan
                                              JOIN pegawai p ON ik.id_pegawai = p.id_pegawai";
                                    $result = mysqli_query($conn, $query);
                                    $i = 1;
                                    while ($data = mysqli_fetch_array($result)) {
                                        $id_pemeliharaan = $data['id'];
                                        $pegawai = $data['nama_pegawai'];
                                        $nomor_polisi = $data['nomor_polisi'];
                                        $tanggal = $data['tanggal'];
                                        $keterangan = $data['keterangan'];
                                        $biaya = $data['biaya'];
                                    ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td><?= htmlspecialchars($pegawai) ?></td>
                                            <td><?= htmlspecialchars($nomor_polisi) ?></td>
                                            <td><?= htmlspecialchars($tanggal) ?></td>
                                            <td><?= htmlspecialchars($keterangan) ?></td>
                                            <td><?= htmlspecialchars(number_format($biaya, 2, ',', '.')) ?></td>
                                            <td>
                                                <?php if ($data_level == 'admin') : ?>
                                                    <button type="button" data-toggle="modal" data-target="#editModal<?= $id_pemeliharaan; ?>" class="btn btn-success btn-sm mr-2">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                    <a href="proses_hapus/pemeliharaan_kendaraan.php?id_pemeliharaan=<?= $id_pemeliharaan; ?>" class="btn btn-danger btn-sm hapus-btn" data-id="<?= $id_pemeliharaan; ?>">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?= $id_pemeliharaan; ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Edit Pemeliharaan Kendaraan</h4>
                                                        <button type="button" class="close" data-dismiss="modal">×</button>
                                                    </div>
                                                    <form method="post">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id_pemeliharaan" value="<?= $id_pemeliharaan; ?>">
                                                            <label>Nomor Polisi:</label>
                                                            <select name="id_inventaris_kendaraan" class="form-control" required>
                                                                <?php
                                                                $kendaraan_list = mysqli_query($conn, "SELECT * FROM inventaris_kendaraan");
                                                                while ($kendaraan = mysqli_fetch_array($kendaraan_list)) {
                                                                    $selected = ($kendaraan['id_inventaris_kendaraan'] == $data['id_inventaris_kendaraan']) ? "selected" : "";
                                                                    echo "<option value='" . $kendaraan['id_inventaris_kendaraan'] . "' $selected>" . htmlspecialchars($kendaraan['nomor_polisi']) . "</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                            <br>
                                                            <label>Tanggal:</label>
                                                            <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal); ?>" class="form-control" required>
                                                            <br>
                                                            <label>Keterangan:</label>
                                                            <textarea name="keterangan" class="form-control" required><?= htmlspecialchars($keterangan); ?></textarea>
                                                            <br>
                                                            <label>Biaya:</label>
                                                            <input type="number" step="0.01" name="biaya" value="<?= htmlspecialchars($biaya); ?>" class="form-control" required>
                                                            <br>
                                                            <button type="submit" class="btn btn-primary" name="edit_pemeliharaan_kendaraan">Simpan</button>
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
                    <div class="text-muted">Copyright © Your Website 2023</div>
                    <div>
                        <a href="#">Privacy Policy</a>
                        ·
                        <a href="#">Terms & Conditions</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Pemeliharaan Kendaraan</h4>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <label>Nomor Polisi:</label>
                        <select name="id_inventaris_kendaraan" class="form-control" required>
                            <?php
                            $kendaraan_list = mysqli_query($conn, "SELECT * FROM inventaris_kendaraan");
                            while ($kendaraan = mysqli_fetch_array($kendaraan_list)) {
                                echo "<option value='" . $kendaraan['id_inventaris_kendaraan'] . "'>" . htmlspecialchars($kendaraan['nomor_polisi']) . "</option>";
                            }
                            ?>
                        </select>
                        <br>
                        <label>Tanggal:</label>
                        <input type="date" name="tanggal" class="form-control" required>
                        <br>
                        <label>Keterangan:</label>
                        <textarea name="keterangan" class="form-control" required></textarea>
                        <br>
                        <label>Biaya:</label>
                        <input type="number" step="0.01" name="biaya" class="form-control" required>
                        <br>
                        <button type="submit" class="btn btn-primary" name="add_pemeliharaan_kendaraan">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php
    // Add Functionality
    if (isset($_POST['add_pemeliharaan_kendaraan'])) {
        $id_inventaris_kendaraan = htmlspecialchars($_POST['id_inventaris_kendaraan']);
        $tanggal = htmlspecialchars($_POST['tanggal']);
        $keterangan = htmlspecialchars($_POST['keterangan']);
        $biaya = htmlspecialchars($_POST['biaya']);

        $query = "INSERT INTO pemeliharaan_kendaraan (id_inventaris_kendaraan, tanggal, keterangan, biaya) 
                  VALUES ('" . mysqli_real_escape_string($conn, $id_inventaris_kendaraan) . "', 
                          '" . mysqli_real_escape_string($conn, $tanggal) . "', 
                          '" . mysqli_real_escape_string($conn, $keterangan) . "', 
                          '" . mysqli_real_escape_string($conn, $biaya) . "')";
        $result = mysqli_query($conn, $query);
        if ($result) {
            echo '<script>alert("Data berhasil ditambahkan!"); window.location.href = window.location.href;</script>';
        } else {
            echo '<script>alert("Gagal Tambah Data Pemeliharaan Kendaraan"); window.location.reload();</script>';
        }
    }

    // Edit Functionality
    if (isset($_POST['edit_pemeliharaan_kendaraan'])) {
        $id_pemeliharaan = htmlspecialchars($_POST['id_pemeliharaan']);
        $id_inventaris_kendaraan = htmlspecialchars($_POST['id_inventaris_kendaraan']);
        $tanggal = htmlspecialchars($_POST['tanggal']);
        $keterangan = htmlspecialchars($_POST['keterangan']);
        $biaya = htmlspecialchars($_POST['biaya']);

        $query = "UPDATE pemeliharaan_kendaraan 
                  SET id_inventaris_kendaraan = '" . mysqli_real_escape_string($conn, $id_inventaris_kendaraan) . "', 
                      tanggal = '" . mysqli_real_escape_string($conn, $tanggal) . "', 
                      keterangan = '" . mysqli_real_escape_string($conn, $keterangan) . "', 
                      biaya = '" . mysqli_real_escape_string($conn, $biaya) . "' 
                  WHERE id = '" . mysqli_real_escape_string($conn, $id_pemeliharaan) . "'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            echo '<script>alert("Data berhasil diperbarui!"); window.location.href = window.location.href;</script>';
        } else {
            echo '<script>alert("Gagal Edit Data Pemeliharaan Kendaraan"); window.location.reload();</script>';
        }
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/chart-area-demo.js"></script>
    <script src="assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>