<?php 
require_once "../koneksi.php";

$id = $_GET['id_pemeliharaan'];

$query = mysqli_query($conn, "DELETE FROM pemeliharaan_kendaraan WHERE id='$id'");
if ($query) {
    echo "<script>alert('Data pemeliharaan Kendaraan Berhasil Dihapus'); window.location.href = '../pemeliharaan_kendaraan.php';</script>";
} else {
    echo '<script>alert("Gagal Hapus Data pemeliharaan Kendaraan");</script>';
}
?>