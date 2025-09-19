<?php
include 'sidebar.php';
include_once "koneksi.php";

// Mulai transaksi
mysqli_begin_transaction($conn);

// Fungsi untuk menghasilkan QR code
function generateQRCode($id_inventaris_kendaraan)
{
    require_once 'phpqrcode/qrlib.php';
    $base_url = "http://localhost/nina/skripsi/nina/";
    $text = $base_url . "detail_kendaraan.php?id=" . urlencode($id_inventaris_kendaraan);
    $qrcode_file = "uploads/qrcode_" . uniqid() . ".png";
    QRcode::png($text, $qrcode_file, QR_ECLEVEL_L, 4);
    return $qrcode_file;
}

// Add Functionality
if (isset($_POST['addinventaris_kendaraan'])) {
    $id_pegawai = htmlspecialchars($_POST['pegawai']);
    $nomor_rangka = htmlspecialchars($_POST['nomor_rangka']);
    $jumlah = htmlspecialchars($_POST['jumlah']);
    $tanggal_masuk = htmlspecialchars($_POST['tanggal_masuk']);
    $nomor_polisi = htmlspecialchars($_POST['nomor_polisi']);
    $nomor_bpkb = htmlspecialchars($_POST['nomor_bpkb']);
    $roda = htmlspecialchars($_POST['roda']);
    $nama_kendaraan = htmlspecialchars($_POST['nama_kendaraan']);

    // Validasi input
    if (!is_numeric($jumlah) || $jumlah < 0 || !is_numeric($roda) || $roda < 0) {
        mysqli_rollback($conn);
        echo '<script>alert("Jumlah dan roda harus berupa angka positif!"); window.location.href = window.location.href;</script>';
        exit;
    }

    // Handle gambar
    $gambar_new_name = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $gambar = $_FILES['gambar']['name'];
        $tmp_gambar = $_FILES['gambar']['tmp_name'];
        $gambar_ext = pathinfo($gambar, PATHINFO_EXTENSION);
        $gambar_new_name = "kendaraan_" . uniqid() . "." . $gambar_ext;
        $gambar_path = "uploads/" . $gambar_new_name;
        move_uploaded_file($tmp_gambar, $gambar_path);
    }

    // Generate QR code dengan ID sementara
    $temp_id = uniqid();
    $qrcode = generateQRCode($temp_id);

    // Insert data ke tabel inventaris_kendaraan
    $query = "INSERT INTO inventaris_kendaraan (id_pegawai, nomor_rangka, jumlah, tanggal_masuk, nomor_polisi, nomor_bpkb, roda, qrcode, gambar, nama_kendaraan) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isisssssss", $id_pegawai, $nomor_rangka, $jumlah, $tanggal_masuk, $nomor_polisi, $nomor_bpkb, $roda, $qrcode, $gambar_new_name, $nama_kendaraan);

    if ($stmt->execute()) {
        $id_inventaris_kendaraan = $conn->insert_id; // Ambil ID baru
        // Generate QR code baru dengan ID yang benar
        if (file_exists($qrcode)) {
            unlink($qrcode); // Hapus QR code sementara
        }
        $qrcode = generateQRCode($id_inventaris_kendaraan);
        // Update QR code
        $update_query = "UPDATE inventaris_kendaraan SET qrcode = ? WHERE id_inventaris_kendaraan = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $qrcode, $id_inventaris_kendaraan);
        $update_stmt->execute();
        $update_stmt->close();
        mysqli_commit($conn);
        echo '<script>alert("Data berhasil ditambahkan!"); window.location.href = window.location.href;</script>';
    } else {
        if (file_exists($qrcode)) {
            unlink($qrcode); // Hapus QR code jika gagal
        }
        mysqli_rollback($conn);
        echo '<script>alert("Gagal menambahkan data: ' . $conn->error . '"); window.location.href = window.location.href;</script>';
    }
    $stmt->close();
}

