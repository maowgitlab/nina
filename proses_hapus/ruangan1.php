<?php 
require_once '../koneksi.php';

if (isset($_POST['hapus'])) {
    $id_ruangan1 = htmlspecialchars($_POST['hapus']);
    $query = mysqli_query($conn, "DELETE FROM ruangan1 WHERE id_ruangan1='$id_ruangan1'");
    
    if ($query) {
        echo "Data berhasil dihapus!";
    } else {
        echo "Gagal menghapus data!";
    }
}