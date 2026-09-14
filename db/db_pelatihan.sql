-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 14 Sep 2026 pada 07.29
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
-- Struktur dari tabel `activity_attendance_answers`
--

CREATE TABLE `activity_attendance_answers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `activity_attendance_response_id` bigint(20) UNSIGNED NOT NULL,
  `activity_attendance_question_id` bigint(20) UNSIGNED NOT NULL,
  `value_text` longtext DEFAULT NULL,
  `value_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`value_json`)),
  `file_path` varchar(255) DEFAULT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `activity_attendance_forms`
--

CREATE TABLE `activity_attendance_forms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `public_token` char(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` text DEFAULT NULL,
  `bidang` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `opens_at` timestamp NULL DEFAULT NULL,
  `closes_at` timestamp NULL DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `confirmation_message` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `activity_attendance_forms`
--

INSERT INTO `activity_attendance_forms` (`id`, `public_token`, `title`, `subtitle`, `bidang`, `status`, `opens_at`, `closes_at`, `location`, `confirmation_message`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '9650538a-0da7-492b-9bce-1d4c198db37d', 'Rapat Persiapan SMM:ISO 2015', 'Semua akan baik baik saja dalam hal ini', 'bidang', 'open', '2026-09-12 11:43:00', '2026-09-14 11:43:00', 'Gedung Kelas Lantai 3', 'teriamkasih sudah mebgisi presensi ini yah ka :)', 2, '2026-09-12 11:43:48', '2026-09-13 11:52:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `activity_attendance_questions`
--

CREATE TABLE `activity_attendance_questions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `activity_attendance_form_id` bigint(20) UNSIGNED NOT NULL,
  `label` text NOT NULL,
  `help_text` text DEFAULT NULL,
  `type` varchar(30) NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `max_file_size_kb` int(10) UNSIGNED DEFAULT NULL,
  `allowed_extensions` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `activity_attendance_questions`
--

INSERT INTO `activity_attendance_questions` (`id`, `activity_attendance_form_id`, `label`, `help_text`, `type`, `options`, `is_required`, `sort_order`, `max_file_size_kb`, `allowed_extensions`, `created_at`, `updated_at`) VALUES
(1, 1, 'Nama Kamu siapa', 'sdadas', 'short_text', NULL, 1, 2, 5120, NULL, '2026-09-12 11:44:39', '2026-09-13 11:51:48'),
(2, 1, 'Mamah siapa', 'sdadas', 'dropdown', '[\"maman karubu\",\"susu budah\",\"asu asu\",\"palanglaraya\"]', 0, 3, 5120, NULL, '2026-09-12 11:45:20', '2026-09-13 11:51:48'),
(3, 1, 'cob attd', 'kudu bener ngisina', 'signature', NULL, 1, 4, 5120, NULL, '2026-09-12 11:45:38', '2026-09-13 11:51:48'),
(8, 1, 'ini paragrapft', NULL, 'long_text', NULL, 1, 5, 5120, NULL, '2026-09-12 12:55:46', '2026-09-13 11:51:48'),
(9, 1, 'ini apa yahdasdas', NULL, 'radio', '[\"maksudnya gimanasdadasd\",\"dsdasdasfasfasfasfaf\",\"fasfasfasf\"]', 0, 7, 5120, NULL, '2026-09-12 12:56:39', '2026-09-13 11:51:48'),
(11, 1, 'shadgajgdas dasjgd jasdainformasi', NULL, 'info', NULL, 0, 1, 5120, NULL, '2026-09-12 12:58:24', '2026-09-13 11:51:48'),
(12, 1, 'apakah ini check', NULL, 'checkbox', '[\"weyuryarar\",\"fafhasfbas fasfas\",\"ffaksjfakj fasf\"]', 0, 6, 5120, NULL, '2026-09-13 11:41:46', '2026-09-13 11:51:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `activity_attendance_responses`
--

CREATE TABLE `activity_attendance_responses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `response_token` char(36) NOT NULL,
  `activity_attendance_form_id` bigint(20) UNSIGNED NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ip_hash` varchar(64) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(17, 12, 'Membuat pelatihan & folder dokumen: Pelatihan Pengkajian Kebutuhan Pascabencana', 'Pelatihan', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-08-30 13:43:37', '2026-08-30 13:43:37'),
(18, 2, 'Menghapus kepesertaan Simpan Aku aja 22 dari pelatihan Pelatihan Pengkajian Kebutuhan Pascabencana beserta seluruh data terkait.', 'Peserta', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-08-31 00:05:34', '2026-08-31 00:05:34'),
(19, 2, 'Menghapus kepesertaan Simpan Aku aja 22 dari pelatihan Pelatihan Pengkajian Kebutuhan Pascabencana beserta seluruh data terkait.', 'Peserta', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 02:06:49', '2026-09-02 02:06:49'),
(20, 2, 'Menghapus kepesertaan Simpan Aku aja 22 dari pelatihan Pealtihan Keuangan Daerah beserta seluruh data terkait.', 'Peserta', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 02:07:01', '2026-09-02 02:07:01'),
(21, 2, 'Menghapus kepesertaan Simpan Aku aja 22 dari pelatihan Pealtihan Keuangan Daerah beserta seluruh data terkait.', 'Peserta', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 02:09:32', '2026-09-02 02:09:32'),
(22, 2, 'Menghapus kepesertaan Simpan Aku aja 22 dari pelatihan Pealtihan Keuangan Daerah beserta seluruh data terkait.', 'Peserta', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 02:32:07', '2026-09-02 02:32:07'),
(23, 2, 'Menghapus kepesertaan Simpan Aku aja 22 dari pelatihan Pelatihan Pengkajian Kebutuhan Pascabencana beserta seluruh data terkait.', 'Peserta', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 05:50:32', '2026-09-02 05:50:32'),
(24, 2, 'Menghapus kepesertaan Simpan Aku aja 22 dari pelatihan Pelatihan Pengkajian Kebutuhan Pascabencana beserta seluruh data terkait.', 'Peserta', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 06:03:48', '2026-09-02 06:03:48'),
(25, 2, 'Mengunggah kelengkapan penyelenggara SEesrersreser.pdf untuk pelatihan Pelatihan Pengkajian Kebutuhan Pascabencana', 'Dokumen', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 06:48:43', '2026-09-02 06:48:43'),
(26, 2, 'Membuat pelatihan & folder dokumen: Pelatihan Contoh Kedalam Inti', 'Pelatihan', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 12:13:56', '2026-09-05 12:13:56'),
(27, 2, 'Menghapus pelatihan Pelatihan Contoh Kedalam Inti beserta peserta, evaluasi, monitoring, jadwal, forum, dan seluruh dokumen terkait.', 'Pelatihan', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 12:57:16', '2026-09-05 12:57:16'),
(28, 2, 'Membuat pelatihan & folder dokumen: Pelatihan Contoh Kedalam Umum', 'Pelatihan', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 12:57:53', '2026-09-05 12:57:53'),
(29, 2, 'Membuat pelatihan & folder dokumen: PELATIHAN LATSAR', 'Pelatihan', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-07 04:30:12', '2026-09-07 04:30:12'),
(30, 2, 'Menghapus pelatihan Pelatihan Pengkajian Kebutuhan Pascabencana beserta peserta, evaluasi, monitoring, jadwal, forum, dan seluruh dokumen terkait.', 'Pelatihan', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-12 06:10:34', '2026-09-12 06:10:34'),
(31, 2, 'Menghapus pelatihan Pelatihan Contoh Kedalam Umum beserta peserta, evaluasi, monitoring, jadwal, forum, dan seluruh dokumen terkait.', 'Pelatihan', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-12 11:05:06', '2026-09-12 11:05:06'),
(32, 2, 'Menghapus pelatihan Pealtihan Keuangan Daerah beserta peserta, evaluasi, monitoring, jadwal, forum, dan seluruh dokumen terkait.', 'Pelatihan', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-12 11:05:10', '2026-09-12 11:05:10'),
(33, 2, 'Menghapus pelatihan PELATIHAN LATSAR beserta peserta, evaluasi, monitoring, jadwal, forum, dan seluruh dokumen terkait.', 'Pelatihan', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-12 11:05:14', '2026-09-12 11:05:14'),
(34, 5, 'Membuat pelatihan & folder dokumen: Pelatihan Contoh Kedalam Umum', 'Pelatihan', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-14 01:27:04', '2026-09-14 01:27:04'),
(35, 5, 'Menghapus kepesertaan Contoh Nama Peserta dari pelatihan Pelatihan Contoh Kedalam Umum beserta seluruh data terkait.', 'Peserta', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-14 04:47:27', '2026-09-14 04:47:27');

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

--
-- Dumping data untuk tabel `agendas`
--

INSERT INTO `agendas` (`id`, `scope`, `agenda_type`, `name`, `description`, `bidang`, `is_public`, `created_by`, `created_at`, `updated_at`) VALUES
(12, 'internal', 'bidang', 'Super Administrator', 'vasvasvasv', 'Bidang Pengembangan Kompetensi Teknis Umum', 0, 5, '2026-09-04 06:22:10', '2026-09-04 06:22:10');

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

--
-- Dumping data untuk tabel `agenda_schedules`
--

INSERT INTO `agenda_schedules` (`id`, `agenda_id`, `title`, `starts_at`, `ends_at`, `external_place`, `zoom_link`, `participants_info`, `notes`, `created_at`, `updated_at`) VALUES
(11, 12, 'Super Administrator', '2026-09-04 08:00:00', '2026-09-04 09:00:00', NULL, NULL, 'asfasfasfafasfasfasffass fasf as', 'vasvasvasv', '2026-09-04 06:22:10', '2026-09-04 06:22:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ai_generations`
--

CREATE TABLE `ai_generations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `feature` varchar(80) NOT NULL,
  `model` varchar(120) DEFAULT NULL,
  `source_hash` varchar(64) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'processing',
  `input_summary` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`input_summary`)),
  `generated_content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`generated_content`)),
  `error_message` text DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
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
  `is_rentable` tinyint(1) NOT NULL DEFAULT 0,
  `hourly_rate` decimal(12,2) DEFAULT NULL,
  `rental_open_time` time DEFAULT NULL,
  `rental_close_time` time DEFAULT NULL,
  `rental_min_hours` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `rental_max_hours` tinyint(3) UNSIGNED NOT NULL DEFAULT 8,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `assets`
--

