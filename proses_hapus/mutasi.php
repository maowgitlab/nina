<?php
include_once "../koneksi.php";

if (isset($_POST['hapus'])) {
    $id_mutasi = mysqli_real_escape_string($conn, $_POST['hapus']);

    // Mulai transaksi
    mysqli_begin_transaction($conn);

    // Get mutation details
    $query = "SELECT id_barang, jumlah FROM mutasi_barang WHERE id_mutasi = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_mutasi);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        $id_barang = $data['id_barang'];
        $jumlah = (int)$data['jumlah'];

        // Restore stock
        $cekStokQuery = "SELECT stok_awal, stok_akhir, stok_dipinjam, stok FROM barang WHERE id_barang = ?";
        $cekStokStmt = $conn->prepare($cekStokQuery);
        $cekStokStmt->bind_param("i", $id_barang);
        $cekStokStmt->execute();
        $cekStokResult = $cekStokStmt->get_result();
        $stokData = $cekStokResult->fetch_assoc();
        $stokAkhirSekarang = (int)($stokData['stok_akhir'] ?? 0);
        $stokDipinjam = (int)($stokData['stok_dipinjam'] ?? 0);
        $stokSekarang = (int)($stokData['stok'] ?? 0);

        $stokAkhirBaru = $stokAkhirSekarang + $jumlah;
        $stokBaru = $stokAkhirBaru - $stokDipinjam;

        $updateStokQuery = "UPDATE barang SET stok_akhir = ?, stok = ? WHERE id_barang = ?";
        $updateStokStmt = $conn->prepare($updateStokQuery);
        $updateStokStmt->bind_param("iii", $stokAkhirBaru, $stokBaru, $id_barang);

        $deleteQuery = "DELETE FROM mutasi_barang WHERE id_mutasi = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("i", $id_mutasi);

        if ($updateStokStmt->execute() && $deleteStmt->execute()) {
            mysqli_commit($conn);
            echo "Data mutasi barang berhasil dihapus!";
        } else {
            mysqli_rollback($conn);
            echo "Gagal menghapus data: " . mysqli_error($conn);
        }

        $stmt->close();
        $cekStokStmt->close();
        $updateStokStmt->close();
        $deleteStmt->close();
    } else {
        echo "Data mutasi tidak ditemukan!";
    }
} else {
    echo "Permintaan tidak valid!";
}
?>