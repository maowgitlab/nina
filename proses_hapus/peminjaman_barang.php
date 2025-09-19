<?php
include_once "../koneksi.php";

// Mulai transaksi
mysqli_begin_transaction($conn);

// Delete Functionality
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);
    $query = "SELECT id_barang, jumlah, jenis FROM peminjaman WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $id_item = $data['id_barang'];
    $jumlah = (int)$data['jumlah'];
    $jenis = $data['jenis'];

    if ($jenis === 'barang') {
        $cekStokQuery = "SELECT stok_awal, stok_akhir, stok_dipinjam, stok FROM barang WHERE id_barang = ? LIMIT 1";
        $cekStokStmt = $conn->prepare($cekStokQuery);
        $cekStokStmt->bind_param("i", $id_item);
        $cekStokStmt->execute();
        $cekStokResult = $cekStokStmt->get_result();
        $stokData = $cekStokResult->fetch_assoc();
        $stokAkhirSekarang = (int)($stokData['stok_akhir'] ?? 0);
        $stokDipinjamSekarang = (int)($stokData['stok_dipinjam'] ?? 0);
        $stokSekarang = (int)($stokData['stok'] ?? 0);

        $stokAkhirBaru = $stokAkhirSekarang + $jumlah;
        $stokDipinjamBaru = $stokDipinjamSekarang - $jumlah;
        $stokBaru = $stokAkhirBaru - $stokDipinjamBaru;

        $updateStokQuery = "UPDATE barang SET stok_akhir = ?, stok_dipinjam = ?, stok = ? WHERE id_barang = ?";
        $updateStokStmt = $conn->prepare($updateStokQuery);
        $updateStokStmt->bind_param("iiii", $stokAkhirBaru, $stokDipinjamBaru, $stokBaru, $id_item);
    } else {
        $cekStokQuery = "SELECT jumlah AS stok FROM inventaris_kendaraan WHERE id_inventaris_kendaraan = ? LIMIT 1";
        $cekStokStmt = $conn->prepare($cekStokQuery);
        $cekStokStmt->bind_param("i", $id_item);
        $cekStokStmt->execute();
        $cekStokResult = $cekStokStmt->get_result();
        $stokData = $cekStokResult->fetch_assoc();
        $stokAwal = (int)($stokData['stok'] ?? 0);
        $stokAkhir = $stokAwal + $jumlah;
        $updateStokQuery = "UPDATE inventaris_kendaraan SET jumlah = ? WHERE id_inventaris_kendaraan = ?";
        $updateStokStmt = $conn->prepare($updateStokQuery);
        $updateStokStmt->bind_param("ii", $stokAkhir, $id_item);
    }

    $deleteQuery = "DELETE FROM peminjaman WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("i", $id);

    if ($updateStokStmt->execute() && $deleteStmt->execute()) {
        mysqli_commit($conn);
        echo "<script>alert('Data peminjaman barang berhasil dihapus!'); window.location.href = '../peminjaman_barang.php';</script>";
    } else {
        mysqli_rollback($conn);
        echo "Gagal menghapus data: " . mysqli_error($conn);
    }

    $stmt->close();
    $cekStokStmt->close();
    $updateStokStmt->close();
    $deleteStmt->close();
}
?>