-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 21 Jul 2026 pada 11.07
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
-- Struktur dari tabel `alumni_profiles`
--

CREATE TABLE `alumni_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `participant_id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED NOT NULL,
  `edu_during_training` varchar(255) DEFAULT NULL,
  `edu_current` varchar(255) DEFAULT NULL,
  `rank_during_training` varchar(255) DEFAULT NULL,
  `rank_current` varchar(255) DEFAULT NULL,
  `pos_during_training` varchar(255) DEFAULT NULL,
  `pos_current` varchar(255) DEFAULT NULL,
  `unit_during_training` varchar(255) DEFAULT NULL,
  `unit_current` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `alumni_profiles`
--

INSERT INTO `alumni_profiles` (`id`, `participant_id`, `training_id`, `edu_during_training`, `edu_current`, `rank_during_training`, `rank_current`, `pos_during_training`, `pos_current`, `unit_during_training`, `unit_current`, `created_at`, `updated_at`) VALUES
(4, 2, 2, 'SD/SMP', 'SD/SMP', 'I/a', 'I/a', 'dasd', 'dsadas', 'dasd', 'asdasdasd', '2026-07-21 02:06:35', '2026-07-21 02:06:35');

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

-- --------------------------------------------------------

--
-- Struktur dari tabel `evaluation_questions`
--

CREATE TABLE `evaluation_questions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_type` varchar(255) DEFAULT NULL,
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

