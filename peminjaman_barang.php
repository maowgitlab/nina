<?php
include 'sidebar.php';
include_once "koneksi.php";

// Mulai transaksi
mysqli_begin_transaction($conn);

// Add Functionality
if (isset($_POST['add_peminjaman'])) {
    $id_item = mysqli_real_escape_string($conn, $_POST['id_item']);
    $id_pegawai = mysqli_real_escape_string($conn, $_POST['id_pegawai']);
    $jumlah = mysqli_real_escape_string($conn, $_POST['jumlah']);
    $tanggal_pinjam = mysqli_real_escape_string($conn, $_POST['tanggal_pinjam']);
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

    error_log("Debug - id_item: $id_item, jenis: $jenis, jumlah: $jumlah");

    if (!is_numeric($jumlah) || $jumlah <= 0) {
        echo '<script>alert("Jumlah harus berupa angka positif!"); window.location.href = window.location.href;</script>';
        exit;
    }
    if (empty($tanggal_pinjam) || !strtotime($tanggal_pinjam)) {
        echo '<script>alert("Tanggal pinjam tidak valid!"); window.location.href = window.location.href;</script>';
        exit;
    }

    if ($jenis === 'barang') {
        $cekStokQuery = "SELECT stok_awal, stok_akhir, stok_dipinjam, stok FROM barang WHERE id_barang = ? LIMIT 1";
        $stmt = $conn->prepare($cekStokQuery);
        $stmt->bind_param("i", $id_item);
        $stmt->execute();
        $cekStokResult = $stmt->get_result();
        $stokData = $cekStokResult->fetch_assoc();
        $stokAkhirSekarang = (int)($stokData['stok_akhir'] ?? 0);

        if ($jumlah > $stokAkhirSekarang) {
            echo '<script>alert("Stok barang tidak mencukupi! Stok tersedia: ' . $stokAkhirSekarang . '"); window.location.href = window.location.href;</script>';
            $stmt->close();
            exit;
        }
    } else {
        $cekStokQuery = "SELECT jumlah AS stok FROM inventaris_kendaraan WHERE id_inventaris_kendaraan = ? LIMIT 1";
        $stmt = $conn->prepare($cekStokQuery);
        $stmt->bind_param("i", $id_item);
        $stmt->execute();
        $cekStokResult = $stmt->get_result();
        $stokData = $cekStokResult->fetch_assoc();
        $stokAwal = (int)($stokData['stok'] ?? 0);

        if ($jumlah > $stokAwal) {
            echo '<script>alert("Jumlah kendaraan tidak mencukupi! Tersedia: ' . $stokAwal . '"); window.location.href = window.location.href;</script>';
            $stmt->close();
            exit;
        }
    }

    // Peminjaman awal status 'menunggu_persetujuan'
    $insertQuery = "INSERT INTO peminjaman (id_barang, id_user, jumlah, tanggal_pinjam, jenis, keterangan, status) VALUES (?, ?, ?, ?, ?, ?, 'menunggu_persetujuan')";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("iiisss", $id_item, $id_pegawai, $jumlah, $tanggal_pinjam, $jenis, $keterangan);

    if ($insertStmt->execute()) {
        mysqli_commit($conn);
        echo '<script>alert("Permintaan peminjaman berhasil diajukan dan menunggu persetujuan admin!"); window.location.href = window.location.href;</script>';
    } else {
        mysqli_rollback($conn);
        echo '<script>alert("Gagal mengajukan peminjaman: ' . mysqli_error($conn) . '"); window.location.href = window.location.href;</script>';
    }
    $stmt->close();
    $insertStmt->close();
}

