<?php
require_once '../koneksi.php';

// Mulai transaksi
mysqli_begin_transaction($conn);

// Hapus data
if (isset($_GET['hapus'])) {
    $id_pengadaan = htmlspecialchars($_GET['hapus']);
    $query = "SELECT id_barang, jumlah FROM pengadaan_barang WHERE id_pengadaan = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_pengadaan);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $id_barang = $data['id_barang'];
    $jumlah = (int)$data['jumlah'];

    if ($data) {
        // Ambil data stok saat ini dari tabel barang
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

        // Hitung stok baru
        $stokAkhirBaru = $stokAkhirSekarang - $jumlah; // Kurangi stok yang dihapus
        $stokBaru = $stokAkhirBaru - $stokDipinjam; // Stok tersedia = stok akhir - stok yang dipinjam

        // Hapus data dari pengadaan_barang
        $deleteQuery = "DELETE FROM pengadaan_barang WHERE id_pengadaan = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("i", $id_pengadaan);

        if ($deleteStmt->execute()) {
            // Update stok di tabel barang
            $updateStokQuery = "UPDATE barang SET stok_akhir = ?, stok = ? WHERE id_barang = ?";
            $updateStokStmt = $conn->prepare($updateStokQuery);
            $updateStokStmt->bind_param("iii", $stokAkhirBaru, $stokBaru, $id_barang);
            if ($updateStokStmt->execute()) {
                mysqli_commit($conn);
                echo "<script>alert('Data pengadaan barang berhasil dihapus!'); window.location.href = '../pengadaan_barang.php';</script>";
            } else {
                mysqli_rollback($conn);
                echo '<script>alert("Gagal memperbarui stok!");</script>';
            }
            $updateStokStmt->close();
        } else {
            mysqli_rollback($conn);
            echo '<script>alert("Gagal menghapus data!");</script>';
        }
        $deleteStmt->close();
    } else {
        mysqli_rollback($conn);
        echo '<script>alert("Data tidak ditemukan!");</script>';
    }
    $stmt->close();
    $cekStokStmt->close();
}
?>
