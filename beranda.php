<?php
include 'sidebar.php';
include "koneksi.php";

// Cek jika user adalah admin dan periksa peminjaman yang belum dikembalikan
$showAlert = false;
$peminjamanBelumKembali = [];
if ($data_level === 'admin') {
    $query = "SELECT p.*, pg.nama_pegawai AS dipinjam_oleh, b.nama_barang, k.nama_kendaraan 
              FROM peminjaman p 
              LEFT JOIN barang b ON p.id_barang = b.id_barang AND p.jenis = 'barang'
              LEFT JOIN inventaris_kendaraan k ON p.id_barang = k.id_inventaris_kendaraan AND p.jenis = 'kendaraan'
              JOIN pegawai pg ON p.id_user = pg.id_pegawai 
              WHERE p.status = 'dipinjam'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $peminjam = htmlspecialchars($row['dipinjam_oleh'], ENT_QUOTES, 'UTF-8');
            $item = $row['jenis'] === 'barang' ? htmlspecialchars($row['nama_barang'] ?? '-', ENT_QUOTES, 'UTF-8') : htmlspecialchars($row['nama_kendaraan'] ?? '-', ENT_QUOTES, 'UTF-8');
            $peminjamanBelumKembali[] = "$peminjam - $item (Jumlah: " . number_format($row['jumlah'], 0, ',', '.') . ", Tanggal: " . date('d-m-Y', strtotime($row['tanggal_pinjam'])) . ")";
        }
        $showAlert = true;
    }
}

// Fetch counts for dashboard cards
$pengadaanCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pengadaan_barang"))['total'] ?? 0;
$barangCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM barang"))['total'] ?? 0;
$kendaraanCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM inventaris_kendaraan"))['total'] ?? 0;
$mutasiCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM mutasi_barang"))['total'] ?? 0;
$peminjamanCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam'"))['total'] ?? 0;

// Statistik total inventaris barang per ruangan/unit
$unitStatistics = [];
$unitStatsQuery = "SELECT r.nama_ruangan AS unit, SUM(COALESCE(CAST(ib.jumlah AS SIGNED), 0)) AS total_inventaris
                    FROM inventaris_barang ib
                    JOIN ruangan r ON ib.id_ruangan = r.id_ruangan
                    GROUP BY r.id_ruangan, r.nama_ruangan
                    ORDER BY total_inventaris DESC";
$unitStatsResult = mysqli_query($conn, $unitStatsQuery);
if ($unitStatsResult) {
    while ($row = mysqli_fetch_assoc($unitStatsResult)) {
        $unitStatistics[] = [
            'unit' => $row['unit'],
            'total' => (int) $row['total_inventaris']
        ];
    }
}

// Grafik kondisi barang berdasarkan status inventaris
$conditionStats = [];
$conditionQuery = "SELECT CASE COALESCE(ib.status, '')
                            WHEN 'Y' THEN 'Baik'
                            WHEN 'N' THEN 'Rusak Berat'
                            WHEN 'P' THEN 'Perlu Perbaikan'
                            ELSE 'Belum Ditentukan'
                        END AS kondisi,
                        SUM(COALESCE(CAST(ib.jumlah AS SIGNED), 0)) AS total_barang
                   FROM inventaris_barang ib
                   GROUP BY kondisi";
$conditionResult = mysqli_query($conn, $conditionQuery);
if ($conditionResult) {
    while ($row = mysqli_fetch_assoc($conditionResult)) {
        $conditionStats[] = [
            'kondisi' => $row['kondisi'],
            'total' => (int) $row['total_barang']
        ];
    }
}

// Reminder servis barang - hitung dari riwayat pemeliharaan dan jadwal berikutnya (6 bulan)
$maintenanceReminders = [];
$reminderQuery = "SELECT ib.id_barang, b.nama_barang, r.nama_ruangan,
                         MAX(STR_TO_DATE(pb.tanggal, '%Y-%m-%d')) AS last_service
                  FROM inventaris_barang ib
                  JOIN barang b ON ib.id_barang = b.id_barang
                  JOIN ruangan r ON ib.id_ruangan = r.id_ruangan
                  LEFT JOIN pemeliharaan_barang pb
                        ON pb.id_barang = ib.id_barang AND pb.id_ruangan = ib.id_ruangan
                  GROUP BY ib.id_barang, ib.id_ruangan, b.nama_barang, r.nama_ruangan";
