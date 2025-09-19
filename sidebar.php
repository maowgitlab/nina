<?php
// Mulai session terlebih dahulu
session_start();

// Cek apakah sesi belum ada, arahkan ke halaman login
if (empty($_SESSION["ses_username"])) {
    header("location:index.php");
    exit();
} else {
    $data_id = $_SESSION["ses_id"];
    $data_nama = $_SESSION["ses_nama"];
    $data_user = $_SESSION["ses_username"];
    $data_level = $_SESSION["ses_level"];
}

// Koneksi database
include "koneksi.php";

// Determine current page
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Sistem Inventaris</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        table,
        th,
        td {
            text-align: center;
        }
    </style>
</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-dark sidebar sidebar-dark accordion" id="accordionSidebar">
            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <img src="img/admin.png" width="60px" />
                </div>
                <div class="sidebar-brand-text mx-3">Sistem Inventaris</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item <?php echo $current_page == 'beranda.php' ? 'active' : ''; ?>">
                <a class="nav-link" href="beranda.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dasbor</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Nav Item - Data Menu -->
            <li class="nav-item <?php echo in_array($current_page, [
                                    'pengadaan_barang.php',
                                    'pemeliharaan_barang.php',
                                    'inventaris_barang.php',
                                    'inventaris_kendaraan.php',
                                    'pemeliharaan_kendaraan.php',
                                    'peminjaman_barang.php',
                                    'mutasi_barang.php',
                                    'barang.php',
                                    'pegawai.php',
                                    'ruangan.php',
                                    'ruangan1.php',
                                    'supplier.php'
                                ]) ? 'active' : ''; ?>">
                <a class="nav-link <?php echo in_array($current_page, [
                                        'pengadaan_barang.php',
                                        'pemeliharaan_barang.php',
                                        'inventaris_barang.php',
                                        'inventaris_kendaraan.php',
                                        'pemeliharaan_kendaraan.php',
                                        'peminjaman_barang.php',
                                        'mutasi_barang.php',
                                        'barang.php',
                                        'pegawai.php',
                                        'ruangan.php',
                                        'ruangan1.php',
                                        'supplier.php'
                                    ]) ? '' : 'collapsed'; ?>" href="#" data-toggle="collapse" data-target="#collapseData" aria-expanded="<?php echo in_array($current_page, [
                                                                                                                                                'pengadaan_barang.php',
                                                                                                                                                'pemeliharaan_barang.php',
                                                                                                                                                'inventaris_barang.php',
                                                                                                                                                'inventaris_kendaraan.php',
                                                                                                                                                'pemeliharaan_kendaraan.php',
                                                                                                                                                'peminjaman_barang.php',
                                                                                                                                                'mutasi_barang.php',
                                                                                                                                                'barang.php',
                                                                                                                                                'pegawai.php',
                                                                                                                                                'ruangan.php',
                                                                                                                                                'ruangan1.php',
                                                                                                                                                'supplier.php'
                                                                                                                                            ]) ? 'true' : 'false'; ?>" aria-controls="collapseData">
                    <i class="fas fa-fw fa-folder"></i>
                    <span>Data</span>
                </a>
                <div id="collapseData" class="collapse <?php echo in_array($current_page, [
                                                            'pengadaan_barang.php',
                                                            'pemeliharaan_barang.php',
                                                            'inventaris_barang.php',
                                                            'inventaris_kendaraan.php',
                                                            'pemeliharaan_kendaraan.php',
                                                            'peminjaman_barang.php',
                                                            'mutasi_barang.php',
                                                            'barang.php',
                                                            'pegawai.php',
                                                            'ruangan.php',
                                                            'ruangan1.php',
                                                            'supplier.php'
                                                        ]) ? 'show' : ''; ?>" aria-labelledby="headingData" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <?php if ($data_level == "admin") : ?>
                            <a class="collapse-item <?php echo $current_page == 'pengadaan_barang.php' ? 'active' : ''; ?>" href="pengadaan_barang.php">Pengadaan Barang</a>
                            <a class="collapse-item <?php echo $current_page == 'pemeliharaan_barang.php' ? 'active' : ''; ?>" href="pemeliharaan_barang.php">Pemeliharaan Barang</a>
                            <a class="collapse-item <?php echo $current_page == 'inventaris_barang.php' ? 'active' : ''; ?>" href="inventaris_barang.php">Inventaris Barang</a>
                            <a class="collapse-item <?php echo $current_page == 'inventaris_kendaraan.php' ? 'active' : ''; ?>" href="inventaris_kendaraan.php">Inventaris Kendaraan</a>
                            <a class="collapse-item <?php echo $current_page == 'pemeliharaan_kendaraan.php' ? 'active' : ''; ?>" href="pemeliharaan_kendaraan.php">Pemeliharaan Kendaraan</a>
                            <a class="collapse-item <?php echo $current_page == 'peminjaman_barang.php' ? 'active' : ''; ?>" href="peminjaman_barang.php">Peminjaman</a>
                            <a class="collapse-item <?php echo $current_page == 'mutasi_barang.php' ? 'active' : ''; ?>" href="mutasi_barang.php">Mutasi Barang</a>
                            <a class="collapse-item <?php echo $current_page == 'barang.php' ? 'active' : ''; ?>" href="barang.php">Barang</a>
                            <a class="collapse-item <?php echo $current_page == 'pegawai.php' ? 'active' : ''; ?>" href="pegawai.php">Pegawai</a>
                            <a class="collapse-item <?php echo $current_page == 'ruangan.php' ? 'active' : ''; ?>" href="ruangan.php">Ruangan</a>
                            <a class="collapse-item <?php echo $current_page == 'ruangan1.php' ? 'active' : ''; ?>" href="ruangan1.php">Ruangan Alternatif</a>
                            <a class="collapse-item <?php echo $current_page == 'supplier.php' ? 'active' : ''; ?>" href="supplier.php">Supplier</a>
                        <?php elseif ($data_level == "pegawai") : ?>
                            <a class="collapse-item <?php echo $current_page == 'barang.php' ? 'active' : ''; ?>" href="barang.php">Barang</a>
                            <a class="collapse-item <?php echo $current_page == 'inventaris_kendaraan.php' ? 'active' : ''; ?>" href="inventaris_kendaraan.php">Kendaraan</a>
                            <a class="collapse-item <?php echo $current_page == 'peminjaman_barang.php' ? 'active' : ''; ?>" href="peminjaman_barang.php">Peminjaman</a>
                        <?php elseif ($data_level == "auditor") : ?>
                            <a class="collapse-item <?php echo $current_page == 'inventaris_barang.php' ? 'active' : ''; ?>" href="inventaris_barang.php">Inventaris Barang</a>
                            <a class="collapse-item <?php echo $current_page == 'inventaris_kendaraan.php' ? 'active' : ''; ?>" href="inventaris_kendaraan.php">Inventaris Kendaraan</a>
                            <!-- <a class="collapse-item <?php echo $current_page == 'peminjaman_barang.php' ? 'active' : ''; ?>" href="peminjaman_barang.php">Peminjaman Barang</a> -->
                            <a class="collapse-item <?php echo $current_page == 'mutasi_barang.php' ? 'active' : ''; ?>" href="mutasi_barang.php">Mutasi Barang</a>
                        <?php endif; ?>
                        <!-- Pimpinan tidak memiliki akses ke menu Data -->
                    </div>
                </div>
            </li>

            <!-- Nav Item - Report Menu -->
            <?php if ($data_level != "pegawai") : ?>
                <li class="nav-item <?php echo in_array($current_page, [
                                        'view_pengadaan.php',
                                        'view_pemeliharaan.php',
                                        'view_inventaris_barang.php',
                                        'view_inventaris_kendaraan.php',
                                        'view_pemeliharaan_kendaraan.php',
                                        'view_peminjaman_barang.php',
                                        'view_mutasi.php',
                                        'view_barang.php'
                                    ]) ? 'active' : ''; ?>">
                    <a class="nav-link <?php echo in_array($current_page, [
                                            'view_pengadaan.php',
                                            'view_pemeliharaan.php',
                                            'view_inventaris_barang.php',
                                            'view_inventaris_kendaraan.php',
                                            'view_pemeliharaan_kendaraan.php',
                                            'view_peminjaman_barang.php',
                                            'view_mutasi.php',
                                            'view_barang.php'
                                        ]) ? '' : 'collapsed'; ?>" href="#" data-toggle="collapse" data-target="#collapseReport" aria-expanded="<?php echo in_array($current_page, [
                                                                                                                                                    'view_pengadaan.php',
                                                                                                                                                    'view_pemeliharaan.php',
                                                                                                                                                    'view_inventaris_barang.php',
                                                                                                                                                    'view_inventaris_kendaraan.php',
                                                                                                                                                    'view_pemeliharaan_kendaraan.php',
                                                                                                                                                    'view_peminjaman_barang.php',
                                                                                                                                                    'view_mutasi.php',
                                                                                                                                                    'view_barang.php'
                                                                                                                                                ]) ? 'true' : 'false'; ?>" aria-controls="collapseReport">
                        <i class="fas fa-fw fa-folder"></i>
                        <span>Laporan</span>
                    </a>
                    <div id="collapseReport" class="collapse <?php echo in_array($current_page, [
                                                                    'view_pengadaan.php',
                                                                    'view_pemeliharaan.php',
                                                                    'view_inventaris_barang.php',
                                                                    'view_inventaris_kendaraan.php',
                                                                    'view_pemeliharaan_kendaraan.php',
                                                                    'view_peminjaman_barang.php',
                                                                    'view_mutasi.php',
                                                                    'view_barang.php'
                                                                ]) ? 'show' : ''; ?>" aria-labelledby="headingReport" data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <a class="collapse-item <?php echo $current_page == 'view_pengadaan.php' ? 'active' : ''; ?>" href="view_pengadaan.php">Pengadaan</a>
                            <a class="collapse-item <?php echo $current_page == 'view_pemeliharaan.php' ? 'active' : ''; ?>" href="view_pemeliharaan.php">Pemeliharaan Barang</a>
                            <a class="collapse-item <?php echo $current_page == 'view_inventaris_barang.php' ? 'active' : ''; ?>" href="view_inventaris_barang.php">Inventaris Barang</a>
                            <a class="collapse-item <?php echo $current_page == 'view_inventaris_kendaraan.php' ? 'active' : ''; ?>" href="view_inventaris_kendaraan.php">Inventaris Kendaraan</a>
                            <a class="collapse-item <?php echo $current_page == 'view_pemeliharaan_kendaraan.php' ? 'active' : ''; ?>" href="view_pemeliharaan_kendaraan.php">Pemeliharaan Kendaraan</a>
                            <a class="collapse-item <?php echo $current_page == 'view_peminjaman_barang.php' ? 'active' : ''; ?>" href="view_peminjaman_barang.php">Peminjaman</a>
                            <a class="collapse-item <?php echo $current_page == 'view_mutasi.php' ? 'active' : ''; ?>" href="view_mutasi.php">Mutasi</a>
                            <a class="collapse-item <?php echo $current_page == 'view_barang.php' ? 'active' : ''; ?>" href="view_barang.php">Barang</three>
                        </div>
                    </div>
                </li>
                <li class="nav-item <?php echo in_array($current_page, [
                                        'view_surat_peminjaman.php',
                                        'view_surat_mutasi.php',
                                        'view_qrcode_kendaraan.php',
                                        'view_qrcode_barang.php'
                                    ]) ? 'active' : ''; ?>">
                    <a class="nav-link <?php echo in_array($current_page, [
                                            'view_surat_peminjaman.php',
                                            'view_surat_mutasi.php',
                                            'view_qrcode_kendaraan.php',
                                            'view_qrcode_barang.php'
                                        ]) ? '' : 'collapsed'; ?>" href="#" data-toggle="collapse" data-target="#collapseReport2" aria-expanded="<?php echo in_array($current_page, [
                                                                                                                                                        'view_surat_peminjaman.php',
                                                                                                                                                        'view_surat_mutasi.php',
                                                                                                                                                        'view_qrcode_kendaraan.php',
                                                                                                                                                        'view_qrcode_barang.php'
                                                                                                                                                    ]) ? 'true' : 'false'; ?>" aria-controls="collapseReport2">
                        <i class="fas fa-fw fa-folder"></i>
                        <span>Laporan 2</span>
                    </a>
                    <div id="collapseReport2" class="collapse <?php echo in_array($current_page, [
                                                                    'view_surat_peminjaman.php',
                                                                    'view_surat_mutasi.php',
                                                                    'view_qrcode_kendaraan.php',
                                                                    'view_qrcode_barang.php'
                                                                ]) ? 'show' : ''; ?>" aria-labelledby="headingReport2" data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <a class="collapse-item <?php echo $current_page == 'view_surat_peminjaman.php' ? 'active' : ''; ?>" href="view_surat_peminjaman.php">Surat Peminjaman</a>
                            <a class="collapse-item <?php echo $current_page == 'view_surat_mutasi.php' ? 'active' : ''; ?>" href="view_surat_mutasi.php">Surat Mutasi</a>
                            <a class="collapse-item <?php echo $current_page == 'view_qrcode_kendaraan.php' ? 'active' : ''; ?>" href="view_qrcode_kendaraan.php">QR Code Kendaraan</a>
                            <a class="collapse-item <?php echo $current_page == 'view_qrcode_barang.php' ? 'active' : ''; ?>" href="view_qrcode_barang.php">QR Code Barang</a>
                        </div>
                    </div>
                </li>
            <?php endif; ?>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in" aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small" placeholder="Cari..." aria-label="Cari" aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= htmlspecialchars($data_nama, ENT_QUOTES, 'UTF-8'); ?></span>
                                <img class="img-profile rounded-circle" src="img/admin2.png">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Keluar
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>