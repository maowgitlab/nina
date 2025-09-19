<?php
require_once '../koneksi.php';

// Mulai transaksi
mysqli_begin_transaction($conn);

if (isset($_POST['hapus'])) {
    $id_inventaris_kendaraan = htmlspecialchars($_POST['hapus']);
    $query = "SELECT qrcode, gambar FROM inventaris_kendaraan WHERE id_inventaris_kendaraan = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_inventaris_kendaraan);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $qrcode = $data['qrcode'];
    $gambar = $data['gambar'];

    $deleteQuery = "DELETE FROM inventaris_kendaraan WHERE id_inventaris_kendaraan = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("i", $id_inventaris_kendaraan);

    if ($deleteStmt->execute()) {
        // Hapus file QR code dan gambar jika ada
        if ($qrcode && file_exists("../uploads/" . $qrcode)) {
            unlink("../uploads/" . $qrcode);
        }
        if ($gambar && file_exists("../uploads/" . $gambar)) {
            unlink("../uploads/" . $gambar);
        }
        mysqli_commit($conn);
        echo "Data berhasil dihapus!";
    } else {
        mysqli_rollback($conn);
        echo "Gagal menghapus data!";
    }
    $stmt->close();
    $deleteStmt->close();
}
?>