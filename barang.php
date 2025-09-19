<?php
include 'sidebar.php';
include_once "koneksi.php";

// Fungsi untuk menghasilkan QR code
function generateQRCode($id_barang)
{
    require_once 'phpqrcode/qrlib.php';
    $base_url = "http://localhost/nina/";
    $text = $base_url . "detail_barang.php?id=" . urlencode($id_barang);
    $qrcode_file = "Uploads/qr_" . uniqid() . ".png";
    QRcode::png($text, $qrcode_file, QR_ECLEVEL_L, 4);
    return $qrcode_file;
}

// Tambah Barang
if (isset($_POST['addbarang'])) {
    $nama_barang = htmlspecialchars($_POST['nama_barang']);
    $kode = htmlspecialchars($_POST['kode']);
    $spesifikasi = trim($_POST['spesifikasi'] ?? '');
    $stok_awal = htmlspecialchars($_POST['stok_awal']);
    $stok = $stok_awal; // Stok awal menjadi stok tersedia
    $stok_akhir = $stok_awal; // Stok akhir mengikuti stok awal saat tambah
    $stok_dipinjam = 0;

    // Validasi input
    if (!is_numeric($stok_awal) || $stok_awal < 0) {
        echo '<script>alert("Stok awal harus berupa angka positif!"); window.location.href = window.location.href;</script>';
        exit;
    }
    if ($spesifikasi === '') {
        echo '<script>alert("Spesifikasi barang wajib diisi!"); window.location.href = window.location.href;</script>';
        exit;
    }

    // Handle upload gambar
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $gambar = uniqid() . '.' . pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $target_dir = "Uploads/";
        $target_file = $target_dir . $gambar;
        move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file);
    }

    // Generate QR code dengan ID sementara (akan diperbarui setelah INSERT)
    $temp_id = uniqid(); // ID sementara untuk QR code
    $qrcode = generateQRCode($temp_id);

    // Insert barang dengan QR code
    $query = "INSERT INTO barang (kode, nama_barang, spesifikasi, stok_awal, stok_akhir, stok_dipinjam, stok, gambar, qrcode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssiiiiss", $kode, $nama_barang, $spesifikasi, $stok_awal, $stok_akhir, $stok_dipinjam, $stok, $gambar, $qrcode);

    if ($stmt->execute()) {
        $id_barang = $conn->insert_id; // Ambil ID barang yang baru ditambahkan
        // Generate QR code baru dengan id_barang yang benar
        if (file_exists($qrcode)) {
            unlink($qrcode); // Hapus QR code sementara
        }
        $qrcode = generateQRCode($id_barang);
        // Update QR code dengan ID yang benar
        $update_query = "UPDATE barang SET qrcode = ? WHERE id_barang = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $qrcode, $id_barang);
        $update_stmt->execute();
        $update_stmt->close();
        echo '<script>alert("Data barang berhasil ditambahkan!"); window.location.href = window.location.href;</script>';
    } else {
        echo '<script>alert("Gagal menambahkan data: ' . $conn->error . '"); window.location.href = window.location.href;</script>';
    }
    $stmt->close();
}

