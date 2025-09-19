<?php
require_once '../koneksi.php';

// Mulai transaksi
mysqli_begin_transaction($conn);

// Hapus data
if (isset($_GET['hapus'])) {
    $id_inventaris_barang = htmlspecialchars($_GET['hapus']);
    $query = "SELECT id_barang, jumlah FROM inventaris_barang WHERE id_inventaris_barang = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_inventaris_barang);
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
        $stokDipinjamSekarang = (int)$stokData['stok_dipinjam'];
        $stokSekarang = (int)$stokData['stok'];

        // Hitung stok baru
        $stokDipinjamBaru = $stokDipinjamSekarang - $jumlah; // Kurangi stok yang dipinjam
        $stokAkhirBaru = $stokAwal - $stokDipinjamBaru; // Hitung ulang stok akhir
        $stokBaru = $stokAkhirBaru; // Stok tersedia = stok akhir

        // Hapus data dari inventaris_barang
        $deleteQuery = "DELETE FROM inventaris_barang WHERE id_inventaris_barang = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("i", $id_inventaris_barang);

        if ($deleteStmt->execute()) {
            // Update stok di tabel barang
            $updateStokQuery = "UPDATE barang SET stok_dipinjam = ?, stok_akhir = ?, stok = ? WHERE id_barang = ?";
            $updateStokStmt = $conn->prepare($updateStokQuery);
            $updateStokStmt->bind_param("iiii", $stokDipinjamBaru, $stokAkhirBaru, $stokBaru, $id_barang);
            if ($updateStokStmt->execute()) {
                mysqli_commit($conn);
                echo "<script>alert('Data inventaris barang berhasil dihapus!'); window.location.href = '../inventaris_barang.php';</script>";
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