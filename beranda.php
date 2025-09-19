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
    <?php if ($showAlert && $data_level === 'admin') : ?>
        document.addEventListener('DOMContentLoaded', function() {
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
                        const cancelButton = Swal.getCancelButton();
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
        });

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