INSERT INTO `evaluation_questions` (`id`, `training_type`, `training_id`, `category`, `metode`, `sub_category`, `question_text`, `type`, `options`, `created_at`, `updated_at`) VALUES
(2, 'Semua', NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Penyelenggara memiliki sertifikat MOT/TOC', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(3, 'Semua', NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Penyelenggara memiliki SP sebagai panitia', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(4, 'Semua', NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Ketersediaan pengelola kelas (pengamat dan petugas kelas)', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(5, 'Semua', NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Lembar biodata peserta', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(6, 'Semua', NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Daftar Hadir Peserta dan fasilitator', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(7, 'Semua', NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Tanda pengenal peserta', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(8, 'Semua', NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Penyiapan Sertifikat Pelatihan', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(9, 'Semua', NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Instrumen dan format pelatihan', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(10, 'Semua', NULL, 'Monitoring Penyelenggara', 'klasikal', NULL, 'Administrasi pelatihan', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(11, 'Semua', NULL, 'Monitoring Peserta', 'klasikal', NULL, 'Kualifikasi peserta sesuai persyaratan', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(12, 'Semua', NULL, 'Monitoring Peserta', 'klasikal', NULL, 'Jumlah peserta sesuai persyaratan', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(13, 'Semua', NULL, 'Monitoring Peserta', 'klasikal', NULL, 'Peserta terdaftar di BPSDM Jabar', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(14, 'Semua', NULL, 'Monitoring Peserta', 'klasikal', NULL, 'Peserta hadir minimal 85%', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(15, 'Semua', NULL, 'Monitoring Peserta', 'klasikal', NULL, 'Peserta aktif mengikuti pelatihan', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(16, 'Semua', NULL, 'Monitoring Peserta', 'klasikal', NULL, 'Peserta mentaati tata tertib', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(17, 'Semua', NULL, 'Monitoring Peserta', 'klasikal', NULL, 'Peserta mengikuti evaluasi', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(18, 'Semua', NULL, 'Monitoring Tenaga Kediklatan', 'klasikal', NULL, 'Menjaga etika dalam penyelenggaraan pelatihan', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(19, 'Semua', NULL, 'Monitoring Tenaga Kediklatan', 'klasikal', NULL, 'Berkoordinasi dengan fasilitator', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(20, 'Semua', NULL, 'Monitoring Tenaga Kediklatan', 'klasikal', NULL, 'Menyampaikan panduan pelatihan kepada peserta', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(21, 'Semua', NULL, 'Monitoring Tenaga Kediklatan', 'klasikal', NULL, 'Menjaga ketepatan waktu', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(22, 'Semua', NULL, 'Monitoring Tenaga Kediklatan', 'klasikal', NULL, 'Memiliki sertifikat TOT atau Workshop', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(23, 'Semua', NULL, 'Monitoring Tenaga Kediklatan', 'klasikal', NULL, 'Terdaftar di BPSDM Jabar', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(24, 'Semua', NULL, 'Monitoring Tenaga Kediklatan', 'klasikal', NULL, 'Mengarsipkan dokumen pelatihan untuk laporan pelaksanaan', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(25, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'klasikal', NULL, 'Sarana: Kursi dan meja belajar', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(26, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'klasikal', NULL, 'Sarana: Proyektor dan layar', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(27, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'klasikal', NULL, 'Sarana: Jaringan internet (LAN/WAN)', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(28, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'klasikal', NULL, 'Sarana: Pengeras Suara', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(29, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'klasikal', NULL, 'Sarana: Perlengkapan P3K', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(30, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'klasikal', NULL, 'Prasarana: Ruang Kelas', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(31, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'klasikal', NULL, 'Prasarana: Ruang Ibadah', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(32, 'Semua', NULL, 'Monitoring Penyelenggara', 'full learning', NULL, 'Penyelenggara memiliki Surat Perintah (SP) / SK sebagai panitia pelatihan', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(33, 'Semua', NULL, 'Monitoring Penyelenggara', 'full learning', NULL, 'Pengelola Kelas yang memiliki sertifikat kompetensi (MOT/TOC/Workshop sejenis)', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(34, 'Semua', NULL, 'Monitoring Penyelenggara', 'full learning', NULL, 'Penyelenggara memiliki kompetensi IT minimal', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(35, 'Semua', NULL, 'Monitoring Penyelenggara', 'full learning', NULL, 'Tersedia Administrator/Host yang kompeten mengelola jalannya kelas virtual (admit peserta, mute/unmute, share screen)', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(36, 'Semua', NULL, 'Monitoring Penyelenggara', 'full learning', NULL, 'Tersedia petugas yang memantau dan mendokumentasikan kehadiran peserta dan fasilitator di setiap sesi.', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(37, 'Semua', NULL, 'Monitoring Penyelenggara', 'full learning', NULL, 'Tersedia penyelenggara yang menyiapkan, melaksanakan, dan mengolah hasil evaluasi pelatihan (Pre-test, Post-test, dan Evaluasi Penyelenggaraan).', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(38, 'Semua', NULL, 'Monitoring Peserta', 'full learning', NULL, 'Kualifikasi peserta yang hadir sesuai dengan persyaratan pelatihan', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(39, 'Semua', NULL, 'Monitoring Peserta', 'full learning', NULL, 'Jumlah peserta sesuai dengan kuota yang telah ditetapkan', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(40, 'Semua', NULL, 'Monitoring Peserta', 'full learning', NULL, 'Peserta telah memiliki akun yang terdaftar dan aktif di dalam Learning Management System (LMS)', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(41, 'Semua', NULL, 'Monitoring Peserta', 'full learning', NULL, 'Peserta memenuhi syarat kehadiran minimal 85% dari total Jam Pelajaran (JP)', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(42, 'Semua', NULL, 'Monitoring Peserta', 'full learning', NULL, 'Peserta mematuhi tata tertib kelas daring (misal: mengaktifkan kamera, mute mikrofon saat tidak berbicara, menggunakan virtual background)', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(43, 'Semua', NULL, 'Monitoring Peserta', 'full learning', NULL, 'Peserta mengikuti dan menyelesaikan seluruh rangkaian evaluasi/penugasan di LMS.', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(44, 'Semua', NULL, 'Monitoring Peserta', 'full learning', NULL, 'Peserta aktif berpartisipasi dalam diskusi kelompok atau tanya jawab', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(45, 'Semua', NULL, 'Monitoring Tenaga Kediklatan', 'full learning', NULL, 'Fasilitator memiliki sertifikat TOT, sertifikat keahlian, atau kompetensi teknis mengajar yang relevan', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(46, 'Semua', NULL, 'Monitoring Tenaga Kediklatan', 'full learning', NULL, 'Fasilitator terdaftar dalam database penyelenggara / LMS', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(47, 'Semua', NULL, 'Monitoring Tenaga Kediklatan', 'full learning', NULL, 'Hadir dan memulai pembelajaran tepat waktu sesuai jadwal (Agenda)', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(48, 'Semua', NULL, 'Monitoring Tenaga Kediklatan', 'full learning', NULL, 'Menyampaikan materi dan panduan sesuai dengan Rancang Bangun Pembelajaran Mata Pelatihan (RBPMP) / Kurikulum', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(49, 'Semua', NULL, 'Monitoring Tenaga Kediklatan', 'full learning', NULL, 'Menguasai penggunaan fitur-fitur platform daring untuk pembelajaran interaktif (misal: polling, whiteboard, anotasi)', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(50, 'Semua', NULL, 'Monitoring Tenaga Kediklatan', 'full learning', NULL, 'Menjaga etika, profesionalisme, dan mematuhi kode etik pengajar ASN selama sesi berlangsung', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(51, 'Semua', NULL, 'Monitoring Tenaga Kediklatan', 'full learning', NULL, 'Menyediakan kelengkapan bahan ajar yang dapat diakses peserta (Modul, Bahan Tayang/Slide, Kasus/Tugas).', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(52, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'full learning', NULL, 'Learning Management System (LMS) berfungsi dengan baik, stabil, dan dapat diakses oleh seluruh pengguna', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(53, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'full learning', NULL, 'Platform Video Conference (misal: Zoom/Teams) memiliki lisensi dan kapasitas yang memadai untuk seluruh peserta', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(54, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'full learning', NULL, 'Tersedia fitur pendukung diskusi interaktif seperti Breakout Rooms jika diperlukan dalam metode pembelajaran', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(55, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'full learning', NULL, 'Tersedia Virtual Background standar yang sesuai dengan tema pelatihan', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(56, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'full learning', NULL, 'Jaringan internet yang digunakan oleh panitia dan fasilitator memadai, stabil, dan lancar', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(57, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'full learning', NULL, 'Perangkat keras Broadcasting panitia/host berfungsi optimal (Komputer/Laptop, Kamera dengan pencahayaan baik, Headset/Mikrofon jernih)', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(58, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'full learning', NULL, 'Tersedia saluran komunikasi/Helpdesk teknis yang responsif untuk membantu kendala sistem yang dialami peserta atau fasilitator.', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(59, 'Semua', NULL, 'Monitoring Penyelenggara', 'blended learning', NULL, 'Penyelenggara memiliki Surat Perintah (SP) / SK sebagai panitia.Keduanya', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(60, 'Semua', NULL, 'Monitoring Penyelenggara', 'blended learning', NULL, 'Terdapat Pengelola Kelas yang memiliki sertifikat kompetensi (MOT/TOC/Workshop sejenis) (daring/luring)', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(61, 'Semua', NULL, 'Monitoring Penyelenggara', 'blended learning', NULL, 'Tersedia Administrator/Host untuk mengelola kelas virtual (admit, mute, breakout room) - Daring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(62, 'Semua', NULL, 'Monitoring Penyelenggara', 'blended learning', NULL, 'Tersedia Pengamat Akademik dan Petugas Kelas yang standby di ruangan -Luring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(63, 'Semua', NULL, 'Monitoring Penyelenggara', 'blended learning', NULL, 'Menyediakan kelengkapan administrasi peserta dikelola dengan baik (Daftar Hadir online/offline, Biodata, Tanda Pengenal/Name Tag) - Daring dan Luring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(64, 'Semua', NULL, 'Monitoring Penyelenggara', 'blended learning', NULL, 'Tersedia Panitia memfasilitasi pelaksanaan evaluasi (Pre-test, Post-test, dan Evaluasi Penyelenggaraan) secara tersistem', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(65, 'Semua', NULL, 'Monitoring Peserta', 'blended learning', NULL, 'Kualifikasi peserta sesuai dengan persyaratan pelatihan - Luring dan Daring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(66, 'Semua', NULL, 'Monitoring Peserta', 'blended learning', NULL, 'Peserta memiliki akun yang terdaftar di BPSDM / LMS penyelenggara - Luring dan daring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(67, 'Semua', NULL, 'Monitoring Peserta', 'blended learning', NULL, 'Memenuhi syarat kehadiran minimal 85% dari total Jam Pelajaran (JP) keseluruhan - Luring dan Daring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(68, 'Semua', NULL, 'Monitoring Peserta', 'blended learning', NULL, 'Peserta mentaati tata tertib Daring (kamera aktif, mute saat tidak bicara, virtual background)', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(69, 'Semua', NULL, 'Monitoring Peserta', 'blended learning', NULL, 'Peserta mentaati tata tertib Luring (pakaian rapi sesuai ketentuan, tepat waktu masuk kelas) _Luring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(70, 'Semua', NULL, 'Monitoring Peserta', 'blended learning', NULL, 'Peserta aktif mengikuti pembelajaran (diskusi, tanya jawab, kerja kelompok) - Luring dan Daring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(71, 'Semua', NULL, 'Monitoring Peserta', 'blended learning', NULL, 'Menyelesaikan seluruh penugasan mandiri maupun kelompok (Tugas baca, makalah, dll).', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(72, 'Semua', NULL, 'Monitoring Tenaga Kediklatan', 'blended learning', NULL, 'Memiliki sertifikat TOT, sertifikat keahlian, atau kompetensi teknis mengajar - Luring dan Daring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(73, 'Semua', NULL, 'Monitoring Tenaga Kediklatan', 'blended learning', NULL, 'Mengunggah dan menyediakan bahan ajar secara lengkap di LMS (Modul, Slide, Kasus) - Daring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(74, 'Semua', NULL, 'Monitoring Tenaga Kediklatan', 'blended learning', NULL, 'Hadir tepat waktu sesuai jadwal (Agenda) yang telah ditetapkan - Daring dan Luring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(75, 'Semua', NULL, 'Monitoring Tenaga Kediklatan', 'blended learning', NULL, 'Menyampaikan materi sesuai Rancang Bangun Pembelajaran Mata Pelatihan (RBPMP) - Luring dan Daring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(76, 'Semua', NULL, 'Monitoring Tenaga Kediklatan', 'blended learning', NULL, 'Menguasai penggunaan platform digital secara interaktif (LMS, polling, whiteboard virtual) - Daring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(77, 'Semua', NULL, 'Monitoring Tenaga Kediklatan', 'blended learning', NULL, 'Mampu mengelola dinamika kelompok, simulasi, atau roleplay secara langsung di kelas - Luring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(78, 'Semua', NULL, 'Monitoring Tenaga Kediklatan', 'blended learning', NULL, 'Mematuhi kode etik pengajar/narasumber dan menjaga etika komunikasi - Luring dan Daring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(79, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'LMS tersedia, berfungsi lancar, dan mudah diakses peserta maupun fasilitator - Daring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(80, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Platform Video Conference memiliki lisensi, stabil, dan berkapasitas memadai -Daring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(81, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Jaringan internet dan peralatan broadcasting panitia (kamera, mic) berfungsi optimal - Daring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(82, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Fasilitas Pembelajaran Luring (Klasikal) Ruang kelas bersih, nyaman, dengan sirkulasi udara / AC yang baik - Luring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(83, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Kursi dan meja belajar memadai dan diatur sesuai metode pembelajaran - Luring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(84, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Perangkat audio visual di kelas berfungsi baik (Proyektor, Layar, Pengeras Suara/Mic) - Luring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(85, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Tersedia alat peraga pendukung (Papan tulis, flipchart, spidol, alat tulis) - Luring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(86, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Jaringan internet / Wi-Fi di area kelas dan asrama memadai untuk peserta - Luring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(87, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Ketersediaan prasarana pendukung: Toilet bersih, Ruang Ibadah, dan Ruang Makan.Luring7Ketersediaan Perlengkapan P3K / akses kesehatan dasar di lokasi pelatihan - Luring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(88, 'Semua', NULL, 'Monitoring Sarana Prasarana', 'blended learning', NULL, 'Ruang Istirahat (Kamar/Wisma/Asrama) bersih dan layak (apabila pelatihan diinapkan) - Luring', 'ya_tidak', NULL, '2026-07-19 20:32:10', '2026-07-19 20:32:10'),
(100, 'Semua', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Ybs memahami bahwa sumber daya yang diperlukan untuk implementasi materi pembelajaran di lingkungan kerja tersedia secara memadai', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(101, 'Semua', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Materi pelatihan sangat bermanfaat dalam menunjang pekerjaan Ybs', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(102, 'Semua', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Keberhasilan pelaksanaan pekerjaan Ybs meningkat setelah mengikuti pelatihan', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(103, 'Semua', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Kualitas dan kecepatan penyelesaian pekerjaan Ybs meningkat setelah mengikuti pelatihan', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(104, 'Semua', NULL, 'l34_mandiri', 'semua', 'Perubahan Perilaku', 'Ybs berkomitmen untuk implementasi materi pelatihan di lingkungan kerja saat ini', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(105, 'Semua', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap unit kerja', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(106, 'Semua', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap pengetahuan teoritis atau konsep Ybs', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(107, 'Semua', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan produktivitas', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(108, 'Semua', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap perbaikan kualitas hasil kerja', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(109, 'Semua', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan kepuasan pelanggan', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(110, 'Semua', NULL, 'l34_mandiri', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap penguatan hubungan antara rekan-rekan kerja', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(111, 'Semua', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Ybs memahami bahwa sumber daya yang diperlukan untuk implementasi materi pembelajaran di lingkungan kerja tersedia secara memadai', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(112, 'Semua', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Materi pelatihan sangat bermanfaat dalam menunjang pekerjaan Ybs', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(113, 'Semua', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Keberhasilan pelaksanaan pekerjaan Ybs meningkat setelah mengikuti pelatihan', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(114, 'Semua', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Kualitas dan kecepatan penyelesaian pekerjaan Ybs meningkat setelah mengikuti pelatihan', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(115, 'Semua', NULL, 'l34_atasan', 'semua', 'Perubahan Perilaku', 'Ybs berkomitmen untuk implementasi materi pelatihan di lingkungan kerja saat ini', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(116, 'Semua', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap unit kerja', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(117, 'Semua', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap pengetahuan teoritis atau konsep Ybs', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(118, 'Semua', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan produktivitas', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(119, 'Semua', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap perbaikan kualitas hasil kerja', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(120, 'Semua', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan kepuasan pelanggan', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(121, 'Semua', NULL, 'l34_atasan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap penguatan hubungan antara rekan-rekan kerja', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(122, 'Semua', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Ybs memahami bahwa sumber daya yang diperlukan untuk implementasi materi pembelajaran di lingkungan kerja tersedia secara memadai', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(123, 'Semua', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Materi pelatihan sangat bermanfaat dalam menunjang pekerjaan Ybs', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(124, 'Semua', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Keberhasilan pelaksanaan pekerjaan Ybs meningkat setelah mengikuti pelatihan', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(125, 'Semua', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Kualitas dan kecepatan penyelesaian pekerjaan Ybs meningkat setelah mengikuti pelatihan', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(126, 'Semua', NULL, 'l34_rekan', 'semua', 'Perubahan Perilaku', 'Ybs berkomitmen untuk implementasi materi pelatihan di lingkungan kerja saat ini', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(127, 'Semua', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap unit kerja', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(128, 'Semua', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap pengetahuan teoritis atau konsep Ybs', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(129, 'Semua', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan produktivitas', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(130, 'Semua', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap perbaikan kualitas hasil kerja', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(131, 'Semua', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap peningkatan kepuasan pelanggan', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16'),
(132, 'Semua', NULL, 'l34_rekan', 'semua', 'Dampak Pelatihan', 'Dampak pelatihan terhadap penguatan hubungan antara rekan-rekan kerja', 'slider', NULL, '2026-07-21 01:56:16', '2026-07-21 01:56:16');

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
  `evaluator_role` enum('mandiri','rekan','atasan') NOT NULL,
  `evaluator_name` varchar(255) NOT NULL,
  `question_id` bigint(20) UNSIGNED NOT NULL,
  `score` int(11) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `evaluation_results_l34`
--

INSERT INTO `evaluation_results_l34` (`id`, `participant_id`, `evaluator_role`, `evaluator_name`, `question_id`, `score`, `note`, `created_at`, `updated_at`) VALUES
(7, 2, 'mandiri', 'Diri Sendiri', 100, 80, NULL, '2026-07-21 02:06:35', '2026-07-21 02:06:35'),
(8, 2, 'mandiri', 'Diri Sendiri', 101, 80, NULL, '2026-07-21 02:06:35', '2026-07-21 02:06:35'),
(9, 2, 'mandiri', 'Diri Sendiri', 102, 80, NULL, '2026-07-21 02:06:35', '2026-07-21 02:06:35'),
(10, 2, 'mandiri', 'Diri Sendiri', 103, 80, NULL, '2026-07-21 02:06:35', '2026-07-21 02:06:35'),
(11, 2, 'mandiri', 'Diri Sendiri', 104, 80, NULL, '2026-07-21 02:06:35', '2026-07-21 02:06:35'),
(12, 2, 'mandiri', 'Diri Sendiri', 105, 80, NULL, '2026-07-21 02:06:35', '2026-07-21 02:06:35'),
(13, 2, 'mandiri', 'Diri Sendiri', 106, 80, NULL, '2026-07-21 02:06:35', '2026-07-21 02:06:35'),
(14, 2, 'mandiri', 'Diri Sendiri', 107, 80, NULL, '2026-07-21 02:06:35', '2026-07-21 02:06:35'),
(15, 2, 'mandiri', 'Diri Sendiri', 108, 80, NULL, '2026-07-21 02:06:35', '2026-07-21 02:06:35'),
(16, 2, 'mandiri', 'Diri Sendiri', 109, 80, NULL, '2026-07-21 02:06:35', '2026-07-21 02:06:35'),
(17, 2, 'mandiri', 'Diri Sendiri', 110, 80, NULL, '2026-07-21 02:06:35', '2026-07-21 02:06:35');

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
(5, '2026_07_18_100415_create_participants_table', 2),
(6, '2026_07_18_100416_create_schedules_table', 2),
(7, '2026_07_18_103645_create_attendances_table', 3),
(8, '2026_07_18_104315_create_evaluation_questions_table', 4),
(9, '2026_07_18_104317_create_evaluation_result_l1_s_table', 4),
(10, '2026_07_18_105109_create_evaluation_result_l2_s_table', 5),
(11, '2026_07_18_105343_create_monitoring_results_table', 6),
(12, '2026_07_18_105344_create_evaluation_result_l34_s_table', 6),
(13, '2026_07_18_120908_update_bidang_column_in_users_and_trainings', 7),
(14, '2026_07_18_124443_create_questions_table', 8),
(15, '2026_07_18_131730_add_responsible_person_to_schedules_table', 9),
(16, '2026_07_18_133807_create_training_stages_table', 10),
(17, '2026_07_18_141133_adjust_questions_table', 11),
(18, '2026_07_18_143132_create_alumni_profiles_table', 12),
(19, '2026_07_18_151051_create_monitoring_summaries_table', 13),
(20, '2026_07_19_080806_add_attendance_window_to_schedules_table', 14),
(21, '2026_07_19_115919_add_timezone_to_attendances_table', 14),
(22, '2026_07_19_133737_add_options_to_questions_table', 14),
(23, '2026_07_19_135500_create_evaluation_form_l1_s_table', 14),
(24, '2026_07_19_143117_add_missing_columns_to_evaluation_questions', 15),
(25, '2026_07_19_144105_change_category_column_type_in_evaluation_questions', 16),
(26, '2026_07_19_144729_add_metode_to_evaluation_questions', 17),
(27, '2026_07_20_032822_change_type_column_in_evaluation_questions', 18),
(28, '2026_07_20_042137_add_status_to_monitoring_results', 19),
(29, '2026_07_20_043404_adjust_monitoring_results_table', 20),
(30, '2026_07_20_043925_make_category_nullable_in_monitoring_results', 21),
(31, '2026_07_20_045127_add_resolution_columns_to_monitoring_results', 22),
(32, '2026_07_20_050903_add_stage_id_to_monitoring_results', 23),
(33, '2026_07_20_054225_add_training_stage_id_to_monitoring_summaries_table', 24),
(34, '2026_07_21_055227_adjust_evaluation_questions_for_specific_training', 25),
(35, '2026_07_21_063241_create_alumni_profiles_table', 26),
(36, '2026_07_21_063744_create_alumni_profiles_table', 27),
(37, '2026_07_21_065123_add_note_to_evaluation_results_l34', 28);

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
  `nip_nik` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `jabatan` varchar(255) DEFAULT NULL,
  `instansi` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `participants`
--

INSERT INTO `participants` (`id`, `training_id`, `nip_nik`, `name`, `jabatan`, `instansi`, `created_at`, `updated_at`) VALUES
(1, 2, '199503032934601221', 'Samsul Arifin', 'Pengelolaa', 'Dinas Kesehatan', '2026-07-19 07:43:50', '2026-07-19 07:43:50'),
(2, 2, '199503032025211003', 'Samsidin Alafghani, A.Md.Kom.', 'Pengelolaa Layanan Operasional', 'Badan Pengembangan Sumber Daya Manusia', '2026-07-19 07:44:31', '2026-07-19 07:44:31');

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
  `pic` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `attendance_open` time DEFAULT NULL,
  `attendance_close` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `schedules`
--

INSERT INTO `schedules` (`id`, `training_id`, `date`, `start_time`, `end_time`, `activity`, `pic`, `created_at`, `updated_at`, `attendance_open`, `attendance_close`) VALUES
(4, 2, '2026-07-19', '08:00:00', '09:00:00', 'Matei I untuk kegiatan itu', 'Maman Karbu', '2026-07-19 08:06:23', '2026-07-19 19:22:32', '09:25:00', '10:00:00'),
(5, 2, '2026-07-20', '09:00:00', '10:00:00', 'Matei II dhadhaJKD hdADadAD', 'dasdasdasdasdasdsd', '2026-07-19 08:06:39', '2026-07-19 08:06:39', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `trainings`
--

CREATE TABLE `trainings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bidang` varchar(255) NOT NULL,
  `nama_pelatihan` varchar(255) NOT NULL,
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

INSERT INTO `trainings` (`id`, `bidang`, `nama_pelatihan`, `model`, `metode`, `lokasi`, `kerjasama`, `anggaran`, `angkatan`, `jumlah_peserta`, `jp`, `tgl_mulai`, `tgl_selesai`, `tgl_mulai_klasikal`, `tgl_selesai_klasikal`, `created_at`, `updated_at`) VALUES
(2, 'Bidang Pengembangan Kompetensi Teknis Inti', 'Pelatihan Percontohan', 'standar', 'klasikal', 'Zoom', NULL, NULL, '1', 20, 45, '2026-07-19', '2026-07-24', NULL, NULL, '2026-07-18 07:09:13', '2026-07-19 08:14:55');

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
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `whatsapp` varchar(255) DEFAULT NULL,
  `role` enum('superadmin','admin_bidang') NOT NULL,
  `bidang` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `whatsapp`, `role`, `bidang`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'superadmin@bpsdm.go.id', '08123456789', 'superadmin', 'Bidang A', '$2y$12$KHa0Fa1GlSnHfvT2cqKTCudgjV7sgty4ki9Y1uwHaY9Eg7t0yTNae', NULL, '2026-07-18 04:34:53', '2026-07-18 04:34:53'),
(2, 'Iman Nurmana', 'bidangpkti@bpsdm.go.id', '0832329342375235', 'admin_bidang', 'Bidang Pengembangan Kompetensi Teknis Inti', '$2y$12$PP5WdHdpb.AxhjlwKwk/x.M6BMOCpO6/lHvMElHLGmFTvaR.EHvey', NULL, '2026-07-18 05:12:05', '2026-07-18 05:12:05'),
(3, 'Ridwan', 'bidangpktu@bpsdm.go.id', '0984782642342435', 'admin_bidang', 'Bidang Pengembangan Kompetensi Teknis Umum', '$2y$12$rjcI5dYrWJOgqIeeAGyhy.hg9uCN9DT03etthsUs.s7AhMODoe.GC', NULL, '2026-07-19 20:38:57', '2026-07-19 20:38:57'),
(4, 'Test User', 'testuser', '1-574-686-1010', 'superadmin', 'Bidang Pengembangan Kompetensi Teknis Inti', '$2y$12$h/Iu2kfvvPSLfYhnF.UAVu.HQoCVRcZtiwu8Z.iNZ.qNFFqgDxA0e', 'BGMzSiO41j', '2026-07-21 01:56:16', '2026-07-21 01:56:16');

--
-- Indexes for dumped tables
--

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
  ADD KEY `evaluation_questions_training_id_foreign` (`training_id`);

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
  ADD KEY `evaluation_results_l34_question_id_foreign` (`question_id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

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
  ADD KEY `participants_training_id_foreign` (`training_id`);

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
  ADD KEY `schedules_training_id_foreign` (`training_id`);

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
-- AUTO_INCREMENT untuk tabel `alumni_profiles`
--
ALTER TABLE `alumni_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `evaluation_forms`
--
ALTER TABLE `evaluation_forms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `evaluation_questions`
--
ALTER TABLE `evaluation_questions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT untuk tabel `evaluation_results_l1`
--
ALTER TABLE `evaluation_results_l1`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `evaluation_results_l2`
--
ALTER TABLE `evaluation_results_l2`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `evaluation_results_l34`
--
ALTER TABLE `evaluation_results_l34`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT untuk tabel `monitoring_results`
--
ALTER TABLE `monitoring_results`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=394;

--
-- AUTO_INCREMENT untuk tabel `monitoring_summaries`
--
ALTER TABLE `monitoring_summaries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT untuk tabel `participants`
--
ALTER TABLE `participants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `questions`
--
ALTER TABLE `questions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `trainings`
--
ALTER TABLE `trainings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `training_stages`
--
ALTER TABLE `training_stages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `alumni_profiles`
--
ALTER TABLE `alumni_profiles`
  ADD CONSTRAINT `alumni_profiles_participant_id_foreign` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alumni_profiles_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `evaluation_results_l34_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `evaluation_questions` (`id`);

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
  ADD CONSTRAINT `participants_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `schedules`
--
ALTER TABLE `schedules`
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
