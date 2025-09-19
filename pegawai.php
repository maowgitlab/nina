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
            <h1 class="mt-4">Pegawai</h1>
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
                                    <th>NIP</th>
                                    <th>Nama Pegawai</th>
                                    <th>Jabatan</th>
                                    <th>Opsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $ambilsemuadata = mysqli_query($conn, "SELECT * FROM pegawai");
                                $i = 1;
                                while ($data = mysqli_fetch_array($ambilsemuadata)) {
                                    $id_pegawai = $data['id_pegawai'];
                                    $nip = $data['nip'];
                                    $nama_pegawai = $data['nama_pegawai'];
                                    $jabatan = $data['jabatan'];
                                ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo $nip; ?></td>
                                    <td><?php echo $nama_pegawai; ?></td>
                                    <td><?php echo $jabatan; ?></td>
                                    <td>
                                        <button type="button" data-toggle="modal" data-target="#Edit-Pegawai<?php echo $id_pegawai; ?>" 
                                            class="btn btn-success btn-sm mr-2"> 
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <a href="#" class="btn btn-danger btn-sm hapus-btn" data-id="<?php echo $data['id_pegawai']; ?>">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                    </td>
                                </tr>
                                <div class="modal fade" id="Edit-Pegawai<?php echo $id_pegawai; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="post">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Pegawai</h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="Edit_id_pegawai" value="<?php echo $id_pegawai; ?>">
                                                    <label>NIP:</label>
                                                    <input type="text" name="Edit_nip" value="<?php echo $nip; ?>" class="form-control" required>
                                                    <label>Nama Pegawai:</label>
                                                    <input type="text" name="Edit_nama_pegawai" value="<?php echo $nama_pegawai; ?>" class="form-control" required>
                                                    <label>Jabatan:</label>
                                                    <input type="text" name="Edit_jabatan" value="<?php echo $jabatan; ?>" class="form-control" required>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" name="SimpanEditPegawai" class="btn btn-primary">Simpan</button>
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

<div class="modal fade" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Pegawai</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="text" name="nip" placeholder="NIP" class="form-control" required>
                    <input type="text" name="nama_pegawai" placeholder="Nama Pegawai" class="form-control" required>
                    <input type="text" name="jabatan" placeholder="Jabatan" class="form-control" required>
                    <br>
                    <button type="submit" class="btn btn-primary" name="addpegawai">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
if (isset($_POST['SimpanEditPegawai'])) {
    $id_pegawai = htmlspecialchars($_POST['Edit_id_pegawai']);
    $nip = htmlspecialchars($_POST['Edit_nip']);
    $nama_pegawai = htmlspecialchars($_POST['Edit_nama_pegawai']);
    $jabatan = htmlspecialchars($_POST['Edit_jabatan']);

    $query = "UPDATE pegawai SET nip='$nip', nama_pegawai='$nama_pegawai', jabatan='$jabatan' WHERE id_pegawai='$id_pegawai'";
    if (mysqli_query($conn, $query)) {
        echo '<script>alert("Data berhasil diedit!"); window.location.href = window.location.href;</script>';
    } else {
        echo '<script>alert("Gagal Edit Data Pegawai");history.go(-1);</script>';
    }
}

if (isset($_POST['addpegawai'])) {
    $nip = htmlspecialchars($_POST['nip']);
    $nama_pegawai = htmlspecialchars($_POST['nama_pegawai']);
    $jabatan = htmlspecialchars($_POST['jabatan']);

    $query = "INSERT INTO pegawai (nip, nama_pegawai, jabatan) values('$nip','$nama_pegawai','$jabatan')";
    if (mysqli_query($conn, $query)) {
        echo '<script>alert("Data berhasil ditambahkan!"); window.location.href = window.location.href;</script>';
    } else {
        echo '<script>alert("Gagal Edit Data Pegawai");history.go(-1);</script>';
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
                    url: 'proses_hapus/pegawai.php',
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