// Approve Functionality (Hanya untuk admin)
if (isset($_POST['approve_peminjaman']) && isset($data_level) && $data_level == 'admin') {
    $id = mysqli_real_escape_string($conn, $_POST['id_peminjaman']);
    $id_item = mysqli_real_escape_string($conn, $_POST['id_item']);
    $jumlah = mysqli_real_escape_string($conn, $_POST['jumlah']);
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis']);

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

        $stokAkhirBaru = $stokAkhirSekarang - $jumlah;
        $stokDipinjamBaru = $stokDipinjamSekarang + $jumlah;
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
        $stokAkhir = $stokAwal - $jumlah;
        $updateStokQuery = "UPDATE inventaris_kendaraan SET jumlah = ? WHERE id_inventaris_kendaraan = ?";
        $updateStokStmt = $conn->prepare($updateStokQuery);
        $updateStokStmt->bind_param("ii", $stokAkhir, $id_item);
    }

    $updatePeminjamanQuery = "UPDATE peminjaman SET status = 'dipinjam' WHERE id = ?";
    $updatePeminjamanStmt = $conn->prepare($updatePeminjamanQuery);
    $updatePeminjamanStmt->bind_param("i", $id);

    if ($updateStokStmt->execute() && $updatePeminjamanStmt->execute()) {
        mysqli_commit($conn);
        echo '<script>alert("Peminjaman disetujui!"); window.location.href = window.location.href;</script>';
    } else {
        mysqli_rollback($conn);
        echo '<script>alert("Gagal menyetujui peminjaman: ' . mysqli_error($conn) . '"); window.location.href = window.location.href;</script>';
    }
    $cekStokStmt->close();
    $updateStokStmt->close();
    $updatePeminjamanStmt->close();
}

// Reject Functionality (Hanya untuk admin)
if (isset($_POST['reject_peminjaman']) && isset($data_level) && $data_level == 'admin') {
    $id = mysqli_real_escape_string($conn, $_POST['id_peminjaman']);
    $updatePeminjamanQuery = "UPDATE peminjaman SET status = 'ditolak' WHERE id = ?";
    $updatePeminjamanStmt = $conn->prepare($updatePeminjamanQuery);
    $updatePeminjamanStmt->bind_param("i", $id);

    if ($updatePeminjamanStmt->execute()) {
        mysqli_commit($conn);
        echo '<script>alert("Peminjaman ditolak!"); window.location.href = window.location.href;</script>';
    } else {
        mysqli_rollback($conn);
        echo '<script>alert("Gagal menolak peminjaman: ' . mysqli_error($conn) . '"); window.location.href = window.location.href;</script>';
    }
    $updatePeminjamanStmt->close();
}

