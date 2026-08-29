-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 29 Agu 2026 pada 05.13
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_pelatihan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `activity` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `activity`, `module`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 2, 'Membuat pelatihan & folder dokumen: Pelatihan Percontohan', 'Pelatihan', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-08-28 06:22:21', '2026-08-28 06:22:21'),
(2, 2, 'Membuat pelatihan & folder dokumen: Pelatihan Bencana', 'Pelatihan', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-08-28 13:14:54', '2026-08-28 13:14:54'),
(3, 5, 'Membuat pelatihan & folder dokumen: MAMAN KARBU tse tstasts', 'Pelatihan', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-08-28 13:21:49', '2026-08-28 13:21:49'),
(4, 5, 'Membuat pelatihan & folder dokumen: Pelatihan Pengkajian Kebutuhan Pascabencana', 'Pelatihan', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-08-28 13:31:14', '2026-08-28 13:31:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `alumni_profiles`
--

CREATE TABLE `alumni_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `participant_id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED NOT NULL,
  `edu_during_training` varchar(255) NOT NULL,
  `edu_current` varchar(255) NOT NULL,
  `rank_during_training` varchar(255) NOT NULL,
  `rank_current` varchar(255) NOT NULL,
  `pos_during_training` varchar(255) NOT NULL,
  `pos_current` varchar(255) NOT NULL,
  `unit_during_training` varchar(255) NOT NULL,
  `unit_current` varchar(255) NOT NULL,
  `dept_during_training` varchar(255) NOT NULL,
  `dept_current` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `alumni_profiles`
--

INSERT INTO `alumni_profiles` (`id`, `participant_id`, `training_id`, `edu_during_training`, `edu_current`, `rank_during_training`, `rank_current`, `pos_during_training`, `pos_current`, `unit_during_training`, `unit_current`, `dept_during_training`, `dept_current`, `created_at`, `updated_at`) VALUES
(2, 12, 4, 'SD/SMP', 'SD/SMP', 'I/a', 'I/a', 'asaddasd', 'dasd', 'dasd', 'dasd', 'fgffg', 'ffdgfdfg', '2026-08-29 01:23:40', '2026-08-29 01:23:40');

-- --------------------------------------------------------

--
-- Struktur dari tabel `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `schedule_id` bigint(20) UNSIGNED NOT NULL,
  `participant_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('hadir','izin','sakit') NOT NULL,
  `check_in_at` timestamp NULL DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `timezone_label` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('integral-cache-superadmin@bpsdm.go.id|127.0.0.1', 'i:1;', 1787918399),
('integral-cache-superadmin@bpsdm.go.id|127.0.0.1:timer', 'i:1787918399;', 1787918399);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `evaluation_forms`
--

CREATE TABLE `evaluation_forms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('penyelenggara','narasumber') NOT NULL,
  `name` varchar(255) NOT NULL,
  `schedule_id` bigint(20) UNSIGNED DEFAULT NULL,
  `target_name` varchar(255) DEFAULT NULL,
  `materi` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `evaluation_forms`
--

INSERT INTO `evaluation_forms` (`id`, `training_id`, `type`, `name`, `schedule_id`, `target_name`, `materi`, `created_at`, `updated_at`) VALUES
(2, 4, 'penyelenggara', 'Penyelenggara', NULL, 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, '2026-08-28 23:36:50', '2026-08-28 23:36:50'),
(3, 4, 'narasumber', 'evaluasi pengajar', 3, 'Simpan Aku aja 22', 'Matei I untuk kegiatan itu', '2026-08-29 02:17:43', '2026-08-29 02:17:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `evaluation_questions`
--

CREATE TABLE `evaluation_questions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_type` varchar(255) DEFAULT NULL,
  `bidang` varchar(255) DEFAULT NULL,
  `training_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category` varchar(255) NOT NULL,
  `metode` varchar(255) DEFAULT 'semua',
  `sub_category` varchar(255) DEFAULT NULL,
  `question_text` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `options` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `evaluation_questions`
--

INSERT INTO `evaluation_questions` (`id`, `training_type`, `bidang`, `training_id`, `category`, `metode`, `sub_category`, `question_text`, `type`, `options`, `created_at`, `updated_at`) VALUES
(1, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kesesuaian materi pembelajaran dengan harapan/kebutuhan peserta pelatihan', 'slider', NULL, '2026-08-28 22:37:15', '2026-08-28 23:20:09'),
(2, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kepuasan terhadap manfaat materi pelatihan yang sudah diberikan', 'slider', NULL, '2026-08-28 22:37:30', '2026-08-28 23:20:14'),
(3, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Peningkatan pengetahuan', 'slider', NULL, '2026-08-28 22:37:47', '2026-08-28 23:20:25'),
(4, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Sajian Materi dalam pelatihan dalam membantu tugas-tugas peserta', 'slider', NULL, '2026-08-28 22:37:57', '2026-08-28 23:25:54'),
(5, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Perubahan Sikap/Perilaku', 'slider', NULL, '2026-08-28 22:38:07', '2026-08-28 23:26:03'),
(6, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kerapian pakaian yang digunakan oleh panitia', 'slider', NULL, '2026-08-28 22:38:34', '2026-08-28 23:26:17'),
(7, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Pelayanan panitia kepada peserta dengan 3 S (senyum, sapa, salam)', 'slider', NULL, '2026-08-28 22:38:46', '2026-08-28 23:26:33'),
(8, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kesigapan panitia terhadap kebutuhan peserta', 'slider', NULL, '2026-08-28 22:38:55', '2026-08-28 23:28:01'),
(9, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Ketersediaan panitia yang kompeten melayani peserta pelatihan', 'slider', NULL, '2026-08-28 22:39:01', '2026-08-28 23:28:11'),
(10, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Penyelenggaraan pelatihan secara keseluruhan', 'slider', NULL, '2026-08-28 22:39:11', '2026-08-28 23:28:19'),
(11, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Administrasi Program (undangan, pendaftaran peserta, dll)', 'slider', NULL, '2026-08-28 22:39:22', '2026-08-28 23:28:27'),
(12, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kesesuaian soal pre & post test dengan materi yang diajarkan', 'slider', NULL, '2026-08-28 22:39:34', '2026-08-28 23:28:35'),
(13, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Ketersediaan waktu yang cukup dalam pengerjaan soal pre & post test', 'slider', NULL, '2026-08-28 22:39:42', '2026-08-28 23:28:49'),
(14, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Ketertiban penyelenggaraan pre & post test', 'slider', NULL, '2026-08-28 22:39:50', '2026-08-28 23:28:56'),
(15, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Profesionalitas panitia dalam memandu pre & post test', 'slider', NULL, '2026-08-28 22:39:59', '2026-08-28 23:29:04'),
(16, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Materi / Paparan yang sudah sesuai dengan kebutuhan Bapak / Ibu :', 'text', NULL, '2026-08-28 22:40:08', '2026-08-28 23:29:13'),
(17, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Materi / Paparan yang perlu diperbaiki :', 'text', NULL, '2026-08-28 22:40:18', '2026-08-28 23:29:23'),
(18, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Mohon Saudara berikan komentar untuk perbaikan kinerja kami :', 'text', NULL, '2026-08-28 22:40:44', '2026-08-28 23:36:15'),
(19, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_narasumber', 'semua', NULL, 'Kerapian dan kesopanan pakaian yang dikenakan oleh Pengajar', 'slider', NULL, '2026-08-28 22:42:00', '2026-08-28 22:42:00'),
(20, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_narasumber', 'semua', NULL, 'Kedisiplinan kehadiran sesuai jadwal', 'slider', NULL, '2026-08-28 22:42:10', '2026-08-28 22:42:10'),
(21, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan memberikan motivasi kepada peserta diklat', 'slider', NULL, '2026-08-28 22:42:19', '2026-08-28 22:42:19'),
(22, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan menggunakan media pembelajaran', 'slider', NULL, '2026-08-28 22:42:32', '2026-08-28 22:42:32'),
(23, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan berkomunikasi dan berinteraksi dengan peserta diklat', 'slider', NULL, '2026-08-28 22:42:44', '2026-08-28 22:42:44'),
(24, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan menyampaikan konsep / materi', 'slider', NULL, '2026-08-28 22:42:58', '2026-08-28 22:42:58'),
(25, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan menghubungkan konsep / materi dengan praktek', 'slider', NULL, '2026-08-28 22:43:11', '2026-08-28 22:43:11'),
(26, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan merespon pertanyaan', 'slider', NULL, '2026-08-28 22:43:20', '2026-08-28 22:43:20'),
(27, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_narasumber', 'semua', NULL, 'Kualitas bahan ajar dalam membantu proses pembelajaran peserta diklat', 'slider', NULL, '2026-08-28 22:43:32', '2026-08-28 22:43:32'),
(28, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_narasumber', 'semua', NULL, 'Kesesuaian bahan ajar dengan kurikulum yang digunakan', 'slider', NULL, '2026-08-28 22:43:43', '2026-08-28 22:43:43'),
(29, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_narasumber', 'semua', NULL, 'Kesesuaian materi pembelajaran dengan keadaan terkini', 'slider', NULL, '2026-08-28 22:43:55', '2026-08-28 22:43:55'),
(30, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_narasumber', 'semua', NULL, 'Manfaat materi bagi perkembangan / perbaikan diri di masa yang akan datang', 'slider', NULL, '2026-08-28 22:44:06', '2026-08-28 22:44:06'),
(31, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_narasumber', 'semua', NULL, 'Bahan ajar disajikan dalam keadaan baik dan bisa digunakan', 'slider', NULL, '2026-08-28 22:44:16', '2026-08-28 22:44:16'),
(32, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_narasumber', 'semua', NULL, 'Metode pembelajaran yang digunakan pengajar memudahkan peserta diklat memahami materi', 'slider', NULL, '2026-08-28 22:44:26', '2026-08-28 22:44:26'),
(33, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan mengelola waktu pembelajaran', 'slider', NULL, '2026-08-28 22:44:34', '2026-08-28 22:44:34'),
(34, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_narasumber', 'semua', NULL, 'Sistematika penyampaian materi pembelajaran', 'slider', NULL, '2026-08-28 22:46:41', '2026-08-28 22:46:41'),
(35, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_narasumber', 'semua', NULL, 'Menciptakan suasana kelas yang kondusif untuk belajar', 'slider', NULL, '2026-08-28 22:46:52', '2026-08-28 22:46:52'),
(36, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_narasumber', 'semua', NULL, 'Memberikan contoh yang membantu memahami konsep yang sulit', 'slider', NULL, '2026-08-28 22:47:09', '2026-08-28 22:47:09'),
(37, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_narasumber', 'semua', NULL, 'Memberikan umpan balik yang konstruktif', 'slider', NULL, '2026-08-28 22:47:19', '2026-08-28 22:47:19'),
(38, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Pedoman Penggunaan web\'elearning informatif dan mudah dipahami', 'slider', NULL, '2026-08-28 23:30:47', '2026-08-28 23:40:05'),
(39, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Website e-learning mudah diakses', 'slider', NULL, '2026-08-28 23:30:56', '2026-08-28 23:39:54'),
(40, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Kemudahan Fitur yang tersedia', 'slider', NULL, '2026-08-28 23:31:08', '2026-08-28 23:39:29'),
(41, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Sistematika penyajian materi', 'slider', NULL, '2026-08-28 23:31:29', '2026-08-28 23:39:17'),
(42, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Tampilan tayangan', 'slider', NULL, '2026-08-28 23:31:40', '2026-08-28 23:39:06'),
(43, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Tujuan pembelajaran dapat tercapai secara optimal', 'slider', NULL, '2026-08-28 23:31:52', '2026-08-28 23:38:57'),
(44, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Kualitas bahan ajar', 'slider', NULL, '2026-08-28 23:32:07', '2026-08-28 23:38:49'),
(45, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Komposisi materi pelatihan', 'slider', NULL, '2026-08-28 23:32:17', '2026-08-28 23:38:43'),
(46, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Sekuensi materi pelatihan', 'slider', NULL, '2026-08-28 23:32:32', '2026-08-28 23:38:35'),
(47, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Durasi penyelenggaraan pelatihan', 'slider', NULL, '2026-08-28 23:32:44', '2026-08-28 23:38:23'),
(48, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Catatan / Saran', 'text', NULL, '2026-08-28 23:32:55', '2026-08-28 23:38:16'),
(49, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'blended', NULL, 'Persyaratan administrative sesuai ketentuan', 'slider', NULL, '2026-08-28 23:41:16', '2026-08-28 23:41:16'),
(50, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kecepatan atau responsivitas penyelenggara dalam memberikan layanan', 'slider', NULL, '2026-08-28 23:41:37', '2026-08-28 23:41:37'),
(51, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'blended', NULL, 'Keramahan penyelenggara', 'slider', NULL, '2026-08-28 23:41:53', '2026-08-28 23:41:53'),
(52, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'blended', NULL, 'Sarana dan prasarana (daring) sudah memadai', 'slider', NULL, '2026-08-28 23:42:07', '2026-08-28 23:42:07'),
(53, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kemudahan mengakses jadwal', 'slider', NULL, '2026-08-28 23:42:24', '2026-08-28 23:42:24'),
(54, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kedisiplinan penerapan jadwal pelatihan', 'slider', NULL, '2026-08-28 23:42:36', '2026-08-28 23:42:36'),
(55, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kecukupan waktu tutorial dan praktek', 'slider', NULL, '2026-08-28 23:42:53', '2026-08-28 23:42:53'),
(56, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'blended', NULL, 'Efektifitas pembimbingan dengan distance learning', 'slider', NULL, '2026-08-28 23:43:09', '2026-08-28 23:43:09'),
(57, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'blended', NULL, 'Tujuan pembelajaran dapat tercapai secara optimal', 'slider', NULL, '2026-08-28 23:43:23', '2026-08-28 23:43:23'),
(58, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kualitas bahan ajar', 'slider', NULL, '2026-08-28 23:43:37', '2026-08-28 23:43:37'),
(59, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'blended', NULL, 'Komposisi materi pelatihan', 'slider', NULL, '2026-08-28 23:43:51', '2026-08-28 23:43:51'),
(60, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'blended', NULL, 'Sekuensi pelatihan', 'slider', NULL, '2026-08-28 23:44:05', '2026-08-28 23:44:05'),
(61, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'blended', NULL, 'Durasi Penyelenggaraan pelatihan', 'slider', NULL, '2026-08-28 23:44:17', '2026-08-28 23:44:17'),
(62, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'blended', NULL, 'Apa yang paling berkesan bagi anda dalam pelatihan ini', 'slider', NULL, '2026-08-28 23:44:33', '2026-08-28 23:44:33'),
(63, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'blended', NULL, 'Apa yang membuat anda kurang/tidak puas dari pelatihan ini', 'text', NULL, '2026-08-28 23:44:48', '2026-08-28 23:44:48'),
(64, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'blended', NULL, 'Secara umum penilaian anda terhadap penyelenggaraan', 'text', NULL, '2026-08-28 23:45:01', '2026-08-28 23:45:01'),
(65, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'blended', NULL, 'Menurut anda, kedepannya apa yang perlu ditambahkan dalam pelatihan ini?', 'text', NULL, '2026-08-28 23:45:18', '2026-08-28 23:45:18'),
(66, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l1_penyelenggara', 'blended', NULL, 'Catatan / Saran', 'text', NULL, '2026-08-28 23:45:34', '2026-08-28 23:45:34'),
(133, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kesesuaian materi pembelajaran dengan harapan/kebutuhan peserta pelatihan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(134, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kepuasan terhadap manfaat materi pelatihan yang sudah diberikan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(135, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Peningkatan pengetahuan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(136, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Sajian Materi dalam pelatihan dalam membantu tugas-tugas peserta', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(137, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Perubahan Sikap/Perilaku', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(138, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kerapian pakaian yang digunakan oleh panitia', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(139, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Pelayanan panitia kepada peserta dengan 3 S (senyum, sapa, salam)', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(140, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kesigapan panitia terhadap kebutuhan peserta', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(141, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Ketersediaan panitia yang kompeten melayani peserta pelatihan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(142, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Penyelenggaraan pelatihan secara keseluruhan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(143, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Administrasi Program (undangan, pendaftaran peserta, dll)', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(144, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kesesuaian soal pre & post test dengan materi yang diajarkan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(145, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Ketersediaan waktu yang cukup dalam pengerjaan soal pre & post test', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(146, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Ketertiban penyelenggaraan pre & post test', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(147, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Profesionalitas panitia dalam memandu pre & post test', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(148, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Materi / Paparan yang sudah sesuai dengan kebutuhan Bapak / Ibu :', 'text', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(149, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Materi / Paparan yang perlu diperbaiki :', 'text', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(150, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Mohon Saudara berikan komentar untuk perbaikan kinerja kami :', 'text', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(151, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_narasumber', 'semua', NULL, 'Kerapian dan kesopanan pakaian yang dikenakan oleh Pengajar', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(152, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_narasumber', 'semua', NULL, 'Kedisiplinan kehadiran sesuai jadwal', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(153, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan memberikan motivasi kepada peserta diklat', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(154, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan menggunakan media pembelajaran', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(155, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan berkomunikasi dan berinteraksi dengan peserta diklat', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(156, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan menyampaikan konsep / materi', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(157, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan menghubungkan konsep / materi dengan praktek', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(158, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan merespon pertanyaan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(159, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_narasumber', 'semua', NULL, 'Kualitas bahan ajar dalam membantu proses pembelajaran peserta diklat', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(160, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_narasumber', 'semua', NULL, 'Kesesuaian bahan ajar dengan kurikulum yang digunakan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(161, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_narasumber', 'semua', NULL, 'Kesesuaian materi pembelajaran dengan keadaan terkini', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(162, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_narasumber', 'semua', NULL, 'Manfaat materi bagi perkembangan / perbaikan diri di masa yang akan datang', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(163, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_narasumber', 'semua', NULL, 'Bahan ajar disajikan dalam keadaan baik dan bisa digunakan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(164, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_narasumber', 'semua', NULL, 'Metode pembelajaran yang digunakan pengajar memudahkan peserta diklat memahami materi', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(165, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan mengelola waktu pembelajaran', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(166, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_narasumber', 'semua', NULL, 'Sistematika penyampaian materi pembelajaran', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(167, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_narasumber', 'semua', NULL, 'Menciptakan suasana kelas yang kondusif untuk belajar', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(168, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_narasumber', 'semua', NULL, 'Memberikan contoh yang membantu memahami konsep yang sulit', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(169, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_narasumber', 'semua', NULL, 'Memberikan umpan balik yang konstruktif', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(170, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Pedoman Penggunaan web\'elearning informatif dan mudah dipahami', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(171, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Website e-learning mudah diakses', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(172, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Kemudahan Fitur yang tersedia', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(173, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Sistematika penyajian materi', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(174, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Tampilan tayangan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(175, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Tujuan pembelajaran dapat tercapai secara optimal', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(176, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Kualitas bahan ajar', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(177, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Komposisi materi pelatihan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(178, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Sekuensi materi pelatihan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(179, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Durasi penyelenggaraan pelatihan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(180, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Catatan / Saran', 'text', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(181, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'blended', NULL, 'Persyaratan administrative sesuai ketentuan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(182, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kecepatan atau responsivitas penyelenggara dalam memberikan layanan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(183, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'blended', NULL, 'Keramahan penyelenggara', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(184, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'blended', NULL, 'Sarana dan prasarana (daring) sudah memadai', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(185, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kemudahan mengakses jadwal', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(186, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kedisiplinan penerapan jadwal pelatihan', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(187, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kecukupan waktu tutorial dan praktek', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(188, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'blended', NULL, 'Efektifitas pembimbingan dengan distance learning', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(189, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'blended', NULL, 'Tujuan pembelajaran dapat tercapai secara optimal', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(190, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kualitas bahan ajar', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(191, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'blended', NULL, 'Komposisi materi pelatihan', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(192, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'blended', NULL, 'Sekuensi pelatihan', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(193, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'blended', NULL, 'Durasi Penyelenggaraan pelatihan', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(194, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'blended', NULL, 'Apa yang paling berkesan bagi anda dalam pelatihan ini', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(195, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'blended', NULL, 'Apa yang membuat anda kurang/tidak puas dari pelatihan ini', 'text', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(196, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'blended', NULL, 'Secara umum penilaian anda terhadap penyelenggaraan', 'text', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(197, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'blended', NULL, 'Menurut anda, kedepannya apa yang perlu ditambahkan dalam pelatihan ini?', 'text', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(198, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l1_penyelenggara', 'blended', NULL, 'Catatan / Saran', 'text', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(199, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kesesuaian materi pembelajaran dengan harapan/kebutuhan peserta pelatihan', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(200, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kepuasan terhadap manfaat materi pelatihan yang sudah diberikan', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(201, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Peningkatan pengetahuan', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(202, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Sajian Materi dalam pelatihan dalam membantu tugas-tugas peserta', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(203, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Perubahan Sikap/Perilaku', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(204, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kerapian pakaian yang digunakan oleh panitia', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(205, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Pelayanan panitia kepada peserta dengan 3 S (senyum, sapa, salam)', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(206, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kesigapan panitia terhadap kebutuhan peserta', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(207, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Ketersediaan panitia yang kompeten melayani peserta pelatihan', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(208, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Penyelenggaraan pelatihan secara keseluruhan', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(209, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Administrasi Program (undangan, pendaftaran peserta, dll)', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(210, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kesesuaian soal pre & post test dengan materi yang diajarkan', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(211, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Ketersediaan waktu yang cukup dalam pengerjaan soal pre & post test', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(212, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Ketertiban penyelenggaraan pre & post test', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(213, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Profesionalitas panitia dalam memandu pre & post test', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(214, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Materi / Paparan yang sudah sesuai dengan kebutuhan Bapak / Ibu :', 'text', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(215, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Materi / Paparan yang perlu diperbaiki :', 'text', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(216, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Mohon Saudara berikan komentar untuk perbaikan kinerja kami :', 'text', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(217, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_narasumber', 'semua', NULL, 'Kerapian dan kesopanan pakaian yang dikenakan oleh Pengajar', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(218, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_narasumber', 'semua', NULL, 'Kedisiplinan kehadiran sesuai jadwal', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(219, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan memberikan motivasi kepada peserta diklat', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(220, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan menggunakan media pembelajaran', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(221, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan berkomunikasi dan berinteraksi dengan peserta diklat', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(222, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan menyampaikan konsep / materi', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(223, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan menghubungkan konsep / materi dengan praktek', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(224, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan merespon pertanyaan', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(225, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_narasumber', 'semua', NULL, 'Kualitas bahan ajar dalam membantu proses pembelajaran peserta diklat', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(226, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_narasumber', 'semua', NULL, 'Kesesuaian bahan ajar dengan kurikulum yang digunakan', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(227, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_narasumber', 'semua', NULL, 'Kesesuaian materi pembelajaran dengan keadaan terkini', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(228, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_narasumber', 'semua', NULL, 'Manfaat materi bagi perkembangan / perbaikan diri di masa yang akan datang', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(229, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_narasumber', 'semua', NULL, 'Bahan ajar disajikan dalam keadaan baik dan bisa digunakan', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(230, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_narasumber', 'semua', NULL, 'Metode pembelajaran yang digunakan pengajar memudahkan peserta diklat memahami materi', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(231, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan mengelola waktu pembelajaran', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(232, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_narasumber', 'semua', NULL, 'Sistematika penyampaian materi pembelajaran', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(233, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_narasumber', 'semua', NULL, 'Menciptakan suasana kelas yang kondusif untuk belajar', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(234, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_narasumber', 'semua', NULL, 'Memberikan contoh yang membantu memahami konsep yang sulit', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(235, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_narasumber', 'semua', NULL, 'Memberikan umpan balik yang konstruktif', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(236, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Pedoman Penggunaan web\'elearning informatif dan mudah dipahami', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(237, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Website e-learning mudah diakses', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(238, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Kemudahan Fitur yang tersedia', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(239, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Sistematika penyajian materi', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(240, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Tampilan tayangan', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(241, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Tujuan pembelajaran dapat tercapai secara optimal', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(242, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Kualitas bahan ajar', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(243, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Komposisi materi pelatihan', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(244, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Sekuensi materi pelatihan', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(245, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Durasi penyelenggaraan pelatihan', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(246, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Catatan / Saran', 'text', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(247, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'blended', NULL, 'Persyaratan administrative sesuai ketentuan', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(248, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kecepatan atau responsivitas penyelenggara dalam memberikan layanan', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(249, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'blended', NULL, 'Keramahan penyelenggara', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(250, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'blended', NULL, 'Sarana dan prasarana (daring) sudah memadai', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(251, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kemudahan mengakses jadwal', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(252, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kedisiplinan penerapan jadwal pelatihan', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(253, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kecukupan waktu tutorial dan praktek', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(254, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'blended', NULL, 'Efektifitas pembimbingan dengan distance learning', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(255, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'blended', NULL, 'Tujuan pembelajaran dapat tercapai secara optimal', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(256, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kualitas bahan ajar', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(257, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'blended', NULL, 'Komposisi materi pelatihan', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(258, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'blended', NULL, 'Sekuensi pelatihan', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(259, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'blended', NULL, 'Durasi Penyelenggaraan pelatihan', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(260, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'blended', NULL, 'Apa yang paling berkesan bagi anda dalam pelatihan ini', 'slider', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(261, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'blended', NULL, 'Apa yang membuat anda kurang/tidak puas dari pelatihan ini', 'text', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(262, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'blended', NULL, 'Secara umum penilaian anda terhadap penyelenggaraan', 'text', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(263, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'blended', NULL, 'Menurut anda, kedepannya apa yang perlu ditambahkan dalam pelatihan ini?', 'text', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(264, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l1_penyelenggara', 'blended', NULL, 'Catatan / Saran', 'text', NULL, '2026-08-28 23:55:36', '2026-08-28 23:55:36'),
(265, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pendidikan terakhir (saat pelatihan)', 'dropdown', '[\"SD\\/SMP\",\"SMA\\/SMK\",\"D3\",\"S1\\/D4\",\"S2\\/S3\"]', '2026-08-28 23:58:53', '2026-08-28 23:58:53'),
(268, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pendidikan terakhir (saat ini)', 'dropdown', '[\"SD\\/SMP\",\"SMA\\/SMK\",\"D3\",\"S1\\/D4\",\"S2\\/S3\"]', '2026-08-29 00:14:15', '2026-08-29 00:14:41');
INSERT INTO `evaluation_questions` (`id`, `training_type`, `bidang`, `training_id`, `category`, `metode`, `sub_category`, `question_text`, `type`, `options`, `created_at`, `updated_at`) VALUES
(270, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pangkat golongan (saat pelatihan)', 'dropdown', '[\"I\\/a\",\"II\\/a\",\"II\\/b\",\"II\\/c\",\"II\\/d\",\"III\\/a\",\"III\\/b\",\"III\\/c\",\"III\\/d\",\"IV\\/a\",\"IV\\/b\",\"IV\\/b\",\"IV\\/c\",\"IV\\/d\",\"IV\\/e\"]', '2026-08-29 00:17:27', '2026-08-29 00:17:27'),
(271, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pangkat golongan (saat ini)', 'dropdown', '[\"I\\/a\",\"II\\/a\",\"II\\/b\",\"II\\/c\",\"II\\/d\",\"III\\/a\",\"III\\/b\",\"III\\/c\",\"III\\/d\",\"IV\\/a\",\"IV\\/b\",\"IV\\/b\",\"IV\\/c\",\"IV\\/d\",\"IV\\/e\"]', '2026-08-29 00:17:32', '2026-08-29 00:17:47'),
(272, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Jabatan (saat pelatihan)', 'text', NULL, '2026-08-29 00:18:18', '2026-08-29 00:18:18'),
(273, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Jabatan (saat ini)', 'text', NULL, '2026-08-29 00:19:00', '2026-08-29 00:19:00'),
(274, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Unit kerja (saat pelatihan)', 'text', NULL, '2026-08-29 00:19:14', '2026-08-29 00:19:14'),
(275, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Unit kerja (saat ini)', 'text', NULL, '2026-08-29 00:19:26', '2026-08-29 00:19:26'),
(276, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Perangkat daerah (saat pelatihan)', 'text', NULL, '2026-08-29 00:19:40', '2026-08-29 00:19:40'),
(277, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Perangkat daerah (saat ini)', 'text', NULL, '2026-08-29 00:19:56', '2026-08-29 00:20:05'),
(278, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah saat ini Anda sedang bertugas yang berkaitan dengan pelatihan?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 00:38:39', '2026-08-29 00:38:39'),
(279, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Iya, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 00:38:44', '2026-08-29 00:38:57'),
(280, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 00:39:07', '2026-08-29 00:39:15'),
(281, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pelatihan memiliki keterkaitan dengan bidang tugas Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 00:39:41', '2026-08-29 00:39:47'),
(282, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah Anda sudah melakukan transfer learning hasil pelatihan pada rekan kerja di tempat kerja Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 00:39:58', '2026-08-29 00:40:05'),
(283, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila sudah transfer learning, bagaimana cara Anda melakukan transfer learning?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 00:40:16', '2026-08-29 00:40:23'),
(284, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya memahami bahwa sumber daya yang diperlukan untuk implementasi materi pembelajaran di lingkungan kerja tersedia secara memadai', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:41:47', '2026-08-29 00:41:47'),
(285, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam menunjang pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:41:58', '2026-08-29 00:42:04'),
(286, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam keberhasilan pelaksanaan pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:42:17', '2026-08-29 00:42:23'),
(287, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam penyelesaian pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:42:41', '2026-08-29 00:42:49'),
(288, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya berkomitmen untuk implementasi materi pelatihan di lingkungan kerja saat ini', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:43:01', '2026-08-29 00:43:08'),
(290, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Bila sebagian besar jawaban Anda untuk pertanyaan nomor 1 sd 6 adalah Cukup, Kurang, Sangat Kurang, maka alasannya adalah (bisa pilih lebih dari satu jawaban)', 'checkbox', '[\"Saya tidak mempunyai pengetahuan dan keterampilan yang memadai\",\"Saya tidak punya rencana implementasi materi pembelajaran secara jelas dan terukur\",\"Saya mempunyai prioritas pekerjaan lain yang tidak sesuai dengan materi pembelajaran\",\"Saya tidak mempunyai dukungan sumber daya yang memadai untuk implementasi materi pembelajaran\",\"Pembelajaran yang saya ikuti tidak memberikan nilai tambah bagi saya\",\"Saya merasa bahwa materi pembelajaran tidak relevan dengan pekerjaan saya\",\"Saya merasa tidak ada kebijakan yang mendukung proses implementasi materi pembelajaran di lingkungan kerja\"]', '2026-08-29 00:53:46', '2026-08-29 00:53:46'),
(291, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap unit kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:54:48', '2026-08-29 00:54:48'),
(292, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap pengetahuan teoritis atau konsep Anda', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:55:02', '2026-08-29 00:55:15'),
(293, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan produktivitas', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:55:32', '2026-08-29 00:55:42'),
(294, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap perbaikan kualitas hasil kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:56:01', '2026-08-29 00:56:07'),
(295, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan kepuasan pelanggan', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:56:21', '2026-08-29 00:56:26'),
(296, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap penguatan hubungan antara rekan-rekan kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:56:43', '2026-08-29 00:56:57'),
(297, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Nama Anda', 'text', NULL, '2026-08-29 00:58:05', '2026-08-29 00:58:05'),
(298, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'NIP (ASN) / NIK (Non ASN) Anda', 'text', NULL, '2026-08-29 00:58:25', '2026-08-29 00:58:25'),
(299, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Jabatan Anda', 'text', NULL, '2026-08-29 00:58:55', '2026-08-29 00:58:55'),
(300, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Unit Kerja Anda', 'text', NULL, '2026-08-29 00:59:14', '2026-08-29 00:59:14'),
(301, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Perangkat Daerah Anda', 'text', NULL, '2026-08-29 00:59:31', '2026-08-29 00:59:31'),
(302, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'Nama Anda', 'text', NULL, '2026-08-29 01:00:08', '2026-08-29 01:00:08'),
(303, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'NIP (ASN) / NIK (Non ASN) Anda', 'text', NULL, '2026-08-29 01:00:24', '2026-08-29 01:00:24'),
(304, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'Jabatan Anda', 'text', NULL, '2026-08-29 01:00:38', '2026-08-29 01:00:38'),
(305, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'Unit Kerja Anda', 'text', NULL, '2026-08-29 01:00:51', '2026-08-29 01:00:51'),
(306, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'Perangkat Daerah Anda', 'text', NULL, '2026-08-29 01:01:05', '2026-08-29 01:01:05'),
(307, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah saat ini Anda sedang bertugas yang berkaitan dengan pelatihan?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(308, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Iya, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(309, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(310, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pelatihan memiliki keterkaitan dengan bidang tugas Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(311, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah Anda sudah melakukan transfer learning hasil pelatihan pada rekan kerja di tempat kerja Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(312, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila sudah transfer learning, bagaimana cara Anda melakukan transfer learning?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(313, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya memahami bahwa sumber daya yang diperlukan untuk implementasi materi pembelajaran di lingkungan kerja tersedia secara memadai', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(314, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam menunjang pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(315, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam keberhasilan pelaksanaan pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(316, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam penyelesaian pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(317, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya berkomitmen untuk implementasi materi pelatihan di lingkungan kerja saat ini', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(318, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Bila sebagian besar jawaban Anda untuk pertanyaan nomor 1 sd 6 adalah Cukup, Kurang, Sangat Kurang, maka alasannya adalah (bisa pilih lebih dari satu jawaban)', 'checkbox', '[\"Saya tidak mempunyai pengetahuan dan keterampilan yang memadai\",\"Saya tidak punya rencana implementasi materi pembelajaran secara jelas dan terukur\",\"Saya mempunyai prioritas pekerjaan lain yang tidak sesuai dengan materi pembelajaran\",\"Saya tidak mempunyai dukungan sumber daya yang memadai untuk implementasi materi pembelajaran\",\"Pembelajaran yang saya ikuti tidak memberikan nilai tambah bagi saya\",\"Saya merasa bahwa materi pembelajaran tidak relevan dengan pekerjaan saya\",\"Saya merasa tidak ada kebijakan yang mendukung proses implementasi materi pembelajaran di lingkungan kerja\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(319, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap unit kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(320, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap pengetahuan teoritis atau konsep Anda', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(321, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan produktivitas', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(322, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap perbaikan kualitas hasil kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(323, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan kepuasan pelanggan', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(324, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap penguatan hubungan antara rekan-rekan kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(325, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah saat ini Anda sedang bertugas yang berkaitan dengan pelatihan?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(326, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Iya, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(327, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(328, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pelatihan memiliki keterkaitan dengan bidang tugas Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(329, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah Anda sudah melakukan transfer learning hasil pelatihan pada rekan kerja di tempat kerja Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(330, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila sudah transfer learning, bagaimana cara Anda melakukan transfer learning?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(331, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya memahami bahwa sumber daya yang diperlukan untuk implementasi materi pembelajaran di lingkungan kerja tersedia secara memadai', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(332, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam menunjang pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(333, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam keberhasilan pelaksanaan pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(334, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam penyelesaian pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(335, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya berkomitmen untuk implementasi materi pelatihan di lingkungan kerja saat ini', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(336, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Bila sebagian besar jawaban Anda untuk pertanyaan nomor 1 sd 6 adalah Cukup, Kurang, Sangat Kurang, maka alasannya adalah (bisa pilih lebih dari satu jawaban)', 'checkbox', '[\"Saya tidak mempunyai pengetahuan dan keterampilan yang memadai\",\"Saya tidak punya rencana implementasi materi pembelajaran secara jelas dan terukur\",\"Saya mempunyai prioritas pekerjaan lain yang tidak sesuai dengan materi pembelajaran\",\"Saya tidak mempunyai dukungan sumber daya yang memadai untuk implementasi materi pembelajaran\",\"Pembelajaran yang saya ikuti tidak memberikan nilai tambah bagi saya\",\"Saya merasa bahwa materi pembelajaran tidak relevan dengan pekerjaan saya\",\"Saya merasa tidak ada kebijakan yang mendukung proses implementasi materi pembelajaran di lingkungan kerja\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(337, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap unit kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(338, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap pengetahuan teoritis atau konsep Anda', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(339, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan produktivitas', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(340, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap perbaikan kualitas hasil kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(341, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan kepuasan pelanggan', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(342, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap penguatan hubungan antara rekan-rekan kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(343, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pendidikan terakhir (saat pelatihan)', 'dropdown', '[\"SD\\/SMP\",\"SMA\\/SMK\",\"D3\",\"S1\\/D4\",\"S2\\/S3\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(344, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pendidikan terakhir (saat ini)', 'dropdown', '[\"SD\\/SMP\",\"SMA\\/SMK\",\"D3\",\"S1\\/D4\",\"S2\\/S3\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(345, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pangkat golongan (saat pelatihan)', 'dropdown', '[\"I\\/a\",\"II\\/a\",\"II\\/b\",\"II\\/c\",\"II\\/d\",\"III\\/a\",\"III\\/b\",\"III\\/c\",\"III\\/d\",\"IV\\/a\",\"IV\\/b\",\"IV\\/b\",\"IV\\/c\",\"IV\\/d\",\"IV\\/e\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(346, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pangkat golongan (saat ini)', 'dropdown', '[\"I\\/a\",\"II\\/a\",\"II\\/b\",\"II\\/c\",\"II\\/d\",\"III\\/a\",\"III\\/b\",\"III\\/c\",\"III\\/d\",\"IV\\/a\",\"IV\\/b\",\"IV\\/b\",\"IV\\/c\",\"IV\\/d\",\"IV\\/e\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(347, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Jabatan (saat pelatihan)', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(348, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Jabatan (saat ini)', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(349, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Unit kerja (saat pelatihan)', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(350, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Unit kerja (saat ini)', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(351, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Perangkat daerah (saat pelatihan)', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(352, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Perangkat daerah (saat ini)', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(353, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah saat ini Anda sedang bertugas yang berkaitan dengan pelatihan?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(354, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Iya, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(355, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(356, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pelatihan memiliki keterkaitan dengan bidang tugas Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(357, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah Anda sudah melakukan transfer learning hasil pelatihan pada rekan kerja di tempat kerja Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(358, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila sudah transfer learning, bagaimana cara Anda melakukan transfer learning?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(359, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya memahami bahwa sumber daya yang diperlukan untuk implementasi materi pembelajaran di lingkungan kerja tersedia secara memadai', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(360, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam menunjang pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(361, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam keberhasilan pelaksanaan pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(362, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam penyelesaian pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(363, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya berkomitmen untuk implementasi materi pelatihan di lingkungan kerja saat ini', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(364, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Bila sebagian besar jawaban Anda untuk pertanyaan nomor 1 sd 6 adalah Cukup, Kurang, Sangat Kurang, maka alasannya adalah (bisa pilih lebih dari satu jawaban)', 'checkbox', '[\"Saya tidak mempunyai pengetahuan dan keterampilan yang memadai\",\"Saya tidak punya rencana implementasi materi pembelajaran secara jelas dan terukur\",\"Saya mempunyai prioritas pekerjaan lain yang tidak sesuai dengan materi pembelajaran\",\"Saya tidak mempunyai dukungan sumber daya yang memadai untuk implementasi materi pembelajaran\",\"Pembelajaran yang saya ikuti tidak memberikan nilai tambah bagi saya\",\"Saya merasa bahwa materi pembelajaran tidak relevan dengan pekerjaan saya\",\"Saya merasa tidak ada kebijakan yang mendukung proses implementasi materi pembelajaran di lingkungan kerja\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(365, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap unit kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(366, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap pengetahuan teoritis atau konsep Anda', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(367, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan produktivitas', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(368, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap perbaikan kualitas hasil kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(369, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan kepuasan pelanggan', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(370, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap penguatan hubungan antara rekan-rekan kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(371, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Nama Anda', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(372, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'NIP (ASN) / NIK (Non ASN) Anda', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(373, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Jabatan Anda', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(374, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Unit Kerja Anda', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(375, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Perangkat Daerah Anda', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(376, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'Nama Anda', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(377, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'NIP (ASN) / NIK (Non ASN) Anda', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(378, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'Jabatan Anda', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(379, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'Unit Kerja Anda', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(380, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'Perangkat Daerah Anda', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(381, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah saat ini Anda sedang bertugas yang berkaitan dengan pelatihan?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(382, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Iya, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(383, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(384, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pelatihan memiliki keterkaitan dengan bidang tugas Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(385, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah Anda sudah melakukan transfer learning hasil pelatihan pada rekan kerja di tempat kerja Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(386, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila sudah transfer learning, bagaimana cara Anda melakukan transfer learning?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(387, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya memahami bahwa sumber daya yang diperlukan untuk implementasi materi pembelajaran di lingkungan kerja tersedia secara memadai', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(388, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam menunjang pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(389, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam keberhasilan pelaksanaan pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(390, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam penyelesaian pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(391, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya berkomitmen untuk implementasi materi pelatihan di lingkungan kerja saat ini', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(392, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Bila sebagian besar jawaban Anda untuk pertanyaan nomor 1 sd 6 adalah Cukup, Kurang, Sangat Kurang, maka alasannya adalah (bisa pilih lebih dari satu jawaban)', 'checkbox', '[\"Saya tidak mempunyai pengetahuan dan keterampilan yang memadai\",\"Saya tidak punya rencana implementasi materi pembelajaran secara jelas dan terukur\",\"Saya mempunyai prioritas pekerjaan lain yang tidak sesuai dengan materi pembelajaran\",\"Saya tidak mempunyai dukungan sumber daya yang memadai untuk implementasi materi pembelajaran\",\"Pembelajaran yang saya ikuti tidak memberikan nilai tambah bagi saya\",\"Saya merasa bahwa materi pembelajaran tidak relevan dengan pekerjaan saya\",\"Saya merasa tidak ada kebijakan yang mendukung proses implementasi materi pembelajaran di lingkungan kerja\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(393, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap unit kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(394, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap pengetahuan teoritis atau konsep Anda', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(395, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan produktivitas', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(396, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap perbaikan kualitas hasil kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(397, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan kepuasan pelanggan', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(398, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap penguatan hubungan antara rekan-rekan kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(399, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah saat ini Anda sedang bertugas yang berkaitan dengan pelatihan?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(400, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Iya, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(401, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(402, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pelatihan memiliki keterkaitan dengan bidang tugas Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(403, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah Anda sudah melakukan transfer learning hasil pelatihan pada rekan kerja di tempat kerja Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(404, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila sudah transfer learning, bagaimana cara Anda melakukan transfer learning?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(405, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya memahami bahwa sumber daya yang diperlukan untuk implementasi materi pembelajaran di lingkungan kerja tersedia secara memadai', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(406, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam menunjang pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(407, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam keberhasilan pelaksanaan pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(408, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam penyelesaian pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(409, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya berkomitmen untuk implementasi materi pelatihan di lingkungan kerja saat ini', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(410, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Bila sebagian besar jawaban Anda untuk pertanyaan nomor 1 sd 6 adalah Cukup, Kurang, Sangat Kurang, maka alasannya adalah (bisa pilih lebih dari satu jawaban)', 'checkbox', '[\"Saya tidak mempunyai pengetahuan dan keterampilan yang memadai\",\"Saya tidak punya rencana implementasi materi pembelajaran secara jelas dan terukur\",\"Saya mempunyai prioritas pekerjaan lain yang tidak sesuai dengan materi pembelajaran\",\"Saya tidak mempunyai dukungan sumber daya yang memadai untuk implementasi materi pembelajaran\",\"Pembelajaran yang saya ikuti tidak memberikan nilai tambah bagi saya\",\"Saya merasa bahwa materi pembelajaran tidak relevan dengan pekerjaan saya\",\"Saya merasa tidak ada kebijakan yang mendukung proses implementasi materi pembelajaran di lingkungan kerja\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(411, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap unit kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24');
INSERT INTO `evaluation_questions` (`id`, `training_type`, `bidang`, `training_id`, `category`, `metode`, `sub_category`, `question_text`, `type`, `options`, `created_at`, `updated_at`) VALUES
(412, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap pengetahuan teoritis atau konsep Anda', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(413, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan produktivitas', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(414, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap perbaikan kualitas hasil kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(415, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan kepuasan pelanggan', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(416, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap penguatan hubungan antara rekan-rekan kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(417, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pendidikan terakhir (saat pelatihan)', 'dropdown', '[\"SD\\/SMP\",\"SMA\\/SMK\",\"D3\",\"S1\\/D4\",\"S2\\/S3\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(418, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pendidikan terakhir (saat ini)', 'dropdown', '[\"SD\\/SMP\",\"SMA\\/SMK\",\"D3\",\"S1\\/D4\",\"S2\\/S3\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(419, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pangkat golongan (saat pelatihan)', 'dropdown', '[\"I\\/a\",\"II\\/a\",\"II\\/b\",\"II\\/c\",\"II\\/d\",\"III\\/a\",\"III\\/b\",\"III\\/c\",\"III\\/d\",\"IV\\/a\",\"IV\\/b\",\"IV\\/b\",\"IV\\/c\",\"IV\\/d\",\"IV\\/e\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(420, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pangkat golongan (saat ini)', 'dropdown', '[\"I\\/a\",\"II\\/a\",\"II\\/b\",\"II\\/c\",\"II\\/d\",\"III\\/a\",\"III\\/b\",\"III\\/c\",\"III\\/d\",\"IV\\/a\",\"IV\\/b\",\"IV\\/b\",\"IV\\/c\",\"IV\\/d\",\"IV\\/e\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(421, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Jabatan (saat pelatihan)', 'text', NULL, '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(422, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Jabatan (saat ini)', 'text', NULL, '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(423, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Unit kerja (saat pelatihan)', 'text', NULL, '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(424, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Unit kerja (saat ini)', 'text', NULL, '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(425, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Perangkat daerah (saat pelatihan)', 'text', NULL, '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(426, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Perangkat daerah (saat ini)', 'text', NULL, '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(427, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah saat ini Anda sedang bertugas yang berkaitan dengan pelatihan?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(428, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Iya, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(429, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(430, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pelatihan memiliki keterkaitan dengan bidang tugas Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(431, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah Anda sudah melakukan transfer learning hasil pelatihan pada rekan kerja di tempat kerja Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(432, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila sudah transfer learning, bagaimana cara Anda melakukan transfer learning?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(433, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya memahami bahwa sumber daya yang diperlukan untuk implementasi materi pembelajaran di lingkungan kerja tersedia secara memadai', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(434, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam menunjang pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(435, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam keberhasilan pelaksanaan pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(436, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam penyelesaian pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(437, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya berkomitmen untuk implementasi materi pelatihan di lingkungan kerja saat ini', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(438, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Bila sebagian besar jawaban Anda untuk pertanyaan nomor 1 sd 6 adalah Cukup, Kurang, Sangat Kurang, maka alasannya adalah (bisa pilih lebih dari satu jawaban)', 'checkbox', '[\"Saya tidak mempunyai pengetahuan dan keterampilan yang memadai\",\"Saya tidak punya rencana implementasi materi pembelajaran secara jelas dan terukur\",\"Saya mempunyai prioritas pekerjaan lain yang tidak sesuai dengan materi pembelajaran\",\"Saya tidak mempunyai dukungan sumber daya yang memadai untuk implementasi materi pembelajaran\",\"Pembelajaran yang saya ikuti tidak memberikan nilai tambah bagi saya\",\"Saya merasa bahwa materi pembelajaran tidak relevan dengan pekerjaan saya\",\"Saya merasa tidak ada kebijakan yang mendukung proses implementasi materi pembelajaran di lingkungan kerja\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(439, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap unit kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(440, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap pengetahuan teoritis atau konsep Anda', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(441, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan produktivitas', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(442, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap perbaikan kualitas hasil kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(443, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan kepuasan pelanggan', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(444, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap penguatan hubungan antara rekan-rekan kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(445, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Nama Anda', 'text', NULL, '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(446, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'NIP (ASN) / NIK (Non ASN) Anda', 'text', NULL, '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(447, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Jabatan Anda', 'text', NULL, '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(448, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Unit Kerja Anda', 'text', NULL, '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(449, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Perangkat Daerah Anda', 'text', NULL, '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(450, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'Nama Anda', 'text', NULL, '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(451, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'NIP (ASN) / NIK (Non ASN) Anda', 'text', NULL, '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(452, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'Jabatan Anda', 'text', NULL, '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(453, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'Unit Kerja Anda', 'text', NULL, '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(454, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'Perangkat Daerah Anda', 'text', NULL, '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(455, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah saat ini Anda sedang bertugas yang berkaitan dengan pelatihan?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(456, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Iya, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(457, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(458, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pelatihan memiliki keterkaitan dengan bidang tugas Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(459, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah Anda sudah melakukan transfer learning hasil pelatihan pada rekan kerja di tempat kerja Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(460, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila sudah transfer learning, bagaimana cara Anda melakukan transfer learning?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(461, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya memahami bahwa sumber daya yang diperlukan untuk implementasi materi pembelajaran di lingkungan kerja tersedia secara memadai', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(462, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam menunjang pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(463, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam keberhasilan pelaksanaan pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(464, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam penyelesaian pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(465, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya berkomitmen untuk implementasi materi pelatihan di lingkungan kerja saat ini', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(466, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Bila sebagian besar jawaban Anda untuk pertanyaan nomor 1 sd 6 adalah Cukup, Kurang, Sangat Kurang, maka alasannya adalah (bisa pilih lebih dari satu jawaban)', 'checkbox', '[\"Saya tidak mempunyai pengetahuan dan keterampilan yang memadai\",\"Saya tidak punya rencana implementasi materi pembelajaran secara jelas dan terukur\",\"Saya mempunyai prioritas pekerjaan lain yang tidak sesuai dengan materi pembelajaran\",\"Saya tidak mempunyai dukungan sumber daya yang memadai untuk implementasi materi pembelajaran\",\"Pembelajaran yang saya ikuti tidak memberikan nilai tambah bagi saya\",\"Saya merasa bahwa materi pembelajaran tidak relevan dengan pekerjaan saya\",\"Saya merasa tidak ada kebijakan yang mendukung proses implementasi materi pembelajaran di lingkungan kerja\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(467, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap unit kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(468, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap pengetahuan teoritis atau konsep Anda', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(469, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan produktivitas', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(470, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap perbaikan kualitas hasil kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(471, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan kepuasan pelanggan', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(472, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap penguatan hubungan antara rekan-rekan kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(473, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah saat ini Anda sedang bertugas yang berkaitan dengan pelatihan?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(474, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Iya, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(475, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(476, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pelatihan memiliki keterkaitan dengan bidang tugas Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(477, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah Anda sudah melakukan transfer learning hasil pelatihan pada rekan kerja di tempat kerja Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(478, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila sudah transfer learning, bagaimana cara Anda melakukan transfer learning?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(479, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya memahami bahwa sumber daya yang diperlukan untuk implementasi materi pembelajaran di lingkungan kerja tersedia secara memadai', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(480, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam menunjang pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(481, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam keberhasilan pelaksanaan pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(482, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam penyelesaian pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(483, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya berkomitmen untuk implementasi materi pelatihan di lingkungan kerja saat ini', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(484, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Bila sebagian besar jawaban Anda untuk pertanyaan nomor 1 sd 6 adalah Cukup, Kurang, Sangat Kurang, maka alasannya adalah (bisa pilih lebih dari satu jawaban)', 'checkbox', '[\"Saya tidak mempunyai pengetahuan dan keterampilan yang memadai\",\"Saya tidak punya rencana implementasi materi pembelajaran secara jelas dan terukur\",\"Saya mempunyai prioritas pekerjaan lain yang tidak sesuai dengan materi pembelajaran\",\"Saya tidak mempunyai dukungan sumber daya yang memadai untuk implementasi materi pembelajaran\",\"Pembelajaran yang saya ikuti tidak memberikan nilai tambah bagi saya\",\"Saya merasa bahwa materi pembelajaran tidak relevan dengan pekerjaan saya\",\"Saya merasa tidak ada kebijakan yang mendukung proses implementasi materi pembelajaran di lingkungan kerja\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(485, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap unit kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(486, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap pengetahuan teoritis atau konsep Anda', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(487, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan produktivitas', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(488, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap perbaikan kualitas hasil kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(489, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan kepuasan pelanggan', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(490, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap penguatan hubungan antara rekan-rekan kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:43', '2026-08-29 01:16:43'),
(491, 'Semua', NULL, NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Penyelenggara memiliki sertifikat MOT/TOC', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(492, 'Semua', NULL, NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Penyelenggara memiliki SP sebagai panitia', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(493, 'Semua', NULL, NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Ketersediaan pengelola kelas (pengamat dan petugas kelas)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(494, 'Semua', NULL, NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Lembar biodata peserta', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(495, 'Semua', NULL, NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Daftar Hadir Peserta dan fasilitator', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(496, 'Semua', NULL, NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Tanda pengenal peserta', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(497, 'Semua', NULL, NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Penyiapan Sertifikat Pelatihan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(498, 'Semua', NULL, NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Instrumen dan format pelatihan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(499, 'Semua', NULL, NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Administrasi pelatihan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(500, 'Semua', NULL, NULL, 'Monitoring Peserta', 'klasikal', NULL, 'Kualifikasi peserta sesuai persyaratan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(501, 'Semua', NULL, NULL, 'Monitoring Peserta', 'klasikal', NULL, 'Jumlah peserta sesuai persyaratan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(502, 'Semua', NULL, NULL, 'Monitoring Peserta', 'klasikal', NULL, 'Peserta terdaftar di BPSDM Jabar', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(503, 'Semua', NULL, NULL, 'Monitoring Peserta', 'klasikal', NULL, 'Peserta hadir minimal 85%', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(504, 'Semua', NULL, NULL, 'Monitoring Peserta', 'klasikal', NULL, 'Peserta aktif mengikuti pelatihan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(505, 'Semua', NULL, NULL, 'Monitoring Peserta', 'klasikal', NULL, 'Peserta mentaati tata tertib', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(506, 'Semua', NULL, NULL, 'Monitoring Peserta', 'klasikal', NULL, 'Peserta mengikuti evaluasi', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(507, 'Semua', NULL, NULL, 'Monitoring Tenaga Kediklatan', 'klasikal', NULL, 'Menjaga etika dalam penyelenggaraan pelatihan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(508, 'Semua', NULL, NULL, 'Monitoring Tenaga Kediklatan', 'klasikal', NULL, 'Berkoordinasi dengan fasilitator', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(509, 'Semua', NULL, NULL, 'Monitoring Tenaga Kediklatan', 'klasikal', NULL, 'Menyampaikan panduan pelatihan kepada peserta', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(510, 'Semua', NULL, NULL, 'Monitoring Tenaga Kediklatan', 'klasikal', NULL, 'Menjaga ketepatan waktu', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(511, 'Semua', NULL, NULL, 'Monitoring Tenaga Kediklatan', 'klasikal', NULL, 'Memiliki sertifikat TOT atau Workshop', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(512, 'Semua', NULL, NULL, 'Monitoring Tenaga Kediklatan', 'klasikal', NULL, 'Terdaftar di BPSDM Jabar', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(513, 'Semua', NULL, NULL, 'Monitoring Tenaga Kediklatan', 'klasikal', NULL, 'Mengarsipkan dokumen pelatihan untuk laporan pelaksanaan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(514, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'klasikal', NULL, 'Sarana: Kursi dan meja belajar', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(515, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'klasikal', NULL, 'Sarana: Proyektor dan layar', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(516, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'klasikal', NULL, 'Sarana: Jaringan internet (LAN/WAN)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(517, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'klasikal', NULL, 'Sarana: Pengeras Suara', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(518, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'klasikal', NULL, 'Sarana: Perlengkapan P3K', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(519, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'klasikal', NULL, 'Prasarana: Ruang Kelas', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(520, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'klasikal', NULL, 'Prasarana: Ruang Ibadah', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(521, 'Semua', NULL, NULL, 'Monitoring Penyelenggara', 'full learning', NULL, 'Penyelenggara memiliki Surat Perintah (SP) / SK sebagai panitia pelatihan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(522, 'Semua', NULL, NULL, 'Monitoring Penyelenggara', 'full learning', NULL, 'Pengelola Kelas yang memiliki sertifikat kompetensi (MOT/TOC/Workshop sejenis)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(523, 'Semua', NULL, NULL, 'Monitoring Penyelenggara', 'full learning', NULL, 'Penyelenggara memiliki kompetensi IT minimal', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(524, 'Semua', NULL, NULL, 'Monitoring Penyelenggara', 'full learning', NULL, 'Tersedia Administrator/Host yang kompeten mengelola jalannya kelas virtual (admit peserta, mute/unmute, share screen)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(525, 'Semua', NULL, NULL, 'Monitoring Penyelenggara', 'full learning', NULL, 'Tersedia petugas yang memantau dan mendokumentasikan kehadiran peserta dan fasilitator di setiap sesi.', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(526, 'Semua', NULL, NULL, 'Monitoring Penyelenggara', 'full learning', NULL, 'Tersedia penyelenggara yang menyiapkan, melaksanakan, dan mengolah hasil evaluasi pelatihan (Pre-test, Post-test, dan Evaluasi Penyelenggaraan).', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(527, 'Semua', NULL, NULL, 'Monitoring Peserta', 'full learning', NULL, 'Kualifikasi peserta yang hadir sesuai dengan persyaratan pelatihan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(528, 'Semua', NULL, NULL, 'Monitoring Peserta', 'full learning', NULL, 'Jumlah peserta sesuai dengan kuota yang telah ditetapkan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(529, 'Semua', NULL, NULL, 'Monitoring Peserta', 'full learning', NULL, 'Peserta telah memiliki akun yang terdaftar dan aktif di dalam Learning Management System (LMS)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(530, 'Semua', NULL, NULL, 'Monitoring Peserta', 'full learning', NULL, 'Peserta memenuhi syarat kehadiran minimal 85% dari total Jam Pelajaran (JP)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(531, 'Semua', NULL, NULL, 'Monitoring Peserta', 'full learning', NULL, 'Peserta mematuhi tata tertib kelas daring (misal: mengaktifkan kamera, mute mikrofon saat tidak berbicara, menggunakan virtual background)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(532, 'Semua', NULL, NULL, 'Monitoring Peserta', 'full learning', NULL, 'Peserta mengikuti dan menyelesaikan seluruh rangkaian evaluasi/penugasan di LMS.', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(533, 'Semua', NULL, NULL, 'Monitoring Peserta', 'full learning', NULL, 'Peserta aktif berpartisipasi dalam diskusi kelompok atau tanya jawab', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(534, 'Semua', NULL, NULL, 'Monitoring Tenaga Kediklatan', 'full learning', NULL, 'Fasilitator memiliki sertifikat TOT, sertifikat keahlian, atau kompetensi teknis mengajar yang relevan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(535, 'Semua', NULL, NULL, 'Monitoring Tenaga Kediklatan', 'full learning', NULL, 'Fasilitator terdaftar dalam database penyelenggara / LMS', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(536, 'Semua', NULL, NULL, 'Monitoring Tenaga Kediklatan', 'full learning', NULL, 'Hadir dan memulai pembelajaran tepat waktu sesuai jadwal (Agenda)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(537, 'Semua', NULL, NULL, 'Monitoring Tenaga Kediklatan', 'full learning', NULL, 'Menyampaikan materi dan panduan sesuai dengan Rancang Bangun Pembelajaran Mata Pelatihan (RBPMP) / Kurikulum', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(538, 'Semua', NULL, NULL, 'Monitoring Tenaga Kediklatan', 'full learning', NULL, 'Menguasai penggunaan fitur-fitur platform daring untuk pembelajaran interaktif (misal: polling, whiteboard, anotasi)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(539, 'Semua', NULL, NULL, 'Monitoring Tenaga Kediklatan', 'full learning', NULL, 'Menjaga etika, profesionalisme, dan mematuhi kode etik pengajar ASN selama sesi berlangsung', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(540, 'Semua', NULL, NULL, 'Monitoring Tenaga Kediklatan', 'full learning', NULL, 'Menyediakan kelengkapan bahan ajar yang dapat diakses peserta (Modul, Bahan Tayang/Slide, Kasus/Tugas).', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(541, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'full learning', NULL, 'Learning Management System (LMS) berfungsi dengan baik, stabil, dan dapat diakses oleh seluruh pengguna', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(542, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'full learning', NULL, 'Platform Video Conference (misal: Zoom/Teams) memiliki lisensi dan kapasitas yang memadai untuk seluruh peserta', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(543, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'full learning', NULL, 'Tersedia fitur pendukung diskusi interaktif seperti Breakout Rooms jika diperlukan dalam metode pembelajaran', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(544, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'full learning', NULL, 'Tersedia Virtual Background standar yang sesuai dengan tema pelatihan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(545, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'full learning', NULL, 'Jaringan internet yang digunakan oleh panitia dan fasilitator memadai, stabil, dan lancar', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(546, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'full learning', NULL, 'Perangkat keras Broadcasting panitia/host berfungsi optimal (Komputer/Laptop, Kamera dengan pencahayaan baik, Headset/Mikrofon jernih)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(547, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'full learning', NULL, 'Tersedia saluran komunikasi/Helpdesk teknis yang responsif untuk membantu kendala sistem yang dialami peserta atau fasilitator.', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(548, 'Semua', NULL, NULL, 'Monitoring Penyelenggara', 'blended learning', NULL, 'Penyelenggara memiliki Surat Perintah (SP) / SK sebagai panitia.Keduanya', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(549, 'Semua', NULL, NULL, 'Monitoring Penyelenggara', 'blended learning', NULL, 'Terdapat Pengelola Kelas yang memiliki sertifikat kompetensi (MOT/TOC/Workshop sejenis) (daring/luring)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(550, 'Semua', NULL, NULL, 'Monitoring Penyelenggara', 'blended learning', NULL, 'Tersedia Administrator/Host untuk mengelola kelas virtual (admit, mute, breakout room) - Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(551, 'Semua', NULL, NULL, 'Monitoring Penyelenggara', 'blended learning', NULL, 'Tersedia Pengamat Akademik dan Petugas Kelas yang standby di ruangan -Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(552, 'Semua', NULL, NULL, 'Monitoring Penyelenggara', 'blended learning', NULL, 'Menyediakan kelengkapan administrasi peserta dikelola dengan baik (Daftar Hadir online/offline, Biodata, Tanda Pengenal/Name Tag) - Daring dan Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(553, 'Semua', NULL, NULL, 'Monitoring Penyelenggara', 'blended learning', NULL, 'Tersedia Panitia memfasilitasi pelaksanaan evaluasi (Pre-test, Post-test, dan Evaluasi Penyelenggaraan) secara tersistem', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(554, 'Semua', NULL, NULL, 'Monitoring Peserta', 'blended learning', NULL, 'Kualifikasi peserta sesuai dengan persyaratan pelatihan - Luring dan Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(555, 'Semua', NULL, NULL, 'Monitoring Peserta', 'blended learning', NULL, 'Peserta memiliki akun yang terdaftar di BPSDM / LMS penyelenggara - Luring dan daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(556, 'Semua', NULL, NULL, 'Monitoring Peserta', 'blended learning', NULL, 'Memenuhi syarat kehadiran minimal 85% dari total Jam Pelajaran (JP) keseluruhan - Luring dan Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(557, 'Semua', NULL, NULL, 'Monitoring Peserta', 'blended learning', NULL, 'Peserta mentaati tata tertib Daring (kamera aktif, mute saat tidak bicara, virtual background)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(558, 'Semua', NULL, NULL, 'Monitoring Peserta', 'blended learning', NULL, 'Peserta mentaati tata tertib Luring (pakaian rapi sesuai ketentuan, tepat waktu masuk kelas) _Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(559, 'Semua', NULL, NULL, 'Monitoring Peserta', 'blended learning', NULL, 'Peserta aktif mengikuti pembelajaran (diskusi, tanya jawab, kerja kelompok) - Luring dan Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(560, 'Semua', NULL, NULL, 'Monitoring Peserta', 'blended learning', NULL, 'Menyelesaikan seluruh penugasan mandiri maupun kelompok (Tugas baca, makalah, dll).', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(561, 'Semua', NULL, NULL, 'Monitoring Tenaga Kediklatan', 'blended learning', NULL, 'Memiliki sertifikat TOT, sertifikat keahlian, atau kompetensi teknis mengajar - Luring dan Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(562, 'Semua', NULL, NULL, 'Monitoring Tenaga Kediklatan', 'blended learning', NULL, 'Mengunggah dan menyediakan bahan ajar secara lengkap di LMS (Modul, Slide, Kasus) - Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(563, 'Semua', NULL, NULL, 'Monitoring Tenaga Kediklatan', 'blended learning', NULL, 'Hadir tepat waktu sesuai jadwal (Agenda) yang telah ditetapkan - Daring dan Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(564, 'Semua', NULL, NULL, 'Monitoring Tenaga Kediklatan', 'blended learning', NULL, 'Menyampaikan materi sesuai Rancang Bangun Pembelajaran Mata Pelatihan (RBPMP) - Luring dan Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(565, 'Semua', NULL, NULL, 'Monitoring Tenaga Kediklatan', 'blended learning', NULL, 'Menguasai penggunaan platform digital secara interaktif (LMS, polling, whiteboard virtual) - Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(566, 'Semua', NULL, NULL, 'Monitoring Tenaga Kediklatan', 'blended learning', NULL, 'Mampu mengelola dinamika kelompok, simulasi, atau roleplay secara langsung di kelas - Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(567, 'Semua', NULL, NULL, 'Monitoring Tenaga Kediklatan', 'blended learning', NULL, 'Mematuhi kode etik pengajar/narasumber dan menjaga etika komunikasi - Luring dan Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(568, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'LMS tersedia, berfungsi lancar, dan mudah diakses peserta maupun fasilitator - Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(569, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Platform Video Conference memiliki lisensi, stabil, dan berkapasitas memadai -Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(570, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Jaringan internet dan peralatan broadcasting panitia (kamera, mic) berfungsi optimal - Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(571, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Fasilitas Pembelajaran Luring (Klasikal) Ruang kelas bersih, nyaman, dengan sirkulasi udara / AC yang baik - Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(572, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Kursi dan meja belajar memadai dan diatur sesuai metode pembelajaran - Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(573, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Perangkat audio visual di kelas berfungsi baik (Proyektor, Layar, Pengeras Suara/Mic) - Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(574, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Tersedia alat peraga pendukung (Papan tulis, flipchart, spidol, alat tulis) - Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(575, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Jaringan internet / Wi-Fi di area kelas dan asrama memadai untuk peserta - Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(576, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Ketersediaan prasarana pendukung: Toilet bersih, Ruang Ibadah, dan Ruang Makan.Luring7Ketersediaan Perlengkapan P3K / akses kesehatan dasar di lokasi pelatihan - Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(577, 'Semua', NULL, NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Ruang Istirahat (Kamar/Wisma/Asrama) bersih dan layak (apabila pelatihan diinapkan) - Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `evaluation_results_l1`
--

CREATE TABLE `evaluation_results_l1` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED NOT NULL,
  `participant_id` bigint(20) UNSIGNED NOT NULL,
  `schedule_id` bigint(20) UNSIGNED DEFAULT NULL,
  `question_id` bigint(20) UNSIGNED NOT NULL,
  `score` int(11) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `evaluation_results_l1`
--

INSERT INTO `evaluation_results_l1` (`id`, `training_id`, `participant_id`, `schedule_id`, `question_id`, `score`, `note`, `created_at`, `updated_at`) VALUES
(2, 4, 12, NULL, 38, 98, NULL, '2026-08-29 02:10:25', '2026-08-29 02:10:25'),
(3, 4, 12, NULL, 39, 94, NULL, '2026-08-29 02:10:25', '2026-08-29 02:10:25'),
(4, 4, 12, NULL, 40, 95, NULL, '2026-08-29 02:10:25', '2026-08-29 02:10:25'),
(5, 4, 12, NULL, 41, 98, NULL, '2026-08-29 02:10:25', '2026-08-29 02:10:25'),
(6, 4, 12, NULL, 42, 92, NULL, '2026-08-29 02:10:25', '2026-08-29 02:10:25'),
(7, 4, 12, NULL, 43, 91, NULL, '2026-08-29 02:10:25', '2026-08-29 02:10:25'),
(8, 4, 12, NULL, 44, 100, NULL, '2026-08-29 02:10:25', '2026-08-29 02:10:25'),
(9, 4, 12, NULL, 45, 80, NULL, '2026-08-29 02:10:25', '2026-08-29 02:10:25'),
(10, 4, 12, NULL, 46, 80, NULL, '2026-08-29 02:10:25', '2026-08-29 02:10:25'),
(11, 4, 12, NULL, 47, 80, NULL, '2026-08-29 02:10:25', '2026-08-29 02:10:25'),
(12, 4, 12, NULL, 48, NULL, 'sudah cukup baik', '2026-08-29 02:10:25', '2026-08-29 02:10:25'),
(13, 4, 12, 3, 19, 90, NULL, '2026-08-29 02:18:33', '2026-08-29 02:18:33'),
(14, 4, 12, 3, 20, 93, NULL, '2026-08-29 02:18:33', '2026-08-29 02:18:33'),
(15, 4, 12, 3, 21, 90, NULL, '2026-08-29 02:18:33', '2026-08-29 02:18:33'),
(16, 4, 12, 3, 22, 91, NULL, '2026-08-29 02:18:33', '2026-08-29 02:18:33'),
(17, 4, 12, 3, 23, 91, NULL, '2026-08-29 02:18:33', '2026-08-29 02:18:33'),
(18, 4, 12, 3, 24, 88, NULL, '2026-08-29 02:18:33', '2026-08-29 02:18:33'),
(19, 4, 12, 3, 25, 88, NULL, '2026-08-29 02:18:33', '2026-08-29 02:18:33'),
(20, 4, 12, 3, 26, 90, NULL, '2026-08-29 02:18:33', '2026-08-29 02:18:33'),
(21, 4, 12, 3, 27, 80, NULL, '2026-08-29 02:18:33', '2026-08-29 02:18:33'),
(22, 4, 12, 3, 28, 86, NULL, '2026-08-29 02:18:33', '2026-08-29 02:18:33'),
(23, 4, 12, 3, 29, 80, NULL, '2026-08-29 02:18:33', '2026-08-29 02:18:33'),
(24, 4, 12, 3, 30, 80, NULL, '2026-08-29 02:18:33', '2026-08-29 02:18:33'),
(25, 4, 12, 3, 31, 96, NULL, '2026-08-29 02:18:33', '2026-08-29 02:18:33'),
(26, 4, 12, 3, 32, 100, NULL, '2026-08-29 02:18:33', '2026-08-29 02:18:33'),
(27, 4, 12, 3, 33, 99, NULL, '2026-08-29 02:18:33', '2026-08-29 02:18:33'),
(28, 4, 12, 3, 34, 97, NULL, '2026-08-29 02:18:33', '2026-08-29 02:18:33'),
(29, 4, 12, 3, 35, 98, NULL, '2026-08-29 02:18:33', '2026-08-29 02:18:33'),
(30, 4, 12, 3, 36, 80, NULL, '2026-08-29 02:18:33', '2026-08-29 02:18:33'),
(31, 4, 12, 3, 37, 98, NULL, '2026-08-29 02:18:33', '2026-08-29 02:18:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `evaluation_results_l2`
--

CREATE TABLE `evaluation_results_l2` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `participant_id` bigint(20) UNSIGNED NOT NULL,
  `pretest` decimal(5,2) NOT NULL DEFAULT 0.00,
  `postest` decimal(5,2) NOT NULL DEFAULT 0.00,
  `n_gain` decimal(5,2) GENERATED ALWAYS AS (`postest` - `pretest`) VIRTUAL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `evaluation_results_l2`
--

INSERT INTO `evaluation_results_l2` (`id`, `participant_id`, `pretest`, `postest`, `created_at`, `updated_at`) VALUES
(1, 12, 80.00, 90.00, '2026-08-29 02:09:29', '2026-08-29 02:09:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `evaluation_results_l34`
--

CREATE TABLE `evaluation_results_l34` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `participant_id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED DEFAULT NULL,
  `evaluator_role` enum('mandiri','rekan','atasan') NOT NULL,
  `evaluator_name` varchar(255) NOT NULL,
  `question_id` bigint(20) UNSIGNED DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `evaluation_results_l34`
--

INSERT INTO `evaluation_results_l34` (`id`, `participant_id`, `training_id`, `evaluator_role`, `evaluator_name`, `question_id`, `score`, `note`, `created_at`, `updated_at`) VALUES
(29, 12, 4, 'mandiri', 'Diri Sendiri', 265, NULL, 'S2/S3', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(30, 12, 4, 'mandiri', 'Diri Sendiri', 268, NULL, 'D3', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(31, 12, 4, 'mandiri', 'Diri Sendiri', 270, NULL, 'IV/b', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(32, 12, 4, 'mandiri', 'Diri Sendiri', 271, NULL, 'IV/b', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(33, 12, 4, 'mandiri', 'Diri Sendiri', 272, NULL, 'dfasfasf', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(34, 12, 4, 'mandiri', 'Diri Sendiri', 273, NULL, 'fasfasfasf', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(35, 12, 4, 'mandiri', 'Diri Sendiri', 274, NULL, 'fasfasfasf', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(36, 12, 4, 'mandiri', 'Diri Sendiri', 275, NULL, 'fsafasfas', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(37, 12, 4, 'mandiri', 'Diri Sendiri', 276, NULL, 'fasfasf', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(38, 12, 4, 'mandiri', 'Diri Sendiri', 277, NULL, 'fasfasf', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(39, 12, 4, 'mandiri', 'Diri Sendiri', 278, NULL, 'YA', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(40, 12, 4, 'mandiri', 'Diri Sendiri', 279, NULL, 'TIDAK', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(41, 12, 4, 'mandiri', 'Diri Sendiri', 280, NULL, 'YA', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(42, 12, 4, 'mandiri', 'Diri Sendiri', 281, NULL, 'YA', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(43, 12, 4, 'mandiri', 'Diri Sendiri', 282, NULL, 'TIDAK', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(44, 12, 4, 'mandiri', 'Diri Sendiri', 283, NULL, 'YA', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(45, 12, 4, 'mandiri', 'Diri Sendiri', 284, NULL, 'Kurang', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(46, 12, 4, 'mandiri', 'Diri Sendiri', 285, NULL, 'Baik', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(47, 12, 4, 'mandiri', 'Diri Sendiri', 286, NULL, 'Baik', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(48, 12, 4, 'mandiri', 'Diri Sendiri', 287, NULL, 'Baik', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(49, 12, 4, 'mandiri', 'Diri Sendiri', 288, NULL, 'Sangat Baik', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(50, 12, 4, 'mandiri', 'Diri Sendiri', 290, NULL, '[\"Saya tidak mempunyai pengetahuan dan keterampilan yang memadai\",\"Saya tidak mempunyai dukungan sumber daya yang memadai untuk implementasi materi pembelajaran\"]', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(51, 12, 4, 'mandiri', 'Diri Sendiri', 291, NULL, 'Kurang', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(52, 12, 4, 'mandiri', 'Diri Sendiri', 292, NULL, 'Kurang', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(53, 12, 4, 'mandiri', 'Diri Sendiri', 293, NULL, 'Kurang', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(54, 12, 4, 'mandiri', 'Diri Sendiri', 294, NULL, 'Cukup', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(55, 12, 4, 'mandiri', 'Diri Sendiri', 295, NULL, 'Baik', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(56, 12, 4, 'mandiri', 'Diri Sendiri', 296, NULL, 'Baik', '2026-08-29 01:23:40', '2026-08-29 01:23:40'),
(57, 12, 4, 'rekan', 'dasdsadsad', 302, NULL, 'dasdasdsa', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(58, 12, 4, 'rekan', 'dasdsadsad', 303, NULL, 'dasdsadas', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(59, 12, 4, 'rekan', 'dasdsadsad', 304, NULL, 'dasdasdsa', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(60, 12, 4, 'rekan', 'dasdsadsad', 305, NULL, 'dasdasddas', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(61, 12, 4, 'rekan', 'dasdsadsad', 306, NULL, 'dasdasdasd', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(62, 12, 4, 'rekan', 'dasdsadsad', 307, NULL, 'YA', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(63, 12, 4, 'rekan', 'dasdsadsad', 308, NULL, 'YA', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(64, 12, 4, 'rekan', 'dasdsadsad', 309, NULL, 'YA', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(65, 12, 4, 'rekan', 'dasdsadsad', 310, NULL, 'TIDAK', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(66, 12, 4, 'rekan', 'dasdsadsad', 311, NULL, 'YA', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(67, 12, 4, 'rekan', 'dasdsadsad', 312, NULL, 'YA', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(68, 12, 4, 'rekan', 'dasdsadsad', 313, NULL, 'Baik', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(69, 12, 4, 'rekan', 'dasdsadsad', 314, NULL, 'Baik', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(70, 12, 4, 'rekan', 'dasdsadsad', 315, NULL, 'Sangat Baik', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(71, 12, 4, 'rekan', 'dasdsadsad', 316, NULL, 'Sangat Baik', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(72, 12, 4, 'rekan', 'dasdsadsad', 317, NULL, 'Sangat Baik', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(73, 12, 4, 'rekan', 'dasdsadsad', 318, NULL, '[\"Saya tidak mempunyai pengetahuan dan keterampilan yang memadai\",\"Pembelajaran yang saya ikuti tidak memberikan nilai tambah bagi saya\"]', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(74, 12, 4, 'rekan', 'dasdsadsad', 319, NULL, 'Cukup', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(75, 12, 4, 'rekan', 'dasdsadsad', 320, NULL, 'Cukup', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(76, 12, 4, 'rekan', 'dasdsadsad', 321, NULL, 'Sangat Baik', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(77, 12, 4, 'rekan', 'dasdsadsad', 322, NULL, 'Sangat Baik', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(78, 12, 4, 'rekan', 'dasdsadsad', 323, NULL, 'Sangat Baik', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(79, 12, 4, 'rekan', 'dasdsadsad', 324, NULL, 'Sangat Baik', '2026-08-29 01:27:41', '2026-08-29 01:27:41'),
(80, 12, 4, 'atasan', 'dadasdDAD', 297, NULL, 'sdafabavfasf asfkas fas', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(81, 12, 4, 'atasan', 'dadasdDAD', 298, NULL, 'fasfas fasfasfasf', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(82, 12, 4, 'atasan', 'dadasdDAD', 299, NULL, 'fasfasfasf', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(83, 12, 4, 'atasan', 'dadasdDAD', 300, NULL, 'fasfasfasg', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(84, 12, 4, 'atasan', 'dadasdDAD', 301, NULL, 'gasgasgasgasg', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(85, 12, 4, 'atasan', 'dadasdDAD', 325, NULL, 'YA', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(86, 12, 4, 'atasan', 'dadasdDAD', 326, NULL, 'YA', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(87, 12, 4, 'atasan', 'dadasdDAD', 327, NULL, 'YA', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(88, 12, 4, 'atasan', 'dadasdDAD', 328, NULL, 'YA', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(89, 12, 4, 'atasan', 'dadasdDAD', 329, NULL, 'YA', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(90, 12, 4, 'atasan', 'dadasdDAD', 330, NULL, 'YA', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(91, 12, 4, 'atasan', 'dadasdDAD', 331, NULL, 'Sangat Baik', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(92, 12, 4, 'atasan', 'dadasdDAD', 332, NULL, 'Sangat Baik', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(93, 12, 4, 'atasan', 'dadasdDAD', 333, NULL, 'Sangat Baik', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(94, 12, 4, 'atasan', 'dadasdDAD', 334, NULL, 'Sangat Baik', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(95, 12, 4, 'atasan', 'dadasdDAD', 335, NULL, 'Sangat Baik', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(96, 12, 4, 'atasan', 'dadasdDAD', 336, NULL, '[\"Saya tidak punya rencana implementasi materi pembelajaran secara jelas dan terukur\",\"Saya tidak mempunyai dukungan sumber daya yang memadai untuk implementasi materi pembelajaran\"]', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(97, 12, 4, 'atasan', 'dadasdDAD', 337, NULL, 'Kurang', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(98, 12, 4, 'atasan', 'dadasdDAD', 338, NULL, 'Sangat Baik', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(99, 12, 4, 'atasan', 'dadasdDAD', 339, NULL, 'Sangat Baik', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(100, 12, 4, 'atasan', 'dadasdDAD', 340, NULL, 'Sangat Baik', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(101, 12, 4, 'atasan', 'dadasdDAD', 341, NULL, 'Sangat Baik', '2026-08-29 01:29:00', '2026-08-29 01:29:00'),
(102, 12, 4, 'atasan', 'dadasdDAD', 342, NULL, 'Sangat Baik', '2026-08-29 01:29:00', '2026-08-29 01:29:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `files`
--

CREATE TABLE `files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `folder_id` bigint(20) UNSIGNED NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(255) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `files`
--

INSERT INTO `files` (`id`, `folder_id`, `display_name`, `file_path`, `file_type`, `file_size`, `user_id`, `created_at`, `updated_at`) VALUES
(60, 29, 'HASIL_EVALUASI_L1_L2_Pelatihan_Pengkajian_Kebutuhan_Pascabencana.xlsx', 'documents/HASIL_EVALUASI_L1_L2_Pelatihan_Pengkajian_Kebutuhan_Pascabencana.xlsx', 'xlsx', 6867, 5, '2026-08-29 01:35:56', '2026-08-29 01:35:56'),
(61, 30, 'LAPORAN_DAMPAK_L3_L4_Pelatihan_Pengkajian_Kebutuhan_Pascabencana.xlsx', 'documents/LAPORAN_DAMPAK_L3_L4_Pelatihan_Pengkajian_Kebutuhan_Pascabencana.xlsx', 'xlsx', 15160, 5, '2026-08-29 01:49:08', '2026-08-29 01:49:08'),
(62, 31, 'LAPORAN_AKHIR_DAMPAK_L34_Pelatihan_Pengkajian_Kebutuhan_Pascabencana.docx', 'documents/LAPORAN_AKHIR_DAMPAK_L34_Pelatihan_Pengkajian_Kebutuhan_Pascabencana.docx', 'docx', 399369, 5, '2026-08-29 01:50:19', '2026-08-29 01:50:19'),
(63, 32, 'SURAT_UNDANGAN_EVALUASI_L34_Pelatihan_Pengkajian_Kebutuhan_Pascabencana.docx', 'documents/SURAT_UNDANGAN_EVALUASI_L34_Pelatihan_Pengkajian_Kebutuhan_Pascabencana.docx', 'docx', 88819, 5, '2026-08-29 01:52:16', '2026-08-29 01:52:16'),
(64, 31, 'LAPORAN_AKHIR_DAMPAK_L34_Pelatihan_Pengkajian_Kebutuhan_Pascabencana.docx', 'documents/LAPORAN_AKHIR_DAMPAK_L34_Pelatihan_Pengkajian_Kebutuhan_Pascabencana.docx', 'docx', 12725, 5, '2026-08-29 02:05:27', '2026-08-29 02:05:27'),
(65, 29, 'HASIL_EVALUASI_L1_L2_Pelatihan_Pengkajian_Kebutuhan_Pascabencana.xlsx', 'documents/HASIL_EVALUASI_L1_L2_Pelatihan_Pengkajian_Kebutuhan_Pascabencana.xlsx', 'xlsx', 6881, 5, '2026-08-29 02:18:44', '2026-08-29 02:18:44'),
(68, 35, 'LAPORAN_EVALUASI_LV1_LV2_Pelatihan_Pengkajian_Kebutuhan_Pascabencana.docx', 'documents/LAPORAN_EVALUASI_LV1_LV2_Pelatihan_Pengkajian_Kebutuhan_Pascabencana.docx', 'docx', 13282, 2, '2026-08-29 02:31:28', '2026-08-29 02:31:28'),
(71, 35, 'LAPORAN_EVALUASI_LV1_LV2_Pelatihan_Pengkajian_Kebutuhan_Pascabencana.docx', 'documents/LAPORAN_EVALUASI_LV1_LV2_Pelatihan_Pengkajian_Kebutuhan_Pascabencana.docx', 'docx', 13625, 5, '2026-08-29 02:41:04', '2026-08-29 02:41:04'),
(72, 36, 'LAPORAN_MONITORING_Pelatihan_Pengkajian_Kebutuhan_Pascabencana_Utama.docx', 'documents/LAPORAN_MONITORING_Pelatihan_Pengkajian_Kebutuhan_Pascabencana_Utama.docx', 'docx', 10856499, 2, '2026-08-29 02:45:22', '2026-08-29 02:45:22');

-- --------------------------------------------------------

--
-- Struktur dari tabel `folders`
--

CREATE TABLE `folders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `bidang` varchar(255) NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `share_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `folders`
--

INSERT INTO `folders` (`id`, `training_id`, `name`, `bidang`, `parent_id`, `user_id`, `is_public`, `share_token`, `created_at`, `updated_at`) VALUES
(28, 4, 'Pelatihan Pengkajian Kebutuhan Pascabencana', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 5, 0, NULL, '2026-08-29 01:35:55', '2026-08-29 01:35:55'),
(29, 4, 'HASIL EVALUASI L1 L2', 'Bidang Pengembangan Kompetensi Teknis Umum', 28, 5, 0, NULL, '2026-08-29 01:35:55', '2026-08-29 01:35:55'),
(30, 4, 'HASIL EVALUASI DAMPAK', 'Bidang Pengembangan Kompetensi Teknis Umum', 28, 5, 0, NULL, '2026-08-29 01:49:08', '2026-08-29 01:49:08'),
(31, 4, 'LAPORAN AKHIR DAMPAK', 'Bidang Pengembangan Kompetensi Teknis Umum', 28, 5, 0, NULL, '2026-08-29 01:50:19', '2026-08-29 01:50:19'),
(32, 4, 'SURAT UNDANGAN EVALUASI', 'Bidang Pengembangan Kompetensi Teknis Umum', 28, 5, 0, NULL, '2026-08-29 01:52:16', '2026-08-29 01:52:16'),
(35, 4, 'LAPORAN EVALUASI LEVEL 1 DAN 2', 'Bidang Pengembangan Kompetensi Teknis Umum', 28, 2, 0, NULL, '2026-08-29 02:31:28', '2026-08-29 02:31:28'),
(36, 4, 'LAPORAN MONITORING', 'Bidang Pengembangan Kompetensi Teknis Umum', 28, 2, 0, NULL, '2026-08-29 02:45:22', '2026-08-29 02:45:22');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_07_18_095101_create_trainings_table', 1),
(5, '2026_07_18_100415_create_participants_table', 1),
(6, '2026_07_18_100416_create_schedules_table', 1),
(7, '2026_07_18_103645_create_attendances_table', 1),
(8, '2026_07_18_104315_create_evaluation_questions_table', 1),
(9, '2026_07_18_104317_create_evaluation_result_l1_s_table', 1),
(10, '2026_07_18_105109_create_evaluation_result_l2_s_table', 1),
(11, '2026_07_18_105343_create_monitoring_results_table', 1),
(12, '2026_07_18_105344_create_evaluation_result_l34_s_table', 1),
(13, '2026_07_18_120908_update_bidang_column_in_users_and_trainings', 1),
(14, '2026_07_18_124443_create_questions_table', 1),
(15, '2026_07_18_133807_create_training_stages_table', 1),
(16, '2026_07_18_141133_adjust_questions_table', 1),
(17, '2026_07_18_143132_create_alumni_profiles_table', 1),
(18, '2026_07_18_151051_create_monitoring_summaries_table', 1),
(19, '2026_07_19_080806_add_attendance_window_to_schedules_table', 1),
(20, '2026_07_19_115919_add_timezone_to_attendances_table', 1),
(21, '2026_07_19_133737_add_options_to_questions_table', 1),
(22, '2026_07_19_135500_create_evaluation_form_l1_s_table', 1),
(23, '2026_07_19_143117_add_missing_columns_to_evaluation_questions', 1),
(24, '2026_07_19_144105_change_category_column_type_in_evaluation_questions', 1),
(25, '2026_07_19_144729_add_metode_to_evaluation_questions', 1),
(26, '2026_07_20_032822_change_type_column_in_evaluation_questions', 1),
(27, '2026_07_20_042137_add_status_to_monitoring_results', 1),
(28, '2026_07_20_043404_adjust_monitoring_results_table', 1),
(29, '2026_07_20_043925_make_category_nullable_in_monitoring_results', 1),
(30, '2026_07_20_045127_add_resolution_columns_to_monitoring_results', 1),
(31, '2026_07_20_050903_add_stage_id_to_monitoring_results', 1),
(32, '2026_07_20_054225_add_training_stage_id_to_monitoring_summaries_table', 1),
(33, '2026_07_21_055227_adjust_evaluation_questions_for_specific_training', 1),
(34, '2026_07_21_065123_add_note_to_evaluation_results_l34', 2),
(35, '2026_07_22_014357_make_question_id_nullable_in_l34_results', 2),
(36, '2026_07_26_140917_add_profile_photo_to_users_table', 2),
(37, '2026_08_15_125110_create_folders_table', 2),
(38, '2026_08_15_125112_create_files_table', 2),
(39, '2026_08_15_130113_create_activity_logs_table', 2),
(40, '2026_08_15_130709_add_user_agent_to_activity_logs', 2),
(41, '2026_08_15_143731_change_bidang_to_string_in_folders_table', 2),
(42, '2026_08_15_152335_add_details_to_participants_table', 2),
(43, '2026_08_15_161839_add_socialite_and_profile_to_users_table', 2),
(44, '2026_08_15_163911_create_personal_access_tokens_table', 2),
(45, '2026_08_15_192443_make_bidang_nullable_in_users_table', 2),
(46, '2026_08_15_192911_update_role_column_in_users_table', 2),
(47, '2026_08_15_202522_add_training_id_to_folders_table', 2),
(48, '2026_08_15_204912_adjust_training_and_participants_for_invitation', 2),
(49, '2026_08_15_223407_add_profile_details_to_users_table', 2),
(50, '2026_08_15_225839_add_file_references_to_participants_table', 2),
(51, '2026_08_16_000737_add_link_lms_to_trainings_table', 2),
(52, '2026_08_16_121011_add_pas_foto_to_participants_table', 2),
(53, '2026_08_16_125348_add_phone_to_participants_table', 2),
(54, '2026_08_19_111444_add_kecamatan_to_participants_table', 2),
(55, '2026_08_19_132443_add_kecamatan_kelurahan_to_users_table', 2),
(56, '2026_08_19_140451_rename_kabupaten_kota_to_kota_on_participants_table', 2),
(57, '2026_08_19_142049_reorder_kecamatan_on_participants_table', 2),
(58, '2026_08_19_142320_add_kelurahan_to_participants_table', 2),
(59, '2026_08_24_082058_create_teachers_table', 3),
(60, '2026_08_24_112506_add_instansi_to_pengajars_table', 3),
(61, '2026_08_24_114741_add_dokumen_to_pengajars_table', 3),
(62, '2026_08_24_134455_add_rekening_to_pengajars_table', 3),
(63, '2026_08_26_084107_add_pengajar_id_to_schedules_table', 3),
(64, '2026_08_27_220613_add_jp_to_schedules_table', 3),
(65, '2026_08_27_222913_add_link_zoom_to_schedules_table', 3),
(66, '2026_08_28_091015_add_registration_status_to_participants', 3),
(67, '2026_08_28_102600_add_registration_status_to_participants', 3),
(68, '2026_08_28_114011_add_address_details_to_users_table', 4),
(69, '2026_08_28_150000_create_pengajar_schedule_documents_table', 5),
(70, '2026_08_28_220000_add_coordinates_to_users_table', 6),
(71, '2026_08_29_090000_add_bidang_to_evaluation_questions_table', 7),
(72, '2026_08_29_150000_classify_legacy_l34_questions', 8),
(73, '2026_08_29_170000_sync_mandiri_l34_questions_to_peer_roles', 9);

-- --------------------------------------------------------

--
-- Struktur dari tabel `monitoring_results`
--

CREATE TABLE `monitoring_results` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED NOT NULL,
  `training_stage_id` bigint(20) UNSIGNED DEFAULT NULL,
  `question_id` bigint(20) UNSIGNED NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `answer` enum('ya','tidak') NOT NULL,
  `notes` text DEFAULT NULL,
  `follow_up_target` varchar(255) DEFAULT NULL,
  `is_resolved` tinyint(1) NOT NULL DEFAULT 0,
  `resolution_notes` text DEFAULT NULL,
  `evidence_file` varchar(255) DEFAULT NULL,
  `status` enum('open','resolved') NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `monitoring_summaries`
--

CREATE TABLE `monitoring_summaries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED NOT NULL,
  `training_stage_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category` varchar(255) NOT NULL,
  `conclusion` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `participants`
--

CREATE TABLE `participants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nip_nik` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `gender` enum('Laki-Laki','Perempuan') DEFAULT NULL,
  `jabatan` varchar(255) DEFAULT NULL,
  `instansi` varchar(255) DEFAULT NULL,
  `provinsi` varchar(255) DEFAULT NULL,
  `kota` varchar(255) DEFAULT NULL,
  `kecamatan` varchar(255) DEFAULT NULL,
  `kelurahan` varchar(255) DEFAULT NULL,
  `status_kepegawaian` varchar(255) DEFAULT NULL,
  `registration_status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `biodata_file_id` bigint(20) UNSIGNED DEFAULT NULL,
  `surat_tugas_file_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pas_foto_file_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `participants`
--

INSERT INTO `participants` (`id`, `training_id`, `user_id`, `nip_nik`, `phone`, `name`, `gender`, `jabatan`, `instansi`, `provinsi`, `kota`, `kecamatan`, `kelurahan`, `status_kepegawaian`, `registration_status`, `created_at`, `updated_at`, `biodata_file_id`, `surat_tugas_file_id`, `pas_foto_file_id`) VALUES
(12, 4, 1, '12387126387126438', NULL, 'Simpan Aku aja 22', 'Laki-Laki', 'Pengelola Layanan', 'jdfsgfjsdbfsdjfsdf', 'JAWA BARAT', 'KABUPATEN BANDUNG BARAT', 'PARONGPONG', 'CIGUGUR GIRANG', 'PNS', 'approved', '2026-08-28 13:56:50', '2026-08-28 14:43:37', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengajars`
--

CREATE TABLE `pengajars` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `npwp` varchar(255) DEFAULT NULL,
  `nama_bank` varchar(255) DEFAULT NULL,
  `nomor_rekening` varchar(255) DEFAULT NULL,
  `nama_rekening` varchar(255) DEFAULT NULL,
  `bidang_keahlian` text DEFAULT NULL,
  `pangkat_golongan` varchar(255) DEFAULT NULL,
  `instansi` varchar(255) DEFAULT NULL,
  `cv_path` varchar(255) DEFAULT NULL,
  `sertifikat_path` varchar(255) DEFAULT NULL,
  `surat_tugas_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengajar_schedule_documents`
--

CREATE TABLE `pengajar_schedule_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `schedule_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `bahan_ajar_path` varchar(255) DEFAULT NULL,
  `rbpmp_rp_path` varchar(255) DEFAULT NULL,
  `bukti_mengajar_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `questions`
--

CREATE TABLE `questions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category` varchar(255) NOT NULL,
  `question_text` text NOT NULL,
  `metode` varchar(255) DEFAULT 'semua',
  `type` varchar(255) NOT NULL DEFAULT 'slider',
  `options` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `training_type` varchar(255) DEFAULT NULL,
  `sub_category` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `schedules`
--

CREATE TABLE `schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `activity` varchar(255) NOT NULL,
  `jp` int(11) DEFAULT NULL,
  `link_zoom` text DEFAULT NULL,
  `pic` varchar(255) NOT NULL,
  `pengajar_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `attendance_open` time DEFAULT NULL,
  `attendance_close` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `schedules`
--

INSERT INTO `schedules` (`id`, `training_id`, `date`, `start_time`, `end_time`, `activity`, `jp`, `link_zoom`, `pic`, `pengajar_id`, `created_at`, `updated_at`, `attendance_open`, `attendance_close`) VALUES
(3, 4, '2026-08-29', '05:00:00', '10:00:00', 'Matei I untuk kegiatan itu', 4, NULL, 'Super Administrator', 1, '2026-08-28 22:51:21', '2026-08-28 22:51:21', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `trainings`
--

CREATE TABLE `trainings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bidang` varchar(255) NOT NULL,
  `nama_pelatihan` varchar(255) NOT NULL,
  `invitation_code` varchar(10) DEFAULT NULL,
  `link_lms` varchar(255) DEFAULT NULL,
  `model` enum('standar','blended') NOT NULL,
  `metode` varchar(255) NOT NULL,
  `lokasi` varchar(255) NOT NULL,
  `kerjasama` varchar(255) DEFAULT NULL,
  `anggaran` varchar(255) DEFAULT NULL,
  `angkatan` varchar(255) NOT NULL,
  `jumlah_peserta` int(11) NOT NULL,
  `jp` int(11) NOT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `tgl_mulai_klasikal` date DEFAULT NULL,
  `tgl_selesai_klasikal` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `trainings`
--

INSERT INTO `trainings` (`id`, `bidang`, `nama_pelatihan`, `invitation_code`, `link_lms`, `model`, `metode`, `lokasi`, `kerjasama`, `anggaran`, `angkatan`, `jumlah_peserta`, `jp`, `tgl_mulai`, `tgl_selesai`, `tgl_mulai_klasikal`, `tgl_selesai_klasikal`, `created_at`, `updated_at`) VALUES
(4, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Pelatihan Pengkajian Kebutuhan Pascabencana', '9TBNOP', NULL, 'standar', 'full learning', 'Zoom', NULL, NULL, '1', 40, 55, '2026-08-28', '2026-08-31', NULL, NULL, '2026-08-28 13:31:14', '2026-08-28 13:31:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `training_stages`
--

CREATE TABLE `training_stages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED NOT NULL,
  `nama_tahapan` varchar(255) NOT NULL,
  `metode` varchar(255) NOT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `nip_nik` varchar(255) DEFAULT NULL,
  `whatsapp` varchar(255) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL,
  `bidang` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `gender` enum('Laki-Laki','Perempuan') DEFAULT NULL,
  `jabatan` varchar(255) DEFAULT NULL,
  `instansi` varchar(255) DEFAULT NULL,
  `provinsi` varchar(255) DEFAULT NULL,
  `kota` varchar(255) DEFAULT NULL,
  `kecamatan` varchar(255) DEFAULT NULL,
  `kelurahan` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `status_kepegawaian` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `google_id`, `avatar`, `name`, `username`, `nip_nik`, `whatsapp`, `profile_photo`, `role`, `bidang`, `password`, `remember_token`, `created_at`, `updated_at`, `gender`, `jabatan`, `instansi`, `provinsi`, `kota`, `kecamatan`, `kelurahan`, `latitude`, `longitude`, `status_kepegawaian`) VALUES
(1, '107781747552867366947', 'https://lh3.googleusercontent.com/a/ACg8ocJTdYYDR-py4kqvc2uXIM_JX56X0cang30ysQGyyWB23sdF2Q=s96-c', 'Simpan Aku aja 22', 'simpanakuajaduadua@gmail.com', '12387126387126438', '081382830814', NULL, 'participant', NULL, '$2y$12$HvkVkDVodkLIaQs6RniJ9.gPBvc7A8xecTEB12IXr1kuezKe.V1fq', NULL, '2026-08-28 04:30:55', '2026-08-28 14:43:37', 'Laki-Laki', 'Pengelola Layanan', 'jdfsgfjsdbfsdjfsdf', 'JAWA BARAT', 'KABUPATEN BANDUNG BARAT', 'PARONGPONG', 'CIGUGUR GIRANG', -6.8335548, 107.5854874, 'PNS'),
(2, NULL, NULL, 'Super Administrator', 'superadmin', '19450817000000', '6281234567890', NULL, 'superadmin', 'Sekretariat', '$2y$12$82TUizUKE.owZ2/L0KDDg.7e.UydaeeVnpdqKnjkcqgV2KRqkKuym', NULL, '2026-08-28 06:19:21', '2026-08-28 06:19:21', 'Laki-Laki', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PNS'),
(3, NULL, NULL, 'Super Admin', 'admin', NULL, NULL, NULL, 'superadmin', 'Semua Bidang', '$2y$12$m2HvoH3gpzLW6aENdxKPDOuKvkysbBK9nIc4f6h2s7U6HIyAXrGRW', NULL, '2026-08-28 06:19:42', '2026-08-28 06:19:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, '118339399391382672190', 'https://lh3.googleusercontent.com/a/ACg8ocJWem1Q3SnD_OF1CZI77YKZA_yxmXI7nkEf8tHs-xnvfPkYNg=s96-c', 'simpanakuaja delapan', 'simpanakuajadelapan@gmail.com', NULL, NULL, NULL, 'participant', NULL, '$2y$12$J4lpZbI2DwCPMgmoz.1dzekWiWnRaPI3L62Q9aECyZT6zLBhggQzy', NULL, '2026-08-28 12:02:38', '2026-08-28 12:02:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, NULL, NULL, 'Ali Ridwan', 'bidangpktu@bpsdm.go.id', NULL, '08123456789', NULL, 'admin_bidang', 'Bidang Pengembangan Kompetensi Teknis Umum', '$2y$12$YPxZ1PjL0nlyZ5xYpyDlpO/QybSb.V1hYbezpk8WV1dARs/x7vtsi', NULL, '2026-08-28 12:49:55', '2026-08-28 13:30:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, '115482024455871232654', 'https://lh3.googleusercontent.com/a/ACg8ocJv9FWX3pw175ExmRwBHW53DHh-_9dp64IljqBNAxRCsDFYdPo=s96-c', 'Sem Syamsidin', 'semsyamsidin.sem@gmail.com', NULL, NULL, NULL, 'participant', NULL, '$2y$12$mqKZXhhesDekZhQWFAcyruw1EL.3KL7DcOUQFWZz19dLLIlY4W4y6', NULL, '2026-08-28 13:01:31', '2026-08-28 13:01:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_logs_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `alumni_profiles`
--
ALTER TABLE `alumni_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alumni_profiles_participant_id_foreign` (`participant_id`),
  ADD KEY `alumni_profiles_training_id_foreign` (`training_id`);

--
-- Indeks untuk tabel `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendances_schedule_id_participant_id_unique` (`schedule_id`,`participant_id`),
  ADD KEY `attendances_participant_id_foreign` (`participant_id`);

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `evaluation_forms`
--
ALTER TABLE `evaluation_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluation_forms_training_id_foreign` (`training_id`),
  ADD KEY `evaluation_forms_schedule_id_foreign` (`schedule_id`);

--
-- Indeks untuk tabel `evaluation_questions`
--
ALTER TABLE `evaluation_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluation_questions_training_id_foreign` (`training_id`),
  ADD KEY `evaluation_questions_bidang_index` (`bidang`);

--
-- Indeks untuk tabel `evaluation_results_l1`
--
ALTER TABLE `evaluation_results_l1`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluation_results_l1_training_id_foreign` (`training_id`),
  ADD KEY `evaluation_results_l1_participant_id_foreign` (`participant_id`),
  ADD KEY `evaluation_results_l1_schedule_id_foreign` (`schedule_id`),
  ADD KEY `evaluation_results_l1_question_id_foreign` (`question_id`);

--
-- Indeks untuk tabel `evaluation_results_l2`
--
ALTER TABLE `evaluation_results_l2`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluation_results_l2_participant_id_foreign` (`participant_id`);

--
-- Indeks untuk tabel `evaluation_results_l34`
--
ALTER TABLE `evaluation_results_l34`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluation_results_l34_participant_id_foreign` (`participant_id`),
  ADD KEY `evaluation_results_l34_question_id_foreign` (`question_id`),
  ADD KEY `evaluation_results_l34_training_id_foreign` (`training_id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `files_folder_id_foreign` (`folder_id`),
  ADD KEY `files_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `folders`
--
ALTER TABLE `folders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folders_share_token_unique` (`share_token`),
  ADD KEY `folders_parent_id_foreign` (`parent_id`),
  ADD KEY `folders_user_id_foreign` (`user_id`),
  ADD KEY `folders_training_id_foreign` (`training_id`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `monitoring_results`
--
ALTER TABLE `monitoring_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `monitoring_results_training_id_foreign` (`training_id`),
  ADD KEY `monitoring_results_question_id_foreign` (`question_id`),
  ADD KEY `monitoring_results_training_stage_id_foreign` (`training_stage_id`);

--
-- Indeks untuk tabel `monitoring_summaries`
--
ALTER TABLE `monitoring_summaries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `monitoring_summaries_training_id_foreign` (`training_id`),
  ADD KEY `monitoring_summaries_training_stage_id_foreign` (`training_stage_id`);

--
-- Indeks untuk tabel `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `participants_training_id_foreign` (`training_id`),
  ADD KEY `participants_user_id_foreign` (`user_id`),
  ADD KEY `participants_biodata_file_id_foreign` (`biodata_file_id`),
  ADD KEY `participants_surat_tugas_file_id_foreign` (`surat_tugas_file_id`),
  ADD KEY `participants_pas_foto_file_id_foreign` (`pas_foto_file_id`);

--
-- Indeks untuk tabel `pengajars`
--
ALTER TABLE `pengajars`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengajars_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `pengajar_schedule_documents`
--
ALTER TABLE `pengajar_schedule_documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pengajar_schedule_documents_schedule_id_unique` (`schedule_id`),
  ADD KEY `pengajar_schedule_documents_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indeks untuk tabel `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedules_training_id_foreign` (`training_id`),
  ADD KEY `schedules_pengajar_id_foreign` (`pengajar_id`);

--
-- Indeks untuk tabel `trainings`
--
ALTER TABLE `trainings`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `training_stages`
--
ALTER TABLE `training_stages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `training_stages_training_id_foreign` (`training_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `alumni_profiles`
--
ALTER TABLE `alumni_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `evaluation_forms`
--
ALTER TABLE `evaluation_forms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `evaluation_questions`
--
ALTER TABLE `evaluation_questions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=578;

--
-- AUTO_INCREMENT untuk tabel `evaluation_results_l1`
--
ALTER TABLE `evaluation_results_l1`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT untuk tabel `evaluation_results_l2`
--
ALTER TABLE `evaluation_results_l2`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `evaluation_results_l34`
--
ALTER TABLE `evaluation_results_l34`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `files`
--
ALTER TABLE `files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT untuk tabel `folders`
--
ALTER TABLE `folders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT untuk tabel `monitoring_results`
--
ALTER TABLE `monitoring_results`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `monitoring_summaries`
--
ALTER TABLE `monitoring_summaries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `participants`
--
ALTER TABLE `participants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `pengajars`
--
ALTER TABLE `pengajars`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pengajar_schedule_documents`
--
ALTER TABLE `pengajar_schedule_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `questions`
--
ALTER TABLE `questions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `trainings`
--
ALTER TABLE `trainings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `training_stages`
--
ALTER TABLE `training_stages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `alumni_profiles`
--
ALTER TABLE `alumni_profiles`
  ADD CONSTRAINT `alumni_profiles_participant_id_foreign` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`),
  ADD CONSTRAINT `alumni_profiles_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`);

--
-- Ketidakleluasaan untuk tabel `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_participant_id_foreign` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendances_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `evaluation_forms`
--
ALTER TABLE `evaluation_forms`
  ADD CONSTRAINT `evaluation_forms_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `evaluation_forms_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `evaluation_questions`
--
ALTER TABLE `evaluation_questions`
  ADD CONSTRAINT `evaluation_questions_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `evaluation_results_l1`
--
ALTER TABLE `evaluation_results_l1`
  ADD CONSTRAINT `evaluation_results_l1_participant_id_foreign` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`),
  ADD CONSTRAINT `evaluation_results_l1_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `evaluation_questions` (`id`),
  ADD CONSTRAINT `evaluation_results_l1_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`),
  ADD CONSTRAINT `evaluation_results_l1_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`);

--
-- Ketidakleluasaan untuk tabel `evaluation_results_l2`
--
ALTER TABLE `evaluation_results_l2`
  ADD CONSTRAINT `evaluation_results_l2_participant_id_foreign` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `evaluation_results_l34`
--
ALTER TABLE `evaluation_results_l34`
  ADD CONSTRAINT `evaluation_results_l34_participant_id_foreign` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`),
  ADD CONSTRAINT `evaluation_results_l34_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `evaluation_questions` (`id`),
  ADD CONSTRAINT `evaluation_results_l34_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `files_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `folders`
--
ALTER TABLE `folders`
  ADD CONSTRAINT `folders_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `folders_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `folders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `monitoring_results`
--
ALTER TABLE `monitoring_results`
  ADD CONSTRAINT `monitoring_results_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `evaluation_questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `monitoring_results_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`),
  ADD CONSTRAINT `monitoring_results_training_stage_id_foreign` FOREIGN KEY (`training_stage_id`) REFERENCES `training_stages` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `monitoring_summaries`
--
ALTER TABLE `monitoring_summaries`
  ADD CONSTRAINT `monitoring_summaries_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `monitoring_summaries_training_stage_id_foreign` FOREIGN KEY (`training_stage_id`) REFERENCES `training_stages` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `participants`
--
ALTER TABLE `participants`
  ADD CONSTRAINT `participants_biodata_file_id_foreign` FOREIGN KEY (`biodata_file_id`) REFERENCES `files` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `participants_pas_foto_file_id_foreign` FOREIGN KEY (`pas_foto_file_id`) REFERENCES `files` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `participants_surat_tugas_file_id_foreign` FOREIGN KEY (`surat_tugas_file_id`) REFERENCES `files` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `participants_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `participants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengajars`
--
ALTER TABLE `pengajars`
  ADD CONSTRAINT `pengajars_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengajar_schedule_documents`
--
ALTER TABLE `pengajar_schedule_documents`
  ADD CONSTRAINT `pengajar_schedule_documents_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pengajar_schedule_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_pengajar_id_foreign` FOREIGN KEY (`pengajar_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `schedules_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `training_stages`
--
ALTER TABLE `training_stages`
  ADD CONSTRAINT `training_stages_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