INSERT INTO `assets` (`id`, `name`, `type`, `facilities`, `location`, `capacity`, `description`, `is_active`, `is_public`, `is_rentable`, `hourly_rate`, `rental_open_time`, `rental_close_time`, `rental_min_hours`, `rental_max_hours`, `created_by`, `created_at`, `updated_at`) VALUES
(11, 'RUANG MAKAN PESERTA', 'ruangan', 'Meja Makan, Kursi Makan, Toilet, live Musik', 'Gedung Kelas Lantai 1', 200, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 07:45:50', '2026-08-31 13:23:49'),
(12, 'LABOLATORIUM BAHASA', 'ruangan', 'Komputer, AC, Infocus, Soundsystem, Headphone', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 07:46:52', '2026-08-31 13:24:01'),
(13, 'RUANG BALLROOM', 'ruangan', 'Kursi, Meja, AC, LED, Soundsystem, Infocus', 'Belakang Amphiteater', 50, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 07:47:41', '2026-08-31 13:23:37'),
(14, 'RUANG RAPAT', 'ruangan', 'AC, Soundsystem, Proyektor, Meja, Kursi', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 07:48:54', '2026-08-31 13:24:13'),
(15, 'RUANG RAPAT BIDANG SKPK', 'ruangan', 'Kursi, TV, AC, Meja', 'Gedung Kantor Lantai 2', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 07:49:38', '2026-08-31 13:24:34'),
(16, 'RUANG KELAS 3-X', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 07:50:36', '2026-08-30 07:50:36'),
(17, 'RUANG KELAS 3-IX', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 07:51:20', '2026-08-30 07:51:20'),
(18, 'RUANG KELAS 3-VIII', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 07:51:58', '2026-08-30 07:51:58'),
(19, 'RUANG KELAS 3-VII', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 07:52:27', '2026-08-30 07:52:27'),
(20, 'RUANG KELAS 3-VI', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 07:53:04', '2026-08-30 07:53:04'),
(21, 'RUANG KELAS 3-V', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 07:53:35', '2026-08-30 07:53:35'),
(22, 'RUANG KELAS 3-IV', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 07:54:06', '2026-08-30 07:54:06'),
(23, 'RUANG KELAS 3-III', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 07:54:39', '2026-08-30 07:54:39'),
(24, 'RUANG KELAS 3-II', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 07:55:03', '2026-08-30 07:55:03'),
(25, 'RUANG KELAS 3-I', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 3', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 07:55:29', '2026-08-30 07:55:29'),
(26, 'RUANG RAPAT UTAMA GEDUNG KANTOR', 'ruangan', 'AC, TV, Kursi, Meja, Sofa, Mini Perpustakaan', 'Gedung Kantor Lantai 1', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 07:56:33', '2026-08-30 07:56:33'),
(27, 'AMPHITEATHER B', 'ruangan', 'AC, Infocus, Soundsystem, Kursi, Meja', 'Gedung Bawah Lantai 1', 120, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 07:57:15', '2026-08-30 07:57:15'),
(28, 'AMPHITEATHER A', 'ruangan', 'AC, Infocus, Soundsystem, Kursi, Meja', 'Gedung Bawah Lantai 1', 120, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 07:57:47', '2026-08-30 07:57:47'),
(29, 'RUANG RAPAT LANTAI 4 GEDUNG KANTOR', 'ruangan', 'Ac, Soundsystem, kursi, meja', 'Gedung Kantor Lantai 4', 15, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 07:58:39', '2026-08-30 07:58:39'),
(30, 'RUANG MULTIMEDIA', 'ruangan', 'Ac, Infocus, Kursi Level, Meja, Soundsystem', 'Wisma Block C', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 07:59:19', '2026-08-30 07:59:19'),
(31, 'GUEST HOUSE', 'ruangan', 'AC, TV, Kursi, Sofa, Meja', 'Sebrang Wisma A4', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 08:00:08', '2026-08-30 08:00:08'),
(32, 'LAB. KOMPUTER', 'ruangan', 'Komputer, AC, Infocus, Soundsystem', 'Gedung Kelas Lantai 1', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 08:01:15', '2026-08-30 08:01:15'),
(33, 'RUANG KELAS 2-X', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 08:01:56', '2026-08-30 08:01:56'),
(34, 'RUANG KELAS 2-IX', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 08:02:37', '2026-08-30 08:02:37'),
(35, 'RUANG KELAS 2-VIII', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 08:03:19', '2026-08-30 08:03:19'),
(36, 'RUANG KELAS 2-VII', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 08:03:57', '2026-08-30 08:03:57'),
(37, 'RUANG KELAS 2-VI', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 08:04:25', '2026-08-30 08:04:25'),
(38, 'RUANG KELAS 2-V', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 08:04:50', '2026-08-30 08:04:50'),
(39, 'RUANG KELAS 2-IV', 'ruangan', 'AC, Soundsystem, Infocus, Meja', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 08:05:14', '2026-08-30 08:05:14'),
(40, 'RUANG KELAS 2-III', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 08:05:51', '2026-08-30 08:05:51'),
(41, 'RUANG KELAS 2-II', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 08:06:12', '2026-08-30 08:06:12'),
(42, 'RUANG KELAS 2-I', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Gedung Kelas Lantai 2', 30, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 08:06:42', '2026-08-30 08:06:42'),
(43, 'AULA KUJANG', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi', 'Twin Tower Lantai 3', 500, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 08:07:25', '2026-08-30 08:07:25'),
(44, 'AULA UTAMA', 'ruangan', 'AC, Soundsystem, Infocus, Meja, Kursi, Sofa', 'Gedung Aula Bawah Lantai 2', 500, NULL, 1, 1, 0, NULL, NULL, NULL, 1, 8, 9, '2026-08-30 08:08:13', '2026-08-30 08:08:13'),
(45, 'LAPANGAN TENIS', 'lainnya', 'Lapangan, Net, Tribun', 'Sport Center Bpsdm Jabar', 8, NULL, 1, 1, 1, 50000.00, '06:00:00', '18:00:00', 1, 12, 9, '2026-09-08 13:24:38', '2026-09-11 16:01:41');

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
(77, 11, 'assets/0qNVk9bNLBwYcnZTvLvTUT2FZdWHiyLa9NiNKyr4.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(78, 25, 'assets/12VGTEsycAD1U8mSAiX6W74Wa5EjYDj4ZpfEU8Kh.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(79, 22, 'assets/1QiYEAF7GY06Xte0EHGi7isQiwe5CwEMDfCoYhXq.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(80, 20, 'assets/1RGyqmCxkZNywrwsgOMMbrInYq1mi5ipU0XVYZgp.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(81, 20, 'assets/2ek9XUoOfR0WBJRAaBTTn7tn1t4JcmzWpyoKXwoi.jpg', 1, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(82, 29, 'assets/4WPAsprl2wMVSJbgZmmGszKB1BgLlPBDAcykp4Eu.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(83, 17, 'assets/4np95DEFkMWet2hFdGhyxVLHeToinXiiZGaoHTKp.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(84, 18, 'assets/61wIkOh7DsTXmpw6Ny74X9lrbalPZzABj0rej5HF.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(85, 24, 'assets/8C8R4ZunGZbgFDpawvKw8AUy2hklK5ZHatKiMbyt.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(86, 13, 'assets/ABYzGLyA70ltyHsBgc51wurler7DOVRybDV2FIkM.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(87, 24, 'assets/CO6wnkfzelSMrtpkdvAGP8B0GESFpxDTRnL3b0Rc.jpg', 1, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(88, 21, 'assets/CP1R7FUxxzKySNmdCcTqUfyTXREwWKdNIxH3ZdWg.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(89, 42, 'assets/DZz9bWDtPzWbrwzYzzngRllXLGTfQidEjk0mdivn.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(90, 43, 'assets/EkMnJCyqqvpIXmRRfM7dgp02xRvyXoT5gsUZz4LR.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(91, 16, 'assets/F4MOdqdOUOTu1FnCh8oAcjfwezVWmXakAiVxSWpc.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(92, 11, 'assets/FSa7L6UVrMrNd1Q0YILW8c3oyEpKHmwvrvdKJUs2.jpg', 1, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(93, 25, 'assets/G5xN4HCywUiH6UakFFO7mXSQzL6y3CvhVuJIMXHX.jpg', 1, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(94, 14, 'assets/GHgiL7QysVEEi1DiFvjmQk2QzJ2bOnwRQVxsiZKD.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(95, 16, 'assets/GW85n7uS37hJOw7qJnRc0YGjEJRAOnKn1PQ2oh6U.jpg', 1, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(96, 44, 'assets/JMlKGgA8uB17qpudO1LlncFLFkeYJJ3MM3tMi4uy.png', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(97, 19, 'assets/JWHpqlybGrN2BuFkl5mmNoyecwu25Mh4M4knr12J.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(98, 24, 'assets/LcxbLyiCxrZgiXcKkp6iBG6Kl1mD4eNp6Tw1TrR0.jpg', 2, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(99, 11, 'assets/Lr1TC2LIdkQBGojz5u8boxxRk87Kj1sDqphJMxOV.jpg', 2, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(100, 15, 'assets/MplV1dslLZLLHoOspkkHCdFkDaW7thoYY3lRnU9U.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(101, 12, 'assets/N6nLz73mNBIuMf1Inr74DSbdsNXgbBXZ2Pj84Ybn.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(102, 20, 'assets/N9qkYT3Ifi22B8U9ldXhFRK8gl3x67AqZfMu0W9z.jpg', 2, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(103, 39, 'assets/Nl2U8YY3otSYTf0n9ncqaZ74VVOiwu1PNMfHA1rk.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(104, 18, 'assets/OdgNB4VkJIJ0oHNlyWfhUkv7yw4hGqDSneQtMAdj.jpg', 1, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(105, 30, 'assets/Pu7HhplVBWwKOOsE4gXPtuRl28VwWa2uNVz5LSVY.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(106, 25, 'assets/Q22UvAeMrakhGt8ULLRLNFm3JfNyJQLf0eJnBVFi.jpg', 2, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(107, 18, 'assets/Q6FrgsDWmdbirVSiBRZWjIDNeYsg5n3txQP1s1jc.jpg', 2, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(108, 26, 'assets/QMylcBa3LrSlz4AeTg3ZWaFsqDveQT34GyJTEqSv.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(109, 23, 'assets/R00FIRr0YewFdAeNZDpA2EnauHsLn1CFWltN3G1A.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(110, 21, 'assets/RRyBwnTQW8VcIp1uJ1sERVvBtwzQHHaJzjkVbRM8.jpg', 1, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(111, 17, 'assets/RYmw5oyaK9gB9rP6KrgIaTKFYYRORF459AMrgsJ0.jpg', 1, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(112, 25, 'assets/SDDfD8AQZQyrsoGa6OvF61SL0eDdFor3PsEJa3qq.jpg', 3, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(113, 37, 'assets/UDg07otPa6ynavjz1f6ZJwRSGDTAiQiChmIsWY3X.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(114, 24, 'assets/UMs2htdiY2VBFWbVp63PQPk9FX0IKJwRRBCbzZDO.jpg', 3, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(115, 22, 'assets/XFxEBlLr7atALbcJ6QkzFOsO0LRlNDq0jKCwMKBa.jpg', 1, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(116, 45, 'assets/Y8bEFyuvK5CakT1RV3PbgBq7Zqh3sYak0oEuotDf.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(117, 20, 'assets/YjoSommN1AmGPBmiJV6LuXq7bp1MPi17MIebMKVN.jpg', 3, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(118, 23, 'assets/ZsEBfUB3k31zc4gEquIEMyYELKhqt6f46gLa8Aml.jpg', 1, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(119, 18, 'assets/ZtQ4RPlc7CjY654Lxd2DTjiW5IWDkmoMy0tFEWVY.jpg', 3, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(120, 35, 'assets/Zz911DRfrC2LOEHK59I8oEeNomAbiXcZq8nyuF9g.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(121, 21, 'assets/dwhY55shH5FwN5gvo5JjaLUXQ11HiGUN2kIAb4g1.jpg', 2, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(122, 19, 'assets/eixKPMPnq9X2NRHjHR2pGoTH16ZWZAbjFhvfRPsi.jpg', 1, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(123, 17, 'assets/fpE2YZ1EAYG8S7495QpkGwz5kPG1P3wWn9iAAq7T.jpg', 2, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(124, 16, 'assets/gdnYnuPLDWedFBdJw0GFSRb9KDliqw3RT1w08oid.jpg', 2, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(125, 16, 'assets/glrQOtwBbxYiXINj0YdQBgwPTea6IIKXrVuueSGL.jpg', 3, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(126, 38, 'assets/h7ZOk3DhIIwOBO4Wyf4t0mVoRmO2uQGQZcGpfecT.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(127, 19, 'assets/hnheFLdfYTWW7c9WJ0ifkKA28eCUdtqzU3Ds2RoN.jpg', 2, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(128, 27, 'assets/kTgFY3oYu9DAU6JHMo4yadsr4Gl68JjhhJo0EV6c.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(129, 31, 'assets/mchBieoUp8pPWO6zh2jg4RIrXgEg21kq4KYTr1dt.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(130, 14, 'assets/nHV7JXMwJdpmjsFX3z3P9pbbz3kmJe9HZ3j9ruZe.jpg', 1, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(131, 34, 'assets/oGgGk65uDo2U5SJt74KkG6c51LBsnHbGtxhZjhNS.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(132, 28, 'assets/pU0duvgm5yfp1t4GLvUzrIGTsLAT9emzSUalbAQG.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(133, 12, 'assets/pvdrS1iTgIyTAYad384csV08WcJMuVWQbQgcwfk2.jpg', 1, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(134, 17, 'assets/qaxxWx9jCHWH2QXdwgyxtdZwwSDx0UpI8dLojuo5.jpg', 3, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(135, 23, 'assets/rboPqxM3sGsOrb22H5lZXNis6fOrwul6Raw4FxkF.jpg', 2, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(136, 41, 'assets/sug57E5LI4RxG6AhLGrgLkEmC7xxKJf8SPtPsLo0.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(137, 23, 'assets/tYKCrjFOp0EzOf8odYVIECzWpi2y1GnMKC1y0p0J.jpg', 3, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(138, 36, 'assets/tlSbSeNvGK4Jxk06zles7hQnXMPh49FQU8jB38AV.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(139, 33, 'assets/vTX1tF1Um7NHqX0uJeOrkHylURURBK0WkTrHfiAF.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(140, 21, 'assets/vb6ZSwOv4ZvhTSxQgqECUOktGahMeyFDRC1rTMxA.jpg', 3, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(141, 22, 'assets/viDsxLYBJFtKfXhyrIXy5rimQKfdV31SUNOfNvDX.jpg', 2, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(142, 22, 'assets/vxVlr5IYmGoPqIhiHKsd0mLLfvJga4KkH45Etiwd.jpg', 3, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(143, 19, 'assets/wOKAiM0u6XsndEOSLyG6uTDvLnHkmHjLBiGVPlAP.jpg', 3, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(144, 40, 'assets/xGL8Yok0QA5VbIJbgwbmsydKh6E8CwVFcc8MIICZ.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03'),
(145, 32, 'assets/z3UV0YANjnFd1q5RXjXO2Ft5X60bxg34oUNpoWpc.jpg', 0, '2026-09-12 05:58:03', '2026-09-12 05:58:03');

-- --------------------------------------------------------

--
-- Struktur dari tabel `asset_loan_requests`
--

CREATE TABLE `asset_loan_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `requestable_type` varchar(255) NOT NULL,
  `requestable_id` bigint(20) UNSIGNED NOT NULL,
  `asset_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`asset_ids`)),
  `letter_path` varchar(255) NOT NULL,
  `purpose` text DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `attendee_count` int(10) UNSIGNED DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `review_note` text DEFAULT NULL,
  `submitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `asset_public_reservations`
--

CREATE TABLE `asset_public_reservations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `asset_id` bigint(20) UNSIGNED NOT NULL,
  `public_token` char(36) NOT NULL,
  `booking_code` varchar(30) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `whatsapp` varchar(30) NOT NULL,
  `email` varchar(255) NOT NULL,
  `organization` varchar(255) DEFAULT NULL,
  `rental_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `duration_hours` tinyint(3) UNSIGNED NOT NULL,
  `usage_type` varchar(60) NOT NULL,
  `usage_other` varchar(255) DEFAULT NULL,
  `additional_note` text DEFAULT NULL,
  `hourly_rate` decimal(12,2) NOT NULL,
  `total_amount` decimal(14,2) NOT NULL,
  `rate_breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rate_breakdown`)),
  `status` varchar(40) NOT NULL DEFAULT 'pending_review',
  `rules_accepted_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_due_at` timestamp NULL DEFAULT NULL,
  `payment_proof_path` varchar(255) DEFAULT NULL,
  `payment_uploaded_at` timestamp NULL DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `tracking_events` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tracking_events`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `asset_rental_rates`
--

CREATE TABLE `asset_rental_rates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `asset_id` bigint(20) UNSIGNED NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `hourly_rate` decimal(12,2) NOT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `asset_rental_rates`
--

INSERT INTO `asset_rental_rates` (`id`, `asset_id`, `start_time`, `end_time`, `hourly_rate`, `sort_order`, `created_at`, `updated_at`) VALUES
(3, 45, '06:00:00', '10:00:00', 75000.00, 0, '2026-09-11 15:29:50', '2026-09-11 15:29:50'),
(4, 45, '10:00:00', '16:00:00', 50000.00, 1, '2026-09-11 15:29:50', '2026-09-11 15:29:50'),
(5, 45, '16:00:00', '18:00:00', 75000.00, 2, '2026-09-11 15:29:50', '2026-09-11 15:29:50');

-- --------------------------------------------------------

--
-- Struktur dari tabel `asset_rental_settings`
--

CREATE TABLE `asset_rental_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `manager_whatsapp` varchar(30) DEFAULT NULL,
  `bank_name` varchar(255) NOT NULL DEFAULT 'Bank BJB',
  `bank_account` varchar(255) NOT NULL DEFAULT '0025506995102',
  `bank_account_name` varchar(255) NOT NULL DEFAULT 'BENDAHARA PENERIMAAN BPSDM PROV JBR',
  `payment_deadline_hours` int(10) UNSIGNED NOT NULL DEFAULT 24,
  `public_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `asset_rental_settings`
--

INSERT INTO `asset_rental_settings` (`id`, `manager_whatsapp`, `bank_name`, `bank_account`, `bank_account_name`, `payment_deadline_hours`, `public_note`, `created_at`, `updated_at`) VALUES
(1, '081382830814', 'Bank BJB', '0025506995102', 'BENDAHARA PENERIMAAN BPSDM PROV JBR', 24, NULL, '2026-09-08 13:15:50', '2026-09-08 13:43:06');

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
('integral-cache-12345|127.0.0.1', 'i:1;', 1789226777),
('integral-cache-12345|127.0.0.1:timer', 'i:1789226777;', 1789226777),
('integral-cache-3201125305870003|127.0.0.1', 'i:1;', 1789300490),
('integral-cache-3201125305870003|127.0.0.1:timer', 'i:1789300490;', 1789300490),
('integral-cache-472b07b9fcf2c2451e8781e944bf5f77cd8457c8', 'i:1;', 1789289055),
('integral-cache-472b07b9fcf2c2451e8781e944bf5f77cd8457c8:timer', 'i:1789289055;', 1789289055),
('integral-cache-5c785c036466adea360111aa28563bfd556b5fba', 'i:2;', 1789210419),
('integral-cache-5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1789210419;', 1789210419),
('integral-cache-9e6a55b6b4563e652a23be9d623ca5055c356940', 'i:1;', 1789210684),
('integral-cache-9e6a55b6b4563e652a23be9d623ca5055c356940:timer', 'i:1789210684;', 1789210684),
('integral-cache-da4b9237bacccdf19c0760cab7aec4a8359010b0', 'i:1;', 1789303062),
('integral-cache-da4b9237bacccdf19c0760cab7aec4a8359010b0:timer', 'i:1789303062;', 1789303062),
('integral-cache-nominatim-profile-geocode-last', 'd:1789288885.654393;', 1789289485),
('integral-cache-profile-geocode:3ebe70b599539f449da2ab3730c613ffd8ee505f', 'a:0:{}', 1789375245),
('integral-cache-profile-geocode:4e249487419effe7123e4dd5f4873f095a1267bf', 'a:0:{}', 1789375261),
('integral-cache-profile-geocode:831c085d90fb161acf64fd7f3ac2dc808262caa5', 'a:0:{}', 1789375235),
('integral-cache-profile-geocode:8cea8b05fe19f5d47d2f1e11728872bb230471c1', 'a:1:{i:0;a:5:{s:4:\"name\";s:14:\"Jalan Ciwaruga\";s:12:\"display_name\";s:87:\"Jalan Ciwaruga, Panyairanjompo, Parongpong, Bandung Barat, Jawa Barat, 40559, Indonesia\";s:3:\"lat\";d:-6.8192959;s:3:\"lon\";d:107.5866061;s:4:\"type\";s:8:\"tertiary\";}}', 1789375284);

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
  `biodata_submitted_at` timestamp NULL DEFAULT NULL,
  `certificate_number` varchar(255) DEFAULT NULL,
  `certificate_file_id` bigint(20) UNSIGNED DEFAULT NULL,
  `certification_rating` tinyint(3) UNSIGNED DEFAULT NULL,
  `certification_feedback` text DEFAULT NULL,
  `certificate_submitted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Struktur dari tabel `electronic_signature_actions`
--

CREATE TABLE `electronic_signature_actions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `electronic_signature_document_id` bigint(20) UNSIGNED NOT NULL,
  `electronic_signature_actor_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'waiting',
  `signed_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `electronic_signature_actions`
--

INSERT INTO `electronic_signature_actions` (`id`, `electronic_signature_document_id`, `electronic_signature_actor_id`, `status`, `signed_at`, `error_message`, `created_at`, `updated_at`) VALUES
(21, 21, 19, 'pending', NULL, NULL, '2026-09-13 11:53:39', '2026-09-13 11:53:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `electronic_signature_actors`
--

CREATE TABLE `electronic_signature_actors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `electronic_signature_request_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(20) NOT NULL,
  `sequence` smallint(5) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `electronic_signature_actors`
--

INSERT INTO `electronic_signature_actors` (`id`, `electronic_signature_request_id`, `user_id`, `role`, `sequence`, `created_at`, `updated_at`) VALUES
(19, 19, 18, 'signer', 1, '2026-09-13 11:53:39', '2026-09-13 11:53:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `electronic_signature_attempts`
--

CREATE TABLE `electronic_signature_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `electronic_signature_action_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `successful` tinyint(1) NOT NULL DEFAULT 0,
  `response_code` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `duration_ms` int(10) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `electronic_signature_documents`
--

CREATE TABLE `electronic_signature_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `electronic_signature_request_id` bigint(20) UNSIGNED NOT NULL,
  `participant_certificate_id` bigint(20) UNSIGNED DEFAULT NULL,
  `internship_participant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `external_user_id` varchar(255) DEFAULT NULL,
  `verification_token` char(36) DEFAULT NULL,
  `original_name` varchar(255) NOT NULL,
  `original_path` varchar(255) NOT NULL,
  `current_path` varchar(255) NOT NULL,
  `final_path` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `status` varchar(30) NOT NULL DEFAULT 'waiting',
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `electronic_signature_documents`
--

INSERT INTO `electronic_signature_documents` (`id`, `electronic_signature_request_id`, `participant_certificate_id`, `internship_participant_id`, `external_user_id`, `verification_token`, `original_name`, `original_path`, `current_path`, `final_path`, `file_size`, `status`, `completed_at`, `created_at`, `updated_at`) VALUES
(21, 19, NULL, 1, NULL, '40e9988b-94d6-4b45-bc31-f67088e38249', '123456.pdf', 'internships/certificates/1/generated/1.pdf', 'internships/certificates/1/generated/1.pdf', NULL, 137175, 'waiting', NULL, '2026-09-13 11:53:39', '2026-09-13 11:53:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `electronic_signature_requests`
--

CREATE TABLE `electronic_signature_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `bidang` varchar(255) DEFAULT NULL,
  `source_type` varchar(40) NOT NULL DEFAULT 'other_documents',
  `page_format` varchar(30) NOT NULL DEFAULT 'f4_portrait',
  `training_id` bigint(20) UNSIGNED DEFAULT NULL,
  `external_reference` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'in_progress',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `electronic_signature_requests`
--

INSERT INTO `electronic_signature_requests` (`id`, `uuid`, `title`, `description`, `bidang`, `source_type`, `page_format`, `training_id`, `external_reference`, `status`, `created_by`, `completed_at`, `created_at`, `updated_at`) VALUES
(19, '44e7ec88-11b1-439c-af8e-d862895b741f', 'Sertifikat Magang/PKL - MAGANG DAN PKL BPSDM JABAR TAHUN 2026', 'Penandatanganan 1 sertifikat peserta magang.', 'Sekretariat', 'training_certificates', 'f4_portrait', NULL, 'internship-program:1', 'in_progress', 2, NULL, '2026-09-13 11:53:39', '2026-09-13 11:53:39');

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

-- --------------------------------------------------------

--
-- Struktur dari tabel `evaluation_l1_text_summaries`
--

CREATE TABLE `evaluation_l1_text_summaries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED NOT NULL,
  `conclusion` longtext NOT NULL,
  `follow_up` longtext NOT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(577, 'Semua', NULL, 'PKTI/PKTU', NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Ruang Istirahat (Kamar/Wisma/Asrama) bersih dan layak (apabila pelatihan diinapkan) - Luring', 'ya_tidak', NULL, '2026-08-29 02:44:39', '2026-08-29 02:44:39'),
(1468, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kesesuaian materi pembelajaran dengan harapan/kebutuhan peserta pelatihan', 'slider', NULL, '2026-09-07 10:33:55', '2026-09-07 10:33:55'),
(1469, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kepuasan terhadap manfaat materi pelatihan yang sudah diberikan', 'slider', NULL, '2026-09-07 10:33:55', '2026-09-07 10:33:55'),
(1470, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Peningkatan pengetahuan', 'slider', NULL, '2026-09-07 10:33:55', '2026-09-07 10:33:55'),
(1471, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Sajian Materi dalam pelatihan dalam membantu tugas-tugas peserta', 'slider', NULL, '2026-09-07 10:33:55', '2026-09-07 10:33:55'),
(1472, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Perubahan Sikap/Perilaku', 'slider', NULL, '2026-09-07 10:33:55', '2026-09-07 10:33:55'),
(1473, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kerapian pakaian yang digunakan oleh panitia', 'slider', NULL, '2026-09-07 10:33:55', '2026-09-07 10:33:55'),
(1474, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Pelayanan panitia kepada peserta dengan 3 S (senyum, sapa, salam)', 'slider', NULL, '2026-09-07 10:33:55', '2026-09-07 10:33:55'),
(1475, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kesigapan panitia terhadap kebutuhan peserta', 'slider', NULL, '2026-09-07 10:33:55', '2026-09-07 10:33:55'),
(1476, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Ketersediaan panitia yang kompeten melayani peserta pelatihan', 'slider', NULL, '2026-09-07 10:33:55', '2026-09-07 10:33:55'),
(1477, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Penyelenggaraan pelatihan secara keseluruhan', 'slider', NULL, '2026-09-07 10:33:55', '2026-09-07 10:33:55'),
(1478, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Administrasi Program (undangan, pendaftaran peserta, dll)', 'slider', NULL, '2026-09-07 10:33:55', '2026-09-07 10:33:55'),
(1479, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Kesesuaian soal pre & post test dengan materi yang diajarkan', 'slider', NULL, '2026-09-07 10:33:55', '2026-09-07 10:33:55'),
(1480, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Ketersediaan waktu yang cukup dalam pengerjaan soal pre & post test', 'slider', NULL, '2026-09-07 10:33:55', '2026-09-07 10:33:55'),
(1481, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Ketertiban penyelenggaraan pre & post test', 'slider', NULL, '2026-09-07 10:33:55', '2026-09-07 10:33:55'),
(1482, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Profesionalitas panitia dalam memandu pre & post test', 'slider', NULL, '2026-09-07 10:33:55', '2026-09-07 10:33:55'),
(1483, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Materi / Paparan yang sudah sesuai dengan kebutuhan Bapak / Ibu :', 'text', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1484, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Materi / Paparan yang perlu diperbaiki :', 'text', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1485, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'klasikal', NULL, 'Mohon Saudara berikan komentar untuk perbaikan kinerja kami :', 'text', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1486, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kerapian dan kesopanan pakaian yang dikenakan oleh Pengajar', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1487, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kedisiplinan kehadiran sesuai jadwal', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1488, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan memberikan motivasi kepada peserta diklat', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1489, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan menggunakan media pembelajaran', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1490, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan berkomunikasi dan berinteraksi dengan peserta diklat', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1491, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan menyampaikan konsep / materi', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1492, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan menghubungkan konsep / materi dengan praktek', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1493, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan merespon pertanyaan', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1494, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kualitas bahan ajar dalam membantu proses pembelajaran peserta diklat', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1495, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kesesuaian bahan ajar dengan kurikulum yang digunakan', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1496, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kesesuaian materi pembelajaran dengan keadaan terkini', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1497, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Manfaat materi bagi perkembangan / perbaikan diri di masa yang akan datang', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1498, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Bahan ajar disajikan dalam keadaan baik dan bisa digunakan', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1499, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Metode pembelajaran yang digunakan pengajar memudahkan peserta diklat memahami materi', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1500, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Kemampuan mengelola waktu pembelajaran', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1501, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Sistematika penyampaian materi pembelajaran', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1502, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Menciptakan suasana kelas yang kondusif untuk belajar', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1503, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Memberikan contoh yang membantu memahami konsep yang sulit', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1504, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_narasumber', 'semua', NULL, 'Memberikan umpan balik yang konstruktif', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1505, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Pedoman Penggunaan web\'elearning informatif dan mudah dipahami', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1506, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Website e-learning mudah diakses', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1507, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Kemudahan Fitur yang tersedia', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1508, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Sistematika penyajian materi', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1509, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Tampilan tayangan', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1510, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Tujuan pembelajaran dapat tercapai secara optimal', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1511, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Kualitas bahan ajar', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1512, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Komposisi materi pelatihan', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1513, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Sekuensi materi pelatihan', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1514, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Durasi penyelenggaraan pelatihan', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1515, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'full learning', NULL, 'Catatan / Saran', 'text', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1516, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Persyaratan administrative sesuai ketentuan', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1517, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kecepatan atau responsivitas penyelenggara dalam memberikan layanan', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1518, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Keramahan penyelenggara', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1519, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Sarana dan prasarana (daring) sudah memadai', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1520, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kemudahan mengakses jadwal', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1521, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kedisiplinan penerapan jadwal pelatihan', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1522, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kecukupan waktu tutorial dan praktek', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1523, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Efektifitas pembimbingan dengan distance learning', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1524, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Tujuan pembelajaran dapat tercapai secara optimal', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1525, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Kualitas bahan ajar', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1526, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Komposisi materi pelatihan', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1527, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Sekuensi pelatihan', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1528, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Durasi Penyelenggaraan pelatihan', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1529, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Apa yang paling berkesan bagi anda dalam pelatihan ini', 'slider', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1530, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Apa yang membuat anda kurang/tidak puas dari pelatihan ini', 'text', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1531, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Secara umum penilaian anda terhadap penyelenggaraan', 'text', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1532, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Menurut anda, kedepannya apa yang perlu ditambahkan dalam pelatihan ini?', 'text', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1533, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l1_penyelenggara', 'blended', NULL, 'Catatan / Saran', 'text', NULL, '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1534, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pendidikan terakhir (saat pelatihan)', 'dropdown', '[\"SD\\/SMP\",\"SMA\\/SMK\",\"D3\",\"S1\\/D4\",\"S2\\/S3\"]', '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1535, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKTI/PKTU', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Pendidikan terakhir (saat ini)', 'dropdown', '[\"SD\\/SMP\",\"SMA\\/SMK\",\"D3\",\"S1\\/D4\",\"S2\\/S3\"]', '2026-09-07 10:33:56', '2026-09-07 10:33:56'),
(1608, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Nama Instansi', 'text', NULL, '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1609, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Jabatan Saat Pelatihan', 'text', NULL, '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1610, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Jabatan Saat Ini', 'text', NULL, '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1611, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Jabatan Saat Ini', 'text', NULL, '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1612, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Metode Pelaksanaan', 'dropdown', '[\"Klasikal\",\"Blended Learning\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1613, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu Menunjukan Sikap Perilaku Bela Negara Yakni Taat Pada Hukum Dan Aturan Negara Dalam Menjalankan Tugas Jabatan', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1614, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu Berpikir Kritis, Analitis, Dan Objektif Dalam Menjalankan Tugas Jabatan', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1615, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Dapat Melakukan Praktik Yang Mencerminkan Kesiapsiagaan Fisik Dan Mental Dalam Suatu Kegiatan Yang Melatih Kedisiplinan, Kepemimpinan, Kerjasama, Dan Berinisiatif', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1616, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Memahami Dan Memenuhi Kebutuhan Masyarakat', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1617, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Ramah, Cekatan, Solutif, Dan Dapat Diandalkan', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1618, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Melakukan Perbaikan Tiada Henti', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1619, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Melaksanakan Tugas Dengan Jujur, Bertanggung Jawab, Cermat, Disiplin Dan Berintegritas Tinggi', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1620, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Menggunakan Kekayaan Dan Barang Milik Negara Secara Bertanggung Jawab, Efektif, Dan Efisien', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1621, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Tidak Menyalahgunakan Kewenangan Jabatan', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1622, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Meningkatkan Kompetensi Diri Untuk Menjawab Tantangan Yang Selalu Berubah', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1623, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Membantu Orang Lain Belajar', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1624, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Melaksanakan Tugas Dengan Kualitas Terbaik', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1625, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Menghargai Setiap Orang Apa Pun Latar Belakangnya', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1626, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Suka Menolong Orang Lain', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1627, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Membangun Lingkungan Kerja Yang Kondusif', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1628, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Memegang Teguh Ideologi Pancasila, Undang Undang Dasar Negara Republik Indonesia Tahun 1945, Nkri Serta Pemerintahan Yang Sah', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1629, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Menjaga Nama Baik Sesama Asn, Pimpinan, Instansi, Dan Negara', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1630, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Menjaga Rahasia Jabatan Dan Negara', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1631, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Cepat Menyesuaikan Diri Menghadapi Perubahan', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1632, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Terus Berinovasi Dan Mengembangkan Kreativitas', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1633, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Bertindak Proaktif', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:38', '2026-09-07 10:35:38'),
(1634, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Memberi Kesempatan Kepada Berbagai Pihak Untuk Berkontribusi', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1635, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Terbuka Dalam Bekerja Sama Untukmenghasilkan Nilai Tambah', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1636, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Menggerakkan Pemanfaatan Berbagai Sumber Daya Untuk Tujuan Bersama', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1637, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Menggerakkan Pemanfaatan Berbagai Sumber Daya Untuk Tujuan Bersama', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1638, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Memahami Sistem Merit Dalam Pengelolaan Asn', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1639, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Memiliki Literasi Digital', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1640, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu Memanfaatkan Sarana Digital Dasar Dalam Organisasi', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1641, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Bersifat Kritis Atas Kasus Kecakapan Digital Dasar', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1642, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu Melaksanakan Kebijakan Publik Yang Telah Ditetapkan Oleh Pemerintah Ataupun Pimpinan Instansi Sesuai Dengan Perundangan-Undangan Yang Berlaku', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1643, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu Memberikan Pelayanan Publik Yang Profesional Dan Berkualitas', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1644, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu Mempererat Persatuan Dan Kesatuan Negara Kesatuan Republik Indonesia', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1645, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Terdapat Peningkatan Pengetahuan Dan Keterampilan Yang Bersifat Umum/Administratif', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1646, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Terdapat Peningkatan Pengetahuan Dan Keterampilan Yang Bersifat Spesifik, Substantif Dan/Atau Bidang Yang Diperlukan', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1647, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Memiliki Pengetahuan Dan Keterampilan Jabatan Fungsional Sesuai Dengan Formasi Jabatannya', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1648, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Terdapat Keberlanjutan Aktualisasi Dalam Jangka Panjang', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1649, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Seluruh Capaian Hasil Dari Aktualisasi Dapat Dicapai Dengan Baik', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1650, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Realisasi Hasil Aktualisasi Tercapai Sesuai Dengan Rencana Aktualisasi', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1651, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Faktor Pendukung Aktualisasi', 'Terdapat Dukungan Dari Mentor', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1652, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Faktor Pendukung Aktualisasi', 'Terdapat Dukungan Pemangku Kepentingan', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1653, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Faktor Pendukung Aktualisasi', 'Terdapat Dukungan Dari Anggota Tim', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1654, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Faktor Pendukung Aktualisasi', 'Terdapat Dukungan Sarana Dan Prasarana', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1655, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Faktor Pendukung Aktualisasi', 'Dijadikan Kegiatan Rutin Dalam Menunjang Tugas Dan Fungsi', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1656, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Faktor Pendukung Aktualisasi', 'Sebutkan Faktor Pendukung Pelaksanaan Aktualisasi Yang Lain, Jika Ada!', 'text', NULL, '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1657, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Faktor Penghambat Aktualisasi', 'Pekerjaan Rutin Menjadi Kendala Dalam Pelaksanaan Aktualisasi', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1658, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Faktor Penghambat Aktualisasi', 'Kurangnya Dukungan Dari Lingkungan Unit Kerja', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1659, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Faktor Penghambat Aktualisasi', 'Keterbatasan Sumber Daya Menghambat Pelaksanaan Aktualisasi', 'dropdown', '[\"Tidak Sesuai\",\"Kurang Sesuai\",\"Sesuai\",\"Sangat Sesuai\"]', '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1660, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_mandiri', 'semua', 'Faktor Penghambat Aktualisasi', 'Sebutkan Kendala/Hambatan Lain Yang Dihadapi Dalam Pelaksanaan Aktualisasi, Jika Ada!', 'text', NULL, '2026-09-07 10:35:39', '2026-09-07 10:35:39'),
(1661, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Nama Responden', 'text', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1662, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Nama Instansi Responden', 'text', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1663, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Jabatan Responden', 'text', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1664, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Nama Instansi Alumni', 'text', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1665, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Jabatan alumni saat pelatihan', 'text', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1666, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Jabatan alumni saat ini', 'text', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1667, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Metode Pelaksanaan', 'checkbox', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1668, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu menunjukan sikap perilaku bela negara yakni taat pada hukum dan aturan negara dalam menjalankan tugas jabatan', 'text', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1669, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu berpikir kritis, analitis, dan objektif dalam menjalankan tugas jabatan', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1670, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Dapat melakukan praktik yang mencerminkan kesiapsiagaan fisik dan mental dalam suatu kegiatan yang melatih kedisiplinan, kepemimpinan, kerjasama, dan berinisiatif', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1671, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Memahami dan memenuhi kebutuhan masyarakat', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1672, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Ramah, cekatan, solutif, dan dapat diandalkan', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1673, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Melakukan perbaikan tiada henti', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1674, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Melaksanakan tugas dengan jujur, bertanggung jawab, cermat, disiplin dan berintegritas tinggi', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1675, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Menggunakan kekayaan dan barang milik negara secara bertanggung jawab, efektif, dan efisien', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1676, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Tidak menyalahgunakan kewenangan jabatan', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1677, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Meningkatkan kompetensi diri untuk menjawab tantangan yang selalu berubah', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1678, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Membantu orang lain belajar', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45');
INSERT INTO `evaluation_questions` (`id`, `training_type`, `bidang`, `program_evaluasi`, `training_id`, `category`, `metode`, `sub_category`, `question_text`, `type`, `options`, `created_at`, `updated_at`) VALUES
(1679, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Melaksanakan tugas dengan kualitas terbaik', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1680, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Menghargai setiap orang apa pun latar belakangnya', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1681, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Suka menolong orang lain', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1682, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Membangun lingkungan kerja yang kondusif', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1683, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Memegang teguh ideologi Pancasila, Undang Undang Dasar Negara Republik Indonesia Tahun 1945, NKRI serta pemerintahan yang sah', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1684, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Menjaga nama baik sesama ASN, Pimpinan, Instansi, dan Negara', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1685, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Menjaga rahasia jabatan dan negara', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1686, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Cepat menyesuaikan diri menghadapi perubahan', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1687, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Terus berinovasi dan mengembangkan kreativitas', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1688, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Bertindak proaktif', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1689, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Memberi kesempatan kepada berbagai pihak untuk berkontribusi', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1690, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Terbuka dalam bekerja sama untukmenghasilkan nilai tambah', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1691, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Menggerakkan pemanfaatan berbagai sumber daya untuk tujuan bersama', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1692, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Menggerakkan pemanfaatan berbagai sumber daya untuk tujuan bersama', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1693, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Memahami sistem merit dalam pengelolaan ASN', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1694, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Memiliki literasi digital', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1695, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu memanfaatkan sarana digital dasar dalam organisasi', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1696, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Bersifat kritis atas kasus kecakapan digital dasar', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1697, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu melaksanakan kebijakan publik yang telah ditetapkan oleh pemerintah ataupun pimpinan instansi sesuai dengan perundangan-undangan yang berlaku', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1698, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu memberikan pelayanan publik yang profesional dan berkualitas', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1699, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu mempererat persatuan dan kesatuan Negara Kesatuan Republik Indonesia', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1700, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Terdapat peningkatan pengetahuan dan keterampilan yang bersifat umum/administratif', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1701, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Terdapat peningkatan pengetahuan dan keterampilan yang bersifat spesifik, substantif dan/atau bidang yang diperlukan', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1702, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Memiliki pengetahuan dan keterampilan jabatan fungsional sesuai dengan formasi jabatannya', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1703, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Terdapat keberlanjutan Aktualisasi dalam jangka panjang', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1704, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Seluruh capaian hasil dari Aktualisasi dapat dicapai dengan baik', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1705, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Realisasi hasil aktualisasi tercapai sesuai dengan rencana aktualisasi', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1706, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Faktor Pendukung Aktualisasi', 'Terdapat dukungan dari Mentor', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1707, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Faktor Pendukung Aktualisasi', 'Terdapat dukungan Pemangku Kepentingan', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1708, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Faktor Pendukung Aktualisasi', 'Terdapat dukungan dari Anggota Tim', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1709, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Faktor Pendukung Aktualisasi', 'Terdapat dukungan Sarana dan Prasarana', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1710, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Faktor Pendukung Aktualisasi', 'Dijadikan kegiatan rutin dalam menunjang tugas dan fungsi', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1711, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Faktor Pendukung Aktualisasi', 'Sebutkan faktor pendukung pelaksanaan aktualisasi yang lain, jika ada!', 'text', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1712, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aktualisasi', 'Pekerjaan rutin menjadi kendala dalam pelaksanaan aktualisasi', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1713, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aktualisasi', 'Kurangnya dukungan dari lingkungan unit kerja', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1714, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aktualisasi', 'Keterbatasan sumber daya menghambat pelaksanaan aktualisasi', 'dropdown', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1715, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'CPNS', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aktualisasi', 'Sebutkan kendala/hambatan lain yang dihadapi dalam pelaksanaan aktualisasi, jika ada!', 'text', NULL, '2026-09-07 10:35:45', '2026-09-07 10:35:45'),
(1716, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Nama Responden', 'text', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1717, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Alamat Responden', 'text', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1718, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Nama Instansi Responden', 'text', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1719, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Jabatan Responden', 'text', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1720, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Nama Instansi Alumni', 'text', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1721, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Jabatan Alumni Saat Pelatihan', 'text', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1722, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Jabatan Alumni Saat Ini', 'text', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1723, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Metode Pelaksanaan', 'checkbox', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1724, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Menerapkan kerangka kebijakan pemerintahan yang bersih dan akuntabel', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1725, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu mengeksplorasi perkembangan teknologi digital dan implikasinya terhadap perbaikan kebijakan strategis organisasi', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1726, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu mengembangkan peran dan fungsi kepemimpinan di era digital', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1727, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu mengimplementasikan kepemimpinan kewirausahaan dan mengorganisir kepemimpinan kewirausahaan', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1728, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu membangun organisasi pembelajar', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1729, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu menerapkan manajemen strategis sektor publik', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1730, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu mempraktikkan teknik penyusunan rencana strategis pada instansi pemerintah', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1731, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Memahami isu strategis tentang integritas kepemimpinan, kepemimpinan kewirausahaan dan kebijakan nasional', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1732, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu mengembangkan strategi marketing 1sektor publik yang efektif', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1733, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu mengembangkan kemitraann sektor publik atau private', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1734, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Terdapat peningkatan produktivitas individu setelah mengikuti pelatihan', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1735, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dapat berkontribusi lebih dalam peningkatan kinerja organisasi', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1736, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', ' Target proyek perubahan dalam jangka panjang tercapai', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1737, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Seluruh tujuan proyek perubahan dapat tercapai', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1738, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Keberhasilan kepemimpinan strategis', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1739, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Manfaat proyek perubahan', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1740, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', ' Terdapat dukungan dari Mentor', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1741, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Terdapat dukungan Pemangku Kepentingan', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1742, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Terdapat dukungan dari Anggota Tim', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1743, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Terdapat dukungan Sarana dan Prasarana', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1744, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Dijadikan kegiatan rutin dalam menunjang tugas dan fungsi', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1745, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', ' Sebutkan faktor pendukung pelaksanaan proyek perubahan yang lain, jika ada', 'text', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1746, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Pekerjaan rutin menjadi kendala dalam pelaksanaan proyek perubahan', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1747, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Kurangnya dukungan dari lingkungan unit kerja', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1748, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Keterbatasan sumber daya menghambat pelaksanaan proyek perubahan', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1749, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Sebutkan kendala/hambatan lain yang dihadapi dalam pelaksanaan proyek perubahan, jika ada!', 'text', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1750, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kesesuaian Rekomendasi Kebijakan Dengan Kebutuhan Instansi', 'Isu yang diangkat bersifat aktual dan bersifat problematic di Instansi', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1751, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kesesuaian Rekomendasi Kebijakan Dengan Kebutuhan Instansi', 'Rekomendasi kebijakan menawarkan alternatif solusi permasalahan kebijakan', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1752, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kesesuaian Rekomendasi Kebijakan Dengan Kebutuhan Instansi', 'Rekomendasi Policy Brief mengandung unsur terobosan inovasi (adanya unsur kebaruan)', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1753, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kesesuaian Rekomendasi Kebijakan Dengan Kebutuhan Instansi', 'Rekomendasi kebijakan yang ditawarkan disertai dengan analisis dampak dan kebutuhan sumber daya', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1754, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kesesuaian Rekomendasi Kebijakan Dengan Kebutuhan Instansi', 'Rekomendasi yang disampaikan mudah dipahami dan mudah diimplementasikan', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1755, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kemanfaatan Rekomendasi', 'Rekomendasi kebijakan dalam policy brief diadopsi (seluruhnya digunakan) oleh Instans', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1756, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kemanfaatan Rekomendasi', 'Rekomendasi kebijakan dalam policy brief diadaptasi (disesuaikan dengan kebutuhan instansi) oleh Instansi', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1757, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kemanfaatan Rekomendasi', 'Rekomendasi kebijakan dalam policy brief menjadireferensi dalam pengambilan kebijakan Instansi', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1758, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kemanfaatan Rekomendasi', 'Rekomendasi kebijakan dalam policy Brief tidak digunakan dalam pengambilan kebijakan instansi', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1759, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kemanfaatan Rekomendasi', 'Bagaimana rekomendasi dalam policy brief tersebut diadopsi/diadaptasi di Instansi Saudara?4', 'text', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1760, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kemanfaatan Rekomendasi', 'Apakah kebijakan sebagaimana disebutkan pada nomor 5 di atas, memberikan dampak pada peningkatan kinerja organisasi?', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1761, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kemanfaatan Rekomendasi', 'Jika Ya, sebutkan kinerja yang dimaksud sebagaimana pertanyaan nomor 6.', 'text', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1762, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kemanfaatan Rekomendasi', 'Apakah kebijakan sebagaimana disebutkan pada nomor 5 di atas, masih diimplementasikan?', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1763, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_mandiri', 'semua', 'Kemanfaatan Rekomendasi', 'Apakah kebijakan sebagaimana disebutkan pada nomor 5 di atas, masih diimplementasikan?', 'dropdown', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1764, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kemanfaatan Rekomendasi', 'Jika rekomendasi kebijakan dalam Policy Brief tidak diadopsi/diadaptasi/menjadi bahan referensi dalam penyusunan Kebijakan, apa penyebabnya?', 'text', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1765, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kemanfaatan Rekomendasi', 'Jika Rekomendasi Kebijakan Dalam Policy Brief Tidak Diadopsi/Diadaptasi/Menjadi Bahan Referensi Dalam Penyusunan Kebijakan, Apa Penyebabnya?', 'text', NULL, '2026-09-07 10:35:52', '2026-09-07 10:35:52'),
(1766, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Nama Instansi', 'text', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1767, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Jabatan saat pelatihan', 'text', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1768, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Jabatan saat ini', 'text', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1769, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Metode Pelaksanaan', 'checkbox', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1770, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Mampu mempraktikkan pengelolaan energi dan potensi diri untuk memimpin perubahan strategis organisasi', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1771, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Mempunyai potensi untuk mengikuti PKN I', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1772, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Data Diri Alumni', 'Mampu mengantisipasi tantangan dan resiko penegakan integritas dalam penyelenggaraan pemerintahan yang bersih dan akuntabel', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1773, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Menerapkan kerangka kebijakan pemerintahan yang bersih dan akuntabel', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1774, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu mengeksplorasi perkembangan teknologi digital dan implikasinya terhadap perbaikan kebijakan strategis organisasi', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1775, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu mengembangkan peran dan fungsi kepemimpinan di era digital', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1776, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu mengimplementasikan kepemimpinan kewirausahaan dan mengorganisir kepemimpinan kewirausahaan', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1777, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu membangun organisasi pembelajar', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1778, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu menerapkan manajemen strategis sektor publik', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1779, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu mempraktikkan teknik penyusunan rencana strategis pada instansi pemerintah', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1780, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Memahami isu strategis tentang integritas kepemimpinan, kepemimpinan kewirausahaan dan kebijakan nasional', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1781, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu mengembangkan strategi marketing 1sektor publik yang efektif', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1782, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu mengembangkan kemitraann sektor publik atau private', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1783, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Terdapat peningkatan produktivitas individu setelah mengikuti pelatihan', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1784, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dapat berkontribusi lebih dalam peningkatan kinerja organisasi', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1785, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', ' Target proyek perubahan dalam jangka panjang tercapai', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1786, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Seluruh tujuan proyek perubahan dapat tercapai', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1787, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Keberhasilan kepemimpinan strategis', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1788, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Manfaat proyek perubahan', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1789, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', ' Terdapat dukungan dari Mentor', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1790, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Terdapat dukungan Pemangku Kepentingan', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1791, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Terdapat dukungan dari Anggota Tim', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1792, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Terdapat dukungan Sarana dan Prasarana', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1793, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Dijadikan kegiatan rutin dalam menunjang tugas dan fungsi', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1794, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', ' Sebutkan faktor pendukung pelaksanaan proyek perubahan yang lain, jika ada', 'text', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1795, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Pekerjaan rutin menjadi kendala dalam pelaksanaan proyek perubahan', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1796, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Kurangnya dukungan dari lingkungan unit kerja', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1797, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Keterbatasan sumber daya menghambat pelaksanaan proyek perubahan', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1798, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Sebutkan kendala/hambatan lain yang dihadapi dalam pelaksanaan proyek perubahan, jika ada!', 'text', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1799, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kesesuaian Rekomendasi Kebijakan Dengan Kebutuhan Instansi', 'Isu yang diangkat bersifat aktual dan bersifat problematic di Instansi', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1800, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kesesuaian Rekomendasi Kebijakan Dengan Kebutuhan Instansi', 'Rekomendasi kebijakan menawarkan alternatif solusi permasalahan kebijakan', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1801, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kesesuaian Rekomendasi Kebijakan Dengan Kebutuhan Instansi', 'Rekomendasi Policy Brief mengandung unsur terobosan inovasi (adanya unsur kebaruan)', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1802, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kesesuaian Rekomendasi Kebijakan Dengan Kebutuhan Instansi', 'Rekomendasi kebijakan yang ditawarkan disertai dengan analisis dampak dan kebutuhan sumber daya', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1803, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kesesuaian Rekomendasi Kebijakan Dengan Kebutuhan Instansi', 'Rekomendasi yang disampaikan mudah dipahami dan mudah diimplementasikan', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1804, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kemanfaatan Rekomendasi', 'Rekomendasi kebijakan dalam policy brief diadopsi (seluruhnya digunakan) oleh Instans', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1805, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kemanfaatan Rekomendasi', 'Rekomendasi kebijakan dalam policy brief diadaptasi (disesuaikan dengan kebutuhan instansi) oleh Instansi', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1806, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kemanfaatan Rekomendasi', 'Rekomendasi kebijakan dalam policy brief menjadireferensi dalam pengambilan kebijakan Instansi', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1807, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kemanfaatan Rekomendasi', 'Rekomendasi kebijakan dalam policy Brief tidak digunakan dalam pengambilan kebijakan instansi', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1808, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kemanfaatan Rekomendasi', 'Bagaimana rekomendasi dalam policy brief tersebut diadopsi/diadaptasi di Instansi Saudara?4', 'text', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1809, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kemanfaatan Rekomendasi', 'Apakah kebijakan sebagaimana disebutkan pada nomor 5 di atas, memberikan dampak pada peningkatan kinerja organisasi?', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1810, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kemanfaatan Rekomendasi', 'Jika Ya, sebutkan kinerja yang dimaksud sebagaimana pertanyaan nomor 6.', 'text', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1811, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kemanfaatan Rekomendasi', 'Apakah kebijakan sebagaimana disebutkan pada nomor 5 di atas, masih diimplementasikan?', 'dropdown', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1812, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kemanfaatan Rekomendasi', 'Apakah kebijakan sebagaimana disebutkan pada nomor 5 di atas, masih diimplementasikan?', 'text', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1813, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKN', NULL, 'l34_atasan', 'semua', 'Kemanfaatan Rekomendasi', 'Jika rekomendasi kebijakan dalam Policy Brief tidak diadopsi/diadaptasi/menjadi bahan referensi dalam penyusunan Kebijakan, apa penyebabnya?', 'text', NULL, '2026-09-07 10:36:00', '2026-09-07 10:36:00'),
(1814, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Nama Instansi Responden', 'text', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1815, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Jabatan Responden', 'text', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1816, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Nama Instansi Alumni', 'text', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1817, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Jabatan alumni saat pelatihan', 'text', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1818, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Jabatan alumni saat ini', 'text', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1819, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Metode Pelaksanaan', 'checkbox', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1820, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu menunjukan sikap perilaku bela negara yakni taat pada hukum dan aturan negara dalam menjalankan tugas jabatan', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1821, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu menginventarisasi solusi kreatif dan kekinian dalam kerangka kerja nilai-nilai Pancasila dan bela Negara dalam mengantisipasi hambatan pemberantasan korupsi dalam pelayanan', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1822, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Dapat memberikan teladan bagi bawahannya dalam melakukan praktik yang mencerminkan nilai-nilai wawasan kebangsaan, kerangka berpikir nilai-nilai Pancasila dan bela negara sebagai fondasi pelayanan', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1823, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Memahami dan memenuhi kebutuhan masyarakat', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1824, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Ramah, cekatan, solutif, dan dapat diandalkan', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1825, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', ' Dapat mendianogsa permasalahan yang ada', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1826, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Melaksanakan tugas dengan jujur, bertanggung jawab, cermat, disiplin dan berintegritas tinggi', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1827, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Dapat berpikir kreatif', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1828, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Tidak menyalahgunakan kewenangan jabatan', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1829, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Meningkatkan kompetensi diri untuk menjawab tantangan yang selalu berubah', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1830, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Membantu orang lain belajar', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1831, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Melaksanakan tugas dengan kualitas terbaik', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1832, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Menghargai setiap orang apa pun latar belakangnya', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1833, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Membangun tim dan memberdayakan tim', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1834, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', ' Membangun lingkungan kerja yang kondusif', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1835, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Memegang teguh ideologi Pancasila, Undang Undang Dasar Negara Republik Indonesia Tahun 1945, NKRI serta pemerintahan yang sah', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1836, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Menjaga nama baik sesama ASN, Pimpinan, Instansi, dan Negara', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1837, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Menjaga rahasia jabatan dan negara', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1838, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Cepat menyesuaikan diri menghadapi perubahan', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1839, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Terus berinovasi dan mengembangkan kreativitas', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1840, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', ' Bertindak proaktif', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1841, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Memberi kesempatan kepada berbagai pihak untuk berkontribusi', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1842, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Terbuka dalam bekerja sama untukmenghasilkan nilai tambah', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1843, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Menggerakkan pemanfaatan berbagai sumber daya untuk tujuan bersama', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1844, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu menyusun RKA', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1845, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Memiliki literasi digital', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1846, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu mengembangkan proses bisnis pemanfaatan sarana digital dalam pelayanan', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1847, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', ' Bersifat kritis atas kasus kecakapan digital dalam pelayanan', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1848, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu menerapkan mutu untuk hasil kegiatan pelayanan', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1849, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu melakukan pengawasan kegiatan', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11');
INSERT INTO `evaluation_questions` (`id`, `training_type`, `bidang`, `program_evaluasi`, `training_id`, `category`, `metode`, `sub_category`, `question_text`, `type`, `options`, `created_at`, `updated_at`) VALUES
(1850, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Terdapat peningkatan produktivitas individu setelah mengikuti pelatihan', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1851, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dapat berkontribusi lebih dalam peningkatan pelayanan kepada stakeholder', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1852, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Target Aksi Perubahan dalam jangka panjang tercapai', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1853, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Seluruh tujuan Aksi Perubahan dapat tercapai', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1854, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Manfaat Aksi Perubahan', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1855, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Terdapat dukungan dari Mentor', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1856, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Terdapat dukungan Pemangku Kepentingan', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1857, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Terdapat dukungan dari Anggota Tim', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1858, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Terdapat dukungan Sarana dan Prasarana', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1859, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Dijadikan kegiatan rutin dalam menunjang tugas dan fungsi', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1860, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Sebutkan faktor pendukung pelaksanaan aksi perubahan yang lain, jika ada!', 'text', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1861, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Pekerjaan rutin menjadi kendala dalam pelaksanaan aksi perubahan', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1862, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Kurangnya dukungan dari lingkungan unit kerja', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1863, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Keterbatasan sumber daya menghambat pelaksanaan aksi perubahan', 'dropdown', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1864, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Sebutkan kendala/hambatan lain yang dihadapi dalam pelaksanaan aksi perubahan, jika ada!', 'text', NULL, '2026-09-07 10:36:11', '2026-09-07 10:36:11'),
(1865, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Jabatan saat pelatihan', 'text', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1866, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Jabatan saat ini', 'text', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1867, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Nama Instansi Alumni', 'text', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1868, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Jabatan alumni saat pelatihan', 'text', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1869, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Jabatan alumni saat ini', 'text', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1870, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Metode Pelaksanaan', 'checkbox', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1871, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu menunjukan sikap perilaku bela negara yakni taat pada hukum dan aturan negara dalam menjalankan tugas jabatan', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1872, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu menginventarisasi solusi kreatif dan kekinian dalam kerangka kerja nilai-nilai Pancasila dan bela Negara dalam mengantisipasi hambatan pemberantasan korupsi dalam pelayanan', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1873, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Dapat memberikan teladan bagi bawahannya dalam melakukan praktik yang mencerminkan nilai-nilai wawasan kebangsaan, kerangka berpikir nilai-nilai Pancasila dan bela negara sebagai fondasi pelayanan', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1874, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Memahami dan memenuhi kebutuhan masyarakat', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1875, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Ramah, cekatan, solutif, dan dapat diandalkan', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1876, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', ' Dapat mendianogsa permasalahan yang ada', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1877, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Melaksanakan tugas dengan jujur, bertanggung jawab, cermat, disiplin dan berintegritas tinggi', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1878, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Dapat berpikir kreatif', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1879, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Tidak menyalahgunakan kewenangan jabatan', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1880, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Meningkatkan kompetensi diri untuk menjawab tantangan yang selalu berubah', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1881, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Membantu orang lain belajar', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1882, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Melaksanakan tugas dengan kualitas terbaik', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1883, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Menghargai setiap orang apa pun latar belakangnya', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1884, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Membangun tim dan memberdayakan tim', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1885, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', ' Membangun lingkungan kerja yang kondusif', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1886, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Memegang teguh ideologi Pancasila, Undang Undang Dasar Negara Republik Indonesia Tahun 1945, NKRI serta pemerintahan yang sah', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1887, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Menjaga nama baik sesama ASN, Pimpinan, Instansi, dan Negara', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1888, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Menjaga rahasia jabatan dan negara', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1889, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Cepat menyesuaikan diri menghadapi perubahan', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1890, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Terus berinovasi dan mengembangkan kreativitas', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1891, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', ' Bertindak proaktif', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1892, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Memberi kesempatan kepada berbagai pihak untuk berkontribusi', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1893, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Terbuka dalam bekerja sama untukmenghasilkan nilai tambah', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1894, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Menggerakkan pemanfaatan berbagai sumber daya untuk tujuan bersama', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1895, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu menyusun RKA', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1896, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Memiliki literasi digital', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1897, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu mengembangkan proses bisnis pemanfaatan sarana digital dalam pelayanan', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1898, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', ' Bersifat kritis atas kasus kecakapan digital dalam pelayanan', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1899, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu menerapkan mutu untuk hasil kegiatan pelayanan', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1900, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu melakukan pengawasan kegiatan', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1901, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Terdapat peningkatan produktivitas individu setelah mengikuti pelatihan', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1902, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dapat berkontribusi lebih dalam peningkatan pelayanan kepada stakeholder', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1903, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Target Aksi Perubahan dalam jangka panjang tercapai', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1904, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Seluruh tujuan Aksi Perubahan dapat tercapai', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1905, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Manfaat Aksi Perubahan', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1906, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Terdapat dukungan dari Mentor', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1907, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Terdapat dukungan Pemangku Kepentingan', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1908, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Terdapat dukungan dari Anggota Tim', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1909, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Terdapat dukungan Sarana dan Prasarana', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1910, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Dijadikan kegiatan rutin dalam menunjang tugas dan fungsi', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1911, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Sebutkan faktor pendukung pelaksanaan aksi perubahan yang lain, jika ada!', 'text', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1912, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Pekerjaan rutin menjadi kendala dalam pelaksanaan aksi perubahan', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1913, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Kurangnya dukungan dari lingkungan unit kerja', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1914, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Keterbatasan sumber daya menghambat pelaksanaan aksi perubahan', 'dropdown', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1915, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKP', NULL, 'l34_mandiri', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Sebutkan kendala/hambatan lain yang dihadapi dalam pelaksanaan aksi perubahan, jika ada!', 'text', NULL, '2026-09-07 10:36:23', '2026-09-07 10:36:23'),
(1916, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Nama Instansi', 'text', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1917, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Jabatan saat pelatihan', 'text', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1918, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Jabatan  saat ini', 'text', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1919, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Data Diri Alumni', 'Metode Pelaksanaan', 'checkbox', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1920, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu menunjukan sikap perilaku bela negara yakni taat pada hukum dan aturan negara dalam menjalankan tugas jabatan', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1921, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Dapat menunjukkan praktik pencegahan ekstremisme berbasis kekerasan yang mengarah pada terorisme', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1922, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Dapat menunjukkan praktik pemberantasan korupsi dan kesinambungan kinerja sebagai refleksi wawasan kebangsaan dan bela Negara', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1923, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu menginventarisasi solusi kreatif dan kekinian dalam kerangka kerja nilai-nilai Pancasila dan bela Negara dalam mengantisipasi hambatan pemberantasan korupsi dan kesinambungan kinerja organisasi sebagai wujud kewaspadaan nasional', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1924, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Dapat melakukan praktik yang mencerminkan nilai-nilai wawasan kebangsaan, kerangka berpikir nilai-nilai Pancasila dan bela negara sebagai fondasi peningkatan kinerja organisasi', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1925, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu menjadi coach atau mentor dalam membantu orang lain belajar', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1926, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu menstimulasi dan merefleksikan kepemimpinan transformasional', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1927, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Melaksanakan tugas dengan jujur, bertanggung jawab, cermat, disiplin dan berintegritas tinggi', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1928, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu memperluas jejaring kerja', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1929, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu membangun komunikasi yang efektif secara internal dan eksternal', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1930, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Memahami konsep manajemen perubahan sektor publik, kepemimpinan dalam manajemen perubahan, strategi perubahan di unit organisasinya, tahapan manajemen perubahan, dan menganalisa permasalahan perubahan sektor publik dari perspektif manajemen perubahan', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1931, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Tidak menyalahgunakan kewenangan jabatan', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1932, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Meningkatkan kompetensi diri untuk menjawab tantangan yang selalu berubah', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1933, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Melaksanakan tugas dengan kualitas terbaik', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1934, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Menghargai setiap orang apa pun latar belakangnya', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1935, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Membangun lingkungan kerja yang kondusif', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1936, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Memegang teguh ideologi Pancasila, Undang-Undang Dasar Negara Republik Indonesia Tahun 1945, NKRI serta pemerintahan yang sah', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1937, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Menjaga nama baik sesama ASN, Pimpinan, Instansi, dan Negara', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1938, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Menjaga rahasia jabatan dan negara', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1939, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Cepat menyesuaikan diri menghadapi perubahan', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1940, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Terus berinovasi dan mengembangkan kreativitas', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1941, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Bertindak proaktif', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1942, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Memberi kesempatan kepada berbagai pihak untuk berkontribusi', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1943, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Terbuka dalam bekerja sama untuk menghasilkan nilai tambah', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1944, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Menggerakkan pemanfaatan berbagai sumber daya untuk tujuan peningkatan kinerja organisasi', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1945, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu merumuskan langkah/upaya perbaikan akuntabilitas', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1946, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu merumuskan langkah/upaya membangun etos kerja pelayanan publik dalam kepemimpinan administrasi', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1947, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu merumuskan langkah/upaya komunikasi dan koordinasi dalam organisasi', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1948, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Perubahan Sikap Perilaku', 'Mampu mengembangkan rencana kerja pemanfaatan sarana digital dalam meningkatkan kinerja organisasi', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1949, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Terdapat peningkatan produktivitas individu setelah mengikuti pelatihan', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1950, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', ' Dapat berkontribusi lebih dalam peningkatan pelayanan kepada stakeholder', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1951, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Target Aksi Perubahan dalam jangka panjang tercapai', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1952, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Seluruh tujuan Aksi Perubahan dapat tercapai', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1953, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Manfaat Aksi Perubahan', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1954, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Terdapat dukungan dari Mentor', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1955, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Terdapat dukungan Pemangku Kepentingan', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1956, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Terdapat dukungan dari Anggota Tim', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1957, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Terdapat dukungan Sarana dan Prasarana', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1958, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Dijadikan kegiatan rutin dalam menunjang tugas dan fungsi', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1959, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Sebutkan faktor pendukung pelaksanaan aksi perubahan yang lain, jika ada!', 'text', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1960, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Pekerjaan rutin menjadi kendala dalam pelaksanaan aksi perubahan', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1961, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Kurangnya dukungan dari lingkungan unit kerja', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1962, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Keterbatasan sumber daya menghambat pelaksanaan aksi perubahan', 'dropdown', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1963, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_mandiri', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Sebutkan kendala/hambatan lain yang dihadapi dalam pelaksanaan aksi perubahan, jika ada!', 'text', NULL, '2026-09-07 10:36:30', '2026-09-07 10:36:30'),
(1964, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Nama Responden', 'text', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1965, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Nama Instansi Responden', 'text', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1966, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Jabatan Responden', 'text', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1967, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Nama Instansi Alumni', 'text', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1968, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Jabatan alumni saat pelatihan', 'text', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1969, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Jabatan alumni saat ini', 'text', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1970, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Data Diri Atasan', 'Metode Pelaksanaan', 'checkbox', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1971, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu menunjukan sikap perilaku bela negara yakni taat pada hukum dan aturan negara dalam menjalankan tugas jabatan', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1972, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Dapat menunjukkan praktik pencegahan ekstremisme berbasis kekerasan yang mengarah pada terorisme', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1973, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Dapat menunjukkan praktik pemberantasan korupsi dan kesinambungan kinerja sebagai refleksi wawasan kebangsaan dan bela Negara', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1974, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu menginventarisasi solusi kreatif dan kekinian dalam kerangka kerja nilai-nilai Pancasila dan bela Negara dalam mengantisipasi hambatan pemberantasan korupsi dan kesinambungan kinerja organisasi sebagai wujud kewaspadaan nasional', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1975, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Dapat melakukan praktik yang mencerminkan nilai-nilai wawasan kebangsaan, kerangka berpikir nilai-nilai Pancasila dan bela negara sebagai fondasi peningkatan kinerja organisasi', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1976, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu menjadi coach atau mentor dalam membantu orang lain belajar', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1977, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu menstimulasi dan merefleksikan kepemimpinan transformasional', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1978, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Melaksanakan tugas dengan jujur, bertanggung jawab, cermat, disiplin dan berintegritas tinggi', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1979, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu memperluas jejaring kerja', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1980, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu membangun komunikasi yang efektif secara internal dan eksternal', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1981, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Memahami konsep manajemen perubahan sektor publik, kepemimpinan dalam manajemen perubahan, strategi perubahan di unit organisasinya, tahapan manajemen perubahan, dan menganalisa permasalahan perubahan sektor publik dari perspektif manajemen perubahan', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1982, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Tidak menyalahgunakan kewenangan jabatan', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1983, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Meningkatkan kompetensi diri untuk menjawab tantangan yang selalu berubah', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1984, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Melaksanakan tugas dengan kualitas terbaik', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1985, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Menghargai setiap orang apa pun latar belakangnya', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1986, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Membangun lingkungan kerja yang kondusif', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1987, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Memegang teguh ideologi Pancasila, Undang-Undang Dasar Negara Republik Indonesia Tahun 1945, NKRI serta pemerintahan yang sah', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1988, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Menjaga nama baik sesama ASN, Pimpinan, Instansi, dan Negara', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1989, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Menjaga rahasia jabatan dan negara', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1990, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Cepat menyesuaikan diri menghadapi perubahan', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1991, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Terus berinovasi dan mengembangkan kreativitas', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1992, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Bertindak proaktif', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1993, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Memberi kesempatan kepada berbagai pihak untuk berkontribusi', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1994, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Terbuka dalam bekerja sama untuk menghasilkan nilai tambah', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1995, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Menggerakkan pemanfaatan berbagai sumber daya untuk tujuan peningkatan kinerja organisasi', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1996, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu merumuskan langkah/upaya perbaikan akuntabilitas', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1997, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu merumuskan langkah/upaya membangun etos kerja pelayanan publik dalam kepemimpinan administrasi', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1998, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu merumuskan langkah/upaya komunikasi dan koordinasi dalam organisasi', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(1999, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Perubahan Sikap Perilaku', 'Mampu mengembangkan rencana kerja pemanfaatan sarana digital dalam meningkatkan kinerja organisasi', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(2000, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Terdapat peningkatan produktivitas individu setelah mengikuti pelatihan', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(2001, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dapat berkontribusi lebih dalam peningkatan pelayanan kepada stakeholder', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(2002, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Target Aksi Perubahan dalam jangka panjang tercapai', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(2003, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Seluruh tujuan Aksi Perubahan dapat tercapai', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(2004, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Manfaat Aksi Perubahan', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(2005, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Terdapat dukungan dari Mentor', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(2006, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Terdapat dukungan Pemangku Kepentingan', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(2007, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Terdapat dukungan dari Anggota Tim', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(2008, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Terdapat dukungan Sarana dan Prasarana', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(2009, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Dijadikan kegiatan rutin dalam menunjang tugas dan fungsi', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(2010, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Faktor Pendukung Aksi Perubahan', 'Sebutkan faktor pendukung pelaksanaan aksi perubahan yang lain, jika ada!', 'text', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(2011, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Pekerjaan rutin menjadi kendala dalam pelaksanaan aksi perubahan', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(2012, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Kurangnya dukungan dari lingkungan unit kerja', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(2013, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Keterbatasan sumber daya menghambat pelaksanaan aksi perubahan', 'dropdown', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40'),
(2014, 'Bidang Pengembangan Kompetensi Manajerial', 'Bidang Pengembangan Kompetensi Manajerial', 'PKA', NULL, 'l34_atasan', 'semua', 'Faktor Penghambat Aksi Perubahan', 'Sebutkan kendala/hambatan lain yang dihadapi dalam pelaksanaan aksi perubahan, jika ada!', 'text', NULL, '2026-09-07 10:36:40', '2026-09-07 10:36:40');

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
  `document_year` smallint(5) UNSIGNED DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `archived_at` timestamp NULL DEFAULT NULL,
  `archived_by` bigint(20) UNSIGNED DEFAULT NULL,
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

INSERT INTO `folders` (`id`, `training_id`, `document_year`, `is_archived`, `archived_at`, `archived_by`, `name`, `bidang`, `parent_id`, `user_id`, `is_public`, `share_token`, `created_at`, `updated_at`) VALUES
(54, NULL, 2026, 0, NULL, NULL, 'Sertifikasi', 'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan', NULL, 12, 0, NULL, '2026-08-30 10:02:29', '2026-09-05 10:49:14'),
(71, NULL, 2026, 0, NULL, NULL, 'Pengajuan Mitra', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 5, 0, NULL, '2026-08-31 08:04:00', '2026-08-31 08:04:00'),
(72, NULL, NULL, 0, NULL, NULL, 'Kabupaten', 'Bidang Pengembangan Kompetensi Teknis Umum', 71, 5, 0, NULL, '2026-08-31 08:04:00', '2026-08-31 08:04:00'),
(73, NULL, NULL, 0, NULL, NULL, 'pasdjsdbjasfasf', 'Bidang Pengembangan Kompetensi Teknis Umum', 72, 5, 0, NULL, '2026-08-31 08:04:00', '2026-08-31 08:04:00'),
(112, 13, 2026, 0, NULL, NULL, 'Pelatihan Contoh Kedalam Umum - Angkatan 3', 'Bidang Pengembangan Kompetensi Teknis Umum', NULL, 5, 0, NULL, '2026-09-14 01:27:04', '2026-09-14 01:27:04');

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
-- Struktur dari tabel `guest_book_locations`
--

CREATE TABLE `guest_book_locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `public_token` char(36) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `guest_book_locations`
--

INSERT INTO `guest_book_locations` (`id`, `name`, `public_token`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'BPSDM Provinsi Jawa Barat', '90b6e452-107a-4e8c-ae58-42b729df4c7f', 1, 2, '2026-09-13 12:28:54', '2026-09-13 12:28:54');

-- --------------------------------------------------------

--
-- Struktur dari tabel `guest_visits`
--

CREATE TABLE `guest_visits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `guest_book_location_id` bigint(20) UNSIGNED NOT NULL,
  `visit_code` varchar(30) NOT NULL,
  `name` varchar(255) NOT NULL,
  `position` varchar(255) DEFAULT NULL,
  `institution` varchar(255) NOT NULL,
  `purpose` text NOT NULL,
  `target_bidang` varchar(255) DEFAULT NULL,
  `whatsapp` varchar(30) NOT NULL,
  `checked_in_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `checked_out_at` timestamp NULL DEFAULT NULL,
  `ip_hash` varchar(64) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `guest_visits`
--

INSERT INTO `guest_visits` (`id`, `guest_book_location_id`, `visit_code`, `name`, `position`, `institution`, `purpose`, `target_bidang`, `whatsapp`, `checked_in_at`, `checked_out_at`, `ip_hash`, `created_at`, `updated_at`) VALUES
(1, 1, 'BT-260913-LJJNKT', 'Samsidin', 'Pengelola', 'bpsdam', 'dmnasjdhasd hajdhajdhajkdads', 'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan', '08123456789', '2026-09-13 12:30:47', '2026-09-13 12:30:47', '12ca17b49af2289436f303e0166030a21e525d266e209267433801a8fd4071a0', '2026-09-13 12:29:29', '2026-09-13 12:30:47'),
(2, 1, 'BT-260913-SN7GQ5', 'SAMIDIN', 'Pengelola', 'Disdik', 'dkjasdadk adh askdah sdkah sd', 'Bidang Pengembangan Kompetensi Manajerial', '6281234567890', '2026-09-13 12:39:25', '2026-09-13 12:39:25', '12ca17b49af2289436f303e0166030a21e525d266e209267433801a8fd4071a0', '2026-09-13 12:36:42', '2026-09-13 12:39:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `internship_attendances`
--

CREATE TABLE `internship_attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `internship_participant_id` bigint(20) UNSIGNED NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('present','permission','sick','absent') NOT NULL,
  `check_in_at` timestamp NULL DEFAULT NULL,
  `check_out_at` timestamp NULL DEFAULT NULL,
  `late_minutes` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `check_in_photo_path` varchar(255) DEFAULT NULL,
  `check_out_photo_path` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `evidence_path` varchar(255) DEFAULT NULL,
  `review_status` enum('not_required','pending','approved','rejected') NOT NULL DEFAULT 'not_required',
  `review_note` text DEFAULT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `internship_attendances`
--

INSERT INTO `internship_attendances` (`id`, `internship_participant_id`, `attendance_date`, `status`, `check_in_at`, `check_out_at`, `late_minutes`, `check_in_photo_path`, `check_out_photo_path`, `note`, `evidence_path`, `review_status`, `review_note`, `reviewed_by`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(3, 2, '2026-09-13', 'present', '2026-09-13 09:01:58', '2026-09-13 09:02:22', 512, 'internships/attendance/2/2026-09-13/v55WFrrWqnJvuUVEC4zVhYHp1F8CPHFMB2xZMmuW.jpg', 'internships/attendance/2/2026-09-13/jA6mbhdKjINqXKc8dSDpTyeHmmW5TQvToGBUeCUl.jpg', NULL, NULL, 'not_required', NULL, NULL, NULL, '2026-09-13 09:01:58', '2026-09-13 09:02:22');

-- --------------------------------------------------------

--
-- Struktur dari tabel `internship_participants`
--

CREATE TABLE `internship_participants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `internship_program_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `student_number` varchar(100) NOT NULL,
  `major` varchar(255) NOT NULL,
  `institution` varchar(255) NOT NULL,
  `placement_unit` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `email` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
  `recommended_grade` enum('Sangat Baik','Baik','Cukup','Kurang') DEFAULT NULL,
  `final_grade` enum('Sangat Baik','Baik','Cukup','Kurang') DEFAULT NULL,
  `certificate_number` varchar(255) DEFAULT NULL,
  `certificate_generated_file_path` varchar(255) DEFAULT NULL,
  `certificate_file_path` varchar(255) DEFAULT NULL,
  `certificate_generated_at` timestamp NULL DEFAULT NULL,
  `certificate_sent_at` timestamp NULL DEFAULT NULL,
  `certificate_sent_by` bigint(20) UNSIGNED DEFAULT NULL,
  `certificate_downloaded_at` timestamp NULL DEFAULT NULL,
  `review_note` text DEFAULT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `internship_participants`
--

INSERT INTO `internship_participants` (`id`, `internship_program_id`, `user_id`, `name`, `student_number`, `major`, `institution`, `placement_unit`, `start_date`, `end_date`, `email`, `status`, `recommended_grade`, `final_grade`, `certificate_number`, `certificate_generated_file_path`, `certificate_file_path`, `certificate_generated_at`, `certificate_sent_at`, `certificate_sent_by`, `certificate_downloaded_at`, `review_note`, `reviewed_by`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(1, 1, 19, 'SAMSIDIN', '123456', 'Informatika', 'Institut Teknology Bandung', 'Bidang SKPK', '2026-09-06', '2026-09-11', 'sem@gmail.com', 'approved', 'Kurang', 'Sangat Baik', '002.1/Bspdm.04.1/GHD', 'internships/certificates/1/generated/1.pdf', NULL, '2026-09-13 11:53:39', NULL, NULL, NULL, 'tolong dijaga kehadirannya yah', 2, '2026-09-12 15:25:10', '2026-09-12 15:21:42', '2026-09-13 11:53:39'),
(2, 1, 20, 'Saepuloh', '1234567890', 'Informatika', 'Institut Teknology Bandung', 'Bidang SKPK', '2026-09-12', '2026-09-19', 'sams@gmail.com', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, '2026-09-13 06:23:25', '2026-09-13 06:21:02', '2026-09-13 06:23:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `internship_programs`
--

CREATE TABLE `internship_programs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `public_token` char(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `bidang` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `registration_opens_at` date DEFAULT NULL,
  `registration_closes_at` date DEFAULT NULL,
  `check_in_opens_at` time NOT NULL DEFAULT '06:00:00',
  `late_after` time NOT NULL DEFAULT '07:30:00',
  `check_out_opens_at` time NOT NULL DEFAULT '16:00:00',
  `status` enum('draft','open','closed','archived') NOT NULL DEFAULT 'draft',
  `certificate_template_path` varchar(255) DEFAULT NULL,
  `certificate_number_format` varchar(255) DEFAULT NULL,
  `certificate_start_sequence` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `certificate_issued_at` date DEFAULT NULL,
  `certificate_signer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `certificate_reviewer_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`certificate_reviewer_ids`)),
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `manager_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `internship_programs`
--

INSERT INTO `internship_programs` (`id`, `public_token`, `title`, `bidang`, `description`, `registration_opens_at`, `registration_closes_at`, `check_in_opens_at`, `late_after`, `check_out_opens_at`, `status`, `certificate_template_path`, `certificate_number_format`, `certificate_start_sequence`, `certificate_issued_at`, `certificate_signer_id`, `certificate_reviewer_ids`, `created_by`, `manager_id`, `created_at`, `updated_at`) VALUES
(1, '6e729d43-0e4d-46d2-9f75-9afc72ec6c7f', 'MAGANG DAN PKL BPSDM JABAR TAHUN 2026', 'Sekretariat', 'Magang ini ditujukan untuk daskdakjdhakd akdhaskdhaskd ashd kas', '2026-01-01', '2026-12-31', '06:00:00', '07:30:00', '16:00:00', 'open', 'internships/certificate-templates/NHGm2mQ6t1LuA90WgNhr2nqBsUSCV9uTWYGU8eep.docx', '002.{X}/Bspdm.04.1/GHD', 1, '2026-09-12', 18, NULL, 2, NULL, '2026-09-12 15:02:24', '2026-09-13 11:53:02');

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
-- Struktur dari tabel `login_help_settings`
--

CREATE TABLE `login_help_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Hubungi Admin via WhatsApp',
  `description` text DEFAULT NULL,
  `contacts` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`contacts`)),
  `message_template` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `login_help_settings`
--

INSERT INTO `login_help_settings` (`id`, `title`, `description`, `contacts`, `message_template`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Hubungi Admin via WhatsApp', 'Pilih admin untuk meminta bantuan reset password.', '[{\"name\":\"Sembiru\",\"phone\":\"6281382830814\"},{\"name\":\"Alam\",\"phone\":\"6281809597757\"},{\"name\":\"Rizky\",\"phone\":\"6281295317499\"}]', 'Halo Admin {ADMIN}, saya ingin meminta bantuan reset password akun INTEGRAL.\n\nNama Lengkap:\nNIP/NIK:\nEmail Terdaftar:\n\nMohon bantuannya. Terima kasih.', 1, '2026-09-12 13:21:06', '2026-09-12 13:21:06');

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
(83, '2026_08_30_171000_add_seen_at_to_folder_permissions', 19),
(84, '2026_08_31_100000_add_user_type_to_users_table', 20),
(85, '2026_08_31_110000_create_partner_submission_tables', 21),
(86, '2026_09_02_120000_create_evaluation_l1_text_summaries_table', 22),
(87, '2026_09_02_120000_create_asset_loan_requests_table', 23),
(88, '2026_09_03_080000_add_duration_unit_to_schedules_table', 24),
(89, '2026_09_03_120000_add_schedule_type_to_schedules_table', 25),
(90, '2026_09_04_000001_create_training_certificates_tables', 26),
(91, '2026_09_04_000002_add_photo_size_to_training_certificate_settings', 27),
(92, '2026_09_04_000003_add_downloaded_at_to_participant_certificates', 28),
(93, '2026_09_04_100000_create_training_activity_reports_tables', 29),
(94, '2026_09_05_000001_add_certificate_submission_to_certification_participants', 30),
(95, '2026_09_05_000002_create_notification_reads_table', 31),
(96, '2026_09_06_000001_create_ai_generations_table', 32),
(97, '2026_09_07_000001_create_training_execution_notes_table', 33),
(98, '2026_09_07_000002_create_public_asset_reservations', 34),
(99, '2026_09_08_000001_add_archiving_to_folders_table', 35),
(100, '2026_09_10_000001_create_electronic_signature_tables', 36),
(101, '2026_09_10_000002_link_electronic_signature_documents_to_participant_certificates', 37),
(102, '2026_09_10_000003_add_source_metadata_to_electronic_signatures', 38),
(103, '2026_09_10_000004_add_verification_token_to_electronic_signature_documents', 39),
(104, '2026_09_11_000001_add_tracking_events_to_asset_public_reservations', 40),
(105, '2026_09_11_000001_add_page_format_to_electronic_signature_requests', 41),
(106, '2026_09_11_000005_add_delivery_to_participant_certificates', 42),
(107, '2026_09_11_000006_create_asset_rental_rates_table', 43),
(109, '2026_09_12_000002_create_activity_attendance_forms', 44),
(110, '2026_09_12_000003_create_login_help_settings_table', 45),
(111, '2026_09_12_000004_create_internship_module_tables', 46),
(112, '2026_09_12_000005_create_internship_attendances_table', 47),
(113, '2026_09_12_000006_add_certificates_to_internship_module', 48),
(114, '2026_09_12_000007_link_internship_certificates_to_tte', 49),
(115, '2026_09_13_180000_add_identity_and_address_to_users_table', 50),
(116, '2026_09_13_180100_normalize_kota_column_on_users_table', 51),
(117, '2026_09_13_180200_rename_non_asn_employment_status_to_pppk_pw', 52),
(118, '2026_09_13_180300_add_internship_manager_role_to_users', 53),
(119, '2026_09_13_180400_create_guest_book_module', 54),
(120, '2026_09_13_180500_add_target_bidang_to_guest_visits', 55),
(121, '2026_09_14_090000_add_import_onboarding_flags_to_users_table', 56);

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
-- Struktur dari tabel `notification_reads`
--

CREATE TABLE `notification_reads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `notification_key` varchar(255) NOT NULL,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `notification_reads`
--

INSERT INTO `notification_reads` (`id`, `user_id`, `notification_key`, `read_at`, `created_at`, `updated_at`) VALUES
(2, 2, 'asset-loan-9-approved', '2026-09-05 12:26:21', '2026-09-05 12:26:21', '2026-09-05 12:26:21'),
(3, 2, 'asset-loan-10-approved', '2026-09-05 12:26:23', '2026-09-05 12:26:23', '2026-09-05 12:26:23'),
(4, 5, 'asset-loan-10-approved', '2026-09-05 12:38:41', '2026-09-05 12:38:41', '2026-09-05 12:38:41'),
(5, 2, 'asset-loan-13-revision', '2026-09-05 13:07:02', '2026-09-05 13:07:02', '2026-09-05 13:07:02'),
(6, 5, 'asset-loan-14-approved', '2026-09-05 13:22:31', '2026-09-05 13:22:31', '2026-09-05 13:22:31'),
(7, 5, 'asset-loan-15-approved', '2026-09-05 13:22:34', '2026-09-05 13:22:34', '2026-09-05 13:22:34'),
(8, 9, 'asset-usage-upcoming-9-2026-09-05', '2026-09-05 13:40:48', '2026-09-05 13:26:43', '2026-09-05 13:40:48'),
(9, 2, 'asset-usage-upcoming-2-2026-09-05', '2026-09-05 13:26:53', '2026-09-05 13:26:53', '2026-09-05 13:26:53'),
(10, 2, 'asset-loan-14-approved', '2026-09-05 13:26:55', '2026-09-05 13:26:55', '2026-09-05 13:26:55'),
(11, 2, 'asset-loan-15-approved', '2026-09-05 13:26:57', '2026-09-05 13:26:57', '2026-09-05 13:26:57'),
(12, 9, 'asset-usage-upcoming-9-2026-09-07', '2026-09-07 04:34:56', '2026-09-07 04:34:56', '2026-09-07 04:34:56'),
(13, 2, 'asset-usage-upcoming-2-2026-09-07', '2026-09-07 08:30:54', '2026-09-07 08:30:54', '2026-09-07 08:30:54'),
(14, 2, 'asset-usage-upcoming-2-2026-09-02', '2026-09-07 08:30:59', '2026-09-07 08:30:59', '2026-09-07 08:30:59'),
(17, 9, 'asset-rentals-payment-20260908214645', '2026-09-09 03:21:09', '2026-09-08 14:54:43', '2026-09-09 03:21:09'),
(18, 2, 'asset-rentals-payment-20260908214645', '2026-09-09 01:34:44', '2026-09-09 01:34:42', '2026-09-09 01:34:44'),
(19, 9, 'asset-rentals-pending-20260909102835', '2026-09-09 03:28:48', '2026-09-09 03:28:48', '2026-09-09 03:28:48'),
(20, 2, 'asset-usage-upcoming-2-2026-09-10', '2026-09-10 13:04:57', '2026-09-10 13:04:57', '2026-09-10 13:04:57'),
(21, 2, 'asset-usage-upcoming-2-2026-09-11', '2026-09-11 08:19:21', '2026-09-10 13:05:00', '2026-09-11 08:19:21'),
(22, 2, 'asset-rentals-payment-20260911151750', '2026-09-11 08:18:41', '2026-09-11 08:18:35', '2026-09-11 08:18:41'),
(23, 2, 'asset-usage-upcoming-2-2026-09-12', '2026-09-11 08:19:25', '2026-09-11 08:19:25', '2026-09-11 08:19:25'),
(24, 9, 'asset-usage-upcoming-9-2026-09-11', '2026-09-11 14:14:42', '2026-09-11 14:14:22', '2026-09-11 14:14:42'),
(25, 9, 'asset-usage-upcoming-9-2026-09-12', '2026-09-12 06:30:12', '2026-09-11 14:14:27', '2026-09-12 06:30:12'),
(26, 9, 'asset-rentals-payment-20260911225516', '2026-09-11 15:56:09', '2026-09-11 15:56:07', '2026-09-11 15:56:09'),
(27, 9, 'asset-rentals-payment-20260911230256', '2026-09-11 16:03:02', '2026-09-11 16:03:02', '2026-09-11 16:03:02');

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
(30, 13, 23, '199503032024011001', NULL, 'Contoh Nama Peserta', NULL, NULL, 'BPSDM Provinsi Jawa Barat', NULL, NULL, NULL, NULL, NULL, 'approved', '2026-09-14 04:48:17', '2026-09-14 04:48:17', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `participant_certificates`
--

CREATE TABLE `participant_certificates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_certificate_setting_id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED NOT NULL,
  `participant_id` bigint(20) UNSIGNED NOT NULL,
  `sequence_number` int(10) UNSIGNED NOT NULL,
  `certificate_number` varchar(255) NOT NULL,
  `generated_file_path` varchar(255) DEFAULT NULL,
  `final_file_path` varchar(255) DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `sent_by` bigint(20) UNSIGNED DEFAULT NULL,
  `downloaded_at` timestamp NULL DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `partner_submissions`
--

CREATE TABLE `partner_submissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(20) NOT NULL,
  `target_bidang` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `background` text DEFAULT NULL,
  `objective` text DEFAULT NULL,
  `scope` text DEFAULT NULL,
  `participant_target` varchar(255) DEFAULT NULL,
  `estimated_participants` int(10) UNSIGNED DEFAULT NULL,
  `competency` text DEFAULT NULL,
  `preferred_start` date DEFAULT NULL,
  `preferred_end` date DEFAULT NULL,
  `method` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `pic_name` varchar(255) NOT NULL,
  `pic_contact` varchar(30) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `folder_id` bigint(20) UNSIGNED DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `finalized_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `partner_submission_comments`
--

CREATE TABLE `partner_submission_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `partner_submission_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `partner_submission_documents`
--

CREATE TABLE `partner_submission_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `partner_submission_id` bigint(20) UNSIGNED NOT NULL,
  `uploaded_by` bigint(20) UNSIGNED NOT NULL,
  `version_number` int(10) UNSIGNED NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(30) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `change_note` text DEFAULT NULL,
  `is_final` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(2, 1, '34242352523523', 'asfsdfsdfsd', '5235235235235', 'dasfasfasfafsasf', NULL, NULL, NULL, 'pengajar/kelengkapan/XqeG8xWazeXKytqpmmwnP1eCnk1sy9dHMWnHS0wZ.pdf', 'pengajar/kelengkapan/3c8crnwPcBn4FVNOaU8Rpcj8Jxyag0awXohD8TEz.pdf', 'pengajar/kelengkapan/QbE8qeCFh0B4k54CVSE3091ewZtxhGRK1UTnkxKK.pdf', '2026-08-30 05:42:30', '2026-08-30 05:42:39'),
(3, 4, '673248264293428935235', 'sdfsdgsdgsdgsdgsdg', '423423423235235235', 'simpanakuaja delapan', NULL, NULL, 'afasasfasfasfasf', NULL, NULL, NULL, '2026-09-01 15:42:59', '2026-09-01 15:42:59'),
(4, 17, '34qeqwrqwrqwr', 'dsfdsgdsgsdg', '3425325235', 'simpan aja aku 6', NULL, NULL, 'faswefasfasf', NULL, NULL, NULL, '2026-09-02 02:04:34', '2026-09-02 02:04:34');

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
  `schedule_type` varchar(20) NOT NULL DEFAULT 'learning',
  `jp` int(11) DEFAULT NULL,
  `duration_unit` varchar(2) NOT NULL DEFAULT 'JP',
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
(13, 5, 'Bidang Pengembangan Kompetensi Teknis Umum', 'PKTI/PKTU', 'Pelatihan Contoh Kedalam Umum', 'T5OVCD', NULL, 'standar', 'full learning', 'Zoom', NULL, NULL, '3', 34, 50, '2026-09-13', '2026-09-19', NULL, NULL, '2026-09-14 01:27:04', '2026-09-14 01:27:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `training_activity_documentations`
--

CREATE TABLE `training_activity_documentations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `caption` text DEFAULT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'lainnya',
  `taken_at` date DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `include_in_report` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `training_activity_reports`
--

CREATE TABLE `training_activity_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED NOT NULL,
  `report_number` varchar(255) DEFAULT NULL,
  `background` longtext DEFAULT NULL,
  `legal_basis` longtext DEFAULT NULL,
  `objectives` longtext DEFAULT NULL,
  `implementation` longtext DEFAULT NULL,
  `achievements` longtext DEFAULT NULL,
  `constraints` longtext DEFAULT NULL,
  `follow_up` longtext DEFAULT NULL,
  `conclusion` longtext DEFAULT NULL,
  `recommendations` longtext DEFAULT NULL,
  `signatory_name` varchar(255) DEFAULT NULL,
  `signatory_nip` varchar(255) DEFAULT NULL,
  `signatory_position` varchar(255) DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  `template_path` varchar(255) DEFAULT NULL,
  `status` enum('draft','final') NOT NULL DEFAULT 'draft',
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `training_activity_report_versions`
--

CREATE TABLE `training_activity_report_versions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_activity_report_id` bigint(20) UNSIGNED NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `docx_path` varchar(255) DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`snapshot`)),
  `generated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `training_certificate_settings`
--

CREATE TABLE `training_certificate_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT 'Sertifikat Pelatihan',
  `template_path` varchar(255) DEFAULT NULL,
  `number_format` varchar(255) NOT NULL,
  `start_sequence` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `issued_at` date DEFAULT NULL,
  `photo_size` varchar(10) NOT NULL DEFAULT '3x4',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `training_execution_notes`
--

CREATE TABLE `training_execution_notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `note` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `role` enum('superadmin','admin_bidang','participant','pengajar','admin_aset','mitra','penandatangan','intern','pengelola_magang','resepsionis') NOT NULL,
  `user_type` varchar(30) DEFAULT NULL,
  `user_type_status` varchar(20) NOT NULL DEFAULT 'approved',
  `bidang` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `must_complete_profile` tinyint(1) NOT NULL DEFAULT 0,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `gender` enum('Laki-Laki','Perempuan') DEFAULT NULL,
  `birth_place` varchar(255) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `jabatan` varchar(255) DEFAULT NULL,
  `golongan` varchar(100) DEFAULT NULL,
  `instansi` varchar(255) DEFAULT NULL,
  `provinsi` varchar(255) DEFAULT NULL,
  `kota` varchar(255) DEFAULT NULL,
  `kecamatan` varchar(255) DEFAULT NULL,
  `kelurahan` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `status_kepegawaian` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `google_id`, `avatar`, `name`, `username`, `nip_nik`, `whatsapp`, `profile_photo`, `role`, `user_type`, `user_type_status`, `bidang`, `password`, `must_complete_profile`, `must_change_password`, `remember_token`, `created_at`, `updated_at`, `gender`, `birth_place`, `birth_date`, `jabatan`, `golongan`, `instansi`, `provinsi`, `kota`, `kecamatan`, `kelurahan`, `address`, `latitude`, `longitude`, `status_kepegawaian`) VALUES
(1, '107781747552867366947', 'https://lh3.googleusercontent.com/a/ACg8ocJTdYYDR-py4kqvc2uXIM_JX56X0cang30ysQGyyWB23sdF2Q=s96-c', 'Simpan Aku aja 22', 'simpanakuajaduadua@gmail.com', '12387126387126438', '081382830814', NULL, 'participant', 'peserta', 'approved', NULL, '$2y$12$HvkVkDVodkLIaQs6RniJ9.gPBvc7A8xecTEB12IXr1kuezKe.V1fq', 0, 0, NULL, '2026-08-28 04:30:55', '2026-08-28 14:43:37', 'Laki-Laki', NULL, NULL, 'Pengelola Layanan', NULL, 'jdfsgfjsdbfsdjfsdf', 'JAWA BARAT', 'KABUPATEN BANDUNG BARAT', 'PARONGPONG', 'CIGUGUR GIRANG', NULL, -6.8335548, 107.5854874, 'PNS'),
(2, NULL, NULL, 'Super Administrator', 'superadmin@bpsdm.go.id', '19450817000000', '6281234567890', 'avatars/TF8Li4BqNhflk57ZhBXfkP5zRboFs4kS0OmSpwil.png', 'superadmin', NULL, 'approved', NULL, '$2y$12$82TUizUKE.owZ2/L0KDDg.7e.UydaeeVnpdqKnjkcqgV2KRqkKuym', 0, 0, NULL, '2026-08-28 06:19:21', '2026-09-13 07:49:39', 'Laki-Laki', NULL, NULL, 'Kepala Bpsdm Jabar', NULL, 'BPSDM Jabar', 'JAWA BARAT', 'KOTA CIMAHI', 'CIMAHI UTARA', 'CIPAGERAN', NULL, -6.8494402, 107.5469204, 'PNS'),
(4, '118339399391382672190', 'https://lh3.googleusercontent.com/a/ACg8ocJWem1Q3SnD_OF1CZI77YKZA_yxmXI7nkEf8tHs-xnvfPkYNg=s96-c', 'simpanakuaja delapan', 'simpanakuajadelapan@gmail.com', '3542312431523123', '989364374327', NULL, 'pengajar', 'narasumber', 'approved', NULL, '$2y$12$J4lpZbI2DwCPMgmoz.1dzekWiWnRaPI3L62Q9aECyZT6zLBhggQzy', 0, 0, NULL, '2026-08-28 12:02:38', '2026-09-01 14:44:56', 'Laki-Laki', NULL, NULL, 'asdasfasfas', NULL, 'afasasfasfasfasf', 'DKI JAKARTA', 'KOTA JAKARTA PUSAT', 'SENEN', 'PASEBAN', NULL, -6.1929872, 106.8515287, 'PNS'),
(5, NULL, NULL, 'Ali Ridwan', 'bidangpktu@bpsdm.go.id', NULL, '08123456789', NULL, 'admin_bidang', NULL, 'approved', 'Bidang Pengembangan Kompetensi Teknis Umum', '$2y$12$YPxZ1PjL0nlyZ5xYpyDlpO/QybSb.V1hYbezpk8WV1dARs/x7vtsi', 0, 0, NULL, '2026-08-28 12:49:55', '2026-08-28 13:30:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, NULL, NULL, 'Ghani', 'aset@bpsdm.go.id', NULL, '08123456789', NULL, 'admin_aset', NULL, 'approved', 'Pengelola Aset', '$2y$12$1/1IVk5j2D/c5zf527T2a.RPbGf9Bu9e1AWOkVSsIcgNZ1dxS6K9i', 0, 0, NULL, '2026-08-29 14:38:46', '2026-08-29 14:38:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, '107913421320469114225', 'https://lh3.googleusercontent.com/a/ACg8ocLwmh1zFGix6WeyXU2zAzUMuDTawdX04V6cZhIgBrCbA9WmRA=s96-c', 'Simpan Aku', 'simpanakuaja@gmail.com', '34554834689342342', '0988653845345', NULL, 'participant', 'peserta', 'approved', NULL, '$2y$12$QV8zTZcMbwc/SxFgVzTYeuWDWYvhzYQnQ.3jcCbZQzmEYQJ.K5A06', 0, 0, NULL, '2026-08-30 05:13:44', '2026-08-30 05:29:27', 'Perempuan', NULL, NULL, 'GURU AHLI PERTAMA', NULL, 'Bpsdm Jabar', 'JAWA BARAT', 'KOTA BANDUNG', 'ARCAMANIK', 'CISARANTEN KULON', NULL, -6.9338798, 107.6823923, 'PPPK'),
(12, NULL, NULL, 'Rizky Adia Mukti', 'skpk@bpsdm.go.id', NULL, '6281382830814', NULL, 'admin_bidang', NULL, 'approved', 'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan', '$2y$12$kxvFfhIPB4OljwYOXBTOheha3eWnpcmLMwY/skCJxZDnmok5upGcO', 0, 0, NULL, '2026-08-30 09:34:20', '2026-08-30 09:34:20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, NULL, NULL, 'IMAN NOERMANA', 'bidangpkti@bpsdm.go.id', NULL, '6281382830814', NULL, 'admin_bidang', NULL, 'approved', 'Bidang Pengembangan Kompetensi Teknis Inti', '$2y$12$lcWJXnQoBoXxqKCQtXwtyu/.2vwcXPoY7z54YyExt4JQeefiJpWoO', 0, 0, NULL, '2026-08-30 23:52:17', '2026-08-30 23:52:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, NULL, NULL, 'Riswandi', 'bidangpkm@bpsdm.go.id', NULL, '6281382830814', NULL, 'admin_bidang', NULL, 'approved', 'Bidang Pengembangan Kompetensi Manajerial', '$2y$12$FhYT4QbCVf8L0PQwePC8seENol4Vsuvkj3YaHGH2F4jPsekqgUOGq', 0, 0, NULL, '2026-08-30 23:52:43', '2026-08-30 23:52:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, NULL, NULL, 'Kabupaten', 'kabupaten', '3201125305870003', '6281382830814', NULL, 'mitra', 'mitra', 'approved', NULL, '$2y$12$qDWvuQDVTrjE7IM9ei0LYupJi5.tCiP9INKYaeq1clTkGd.OLfJyK', 0, 0, NULL, '2026-08-31 07:09:45', '2026-09-12 13:11:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, '117918344361205538591', 'https://lh3.googleusercontent.com/a/ACg8ocL7HSK9iiFxOXKcrxJapyRX4AY-g37wIcx801FPfDd0ivomDg=s96-c', 'simpan aja aku 6', 'simpanakuajaenam@gmail.com', '3224235235235235', '8656565626333', NULL, 'pengajar', 'narasumber', 'approved', NULL, '$2y$12$CSMgGuQ76pirg0mVSc80wOiw50jB1euboqOuHJV484ocDNByMKGVy', 0, 0, NULL, '2026-09-01 15:12:43', '2026-09-01 15:14:09', 'Laki-Laki', NULL, NULL, 'fsdfsdfsdfsdf', NULL, 'faswefasfasf', 'SUMATERA BARAT', 'KABUPATEN TANAH DATAR', 'BATIPUH', 'GUNUNG RAJO', NULL, -3.3569196, 122.8328450, 'PPPK'),
(18, NULL, NULL, 'Samsidin, A.Md.Kom.', '3202450303950001', '3202450303950001', '086654234', 'avatars/v7fsqOyeoo8YyVwKtN5CnrPLxFY2ezO4xJrSM4wB.png', 'penandatangan', NULL, 'approved', NULL, '$2y$12$blmDzUv97YejsCf.DbB5c..eETYj8tyvxXVvjusxj4Z4irv6l8QsO', 0, 0, NULL, '2026-09-10 13:22:21', '2026-09-12 06:42:24', 'Laki-Laki', NULL, NULL, 'Pengelolaa Layanan Operasional', NULL, 'Bpsdm Jabar', 'JAWA BARAT', 'KABUPATEN BANDUNG BARAT', 'PARONGPONG', 'CIGUGUR GIRANG', NULL, NULL, NULL, 'PNS'),
(19, NULL, NULL, 'SAMSIDIN', '123456', NULL, NULL, NULL, 'intern', 'internship', 'approved', 'Bidang SKPK', '$2y$12$a6MzTi6cw54DV4KnfWboH.OlS4IOrfP6BKMW5oPyblI0O4rn6ttK.', 0, 0, NULL, '2026-09-12 15:21:42', '2026-09-12 15:25:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, NULL, NULL, 'Saepuloh', '1234567890', NULL, NULL, NULL, 'intern', 'internship', 'approved', 'Bidang SKPK', '$2y$12$RlCfNjXJNraxsbulX6M7luNxWIvcBTZdzmPj9FWqgc/hZdoQk9sKK', 0, 0, NULL, '2026-09-13 06:21:02', '2026-09-13 06:23:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, '115482024455871232654', 'https://lh3.googleusercontent.com/a/ACg8ocJv9FWX3pw175ExmRwBHW53DHh-_9dp64IljqBNAxRCsDFYdPo=s96-c', 'Sem Syamsidin', 'semsyamsidin.sem@gmail.com', '3202450303950002', '08123456789', NULL, 'participant', 'peserta', 'approved', NULL, '$2y$12$cmNRY0vYnL1bkr4P0g4Se.4WgQhpKdkt.kk7WE0L1aI.3eaGSJb2W', 0, 0, NULL, '2026-09-13 08:07:43', '2026-09-13 08:43:19', 'Laki-Laki', 'Sukabumi', '1995-03-03', 'Pengelolaa', 'III/a', 'Badan Pengembangan Sumber Daya Manusia', 'JAWA BARAT', 'KABUPATEN BANDUNG BARAT', 'PARONGPONG', 'CIGUGUR GIRANG', 'Jalan Ciwaruga, Panyairanjompo, Parongpong, Bandung Barat, Jawa Barat, 40559, Indonesia', -6.8192959, 107.5866061, 'PNS'),
(23, NULL, NULL, 'Contoh Nama Peserta', '199503032024011001', '199503032024011001', NULL, NULL, 'participant', 'peserta', 'approved', NULL, '$2y$12$5N4jzdq84PinjF4lkIL3xuPbs9FWkwoDzN1onn49IAbUHH3wbck..', 1, 1, NULL, '2026-09-14 04:48:17', '2026-09-14 04:48:17', NULL, NULL, NULL, NULL, NULL, 'BPSDM Provinsi Jawa Barat', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `activity_attendance_answers`
--
ALTER TABLE `activity_attendance_answers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `activity_answers_response_question_unique` (`activity_attendance_response_id`,`activity_attendance_question_id`),
  ADD KEY `aa_answers_question_fk` (`activity_attendance_question_id`);

--
-- Indeks untuk tabel `activity_attendance_forms`
--
ALTER TABLE `activity_attendance_forms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `activity_attendance_forms_public_token_unique` (`public_token`),
  ADD KEY `activity_attendance_forms_created_by_foreign` (`created_by`),
  ADD KEY `activity_attendance_forms_bidang_index` (`bidang`),
  ADD KEY `activity_attendance_forms_status_index` (`status`);

--
-- Indeks untuk tabel `activity_attendance_questions`
--
ALTER TABLE `activity_attendance_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_questions_form_sort_idx` (`activity_attendance_form_id`,`sort_order`);

--
-- Indeks untuk tabel `activity_attendance_responses`
--
ALTER TABLE `activity_attendance_responses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `activity_attendance_responses_response_token_unique` (`response_token`),
  ADD KEY `activity_responses_form_date_idx` (`activity_attendance_form_id`,`submitted_at`);

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
-- Indeks untuk tabel `ai_generations`
--
ALTER TABLE `ai_generations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ai_generations_training_id_foreign` (`training_id`),
  ADD KEY `ai_generations_user_id_foreign` (`user_id`),
  ADD KEY `ai_generations_source_hash_index` (`source_hash`);

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
-- Indeks untuk tabel `asset_loan_requests`
--
ALTER TABLE `asset_loan_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `asset_loan_requests_requestable_type_requestable_id_unique` (`requestable_type`,`requestable_id`),
  ADD KEY `asset_loan_requests_requestable_type_requestable_id_index` (`requestable_type`,`requestable_id`),
  ADD KEY `asset_loan_requests_submitted_by_foreign` (`submitted_by`),
  ADD KEY `asset_loan_requests_reviewed_by_foreign` (`reviewed_by`);

--
-- Indeks untuk tabel `asset_public_reservations`
--
ALTER TABLE `asset_public_reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `apr_public_token_uq` (`public_token`),
  ADD UNIQUE KEY `apr_booking_code_uq` (`booking_code`),
  ADD KEY `asset_public_reservations_reviewed_by_foreign` (`reviewed_by`),
  ADD KEY `apr_asset_schedule_idx` (`asset_id`,`rental_date`,`start_time`,`end_time`),
  ADD KEY `apr_status_created_idx` (`status`,`created_at`);

--
-- Indeks untuk tabel `asset_rental_rates`
--
ALTER TABLE `asset_rental_rates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asset_rates_time_idx` (`asset_id`,`start_time`,`end_time`);

--
-- Indeks untuk tabel `asset_rental_settings`
--
ALTER TABLE `asset_rental_settings`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `certification_participants_biodata_file_id_foreign` (`biodata_file_id`),
  ADD KEY `certification_participants_certificate_file_id_foreign` (`certificate_file_id`);

--
-- Indeks untuk tabel `certification_types`
--
ALTER TABLE `certification_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certification_types_name_unique` (`name`);

--
-- Indeks untuk tabel `electronic_signature_actions`
--
ALTER TABLE `electronic_signature_actions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `es_document_actor_unique` (`electronic_signature_document_id`,`electronic_signature_actor_id`),
  ADD KEY `es_action_actor_fk` (`electronic_signature_actor_id`);

--
-- Indeks untuk tabel `electronic_signature_actors`
--
ALTER TABLE `electronic_signature_actors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `es_actor_sequence_unique` (`electronic_signature_request_id`,`sequence`),
  ADD UNIQUE KEY `es_actor_user_unique` (`electronic_signature_request_id`,`user_id`),
  ADD KEY `es_actor_user_fk` (`user_id`);

--
-- Indeks untuk tabel `electronic_signature_attempts`
--
ALTER TABLE `electronic_signature_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `es_attempt_user_fk` (`user_id`),
  ADD KEY `es_attempt_action_index` (`electronic_signature_action_id`,`created_at`);

--
-- Indeks untuk tabel `electronic_signature_documents`
--
ALTER TABLE `electronic_signature_documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `electronic_signature_documents_verification_token_unique` (`verification_token`),
  ADD KEY `es_doc_request_fk` (`electronic_signature_request_id`),
  ADD KEY `es_doc_participant_cert_idx` (`participant_certificate_id`),
  ADD KEY `electronic_signature_documents_internship_participant_id_foreign` (`internship_participant_id`);

--
-- Indeks untuk tabel `electronic_signature_requests`
--
ALTER TABLE `electronic_signature_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `electronic_signature_requests_uuid_unique` (`uuid`),
  ADD KEY `es_request_creator_fk` (`created_by`),
  ADD KEY `electronic_signature_requests_bidang_status_index` (`bidang`,`status`),
  ADD KEY `electronic_signature_requests_training_id_foreign` (`training_id`),
  ADD KEY `electronic_signature_requests_source_type_index` (`source_type`);

--
-- Indeks untuk tabel `evaluation_forms`
--
ALTER TABLE `evaluation_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluation_forms_training_id_foreign` (`training_id`),
  ADD KEY `evaluation_forms_schedule_id_foreign` (`schedule_id`);

--
-- Indeks untuk tabel `evaluation_l1_text_summaries`
--
ALTER TABLE `evaluation_l1_text_summaries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `evaluation_l1_text_summaries_training_id_unique` (`training_id`),
  ADD KEY `evaluation_l1_text_summaries_reviewed_by_foreign` (`reviewed_by`);

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
  ADD KEY `folders_user_id_foreign` (`user_id`),
  ADD KEY `folders_training_id_foreign` (`training_id`),
  ADD KEY `folders_archived_by_foreign` (`archived_by`),
  ADD KEY `folders_archive_index` (`parent_id`,`document_year`,`is_archived`);

--
-- Indeks untuk tabel `folder_user_permissions`
--
ALTER TABLE `folder_user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folder_user_permissions_folder_id_user_id_unique` (`folder_id`,`user_id`),
  ADD KEY `folder_user_permissions_shared_by_foreign` (`shared_by`),
  ADD KEY `folder_user_permissions_user_id_permission_index` (`user_id`,`permission`);

--
-- Indeks untuk tabel `guest_book_locations`
--
ALTER TABLE `guest_book_locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guest_book_locations_public_token_unique` (`public_token`),
  ADD KEY `guest_book_locations_created_by_foreign` (`created_by`);

--
-- Indeks untuk tabel `guest_visits`
--
ALTER TABLE `guest_visits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guest_visits_visit_code_unique` (`visit_code`),
  ADD KEY `guest_visits_guest_book_location_id_checked_in_at_index` (`guest_book_location_id`,`checked_in_at`),
  ADD KEY `guest_visits_target_bidang_index` (`target_bidang`);

--
-- Indeks untuk tabel `internship_attendances`
--
ALTER TABLE `internship_attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `intern_attendance_day_unique` (`internship_participant_id`,`attendance_date`),
  ADD KEY `internship_attendances_reviewed_by_foreign` (`reviewed_by`);

--
-- Indeks untuk tabel `internship_participants`
--
ALTER TABLE `internship_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `intern_program_student_unique` (`internship_program_id`,`student_number`),
  ADD KEY `internship_participants_user_id_foreign` (`user_id`),
  ADD KEY `internship_participants_reviewed_by_foreign` (`reviewed_by`),
  ADD KEY `internship_participants_certificate_sent_by_foreign` (`certificate_sent_by`);

--
-- Indeks untuk tabel `internship_programs`
--
ALTER TABLE `internship_programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `internship_programs_public_token_unique` (`public_token`),
  ADD KEY `internship_programs_created_by_foreign` (`created_by`),
  ADD KEY `internship_programs_certificate_signer_id_foreign` (`certificate_signer_id`),
  ADD KEY `internship_programs_manager_id_foreign` (`manager_id`);

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
-- Indeks untuk tabel `login_help_settings`
--
ALTER TABLE `login_help_settings`
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
-- Indeks untuk tabel `notification_reads`
--
ALTER TABLE `notification_reads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `notification_reads_user_id_notification_key_unique` (`user_id`,`notification_key`);

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
-- Indeks untuk tabel `participant_certificates`
--
ALTER TABLE `participant_certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `participant_certificates_training_id_participant_id_unique` (`training_id`,`participant_id`),
  ADD UNIQUE KEY `participant_certificates_certificate_number_unique` (`certificate_number`),
  ADD KEY `participant_certificates_training_certificate_setting_id_foreign` (`training_certificate_setting_id`),
  ADD KEY `participant_certificates_participant_id_foreign` (`participant_id`),
  ADD KEY `participant_certificates_uploaded_by_foreign` (`uploaded_by`),
  ADD KEY `participant_certificates_sent_by_foreign` (`sent_by`),
  ADD KEY `participant_certificates_training_sent_idx` (`training_id`,`sent_at`);

--
-- Indeks untuk tabel `partner_submissions`
--
ALTER TABLE `partner_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partner_submissions_user_id_foreign` (`user_id`),
  ADD KEY `partner_submissions_assigned_to_foreign` (`assigned_to`),
  ADD KEY `partner_submissions_folder_id_foreign` (`folder_id`),
  ADD KEY `partner_submissions_target_bidang_status_index` (`target_bidang`,`status`);

--
-- Indeks untuk tabel `partner_submission_comments`
--
ALTER TABLE `partner_submission_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partner_submission_comments_partner_submission_id_foreign` (`partner_submission_id`),
  ADD KEY `partner_submission_comments_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `partner_submission_documents`
--
ALTER TABLE `partner_submission_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partner_submission_documents_partner_submission_id_foreign` (`partner_submission_id`),
  ADD KEY `partner_submission_documents_uploaded_by_foreign` (`uploaded_by`);

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
-- Indeks untuk tabel `training_activity_documentations`
--
ALTER TABLE `training_activity_documentations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `training_activity_documentations_training_id_foreign` (`training_id`),
  ADD KEY `training_activity_documentations_uploaded_by_foreign` (`uploaded_by`);

--
-- Indeks untuk tabel `training_activity_reports`
--
ALTER TABLE `training_activity_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `training_activity_reports_training_id_unique` (`training_id`),
  ADD KEY `training_activity_reports_updated_by_foreign` (`updated_by`);

--
-- Indeks untuk tabel `training_activity_report_versions`
--
ALTER TABLE `training_activity_report_versions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tar_versions_report_version_uq` (`training_activity_report_id`,`version`),
  ADD KEY `tar_versions_user_fk` (`generated_by`);

--
-- Indeks untuk tabel `training_certificate_settings`
--
ALTER TABLE `training_certificate_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `training_certificate_settings_training_id_unique` (`training_id`),
  ADD KEY `training_certificate_settings_created_by_foreign` (`created_by`);

--
-- Indeks untuk tabel `training_execution_notes`
--
ALTER TABLE `training_execution_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `training_execution_notes_created_by_foreign` (`created_by`),
  ADD KEY `training_execution_notes_training_id_created_at_index` (`training_id`,`created_at`);

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
-- AUTO_INCREMENT untuk tabel `activity_attendance_answers`
--
ALTER TABLE `activity_attendance_answers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `activity_attendance_forms`
--
ALTER TABLE `activity_attendance_forms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `activity_attendance_questions`
--
ALTER TABLE `activity_attendance_questions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `activity_attendance_responses`
--
ALTER TABLE `activity_attendance_responses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT untuk tabel `agendas`
--
ALTER TABLE `agendas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `agenda_schedules`
--
ALTER TABLE `agenda_schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `ai_generations`
--
ALTER TABLE `ai_generations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `alumni_profiles`
--
ALTER TABLE `alumni_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `assets`
--
ALTER TABLE `assets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT untuk tabel `asset_bookings`
--
ALTER TABLE `asset_bookings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT untuk tabel `asset_images`
--
ALTER TABLE `asset_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=146;

--
-- AUTO_INCREMENT untuk tabel `asset_loan_requests`
--
ALTER TABLE `asset_loan_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `asset_public_reservations`
--
ALTER TABLE `asset_public_reservations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `asset_rental_rates`
--
ALTER TABLE `asset_rental_rates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `asset_rental_settings`
--
ALTER TABLE `asset_rental_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `certification_events`
--
ALTER TABLE `certification_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `certification_participants`
--
ALTER TABLE `certification_participants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `certification_types`
--
ALTER TABLE `certification_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `electronic_signature_actions`
--
ALTER TABLE `electronic_signature_actions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `electronic_signature_actors`
--
ALTER TABLE `electronic_signature_actors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `electronic_signature_attempts`
--
ALTER TABLE `electronic_signature_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `electronic_signature_documents`
--
ALTER TABLE `electronic_signature_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `electronic_signature_requests`
--
ALTER TABLE `electronic_signature_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `evaluation_forms`
--
ALTER TABLE `evaluation_forms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `evaluation_l1_text_summaries`
--
ALTER TABLE `evaluation_l1_text_summaries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `evaluation_questions`
--
ALTER TABLE `evaluation_questions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2027;

--
-- AUTO_INCREMENT untuk tabel `evaluation_results_l1`
--
ALTER TABLE `evaluation_results_l1`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT untuk tabel `evaluation_results_l2`
--
ALTER TABLE `evaluation_results_l2`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `evaluation_results_l34`
--
ALTER TABLE `evaluation_results_l34`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `files`
--
ALTER TABLE `files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190;

--
-- AUTO_INCREMENT untuk tabel `file_versions`
--
ALTER TABLE `file_versions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `folders`
--
ALTER TABLE `folders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT untuk tabel `folder_user_permissions`
--
ALTER TABLE `folder_user_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `guest_book_locations`
--
ALTER TABLE `guest_book_locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `guest_visits`
--
ALTER TABLE `guest_visits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `internship_attendances`
--
ALTER TABLE `internship_attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `internship_participants`
--
ALTER TABLE `internship_participants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `internship_programs`
--
ALTER TABLE `internship_programs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `login_help_settings`
--
ALTER TABLE `login_help_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

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
-- AUTO_INCREMENT untuk tabel `notification_reads`
--
ALTER TABLE `notification_reads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `participants`
--
ALTER TABLE `participants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT untuk tabel `participant_certificates`
--
ALTER TABLE `participant_certificates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `partner_submissions`
--
ALTER TABLE `partner_submissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `partner_submission_comments`
--
ALTER TABLE `partner_submission_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `partner_submission_documents`
--
ALTER TABLE `partner_submission_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pengajars`
--
ALTER TABLE `pengajars`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT untuk tabel `trainings`
--
ALTER TABLE `trainings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `training_activity_documentations`
--
ALTER TABLE `training_activity_documentations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `training_activity_reports`
--
ALTER TABLE `training_activity_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `training_activity_report_versions`
--
ALTER TABLE `training_activity_report_versions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `training_certificate_settings`
--
ALTER TABLE `training_certificate_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `training_execution_notes`
--
ALTER TABLE `training_execution_notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `training_forum_reads`
--
ALTER TABLE `training_forum_reads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `training_messages`
--
ALTER TABLE `training_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `training_stages`
--
ALTER TABLE `training_stages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `activity_attendance_answers`
--
ALTER TABLE `activity_attendance_answers`
  ADD CONSTRAINT `aa_answers_question_fk` FOREIGN KEY (`activity_attendance_question_id`) REFERENCES `activity_attendance_questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `aa_answers_response_fk` FOREIGN KEY (`activity_attendance_response_id`) REFERENCES `activity_attendance_responses` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `activity_attendance_forms`
--
ALTER TABLE `activity_attendance_forms`
  ADD CONSTRAINT `activity_attendance_forms_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `activity_attendance_questions`
--
ALTER TABLE `activity_attendance_questions`
  ADD CONSTRAINT `aa_questions_form_fk` FOREIGN KEY (`activity_attendance_form_id`) REFERENCES `activity_attendance_forms` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `activity_attendance_responses`
--
ALTER TABLE `activity_attendance_responses`
  ADD CONSTRAINT `aa_responses_form_fk` FOREIGN KEY (`activity_attendance_form_id`) REFERENCES `activity_attendance_forms` (`id`) ON DELETE CASCADE;

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
-- Ketidakleluasaan untuk tabel `ai_generations`
--
ALTER TABLE `ai_generations`
  ADD CONSTRAINT `ai_generations_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_generations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
-- Ketidakleluasaan untuk tabel `asset_loan_requests`
--
ALTER TABLE `asset_loan_requests`
  ADD CONSTRAINT `asset_loan_requests_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `asset_loan_requests_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `asset_public_reservations`
--
ALTER TABLE `asset_public_reservations`
  ADD CONSTRAINT `asset_public_reservations_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `asset_public_reservations_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `asset_rental_rates`
--
ALTER TABLE `asset_rental_rates`
  ADD CONSTRAINT `asset_rental_rates_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `certification_participants_certificate_file_id_foreign` FOREIGN KEY (`certificate_file_id`) REFERENCES `files` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `certification_participants_certification_event_id_foreign` FOREIGN KEY (`certification_event_id`) REFERENCES `certification_events` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `electronic_signature_actions`
--
ALTER TABLE `electronic_signature_actions`
  ADD CONSTRAINT `es_action_actor_fk` FOREIGN KEY (`electronic_signature_actor_id`) REFERENCES `electronic_signature_actors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `es_action_document_fk` FOREIGN KEY (`electronic_signature_document_id`) REFERENCES `electronic_signature_documents` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `electronic_signature_actors`
--
ALTER TABLE `electronic_signature_actors`
  ADD CONSTRAINT `es_actor_request_fk` FOREIGN KEY (`electronic_signature_request_id`) REFERENCES `electronic_signature_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `es_actor_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `electronic_signature_attempts`
--
ALTER TABLE `electronic_signature_attempts`
  ADD CONSTRAINT `es_attempt_action_fk` FOREIGN KEY (`electronic_signature_action_id`) REFERENCES `electronic_signature_actions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `es_attempt_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `electronic_signature_documents`
--
ALTER TABLE `electronic_signature_documents`
  ADD CONSTRAINT `electronic_signature_documents_internship_participant_id_foreign` FOREIGN KEY (`internship_participant_id`) REFERENCES `internship_participants` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `es_doc_participant_cert_fk` FOREIGN KEY (`participant_certificate_id`) REFERENCES `participant_certificates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `es_doc_request_fk` FOREIGN KEY (`electronic_signature_request_id`) REFERENCES `electronic_signature_requests` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `electronic_signature_requests`
--
ALTER TABLE `electronic_signature_requests`
  ADD CONSTRAINT `electronic_signature_requests_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `es_request_creator_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `evaluation_forms`
--
ALTER TABLE `evaluation_forms`
  ADD CONSTRAINT `evaluation_forms_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `evaluation_forms_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `evaluation_l1_text_summaries`
--
ALTER TABLE `evaluation_l1_text_summaries`
  ADD CONSTRAINT `evaluation_l1_text_summaries_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `evaluation_l1_text_summaries_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `folders_archived_by_foreign` FOREIGN KEY (`archived_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
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
-- Ketidakleluasaan untuk tabel `guest_book_locations`
--
ALTER TABLE `guest_book_locations`
  ADD CONSTRAINT `guest_book_locations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `guest_visits`
--
ALTER TABLE `guest_visits`
  ADD CONSTRAINT `guest_visits_guest_book_location_id_foreign` FOREIGN KEY (`guest_book_location_id`) REFERENCES `guest_book_locations` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `internship_attendances`
--
ALTER TABLE `internship_attendances`
  ADD CONSTRAINT `internship_attendances_internship_participant_id_foreign` FOREIGN KEY (`internship_participant_id`) REFERENCES `internship_participants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `internship_attendances_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `internship_participants`
--
ALTER TABLE `internship_participants`
  ADD CONSTRAINT `internship_participants_certificate_sent_by_foreign` FOREIGN KEY (`certificate_sent_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `internship_participants_internship_program_id_foreign` FOREIGN KEY (`internship_program_id`) REFERENCES `internship_programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `internship_participants_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `internship_participants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `internship_programs`
--
ALTER TABLE `internship_programs`
  ADD CONSTRAINT `internship_programs_certificate_signer_id_foreign` FOREIGN KEY (`certificate_signer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `internship_programs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `internship_programs_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
-- Ketidakleluasaan untuk tabel `notification_reads`
--
ALTER TABLE `notification_reads`
  ADD CONSTRAINT `notification_reads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Ketidakleluasaan untuk tabel `participant_certificates`
--
ALTER TABLE `participant_certificates`
  ADD CONSTRAINT `participant_certificates_participant_id_foreign` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `participant_certificates_sent_by_foreign` FOREIGN KEY (`sent_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `participant_certificates_training_certificate_setting_id_foreign` FOREIGN KEY (`training_certificate_setting_id`) REFERENCES `training_certificate_settings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `participant_certificates_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `participant_certificates_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `partner_submissions`
--
ALTER TABLE `partner_submissions`
  ADD CONSTRAINT `partner_submissions_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `partner_submissions_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `partner_submissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `partner_submission_comments`
--
ALTER TABLE `partner_submission_comments`
  ADD CONSTRAINT `partner_submission_comments_partner_submission_id_foreign` FOREIGN KEY (`partner_submission_id`) REFERENCES `partner_submissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `partner_submission_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `partner_submission_documents`
--
ALTER TABLE `partner_submission_documents`
  ADD CONSTRAINT `partner_submission_documents_partner_submission_id_foreign` FOREIGN KEY (`partner_submission_id`) REFERENCES `partner_submissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `partner_submission_documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Ketidakleluasaan untuk tabel `training_activity_documentations`
--
ALTER TABLE `training_activity_documentations`
  ADD CONSTRAINT `training_activity_documentations_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `training_activity_documentations_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `training_activity_reports`
--
ALTER TABLE `training_activity_reports`
  ADD CONSTRAINT `training_activity_reports_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `training_activity_reports_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `training_activity_report_versions`
--
ALTER TABLE `training_activity_report_versions`
  ADD CONSTRAINT `tar_versions_report_fk` FOREIGN KEY (`training_activity_report_id`) REFERENCES `training_activity_reports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tar_versions_user_fk` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `training_certificate_settings`
--
ALTER TABLE `training_certificate_settings`
  ADD CONSTRAINT `training_certificate_settings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `training_certificate_settings_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `training_execution_notes`
--
ALTER TABLE `training_execution_notes`
  ADD CONSTRAINT `training_execution_notes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `training_execution_notes_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE;

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
