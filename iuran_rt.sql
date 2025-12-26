-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 26 Okt 2025 pada 12.14
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `iuran_rt`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id` int(11) NOT NULL,
  `warga_id` int(11) NOT NULL,
  `tahun` int(11) NOT NULL,
  `bulan` tinyint(4) NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `jumlah` int(11) NOT NULL,
  `metode` enum('tunai','transfer') DEFAULT 'tunai',
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data untuk tabel `pembayaran`
--

INSERT INTO `pembayaran` (`id`, `warga_id`, `tahun`, `bulan`, `tanggal_bayar`, `jumlah`, `metode`, `keterangan`, `created_at`) VALUES
(2, 7, 2025, 10, '2025-10-18', 10000, 'tunai', NULL, '2025-10-23 13:19:22'),
(3, 6, 2025, 10, '2025-10-21', 10000, 'tunai', NULL, '2025-10-23 13:19:22');

-- --------------------------------------------------------

--
-- Struktur dari tabel `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL DEFAULT 1,
  `nominal_iuran` int(11) NOT NULL DEFAULT 50000,
  `rekening_text` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `settings`
--

INSERT INTO `settings` (`id`, `nominal_iuran`, `rekening_text`) VALUES
(1, 10000, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `role` enum('admin') DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `nama`, `role`, `created_at`) VALUES
(1, 'admin2', '<HASH_DISINI>', 'Administrator Baru', 'admin', '2025-10-23 06:10:34'),
(2, 'admin', '$2y$10$iIoWTZBNO9RlQXNp283mFufyihw3Za9JB/QbyNLekNkfHepa.z9iO', 'Administrator', 'admin', '2025-10-23 06:16:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `warga`
--

CREATE TABLE `warga` (
  `id` int(11) NOT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `kk` varchar(20) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `aktif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `warga`
--

INSERT INTO `warga` (`id`, `nik`, `kk`, `nama`, `alamat`, `no_hp`, `aktif`, `created_at`) VALUES
(6, '3276035609040001', NULL, 'Cindy Bela Amelia', 'Jl. Pengasinan Sawangan Depok', '08991104539', 1, '2025-10-23 13:18:22'),
(7, '3276090409030001', NULL, 'Adib Praditya', 'Jl. Pengasinan Sawangan Depok', '08979384446', 1, '2025-10-23 13:19:00'),
(8, '3276090608030001', NULL, 'Adelia Indriyani', 'Sawangan Depok', '08999831234', 1, '2025-10-26 10:20:24');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_warga_bulan` (`warga_id`,`tahun`,`bulan`);

--
-- Indeks untuk tabel `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `warga`
--
ALTER TABLE `warga`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nik` (`nik`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `warga`
--
ALTER TABLE `warga`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `fk_bayar_warga` FOREIGN KEY (`warga_id`) REFERENCES `warga` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