// Edit Functionality (Hanya untuk pegawai saat status 'dipinjam')
if (isset($_POST['SimpanEditPeminjaman']) && isset($data_level) && $data_level == 'pegawai' || isset($_POST['SimpanEditPeminjaman']) && isset($data_level) && $data_level == 'admin') {
    $id = mysqli_real_escape_string($conn, $_POST['Edit_id']);
    $id_item = mysqli_real_escape_string($conn, $_POST['Edit_id_item']);
    $id_pegawai = mysqli_real_escape_string($conn, $_POST['Edit_id_pegawai']);
    $jumlah = mysqli_real_escape_string($conn, $_POST['Edit_jumlah']);
    $tanggal_pinjam = mysqli_real_escape_string($conn, $_POST['Edit_tanggal_pinjam']);
    $jenis = mysqli_real_escape_string($conn, $_POST['Edit_jenis']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['Edit_keterangan']);

    error_log("Debug - Edit_id_item: $id_item, Edit_jenis: $jenis, Edit_jumlah: $jumlah");

    if (!is_numeric($jumlah) || $jumlah <= 0) {
        echo '<script>alert("Jumlah harus berupa angka positif!"); window.location.href = window.location.href;</script>';
        exit;
    }
    if (empty($tanggal_pinjam) || !strtotime($tanggal_pinjam)) {
        echo '<script>alert("Tanggal pinjam tidak valid!"); window.location.href = window.location.href;</script>';
        exit;
    }
    if (empty($id_item)) {
        echo '<script>alert("ID item tidak valid! Silakan pilih item."); window.location.href = window.location.href;</script>';
        exit;
    }

    $cekSebelumnyaQuery = "SELECT jumlah, jenis, id_barang FROM peminjaman WHERE id = ? LIMIT 1";
    $cekSebelumnyaStmt = $conn->prepare($cekSebelumnyaQuery);
    $cekSebelumnyaStmt->bind_param("i", $id);
    $cekSebelumnyaStmt->execute();
    $cekSebelumnyaResult = $cekSebelumnyaStmt->get_result();
    $dataSebelumnya = $cekSebelumnyaResult->fetch_assoc();
    $jumlahSebelumnya = (int)($dataSebelumnya['jumlah'] ?? 0);
    $jenisSebelumnya = $dataSebelumnya['jenis'];
    $idItemSebelumnya = $dataSebelumnya['id_barang'];

    if (($jenisSebelumnya !== $jenis) || ($idItemSebelumnya != $id_item)) {
        if ($jenisSebelumnya === 'barang') {
            $cekStokSebelumQuery = "SELECT stok_awal, stok_akhir, stok_dipinjam, stok FROM barang WHERE id_barang = ? LIMIT 1";
            $cekStokSebelumStmt = $conn->prepare($cekStokSebelumQuery);
            $cekStokSebelumStmt->bind_param("i", $idItemSebelumnya);
            $cekStokSebelumStmt->execute();
            $cekStokSebelumResult = $cekStokSebelumStmt->get_result();
            $stokSebelumData = $cekStokSebelumResult->fetch_assoc();
            $stokAkhirSebelum = (int)($stokSebelumData['stok_akhir'] ?? 0) + $jumlahSebelumnya;
            $stokDipinjamSebelum = (int)($stokSebelumData['stok_dipinjam'] ?? 0) - $jumlahSebelumnya;
            $stokSebelum = $stokAkhirSebelum - $stokDipinjamSebelum;
            $updateStokSebelumQuery = "UPDATE barang SET stok_akhir = ?, stok_dipinjam = ?, stok = ? WHERE id_barang = ?";
            $updateStokSebelumStmt = $conn->prepare($updateStokSebelumQuery);
            $updateStokSebelumStmt->bind_param("iiii", $stokAkhirSebelum, $stokDipinjamSebelum, $stokSebelum, $idItemSebelumnya);
            $updateStokSebelumStmt->execute();
        } else {
            $cekStokSebelumQuery = "SELECT jumlah AS stok FROM inventaris_kendaraan WHERE id_inventaris_kendaraan = ? LIMIT 1";
            $cekStokSebelumStmt = $conn->prepare($cekStokSebelumQuery);
            $cekStokSebelumStmt->bind_param("i", $idItemSebelumnya);
            $cekStokSebelumStmt->execute();
            $cekStokSebelumResult = $cekStokSebelumStmt->get_result();
            $stokSebelumData = $cekStokSebelumResult->fetch_assoc();
            $stokSebelum = (int)($stokSebelumData['stok'] ?? 0) + $jumlahSebelumnya;
            $updateStokSebelumQuery = "UPDATE inventaris_kendaraan SET jumlah = ? WHERE id_inventaris_kendaraan = ?";
            $updateStokSebelumStmt = $conn->prepare($updateStokSebelumQuery);
            $updateStokSebelumStmt->bind_param("ii", $stokSebelum, $idItemSebelumnya);
            $updateStokSebelumStmt->execute();
        }
    }

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
    } else {
        $cekStokQuery = "SELECT jumlah AS stok FROM inventaris_kendaraan WHERE id_inventaris_kendaraan = ? LIMIT 1";
        $cekStokStmt = $conn->prepare($cekStokQuery);
        $cekStokStmt->bind_param("i", $id_item);
        $cekStokStmt->execute();
        $cekStokResult = $cekStokStmt->get_result();
        $stokData = $cekStokResult->fetch_assoc();
        $stokSekarang = (int)($stokData['stok'] ?? 0);
    }

    $selisih = $jumlah - $jumlahSebelumnya;

    if ($selisih > 0 && $stokSekarang < $selisih) {
        echo '<script>alert("Stok ' . ($jenis === 'barang' ? 'barang' : 'kendaraan') . ' tidak cukup! Stok tersedia: ' . $stokSekarang . '"); window.location.href = window.location.href;</script>';
        exit;
    }

    if ($jenis === 'barang') {
        $stokAkhirBaru = $stokAkhirSekarang - $selisih;
        $stokDipinjamBaru = $stokDipinjamSekarang + $selisih;
        $stokBaru = $stokAkhirBaru - $stokDipinjamBaru;
        $updateStokQuery = "UPDATE barang SET stok_akhir = ?, stok_dipinjam = ?, stok = ? WHERE id_barang = ?";
        $updateStokStmt = $conn->prepare($updateStokQuery);
        $updateStokStmt->bind_param("iiii", $stokAkhirBaru, $stokDipinjamBaru, $stokBaru, $id_item);
    } else {
        $stokBaru = $stokSekarang - $selisih;
        $updateStokQuery = "UPDATE inventaris_kendaraan SET jumlah = ? WHERE id_inventaris_kendaraan = ?";
        $updateStokStmt = $conn->prepare($updateStokQuery);
        $updateStokStmt->bind_param("ii", $stokBaru, $id_item);
    }

    $updatePeminjamanQuery = "UPDATE peminjaman SET id_barang = ?, id_user = ?, jumlah = ?, tanggal_pinjam = ?, jenis = ?, keterangan = ? WHERE id = ?";
    $updatePeminjamanStmt = $conn->prepare($updatePeminjamanQuery);
    $updatePeminjamanStmt->bind_param("iissssi", $id_item, $id_pegawai, $jumlah, $tanggal_pinjam, $jenis, $keterangan, $id);

    if ($updateStokStmt->execute() && $updatePeminjamanStmt->execute()) {
        mysqli_commit($conn);
        echo '<script>alert("Data berhasil diperbarui!"); window.location.href = window.location.href;</script>';
    } else {
        mysqli_rollback($conn);
        echo '<script>alert("Gagal memperbarui data: ' . mysqli_error($conn) . '"); window.location.href = window.location.href;</script>';
    }
    $cekSebelumnyaStmt->close();
    $cekStokStmt->close();
    $updateStokStmt->close();
    $updatePeminjamanStmt->close();
}

