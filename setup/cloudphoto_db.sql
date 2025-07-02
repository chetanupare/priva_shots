-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 02, 2025 at 08:58 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cloudphoto_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `details`, `created_at`) VALUES
(1, 1, 'Photo Upload', 'Uploaded 5 photos', '2025-07-01 22:06:21'),
(2, 1, 'Album Created', 'Created album \"Summer Vacation\"', '2025-07-01 20:06:21'),
(3, 1, 'Photo Upload', 'Uploaded 3 videos', '2025-07-01 18:06:21'),
(4, 1, 'Album Updated', 'Added 10 photos to album \"Family Photos\"', '2025-07-01 16:06:21'),
(5, 1, 'Photo Upload', 'Uploaded 15 photos', '2025-07-01 00:06:21'),
(6, 1, 'Photo Upload', 'Uploaded 5 photos from iPhone', '2025-07-01 23:06:49'),
(7, 1, 'Album Created', 'Created album \"Summer Vacation 2024\"', '2025-07-01 21:06:49'),
(8, 1, 'Photo Upload', 'Uploaded 12 photos from Canon EOS R5', '2025-07-01 19:06:49'),
(9, 1, 'Album Updated', 'Added 8 photos to album \"Family Photos\"', '2025-07-01 17:06:49'),
(10, 1, 'Video Upload', 'Uploaded 3 videos from GoPro', '2025-07-01 15:06:49'),
(11, 1, 'Photo Upload', 'Uploaded 7 photos from Sony A7III', '2025-07-01 12:06:49'),
(12, 1, 'Album Created', 'Created album \"Nature Photography\"', '2025-07-01 09:06:49'),
(13, 1, 'Photo Upload', 'Uploaded 20 photos from iPhone', '2025-07-01 06:06:49'),
(14, 1, 'Album Updated', 'Added 15 photos to album \"Travel Memories\"', '2025-07-01 03:06:49'),
(15, 1, 'Video Upload', 'Uploaded 2 videos from DJI Drone', '2025-07-01 00:06:49'),
(16, 1, 'Photo Upload', 'Uploaded 9 photos from Nikon D850', '2025-06-30 21:06:49'),
(17, 1, 'Album Created', 'Created album \"Street Photography\"', '2025-06-30 18:06:49'),
(18, 1, 'Photo Upload', 'Uploaded 14 photos from Fujifilm X-T4', '2025-06-30 15:06:49'),
(19, 1, 'Album Updated', 'Added 6 photos to album \"Portraits\"', '2025-06-30 12:06:49'),
(20, 1, 'Video Upload', 'Uploaded 4 videos from iPhone', '2025-06-30 09:06:49'),
(21, 1, 'Photo Upload', 'Uploaded 5 photos from iPhone', '2025-07-01 23:06:52'),
(22, 1, 'Album Created', 'Created album \"Summer Vacation 2024\"', '2025-07-01 21:06:52'),
(23, 1, 'Photo Upload', 'Uploaded 12 photos from Canon EOS R5', '2025-07-01 19:06:52'),
(24, 1, 'Album Updated', 'Added 8 photos to album \"Family Photos\"', '2025-07-01 17:06:52'),
(25, 1, 'Video Upload', 'Uploaded 3 videos from GoPro', '2025-07-01 15:06:52'),
(26, 1, 'Photo Upload', 'Uploaded 7 photos from Sony A7III', '2025-07-01 12:06:52'),
(27, 1, 'Album Created', 'Created album \"Nature Photography\"', '2025-07-01 09:06:52'),
(28, 1, 'Photo Upload', 'Uploaded 20 photos from iPhone', '2025-07-01 06:06:52'),
(29, 1, 'Album Updated', 'Added 15 photos to album \"Travel Memories\"', '2025-07-01 03:06:52'),
(30, 1, 'Video Upload', 'Uploaded 2 videos from DJI Drone', '2025-07-01 00:06:52'),
(31, 1, 'Photo Upload', 'Uploaded 9 photos from Nikon D850', '2025-06-30 21:06:52'),
(32, 1, 'Album Created', 'Created album \"Street Photography\"', '2025-06-30 18:06:52'),
(33, 1, 'Photo Upload', 'Uploaded 14 photos from Fujifilm X-T4', '2025-06-30 15:06:52'),
(34, 1, 'Album Updated', 'Added 6 photos to album \"Portraits\"', '2025-06-30 12:06:52'),
(35, 1, 'Video Upload', 'Uploaded 4 videos from iPhone', '2025-06-30 09:06:52');

-- --------------------------------------------------------

--
-- Table structure for table `albums`
--

CREATE TABLE `albums` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('manual','auto') DEFAULT 'manual',
  `cover_image_id` int(11) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `albums`
--

INSERT INTO `albums` (`id`, `user_id`, `name`, `description`, `type`, `cover_image_id`, `is_public`, `created_at`, `updated_at`) VALUES
(1, 1, 'July 2, 2025 (37 photos)', 'Auto-generated album with 37 photos', 'auto', 40, 0, '2025-07-01 23:51:07', '2025-07-01 23:51:07'),
(2, 1, 'July 2, 2025 (41 photos)', 'Auto-generated album with 41 photos', 'auto', 97, 0, '2025-07-02 14:07:45', '2025-07-02 14:07:45'),
(3, 1, 'October 22, 2008 (9 photos)', 'Auto-generated album with 9 photos', 'auto', 39, 0, '2025-07-02 14:07:45', '2025-07-02 14:07:45'),
(5, 1, '153', '', 'manual', NULL, 0, '2025-07-02 18:03:40', '2025-07-02 18:03:40');

-- --------------------------------------------------------

--
-- Table structure for table `album_files`
--