// Edit Functionality
if (isset($_POST['edit_inventaris_kendaraan'])) {
    $id = htmlspecialchars($_POST['id']);
    $id_pegawai = htmlspecialchars($_POST['pegawai']);
    $nomor_rangka = htmlspecialchars($_POST['nomor_rangka']);
    $jumlah = htmlspecialchars($_POST['jumlah']);
    $tanggal_masuk = htmlspecialchars($_POST['tanggal_masuk']);
    $nomor_polisi = htmlspecialchars($_POST['nomor_polisi']);
    $nomor_bpkb = htmlspecialchars($_POST['nomor_bpkb']);
    $roda = htmlspecialchars($_POST['roda']);
    $nama_kendaraan = htmlspecialchars($_POST['nama_kendaraan']);

    // Validasi input
    if (!is_numeric($jumlah) || $jumlah < 0 || !is_numeric($roda) || $roda < 0) {
        mysqli_rollback($conn);
        echo '<script>alert("Jumlah dan roda harus berupa angka positif!"); window.location.href = window.location.href;</script>';
        exit;
    }

    // Ambil data lama
    $cekQuery = "SELECT id_pegawai, nomor_rangka, nomor_polisi, nama_kendaraan, gambar, qrcode FROM inventaris_kendaraan WHERE id_inventaris_kendaraan = ?";
    $cekStmt = $conn->prepare($cekQuery);
    $cekStmt->bind_param("i", $id);
    $cekStmt->execute();
    $cekResult = $cekStmt->get_result();
    $dataLama = $cekResult->fetch_assoc();
    $old_id_pegawai = $dataLama['id_pegawai'];
    $old_nomor_rangka = $dataLama['nomor_rangka'];
    $old_nomor_polisi = $dataLama['nomor_polisi'];
    $old_nama_kendaraan = $dataLama['nama_kendaraan'];
    $old_gambar = $dataLama['gambar'];
    $old_qrcode = $dataLama['qrcode'];
    $cekStmt->close();

    // Handle gambar
    $gambar_new_name = $old_gambar;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $gambar = $_FILES['gambar']['name'];
        $tmp_gambar = $_FILES['gambar']['tmp_name'];
        $gambar_ext = pathinfo($gambar, PATHINFO_EXTENSION);
        $gambar_new_name = "kendaraan_" . uniqid() . "." . $gambar_ext;
        $gambar_path = "uploads/" . $gambar_new_name;
        move_uploaded_file($tmp_gambar, $gambar_path);
        // Hapus gambar lama jika ada
        if ($old_gambar && file_exists("uploads/" . $old_gambar)) {
            unlink("uploads/" . $old_gambar);
        }
    }

    // Generate QR code baru jika data relevan berubah
    $qrcode = $old_qrcode;
    if ($nama_kendaraan !== $old_nama_kendaraan || $nomor_polisi !== $old_nomor_polisi || $nomor_rangka !== $old_nomor_rangka || $gambar_new_name !== $old_gambar) {
        if ($old_qrcode && file_exists($old_qrcode)) {
            unlink($old_qrcode);
        }
        $qrcode = generateQRCode($id);
    }

    $query = "UPDATE inventaris_kendaraan SET id_pegawai = ?, nomor_rangka = ?, jumlah = ?, tanggal_masuk = ?, nomor_polisi = ?, nomor_bpkb = ?, roda = ?, qrcode = ?, gambar = ?, nama_kendaraan = ? WHERE id_inventaris_kendaraan = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isisssssssi", $id_pegawai, $nomor_rangka, $jumlah, $tanggal_masuk, $nomor_polisi, $nomor_bpkb, $roda, $qrcode, $gambar_new_name, $nama_kendaraan, $id);

    if ($stmt->execute()) {
        mysqli_commit($conn);
        echo '<script>alert("Data berhasil diperbarui!"); window.location.href = window.location.href;</script>';
    } else {
        mysqli_rollback($conn);
        echo '<script>alert("Gagal memperbarui data: ' . $conn->error . '"); window.location.href = window.location.href;</script>';
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <title>Inventaris Kendaraan</title>
    <style>
        .table img {
            border-radius: 4px;
            object-fit: cover;
        }

        /* Modal Header Styling */
        .modal-header.bg-purple {
            background-color: #6f42c1;
            color: #fff;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .table-responsive .table {
                font-size: 13px;
            }

            .table thead th,
            .table tbody td {
                padding: 8px;
            }

            .table img {
                width: 40px;
                height: 40px;
            }
        }
    </style>
