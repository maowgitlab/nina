<?php 
require_once '../koneksi.php';

if (isset($_POST['hapus'])) {
    $id_supplier = htmlspecialchars($_POST['hapus']);
    $query = mysqli_query($conn, "DELETE FROM supplier WHERE id_supplier='$id_supplier'");
    
    if ($query) {
        echo "Data berhasil dihapus!";
    } else {
        echo "Gagal menghapus data!";
    }
}