// Mark as Returned (Hanya untuk pegawai saat status 'dipinjam')
if (isset($_POST['kembalikan_peminjaman']) && isset($data_level) && $data_level == 'pegawai') {
    $id = mysqli_real_escape_string($conn, $_POST['id_peminjaman']);
    $id_item = mysqli_real_escape_string($conn, $_POST['id_item']);
    $jumlah = mysqli_real_escape_string($conn, $_POST['jumlah']);
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis']);

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
        $stokAkhir = $stokAwal + (int)$jumlah;
        $updateStokQuery = "UPDATE inventaris_kendaraan SET jumlah = ? WHERE id_inventaris_kendaraan = ?";
        $updateStokStmt = $conn->prepare($updateStokQuery);
        $updateStokStmt->bind_param("ii", $stokAkhir, $id_item);
    }

    $updatePeminjamanQuery = "UPDATE peminjaman SET status = 'dikembalikan', tanggal_kembali = CURDATE() WHERE id = ?";
    $updatePeminjamanStmt = $conn->prepare($updatePeminjamanQuery);
    $updatePeminjamanStmt->bind_param("i", $id);

    if ($updateStokStmt->execute() && $updatePeminjamanStmt->execute()) {
        mysqli_commit($conn);
        echo '<script>alert("' . ($jenis === 'barang' ? 'Barang' : 'Kendaraan') . ' berhasil dikembalikan!"); window.location.href = window.location.href;</script>';
    } else {
        mysqli_rollback($conn);
        echo '<script>alert("Gagal mengembalikan ' . ($jenis === 'barang' ? 'barang' : 'kendaraan') . ': ' . mysqli_error($conn) . '"); window.location.href = window.location.href;</script>';
    }
    $cekStokStmt->close();
    $updateStokStmt->close();
    $updatePeminjamanStmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <title>Peminjaman Barang/Kendaraan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .status-approved {
            background-color: #28a745;
            color: #fff;
        }
        .status-returned {
            background-color: #17a2b8;
            color: #fff;
        }
        .status-rejected {
            background-color: #dc3545;
            color: #fff;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>

<body>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Peminjaman Barang/Kendaraan</h1>
                <div class="card mb-4">
                    <div class="card-header">
                        <?php if (isset($data_level) && $data_level == 'pegawai') : ?>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addModal">
                                Tambah Data
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>ID Item</th>
                                        <th>Nama Item</th>
                                        <th>Dipinjam Oleh</th>
                                        <th>Jumlah</th>
                                        <th>Jenis</th>
                                        <th>Tanggal Pinjam</th>
                                        <th>Tanggal Kembalikan</th>
                                        <th>Keterangan</th>
                                        <th>Status</th>
                                        <th>Gambar</th>
                                        <th>QR Code</th>
                                        <th>Opsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT p.*, b.nama_barang, b.gambar AS gambar_barang, b.qrcode AS qrcode_barang, k.nama_kendaraan AS nama_kendaraan, k.gambar AS gambar_kendaraan, k.qrcode AS qrcode_kendaraan, pg.nama_pegawai AS dipinjam_oleh 
                                              FROM peminjaman p 
                                              LEFT JOIN barang b ON p.id_barang = b.id_barang AND p.jenis = 'barang'
                                              LEFT JOIN inventaris_kendaraan k ON p.id_barang = k.id_inventaris_kendaraan AND p.jenis = 'kendaraan'
                                              JOIN pegawai pg ON p.id_user = pg.id_pegawai ORDER BY p.tanggal_pinjam DESC";
                                    $result = mysqli_query($conn, $query);
                                    $i = 1;
                                    while ($data = mysqli_fetch_array($result)) {
                                        $nama = $data['jenis'] === 'barang' ? $data['nama_barang'] : $data['nama_kendaraan'];
                                        $gambar = $data['jenis'] === 'barang' ? $data['gambar_barang'] : $data['gambar_kendaraan'];
                                        $qrcode = $data['jenis'] === 'barang' ? $data['qrcode_barang'] : $data['qrcode_kendaraan'];
                                        $statusClass = '';
                                        switch ($data['status']) {
                                            case 'menunggu_persetujuan':
                                                $statusClass = 'status-pending';
                                                $statusText = 'Menunggu Persetujuan';
                                                break;
                                            case 'dipinjam':
                                                $statusClass = 'status-approved';
                                                $statusText = 'Dipinjam';
                                                break;
                                            case 'dikembalikan':
                                                $statusClass = 'status-returned';
                                                $statusText = 'Dikembalikan';
                                                break;
                                            case 'ditolak':
                                                $statusClass = 'status-rejected';
                                                $statusText = 'Ditolak';
                                                break;
                                            default:
                                                $statusText = $data['status'];
                                        }
                                    ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td><?= htmlspecialchars($data['id_barang'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($nama ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($data['dipinjam_oleh'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars(number_format($data['jumlah'], 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars(ucfirst($data['jenis']), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars(date('d-m-Y', strtotime($data['tanggal_pinjam'])), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= $data['tanggal_kembali'] == null ? 'Belum dikembalikan' : htmlspecialchars(date('d-m-Y', strtotime($data['tanggal_kembali'])), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($data['keterangan'] ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><span class="badge <?php echo $statusClass; ?>"><?= htmlspecialchars($statusText, ENT_QUOTES, 'UTF-8') ?></span></td>
                                            <td><img src="Uploads/<?= htmlspecialchars($gambar ?: '', ENT_QUOTES, 'UTF-8') ?>" alt="Gambar" width="50" style="display: <?= $gambar ? 'block' : 'none' ?>;"></td>
                                            <td><img src="<?= htmlspecialchars($qrcode ?: '', ENT_QUOTES, 'UTF-8') ?>" alt="QR Code" width="50" style="display: <?= $qrcode ? 'block' : 'none' ?>;"></td>
                                            <td>
                                                <?php if (isset($data_level) && $data_level == 'pegawai') : ?>
                                                    <form method="post" style="display:inline;">
                                                        <input type="hidden" name="id_peminjaman" value="<?php echo $data['id']; ?>">
                                                        <input type="hidden" name="id_item" value="<?php echo $data['id_barang']; ?>">
                                                        <input type="hidden" name="jumlah" value="<?php echo $data['jumlah']; ?>">
                                                        <input type="hidden" name="jenis" value="<?php echo $data['jenis']; ?>">

                                                        <?php if ($data['status'] == 'dipinjam') : ?>
                                                            <button type="button" class="btn btn-success btn-sm mr-2" data-toggle="modal" data-target="#editModal<?php echo $data['id']; ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <a href="proses_hapus/peminjaman_barang.php?hapus=<?= $data['id']; ?>" class="btn btn-danger btn-sm mr-2" onclick="return confirm('Yakin ingin menghapus?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                            <button type="submit" class="btn btn-primary btn-sm mr-2" name="kembalikan_peminjaman">Kembalikan</button>
                                                            <a href="cetak_surat_peminjaman.php?id=<?= $data['id']; ?>" class="btn btn-info btn-sm" target="_blank">
                                                                <i class="fas fa-print"></i>
                                                            </a>
                                                        <?php elseif ($data['status'] == 'dikembalikan') : ?>
                                                            <a href="cetak_surat_peminjaman.php?id=<?= $data['id']; ?>" class="btn btn-info btn-sm" target="_blank">
                                                                <i class="fas fa-print"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if (isset($data_level) && $data_level == 'admin') : ?>
                                                    <form method="post" style="display:inline;">
                                                        <input type="hidden" name="id_peminjaman" value="<?php echo $data['id']; ?>">
                                                        <input type="hidden" name="id_item" value="<?php echo $data['id_barang']; ?>">
                                                        <input type="hidden" name="jumlah" value="<?php echo $data['jumlah']; ?>">
                                                        <input type="hidden" name="jenis" value="<?php echo $data['jenis']; ?>">
                                                        <button type="button" class="btn btn-success btn-sm mr-2" data-toggle="modal" data-target="#editModal<?php echo $data['id']; ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <a href="proses_hapus/peminjaman_barang.php?hapus=<?= $data['id']; ?>" class="btn btn-danger btn-sm mr-2 my-3" onclick="return confirm('Yakin ingin menghapus?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                        <?php if ($data['status'] == 'menunggu_persetujuan') : ?>
                                                            <button type="submit" class="btn btn-success btn-sm mr-2" name="approve_peminjaman">Setujui</button>
                                                            <button type="submit" class="btn btn-warning btn-sm mr-2" name="reject_peminjaman">Tolak</button>
                                                        <?php elseif ($data['status'] == 'dikembalikan') : ?>
                                                            <a href="cetak_surat_peminjaman.php?id=<?= $data['id']; ?>" class="btn btn-info btn-sm" target="_blank">
                                                                <i class="fas fa-print"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?php echo $data['id']; ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Edit Peminjaman</h4>
                                                        <button type="button" class="close" data-dismiss="modal">×</button>
                                                    </div>
                                                    <form method="post" onsubmit="return validateForm(this, '<?php echo $data['id']; ?>')">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="Edit_id" value="<?php echo htmlspecialchars($data['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                                            <label>Jenis:</label>
                                                            <select name="Edit_jenis" class="form-control" id="editJenisSelect<?php echo $data['id']; ?>" required onchange="toggleItemSelection(this.value, '<?php echo $data['id']; ?>')">
                                                                <option value="barang" <?= $data['jenis'] === 'barang' ? 'selected' : '' ?>>Barang</option>
                                                                <option value="kendaraan" <?= $data['jenis'] === 'kendaraan' ? 'selected' : '' ?>>Kendaraan</option>
                                                            </select>
                                                            <br>
                                                            <div id="editBarangSection<?php echo $data['id']; ?>" style="display: <?= $data['jenis'] === 'barang' ? 'block' : 'none' ?>;">
                                                                <label>Barang:</label>
                                                                <select class="form-control" id="editBarangSelect<?php echo $data['id']; ?>" required>
                                                                    <?php
                                                                    $barang_list = mysqli_query($conn, "SELECT * FROM barang");
                                                                    while ($barang = mysqli_fetch_array($barang_list)) {
                                                                        $selected = ($barang['id_barang'] == $data['id_barang'] && $data['jenis'] === 'barang') ? 'selected' : '';
                                                                        echo "<option value='" . htmlspecialchars($barang['id_barang'], ENT_QUOTES, 'UTF-8') . "' $selected>" . htmlspecialchars($barang['nama_barang'], ENT_QUOTES, 'UTF-8') . " (Stok: " . $barang['stok'] . ")</option>";
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <div id="editKendaraanSection<?php echo $data['id']; ?>" style="display: <?= $data['jenis'] === 'kendaraan' ? 'block' : 'none' ?>;">
                                                                <label>Kendaraan:</label>
                                                                <select class="form-control" id="editKendaraanSelect<?php echo $data['id']; ?>" required>
                                                                    <?php
                                                                    $kendaraan_list = mysqli_query($conn, "SELECT * FROM inventaris_kendaraan");
                                                                    while ($kendaraan = mysqli_fetch_array($kendaraan_list)) {
                                                                        $selected = ($kendaraan['id_inventaris_kendaraan'] == $data['id_barang'] && $data['jenis'] === 'kendaraan') ? 'selected' : '';
                                                                        echo "<option value='" . htmlspecialchars($kendaraan['id_inventaris_kendaraan'], ENT_QUOTES, 'UTF-8') . "' $selected>" . htmlspecialchars($kendaraan['nomor_polisi'], ENT_QUOTES, 'UTF-8') . " (Jumlah: " . $kendaraan['jumlah'] . ")</option>";
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <br>
                                                            <label>Peminjam:</label>
                                                            <select name="Edit_id_pegawai" class="form-control" required>
                                                                <?php
                                                                $pegawai_list = mysqli_query($conn, "SELECT * FROM pegawai");
                                                                while ($pegawai = mysqli_fetch_array($pegawai_list)) {
                                                                    $selected = ($pegawai['id_pegawai'] == $data['id_user']) ? 'selected' : '';
                                                                    echo "<option value='" . htmlspecialchars($pegawai['id_pegawai'], ENT_QUOTES, 'UTF-8') . "' $selected>" . htmlspecialchars($pegawai['nama_pegawai'], ENT_QUOTES, 'UTF-8') . "</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                            <br>
                                                            <label>Jumlah:</label>
                                                            <input type="number" name="Edit_jumlah" value="<?php echo htmlspecialchars($data['jumlah'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" min="1" required>
                                                            <br>
                                                            <label>Tanggal Pinjam:</label>
                                                            <input type="date" name="Edit_tanggal_pinjam" value="<?php echo htmlspecialchars($data['tanggal_pinjam'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
                                                            <br>
                                                            <label>Keterangan:</label>
                                                            <input type="text" name="Edit_keterangan" value="<?php echo htmlspecialchars($data['keterangan'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="Masukkan keterangan">
                                                            <br>
                                                            <button type="submit" class="btn btn-primary" name="SimpanEditPeminjaman">Simpan</button>
                                                            <input type="hidden" name="Edit_id_item" id="editIdItem<?php echo $data['id']; ?>">
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
                <div class="d-flex align-items-center justify-content-between small">
                    <div class="text-muted">Hak Cipta © Website Anda 2025</div>
                    <div>
                        <a href="#">Kebijakan Privasi</a>
                        ·
                        <a href="#">Syarat & Ketentuan</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Peminjaman</h4>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <form method="post" onsubmit="return validateForm(this)">
                    <div class="modal-body">
                        <label>Jenis Peminjaman:</label>
                        <select name="jenis" class="form-control" id="jenisSelect" required onchange="toggleItemSelection(this.value)">
                            <option value="">Pilih Jenis</option>
                            <option value="barang">Barang</option>
                            <option value="kendaraan">Kendaraan</option>
                        </select>
                        <br>
                        <div id="barangSection" style="display:none;">
                            <label>Barang:</label>
                            <select class="form-control" id="barangSelect" required>
                                <?php
                                $barang_list = mysqli_query($conn, "SELECT * FROM barang");
                                while ($barang = mysqli_fetch_array($barang_list)) {
                                    echo "<option value='" . htmlspecialchars($barang['id_barang'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($barang['nama_barang'], ENT_QUOTES, 'UTF-8') . " (Stok: " . $barang['stok'] . ")</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div id="kendaraanSection" style="display:none;">
                            <label>Kendaraan:</label>
                            <select class="form-control" id="kendaraanSelect" required>
                                <?php
                                $kendaraan_list = mysqli_query($conn, "SELECT * FROM inventaris_kendaraan");
                                while ($kendaraan = mysqli_fetch_array($kendaraan_list)) {
                                    echo "<option value='" . htmlspecialchars($kendaraan['id_inventaris_kendaraan'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($kendaraan['nama_kendaraan'], ENT_QUOTES, 'UTF-8') . " (Nomor Polisi: " . $kendaraan['nomor_polisi'] . ", Jumlah: " . $kendaraan['jumlah'] . ")</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <br>
                        <label>Peminjam:</label>
                        <select name="id_pegawai" class="form-control" required>
                            <?php
                            $pegawai_list = mysqli_query($conn, "SELECT * FROM pegawai");
                            while ($pegawai = mysqli_fetch_array($pegawai_list)) {
                                echo "<option value='" . htmlspecialchars($pegawai['id_pegawai'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($pegawai['nama_pegawai'], ENT_QUOTES, 'UTF-8') . "</option>";
                            }
                            ?>
                        </select>
                        <br>
                        <label>Jumlah:</label>
                        <input type="number" name="jumlah" class="form-control" min="1" placeholder="Masukkan jumlah" required>
                        <br>
                        <label>Tanggal Pinjam:</label>
                        <input type="date" name="tanggal_pinjam" class="form-control" required>
                        <br>
                        <label>Keterangan:</label>
                        <input type="text" name="keterangan" class="form-control" placeholder="Masukkan keterangan">
                        <br>
                        <button type="submit" class="btn btn-primary" name="add_peminjaman">Submit</button>
                        <input type="hidden" name="id_item" id="addIdItem">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleItemSelection(jenis, modalId = '') {
            let barangSection, kendaraanSection, barangSelect, kendaraanSelect, idItemInput;

            if (!modalId) {
                barangSection = document.getElementById('barangSection');
                kendaraanSection = document.getElementById('kendaraanSection');
                barangSelect = document.getElementById('barangSelect');
                kendaraanSelect = document.getElementById('kendaraanSelect');
                idItemInput = document.getElementById('addIdItem');
            } else {
                barangSection = document.getElementById('editBarangSection' + modalId);
                kendaraanSection = document.getElementById('editKendaraanSection' + modalId);
                barangSelect = document.getElementById('editBarangSelect' + modalId);
                kendaraanSelect = document.getElementById('editKendaraanSelect' + modalId);
                idItemInput = document.getElementById('editIdItem' + modalId);
            }

            if (!barangSection || !kendaraanSection || !barangSelect || !kendaraanSelect || !idItemInput) {
                console.error('Element not found:', {
                    barangSection,
                    kendaraanSection,
                    barangSelect,
                    kendaraanSelect,
                    idItemInput
                });
                return;
            }

            barangSection.style.display = 'none';
            kendaraanSection.style.display = 'none';
            idItemInput.value = '';

            if (jenis === 'barang') {
                barangSection.style.display = 'block';
                kendaraanSection.style.display = 'none';
                barangSelect.setAttribute('name', modalId ? 'Edit_id_item' : 'id_item');
                idItemInput.value = barangSelect.value || '';
                kendaraanSelect.removeAttribute('name');
                barangSelect.required = true;
                kendaraanSelect.required = false;
                barangSelect.selectedIndex = barangSelect.querySelector('option[selected]') ? barangSelect.querySelector('option[selected]').index : 0;
            } else if (jenis === 'kendaraan') {
                barangSection.style.display = 'none';
                kendaraanSection.style.display = 'block';
                kendaraanSelect.setAttribute('name', modalId ? 'Edit_id_item' : 'id_item');
                idItemInput.value = kendaraanSelect.value || '';
                barangSelect.removeAttribute('name');
                barangSelect.required = false;
                kendaraanSelect.required = true;
                kendaraanSelect.selectedIndex = kendaraanSelect.querySelector('option[selected]') ? kendaraanSelect.querySelector('option[selected]').index : 0;
            }

            const updateValue = () => {
                idItemInput.value = (barangSection.style.display !== 'none' && barangSelect.value) ? barangSelect.value :
                    (kendaraanSection.style.display !== 'none' && kendaraanSelect.value) ? kendaraanSelect.value : '';
                console.log("Selected ID: " + idItemInput.value);
            };

            barangSelect.addEventListener('change', updateValue);
            kendaraanSelect.addEventListener('change', updateValue);
            updateValue();
        }

        function validateForm(form, modalId = '') {
            const idItemInput = document.getElementById((modalId ? 'editIdItem' : 'addIdItem') + (modalId ? modalId : ''));
            if (!idItemInput.value) {
                alert("Silakan pilih item sebelum menyimpan!");
                return false;
            }
            console.log("Submitting with idItem: " + idItemInput.value);
            return true;
        }

        document.addEventListener('DOMContentLoaded', () => {
            <?php $result->data_seek(0);
            while ($data = mysqli_fetch_array($result)) {
                echo "document.querySelector('#editModal" . $data['id'] . " select[name=\"Edit_jenis\"]').dispatchEvent(new Event('change'));";
            } ?>
            $('.modal').on('shown.bs.modal', function() {
                const modalId = this.id.replace('editModal', '');
                const jenisSelect = modalId ? document.getElementById('editJenisSelect' + modalId) : document.getElementById('jenisSelect');
                if (jenisSelect) {
                    toggleItemSelection(jenisSelect.value, modalId);
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/chart-area-demo.js"></script>
    <script src="assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>
</body>

</html>