CREATE TABLE `album_files` (
  `id` int(11) NOT NULL,
  `album_id` int(11) NOT NULL,
  `media_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `api_key` varchar(64) NOT NULL,
  `name` varchar(100) NOT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `is_active` tinyint(1) DEFAULT 1,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_exif`
--

CREATE TABLE `media_exif` (
  `id` int(11) NOT NULL,
  `media_id` int(11) NOT NULL,
  `camera_make` varchar(100) DEFAULT NULL,
  `camera_model` varchar(100) DEFAULT NULL,
  `date_taken` datetime DEFAULT NULL,
  `gps_latitude` decimal(10,8) DEFAULT NULL,
  `gps_longitude` decimal(11,8) DEFAULT NULL,
  `orientation` int(11) DEFAULT NULL,
  `iso` int(11) DEFAULT NULL,
  `aperture` varchar(20) DEFAULT NULL,
  `shutter_speed` varchar(20) DEFAULT NULL,
  `focal_length` varchar(20) DEFAULT NULL,
  `flash` int(11) DEFAULT NULL,
  `white_balance` int(11) DEFAULT NULL,
  `exposure_mode` int(11) DEFAULT NULL,
  `metering_mode` int(11) DEFAULT NULL,
  `software` varchar(200) DEFAULT NULL,
  `copyright` varchar(200) DEFAULT NULL,
  `artist` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media_exif`
--

INSERT INTO `media_exif` (`id`, `media_id`, `camera_make`, `camera_model`, `date_taken`, `gps_latitude`, `gps_longitude`, `orientation`, `iso`, `aperture`, `shutter_speed`, `focal_length`, `flash`, `white_balance`, `exposure_mode`, `metering_mode`, `software`, `copyright`, `artist`, `description`, `created_at`) VALUES
(65, 104, 'Canon', 'Canon DIGITAL IXUS', '2001-06-09 15:17:32', NULL, NULL, 1, NULL, NULL, '1/350', '346/32', 0, NULL, NULL, 2, NULL, NULL, NULL, NULL, '2025-07-02 16:03:23'),
(66, 105, 'FUJIFILM', 'FinePix40i', '2000-08-04 18:22:57', NULL, NULL, 1, 200, NULL, NULL, '870/100', 1, NULL, NULL, 5, 'Digital Camera FinePix40i Ver1.39', '', NULL, NULL, '2025-07-02 16:03:23'),
(67, 106, 'EASTMAN KODAK COMPANY', 'KODAK DC240 ZOOM DIGITAL CAMERA', '1999-05-25 21:00:09', NULL, NULL, 1, NULL, NULL, '1/30', '140/10', 1, NULL, NULL, 1, NULL, 'KODAK DC240 ZOOM DIGITAL CAMERA', NULL, NULL, '2025-07-02 16:03:23'),
(68, 108, 'Eastman Kodak Company', 'DC210 Zoom (V05.00)', '2000-10-26 16:46:51', NULL, NULL, 1, NULL, NULL, '1/30', '44/10', 1, NULL, NULL, 2, NULL, NULL, NULL, NULL, '2025-07-02 16:03:23'),
(69, 107, 'FUJIFILM', 'DX-10', '2001-04-12 20:33:14', NULL, NULL, 1, 150, NULL, NULL, '58/10', 1, NULL, NULL, 5, 'Digital Camera DX-10 Ver1.00', 'J P Bowen', NULL, NULL, '2025-07-02 16:03:23'),
(70, 109, 'FUJIFILM', 'MX-1700ZOOM', '2000-09-02 14:30:10', NULL, NULL, 1, 125, NULL, NULL, '99/10', 0, NULL, NULL, 5, 'Digital Camera MX-1700ZOOM Ver1.00', '', NULL, NULL, '2025-07-02 16:03:23'),
(71, 110, 'NIKON', 'E950', '2001-04-06 11:51:40', NULL, NULL, 1, 80, NULL, '10/770', '128/10', 0, NULL, NULL, 5, 'v981-79', NULL, NULL, '', '2025-07-02 16:03:23'),
(72, 111, 'OLYMPUS OPTICAL CO.,LTD', 'C960Z,D460Z', '2000-11-07 10:41:43', NULL, NULL, 1, 125, NULL, '1/345', '56/10', 0, NULL, NULL, 5, 'OLYMPUS CAMEDIA Master', NULL, NULL, 'OLYMPUS DIGITAL CAMERA', '2025-07-02 16:03:23'),
(73, 112, 'RICOH', 'RDC-5300', '2000-05-31 21:50:40', NULL, NULL, 1, NULL, NULL, NULL, '133/10', 1, NULL, NULL, NULL, NULL, '(C) by RDC-5300 User', NULL, NULL, '2025-07-02 16:03:23'),
(74, 114, 'SONY', 'DSC-D700', '1998-12-01 14:22:36', NULL, NULL, 1, 200, NULL, NULL, NULL, 0, NULL, NULL, 2, NULL, NULL, NULL, NULL, '2025-07-02 16:03:23'),
(75, 115, 'SANYO Electric Co.,Ltd.', 'SR6', '1998-01-01 00:00:00', NULL, NULL, 1, NULL, NULL, '1/171', '60/10', 1, NULL, NULL, 2, 'V06P-74', NULL, NULL, 'SANYO DIGITAL CAMERA', '2025-07-02 16:03:23'),
(76, 117, 'SONY', 'CYBERSHOT', '2000-09-30 10:59:45', NULL, NULL, 1, 100, NULL, '1/197', '216/10', 0, NULL, NULL, 2, NULL, NULL, NULL, '', '2025-07-02 16:03:23'),
(77, 116, 'SANYO Electric Co.,Ltd.', 'SX113', '2000-11-18 21:14:19', NULL, NULL, 1, 400, NULL, '10/483', '60/10', 0, NULL, NULL, 2, 'V113p-73', NULL, NULL, 'SANYO DIGITAL CAMERA', '2025-07-02 16:03:23'),
(78, 122, 'NIKON', 'COOLPIX P6000', '2008-10-22 16:44:01', 43.46844167, 11.88151500, 1, 64, NULL, '676132/100000000', '6/1', 16, 0, 0, 5, 'Nikon Transfer 1.1 W', NULL, NULL, '', '2025-07-02 17:20:12'),
(79, 123, 'NIKON', 'COOLPIX P6000', '2008-10-22 16:46:53', 43.46824333, 11.88017167, 1, 64, NULL, '1543209/100000000', '221/10', 16, 0, 0, 5, 'Nikon Transfer 1.1 W', NULL, NULL, '', '2025-07-02 17:20:12'),
(80, 120, 'NIKON', 'COOLPIX P6000', '2008-10-22 16:28:39', 43.46744833, 11.88512667, 1, 64, NULL, '4/300', '24/1', 16, 0, 0, 5, 'Nikon Transfer 1.1 W', NULL, NULL, '', '2025-07-02 17:20:12'),
(81, 121, 'NIKON', 'COOLPIX P6000', '2008-10-22 16:38:20', 43.46708167, 11.88453833, 1, 64, NULL, '1044932/100000000', '166/10', 16, 0, 0, 5, 'Nikon Transfer 1.1 W', NULL, NULL, '', '2025-07-02 17:20:12'),
(82, 124, 'NIKON', 'COOLPIX P6000', '2008-10-22 16:29:49', 43.46715667, 11.88539500, 1, 64, NULL, '560852/100000000', '6/1', 16, 0, 0, 5, 'Nikon Transfer 1.1 W', NULL, NULL, '', '2025-07-02 17:20:12'),
(83, 125, 'NIKON', 'COOLPIX P6000', '2008-10-22 16:43:21', 43.46836500, 11.88163500, 1, 64, NULL, '813669/100000000', '81/10', 16, 0, 0, 5, 'Nikon Transfer 1.1 W', NULL, NULL, '', '2025-07-02 17:20:12'),
(84, 126, 'NIKON', 'COOLPIX P6000', '2008-10-22 16:55:37', 43.46601167, 11.87911167, 1, 64, NULL, '761035/100000000', '6/1', 16, 0, 0, 5, 'Nikon Transfer 1.1 W', NULL, NULL, '', '2025-07-02 17:20:12'),
(85, 127, 'NIKON', 'COOLPIX P6000', '2008-10-22 16:52:15', 43.46725500, 11.87921333, 1, 103, NULL, '1555209/100000000', '24/1', 16, 0, 0, 5, 'Nikon Transfer 1.1 W', NULL, NULL, '', '2025-07-02 17:20:12'),
(86, 128, 'NIKON', 'COOLPIX P6000', '2008-10-22 17:00:07', 43.46445500, 11.88147833, 1, 64, NULL, '1/100', '15/1', 16, 0, 0, 5, 'Nikon Transfer 1.1 W', NULL, NULL, '', '2025-07-02 17:20:12'),
(87, 129, 'Canon', 'Canon PowerShot SX60 HS', '2015-02-09 22:48:10', NULL, NULL, 6, 1000, NULL, '1/200', '24005/421', 16, 0, 0, 5, NULL, NULL, NULL, '', '2025-07-02 17:20:27'),
(88, 130, 'Canon', 'Canon PowerShot SX60 HS', '2015-02-09 22:47:44', NULL, NULL, 6, 800, NULL, '1/60', '24005/421', 16, 0, 1, 5, NULL, NULL, NULL, '', '2025-07-02 17:20:27'),
(89, 132, 'Apple', 'iPhone 6', '2015-04-10 20:12:23', 40.44697222, -3.72475278, 1, 32, NULL, '1/40', '83/20', 16, 0, 0, 3, '8.3', NULL, NULL, NULL, '2025-07-02 17:20:27'),
(90, 131, 'Apple', 'iPhone 6', '2015-04-10 20:12:23', 40.44697222, -3.72475278, 1, 32, NULL, '1/40', '83/20', 16, 0, 0, 3, '8.3', NULL, NULL, NULL, '2025-07-02 17:20:27'),
(91, 133, 'Jolla', 'Jolla', '2014-09-21 16:00:56', NULL, NULL, 1, 320, NULL, '1/25', '4/1', 0, 1, NULL, 1, NULL, NULL, NULL, NULL, '2025-07-02 17:20:39'),
(92, 134, 'HMD Global', 'Nokia 8.3 5G', '2022-08-14 14:12:31', 60.14670556, 24.90677222, 1, 100, NULL, '541/1000000', '275/100', 0, 0, NULL, 2, '00WW_3_380_SP02', NULL, NULL, NULL, '2025-07-02 17:20:39'),
(93, 135, 'HMD Global', 'Nokia 8.3 5G', '2021-07-29 21:28:46', 60.99138056, 24.42419167, 6, 601, NULL, '16666/1000000', '542/100', 0, 0, NULL, 2, '00WW_2_270_SP01', NULL, NULL, NULL, '2025-07-02 17:20:39'),
(94, 136, NULL, NULL, '2013-07-07 17:20:59', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Adobe Photoshop Elements 7.0', 'Francisco Gonzalez', NULL, NULL, '2025-07-02 17:20:53'),
(95, 138, 'Canon', 'Canon DIGITAL IXUS 40', '2007-09-03 16:03:45', NULL, NULL, NULL, NULL, NULL, '1/500', '5800/1000', 24, NULL, NULL, 5, NULL, NULL, NULL, NULL, '2025-07-02 17:20:53'),
(96, 137, 'NIKON CORPORATION', 'NIKON D300', '2012-07-14 16:30:12', NULL, NULL, 1, 200, NULL, '10/5000', '1050/10', 0, 0, 0, 5, 'GIMP 2.8.10', 'Ilya Kurikhin', 'Ilya Kurikhin', NULL, '2025-07-02 17:20:53'),
(97, 139, 'Canon', 'Canon PowerShot G9', '2008-05-25 19:31:26', NULL, NULL, 1, 80, NULL, '1/1250', '7400/1000', 24, 0, 0, 5, 'GIMP 2.6.11', NULL, NULL, 'El cielo sobre Berlin (Heaven above Berlin)', '2025-07-02 17:20:53'),
(98, 141, 'Canon', 'Canon DIGITAL IXUS 40', '2008-08-21 14:53:03', NULL, NULL, 1, NULL, NULL, '1/640', '5800/1000', 24, 0, 0, 5, 'Microsoft Windows Photo Gallery 6.0.6001.18000', NULL, NULL, NULL, '2025-07-02 17:20:53'),
(99, 140, 'Canon', 'Canon PowerShot SD300', '2007-11-29 16:16:21', NULL, NULL, 1, NULL, NULL, '1/6', '5800/1000', 16, 0, 0, 5, NULL, NULL, NULL, NULL, '2025-07-02 17:20:53'),
(101, 143, 'Polyphony Digital Inc.', 'Gran Turismo 5', '2012-06-23 06:55:49', NULL, NULL, 1, NULL, NULL, '1/59', '595/10', 0, NULL, NULL, NULL, 'PMB Service Uploader', 'TM&Copyright (C) 2010 Sony Computer Entertainment Inc.', NULL, 'Gran Turismo 5', '2025-07-02 17:20:53'),
(102, 144, 'CASIO COMPUTER CO.,LTD.', 'EX-M2', '2002-04-21 00:30:33', NULL, NULL, 1, NULL, NULL, '1/40', '750/100', 24, 0, 0, 5, '1.01+R', NULL, NULL, NULL, '2025-07-02 17:20:53'),
(103, 145, 'CASIO COMPUTER CO.,LTD', 'EX-Z750', '2005-07-19 18:25:29', NULL, NULL, 1, NULL, NULL, '1/250', '790/100', 16, 0, 0, 5, '1.00', NULL, NULL, NULL, '2025-07-02 17:20:53'),
(104, 146, 'NIKON CORPORATION', 'NIKON D70s', '2005-12-14 14:39:47', 43.78559443, 11.23461943, 1, 1000, NULL, '10/600', '600/10', 0, 0, 1, 5, 'Ver.1.00', NULL, NULL, NULL, '2025-07-02 17:20:53'),
(105, 147, 'OLYMPUS IMAGING CORP.', 'E-P3', '2014-08-23 13:05:43', NULL, NULL, 1, 1600, NULL, '1/30', '15/1', 8, 0, 0, 5, 'Version 1.3', NULL, NULL, 'OLYMPUS DIGITAL CAMERA', '2025-07-02 17:20:53'),
(106, 149, 'Canon', 'Canon PowerShot S40', '2003-12-14 12:01:44', NULL, NULL, 1, NULL, NULL, '1/500', '682/32', 24, 0, 0, 2, NULL, NULL, NULL, NULL, '2025-07-02 17:21:19'),
(107, 150, 'Canon', 'Canon DIGITAL IXUS 400', '2004-08-27 13:52:55', NULL, NULL, NULL, NULL, NULL, '1/200', '494/32', 24, 0, 0, 5, 'GIMP 2.4.5', NULL, NULL, NULL, '2025-07-02 17:21:19'),
(108, 148, 'Canon', 'Canon EOS 40D', '2008-05-30 15:56:01', NULL, NULL, 1, 100, NULL, '1/160', '135/1', 9, 0, 1, 5, 'GIMP 2.4.5', NULL, NULL, NULL, '2025-07-02 17:21:19'),
(109, 151, NULL, NULL, '2008-07-31 10:05:49', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GIMP 2.4.5', NULL, NULL, NULL, '2025-07-02 17:21:19');

-- --------------------------------------------------------

--
-- Table structure for table `media_files`
--

CREATE TABLE `media_files` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `filepath` varchar(500) NOT NULL,
  `mimetype` varchar(100) NOT NULL,
  `filesize` bigint(20) NOT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `capture_time` timestamp NULL DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `upload_ip` varchar(45) DEFAULT NULL,
  `has_exif` tinyint(1) DEFAULT 0,
  `date_taken` datetime DEFAULT NULL,
  `camera_model` varchar(100) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media_files`
--

INSERT INTO `media_files` (`id`, `user_id`, `filename`, `original_filename`, `filepath`, `mimetype`, `filesize`, `width`, `height`, `duration`, `capture_time`, `device_id`, `upload_ip`, `has_exif`, `date_taken`, `camera_model`, `uploaded_at`, `updated_at`) VALUES
(104, 1, '1_6865584b5dc88.jpg', 'canon-ixus.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2001-06-09/1_6865584b5dc88.jpg', 'image/jpeg', 128037, 640, 480, NULL, NULL, NULL, '::1', 1, '2001-06-09 15:17:32', 'Canon DIGITAL IXUS', '2025-07-02 16:03:23', '2025-07-02 16:03:23'),
(105, 1, '1_6865584b630e5.jpg', 'fujifilm-finepix40i.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2000-08-04/1_6865584b630e5.jpg', 'image/jpeg', 43183, 600, 450, NULL, NULL, NULL, '::1', 1, '2000-08-04 18:22:57', 'FinePix40i', '2025-07-02 16:03:23', '2025-07-02 16:03:23'),
(106, 1, '1_6865584b6597b.jpg', 'kodak-dc240.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/1999-05-25/1_6865584b6597b.jpg', 'image/jpeg', 81901, 640, 480, NULL, NULL, NULL, '::1', 1, '1999-05-25 21:00:09', 'KODAK DC240 ZOOM DIGITAL CAMERA', '2025-07-02 16:03:23', '2025-07-02 16:03:23'),
(107, 1, '1_6865584b65553.jpg', 'fujifilm-dx10.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2001-04-12/1_6865584b65553.jpg', 'image/jpeg', 133074, 1024, 768, NULL, NULL, NULL, '::1', 1, '2001-04-12 20:33:14', 'DX-10', '2025-07-02 16:03:23', '2025-07-02 16:03:23'),
(108, 1, '1_6865584b64929.jpg', 'kodak-dc210.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2000-10-26/1_6865584b64929.jpg', 'image/jpeg', 79837, 640, 480, NULL, NULL, NULL, '::1', 1, '2000-10-26 16:46:51', 'DC210 Zoom (V05.00)', '2025-07-02 16:03:23', '2025-07-02 16:03:23'),
(109, 1, '1_6865584b68d0c.jpg', 'fujifilm-mx1700.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2000-09-02/1_6865584b68d0c.jpg', 'image/jpeg', 100227, 640, 480, NULL, NULL, NULL, '::1', 1, '2000-09-02 14:30:10', 'MX-1700ZOOM', '2025-07-02 16:03:23', '2025-07-02 16:03:23'),
(110, 1, '1_6865584b6ffb7.jpg', 'nikon-e950.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2001-04-06/1_6865584b6ffb7.jpg', 'image/jpeg', 164151, 800, 600, NULL, NULL, NULL, '::1', 1, '2001-04-06 11:51:40', 'E950', '2025-07-02 16:03:23', '2025-07-02 16:03:23'),
(111, 1, '1_6865584b75045.jpg', 'olympus-c960.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2000-11-07/1_6865584b75045.jpg', 'image/jpeg', 87599, 640, 480, NULL, NULL, NULL, '::1', 1, '2000-11-07 10:41:43', 'C960Z,D460Z', '2025-07-02 16:03:23', '2025-07-02 16:03:23'),
(112, 1, '1_6865584b7f1b4.jpg', 'ricoh-rdc5300.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2000-05-31/1_6865584b7f1b4.jpg', 'image/jpeg', 87626, 896, 600, NULL, NULL, NULL, '::1', 1, '2000-05-31 21:50:40', 'RDC-5300', '2025-07-02 16:03:23', '2025-07-02 16:03:23'),
(113, 1, '1_6865584b9082a.jpg', 'olympus-d320l.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2025-07-02/1_6865584b9082a.jpg', 'image/jpeg', 61264, 640, 480, NULL, NULL, NULL, '::1', 0, NULL, NULL, '2025-07-02 16:03:23', '2025-07-02 16:03:23'),
(114, 1, '1_6865584b924b3.jpg', 'sony-d700.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/1998-12-01/1_6865584b924b3.jpg', 'image/jpeg', 79446, 672, 512, NULL, NULL, NULL, '::1', 1, '1998-12-01 14:22:36', 'DSC-D700', '2025-07-02 16:03:23', '2025-07-02 16:03:23'),
(115, 1, '1_6865584b91f29.jpg', 'sanyo-vpcg250.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/1998-01-01/1_6865584b91f29.jpg', 'image/jpeg', 62096, 640, 480, NULL, NULL, NULL, '::1', 1, '1998-01-01 00:00:00', 'SR6', '2025-07-02 16:03:23', '2025-07-02 16:03:23'),
(116, 1, '1_6865584b93d38.jpg', 'sanyo-vpcsx550.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2000-11-18/1_6865584b93d38.jpg', 'image/jpeg', 102448, 640, 480, NULL, NULL, NULL, '::1', 1, '2000-11-18 21:14:19', 'SX113', '2025-07-02 16:03:23', '2025-07-02 16:03:23'),
(117, 1, '1_6865584b9b24a.jpg', 'sony-cybershot.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2000-09-30/1_6865584b9b24a.jpg', 'image/jpeg', 63643, 640, 480, NULL, NULL, NULL, '::1', 1, '2000-09-30 10:59:45', 'CYBERSHOT', '2025-07-02 16:03:23', '2025-07-02 16:03:23'),
(118, 1, '1_6865584ba2b46.jpg', 'sony-powershota5.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2025-07-02/1_6865584ba2b46.jpg', 'image/jpeg', 58405, 1024, 768, NULL, NULL, NULL, '::1', 0, NULL, NULL, '2025-07-02 16:03:23', '2025-07-02 16:03:23'),
(119, 1, '1_68655a1b63695.jpg', 'Famous-British-Singers.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2025-07-02/1_68655a1b63695.jpg', 'image/jpeg', 120890, 1203, 836, NULL, NULL, NULL, '::1', 0, NULL, NULL, '2025-07-02 16:11:07', '2025-07-02 16:11:07'),
(120, 1, '1_68656a4cbecce.jpg', 'DSCN0010.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2008-10-22/1_68656a4cbecce.jpg', 'image/jpeg', 161713, 640, 480, NULL, NULL, NULL, '::1', 1, '2008-10-22 16:28:39', 'COOLPIX P6000', '2025-07-02 17:20:12', '2025-07-02 17:20:12'),
(121, 1, '1_68656a4cc08ae.jpg', 'DSCN0021.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2008-10-22/1_68656a4cc08ae.jpg', 'image/jpeg', 157382, 640, 480, NULL, NULL, NULL, '::1', 1, '2008-10-22 16:38:20', 'COOLPIX P6000', '2025-07-02 17:20:12', '2025-07-02 17:20:12'),
(122, 1, '1_68656a4cc1736.jpg', 'DSCN0027.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2008-10-22/1_68656a4cc1736.jpg', 'image/jpeg', 157723, 640, 480, NULL, NULL, NULL, '::1', 1, '2008-10-22 16:44:01', 'COOLPIX P6000', '2025-07-02 17:20:12', '2025-07-02 17:20:12'),
(123, 1, '1_68656a4cc37fe.jpg', 'DSCN0029.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2008-10-22/1_68656a4cc37fe.jpg', 'image/jpeg', 150085, 640, 480, NULL, NULL, NULL, '::1', 1, '2008-10-22 16:46:53', 'COOLPIX P6000', '2025-07-02 17:20:12', '2025-07-02 17:20:12'),
(124, 1, '1_68656a4cd001d.jpg', 'DSCN0012.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2008-10-22/1_68656a4cd001d.jpg', 'image/jpeg', 159137, 640, 480, NULL, NULL, NULL, '::1', 1, '2008-10-22 16:29:49', 'COOLPIX P6000', '2025-07-02 17:20:12', '2025-07-02 17:20:12'),
(125, 1, '1_68656a4cd0d2e.jpg', 'DSCN0025.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2008-10-22/1_68656a4cd0d2e.jpg', 'image/jpeg', 150301, 640, 480, NULL, NULL, NULL, '::1', 1, '2008-10-22 16:43:21', 'COOLPIX P6000', '2025-07-02 17:20:12', '2025-07-02 17:20:12'),
(126, 1, '1_68656a4ce0428.jpg', 'DSCN0040.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2008-10-22/1_68656a4ce0428.jpg', 'image/jpeg', 152893, 640, 480, NULL, NULL, NULL, '::1', 1, '2008-10-22 16:55:37', 'COOLPIX P6000', '2025-07-02 17:20:12', '2025-07-02 17:20:12'),
(127, 1, '1_68656a4ce197f.jpg', 'DSCN0038.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2008-10-22/1_68656a4ce197f.jpg', 'image/jpeg', 157569, 640, 480, NULL, NULL, NULL, '::1', 1, '2008-10-22 16:52:15', 'COOLPIX P6000', '2025-07-02 17:20:12', '2025-07-02 17:20:12'),
(128, 1, '1_68656a4ce2cdb.jpg', 'DSCN0042.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2008-10-22/1_68656a4ce2cdb.jpg', 'image/jpeg', 156695, 640, 480, NULL, NULL, NULL, '::1', 1, '2008-10-22 17:00:07', 'COOLPIX P6000', '2025-07-02 17:20:12', '2025-07-02 17:20:12'),
(129, 1, '1_68656a5b3f764.jpg', 'canon_hdr_YES.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2015-02-09/1_68656a5b3f764.jpg', 'image/jpeg', 722875, 2048, 1536, NULL, NULL, NULL, '::1', 1, '2015-02-09 22:48:10', 'Canon PowerShot SX60 HS', '2025-07-02 17:20:27', '2025-07-02 17:20:27'),
(130, 1, '1_68656a5b40d05.jpg', 'canon_hdr_NO.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2015-02-09/1_68656a5b40d05.jpg', 'image/jpeg', 784371, 2048, 1536, NULL, NULL, NULL, '::1', 1, '2015-02-09 22:47:44', 'Canon PowerShot SX60 HS', '2025-07-02 17:20:27', '2025-07-02 17:20:27'),
(131, 1, '1_68656a5b4df68.jpg', 'iphone_hdr_YES.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2015-04-10/1_68656a5b4df68.jpg', 'image/jpeg', 1976579, 3264, 2448, NULL, NULL, NULL, '::1', 1, '2015-04-10 20:12:23', 'iPhone 6', '2025-07-02 17:20:27', '2025-07-02 17:20:27'),
(132, 1, '1_68656a5b4df5b.jpg', 'iphone_hdr_NO.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2015-04-10/1_68656a5b4df5b.jpg', 'image/jpeg', 1957448, 3264, 2448, NULL, NULL, NULL, '::1', 1, '2015-04-10 20:12:23', 'iPhone 6', '2025-07-02 17:20:27', '2025-07-02 17:20:27'),
(133, 1, '1_68656a676c53d.jpg', 'jolla.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2014-09-21/1_68656a676c53d.jpg', 'image/jpeg', 811904, 3264, 2448, NULL, NULL, NULL, '::1', 1, '2014-09-21 16:00:56', 'Jolla', '2025-07-02 17:20:39', '2025-07-02 17:20:39'),
(134, 1, '1_68656a676dd69.jpg', 'HMD_Nokia_8.3_5G.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2022-08-14/1_68656a676dd69.jpg', 'image/jpeg', 2190194, 4608, 1976, NULL, NULL, NULL, '::1', 1, '2022-08-14 14:12:31', 'Nokia 8.3 5G', '2025-07-02 17:20:39', '2025-07-02 17:20:39'),
(135, 1, '1_68656a6777082.jpg', 'HMD_Nokia_8.3_5G_hdr.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2021-07-29/1_68656a6777082.jpg', 'image/jpeg', 5168013, 4608, 3456, NULL, NULL, NULL, '::1', 1, '2021-07-29 21:28:46', 'Nokia 8.3 5G', '2025-07-02 17:20:39', '2025-07-02 17:20:39'),
(136, 1, '1_68656a752e56a.jpg', '30-type_error.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2013-07-07/1_68656a752e56a.jpg', 'image/jpeg', 300825, 3872, 2403, NULL, NULL, NULL, '::1', 1, '2013-07-07 17:20:59', NULL, '2025-07-02 17:20:53', '2025-07-02 17:20:53'),
(137, 1, '1_68656a7534c6e.jpeg', '32-lens_data.jpeg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2012-07-14/1_68656a7534c6e.jpeg', 'image/jpeg', 36731, 200, 133, NULL, NULL, NULL, '::1', 1, '2012-07-14 16:30:12', 'NIKON D300', '2025-07-02 17:20:53', '2025-07-02 17:20:53'),
(138, 1, '1_68656a7534c1c.jpg', '11-tests.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2007-09-03/1_68656a7534c1c.jpg', 'image/jpeg', 236569, 1136, 775, NULL, NULL, NULL, '::1', 1, '2007-09-03 16:03:45', 'Canon DIGITAL IXUS 40', '2025-07-02 17:20:53', '2025-07-02 17:20:53'),
(139, 1, '1_68656a7534c19.jpg', '33-type_error.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2008-05-25/1_68656a7534c19.jpg', 'image/jpeg', 178028, 2560, 1600, NULL, NULL, NULL, '::1', 1, '2008-05-25 19:31:26', 'Canon PowerShot G9', '2025-07-02 17:20:53', '2025-07-02 17:20:53'),
(140, 1, '1_68656a7534c86.jpg', '22-canon_tags.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2007-11-29/1_68656a7534c86.jpg', 'image/jpeg', 448492, 1600, 1200, NULL, NULL, NULL, '::1', 1, '2007-11-29 16:16:21', 'Canon PowerShot SD300', '2025-07-02 17:20:53', '2025-07-02 17:20:53'),
(141, 1, '1_68656a7534c1c.jpg', '28-hex_value.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2008-08-21/1_68656a7534c1c.jpg', 'image/jpeg', 1350507, 1704, 2272, NULL, NULL, NULL, '::1', 1, '2008-08-21 14:53:03', 'Canon DIGITAL IXUS 40', '2025-07-02 17:20:53', '2025-07-02 17:20:53'),
(143, 1, '1_68656a754ac2d.jpg', '45-gps_ifd.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2012-06-23/1_68656a754ac2d.jpg', 'image/jpeg', 230349, 1600, 900, NULL, NULL, NULL, '::1', 1, '2012-06-23 06:55:49', 'Gran Turismo 5', '2025-07-02 17:20:53', '2025-07-02 17:20:53'),
(144, 1, '1_68656a754f79f.jpg', '36-memory_error.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2002-04-21/1_68656a754f79f.jpg', 'image/jpeg', 865978, 1600, 1200, NULL, NULL, NULL, '::1', 1, '2002-04-21 00:30:33', 'EX-M2', '2025-07-02 17:20:53', '2025-07-02 17:20:53'),
(145, 1, '1_68656a7551b44.jpg', '35-empty.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2005-07-19/1_68656a7551b44.jpg', 'image/jpeg', 1010466, 2164, 1626, NULL, NULL, NULL, '::1', 1, '2005-07-19 18:25:29', 'EX-Z750', '2025-07-02 17:20:53', '2025-07-02 17:20:53'),
(146, 1, '1_68656a7559f0d.jpg', '87_OSError.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2005-12-14/1_68656a7559f0d.jpg', 'image/jpeg', 889829, 1488, 2240, NULL, NULL, NULL, '::1', 1, '2005-12-14 14:39:47', 'NIKON D70s', '2025-07-02 17:20:53', '2025-07-02 17:20:53'),
(147, 1, '1_68656a755e3cf.jpg', '42_IndexError.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2014-08-23/1_68656a755e3cf.jpg', 'image/jpeg', 2913134, 4032, 3024, NULL, NULL, NULL, '::1', 1, '2014-08-23 13:05:43', 'E-P3', '2025-07-02 17:20:53', '2025-07-02 17:20:53'),
(148, 1, '1_68656a8f2f26e.jpg', 'Canon_40D.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2008-05-30/1_68656a8f2f26e.jpg', 'image/jpeg', 7958, 100, 68, NULL, NULL, NULL, '::1', 1, '2008-05-30 15:56:01', 'Canon EOS 40D', '2025-07-02 17:21:19', '2025-07-02 17:21:19'),
(149, 1, '1_68656a8f30e94.jpg', 'Canon_PowerShot_S40.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2003-12-14/1_68656a8f30e94.jpg', 'image/jpeg', 32764, 480, 360, NULL, NULL, NULL, '::1', 1, '2003-12-14 12:01:44', 'Canon PowerShot S40', '2025-07-02 17:21:19', '2025-07-02 17:21:19'),
(150, 1, '1_68656a8f31401.jpg', 'Canon_DIGITAL_IXUS_400.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2004-08-27/1_68656a8f31401.jpg', 'image/jpeg', 9198, 100, 75, NULL, NULL, NULL, '::1', 1, '2004-08-27 13:52:55', 'Canon DIGITAL IXUS 400', '2025-07-02 17:21:19', '2025-07-02 17:21:19'),
(151, 1, '1_68656a8f378d0.jpg', 'Canon_40D_photoshop_import.jpg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2008-07-31/1_68656a8f378d0.jpg', 'image/jpeg', 9686, 100, 77, NULL, NULL, NULL, '::1', 1, '2008-07-31 10:05:49', NULL, '2025-07-02 17:21:19', '2025-07-02 17:21:19'),
(152, 1, '1_686573266a9c7.jpeg', 'person-img-1.jpeg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2025-07-02/1_686573266a9c7.jpeg', 'image/jpeg', 22324, 240, 240, NULL, NULL, NULL, '::1', 0, NULL, NULL, '2025-07-02 17:57:58', '2025-07-02 17:57:58'),
(154, 1, '1_68657468340dd.jpeg', 'person-img-1.jpeg', 'C:\\xampp\\htdocs\\Cloudphoto\\config/../media/1/2025-07-02/1_68657468340dd.jpeg', 'image/jpeg', 22324, 240, 240, NULL, NULL, NULL, '::1', 0, NULL, NULL, '2025-07-02 18:03:20', '2025-07-02 18:03:20');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `storage_quota` bigint(20) DEFAULT 5368709120,
  `storage_used` bigint(20) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `storage_quota`, `storage_used`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@cloudphoto.local', '$2y$10$qPuJ3cXCjoYKLt.kzm.Ki.puiq0AS/Ju3f8JdH73i0ABMBvfBNpu2', 107374182400, 54691862, 1, '2025-07-01 22:13:19', '2025-07-02 18:03:20');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `device_info` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_used_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `token_hash`, `device_info`, `ip_address`, `expires_at`, `created_at`, `last_used_at`) VALUES
(1, 1, 'ffa62c93ea7621f8e3a10386787f948e90e7ef03461187060efd337dadf0996b', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-02 18:49:20', '2025-07-01 22:19:20', '2025-07-01 22:19:20'),
(2, 1, '4b28259022dfcb3085b42612b5e4a7c0895d6ead1a917585cf6e0db03d26a2df', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-02 19:08:08', '2025-07-01 22:38:08', '2025-07-01 22:38:08'),
(3, 1, '653b4b44a1cc76924be04e918f352b197ea5928f11ad1d7c0738d0fde0b42357', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-02 19:24:00', '2025-07-01 22:54:00', '2025-07-01 22:54:00'),
(4, 1, 'ab4a5233fe25cf3e6eceaecd0b7368cd281a59cad933fa0a574d732d50f0873f', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-02 19:53:08', '2025-07-01 23:23:08', '2025-07-01 23:23:08'),
(5, 1, '653d672f1fe7a8bda5de86fc76e0eb825a3572e2f247ebe4f99ba77eb5b575e7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-02 20:35:47', '2025-07-02 00:05:47', '2025-07-02 00:05:47'),
(6, 1, '535dbeec11cd24766e9c9c1d25ac525213900d21504838bef6526af96393c65c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-02 20:35:52', '2025-07-02 00:05:52', '2025-07-02 00:05:52'),
(7, 1, '23f7b17727427c92a9ecd4515e8690c86df12965f6b55695cc4f765989d3b865', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-02 20:37:12', '2025-07-02 00:07:12', '2025-07-02 00:07:12'),
(8, 1, '0bf3bcfa73e7e822366b23265f3f721926999f6837aebe85be845cecc8d5fd0b', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-02 20:37:17', '2025-07-02 00:07:17', '2025-07-02 00:07:17'),
(9, 1, '97e6a643c2e87c655dd3b4826fe38cb771810b38a0fd9ddaa16256ad9c2fbbe0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-03 10:44:28', '2025-07-02 14:14:28', '2025-07-02 14:14:28'),
(10, 1, 'ca5b63ad950a9702e53d195ef3f26dcd6471a3fea90358e0aec5e41c66952386', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-03 11:10:07', '2025-07-02 14:40:07', '2025-07-02 14:40:07'),
(11, 1, 'af96cf3c6a679b7752b47a177fc6c5b7de6b82da20bf10eeab52ba20d99594c8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-03 11:38:19', '2025-07-02 15:08:19', '2025-07-02 15:08:19'),
(12, 1, '725f1135bc13cb9e85edbbc024f3e35374c851813b9e5f8654aef125792ab598', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-03 11:40:49', '2025-07-02 15:10:49', '2025-07-02 15:10:49'),
(13, 1, 'e4739eba8229e6f37be5fe0c0040bee39ad7303dbf45e2aeb34770496c018272', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-03 11:41:04', '2025-07-02 15:11:04', '2025-07-02 15:11:04'),
(14, 1, 'e4739eba8229e6f37be5fe0c0040bee39ad7303dbf45e2aeb34770496c018272', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-03 11:41:04', '2025-07-02 15:11:04', '2025-07-02 15:11:04'),
(15, 1, '786220f8c3097ba9f8a89974df098f48b7d41b21def20ae70d15b0493bfefff0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-03 12:15:45', '2025-07-02 15:45:45', '2025-07-02 15:45:45'),
(16, 1, '9419d074479cc81a8eedea724d7042ab7d7eb286d6bbb2ac6c682b9c7d070df0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-03 12:31:57', '2025-07-02 16:01:57', '2025-07-02 16:01:57'),
(17, 1, '14484f10fdea2f35641aba2caf9d74bf4286195b534e9eff4afdd3025870d0a0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-03 14:08:54', '2025-07-02 17:38:54', '2025-07-02 17:38:54'),
(18, 1, '32da35d5a68c6d06f9f00cad01b07aec62d3cbfa48820b034b7332525f77d578', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-03 14:09:03', '2025-07-02 17:39:03', '2025-07-02 17:39:03'),
(19, 1, '7aa0dc8f793f4bafb1c0b2373b95c159d9b8622fd608ba9c0fda91912587f9ac', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-03 14:09:14', '2025-07-02 17:39:14', '2025-07-02 17:39:14'),
(20, 1, '483bcf123e1dc259f392a30956e8fdb76990044c5d0f2b1d164099876871c3d1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-03 14:22:16', '2025-07-02 17:52:16', '2025-07-02 17:52:16'),
(21, 1, '8fc20c94e57db25e30f1cc23d56431a9036abe10ac64733e088251fbb358f0b5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-03 14:22:20', '2025-07-02 17:52:20', '2025-07-02 17:52:20'),
(22, 1, 'd6e4b32fe7a3b305a2c0e45f89603d1a7bd414414d74ddd068a57590cc417cf2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-03 14:27:09', '2025-07-02 17:57:09', '2025-07-02 17:57:09'),
(23, 1, '31c005aa154451b21fd19146dd0c5071a0462a0dca17f652538a612fa34c377c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', '2025-07-03 14:32:30', '2025-07-02 18:02:30', '2025-07-02 18:02:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `albums`
--
ALTER TABLE `albums`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_type` (`type`);

--
-- Indexes for table `album_files`
--
ALTER TABLE `album_files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_album_media` (`album_id`,`media_id`),
  ADD KEY `media_id` (`media_id`);

--
-- Indexes for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `api_key` (`api_key`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `media_exif`
--
ALTER TABLE `media_exif`
  ADD PRIMARY KEY (`id`),
  ADD KEY `media_id` (`media_id`),
  ADD KEY `camera_model` (`camera_model`),
  ADD KEY `date_taken` (`date_taken`),
  ADD KEY `gps_location` (`gps_latitude`,`gps_longitude`),
  ADD KEY `idx_exif_camera_make_model` (`camera_make`,`camera_model`),
  ADD KEY `idx_exif_date_taken` (`date_taken`),
  ADD KEY `idx_exif_gps` (`gps_latitude`,`gps_longitude`),
  ADD KEY `idx_exif_iso` (`iso`),
  ADD KEY `idx_exif_orientation` (`orientation`);

--
-- Indexes for table `media_files`
--
ALTER TABLE `media_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_media_date_taken` (`date_taken`),
  ADD KEY `idx_media_camera_model` (`camera_model`),
  ADD KEY `idx_media_has_exif` (`has_exif`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `albums`
--
ALTER TABLE `albums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `album_files`
--
ALTER TABLE `album_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT for table `api_keys`
--
ALTER TABLE `api_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media_exif`
--
ALTER TABLE `media_exif`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `media_files`
--
ALTER TABLE `media_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `albums`
--
ALTER TABLE `albums`
  ADD CONSTRAINT `albums_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `album_files`
--
ALTER TABLE `album_files`
  ADD CONSTRAINT `album_files_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `album_files_ibfk_2` FOREIGN KEY (`media_id`) REFERENCES `media_files` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD CONSTRAINT `api_keys_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `media_exif`
--
ALTER TABLE `media_exif`
  ADD CONSTRAINT `fk_media_exif` FOREIGN KEY (`media_id`) REFERENCES `media_files` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `media_files`
--
ALTER TABLE `media_files`
  ADD CONSTRAINT `media_files_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
