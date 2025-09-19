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
            <h1 class="mt-4">Ruangan</h1>
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
                                    <th>Nama Ruangan</th>
                                    <th>Opsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $ambilsemuadatanya = mysqli_query($conn,"SELECT * FROM ruangan");
                                $i = 1;
                                while($data=mysqli_fetch_array($ambilsemuadatanya)){
                                    $id_ruangan = $data['id_ruangan'];
                                    $nama_ruangan = $data['nama_ruangan'];
                                    if (!empty($nama_ruangan)) {
                                ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?=$nama_ruangan?></td>
                                    <td>
                                        <button type="button" data-toggle="modal" data-target="#Edit-ruangan<?php echo $id_ruangan; ?>" 
                                            class="btn btn-success btn-sm mr-2"> 
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <a href="#" class="btn btn-danger btn-sm hapus-btn" data-id="<?php echo $data['id_ruangan']; ?>">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                    </td>
                                </tr>
                                <!-- Modal Edit Ruangan -->
                                <div class="modal fade" id="Edit-ruangan<?php echo $id_ruangan; ?>" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content border-0">
                                            <form method="post">
                                                <div class="modal-header bg-purple">
                                                    <h5 class="modal-title text-black">Edit Ruangan</h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label class="small">Nama Ruangan:</label>
                                                        <input type="hidden" name="Edit_id_ruangan" value="<?php echo $id_ruangan; ?>">
                                                        <input type="text" name="Edit_nama_ruangan" value="<?php echo $nama_ruangan; ?>" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" name="SimpanEditruangan" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                    }
                                } ?>
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
                <div class="text-muted">Copyright &copy; Your Website 2023</div>
                <div>
                    <a href="#">Privacy Policy</a>
                    &middot;
                    <a href="#">Terms &amp; Conditions</a>
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
                <h4 class="modal-title">Tambah Ruangan</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <!-- Modal body -->
            <form method="post" action="">
                <div class="modal-body">
                    <input type="text" name="nama_ruangan" placeholder="nama ruangan" class="form-control" required>
                    <br>
                    <button type="submit" class="btn btn-primary" name="addruangan">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
if (isset($_POST['addruangan'])) {
    $nama_ruangan = htmlspecialchars($_POST['nama_ruangan']);
    if (!empty($nama_ruangan)) {
        $query = "INSERT INTO ruangan (nama_ruangan) VALUES ('$nama_ruangan')";
        $result = mysqli_query($conn, $query);

        if ($result) {
            echo "<script>
                    alert('Data berhasil ditambahkan');
                    window.location.href=window.location.href;
                  </script>";
        } else {
            echo '<script>alert("Gagal Tambah Data Ruangan");history.go(-1);</script>';
        }
    } else {
        echo '<script>alert("Nama ruangan tidak boleh kosong");history.go(-1);</script>';
    }
}

if (isset($_POST['SimpanEditruangan'])) {
    $id_ruangan = htmlspecialchars($_POST['Edit_id_ruangan']);
    $nama_ruangan = htmlspecialchars($_POST['Edit_nama_ruangan']);

    if (!empty($nama_ruangan)) {
        $query = "UPDATE ruangan SET nama_ruangan='$nama_ruangan' WHERE id_ruangan='$id_ruangan'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            echo '<script>alert("Data berhasil diedit!"); window.location.href = window.location.href;</script>';
        } else {
            echo '<script>alert("Gagal Edit Data Ruangan");history.go(-1);</script>';
        }
    } else {
        echo '<script>alert("Nama ruangan tidak boleh kosong");history.go(-1);</script>';
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
                    url: 'proses_hapus/ruangan.php',
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