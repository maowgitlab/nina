<?php
include 'sidebar.php';
include_once "koneksi.php";

// Pastikan tabel berita acara tersedia
$createBeritaAcaraTableQuery = "CREATE TABLE IF NOT EXISTS berita_acara_mutasi (
    id_berita INT NOT NULL AUTO_INCREMENT,
    id_mutasi INT NOT NULL,
    tanggal_berita DATE NOT NULL,
    unit_asal VARCHAR(150) NOT NULL,
    unit_tujuan VARCHAR(150) NOT NULL,
    penanggung_jawab_asal VARCHAR(150) NOT NULL,
    penanggung_jawab_tujuan VARCHAR(150) NOT NULL,
    rincian_barang TEXT NOT NULL,
    catatan TEXT NULL,
    PRIMARY KEY (id_berita),
    UNIQUE KEY unique_mutasi (id_mutasi),
    CONSTRAINT fk_berita_mutasi FOREIGN KEY (id_mutasi) REFERENCES mutasi_barang (id_mutasi) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
mysqli_query($conn, $createBeritaAcaraTableQuery);

function getRuanganName(mysqli $conn, int $ruanganId): string
{
    $stmt = $conn->prepare("SELECT nama_ruangan FROM ruangan WHERE id_ruangan = ? LIMIT 1");
    $stmt->bind_param("i", $ruanganId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['nama_ruangan'] ?? ('Ruangan #' . $ruanganId);
}

function getBarangInfo(mysqli $conn, int $barangId): array
{
    $stmt = $conn->prepare("SELECT nama_barang, spesifikasi FROM barang WHERE id_barang = ? LIMIT 1");
    $stmt->bind_param("i", $barangId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return [
        'nama' => $row['nama_barang'] ?? 'Barang #' . $barangId,
        'spesifikasi' => $row['spesifikasi'] ?? ''
    ];
}

// Ambil filter status dari parameter GET
$filter_status = isset($_GET['filter_status']) ? htmlspecialchars($_GET['filter_status']) : 'mutasi';

// Add Functionality
if (isset($_POST['add_mutasi_barang'])) {
    $id_barang = htmlspecialchars($_POST['id_barang']);
    $id_ruangan = htmlspecialchars($_POST['id_ruangan']);
    $id_ruangan1 = htmlspecialchars($_POST['id_ruangan1']);
    $jumlah = htmlspecialchars($_POST['jumlah']);
    $status = htmlspecialchars($_POST['status']);
    $tanggal_berita = trim($_POST['tanggal_berita'] ?? '');
    $penanggung_jawab_asal = trim($_POST['penanggung_jawab_asal'] ?? '');
    $penanggung_jawab_tujuan = trim($_POST['penanggung_jawab_tujuan'] ?? '');
    $rincian_barang = trim($_POST['rincian_barang'] ?? '');
    $catatan_berita = trim($_POST['catatan_berita'] ?? '');
    $user_id = $data_id; // Ambil dari sesi pengguna (pastikan $data_id ada di sidebar.php)

    // Validasi input
    if (!is_numeric($jumlah) || $jumlah <= 0) {
        echo '<script>alert("Jumlah harus berupa angka positif!"); window.location.href = window.location.href;</script>';
        exit;
    }
    if (empty($tanggal_berita) || !strtotime($tanggal_berita)) {
        echo '<script>alert("Tanggal berita acara tidak valid!"); window.location.href = window.location.href;</script>';
        exit;
    }
    if ($penanggung_jawab_asal === '' || $penanggung_jawab_tujuan === '') {
        echo '<script>alert("Penanggung jawab asal dan tujuan wajib diisi!"); window.location.href = window.location.href;</script>';
        exit;
    }

    // Cek stok
    $cekStokQuery = "SELECT nama_barang, spesifikasi, stok_awal, stok_akhir, stok_dipinjam, stok FROM barang WHERE id_barang = ?";
    $stmt = $conn->prepare($cekStokQuery);
    $stmt->bind_param("i", $id_barang);
    $stmt->execute();
    $cekStokResult = $stmt->get_result();
    $stok = $cekStokResult->fetch_assoc();
    $namaBarang = $stok['nama_barang'] ?? '';
    $spesifikasiBarang = $stok['spesifikasi'] ?? '';
    $stokAwal = (int)($stok['stok_awal'] ?? 0);
    $stokAkhirSekarang = (int)($stok['stok_akhir'] ?? 0);
    $stokDipinjam = (int)($stok['stok_dipinjam'] ?? 0);
    $stokSekarang = (int)($stok['stok'] ?? 0);

    $stokTersedia = $stokAkhirSekarang - $stokDipinjam;
    if ($jumlah > $stokTersedia) {
        echo '<script>alert("Stok barang tidak mencukupi! Stok tersedia: ' . $stokTersedia . '"); window.location.href = window.location.href;</script>';
        $stmt->close();
        exit;
    }

    $stokAkhirBaru = $stokAkhirSekarang - $jumlah;
    $stokBaru = $stokAkhirBaru - $stokDipinjam;

    mysqli_begin_transaction($conn);

    $updateStokQuery = "UPDATE barang SET stok_akhir = ?, stok = ? WHERE id_barang = ?";
    $updateStokStmt = $conn->prepare($updateStokQuery);
    $updateStokStmt->bind_param("iii", $stokAkhirBaru, $stokBaru, $id_barang);

    $insertQuery = "INSERT INTO mutasi_barang (id_barang, id_ruangan, id_ruangan1, jumlah, status, id_user)
                    VALUES (?, ?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("iiiisi", $id_barang, $id_ruangan, $id_ruangan1, $jumlah, $status, $user_id);

    if ($updateStokStmt->execute() && $insertStmt->execute()) {
        $id_mutasi_baru = $conn->insert_id;
        $ruanganAsalNama = getRuanganName($conn, (int)$id_ruangan);
        $ruanganTujuanNama = getRuanganName($conn, (int)$id_ruangan1);
        if ($rincian_barang === '') {
            $rincian_barang = 'Mutasi ' . number_format($jumlah, 0, ',', '.') . ' unit ' . $namaBarang;
            if (!empty($spesifikasiBarang)) {
                $rincian_barang .= ' (' . $spesifikasiBarang . ')';
            }
        }

        $insertBeritaQuery = "INSERT INTO berita_acara_mutasi (id_mutasi, tanggal_berita, unit_asal, unit_tujuan, penanggung_jawab_asal, penanggung_jawab_tujuan, rincian_barang, catatan)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $insertBeritaStmt = $conn->prepare($insertBeritaQuery);
        $insertBeritaStmt->bind_param(
            "isssssss",
            $id_mutasi_baru,
            $tanggal_berita,
            $ruanganAsalNama,
            $ruanganTujuanNama,
            $penanggung_jawab_asal,
            $penanggung_jawab_tujuan,
            $rincian_barang,
            $catatan_berita
        );

        if ($insertBeritaStmt->execute()) {
            mysqli_commit($conn);
            echo '<script>alert("Data mutasi barang dan berita acara berhasil ditambahkan!"); window.location.href = window.location.href;</script>';
        } else {
            mysqli_rollback($conn);
            echo '<script>alert("Gagal menyimpan berita acara: ' . addslashes($conn->error) . '"); window.location.href = window.location.href;</script>';
        }
        $insertBeritaStmt->close();
    } else {
        mysqli_rollback($conn);
        echo '<script>alert("Gagal menambahkan data: ' . addslashes($conn->error) . '"); window.location.href = window.location.href;</script>';
    }
    $stmt->close();
    $updateStokStmt->close();
    $insertStmt->close();
}

// Edit Functionality
if (isset($_POST['SimpanEditMutasi'])) {
    $id_mutasi = htmlspecialchars($_POST['Edit_id_mutasi']);
    $id_barang = htmlspecialchars($_POST['Edit_id_barang']);
    $id_ruangan = htmlspecialchars($_POST['Edit_id_ruangan']);
    $id_ruangan1 = htmlspecialchars($_POST['Edit_id_ruangan1']);
    $jumlah = htmlspecialchars($_POST['Edit_jumlah']);
    $status = htmlspecialchars($_POST['Edit_status']);
    $tanggal_berita = trim($_POST['Edit_tanggal_berita'] ?? '');
    $penanggung_jawab_asal = trim($_POST['Edit_penanggung_jawab_asal'] ?? '');
    $penanggung_jawab_tujuan = trim($_POST['Edit_penanggung_jawab_tujuan'] ?? '');
    $rincian_barang = trim($_POST['Edit_rincian_barang'] ?? '');
    $catatan_berita = trim($_POST['Edit_catatan_berita'] ?? '');
    $user_id = $data_id; // Ambil dari sesi pengguna

    // Validasi input
    if (!is_numeric($jumlah) || $jumlah <= 0) {
        echo '<script>alert("Jumlah harus berupa angka positif!"); window.location.href = window.location.href;</script>';
        exit;
    }
    if (empty($tanggal_berita) || !strtotime($tanggal_berita)) {
        echo '<script>alert("Tanggal berita acara tidak valid!"); window.location.href = window.location.href;</script>';
        exit;
    }
    if ($penanggung_jawab_asal === '' || $penanggung_jawab_tujuan === '') {
        echo '<script>alert("Penanggung jawab asal dan tujuan wajib diisi!"); window.location.href = window.location.href;</script>';
        exit;
    }

    // Ambil stok saat ini dan jumlah mutasi sebelumnya
    $cekStokQuery = "SELECT nama_barang, spesifikasi, stok_awal, stok_akhir, stok_dipinjam, stok FROM barang WHERE id_barang = ?";
    $cekStokStmt = $conn->prepare($cekStokQuery);
    $cekStokStmt->bind_param("i", $id_barang);
    $cekStokStmt->execute();
    $cekStokResult = $cekStokStmt->get_result();
    $stok = $cekStokResult->fetch_assoc();
    $namaBarang = $stok['nama_barang'] ?? '';
    $spesifikasiBarang = $stok['spesifikasi'] ?? '';
    $stokAwal = (int)($stok['stok_awal'] ?? 0);
    $stokAkhirSekarang = (int)($stok['stok_akhir'] ?? 0);
    $stokDipinjam = (int)($stok['stok_dipinjam'] ?? 0);
    $stokSekarang = (int)($stok['stok'] ?? 0);

    $cekMutasiQuery = "SELECT jumlah FROM mutasi_barang WHERE id_mutasi = ?";
    $cekMutasiStmt = $conn->prepare($cekMutasiQuery);
    $cekMutasiStmt->bind_param("i", $id_mutasi);
    $cekMutasiStmt->execute();
    $cekMutasiResult = $cekMutasiStmt->get_result();
    $mutasi = $cekMutasiResult->fetch_assoc();
    $jumlahAwal = (int)($mutasi['jumlah'] ?? 0);

    $stokTersedia = $stokAkhirSekarang + $jumlahAwal - $stokDipinjam; // Kembalikan jumlah mutasi sebelumnya
    if ($jumlah > $stokTersedia) {
        echo '<script>alert("Jumlah melebihi stok yang tersedia! Stok tersedia: ' . $stokTersedia . '"); window.location.href = window.location.href;</script>';
        $cekStokStmt->close();
        $cekMutasiStmt->close();
        exit;
    }

    $selisih = $jumlah - $jumlahAwal;
    $stokAkhirBaru = $stokAkhirSekarang - $selisih;
    $stokBaru = $stokAkhirBaru - $stokDipinjam;

    mysqli_begin_transaction($conn);

    $updateStokQuery = "UPDATE barang SET stok_akhir = ?, stok = ? WHERE id_barang = ?";
    $updateStokStmt = $conn->prepare($updateStokQuery);
    $updateStokStmt->bind_param("iii", $stokAkhirBaru, $stokBaru, $id_barang);

    $updateMutasiQuery = "UPDATE mutasi_barang
                          SET id_barang = ?, id_ruangan = ?, id_ruangan1 = ?, jumlah = ?, status = ?, id_user = ?
                          WHERE id_mutasi = ?";
    $updateMutasiStmt = $conn->prepare($updateMutasiQuery);
    $updateMutasiStmt->bind_param("iiiisii", $id_barang, $id_ruangan, $id_ruangan1, $jumlah, $status, $user_id, $id_mutasi);

    if ($updateStokStmt->execute() && $updateMutasiStmt->execute()) {
        if ($rincian_barang === '') {
            $rincian_barang = 'Mutasi ' . number_format($jumlah, 0, ',', '.') . ' unit ' . $namaBarang;
            if (!empty($spesifikasiBarang)) {
                $rincian_barang .= ' (' . $spesifikasiBarang . ')';
            }
        }
        $ruanganAsalNama = getRuanganName($conn, (int)$id_ruangan);
        $ruanganTujuanNama = getRuanganName($conn, (int)$id_ruangan1);

        $upsertBeritaQuery = "INSERT INTO berita_acara_mutasi (id_mutasi, tanggal_berita, unit_asal, unit_tujuan, penanggung_jawab_asal, penanggung_jawab_tujuan, rincian_barang, catatan)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                               ON DUPLICATE KEY UPDATE tanggal_berita = VALUES(tanggal_berita), unit_asal = VALUES(unit_asal), unit_tujuan = VALUES(unit_tujuan), penanggung_jawab_asal = VALUES(penanggung_jawab_asal), penanggung_jawab_tujuan = VALUES(penanggung_jawab_tujuan), rincian_barang = VALUES(rincian_barang), catatan = VALUES(catatan)";
        $upsertBeritaStmt = $conn->prepare($upsertBeritaQuery);
        $upsertBeritaStmt->bind_param(
            "isssssss",
            $id_mutasi,
            $tanggal_berita,
            $ruanganAsalNama,
            $ruanganTujuanNama,
            $penanggung_jawab_asal,
            $penanggung_jawab_tujuan,
            $rincian_barang,
            $catatan_berita
        );

        if ($upsertBeritaStmt->execute()) {
            mysqli_commit($conn);
            echo '<script>alert("Data mutasi barang dan berita acara berhasil diperbarui!"); window.location.href = window.location.href;</script>';
        } else {
            mysqli_rollback($conn);
            echo '<script>alert("Gagal memperbarui berita acara: ' . addslashes($conn->error) . '"); window.location.href = window.location.href;</script>';
        }
        $upsertBeritaStmt->close();
    } else {
        mysqli_rollback($conn);
        echo '<script>alert("Gagal memperbarui data: ' . addslashes($conn->error) . '"); window.location.href = window.location.href;</script>';
    }
    $cekStokStmt->close();
    $cekMutasiStmt->close();
    $updateStokStmt->close();
    $updateMutasiStmt->close();
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
    <title>Mutasi Barang</title>
</head>

<body>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Mutasi Barang</h1>
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <?php if ($data_level == 'auditor' || $data_level == 'admin') : ?>
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                                        Tambah Data
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 text-right">
                                <form method="get" action="">
                                    <label for="filter_status">Filter Status:</label>
                                    <select name="filter_status" id="filter_status" class="form-control d-inline-block w-auto" onchange="this.form.submit()">
                                        <option value="all" <?php echo ($filter_status == 'all') ? 'selected' : ''; ?>>Semua</option>
                                        <option value="mutasi" <?php echo ($filter_status == 'mutasi') ? 'selected' : ''; ?>>Mutasi Permanen</option>
                                        <option value="temporary" <?php echo ($filter_status == 'temporary') ? 'selected' : ''; ?>>Mutasi Sementara</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th>Ruangan Asal</th>
                                        <th>Ruangan Tujuan</th>
                                        <th>Jumlah</th>
                                        <th>Status</th>
                                        <th>Dimutasi Oleh</th>
                                        <th>Berita Acara</th>
                                        <th>Opsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT m.*, b.kode, b.nama_barang, r1.nama_ruangan AS ruangan_asal, r2.nama_ruangan AS ruangan_tujuan, u.nama AS user_nama,
                                                     bam.tanggal_berita, bam.unit_asal AS berita_unit_asal, bam.unit_tujuan AS berita_unit_tujuan,
                                                     bam.penanggung_jawab_asal, bam.penanggung_jawab_tujuan, bam.rincian_barang, bam.catatan AS catatan_berita
                                              FROM mutasi_barang m
                                              JOIN barang b ON m.id_barang = b.id_barang
                                              JOIN ruangan r1 ON m.id_ruangan = r1.id_ruangan
                                              JOIN ruangan r2 ON m.id_ruangan1 = r2.id_ruangan
                                              JOIN user u ON m.id_user = u.id_user
                                              LEFT JOIN berita_acara_mutasi bam ON m.id_mutasi = bam.id_mutasi";
                                    if ($filter_status != 'all') {
                                        $query .= " WHERE m.status = '" . mysqli_real_escape_string($conn, $filter_status) . "'";
                                    }
                                    $result = mysqli_query($conn, $query);
                                    $i = 1;
                                    while ($data = mysqli_fetch_array($result)) {
                                    ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td><?= htmlspecialchars($data['kode'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($data['nama_barang'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($data['ruangan_asal'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($data['ruangan_tujuan'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars(number_format($data['jumlah'], 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($data['status'] == 'mutasi' ? 'Mutasi Permanen' : 'Mutasi Sementara', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($data['user_nama'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <?php if (!empty($data['tanggal_berita'])) : ?>
                                                    <span class="badge badge-info mb-2 d-inline-block">
                                                        <?= htmlspecialchars(date('d/m/Y', strtotime($data['tanggal_berita'])), ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                    <div class="d-flex flex-column flex-sm-row">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm mr-sm-2 mb-2 mb-sm-0" data-toggle="modal" data-target="#beritaModal<?= $data['id_mutasi']; ?>">
                                                            Detail
                                                        </button>
                                                        <a href="cetak_berita_acara_mutasi.php?id=<?= $data['id_mutasi']; ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                                                            Cetak
                                                        </a>
                                                    </div>
                                                <?php else : ?>
                                                    <span class="text-muted small">Belum ada berita acara. Gunakan menu edit untuk melengkapinya.</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($data_level == 'auditor' || $data_level == 'admin') : ?>
                                                    <a href="#" class="btn btn-success btn-sm" data-toggle="modal" data-target="#editModal<?php echo $data['id_mutasi']; ?>">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-danger btn-sm hapus-btn" data-id="<?php echo $data['id_mutasi']; ?>">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                    <a href="cetak_surat_mutasi.php?id=<?= $data['id_mutasi']; ?>" class="btn btn-info btn-sm" target="_blank">
                                                        <i class="fa fa-print"></i> Cetak Surat
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <!-- Modal Edit Data -->
                                        <div class="modal fade" id="editModal<?php echo $data['id_mutasi']; ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Edit Mutasi Barang</h4>
                                                        <button type="button" class="close" data-dismiss="modal">×</button>
                                                    </div>
                                                    <form method="post">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="Edit_id_mutasi" value="<?php echo htmlspecialchars($data['id_mutasi'], ENT_QUOTES, 'UTF-8'); ?>">
                                                            <label>Barang:</label>
                                                            <input type="hidden" name="Edit_id_barang" value="<?php echo htmlspecialchars($data['id_barang'], ENT_QUOTES, 'UTF-8'); ?>">
                                                            <input type="text" value="Kode Barang: <?php echo htmlspecialchars($data['id_barang'], ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars($data['nama_barang'], ENT_QUOTES, 'UTF-8'); ?> | Stok: <?php echo htmlspecialchars($data['stok'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>" class="form-control" readonly>
                                                            <br>
                                                            <label>Ruangan Asal:</label>
                                                            <select name="Edit_id_ruangan" class="form-control" required>
                                                                <?php
                                                                $ruangan = mysqli_query($conn, "SELECT * FROM ruangan");
                                                                while ($r = mysqli_fetch_array($ruangan)) {
                                                                    $selected = ($r['id_ruangan'] == $data['id_ruangan']) ? 'selected' : '';
                                                                    echo "<option value='" . htmlspecialchars($r['id_ruangan'], ENT_QUOTES, 'UTF-8') . "' $selected>" . htmlspecialchars($r['nama_ruangan'], ENT_QUOTES, 'UTF-8') . "</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                            <br>
                                                            <label>Ruangan Tujuan:</label>
                                                            <select name="Edit_id_ruangan1" class="form-control" required>
                                                                <?php
                                                                $ruangan = mysqli_query($conn, "SELECT * FROM ruangan");
                                                                while ($r = mysqli_fetch_array($ruangan)) {
                                                                    $selected = ($r['id_ruangan'] == $data['id_ruangan1']) ? 'selected' : '';
                                                                    echo "<option value='" . htmlspecialchars($r['id_ruangan'], ENT_QUOTES, 'UTF-8') . "' $selected>" . htmlspecialchars($r['nama_ruangan'], ENT_QUOTES, 'UTF-8') . "</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                            <br>
                                                            <label>Jumlah:</label>
                                                            <input type="number" name="Edit_jumlah" value="<?php echo htmlspecialchars($data['jumlah'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" min="1" placeholder="Masukkan jumlah" required>
                                                            <br>
                                                            <label>Status:</label>
                                                            <select name="Edit_status" class="form-control" required>
                                                                <option value="mutasi" <?php echo ($data['status'] == 'mutasi') ? 'selected' : ''; ?>>Mutasi Permanen</option>
                                                                <option value="temporary" <?php echo ($data['status'] == 'temporary') ? 'selected' : ''; ?>>Mutasi Sementara</option>
                                                            </select>
                                                            <br>
                                                            <label>Tanggal Berita Acara:</label>
                                                            <input type="date" name="Edit_tanggal_berita" value="<?php echo htmlspecialchars(!empty($data['tanggal_berita']) ? $data['tanggal_berita'] : date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
                                                            <br>
                                                            <label>Penanggung Jawab Asal:</label>
                                                            <input type="text" name="Edit_penanggung_jawab_asal" value="<?php echo htmlspecialchars($data['penanggung_jawab_asal'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="Nama penanggung jawab ruangan asal" required>
                                                            <br>
                                                            <label>Penanggung Jawab Tujuan:</label>
                                                            <input type="text" name="Edit_penanggung_jawab_tujuan" value="<?php echo htmlspecialchars($data['penanggung_jawab_tujuan'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="Nama penanggung jawab ruangan tujuan" required>
                                                            <br>
                                                            <label>Rincian Barang (untuk berita acara):</label>
                                                            <textarea name="Edit_rincian_barang" class="form-control" rows="3" placeholder="Contoh: Mutasi 2 unit Laptop 14 inci"><?php echo htmlspecialchars($data['rincian_barang'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                            <small class="form-text text-muted">Biarkan kosong jika ingin menggunakan format otomatis berdasarkan jumlah dan nama barang.</small>
                                                            <br>
                                                            <label>Catatan Tambahan:</label>
                                                            <textarea name="Edit_catatan_berita" class="form-control" rows="2" placeholder="Catatan kondisi barang atau instruksi lain"><?php echo htmlspecialchars($data['catatan_berita'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                            <br>
                                                            <button type="submit" class="btn btn-primary" name="SimpanEditMutasi">Simpan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                </div>
                                <!-- Modal Detail Berita Acara -->
                                <div class="modal fade" id="beritaModal<?= $data['id_mutasi']; ?>" tabindex="-1" role="dialog" aria-labelledby="beritaModalLabel<?= $data['id_mutasi']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="beritaModalLabel<?= $data['id_mutasi']; ?>">Detail Berita Acara</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <?php if (!empty($data['tanggal_berita'])) : ?>
                                                    <dl class="row">
                                                        <dt class="col-sm-4">Tanggal Berita</dt>
                                                        <dd class="col-sm-8"><?= htmlspecialchars(date('d F Y', strtotime($data['tanggal_berita'])), ENT_QUOTES, 'UTF-8'); ?></dd>
                                                        <dt class="col-sm-4">Unit Asal</dt>
                                                        <dd class="col-sm-8"><?= htmlspecialchars($data['berita_unit_asal'] ?? $data['ruangan_asal'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                                        <dt class="col-sm-4">Penanggung Jawab Asal</dt>
                                                        <dd class="col-sm-8"><?= htmlspecialchars($data['penanggung_jawab_asal'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></dd>
                                                        <dt class="col-sm-4">Unit Tujuan</dt>
                                                        <dd class="col-sm-8"><?= htmlspecialchars($data['berita_unit_tujuan'] ?? $data['ruangan_tujuan'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                                        <dt class="col-sm-4">Penanggung Jawab Tujuan</dt>
                                                        <dd class="col-sm-8"><?= htmlspecialchars($data['penanggung_jawab_tujuan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></dd>
                                                        <dt class="col-sm-4">Rincian Barang</dt>
                                                        <dd class="col-sm-8"><pre class="mb-0" style="white-space: pre-wrap; font-family: inherit;"><?= htmlspecialchars($data['rincian_barang'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></pre></dd>
                                                        <dt class="col-sm-4">Catatan</dt>
                                                        <dd class="col-sm-8"><pre class="mb-0" style="white-space: pre-wrap; font-family: inherit;"><?= htmlspecialchars($data['catatan_berita'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></pre></dd>
                                                    </dl>
                                                <?php else : ?>
                                                    <p class="text-muted mb-0">Berita acara belum tersedia untuk mutasi ini.</p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                                <?php if (!empty($data['tanggal_berita'])) : ?>
                                                    <a href="cetak_berita_acara_mutasi.php?id=<?= $data['id_mutasi']; ?>" class="btn btn-primary" target="_blank">Cetak</a>
                                                <?php endif; ?>
                                            </div>
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

    <!-- Modal Tambah Data -->
    <div class="modal fade" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Mutasi Barang</h4>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <label>Barang:</label>
                        <select name="id_barang" class="form-control" required>
                            <?php
                            $barang = mysqli_query($conn, "SELECT * FROM barang");
                            while ($b = mysqli_fetch_array($barang)) {
                                echo "<option value='" . htmlspecialchars($b['id_barang'], ENT_QUOTES, 'UTF-8') . "'>Kode Barang: " . htmlspecialchars($b['id_barang'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($b['nama_barang'], ENT_QUOTES, 'UTF-8') . " | Stok: " . htmlspecialchars($b['stok'], ENT_QUOTES, 'UTF-8') . "</option>";
                            }
                            ?>
                        </select>
                        <br>
                        <label>Ruangan Asal:</label>
                        <select name="id_ruangan" class="form-control" required>
                            <?php
                            $ruangan = mysqli_query($conn, "SELECT * FROM ruangan");
                            while ($r = mysqli_fetch_array($ruangan)) {
                                echo "<option value='" . htmlspecialchars($r['id_ruangan'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($r['nama_ruangan'], ENT_QUOTES, 'UTF-8') . "</option>";
                            }
                            ?>
                        </select>
                        <br>
                        <label>Ruangan Tujuan:</label>
                        <select name="id_ruangan1" class="form-control" required>
                            <?php
                            $ruangan = mysqli_query($conn, "SELECT * FROM ruangan");
                            while ($r = mysqli_fetch_array($ruangan)) {
                                echo "<option value='" . htmlspecialchars($r['id_ruangan'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($r['nama_ruangan'], ENT_QUOTES, 'UTF-8') . "</option>";
                            }
                            ?>
                        </select>
                        <br>
                        <label>Jumlah:</label>
                        <input type="number" name="jumlah" class="form-control" min="1" placeholder="Masukkan jumlah" required>
                        <br>
                        <label>Status:</label>
                        <select name="status" class="form-control" required>
                            <option value="mutasi">Mutasi Permanen</option>
                            <option value="temporary">Mutasi Sementara</option>
                        </select>
                        <br>
                        <label>Tanggal Berita Acara:</label>
                        <input type="date" name="tanggal_berita" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                        <br>
                        <label>Penanggung Jawab Asal:</label>
                        <input type="text" name="penanggung_jawab_asal" class="form-control" placeholder="Nama penanggung jawab ruangan asal" required>
                        <br>
                        <label>Penanggung Jawab Tujuan:</label>
                        <input type="text" name="penanggung_jawab_tujuan" class="form-control" placeholder="Nama penanggung jawab ruangan tujuan" required>
                        <br>
                        <label>Rincian Barang (untuk berita acara):</label>
                        <textarea name="rincian_barang" class="form-control" rows="3" placeholder="Contoh: Mutasi 2 unit Laptop 14 inci"></textarea>
                        <small class="form-text text-muted">Biarkan kosong jika ingin menggunakan format otomatis berdasarkan barang dan jumlah yang dipilih.</small>
                        <br>
                        <label>Catatan Tambahan:</label>
                        <textarea name="catatan_berita" class="form-control" rows="2" placeholder="Catatan kondisi atau instruksi tambahan"></textarea>
                        <br>
                        <button type="submit" class="btn btn-primary" name="add_mutasi_barang">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/chart-area-demo.js"></script>
    <script src="assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>
    <script>
        $(document).ready(function() {
            $(document).on('click', '.hapus-btn', function(e) {
                e.preventDefault();
                var id_mutasi = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                    $.ajax({
                        url: 'proses_hapus/mutasi.php',
                        type: 'POST',
                        data: {
                            hapus: id_mutasi
                        },
                        success: function(response) {
                            alert(response);
                            location.reload();
                        },
                        error: function() {
                            alert('Terjadi kesalahan saat menghapus data!');
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>