$reminderResult = mysqli_query($conn, $reminderQuery);
if ($reminderResult) {
    $today = new DateTime();
    while ($row = mysqli_fetch_assoc($reminderResult)) {
        $lastServiceRaw = $row['last_service'];
        $lastService = $lastServiceRaw ? new DateTime($lastServiceRaw) : null;
        $nextService = $lastService ? clone $lastService : new DateTime();
        if ($lastService) {
            $nextService->modify('+6 months');
        }
        $intervalDays = (int) $today->diff($nextService)->format('%r%a');
        $isOverdue = $nextService < $today;

        // Tampilkan pengingat jika belum pernah diservis atau servis berikutnya jatuh tempo <= 30 hari
        if (!$lastService || $isOverdue || $intervalDays <= 30) {
            $maintenanceReminders[] = [
                'barang' => $row['nama_barang'],
                'unit' => $row['nama_ruangan'],
                'last_service' => $lastService ? $lastService->format('d/m/Y') : 'Belum ada riwayat',
                'next_service' => $nextService->format('d/m/Y'),
                'status' => $isOverdue ? 'overdue' : ($lastService ? 'upcoming' : 'overdue'),
                'days_remaining' => $isOverdue ? 0 : max($intervalDays, 0)
            ];
        }
    }
    // Urutkan agar yang overdue tampil di atas
    usort($maintenanceReminders, function ($a, $b) {
        if ($a['status'] === $b['status']) {
            return $a['days_remaining'] <=> $b['days_remaining'];
        }
        return $a['status'] === 'overdue' ? -1 : 1;
    });
}

// Rekap mutasi dan penghapusan bulanan
$mutasiRekapBulanan = [];
$rekapBulananQuery = "SELECT DATE_FORMAT(bam.tanggal_berita, '%Y-%m') AS periode,
                             DATE_FORMAT(bam.tanggal_berita, '%M %Y') AS label,
                             SUM(CASE WHEN mb.status = 'mutasi' THEN 1 ELSE 0 END) AS total_mutasi,
                             SUM(CASE WHEN mb.status = 'penghapusan' THEN 1 ELSE 0 END) AS total_penghapusan
                      FROM mutasi_barang mb
                      LEFT JOIN berita_acara_mutasi bam ON bam.id_mutasi = mb.id_mutasi
                      WHERE bam.tanggal_berita IS NOT NULL
                      GROUP BY periode, label
                      ORDER BY periode DESC
                      LIMIT 12";
$rekapBulananResult = mysqli_query($conn, $rekapBulananQuery);
if ($rekapBulananResult) {
    while ($row = mysqli_fetch_assoc($rekapBulananResult)) {
        $mutasiRekapBulanan[] = [
            'label' => $row['label'],
            'mutasi' => (int) $row['total_mutasi'],
            'penghapusan' => (int) $row['total_penghapusan']
        ];
    }
    $mutasiRekapBulanan = array_reverse($mutasiRekapBulanan);
}

// Rekap mutasi dan penghapusan tahunan
$mutasiRekapTahunan = [];
$rekapTahunanQuery = "SELECT YEAR(bam.tanggal_berita) AS tahun,
                             SUM(CASE WHEN mb.status = 'mutasi' THEN 1 ELSE 0 END) AS total_mutasi,
                             SUM(CASE WHEN mb.status = 'penghapusan' THEN 1 ELSE 0 END) AS total_penghapusan
                      FROM mutasi_barang mb
                      LEFT JOIN berita_acara_mutasi bam ON bam.id_mutasi = mb.id_mutasi
                      WHERE bam.tanggal_berita IS NOT NULL
                      GROUP BY tahun
                      ORDER BY tahun DESC
                      LIMIT 5";
$rekapTahunanResult = mysqli_query($conn, $rekapTahunanQuery);
if ($rekapTahunanResult) {
    while ($row = mysqli_fetch_assoc($rekapTahunanResult)) {
        $mutasiRekapTahunan[] = [
            'tahun' => (int) $row['tahun'],
            'mutasi' => (int) $row['total_mutasi'],
            'penghapusan' => (int) $row['total_penghapusan']
        ];
    }
    $mutasiRekapTahunan = array_reverse($mutasiRekapTahunan);
}

