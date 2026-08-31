-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 30 Agu 2026 pada 16.31
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
(4, 5, 'Membuat pelatihan & folder dokumen: Pelatihan Pengkajian Kebutuhan Pascabencana', 'Pelatihan', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-08-28 13:31:14', '2026-08-28 13:31:14'),
(6, 2, 'Menghapus kepesertaan Simpan Aku aja 22 dari pelatihan Pelatihan Pengkajian Kebutuhan Pascabencana beserta seluruh data terkait.', 'Peserta', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-08-30 07:02:05', '2026-08-30 07:02:05'),
(8, 2, 'Menghapus pelatihan Pelatihan Pengkajian Kebutuhan Pascabencana beserta peserta, evaluasi, monitoring, jadwal, forum, dan seluruh dokumen terkait.', 'Pelatihan', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-08-30 07:42:08', '2026-08-30 07:42:08'),
(9, 2, 'Membuat pelatihan & folder dokumen: Pealtihan Keuangan Daerah', 'Pelatihan', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-08-30 08:21:26', '2026-08-30 08:21:26'),
(10, 2, 'Menghapus pelatihan Pealtihan Keuangan Daerah beserta peserta, evaluasi, monitoring, jadwal, forum, dan seluruh dokumen terkait.', 'Pelatihan', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-08-30 08:22:23', '2026-08-30 08:22:23'),
(11, 2, 'Membuat pelatihan & folder dokumen: Pealtihan Keuangan Daerah', 'Pelatihan', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-08-30 09:11:51', '2026-08-30 09:11:51'),
(12, 12, 'Menghapus file: Biodata - Contoh Peserta - 19950332026211005.pdf', 'Dokumen', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-08-30 11:05:43', '2026-08-30 11:05:43'),
(13, 12, 'Menghapus file: Biodata - Contoh Peserta - 19950332026211005.pdf', 'Dokumen', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-08-30 12:15:08', '2026-08-30 12:15:08'),
(14, 12, 'Menghapus file: Biodata - Contoh Peserta - 19950332026211005.pdf', 'Dokumen', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-08-30 12:21:03', '2026-08-30 12:21:03'),
(15, 12, 'Membagikan folder Sertifikasi kepada Simpan Aku aja 22 sebagai contributor', 'Dokumen', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-08-30 13:27:35', '2026-08-30 13:27:35'),
(16, 1, 'Mengunggah 1 file ke folder Berita Acara', 'Dokumen', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-08-30 13:29:39', '2026-08-30 13:29:39'),
(17, 12, 'Membuat pelatihan & folder dokumen: Pelatihan Pengkajian Kebutuhan Pascabencana', 'Pelatihan', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-08-30 13:43:37', '2026-08-30 13:43:37');

-- --------------------------------------------------------

--
-- Struktur dari tabel `agendas`
--

CREATE TABLE `agendas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `scope` varchar(255) NOT NULL,
  `agenda_type` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `bidang` varchar(255) DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `agenda_schedules`
--

CREATE TABLE `agenda_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `agenda_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `external_place` varchar(255) DEFAULT NULL,
  `zoom_link` varchar(255) DEFAULT NULL,
  `participants_info` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Struktur dari tabel `assets`
--

CREATE TABLE `assets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'ruangan',
  `facilities` text DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `capacity` int(10) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `assets`
--

INSERT INTO `assets` (`id`, `name`, `type`, `facilities`, `location`, `capacity`, `description`, `is_active`, `is_public`, `created_by`, `created_at`, `updated_at`) VALUES
(11, 'Ruang Makan Peserta', 'ruangan', 'Meja Makan, Kursi Makan, Toilet, live Musik', 'Gedung Kelas Lantai 1', 200, NULL, 1, 1, 9, '2026-08-30 07:45:50', '2026-08-30 07:45:50'),
(12, 'Labolatorium Bahasa', 'ruangan', 'Komputer, AC, Infocus, Soundsystem, Headphone', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 9, '2026-08-30 07:46:52', '2026-08-30 07:46:52'),
(13, 'Ruang Ballroom', 'ruangan', 'Kursi, Meja, AC, LED, Soundsystem, Infocus', 'Belakang Amphiteater', 50, NULL, 1, 1, 9, '2026-08-30 07:47:41', '2026-08-30 07:47:41'),
(14, 'Ruang Rapat', 'ruangan', 'AC, Soundsystem, Proyektor, Meja, Kursi', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 9, '2026-08-30 07:48:54', '2026-08-30 07:48:54'),
(15, 'Ruang Rapat Bidang SKPK', 'ruangan', 'Kursi, TV, AC, Meja', 'Gedung Kantor Lantai 2', 30, NULL, 1, 1, 9, '2026-08-30 07:49:38', '2026-08-30 07:49:38'),
(16, 'RUANG KELAS 3-X', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 9, '2026-08-30 07:50:36', '2026-08-30 07:50:36'),
(17, 'RUANG KELAS 3-IX', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 9, '2026-08-30 07:51:20', '2026-08-30 07:51:20'),
(18, 'RUANG KELAS 3-VIII', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 9, '2026-08-30 07:51:58', '2026-08-30 07:51:58'),
(19, 'RUANG KELAS 3-VII', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 9, '2026-08-30 07:52:27', '2026-08-30 07:52:27'),
(20, 'RUANG KELAS 3-VI', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 9, '2026-08-30 07:53:04', '2026-08-30 07:53:04'),
(21, 'RUANG KELAS 3-V', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 9, '2026-08-30 07:53:35', '2026-08-30 07:53:35'),
(22, 'RUANG KELAS 3-IV', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 9, '2026-08-30 07:54:06', '2026-08-30 07:54:06'),
(23, 'RUANG KELAS 3-III', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 9, '2026-08-30 07:54:39', '2026-08-30 07:54:39'),
(24, 'RUANG KELAS 3-II', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 9, '2026-08-30 07:55:03', '2026-08-30 07:55:03'),
(25, 'RUANG KELAS 3-I', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 9, '2026-08-30 07:55:29', '2026-08-30 07:55:29'),
(26, 'RUANG RAPAT UTAMA GEDUNG KANTOR', 'ruangan', 'AC, TV, Kursi, Meja, Sofa, Mini Perpustakaan', 'Gedung Kantor Lantai 1', 30, NULL, 1, 1, 9, '2026-08-30 07:56:33', '2026-08-30 07:56:33'),
(27, 'AMPHITEATHER B', 'ruangan', 'AC, Infocus, Soundsystem, Kursi, Meja', 'Gedung Bawah Lantai 1', 120, NULL, 1, 1, 9, '2026-08-30 07:57:15', '2026-08-30 07:57:15'),
(28, 'AMPHITEATHER A', 'ruangan', 'AC, Infocus, Soundsystem, Kursi, Meja', 'Gedung Bawah Lantai 1', 120, NULL, 1, 1, 9, '2026-08-30 07:57:47', '2026-08-30 07:57:47'),
(29, 'RUANG RAPAT LANTAI 4 GEDUNG KANTOR', 'ruangan', 'Ac, Soundsystem, kursi, meja', 'Gedung Kantor Lantai 4', 15, NULL, 1, 1, 9, '2026-08-30 07:58:39', '2026-08-30 07:58:39'),
(30, 'RUANG MULTIMEDIA', 'ruangan', 'Ac, Infocus, Kursi Level, Meja, Soundsystem', 'Wisma Block C', 30, NULL, 1, 1, 9, '2026-08-30 07:59:19', '2026-08-30 07:59:19'),
(31, 'GUEST HOUSE', 'ruangan', 'AC, TV, Kursi, Sofa, Meja', 'Sebrang Wisma A4', 30, NULL, 1, 1, 9, '2026-08-30 08:00:08', '2026-08-30 08:00:08'),
(32, 'LAB. KOMPUTER', 'ruangan', 'Komputer, AC, Infocus, Soundsystem', 'Gedung Kelas Lantai 1', 30, NULL, 1, 1, 9, '2026-08-30 08:01:15', '2026-08-30 08:01:15'),
(33, 'RUANG KELAS 2-X', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 9, '2026-08-30 08:01:56', '2026-08-30 08:01:56'),
(34, 'RUANG KELAS 2-IX', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 9, '2026-08-30 08:02:37', '2026-08-30 08:02:37'),
(35, 'RUANG KELAS 2-VIII', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 9, '2026-08-30 08:03:19', '2026-08-30 08:03:19'),
(36, 'RUANG KELAS 2-VII', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 9, '2026-08-30 08:03:57', '2026-08-30 08:03:57'),
(37, 'RUANG KELAS 2-VI', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 9, '2026-08-30 08:04:25', '2026-08-30 08:04:25'),
(38, 'RUANG KELAS 2-V', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 9, '2026-08-30 08:04:50', '2026-08-30 08:04:50'),
(39, 'RUANG KELAS 2-IV', 'ruangan', 'AC, Soundsystem, Infocus, Meja', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 9, '2026-08-30 08:05:14', '2026-08-30 08:05:14'),
(40, 'RUANG KELAS 2-III', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 9, '2026-08-30 08:05:51', '2026-08-30 08:05:51'),
(41, 'RUANG KELAS 2-II', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 9, '2026-08-30 08:06:12', '2026-08-30 08:06:12'),
(42, 'RUANG KELAS 2-I', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 9, '2026-08-30 08:06:42', '2026-08-30 08:06:42'),
(43, 'AULA KUJANG', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Twin Tower Lantai 3', 500, NULL, 1, 1, 9, '2026-08-30 08:07:25', '2026-08-30 08:07:25'),
(44, 'AULA UTAMA', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi, Sofa', 'Gedung Aula Bawah Lantai 2', 500, NULL, 1, 1, 9, '2026-08-30 08:08:13', '2026-08-30 08:08:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `asset_bookings`
--

CREATE TABLE `asset_bookings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `asset_id` bigint(20) UNSIGNED NOT NULL,
  `bookable_type` varchar(255) NOT NULL,
  `bookable_id` bigint(20) UNSIGNED NOT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `asset_bookings`
--

INSERT INTO `asset_bookings` (`id`, `asset_id`, `bookable_type`, `bookable_id`, `starts_at`, `ends_at`, `created_by`, `created_at`, `updated_at`) VALUES
(17, 28, 'App\\Models\\Schedule', 9, '2026-08-30 08:00:00', '2026-08-30 09:00:00', 12, '2026-08-30 13:44:29', '2026-08-30 13:44:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `asset_images`
--

CREATE TABLE `asset_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `asset_id` bigint(20) UNSIGNED NOT NULL,
  `path` varchar(255) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `asset_images`
--

INSERT INTO `asset_images` (`id`, `asset_id`, `path`, `sort_order`, `created_at`, `updated_at`) VALUES
(8, 11, 'assets/Lr1TC2LIdkQBGojz5u8boxxRk87Kj1sDqphJMxOV.jpg', 0, '2026-08-30 07:45:50', '2026-08-30 07:45:50'),
(9, 11, 'assets/0qNVk9bNLBwYcnZTvLvTUT2FZdWHiyLa9NiNKyr4.jpg', 1, '2026-08-30 07:45:50', '2026-08-30 07:45:50'),
(10, 11, 'assets/FSa7L6UVrMrNd1Q0YILW8c3oyEpKHmwvrvdKJUs2.jpg', 2, '2026-08-30 07:45:50', '2026-08-30 07:45:50'),
(11, 12, 'assets/pvdrS1iTgIyTAYad384csV08WcJMuVWQbQgcwfk2.jpg', 0, '2026-08-30 07:46:52', '2026-08-30 07:46:52'),
(12, 12, 'assets/N6nLz73mNBIuMf1Inr74DSbdsNXgbBXZ2Pj84Ybn.jpg', 1, '2026-08-30 07:46:52', '2026-08-30 07:46:52'),
(13, 13, 'assets/ABYzGLyA70ltyHsBgc51wurler7DOVRybDV2FIkM.jpg', 0, '2026-08-30 07:47:41', '2026-08-30 07:47:41'),
(14, 14, 'assets/nHV7JXMwJdpmjsFX3z3P9pbbz3kmJe9HZ3j9ruZe.jpg', 0, '2026-08-30 07:48:54', '2026-08-30 07:48:54'),
(15, 14, 'assets/GHgiL7QysVEEi1DiFvjmQk2QzJ2bOnwRQVxsiZKD.jpg', 1, '2026-08-30 07:48:54', '2026-08-30 07:48:54'),
(16, 15, 'assets/MplV1dslLZLLHoOspkkHCdFkDaW7thoYY3lRnU9U.jpg', 0, '2026-08-30 07:49:38', '2026-08-30 07:49:38'),
(17, 16, 'assets/glrQOtwBbxYiXINj0YdQBgwPTea6IIKXrVuueSGL.jpg', 0, '2026-08-30 07:50:36', '2026-08-30 07:50:36'),
(18, 16, 'assets/gdnYnuPLDWedFBdJw0GFSRb9KDliqw3RT1w08oid.jpg', 1, '2026-08-30 07:50:36', '2026-08-30 07:50:36'),
(19, 16, 'assets/GW85n7uS37hJOw7qJnRc0YGjEJRAOnKn1PQ2oh6U.jpg', 2, '2026-08-30 07:50:36', '2026-08-30 07:50:36'),
(20, 16, 'assets/F4MOdqdOUOTu1FnCh8oAcjfwezVWmXakAiVxSWpc.jpg', 3, '2026-08-30 07:50:36', '2026-08-30 07:50:36'),
(21, 17, 'assets/RYmw5oyaK9gB9rP6KrgIaTKFYYRORF459AMrgsJ0.jpg', 0, '2026-08-30 07:51:20', '2026-08-30 07:51:20'),
(22, 17, 'assets/fpE2YZ1EAYG8S7495QpkGwz5kPG1P3wWn9iAAq7T.jpg', 1, '2026-08-30 07:51:20', '2026-08-30 07:51:20'),
(23, 17, 'assets/qaxxWx9jCHWH2QXdwgyxtdZwwSDx0UpI8dLojuo5.jpg', 2, '2026-08-30 07:51:20', '2026-08-30 07:51:20'),
(24, 17, 'assets/4np95DEFkMWet2hFdGhyxVLHeToinXiiZGaoHTKp.jpg', 3, '2026-08-30 07:51:20', '2026-08-30 07:51:20'),
(25, 18, 'assets/61wIkOh7DsTXmpw6Ny74X9lrbalPZzABj0rej5HF.jpg', 0, '2026-08-30 07:51:58', '2026-08-30 07:51:58'),
(26, 18, 'assets/Q6FrgsDWmdbirVSiBRZWjIDNeYsg5n3txQP1s1jc.jpg', 1, '2026-08-30 07:51:58', '2026-08-30 07:51:58'),
(27, 18, 'assets/ZtQ4RPlc7CjY654Lxd2DTjiW5IWDkmoMy0tFEWVY.jpg', 2, '2026-08-30 07:51:58', '2026-08-30 07:51:58'),
(28, 18, 'assets/OdgNB4VkJIJ0oHNlyWfhUkv7yw4hGqDSneQtMAdj.jpg', 3, '2026-08-30 07:51:58', '2026-08-30 07:51:58'),
(29, 19, 'assets/hnheFLdfYTWW7c9WJ0ifkKA28eCUdtqzU3Ds2RoN.jpg', 0, '2026-08-30 07:52:27', '2026-08-30 07:52:27'),
(30, 19, 'assets/wOKAiM0u6XsndEOSLyG6uTDvLnHkmHjLBiGVPlAP.jpg', 1, '2026-08-30 07:52:27', '2026-08-30 07:52:27'),
(31, 19, 'assets/JWHpqlybGrN2BuFkl5mmNoyecwu25Mh4M4knr12J.jpg', 2, '2026-08-30 07:52:27', '2026-08-30 07:52:27'),
(32, 19, 'assets/eixKPMPnq9X2NRHjHR2pGoTH16ZWZAbjFhvfRPsi.jpg', 3, '2026-08-30 07:52:27', '2026-08-30 07:52:27'),
(33, 20, 'assets/2ek9XUoOfR0WBJRAaBTTn7tn1t4JcmzWpyoKXwoi.jpg', 0, '2026-08-30 07:53:04', '2026-08-30 07:53:04'),
(34, 20, 'assets/N9qkYT3Ifi22B8U9ldXhFRK8gl3x67AqZfMu0W9z.jpg', 1, '2026-08-30 07:53:04', '2026-08-30 07:53:04'),
(35, 20, 'assets/YjoSommN1AmGPBmiJV6LuXq7bp1MPi17MIebMKVN.jpg', 2, '2026-08-30 07:53:04', '2026-08-30 07:53:04'),
(36, 20, 'assets/1RGyqmCxkZNywrwsgOMMbrInYq1mi5ipU0XVYZgp.jpg', 3, '2026-08-30 07:53:04', '2026-08-30 07:53:04'),
(37, 21, 'assets/RRyBwnTQW8VcIp1uJ1sERVvBtwzQHHaJzjkVbRM8.jpg', 0, '2026-08-30 07:53:35', '2026-08-30 07:53:35'),
(38, 21, 'assets/vb6ZSwOv4ZvhTSxQgqECUOktGahMeyFDRC1rTMxA.jpg', 1, '2026-08-30 07:53:35', '2026-08-30 07:53:35'),
(39, 21, 'assets/CP1R7FUxxzKySNmdCcTqUfyTXREwWKdNIxH3ZdWg.jpg', 2, '2026-08-30 07:53:35', '2026-08-30 07:53:35'),
(40, 21, 'assets/dwhY55shH5FwN5gvo5JjaLUXQ11HiGUN2kIAb4g1.jpg', 3, '2026-08-30 07:53:35', '2026-08-30 07:53:35'),
(41, 22, 'assets/1QiYEAF7GY06Xte0EHGi7isQiwe5CwEMDfCoYhXq.jpg', 0, '2026-08-30 07:54:06', '2026-08-30 07:54:06'),
(42, 22, 'assets/viDsxLYBJFtKfXhyrIXy5rimQKfdV31SUNOfNvDX.jpg', 1, '2026-08-30 07:54:06', '2026-08-30 07:54:06'),
(43, 22, 'assets/vxVlr5IYmGoPqIhiHKsd0mLLfvJga4KkH45Etiwd.jpg', 2, '2026-08-30 07:54:06', '2026-08-30 07:54:06'),
(44, 22, 'assets/XFxEBlLr7atALbcJ6QkzFOsO0LRlNDq0jKCwMKBa.jpg', 3, '2026-08-30 07:54:07', '2026-08-30 07:54:07'),
(45, 23, 'assets/ZsEBfUB3k31zc4gEquIEMyYELKhqt6f46gLa8Aml.jpg', 0, '2026-08-30 07:54:39', '2026-08-30 07:54:39'),
(46, 23, 'assets/R00FIRr0YewFdAeNZDpA2EnauHsLn1CFWltN3G1A.jpg', 1, '2026-08-30 07:54:39', '2026-08-30 07:54:39'),
(47, 23, 'assets/tYKCrjFOp0EzOf8odYVIECzWpi2y1GnMKC1y0p0J.jpg', 2, '2026-08-30 07:54:39', '2026-08-30 07:54:39'),
(48, 23, 'assets/rboPqxM3sGsOrb22H5lZXNis6fOrwul6Raw4FxkF.jpg', 3, '2026-08-30 07:54:39', '2026-08-30 07:54:39'),
(49, 24, 'assets/8C8R4ZunGZbgFDpawvKw8AUy2hklK5ZHatKiMbyt.jpg', 0, '2026-08-30 07:55:03', '2026-08-30 07:55:03'),
(50, 24, 'assets/LcxbLyiCxrZgiXcKkp6iBG6Kl1mD4eNp6Tw1TrR0.jpg', 1, '2026-08-30 07:55:03', '2026-08-30 07:55:03'),
(51, 24, 'assets/CO6wnkfzelSMrtpkdvAGP8B0GESFpxDTRnL3b0Rc.jpg', 2, '2026-08-30 07:55:03', '2026-08-30 07:55:03'),
(52, 24, 'assets/UMs2htdiY2VBFWbVp63PQPk9FX0IKJwRRBCbzZDO.jpg', 3, '2026-08-30 07:55:03', '2026-08-30 07:55:03'),
(53, 25, 'assets/G5xN4HCywUiH6UakFFO7mXSQzL6y3CvhVuJIMXHX.jpg', 0, '2026-08-30 07:55:29', '2026-08-30 07:55:29'),
(54, 25, 'assets/SDDfD8AQZQyrsoGa6OvF61SL0eDdFor3PsEJa3qq.jpg', 1, '2026-08-30 07:55:29', '2026-08-30 07:55:29'),
(55, 25, 'assets/12VGTEsycAD1U8mSAiX6W74Wa5EjYDj4ZpfEU8Kh.jpg', 2, '2026-08-30 07:55:29', '2026-08-30 07:55:29'),
(56, 25, 'assets/Q22UvAeMrakhGt8ULLRLNFm3JfNyJQLf0eJnBVFi.jpg', 3, '2026-08-30 07:55:29', '2026-08-30 07:55:29'),
(57, 26, 'assets/QMylcBa3LrSlz4AeTg3ZWaFsqDveQT34GyJTEqSv.jpg', 0, '2026-08-30 07:56:33', '2026-08-30 07:56:33'),
(58, 27, 'assets/kTgFY3oYu9DAU6JHMo4yadsr4Gl68JjhhJo0EV6c.jpg', 0, '2026-08-30 07:57:15', '2026-08-30 07:57:15'),
(59, 28, 'assets/pU0duvgm5yfp1t4GLvUzrIGTsLAT9emzSUalbAQG.jpg', 0, '2026-08-30 07:57:47', '2026-08-30 07:57:47'),
(60, 29, 'assets/4WPAsprl2wMVSJbgZmmGszKB1BgLlPBDAcykp4Eu.jpg', 0, '2026-08-30 07:58:39', '2026-08-30 07:58:39'),
(61, 30, 'assets/Pu7HhplVBWwKOOsE4gXPtuRl28VwWa2uNVz5LSVY.jpg', 0, '2026-08-30 07:59:19', '2026-08-30 07:59:19'),
(62, 31, 'assets/mchBieoUp8pPWO6zh2jg4RIrXgEg21kq4KYTr1dt.jpg', 0, '2026-08-30 08:00:08', '2026-08-30 08:00:08'),
(63, 32, 'assets/z3UV0YANjnFd1q5RXjXO2Ft5X60bxg34oUNpoWpc.jpg', 0, '2026-08-30 08:01:15', '2026-08-30 08:01:15'),
(64, 33, 'assets/vTX1tF1Um7NHqX0uJeOrkHylURURBK0WkTrHfiAF.jpg', 0, '2026-08-30 08:01:56', '2026-08-30 08:01:56'),
(65, 34, 'assets/oGgGk65uDo2U5SJt74KkG6c51LBsnHbGtxhZjhNS.jpg', 0, '2026-08-30 08:02:37', '2026-08-30 08:02:37'),
(66, 35, 'assets/Zz911DRfrC2LOEHK59I8oEeNomAbiXcZq8nyuF9g.jpg', 0, '2026-08-30 08:03:19', '2026-08-30 08:03:19'),
(67, 36, 'assets/tlSbSeNvGK4Jxk06zles7hQnXMPh49FQU8jB38AV.jpg', 0, '2026-08-30 08:03:57', '2026-08-30 08:03:57'),
(68, 37, 'assets/UDg07otPa6ynavjz1f6ZJwRSGDTAiQiChmIsWY3X.jpg', 0, '2026-08-30 08:04:25', '2026-08-30 08:04:25'),
(69, 38, 'assets/h7ZOk3DhIIwOBO4Wyf4t0mVoRmO2uQGQZcGpfecT.jpg', 0, '2026-08-30 08:04:50', '2026-08-30 08:04:50'),
(70, 39, 'assets/Nl2U8YY3otSYTf0n9ncqaZ74VVOiwu1PNMfHA1rk.jpg', 0, '2026-08-30 08:05:14', '2026-08-30 08:05:14'),
(71, 40, 'assets/xGL8Yok0QA5VbIJbgwbmsydKh6E8CwVFcc8MIICZ.jpg', 0, '2026-08-30 08:05:51', '2026-08-30 08:05:51'),
(72, 41, 'assets/sug57E5LI4RxG6AhLGrgLkEmC7xxKJf8SPtPsLo0.jpg', 0, '2026-08-30 08:06:12', '2026-08-30 08:06:12'),
(73, 42, 'assets/DZz9bWDtPzWbrwzYzzngRllXLGTfQidEjk0mdivn.jpg', 0, '2026-08-30 08:06:42', '2026-08-30 08:06:42'),
(74, 43, 'assets/EkMnJCyqqvpIXmRRfM7dgp02xRvyXoT5gsUZz4LR.jpg', 0, '2026-08-30 08:07:25', '2026-08-30 08:07:25'),
(75, 44, 'assets/JMlKGgA8uB17qpudO1LlncFLFkeYJJ3MM3tMi4uy.png', 0, '2026-08-30 08:08:13', '2026-08-30 08:08:13');

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

--
-- Dumping data untuk tabel `attendances`
--

INSERT INTO `attendances` (`id`, `schedule_id`, `participant_id`, `status`, `check_in_at`, `keterangan`, `created_at`, `updated_at`, `timezone_label`) VALUES
(3, 9, 18, 'hadir', '2026-08-30 14:14:20', NULL, '2026-08-30 14:14:21', '2026-08-30 14:14:21', 'WIB');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Struktur dari tabel `certification_events`
--

CREATE TABLE `certification_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `certification_type_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `location` varchar(255) NOT NULL,
  `supervisor_name` varchar(255) NOT NULL,
  `supervisor_phone` varchar(30) DEFAULT NULL,
  `supervisor_institution` varchar(255) DEFAULT NULL,
  `participant_quota` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `public_token` varchar(64) DEFAULT NULL,
  `folder_id` bigint(20) UNSIGNED DEFAULT NULL,
  `minutes_file_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `certification_events`
--

INSERT INTO `certification_events` (`id`, `certification_type_id`, `title`, `start_date`, `end_date`, `location`, `supervisor_name`, `supervisor_phone`, `supervisor_institution`, `participant_quota`, `public_token`, `folder_id`, `minutes_file_id`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Sertifikasi PBPJ Level 1 Kabupaten Bandung', '2026-08-27', '2026-08-27', 'Bpsdm Provinsi Jawa Barat', 'Velicia', '08080453685345', 'LKPP', 40, 'iciQIYpZW0hMi9ppPgdqJFjoE4JsA64gB3lLGjlyFBakdr2F', 55, 111, 12, '2026-08-30 10:02:29', '2026-08-30 10:02:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `certification_participants`
--

CREATE TABLE `certification_participants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `certification_event_id` bigint(20) UNSIGNED NOT NULL,
  `nip_nik` varchar(80) NOT NULL,
  `name` varchar(255) NOT NULL,
  `position` varchar(255) DEFAULT NULL,
  `institution` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `result` varchar(30) NOT NULL DEFAULT 'belum_ditentukan',
  `biodata_token` varchar(64) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `birth_place_date` varchar(255) DEFAULT NULL,
  `rank_grade` varchar(255) DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `gender` varchar(30) DEFAULT NULL,
  `education` varchar(255) DEFAULT NULL,
  `office_address` text DEFAULT NULL,
  `trainings` text DEFAULT NULL,
  `signature_path` varchar(255) DEFAULT NULL,
  `biodata_file_id` bigint(20) UNSIGNED DEFAULT NULL,
  `biodata_submitted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `certification_participants`
--

INSERT INTO `certification_participants` (`id`, `certification_event_id`, `nip_nik`, `name`, `position`, `institution`, `province`, `city`, `phone`, `email`, `result`, `biodata_token`, `notes`, `created_at`, `updated_at`, `birth_place_date`, `rank_grade`, `religion`, `gender`, `education`, `office_address`, `trainings`, `signature_path`, `biodata_file_id`, `biodata_submitted_at`) VALUES
(5, 1, '19950332026211005', 'Contoh Peserta', 'Analis', 'Pemerintah Kabupaten/Kota', 'Jawa Barat', 'Kota Bandung', '081234567890', 'samsidin@example.go.id', 'lulus', '1AO9MekTBQeYauyjancj1KnzgmDkImQPiXOWoZqxTNvo7oqT', NULL, '2026-08-30 11:10:49', '2026-08-30 12:32:53', 'Bandung, 04 maret 2025', 'dasd', 'Islam', 'Laki-laki', 'S1', 'Kp. Nihmat, No.1 Rt/Rw 003/006, Cigugur Girang, Parongpong\r\nKp. Nihmat, No.1 Rt/Rw 003/006, Cigugur Girang,Parongpong', '-', 'certifications/signatures/5-5eDn8hpl.png', 125, '2026-08-30 12:21:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `certification_types`
--

CREATE TABLE `certification_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `certification_types`
--

INSERT INTO `certification_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'PBJP Level 1', '2026-08-30 10:00:37', '2026-08-30 10:00:37'),
(2, 'PPK Tipe-B', '2026-08-30 10:00:37', '2026-08-30 10:00:37'),
(3, 'PPK Tipe-C', '2026-08-30 10:00:37', '2026-08-30 10:00:37'),
(4, 'Pol PP', '2026-08-30 10:00:37', '2026-08-30 10:00:37'),
(5, 'P2UPD', '2026-08-30 10:00:37', '2026-08-30 10:00:37'),
(6, 'Keuangan Daerah', '2026-08-30 10:00:37', '2026-08-30 10:00:37');

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
(5, 9, 'penyelenggara', 'Evaluasi Penyelenggara', NULL, 'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan', NULL, '2026-08-30 13:58:37', '2026-08-30 13:58:37'),
(6, 9, 'narasumber', 'Evaluasi Narasumber', 9, 'Simpan Aku', 'Matei I untuk kegiatan itu', '2026-08-30 13:58:50', '2026-08-30 13:58:50');

-- --------------------------------------------------------

--
-- Struktur dari tabel `evaluation_questions`
--

CREATE TABLE `evaluation_questions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_type` varchar(255) DEFAULT NULL,
  `bidang` varchar(255) DEFAULT NULL,
  `program_evaluasi` varchar(30) NOT NULL DEFAULT 'semua',
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

INSERT INTO `evaluation_questions` (`id`, `training_type`, `bidang`, `program_evaluasi`, `training_id`, `category`, `metode`, `sub_category`, `question_text`, `type`, `options`, `created_at`, `updated_at`) VALUES
(1, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kesesuaian materi pembelajaran dengan harapan/kebutuhan peserta pelatihan', 'slider', NULL, '2026-08-28 22:37:15', '2026-08-28 23:20:09'),
(2, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kepuasan terhadap manfaat materi pelatihan yang sudah diberikan', 'slider', NULL, '2026-08-28 22:37:30', '2026-08-28 23:20:14'),
(3, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Peningkatan pengetahuan', 'slider', NULL, '2026-08-28 22:37:47', '2026-08-28 23:20:25'),
(4, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Sajian Materi dalam pelatihan dalam membantu tugas-tugas peserta', 'slider', NULL, '2026-08-28 22:37:57', '2026-08-28 23:25:54'),
(5, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Perubahan Sikap/Perilaku', 'slider', NULL, '2026-08-28 22:38:07', '2026-08-28 23:26:03'),
(6, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kerapian pakaian yang digunakan oleh panitia', 'slider', NULL, '2026-08-28 22:38:34', '2026-08-28 23:26:17'),
(7, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Pelayanan panitia kepada peserta dengan 3 S (senyum, sapa, salam)', 'slider', NULL, '2026-08-28 22:38:46', '2026-08-28 23:26:33'),
(8, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kesigapan panitia terhadap kebutuhan peserta', 'slider', NULL, '2026-08-28 22:38:55', '2026-08-28 23:28:01'),
(9, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Ketersediaan panitia yang kompeten melayani peserta pelatihan', 'slider', NULL, '2026-08-28 22:39:01', '2026-08-28 23:28:11'),
(10, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Penyelenggaraan pelatihan secara keseluruhan', 'slider', NULL, '2026-08-28 22:39:11', '2026-08-28 23:28:19'),
(11, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Administrasi Program (undangan, pendaftaran peserta, dll)', 'slider', NULL, '2026-08-28 22:39:22', '2026-08-28 23:28:27'),
(12, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kesesuaian soal pre & post test dengan materi yang diajarkan', 'slider', NULL, '2026-08-28 22:39:34', '2026-08-28 23:28:35'),
(13, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Ketersediaan waktu yang cukup dalam pengerjaan soal pre & post test', 'slider', NULL, '2026-08-28 22:39:42', '2026-08-28 23:28:49'),
(14, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Ketertiban penyelenggaraan pre & post test', 'slider', NULL, '2026-08-28 22:39:50', '2026-08-28 23:28:56'),
(15, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Profesionalitas panitia dalam memandu pre & post test', 'slider', NULL, '2026-08-28 22:39:59', '2026-08-28 23:29:04'),
(16, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Materi / Paparan yang sudah sesuai dengan kebutuhan Bapak / Ibu :', 'text', NULL, '2026-08-28 22:40:08', '2026-08-28 23:29:13'),
(17, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Materi / Paparan yang perlu diperbaiki :', 'text', NULL, '2026-08-28 22:40:18', '2026-08-28 23:29:23'),
(18, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Mohon Saudara berikan komentar untuk perbaikan kinerja kami :', 'text', NULL, '2026-08-28 22:40:44', '2026-08-28 23:36:15'),
(19, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kerapian dan kesopanan pakaian yang dikenakan oleh Pengajar', 'slider', NULL, '2026-08-28 22:42:00', '2026-08-28 22:42:00'),
(20, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kedisiplinan kehadiran sesuai jadwal', 'slider', NULL, '2026-08-28 22:42:10', '2026-08-28 22:42:10'),
(21, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan memberikan motivasi kepada peserta diklat', 'slider', NULL, '2026-08-28 22:42:19', '2026-08-28 22:42:19'),
(22, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan menggunakan media pembelajaran', 'slider', NULL, '2026-08-28 22:42:32', '2026-08-28 22:42:32'),
(23, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan berkomunikasi dan berinteraksi dengan peserta diklat', 'slider', NULL, '2026-08-28 22:42:44', '2026-08-28 22:42:44'),
(24, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan menyampaikan konsep / materi', 'slider', NULL, '2026-08-28 22:42:58', '2026-08-28 22:42:58'),
(25, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan menghubungkan konsep / materi dengan praktek', 'slider', NULL, '2026-08-28 22:43:11', '2026-08-28 22:43:11'),
(26, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan merespon pertanyaan', 'slider', NULL, '2026-08-28 22:43:20', '2026-08-28 22:43:20'),
(27, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kualitas bahan ajar dalam membantu proses pembelajaran peserta diklat', 'slider', NULL, '2026-08-28 22:43:32', '2026-08-28 22:43:32'),
(28, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kesesuaian bahan ajar dengan kurikulum yang digunakan', 'slider', NULL, '2026-08-28 22:43:43', '2026-08-28 22:43:43'),
(29, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kesesuaian materi pembelajaran dengan keadaan terkini', 'slider', NULL, '2026-08-28 22:43:55', '2026-08-28 22:43:55'),
(30, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Manfaat materi bagi perkembangan / perbaikan diri di masa yang akan datang', 'slider', NULL, '2026-08-28 22:44:06', '2026-08-28 22:44:06'),
(31, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Bahan ajar disajikan dalam keadaan baik dan bisa digunakan', 'slider', NULL, '2026-08-28 22:44:16', '2026-08-28 22:44:16'),
(32, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Metode pembelajaran yang digunakan pengajar memudahkan peserta diklat memahami materi', 'slider', NULL, '2026-08-28 22:44:26', '2026-08-28 22:44:26'),
(33, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan mengelola waktu pembelajaran', 'slider', NULL, '2026-08-28 22:44:34', '2026-08-28 22:44:34'),
(34, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Sistematika penyampaian materi pembelajaran', 'slider', NULL, '2026-08-28 22:46:41', '2026-08-28 22:46:41'),
(35, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Menciptakan suasana kelas yang kondusif untuk belajar', 'slider', NULL, '2026-08-28 22:46:52', '2026-08-28 22:46:52'),
(36, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Memberikan contoh yang membantu memahami konsep yang sulit', 'slider', NULL, '2026-08-28 22:47:09', '2026-08-28 22:47:09'),
(37, 'PKTI/PKTU', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Memberikan umpan balik yang konstruktif', 'slider', NULL, '2026-08-28 22:47:19', '2026-08-28 22:47:19'),
(38, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Pedoman Penggunaan web\'elearning informatif dan mudah dipahami', 'slider', NULL, '2026-08-28 23:30:47', '2026-08-28 23:40:05'),
(39, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Website e-learning mudah diakses', 'slider', NULL, '2026-08-28 23:30:56', '2026-08-28 23:39:54'),
(40, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Kemudahan Fitur yang tersedia', 'slider', NULL, '2026-08-28 23:31:08', '2026-08-28 23:39:29'),
(41, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Sistematika penyajian materi', 'slider', NULL, '2026-08-28 23:31:29', '2026-08-28 23:39:17'),
(42, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Tampilan tayangan', 'slider', NULL, '2026-08-28 23:31:40', '2026-08-28 23:39:06'),
(43, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Tujuan pembelajaran dapat tercapai secara optimal', 'slider', NULL, '2026-08-28 23:31:52', '2026-08-28 23:38:57'),
(44, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Kualitas bahan ajar', 'slider', NULL, '2026-08-28 23:32:07', '2026-08-28 23:38:49'),
(45, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Komposisi materi pelatihan', 'slider', NULL, '2026-08-28 23:32:17', '2026-08-28 23:38:43'),
(46, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Sekuensi materi pelatihan', 'slider', NULL, '2026-08-28 23:32:32', '2026-08-28 23:38:35'),
(47, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Durasi penyelenggaraan pelatihan', 'slider', NULL, '2026-08-28 23:32:44', '2026-08-28 23:38:23'),
(48, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Catatan / Saran', 'text', NULL, '2026-08-28 23:32:55', '2026-08-28 23:38:16'),
(49, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Persyaratan administrative sesuai ketentuan', 'slider', NULL, '2026-08-28 23:41:16', '2026-08-28 23:41:16'),
(50, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kecepatan atau responsivitas penyelenggara dalam memberikan layanan', 'slider', NULL, '2026-08-28 23:41:37', '2026-08-28 23:41:37'),
(51, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Keramahan penyelenggara', 'slider', NULL, '2026-08-28 23:41:53', '2026-08-28 23:41:53'),
(52, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Sarana dan prasarana (daring) sudah memadai', 'slider', NULL, '2026-08-28 23:42:07', '2026-08-28 23:42:07'),
(53, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kemudahan mengakses jadwal', 'slider', NULL, '2026-08-28 23:42:24', '2026-08-28 23:42:24'),
(54, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kedisiplinan penerapan jadwal pelatihan', 'slider', NULL, '2026-08-28 23:42:36', '2026-08-28 23:42:36'),
(55, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kecukupan waktu tutorial dan praktek', 'slider', NULL, '2026-08-28 23:42:53', '2026-08-28 23:42:53'),
(56, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Efektifitas pembimbingan dengan distance learning', 'slider', NULL, '2026-08-28 23:43:09', '2026-08-28 23:43:09'),
(57, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Tujuan pembelajaran dapat tercapai secara optimal', 'slider', NULL, '2026-08-28 23:43:23', '2026-08-28 23:43:23'),
(58, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kualitas bahan ajar', 'slider', NULL, '2026-08-28 23:43:37', '2026-08-28 23:43:37'),
(59, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Komposisi materi pelatihan', 'slider', NULL, '2026-08-28 23:43:51', '2026-08-28 23:43:51'),
(60, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Sekuensi pelatihan', 'slider', NULL, '2026-08-28 23:44:05', '2026-08-28 23:44:05'),
(61, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Durasi Penyelenggaraan pelatihan', 'slider', NULL, '2026-08-28 23:44:17', '2026-08-28 23:44:17'),
(62, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Apa yang paling berkesan bagi anda dalam pelatihan ini', 'slider', NULL, '2026-08-28 23:44:33', '2026-08-28 23:44:33'),
(63, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Apa yang membuat anda kurang/tidak puas dari pelatihan ini', 'text', NULL, '2026-08-28 23:44:48', '2026-08-28 23:44:48'),
(64, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Secara umum penilaian anda terhadap penyelenggaraan', 'text', NULL, '2026-08-28 23:45:01', '2026-08-28 23:45:01'),
(65, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Menurut anda, kedepannya apa yang perlu ditambahkan dalam pelatihan ini?', 'text', NULL, '2026-08-28 23:45:18', '2026-08-28 23:45:18'),
(66, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Catatan / Saran', 'text', NULL, '2026-08-28 23:45:34', '2026-08-28 23:45:34'),
(133, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kesesuaian materi pembelajaran dengan harapan/kebutuhan peserta pelatihan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(134, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kepuasan terhadap manfaat materi pelatihan yang sudah diberikan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(135, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Peningkatan pengetahuan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(136, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Sajian Materi dalam pelatihan dalam membantu tugas-tugas peserta', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(137, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Perubahan Sikap/Perilaku', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(138, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kerapian pakaian yang digunakan oleh panitia', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(139, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Pelayanan panitia kepada peserta dengan 3 S (senyum, sapa, salam)', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(140, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kesigapan panitia terhadap kebutuhan peserta', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(141, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Ketersediaan panitia yang kompeten melayani peserta pelatihan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(142, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Penyelenggaraan pelatihan secara keseluruhan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(143, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Administrasi Program (undangan, pendaftaran peserta, dll)', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(144, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kesesuaian soal pre & post test dengan materi yang diajarkan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(145, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Ketersediaan waktu yang cukup dalam pengerjaan soal pre & post test', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(146, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Ketertiban penyelenggaraan pre & post test', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(147, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Profesionalitas panitia dalam memandu pre & post test', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(148, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Materi / Paparan yang sudah sesuai dengan kebutuhan Bapak / Ibu :', 'text', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(149, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Materi / Paparan yang perlu diperbaiki :', 'text', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(150, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Mohon Saudara berikan komentar untuk perbaikan kinerja kami :', 'text', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(151, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kerapian dan kesopanan pakaian yang dikenakan oleh Pengajar', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(152, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kedisiplinan kehadiran sesuai jadwal', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(153, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan memberikan motivasi kepada peserta diklat', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(154, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan menggunakan media pembelajaran', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(155, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan berkomunikasi dan berinteraksi dengan peserta diklat', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(156, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan menyampaikan konsep / materi', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(157, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan menghubungkan konsep / materi dengan praktek', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(158, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan merespon pertanyaan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(159, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kualitas bahan ajar dalam membantu proses pembelajaran peserta diklat', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(160, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kesesuaian bahan ajar dengan kurikulum yang digunakan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(161, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kesesuaian materi pembelajaran dengan keadaan terkini', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(162, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Manfaat materi bagi perkembangan / perbaikan diri di masa yang akan datang', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(163, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Bahan ajar disajikan dalam keadaan baik dan bisa digunakan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(164, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Metode pembelajaran yang digunakan pengajar memudahkan peserta diklat memahami materi', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(165, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan mengelola waktu pembelajaran', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(166, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Sistematika penyampaian materi pembelajaran', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(167, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Menciptakan suasana kelas yang kondusif untuk belajar', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(168, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Memberikan contoh yang membantu memahami konsep yang sulit', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(169, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Memberikan umpan balik yang konstruktif', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(170, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Pedoman Penggunaan web\'elearning informatif dan mudah dipahami', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(171, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Website e-learning mudah diakses', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(172, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Kemudahan Fitur yang tersedia', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(173, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Sistematika penyajian materi', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(174, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Tampilan tayangan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(175, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Tujuan pembelajaran dapat tercapai secara optimal', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(176, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Kualitas bahan ajar', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(177, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Komposisi materi pelatihan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(178, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Sekuensi materi pelatihan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(179, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Durasi penyelenggaraan pelatihan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(180, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Catatan / Saran', 'text', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(181, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Persyaratan administrative sesuai ketentuan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(182, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kecepatan atau responsivitas penyelenggara dalam memberikan layanan', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(183, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Keramahan penyelenggara', 'slider', NULL, '2026-08-28 23:53:20', '2026-08-28 23:53:20'),
(184, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Sarana dan prasarana (daring) sudah memadai', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(185, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kemudahan mengakses jadwal', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(186, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kedisiplinan penerapan jadwal pelatihan', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(187, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kecukupan waktu tutorial dan praktek', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(188, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Efektifitas pembimbingan dengan distance learning', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(189, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Tujuan pembelajaran dapat tercapai secara optimal', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(190, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kualitas bahan ajar', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(191, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Komposisi materi pelatihan', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(192, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Sekuensi pelatihan', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(193, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Durasi Penyelenggaraan pelatihan', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(194, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Apa yang paling berkesan bagi anda dalam pelatihan ini', 'slider', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(195, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Apa yang membuat anda kurang/tidak puas dari pelatihan ini', 'text', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(196, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Secara umum penilaian anda terhadap penyelenggaraan', 'text', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(197, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Menurut anda, kedepannya apa yang perlu ditambahkan dalam pelatihan ini?', 'text', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(198, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Catatan / Saran', 'text', NULL, '2026-08-28 23:53:21', '2026-08-28 23:53:21'),
(265, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pendidikan terakhir (saat pelatihan)', 'dropdown', '[\"SD\\/SMP\",\"SMA\\/SMK\",\"D3\",\"S1\\/D4\",\"S2\\/S3\"]', '2026-08-28 23:58:53', '2026-08-28 23:58:53'),
(268, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pendidikan terakhir (saat ini)', 'dropdown', '[\"SD\\/SMP\",\"SMA\\/SMK\",\"D3\",\"S1\\/D4\",\"S2\\/S3\"]', '2026-08-29 00:14:15', '2026-08-29 00:14:41'),
(270, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pangkat golongan (saat pelatihan)', 'dropdown', '[\"I\\/a\",\"II\\/a\",\"II\\/b\",\"II\\/c\",\"II\\/d\",\"III\\/a\",\"III\\/b\",\"III\\/c\",\"III\\/d\",\"IV\\/a\",\"IV\\/b\",\"IV\\/b\",\"IV\\/c\",\"IV\\/d\",\"IV\\/e\"]', '2026-08-29 00:17:27', '2026-08-29 00:17:27'),
(271, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pangkat golongan (saat ini)', 'dropdown', '[\"I\\/a\",\"II\\/a\",\"II\\/b\",\"II\\/c\",\"II\\/d\",\"III\\/a\",\"III\\/b\",\"III\\/c\",\"III\\/d\",\"IV\\/a\",\"IV\\/b\",\"IV\\/b\",\"IV\\/c\",\"IV\\/d\",\"IV\\/e\"]', '2026-08-29 00:17:32', '2026-08-29 00:17:47'),
(272, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Jabatan (saat pelatihan)', 'text', NULL, '2026-08-29 00:18:18', '2026-08-29 00:18:18'),
(273, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Jabatan (saat ini)', 'text', NULL, '2026-08-29 00:19:00', '2026-08-29 00:19:00'),
(274, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Unit kerja (saat pelatihan)', 'text', NULL, '2026-08-29 00:19:14', '2026-08-29 00:19:14'),
(275, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Unit kerja (saat ini)', 'text', NULL, '2026-08-29 00:19:26', '2026-08-29 00:19:26'),
(276, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Perangkat daerah (saat pelatihan)', 'text', NULL, '2026-08-29 00:19:40', '2026-08-29 00:19:40'),
(277, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Perangkat daerah (saat ini)', 'text', NULL, '2026-08-29 00:19:56', '2026-08-29 00:20:05'),
(278, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah saat ini Anda sedang bertugas yang berkaitan dengan pelatihan?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 00:38:39', '2026-08-29 00:38:39'),
(279, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Iya, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 00:38:44', '2026-08-29 00:38:57'),
(280, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 00:39:07', '2026-08-29 00:39:15'),
(281, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pelatihan memiliki keterkaitan dengan bidang tugas Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 00:39:41', '2026-08-29 00:39:47'),
(282, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah Anda sudah melakukan transfer learning hasil pelatihan pada rekan kerja di tempat kerja Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 00:39:58', '2026-08-29 00:40:05'),
(283, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila sudah transfer learning, bagaimana cara Anda melakukan transfer learning?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 00:40:16', '2026-08-29 00:40:23'),
(284, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya memahami bahwa sumber daya yang diperlukan untuk implementasi materi pembelajaran di lingkungan kerja tersedia secara memadai', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:41:47', '2026-08-29 00:41:47'),
(285, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam menunjang pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:41:58', '2026-08-29 00:42:04'),
(286, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam keberhasilan pelaksanaan pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:42:17', '2026-08-29 00:42:23'),
(287, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam penyelesaian pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:42:41', '2026-08-29 00:42:49'),
(288, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya berkomitmen untuk implementasi materi pelatihan di lingkungan kerja saat ini', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:43:01', '2026-08-29 00:43:08'),
(290, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Bila sebagian besar jawaban Anda untuk pertanyaan nomor 1 sd 6 adalah Cukup, Kurang, Sangat Kurang, maka alasannya adalah (bisa pilih lebih dari satu jawaban)', 'checkbox', '[\"Saya tidak mempunyai pengetahuan dan keterampilan yang memadai\",\"Saya tidak punya rencana implementasi materi pembelajaran secara jelas dan terukur\",\"Saya mempunyai prioritas pekerjaan lain yang tidak sesuai dengan materi pembelajaran\",\"Saya tidak mempunyai dukungan sumber daya yang memadai untuk implementasi materi pembelajaran\",\"Pembelajaran yang saya ikuti tidak memberikan nilai tambah bagi saya\",\"Saya merasa bahwa materi pembelajaran tidak relevan dengan pekerjaan saya\",\"Saya merasa tidak ada kebijakan yang mendukung proses implementasi materi pembelajaran di lingkungan kerja\"]', '2026-08-29 00:53:46', '2026-08-29 00:53:46'),
(291, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap unit kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:54:48', '2026-08-29 00:54:48'),
(292, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap pengetahuan teoritis atau konsep Anda', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:55:02', '2026-08-29 00:55:15'),
(293, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan produktivitas', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:55:32', '2026-08-29 00:55:42'),
(294, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap perbaikan kualitas hasil kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:56:01', '2026-08-29 00:56:07'),
(295, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan kepuasan pelanggan', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:56:21', '2026-08-29 00:56:26'),
(296, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap penguatan hubungan antara rekan-rekan kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 00:56:43', '2026-08-29 00:56:57'),
(297, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Nama Anda', 'text', NULL, '2026-08-29 00:58:05', '2026-08-29 00:58:05'),
(298, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'NIP (ASN) / NIK (Non ASN) Anda', 'text', NULL, '2026-08-29 00:58:25', '2026-08-29 00:58:25'),
(299, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Jabatan Anda', 'text', NULL, '2026-08-29 00:58:55', '2026-08-29 00:58:55'),
(300, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Unit Kerja Anda', 'text', NULL, '2026-08-29 00:59:14', '2026-08-29 00:59:14'),
(301, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Perangkat Daerah Anda', 'text', NULL, '2026-08-29 00:59:31', '2026-08-29 00:59:31'),
(302, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'Nama Anda', 'text', NULL, '2026-08-29 01:00:08', '2026-08-29 01:00:08'),
(303, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'NIP (ASN) / NIK (Non ASN) Anda', 'text', NULL, '2026-08-29 01:00:24', '2026-08-29 01:00:24'),
(304, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'Jabatan Anda', 'text', NULL, '2026-08-29 01:00:38', '2026-08-29 01:00:38'),
(305, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'Unit Kerja Anda', 'text', NULL, '2026-08-29 01:00:51', '2026-08-29 01:00:51'),
(306, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'Perangkat Daerah Anda', 'text', NULL, '2026-08-29 01:01:05', '2026-08-29 01:01:05'),
(307, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah saat ini Anda sedang bertugas yang berkaitan dengan pelatihan?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(308, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Iya, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(309, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(310, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pelatihan memiliki keterkaitan dengan bidang tugas Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(311, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah Anda sudah melakukan transfer learning hasil pelatihan pada rekan kerja di tempat kerja Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(312, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila sudah transfer learning, bagaimana cara Anda melakukan transfer learning?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(313, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya memahami bahwa sumber daya yang diperlukan untuk implementasi materi pembelajaran di lingkungan kerja tersedia secara memadai', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(314, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam menunjang pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09');
INSERT INTO `evaluation_questions` (`id`, `training_type`, `bidang`, `program_evaluasi`, `training_id`, `category`, `metode`, `sub_category`, `question_text`, `type`, `options`, `created_at`, `updated_at`) VALUES
(315, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam keberhasilan pelaksanaan pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(316, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam penyelesaian pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(317, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya berkomitmen untuk implementasi materi pelatihan di lingkungan kerja saat ini', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(318, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Bila sebagian besar jawaban Anda untuk pertanyaan nomor 1 sd 6 adalah Cukup, Kurang, Sangat Kurang, maka alasannya adalah (bisa pilih lebih dari satu jawaban)', 'checkbox', '[\"Saya tidak mempunyai pengetahuan dan keterampilan yang memadai\",\"Saya tidak punya rencana implementasi materi pembelajaran secara jelas dan terukur\",\"Saya mempunyai prioritas pekerjaan lain yang tidak sesuai dengan materi pembelajaran\",\"Saya tidak mempunyai dukungan sumber daya yang memadai untuk implementasi materi pembelajaran\",\"Pembelajaran yang saya ikuti tidak memberikan nilai tambah bagi saya\",\"Saya merasa bahwa materi pembelajaran tidak relevan dengan pekerjaan saya\",\"Saya merasa tidak ada kebijakan yang mendukung proses implementasi materi pembelajaran di lingkungan kerja\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(319, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap unit kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(320, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap pengetahuan teoritis atau konsep Anda', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(321, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan produktivitas', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(322, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap perbaikan kualitas hasil kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(323, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan kepuasan pelanggan', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(324, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap penguatan hubungan antara rekan-rekan kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(325, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah saat ini Anda sedang bertugas yang berkaitan dengan pelatihan?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(326, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Iya, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(327, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(328, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pelatihan memiliki keterkaitan dengan bidang tugas Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(329, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah Anda sudah melakukan transfer learning hasil pelatihan pada rekan kerja di tempat kerja Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(330, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila sudah transfer learning, bagaimana cara Anda melakukan transfer learning?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(331, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya memahami bahwa sumber daya yang diperlukan untuk implementasi materi pembelajaran di lingkungan kerja tersedia secara memadai', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(332, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam menunjang pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(333, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam keberhasilan pelaksanaan pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(334, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam penyelesaian pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(335, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya berkomitmen untuk implementasi materi pelatihan di lingkungan kerja saat ini', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(336, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Bila sebagian besar jawaban Anda untuk pertanyaan nomor 1 sd 6 adalah Cukup, Kurang, Sangat Kurang, maka alasannya adalah (bisa pilih lebih dari satu jawaban)', 'checkbox', '[\"Saya tidak mempunyai pengetahuan dan keterampilan yang memadai\",\"Saya tidak punya rencana implementasi materi pembelajaran secara jelas dan terukur\",\"Saya mempunyai prioritas pekerjaan lain yang tidak sesuai dengan materi pembelajaran\",\"Saya tidak mempunyai dukungan sumber daya yang memadai untuk implementasi materi pembelajaran\",\"Pembelajaran yang saya ikuti tidak memberikan nilai tambah bagi saya\",\"Saya merasa bahwa materi pembelajaran tidak relevan dengan pekerjaan saya\",\"Saya merasa tidak ada kebijakan yang mendukung proses implementasi materi pembelajaran di lingkungan kerja\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(337, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap unit kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(338, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap pengetahuan teoritis atau konsep Anda', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(339, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan produktivitas', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(340, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap perbaikan kualitas hasil kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(341, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan kepuasan pelanggan', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(342, 'Bidang Pengembangan Kompetensi Teknis Umum', 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap penguatan hubungan antara rekan-rekan kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:07:09', '2026-08-29 01:07:09'),
(343, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pendidikan terakhir (saat pelatihan)', 'dropdown', '[\"SD\\/SMP\",\"SMA\\/SMK\",\"D3\",\"S1\\/D4\",\"S2\\/S3\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(344, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pendidikan terakhir (saat ini)', 'dropdown', '[\"SD\\/SMP\",\"SMA\\/SMK\",\"D3\",\"S1\\/D4\",\"S2\\/S3\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(345, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pangkat golongan (saat pelatihan)', 'dropdown', '[\"I\\/a\",\"II\\/a\",\"II\\/b\",\"II\\/c\",\"II\\/d\",\"III\\/a\",\"III\\/b\",\"III\\/c\",\"III\\/d\",\"IV\\/a\",\"IV\\/b\",\"IV\\/b\",\"IV\\/c\",\"IV\\/d\",\"IV\\/e\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(346, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pangkat golongan (saat ini)', 'dropdown', '[\"I\\/a\",\"II\\/a\",\"II\\/b\",\"II\\/c\",\"II\\/d\",\"III\\/a\",\"III\\/b\",\"III\\/c\",\"III\\/d\",\"IV\\/a\",\"IV\\/b\",\"IV\\/b\",\"IV\\/c\",\"IV\\/d\",\"IV\\/e\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(347, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Jabatan (saat pelatihan)', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(348, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Jabatan (saat ini)', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(349, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Unit kerja (saat pelatihan)', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(350, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Unit kerja (saat ini)', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(351, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Perangkat daerah (saat pelatihan)', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(352, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Perangkat daerah (saat ini)', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(353, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah saat ini Anda sedang bertugas yang berkaitan dengan pelatihan?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(354, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Iya, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(355, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(356, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pelatihan memiliki keterkaitan dengan bidang tugas Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(357, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah Anda sudah melakukan transfer learning hasil pelatihan pada rekan kerja di tempat kerja Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(358, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila sudah transfer learning, bagaimana cara Anda melakukan transfer learning?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(359, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya memahami bahwa sumber daya yang diperlukan untuk implementasi materi pembelajaran di lingkungan kerja tersedia secara memadai', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(360, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam menunjang pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(361, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam keberhasilan pelaksanaan pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(362, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam penyelesaian pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(363, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Saya berkomitmen untuk implementasi materi pelatihan di lingkungan kerja saat ini', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(364, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Bila sebagian besar jawaban Anda untuk pertanyaan nomor 1 sd 6 adalah Cukup, Kurang, Sangat Kurang, maka alasannya adalah (bisa pilih lebih dari satu jawaban)', 'checkbox', '[\"Saya tidak mempunyai pengetahuan dan keterampilan yang memadai\",\"Saya tidak punya rencana implementasi materi pembelajaran secara jelas dan terukur\",\"Saya mempunyai prioritas pekerjaan lain yang tidak sesuai dengan materi pembelajaran\",\"Saya tidak mempunyai dukungan sumber daya yang memadai untuk implementasi materi pembelajaran\",\"Pembelajaran yang saya ikuti tidak memberikan nilai tambah bagi saya\",\"Saya merasa bahwa materi pembelajaran tidak relevan dengan pekerjaan saya\",\"Saya merasa tidak ada kebijakan yang mendukung proses implementasi materi pembelajaran di lingkungan kerja\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(365, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap unit kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(366, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap pengetahuan teoritis atau konsep Anda', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(367, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan produktivitas', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(368, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap perbaikan kualitas hasil kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(369, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan kepuasan pelanggan', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(370, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap penguatan hubungan antara rekan-rekan kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(371, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Nama Anda', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(372, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'NIP (ASN) / NIK (Non ASN) Anda', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(373, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Jabatan Anda', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(374, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Unit Kerja Anda', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(375, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Perangkat Daerah Anda', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(376, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'Nama Anda', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(377, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'NIP (ASN) / NIK (Non ASN) Anda', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(378, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'Jabatan Anda', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(379, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'Unit Kerja Anda', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(380, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Data Diri Alumni', 'Perangkat Daerah Anda', 'text', NULL, '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(381, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah saat ini Anda sedang bertugas yang berkaitan dengan pelatihan?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(382, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Iya, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(383, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(384, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pelatihan memiliki keterkaitan dengan bidang tugas Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(385, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah Anda sudah melakukan transfer learning hasil pelatihan pada rekan kerja di tempat kerja Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(386, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila sudah transfer learning, bagaimana cara Anda melakukan transfer learning?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(387, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya memahami bahwa sumber daya yang diperlukan untuk implementasi materi pembelajaran di lingkungan kerja tersedia secara memadai', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(388, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam menunjang pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(389, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam keberhasilan pelaksanaan pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(390, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam penyelesaian pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(391, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Saya berkomitmen untuk implementasi materi pelatihan di lingkungan kerja saat ini', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(392, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Bila sebagian besar jawaban Anda untuk pertanyaan nomor 1 sd 6 adalah Cukup, Kurang, Sangat Kurang, maka alasannya adalah (bisa pilih lebih dari satu jawaban)', 'checkbox', '[\"Saya tidak mempunyai pengetahuan dan keterampilan yang memadai\",\"Saya tidak punya rencana implementasi materi pembelajaran secara jelas dan terukur\",\"Saya mempunyai prioritas pekerjaan lain yang tidak sesuai dengan materi pembelajaran\",\"Saya tidak mempunyai dukungan sumber daya yang memadai untuk implementasi materi pembelajaran\",\"Pembelajaran yang saya ikuti tidak memberikan nilai tambah bagi saya\",\"Saya merasa bahwa materi pembelajaran tidak relevan dengan pekerjaan saya\",\"Saya merasa tidak ada kebijakan yang mendukung proses implementasi materi pembelajaran di lingkungan kerja\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(393, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap unit kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(394, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap pengetahuan teoritis atau konsep Anda', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(395, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan produktivitas', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(396, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap perbaikan kualitas hasil kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(397, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan kepuasan pelanggan', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(398, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap penguatan hubungan antara rekan-rekan kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(399, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah saat ini Anda sedang bertugas yang berkaitan dengan pelatihan?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(400, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Iya, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(401, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pengetahuan yang diperoleh membantu Anda dalam menjalankan tugas?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(402, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila jawaban nomor 1 adalah Tidak, apakah pelatihan memiliki keterkaitan dengan bidang tugas Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(403, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Apakah Anda sudah melakukan transfer learning hasil pelatihan pada rekan kerja di tempat kerja Anda?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(404, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Penempatan Tugas dan Transfer Learning', 'Bila sudah transfer learning, bagaimana cara Anda melakukan transfer learning?', 'dropdown', '[\"YA\",\"TIDAK\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(405, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya memahami bahwa sumber daya yang diperlukan untuk implementasi materi pembelajaran di lingkungan kerja tersedia secara memadai', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(406, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam menunjang pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(407, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam keberhasilan pelaksanaan pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(408, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya yakin bahwa materi pelatihan ini sangat bermanfaat dalam penyelesaian pekerjaan saya', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(409, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Saya berkomitmen untuk implementasi materi pelatihan di lingkungan kerja saat ini', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(410, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Bila sebagian besar jawaban Anda untuk pertanyaan nomor 1 sd 6 adalah Cukup, Kurang, Sangat Kurang, maka alasannya adalah (bisa pilih lebih dari satu jawaban)', 'checkbox', '[\"Saya tidak mempunyai pengetahuan dan keterampilan yang memadai\",\"Saya tidak punya rencana implementasi materi pembelajaran secara jelas dan terukur\",\"Saya mempunyai prioritas pekerjaan lain yang tidak sesuai dengan materi pembelajaran\",\"Saya tidak mempunyai dukungan sumber daya yang memadai untuk implementasi materi pembelajaran\",\"Pembelajaran yang saya ikuti tidak memberikan nilai tambah bagi saya\",\"Saya merasa bahwa materi pembelajaran tidak relevan dengan pekerjaan saya\",\"Saya merasa tidak ada kebijakan yang mendukung proses implementasi materi pembelajaran di lingkungan kerja\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(411, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap unit kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(412, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap pengetahuan teoritis atau konsep Anda', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(413, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan produktivitas', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(414, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap perbaikan kualitas hasil kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(415, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan kepuasan pelanggan', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(416, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Bidang Pengembangan Kompetensi Teknis Inti', 'PKTI/PKTU', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap penguatan hubungan antara rekan-rekan kerja', 'dropdown', '[\"Sangat Kurang\",\"Kurang\",\"Cukup\",\"Baik\",\"Sangat Baik\"]', '2026-08-29 01:16:24', '2026-08-29 01:16:24'),
(491, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Penyelenggara memiliki sertifikat MOT/TOC', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(492, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Penyelenggara memiliki SP sebagai panitia', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(493, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Ketersediaan pengelola kelas (pengamat dan petugas kelas)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(494, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Lembar biodata peserta', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(495, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Daftar Hadir Peserta dan fasilitator', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(496, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Tanda pengenal peserta', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(497, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Penyiapan Sertifikat Pelatihan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(498, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Instrumen dan format pelatihan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(499, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Administrasi pelatihan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(500, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Peserta', 'klasikal', NULL, 'Kualifikasi peserta sesuai persyaratan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(501, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Peserta', 'klasikal', NULL, 'Jumlah peserta sesuai persyaratan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(502, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Peserta', 'klasikal', NULL, 'Peserta terdaftar di BPSDM Jabar', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(503, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Peserta', 'klasikal', NULL, 'Peserta hadir minimal 85%', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(504, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Peserta', 'klasikal', NULL, 'Peserta aktif mengikuti pelatihan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(505, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Peserta', 'klasikal', NULL, 'Peserta mentaati tata tertib', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(506, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Peserta', 'klasikal', NULL, 'Peserta mengikuti evaluasi', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(507, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Tenaga Kediklatan', 'klasikal', NULL, 'Menjaga etika dalam penyelenggaraan pelatihan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(508, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Tenaga Kediklatan', 'klasikal', NULL, 'Berkoordinasi dengan fasilitator', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(509, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Tenaga Kediklatan', 'klasikal', NULL, 'Menyampaikan panduan pelatihan kepada peserta', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(510, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Tenaga Kediklatan', 'klasikal', NULL, 'Menjaga ketepatan waktu', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(511, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Tenaga Kediklatan', 'klasikal', NULL, 'Memiliki sertifikat TOT atau Workshop', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(512, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Tenaga Kediklatan', 'klasikal', NULL, 'Terdaftar di BPSDM Jabar', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(513, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Tenaga Kediklatan', 'klasikal', NULL, 'Mengarsipkan dokumen pelatihan untuk laporan pelaksanaan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(514, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'klasikal', NULL, 'Sarana: Kursi dan meja belajar', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(515, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'klasikal', NULL, 'Sarana: Proyektor dan layar', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(516, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'klasikal', NULL, 'Sarana: Jaringan internet (LAN/WAN)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(517, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'klasikal', NULL, 'Sarana: Pengeras Suara', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(518, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'klasikal', NULL, 'Sarana: Perlengkapan P3K', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(519, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'klasikal', NULL, 'Prasarana: Ruang Kelas', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(520, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'klasikal', NULL, 'Prasarana: Ruang Ibadah', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(521, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Penyelenggara', 'full learning', NULL, 'Penyelenggara memiliki Surat Perintah (SP) / SK sebagai panitia pelatihan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(522, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Penyelenggara', 'full learning', NULL, 'Pengelola Kelas yang memiliki sertifikat kompetensi (MOT/TOC/Workshop sejenis)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(523, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Penyelenggara', 'full learning', NULL, 'Penyelenggara memiliki kompetensi IT minimal', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(524, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Penyelenggara', 'full learning', NULL, 'Tersedia Administrator/Host yang kompeten mengelola jalannya kelas virtual (admit peserta, mute/unmute, share screen)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(525, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Penyelenggara', 'full learning', NULL, 'Tersedia petugas yang memantau dan mendokumentasikan kehadiran peserta dan fasilitator di setiap sesi.', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(526, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Penyelenggara', 'full learning', NULL, 'Tersedia penyelenggara yang menyiapkan, melaksanakan, dan mengolah hasil evaluasi pelatihan (Pre-test, Post-test, dan Evaluasi Penyelenggaraan).', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(527, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Peserta', 'full learning', NULL, 'Kualifikasi peserta yang hadir sesuai dengan persyaratan pelatihan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(528, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Peserta', 'full learning', NULL, 'Jumlah peserta sesuai dengan kuota yang telah ditetapkan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(529, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Peserta', 'full learning', NULL, 'Peserta telah memiliki akun yang terdaftar dan aktif di dalam Learning Management System (LMS)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(530, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Peserta', 'full learning', NULL, 'Peserta memenuhi syarat kehadiran minimal 85% dari total Jam Pelajaran (JP)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(531, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Peserta', 'full learning', NULL, 'Peserta mematuhi tata tertib kelas daring (misal: mengaktifkan kamera, mute mikrofon saat tidak berbicara, menggunakan virtual background)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(532, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Peserta', 'full learning', NULL, 'Peserta mengikuti dan menyelesaikan seluruh rangkaian evaluasi/penugasan di LMS.', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(533, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Peserta', 'full learning', NULL, 'Peserta aktif berpartisipasi dalam diskusi kelompok atau tanya jawab', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(534, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Tenaga Kediklatan', 'full learning', NULL, 'Fasilitator memiliki sertifikat TOT, sertifikat keahlian, atau kompetensi teknis mengajar yang relevan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(535, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Tenaga Kediklatan', 'full learning', NULL, 'Fasilitator terdaftar dalam database penyelenggara / LMS', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(536, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Tenaga Kediklatan', 'full learning', NULL, 'Hadir dan memulai pembelajaran tepat waktu sesuai jadwal (Agenda)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(537, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Tenaga Kediklatan', 'full learning', NULL, 'Menyampaikan materi dan panduan sesuai dengan Rancang Bangun Pembelajaran Mata Pelatihan (RBPMP) / Kurikulum', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(538, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Tenaga Kediklatan', 'full learning', NULL, 'Menguasai penggunaan fitur-fitur platform daring untuk pembelajaran interaktif (misal: polling, whiteboard, anotasi)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(539, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Tenaga Kediklatan', 'full learning', NULL, 'Menjaga etika, profesionalisme, dan mematuhi kode etik pengajar ASN selama sesi berlangsung', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(540, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Tenaga Kediklatan', 'full learning', NULL, 'Menyediakan kelengkapan bahan ajar yang dapat diakses peserta (Modul, Bahan Tayang/Slide, Kasus/Tugas).', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(541, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'full learning', NULL, 'Learning Management System (LMS) berfungsi dengan baik, stabil, dan dapat diakses oleh seluruh pengguna', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(542, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'full learning', NULL, 'Platform Video Conference (misal: Zoom/Teams) memiliki lisensi dan kapasitas yang memadai untuk seluruh peserta', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(543, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'full learning', NULL, 'Tersedia fitur pendukung diskusi interaktif seperti Breakout Rooms jika diperlukan dalam metode pembelajaran', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(544, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'full learning', NULL, 'Tersedia Virtual Background standar yang sesuai dengan tema pelatihan', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39');
INSERT INTO `evaluation_questions` (`id`, `training_type`, `bidang`, `program_evaluasi`, `training_id`, `category`, `metode`, `sub_category`, `question_text`, `type`, `options`, `created_at`, `updated_at`) VALUES
(545, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'full learning', NULL, 'Jaringan internet yang digunakan oleh panitia dan fasilitator memadai, stabil, dan lancar', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(546, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'full learning', NULL, 'Perangkat keras Broadcasting panitia/host berfungsi optimal (Komputer/Laptop, Kamera dengan pencahayaan baik, Headset/Mikrofon jernih)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(547, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'full learning', NULL, 'Tersedia saluran komunikasi/Helpdesk teknis yang responsif untuk membantu kendala sistem yang dialami peserta atau fasilitator.', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(548, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Penyelenggara', 'blended learning', NULL, 'Penyelenggara memiliki Surat Perintah (SP) / SK sebagai panitia.Keduanya', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(549, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Penyelenggara', 'blended learning', NULL, 'Terdapat Pengelola Kelas yang memiliki sertifikat kompetensi (MOT/TOC/Workshop sejenis) (daring/luring)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(550, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Penyelenggara', 'blended learning', NULL, 'Tersedia Administrator/Host untuk mengelola kelas virtual (admit, mute, breakout room) - Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(551, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Penyelenggara', 'blended learning', NULL, 'Tersedia Pengamat Akademik dan Petugas Kelas yang standby di ruangan -Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(552, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Penyelenggara', 'blended learning', NULL, 'Menyediakan kelengkapan administrasi peserta dikelola dengan baik (Daftar Hadir online/offline, Biodata, Tanda Pengenal/Name Tag) - Daring dan Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(553, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Penyelenggara', 'blended learning', NULL, 'Tersedia Panitia memfasilitasi pelaksanaan evaluasi (Pre-test, Post-test, dan Evaluasi Penyelenggaraan) secara tersistem', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(554, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Peserta', 'blended learning', NULL, 'Kualifikasi peserta sesuai dengan persyaratan pelatihan - Luring dan Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(555, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Peserta', 'blended learning', NULL, 'Peserta memiliki akun yang terdaftar di BPSDM / LMS penyelenggara - Luring dan daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(556, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Peserta', 'blended learning', NULL, 'Memenuhi syarat kehadiran minimal 85% dari total Jam Pelajaran (JP) keseluruhan - Luring dan Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(557, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Peserta', 'blended learning', NULL, 'Peserta mentaati tata tertib Daring (kamera aktif, mute saat tidak bicara, virtual background)', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(558, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Peserta', 'blended learning', NULL, 'Peserta mentaati tata tertib Luring (pakaian rapi sesuai ketentuan, tepat waktu masuk kelas) _Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(559, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Peserta', 'blended learning', NULL, 'Peserta aktif mengikuti pembelajaran (diskusi, tanya jawab, kerja kelompok) - Luring dan Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(560, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Peserta', 'blended learning', NULL, 'Menyelesaikan seluruh penugasan mandiri maupun kelompok (Tugas baca, makalah, dll).', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(561, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Tenaga Kediklatan', 'blended learning', NULL, 'Memiliki sertifikat TOT, sertifikat keahlian, atau kompetensi teknis mengajar - Luring dan Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(562, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Tenaga Kediklatan', 'blended learning', NULL, 'Mengunggah dan menyediakan bahan ajar secara lengkap di LMS (Modul, Slide, Kasus) - Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(563, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Tenaga Kediklatan', 'blended learning', NULL, 'Hadir tepat waktu sesuai jadwal (Agenda) yang telah ditetapkan - Daring dan Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(564, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Tenaga Kediklatan', 'blended learning', NULL, 'Menyampaikan materi sesuai Rancang Bangun Pembelajaran Mata Pelatihan (RBPMP) - Luring dan Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(565, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Tenaga Kediklatan', 'blended learning', NULL, 'Menguasai penggunaan platform digital secara interaktif (LMS, polling, whiteboard virtual) - Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(566, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Tenaga Kediklatan', 'blended learning', NULL, 'Mampu mengelola dinamika kelompok, simulasi, atau roleplay secara langsung di kelas - Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(567, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Tenaga Kediklatan', 'blended learning', NULL, 'Mematuhi kode etik pengajar/narasumber dan menjaga etika komunikasi - Luring dan Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(568, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'LMS tersedia, berfungsi lancar, dan mudah diakses peserta maupun fasilitator - Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(569, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Platform Video Conference memiliki lisensi, stabil, dan berkapasitas memadai -Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(570, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Jaringan internet dan peralatan broadcasting panitia (kamera, mic) berfungsi optimal - Daring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(571, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Fasilitas Pembelajaran Luring (Klasikal) Ruang kelas bersih, nyaman, dengan sirkulasi udara / AC yang baik - Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(572, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Kursi dan meja belajar memadai dan diatur sesuai metode pembelajaran - Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(573, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Perangkat audio visual di kelas berfungsi baik (Proyektor, Layar, Pengeras Suara/Mic) - Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(574, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Tersedia alat peraga pendukung (Papan tulis, flipchart, spidol, alat tulis) - Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(575, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Jaringan internet / Wi-Fi di area kelas dan asrama memadai untuk peserta - Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(576, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Ketersediaan prasarana pendukung: Toilet bersih, Ruang Ibadah, dan Ruang Makan.Luring7Ketersediaan Perlengkapan P3K / akses kesehatan dasar di lokasi pelatihan - Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(577, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Ruang Istirahat (Kamar/Wisma/Asrama) bersih dan layak (apabila pelatihan diinapkan) - Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39');

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
(111, 56, 'RINGKASAN_LAPORAN_AKSES_INFORMASI_PUBLIK_TAHUN_2025_190826_a96f7860_signed.pdf', 'documents/cQTF5E3BNZkvmWeWloPNRwDHL38fJ3xmEnamgBPP.pdf', 'pdf', 1671695, 12, '2026-08-30 10:02:47', '2026-08-30 10:02:47'),
(112, 57, 'template_peserta_sertifikasi.xlsx', 'documents/kpemOGZ2WxrWFfoOtBCMj5xrMNYkR49LKmQgjhlI.xlsx', 'xlsx', 9401, 12, '2026-08-30 10:04:01', '2026-08-30 10:04:01'),
(122, 57, 'template_peserta_sertifikasi (2).xlsx', 'documents/HaInOTsSUpQreyjjTUvuuQ9iRu4fFaAD163ecujz.xlsx', 'xlsx', 9401, 2, '2026-08-30 11:10:49', '2026-08-30 11:10:49'),
(125, 66, 'Biodata - Contoh Peserta - 19950332026211005.pdf', 'documents/certification-biodata/d728bcf5-1366-4b9d-a60c-d4ee79bd14c4.pdf', 'pdf', 934671, 12, '2026-08-30 12:21:33', '2026-08-30 12:21:33'),
(126, 56, 'Biodata - Contoh Peserta - 19950332026211005 (2).pdf', 'documents/xAlZn0XdLCU2oHcFcCJfBJn8TTwUlCkofBhjzmlH.pdf', 'pdf', 888150, 1, '2026-08-30 13:29:39', '2026-08-30 13:29:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `file_versions`
--

CREATE TABLE `file_versions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `file_id` bigint(20) UNSIGNED NOT NULL,
  `version_number` int(10) UNSIGNED NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(30) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(52, 8, 'Pealtihan Keuangan Daerah - Angkatan 1', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 2, 0, NULL, '2026-08-30 09:11:51', '2026-08-30 09:32:04'),
(54, NULL, 'Sertifikasi', 'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan', NULL, 12, 1, 'Skl56zepQmVTZbWfS74AXvVatytKgwpyC32EWPNc', '2026-08-30 10:02:29', '2026-08-30 12:38:18'),
(55, NULL, 'Sertifikasi PBPJ Level 1 Kabupaten Bandung', 'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan', 54, 12, 0, NULL, '2026-08-30 10:02:29', '2026-08-30 13:23:22'),
(56, NULL, 'Berita Acara', 'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan', 55, 12, 0, NULL, '2026-08-30 10:02:29', '2026-08-30 13:23:22'),
(57, NULL, 'Data Peserta', 'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan', 55, 12, 0, NULL, '2026-08-30 10:02:29', '2026-08-30 13:23:22'),
(66, NULL, 'Biodata Peserta', 'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan', 55, 12, 0, NULL, '2026-08-30 11:08:51', '2026-08-30 13:23:22'),
(68, 9, 'Pelatihan Pengkajian Kebutuhan Pascabencana - Angkatan I', 'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan', NULL, 12, 0, NULL, '2026-08-30 13:43:37', '2026-08-30 13:43:37');

-- --------------------------------------------------------

--
-- Struktur dari tabel `folder_user_permissions`
--

CREATE TABLE `folder_user_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `folder_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `permission` varchar(20) NOT NULL DEFAULT 'contributor',
  `shared_by` bigint(20) UNSIGNED DEFAULT NULL,
  `seen_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `folder_user_permissions`
--

INSERT INTO `folder_user_permissions` (`id`, `folder_id`, `user_id`, `permission`, `shared_by`, `seen_at`, `created_at`, `updated_at`) VALUES
(2, 54, 1, 'contributor', 12, '2026-08-30 13:28:16', '2026-08-30 13:27:35', '2026-08-30 13:28:16');

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
(73, '2026_08_29_170000_sync_mandiri_l34_questions_to_peer_roles', 9),
(74, '2026_08_29_100000_expand_monitoring_follow_up_workflow', 10),
(75, '2026_08_29_210000_create_training_forum_tables', 11),
(76, '2026_08_30_000000_create_asset_management_tables', 12),
(77, '2026_08_30_120000_add_program_evaluasi_to_trainings_and_questions', 13),
(78, '2026_08_30_130000_replace_semua_program_with_pkti_pktu', 14),
(79, '2026_08_30_140000_create_certification_module_tables', 15),
(80, '2026_08_30_150000_flatten_certification_document_folder', 16),
(81, '2026_08_30_160000_add_public_biodata_to_certifications', 17),
(82, '2026_08_30_170000_create_document_collaboration_tables', 18),
(83, '2026_08_30_171000_add_seen_at_to_folder_permissions', 19);

-- --------------------------------------------------------

--
-- Struktur dari tabel `monitoring_results`
--

CREATE TABLE `monitoring_results` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED NOT NULL,
  `training_stage_id` bigint(20) UNSIGNED DEFAULT NULL,
  `monitoring_date` date DEFAULT NULL,
  `question_id` bigint(20) UNSIGNED NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `answer` enum('ya','tidak') NOT NULL,
  `notes` text DEFAULT NULL,
  `recommendation` text DEFAULT NULL,
  `follow_up_target` varchar(255) DEFAULT NULL,
  `priority` varchar(20) NOT NULL DEFAULT 'sedang',
  `due_date` date DEFAULT NULL,
  `workflow_status` varchar(30) NOT NULL DEFAULT 'open',
  `is_resolved` tinyint(1) NOT NULL DEFAULT 0,
  `resolution_notes` text DEFAULT NULL,
  `evidence_file` varchar(255) DEFAULT NULL,
  `submitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verification_notes` text DEFAULT NULL,
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
(18, 9, 1, '12387126387126438', NULL, 'Simpan Aku aja 22', 'Laki-Laki', 'Pengelola Layanan', 'jdfsgfjsdbfsdjfsdf', 'JAWA BARAT', NULL, 'PARONGPONG', 'CIGUGUR GIRANG', 'PNS', 'approved', '2026-08-30 13:57:09', '2026-08-30 13:57:17', NULL, NULL, NULL);

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

--
-- Dumping data untuk tabel `pengajars`
--

INSERT INTO `pengajars` (`id`, `user_id`, `npwp`, `nama_bank`, `nomor_rekening`, `nama_rekening`, `bidang_keahlian`, `pangkat_golongan`, `instansi`, `cv_path`, `sertifikat_path`, `surat_tugas_path`, `created_at`, `updated_at`) VALUES
(2, 1, '34242352523523', 'asfsdfsdfsd', '5235235235235', 'dasfasfasfafsasf', NULL, NULL, NULL, 'pengajar/kelengkapan/XqeG8xWazeXKytqpmmwnP1eCnk1sy9dHMWnHS0wZ.pdf', 'pengajar/kelengkapan/3c8crnwPcBn4FVNOaU8Rpcj8Jxyag0awXohD8TEz.pdf', 'pengajar/kelengkapan/QbE8qeCFh0B4k54CVSE3091ewZtxhGRK1UTnkxKK.pdf', '2026-08-30 05:42:30', '2026-08-30 05:42:39');

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
  `attendance_close` time DEFAULT NULL,
  `venue_type` varchar(255) NOT NULL DEFAULT 'external',
  `external_place` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `schedules`
--

INSERT INTO `schedules` (`id`, `training_id`, `date`, `start_time`, `end_time`, `activity`, `jp`, `link_zoom`, `pic`, `pengajar_id`, `created_at`, `updated_at`, `attendance_open`, `attendance_close`, `venue_type`, `external_place`) VALUES
(9, 9, '2026-08-30', '08:00:00', '09:00:00', 'Matei I untuk kegiatan itu', 4, NULL, 'Rizky Adia Mukti', 11, '2026-08-30 13:44:29', '2026-08-30 14:14:08', '07:30:00', '22:00:00', 'internal', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `trainings`
--

CREATE TABLE `trainings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `bidang` varchar(255) NOT NULL,
  `program_evaluasi` varchar(30) NOT NULL DEFAULT 'PKTI/PKTU',
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

INSERT INTO `trainings` (`id`, `created_by`, `bidang`, `program_evaluasi`, `nama_pelatihan`, `invitation_code`, `link_lms`, `model`, `metode`, `lokasi`, `kerjasama`, `anggaran`, `angkatan`, `jumlah_peserta`, `jp`, `tgl_mulai`, `tgl_selesai`, `tgl_mulai_klasikal`, `tgl_selesai_klasikal`, `created_at`, `updated_at`) VALUES
(8, 2, 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', 'Pealtihan Keuangan Daerah', 'MQFO9Q', NULL, 'standar', 'klasikal', 'Gedung kelas lantai 2', NULL, NULL, '1', 1, 24, '2026-08-27', '2026-08-28', NULL, NULL, '2026-08-30 09:11:51', '2026-08-30 09:32:04'),
(9, 12, 'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan', 'PKTI/PKTU', 'Pelatihan Pengkajian Kebutuhan Pascabencana', '20U5QG', NULL, 'standar', 'klasikal', 'Zoom', NULL, NULL, 'I', 30, 29, '2026-08-30', '2026-09-01', NULL, NULL, '2026-08-30 13:43:37', '2026-08-30 13:43:37');

-- --------------------------------------------------------

--
-- Struktur dari tabel `training_forum_reads`
--

CREATE TABLE `training_forum_reads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `last_read_message_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `training_messages`
--

CREATE TABLE `training_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(2, NULL, NULL, 'Super Administrator', 'superadmin@bpsdm.go.id', '19450817000000', '6281234567890', NULL, 'superadmin', NULL, '$2y$12$82TUizUKE.owZ2/L0KDDg.7e.UydaeeVnpdqKnjkcqgV2KRqkKuym', NULL, '2026-08-28 06:19:21', '2026-08-30 14:16:38', 'Laki-Laki', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PNS'),
(4, '118339399391382672190', 'https://lh3.googleusercontent.com/a/ACg8ocJWem1Q3SnD_OF1CZI77YKZA_yxmXI7nkEf8tHs-xnvfPkYNg=s96-c', 'simpanakuaja delapan', 'simpanakuajadelapan@gmail.com', NULL, NULL, NULL, 'participant', NULL, '$2y$12$J4lpZbI2DwCPMgmoz.1dzekWiWnRaPI3L62Q9aECyZT6zLBhggQzy', NULL, '2026-08-28 12:02:38', '2026-08-28 12:02:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, NULL, NULL, 'Ali Ridwan', 'bidangpktu@bpsdm.go.id', NULL, '08123456789', NULL, 'admin_bidang', 'Bidang Pengembangan Kompetensi Teknis Umum', '$2y$12$YPxZ1PjL0nlyZ5xYpyDlpO/QybSb.V1hYbezpk8WV1dARs/x7vtsi', NULL, '2026-08-28 12:49:55', '2026-08-28 13:30:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, '115482024455871232654', 'https://lh3.googleusercontent.com/a/ACg8ocJv9FWX3pw175ExmRwBHW53DHh-_9dp64IljqBNAxRCsDFYdPo=s96-c', 'Sem Syamsidin', 'semsyamsidin.sem@gmail.com', NULL, NULL, NULL, 'participant', NULL, '$2y$12$mqKZXhhesDekZhQWFAcyruw1EL.3KL7DcOUQFWZz19dLLIlY4W4y6', NULL, '2026-08-28 13:01:31', '2026-08-28 13:01:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, NULL, NULL, 'Ghani', 'aset@bpsdm.go.id', NULL, '08123456789', NULL, 'admin_aset', 'Pengelola Aset', '$2y$12$1/1IVk5j2D/c5zf527T2a.RPbGf9Bu9e1AWOkVSsIcgNZ1dxS6K9i', NULL, '2026-08-29 14:38:46', '2026-08-29 14:38:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, '107913421320469114225', 'https://lh3.googleusercontent.com/a/ACg8ocLwmh1zFGix6WeyXU2zAzUMuDTawdX04V6cZhIgBrCbA9WmRA=s96-c', 'Simpan Aku', 'simpanakuaja@gmail.com', '34554834689342342', '0988653845345', NULL, 'participant', NULL, '$2y$12$QV8zTZcMbwc/SxFgVzTYeuWDWYvhzYQnQ.3jcCbZQzmEYQJ.K5A06', NULL, '2026-08-30 05:13:44', '2026-08-30 05:29:27', 'Perempuan', 'GURU AHLI PERTAMA', 'Bpsdm Jabar', 'JAWA BARAT', 'KOTA BANDUNG', 'ARCAMANIK', 'CISARANTEN KULON', -6.9338798, 107.6823923, 'PPPK'),
(12, NULL, NULL, 'Rizky Adia Mukti', 'skpk@bpsdm.go.id', NULL, '6281382830814', NULL, 'admin_bidang', 'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan', '$2y$12$kxvFfhIPB4OljwYOXBTOheha3eWnpcmLMwY/skCJxZDnmok5upGcO', NULL, '2026-08-30 09:34:20', '2026-08-30 09:34:20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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
-- Indeks untuk tabel `agendas`
--
ALTER TABLE `agendas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agendas_created_by_foreign` (`created_by`);

--
-- Indeks untuk tabel `agenda_schedules`
--
ALTER TABLE `agenda_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agenda_schedules_agenda_id_foreign` (`agenda_id`);

--
-- Indeks untuk tabel `alumni_profiles`
--
ALTER TABLE `alumni_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alumni_profiles_participant_id_foreign` (`participant_id`),
  ADD KEY `alumni_profiles_training_id_foreign` (`training_id`);

--
-- Indeks untuk tabel `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assets_created_by_foreign` (`created_by`);

--
-- Indeks untuk tabel `asset_bookings`
--
ALTER TABLE `asset_bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `asset_bookings_asset_id_bookable_type_bookable_id_unique` (`asset_id`,`bookable_type`,`bookable_id`),
  ADD KEY `asset_bookings_created_by_foreign` (`created_by`),
  ADD KEY `asset_bookings_asset_id_starts_at_ends_at_index` (`asset_id`,`starts_at`,`ends_at`);

--
-- Indeks untuk tabel `asset_images`
--
ALTER TABLE `asset_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asset_images_asset_id_foreign` (`asset_id`);

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
-- Indeks untuk tabel `certification_events`
--
ALTER TABLE `certification_events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certification_events_public_token_unique` (`public_token`),
  ADD KEY `certification_events_certification_type_id_foreign` (`certification_type_id`),
  ADD KEY `certification_events_folder_id_foreign` (`folder_id`),
  ADD KEY `certification_events_minutes_file_id_foreign` (`minutes_file_id`),
  ADD KEY `certification_events_created_by_foreign` (`created_by`);

--
-- Indeks untuk tabel `certification_participants`
--
ALTER TABLE `certification_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cert_event_nip_unique` (`certification_event_id`,`nip_nik`),
  ADD UNIQUE KEY `certification_participants_biodata_token_unique` (`biodata_token`),
  ADD KEY `certification_participants_nip_nik_result_index` (`nip_nik`,`result`),
  ADD KEY `certification_participants_biodata_file_id_foreign` (`biodata_file_id`);

--
-- Indeks untuk tabel `certification_types`
--
ALTER TABLE `certification_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certification_types_name_unique` (`name`);

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
  ADD KEY `evaluation_questions_bidang_index` (`bidang`),
  ADD KEY `evaluation_questions_program_evaluasi_index` (`program_evaluasi`);

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
-- Indeks untuk tabel `file_versions`
--
ALTER TABLE `file_versions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `file_versions_file_id_version_number_unique` (`file_id`,`version_number`),
  ADD KEY `file_versions_uploaded_by_foreign` (`uploaded_by`);

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
-- Indeks untuk tabel `folder_user_permissions`
--
ALTER TABLE `folder_user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folder_user_permissions_folder_id_user_id_unique` (`folder_id`,`user_id`),
  ADD KEY `folder_user_permissions_shared_by_foreign` (`shared_by`),
  ADD KEY `folder_user_permissions_user_id_permission_index` (`user_id`,`permission`);

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
  ADD KEY `monitoring_results_training_stage_id_foreign` (`training_stage_id`),
  ADD KEY `monitoring_results_submitted_by_foreign` (`submitted_by`),
  ADD KEY `monitoring_results_verified_by_foreign` (`verified_by`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `trainings_created_by_foreign` (`created_by`),
  ADD KEY `trainings_program_evaluasi_index` (`program_evaluasi`);

--
-- Indeks untuk tabel `training_forum_reads`
--
ALTER TABLE `training_forum_reads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `training_forum_reads_training_id_user_id_unique` (`training_id`,`user_id`),
  ADD KEY `training_forum_reads_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `training_messages`
--
ALTER TABLE `training_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `training_messages_user_id_foreign` (`user_id`),
  ADD KEY `training_messages_training_id_id_index` (`training_id`,`id`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `agendas`
--
ALTER TABLE `agendas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `agenda_schedules`
--
ALTER TABLE `agenda_schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `alumni_profiles`
--
ALTER TABLE `alumni_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `assets`
--
ALTER TABLE `assets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT untuk tabel `asset_bookings`
--
ALTER TABLE `asset_bookings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `asset_images`
--
ALTER TABLE `asset_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT untuk tabel `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `certification_events`
--
ALTER TABLE `certification_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `certification_participants`
--
ALTER TABLE `certification_participants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `certification_types`
--
ALTER TABLE `certification_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `evaluation_forms`
--
ALTER TABLE `evaluation_forms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT untuk tabel `file_versions`
--
ALTER TABLE `file_versions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `folders`
--
ALTER TABLE `folders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT untuk tabel `folder_user_permissions`
--
ALTER TABLE `folder_user_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT untuk tabel `monitoring_results`
--
ALTER TABLE `monitoring_results`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT untuk tabel `monitoring_summaries`
--
ALTER TABLE `monitoring_summaries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `participants`
--
ALTER TABLE `participants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `pengajars`
--
ALTER TABLE `pengajars`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `pengajar_schedule_documents`
--
ALTER TABLE `pengajar_schedule_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `trainings`
--
ALTER TABLE `trainings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `training_forum_reads`
--
ALTER TABLE `training_forum_reads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `training_messages`
--
ALTER TABLE `training_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `training_stages`
--
ALTER TABLE `training_stages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `agendas`
--
ALTER TABLE `agendas`
  ADD CONSTRAINT `agendas_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `agenda_schedules`
--
ALTER TABLE `agenda_schedules`
  ADD CONSTRAINT `agenda_schedules_agenda_id_foreign` FOREIGN KEY (`agenda_id`) REFERENCES `agendas` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `alumni_profiles`
--
ALTER TABLE `alumni_profiles`
  ADD CONSTRAINT `alumni_profiles_participant_id_foreign` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`),
  ADD CONSTRAINT `alumni_profiles_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`);

--
-- Ketidakleluasaan untuk tabel `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `asset_bookings`
--
ALTER TABLE `asset_bookings`
  ADD CONSTRAINT `asset_bookings_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `asset_bookings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `asset_images`
--
ALTER TABLE `asset_images`
  ADD CONSTRAINT `asset_images_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_participant_id_foreign` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendances_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `certification_events`
--
ALTER TABLE `certification_events`
  ADD CONSTRAINT `certification_events_certification_type_id_foreign` FOREIGN KEY (`certification_type_id`) REFERENCES `certification_types` (`id`),
  ADD CONSTRAINT `certification_events_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `certification_events_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `certification_events_minutes_file_id_foreign` FOREIGN KEY (`minutes_file_id`) REFERENCES `files` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `certification_participants`
--
ALTER TABLE `certification_participants`
  ADD CONSTRAINT `certification_participants_biodata_file_id_foreign` FOREIGN KEY (`biodata_file_id`) REFERENCES `files` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `certification_participants_certification_event_id_foreign` FOREIGN KEY (`certification_event_id`) REFERENCES `certification_events` (`id`) ON DELETE CASCADE;

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
-- Ketidakleluasaan untuk tabel `file_versions`
--
ALTER TABLE `file_versions`
  ADD CONSTRAINT `file_versions_file_id_foreign` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `file_versions_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `folders`
--
ALTER TABLE `folders`
  ADD CONSTRAINT `folders_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `folders_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `folders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `folder_user_permissions`
--
ALTER TABLE `folder_user_permissions`
  ADD CONSTRAINT `folder_user_permissions_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `folder_user_permissions_shared_by_foreign` FOREIGN KEY (`shared_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `folder_user_permissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `monitoring_results`
--
ALTER TABLE `monitoring_results`
  ADD CONSTRAINT `monitoring_results_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `evaluation_questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `monitoring_results_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `monitoring_results_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`),
  ADD CONSTRAINT `monitoring_results_training_stage_id_foreign` FOREIGN KEY (`training_stage_id`) REFERENCES `training_stages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `monitoring_results_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
-- Ketidakleluasaan untuk tabel `trainings`
--
ALTER TABLE `trainings`
  ADD CONSTRAINT `trainings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `training_forum_reads`
--
ALTER TABLE `training_forum_reads`
  ADD CONSTRAINT `training_forum_reads_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `training_forum_reads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `training_messages`
--
ALTER TABLE `training_messages`
  ADD CONSTRAINT `training_messages_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `training_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `training_stages`
--
ALTER TABLE `training_stages`
  ADD CONSTRAINT `training_stages_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
