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
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Supplier</h1>
            <div class="card mb-4">
                <div class="card-header">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">Tambah Data</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Supplier</th>
                                    <th>No Telepon</th>
                                    <th>Alamat</th>
                                    <th>Opsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $ambilsemuadata = mysqli_query($conn, "SELECT * FROM supplier");
                                $i = 1;
                                while ($data = mysqli_fetch_array($ambilsemuadata)) {
                                    $id_supplier = $data['id_supplier'];
                                    $nama_supplier = $data['nama_supplier'];
                                    $no_telepon = $data['no_telepon'];
                                    $alamat = $data['alamat'];
                                ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo $nama_supplier; ?></td>
                                    <td><?php echo $no_telepon; ?></td>
                                    <td><?php echo $alamat; ?></td>
                                    <td>
                                        <button type="button" data-toggle="modal" data-target="#Edit-Supplier<?php echo $id_supplier; ?>" 
                                            class="btn btn-success btn-sm mr-2"> 
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <a href="#" class="btn btn-danger btn-sm hapus-btn" data-id="<?php echo $id_supplier; ?>">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                    </td>
                                </tr>
                                <!-- Modal Edit Supplier -->
                                <div class="modal fade" id="Edit-Supplier<?php echo $id_supplier; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="post">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Supplier</h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="Edit_id_supplier" value="<?php echo $id_supplier; ?>">
                                                    <label>Nama Supplier:</label>
                                                    <input type="text" name="Edit_nama_supplier" value="<?php echo $nama_supplier; ?>" class="form-control" required>
                                                    <label>No Telepon:</label>
                                                    <input type="text" name="Edit_no_telepon" value="<?php echo $no_telepon; ?>" class="form-control" required>
                                                    <label>Alamat:</label>
                                                    <textarea name="Edit_alamat" class="form-control" required><?php echo $alamat; ?></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" name="SimpanEditSupplier" class="btn btn-primary">Simpan</button>
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
</div>

<!-- Modal Tambah Supplier -->
<div class="modal fade" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Supplier</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="text" name="nama_supplier" placeholder="Nama Supplier" class="form-control" required>
                    <input type="text" name="no_telepon" placeholder="No Telepon" class="form-control" required>
                    <textarea name="alamat" placeholder="Alamat" class="form-control" required></textarea>
                    <br>
                    <button type="submit" class="btn btn-primary" name="addsupplier">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
if (isset($_POST['SimpanEditSupplier'])) {
    $id_supplier = htmlspecialchars($_POST['Edit_id_supplier']);
    $nama_supplier = htmlspecialchars($_POST['Edit_nama_supplier']);
    $no_telepon = htmlspecialchars($_POST['Edit_no_telepon']);
    $alamat = htmlspecialchars($_POST['Edit_alamat']);

    $query = "UPDATE supplier SET nama_supplier='$nama_supplier', no_telepon='$no_telepon', alamat='$alamat' WHERE id_supplier='$id_supplier'";
    if (mysqli_query($conn, $query)) {
        echo '<script>alert("Data berhasil diedit!"); window.location.href = window.location.href;</script>';
    } else {
        echo '<script>alert("Gagal Edit Data Supplier");history.go(-1);</script>';
    }
}
if (isset($_POST['addsupplier'])) {
    $nama_supplier = htmlspecialchars($_POST['nama_supplier']);
    $no_telepon = htmlspecialchars($_POST['no_telepon']);
    $alamat = htmlspecialchars($_POST['alamat']);

    $query = "INSERT INTO supplier (nama_supplier, no_telepon, alamat) VALUES ('$nama_supplier', '$no_telepon', '$alamat')";
    if (mysqli_query($conn, $query)) {
        echo "<script>
                alert('Data berhasil ditambahkan');
                window.location.href=window.location.href;
            </script>";
    } else {
        echo '<script>alert("Gagal Edit Data Supplier");history.go(-1);</script>';
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
</body>
</html>
<script>
    $(document).ready(function() {
        $(document).on('click', '.hapus-btn', function(e) {
            e.preventDefault();
            var id_pemeliharaan = $(this).data('id');
            if (confirm('Apakah anda yakin ingin menghapus data ini?')) {
                $.ajax({
                    url: 'proses_hapus/supplier.php',
                    type: 'POST',
                    data: {
                        hapus: id_pemeliharaan
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