<?php
include '../koneksi.php';

if (isset($_POST['hapus'])) {
    $id_barang = htmlspecialchars($_POST['hapus']);
    $data = mysqli_fetch_array(mysqli_query($conn, "SELECT gambar, qrcode FROM barang WHERE id_barang = '$id_barang'"));
    $old_gambar = $data['gambar'];
    $old_qrcode = $data['qrcode'];

    $query = "DELETE FROM barang WHERE id_barang = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_barang);

    if ($stmt->execute()) {
        // Hapus gambar dan QR code jika ada
        if ($old_gambar && file_exists("../uploads/" . $old_gambar)) {
            unlink("../uploads/" . $old_gambar);
        }
        if ($old_qrcode && file_exists("../" . $old_qrcode)) {
            unlink("../" . $old_qrcode);
        }
        echo "Data barang berhasil dihapus!";
    } else {
        echo "Gagal menghapus data: " . $conn->error;
    }
    $stmt->close();
}
?>