$unitChartLabels = array_column($unitStatistics, 'unit');
$unitChartValues = array_map(static function ($item) {
    return (int) $item['total'];
}, $unitStatistics);
$conditionChartLabels = array_column($conditionStats, 'kondisi');
$conditionChartValues = array_map(static function ($item) {
    return (int) $item['total'];
}, $conditionStats);
$mutasiBulananLabels = array_column($mutasiRekapBulanan, 'label');
$mutasiBulananMutasi = array_map(static function ($item) {
    return (int) $item['mutasi'];
}, $mutasiRekapBulanan);
$mutasiBulananPenghapusan = array_map(static function ($item) {
    return (int) $item['penghapusan'];
}, $mutasiRekapBulanan);
$mutasiTahunanLabels = array_map(static function ($item) {
    return (string) $item['tahun'];
}, $mutasiRekapTahunan);
$mutasiTahunanMutasi = array_map(static function ($item) {
    return (int) $item['mutasi'];
}, $mutasiRekapTahunan);
$mutasiTahunanPenghapusan = array_map(static function ($item) {
    return (int) $item['penghapusan'];
}, $mutasiRekapTahunan);

// Notifikasi perawatan/maintenance barang
$maintenanceNotifications = [];
$maintenanceQuery = "SELECT pb.id_pemeliharaan, pb.tanggal, pb.keterangan, b.nama_barang, r.nama_ruangan,
                            STR_TO_DATE(pb.tanggal, '%Y-%m-%d') AS tanggal_terformat
                     FROM pemeliharaan_barang pb
                     JOIN barang b ON pb.id_barang = b.id_barang
                     LEFT JOIN ruangan r ON pb.id_ruangan = r.id_ruangan
                     WHERE STR_TO_DATE(pb.tanggal, '%Y-%m-%d') IS NOT NULL
                       AND STR_TO_DATE(pb.tanggal, '%Y-%m-%d') <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                     ORDER BY STR_TO_DATE(pb.tanggal, '%Y-%m-%d')";
$maintenanceResult = mysqli_query($conn, $maintenanceQuery);
if ($maintenanceResult && mysqli_num_rows($maintenanceResult) > 0) {
    while ($row = mysqli_fetch_assoc($maintenanceResult)) {
        $tanggalPerawatan = strtotime($row['tanggal_terformat']);
        $statusPerawatan = $tanggalPerawatan < strtotime(date('Y-m-d')) ? 'overdue' : 'upcoming';
        $maintenanceNotifications[] = [
            'barang' => htmlspecialchars($row['nama_barang'], ENT_QUOTES, 'UTF-8'),
            'ruangan' => htmlspecialchars($row['nama_ruangan'] ?? '-', ENT_QUOTES, 'UTF-8'),
            'tanggal' => date('d/m/Y', $tanggalPerawatan),
            'keterangan' => htmlspecialchars($row['keterangan'], ENT_QUOTES, 'UTF-8'),
            'status' => $statusPerawatan
        ];
    }
}
$hasOverdueMaintenance = false;
foreach ($maintenanceNotifications as $notification) {
    if ($notification['status'] === 'overdue') {
        $hasOverdueMaintenance = true;
        break;
    }
}
?>

<!-- End of Topbar -->