</head>

<body>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Inventaris Kendaraan</h1>
                <div class="card mb-4">
                    <div class="card-header">
                        <?php if ($data_level == 'admin' || $data_level == 'auditor') : ?>
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
                                        <th>Nama Kendaraan</th>
                                        <th>Nomor Rangka</th>
                                        <th>Jumlah</th>
                                        <th>Tanggal Masuk</th>
                                        <th>Nomor Polisi</th>
                                        <th>Nomor BPKB</th>
                                        <th>Roda</th>
                                        <th>QR Code</th>
                                        <th>Gambar</th>
                                        <?php if ($data_level == 'admin' || $data_level == 'auditor') : ?>
                                            <th>Opsi</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT * FROM inventaris_kendaraan JOIN pegawai ON inventaris_kendaraan.id_pegawai = pegawai.id_pegawai ORDER BY id_inventaris_kendaraan DESC";
                                    $result = mysqli_query($conn, $query);
                                    $i = 1;
                                    while ($data = mysqli_fetch_array($result)) {
                                        $gambar = $data['gambar'] ? $data['gambar'] : 'default.jpg';
                                        $qrcode = $data['qrcode'] ? $data['qrcode'] : 'default_qr.png';
                                    ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td><?= htmlspecialchars($data['nama_pegawai']) ?></td>
                                            <td><?= htmlspecialchars($data['nama_kendaraan'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($data['nomor_rangka']) ?></td>
                                            <td><?= htmlspecialchars($data['jumlah']) ?></td>
                                            <td><?= htmlspecialchars($data['tanggal_masuk']) ?></td>
                                            <td><?= htmlspecialchars($data['nomor_polisi']) ?></td>
                                            <td><?= htmlspecialchars($data['nomor_bpkb']) ?></td>
                                            <td><?= htmlspecialchars($data['roda']) ?></td>
                                            <td><img src="<?= htmlspecialchars($qrcode); ?>" alt="QR Code" style="width: 50px; height: 50px;"></td>
                                            <td><img src="uploads/<?= htmlspecialchars($gambar); ?>" alt="Gambar" style="width: 50px; height: 50px;"></td>
                                            <?php if ($data_level == 'admin' || $data_level == 'auditor') : ?>
                                                <td>
                                                    <button type="button" data-toggle="modal" data-target="#editModal<?= $data['id_inventaris_kendaraan']; ?>" class="btn btn-success btn-sm mr-2">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="#" class="btn btn-danger btn-sm hapus-btn" data-id="<?= $data['id_inventaris_kendaraan']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?= $data['id_inventaris_kendaraan']; ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-purple">
                                                        <h4 class="modal-title text-white">Edit Inventaris Kendaraan</h4>
                                                        <button type="button" class="close text-white" data-dismiss="modal">×</button>
                                                    </div>
                                                    <form method="post" enctype="multipart/form-data">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?= $data['id_inventaris_kendaraan']; ?>">
                                                            <div class="form-group">
                                                                <label class="small">Nama Pegawai:</label>
                                                                <select name="pegawai" class="form-control" required>
                                                                    <?php
                                                                    $ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM pegawai");
                                                                    while ($fetcharray = mysqli_fetch_array($ambilsemuadatanya)) {
                                                                        $selected = ($fetcharray['id_pegawai'] == $data['id_pegawai']) ? "selected" : "";
                                                                        echo "<option value='" . htmlspecialchars($fetcharray['id_pegawai']) . "' $selected>" . htmlspecialchars($fetcharray['nama_pegawai']) . "</option>";
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="small">Nama Kendaraan:</label>
                                                                <input type="text" name="nama_kendaraan" value="<?= htmlspecialchars($data['nama_kendaraan']); ?>" class="form-control" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="small">Nomor Rangka:</label>
                                                                <input type="text" name="nomor_rangka" value="<?= htmlspecialchars($data['nomor_rangka']); ?>" class="form-control" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="small">Jumlah:</label>
                                                                <input type="number" name="jumlah" value="<?= htmlspecialchars($data['jumlah']); ?>" class="form-control" min="0" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="small">Tanggal Masuk:</label>
                                                                <input type="date" name="tanggal_masuk" value="<?= htmlspecialchars($data['tanggal_masuk']); ?>" class="form-control" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="small">Nomor Polisi:</label>
                                                                <input type="text" name="nomor_polisi" value="<?= htmlspecialchars($data['nomor_polisi']); ?>" class="form-control" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="small">Nomor BPKB:</label>
                                                                <input type="text" name="nomor_bpkb" value="<?= htmlspecialchars($data['nomor_bpkb']); ?>" class="form-control" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="small">Roda:</label>
                                                                <input type="number" name="roda" value="<?= htmlspecialchars($data['roda']); ?>" class="form-control" min="0" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="small">Gambar Lama:</label>
                                                                <?php if ($data['gambar'] && file_exists("uploads/" . $data['gambar'])) : ?>
                                                                    <img src="uploads/<?= htmlspecialchars($data['gambar']); ?>" alt="Gambar Lama" style="width: 100px; height: 100px;">
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="small">Gambar Baru:</label>
                                                                <input type="file" name="gambar" class="form-control-file">
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="small">QR Code Lama:</label>
                                                                <img src="<?= htmlspecialchars($data['qrcode']); ?>" alt="QR Code Lama" style="width: 100px; height: 100px;">
                                                            </div>
                                                            <button type="submit" class="btn btn-primary" name="edit_inventaris_kendaraan">Simpan</button>
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
                    <div class="text-muted">Copyright © Your Website 2025</div>
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
                <!-- Modal Header -->
                <div class="modal-header bg-purple">
                    <h4 class="modal-title text-white">Tambah Inventaris Kendaraan</h4>
                    <button type="button" class="close text-white" data-dismiss="modal">×</button>
                </div>
                <!-- Modal body -->
                <form method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="small">Nama Pegawai:</label>
                            <select name="pegawai" class="form-control" required>
                                <?php
                                $ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM pegawai");
                                while ($fetcharray = mysqli_fetch_array($ambilsemuadatanya)) {
                                    $nama_pegawai = htmlspecialchars($fetcharray['nama_pegawai'], ENT_QUOTES, 'UTF-8');
                                    $id_pegawai = htmlspecialchars($fetcharray['id_pegawai'], ENT_QUOTES, 'UTF-8');
                                ?>
                                    <option value="<?= $id_pegawai; ?>"><?= $nama_pegawai; ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="small">Nama Kendaraan:</label>
                            <input type="text" name="nama_kendaraan" placeholder="Nama Kendaraan" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="small">Nomor Rangka:</label>
                            <input type="text" name="nomor_rangka" placeholder="Nomor Rangka" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="small">Jumlah:</label>
                            <input type="number" name="jumlah" placeholder="Jumlah" class="form-control" min="0" required>
                        </div>
                        <div class="form-group">
                            <label class="small">Tanggal Masuk:</label>
                            <input type="date" name="tanggal_masuk" placeholder="Tanggal Masuk" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="small">Nomor Polisi:</label>
                            <input type="text" name="nomor_polisi" placeholder="Nomor Polisi" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="small">Nomor BPKB:</label>
                            <input type="text" name="nomor_bpkb" placeholder="Nomor BPKB" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="small">Roda:</label>
                            <input type="number" name="roda" placeholder="Roda" class="form-control" min="0" required>
                        </div>
                        <div class="form-group">
                            <label class="small">Gambar:</label>
                            <input type="file" name="gambar" class="form-control-file">
                        </div>
                        <button type="submit" class="btn btn-primary" name="addinventaris_kendaraan">Submit</button>
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
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $(document).on('click', '.hapus-btn', function(e) {
                e.preventDefault();
                var id_inventaris_kendaraan = $(this).data('id');
                if (confirm('Apakah anda yakin ingin menghapus data ini?')) {
                    $.ajax({
                        url: 'proses_hapus/inventaris_kenderaan.php', // Perbaiki typo 'kenderaan' menjadi 'kendaraan'
                        type: 'POST',
                        data: {
                            hapus: id_inventaris_kendaraan
                        },
                        success: function(response) {
                            alert(response);
                            location.reload();
                        },
                        error: function() {
                            alert('Terjadi kesalahan!');
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>