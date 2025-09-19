<?php 
require_once '../koneksi.php';

if (isset($_POST['hapus'])) {
    $id_ruangan = htmlspecialchars($_POST['hapus']);
    $query = mysqli_query($conn, "DELETE FROM ruangan WHERE id_ruangan='$id_ruangan'");
    
    if ($query) {
        echo "Data berhasil dihapus!";
    } else {
        echo "Gagal menghapus data!";
    }
}