<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Header with Logo -->
    <div class="card shadow mb-4" style="background: linear-gradient(135deg, #343a40, #6c757d); color: white;">
        <div class="card-body text-center">
            <img src="img/logo disdag.png" alt="Company Logo" style="max-width: 150px; margin-bottom: 1rem;">
            <h1 class="h3 mb-0">Selamat Datang, <?= htmlspecialchars($data_user, ENT_QUOTES, 'UTF-8'); ?>!</h1>
            <p class="mb-0">Sistem Inventaris - Kelola aset Anda dengan mudah dan efisien</p>
        </div>
    </div>

    <!-- Admin Alert Banner -->
    <?php if ($showAlert && $data_level === 'admin') : ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Peringatan!</strong> Ada <?php echo count($peminjamanBelumKembali); ?> peminjaman yang belum dikembalikan.
            <a href="peminjaman_barang.php" class="alert-link">Lihat detail</a>.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (!empty($maintenanceNotifications) && in_array($data_level, ['admin', 'auditor'])) : ?>
        <div class="alert alert-<?php echo $hasOverdueMaintenance ? 'danger' : 'info'; ?> alert-dismissible fade show" role="alert">
            <strong><?php echo $hasOverdueMaintenance ? 'Perhatian!' : 'Informasi'; ?></strong>
            <?php echo $hasOverdueMaintenance ? 'Beberapa barang membutuhkan perawatan segera.' : 'Terdapat jadwal perawatan barang dalam 7 hari ke depan.'; ?>
            <ul class="mb-0 mt-2">
                <?php foreach ($maintenanceNotifications as $maintenance) : ?>
                    <li>
                        <strong><?php echo $maintenance['barang']; ?></strong> (<?php echo $maintenance['ruangan']; ?>) -
                        Jadwal: <?php echo $maintenance['tanggal']; ?>,
                        Keterangan: <?php echo $maintenance['keterangan']; ?>
                        <?php if ($maintenance['status'] === 'overdue') : ?>
                            <span class="badge badge-danger">Terlambat</span>
                        <?php else : ?>
                            <span class="badge badge-warning text-dark">Akan Datang</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Dashboard Cards -->
    <div class="row">
        <!-- Pengadaan Barang -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 hover-effect">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Pengadaan Barang
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($pengadaanCount, 0, ',', '.'); ?> Item</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box-open fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Inventaris Barang -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 hover-effect">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Inventaris Barang
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($barangCount, 0, ',', '.'); ?> Item</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-warehouse fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Inventaris Kendaraan -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2 hover-effect">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Inventaris Kendaraan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($kendaraanCount, 0, ',', '.'); ?> Unit</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-car fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Mutasi Barang -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 hover-effect">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Mutasi Barang
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($mutasiCount, 0, ',', '.'); ?> Transaksi</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exchange-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Peminjaman Aktif -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2 hover-effect">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Peminjaman Aktif
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($peminjamanCount, 0, ',', '.'); ?> Peminjaman</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hand-holding fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Inventaris dan Kondisi Barang -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Inventaris per Bidang / Unit</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($unitStatistics)) : ?>
                        <canvas id="unitDistributionChart" height="180"></canvas>
                        <div class="mt-3">
                            <h6 class="font-weight-bold text-secondary">5 Unit Teratas</h6>
                            <ul class="list-group list-group-flush">
                                <?php foreach (array_slice($unitStatistics, 0, 5) as $stat) : ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span><?= htmlspecialchars($stat['unit'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="badge badge-primary badge-pill"><?= number_format($stat['total'], 0, ',', '.'); ?> Item</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else : ?>
                        <p class="text-muted mb-0">Belum ada data inventaris untuk ditampilkan.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Kondisi Barang</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($conditionStats)) : ?>
                        <canvas id="conditionChart" height="180"></canvas>
                        <div class="mt-3">
                            <div class="d-flex flex-wrap">
                                <?php foreach ($conditionStats as $stat) : ?>
                                    <div class="mr-4 mb-2">
                                        <span class="font-weight-bold text-dark"><?= htmlspecialchars($stat['kondisi'], ENT_QUOTES, 'UTF-8'); ?>:</span>
                                        <span><?= number_format($stat['total'], 0, ',', '.'); ?> Item</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else : ?>
                        <p class="text-muted mb-0">Belum ada data kondisi barang yang tercatat.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Reminder Servis dan Rekap Mutasi/Penghapusan -->
    <div class="row">
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Reminder Servis Barang</h6>
                    <span class="badge badge-info badge-pill"><?= count($maintenanceReminders); ?></span>
                </div>
                <div class="card-body">
                    <?php if (!empty($maintenanceReminders)) : ?>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($maintenanceReminders, 0, 6) as $reminder) : ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark"><?= htmlspecialchars($reminder['barang'], ENT_QUOTES, 'UTF-8'); ?></h6>
                                            <p class="mb-1 text-muted small">Lokasi: <?= htmlspecialchars($reminder['unit'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="mb-0 small">Terakhir servis: <strong><?= htmlspecialchars($reminder['last_service'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                                            <p class="mb-0 small">Jadwal berikutnya: <strong><?= htmlspecialchars($reminder['next_service'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                                        </div>
                                        <span class="badge badge-<?= $reminder['status'] === 'overdue' ? 'danger' : 'warning'; ?> text-uppercase"><?= $reminder['status'] === 'overdue' ? 'Perlu segera' : 'Mendatang'; ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($maintenanceReminders) > 6) : ?>
                            <p class="mt-3 mb-0 text-muted small">Ada <?= count($maintenanceReminders) - 6; ?> item lain yang juga perlu diperhatikan.</p>
                        <?php endif; ?>
                    <?php else : ?>
                        <p class="text-muted mb-0">Tidak ada pengingat servis dalam 30 hari ke depan.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Rekap Mutasi & Penghapusan</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($mutasiRekapBulanan)) : ?>
                        <h6 class="font-weight-bold text-secondary">Perkembangan Bulanan</h6>
                        <canvas id="mutasiBulananChart" height="200"></canvas>
                    <?php else : ?>
                        <p class="text-muted">Belum ada data mutasi untuk periode bulan berjalan.</p>
                    <?php endif; ?>
                    <hr>
                    <h6 class="font-weight-bold text-secondary">Ringkasan Tahunan</h6>
                    <?php if (!empty($mutasiRekapTahunan)) : ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Tahun</th>
                                        <th>Mutasi</th>
                                        <th>Penghapusan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mutasiRekapTahunan as $rekap) : ?>
                                        <tr>
                                            <td><?= htmlspecialchars($rekap['tahun'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= number_format($rekap['mutasi'], 0, ',', '.'); ?></td>
                                            <td><?= number_format($rekap['penghapusan'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <p class="text-muted mb-0">Belum ada data tahunan untuk ditampilkan.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.container-fluid -->
</div>
<!-- End of Main Content -->

<!-- Footer -->
<footer class="sticky-footer bg-white">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>Hak Cipta &copy; Sistem Inventaris 2025</span>
        </div>
    </div>
</footer>
<!-- End of Footer -->
</div>
<!-- End of Content Wrapper -->
</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Siap untuk Keluar?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">Pilih "Keluar" untuk mengakhiri sesi Anda.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                <a class="btn btn-primary" href="logout.php">Keluar</a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap core JavaScript-->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>

<!-- Custom scripts for all pages-->
<script src="js/sb-admin-2.min.js"></script>

<style>
    .hover-effect:hover {
        transform: translateY(-5px);
        transition: transform 0.3s ease-in-out;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2) !important;
    }

    .card-header {
        background: transparent;
        border-bottom: none;
    }
</style>

<script>
    (function () {
        const initializeDashboard = () => {
            <?php if ($showAlert && $data_level === 'admin') : ?>
                const hideAlert = getCookie('hidePeminjamanAlert');
                const now = new Date().getTime();
                if (!hideAlert || now > parseInt(hideAlert)) {
                    Swal.fire({
                        title: 'Peminjaman Belum Dikembalikan',
                        html: 'Berikut adalah daftar peminjaman yang belum dikembalikan:<br><ul style="text-align: left;"><?php foreach ($peminjamanBelumKembali as $item) echo "<li>$item</li>"; ?></ul>',
                        icon: 'warning',
                        showCloseButton: true,
                        showCancelButton: true,
                        showConfirmButton: true,
                        confirmButtonText: 'Lihat Peminjaman',
                        cancelButtonText: 'Tutup',
                        cancelButtonColor: '#d33',
                        confirmButtonColor: '#3085d6',
                        reverseButtons: true,
                        showDenyButton: true,
                        denyButtonText: 'Jangan Tampilkan (1 Jam)',
                        denyButtonColor: '#6c757d',
                        customClass: {
                            popup: 'animated fadeInDown faster',
                            content: 'text-left'
                        },
                        didOpen: () => {
                            const confirmButton = Swal.getConfirmButton();
                            const denyButton = Swal.getDenyButton();

                            confirmButton.onclick = () => {
                                window.location.href = 'peminjaman_barang.php';
                            };

                            denyButton.onclick = () => {
                                const oneHourLater = now + (60 * 60 * 1000);
                                document.cookie = "hidePeminjamanAlert=" + oneHourLater + "; path=/; max-age=" + (60 * 60);
                                Swal.close();
                            };
                        }
                    });
                }
            <?php endif; ?>

            const unitLabels = <?php echo json_encode($unitChartLabels, JSON_UNESCAPED_UNICODE); ?>;
            const unitData = <?php echo json_encode($unitChartValues); ?>;
            const conditionLabels = <?php echo json_encode($conditionChartLabels, JSON_UNESCAPED_UNICODE); ?>;
            const conditionData = <?php echo json_encode($conditionChartValues); ?>;
            const mutasiBulananLabels = <?php echo json_encode($mutasiBulananLabels, JSON_UNESCAPED_UNICODE); ?>;
            const mutasiBulananData = {
                mutasi: <?php echo json_encode($mutasiBulananMutasi); ?>,
                penghapusan: <?php echo json_encode($mutasiBulananPenghapusan); ?>
            };

            if (typeof Chart === 'undefined') {
                return;
            }

            if (Chart.defaults && Chart.defaults.global) {
                Chart.defaults.global.defaultFontFamily = 'Nunito';
                Chart.defaults.global.defaultFontColor = '#858796';
            }

            window.dashboardCharts = window.dashboardCharts || {};

            const createOrUpdateChart = (key, canvas, config) => {
                if (!canvas) {
                    return;
                }

                const context = canvas.getContext('2d');
                if (!context) {
                    return;
                }

                if (window.dashboardCharts[key]) {
                    window.dashboardCharts[key].destroy();
                }

                window.dashboardCharts[key] = new Chart(context, config);
            };

            const unitCanvas = document.getElementById('unitDistributionChart');
            if (unitCanvas && unitLabels.length) {
                createOrUpdateChart('unitDistribution', unitCanvas, {
                    type: 'bar',
                    data: {
                        labels: unitLabels,
                        datasets: [{
                            label: 'Total Inventaris',
                            data: unitData,
                            backgroundColor: '#4e73df',
                            hoverBackgroundColor: '#2e59d9',
                            borderColor: '#4e73df',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        scales: {
                            xAxes: [{
                                gridLines: {
                                    display: false,
                                    drawBorder: false
                                },
                                ticks: {
                                    autoSkip: false
                                }
                            }],
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    precision: 0
                                },
                                gridLines: {
                                    color: 'rgba(234, 236, 244, 1)',
                                    zeroLineColor: 'rgba(234, 236, 244, 1)',
                                    drawBorder: false,
                                    borderDash: [2],
                                    zeroLineBorderDash: [2]
                                }
                            }]
                        },
                        legend: {
                            display: false
                        }
                    }
                });
            }

            const conditionCanvas = document.getElementById('conditionChart');
            if (conditionCanvas && conditionLabels.length) {
                createOrUpdateChart('condition', conditionCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: conditionLabels,
                        datasets: [{
                            data: conditionData,
                            backgroundColor: ['#1cc88a', '#e74a3b', '#f6c23e', '#858796'],
                            hoverBackgroundColor: ['#17a673', '#be2617', '#dda20a', '#6c757d']
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        legend: {
                            position: 'bottom'
                        },
                        tooltips: {
                            callbacks: {
                                label: function (tooltipItem, data) {
                                    const dataset = data.datasets[tooltipItem.datasetIndex];
                                    const value = dataset.data[tooltipItem.index];
                                    const label = data.labels[tooltipItem.index] || '';
                                    return `${label}: ${value}`;
                                }
                            }
                        }
                    }
                });
            }

            const mutasiBulananCanvas = document.getElementById('mutasiBulananChart');
            if (mutasiBulananCanvas && mutasiBulananLabels.length) {
                createOrUpdateChart('mutasiBulanan', mutasiBulananCanvas, {
                    type: 'line',
                    data: {
                        labels: mutasiBulananLabels,
                        datasets: [
                            {
                                label: 'Mutasi',
                                data: mutasiBulananData.mutasi,
                                borderColor: '#4e73df',
                                backgroundColor: 'rgba(78, 115, 223, 0.2)',
                                lineTension: 0.3,
                                pointBackgroundColor: '#4e73df',
                                pointBorderColor: '#4e73df',
                                fill: true
                            },
                            {
                                label: 'Penghapusan',
                                data: mutasiBulananData.penghapusan,
                                borderColor: '#e74a3b',
                                backgroundColor: 'rgba(231, 74, 59, 0.2)',
                                lineTension: 0.3,
                                pointBackgroundColor: '#e74a3b',
                                pointBorderColor: '#e74a3b',
                                fill: true
                            }
                        ]
                    },
                    options: {
                        maintainAspectRatio: false,
                        scales: {
                            xAxes: [{
                                gridLines: {
                                    display: false,
                                    drawBorder: false
                                }
                            }],
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    precision: 0
                                },
                                gridLines: {
                                    color: 'rgba(234, 236, 244, 1)',
                                    zeroLineColor: 'rgba(234, 236, 244, 1)',
                                    drawBorder: false,
                                    borderDash: [2],
                                    zeroLineBorderDash: [2]
                                }
                            }]
                        },
                        legend: {
                            display: true
                        }
                    }
                });
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeDashboard);
        } else {
            initializeDashboard();
        }
    })();

    <?php if ($showAlert && $data_level === 'admin') : ?>
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }
    <?php endif; ?>
</script>
</body>

</html>