<?php 
require_once '../koneksi.php';

if (isset($_POST['hapus'])) {
    $id_pegawai = htmlspecialchars($_POST['hapus']);
    $query = mysqli_query($conn, "DELETE FROM pegawai WHERE id_pegawai='$id_pegawai'");
    
    if ($query) {
        echo "Data berhasil dihapus!";
    } else {
        echo "Gagal menghapus data!";
    }
}