// Edit Barang
if (isset($_POST['SimpanEditBarang'])) {
    $id_barang = htmlspecialchars($_POST['Edit_id_barang']);
    $nama_barang = htmlspecialchars($_POST['Edit_nama_barang']);
    $kode = htmlspecialchars($_POST['Edit_kode']);
    $spesifikasi = trim($_POST['Edit_spesifikasi'] ?? '');
    $stok_awal = htmlspecialchars($_POST['Edit_stok_awal']);
    $stok_akhir = htmlspecialchars($_POST['Edit_stok_akhir']);
    $stok_dipinjam = htmlspecialchars($_POST['Edit_stok_dipinjam']);
    $stok = $stok_awal + ($stok_akhir - $stok_dipinjam); // Hitung stok baru

    // Validasi input
    if (!is_numeric($stok_awal) || $stok_awal < 0 || !is_numeric($stok_akhir) || $stok_akhir < 0 || !is_numeric($stok_dipinjam) || $stok_dipinjam < 0) {
        echo '<script>alert("Stok harus berupa angka positif!"); window.location.href = window.location.href;</script>';
        exit;
    }

    if ($spesifikasi === '') {
        echo '<script>alert("Spesifikasi barang wajib diisi!"); window.location.href = window.location.href;</script>';
        exit;
    }

    // Ambil data lama dari database
    $old_data = mysqli_fetch_array(mysqli_query($conn, "SELECT kode, nama_barang, spesifikasi, stok_awal, gambar, qrcode FROM barang WHERE id_barang = '$id_barang'"));
    $old_kode = $old_data['kode'];
    $old_nama_barang = $old_data['nama_barang'];
    $old_spesifikasi = $old_data['spesifikasi'];
    $old_stok_awal = $old_data['stok_awal'];
    $old_gambar = $old_data['gambar'];
    $old_qrcode = $old_data['qrcode'];

    // Handle upload gambar baru
    $gambar = htmlspecialchars($_POST['Edit_gambar_lama']);
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        // Hapus gambar lama jika ada
        if ($old_gambar && file_exists("Uploads/" . $old_gambar)) {
            unlink("Uploads/" . $old_gambar);
        }
        // Simpan gambar baru
        $gambar = uniqid() . '.' . pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $target_dir = "Uploads/";
        $target_file = $target_dir . $gambar;
        move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file);
    }

    // Generate QR code baru jika kode, nama barang, stok awal, atau gambar berubah
    $qrcode = $old_qrcode; // Default gunakan QR code lama
    if ($kode !== $old_kode || $nama_barang !== $old_nama_barang || $stok_awal != $old_stok_awal || $gambar !== $old_gambar || $spesifikasi !== $old_spesifikasi) {
        if ($old_qrcode && file_exists($old_qrcode)) {
            unlink($old_qrcode);
        }
        $qrcode = generateQRCode($id_barang);
    }

    $query = "UPDATE barang SET kode = ?, nama_barang = ?, spesifikasi = ?, stok_awal = ?, stok_akhir = ?, stok_dipinjam = ?, stok = ?, gambar = ?, qrcode = ? WHERE id_barang = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssiiiissi", $kode, $nama_barang, $spesifikasi, $stok_awal, $stok_akhir, $stok_dipinjam, $stok, $gambar, $qrcode, $id_barang);

    if ($stmt->execute()) {
        echo '<script>alert("Data barang berhasil diperbarui!"); window.location.href = window.location.href;</script>';
    } else {
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
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <title>Data Barang</title>
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
                <h1 class="mt-4">Data Barang</h1>
                <div class="card mb-4">
                    <div class="card-header">
                        <?php if ($data_level == 'admin') : ?>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">Tambah Data</button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode</th>
                                        <th>Gambar</th>
                                        <th>QR Code</th>
                                        <th>Nama Barang</th>
                                        <th>Spesifikasi</th>
                                        <th>Stok Awal</th>
                                        <th>Stok Dipinjam</th>
                                        <th>Stok Tersedia</th>
                                        <?php if ($data_level == 'admin') : ?>
                                            <th>Opsi</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM barang ORDER BY id_barang DESC");
                                    $i = 1;
                                    while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                        $id_barang = $data['id_barang'];
                                        $kode = $data['kode'];
                                        $nama_barang = $data['nama_barang'];
                                        $spesifikasi = $data['spesifikasi'];
                                        $stok_awal = $data['stok_awal'];
                                        $stok_akhir = $data['stok_akhir'];
                                        $stok_dipinjam = $data['stok_dipinjam'];
                                        $stok = $data['stok'];
                                        $gambar = $data['gambar'] ? $data['gambar'] : 'default.jpg';
                                        $qrcode = $data['qrcode'] ? $data['qrcode'] : 'default_qr.png';
                                    ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><?php echo htmlspecialchars($kode); ?></td>
                                            <td><img src="Uploads/<?php echo htmlspecialchars($gambar); ?>" alt="Gambar Barang" style="width: 50px; height: 50px;"></td>
                                            <td><img src="<?php echo htmlspecialchars($qrcode); ?>" alt="QR Code" style="width: 50px; height: 50px;"></td>
                                            <td><?php echo htmlspecialchars($nama_barang); ?></td>
                                            <td class="text-left"><?php echo nl2br(htmlspecialchars($spesifikasi)); ?></td>
                                            <td><?php echo htmlspecialchars($stok_awal); ?></td>
                                            <td><?php echo htmlspecialchars($stok_dipinjam); ?></td>
                                            <td><?php echo htmlspecialchars($stok); ?></td>
                                            <?php if ($data_level == 'admin') : ?>
                                                <td>
                                                    <button type="button" data-toggle="modal" data-target="#Edit-Barang<?php echo $id_barang; ?>"
                                                        class="btn btn-success btn-sm mr-2">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="#" class="btn btn-danger btn-sm hapus-btn" data-id="<?php echo $id_barang; ?>"><i class="fas fa-trash"></i></a>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                        <!-- Modal Edit Barang -->
                                        <div class="modal fade" id="Edit-Barang<?php echo $id_barang; ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content border-0">
                                                    <form method="post" enctype="multipart/form-data">
                                                        <div class="modal-header bg-purple">
                                                            <h5 class="modal-title text-white">Edit Barang</h5>
                                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">×</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label class="small">Kode:</label>
                                                                <input type="hidden" name="Edit_id_barang" value="<?php echo $id_barang; ?>">
                                                                <input type="hidden" name="Edit_kode_lama" value="<?php echo $kode; ?>">
                                                                <input type="text" name="Edit_kode" value="<?php echo htmlspecialchars($kode); ?>" class="form-control" required>
                                                            </div>
                                                        <div class="form-group">
                                                            <label class="small">Nama Barang:</label>
                                                            <input type="hidden" name="Edit_nama_barang_lama" value="<?php echo $nama_barang; ?>">
                                                            <input type="text" name="Edit_nama_barang" value="<?php echo htmlspecialchars($nama_barang); ?>" class="form-control" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="small">Spesifikasi:</label>
                                                            <textarea name="Edit_spesifikasi" class="form-control" rows="3" placeholder="Detail spesifikasi barang" required><?php echo htmlspecialchars($spesifikasi); ?></textarea>
                                                        </div>
                                                            <div class="form-group">
                                                                <label class="small">Stok Awal:</label>
                                                                <input type="number" name="Edit_stok_awal" value="<?php echo htmlspecialchars($stok_awal); ?>" class="form-control" min="0" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="small">Stok Akhir:</label>
                                                                <input type="number" name="Edit_stok_akhir" value="<?php echo htmlspecialchars($stok_akhir); ?>" class="form-control" min="0" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="small">Stok Dipinjam:</label>
                                                                <input type="number" name="Edit_stok_dipinjam" value="<?php echo htmlspecialchars($stok_dipinjam); ?>" class="form-control" min="0" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="small">Gambar Lama:</label>
                                                                <input type="hidden" name="Edit_gambar_lama" value="<?php echo $gambar; ?>">
                                                                <?php if ($gambar && file_exists("Uploads/" . $gambar)) : ?>
                                                                    <img src="Uploads/<?php echo htmlspecialchars($gambar); ?>" alt="Gambar Lama" style="width: 100px; height: 100px;">
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="small">Gambar Baru:</label>
                                                                <input type="file" name="gambar" class="form-control-file">
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="small">QR Code Lama:</label>
                                                                <img src="<?php echo htmlspecialchars($qrcode); ?>" alt="QR Code Lama" style="width: 100px; height: 100px;">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                            <button type="submit" name="SimpanEditBarang" class="btn btn-primary">Simpan</button>
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

    <!-- The Modal -->
    <div class="modal fade" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header bg-purple">
                    <h4 class="modal-title text-white">Tambah Barang</h4>
                    <button type="button" class="close text-white" data-dismiss="modal">×</button>
                </div>
                <!-- Modal body -->
                <form method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="small">Kode:</label>
                            <input type="text" name="kode" placeholder="Kode Barang" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="small">Nama Barang:</label>
                            <input type="text" name="nama_barang" placeholder="Nama Barang" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="small">Spesifikasi:</label>
                            <textarea name="spesifikasi" class="form-control" rows="3" placeholder="Contoh: Merk, tipe, ukuran, kondisi" required></textarea>
                            <small class="form-text text-muted">Cantumkan informasi detail agar berita acara dan laporan lebih akurat.</small>
                        </div>
                        <div class="form-group">
                            <label class="small">Stok Awal:</label>
                            <input type="number" name="stok_awal" placeholder="Stok Awal" class="form-control" min="0" required>
                        </div>
                        <input type="hidden" name="stok_dipinjam" placeholder="Stok Dipinjam" class="form-control" min="0" value="0" required>
                        <div class="form-group">
                            <label class="small">Gambar:</label>
                            <input type="file" name="gambar" class="form-control-file">
                        </div>
                        <button type="submit" class="btn btn-primary" name="addbarang">Submit</button>
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
                var id_barang = $(this).data('id');
                if (confirm('Apakah anda yakin ingin menghapus data ini?')) {
                    $.ajax({
                        url: 'proses_hapus/barang.php', // Sesuaikan dengan file hapus
                        type: 'POST',
                        data: {
                            hapus: id_barang
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