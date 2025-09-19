-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for inventaris
DROP DATABASE IF EXISTS `inventaris`;
CREATE DATABASE IF NOT EXISTS `inventaris` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `inventaris`;

-- Dumping structure for table inventaris.barang
DROP TABLE IF EXISTS `barang`;
CREATE TABLE IF NOT EXISTS `barang` (
  `id_barang` int NOT NULL AUTO_INCREMENT,
  `kode` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_barang` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `spesifikasi` text COLLATE utf8mb4_general_ci NOT NULL,
  `stok_awal` int DEFAULT NULL,
  `stok_akhir` int DEFAULT NULL,
  `stok_dipinjam` int DEFAULT NULL,
  `stok` int DEFAULT NULL,
  `gambar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `qrcode` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_barang`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table inventaris.barang: ~6 rows (approximately)
DELETE FROM `barang`;
INSERT INTO `barang` (`id_barang`, `kode`, `nama_barang`, `spesifikasi`, `stok_awal`, `stok_akhir`, `stok_dipinjam`, `stok`, `gambar`, `qrcode`) VALUES
        (1, 'BRG1000', 'Mouse', 'Mouse optik USB 1200 DPI', 4, 2, 1, 1, 'barang_1.jpg', 'uploads/qr_6862b1b97f751.png'),
        (2, 'BRG1001', 'Ac', 'Air conditioner 1 PK hemat energi', 2, 1, 1, 2, 'barang_2.jpg', 'uploads/qr_6862b191a3c79.png'),
        (3, 'BRG1002', 'Printer', 'Printer inkjet warna A4', 4, 3, 1, 2, 'barang_3.jpg', 'uploads/qr_6862b17c560ae.png'),
        (4, 'BRG1003', 'Komputer', 'PC rakitan Core i5, RAM 8GB, SSD 256GB', 5, 4, 1, 8, 'barang_4.jpg', 'uploads/qr_6862b15b9ac38.png'),
        (5, 'BRG1004', 'Laptop', 'Laptop 14 inci, Intel i5, RAM 8GB, SSD 512GB', 15, 10, 2, 8, 'barang_5.jpg', 'uploads/qr_6862b0e5a7e27.png'),
        (7, 'btg001', 'lapotop', 'Laptop cadangan 13 inci, RAM 4GB', 2, 2, 0, 4, '68647eb16a9bd.jpg', 'uploads/qr_6862be48b817c.png');

-- Dumping structure for table inventaris.inventaris_barang
DROP TABLE IF EXISTS `inventaris_barang`;
CREATE TABLE IF NOT EXISTS `inventaris_barang` (
  `id_inventaris_barang` int NOT NULL AUTO_INCREMENT,
  `id_ruangan` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `id_barang` int NOT NULL,
  `merk` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `jumlah` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Y','N','P') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'P',
  PRIMARY KEY (`id_inventaris_barang`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table inventaris.inventaris_barang: ~5 rows (approximately)
DELETE FROM `inventaris_barang`;
INSERT INTO `inventaris_barang` (`id_inventaris_barang`, `id_ruangan`, `id_user`, `id_barang`, `merk`, `jumlah`, `tanggal`, `status`) VALUES
	(1, 1, 1, 5, 'Asus', '4', '2023-09-12', 'Y'),
	(2, 4, 3, 3, 'Canon', '2', '2023-02-03', 'N'),
	(3, 4, 3, 1, 'Robot', '2', '2023-07-03', 'Y'),
	(4, 4, 2, 4, 'Lenovo', '8', '2023-11-27', 'P'),
	(5, 3, 4, 1, 'Razer', '3', '2023-02-11', 'N');

-- Dumping structure for table inventaris.inventaris_kendaraan
DROP TABLE IF EXISTS `inventaris_kendaraan`;
CREATE TABLE IF NOT EXISTS `inventaris_kendaraan` (
  `id_inventaris_kendaraan` int NOT NULL AUTO_INCREMENT,
  `id_pegawai` int NOT NULL,
  `nomor_rangka` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `jumlah` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_masuk` text COLLATE utf8mb4_general_ci NOT NULL,
  `nomor_polisi` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `nomor_bpkb` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `roda` varchar(5) COLLATE utf8mb4_general_ci NOT NULL,
  `qrcode` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gambar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_kendaraan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_inventaris_kendaraan`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table inventaris.inventaris_kendaraan: ~5 rows (approximately)
DELETE FROM `inventaris_kendaraan`;
INSERT INTO `inventaris_kendaraan` (`id_inventaris_kendaraan`, `id_pegawai`, `nomor_rangka`, `jumlah`, `tanggal_masuk`, `nomor_polisi`, `nomor_bpkb`, `roda`, `qrcode`, `gambar`, `nama_kendaraan`) VALUES
	(1, 1, 'NRK100', '7', '2023-10-09', 'DA1234YZ', 'BPKB1000', '4', 'uploads/qrcode_1751299137.png', 'kendaraan_1.jpg', 'Sienta'),
	(2, 5, 'NRK101', '1', '2023-04-23', 'DA1235YZ', 'BPKB1001', '2', 'uploads/qrcode_1751299022.png', 'kendaraan_2.jpg', 'Scoopy'),
	(3, 3, 'NRK102', '1', '2023-11-13', 'DA1236YZ', 'BPKB1002', '4', 'uploads/qrcode_1751298968.png', 'kendaraan_3.jpg', 'Avanza'),
	(4, 1, 'NRK103', '1', '2023-11-29', 'DA1237YZ', 'BPKB1003', '2', 'uploads/qrcode_1751298735.png', 'kendaraan_4.jpg', 'Scoopy'),
	(5, 5, 'NRK104', '1', '2023-06-30', 'DA1238YZ', 'BPKB1004', '2', 'uploads/qrcode_1751416323.png', 'kendaraan_5.jpg', 'Vario 125');

-- Dumping structure for table inventaris.mutasi_barang
DROP TABLE IF EXISTS `mutasi_barang`;
CREATE TABLE IF NOT EXISTS `mutasi_barang` (
  `id_mutasi` int NOT NULL AUTO_INCREMENT,
  `id_barang` int NOT NULL,
  `id_ruangan` int NOT NULL,
  `id_ruangan1` int NOT NULL,
  `jumlah` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'mutasi',
  `id_user` int DEFAULT NULL,
  PRIMARY KEY (`id_mutasi`),
  KEY `id_barang` (`id_barang`,`id_ruangan`,`id_ruangan1`),
  KEY `id_ruangan1` (`id_ruangan1`),
  KEY `id_ruangan` (`id_ruangan`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel berita acara untuk mutasi barang
CREATE TABLE IF NOT EXISTS `berita_acara_mutasi` (
  `id_berita` int NOT NULL AUTO_INCREMENT,
  `id_mutasi` int NOT NULL,
  `tanggal_berita` date NOT NULL,
  `unit_asal` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `unit_tujuan` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `penanggung_jawab_asal` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `penanggung_jawab_tujuan` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `rincian_barang` text COLLATE utf8mb4_general_ci NOT NULL,
  `catatan` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id_berita`),
  UNIQUE KEY `unique_mutasi` (`id_mutasi`),
  CONSTRAINT `fk_berita_mutasi` FOREIGN KEY (`id_mutasi`) REFERENCES `mutasi_barang` (`id_mutasi`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table inventaris.mutasi_barang: ~5 rows (approximately)
DELETE FROM `mutasi_barang`;
INSERT INTO `mutasi_barang` (`id_mutasi`, `id_barang`, `id_ruangan`, `id_ruangan1`, `jumlah`, `status`, `id_user`) VALUES
        (1, 5, 3, 3, '1', 'mutasi', 4),
        (2, 1, 5, 1, '2', 'mutasi', 2),
        (3, 2, 2, 1, '3', 'mutasi', 4),
        (4, 2, 4, 2, '2', 'mutasi', 2),
        (5, 4, 2, 1, '2', 'mutasi', 2);

-- Contoh data berita acara mutasi
DELETE FROM `berita_acara_mutasi`;
INSERT INTO `berita_acara_mutasi` (`id_berita`, `id_mutasi`, `tanggal_berita`, `unit_asal`, `unit_tujuan`, `penanggung_jawab_asal`, `penanggung_jawab_tujuan`, `rincian_barang`, `catatan`) VALUES
        (1, 1, '2024-01-05', 'Gudang Utama', 'Ruang Rapat', 'Ahmad Suryana', 'Rina Dewi', 'Mutasi 1 unit Laptop 14 inci, Intel i5, RAM 8GB, SSD 512GB', 'Barang dalam kondisi baik'),
        (2, 2, '2024-02-12', 'Ruang Server', 'Ruang Kepala Bidang', 'Budi Hartono', 'Siti Marlina', 'Mutasi 2 unit Mouse optik USB 1200 DPI', 'Segera lakukan pengecekan ulang setelah pemasangan');

-- Dumping structure for table inventaris.pegawai
DROP TABLE IF EXISTS `pegawai`;
CREATE TABLE IF NOT EXISTS `pegawai` (
  `id_pegawai` int NOT NULL AUTO_INCREMENT,
  `nip` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_pegawai` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `jabatan` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_pegawai`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table inventaris.pegawai: ~5 rows (approximately)
DELETE FROM `pegawai`;
INSERT INTO `pegawai` (`id_pegawai`, `nip`, `nama_pegawai`, `jabatan`) VALUES
	(1, '19871230', 'Sulkan, SH.MM', 'Kepala Dinas'),
	(2, '19871231', 'M. Hasan, SKM.MM', 'Sekretaris'),
	(3, '19871232', 'Jadri, SE', 'Kasubag Perencanaan '),
	(4, '19871233', 'Taufiqurrahman, SE', 'Kasubag Umum dan Kep'),
	(5, '19871234', 'Gusnawansyah, ST', 'Kasi Tertib Niaga');

-- Dumping structure for table inventaris.pemeliharaan_barang
DROP TABLE IF EXISTS `pemeliharaan_barang`;
CREATE TABLE IF NOT EXISTS `pemeliharaan_barang` (
  `id_pemeliharaan` int NOT NULL AUTO_INCREMENT,
  `id_ruangan` int NOT NULL DEFAULT '0',
  `id_barang` int NOT NULL,
  `keterangan` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal` text COLLATE utf8mb4_general_ci NOT NULL,
  `biaya` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `jumlah` int DEFAULT '1',
  PRIMARY KEY (`id_pemeliharaan`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table inventaris.pemeliharaan_barang: ~5 rows (approximately)
DELETE FROM `pemeliharaan_barang`;
INSERT INTO `pemeliharaan_barang` (`id_pemeliharaan`, `id_ruangan`, `id_barang`, `keterangan`, `tanggal`, `biaya`, `jumlah`) VALUES
	(1, 4, 2, 'Perbaikan 1', '2023-10-17', '101440', 1),
	(2, 5, 2, 'Perbaikan 2', '2023-03-20', '188727', 5),
	(3, 2, 1, 'Perbaikan 3', '2023-05-17', '53307', 2),
	(4, 1, 5, 'Perbaikan 4', '2023-12-07', '93128', 3),
	(5, 5, 3, 'Perbaikan 5', '2023-07-21', '138043', 4);

-- Dumping structure for table inventaris.pemeliharaan_kendaraan
DROP TABLE IF EXISTS `pemeliharaan_kendaraan`;
CREATE TABLE IF NOT EXISTS `pemeliharaan_kendaraan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_inventaris_kendaraan` int DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci,
  `biaya` decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table inventaris.pemeliharaan_kendaraan: ~5 rows (approximately)
DELETE FROM `pemeliharaan_kendaraan`;
INSERT INTO `pemeliharaan_kendaraan` (`id`, `id_inventaris_kendaraan`, `tanggal`, `keterangan`, `biaya`) VALUES
	(1, 4, '2023-06-30', 'Ganti oli', 55000.00),
	(2, 5, '2023-10-13', 'Servis Full', 400000.00),
	(3, 2, '2023-03-15', 'Ganti Kampas rem', 30000.00),
	(4, 5, '2023-09-16', 'Perbaikan mesin', 100000.00),
	(5, 1, '2023-10-31', 'Minyak Rem', 25000.00);

-- Dumping structure for table inventaris.peminjaman
DROP TABLE IF EXISTS `peminjaman`;
CREATE TABLE IF NOT EXISTS `peminjaman` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_barang` int NOT NULL,
  `jenis` enum('barang','kendaraan') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'barang',
  `keterangan` text COLLATE utf8mb4_general_ci,
  `id_user` int NOT NULL,
  `jumlah` int NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `status` enum('dipinjam','dikembalikan','menunggu_persetujuan','ditolak') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'menunggu_persetujuan',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table inventaris.peminjaman: ~10 rows (approximately)
DELETE FROM `peminjaman`;
INSERT INTO `peminjaman` (`id`, `id_barang`, `jenis`, `keterangan`, `id_user`, `jumlah`, `tanggal_pinjam`, `tanggal_kembali`, `status`) VALUES
	(1, 2, 'kendaraan', 'Pinjam keperluan 1', 3, 2, '2023-03-05', '2023-10-08', 'dikembalikan'),
	(2, 2, 'kendaraan', 'Pinjam keperluan 2', 2, 1, '2023-02-22', '2023-09-21', 'dipinjam'),
	(3, 1, 'kendaraan', 'Pinjam keperluan 3', 3, 3, '2023-06-26', '2025-07-24', 'dikembalikan'),
	(4, 2, 'barang', 'Pinjam keperluan 4', 3, 1, '2023-03-07', '2023-11-25', 'dipinjam'),
	(5, 3, 'kendaraan', 'Pinjam keperluan 5', 4, 1, '2023-01-10', '2023-07-02', 'dikembalikan'),
	(8, 5, 'barang', 'test', 1, 10, '2025-06-26', '2025-06-26', 'dikembalikan'),
	(11, 1, 'barang', 'test', 1, 1, '2025-07-02', '2025-07-02', 'dikembalikan'),
	(12, 1, 'barang', 'gg', 1, 1, '2025-07-02', '2025-07-24', 'dikembalikan'),
	(14, 5, 'barang', 'gg', 4, 1, '2025-07-09', '2025-07-09', 'dikembalikan'),
	(16, 1, 'kendaraan', 'test', 1, 1, '2025-07-24', '2025-07-24', 'dikembalikan');

-- Dumping structure for table inventaris.pengadaan_barang
DROP TABLE IF EXISTS `pengadaan_barang`;
CREATE TABLE IF NOT EXISTS `pengadaan_barang` (
  `id_pengadaan` int NOT NULL AUTO_INCREMENT,
  `tanggal_masuk` date NOT NULL,
  `id_supplier` int NOT NULL,
  `id_barang` int NOT NULL,
  `jumlah` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `id_ruangan` int NOT NULL,
  PRIMARY KEY (`id_pengadaan`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table inventaris.pengadaan_barang: ~5 rows (approximately)
DELETE FROM `pengadaan_barang`;
INSERT INTO `pengadaan_barang` (`id_pengadaan`, `tanggal_masuk`, `id_supplier`, `id_barang`, `jumlah`, `id_ruangan`) VALUES
	(1, '2023-12-02', 4, 1, '2', 4),
	(2, '2023-09-02', 4, 3, '1', 2),
	(3, '2023-01-03', 1, 4, '3', 1),
	(4, '2023-03-31', 5, 3, '1', 3),
	(5, '2023-09-12', 2, 4, '1', 2);

-- Dumping structure for table inventaris.ruangan
DROP TABLE IF EXISTS `ruangan`;
CREATE TABLE IF NOT EXISTS `ruangan` (
  `id_ruangan` int NOT NULL AUTO_INCREMENT,
  `nama_ruangan` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_ruangan`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table inventaris.ruangan: ~5 rows (approximately)
DELETE FROM `ruangan`;
INSERT INTO `ruangan` (`id_ruangan`, `nama_ruangan`) VALUES
	(1, 'Kepala Dinas'),
	(2, 'Sekretaris'),
	(3, 'Umum Kepegawaian'),
	(4, 'Ruang Bidang Industri'),
	(5, 'Ruang Bidang Perdagangan Dalam Negeri');

-- Dumping structure for table inventaris.ruangan1
DROP TABLE IF EXISTS `ruangan1`;
CREATE TABLE IF NOT EXISTS `ruangan1` (
  `id_ruangan1` int NOT NULL AUTO_INCREMENT,
  `nama_ruangan1` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_ruangan1`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table inventaris.ruangan1: ~5 rows (approximately)
DELETE FROM `ruangan1`;
INSERT INTO `ruangan1` (`id_ruangan1`, `nama_ruangan1`) VALUES
	(1, 'Ruang Rapat'),
	(2, 'Ruang Arsip'),
	(3, 'Ruang Pengujian Mutu Barang'),
	(4, 'Gudang'),
	(5, 'Ruang Tunggu');

-- Dumping structure for table inventaris.supplier
DROP TABLE IF EXISTS `supplier`;
CREATE TABLE IF NOT EXISTS `supplier` (
  `id_supplier` int NOT NULL AUTO_INCREMENT,
  `nama_supplier` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `no_telepon` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_supplier`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table inventaris.supplier: ~5 rows (approximately)
DELETE FROM `supplier`;
INSERT INTO `supplier` (`id_supplier`, `nama_supplier`, `no_telepon`, `alamat`) VALUES
	(1, 'Ahmas Fauzan', '081234678612', 'Jl. Ahmad Yani KM.05 No. 17, Banjarmasin Timur'),
	(2, 'Indra Setiawan', '087716234567', 'Jl. Gatot Subroto No.28, Banjarmasin Selatan'),
	(3, 'Syamsul Bahri', '08118889943', 'Jl. Zafri Zamzam No.56, Kelayan, Banjarmasin Selatan'),
	(4, 'Rizky Pratama', '082165782311', 'Jl. Sutoyo S No.10, Banjarmasin Barat'),
	(5, 'Bambang Santoso', '082167895612', 'Jl. Veteran No.45, Banjarmasin Utara');

-- Dumping structure for table inventaris.user
DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `level` enum('admin','pegawai','pimpinan','auditor') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pegawai',
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table inventaris.user: ~5 rows (approximately)
DELETE FROM `user`;
INSERT INTO `user` (`id_user`, `nama`, `username`, `password`, `level`) VALUES
	(1, 'Pegawai ', 'pegawai', 'pegawai', 'pegawai'),
	(2, 'Auditor', 'auditor', 'auditor', 'auditor'),
	(3, 'Pimpinan', 'pimpinan', 'pimpinan', 'pimpinan'),
	(4, 'Admin ', 'admin', 'admin', 'admin'),
	(5, 'Admin 5', 'admin5', 'admin5', 'admin');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
