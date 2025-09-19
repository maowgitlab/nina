<?php
require_once '../koneksi.php';

// Mulai transaksi
mysqli_begin_transaction($conn);

// Hapus Functionality
if (isset($_GET['hapus'])) {
    $id_pemeliharaan = htmlspecialchars($_GET['hapus']);
    $query = "SELECT id_barang, jumlah FROM pemeliharaan_barang WHERE id_pemeliharaan = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_pemeliharaan);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $id_barang = $data['id_barang'];
    $jumlah = (int)$data['jumlah'];

    $cekStokQuery = "SELECT stok_awal, stok_akhir, stok_dipinjam, stok FROM barang WHERE id_barang = ?";
    $cekStokStmt = $conn->prepare($cekStokQuery);
    $cekStokStmt->bind_param("i", $id_barang);
    $cekStokStmt->execute();
    $cekStokResult = $cekStokStmt->get_result();
    $stokData = $cekStokResult->fetch_assoc();
    $stokAwal = (int)$stokData['stok_awal'];
    $stokAkhirSekarang = (int)$stokData['stok_akhir'];
    $stokDipinjam = (int)$stokData['stok_dipinjam'];
    $stokSekarang = (int)$stokData['stok'];

    $stokAkhirBaru = $stokAkhirSekarang + $jumlah; // Kembalikan stok yang dihapus
    $stokBaru = $stokAkhirBaru - $stokDipinjam;

    $updateStokQuery = "UPDATE barang SET stok_akhir = ?, stok = ? WHERE id_barang = ?";
    $updateStokStmt = $conn->prepare($updateStokQuery);
    $updateStokStmt->bind_param("iii", $stokAkhirBaru, $stokBaru, $id_barang);

    $deleteQuery = "DELETE FROM pemeliharaan_barang WHERE id_pemeliharaan = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("i", $id_pemeliharaan);

    if ($updateStokStmt->execute() && $deleteStmt->execute()) {
        mysqli_commit($conn);
        echo "<script>alert('Data pemeliharaan barang berhasil dihapus!'); window.location.href = '../pemeliharaan_barang.php';</script>";
    } else {
        mysqli_rollback($conn);
        echo '<script>alert("Gagal menghapus data: ' . $conn->error . '");</script>';
    }
    $stmt->close();
    $updateStokStmt->close();
    $deleteStmt->close();
    $cekStokStmt->close();
}
?>
