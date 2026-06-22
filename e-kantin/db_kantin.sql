-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 24, 2026 at 09:59 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_kantin`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `id_menu` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `subtotal` float NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail`, `id_transaksi`, `id_menu`, `qty`, `subtotal`) VALUES
(1, 1, 3, 2, 36000),
(2, 2, 3, 4, 72000),
(3, 3, 5, 3, 18000),
(4, 4, 5, 1, 6000),
(5, 5, 2, 3, 36000),
(6, 5, 3, 1, 18000),
(7, 6, 5, 10, 60000);

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id_menu` int(11) NOT NULL,
  `nama_makanan` varchar(100) NOT NULL,
  `harga` float NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id_menu`, `nama_makanan`, `harga`, `stok`, `gambar`, `created_at`) VALUES
(1, 'Nasi Goreng', 15000, 50, 'menu_1777056512_7488.jpg', '2026-04-24 14:04:41'),
(2, 'Mie Goreng', 12000, 37, 'menu_1777056458_1468.jpg', '2026-04-24 14:04:41'),
(3, 'Ayam Goreng', 18000, 23, 'menu_1777056422_1760.jpg', '2026-04-24 14:04:41'),
(4, 'Es Teh Manis', 5000, 100, 'menu_1777056360_5797.jpg', '2026-04-24 14:04:41'),
(5, 'Es Jeruk', 6000, 40, 'menu_1777056312_3704.jpg', '2026-04-24 14:04:41');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `total_bayar` float NOT NULL DEFAULT 0,
  `status` enum('pending','diproses','selesai','dibatalkan') NOT NULL DEFAULT 'pending',
  `metode_pembayaran` varchar(10) NOT NULL DEFAULT 'cash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_user`, `tanggal`, `total_bayar`, `status`, `metode_pembayaran`) VALUES
(1, 22, '2026-04-24 21:31:47', 36000, 'selesai', 'cash'),
(2, 21, '2026-04-25 00:46:15', 72000, 'selesai', 'cash'),
(3, 21, '2026-04-25 01:10:00', 18000, 'selesai', 'cash'),
(4, 21, '2026-04-25 01:25:01', 6000, 'selesai', 'qris'),
(5, 21, '2026-04-25 02:33:12', 54000, 'selesai', 'qris'),
(6, 23, '2026-04-25 02:42:50', 60000, 'selesai', 'qris');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','kasir','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `username`, `password`, `role`, `created_at`) VALUES
(19, 'admin', '$2y$10$x2/ShhLRTHfROLN1EYzZaO2kBRtPUtRqW/5KkkaBqluVDO4wZQuAO', 'admin', '2026-04-24 14:29:35'),
(20, 'kasir', '$2y$10$x2/ShhLRTHfROLN1EYzZaO2kBRtPUtRqW/5KkkaBqluVDO4wZQuAO', 'kasir', '2026-04-24 14:29:35'),
(21, 'user', '$2y$10$x2/ShhLRTHfROLN1EYzZaO2kBRtPUtRqW/5KkkaBqluVDO4wZQuAO', 'user', '2026-04-24 14:29:35'),
(22, 'alif', '$2y$10$gUTFgq5cklO0Cj8mcZHcL.3dRSF9LYGk72AkADAZ4ZGtU1kYuCtc2', 'user', '2026-04-24 14:31:21'),
(23, 'Ardra', '$2y$10$LvBPQkm2Q2y5aGaSjSamCuLnhtTXNFGavJhMo95/LGWwCqfFl4z26', 'user', '2026-04-24 19:27:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_menu` (`id_menu`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id_menu`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`) ON DELETE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
