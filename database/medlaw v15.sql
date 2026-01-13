-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 05, 2025 at 08:03 AM
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
-- Database: `medlaw`
--

-- --------------------------------------------------------

--
-- Table structure for table `analytics_events`
--

CREATE TABLE `analytics_events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `event_category` varchar(100) NOT NULL,
  `event_action` varchar(100) NOT NULL,
  `event_label` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `analytics_events`
--

INSERT INTO `analytics_events` (`id`, `user_id`, `event_type`, `event_category`, `event_action`, `event_label`, `created_at`) VALUES
(1, 21003, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(2, 21006, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(3, 21009, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(4, 21010, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(5, 21037, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(6, 21041, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(7, 21042, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(8, 21043, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(9, 21044, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(10, 21047, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(11, 21050, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(12, 21051, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(13, 21053, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(14, 21054, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(15, 21056, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(16, 21058, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(17, 21060, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(18, 21062, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(19, 21064, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(20, 21067, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(21, 21068, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(22, 21071, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(23, 21074, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(24, 21075, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(25, 21076, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(26, 21078, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(27, 21081, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(28, 21082, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(29, 21084, 'profile_updated', 'user', 'profile_update', 'Profile information updated', '2025-10-27 13:24:15'),
(30, 21061, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(31, 21086, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(32, 1, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(33, 20001, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(34, 20002, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(35, 20004, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(36, 20005, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(37, 20007, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(38, 20008, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(39, 20010, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(40, 21001, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(41, 21006, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(42, 21008, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(43, 21009, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(44, 21010, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(45, 21013, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(46, 21014, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(47, 21017, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(48, 21018, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(49, 21019, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(50, 21021, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(51, 21024, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(52, 21025, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(53, 21026, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(54, 21027, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(55, 21030, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(56, 21032, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(57, 21033, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(58, 21034, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(59, 21035, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(60, 21036, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(61, 21037, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(62, 21038, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(63, 21041, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(64, 21042, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(65, 21044, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(66, 21045, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(67, 21046, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(68, 21047, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(69, 21048, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(70, 21049, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(71, 21050, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(72, 21051, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(73, 21052, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(74, 21053, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(75, 21057, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(76, 21059, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(77, 21060, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(78, 21062, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(79, 21064, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(80, 21065, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(81, 21066, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(82, 21068, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(83, 21069, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(84, 21070, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(85, 21071, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(86, 21073, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(87, 21074, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(88, 21075, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(89, 21077, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(90, 21080, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(91, 21081, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(92, 21082, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(93, 21084, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15'),
(94, 21085, 'login', 'auth', 'login_success', 'User login', '2025-10-27 13:24:15');

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL,
  `key_name` varchar(255) NOT NULL,
  `key_type` enum('internal','external','webhook','integration') NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `api_secret` varchar(255) DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Allowed permissions/endpoints' CHECK (json_valid(`permissions`)),
  `rate_limit` int(11) DEFAULT NULL COMMENT 'Requests per hour',
  `expires_at` timestamp NULL DEFAULT NULL,
  `last_used` timestamp NULL DEFAULT NULL,
  `usage_count` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `reminder_minutes_before` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `case_id`, `created_by`, `assigned_to`, `title`, `description`, `location`, `start_time`, `end_time`, `status`, `reminder_minutes_before`, `created_at`, `updated_at`) VALUES
(28001, 24001, 21001, 20007, 'Initial consultation', NULL, NULL, '2025-10-30 13:24:10', NULL, 'scheduled', NULL, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(28002, 24002, 21002, 20008, 'Review surgical notes', NULL, NULL, '2025-11-01 13:24:10', NULL, 'scheduled', NULL, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(28003, 24003, 21003, 20009, 'Site visit and intake', NULL, NULL, '2025-10-25 13:24:10', NULL, 'completed', NULL, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(28004, 24006, 21006, 20007, 'Witness statement', NULL, NULL, '2025-10-28 13:24:10', NULL, 'scheduled', NULL, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(28005, 24001, 21001, 21017, 'Consultation', 'Scheduled via seed data', 'Office – Durban', '2025-10-01 13:24:14', '2025-11-20 14:24:14', 'scheduled', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28006, 24077, 21001, 21017, 'Document Review', 'Scheduled via seed data', 'Phone Call', '2025-11-14 13:24:14', '2025-10-03 14:24:14', 'scheduled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28007, 24078, 21001, 21018, 'Consultation', 'Scheduled via seed data', 'Phone Call', '2025-11-09 13:24:14', '2025-09-29 14:24:14', 'scheduled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28008, 24002, 21002, 21017, 'Consultation', 'Scheduled via seed data', 'Office – Durban', '2025-10-11 13:24:14', '2025-10-23 14:24:14', 'completed', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28009, 24094, 21002, 21019, 'Strategy Session', 'Scheduled via seed data', 'Phone Call', '2025-11-22 13:24:14', '2025-10-15 14:24:14', 'cancelled', 60, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28010, 24003, 21003, 20006, 'Follow-up', 'Scheduled via seed data', 'Video Call', '2025-10-07 13:24:14', '2025-09-27 14:24:14', 'completed', 60, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28011, 24004, 21004, 20008, 'Follow-up', 'Scheduled via seed data', 'Phone Call', '2025-11-15 13:24:14', '2025-10-25 14:24:14', 'cancelled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28012, 24033, 21004, 21034, 'Follow-up', 'Scheduled via seed data', 'Office – Durban', '2025-11-08 13:24:14', '2025-11-24 14:24:14', 'cancelled', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28013, 24034, 21004, 20008, 'Consultation', 'Scheduled via seed data', 'Office – Durban', '2025-10-30 13:24:14', '2025-10-04 14:24:14', 'cancelled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28014, 24005, 21005, 21033, 'Consultation', 'Scheduled via seed data', 'Video Call', '2025-11-11 13:24:14', '2025-11-18 14:24:14', 'scheduled', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28015, 24053, 21005, 20006, 'Document Review', 'Scheduled via seed data', 'Video Call', '2025-10-28 13:24:14', '2025-10-17 14:24:14', 'scheduled', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28016, 24054, 21005, 21017, 'Strategy Session', 'Scheduled via seed data', 'Phone Call', '2025-10-24 13:24:14', '2025-11-14 14:24:14', 'cancelled', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28017, 24006, 21006, 20009, 'Document Review', 'Scheduled via seed data', 'Phone Call', '2025-11-07 13:24:14', '2025-10-27 14:24:14', 'completed', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28018, 24055, 21006, 20006, 'Document Review', 'Scheduled via seed data', 'Office – Durban', '2025-11-19 13:24:14', '2025-09-29 14:24:14', 'completed', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28019, 24056, 21006, 20009, 'Strategy Session', 'Scheduled via seed data', 'Office – Johannesburg', '2025-11-03 13:24:14', '2025-11-24 14:24:14', 'cancelled', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28020, 24007, 21007, 21018, 'Document Review', 'Scheduled via seed data', 'Video Call', '2025-10-14 13:24:14', '2025-10-28 14:24:14', 'cancelled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28021, 24036, 21008, 20010, 'Strategy Session', 'Scheduled via seed data', 'Office – Cape Town', '2025-11-19 13:24:14', '2025-10-20 14:24:14', 'scheduled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28022, 24009, 21009, 21017, 'Strategy Session', 'Scheduled via seed data', 'Phone Call', '2025-11-19 13:24:14', '2025-11-14 14:24:14', 'completed', 60, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28023, 24061, 21009, 21034, 'Follow-up', 'Scheduled via seed data', 'Office – Johannesburg', '2025-10-23 13:24:14', '2025-11-02 14:24:14', 'cancelled', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28024, 24062, 21009, 20007, 'Document Review', 'Scheduled via seed data', 'Office – Durban', '2025-11-22 13:24:14', '2025-10-19 14:24:14', 'scheduled', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28025, 24090, 21010, 20010, 'Strategy Session', 'Scheduled via seed data', 'Video Call', '2025-10-02 13:24:14', '2025-10-14 14:24:14', 'scheduled', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28026, 24028, 21037, 20010, 'Follow-up', 'Scheduled via seed data', 'Phone Call', '2025-11-03 13:24:14', '2025-10-20 14:24:14', 'scheduled', 60, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28027, 24073, 21038, 21018, 'Consultation', 'Scheduled via seed data', 'Office – Johannesburg', '2025-09-29 13:24:14', '2025-11-19 14:24:14', 'completed', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28028, 24074, 21038, 21034, 'Follow-up', 'Scheduled via seed data', 'Office – Durban', '2025-10-21 13:24:14', '2025-11-11 14:24:14', 'completed', 60, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28029, 24067, 21039, 21018, 'Follow-up', 'Scheduled via seed data', 'Office – Durban', '2025-11-24 13:24:14', '2025-10-26 14:24:14', 'completed', 60, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28030, 24068, 21039, 20007, 'Follow-up', 'Scheduled via seed data', 'Video Call', '2025-10-06 13:24:14', '2025-11-10 14:24:14', 'scheduled', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28031, 24063, 21043, 21019, 'Document Review', 'Scheduled via seed data', 'Office – Cape Town', '2025-10-05 13:24:14', '2025-09-28 14:24:14', 'cancelled', 60, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28032, 24064, 21043, 20010, 'Strategy Session', 'Scheduled via seed data', 'Office – Durban', '2025-10-21 13:24:14', '2025-11-10 14:24:14', 'completed', 60, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28033, 24045, 21044, 21034, 'Document Review', 'Scheduled via seed data', 'Phone Call', '2025-10-08 13:24:14', '2025-10-14 14:24:14', 'cancelled', 60, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28034, 24046, 21044, 20008, 'Consultation', 'Scheduled via seed data', 'Office – Johannesburg', '2025-10-15 13:24:14', '2025-09-29 14:24:14', 'scheduled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28035, 24017, 21045, 21019, 'Document Review', 'Scheduled via seed data', 'Office – Cape Town', '2025-10-28 13:24:14', '2025-11-15 14:24:14', 'completed', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28036, 24018, 21045, 20010, 'Document Review', 'Scheduled via seed data', 'Phone Call', '2025-11-23 13:24:14', '2025-11-21 14:24:14', 'cancelled', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28037, 24049, 21046, 21017, 'Strategy Session', 'Scheduled via seed data', 'Office – Cape Town', '2025-10-16 13:24:14', '2025-11-08 14:24:14', 'completed', 60, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28038, 24050, 21046, 20008, 'Consultation', 'Scheduled via seed data', 'Phone Call', '2025-10-30 13:24:14', '2025-10-11 14:24:14', 'completed', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28039, 24085, 21047, 20009, 'Follow-up', 'Scheduled via seed data', 'Office – Johannesburg', '2025-10-12 13:24:14', '2025-11-21 14:24:14', 'cancelled', 60, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28040, 24086, 21047, 21024, 'Document Review', 'Scheduled via seed data', 'Office – Durban', '2025-10-16 13:24:14', '2025-11-22 14:24:14', 'cancelled', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28041, 24037, 21048, 21033, 'Consultation', 'Scheduled via seed data', 'Video Call', '2025-10-03 13:24:14', '2025-10-28 14:24:14', 'scheduled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28042, 24038, 21048, 20007, 'Document Review', 'Scheduled via seed data', 'Video Call', '2025-10-03 13:24:14', '2025-10-28 14:24:14', 'scheduled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28043, 24039, 21049, 21018, 'Consultation', 'Scheduled via seed data', 'Phone Call', '2025-11-20 13:24:14', '2025-11-25 14:24:14', 'scheduled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28044, 24071, 21050, 21033, 'Consultation', 'Scheduled via seed data', 'Video Call', '2025-10-22 13:24:14', '2025-11-19 14:24:14', 'scheduled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28045, 24072, 21050, 20007, 'Follow-up', 'Scheduled via seed data', 'Phone Call', '2025-10-28 13:24:14', '2025-10-01 14:24:14', 'cancelled', 60, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28046, 24029, 21051, 21019, 'Document Review', 'Scheduled via seed data', 'Office – Durban', '2025-10-07 13:24:14', '2025-10-29 14:24:14', 'scheduled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28047, 24030, 21051, 21023, 'Document Review', 'Scheduled via seed data', 'Office – Durban', '2025-10-17 13:24:14', '2025-10-24 14:24:14', 'scheduled', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28048, 24019, 21052, 21034, 'Consultation', 'Scheduled via seed data', 'Office – Cape Town', '2025-10-15 13:24:14', '2025-11-20 14:24:14', 'completed', 60, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28049, 24107, 21053, 21023, 'Consultation', 'Scheduled via seed data', 'Office – Cape Town', '2025-10-13 13:24:14', '2025-10-11 14:24:14', 'scheduled', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28050, 24043, 21055, 21018, 'Follow-up', 'Scheduled via seed data', 'Video Call', '2025-11-22 13:24:14', '2025-10-30 14:24:14', 'cancelled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28051, 24044, 21055, 20011, 'Consultation', 'Scheduled via seed data', 'Video Call', '2025-10-03 13:24:14', '2025-10-20 14:24:14', 'completed', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28052, 24075, 21056, 21033, 'Follow-up', 'Scheduled via seed data', 'Office – Cape Town', '2025-10-13 13:24:14', '2025-10-22 14:24:14', 'scheduled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28053, 24076, 21056, 20009, 'Document Review', 'Scheduled via seed data', 'Video Call', '2025-11-11 13:24:14', '2025-10-27 14:24:14', 'scheduled', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28054, 24025, 21057, 20010, 'Strategy Session', 'Scheduled via seed data', 'Office – Johannesburg', '2025-11-08 13:24:14', '2025-10-05 14:24:14', 'completed', 60, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28055, 24026, 21057, 20009, 'Consultation', 'Scheduled via seed data', 'Office – Johannesburg', '2025-11-17 13:24:14', '2025-10-05 14:24:14', 'scheduled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28056, 24110, 21058, 20009, 'Strategy Session', 'Scheduled via seed data', 'Office – Cape Town', '2025-09-27 13:24:14', '2025-10-04 14:24:14', 'completed', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28057, 24103, 21059, 20009, 'Consultation', 'Scheduled via seed data', 'Office – Cape Town', '2025-11-01 13:24:14', '2025-11-15 14:24:14', 'completed', 60, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28058, 24060, 21060, 20007, 'Consultation', 'Scheduled via seed data', 'Video Call', '2025-11-23 13:24:14', '2025-11-22 14:24:14', 'cancelled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28059, 24011, 21062, 21017, 'Document Review', 'Scheduled via seed data', 'Office – Johannesburg', '2025-11-23 13:24:14', '2025-11-10 14:24:14', 'cancelled', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28060, 24012, 21062, 20006, 'Strategy Session', 'Scheduled via seed data', 'Video Call', '2025-11-18 13:24:14', '2025-10-15 14:24:14', 'cancelled', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28061, 24058, 21065, 21019, 'Strategy Session', 'Scheduled via seed data', 'Video Call', '2025-11-13 13:24:14', '2025-11-09 14:24:14', 'scheduled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28062, 24091, 21066, 21017, 'Strategy Session', 'Scheduled via seed data', 'Office – Durban', '2025-10-27 13:24:14', '2025-10-03 14:24:14', 'scheduled', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28063, 24051, 21067, 21034, 'Consultation', 'Scheduled via seed data', 'Video Call', '2025-11-23 13:24:14', '2025-11-08 14:24:14', 'completed', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28064, 24052, 21067, 20006, 'Strategy Session', 'Scheduled via seed data', 'Phone Call', '2025-11-25 13:24:14', '2025-10-08 14:24:14', 'cancelled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28065, 24098, 21068, 20011, 'Consultation', 'Scheduled via seed data', 'Office – Cape Town', '2025-09-28 13:24:14', '2025-10-28 14:24:14', 'completed', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28066, 24079, 21069, 21019, 'Consultation', 'Scheduled via seed data', 'Office – Cape Town', '2025-10-24 13:24:14', '2025-10-05 14:24:14', 'completed', 60, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28067, 24096, 21071, 21033, 'Follow-up', 'Scheduled via seed data', 'Phone Call', '2025-11-16 13:24:14', '2025-10-18 14:24:14', 'scheduled', 60, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28068, 24013, 21072, 20006, 'Strategy Session', 'Scheduled via seed data', 'Phone Call', '2025-10-09 13:24:14', '2025-10-26 14:24:14', 'cancelled', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28069, 24014, 21072, 20011, 'Document Review', 'Scheduled via seed data', 'Office – Cape Town', '2025-10-15 13:24:14', '2025-11-06 14:24:14', 'completed', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28070, 24101, 21073, 20010, 'Follow-up', 'Scheduled via seed data', 'Office – Durban', '2025-10-29 13:24:14', '2025-10-18 14:24:14', 'scheduled', 60, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28071, 24102, 21073, 21023, 'Consultation', 'Scheduled via seed data', 'Office – Johannesburg', '2025-11-20 13:24:14', '2025-10-12 14:24:14', 'completed', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28072, 24031, 21075, 20006, 'Strategy Session', 'Scheduled via seed data', 'Office – Johannesburg', '2025-10-29 13:24:14', '2025-10-31 14:24:14', 'scheduled', 60, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28073, 24032, 21075, 21024, 'Consultation', 'Scheduled via seed data', 'Office – Johannesburg', '2025-11-10 13:24:14', '2025-11-07 14:24:14', 'scheduled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28074, 24041, 21076, 21019, 'Document Review', 'Scheduled via seed data', 'Office – Johannesburg', '2025-10-18 13:24:14', '2025-10-14 14:24:14', 'completed', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28075, 24042, 21076, 20010, 'Consultation', 'Scheduled via seed data', 'Video Call', '2025-10-28 13:24:14', '2025-10-23 14:24:14', 'completed', 60, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28076, 24099, 21077, 21033, 'Consultation', 'Scheduled via seed data', 'Video Call', '2025-11-22 13:24:14', '2025-11-05 14:24:14', 'completed', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28077, 24100, 21077, 21033, 'Strategy Session', 'Scheduled via seed data', 'Video Call', '2025-10-05 13:24:14', '2025-11-13 14:24:14', 'completed', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28078, 24015, 21079, 20007, 'Strategy Session', 'Scheduled via seed data', 'Video Call', '2025-10-20 13:24:14', '2025-11-17 14:24:14', 'scheduled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28079, 24016, 21079, 20006, 'Strategy Session', 'Scheduled via seed data', 'Video Call', '2025-10-25 13:24:14', '2025-10-23 14:24:14', 'cancelled', 60, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28080, 24088, 21081, 21020, 'Follow-up', 'Scheduled via seed data', 'Phone Call', '2025-11-03 13:24:14', '2025-10-31 14:24:14', 'cancelled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28081, 24065, 21082, 20007, 'Consultation', 'Scheduled via seed data', 'Office – Cape Town', '2025-11-14 13:24:14', '2025-11-17 14:24:14', 'cancelled', 120, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28082, 24066, 21082, 21019, 'Consultation', 'Scheduled via seed data', 'Office – Durban', '2025-11-17 13:24:14', '2025-11-17 14:24:14', 'cancelled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28083, 24083, 21084, 21019, 'Consultation', 'Scheduled via seed data', 'Video Call', '2025-11-10 13:24:14', '2025-11-08 14:24:14', 'scheduled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28084, 24084, 21084, 21017, 'Consultation', 'Scheduled via seed data', 'Video Call', '2025-09-30 13:24:14', '2025-10-05 14:24:14', 'completed', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(28085, 24047, 21085, 20010, 'Strategy Session', 'Scheduled via seed data', 'Office – Durban', '2025-10-10 13:24:14', '2025-11-14 14:24:14', 'scheduled', 30, '2025-10-27 13:24:14', '2025-10-27 13:24:14');

-- --------------------------------------------------------

--
-- Table structure for table `attorney_profiles`
--

CREATE TABLE `attorney_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `bio` mediumtext DEFAULT NULL,
  `practice_areas` varchar(255) DEFAULT NULL,
  `years_experience` int(11) DEFAULT NULL,
  `bar_admission_year` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attorney_profiles`
--

INSERT INTO `attorney_profiles` (`id`, `user_id`, `name`, `email`, `title`, `bio`, `practice_areas`, `years_experience`, `bar_admission_year`, `created_at`, `updated_at`) VALUES
(30001, 20007, 'Senior Attorney 1', NULL, 'Senior Attorney', 'Senior attorney specializing in orthopedic injuries.', 'Medical Negligence, Product Liability', 14, 2011, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(30002, 20008, 'Senior Attorney 2', NULL, 'Trial Attorney', 'Trial attorney for complex surgery cases.', 'Medical Negligence, Litigation', 11, 2014, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(30003, 20009, 'Associate Attorney', NULL, 'Associate Attorney', 'Associate focusing on premises liability.', 'Premises Liability, Investigation', 5, 2019, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(30004, 21017, 'Attorney One', NULL, 'Senior Attorney', 'Experienced attorney specializing in medical negligence and personal injury cases. Committed to providing excellent legal representation for clients.', 'Medical Negligence, Personal Injury, Product Liability', 12, 2012, '2025-11-05 09:02:03', '2025-11-05 07:02:03'),
(30005, 21018, 'Attorney Two', NULL, 'Associate Attorney', 'Dedicated attorney with expertise in motor vehicle accidents and premises liability. Focused on achieving the best outcomes for clients.', 'Motor Vehicle Accidents, Premises Liability, General Injury', 8, 2016, '2025-11-05 09:02:03', '2025-11-05 07:02:03');

-- --------------------------------------------------------

--
-- Table structure for table `backup_logs`
--

CREATE TABLE `backup_logs` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `backup_type` enum('full','incremental','differential') NOT NULL,
  `status` enum('running','completed','failed','cancelled') NOT NULL DEFAULT 'running',
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `duration_seconds` int(11) DEFAULT NULL,
  `backup_size_bytes` bigint(20) DEFAULT NULL,
  `backup_path` varchar(500) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `executed_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `backup_schedules`
--

CREATE TABLE `backup_schedules` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `backup_type` enum('full','incremental','differential') NOT NULL DEFAULT 'full',
  `schedule_type` enum('daily','weekly','monthly','custom') NOT NULL DEFAULT 'daily',
  `schedule_time` time NOT NULL DEFAULT '02:00:00',
  `schedule_days` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'For weekly/monthly schedules' CHECK (json_valid(`schedule_days`)),
  `retention_days` int(11) NOT NULL DEFAULT 30,
  `backup_path` varchar(500) NOT NULL,
  `include_files` tinyint(1) NOT NULL DEFAULT 1,
  `include_database` tinyint(1) NOT NULL DEFAULT 1,
  `compression` tinyint(1) NOT NULL DEFAULT 1,
  `encryption` tinyint(1) NOT NULL DEFAULT 0,
  `encryption_key` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_run` timestamp NULL DEFAULT NULL,
  `next_run` timestamp NULL DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cases`
--

CREATE TABLE `cases` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `case_type` enum('medical_negligence','product_liability','motor_vehicle','premises_liability','general_injury','other') DEFAULT 'other',
  `status` enum('draft','active','under_review','closed') NOT NULL DEFAULT 'draft',
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cases`
--

INSERT INTO `cases` (`id`, `user_id`, `assigned_to`, `title`, `description`, `case_type`, `status`, `priority`, `created_at`, `updated_at`) VALUES
(24001, 21001, 20007, 'MVA – Rear-end collision', 'Client suffered whiplash/back pain.', 'motor_vehicle', 'active', 'medium', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(24002, 21002, 20008, 'Medical negligence – surgical error', 'Complications after procedure.', 'medical_negligence', 'under_review', 'high', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(24003, 21003, 20009, 'Premises liability – supermarket fall', 'Hip injury from slip and fall.', 'premises_liability', 'draft', 'medium', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(24004, 21004, 20007, 'Product liability – defective ladder', 'Multiple fractures from fall.', 'product_liability', 'active', 'high', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(24005, 21005, 20009, 'General injury – dog bite', 'Lacerations and infection.', 'general_injury', 'active', 'low', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(24006, 21006, 20007, 'MVA – hit and run', 'Soft tissue injuries, lost income.', 'motor_vehicle', 'under_review', 'urgent', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(24007, 21007, 20008, 'Medical negligence – misdiagnosis', 'Delayed treatment worsened outcome.', 'medical_negligence', 'draft', 'high', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(24008, 21008, 20009, 'Premises liability – unsafe stairs', 'Knee ligament damage.', 'premises_liability', 'active', 'medium', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(24009, 21009, 20007, 'Product liability – faulty appliance', 'Burn injuries.', 'product_liability', 'active', 'high', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(24010, 21010, 20008, 'General injury – workplace accident', 'Hand crush injury.', 'general_injury', 'under_review', 'urgent', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(24011, 21062, 20011, 'General injury – misdiagnosis', 'Lorem ipsum case description with contextual details and client background.', 'premises_liability', 'active', 'urgent', '2025-02-09 13:24:14', '2025-09-15 13:24:14'),
(24012, 21062, 21015, 'Other – misdiagnosis', 'Lorem ipsum case description with contextual details and client background.', 'other', 'under_review', 'high', '2025-03-28 13:24:14', '2025-07-24 13:24:14'),
(24013, 21072, 21019, 'Motor vehicle – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'medical_negligence', 'draft', 'high', '2025-07-12 13:24:14', '2025-07-16 13:24:14'),
(24014, 21072, 20010, 'Other – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'other', 'under_review', 'urgent', '2025-05-12 13:24:14', '2025-07-12 13:24:14'),
(24015, 21079, 20008, 'Premises liability – rear-end collision', 'Lorem ipsum case description with contextual details and client background.', 'general_injury', 'draft', 'high', '2025-07-29 13:24:14', '2025-08-08 13:24:14'),
(24016, 21079, 20011, 'Other – slip-and-fall', 'Lorem ipsum case description with contextual details and client background.', 'medical_negligence', 'under_review', 'urgent', '2025-03-03 13:24:14', '2025-08-11 13:24:14'),
(24017, 21045, 21018, 'Motor vehicle – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'general_injury', 'draft', 'high', '2025-03-15 13:24:14', '2025-09-18 13:24:14'),
(24018, 21045, 20008, 'Medical negligence – slip-and-fall', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'under_review', 'medium', '2025-09-11 13:24:14', '2025-07-16 13:24:14'),
(24019, 21052, 20008, 'Product liability – slip-and-fall', 'Lorem ipsum case description with contextual details and client background.', 'medical_negligence', 'under_review', 'high', '2025-05-20 13:24:14', '2025-09-09 13:24:14'),
(24020, 21052, 21019, 'Product liability – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'other', 'active', 'medium', '2025-04-09 13:24:14', '2025-08-05 13:24:14'),
(24021, 21078, 20006, 'General injury – surgical error', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'active', 'urgent', '2025-09-30 13:24:14', '2025-07-16 13:24:14'),
(24022, 21078, 21017, 'Medical negligence – rear-end collision', 'Lorem ipsum case description with contextual details and client background.', 'premises_liability', 'under_review', 'low', '2025-05-26 13:24:14', '2025-08-28 13:24:14'),
(24023, 21042, 20008, 'Product liability – slip-and-fall', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'closed', 'urgent', '2025-07-01 13:24:14', '2025-07-21 13:24:14'),
(24024, 21042, 21019, 'Premises liability – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'medical_negligence', 'active', 'low', '2025-03-29 13:24:14', '2025-08-12 13:24:14'),
(24025, 21057, 21020, 'General injury – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'medical_negligence', 'under_review', 'low', '2025-01-16 13:24:14', '2025-09-15 13:24:14'),
(24026, 21057, 20008, 'Other – surgical error', 'Lorem ipsum case description with contextual details and client background.', 'general_injury', 'under_review', 'high', '2025-07-17 13:24:14', '2025-08-23 13:24:14'),
(24027, 21037, 21018, 'General injury – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'premises_liability', 'closed', 'low', '2025-09-11 13:24:14', '2025-08-20 13:24:14'),
(24028, 21037, 20006, 'Other – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'general_injury', 'active', 'high', '2025-04-28 13:24:14', '2025-09-09 13:24:14'),
(24029, 21051, 21024, 'Product liability – misdiagnosis', 'Lorem ipsum case description with contextual details and client background.', 'premises_liability', 'draft', 'low', '2025-06-18 13:24:14', '2025-09-19 13:24:14'),
(24030, 21051, 21017, 'Medical negligence – misdiagnosis', 'Lorem ipsum case description with contextual details and client background.', 'premises_liability', 'draft', 'high', '2025-10-27 13:24:14', '2025-10-23 13:24:14'),
(24031, 21075, 21024, 'Premises liability – slip-and-fall', 'Lorem ipsum case description with contextual details and client background.', 'other', 'closed', 'urgent', '2024-12-05 13:24:14', '2025-07-31 13:24:14'),
(24032, 21075, 21024, 'Motor vehicle – rear-end collision', 'Lorem ipsum case description with contextual details and client background.', 'product_liability', 'under_review', 'urgent', '2025-07-24 13:24:14', '2025-07-01 13:24:14'),
(24033, 21004, 21016, 'Other – defective device', 'Lorem ipsum case description with contextual details and client background.', 'premises_liability', 'active', 'urgent', '2025-10-27 13:24:14', '2025-08-19 13:24:14'),
(24034, 21004, 20006, 'General injury – rear-end collision', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'draft', 'urgent', '2025-06-29 13:24:14', '2025-10-24 13:24:14'),
(24035, 21008, 20008, 'General injury – slip-and-fall', 'Lorem ipsum case description with contextual details and client background.', 'medical_negligence', 'under_review', 'high', '2025-10-24 13:24:14', '2025-09-07 13:24:14'),
(24036, 21008, 21019, 'Medical negligence – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'closed', 'low', '2025-04-05 13:24:14', '2025-07-23 13:24:14'),
(24037, 21048, 20010, 'Premises liability – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'other', 'under_review', 'medium', '2025-07-10 13:24:14', '2025-10-15 13:24:14'),
(24038, 21048, 21018, 'Premises liability – rear-end collision', 'Lorem ipsum case description with contextual details and client background.', 'other', 'under_review', 'medium', '2025-09-12 13:24:14', '2025-09-13 13:24:14'),
(24039, 21049, 20010, 'Medical negligence – defective device', 'Lorem ipsum case description with contextual details and client background.', 'product_liability', 'under_review', 'high', '2024-11-24 13:24:14', '2025-08-13 13:24:14'),
(24040, 21049, 20009, 'Other – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'general_injury', 'closed', 'high', '2025-01-07 13:24:14', '2025-10-06 13:24:14'),
(24041, 21076, 21023, 'Premises liability – defective device', 'Lorem ipsum case description with contextual details and client background.', 'medical_negligence', 'closed', 'high', '2024-12-08 13:24:14', '2025-07-23 13:24:14'),
(24042, 21076, 21017, 'Motor vehicle – slip-and-fall', 'Lorem ipsum case description with contextual details and client background.', 'premises_liability', 'draft', 'low', '2025-05-03 13:24:14', '2025-07-15 13:24:14'),
(24043, 21055, 21016, 'Product liability – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'medical_negligence', 'active', 'medium', '2025-04-11 13:24:14', '2025-07-20 13:24:14'),
(24044, 21055, 20006, 'Motor vehicle – slip-and-fall', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'active', 'high', '2025-02-26 13:24:14', '2025-08-02 13:24:14'),
(24045, 21044, 21023, 'Other – slip-and-fall', 'Lorem ipsum case description with contextual details and client background.', 'premises_liability', 'active', 'medium', '2025-05-18 13:24:14', '2025-07-16 13:24:14'),
(24046, 21044, 21020, 'General injury – surgical error', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'closed', 'high', '2025-04-02 13:24:14', '2025-09-14 13:24:14'),
(24047, 21085, 21016, 'Medical negligence – slip-and-fall', 'Lorem ipsum case description with contextual details and client background.', 'product_liability', 'closed', 'urgent', '2025-10-03 13:24:14', '2025-10-26 13:24:14'),
(24048, 21085, 21020, 'General injury – defective device', 'Lorem ipsum case description with contextual details and client background.', 'medical_negligence', 'closed', 'medium', '2025-10-03 13:24:14', '2025-07-08 13:24:14'),
(24049, 21046, 20007, 'Other – rear-end collision', 'Lorem ipsum case description with contextual details and client background.', 'premises_liability', 'active', 'high', '2025-05-17 13:24:14', '2025-08-18 13:24:14'),
(24050, 21046, 21015, 'Other – defective device', 'Lorem ipsum case description with contextual details and client background.', 'medical_negligence', 'under_review', 'low', '2025-04-01 13:24:14', '2025-09-01 13:24:14'),
(24051, 21067, 20007, 'Other – surgical error', 'Lorem ipsum case description with contextual details and client background.', 'premises_liability', 'closed', 'low', '2025-02-11 13:24:14', '2025-08-21 13:24:14'),
(24052, 21067, 20006, 'Motor vehicle – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'premises_liability', 'under_review', 'high', '2024-12-08 13:24:14', '2025-07-28 13:24:14'),
(24053, 21005, 21017, 'General injury – rear-end collision', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'closed', 'low', '2025-09-05 13:24:14', '2025-09-23 13:24:14'),
(24054, 21005, 21019, 'Other – rear-end collision', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'under_review', 'medium', '2024-12-03 13:24:14', '2025-09-16 13:24:14'),
(24055, 21006, 20004, 'Other – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'product_liability', 'closed', 'low', '2025-06-17 13:24:14', '2025-08-05 13:24:14'),
(24056, 21006, 20006, 'General injury – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'other', 'closed', 'high', '2025-01-23 13:24:14', '2025-07-16 13:24:14'),
(24057, 21065, 20004, 'Medical negligence – misdiagnosis', 'Lorem ipsum case description with contextual details and client background.', 'medical_negligence', 'closed', 'medium', '2025-09-21 13:24:14', '2025-09-20 13:24:14'),
(24058, 21065, 21016, 'Premises liability – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'closed', 'medium', '2025-08-19 13:24:14', '2025-08-31 13:24:14'),
(24059, 21060, 21023, 'Motor vehicle – rear-end collision', 'Lorem ipsum case description with contextual details and client background.', 'general_injury', 'under_review', 'high', '2025-03-24 13:24:14', '2025-07-01 13:24:14'),
(24060, 21060, 21018, 'Product liability – surgical error', 'Lorem ipsum case description with contextual details and client background.', 'premises_liability', 'draft', 'high', '2025-02-10 13:24:14', '2025-07-28 13:24:14'),
(24061, 21009, 21015, 'Other – surgical error', 'Lorem ipsum case description with contextual details and client background.', 'medical_negligence', 'under_review', 'high', '2025-06-04 13:24:14', '2025-08-29 13:24:14'),
(24062, 21009, 20006, 'Premises liability – misdiagnosis', 'Lorem ipsum case description with contextual details and client background.', 'premises_liability', 'under_review', 'urgent', '2025-08-09 13:24:14', '2025-07-22 13:24:14'),
(24063, 21043, 21023, 'Other – rear-end collision', 'Lorem ipsum case description with contextual details and client background.', 'premises_liability', 'active', 'urgent', '2025-02-20 13:24:14', '2025-08-02 13:24:14'),
(24064, 21043, 20007, 'General injury – misdiagnosis', 'Lorem ipsum case description with contextual details and client background.', 'product_liability', 'under_review', 'urgent', '2024-11-21 13:24:14', '2025-10-26 13:24:14'),
(24065, 21082, 21015, 'Medical negligence – misdiagnosis', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'active', 'high', '2025-04-17 13:24:14', '2025-10-15 13:24:14'),
(24066, 21082, 20006, 'Premises liability – defective device', 'Lorem ipsum case description with contextual details and client background.', 'premises_liability', 'closed', 'medium', '2025-02-28 13:24:14', '2025-10-14 13:24:14'),
(24067, 21039, 20006, 'General injury – rear-end collision', 'Lorem ipsum case description with contextual details and client background.', 'general_injury', 'under_review', 'high', '2025-02-28 13:24:14', '2025-09-28 13:24:14'),
(24068, 21039, 21023, 'Other – rear-end collision', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'draft', 'urgent', '2025-10-11 13:24:14', '2025-08-04 13:24:14'),
(24069, 21063, 20007, 'Premises liability – misdiagnosis', 'Lorem ipsum case description with contextual details and client background.', 'medical_negligence', 'draft', 'low', '2025-10-17 13:24:14', '2025-08-03 13:24:14'),
(24070, 21063, 20006, 'Medical negligence – slip-and-fall', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'under_review', 'urgent', '2025-07-13 13:24:14', '2025-07-03 13:24:14'),
(24071, 21050, 20011, 'Premises liability – misdiagnosis', 'Lorem ipsum case description with contextual details and client background.', 'general_injury', 'closed', 'urgent', '2025-02-21 13:24:14', '2025-10-24 13:24:14'),
(24072, 21050, 20004, 'Motor vehicle – surgical error', 'Lorem ipsum case description with contextual details and client background.', 'premises_liability', 'active', 'high', '2025-07-19 13:24:14', '2025-07-23 13:24:14'),
(24073, 21038, 21020, 'Other – misdiagnosis', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'closed', 'low', '2025-02-05 13:24:14', '2025-09-11 13:24:14'),
(24074, 21038, 21023, 'General injury – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'medical_negligence', 'draft', 'high', '2025-08-30 13:24:14', '2025-09-24 13:24:14'),
(24075, 21056, 21019, 'General injury – slip-and-fall', 'Lorem ipsum case description with contextual details and client background.', 'product_liability', 'closed', 'low', '2025-04-29 13:24:14', '2025-07-04 13:24:14'),
(24076, 21056, 21020, 'Premises liability – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'general_injury', 'closed', 'medium', '2025-01-05 13:24:14', '2025-10-14 13:24:14'),
(24077, 21001, 21020, 'General injury – slip-and-fall', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'active', 'high', '2025-10-08 13:24:14', '2025-09-15 13:24:14'),
(24078, 21001, 21015, 'Medical negligence – defective device', 'Lorem ipsum case description with contextual details and client background.', 'premises_liability', 'draft', 'high', '2025-07-10 13:24:14', '2025-09-19 13:24:14'),
(24079, 21069, 21018, 'General injury – surgical error', 'Lorem ipsum case description with contextual details and client background.', 'general_injury', 'under_review', 'medium', '2025-01-14 13:24:14', '2025-07-25 13:24:14'),
(24080, 21069, 21024, 'Other – slip-and-fall', 'Lorem ipsum case description with contextual details and client background.', 'product_liability', 'draft', 'urgent', '2025-01-21 13:24:14', '2025-08-28 13:24:14'),
(24081, 21083, 21018, 'Premises liability – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'medical_negligence', 'active', 'urgent', '2024-12-12 13:24:14', '2025-08-12 13:24:14'),
(24082, 21083, 21020, 'Other – surgical error', 'Lorem ipsum case description with contextual details and client background.', 'general_injury', 'under_review', 'low', '2025-06-16 13:24:14', '2025-09-19 13:24:14'),
(24083, 21084, 21016, 'Medical negligence – surgical error', 'Lorem ipsum case description with contextual details and client background.', 'general_injury', 'closed', 'high', '2025-08-10 13:24:14', '2025-07-13 13:24:14'),
(24084, 21084, 21023, 'General injury – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'product_liability', 'active', 'medium', '2025-08-30 13:24:14', '2025-07-09 13:24:14'),
(24085, 21047, 21019, 'General injury – misdiagnosis', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'draft', 'high', '2025-02-13 13:24:14', '2025-08-14 13:24:14'),
(24086, 21047, 20011, 'General injury – slip-and-fall', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'active', 'medium', '2025-03-04 13:24:14', '2025-09-08 13:24:14'),
(24087, 21081, 20004, 'Motor vehicle – misdiagnosis', 'Lorem ipsum case description with contextual details and client background.', 'product_liability', 'draft', 'urgent', '2025-10-20 13:24:14', '2025-08-22 13:24:14'),
(24088, 21081, 20007, 'Premises liability – defective device', 'Lorem ipsum case description with contextual details and client background.', 'general_injury', 'draft', 'low', '2025-09-15 13:24:14', '2025-09-14 13:24:14'),
(24089, 21010, 21020, 'Medical negligence – misdiagnosis', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'active', 'medium', '2025-08-13 13:24:14', '2025-08-08 13:24:14'),
(24090, 21010, 20010, 'General injury – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'other', 'active', 'high', '2025-02-21 13:24:14', '2025-09-27 13:24:14'),
(24091, 21066, 21024, 'Product liability – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'premises_liability', 'active', 'medium', '2025-07-06 13:24:14', '2025-08-09 13:24:14'),
(24092, 21066, 21024, 'General injury – misdiagnosis', 'Lorem ipsum case description with contextual details and client background.', 'general_injury', 'closed', 'urgent', '2025-08-17 13:24:14', '2025-10-06 13:24:14'),
(24093, 21002, 20011, 'Medical negligence – defective device', 'Lorem ipsum case description with contextual details and client background.', 'medical_negligence', 'active', 'urgent', '2025-04-29 13:24:14', '2025-08-08 13:24:14'),
(24094, 21002, 21024, 'Other – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'draft', 'medium', '2025-03-16 13:24:14', '2025-07-11 13:24:14'),
(24095, 21071, 20009, 'Motor vehicle – slip-and-fall', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'under_review', 'high', '2025-07-23 13:24:14', '2025-08-24 13:24:14'),
(24096, 21071, 20011, 'Medical negligence – rear-end collision', 'Lorem ipsum case description with contextual details and client background.', 'other', 'under_review', 'urgent', '2025-08-20 13:24:14', '2025-08-09 13:24:14'),
(24097, 21068, 21017, 'General injury – surgical error', 'Lorem ipsum case description with contextual details and client background.', 'medical_negligence', 'closed', 'high', '2025-02-13 13:24:14', '2025-09-05 13:24:14'),
(24098, 21068, 21020, 'Other – rear-end collision', 'Lorem ipsum case description with contextual details and client background.', 'other', 'under_review', 'urgent', '2025-09-04 13:24:14', '2025-10-15 13:24:14'),
(24099, 21077, 21018, 'Motor vehicle – misdiagnosis', 'Lorem ipsum case description with contextual details and client background.', 'other', 'active', 'medium', '2025-03-16 13:24:14', '2025-08-09 13:24:14'),
(24100, 21077, 20007, 'Premises liability – defective device', 'Lorem ipsum case description with contextual details and client background.', 'other', 'under_review', 'urgent', '2025-07-28 13:24:14', '2025-09-11 13:24:14'),
(24101, 21073, 20011, 'Premises liability – defective device', 'Lorem ipsum case description with contextual details and client background.', 'general_injury', 'draft', 'high', '2025-04-29 13:24:14', '2025-08-13 13:24:14'),
(24102, 21073, 20010, 'General injury – surgical error', 'Lorem ipsum case description with contextual details and client background.', 'medical_negligence', 'draft', 'urgent', '2025-09-12 13:24:14', '2025-07-29 13:24:14'),
(24103, 21059, 20008, 'Premises liability – defective device', 'Lorem ipsum case description with contextual details and client background.', 'product_liability', 'under_review', 'medium', '2025-02-10 13:24:14', '2025-08-11 13:24:14'),
(24104, 21059, 20004, 'Other – misdiagnosis', 'Lorem ipsum case description with contextual details and client background.', 'medical_negligence', 'under_review', 'high', '2024-12-31 13:24:14', '2025-08-08 13:24:14'),
(24105, 21064, 20008, 'Medical negligence – defective device', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'under_review', 'medium', '2025-09-09 13:24:14', '2025-09-15 13:24:14'),
(24106, 21064, 20006, 'Premises liability – defective device', 'Lorem ipsum case description with contextual details and client background.', 'general_injury', 'draft', 'low', '2025-07-15 13:24:14', '2025-10-12 13:24:14'),
(24107, 21053, 21018, 'Product liability – slip-and-fall', 'Lorem ipsum case description with contextual details and client background.', 'product_liability', 'under_review', 'high', '2024-12-27 13:24:14', '2025-08-28 13:24:14'),
(24108, 21053, 21023, 'Product liability – dog bite', 'Lorem ipsum case description with contextual details and client background.', 'motor_vehicle', 'active', 'low', '2024-11-10 13:24:14', '2025-09-20 13:24:14'),
(24109, 21058, 21019, 'Premises liability – rear-end collision', 'Lorem ipsum case description with contextual details and client background.', 'premises_liability', 'under_review', 'low', '2025-06-24 13:24:14', '2025-07-31 13:24:14'),
(24110, 21058, 20011, 'Product liability – misdiagnosis', 'Lorem ipsum case description with contextual details and client background.', 'premises_liability', 'active', 'urgent', '2025-09-04 13:24:14', '2025-09-25 13:24:14');

-- --------------------------------------------------------

--
-- Table structure for table `case_activities`
--

CREATE TABLE `case_activities` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` enum('note','status_change','document_upload','service_request','admin_action') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `case_activities`
--

INSERT INTO `case_activities` (`id`, `case_id`, `user_id`, `activity_type`, `title`, `description`, `metadata`, `created_at`) VALUES
(1, 24003, 20009, 'document_upload', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-10-07 13:24:14'),
(2, 24003, 21017, 'document_upload', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-09-24 13:24:14'),
(3, 24003, 20008, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-17 13:24:14'),
(4, 24003, 21023, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-06 13:24:14'),
(5, 24007, 21023, 'note', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-22 13:24:14'),
(6, 24007, 21013, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-10-23 13:24:14'),
(7, 24007, 20004, 'note', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-10-12 13:24:14'),
(8, 24007, 1, 'document_upload', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-30 13:24:14'),
(9, 24013, 21016, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-09-18 13:24:14'),
(10, 24013, 21016, 'service_request', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-29 13:24:14'),
(11, 24013, 1, 'document_upload', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-03 13:24:14'),
(12, 24013, 20006, 'service_request', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-08-03 13:24:14'),
(13, 24015, 21014, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-01 13:24:14'),
(14, 24015, 21036, 'service_request', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-11 13:24:14'),
(15, 24015, 20011, 'status_change', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-20 13:24:14'),
(16, 24015, 21024, 'document_upload', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-08-18 13:24:14'),
(17, 24017, 20010, 'status_change', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-10-06 13:24:14'),
(18, 24017, 20009, 'service_request', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-10-24 13:24:14'),
(19, 24017, 21013, 'service_request', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-10-16 13:24:14'),
(20, 24017, 21036, 'document_upload', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-29 13:24:14'),
(21, 24029, 21017, 'service_request', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-12 13:24:14'),
(22, 24029, 1, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-07-26 13:24:14'),
(23, 24029, 21035, 'admin_action', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-31 13:24:14'),
(24, 24029, 21017, 'status_change', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-07-29 13:24:14'),
(25, 24030, 21014, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-07-02 13:24:14'),
(26, 24030, 20007, 'note', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-16 13:24:14'),
(27, 24030, 21017, 'note', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-16 13:24:14'),
(28, 24030, 21013, 'document_upload', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-20 13:24:14'),
(29, 24034, 20010, 'admin_action', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-10-22 13:24:14'),
(30, 24034, 21017, 'service_request', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-17 13:24:14'),
(31, 24034, 20004, 'service_request', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-09-09 13:24:14'),
(32, 24034, 21019, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-08-16 13:24:14'),
(33, 24042, 21018, 'service_request', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-08 13:24:14'),
(34, 24042, 20004, 'admin_action', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-09-20 13:24:14'),
(35, 24042, 21020, 'note', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-08-07 13:24:14'),
(36, 24042, 21020, 'service_request', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-07-18 13:24:14'),
(37, 24060, 21019, 'service_request', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-09-30 13:24:14'),
(38, 24060, 20008, 'admin_action', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-22 13:24:14'),
(39, 24060, 20004, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-20 13:24:14'),
(40, 24060, 20011, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-12 13:24:14'),
(41, 24068, 20009, 'document_upload', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-09-22 13:24:14'),
(42, 24068, 21016, 'service_request', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-08-10 13:24:14'),
(43, 24068, 20011, 'note', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-08-10 13:24:14'),
(44, 24069, 20011, 'service_request', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-07-17 13:24:14'),
(45, 24069, 20004, 'service_request', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-08-08 13:24:14'),
(46, 24069, 20010, 'document_upload', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-12 13:24:14'),
(47, 24069, 20004, 'note', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-22 13:24:14'),
(48, 24074, 20010, 'admin_action', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-09-27 13:24:14'),
(49, 24074, 21019, 'service_request', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-09-16 13:24:14'),
(50, 24074, 20010, 'document_upload', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-08-21 13:24:14'),
(51, 24074, 21018, 'admin_action', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-24 13:24:14'),
(52, 24078, 20008, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-28 13:24:14'),
(53, 24078, 21017, 'service_request', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-10-22 13:24:14'),
(54, 24078, 20010, 'note', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-07-15 13:24:14'),
(55, 24078, 20009, 'service_request', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-07-05 13:24:14'),
(56, 24080, 20006, 'admin_action', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-07-07 13:24:14'),
(57, 24080, 20007, 'document_upload', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-10-23 13:24:14'),
(58, 24080, 20007, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-07-26 13:24:14'),
(59, 24080, 20004, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-22 13:24:14'),
(60, 24085, 20007, 'document_upload', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-09-18 13:24:14'),
(61, 24085, 20004, 'document_upload', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-14 13:24:14'),
(62, 24085, 20009, 'status_change', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-07-25 13:24:14'),
(63, 24085, 20010, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-07-01 13:24:14'),
(64, 24087, 1, 'service_request', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-10-20 13:24:14'),
(65, 24087, 20009, 'note', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-28 13:24:14'),
(66, 24087, 21036, 'document_upload', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-10-06 13:24:14'),
(67, 24087, 20004, 'note', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-15 13:24:14'),
(68, 24088, 1, 'service_request', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-09-19 13:24:14'),
(69, 24088, 21013, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-07-10 13:24:14'),
(70, 24088, 20011, 'status_change', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-09-14 13:24:14'),
(71, 24088, 21016, 'note', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-09-28 13:24:14'),
(72, 24094, 20010, 'document_upload', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-09-11 13:24:14'),
(73, 24094, 21020, 'document_upload', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-10-05 13:24:14'),
(74, 24094, 20007, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-21 13:24:14'),
(75, 24094, 20010, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-05 13:24:14'),
(76, 24101, 20011, 'status_change', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-10-23 13:24:14'),
(77, 24101, 21018, 'note', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-08-22 13:24:14'),
(78, 24101, 21035, 'note', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-09-07 13:24:14'),
(79, 24101, 20007, 'status_change', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-10-19 13:24:14'),
(80, 24102, 21024, 'document_upload', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-10-09 13:24:14'),
(81, 24102, 21036, 'note', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-31 13:24:14'),
(82, 24102, 21018, 'admin_action', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-10-04 13:24:14'),
(83, 24102, 1, 'service_request', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-10-02 13:24:14'),
(84, 24106, 20005, 'status_change', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-06-30 13:24:14'),
(85, 24106, 21024, 'service_request', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-09-14 13:24:14'),
(86, 24106, 21019, 'note', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-25 13:24:14'),
(87, 24106, 21024, 'admin_action', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-09-12 13:24:14'),
(88, 24001, 21035, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-07-04 13:24:14'),
(89, 24001, 21013, 'status_change', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-23 13:24:14'),
(90, 24001, 21015, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-10 13:24:14'),
(91, 24001, 20008, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-07-29 13:24:14'),
(92, 24004, 20007, 'service_request', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-10-11 13:24:14'),
(93, 24004, 21014, 'status_change', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-08-25 13:24:14'),
(94, 24004, 21017, 'service_request', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-07 13:24:14'),
(95, 24004, 21015, 'status_change', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-10-20 13:24:14'),
(96, 24005, 21035, 'admin_action', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-07-19 13:24:14'),
(97, 24005, 20011, 'service_request', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-09-09 13:24:14'),
(98, 24005, 21015, 'status_change', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-10-26 13:24:14'),
(99, 24005, 21035, 'document_upload', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-10-17 13:24:14'),
(100, 24008, 20004, 'document_upload', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-05 13:24:14'),
(101, 24008, 21018, 'admin_action', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-30 13:24:14'),
(102, 24008, 21016, 'status_change', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-08-23 13:24:14'),
(103, 24008, 20009, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-09-02 13:24:14'),
(104, 24009, 21020, 'service_request', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-13 13:24:14'),
(105, 24009, 21024, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-09 13:24:14'),
(106, 24009, 20007, 'note', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-09-22 13:24:14'),
(107, 24011, 21014, 'admin_action', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-08-03 13:24:14'),
(108, 24011, 20006, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-10 13:24:14'),
(109, 24011, 21019, 'admin_action', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-09-13 13:24:14'),
(110, 24011, 20011, 'status_change', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-24 13:24:14'),
(111, 24020, 20010, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-08 13:24:14'),
(112, 24020, 20009, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-30 13:24:14'),
(113, 24020, 20009, 'document_upload', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-10-25 13:24:14'),
(114, 24020, 20004, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-21 13:24:14'),
(115, 24021, 20010, 'status_change', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-09-24 13:24:14'),
(116, 24021, 20010, 'service_request', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-07-10 13:24:14'),
(117, 24021, 20011, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-10-26 13:24:14'),
(118, 24021, 20005, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-09-03 13:24:14'),
(119, 24024, 20010, 'document_upload', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-09-04 13:24:14'),
(120, 24024, 21036, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-20 13:24:14'),
(121, 24024, 21024, 'admin_action', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-09-01 13:24:14'),
(122, 24024, 21036, 'service_request', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-07-31 13:24:14'),
(123, 24028, 20008, 'document_upload', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-10-15 13:24:14'),
(124, 24028, 21018, 'document_upload', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-20 13:24:14'),
(125, 24028, 21023, 'status_change', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-25 13:24:14'),
(126, 24028, 21013, 'document_upload', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-08-19 13:24:14'),
(127, 24033, 20005, 'note', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-10-22 13:24:14'),
(128, 24033, 20011, 'status_change', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-28 13:24:14'),
(129, 24033, 20007, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-27 13:24:14'),
(130, 24033, 20005, 'service_request', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-07-03 13:24:14'),
(131, 24043, 21014, 'document_upload', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-25 13:24:14'),
(132, 24043, 20010, 'service_request', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-25 13:24:14'),
(133, 24043, 20009, 'note', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-07-25 13:24:14'),
(134, 24043, 21023, 'service_request', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-18 13:24:14'),
(135, 24044, 21014, 'service_request', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-08-19 13:24:14'),
(136, 24044, 21014, 'admin_action', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-25 13:24:14'),
(137, 24044, 21017, 'status_change', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-08-29 13:24:14'),
(138, 24044, 1, 'admin_action', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-10-24 13:24:14'),
(139, 24045, 20011, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-05 13:24:14'),
(140, 24045, 21020, 'status_change', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-08-13 13:24:14'),
(141, 24045, 21018, 'document_upload', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-04 13:24:14'),
(142, 24045, 21016, 'service_request', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-11 13:24:14'),
(143, 24049, 21014, 'status_change', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-10-22 13:24:14'),
(144, 24049, 20009, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-14 13:24:14'),
(145, 24049, 21017, 'service_request', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-09-18 13:24:14'),
(146, 24063, 20006, 'service_request', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-10-23 13:24:14'),
(147, 24063, 1, 'service_request', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-23 13:24:14'),
(148, 24063, 21019, 'admin_action', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-10-25 13:24:14'),
(149, 24063, 21024, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-10-21 13:24:14'),
(150, 24065, 20006, 'service_request', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-06 13:24:14'),
(151, 24065, 21035, 'document_upload', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-09 13:24:14'),
(152, 24065, 20004, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-09-06 13:24:14'),
(153, 24072, 20009, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-07-20 13:24:14'),
(154, 24072, 21017, 'note', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-08-24 13:24:14'),
(155, 24072, 21023, 'document_upload', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-10-26 13:24:14'),
(156, 24077, 21016, 'admin_action', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-10-09 13:24:14'),
(157, 24077, 20004, 'document_upload', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-07-19 13:24:14'),
(158, 24077, 20005, 'note', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-23 13:24:14'),
(159, 24077, 20005, 'service_request', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-23 13:24:14'),
(160, 24081, 20008, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-25 13:24:14'),
(161, 24081, 21023, 'service_request', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-10-11 13:24:14'),
(162, 24081, 20005, 'service_request', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-23 13:24:14'),
(163, 24081, 1, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-09-12 13:24:14'),
(164, 24084, 21035, 'status_change', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-10-21 13:24:14'),
(165, 24084, 20009, 'document_upload', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-07 13:24:14'),
(166, 24084, 20006, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-09-14 13:24:14'),
(167, 24084, 1, 'document_upload', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-30 13:24:14'),
(168, 24086, 21020, 'status_change', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-21 13:24:14'),
(169, 24086, 21019, 'status_change', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-06 13:24:14'),
(170, 24086, 20004, 'status_change', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-12 13:24:14'),
(171, 24086, 20009, 'status_change', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-10-12 13:24:14'),
(172, 24089, 21023, 'document_upload', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-26 13:24:14'),
(173, 24089, 20004, 'document_upload', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-08-30 13:24:14'),
(174, 24089, 20009, 'admin_action', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-10 13:24:14'),
(175, 24089, 1, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-10-04 13:24:14'),
(176, 24090, 20010, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-10-04 13:24:14'),
(177, 24090, 20004, 'admin_action', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-07-08 13:24:14'),
(178, 24090, 21024, 'admin_action', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-10-10 13:24:14'),
(179, 24090, 21017, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-09-07 13:24:14'),
(180, 24091, 21015, 'status_change', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-08-01 13:24:14'),
(181, 24091, 21016, 'document_upload', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-22 13:24:14'),
(182, 24091, 21020, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-10-21 13:24:14'),
(183, 24091, 1, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-09 13:24:14'),
(184, 24093, 20009, 'document_upload', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-08-18 13:24:14'),
(185, 24093, 20008, 'status_change', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-20 13:24:14'),
(186, 24093, 20007, 'document_upload', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-09-19 13:24:14'),
(187, 24093, 21020, 'document_upload', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-09 13:24:14'),
(188, 24099, 21023, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-26 13:24:14'),
(189, 24099, 20005, 'status_change', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-08-12 13:24:14'),
(190, 24099, 21013, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-07-09 13:24:14'),
(191, 24108, 21023, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-14 13:24:14'),
(192, 24108, 20011, 'status_change', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-08-11 13:24:14'),
(193, 24108, 21023, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-09-12 13:24:14'),
(194, 24108, 20005, 'admin_action', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-09-05 13:24:14'),
(195, 24110, 20006, 'status_change', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-06-30 13:24:14'),
(196, 24110, 20004, 'service_request', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-10-11 13:24:14'),
(197, 24110, 21023, 'note', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-10-25 13:24:14'),
(198, 24110, 20005, 'service_request', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-29 13:24:14'),
(199, 24002, 21024, 'note', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-24 13:24:14'),
(200, 24002, 21036, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-09-29 13:24:14'),
(201, 24002, 20005, 'admin_action', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-19 13:24:14'),
(202, 24002, 21036, 'service_request', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-14 13:24:14'),
(203, 24006, 20007, 'document_upload', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-10-19 13:24:14'),
(204, 24006, 21016, 'service_request', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-04 13:24:14'),
(205, 24006, 21024, 'note', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-23 13:24:14'),
(206, 24010, 20007, 'service_request', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-26 13:24:14'),
(207, 24010, 21023, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-10-17 13:24:14'),
(208, 24010, 20009, 'service_request', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-10-03 13:24:14'),
(209, 24012, 20005, 'note', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-10-25 13:24:14'),
(210, 24012, 21016, 'service_request', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-13 13:24:14'),
(211, 24012, 21015, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-10-24 13:24:14'),
(212, 24012, 21017, 'admin_action', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-07-12 13:24:14'),
(213, 24014, 20008, 'service_request', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-10-25 13:24:14'),
(214, 24014, 20004, 'document_upload', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-09-22 13:24:14'),
(215, 24014, 20004, 'admin_action', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-07-19 13:24:14'),
(216, 24014, 21018, 'document_upload', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-10-15 13:24:14'),
(217, 24016, 20011, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-08 13:24:14'),
(218, 24016, 21020, 'note', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-09-16 13:24:14'),
(219, 24016, 21019, 'service_request', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-12 13:24:14'),
(220, 24016, 21015, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-10-13 13:24:14'),
(221, 24018, 20010, 'service_request', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-07-19 13:24:14'),
(222, 24018, 21015, 'document_upload', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-08-18 13:24:14'),
(223, 24018, 21023, 'document_upload', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-09-23 13:24:14'),
(224, 24018, 21035, 'status_change', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-02 13:24:14'),
(225, 24019, 20010, 'status_change', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-10-13 13:24:14'),
(226, 24019, 21018, 'service_request', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-10-04 13:24:14'),
(227, 24019, 21023, 'service_request', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-02 13:24:14'),
(228, 24019, 20007, 'note', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-09-18 13:24:14'),
(229, 24022, 21013, 'note', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-08-22 13:24:14'),
(230, 24022, 20011, 'admin_action', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-10-05 13:24:14'),
(231, 24022, 20009, 'status_change', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-10-09 13:24:14'),
(232, 24025, 21014, 'note', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-09-11 13:24:14'),
(233, 24025, 21013, 'document_upload', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-08-23 13:24:14'),
(234, 24025, 21015, 'status_change', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-11 13:24:14'),
(235, 24025, 20008, 'service_request', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-07-15 13:24:14'),
(236, 24026, 21023, 'document_upload', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-09-23 13:24:14'),
(237, 24026, 21020, 'document_upload', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-09-18 13:24:14'),
(238, 24026, 20011, 'note', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-08-14 13:24:14'),
(239, 24032, 21035, 'document_upload', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-19 13:24:14'),
(240, 24032, 21018, 'note', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-25 13:24:14'),
(241, 24032, 20006, 'service_request', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-07-27 13:24:14'),
(242, 24032, 20011, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-09-04 13:24:14'),
(243, 24035, 20004, 'status_change', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-08-11 13:24:14'),
(244, 24035, 21020, 'service_request', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-21 13:24:14'),
(245, 24035, 21023, 'service_request', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-07-16 13:24:14'),
(246, 24037, 21023, 'status_change', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-10-27 13:24:14'),
(247, 24037, 21036, 'note', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-22 13:24:14'),
(248, 24037, 21019, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-10-19 13:24:14'),
(249, 24038, 20006, 'service_request', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-08-26 13:24:14'),
(250, 24038, 21019, 'note', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-10-20 13:24:14'),
(251, 24038, 20004, 'service_request', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-08 13:24:14'),
(252, 24038, 21036, 'document_upload', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-08-01 13:24:14'),
(253, 24039, 21036, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-23 13:24:14'),
(254, 24039, 21013, 'admin_action', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-10-01 13:24:14'),
(255, 24039, 20004, 'status_change', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-14 13:24:14'),
(256, 24039, 20009, 'status_change', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-07-22 13:24:14'),
(257, 24050, 20004, 'admin_action', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-07-10 13:24:14'),
(258, 24050, 21024, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-13 13:24:14'),
(259, 24050, 21015, 'document_upload', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-17 13:24:14'),
(260, 24050, 20004, 'note', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-10-27 13:24:14'),
(261, 24052, 20010, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-10-19 13:24:14'),
(262, 24052, 20004, 'note', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-16 13:24:14'),
(263, 24052, 21023, 'note', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-09-07 13:24:14'),
(264, 24052, 21019, 'admin_action', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-10-25 13:24:14'),
(265, 24054, 20006, 'note', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-08-02 13:24:14'),
(266, 24054, 21016, 'document_upload', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-08 13:24:14'),
(267, 24054, 20009, 'document_upload', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-18 13:24:14'),
(268, 24054, 20009, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-10-09 13:24:14'),
(269, 24059, 20011, 'note', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-10-01 13:24:14'),
(270, 24059, 21017, 'admin_action', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-08-23 13:24:14'),
(271, 24059, 21018, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-28 13:24:14'),
(272, 24059, 20007, 'document_upload', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-09-21 13:24:14'),
(273, 24061, 21023, 'note', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-09-11 13:24:14'),
(274, 24061, 21036, 'document_upload', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-08 13:24:14'),
(275, 24061, 21018, 'document_upload', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-10-01 13:24:14'),
(276, 24061, 21035, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-06-30 13:24:14'),
(277, 24062, 20005, 'note', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-08-22 13:24:14'),
(278, 24064, 20010, 'document_upload', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-10-14 13:24:14'),
(279, 24064, 21035, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-05 13:24:14'),
(280, 24064, 21015, 'note', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-10-17 13:24:14'),
(281, 24064, 20006, 'status_change', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-22 13:24:14'),
(282, 24067, 20010, 'status_change', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-10-16 13:24:14'),
(283, 24067, 20010, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-07-22 13:24:14'),
(284, 24067, 21018, 'note', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-09-27 13:24:14'),
(285, 24070, 21023, 'admin_action', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-09-28 13:24:14'),
(286, 24070, 21018, 'admin_action', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-27 13:24:14'),
(287, 24070, 20004, 'status_change', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-07-10 13:24:14'),
(288, 24070, 1, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-10-03 13:24:14'),
(289, 24079, 21020, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-25 13:24:14'),
(290, 24079, 21018, 'status_change', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-27 13:24:14'),
(291, 24079, 21014, 'status_change', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-26 13:24:14'),
(292, 24082, 21016, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-02 13:24:14'),
(293, 24082, 1, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-10-02 13:24:14'),
(294, 24082, 20005, 'service_request', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-10-02 13:24:14'),
(295, 24095, 20005, 'service_request', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-09-16 13:24:14'),
(296, 24095, 21020, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-21 13:24:14'),
(297, 24095, 21013, 'note', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-08-23 13:24:14'),
(298, 24095, 1, 'note', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-10-07 13:24:14'),
(299, 24096, 21036, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-12 13:24:14'),
(300, 24096, 21018, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-23 13:24:14'),
(301, 24096, 20005, 'document_upload', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-03 13:24:14'),
(302, 24096, 21020, 'service_request', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-07-29 13:24:14'),
(303, 24098, 21035, 'document_upload', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-10-24 13:24:14'),
(304, 24098, 21023, 'admin_action', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-14 13:24:14'),
(305, 24098, 21018, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-07 13:24:14'),
(306, 24100, 21020, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-21 13:24:14'),
(307, 24100, 20010, 'document_upload', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-08-03 13:24:14'),
(308, 24100, 21018, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-29 13:24:14'),
(309, 24103, 1, 'note', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-10-19 13:24:14'),
(310, 24103, 20008, 'service_request', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-05 13:24:14'),
(311, 24103, 20011, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-09-24 13:24:14'),
(312, 24103, 21018, 'note', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-08-19 13:24:14'),
(313, 24104, 20009, 'status_change', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-29 13:24:14'),
(314, 24104, 20006, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-10-22 13:24:14'),
(315, 24104, 21018, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-10-27 13:24:14'),
(316, 24104, 21036, 'service_request', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-18 13:24:14'),
(317, 24105, 21024, 'status_change', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-07-20 13:24:14'),
(318, 24105, 21024, 'admin_action', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-07-16 13:24:14'),
(319, 24105, 1, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-05 13:24:14'),
(320, 24105, 20008, 'service_request', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-10-08 13:24:14'),
(321, 24107, 1, 'note', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-08-09 13:24:14'),
(322, 24107, 20007, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-07-02 13:24:14'),
(323, 24107, 21013, 'admin_action', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-30 13:24:14'),
(324, 24107, 20010, 'admin_action', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-02 13:24:14'),
(325, 24109, 20010, 'admin_action', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-19 13:24:14'),
(326, 24109, 21035, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-09 13:24:14'),
(327, 24109, 20005, 'status_change', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-08-26 13:24:14'),
(328, 24109, 20008, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-25 13:24:14'),
(329, 24023, 21023, 'admin_action', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-19 13:24:14'),
(330, 24023, 21036, 'admin_action', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-27 13:24:14'),
(331, 24023, 20008, 'status_change', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-27 13:24:14'),
(332, 24023, 20006, 'document_upload', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-09-25 13:24:14'),
(333, 24027, 21016, 'status_change', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-15 13:24:14'),
(334, 24027, 21015, 'admin_action', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-09-15 13:24:14'),
(335, 24027, 20006, 'note', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-19 13:24:14'),
(336, 24027, 20010, 'service_request', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-07-22 13:24:14'),
(337, 24031, 21017, 'status_change', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-20 13:24:14'),
(338, 24031, 21013, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-10-11 13:24:14'),
(339, 24031, 20011, 'service_request', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-21 13:24:14'),
(340, 24036, 20007, 'service_request', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-07-18 13:24:14'),
(341, 24036, 21014, 'status_change', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-08 13:24:14'),
(342, 24036, 20011, 'admin_action', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-07-02 13:24:14'),
(343, 24036, 21023, 'note', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-08-11 13:24:14'),
(344, 24040, 21014, 'service_request', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-08-29 13:24:14'),
(345, 24040, 21016, 'document_upload', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-10-21 13:24:14'),
(346, 24040, 1, 'note', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-10-04 13:24:14'),
(347, 24040, 21035, 'status_change', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-19 13:24:14'),
(348, 24041, 21035, 'document_upload', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-09-24 13:24:14'),
(349, 24041, 21015, 'admin_action', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-07-09 13:24:14'),
(350, 24041, 21024, 'admin_action', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-07-05 13:24:14'),
(351, 24041, 1, 'status_change', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-02 13:24:14'),
(352, 24046, 20005, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-29 13:24:14'),
(353, 24046, 21015, 'note', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-06 13:24:14'),
(354, 24046, 21015, 'note', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-08-03 13:24:14'),
(355, 24046, 21017, 'status_change', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-10-19 13:24:14'),
(356, 24047, 21013, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-26 13:24:14'),
(357, 24047, 21035, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-19 13:24:14'),
(358, 24047, 1, 'admin_action', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-08-28 13:24:14'),
(359, 24048, 21013, 'note', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-07-07 13:24:14'),
(360, 24048, 20011, 'admin_action', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-12 13:24:14'),
(361, 24048, 21020, 'status_change', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-10-21 13:24:14'),
(362, 24051, 21013, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-10-18 13:24:14'),
(363, 24051, 20009, 'service_request', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-09-17 13:24:14'),
(364, 24053, 20011, 'document_upload', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-10-25 13:24:14'),
(365, 24053, 20004, 'admin_action', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-10-15 13:24:14'),
(366, 24053, 20011, 'status_change', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-10-22 13:24:14'),
(367, 24053, 21036, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-17 13:24:14'),
(368, 24055, 20006, 'document_upload', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-07-01 13:24:14'),
(369, 24055, 21016, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-15 13:24:14'),
(370, 24055, 21024, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-17 13:24:14'),
(371, 24055, 21020, 'service_request', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-08-29 13:24:14'),
(372, 24056, 20011, 'service_request', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-07-17 13:24:14'),
(373, 24056, 21024, 'document_upload', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-06 13:24:14'),
(374, 24056, 20009, 'note', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-28 13:24:14'),
(375, 24056, 21024, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-10-05 13:24:14'),
(376, 24057, 20006, 'admin_action', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-10-02 13:24:14'),
(377, 24057, 21019, 'document_upload', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-10-27 13:24:14'),
(378, 24057, 21020, 'admin_action', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-29 13:24:14'),
(379, 24057, 20007, 'service_request', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-09-20 13:24:14'),
(380, 24058, 21035, 'service_request', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-08-14 13:24:14'),
(381, 24058, 1, 'admin_action', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-07-18 13:24:14'),
(382, 24058, 21013, 'note', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-16 13:24:14');
INSERT INTO `case_activities` (`id`, `case_id`, `user_id`, `activity_type`, `title`, `description`, `metadata`, `created_at`) VALUES
(383, 24058, 21036, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-18 13:24:14'),
(384, 24066, 20006, 'service_request', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-07-23 13:24:14'),
(385, 24066, 21023, 'service_request', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-10-27 13:24:14'),
(386, 24066, 20008, 'admin_action', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-09-19 13:24:14'),
(387, 24066, 21016, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-07-13 13:24:14'),
(388, 24071, 21019, 'service_request', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-22 13:24:14'),
(389, 24071, 21024, 'status_change', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-14 13:24:14'),
(390, 24071, 21035, 'admin_action', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-10-13 13:24:14'),
(391, 24073, 21016, 'note', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-08-16 13:24:14'),
(392, 24073, 21018, 'admin_action', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-10-12 13:24:14'),
(393, 24073, 20010, 'note', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-09-23 13:24:14'),
(394, 24073, 21023, 'document_upload', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-14 13:24:14'),
(395, 24075, 21036, 'status_change', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-10-07 13:24:14'),
(396, 24075, 20006, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-08-02 13:24:14'),
(397, 24075, 21024, 'admin_action', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-09-22 13:24:14'),
(398, 24076, 21023, 'admin_action', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-10-21 13:24:14'),
(399, 24076, 20007, 'status_change', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-01 13:24:14'),
(400, 24076, 20004, 'service_request', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-26 13:24:14'),
(401, 24083, 21035, 'document_upload', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-09 13:24:14'),
(402, 24083, 20009, 'document_upload', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-10-06 13:24:14'),
(403, 24083, 21023, 'status_change', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-10-07 13:24:14'),
(404, 24092, 1, 'service_request', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-07-04 13:24:14'),
(405, 24092, 20010, 'service_request', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-21 13:24:14'),
(406, 24092, 21016, 'status_change', 'Document uploaded', 'Auto-generated activity for demo purposes', NULL, '2025-09-12 13:24:14'),
(407, 24092, 1, 'status_change', 'Administrative action', 'Auto-generated activity for demo purposes', NULL, '2025-08-12 13:24:14'),
(408, 24097, 21024, 'service_request', 'Case note added', 'Auto-generated activity for demo purposes', NULL, '2025-07-15 13:24:14'),
(409, 24097, 21035, 'service_request', 'Service request updated', 'Auto-generated activity for demo purposes', NULL, '2025-07-08 13:24:14'),
(410, 24097, 21035, 'admin_action', 'Status changed', 'Auto-generated activity for demo purposes', NULL, '2025-09-10 13:24:14');

-- --------------------------------------------------------

--
-- Table structure for table `case_documents`
--

CREATE TABLE `case_documents` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `document_type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `is_confidential` tinyint(1) NOT NULL DEFAULT 0,
  `version` int(11) NOT NULL DEFAULT 1,
  `parent_document_id` int(11) DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `checksum` varchar(64) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `case_documents`
--

INSERT INTO `case_documents` (`id`, `case_id`, `filename`, `original_filename`, `file_path`, `file_size`, `mime_type`, `document_type`, `description`, `uploaded_by`, `is_confidential`, `version`, `parent_document_id`, `is_current`, `checksum`, `uploaded_at`) VALUES
(1, 24001, 'doc_24001_1.pdf', 'Document_24001_1.pdf', 'cases/documents/21001/doc_24001_1.pdf', 119762, 'application/pdf', 'evidence', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, '7C889D3DF222', '2025-05-07 13:24:14'),
(2, 24001, 'doc_24001_2.pdf', 'Document_24001_2.pdf', 'cases/documents/21001/doc_24001_2.pdf', 437222, 'application/msword', 'correspondence', 'Auto-generated demo document', 21020, 0, 1, NULL, 0, '05520C241548', '2025-07-10 13:24:14'),
(3, 24001, 'doc_24001_4.pdf', 'Document_24001_4.pdf', 'cases/documents/21001/doc_24001_4.pdf', 724474, 'image/jpeg', 'report', 'Auto-generated demo document', 21019, 1, 1, NULL, 1, '1040445C4101', '2025-02-03 13:24:14'),
(4, 24077, 'doc_24077_1.pdf', 'Document_24077_1.pdf', 'cases/documents/21001/doc_24077_1.pdf', 562831, 'application/pdf', 'report', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, 'D6127BA75849', '2025-04-16 13:24:14'),
(5, 24077, 'doc_24077_2.pdf', 'Document_24077_2.pdf', 'cases/documents/21001/doc_24077_2.pdf', 722274, 'image/jpeg', 'evidence', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, '187E82B061FA', '2025-02-12 13:24:14'),
(6, 24077, 'doc_24077_4.pdf', 'Document_24077_4.pdf', 'cases/documents/21001/doc_24077_4.pdf', 471632, 'image/jpeg', 'evidence', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, '82C3648A0B0D', '2025-04-16 13:24:14'),
(7, 24078, 'doc_24078_1.pdf', 'Document_24078_1.pdf', 'cases/documents/21001/doc_24078_1.pdf', 572303, 'image/png', 'evidence', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, '5179122D45E4', '2025-01-30 13:24:14'),
(8, 24078, 'doc_24078_2.pdf', 'Document_24078_2.pdf', 'cases/documents/21001/doc_24078_2.pdf', 108288, 'image/png', 'medical_record', 'Auto-generated demo document', 20009, 0, 1, NULL, 0, '7D03ED41F40F', '2025-07-27 13:24:14'),
(9, 24078, 'doc_24078_4.pdf', 'Document_24078_4.pdf', 'cases/documents/21001/doc_24078_4.pdf', 492595, 'application/pdf', 'court_filing', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, '877AAFE61DEA', '2025-03-16 13:24:14'),
(10, 24002, 'doc_24002_1.pdf', 'Document_24002_1.pdf', 'cases/documents/21002/doc_24002_1.pdf', 606639, 'image/jpeg', 'correspondence', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, '69357DC9A4D5', '2025-03-21 13:24:14'),
(11, 24002, 'doc_24002_2.pdf', 'Document_24002_2.pdf', 'cases/documents/21002/doc_24002_2.pdf', 81258, 'application/msword', 'evidence', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, '02CC508C0B31', '2025-02-16 13:24:14'),
(12, 24002, 'doc_24002_4.pdf', 'Document_24002_4.pdf', 'cases/documents/21002/doc_24002_4.pdf', 475832, 'image/png', 'medical_record', 'Auto-generated demo document', 21020, 0, 1, NULL, 1, '40282DB900A0', '2025-05-28 13:24:14'),
(13, 24093, 'doc_24093_1.pdf', 'Document_24093_1.pdf', 'cases/documents/21002/doc_24093_1.pdf', 421162, 'image/png', 'medical_record', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, '395AC75CE56B', '2025-09-27 13:24:14'),
(14, 24094, 'doc_24094_2.pdf', 'Document_24094_2.pdf', 'cases/documents/21002/doc_24094_2.pdf', 745802, 'image/jpeg', 'correspondence', 'Auto-generated demo document', 20007, 0, 1, NULL, 1, '791975D1E465', '2025-04-27 13:24:14'),
(15, 24094, 'doc_24094_4.pdf', 'Document_24094_4.pdf', 'cases/documents/21002/doc_24094_4.pdf', 388322, 'application/msword', 'report', 'Auto-generated demo document', 20008, 0, 1, NULL, 1, '5060B4894182', '2025-06-17 13:24:14'),
(16, 24003, 'doc_24003_1.pdf', 'Document_24003_1.pdf', 'cases/documents/21003/doc_24003_1.pdf', 63998, 'image/jpeg', 'court_filing', 'Auto-generated demo document', 21028, 0, 1, NULL, 0, 'FA15CCCBE857', '2025-08-27 13:24:14'),
(17, 24003, 'doc_24003_2.pdf', 'Document_24003_2.pdf', 'cases/documents/21003/doc_24003_2.pdf', 820636, 'application/msword', 'medical_record', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, 'BF72F066FDCB', '2025-07-03 13:24:14'),
(18, 24003, 'doc_24003_4.pdf', 'Document_24003_4.pdf', 'cases/documents/21003/doc_24003_4.pdf', 367643, 'image/png', 'medical_record', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, '36E1B22CDB86', '2025-10-03 13:24:14'),
(19, 24004, 'doc_24004_1.pdf', 'Document_24004_1.pdf', 'cases/documents/21004/doc_24004_1.pdf', 594212, 'image/png', 'report', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, '73D86FF9CF61', '2025-07-09 13:24:14'),
(20, 24004, 'doc_24004_2.pdf', 'Document_24004_2.pdf', 'cases/documents/21004/doc_24004_2.pdf', 370244, 'image/jpeg', 'court_filing', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, '3555A200D556', '2025-05-09 13:24:14'),
(21, 24004, 'doc_24004_4.pdf', 'Document_24004_4.pdf', 'cases/documents/21004/doc_24004_4.pdf', 496996, 'image/png', 'medical_record', 'Auto-generated demo document', 20007, 1, 1, NULL, 1, 'C6C10CA71B04', '2025-04-07 13:24:14'),
(22, 24033, 'doc_24033_1.pdf', 'Document_24033_1.pdf', 'cases/documents/21004/doc_24033_1.pdf', 327307, 'image/jpeg', 'report', 'Auto-generated demo document', 20007, 0, 1, NULL, 1, 'A4E9E26693A7', '2025-06-11 13:24:14'),
(23, 24033, 'doc_24033_2.pdf', 'Document_24033_2.pdf', 'cases/documents/21004/doc_24033_2.pdf', 527629, 'image/jpeg', 'correspondence', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, 'D9ACE71F66B3', '2025-03-13 13:24:14'),
(24, 24033, 'doc_24033_4.pdf', 'Document_24033_4.pdf', 'cases/documents/21004/doc_24033_4.pdf', 950906, 'application/pdf', 'correspondence', 'Auto-generated demo document', 20007, 0, 1, NULL, 0, 'AA1F4382A87D', '2025-08-23 13:24:14'),
(25, 24034, 'doc_24034_1.pdf', 'Document_24034_1.pdf', 'cases/documents/21004/doc_24034_1.pdf', 843237, 'application/msword', 'court_filing', 'Auto-generated demo document', 21019, 0, 1, NULL, 1, 'F0234447C08D', '2025-04-15 13:24:14'),
(26, 24034, 'doc_24034_2.pdf', 'Document_24034_2.pdf', 'cases/documents/21004/doc_24034_2.pdf', 297517, 'application/pdf', 'court_filing', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, 'BE45B3F2F916', '2025-05-01 13:24:14'),
(27, 24034, 'doc_24034_4.pdf', 'Document_24034_4.pdf', 'cases/documents/21004/doc_24034_4.pdf', 60893, 'application/msword', 'evidence', 'Auto-generated demo document', 21020, 0, 1, NULL, 0, 'A28F3AF28A3C', '2025-02-11 13:24:14'),
(28, 24005, 'doc_24005_1.pdf', 'Document_24005_1.pdf', 'cases/documents/21005/doc_24005_1.pdf', 426270, 'application/msword', 'evidence', 'Auto-generated demo document', 21017, 1, 1, NULL, 1, 'DCB17E7772C5', '2025-01-11 13:24:14'),
(29, 24005, 'doc_24005_2.pdf', 'Document_24005_2.pdf', 'cases/documents/21005/doc_24005_2.pdf', 349647, 'application/msword', 'report', 'Auto-generated demo document', 20009, 0, 1, NULL, 0, 'C89C371B2270', '2025-04-19 13:24:14'),
(30, 24053, 'doc_24053_1.pdf', 'Document_24053_1.pdf', 'cases/documents/21005/doc_24053_1.pdf', 116830, 'image/jpeg', 'correspondence', 'Auto-generated demo document', 20007, 0, 1, NULL, 1, 'DC96DBEF725B', '2025-05-23 13:24:14'),
(31, 24053, 'doc_24053_2.pdf', 'Document_24053_2.pdf', 'cases/documents/21005/doc_24053_2.pdf', 594832, 'application/msword', 'report', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, '8B208FF62C82', '2025-04-24 13:24:14'),
(32, 24053, 'doc_24053_4.pdf', 'Document_24053_4.pdf', 'cases/documents/21005/doc_24053_4.pdf', 506038, 'application/pdf', 'correspondence', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, '50B6B75142DA', '2025-07-11 13:24:14'),
(33, 24054, 'doc_24054_2.pdf', 'Document_24054_2.pdf', 'cases/documents/21005/doc_24054_2.pdf', 688355, 'image/png', 'court_filing', 'Auto-generated demo document', 21020, 0, 1, NULL, 0, 'F2773BB7C9DC', '2025-09-18 13:24:14'),
(34, 24006, 'doc_24006_1.pdf', 'Document_24006_1.pdf', 'cases/documents/21006/doc_24006_1.pdf', 968078, 'application/msword', 'report', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, '224C6BEC8931', '2025-01-13 13:24:14'),
(35, 24006, 'doc_24006_2.pdf', 'Document_24006_2.pdf', 'cases/documents/21006/doc_24006_2.pdf', 136370, 'image/jpeg', 'medical_record', 'Auto-generated demo document', 21020, 0, 1, NULL, 1, '77B88E21DEE2', '2025-04-29 13:24:14'),
(36, 24006, 'doc_24006_4.pdf', 'Document_24006_4.pdf', 'cases/documents/21006/doc_24006_4.pdf', 332023, 'image/png', 'court_filing', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, '13F681504FDA', '2025-06-03 13:24:14'),
(37, 24055, 'doc_24055_1.pdf', 'Document_24055_1.pdf', 'cases/documents/21006/doc_24055_1.pdf', 597334, 'image/jpeg', 'evidence', 'Auto-generated demo document', 21028, 1, 1, NULL, 1, '2BA6EE30AE9B', '2025-06-07 13:24:14'),
(38, 24056, 'doc_24056_2.pdf', 'Document_24056_2.pdf', 'cases/documents/21006/doc_24056_2.pdf', 94985, 'image/png', 'report', 'Auto-generated demo document', 20010, 0, 1, NULL, 1, '74D4C5CDD353', '2025-03-28 13:24:14'),
(39, 24056, 'doc_24056_4.pdf', 'Document_24056_4.pdf', 'cases/documents/21006/doc_24056_4.pdf', 829101, 'image/png', 'correspondence', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, '5790CEB95E43', '2025-10-26 13:24:14'),
(40, 24007, 'doc_24007_1.pdf', 'Document_24007_1.pdf', 'cases/documents/21007/doc_24007_1.pdf', 969086, 'application/msword', 'evidence', 'Auto-generated demo document', 21019, 0, 1, NULL, 1, '8FD82B263F60', '2025-01-10 13:24:14'),
(41, 24007, 'doc_24007_2.pdf', 'Document_24007_2.pdf', 'cases/documents/21007/doc_24007_2.pdf', 845845, 'application/msword', 'correspondence', 'Auto-generated demo document', 20008, 0, 1, NULL, 1, '2A10A67CA842', '2025-02-15 13:24:14'),
(42, 24007, 'doc_24007_4.pdf', 'Document_24007_4.pdf', 'cases/documents/21007/doc_24007_4.pdf', 224896, 'image/png', 'evidence', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, '9D593D167564', '2025-06-08 13:24:14'),
(43, 24008, 'doc_24008_1.pdf', 'Document_24008_1.pdf', 'cases/documents/21008/doc_24008_1.pdf', 203455, 'image/jpeg', 'evidence', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, '9A87D40E6A1F', '2025-08-24 13:24:14'),
(44, 24008, 'doc_24008_2.pdf', 'Document_24008_2.pdf', 'cases/documents/21008/doc_24008_2.pdf', 692522, 'image/png', 'court_filing', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, '12791D2449E4', '2025-09-06 13:24:14'),
(45, 24008, 'doc_24008_4.pdf', 'Document_24008_4.pdf', 'cases/documents/21008/doc_24008_4.pdf', 659435, 'image/jpeg', 'medical_record', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, 'C7049F371C12', '2025-03-04 13:24:14'),
(46, 24035, 'doc_24035_1.pdf', 'Document_24035_1.pdf', 'cases/documents/21008/doc_24035_1.pdf', 734802, 'application/msword', 'evidence', 'Auto-generated demo document', 20009, 1, 1, NULL, 1, '8057E21A015F', '2025-08-29 13:24:14'),
(47, 24035, 'doc_24035_2.pdf', 'Document_24035_2.pdf', 'cases/documents/21008/doc_24035_2.pdf', 831563, 'application/msword', 'court_filing', 'Auto-generated demo document', 20008, 0, 1, NULL, 1, 'F89D40FBE275', '2025-10-04 13:24:14'),
(48, 24035, 'doc_24035_4.pdf', 'Document_24035_4.pdf', 'cases/documents/21008/doc_24035_4.pdf', 223143, 'image/jpeg', 'evidence', 'Auto-generated demo document', 20008, 0, 1, NULL, 0, '5AB3CB456ACF', '2025-09-29 13:24:14'),
(49, 24036, 'doc_24036_1.pdf', 'Document_24036_1.pdf', 'cases/documents/21008/doc_24036_1.pdf', 760150, 'image/png', 'correspondence', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, 'A9C93742A724', '2025-04-19 13:24:14'),
(50, 24036, 'doc_24036_2.pdf', 'Document_24036_2.pdf', 'cases/documents/21008/doc_24036_2.pdf', 124116, 'application/msword', 'correspondence', 'Auto-generated demo document', 21020, 0, 1, NULL, 1, 'D820FD636083', '2025-03-09 13:24:14'),
(51, 24036, 'doc_24036_4.pdf', 'Document_24036_4.pdf', 'cases/documents/21008/doc_24036_4.pdf', 374500, 'application/msword', 'correspondence', 'Auto-generated demo document', 21020, 1, 1, NULL, 1, '546916E151A4', '2025-08-06 13:24:14'),
(52, 24009, 'doc_24009_1.pdf', 'Document_24009_1.pdf', 'cases/documents/21009/doc_24009_1.pdf', 154146, 'image/jpeg', 'correspondence', 'Auto-generated demo document', 20007, 0, 1, NULL, 1, '1D5C88587572', '2025-09-09 13:24:14'),
(53, 24009, 'doc_24009_2.pdf', 'Document_24009_2.pdf', 'cases/documents/21009/doc_24009_2.pdf', 837376, 'application/msword', 'correspondence', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, '5EB3AC697ACE', '2025-10-01 13:24:14'),
(54, 24009, 'doc_24009_4.pdf', 'Document_24009_4.pdf', 'cases/documents/21009/doc_24009_4.pdf', 414235, 'application/msword', 'correspondence', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, '4C67E099319F', '2025-07-11 13:24:14'),
(55, 24061, 'doc_24061_2.pdf', 'Document_24061_2.pdf', 'cases/documents/21009/doc_24061_2.pdf', 639247, 'application/msword', 'evidence', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, '30535AACC14D', '2025-05-21 13:24:14'),
(56, 24061, 'doc_24061_4.pdf', 'Document_24061_4.pdf', 'cases/documents/21009/doc_24061_4.pdf', 864962, 'application/pdf', 'evidence', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, '4E84A8F13A12', '2025-03-06 13:24:14'),
(57, 24062, 'doc_24062_1.pdf', 'Document_24062_1.pdf', 'cases/documents/21009/doc_24062_1.pdf', 658010, 'image/jpeg', 'report', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, 'A6EE09969BB8', '2025-03-13 13:24:14'),
(58, 24010, 'doc_24010_1.pdf', 'Document_24010_1.pdf', 'cases/documents/21010/doc_24010_1.pdf', 402255, 'application/pdf', 'correspondence', 'Auto-generated demo document', 20010, 0, 1, NULL, 1, 'F295A913CA56', '2025-03-04 13:24:14'),
(59, 24010, 'doc_24010_2.pdf', 'Document_24010_2.pdf', 'cases/documents/21010/doc_24010_2.pdf', 232330, 'image/png', 'correspondence', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, '59C3C065670F', '2025-01-11 13:24:14'),
(60, 24010, 'doc_24010_4.pdf', 'Document_24010_4.pdf', 'cases/documents/21010/doc_24010_4.pdf', 52727, 'image/png', 'court_filing', 'Auto-generated demo document', 21019, 1, 1, NULL, 1, 'C35B88EB0D6E', '2025-04-26 13:24:14'),
(61, 24089, 'doc_24089_1.pdf', 'Document_24089_1.pdf', 'cases/documents/21010/doc_24089_1.pdf', 107722, 'application/msword', 'correspondence', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, 'CD53A86F354E', '2025-09-26 13:24:14'),
(62, 24089, 'doc_24089_2.pdf', 'Document_24089_2.pdf', 'cases/documents/21010/doc_24089_2.pdf', 283905, 'application/msword', 'evidence', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, '5C1A11857068', '2025-09-16 13:24:14'),
(63, 24089, 'doc_24089_4.pdf', 'Document_24089_4.pdf', 'cases/documents/21010/doc_24089_4.pdf', 677344, 'image/png', 'correspondence', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, 'E0CC83CB8332', '2025-10-04 13:24:14'),
(64, 24090, 'doc_24090_1.pdf', 'Document_24090_1.pdf', 'cases/documents/21010/doc_24090_1.pdf', 553123, 'image/jpeg', 'correspondence', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, '8E7A70EE39E9', '2025-02-15 13:24:14'),
(65, 24090, 'doc_24090_2.pdf', 'Document_24090_2.pdf', 'cases/documents/21010/doc_24090_2.pdf', 334644, 'application/msword', 'court_filing', 'Auto-generated demo document', 21019, 0, 1, NULL, 1, 'A17C3CB685F0', '2025-10-16 13:24:14'),
(66, 24090, 'doc_24090_4.pdf', 'Document_24090_4.pdf', 'cases/documents/21010/doc_24090_4.pdf', 395849, 'application/msword', 'correspondence', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, 'E6CAF5F79B2B', '2025-06-08 13:24:14'),
(67, 24027, 'doc_24027_1.pdf', 'Document_24027_1.pdf', 'cases/documents/21037/doc_24027_1.pdf', 836756, 'image/jpeg', 'evidence', 'Auto-generated demo document', 21020, 0, 1, NULL, 1, 'A384633A8E11', '2025-10-11 13:24:14'),
(68, 24027, 'doc_24027_2.pdf', 'Document_24027_2.pdf', 'cases/documents/21037/doc_24027_2.pdf', 645435, 'application/pdf', 'correspondence', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, '105DA5144176', '2025-04-09 13:24:14'),
(69, 24027, 'doc_24027_4.pdf', 'Document_24027_4.pdf', 'cases/documents/21037/doc_24027_4.pdf', 816655, 'image/png', 'correspondence', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, '2539447894E5', '2025-01-30 13:24:14'),
(70, 24028, 'doc_24028_1.pdf', 'Document_24028_1.pdf', 'cases/documents/21037/doc_24028_1.pdf', 668434, 'application/pdf', 'correspondence', 'Auto-generated demo document', 21018, 1, 1, NULL, 1, '3A8D5CA8EA35', '2025-03-21 13:24:14'),
(71, 24028, 'doc_24028_4.pdf', 'Document_24028_4.pdf', 'cases/documents/21037/doc_24028_4.pdf', 598041, 'application/msword', 'report', 'Auto-generated demo document', 21019, 0, 1, NULL, 1, '5388D20D4E23', '2025-03-30 13:24:14'),
(72, 24073, 'doc_24073_1.pdf', 'Document_24073_1.pdf', 'cases/documents/21038/doc_24073_1.pdf', 603944, 'image/jpeg', 'medical_record', 'Auto-generated demo document', 20010, 0, 1, NULL, 1, 'BB040FDEEC10', '2025-07-18 13:24:14'),
(73, 24073, 'doc_24073_2.pdf', 'Document_24073_2.pdf', 'cases/documents/21038/doc_24073_2.pdf', 481684, 'application/msword', 'evidence', 'Auto-generated demo document', 20010, 0, 1, NULL, 1, '93ECA0AA4FB2', '2025-01-26 13:24:14'),
(74, 24074, 'doc_24074_1.pdf', 'Document_24074_1.pdf', 'cases/documents/21038/doc_24074_1.pdf', 677889, 'application/pdf', 'court_filing', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, '05AA5AE016A9', '2025-07-02 13:24:14'),
(75, 24074, 'doc_24074_4.pdf', 'Document_24074_4.pdf', 'cases/documents/21038/doc_24074_4.pdf', 649942, 'image/jpeg', 'court_filing', 'Auto-generated demo document', 20008, 0, 1, NULL, 1, '9D2B385274AC', '2025-02-06 13:24:14'),
(76, 24067, 'doc_24067_1.pdf', 'Document_24067_1.pdf', 'cases/documents/21039/doc_24067_1.pdf', 143420, 'application/msword', 'medical_record', 'Auto-generated demo document', 21020, 0, 1, NULL, 0, 'F5CBE1BFD72F', '2025-08-27 13:24:14'),
(77, 24067, 'doc_24067_2.pdf', 'Document_24067_2.pdf', 'cases/documents/21039/doc_24067_2.pdf', 176871, 'application/pdf', 'correspondence', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, '56D8667D5B61', '2025-02-27 13:24:14'),
(78, 24067, 'doc_24067_4.pdf', 'Document_24067_4.pdf', 'cases/documents/21039/doc_24067_4.pdf', 659821, 'image/jpeg', 'court_filing', 'Auto-generated demo document', 21019, 1, 1, NULL, 1, 'D3E2034F4F88', '2025-01-26 13:24:14'),
(79, 24068, 'doc_24068_1.pdf', 'Document_24068_1.pdf', 'cases/documents/21039/doc_24068_1.pdf', 736606, 'image/jpeg', 'evidence', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, 'A3ECF7A68FB3', '2025-06-17 13:24:14'),
(80, 24068, 'doc_24068_2.pdf', 'Document_24068_2.pdf', 'cases/documents/21039/doc_24068_2.pdf', 173155, 'application/msword', 'report', 'Auto-generated demo document', 20010, 0, 1, NULL, 1, 'DAE7F4F36B9F', '2025-01-02 13:24:14'),
(81, 24068, 'doc_24068_4.pdf', 'Document_24068_4.pdf', 'cases/documents/21039/doc_24068_4.pdf', 128660, 'application/pdf', 'correspondence', 'Auto-generated demo document', 20008, 0, 1, NULL, 1, 'B6A7474ADA9D', '2025-02-09 13:24:14'),
(82, 24023, 'doc_24023_1.pdf', 'Document_24023_1.pdf', 'cases/documents/21042/doc_24023_1.pdf', 452387, 'image/png', 'report', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, 'EDF48903B7D2', '2025-02-04 13:24:14'),
(83, 24023, 'doc_24023_2.pdf', 'Document_24023_2.pdf', 'cases/documents/21042/doc_24023_2.pdf', 555631, 'application/msword', 'report', 'Auto-generated demo document', 20008, 0, 1, NULL, 1, '40123C750048', '2025-01-23 13:24:14'),
(84, 24024, 'doc_24024_1.pdf', 'Document_24024_1.pdf', 'cases/documents/21042/doc_24024_1.pdf', 421838, 'application/pdf', 'correspondence', 'Auto-generated demo document', 20007, 0, 1, NULL, 1, '81D5A60A0756', '2025-08-26 13:24:14'),
(85, 24024, 'doc_24024_2.pdf', 'Document_24024_2.pdf', 'cases/documents/21042/doc_24024_2.pdf', 938397, 'image/jpeg', 'report', 'Auto-generated demo document', 21019, 0, 1, NULL, 1, 'DDB98EDF76E6', '2025-08-16 13:24:14'),
(86, 24024, 'doc_24024_4.pdf', 'Document_24024_4.pdf', 'cases/documents/21042/doc_24024_4.pdf', 358077, 'application/msword', 'medical_record', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, '9DE666927799', '2025-08-26 13:24:14'),
(87, 24063, 'doc_24063_1.pdf', 'Document_24063_1.pdf', 'cases/documents/21043/doc_24063_1.pdf', 390170, 'application/pdf', 'medical_record', 'Auto-generated demo document', 20010, 0, 1, NULL, 1, '4FECB0953FB2', '2025-07-16 13:24:14'),
(88, 24063, 'doc_24063_2.pdf', 'Document_24063_2.pdf', 'cases/documents/21043/doc_24063_2.pdf', 894575, 'application/pdf', 'court_filing', 'Auto-generated demo document', 20008, 0, 1, NULL, 1, '932AEAAA4CAB', '2025-08-09 13:24:14'),
(89, 24063, 'doc_24063_4.pdf', 'Document_24063_4.pdf', 'cases/documents/21043/doc_24063_4.pdf', 227636, 'application/pdf', 'court_filing', 'Auto-generated demo document', 21018, 1, 1, NULL, 1, '7217FC05C85F', '2025-08-30 13:24:14'),
(90, 24064, 'doc_24064_1.pdf', 'Document_24064_1.pdf', 'cases/documents/21043/doc_24064_1.pdf', 589386, 'application/pdf', 'court_filing', 'Auto-generated demo document', 20010, 1, 1, NULL, 1, '6E4C1B9DB930', '2025-10-06 13:24:14'),
(91, 24064, 'doc_24064_2.pdf', 'Document_24064_2.pdf', 'cases/documents/21043/doc_24064_2.pdf', 158254, 'image/jpeg', 'report', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, 'E03085AB80C2', '2025-06-01 13:24:14'),
(92, 24045, 'doc_24045_1.pdf', 'Document_24045_1.pdf', 'cases/documents/21044/doc_24045_1.pdf', 139245, 'image/jpeg', 'court_filing', 'Auto-generated demo document', 21019, 0, 1, NULL, 0, 'FBFDDDF7EFF7', '2025-01-16 13:24:14'),
(93, 24045, 'doc_24045_2.pdf', 'Document_24045_2.pdf', 'cases/documents/21044/doc_24045_2.pdf', 167320, 'application/pdf', 'evidence', 'Auto-generated demo document', 21018, 0, 1, NULL, 0, '1B0678CC6C19', '2025-04-21 13:24:14'),
(94, 24046, 'doc_24046_1.pdf', 'Document_24046_1.pdf', 'cases/documents/21044/doc_24046_1.pdf', 132018, 'image/jpeg', 'medical_record', 'Auto-generated demo document', 20010, 0, 1, NULL, 1, '24BBBA8C92EE', '2025-03-15 13:24:14'),
(95, 24046, 'doc_24046_2.pdf', 'Document_24046_2.pdf', 'cases/documents/21044/doc_24046_2.pdf', 463792, 'application/pdf', 'correspondence', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, '07BFE1841EFF', '2025-08-15 13:24:14'),
(96, 24046, 'doc_24046_4.pdf', 'Document_24046_4.pdf', 'cases/documents/21044/doc_24046_4.pdf', 943030, 'image/jpeg', 'medical_record', 'Auto-generated demo document', 21019, 0, 1, NULL, 0, 'E793547B9E4D', '2025-08-04 13:24:14'),
(97, 24017, 'doc_24017_1.pdf', 'Document_24017_1.pdf', 'cases/documents/21045/doc_24017_1.pdf', 607555, 'application/msword', 'evidence', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, '9AB0DB1A6AC3', '2025-05-28 13:24:14'),
(98, 24017, 'doc_24017_2.pdf', 'Document_24017_2.pdf', 'cases/documents/21045/doc_24017_2.pdf', 162368, 'image/jpeg', 'correspondence', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, 'B557A3DED55E', '2025-05-23 13:24:14'),
(99, 24017, 'doc_24017_4.pdf', 'Document_24017_4.pdf', 'cases/documents/21045/doc_24017_4.pdf', 872124, 'application/msword', 'court_filing', 'Auto-generated demo document', 20008, 1, 1, NULL, 1, 'A6A388569A8E', '2025-01-04 13:24:14'),
(100, 24049, 'doc_24049_1.pdf', 'Document_24049_1.pdf', 'cases/documents/21046/doc_24049_1.pdf', 938659, 'application/msword', 'evidence', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, '12C543484B15', '2025-09-08 13:24:14'),
(101, 24049, 'doc_24049_2.pdf', 'Document_24049_2.pdf', 'cases/documents/21046/doc_24049_2.pdf', 519024, 'image/png', 'medical_record', 'Auto-generated demo document', 21019, 0, 1, NULL, 1, '12F15E704BC5', '2025-05-05 13:24:14'),
(102, 24049, 'doc_24049_4.pdf', 'Document_24049_4.pdf', 'cases/documents/21046/doc_24049_4.pdf', 751216, 'image/png', 'medical_record', 'Auto-generated demo document', 21019, 0, 1, NULL, 1, '9099FF624268', '2025-05-27 13:24:14'),
(103, 24050, 'doc_24050_2.pdf', 'Document_24050_2.pdf', 'cases/documents/21046/doc_24050_2.pdf', 374881, 'image/jpeg', 'evidence', 'Auto-generated demo document', 20007, 0, 1, NULL, 1, 'E6C0B4D39B02', '2025-05-11 13:24:14'),
(104, 24050, 'doc_24050_4.pdf', 'Document_24050_4.pdf', 'cases/documents/21046/doc_24050_4.pdf', 894259, 'application/pdf', 'court_filing', 'Auto-generated demo document', 20010, 1, 1, NULL, 1, '2EEAD310BBAB', '2025-05-04 13:24:14'),
(105, 24085, 'doc_24085_1.pdf', 'Document_24085_1.pdf', 'cases/documents/21047/doc_24085_1.pdf', 227878, 'application/msword', 'court_filing', 'Auto-generated demo document', 21028, 1, 1, NULL, 1, 'F655BEF7D956', '2025-04-17 13:24:14'),
(106, 24085, 'doc_24085_2.pdf', 'Document_24085_2.pdf', 'cases/documents/21047/doc_24085_2.pdf', 752803, 'application/msword', 'correspondence', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, '81D644CE0759', '2025-10-17 13:24:14'),
(107, 24085, 'doc_24085_4.pdf', 'Document_24085_4.pdf', 'cases/documents/21047/doc_24085_4.pdf', 187607, 'application/msword', 'report', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, '0A1350D4284D', '2025-07-17 13:24:14'),
(108, 24086, 'doc_24086_1.pdf', 'Document_24086_1.pdf', 'cases/documents/21047/doc_24086_1.pdf', 922149, 'application/msword', 'evidence', 'Auto-generated demo document', 20008, 0, 1, NULL, 0, 'E6CD96039B36', '2025-07-12 13:24:14'),
(109, 24086, 'doc_24086_2.pdf', 'Document_24086_2.pdf', 'cases/documents/21047/doc_24086_2.pdf', 371478, 'image/jpeg', 'court_filing', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, 'B12A7FEEC4AA', '2025-08-02 13:24:14'),
(110, 24086, 'doc_24086_4.pdf', 'Document_24086_4.pdf', 'cases/documents/21047/doc_24086_4.pdf', 912280, 'image/png', 'medical_record', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, 'D3BF8B2F4EFE', '2025-03-02 13:24:14'),
(111, 24037, 'doc_24037_1.pdf', 'Document_24037_1.pdf', 'cases/documents/21048/doc_24037_1.pdf', 186552, 'application/pdf', 'correspondence', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, '9364F44E4D93', '2025-02-18 13:24:14'),
(112, 24037, 'doc_24037_2.pdf', 'Document_24037_2.pdf', 'cases/documents/21048/doc_24037_2.pdf', 772688, 'image/png', 'court_filing', 'Auto-generated demo document', 20010, 1, 1, NULL, 1, '7964112DE590', '2025-07-31 13:24:14'),
(113, 24037, 'doc_24037_4.pdf', 'Document_24037_4.pdf', 'cases/documents/21048/doc_24037_4.pdf', 388777, 'image/png', 'court_filing', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, '5281C68D4A07', '2025-02-15 13:24:14'),
(114, 24038, 'doc_24038_1.pdf', 'Document_24038_1.pdf', 'cases/documents/21048/doc_24038_1.pdf', 833080, 'image/jpeg', 'report', 'Auto-generated demo document', 20007, 1, 1, NULL, 1, 'F6C0F85BDB03', '2025-04-22 13:24:14'),
(115, 24038, 'doc_24038_2.pdf', 'Document_24038_2.pdf', 'cases/documents/21048/doc_24038_2.pdf', 387877, 'application/pdf', 'court_filing', 'Auto-generated demo document', 20009, 0, 1, NULL, 0, 'F1E81B5BC7A0', '2025-01-19 13:24:14'),
(116, 24039, 'doc_24039_1.pdf', 'Document_24039_1.pdf', 'cases/documents/21049/doc_24039_1.pdf', 823368, 'image/png', 'medical_record', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, '0EA6924C3A9A', '2025-02-14 13:24:14'),
(117, 24039, 'doc_24039_2.pdf', 'Document_24039_2.pdf', 'cases/documents/21049/doc_24039_2.pdf', 885332, 'application/pdf', 'report', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, 'FE078F4FF81E', '2025-01-29 13:24:14'),
(118, 24039, 'doc_24039_4.pdf', 'Document_24039_4.pdf', 'cases/documents/21049/doc_24039_4.pdf', 79110, 'image/png', 'report', 'Auto-generated demo document', 20010, 0, 1, NULL, 1, '6AE73AE9AB9C', '2025-05-20 13:24:14'),
(119, 24040, 'doc_24040_1.pdf', 'Document_24040_1.pdf', 'cases/documents/21049/doc_24040_1.pdf', 499227, 'application/pdf', 'report', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, '5360F8F94D83', '2025-09-25 13:24:14'),
(120, 24040, 'doc_24040_2.pdf', 'Document_24040_2.pdf', 'cases/documents/21049/doc_24040_2.pdf', 511130, 'application/msword', 'report', 'Auto-generated demo document', 21018, 1, 1, NULL, 1, 'C0932ACF024C', '2025-06-15 13:24:14'),
(121, 24071, 'doc_24071_1.pdf', 'Document_24071_1.pdf', 'cases/documents/21050/doc_24071_1.pdf', 885716, 'application/msword', 'court_filing', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, 'BD5337B6F54C', '2025-06-22 13:24:14'),
(122, 24071, 'doc_24071_4.pdf', 'Document_24071_4.pdf', 'cases/documents/21050/doc_24071_4.pdf', 527131, 'application/msword', 'correspondence', 'Auto-generated demo document', 20008, 1, 1, NULL, 1, 'EC66C553B19B', '2025-07-19 13:24:14'),
(123, 24072, 'doc_24072_2.pdf', 'Document_24072_2.pdf', 'cases/documents/21050/doc_24072_2.pdf', 81045, 'image/jpeg', 'report', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, 'C4301C4F10C0', '2025-06-10 13:24:14'),
(124, 24072, 'doc_24072_4.pdf', 'Document_24072_4.pdf', 'cases/documents/21050/doc_24072_4.pdf', 718334, 'image/png', 'report', 'Auto-generated demo document', 20007, 0, 1, NULL, 1, '8252685E0949', '2025-04-10 13:24:14'),
(125, 24029, 'doc_24029_2.pdf', 'Document_24029_2.pdf', 'cases/documents/21051/doc_24029_2.pdf', 763865, 'image/png', 'report', 'Auto-generated demo document', 20008, 0, 1, NULL, 1, 'C49BED93126F', '2025-10-17 13:24:14'),
(126, 24030, 'doc_24030_1.pdf', 'Document_24030_1.pdf', 'cases/documents/21051/doc_24030_1.pdf', 780053, 'application/pdf', 'medical_record', 'Auto-generated demo document', 21017, 1, 1, NULL, 1, 'C7E58D7F1F96', '2025-06-02 13:24:14'),
(127, 24030, 'doc_24030_2.pdf', 'Document_24030_2.pdf', 'cases/documents/21051/doc_24030_2.pdf', 128554, 'application/pdf', 'medical_record', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, 'F93716A3E4DC', '2025-01-09 13:24:14'),
(128, 24019, 'doc_24019_1.pdf', 'Document_24019_1.pdf', 'cases/documents/21052/doc_24019_1.pdf', 957070, 'image/png', 'court_filing', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, 'E49575139255', '2025-02-07 13:24:14'),
(129, 24019, 'doc_24019_2.pdf', 'Document_24019_2.pdf', 'cases/documents/21052/doc_24019_2.pdf', 859113, 'image/jpeg', 'correspondence', 'Auto-generated demo document', 21020, 1, 1, NULL, 1, '538ED3814E3B', '2025-05-17 13:24:14'),
(130, 24019, 'doc_24019_4.pdf', 'Document_24019_4.pdf', 'cases/documents/21052/doc_24019_4.pdf', 167710, 'image/jpeg', 'report', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, 'F7C2108FDF08', '2025-10-15 13:24:14'),
(131, 24020, 'doc_24020_1.pdf', 'Document_24020_1.pdf', 'cases/documents/21052/doc_24020_1.pdf', 423284, 'application/pdf', 'court_filing', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, '008438F40210', '2025-02-13 13:24:14'),
(132, 24020, 'doc_24020_2.pdf', 'Document_24020_2.pdf', 'cases/documents/21052/doc_24020_2.pdf', 773655, 'application/pdf', 'medical_record', 'Auto-generated demo document', 21018, 0, 1, NULL, 0, '8ED053463B41', '2025-10-12 13:24:14'),
(133, 24020, 'doc_24020_4.pdf', 'Document_24020_4.pdf', 'cases/documents/21052/doc_24020_4.pdf', 736628, 'application/msword', 'correspondence', 'Auto-generated demo document', 20008, 0, 1, NULL, 1, 'C4503FF31141', '2025-01-12 13:24:14'),
(134, 24107, 'doc_24107_1.pdf', 'Document_24107_1.pdf', 'cases/documents/21053/doc_24107_1.pdf', 640019, 'image/png', 'report', 'Auto-generated demo document', 21018, 1, 1, NULL, 1, '85BC2E2216F0', '2025-05-10 13:24:14'),
(135, 24107, 'doc_24107_2.pdf', 'Document_24107_2.pdf', 'cases/documents/21053/doc_24107_2.pdf', 665887, 'image/png', 'report', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, '74282485D0A0', '2025-08-21 13:24:14'),
(136, 24107, 'doc_24107_4.pdf', 'Document_24107_4.pdf', 'cases/documents/21053/doc_24107_4.pdf', 188165, 'image/jpeg', 'correspondence', 'Auto-generated demo document', 20010, 0, 1, NULL, 1, '3EC1D380FB07', '2025-09-12 13:24:14'),
(137, 24108, 'doc_24108_1.pdf', 'Document_24108_1.pdf', 'cases/documents/21053/doc_24108_1.pdf', 677663, 'image/jpeg', 'report', 'Auto-generated demo document', 21027, 0, 1, NULL, 0, 'B5BF6CB6D6FD', '2025-10-17 13:24:14'),
(138, 24108, 'doc_24108_2.pdf', 'Document_24108_2.pdf', 'cases/documents/21053/doc_24108_2.pdf', 134481, 'image/jpeg', 'report', 'Auto-generated demo document', 20008, 0, 1, NULL, 1, 'EB931073AE4C', '2025-03-22 13:24:14'),
(139, 24043, 'doc_24043_1.pdf', 'Document_24043_1.pdf', 'cases/documents/21055/doc_24043_1.pdf', 685503, 'image/png', 'medical_record', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, '6E6E2CCDB9B8', '2025-01-16 13:24:14'),
(140, 24043, 'doc_24043_2.pdf', 'Document_24043_2.pdf', 'cases/documents/21055/doc_24043_2.pdf', 432374, 'image/png', 'court_filing', 'Auto-generated demo document', 20010, 0, 1, NULL, 1, '6CAAFA9DB2AB', '2025-06-23 13:24:14'),
(141, 24044, 'doc_24044_4.pdf', 'Document_24044_4.pdf', 'cases/documents/21055/doc_24044_4.pdf', 198523, 'application/pdf', 'court_filing', 'Auto-generated demo document', 21017, 1, 1, NULL, 1, 'C63D4FAF18F5', '2025-06-16 13:24:14'),
(142, 24075, 'doc_24075_2.pdf', 'Document_24075_2.pdf', 'cases/documents/21056/doc_24075_2.pdf', 292095, 'image/png', 'evidence', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, 'FBD44ADBEF51', '2025-05-07 13:24:14'),
(143, 24076, 'doc_24076_2.pdf', 'Document_24076_2.pdf', 'cases/documents/21056/doc_24076_2.pdf', 326732, 'image/jpeg', 'evidence', 'Auto-generated demo document', 20007, 0, 1, NULL, 1, '5CBCDC3572F3', '2025-10-02 13:24:14'),
(144, 24076, 'doc_24076_4.pdf', 'Document_24076_4.pdf', 'cases/documents/21056/doc_24076_4.pdf', 423551, 'application/pdf', 'evidence', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, 'DC3B81B770EE', '2025-10-21 13:24:14'),
(145, 24025, 'doc_24025_1.pdf', 'Document_24025_1.pdf', 'cases/documents/21057/doc_24025_1.pdf', 597112, 'image/jpeg', 'medical_record', 'Auto-generated demo document', 21027, 1, 1, NULL, 0, '8D259EE63496', '2025-05-05 13:24:14'),
(146, 24025, 'doc_24025_2.pdf', 'Document_24025_2.pdf', 'cases/documents/21057/doc_24025_2.pdf', 585606, 'application/pdf', 'correspondence', 'Auto-generated demo document', 21019, 0, 1, NULL, 1, '100B4170402D', '2025-05-12 13:24:14'),
(147, 24025, 'doc_24025_4.pdf', 'Document_24025_4.pdf', 'cases/documents/21057/doc_24025_4.pdf', 432096, 'application/pdf', 'correspondence', 'Auto-generated demo document', 21028, 1, 1, NULL, 0, '3376CF8CCDDB', '2025-04-05 13:24:14'),
(148, 24026, 'doc_24026_2.pdf', 'Document_24026_2.pdf', 'cases/documents/21057/doc_24026_2.pdf', 730298, 'image/png', 'correspondence', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, '2D41EA70B507', '2025-07-17 13:24:14'),
(149, 24026, 'doc_24026_4.pdf', 'Document_24026_4.pdf', 'cases/documents/21057/doc_24026_4.pdf', 873676, 'application/msword', 'evidence', 'Auto-generated demo document', 21019, 0, 1, NULL, 1, '780C2CDDE030', '2025-07-29 13:24:14'),
(150, 24109, 'doc_24109_1.pdf', 'Document_24109_1.pdf', 'cases/documents/21058/doc_24109_1.pdf', 590439, 'image/png', 'report', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, 'BD6EDDCAF5BB', '2025-08-31 13:24:14'),
(151, 24109, 'doc_24109_2.pdf', 'Document_24109_2.pdf', 'cases/documents/21058/doc_24109_2.pdf', 172718, 'image/jpeg', 'correspondence', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, '930E295E4C38', '2025-09-07 13:24:14'),
(152, 24109, 'doc_24109_4.pdf', 'Document_24109_4.pdf', 'cases/documents/21058/doc_24109_4.pdf', 130365, 'application/pdf', 'medical_record', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, 'FCB42D93F2D0', '2025-04-25 13:24:14'),
(153, 24110, 'doc_24110_1.pdf', 'Document_24110_1.pdf', 'cases/documents/21058/doc_24110_1.pdf', 788542, 'image/png', 'correspondence', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, '0EA8BB303AA2', '2025-07-02 13:24:14'),
(154, 24110, 'doc_24110_2.pdf', 'Document_24110_2.pdf', 'cases/documents/21058/doc_24110_2.pdf', 743636, 'image/jpeg', 'correspondence', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, '5B881BD16E20', '2025-02-28 13:24:14'),
(155, 24103, 'doc_24103_1.pdf', 'Document_24103_1.pdf', 'cases/documents/21059/doc_24103_1.pdf', 810599, 'application/pdf', 'medical_record', 'Auto-generated demo document', 20007, 0, 1, NULL, 1, '7D6C3425F5B0', '2025-05-28 13:24:14'),
(156, 24103, 'doc_24103_2.pdf', 'Document_24103_2.pdf', 'cases/documents/21059/doc_24103_2.pdf', 797359, 'application/msword', 'evidence', 'Auto-generated demo document', 20010, 0, 1, NULL, 1, 'E56D536B95B5', '2025-07-31 13:24:14'),
(157, 24103, 'doc_24103_4.pdf', 'Document_24103_4.pdf', 'cases/documents/21059/doc_24103_4.pdf', 111277, 'application/msword', 'report', 'Auto-generated demo document', 20007, 0, 1, NULL, 1, '1E54372C7950', '2025-08-29 13:24:14'),
(158, 24104, 'doc_24104_1.pdf', 'Document_24104_1.pdf', 'cases/documents/21059/doc_24104_1.pdf', 577742, 'application/msword', 'medical_record', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, '2E21818CB886', '2025-08-22 13:24:14'),
(159, 24104, 'doc_24104_2.pdf', 'Document_24104_2.pdf', 'cases/documents/21059/doc_24104_2.pdf', 226258, 'application/pdf', 'correspondence', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, '49A5A0052696', '2025-04-12 13:24:14'),
(160, 24104, 'doc_24104_4.pdf', 'Document_24104_4.pdf', 'cases/documents/21059/doc_24104_4.pdf', 245581, 'image/png', 'medical_record', 'Auto-generated demo document', 20007, 0, 1, NULL, 1, '17B8594C5EE1', '2025-08-22 13:24:14'),
(161, 24059, 'doc_24059_2.pdf', 'Document_24059_2.pdf', 'cases/documents/21060/doc_24059_2.pdf', 837249, 'application/msword', 'correspondence', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, 'E8180083A060', '2025-07-07 13:24:14'),
(162, 24059, 'doc_24059_4.pdf', 'Document_24059_4.pdf', 'cases/documents/21060/doc_24059_4.pdf', 650110, 'application/msword', 'evidence', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, '4D5EDFA5357B', '2025-08-03 13:24:14'),
(163, 24060, 'doc_24060_1.pdf', 'Document_24060_1.pdf', 'cases/documents/21060/doc_24060_1.pdf', 743845, 'application/pdf', 'correspondence', 'Auto-generated demo document', 20010, 0, 1, NULL, 0, 'BCD6C45EF35B', '2025-04-01 13:24:14'),
(164, 24060, 'doc_24060_2.pdf', 'Document_24060_2.pdf', 'cases/documents/21060/doc_24060_2.pdf', 300743, 'image/png', 'evidence', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, '65684FC595A1', '2025-06-06 13:24:14'),
(165, 24060, 'doc_24060_4.pdf', 'Document_24060_4.pdf', 'cases/documents/21060/doc_24060_4.pdf', 569012, 'application/pdf', 'court_filing', 'Auto-generated demo document', 20010, 1, 1, NULL, 1, 'D8836C5B620D', '2025-02-24 13:24:14'),
(166, 24011, 'doc_24011_1.pdf', 'Document_24011_1.pdf', 'cases/documents/21062/doc_24011_1.pdf', 322370, 'application/msword', 'court_filing', 'Auto-generated demo document', 21019, 0, 1, NULL, 1, '129E08F84A78', '2025-02-04 13:24:14'),
(167, 24011, 'doc_24011_2.pdf', 'Document_24011_2.pdf', 'cases/documents/21062/doc_24011_2.pdf', 429538, 'image/jpeg', 'evidence', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, '15D4A5F45752', '2025-03-26 13:24:14'),
(168, 24011, 'doc_24011_4.pdf', 'Document_24011_4.pdf', 'cases/documents/21062/doc_24011_4.pdf', 542067, 'image/png', 'correspondence', 'Auto-generated demo document', 20010, 0, 1, NULL, 1, '077543B81DD5', '2025-04-25 13:24:14'),
(169, 24012, 'doc_24012_1.pdf', 'Document_24012_1.pdf', 'cases/documents/21062/doc_24012_1.pdf', 216864, 'application/msword', 'evidence', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, 'D33E675F4CF9', '2025-08-27 13:24:14'),
(170, 24012, 'doc_24012_2.pdf', 'Document_24012_2.pdf', 'cases/documents/21062/doc_24012_2.pdf', 206179, 'application/pdf', 'court_filing', 'Auto-generated demo document', 20007, 0, 1, NULL, 1, '9A168466685A', '2025-08-27 13:24:14'),
(171, 24012, 'doc_24012_4.pdf', 'Document_24012_4.pdf', 'cases/documents/21062/doc_24012_4.pdf', 516601, 'application/msword', 'correspondence', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, '20ECF0E083B3', '2025-04-21 13:24:14'),
(172, 24069, 'doc_24069_1.pdf', 'Document_24069_1.pdf', 'cases/documents/21063/doc_24069_1.pdf', 930570, 'image/jpeg', 'report', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, '53E67B394F99', '2025-04-08 13:24:14'),
(173, 24069, 'doc_24069_2.pdf', 'Document_24069_2.pdf', 'cases/documents/21063/doc_24069_2.pdf', 896770, 'image/jpeg', 'court_filing', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, 'C3C29BA70F0A', '2025-01-06 13:24:14'),
(174, 24069, 'doc_24069_4.pdf', 'Document_24069_4.pdf', 'cases/documents/21063/doc_24069_4.pdf', 145507, 'image/png', 'medical_record', 'Auto-generated demo document', 20010, 0, 1, NULL, 1, 'DAB7FDFF6ADF', '2025-10-21 13:24:14'),
(175, 24070, 'doc_24070_1.pdf', 'Document_24070_1.pdf', 'cases/documents/21063/doc_24070_1.pdf', 656739, 'image/png', 'court_filing', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, '044CCA9C1133', '2025-10-12 13:24:14'),
(176, 24070, 'doc_24070_2.pdf', 'Document_24070_2.pdf', 'cases/documents/21063/doc_24070_2.pdf', 873172, 'application/msword', 'report', 'Auto-generated demo document', 21019, 0, 1, NULL, 0, 'EF0897D3BC22', '2025-09-15 13:24:14'),
(177, 24105, 'doc_24105_1.pdf', 'Document_24105_1.pdf', 'cases/documents/21064/doc_24105_1.pdf', 821940, 'application/msword', 'correspondence', 'Auto-generated demo document', 20007, 0, 1, NULL, 0, 'A0E5677A8395', '2025-02-06 13:24:14'),
(178, 24105, 'doc_24105_2.pdf', 'Document_24105_2.pdf', 'cases/documents/21064/doc_24105_2.pdf', 868209, 'application/msword', 'evidence', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, '1DEC70AC77B1', '2025-06-28 13:24:14'),
(179, 24105, 'doc_24105_4.pdf', 'Document_24105_4.pdf', 'cases/documents/21064/doc_24105_4.pdf', 167994, 'image/png', 'correspondence', 'Auto-generated demo document', 21017, 1, 1, NULL, 1, '0776754C1DD9', '2025-03-01 13:24:14'),
(180, 24106, 'doc_24106_2.pdf', 'Document_24106_2.pdf', 'cases/documents/21064/doc_24106_2.pdf', 152948, 'application/pdf', 'evidence', 'Auto-generated demo document', 20010, 0, 1, NULL, 1, 'BF6A9B06FDAA', '2025-05-15 13:24:14'),
(181, 24106, 'doc_24106_4.pdf', 'Document_24106_4.pdf', 'cases/documents/21064/doc_24106_4.pdf', 893525, 'application/pdf', 'report', 'Auto-generated demo document', 21019, 0, 1, NULL, 1, '016C6A9405B1', '2025-05-27 13:24:14'),
(182, 24057, 'doc_24057_1.pdf', 'Document_24057_1.pdf', 'cases/documents/21065/doc_24057_1.pdf', 221820, 'image/jpeg', 'evidence', 'Auto-generated demo document', 20010, 0, 1, NULL, 1, 'D6C3AE835B0E', '2025-03-23 13:24:14'),
(183, 24057, 'doc_24057_2.pdf', 'Document_24057_2.pdf', 'cases/documents/21065/doc_24057_2.pdf', 430486, 'image/png', 'court_filing', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, '55EC51A957B1', '2025-07-06 13:24:14'),
(184, 24058, 'doc_24058_1.pdf', 'Document_24058_1.pdf', 'cases/documents/21065/doc_24058_1.pdf', 831609, 'image/jpeg', 'medical_record', 'Auto-generated demo document', 21019, 0, 1, NULL, 0, '8C6FF74A31BF', '2025-09-21 13:24:14'),
(185, 24058, 'doc_24058_4.pdf', 'Document_24058_4.pdf', 'cases/documents/21065/doc_24058_4.pdf', 337013, 'application/pdf', 'court_filing', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, '30F92E5CC3E4', '2025-09-12 13:24:14'),
(186, 24091, 'doc_24091_1.pdf', 'Document_24091_1.pdf', 'cases/documents/21066/doc_24091_1.pdf', 499373, 'application/msword', 'evidence', 'Auto-generated demo document', 20008, 0, 1, NULL, 1, '4002BA85000A', '2025-09-04 13:24:14'),
(187, 24091, 'doc_24091_2.pdf', 'Document_24091_2.pdf', 'cases/documents/21066/doc_24091_2.pdf', 218558, 'image/jpeg', 'evidence', 'Auto-generated demo document', 21027, 1, 1, NULL, 1, 'D172E0A745CB', '2025-03-10 13:24:14'),
(188, 24091, 'doc_24091_4.pdf', 'Document_24091_4.pdf', 'cases/documents/21066/doc_24091_4.pdf', 679683, 'application/pdf', 'medical_record', 'Auto-generated demo document', 21020, 1, 1, NULL, 0, 'CEC74E873B1D', '2025-10-17 13:24:14'),
(189, 24092, 'doc_24092_1.pdf', 'Document_24092_1.pdf', 'cases/documents/21066/doc_24092_1.pdf', 620756, 'application/msword', 'report', 'Auto-generated demo document', 21019, 0, 1, NULL, 1, '234FF81C8D3F', '2025-04-03 13:24:14'),
(190, 24092, 'doc_24092_2.pdf', 'Document_24092_2.pdf', 'cases/documents/21066/doc_24092_2.pdf', 157703, 'image/jpeg', 'evidence', 'Auto-generated demo document', 21019, 0, 1, NULL, 1, 'D42172F75085', '2025-03-04 13:24:14'),
(191, 24092, 'doc_24092_4.pdf', 'Document_24092_4.pdf', 'cases/documents/21066/doc_24092_4.pdf', 62580, 'image/png', 'medical_record', 'Auto-generated demo document', 21019, 0, 1, NULL, 1, '4A36610D28D9', '2025-01-23 13:24:14'),
(192, 24051, 'doc_24051_1.pdf', 'Document_24051_1.pdf', 'cases/documents/21067/doc_24051_1.pdf', 59150, 'application/msword', 'evidence', 'Auto-generated demo document', 20010, 0, 1, NULL, 1, '1B660DBC6D98', '2025-10-10 13:24:14'),
(193, 24051, 'doc_24051_4.pdf', 'Document_24051_4.pdf', 'cases/documents/21067/doc_24051_4.pdf', 350714, 'image/png', 'report', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, '419418250650', '2025-07-16 13:24:14'),
(194, 24052, 'doc_24052_2.pdf', 'Document_24052_2.pdf', 'cases/documents/21067/doc_24052_2.pdf', 720959, 'image/jpeg', 'court_filing', 'Auto-generated demo document', 20009, 1, 1, NULL, 1, 'E117D9CF845F', '2025-07-09 13:24:14'),
(195, 24052, 'doc_24052_4.pdf', 'Document_24052_4.pdf', 'cases/documents/21067/doc_24052_4.pdf', 877915, 'application/msword', 'correspondence', 'Auto-generated demo document', 20007, 0, 1, NULL, 1, 'F6515937D945', '2025-06-11 13:24:14'),
(196, 24097, 'doc_24097_1.pdf', 'Document_24097_1.pdf', 'cases/documents/21068/doc_24097_1.pdf', 736421, 'image/jpeg', 'evidence', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, 'D0DBB543436E', '2025-08-01 13:24:14'),
(197, 24097, 'doc_24097_2.pdf', 'Document_24097_2.pdf', 'cases/documents/21068/doc_24097_2.pdf', 211367, 'application/msword', 'correspondence', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, 'C13CF41F04F3', '2025-02-25 13:24:14'),
(198, 24098, 'doc_24098_1.pdf', 'Document_24098_1.pdf', 'cases/documents/21068/doc_24098_1.pdf', 651668, 'image/jpeg', 'medical_record', 'Auto-generated demo document', 20007, 0, 1, NULL, 1, 'F1267383C499', '2025-10-26 13:24:14'),
(199, 24098, 'doc_24098_2.pdf', 'Document_24098_2.pdf', 'cases/documents/21068/doc_24098_2.pdf', 62878, 'image/jpeg', 'medical_record', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, 'FCA80543F2A0', '2025-07-22 13:24:14'),
(200, 24098, 'doc_24098_4.pdf', 'Document_24098_4.pdf', 'cases/documents/21068/doc_24098_4.pdf', 386988, 'application/msword', 'evidence', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, '3254D054C953', '2025-07-01 13:24:14'),
(201, 24079, 'doc_24079_1.pdf', 'Document_24079_1.pdf', 'cases/documents/21069/doc_24079_1.pdf', 762364, 'image/png', 'medical_record', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, '2A54B688A952', '2025-02-16 13:24:14'),
(202, 24079, 'doc_24079_2.pdf', 'Document_24079_2.pdf', 'cases/documents/21069/doc_24079_2.pdf', 170302, 'image/jpeg', 'evidence', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, 'D7EF4C675FBD', '2025-05-08 13:24:14'),
(203, 24079, 'doc_24079_4.pdf', 'Document_24079_4.pdf', 'cases/documents/21069/doc_24079_4.pdf', 964085, 'application/msword', 'correspondence', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, '59CF6809673D', '2025-07-20 13:24:14'),
(204, 24080, 'doc_24080_1.pdf', 'Document_24080_1.pdf', 'cases/documents/21069/doc_24080_1.pdf', 80624, 'image/jpeg', 'correspondence', 'Auto-generated demo document', 20007, 1, 1, NULL, 1, '341E4F70D079', '2025-02-27 13:24:14'),
(205, 24080, 'doc_24080_2.pdf', 'Document_24080_2.pdf', 'cases/documents/21069/doc_24080_2.pdf', 696853, 'application/pdf', 'medical_record', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, '57626FA15D89', '2025-07-13 13:24:14'),
(206, 24080, 'doc_24080_4.pdf', 'Document_24080_4.pdf', 'cases/documents/21069/doc_24080_4.pdf', 673189, 'application/pdf', 'correspondence', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, '18035ACC600D', '2025-04-23 13:24:14'),
(207, 24095, 'doc_24095_2.pdf', 'Document_24095_2.pdf', 'cases/documents/21071/doc_24095_2.pdf', 314604, 'image/jpeg', 'correspondence', 'Auto-generated demo document', 21017, 1, 1, NULL, 1, '23E313588F8C', '2025-06-30 13:24:14'),
(208, 24095, 'doc_24095_4.pdf', 'Document_24095_4.pdf', 'cases/documents/21071/doc_24095_4.pdf', 682826, 'image/png', 'report', 'Auto-generated demo document', 20007, 1, 1, NULL, 1, '36DC94D0DB72', '2025-04-12 13:24:14'),
(209, 24096, 'doc_24096_1.pdf', 'Document_24096_1.pdf', 'cases/documents/21071/doc_24096_1.pdf', 392224, 'application/msword', 'medical_record', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, 'CDD4565B3751', '2025-05-04 13:24:14'),
(210, 24096, 'doc_24096_2.pdf', 'Document_24096_2.pdf', 'cases/documents/21071/doc_24096_2.pdf', 866747, 'application/msword', 'report', 'Auto-generated demo document', 20007, 0, 1, NULL, 1, '279C87E49E72', '2025-10-10 13:24:14'),
(211, 24013, 'doc_24013_2.pdf', 'Document_24013_2.pdf', 'cases/documents/21072/doc_24013_2.pdf', 382650, 'image/jpeg', 'evidence', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, '4D9191513646', '2025-02-17 13:24:14'),
(212, 24013, 'doc_24013_4.pdf', 'Document_24013_4.pdf', 'cases/documents/21072/doc_24013_4.pdf', 920414, 'application/msword', 'court_filing', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, 'CD8A1D4B3628', '2025-04-29 13:24:14'),
(213, 24014, 'doc_24014_1.pdf', 'Document_24014_1.pdf', 'cases/documents/21072/doc_24014_1.pdf', 281725, 'image/jpeg', 'court_filing', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, '61B89A7986E2', '2025-08-03 13:24:14'),
(214, 24014, 'doc_24014_2.pdf', 'Document_24014_2.pdf', 'cases/documents/21072/doc_24014_2.pdf', 574573, 'application/msword', 'court_filing', 'Auto-generated demo document', 20007, 0, 1, NULL, 1, '93F656F64FD9', '2025-03-24 13:24:14'),
(215, 24101, 'doc_24101_1.pdf', 'Document_24101_1.pdf', 'cases/documents/21073/doc_24101_1.pdf', 748489, 'application/msword', 'court_filing', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, '730425A9CC10', '2025-06-01 13:24:14'),
(216, 24101, 'doc_24101_2.pdf', 'Document_24101_2.pdf', 'cases/documents/21073/doc_24101_2.pdf', 204601, 'image/jpeg', 'correspondence', 'Auto-generated demo document', 21018, 1, 1, NULL, 1, 'FB884263EE21', '2025-04-24 13:24:14'),
(217, 24101, 'doc_24101_4.pdf', 'Document_24101_4.pdf', 'cases/documents/21073/doc_24101_4.pdf', 912862, 'application/pdf', 'medical_record', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, 'E0B3F81B82CF', '2025-01-09 13:24:14');
INSERT INTO `case_documents` (`id`, `case_id`, `filename`, `original_filename`, `file_path`, `file_size`, `mime_type`, `document_type`, `description`, `uploaded_by`, `is_confidential`, `version`, `parent_document_id`, `is_current`, `checksum`, `uploaded_at`) VALUES
(218, 24102, 'doc_24102_1.pdf', 'Document_24102_1.pdf', 'cases/documents/21073/doc_24102_1.pdf', 243141, 'image/jpeg', 'court_filing', 'Auto-generated demo document', 20008, 1, 1, NULL, 1, '49895F7D2625', '2025-09-04 13:24:14'),
(219, 24102, 'doc_24102_2.pdf', 'Document_24102_2.pdf', 'cases/documents/21073/doc_24102_2.pdf', 595594, 'application/msword', 'correspondence', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, '701F3281C07C', '2025-10-10 13:24:14'),
(220, 24031, 'doc_24031_1.pdf', 'Document_24031_1.pdf', 'cases/documents/21075/doc_24031_1.pdf', 566298, 'image/png', 'evidence', 'Auto-generated demo document', 20007, 1, 1, NULL, 1, '8149A4920526', '2025-05-24 13:24:14'),
(221, 24031, 'doc_24031_2.pdf', 'Document_24031_2.pdf', 'cases/documents/21075/doc_24031_2.pdf', 891015, 'image/jpeg', 'correspondence', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, '26FB9AA09BEE', '2025-07-08 13:24:14'),
(222, 24031, 'doc_24031_4.pdf', 'Document_24031_4.pdf', 'cases/documents/21075/doc_24031_4.pdf', 890958, 'image/jpeg', 'evidence', 'Auto-generated demo document', 21027, 0, 1, NULL, 0, '05B4B50016D2', '2025-01-10 13:24:14'),
(223, 24032, 'doc_24032_1.pdf', 'Document_24032_1.pdf', 'cases/documents/21075/doc_24032_1.pdf', 933537, 'image/jpeg', 'report', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, '9A626CC26989', '2025-09-16 13:24:14'),
(224, 24041, 'doc_24041_1.pdf', 'Document_24041_1.pdf', 'cases/documents/21076/doc_24041_1.pdf', 620236, 'application/pdf', 'correspondence', 'Auto-generated demo document', 21017, 0, 1, NULL, 0, '44C355FD130D', '2025-07-09 13:24:14'),
(225, 24041, 'doc_24041_2.pdf', 'Document_24041_2.pdf', 'cases/documents/21076/doc_24041_2.pdf', 106622, 'application/pdf', 'evidence', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, 'FBD31923EF4C', '2025-02-01 13:24:14'),
(226, 24041, 'doc_24041_4.pdf', 'Document_24041_4.pdf', 'cases/documents/21076/doc_24041_4.pdf', 904966, 'application/pdf', 'report', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, '2442C3FC910B', '2025-02-23 13:24:14'),
(227, 24042, 'doc_24042_1.pdf', 'Document_24042_1.pdf', 'cases/documents/21076/doc_24042_1.pdf', 905882, 'image/png', 'court_filing', 'Auto-generated demo document', 20008, 0, 1, NULL, 1, 'FBA8722BEEA1', '2025-04-24 13:24:14'),
(228, 24042, 'doc_24042_2.pdf', 'Document_24042_2.pdf', 'cases/documents/21076/doc_24042_2.pdf', 939284, 'image/jpeg', 'correspondence', 'Auto-generated demo document', 21019, 0, 1, NULL, 1, '43A0DDBD0E83', '2025-08-21 13:24:14'),
(229, 24042, 'doc_24042_4.pdf', 'Document_24042_4.pdf', 'cases/documents/21076/doc_24042_4.pdf', 70326, 'application/pdf', 'court_filing', 'Auto-generated demo document', 20010, 0, 1, NULL, 1, '94E23BEE5388', '2025-04-17 13:24:14'),
(230, 24099, 'doc_24099_1.pdf', 'Document_24099_1.pdf', 'cases/documents/21077/doc_24099_1.pdf', 467803, 'application/msword', 'correspondence', 'Auto-generated demo document', 21019, 0, 1, NULL, 1, 'F9B647CBE6D9', '2025-07-14 13:24:14'),
(231, 24099, 'doc_24099_4.pdf', 'Document_24099_4.pdf', 'cases/documents/21077/doc_24099_4.pdf', 966389, 'image/png', 'court_filing', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, '410962A50425', '2025-10-16 13:24:14'),
(232, 24100, 'doc_24100_1.pdf', 'Document_24100_1.pdf', 'cases/documents/21077/doc_24100_1.pdf', 108272, 'application/pdf', 'evidence', 'Auto-generated demo document', 20008, 0, 1, NULL, 1, 'CBBDFF5B2EF8', '2025-06-20 13:24:14'),
(233, 24100, 'doc_24100_2.pdf', 'Document_24100_2.pdf', 'cases/documents/21077/doc_24100_2.pdf', 580205, 'image/png', 'medical_record', 'Auto-generated demo document', 21020, 0, 1, NULL, 1, '94D4B6EA5352', '2025-01-25 13:24:14'),
(234, 24021, 'doc_24021_1.pdf', 'Document_24021_1.pdf', 'cases/documents/21078/doc_24021_1.pdf', 797105, 'image/png', 'correspondence', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, 'F4F09473D3C2', '2025-09-18 13:24:14'),
(235, 24021, 'doc_24021_2.pdf', 'Document_24021_2.pdf', 'cases/documents/21078/doc_24021_2.pdf', 578064, 'image/jpeg', 'correspondence', 'Auto-generated demo document', 20010, 0, 1, NULL, 1, '4FED95553FB6', '2025-02-10 13:24:14'),
(236, 24021, 'doc_24021_4.pdf', 'Document_24021_4.pdf', 'cases/documents/21078/doc_24021_4.pdf', 361523, 'image/png', 'correspondence', 'Auto-generated demo document', 20010, 0, 1, NULL, 1, 'CC1F0AEB307C', '2025-05-11 13:24:14'),
(237, 24022, 'doc_24022_1.pdf', 'Document_24022_1.pdf', 'cases/documents/21078/doc_24022_1.pdf', 476068, 'application/pdf', 'correspondence', 'Auto-generated demo document', 21018, 0, 1, NULL, 1, 'A496DB4E925B', '2025-09-04 13:24:14'),
(238, 24022, 'doc_24022_4.pdf', 'Document_24022_4.pdf', 'cases/documents/21078/doc_24022_4.pdf', 386972, 'application/pdf', 'court_filing', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, '868C6D961A31', '2025-09-21 13:24:14'),
(239, 24015, 'doc_24015_1.pdf', 'Document_24015_1.pdf', 'cases/documents/21079/doc_24015_1.pdf', 814397, 'application/pdf', 'correspondence', 'Auto-generated demo document', 20007, 0, 1, NULL, 1, '5A2124C16884', '2025-04-10 13:24:14'),
(240, 24015, 'doc_24015_2.pdf', 'Document_24015_2.pdf', 'cases/documents/21079/doc_24015_2.pdf', 425566, 'application/pdf', 'correspondence', 'Auto-generated demo document', 20009, 0, 1, NULL, 1, '55E8D36D57A3', '2025-05-20 13:24:14'),
(241, 24015, 'doc_24015_4.pdf', 'Document_24015_4.pdf', 'cases/documents/21079/doc_24015_4.pdf', 708741, 'image/png', 'correspondence', 'Auto-generated demo document', 20007, 0, 1, NULL, 1, 'F6C598E3DB16', '2025-01-29 13:24:14'),
(242, 24016, 'doc_24016_1.pdf', 'Document_24016_1.pdf', 'cases/documents/21079/doc_24016_1.pdf', 494741, 'image/jpeg', 'evidence', 'Auto-generated demo document', 21017, 1, 1, NULL, 1, '505D808D4176', '2025-06-30 13:24:14'),
(243, 24016, 'doc_24016_2.pdf', 'Document_24016_2.pdf', 'cases/documents/21079/doc_24016_2.pdf', 115042, 'application/pdf', 'correspondence', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, '72173725C85C', '2025-07-12 13:24:14'),
(244, 24016, 'doc_24016_4.pdf', 'Document_24016_4.pdf', 'cases/documents/21079/doc_24016_4.pdf', 243306, 'image/png', 'correspondence', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, '79316E15E4C5', '2025-08-14 13:24:14'),
(245, 24087, 'doc_24087_2.pdf', 'Document_24087_2.pdf', 'cases/documents/21081/doc_24087_2.pdf', 288860, 'image/jpeg', 'report', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, '771C3781DC70', '2025-06-06 13:24:14'),
(246, 24087, 'doc_24087_4.pdf', 'Document_24087_4.pdf', 'cases/documents/21081/doc_24087_4.pdf', 585148, 'application/msword', 'correspondence', 'Auto-generated demo document', 21020, 0, 1, NULL, 1, 'BCAE90EAF2BA', '2025-07-06 13:24:14'),
(247, 24088, 'doc_24088_1.pdf', 'Document_24088_1.pdf', 'cases/documents/21081/doc_24088_1.pdf', 328987, 'image/jpeg', 'court_filing', 'Auto-generated demo document', 21028, 0, 1, NULL, 1, 'CD31D30334C7', '2025-09-30 13:24:14'),
(248, 24088, 'doc_24088_2.pdf', 'Document_24088_2.pdf', 'cases/documents/21081/doc_24088_2.pdf', 941739, 'image/png', 'report', 'Auto-generated demo document', 21020, 0, 1, NULL, 1, '4D4BD061352F', '2025-05-01 13:24:14'),
(249, 24088, 'doc_24088_4.pdf', 'Document_24088_4.pdf', 'cases/documents/21081/doc_24088_4.pdf', 628723, 'application/msword', 'report', 'Auto-generated demo document', 21027, 0, 1, NULL, 1, '5D241A7D7490', '2025-02-04 13:24:14'),
(250, 24065, 'doc_24065_1.pdf', 'Document_24065_1.pdf', 'cases/documents/21082/doc_24065_1.pdf', 971918, 'application/pdf', 'medical_record', 'Auto-generated demo document', 21017, 0, 1, NULL, 1, '80FB98FE03EE', '2025-03-01 13:24:14'),
(256, 24001, 'doc_24001_2_v2.pdf', 'Document_24001_2 v2.pdf', 'cases/documents/21001/doc_24001_2_v2.pdf', 447462, 'application/msword', 'correspondence', 'Revised version', 21020, 0, 2, 2, 1, 'C7B6FB371EDB', '2025-07-17 13:24:14'),
(257, 24078, 'doc_24078_2_v2.pdf', 'Document_24078_2 v2.pdf', 'cases/documents/21001/doc_24078_2_v2.pdf', 118528, 'image/png', 'medical_record', 'Revised version', 20009, 0, 2, 8, 1, 'CBD8DC9F2F63', '2025-08-03 13:24:14'),
(258, 24003, 'doc_24003_1_v2.pdf', 'Document_24003_1 v2.pdf', 'cases/documents/21003/doc_24003_1_v2.pdf', 74238, 'image/jpeg', 'court_filing', 'Revised version', 21028, 0, 2, 16, 1, 'D78861A75E21', '2025-09-03 13:24:14'),
(259, 24033, 'doc_24033_4_v2.pdf', 'Document_24033_4 v2.pdf', 'cases/documents/21004/doc_24033_4_v2.pdf', 961146, 'application/pdf', 'correspondence', 'Revised version', 20007, 0, 2, 24, 1, 'AFCB5CDEBF2D', '2025-08-30 13:24:14'),
(260, 24034, 'doc_24034_4_v2.pdf', 'Document_24034_4 v2.pdf', 'cases/documents/21004/doc_24034_4_v2.pdf', 71133, 'application/msword', 'evidence', 'Revised version', 21020, 0, 2, 27, 1, 'A778020A9DE0', '2025-02-18 13:24:14'),
(261, 24005, 'doc_24005_2_v2.pdf', 'Document_24005_2 v2.pdf', 'cases/documents/21005/doc_24005_2_v2.pdf', 359887, 'application/msword', 'report', 'Revised version', 20009, 0, 2, 29, 1, 'CDCE39AB3738', '2025-04-26 13:24:14'),
(262, 24054, 'doc_24054_2_v2.pdf', 'Document_24054_2 v2.pdf', 'cases/documents/21005/doc_24054_2_v2.pdf', 698595, 'image/png', 'court_filing', 'Revised version', 21020, 0, 2, 33, 1, '8AF2707A2BC9', '2025-09-25 13:24:14'),
(263, 24035, 'doc_24035_4_v2.pdf', 'Document_24035_4 v2.pdf', 'cases/documents/21008/doc_24035_4_v2.pdf', 233383, 'image/jpeg', 'evidence', 'Revised version', 20008, 0, 2, 48, 1, '6D583761B560', '2025-10-06 13:24:14'),
(264, 24067, 'doc_24067_1_v2.pdf', 'Document_24067_1 v2.pdf', 'cases/documents/21039/doc_24067_1_v2.pdf', 153660, 'application/msword', 'medical_record', 'Revised version', 21020, 0, 2, 76, 1, '57C0EC415F03', '2025-09-03 13:24:14'),
(265, 24045, 'doc_24045_1_v2.pdf', 'Document_24045_1 v2.pdf', 'cases/documents/21044/doc_24045_1_v2.pdf', 149485, 'image/jpeg', 'court_filing', 'Revised version', 21019, 0, 2, 92, 1, '6DC9DBE1B727', '2025-01-23 13:24:14'),
(266, 24045, 'doc_24045_2_v2.pdf', 'Document_24045_2 v2.pdf', 'cases/documents/21044/doc_24045_2_v2.pdf', 177560, 'application/pdf', 'evidence', 'Revised version', 21018, 0, 2, 93, 1, '08BECC3022FB', '2025-04-28 13:24:14'),
(267, 24046, 'doc_24046_4_v2.pdf', 'Document_24046_4 v2.pdf', 'cases/documents/21044/doc_24046_4_v2.pdf', 953270, 'image/jpeg', 'medical_record', 'Revised version', 21019, 0, 2, 96, 1, 'B0833F3AC20D', '2025-08-11 13:24:14'),
(268, 24086, 'doc_24086_1_v2.pdf', 'Document_24086_1 v2.pdf', 'cases/documents/21047/doc_24086_1_v2.pdf', 932389, 'application/msword', 'evidence', 'Revised version', 20008, 0, 2, 108, 1, '9EC8E5F67B23', '2025-07-19 13:24:14'),
(269, 24038, 'doc_24038_2_v2.pdf', 'Document_24038_2 v2.pdf', 'cases/documents/21048/doc_24038_2_v2.pdf', 398117, 'application/pdf', 'court_filing', 'Revised version', 20009, 0, 2, 115, 1, 'ECC9F85FB327', '2025-01-26 13:24:14'),
(270, 24020, 'doc_24020_2_v2.pdf', 'Document_24020_2 v2.pdf', 'cases/documents/21052/doc_24020_2_v2.pdf', 783895, 'application/pdf', 'medical_record', 'Revised version', 21018, 0, 2, 132, 1, 'D0CA30DF4328', '2025-10-19 13:24:14'),
(271, 24108, 'doc_24108_1_v2.pdf', 'Document_24108_1 v2.pdf', 'cases/documents/21053/doc_24108_1_v2.pdf', 687903, 'image/jpeg', 'report', 'Revised version', 21027, 0, 2, 137, 1, '4AA39F9D2A8E', '2025-10-24 13:24:14'),
(272, 24025, 'doc_24025_1_v2.pdf', 'Document_24025_1 v2.pdf', 'cases/documents/21057/doc_24025_1_v2.pdf', 607352, 'image/jpeg', 'medical_record', 'Revised version', 21027, 1, 2, 145, 1, 'CFD729A73F5C', '2025-05-12 13:24:14'),
(273, 24025, 'doc_24025_4_v2.pdf', 'Document_24025_4 v2.pdf', 'cases/documents/21057/doc_24025_4_v2.pdf', 442336, 'application/pdf', 'correspondence', 'Revised version', 21028, 1, 2, 147, 1, 'C5DEF9A3177B', '2025-04-12 13:24:14'),
(274, 24060, 'doc_24060_1_v2.pdf', 'Document_24060_1 v2.pdf', 'cases/documents/21060/doc_24060_1_v2.pdf', 754085, 'application/pdf', 'correspondence', 'Revised version', 20010, 0, 2, 163, 1, '7FD689C9FF5A', '2025-04-08 13:24:14'),
(275, 24070, 'doc_24070_2_v2.pdf', 'Document_24070_2 v2.pdf', 'cases/documents/21063/doc_24070_2_v2.pdf', 883412, 'application/msword', 'report', 'Revised version', 21019, 0, 2, 176, 1, '74C2470DD309', '2025-09-22 13:24:14'),
(276, 24105, 'doc_24105_1_v2.pdf', 'Document_24105_1 v2.pdf', 'cases/documents/21064/doc_24105_1_v2.pdf', 832180, 'application/msword', 'correspondence', 'Revised version', 20007, 0, 2, 177, 1, 'C5E4F46F1793', '2025-02-13 13:24:14'),
(277, 24058, 'doc_24058_1_v2.pdf', 'Document_24058_1 v2.pdf', 'cases/documents/21065/doc_24058_1_v2.pdf', 841849, 'image/jpeg', 'medical_record', 'Revised version', 21019, 0, 2, 184, 1, '4C4B4995312D', '2025-09-28 13:24:14'),
(278, 24091, 'doc_24091_4_v2.pdf', 'Document_24091_4 v2.pdf', 'cases/documents/21066/doc_24091_4_v2.pdf', 689923, 'application/pdf', 'medical_record', 'Revised version', 21020, 1, 2, 188, 1, '84C796E2131E', '2025-10-24 13:24:14'),
(279, 24031, 'doc_24031_4_v2.pdf', 'Document_24031_4 v2.pdf', 'cases/documents/21075/doc_24031_4_v2.pdf', 901198, 'image/jpeg', 'evidence', 'Revised version', 21027, 0, 2, 222, 1, 'C836087720D8', '2025-01-17 13:24:14'),
(280, 24041, 'doc_24041_1_v2.pdf', 'Document_24041_1 v2.pdf', 'cases/documents/21076/doc_24041_1_v2.pdf', 630476, 'application/pdf', 'correspondence', 'Revised version', 21017, 0, 2, 224, 1, 'A7EE5B529FB9', '2025-07-16 13:24:14');

-- --------------------------------------------------------

--
-- Table structure for table `compensations`
--

CREATE TABLE `compensations` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `compensation_amount` decimal(10,2) NOT NULL,
  `client_payment_amount` decimal(10,2) NOT NULL COMMENT 'Auto-calculated as 25% of compensation_amount',
  `status` enum('pending','approved','paid','completed') NOT NULL DEFAULT 'pending',
  `entered_by` int(11) NOT NULL,
  `entered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `compliance_logs`
--

CREATE TABLE `compliance_logs` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `action_type` enum('status_change','verification','approval','rejection','completion','note_added') NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `performed_by` int(11) NOT NULL,
  `performed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `compliance_requests`
--

CREATE TABLE `compliance_requests` (
  `id` int(11) NOT NULL,
  `request_type` enum('data_access','data_deletion','data_export','data_correction','consent_withdrawal') NOT NULL,
  `requestor_name` varchar(255) NOT NULL,
  `requestor_email` varchar(255) NOT NULL,
  `requestor_phone` varchar(50) DEFAULT NULL,
  `requestor_id_number` varchar(50) DEFAULT NULL,
  `subject` varchar(500) NOT NULL,
  `description` text NOT NULL,
  `status` enum('submitted','under_review','verified','approved','rejected','completed','cancelled') NOT NULL DEFAULT 'submitted',
  `priority` enum('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `verification_status` enum('pending','in_progress','verified','failed') NOT NULL DEFAULT 'pending',
  `verification_method` enum('email','phone','id_document','in_person') DEFAULT NULL,
  `verification_notes` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verified_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `compliance_templates`
--

CREATE TABLE `compliance_templates` (
  `id` int(11) NOT NULL,
  `template_name` varchar(255) NOT NULL,
  `template_type` enum('request_form','verification_checklist','response_letter','notification') NOT NULL,
  `subject` varchar(500) DEFAULT NULL,
  `content` text NOT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Available template variables' CHECK (json_valid(`variables`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `compliance_verification`
--

CREATE TABLE `compliance_verification` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `verification_step` varchar(255) NOT NULL,
  `verification_type` enum('identity','authorization','data_scope','legal_basis') NOT NULL,
  `status` enum('pending','in_progress','completed','failed','skipped') NOT NULL DEFAULT 'pending',
  `verification_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`verification_data`)),
  `verification_result` text DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `connection_tests`
--

CREATE TABLE `connection_tests` (
  `id` int(11) NOT NULL,
  `provider_id` int(11) DEFAULT NULL,
  `test_type` enum('provider','api','database','file_system','email','sms') NOT NULL,
  `test_name` varchar(255) NOT NULL,
  `test_endpoint` varchar(500) DEFAULT NULL,
  `status` enum('success','failed','timeout','error') NOT NULL,
  `response_time_ms` int(11) DEFAULT NULL,
  `response_data` text DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `tested_by` int(11) NOT NULL,
  `tested_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_submissions`
--

CREATE TABLE `contact_submissions` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('new','contacted','resolved','archived') DEFAULT 'new',
  `assigned_to` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `data_retention_holds`
--

CREATE TABLE `data_retention_holds` (
  `id` int(11) NOT NULL,
  `hold_name` varchar(255) NOT NULL,
  `hold_reason` text NOT NULL,
  `hold_type` enum('legal_hold','litigation_hold','regulatory_hold','investigation_hold') NOT NULL,
  `affected_data_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Array of data types affected by this hold' CHECK (json_valid(`affected_data_types`)),
  `affected_users` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of user IDs affected, NULL means all users' CHECK (json_valid(`affected_users`)),
  `affected_cases` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of case IDs affected, NULL means all cases' CHECK (json_valid(`affected_cases`)),
  `status` enum('active','suspended','lifted','expired') NOT NULL DEFAULT 'active',
  `hold_start_date` date NOT NULL,
  `hold_end_date` date DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `financial_requests`
--

CREATE TABLE `financial_requests` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `type` enum('expense','refund','advance','settlement','other') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
  `requested_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `health_check_results`
--

CREATE TABLE `health_check_results` (
  `id` int(11) NOT NULL,
  `check_id` int(11) NOT NULL,
  `status` enum('ok','warning','critical','unknown') NOT NULL,
  `result_value` decimal(15,4) DEFAULT NULL,
  `result_message` text DEFAULT NULL,
  `response_time_ms` int(11) DEFAULT NULL,
  `checked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `integration_status`
--

CREATE TABLE `integration_status` (
  `id` int(11) NOT NULL,
  `integration_name` varchar(255) NOT NULL,
  `integration_type` enum('api','database','file_system','email','sms','payment') NOT NULL,
  `status` enum('active','inactive','error','maintenance') NOT NULL DEFAULT 'active',
  `last_check` timestamp NULL DEFAULT NULL,
  `last_success` timestamp NULL DEFAULT NULL,
  `error_count` int(11) NOT NULL DEFAULT 0,
  `response_time_ms` int(11) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `config_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `case_id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 15.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','pending','sent','paid','overdue','void','cancelled') NOT NULL DEFAULT 'draft',
  `due_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `terms_conditions` text DEFAULT NULL,
  `payment_instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `invoices`
--
DELIMITER $$
CREATE TRIGGER `trg_invoices_bi_sync_amount` BEFORE INSERT ON `invoices` FOR EACH ROW BEGIN
    IF NEW.total_amount IS NULL OR NEW.total_amount = 0 THEN
        SET NEW.total_amount = IFNULL(NEW.amount, 0.00);
    END IF;
    IF NEW.amount IS NULL OR NEW.amount = 0 THEN
        SET NEW.amount = IFNULL(NEW.total_amount, 0.00);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_invoices_bu_sync_amount` BEFORE UPDATE ON `invoices` FOR EACH ROW BEGIN
    IF NEW.total_amount <> OLD.total_amount THEN
        SET NEW.amount = NEW.total_amount;
    ELSEIF NEW.amount <> OLD.amount THEN
        SET NEW.total_amount = NEW.amount;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 15.00,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_payments`
--

CREATE TABLE `invoice_payments` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `payfast_payment_id` varchar(100) DEFAULT NULL,
  `payfast_status` varchar(50) DEFAULT NULL,
  `payfast_raw_response` mediumtext DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_queue`
--

CREATE TABLE `job_queue` (
  `id` int(11) NOT NULL,
  `job_name` varchar(255) NOT NULL,
  `job_type` enum('backup','report','notification','cleanup','sync','custom') NOT NULL,
  `job_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`job_data`)),
  `priority` enum('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `status` enum('pending','running','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `retry_count` int(11) NOT NULL DEFAULT 0,
  `max_retries` int(11) NOT NULL DEFAULT 3,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `thread_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `body` text DEFAULT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `message_type` varchar(50) DEFAULT NULL,
  `has_attachments` tinyint(1) NOT NULL DEFAULT 0,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `thread_id`, `sender_id`, `body`, `recipient_id`, `message`, `message_type`, `has_attachments`, `is_read`, `read_at`, `created_at`) VALUES
(39001, 38001, 21001, NULL, 20007, 'Hello, what documents are needed?', NULL, 0, 0, NULL, '2025-10-27 13:24:10'),
(39002, 38001, 20007, NULL, 21001, 'Please upload the accident report and medical notes.', NULL, 0, 0, NULL, '2025-10-27 13:24:10'),
(39003, 38002, 21002, NULL, 20008, 'Could you confirm the hospital contact?', NULL, 0, 0, NULL, '2025-10-27 13:24:10'),
(39004, 38002, 20008, NULL, 21002, 'Yes, we will request them. Details attached.', NULL, 0, 0, NULL, '2025-10-27 13:24:10'),
(39005, 38001, 20010, NULL, 21001, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39006, 38001, 21020, NULL, 21001, 'Noted. I will revert with details.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39007, 38001, 21001, NULL, 20007, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39008, 38001, 21001, NULL, 20007, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39009, 38003, 21001, NULL, 20007, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39010, 38003, 21019, NULL, 21001, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39011, 38003, 20007, NULL, 21001, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39012, 38002, 21002, NULL, 20008, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39013, 38002, 21002, NULL, 20008, 'Update: we have received the hospital file.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39014, 38002, 21020, NULL, 21002, 'Can we schedule a call next week?', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39015, 38002, 21018, NULL, 21002, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39016, 38004, 21002, NULL, 20008, 'Can we schedule a call next week?', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39017, 38004, 21024, NULL, 21002, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39018, 38004, 20004, NULL, 21002, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39019, 38005, 21003, NULL, 20009, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39020, 38005, 21024, NULL, 21003, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39021, 38005, 21017, NULL, 21003, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39022, 38005, 21003, NULL, 20009, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39023, 38006, 21004, NULL, 20007, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39024, 38006, 21004, NULL, 20007, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39025, 38006, 20005, NULL, 21004, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39026, 38006, 21004, NULL, 20007, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39027, 38007, 21018, NULL, 21005, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39028, 38007, 21019, NULL, 21005, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39029, 38007, 21005, NULL, 20009, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39030, 38008, 20006, NULL, 21006, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39031, 38008, 21006, NULL, 20007, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39032, 38008, 21006, NULL, 20007, 'Please see attached records.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39033, 38009, 21007, NULL, 20008, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39034, 38009, 21007, NULL, 20008, 'Update: we have received the hospital file.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39035, 38009, 21020, NULL, 21007, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39036, 38010, 21008, NULL, 20009, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39037, 38010, 21008, NULL, 20009, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39038, 38010, 21008, NULL, 20009, 'Can we schedule a call next week?', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39039, 38010, 21008, NULL, 20009, 'Noted. I will revert with details.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39040, 38011, 21009, NULL, 20007, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39041, 38011, 21013, NULL, 21009, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39042, 38011, 21009, NULL, 20007, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39043, 38011, 21009, NULL, 20007, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39044, 38012, 21010, NULL, 20008, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39045, 38012, 21010, NULL, 20008, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39046, 38012, 21018, NULL, 21010, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39047, 38012, 20008, NULL, 21010, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39048, 38013, 20010, NULL, 21062, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39049, 38013, 21019, NULL, 21062, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39050, 38014, 20004, NULL, 21062, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39051, 38014, 21019, NULL, 21062, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39052, 38014, 21062, NULL, 21015, 'Acknowledged. We will proceed accordingly.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39053, 38015, 21072, NULL, 21019, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39054, 38015, 21014, NULL, 21072, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39055, 38015, 21024, NULL, 21072, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39056, 38015, 21072, NULL, 21019, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39057, 38016, 21016, NULL, 21079, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39058, 38016, 21079, NULL, 20008, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39059, 38017, 21019, NULL, 21079, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39060, 38017, 21015, NULL, 21079, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39061, 38017, 21014, NULL, 21079, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39062, 38017, 21079, NULL, 20011, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39063, 38018, 20010, NULL, 21045, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39064, 38018, 21023, NULL, 21045, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39065, 38018, 21045, NULL, 21018, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39066, 38019, 21019, NULL, 21052, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39067, 38019, 20010, NULL, 21052, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39068, 38020, 21052, NULL, 21019, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39069, 38020, 20010, NULL, 21052, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39070, 38020, 21052, NULL, 21019, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39071, 38021, 21017, NULL, 21078, 'Noted. I will revert with details.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39072, 38021, 21078, NULL, 20006, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39073, 38021, 21013, NULL, 21078, 'Can we schedule a call next week?', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39074, 38022, 21078, NULL, 21017, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39075, 38022, 20004, NULL, 21078, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39076, 38022, 21078, NULL, 21017, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39077, 38022, 20010, NULL, 21078, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39078, 38023, 21042, NULL, 21019, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39079, 38023, 21042, NULL, 21019, 'Update: we have received the hospital file.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39080, 38023, 20011, NULL, 21042, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39081, 38023, 21016, NULL, 21042, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39082, 38024, 21057, NULL, 21020, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39083, 38024, 21057, NULL, 21020, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39084, 38025, 21057, NULL, 20008, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39085, 38025, 20006, NULL, 21057, 'Noted. I will revert with details.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39086, 38025, 21057, NULL, 20008, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39087, 38025, 21019, NULL, 21057, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39088, 38026, 21015, NULL, 21037, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39089, 38026, 20004, NULL, 21037, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39090, 38026, 21037, NULL, 20006, 'Acknowledged. We will proceed accordingly.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39091, 38026, 21037, NULL, 20006, 'Please see attached records.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39092, 38027, 21051, NULL, 21024, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39093, 38027, 21051, NULL, 21024, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39094, 38027, 21051, NULL, 21024, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39095, 38028, 21051, NULL, 21017, 'Can we schedule a call next week?', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39096, 38028, 21015, NULL, 21051, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39097, 38028, 21017, NULL, 21051, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39098, 38028, 20010, NULL, 21051, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39099, 38029, 20007, NULL, 21075, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39100, 38029, 21075, NULL, 21024, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39101, 38029, 21017, NULL, 21075, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39102, 38029, 21017, NULL, 21075, 'Acknowledged. We will proceed accordingly.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39103, 38030, 20010, NULL, 21004, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39104, 38030, 21004, NULL, 21016, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39105, 38030, 21016, NULL, 21004, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39106, 38030, 21014, NULL, 21004, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39107, 38031, 21008, NULL, 20008, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39108, 38031, 21008, NULL, 20008, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39109, 38031, 21013, NULL, 21008, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39110, 38031, 21014, NULL, 21008, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39111, 38032, 21008, NULL, 21019, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39112, 38032, 20008, NULL, 21008, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39113, 38032, 21008, NULL, 21019, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39114, 38032, 21008, NULL, 21019, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39115, 38033, 21013, NULL, 21048, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39116, 38033, 21048, NULL, 20010, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39117, 38033, 21048, NULL, 20010, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39118, 38034, 21015, NULL, 21048, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39119, 38034, 20004, NULL, 21048, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39120, 38034, 21023, NULL, 21048, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39121, 38034, 21048, NULL, 21018, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39122, 38035, 21049, NULL, 20010, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39123, 38035, 21015, NULL, 21049, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39124, 38035, 21049, NULL, 20010, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39125, 38035, 20004, NULL, 21049, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39126, 38036, 21076, NULL, 21023, 'Update: we have received the hospital file.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39127, 38036, 21076, NULL, 21023, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39128, 38036, 20006, NULL, 21076, 'Noted. I will revert with details.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39129, 38036, 21024, NULL, 21076, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39130, 38037, 21076, NULL, 21017, 'Update: we have received the hospital file.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39131, 38037, 21076, NULL, 21017, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39132, 38037, 21076, NULL, 21017, 'Noted. I will revert with details.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39133, 38038, 21055, NULL, 21016, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39134, 38038, 20011, NULL, 21055, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39135, 38038, 21019, NULL, 21055, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39136, 38039, 20005, NULL, 21055, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39137, 38039, 21055, NULL, 20006, 'Update: we have received the hospital file.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39138, 38039, 21055, NULL, 20006, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39139, 38039, 21055, NULL, 20006, 'Can we schedule a call next week?', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39140, 38040, 21044, NULL, 21023, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39141, 38040, 20010, NULL, 21044, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39142, 38040, 21024, NULL, 21044, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39143, 38040, 21044, NULL, 21023, 'Please see attached records.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39144, 38041, 21085, NULL, 21016, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39145, 38041, 20008, NULL, 21085, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39146, 38041, 21085, NULL, 21016, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39147, 38041, 20010, NULL, 21085, 'Noted. I will revert with details.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39148, 38042, 21085, NULL, 21020, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39149, 38042, 21085, NULL, 21020, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39150, 38042, 21018, NULL, 21085, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39151, 38042, 20006, NULL, 21085, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39152, 38043, 21024, NULL, 21046, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39153, 38043, 20006, NULL, 21046, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39154, 38043, 21046, NULL, 20007, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39155, 38043, 20006, NULL, 21046, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39156, 38044, 21046, NULL, 21015, 'Acknowledged. We will proceed accordingly.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39157, 38044, 21046, NULL, 21015, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39158, 38044, 21046, NULL, 21015, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39159, 38045, 21015, NULL, 21067, 'Acknowledged. We will proceed accordingly.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39160, 38045, 21024, NULL, 21067, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39161, 38045, 20011, NULL, 21067, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39162, 38045, 21024, NULL, 21067, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39163, 38046, 21016, NULL, 21067, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39164, 38046, 21019, NULL, 21067, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39165, 38046, 21020, NULL, 21067, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39166, 38046, 20004, NULL, 21067, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39167, 38047, 21005, NULL, 21017, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39168, 38047, 20005, NULL, 21005, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39169, 38047, 21018, NULL, 21005, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39170, 38047, 20004, NULL, 21005, 'Update: we have received the hospital file.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39171, 38048, 21014, NULL, 21005, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39172, 38048, 21005, NULL, 21019, 'Acknowledged. We will proceed accordingly.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39173, 38048, 20006, NULL, 21005, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39174, 38048, 21019, NULL, 21005, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39175, 38049, 21006, NULL, 20006, 'Can we schedule a call next week?', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39176, 38049, 21006, NULL, 20006, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39177, 38049, 21018, NULL, 21006, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39178, 38049, 21018, NULL, 21006, 'Noted. I will revert with details.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39179, 38050, 20011, NULL, 21065, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39180, 38050, 21019, NULL, 21065, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39181, 38052, 21014, NULL, 21060, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39182, 38052, 21060, NULL, 21023, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39183, 38052, 21060, NULL, 21023, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39184, 38052, 21060, NULL, 21023, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39185, 38053, 21060, NULL, 21018, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39186, 38053, 21020, NULL, 21060, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39187, 38053, 21060, NULL, 21018, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39188, 38054, 21009, NULL, 21015, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39189, 38054, 20007, NULL, 21009, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39190, 38054, 21009, NULL, 21015, 'Update: we have received the hospital file.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39191, 38054, 21020, NULL, 21009, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39192, 38055, 21009, NULL, 20006, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39193, 38055, 21009, NULL, 20006, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39194, 38055, 21020, NULL, 21009, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39195, 38055, 20011, NULL, 21009, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39196, 38056, 21043, NULL, 21023, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39197, 38056, 21014, NULL, 21043, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39198, 38056, 21017, NULL, 21043, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39199, 38056, 21014, NULL, 21043, 'Please see attached records.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39200, 38057, 21043, NULL, 20007, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39201, 38057, 20008, NULL, 21043, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39202, 38057, 21043, NULL, 20007, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39203, 38057, 20008, NULL, 21043, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39204, 38058, 20011, NULL, 21082, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39205, 38058, 20008, NULL, 21082, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39206, 38058, 21082, NULL, 21015, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39207, 38058, 21082, NULL, 21015, 'Can we schedule a call next week?', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39208, 38059, 21082, NULL, 20006, 'Noted. I will revert with details.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39209, 38059, 21082, NULL, 20006, 'Acknowledged. We will proceed accordingly.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39210, 38059, 21082, NULL, 20006, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39211, 38059, 21082, NULL, 20006, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39212, 38060, 21014, NULL, 21039, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39213, 38060, 21015, NULL, 21039, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39214, 38060, 21039, NULL, 20006, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39215, 38060, 21018, NULL, 21039, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39216, 38061, 20005, NULL, 21063, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39217, 38061, 20004, NULL, 21063, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39218, 38061, 21019, NULL, 21063, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39219, 38061, 21063, NULL, 20007, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39220, 38062, 21063, NULL, 20006, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39221, 38062, 21013, NULL, 21063, 'Noted. I will revert with details.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39222, 38062, 21063, NULL, 20006, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39223, 38062, 21017, NULL, 21063, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39224, 38063, 21019, NULL, 21050, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39225, 38063, 21050, NULL, 20011, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39226, 38063, 20007, NULL, 21050, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39227, 38063, 21050, NULL, 20011, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39228, 38064, 21020, NULL, 21050, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39229, 38064, 21014, NULL, 21050, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39230, 38064, 20010, NULL, 21050, 'Can we schedule a call next week?', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39231, 38065, 21019, NULL, 21038, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39232, 38065, 20008, NULL, 21038, 'Acknowledged. We will proceed accordingly.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39233, 38065, 20009, NULL, 21038, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39234, 38065, 21038, NULL, 21020, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39235, 38066, 20007, NULL, 21038, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39236, 38066, 21038, NULL, 21023, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39237, 38066, 21019, NULL, 21038, 'Please see attached records.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39238, 38066, 20006, NULL, 21038, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39239, 38067, 21056, NULL, 21019, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39240, 38067, 21056, NULL, 21019, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39241, 38067, 21023, NULL, 21056, 'Noted. I will revert with details.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39242, 38067, 21056, NULL, 21019, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39243, 38068, 20008, NULL, 21056, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39244, 38068, 20011, NULL, 21056, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39245, 38068, 20006, NULL, 21056, 'Can we schedule a call next week?', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39246, 38068, 21056, NULL, 21020, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39247, 38069, 21001, NULL, 21020, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39248, 38069, 21016, NULL, 21001, 'Update: we have received the hospital file.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39249, 38069, 21001, NULL, 21020, 'Update: we have received the hospital file.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39250, 38070, 20006, NULL, 21001, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39251, 38070, 21001, NULL, 21015, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39252, 38070, 21001, NULL, 21015, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39253, 38071, 20010, NULL, 21069, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39254, 38071, 21069, NULL, 21018, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39255, 38071, 21020, NULL, 21069, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39256, 38072, 20004, NULL, 21069, 'Acknowledged. We will proceed accordingly.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39257, 38072, 20011, NULL, 21069, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39258, 38072, 21069, NULL, 21024, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39259, 38072, 21069, NULL, 21024, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39260, 38073, 21023, NULL, 21083, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39261, 38073, 20009, NULL, 21083, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39262, 38073, 20007, NULL, 21083, 'Noted. I will revert with details.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39263, 38074, 21084, NULL, 21016, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39264, 38074, 21084, NULL, 21016, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39265, 38074, 21019, NULL, 21084, 'Noted. I will revert with details.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39266, 38074, 21084, NULL, 21016, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39267, 38075, 20004, NULL, 21084, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39268, 38075, 21084, NULL, 21023, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39269, 38075, 21084, NULL, 21023, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39270, 38075, 21023, NULL, 21084, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39271, 38076, 21047, NULL, 21019, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39272, 38076, 21047, NULL, 21019, 'Please see attached records.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39273, 38076, 21047, NULL, 21019, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39274, 38076, 21047, NULL, 21019, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39275, 38077, 20008, NULL, 21081, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39276, 38077, 20004, NULL, 21081, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39277, 38077, 20009, NULL, 21081, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39278, 38078, 21010, NULL, 21020, 'Update: we have received the hospital file.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39279, 38078, 20004, NULL, 21010, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39280, 38078, 21024, NULL, 21010, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39281, 38079, 21010, NULL, 20010, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39282, 38079, 21023, NULL, 21010, 'Noted. I will revert with details.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39283, 38079, 20007, NULL, 21010, 'Can we schedule a call next week?', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39284, 38079, 21010, NULL, 20010, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39285, 38080, 21066, NULL, 21024, 'Acknowledged. We will proceed accordingly.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39286, 38080, 21020, NULL, 21066, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39287, 38080, 21024, NULL, 21066, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39288, 38081, 21002, NULL, 20011, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39289, 38081, 20007, NULL, 21002, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39290, 38081, 20010, NULL, 21002, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39291, 38081, 21018, NULL, 21002, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39292, 38082, 21002, NULL, 21024, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39293, 38082, 21002, NULL, 21024, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39294, 38082, 21002, NULL, 21024, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39295, 38082, 20004, NULL, 21002, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39296, 38083, 21013, NULL, 21071, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39297, 38083, 21071, NULL, 20009, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39298, 38083, 21019, NULL, 21071, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39299, 38083, 21015, NULL, 21071, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39300, 38084, 21013, NULL, 21071, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39301, 38084, 21071, NULL, 20011, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39302, 38084, 21024, NULL, 21071, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39303, 38085, 21068, NULL, 21017, 'Update: we have received the hospital file.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39304, 38085, 20009, NULL, 21068, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39305, 38085, 21068, NULL, 21017, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39306, 38086, 20005, NULL, 21068, 'Please see attached records.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39307, 38086, 20009, NULL, 21068, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39308, 38086, 21068, NULL, 21020, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39309, 38086, 21068, NULL, 21020, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39310, 38087, 21013, NULL, 21077, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39311, 38087, 20009, NULL, 21077, 'Noted. I will revert with details.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39312, 38087, 21077, NULL, 20007, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39313, 38088, 21073, NULL, 20011, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39314, 38088, 21073, NULL, 20011, 'Can we schedule a call next week?', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39315, 38088, 21020, NULL, 21073, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39316, 38089, 21015, NULL, 21073, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39317, 38089, 20010, NULL, 21073, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39318, 38089, 21017, NULL, 21073, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39319, 38089, 20010, NULL, 21073, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39320, 38090, 21023, NULL, 21059, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39321, 38090, 20006, NULL, 21059, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39322, 38090, 21018, NULL, 21059, 'Can we schedule a call next week?', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39323, 38090, 20007, NULL, 21059, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39324, 38091, 21018, NULL, 21064, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39325, 38091, 20005, NULL, 21064, 'Noted. I will revert with details.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39326, 38091, 21064, NULL, 20008, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39327, 38092, 21064, NULL, 20006, 'Acknowledged. We will proceed accordingly.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39328, 38092, 21019, NULL, 21064, 'Can we schedule a call next week?', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39329, 38092, 21015, NULL, 21064, 'Update: we have received the hospital file.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39330, 38092, 21064, NULL, 20006, 'Please see attached records.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39331, 38093, 21019, NULL, 21053, 'Can we schedule a call next week?', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39332, 38093, 21053, NULL, 21018, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39333, 38093, 21053, NULL, 21018, 'Can we schedule a call next week?', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39334, 38093, 21053, NULL, 21018, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39335, 38094, 21015, NULL, 21053, 'Update: we have received the hospital file.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39336, 38094, 21053, NULL, 21023, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39337, 38094, 20007, NULL, 21053, 'Noted. I will revert with details.', NULL, 1, 0, NULL, '2025-10-27 13:24:14'),
(39338, 38094, 21053, NULL, 21023, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39339, 38095, 20010, NULL, 21058, 'Please see attached records.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39340, 38095, 21024, NULL, 21058, 'Can we schedule a call next week?', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39341, 38095, 21020, NULL, 21058, 'Noted. I will revert with details.', NULL, 0, 0, NULL, '2025-10-27 13:24:14'),
(39342, 38095, 20009, NULL, 21058, 'Can we schedule a call next week?', NULL, 1, 0, NULL, '2025-10-27 13:24:14');

--
-- Triggers `messages`
--
DELIMITER $$
CREATE TRIGGER `messages_bi_sync` BEFORE INSERT ON `messages` FOR EACH ROW BEGIN
  SET NEW.body = COALESCE(NEW.body, NEW.message);
  SET NEW.message = COALESCE(NEW.message, NEW.body);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `messages_bu_sync` BEFORE UPDATE ON `messages` FOR EACH ROW BEGIN
  SET NEW.body = COALESCE(NEW.body, NEW.message);
  SET NEW.message = COALESCE(NEW.message, NEW.body);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `message_participants`
--

CREATE TABLE `message_participants` (
  `id` int(11) NOT NULL,
  `thread_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `last_read_message_id` int(11) DEFAULT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `left_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `message_participants`
--

INSERT INTO `message_participants` (`id`, `thread_id`, `user_id`, `last_read_message_id`, `joined_at`, `left_at`, `is_active`) VALUES
(1, 38001, 21001, NULL, '2025-10-27 13:24:10', NULL, 1),
(2, 38002, 21002, NULL, '2025-10-27 13:24:10', NULL, 1),
(3, 38003, 21001, NULL, '2025-10-27 13:24:14', NULL, 1),
(4, 38004, 21002, NULL, '2025-10-27 13:24:14', NULL, 1),
(5, 38005, 21003, NULL, '2025-10-27 13:24:14', NULL, 1),
(6, 38006, 21004, NULL, '2025-10-27 13:24:14', NULL, 1),
(7, 38007, 21005, NULL, '2025-10-27 13:24:14', NULL, 1),
(8, 38008, 21006, NULL, '2025-10-27 13:24:14', NULL, 1),
(9, 38009, 21007, NULL, '2025-10-27 13:24:14', NULL, 1),
(10, 38010, 21008, NULL, '2025-10-27 13:24:14', NULL, 1),
(11, 38011, 21009, NULL, '2025-10-27 13:24:14', NULL, 1),
(12, 38012, 21010, NULL, '2025-10-27 13:24:14', NULL, 1),
(13, 38013, 21062, NULL, '2025-10-27 13:24:14', NULL, 1),
(14, 38014, 21062, NULL, '2025-10-27 13:24:14', NULL, 1),
(15, 38015, 21072, NULL, '2025-10-27 13:24:14', NULL, 1),
(16, 38016, 21079, NULL, '2025-10-27 13:24:14', NULL, 1),
(17, 38017, 21079, NULL, '2025-10-27 13:24:14', NULL, 1),
(18, 38018, 21045, NULL, '2025-10-27 13:24:14', NULL, 1),
(19, 38019, 21052, NULL, '2025-10-27 13:24:14', NULL, 1),
(20, 38020, 21052, NULL, '2025-10-27 13:24:14', NULL, 1),
(21, 38021, 21078, NULL, '2025-10-27 13:24:14', NULL, 1),
(22, 38022, 21078, NULL, '2025-10-27 13:24:14', NULL, 1),
(23, 38023, 21042, NULL, '2025-10-27 13:24:14', NULL, 1),
(24, 38024, 21057, NULL, '2025-10-27 13:24:14', NULL, 1),
(25, 38025, 21057, NULL, '2025-10-27 13:24:14', NULL, 1),
(26, 38026, 21037, NULL, '2025-10-27 13:24:14', NULL, 1),
(27, 38027, 21051, NULL, '2025-10-27 13:24:14', NULL, 1),
(28, 38028, 21051, NULL, '2025-10-27 13:24:14', NULL, 1),
(29, 38029, 21075, NULL, '2025-10-27 13:24:14', NULL, 1),
(30, 38030, 21004, NULL, '2025-10-27 13:24:14', NULL, 1),
(31, 38031, 21008, NULL, '2025-10-27 13:24:14', NULL, 1),
(32, 38032, 21008, NULL, '2025-10-27 13:24:14', NULL, 1),
(33, 38033, 21048, NULL, '2025-10-27 13:24:14', NULL, 1),
(34, 38034, 21048, NULL, '2025-10-27 13:24:14', NULL, 1),
(35, 38035, 21049, NULL, '2025-10-27 13:24:14', NULL, 1),
(36, 38036, 21076, NULL, '2025-10-27 13:24:14', NULL, 1),
(37, 38037, 21076, NULL, '2025-10-27 13:24:14', NULL, 1),
(38, 38038, 21055, NULL, '2025-10-27 13:24:14', NULL, 1),
(39, 38039, 21055, NULL, '2025-10-27 13:24:14', NULL, 1),
(40, 38040, 21044, NULL, '2025-10-27 13:24:14', NULL, 1),
(41, 38041, 21085, NULL, '2025-10-27 13:24:14', NULL, 1),
(42, 38042, 21085, NULL, '2025-10-27 13:24:14', NULL, 1),
(43, 38043, 21046, NULL, '2025-10-27 13:24:14', NULL, 1),
(44, 38044, 21046, NULL, '2025-10-27 13:24:14', NULL, 1),
(45, 38045, 21067, NULL, '2025-10-27 13:24:14', NULL, 1),
(46, 38046, 21067, NULL, '2025-10-27 13:24:14', NULL, 1),
(47, 38047, 21005, NULL, '2025-10-27 13:24:14', NULL, 1),
(48, 38048, 21005, NULL, '2025-10-27 13:24:14', NULL, 1),
(49, 38049, 21006, NULL, '2025-10-27 13:24:14', NULL, 1),
(50, 38050, 21065, NULL, '2025-10-27 13:24:14', NULL, 1),
(51, 38051, 21065, NULL, '2025-10-27 13:24:14', NULL, 1),
(52, 38052, 21060, NULL, '2025-10-27 13:24:14', NULL, 1),
(53, 38053, 21060, NULL, '2025-10-27 13:24:14', NULL, 1),
(54, 38054, 21009, NULL, '2025-10-27 13:24:14', NULL, 1),
(55, 38055, 21009, NULL, '2025-10-27 13:24:14', NULL, 1),
(56, 38056, 21043, NULL, '2025-10-27 13:24:14', NULL, 1),
(57, 38057, 21043, NULL, '2025-10-27 13:24:14', NULL, 1),
(58, 38058, 21082, NULL, '2025-10-27 13:24:14', NULL, 1),
(59, 38059, 21082, NULL, '2025-10-27 13:24:14', NULL, 1),
(60, 38060, 21039, NULL, '2025-10-27 13:24:14', NULL, 1),
(61, 38061, 21063, NULL, '2025-10-27 13:24:14', NULL, 1),
(62, 38062, 21063, NULL, '2025-10-27 13:24:14', NULL, 1),
(63, 38063, 21050, NULL, '2025-10-27 13:24:14', NULL, 1),
(64, 38064, 21050, NULL, '2025-10-27 13:24:14', NULL, 1),
(65, 38065, 21038, NULL, '2025-10-27 13:24:14', NULL, 1),
(66, 38066, 21038, NULL, '2025-10-27 13:24:14', NULL, 1),
(67, 38067, 21056, NULL, '2025-10-27 13:24:14', NULL, 1),
(68, 38068, 21056, NULL, '2025-10-27 13:24:14', NULL, 1),
(69, 38069, 21001, NULL, '2025-10-27 13:24:14', NULL, 1),
(70, 38070, 21001, NULL, '2025-10-27 13:24:14', NULL, 1),
(71, 38071, 21069, NULL, '2025-10-27 13:24:14', NULL, 1),
(72, 38072, 21069, NULL, '2025-10-27 13:24:14', NULL, 1),
(73, 38073, 21083, NULL, '2025-10-27 13:24:14', NULL, 1),
(74, 38074, 21084, NULL, '2025-10-27 13:24:14', NULL, 1),
(75, 38075, 21084, NULL, '2025-10-27 13:24:14', NULL, 1),
(76, 38076, 21047, NULL, '2025-10-27 13:24:14', NULL, 1),
(77, 38077, 21081, NULL, '2025-10-27 13:24:14', NULL, 1),
(78, 38078, 21010, NULL, '2025-10-27 13:24:14', NULL, 1),
(79, 38079, 21010, NULL, '2025-10-27 13:24:14', NULL, 1),
(80, 38080, 21066, NULL, '2025-10-27 13:24:14', NULL, 1),
(81, 38081, 21002, NULL, '2025-10-27 13:24:14', NULL, 1),
(82, 38082, 21002, NULL, '2025-10-27 13:24:14', NULL, 1),
(83, 38083, 21071, NULL, '2025-10-27 13:24:14', NULL, 1),
(84, 38084, 21071, NULL, '2025-10-27 13:24:14', NULL, 1),
(85, 38085, 21068, NULL, '2025-10-27 13:24:14', NULL, 1),
(86, 38086, 21068, NULL, '2025-10-27 13:24:14', NULL, 1),
(87, 38087, 21077, NULL, '2025-10-27 13:24:14', NULL, 1),
(88, 38088, 21073, NULL, '2025-10-27 13:24:14', NULL, 1),
(89, 38089, 21073, NULL, '2025-10-27 13:24:14', NULL, 1),
(90, 38090, 21059, NULL, '2025-10-27 13:24:14', NULL, 1),
(91, 38091, 21064, NULL, '2025-10-27 13:24:14', NULL, 1),
(92, 38092, 21064, NULL, '2025-10-27 13:24:14', NULL, 1),
(93, 38093, 21053, NULL, '2025-10-27 13:24:14', NULL, 1),
(94, 38094, 21053, NULL, '2025-10-27 13:24:14', NULL, 1),
(95, 38095, 21058, NULL, '2025-10-27 13:24:14', NULL, 1),
(128, 38001, 20007, NULL, '2025-10-27 13:24:10', NULL, 1),
(129, 38002, 20008, NULL, '2025-10-27 13:24:10', NULL, 1),
(130, 38003, 20007, NULL, '2025-10-27 13:24:14', NULL, 1),
(131, 38004, 20008, NULL, '2025-10-27 13:24:14', NULL, 1),
(132, 38005, 20009, NULL, '2025-10-27 13:24:14', NULL, 1),
(133, 38006, 20007, NULL, '2025-10-27 13:24:14', NULL, 1),
(134, 38007, 20009, NULL, '2025-10-27 13:24:14', NULL, 1),
(135, 38008, 20007, NULL, '2025-10-27 13:24:14', NULL, 1),
(136, 38009, 20008, NULL, '2025-10-27 13:24:14', NULL, 1),
(137, 38010, 20009, NULL, '2025-10-27 13:24:14', NULL, 1),
(138, 38011, 20007, NULL, '2025-10-27 13:24:14', NULL, 1),
(139, 38012, 20008, NULL, '2025-10-27 13:24:14', NULL, 1),
(140, 38013, 20011, NULL, '2025-10-27 13:24:14', NULL, 1),
(141, 38014, 21015, NULL, '2025-10-27 13:24:14', NULL, 1),
(142, 38015, 21019, NULL, '2025-10-27 13:24:14', NULL, 1),
(143, 38016, 20008, NULL, '2025-10-27 13:24:14', NULL, 1),
(144, 38017, 20011, NULL, '2025-10-27 13:24:14', NULL, 1),
(145, 38018, 21018, NULL, '2025-10-27 13:24:14', NULL, 1),
(146, 38019, 20008, NULL, '2025-10-27 13:24:14', NULL, 1),
(147, 38020, 21019, NULL, '2025-10-27 13:24:14', NULL, 1),
(148, 38021, 20006, NULL, '2025-10-27 13:24:14', NULL, 1),
(149, 38022, 21017, NULL, '2025-10-27 13:24:14', NULL, 1),
(150, 38023, 21019, NULL, '2025-10-27 13:24:14', NULL, 1),
(151, 38024, 21020, NULL, '2025-10-27 13:24:14', NULL, 1),
(152, 38025, 20008, NULL, '2025-10-27 13:24:14', NULL, 1),
(153, 38026, 20006, NULL, '2025-10-27 13:24:14', NULL, 1),
(154, 38027, 21024, NULL, '2025-10-27 13:24:14', NULL, 1),
(155, 38028, 21017, NULL, '2025-10-27 13:24:14', NULL, 1),
(156, 38029, 21024, NULL, '2025-10-27 13:24:14', NULL, 1),
(157, 38030, 21016, NULL, '2025-10-27 13:24:14', NULL, 1),
(158, 38031, 20008, NULL, '2025-10-27 13:24:14', NULL, 1),
(159, 38032, 21019, NULL, '2025-10-27 13:24:14', NULL, 1),
(160, 38033, 20010, NULL, '2025-10-27 13:24:14', NULL, 1),
(161, 38034, 21018, NULL, '2025-10-27 13:24:14', NULL, 1),
(162, 38035, 20010, NULL, '2025-10-27 13:24:14', NULL, 1),
(163, 38036, 21023, NULL, '2025-10-27 13:24:14', NULL, 1),
(164, 38037, 21017, NULL, '2025-10-27 13:24:14', NULL, 1),
(165, 38038, 21016, NULL, '2025-10-27 13:24:14', NULL, 1),
(166, 38039, 20006, NULL, '2025-10-27 13:24:14', NULL, 1),
(167, 38040, 21023, NULL, '2025-10-27 13:24:14', NULL, 1),
(168, 38041, 21016, NULL, '2025-10-27 13:24:14', NULL, 1),
(169, 38042, 21020, NULL, '2025-10-27 13:24:14', NULL, 1),
(170, 38043, 20007, NULL, '2025-10-27 13:24:14', NULL, 1),
(171, 38044, 21015, NULL, '2025-10-27 13:24:14', NULL, 1),
(172, 38045, 20007, NULL, '2025-10-27 13:24:14', NULL, 1),
(173, 38046, 20006, NULL, '2025-10-27 13:24:14', NULL, 1),
(174, 38047, 21017, NULL, '2025-10-27 13:24:14', NULL, 1),
(175, 38048, 21019, NULL, '2025-10-27 13:24:14', NULL, 1),
(176, 38049, 20006, NULL, '2025-10-27 13:24:14', NULL, 1),
(177, 38050, 20004, NULL, '2025-10-27 13:24:14', NULL, 1),
(178, 38051, 21016, NULL, '2025-10-27 13:24:14', NULL, 1),
(179, 38052, 21023, NULL, '2025-10-27 13:24:14', NULL, 1),
(180, 38053, 21018, NULL, '2025-10-27 13:24:14', NULL, 1),
(181, 38054, 21015, NULL, '2025-10-27 13:24:14', NULL, 1),
(182, 38055, 20006, NULL, '2025-10-27 13:24:14', NULL, 1),
(183, 38056, 21023, NULL, '2025-10-27 13:24:14', NULL, 1),
(184, 38057, 20007, NULL, '2025-10-27 13:24:14', NULL, 1),
(185, 38058, 21015, NULL, '2025-10-27 13:24:14', NULL, 1),
(186, 38059, 20006, NULL, '2025-10-27 13:24:14', NULL, 1),
(187, 38060, 20006, NULL, '2025-10-27 13:24:14', NULL, 1),
(188, 38061, 20007, NULL, '2025-10-27 13:24:14', NULL, 1),
(189, 38062, 20006, NULL, '2025-10-27 13:24:14', NULL, 1),
(190, 38063, 20011, NULL, '2025-10-27 13:24:14', NULL, 1),
(191, 38064, 20004, NULL, '2025-10-27 13:24:14', NULL, 1),
(192, 38065, 21020, NULL, '2025-10-27 13:24:14', NULL, 1),
(193, 38066, 21023, NULL, '2025-10-27 13:24:14', NULL, 1),
(194, 38067, 21019, NULL, '2025-10-27 13:24:14', NULL, 1),
(195, 38068, 21020, NULL, '2025-10-27 13:24:14', NULL, 1),
(196, 38069, 21020, NULL, '2025-10-27 13:24:14', NULL, 1),
(197, 38070, 21015, NULL, '2025-10-27 13:24:14', NULL, 1),
(198, 38071, 21018, NULL, '2025-10-27 13:24:14', NULL, 1),
(199, 38072, 21024, NULL, '2025-10-27 13:24:14', NULL, 1),
(200, 38073, 21018, NULL, '2025-10-27 13:24:14', NULL, 1),
(201, 38074, 21016, NULL, '2025-10-27 13:24:14', NULL, 1),
(202, 38075, 21023, NULL, '2025-10-27 13:24:14', NULL, 1),
(203, 38076, 21019, NULL, '2025-10-27 13:24:14', NULL, 1),
(204, 38077, 20004, NULL, '2025-10-27 13:24:14', NULL, 1),
(205, 38078, 21020, NULL, '2025-10-27 13:24:14', NULL, 1),
(206, 38079, 20010, NULL, '2025-10-27 13:24:14', NULL, 1),
(207, 38080, 21024, NULL, '2025-10-27 13:24:14', NULL, 1),
(208, 38081, 20011, NULL, '2025-10-27 13:24:14', NULL, 1),
(209, 38082, 21024, NULL, '2025-10-27 13:24:14', NULL, 1),
(210, 38083, 20009, NULL, '2025-10-27 13:24:14', NULL, 1),
(211, 38084, 20011, NULL, '2025-10-27 13:24:14', NULL, 1),
(212, 38085, 21017, NULL, '2025-10-27 13:24:14', NULL, 1),
(213, 38086, 21020, NULL, '2025-10-27 13:24:14', NULL, 1),
(214, 38087, 20007, NULL, '2025-10-27 13:24:14', NULL, 1),
(215, 38088, 20011, NULL, '2025-10-27 13:24:14', NULL, 1),
(216, 38089, 20010, NULL, '2025-10-27 13:24:14', NULL, 1),
(217, 38090, 20008, NULL, '2025-10-27 13:24:14', NULL, 1),
(218, 38091, 20008, NULL, '2025-10-27 13:24:14', NULL, 1),
(219, 38092, 20006, NULL, '2025-10-27 13:24:14', NULL, 1),
(220, 38093, 21018, NULL, '2025-10-27 13:24:14', NULL, 1),
(221, 38094, 21023, NULL, '2025-10-27 13:24:14', NULL, 1),
(222, 38095, 20011, NULL, '2025-10-27 13:24:14', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `message_read_status`
--

CREATE TABLE `message_read_status` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_threads`
--

CREATE TABLE `message_threads` (
  `id` int(11) NOT NULL,
  `case_id` int(11) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `thread_type` enum('case','support') NOT NULL DEFAULT 'case',
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `message_threads`
--

INSERT INTO `message_threads` (`id`, `case_id`, `subject`, `created_by`, `thread_type`, `assigned_to`, `created_at`, `updated_at`) VALUES
(38001, 24001, 'Initial Questions about MVA Claim', 21001, 'case', NULL, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(38002, 24002, 'Surgical Records and Next Steps', 21002, 'case', NULL, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(38003, 24001, 'Discussion on MVA – Rear-end collision', 21001, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38004, 24002, 'Discussion on Medical negligence – surgical error', 21002, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38005, 24003, 'Discussion on Premises liability – supermarket fall', 21003, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38006, 24004, 'Discussion on Product liability – defective ladder', 21004, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38007, 24005, 'Discussion on General injury – dog bite', 21005, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38008, 24006, 'Discussion on MVA – hit and run', 21006, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38009, 24007, 'Discussion on Medical negligence – misdiagnosis', 21007, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38010, 24008, 'Discussion on Premises liability – unsafe stairs', 21008, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38011, 24009, 'Discussion on Product liability – faulty appliance', 21009, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38012, 24010, 'Discussion on General injury – workplace accident', 21010, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38013, 24011, 'Discussion on General injury – misdiagnosis', 21062, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38014, 24012, 'Discussion on Other – misdiagnosis', 21062, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38015, 24013, 'Discussion on Motor vehicle – dog bite', 21072, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38016, 24015, 'Discussion on Premises liability – rear-end collision', 21079, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38017, 24016, 'Discussion on Other – slip-and-fall', 21079, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38018, 24017, 'Discussion on Motor vehicle – dog bite', 21045, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38019, 24019, 'Discussion on Product liability – slip-and-fall', 21052, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38020, 24020, 'Discussion on Product liability – dog bite', 21052, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38021, 24021, 'Discussion on General injury – surgical error', 21078, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38022, 24022, 'Discussion on Medical negligence – rear-end collision', 21078, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38023, 24024, 'Discussion on Premises liability – dog bite', 21042, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38024, 24025, 'Discussion on General injury – dog bite', 21057, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38025, 24026, 'Discussion on Other – surgical error', 21057, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38026, 24028, 'Discussion on Other – dog bite', 21037, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38027, 24029, 'Discussion on Product liability – misdiagnosis', 21051, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38028, 24030, 'Discussion on Medical negligence – misdiagnosis', 21051, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38029, 24032, 'Discussion on Motor vehicle – rear-end collision', 21075, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38030, 24033, 'Discussion on Other – defective device', 21004, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38031, 24035, 'Discussion on General injury – slip-and-fall', 21008, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38032, 24036, 'Discussion on Medical negligence – dog bite', 21008, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38033, 24037, 'Discussion on Premises liability – dog bite', 21048, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38034, 24038, 'Discussion on Premises liability – rear-end collision', 21048, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38035, 24039, 'Discussion on Medical negligence – defective device', 21049, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38036, 24041, 'Discussion on Premises liability – defective device', 21076, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38037, 24042, 'Discussion on Motor vehicle – slip-and-fall', 21076, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38038, 24043, 'Discussion on Product liability – dog bite', 21055, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38039, 24044, 'Discussion on Motor vehicle – slip-and-fall', 21055, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38040, 24045, 'Discussion on Other – slip-and-fall', 21044, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38041, 24047, 'Discussion on Medical negligence – slip-and-fall', 21085, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38042, 24048, 'Discussion on General injury – defective device', 21085, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38043, 24049, 'Discussion on Other – rear-end collision', 21046, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38044, 24050, 'Discussion on Other – defective device', 21046, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38045, 24051, 'Discussion on Other – surgical error', 21067, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38046, 24052, 'Discussion on Motor vehicle – dog bite', 21067, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38047, 24053, 'Discussion on General injury – rear-end collision', 21005, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38048, 24054, 'Discussion on Other – rear-end collision', 21005, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38049, 24056, 'Discussion on General injury – dog bite', 21006, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38050, 24057, 'Discussion on Medical negligence – misdiagnosis', 21065, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38051, 24058, 'Discussion on Premises liability – dog bite', 21065, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38052, 24059, 'Discussion on Motor vehicle – rear-end collision', 21060, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38053, 24060, 'Discussion on Product liability – surgical error', 21060, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38054, 24061, 'Discussion on Other – surgical error', 21009, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38055, 24062, 'Discussion on Premises liability – misdiagnosis', 21009, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38056, 24063, 'Discussion on Other – rear-end collision', 21043, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38057, 24064, 'Discussion on General injury – misdiagnosis', 21043, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38058, 24065, 'Discussion on Medical negligence – misdiagnosis', 21082, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38059, 24066, 'Discussion on Premises liability – defective device', 21082, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38060, 24067, 'Discussion on General injury – rear-end collision', 21039, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38061, 24069, 'Discussion on Premises liability – misdiagnosis', 21063, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38062, 24070, 'Discussion on Medical negligence – slip-and-fall', 21063, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38063, 24071, 'Discussion on Premises liability – misdiagnosis', 21050, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38064, 24072, 'Discussion on Motor vehicle – surgical error', 21050, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38065, 24073, 'Discussion on Other – misdiagnosis', 21038, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38066, 24074, 'Discussion on General injury – dog bite', 21038, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38067, 24075, 'Discussion on General injury – slip-and-fall', 21056, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38068, 24076, 'Discussion on Premises liability – dog bite', 21056, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38069, 24077, 'Discussion on General injury – slip-and-fall', 21001, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38070, 24078, 'Discussion on Medical negligence – defective device', 21001, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38071, 24079, 'Discussion on General injury – surgical error', 21069, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38072, 24080, 'Discussion on Other – slip-and-fall', 21069, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38073, 24081, 'Discussion on Premises liability – dog bite', 21083, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38074, 24083, 'Discussion on Medical negligence – surgical error', 21084, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38075, 24084, 'Discussion on General injury – dog bite', 21084, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38076, 24085, 'Discussion on General injury – misdiagnosis', 21047, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38077, 24087, 'Discussion on Motor vehicle – misdiagnosis', 21081, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38078, 24089, 'Discussion on Medical negligence – misdiagnosis', 21010, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38079, 24090, 'Discussion on General injury – dog bite', 21010, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38080, 24091, 'Discussion on Product liability – dog bite', 21066, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38081, 24093, 'Discussion on Medical negligence – defective device', 21002, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38082, 24094, 'Discussion on Other – dog bite', 21002, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38083, 24095, 'Discussion on Motor vehicle – slip-and-fall', 21071, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38084, 24096, 'Discussion on Medical negligence – rear-end collision', 21071, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38085, 24097, 'Discussion on General injury – surgical error', 21068, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38086, 24098, 'Discussion on Other – rear-end collision', 21068, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38087, 24100, 'Discussion on Premises liability – defective device', 21077, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38088, 24101, 'Discussion on Premises liability – defective device', 21073, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38089, 24102, 'Discussion on General injury – surgical error', 21073, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38090, 24103, 'Discussion on Premises liability – defective device', 21059, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38091, 24105, 'Discussion on Medical negligence – defective device', 21064, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38092, 24106, 'Discussion on Premises liability – defective device', 21064, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38093, 24107, 'Discussion on Product liability – slip-and-fall', 21053, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38094, 24108, 'Discussion on Product liability – dog bite', 21053, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(38095, 24110, 'Discussion on Product liability – misdiagnosis', 21058, 'case', NULL, '2025-10-27 13:24:14', '2025-10-27 13:24:14');

-- --------------------------------------------------------

--
-- Table structure for table `news_articles`
--

CREATE TABLE `news_articles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `body` mediumtext DEFAULT NULL,
  `author` varchar(190) DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `news_articles`
--

INSERT INTO `news_articles` (`id`, `title`, `slug`, `body`, `author`, `published_at`, `is_published`, `created_at`, `updated_at`) VALUES
(34001, 'Landmark Med Negligence Ruling', 'landmark-med-negligence', '<p>High Court delivers a landmark ruling ...</p>', 'Senior Attorney 1', '2025-09-27 13:24:10', 1, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(34002, 'Product Recall Notice Issued', 'product-recall-notice', '<p>Major recall of defective appliances ...</p>', 'Operations Manager', '2025-10-17 13:24:10', 1, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(34003, 'High Court Ruling on Negligence', 'hc-ruling-negligence', '<p>Landmark ruling impacts claims...</p>', 'Partner One', '2025-09-27 13:24:15', 1, '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(34004, 'Major Product Recall Notice', 'product-recall', '<p>Important safety update...</p>', 'Operations Team', '2025-10-17 13:24:15', 1, '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(34005, 'Practice Update', 'practice-update', '<p>We have expanded our services...</p>', 'Owner One', NULL, 0, '2025-10-27 13:24:15', '2025-10-27 13:24:15');

-- --------------------------------------------------------

--
-- Table structure for table `notification_campaigns`
--

CREATE TABLE `notification_campaigns` (
  `id` int(11) NOT NULL,
  `campaign_name` varchar(255) NOT NULL,
  `template_id` int(11) NOT NULL,
  `target_type` enum('all_users','role_based','user_list','case_based','custom') NOT NULL,
  `target_criteria` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Targeting criteria based on target_type' CHECK (json_valid(`target_criteria`)),
  `status` enum('draft','scheduled','sending','sent','failed','cancelled') NOT NULL DEFAULT 'draft',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `total_recipients` int(11) NOT NULL DEFAULT 0,
  `sent_count` int(11) NOT NULL DEFAULT 0,
  `failed_count` int(11) NOT NULL DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_delivery`
--

CREATE TABLE `notification_delivery` (
  `id` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notification_type` enum('email','sms','push','system') NOT NULL,
  `recipient_address` varchar(255) NOT NULL COMMENT 'Email, phone, or device token',
  `subject` varchar(500) DEFAULT NULL,
  `content` text NOT NULL,
  `status` enum('pending','sent','delivered','failed','bounced','unsubscribed') NOT NULL DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `failed_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `retry_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_preferences`
--

CREATE TABLE `notification_preferences` (
  `user_id` int(11) NOT NULL,
  `email_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `sms_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `push_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `email_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `sms_notifications` tinyint(1) NOT NULL DEFAULT 0,
  `push_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `system_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `marketing_emails` tinyint(1) NOT NULL DEFAULT 0,
  `case_updates` tinyint(1) NOT NULL DEFAULT 1,
  `appointment_reminders` tinyint(1) NOT NULL DEFAULT 1,
  `document_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `message_notifications` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification_preferences`
--

INSERT INTO `notification_preferences` (`user_id`, `email_enabled`, `sms_enabled`, `push_enabled`, `updated_at`, `email_notifications`, `sms_notifications`, `push_notifications`, `system_notifications`, `marketing_emails`, `case_updates`, `appointment_reminders`, `document_notifications`, `message_notifications`) VALUES
(20001, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(20005, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(20006, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(20007, 1, 0, 0, '2025-10-27 13:24:10', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(20008, 0, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(20009, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(20010, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21001, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21003, 1, 0, 1, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21004, 1, 1, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21005, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21007, 0, 0, 1, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21009, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21012, 1, 1, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21013, 0, 0, 1, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21014, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21015, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21016, 1, 1, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21017, 1, 0, 1, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21018, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21021, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21022, 1, 0, 1, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21023, 1, 1, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21027, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21028, 1, 1, 1, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21031, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21034, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21035, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21038, 1, 0, 1, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21041, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21042, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21046, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21047, 1, 0, 1, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21050, 1, 1, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21052, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21053, 1, 0, 1, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21058, 1, 0, 1, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21059, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21062, 0, 0, 1, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21064, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21065, 1, 1, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21067, 1, 0, 1, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21068, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21070, 1, 1, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21074, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21075, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21076, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21077, 1, 0, 1, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21083, 1, 0, 0, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21084, 1, 0, 1, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1),
(21085, 1, 0, 1, '2025-10-27 13:24:15', 1, 0, 1, 1, 0, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `notification_role_targeting`
--

CREATE TABLE `notification_role_targeting` (
  `id` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `target_role` varchar(50) NOT NULL,
  `include_conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Additional conditions for inclusion' CHECK (json_valid(`include_conditions`)),
  `exclude_conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Conditions for exclusion' CHECK (json_valid(`exclude_conditions`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_templates`
--

CREATE TABLE `notification_templates` (
  `id` int(11) NOT NULL,
  `template_name` varchar(255) NOT NULL,
  `template_type` enum('email','sms','push','system','broadcast') NOT NULL,
  `subject` varchar(500) DEFAULT NULL,
  `content` text NOT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Available template variables' CHECK (json_valid(`variables`)),
  `is_html` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` mediumtext DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 'About Our Firm', 'about-our-firm', '<p>MedLaw serves clients nationwide...</p>', 1, '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(2, 'Client Resources', 'client-resources', '<p>Useful resources for clients...</p>', 1, '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(3, 'Privacy Policy', 'privacy-policy', '<p>We respect your privacy...</p>', 1, '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(4, 'Terms of Service', 'terms-of-service', '<p>Terms and conditions...</p>', 1, '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(5, 'Careers', 'careers', '<p>Join our team...</p>', 0, '2025-10-27 13:24:15', '2025-10-27 13:24:15');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payfast_transactions`
--

CREATE TABLE `payfast_transactions` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `pf_payment_id` varchar(100) NOT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `signature_verified` tinyint(1) NOT NULL DEFAULT 0,
  `raw_post` mediumtext DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','bank_transfer','credit_card','eft','other') NOT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `module` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `description`, `module`, `created_at`) VALUES
(1, 'users.view.own', 'View own user profile', 'users', '2025-10-27 13:24:10'),
(2, 'users.edit.own', 'Edit own user profile', 'users', '2025-10-27 13:24:10'),
(3, 'cases.view.own', 'View own cases', 'cases', '2025-10-27 13:24:10'),
(4, 'cases.create', 'Create new cases', 'cases', '2025-10-27 13:24:10'),
(5, 'services.view', 'View available services', 'services', '2025-10-27 13:24:10'),
(6, 'services.request', 'Request services', 'services', '2025-10-27 13:24:10'),
(7, 'documents.upload.own', 'Upload documents to own cases', 'documents', '2025-10-27 13:24:10'),
(8, 'documents.view.own', 'View documents in own cases', 'documents', '2025-10-27 13:24:10'),
(9, 'user:create', 'Create user accounts', 'users', '2025-10-27 13:24:10'),
(10, 'user:update', 'Update user accounts', 'users', '2025-10-27 13:24:10'),
(11, 'user:delete', 'Delete user accounts', 'users', '2025-10-27 13:24:10'),
(12, 'role:assign', 'Assign roles to users', 'users', '2025-10-27 13:24:10'),
(13, 'settings:manage', 'Manage system settings', 'system', '2025-10-27 13:24:10'),
(14, 'backup:run', 'Run database backups', 'system', '2025-10-27 13:24:10'),
(15, 'audit:view', 'View audit logs', 'audit', '2025-10-27 13:24:10'),
(16, 'case:create', 'Create cases', 'cases', '2025-10-27 13:24:10'),
(17, 'case:view', 'View cases', 'cases', '2025-10-27 13:24:10'),
(18, 'case:view_all', 'View all cases', 'cases', '2025-10-27 13:24:10'),
(19, 'case:update', 'Update cases', 'cases', '2025-10-27 13:24:10'),
(20, 'case:close', 'Close cases', 'cases', '2025-10-27 13:24:10'),
(21, 'document:upload', 'Upload documents', 'documents', '2025-10-27 13:24:10'),
(22, 'document:download', 'Download documents', 'documents', '2025-10-27 13:24:10'),
(23, 'document:delete', 'Delete documents', 'documents', '2025-10-27 13:24:10'),
(24, 'document:archive', 'Archive documents', 'documents', '2025-10-27 13:24:10'),
(25, 'message:send', 'Send messages', 'messages', '2025-10-27 13:24:10'),
(26, 'message:view', 'View messages', 'messages', '2025-10-27 13:24:10'),
(27, 'appointment:create', 'Create appointments', 'appointments', '2025-10-27 13:24:10'),
(28, 'appointment:view', 'View appointments', 'appointments', '2025-10-27 13:24:10'),
(29, 'appointment:cancel', 'Cancel appointments', 'appointments', '2025-10-27 13:24:10'),
(30, 'billing:invoice_create', 'Create invoices', 'billing', '2025-10-27 13:24:10'),
(31, 'billing:invoice_approve', 'Approve invoices', 'billing', '2025-10-27 13:24:10'),
(32, 'billing:refund', 'Process refunds', 'billing', '2025-10-27 13:24:10'),
(33, 'integration:manage', 'Manage integrations/API keys', 'system', '2025-10-27 13:24:10'),
(34, 'data:export', 'Export user/data', 'compliance', '2025-10-27 13:24:10'),
(35, 'data:delete', 'Delete user/data (legal hold)', 'compliance', '2025-10-27 13:24:10'),
(36, 'backup:create', 'Create backup schedules', 'backup', '2025-10-27 13:24:12'),
(37, 'backup:view', 'View backup schedules and logs', 'backup', '2025-10-27 13:24:12'),
(38, 'backup:edit', 'Edit backup schedules', 'backup', '2025-10-27 13:24:12'),
(39, 'backup:delete', 'Delete backup schedules', 'backup', '2025-10-27 13:24:12'),
(40, 'backup:execute', 'Execute manual backups', 'backup', '2025-10-27 13:24:12'),
(41, 'backup:restore', 'Request and approve restores', 'backup', '2025-10-27 13:24:12'),
(42, 'retention:manage', 'Manage retention policies', 'backup', '2025-10-27 13:24:12'),
(43, 'health:view', 'View system health status', 'system', '2025-10-27 13:24:12'),
(44, 'health:manage', 'Manage health checks', 'system', '2025-10-27 13:24:12'),
(45, 'health:test', 'Test system endpoints', 'system', '2025-10-27 13:24:12'),
(46, 'uptime:view', 'View server uptime statistics', 'system', '2025-10-27 13:24:12'),
(47, 'logs:view', 'View system logs', 'system', '2025-10-27 13:24:12'),
(48, 'jobs:view', 'View job queue status', 'system', '2025-10-27 13:24:12'),
(49, 'jobs:manage', 'Manage job queue', 'system', '2025-10-27 13:24:12'),
(50, 'compliance:view', 'View compliance requests', 'compliance', '2025-10-27 13:24:12'),
(51, 'compliance:manage', 'Manage compliance requests', 'compliance', '2025-10-27 13:24:12'),
(52, 'compliance:verify', 'Verify compliance requests', 'compliance', '2025-10-27 13:24:12'),
(53, 'compliance:approve', 'Approve compliance requests', 'compliance', '2025-10-27 13:24:12'),
(54, 'compliance:hold', 'Manage data retention holds', 'compliance', '2025-10-27 13:24:12'),
(55, 'compliance:template', 'Manage compliance templates', 'compliance', '2025-10-27 13:24:12'),
(56, 'notification:template', 'Manage notification templates', 'notification', '2025-10-27 13:24:12'),
(57, 'notification:campaign', 'Create and manage campaigns', 'notification', '2025-10-27 13:24:12'),
(58, 'notification:broadcast', 'Send broadcast notifications', 'notification', '2025-10-27 13:24:12'),
(59, 'notification:targeting', 'Configure role targeting', 'notification', '2025-10-27 13:24:12'),
(60, 'notification:delivery', 'View delivery status', 'notification', '2025-10-27 13:24:12'),
(61, 'settings:config', 'Manage system configuration', 'settings', '2025-10-27 13:24:12'),
(62, 'settings:provider', 'Manage provider configurations', 'settings', '2025-10-27 13:24:12'),
(63, 'settings:api', 'Manage API keys', 'settings', '2025-10-27 13:24:12'),
(64, 'settings:test', 'Test connections and endpoints', 'settings', '2025-10-27 13:24:12'),
(65, 'settings:audit', 'View settings audit logs', 'settings', '2025-10-27 13:24:12'),
(66, 'task:create', 'Create new tasks', 'tasks', '2025-10-27 13:24:15'),
(67, 'task:view', 'View tasks', 'tasks', '2025-10-27 13:24:15'),
(68, 'task:view_all', 'View all tasks (not just assigned)', 'tasks', '2025-10-27 13:24:15'),
(69, 'task:update', 'Update task details', 'tasks', '2025-10-27 13:24:15'),
(70, 'task:complete', 'Mark tasks as completed', 'tasks', '2025-10-27 13:24:15'),
(71, 'task:delete', 'Delete tasks', 'tasks', '2025-10-27 13:24:15'),
(72, 'task:assign', 'Assign tasks to users', 'tasks', '2025-10-27 13:24:15'),
(73, 'task:reassign', 'Reassign tasks to different users', 'tasks', '2025-10-27 13:24:15');

-- --------------------------------------------------------

--
-- Table structure for table `provider_configs`
--

CREATE TABLE `provider_configs` (
  `id` int(11) NOT NULL,
  `provider_name` varchar(255) NOT NULL,
  `provider_type` enum('email','sms','payment','storage','api','database','file_system') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `config_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Provider-specific configuration' CHECK (json_valid(`config_data`)),
  `test_endpoint` varchar(500) DEFAULT NULL,
  `last_test` timestamp NULL DEFAULT NULL,
  `last_test_status` enum('success','failed','not_tested') NOT NULL DEFAULT 'not_tested',
  `last_test_message` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `restore_audit`
--

CREATE TABLE `restore_audit` (
  `id` int(11) NOT NULL,
  `backup_log_id` int(11) NOT NULL,
  `restore_type` enum('full','partial','file','database') NOT NULL,
  `restore_path` varchar(500) NOT NULL,
  `restore_reason` text NOT NULL,
  `status` enum('requested','approved','in_progress','completed','failed') NOT NULL DEFAULT 'requested',
  `requested_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `retention_policies`
--

CREATE TABLE `retention_policies` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `policy_type` enum('backup','log','document','case') NOT NULL,
  `retention_days` int(11) NOT NULL,
  `archive_before_delete` tinyint(1) NOT NULL DEFAULT 1,
  `archive_path` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role` enum('client','super_admin','office_admin','partner','attorney','paralegal','intake','case_manager','billing','doc_specialist','it_admin','compliance','receptionist','manager','admin') NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role`, `permission_id`, `created_at`) VALUES
('', 37, '2025-10-27 13:24:12'),
('', 43, '2025-10-27 13:24:12'),
('', 46, '2025-10-27 13:24:12'),
('', 50, '2025-10-27 13:24:12'),
('', 52, '2025-10-27 13:24:12'),
('', 60, '2025-10-27 13:24:12'),
('', 64, '2025-10-27 13:24:12'),
('', 67, '2025-10-27 13:24:15'),
('', 69, '2025-10-27 13:24:15'),
('', 70, '2025-10-27 13:24:15'),
('client', 1, '2025-10-27 13:24:10'),
('client', 2, '2025-10-27 13:24:10'),
('client', 3, '2025-10-27 13:24:10'),
('client', 4, '2025-10-27 13:24:10'),
('client', 5, '2025-10-27 13:24:10'),
('client', 6, '2025-10-27 13:24:10'),
('client', 7, '2025-10-27 13:24:10'),
('client', 8, '2025-10-27 13:24:10'),
('super_admin', 1, '2025-10-27 13:24:10'),
('super_admin', 2, '2025-10-27 13:24:10'),
('super_admin', 3, '2025-10-27 13:24:10'),
('super_admin', 4, '2025-10-27 13:24:10'),
('super_admin', 5, '2025-10-27 13:24:10'),
('super_admin', 6, '2025-10-27 13:24:10'),
('super_admin', 7, '2025-10-27 13:24:10'),
('super_admin', 8, '2025-10-27 13:24:10'),
('super_admin', 9, '2025-10-27 13:24:10'),
('super_admin', 10, '2025-10-27 13:24:10'),
('super_admin', 11, '2025-10-27 13:24:10'),
('super_admin', 12, '2025-10-27 13:24:10'),
('super_admin', 13, '2025-10-27 13:24:10'),
('super_admin', 14, '2025-10-27 13:24:10'),
('super_admin', 15, '2025-10-27 13:24:10'),
('super_admin', 16, '2025-10-27 13:24:10'),
('super_admin', 17, '2025-10-27 13:24:10'),
('super_admin', 18, '2025-10-27 13:24:10'),
('super_admin', 19, '2025-10-27 13:24:10'),
('super_admin', 20, '2025-10-27 13:24:10'),
('super_admin', 21, '2025-10-27 13:24:10'),
('super_admin', 22, '2025-10-27 13:24:10'),
('super_admin', 23, '2025-10-27 13:24:10'),
('super_admin', 24, '2025-10-27 13:24:10'),
('super_admin', 25, '2025-10-27 13:24:10'),
('super_admin', 26, '2025-10-27 13:24:10'),
('super_admin', 27, '2025-10-27 13:24:10'),
('super_admin', 28, '2025-10-27 13:24:10'),
('super_admin', 29, '2025-10-27 13:24:10'),
('super_admin', 30, '2025-10-27 13:24:10'),
('super_admin', 31, '2025-10-27 13:24:10'),
('super_admin', 32, '2025-10-27 13:24:10'),
('super_admin', 33, '2025-10-27 13:24:10'),
('super_admin', 34, '2025-10-27 13:24:10'),
('super_admin', 35, '2025-10-27 13:24:10'),
('super_admin', 66, '2025-10-27 13:24:15'),
('super_admin', 67, '2025-10-27 13:24:15'),
('super_admin', 68, '2025-10-27 13:24:15'),
('super_admin', 69, '2025-10-27 13:24:15'),
('super_admin', 70, '2025-10-27 13:24:15'),
('super_admin', 71, '2025-10-27 13:24:15'),
('super_admin', 72, '2025-10-27 13:24:15'),
('super_admin', 73, '2025-10-27 13:24:15'),
('office_admin', 9, '2025-10-27 13:24:10'),
('office_admin', 10, '2025-10-27 13:24:10'),
('office_admin', 17, '2025-10-27 13:24:10'),
('office_admin', 18, '2025-10-27 13:24:10'),
('office_admin', 27, '2025-10-27 13:24:10'),
('office_admin', 28, '2025-10-27 13:24:10'),
('office_admin', 29, '2025-10-27 13:24:10'),
('office_admin', 66, '2025-10-27 13:24:15'),
('office_admin', 67, '2025-10-27 13:24:15'),
('office_admin', 68, '2025-10-27 13:24:15'),
('office_admin', 69, '2025-10-27 13:24:15'),
('office_admin', 70, '2025-10-27 13:24:15'),
('office_admin', 71, '2025-10-27 13:24:15'),
('office_admin', 72, '2025-10-27 13:24:15'),
('office_admin', 73, '2025-10-27 13:24:15'),
('partner', 17, '2025-10-27 13:24:10'),
('partner', 18, '2025-10-27 13:24:10'),
('partner', 19, '2025-10-27 13:24:10'),
('partner', 20, '2025-10-27 13:24:10'),
('partner', 21, '2025-10-27 13:24:10'),
('partner', 22, '2025-10-27 13:24:10'),
('partner', 24, '2025-10-27 13:24:10'),
('partner', 25, '2025-10-27 13:24:10'),
('partner', 26, '2025-10-27 13:24:10'),
('partner', 27, '2025-10-27 13:24:10'),
('partner', 28, '2025-10-27 13:24:10'),
('partner', 31, '2025-10-27 13:24:10'),
('partner', 66, '2025-10-27 13:24:15'),
('partner', 67, '2025-10-27 13:24:15'),
('partner', 68, '2025-10-27 13:24:15'),
('partner', 69, '2025-10-27 13:24:15'),
('partner', 70, '2025-10-27 13:24:15'),
('partner', 72, '2025-10-27 13:24:15'),
('partner', 73, '2025-10-27 13:24:15'),
('attorney', 17, '2025-10-27 13:24:10'),
('attorney', 19, '2025-10-27 13:24:10'),
('attorney', 21, '2025-10-27 13:24:10'),
('attorney', 22, '2025-10-27 13:24:10'),
('attorney', 25, '2025-10-27 13:24:10'),
('attorney', 26, '2025-10-27 13:24:10'),
('attorney', 27, '2025-10-27 13:24:10'),
('attorney', 28, '2025-10-27 13:24:10'),
('attorney', 66, '2025-10-27 13:24:15'),
('attorney', 67, '2025-10-27 13:24:15'),
('attorney', 69, '2025-10-27 13:24:15'),
('attorney', 70, '2025-10-27 13:24:15'),
('attorney', 72, '2025-10-27 13:24:15'),
('paralegal', 17, '2025-10-27 13:24:10'),
('paralegal', 21, '2025-10-27 13:24:10'),
('paralegal', 22, '2025-10-27 13:24:10'),
('paralegal', 24, '2025-10-27 13:24:10'),
('paralegal', 25, '2025-10-27 13:24:10'),
('paralegal', 26, '2025-10-27 13:24:10'),
('paralegal', 27, '2025-10-27 13:24:10'),
('paralegal', 28, '2025-10-27 13:24:10'),
('paralegal', 67, '2025-10-27 13:24:15'),
('paralegal', 69, '2025-10-27 13:24:15'),
('paralegal', 70, '2025-10-27 13:24:15'),
('intake', 9, '2025-10-27 13:24:10'),
('intake', 16, '2025-10-27 13:24:10'),
('intake', 17, '2025-10-27 13:24:10'),
('intake', 25, '2025-10-27 13:24:10'),
('intake', 26, '2025-10-27 13:24:10'),
('intake', 27, '2025-10-27 13:24:10'),
('intake', 28, '2025-10-27 13:24:10'),
('intake', 66, '2025-10-27 13:24:15'),
('intake', 67, '2025-10-27 13:24:15'),
('intake', 69, '2025-10-27 13:24:15'),
('intake', 70, '2025-10-27 13:24:15'),
('case_manager', 17, '2025-10-27 13:24:10'),
('case_manager', 18, '2025-10-27 13:24:10'),
('case_manager', 19, '2025-10-27 13:24:10'),
('case_manager', 22, '2025-10-27 13:24:10'),
('case_manager', 25, '2025-10-27 13:24:10'),
('case_manager', 26, '2025-10-27 13:24:10'),
('case_manager', 27, '2025-10-27 13:24:10'),
('case_manager', 28, '2025-10-27 13:24:10'),
('case_manager', 29, '2025-10-27 13:24:10'),
('case_manager', 66, '2025-10-27 13:24:15'),
('case_manager', 67, '2025-10-27 13:24:15'),
('case_manager', 68, '2025-10-27 13:24:15'),
('case_manager', 69, '2025-10-27 13:24:15'),
('case_manager', 70, '2025-10-27 13:24:15'),
('case_manager', 72, '2025-10-27 13:24:15'),
('case_manager', 73, '2025-10-27 13:24:15'),
('billing', 15, '2025-10-27 13:24:10'),
('billing', 17, '2025-10-27 13:24:10'),
('billing', 30, '2025-10-27 13:24:10'),
('billing', 31, '2025-10-27 13:24:10'),
('billing', 32, '2025-10-27 13:24:10'),
('billing', 67, '2025-10-27 13:24:15'),
('billing', 69, '2025-10-27 13:24:15'),
('billing', 70, '2025-10-27 13:24:15'),
('doc_specialist', 15, '2025-10-27 13:24:10'),
('doc_specialist', 17, '2025-10-27 13:24:10'),
('doc_specialist', 21, '2025-10-27 13:24:10'),
('doc_specialist', 22, '2025-10-27 13:24:10'),
('doc_specialist', 23, '2025-10-27 13:24:10'),
('doc_specialist', 24, '2025-10-27 13:24:10'),
('it_admin', 13, '2025-10-27 13:24:10'),
('it_admin', 14, '2025-10-27 13:24:10'),
('it_admin', 15, '2025-10-27 13:24:10'),
('it_admin', 25, '2025-10-27 13:24:15'),
('it_admin', 26, '2025-10-27 13:24:15'),
('it_admin', 33, '2025-10-27 13:24:10'),
('compliance', 15, '2025-10-27 13:24:10'),
('compliance', 25, '2025-10-27 13:24:15'),
('compliance', 26, '2025-10-27 13:24:15'),
('compliance', 34, '2025-10-27 13:24:10'),
('compliance', 35, '2025-10-27 13:24:10'),
('compliance', 67, '2025-10-27 13:24:15'),
('compliance', 69, '2025-10-27 13:24:15'),
('compliance', 70, '2025-10-27 13:24:15'),
('receptionist', 25, '2025-10-27 13:24:15'),
('receptionist', 26, '2025-10-27 13:24:10'),
('receptionist', 27, '2025-10-27 13:24:10'),
('receptionist', 28, '2025-10-27 13:24:10'),
('manager', 25, '2025-10-27 13:24:15'),
('manager', 26, '2025-10-27 13:24:15'),
('manager', 37, '2025-10-27 13:24:12'),
('manager', 40, '2025-10-27 13:24:12'),
('manager', 43, '2025-10-27 13:24:12'),
('manager', 46, '2025-10-27 13:24:12'),
('manager', 47, '2025-10-27 13:24:12'),
('manager', 48, '2025-10-27 13:24:12'),
('manager', 50, '2025-10-27 13:24:12'),
('manager', 52, '2025-10-27 13:24:12'),
('manager', 53, '2025-10-27 13:24:12'),
('manager', 56, '2025-10-27 13:24:12'),
('manager', 57, '2025-10-27 13:24:12'),
('manager', 60, '2025-10-27 13:24:12'),
('manager', 61, '2025-10-27 13:24:12'),
('manager', 64, '2025-10-27 13:24:12'),
('manager', 66, '2025-10-27 13:24:15'),
('manager', 67, '2025-10-27 13:24:15'),
('manager', 68, '2025-10-27 13:24:15'),
('manager', 69, '2025-10-27 13:24:15'),
('manager', 70, '2025-10-27 13:24:15'),
('manager', 72, '2025-10-27 13:24:15'),
('manager', 73, '2025-10-27 13:24:15'),
('admin', 25, '2025-10-27 13:24:15'),
('admin', 26, '2025-10-27 13:24:15'),
('admin', 36, '2025-10-27 13:24:12'),
('admin', 37, '2025-10-27 13:24:12'),
('admin', 38, '2025-10-27 13:24:12'),
('admin', 39, '2025-10-27 13:24:12'),
('admin', 40, '2025-10-27 13:24:12'),
('admin', 41, '2025-10-27 13:24:12'),
('admin', 42, '2025-10-27 13:24:12'),
('admin', 43, '2025-10-27 13:24:12'),
('admin', 44, '2025-10-27 13:24:12'),
('admin', 45, '2025-10-27 13:24:12'),
('admin', 46, '2025-10-27 13:24:12'),
('admin', 47, '2025-10-27 13:24:12'),
('admin', 48, '2025-10-27 13:24:12'),
('admin', 49, '2025-10-27 13:24:12'),
('admin', 50, '2025-10-27 13:24:12'),
('admin', 51, '2025-10-27 13:24:12'),
('admin', 52, '2025-10-27 13:24:12'),
('admin', 53, '2025-10-27 13:24:12'),
('admin', 54, '2025-10-27 13:24:12'),
('admin', 55, '2025-10-27 13:24:12'),
('admin', 56, '2025-10-27 13:24:12'),
('admin', 57, '2025-10-27 13:24:12'),
('admin', 58, '2025-10-27 13:24:12'),
('admin', 59, '2025-10-27 13:24:12'),
('admin', 60, '2025-10-27 13:24:12'),
('admin', 61, '2025-10-27 13:24:12'),
('admin', 62, '2025-10-27 13:24:12'),
('admin', 63, '2025-10-27 13:24:12'),
('admin', 64, '2025-10-27 13:24:12'),
('admin', 65, '2025-10-27 13:24:12'),
('admin', 66, '2025-10-27 13:24:15'),
('admin', 67, '2025-10-27 13:24:15'),
('admin', 68, '2025-10-27 13:24:15'),
('admin', 69, '2025-10-27 13:24:15'),
('admin', 70, '2025-10-27 13:24:15'),
('admin', 71, '2025-10-27 13:24:15'),
('admin', 72, '2025-10-27 13:24:15'),
('admin', 73, '2025-10-27 13:24:15');

-- --------------------------------------------------------

--
-- Table structure for table `security_logs`
--

CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `message` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `security_logs`
--

INSERT INTO `security_logs` (`id`, `event_type`, `message`, `user_id`, `ip_address`, `created_at`) VALUES
(1, 'invalid_credentials', '{\"info\": \"seed\"}', 20007, '127.0.0.1', '2025-10-01 13:24:15'),
(2, 'account_locked', '{\"info\": \"seed\"}', 20010, '127.0.0.1', '2025-09-24 13:24:15'),
(3, 'account_locked', '{\"info\": \"seed\"}', 20005, '127.0.0.1', '2025-10-24 13:24:15'),
(4, 'account_locked', '{\"info\": \"seed\"}', 21043, '127.0.0.1', '2025-09-13 13:24:15'),
(5, 'rate_limit_exceeded', '{\"info\": \"seed\"}', 21080, '127.0.0.1', '2025-09-30 13:24:15'),
(6, 'account_locked', '{\"info\": \"seed\"}', 21076, '127.0.0.1', '2025-10-03 13:24:15'),
(7, 'rate_limit_exceeded', '{\"info\": \"seed\"}', 21033, '127.0.0.1', '2025-09-23 13:24:15'),
(8, 'rate_limit_exceeded', '{\"info\": \"seed\"}', 21002, '127.0.0.1', '2025-10-15 13:24:15'),
(9, 'account_locked', '{\"info\": \"seed\"}', 21081, '127.0.0.1', '2025-09-24 13:24:15'),
(10, 'login_success', '{\"info\": \"seed\"}', 21035, '127.0.0.1', '2025-10-14 13:24:15');

-- --------------------------------------------------------

--
-- Table structure for table `server_uptime`
--

CREATE TABLE `server_uptime` (
  `id` int(11) NOT NULL,
  `server_name` varchar(255) NOT NULL,
  `uptime_seconds` bigint(20) NOT NULL,
  `load_average_1min` decimal(5,2) DEFAULT NULL,
  `load_average_5min` decimal(5,2) DEFAULT NULL,
  `load_average_15min` decimal(5,2) DEFAULT NULL,
  `memory_used_mb` bigint(20) DEFAULT NULL,
  `memory_total_mb` bigint(20) DEFAULT NULL,
  `disk_used_gb` decimal(10,2) DEFAULT NULL,
  `disk_total_gb` decimal(10,2) DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `subcategory` varchar(100) DEFAULT NULL,
  `estimated_duration` varchar(50) DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `category`, `subcategory`, `estimated_duration`, `requirements`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Initial Legal Consultation', 'Comprehensive consultation to assess your case and provide legal guidance', 'consultation', 'initial', '60 minutes', 'Case details and any relevant documentation', 1, 0, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(2, 'Follow-up Consultation', 'Additional consultation to discuss case progress and next steps', 'consultation', 'follow_up', '30 minutes', 'Previous consultation completed', 1, 0, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(3, 'Phone Consultation', 'Quick consultation via phone for urgent matters', 'consultation', 'phone', '15-30 minutes', 'Scheduled appointment', 1, 0, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(4, 'Medical Record Request', 'Obtain medical records from healthcare providers', 'medical', 'records', '5-10 business days', 'Signed authorization form', 1, 0, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(5, 'Independent Medical Examination', 'Arrange independent medical assessment', 'medical', 'examination', '2-3 hours', 'Medical history and current condition details', 1, 0, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(22001, 'Additional Consultation', 'Introductory legal consultation (up to 60 min)', 'Consultation', NULL, NULL, NULL, 1, 0, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(22002, 'Medical Record Review', 'Detailed review of medical records', 'Investigation', NULL, NULL, NULL, 1, 0, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(22003, 'Expert Opinion', 'Obtain specialist expert opinion', 'Investigation', NULL, NULL, NULL, 1, 0, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(22004, 'Court Filing', 'Prepare and file court documents', 'Procedure', NULL, NULL, NULL, 1, 0, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(22005, 'Settlement Negotiation', 'Negotiate settlement with opposing party', 'Negotiation', NULL, NULL, NULL, 1, 0, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(22006, 'Initial Legal Consultation', 'Detailed assessment of your case', 'consultation', 'initial', '60 minutes', 'Case summary, ID copy', 1, 1, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(22007, 'Follow-up Consultation', 'Progress discussion and next steps', 'consultation', 'follow_up', '30 minutes', 'Prior consultation notes', 1, 2, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(22008, 'Phone Consultation', 'Urgent telephonic consult', 'consultation', 'phone', '15-30 minutes', 'Appointment confirmation', 1, 3, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(22009, 'Medical Record Request', 'Obtain medical records', 'medical', 'records', '5-10 business days', 'Signed authorization', 1, 4, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(22010, 'Independent Medical Examination', 'Arrange IME', 'medical', 'examination', '2-3 hours', 'Medical history', 1, 5, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(22011, 'Expert Opinion', 'Specialist review', 'investigation', 'expert', '7-14 days', 'Complete record set', 1, 6, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(22012, 'Court Filing', 'Prepare and file court documents', 'procedure', 'filing', '2-3 days', 'Signed instructions', 1, 7, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(22013, 'Site Inspection', 'On-site investigation', 'investigation', 'site', '2-4 hours', 'Access permissions', 1, 8, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(22014, 'Witness Statement', 'Take witness statements', 'investigation', 'witness', '1-2 hours', 'Witness details', 1, 9, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(22015, 'Settlement Negotiation', 'Negotiate settlements', 'negotiation', 'settlement', 'variable', 'Mandate from client', 1, 10, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(22016, 'Medical Record Review', 'Review medical files', 'medical', 'review', '3-5 days', 'Complete records', 1, 11, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(22017, 'Accident Reconstruction', 'Reconstruction analysis', 'investigation', 'recon', '7-21 days', 'Scene data, photos', 1, 12, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(22018, 'Forensic Accounting', 'Financial damages analysis', 'billing', 'forensic', '7-21 days', 'Financial docs', 1, 13, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(22019, 'Court Appearance', 'Representation at court', 'procedure', 'appearance', 'Half/full day', 'Case file', 1, 14, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(22020, 'Mediation', 'External mediation session', 'procedure', 'mediation', 'Half/full day', 'Mediator scheduled', 1, 15, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(22021, 'Correspondence Drafting', 'Formal legal correspondence', 'procedure', 'letter', '1-3 days', 'Facts summary', 1, 16, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(22022, 'Records Summarization', 'Summarise large records', 'medical', 'summary', '3-7 days', 'Record set', 1, 17, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(22023, 'Expert Joint Minute', 'Draft joint minute', 'procedure', 'minute', '3-7 days', 'Expert availability', 1, 18, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(22024, 'Claims Submission', 'Submit claim forms', 'procedure', 'submission', '3-5 days', 'Client signatures', 1, 19, '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(22025, 'Opinion on Merits', 'Legal merits opinion', 'consultation', 'opinion', '5-10 days', 'Case documents', 1, 20, '2025-10-27 13:24:14', '2025-10-27 13:24:14');

-- --------------------------------------------------------

--
-- Table structure for table `service_gallery`
--

CREATE TABLE `service_gallery` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `image_name` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

CREATE TABLE `service_requests` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `status` enum('cart','pending','approved','rejected','completed') NOT NULL DEFAULT 'cart',
  `quantity` int(11) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `urgency` enum('standard','urgent') NOT NULL DEFAULT 'standard',
  `consult_date` date DEFAULT NULL,
  `consult_time` time DEFAULT NULL,
  `requested_at` timestamp NULL DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_requests`
--

INSERT INTO `service_requests` (`id`, `case_id`, `service_id`, `status`, `quantity`, `notes`, `urgency`, `consult_date`, `consult_time`, `requested_at`, `processed_at`, `processed_by`, `admin_notes`, `created_at`, `updated_at`) VALUES
(30001, 24001, 22001, 'pending', 1, NULL, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(30002, 24002, 22002, 'pending', 1, NULL, 'urgent', NULL, NULL, NULL, NULL, NULL, 'Expedite', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(30003, 24004, 22003, 'approved', 1, NULL, 'standard', NULL, NULL, NULL, NULL, 20004, 'Approved by manager', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(30004, 24006, 22004, 'rejected', 1, NULL, 'standard', NULL, NULL, NULL, NULL, 20002, 'Insufficient docs', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(30005, 24007, 22002, 'pending', 1, NULL, 'urgent', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(30006, 24003, 22023, 'rejected', 3, 'Auto-generated request', 'standard', NULL, NULL, '2025-10-22 13:24:14', '2025-09-07 13:24:14', 21023, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30007, 24007, 22016, 'rejected', 1, 'Auto-generated request', 'standard', NULL, NULL, '2025-08-06 13:24:14', '2025-09-13 13:24:14', NULL, 'Approved as requested', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30008, 24013, 22015, 'approved', 1, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-10 13:24:14', '2025-09-03 13:24:14', 21014, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30009, 24015, 22006, 'cart', 3, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-06 13:24:14', NULL, 1, 'Approved as requested', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30010, 24017, 22009, 'pending', 1, 'Auto-generated request', 'standard', NULL, NULL, '2025-10-09 13:24:14', '2025-09-19 13:24:14', 20005, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30011, 24030, 22019, 'completed', 3, 'Auto-generated request', 'urgent', NULL, NULL, '2025-08-15 13:24:14', NULL, NULL, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30012, 24034, 22017, 'pending', 1, 'Auto-generated request', 'urgent', NULL, NULL, '2025-09-08 13:24:14', '2025-10-26 13:24:14', 21013, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30013, 24042, 22003, 'completed', 3, 'Auto-generated request', 'standard', NULL, NULL, '2025-08-11 13:24:14', NULL, NULL, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30014, 24068, 22023, 'approved', 3, 'Auto-generated request', 'standard', NULL, NULL, '2025-10-06 13:24:14', '2025-10-26 13:24:14', 21036, 'Approved as requested', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30015, 24069, 22006, 'completed', 1, 'Auto-generated request', 'urgent', NULL, NULL, '2025-10-10 13:24:14', '2025-10-13 13:24:14', 21025, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30016, 24074, 22006, 'cart', 3, 'Auto-generated request', 'standard', NULL, NULL, '2025-10-26 13:24:14', NULL, 21024, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30017, 24078, 22022, 'rejected', 1, 'Auto-generated request', 'standard', NULL, NULL, '2025-10-27 13:24:14', NULL, NULL, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30018, 24080, 22007, 'cart', 1, 'Auto-generated request', 'standard', NULL, NULL, '2025-10-14 13:24:14', '2025-10-14 13:24:14', 21025, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30019, 24085, 22009, 'cart', 2, 'Auto-generated request', 'urgent', NULL, NULL, '2025-08-12 13:24:14', '2025-10-07 13:24:14', 21035, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30020, 24087, 22012, 'rejected', 1, 'Auto-generated request', 'standard', NULL, NULL, '2025-10-25 13:24:14', '2025-10-01 13:24:14', NULL, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30021, 24088, 22023, 'completed', 2, 'Auto-generated request', 'standard', NULL, NULL, '2025-08-27 13:24:14', '2025-10-22 13:24:14', NULL, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30022, 24094, 22023, 'rejected', 1, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-24 13:24:14', '2025-09-12 13:24:14', 20004, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30023, 24101, 22002, 'completed', 2, 'Auto-generated request', 'standard', NULL, NULL, '2025-10-09 13:24:14', '2025-09-10 13:24:14', 21025, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30024, 24102, 22004, 'pending', 3, 'Auto-generated request', 'urgent', NULL, NULL, '2025-09-23 13:24:14', '2025-09-25 13:24:14', 20005, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30025, 24106, 22001, 'completed', 2, 'Auto-generated request', 'urgent', NULL, NULL, '2025-08-02 13:24:14', NULL, 21013, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30026, 24001, 22022, 'completed', 2, 'Auto-generated request', 'urgent', NULL, NULL, '2025-09-11 13:24:14', '2025-09-11 13:24:14', 21023, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30027, 24004, 22020, 'completed', 1, 'Auto-generated request', 'urgent', NULL, NULL, '2025-10-13 13:24:14', '2025-09-25 13:24:14', 21023, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30028, 24005, 22023, 'approved', 1, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-10 13:24:14', '2025-09-10 13:24:14', 21016, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30029, 24008, 22021, 'rejected', 2, 'Auto-generated request', 'urgent', NULL, NULL, '2025-08-20 13:24:14', '2025-10-25 13:24:14', 20006, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30030, 24009, 22019, 'pending', 1, 'Auto-generated request', 'urgent', NULL, NULL, '2025-09-10 13:24:14', '2025-10-15 13:24:14', 21014, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30031, 24011, 22009, 'cart', 3, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-20 13:24:14', '2025-09-23 13:24:14', 20004, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30032, 24020, 4, 'approved', 2, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-27 13:24:14', '2025-09-12 13:24:14', NULL, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30033, 24021, 22011, 'completed', 1, 'Auto-generated request', 'urgent', NULL, NULL, '2025-09-21 13:24:14', '2025-10-25 13:24:14', NULL, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30034, 24024, 1, 'completed', 1, 'Auto-generated request', 'standard', NULL, NULL, '2025-10-21 13:24:14', '2025-10-24 13:24:14', 21026, 'Approved as requested', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30035, 24028, 5, 'approved', 3, 'Auto-generated request', 'standard', NULL, NULL, '2025-10-01 13:24:14', NULL, 20005, 'Approved as requested', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30036, 24043, 2, 'cart', 1, 'Auto-generated request', 'urgent', NULL, NULL, '2025-08-16 13:24:14', '2025-10-21 13:24:14', NULL, 'Approved as requested', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30037, 24044, 5, 'approved', 2, 'Auto-generated request', 'standard', NULL, NULL, '2025-08-18 13:24:14', NULL, 21014, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30038, 24045, 22016, 'cart', 1, 'Auto-generated request', 'standard', NULL, NULL, '2025-08-27 13:24:14', '2025-10-12 13:24:14', 20011, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30039, 24049, 22021, 'completed', 2, 'Auto-generated request', 'standard', NULL, NULL, '2025-08-30 13:24:14', '2025-10-02 13:24:14', NULL, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30040, 24063, 22006, 'completed', 1, 'Auto-generated request', 'standard', NULL, NULL, '2025-10-18 13:24:14', '2025-09-23 13:24:14', 20004, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30041, 24065, 22015, 'rejected', 1, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-02 13:24:14', NULL, NULL, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30042, 24072, 22003, 'pending', 2, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-18 13:24:14', '2025-10-06 13:24:14', NULL, 'Approved as requested', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30043, 24077, 22019, 'cart', 2, 'Auto-generated request', 'urgent', NULL, NULL, '2025-09-18 13:24:14', '2025-09-25 13:24:14', NULL, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30044, 24081, 22011, 'pending', 1, 'Auto-generated request', 'standard', NULL, NULL, '2025-08-03 13:24:14', '2025-10-10 13:24:14', 20004, 'Approved as requested', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30045, 24084, 22018, 'cart', 1, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-18 13:24:14', NULL, 21013, 'Approved as requested', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30046, 24086, 22009, 'cart', 3, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-14 13:24:14', NULL, NULL, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30047, 24089, 22002, 'approved', 2, 'Auto-generated request', 'standard', NULL, NULL, '2025-08-25 13:24:14', '2025-10-04 13:24:14', 21015, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30048, 24090, 22004, 'completed', 3, 'Auto-generated request', 'urgent', NULL, NULL, '2025-09-29 13:24:14', '2025-09-24 13:24:14', 21025, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30049, 24091, 22011, 'pending', 2, 'Auto-generated request', 'urgent', NULL, NULL, '2025-09-02 13:24:14', '2025-09-23 13:24:14', NULL, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30050, 24093, 22022, 'approved', 2, 'Auto-generated request', 'urgent', NULL, NULL, '2025-09-20 13:24:14', '2025-10-07 13:24:14', 21013, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30051, 24099, 22020, 'cart', 1, 'Auto-generated request', 'urgent', NULL, NULL, '2025-09-14 13:24:14', '2025-09-12 13:24:14', NULL, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30052, 24108, 22005, 'pending', 2, 'Auto-generated request', 'urgent', NULL, NULL, '2025-08-06 13:24:14', '2025-10-15 13:24:14', 21024, 'Approved as requested', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30053, 24002, 22021, 'rejected', 1, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-17 13:24:14', '2025-10-02 13:24:14', NULL, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30054, 24006, 22002, 'completed', 2, 'Auto-generated request', 'urgent', NULL, NULL, '2025-08-01 13:24:14', '2025-09-09 13:24:14', 20006, 'Approved as requested', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30055, 24010, 22017, 'completed', 3, 'Auto-generated request', 'urgent', NULL, NULL, '2025-09-22 13:24:14', '2025-10-15 13:24:14', 21014, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30056, 24012, 5, 'rejected', 1, 'Auto-generated request', 'urgent', NULL, NULL, '2025-07-31 13:24:14', '2025-10-13 13:24:14', 20006, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30057, 24014, 22016, 'approved', 2, 'Auto-generated request', 'urgent', NULL, NULL, '2025-09-13 13:24:14', '2025-10-21 13:24:14', 21023, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30058, 24016, 22023, 'completed', 1, 'Auto-generated request', 'urgent', NULL, NULL, '2025-08-15 13:24:14', NULL, 1, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30059, 24018, 22011, 'pending', 2, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-15 13:24:14', '2025-10-10 13:24:14', 21026, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30060, 24019, 3, 'approved', 3, 'Auto-generated request', 'urgent', NULL, NULL, '2025-08-31 13:24:14', '2025-10-09 13:24:14', NULL, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30061, 24022, 22011, 'cart', 3, 'Auto-generated request', 'standard', NULL, NULL, '2025-08-31 13:24:14', NULL, NULL, 'Approved as requested', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30062, 24032, 22025, 'approved', 1, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-29 13:24:14', '2025-09-24 13:24:14', 21013, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30063, 24035, 22022, 'rejected', 3, 'Auto-generated request', 'standard', NULL, NULL, '2025-10-13 13:24:14', '2025-10-07 13:24:14', NULL, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30064, 24037, 22015, 'rejected', 3, 'Auto-generated request', 'urgent', NULL, NULL, '2025-09-25 13:24:14', '2025-09-25 13:24:14', NULL, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30065, 24038, 22004, 'approved', 2, 'Auto-generated request', 'standard', NULL, NULL, '2025-10-17 13:24:14', NULL, 21025, 'Approved as requested', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30066, 24039, 22007, 'cart', 3, 'Auto-generated request', 'urgent', NULL, NULL, '2025-10-03 13:24:14', '2025-09-13 13:24:14', 21023, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30067, 24050, 5, 'approved', 1, 'Auto-generated request', 'urgent', NULL, NULL, '2025-10-13 13:24:14', '2025-09-05 13:24:14', 21035, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30068, 24052, 22019, 'rejected', 2, 'Auto-generated request', 'standard', NULL, NULL, '2025-10-18 13:24:14', '2025-09-16 13:24:14', NULL, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30069, 24054, 22008, 'pending', 3, 'Auto-generated request', 'standard', NULL, NULL, '2025-10-15 13:24:14', '2025-09-14 13:24:14', 20004, 'Approved as requested', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30070, 24059, 22023, 'completed', 1, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-19 13:24:14', '2025-09-25 13:24:14', 21025, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30071, 24061, 22001, 'pending', 3, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-27 13:24:14', '2025-10-17 13:24:14', 20004, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30072, 24062, 22018, 'approved', 1, 'Auto-generated request', 'urgent', NULL, NULL, '2025-10-11 13:24:14', '2025-09-25 13:24:14', 21015, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30073, 24067, 22019, 'pending', 1, 'Auto-generated request', 'urgent', NULL, NULL, '2025-10-25 13:24:14', '2025-10-17 13:24:14', 21013, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30074, 24079, 22010, 'pending', 2, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-22 13:24:14', NULL, 21023, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30075, 24082, 22012, 'completed', 1, 'Auto-generated request', 'standard', NULL, NULL, '2025-08-29 13:24:14', '2025-08-30 13:24:14', 21013, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30076, 24098, 22004, 'approved', 2, 'Auto-generated request', 'standard', NULL, NULL, '2025-08-15 13:24:14', '2025-09-06 13:24:14', 20005, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30077, 24100, 22004, 'pending', 3, 'Auto-generated request', 'standard', NULL, NULL, '2025-10-04 13:24:14', '2025-10-27 13:24:14', 21015, 'Approved as requested', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30078, 24103, 22016, 'rejected', 1, 'Auto-generated request', 'standard', NULL, NULL, '2025-08-10 13:24:14', NULL, 20005, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30079, 24104, 22005, 'approved', 1, 'Auto-generated request', 'standard', NULL, NULL, '2025-08-09 13:24:14', NULL, 21025, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30080, 24105, 22021, 'pending', 3, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-07 13:24:14', '2025-10-13 13:24:14', NULL, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30081, 24107, 22018, 'cart', 2, 'Auto-generated request', 'urgent', NULL, NULL, '2025-10-23 13:24:14', NULL, NULL, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30082, 24109, 22017, 'cart', 3, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-05 13:24:14', NULL, 21023, 'Approved as requested', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30083, 24023, 22003, 'completed', 2, 'Auto-generated request', 'standard', NULL, NULL, '2025-08-22 13:24:14', '2025-10-09 13:24:14', 20005, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30084, 24027, 22006, 'rejected', 2, 'Auto-generated request', 'urgent', NULL, NULL, '2025-10-01 13:24:14', NULL, 20011, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30085, 24031, 22006, 'rejected', 2, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-17 13:24:14', '2025-08-31 13:24:14', NULL, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30086, 24036, 22010, 'completed', 3, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-21 13:24:14', '2025-10-05 13:24:14', 21025, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30087, 24041, 22023, 'approved', 3, 'Auto-generated request', 'standard', NULL, NULL, '2025-08-07 13:24:14', '2025-09-14 13:24:14', 21036, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30088, 24046, 22002, 'completed', 1, 'Auto-generated request', 'urgent', NULL, NULL, '2025-08-01 13:24:14', '2025-09-30 13:24:14', 20005, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30089, 24047, 5, 'rejected', 2, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-21 13:24:14', '2025-09-20 13:24:14', 21015, 'Approved as requested', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30090, 24048, 22018, 'cart', 1, 'Auto-generated request', 'urgent', NULL, NULL, '2025-08-04 13:24:14', NULL, 21023, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30091, 24051, 22013, 'cart', 3, 'Auto-generated request', 'urgent', NULL, NULL, '2025-08-14 13:24:14', '2025-10-16 13:24:14', NULL, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30092, 24053, 22015, 'pending', 1, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-20 13:24:14', '2025-09-22 13:24:14', NULL, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30093, 24055, 22018, 'cart', 2, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-02 13:24:14', '2025-10-23 13:24:14', 21025, 'Approved as requested', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30094, 24056, 22025, 'cart', 2, 'Auto-generated request', 'urgent', NULL, NULL, '2025-09-11 13:24:14', '2025-09-17 13:24:14', 20005, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30095, 24057, 1, 'rejected', 2, 'Auto-generated request', 'urgent', NULL, NULL, '2025-08-15 13:24:14', '2025-09-27 13:24:14', 1, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30096, 24058, 22004, 'rejected', 3, 'Auto-generated request', 'urgent', NULL, NULL, '2025-10-10 13:24:14', '2025-10-04 13:24:14', 20006, 'Approved as requested', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30097, 24066, 22019, 'rejected', 1, 'Auto-generated request', 'urgent', NULL, NULL, '2025-09-30 13:24:14', NULL, 20006, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30098, 24071, 5, 'rejected', 3, 'Auto-generated request', 'standard', NULL, NULL, '2025-10-20 13:24:14', '2025-09-27 13:24:14', 21015, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30099, 24073, 22018, 'approved', 3, 'Auto-generated request', 'urgent', NULL, NULL, '2025-10-21 13:24:14', '2025-10-26 13:24:14', 21035, 'Approved as requested', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30100, 24076, 22014, 'completed', 1, 'Auto-generated request', 'urgent', NULL, NULL, '2025-09-03 13:24:14', NULL, 21014, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30101, 24083, 3, 'pending', 2, 'Auto-generated request', 'standard', NULL, NULL, '2025-09-12 13:24:14', '2025-10-01 13:24:14', 21014, 'Insufficient documents', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30102, 24092, 22016, 'cart', 3, 'Auto-generated request', 'urgent', NULL, NULL, '2025-08-27 13:24:14', NULL, 21023, '', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(30103, 24097, 22015, 'cart', 1, 'Auto-generated request', 'urgent', NULL, NULL, '2025-08-06 13:24:14', '2025-08-31 13:24:14', 20005, 'Please expedite', '2025-10-27 13:24:14', '2025-10-27 13:24:14');

-- --------------------------------------------------------

--
-- Table structure for table `settings_audit`
--

CREATE TABLE `settings_audit` (
  `id` int(11) NOT NULL,
  `setting_type` enum('config','provider','api_key') NOT NULL,
  `setting_id` int(11) NOT NULL,
  `action` enum('created','updated','deleted','activated','deactivated') NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `changed_by` int(11) NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `smtp_logs`
--

CREATE TABLE `smtp_logs` (
  `id` int(11) NOT NULL,
  `to_email` varchar(255) NOT NULL,
  `from_email` varchar(255) NOT NULL,
  `subject` varchar(500) DEFAULT NULL,
  `message_type` enum('notification','alert','report','system') NOT NULL,
  `status` enum('sent','failed','pending','bounced') NOT NULL,
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `status` enum('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_config`
--

CREATE TABLE `system_config` (
  `id` int(11) NOT NULL,
  `config_key` varchar(255) NOT NULL,
  `config_value` text DEFAULT NULL,
  `config_type` enum('string','integer','boolean','json','encrypted') NOT NULL DEFAULT 'string',
  `category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_encrypted` tinyint(1) NOT NULL DEFAULT 0,
  `is_public` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether this setting can be accessed by non-admin users',
  `validation_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`validation_rules`)),
  `default_value` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_health_checks`
--

CREATE TABLE `system_health_checks` (
  `id` int(11) NOT NULL,
  `check_name` varchar(255) NOT NULL,
  `check_type` enum('database','file_system','memory','disk_space','network','service','api') NOT NULL,
  `check_command` text DEFAULT NULL,
  `expected_result` text DEFAULT NULL,
  `warning_threshold` decimal(10,2) DEFAULT NULL,
  `critical_threshold` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `check_interval_minutes` int(11) NOT NULL DEFAULT 5,
  `last_check` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `case_id` int(11) DEFAULT NULL,
  `assigned_to` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `task_type` enum('custom','appointment_reminder','service_followup','admin_task','case_review','document_review','billing_task','compliance_task') NOT NULL DEFAULT 'custom',
  `status` enum('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_by` int(11) NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `completed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `case_id`, `assigned_to`, `title`, `description`, `due_date`, `priority`, `task_type`, `status`, `created_by`, `completed_at`, `completed_by`, `created_at`, `updated_at`) VALUES
(1, 24001, 20007, 'Review medical records for MVA case', 'Client suffered whiplash - need to review all medical documentation', '2025-10-30 13:24:15', 'high', 'case_review', 'pending', 20001, NULL, NULL, '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(2, 24002, 20008, 'Prepare expert witness report', 'Surgical error case - need to prepare expert witness documentation', '2025-11-03 13:24:15', 'urgent', 'admin_task', 'pending', 20001, NULL, NULL, '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(3, 24003, 20009, 'Site inspection for premises liability', 'Supermarket fall case - conduct site inspection', '2025-10-29 13:24:15', 'medium', 'case_review', 'pending', 20001, NULL, NULL, '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(4, 24004, 20007, 'Review product liability documentation', 'Defective ladder case - review all product documentation', '2025-11-01 13:24:15', 'medium', 'document_review', 'pending', 20001, NULL, NULL, '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(5, 24005, 20008, 'Follow up with client on dog bite case', 'General injury case - check on client recovery status', '2025-10-28 13:24:15', 'medium', 'service_followup', 'pending', 20001, NULL, NULL, '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(6, NULL, 20010, 'Update case templates', 'Review and update standard case documentation templates', '2025-11-10 13:24:15', 'low', 'admin_task', 'pending', 20001, NULL, NULL, '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(7, NULL, 20011, 'Prepare monthly case reports', 'Generate monthly case status reports for management', '2025-10-30 13:24:15', 'low', 'admin_task', 'pending', 20001, NULL, NULL, '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(8, 24001, 20007, 'Initial case assessment completed', 'Initial assessment of MVA case completed', '2025-10-25 13:24:15', 'high', 'case_review', 'completed', 20001, '2025-10-26 13:24:15', 20007, '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(9, 24002, 20008, 'Medical expert consultation', 'Consultation with medical expert completed', '2025-10-26 13:24:15', 'urgent', 'admin_task', 'completed', 20001, '2025-10-26 13:24:15', 20008, '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(10, 24006, 20007, 'Draft settlement proposal', 'Hit and run case - draft initial settlement proposal', '2025-10-31 13:24:15', 'high', 'admin_task', 'in_progress', 20001, NULL, NULL, '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(11, 24007, 20008, 'Review hospital records', 'Medical negligence case - review all hospital documentation', '2025-11-02 13:24:15', 'medium', 'document_review', 'in_progress', 20001, NULL, NULL, '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(12, 24001, 21017, 'Review medical records for MVA case', 'Client suffered whiplash - need to review all medical documentation', '2025-11-08 09:02:03', 'high', 'case_review', 'pending', 20001, NULL, NULL, '2025-11-05 07:02:03', '2025-11-05 07:02:03'),
(13, 24004, 21017, 'Prepare settlement proposal', 'Product liability case - prepare initial settlement proposal', '2025-11-10 09:02:03', 'medium', 'admin_task', 'pending', 20001, NULL, NULL, '2025-11-05 07:02:03', '2025-11-05 07:02:03'),
(14, 24009, 21017, 'Conduct client interview', 'Product liability case - conduct detailed client interview', '2025-11-07 09:02:03', 'high', 'case_review', 'pending', 20001, NULL, NULL, '2025-11-05 07:02:03', '2025-11-05 07:02:03'),
(15, 24002, 21018, 'Review surgical records', 'Medical negligence case - review all surgical documentation', '2025-11-09 09:02:03', 'urgent', 'document_review', 'pending', 20001, NULL, NULL, '2025-11-05 07:02:03', '2025-11-05 07:02:03'),
(16, 24005, 21018, 'Site inspection for dog bite case', 'General injury case - conduct site inspection', '2025-11-06 09:02:03', 'medium', 'case_review', 'pending', 20001, NULL, NULL, '2025-11-05 07:02:03', '2025-11-05 07:02:03'),
(17, 24010, 21018, 'Prepare expert witness list', 'General injury case - prepare list of expert witnesses', '2025-11-12 09:02:03', 'medium', 'admin_task', 'pending', 20001, NULL, NULL, '2025-11-05 07:02:03', '2025-11-05 07:02:03');

-- --------------------------------------------------------

--
-- Table structure for table `trust_accounts`
--

CREATE TABLE `trust_accounts` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','closed','frozen') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `name` varchar(190) NOT NULL DEFAULT 'Primary Trust Account'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(190) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'South Africa',
  `date_of_birth` date DEFAULT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `medical_aid` varchar(100) DEFAULT NULL,
  `medical_aid_number` varchar(100) DEFAULT NULL,
  `emergency_contact_name` varchar(190) DEFAULT NULL,
  `emergency_contact_phone` varchar(50) DEFAULT NULL,
  `profile_completed` tinyint(1) NOT NULL DEFAULT 0,
  `avatar` varchar(255) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `login_count` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verification_token` varchar(64) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `role` enum('client','super_admin','office_admin','partner','attorney','paralegal','intake','case_manager','billing','doc_specialist','it_admin','compliance','receptionist','manager','admin') NOT NULL DEFAULT 'client',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `name`, `phone`, `address`, `city`, `postal_code`, `country`, `date_of_birth`, `id_number`, `medical_aid`, `medical_aid_number`, `emergency_contact_name`, `emergency_contact_phone`, `profile_completed`, `avatar`, `last_login`, `login_count`, `is_active`, `email_verified`, `email_verification_token`, `two_factor_enabled`, `two_factor_secret`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin@medlaw.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', '+27 12 345 6789', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'admin', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(20001, 'owner.admin@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Firm Owner (Admin)', '+27 11 100 2001', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'super_admin', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(20002, 'compliance.admin@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Head of Compliance (Admin)', '+27 11 100 2002', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'compliance', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(20003, 'it.admin@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'IT Systems (Admin)', '+27 11 100 2003', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'it_admin', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(20004, 'litigation.manager@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Litigation Manager', '+27 21 200 2004', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'partner', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(20005, 'operations.manager@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Operations Manager', '+27 31 300 2005', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'office_admin', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(20006, 'clientservices.manager@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client Services Manager', '+27 12 400 2006', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'case_manager', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(20007, 'attorney.senior1@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Senior Attorney 1', '+27 11 110 2007', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'attorney', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(20008, 'attorney.senior2@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Senior Attorney 2', '+27 21 210 2008', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'attorney', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(20009, 'attorney.associate@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Associate Attorney', '+27 31 310 2009', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'attorney', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(20010, 'paralegal.staff@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Paralegal', '+27 12 400 2010', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'paralegal', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(20011, 'case.coordinator@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Case Coordinator', '+27 41 500 2011', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'case_manager', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(20012, 'compliance.staff@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Compliance Officer (Staff)', '+27 51 600 2112', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'compliance', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(21001, 'client.ayanda@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Ayanda M.', '+27 82 700 1001', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'client', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(21002, 'client.liam@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Liam K.', '+27 82 700 1002', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'client', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(21003, 'client.thandi@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Thandi N.', '+27 82 700 1003', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'client', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(21004, 'client.matt@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Matthew R.', '+27 82 700 1004', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'client', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(21005, 'client.sibongile@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Sibongile P.', '+27 82 700 1005', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'client', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(21006, 'client.jacob@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Jacob D.', '+27 82 700 1006', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'client', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(21007, 'client.karen@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Karen B.', '+27 82 700 1007', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'client', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(21008, 'client.nathi@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Nathi X.', '+27 82 700 1008', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'client', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(21009, 'client.sarah@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Sarah T.', '+27 82 700 1009', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'client', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(21010, 'client.mandla@gmail.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Mandla G.', '+27 82 700 1010', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, 1, 0, NULL, 0, NULL, 'client', '2025-10-27 13:24:10', '2025-10-27 13:24:10'),
(21011, 'owner1@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Owner One', '+27 11 100 1001', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 45, 1, 1, NULL, 0, NULL, 'super_admin', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21012, 'owner2@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Owner Two', '+27 11 100 1002', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 12, 1, 1, NULL, 0, NULL, 'super_admin', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21013, 'office.admin1@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Office Admin One', '+27 11 110 1001', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 23, 1, 1, NULL, 0, NULL, 'office_admin', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21014, 'office.admin2@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Office Admin Two', '+27 11 110 1002', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 18, 1, 1, NULL, 0, NULL, 'office_admin', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21015, 'partner1@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Partner One', '+27 21 120 1001', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 34, 1, 1, NULL, 0, NULL, 'partner', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21016, 'partner2@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Partner Two', '+27 21 120 1002', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 28, 1, 1, NULL, 0, NULL, 'partner', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21017, 'attorney1@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Attorney One', '+27 11 200 3001', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 39, 1, 1, NULL, 0, NULL, 'attorney', '2025-10-27 13:24:14', '2025-11-05 07:02:03'),
(21018, 'attorney2@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Attorney Two', '+27 11 200 3002', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 25, 1, 1, NULL, 0, NULL, 'attorney', '2025-10-27 13:24:14', '2025-11-05 07:02:03'),
(21019, 'paralegal1@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Paralegal One', '+27 31 140 1001', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 22, 1, 1, NULL, 0, NULL, 'paralegal', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21020, 'paralegal2@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Paralegal Two', '+27 31 140 1002', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 19, 1, 1, NULL, 0, NULL, 'paralegal', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21021, 'intake1@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Intake One', '+27 12 150 1001', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 17, 1, 1, NULL, 0, NULL, 'intake', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21022, 'intake2@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Intake Two', '+27 12 150 1002', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 12, 1, 1, NULL, 0, NULL, 'intake', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21023, 'casemgr1@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Case Manager One', '+27 41 160 1001', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 40, 1, 1, NULL, 0, NULL, 'case_manager', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21024, 'casemgr2@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Case Manager Two', '+27 41 160 1002', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 33, 1, 1, NULL, 0, NULL, 'case_manager', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21025, 'billing1@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Billing One', '+27 51 170 1001', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 13, 1, 1, NULL, 0, NULL, 'billing', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21026, 'billing2@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Billing Two', '+27 51 170 1002', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 21, 1, 1, NULL, 0, NULL, 'billing', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21027, 'docspec1@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Doc Specialist One', '+27 61 180 1001', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 15, 1, 1, NULL, 0, NULL, 'doc_specialist', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21028, 'docspec2@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Doc Specialist Two', '+27 61 180 1002', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 12, 1, 1, NULL, 0, NULL, 'doc_specialist', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21029, 'it1@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'IT Admin One', '+27 71 190 1001', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 9, 1, 1, NULL, 0, NULL, 'it_admin', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21030, 'it2@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'IT Admin Two', '+27 71 190 1002', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 7, 1, 1, NULL, 0, NULL, 'it_admin', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21031, 'compliance1@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Compliance One', '+27 81 200 1001', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 16, 1, 1, NULL, 0, NULL, 'compliance', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21032, 'compliance2@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Compliance Two', '+27 81 200 1002', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 14, 1, 1, NULL, 0, NULL, 'compliance', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21033, 'reception1@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Reception One', '+27 82 210 1001', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 20, 1, 1, NULL, 0, NULL, 'receptionist', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21034, 'reception2@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Reception Two', '+27 82 210 1002', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 18, 1, 1, NULL, 0, NULL, 'receptionist', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21035, 'legacy.admin@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Legacy Admin', '+27 11 220 1001', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 5, 1, 1, NULL, 0, NULL, 'admin', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21036, 'legacy.manager@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Legacy Manager', '+27 11 220 1002', NULL, NULL, NULL, 'South Africa', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-10-27 13:24:14', 8, 1, 1, NULL, 0, NULL, 'manager', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21037, 'client.001@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 1', '+27 82 199 2014', '47 Main Street', 'Gqeberha', '4704', 'South Africa', '1982-04-14', '7595818836796', 'None', '5044723236', 'Contact 1', '+27 62 626 2490', 1, NULL, '2025-08-20 13:24:14', 39, 1, 0, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21038, 'client.011@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 11', '+27 89 784 2101', '66 Main Street', 'Cape Town', '4777', 'South Africa', '1996-04-05', '1061321634856', 'Momentum Health', '9427302039', 'Contact 11', '+27 65 388 7528', 1, NULL, '2025-09-28 13:24:14', 11, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21039, 'client.021@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 21', '+27 40 986 8988', '97 Main Street', 'Gqeberha', '3630', 'South Africa', '1997-09-10', '2932238666277', 'Medicover', '9878885991', 'Contact 21', '+27 62 980 2140', 0, NULL, '2025-10-02 13:24:14', 5, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21040, 'client.031@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 31', '+27 66 573 7607', '19 Main Street', 'Cape Town', '9990', 'South Africa', '1997-03-03', '2422623319944', 'Discovery Health', '7933748356', 'Contact 31', '+27 82 785 4289', 1, NULL, '2025-09-11 13:24:14', 13, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21041, 'client.041@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 41', '+27 91 478 4194', '103 Main Street', 'Pretoria', '9745', 'South Africa', '1992-03-23', '8578141285644', 'Discovery Health', '8167527891', 'Contact 41', '+27 29 756 9792', 1, NULL, '2025-10-12 13:24:14', 24, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21042, 'client.002@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 2', '+27 82 692 8710', '62 Main Street', 'Bloemfontein', '9513', 'South Africa', '1973-07-23', '2728185860186', 'Bonitas', '1435137325', 'Contact 2', '+27 84 134 7149', 1, NULL, '2025-10-09 13:24:14', 17, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21043, 'client.012@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 12', '+27 48 748 3597', '57 Main Street', 'Pretoria', '8814', 'South Africa', '1977-05-14', '9653942633097', 'Fedhealth', '3445641006', 'Contact 12', '+27 98 236 7994', 1, NULL, '2025-10-20 13:24:14', 28, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21044, 'client.022@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 22', '+27 25 703 8526', '34 Main Street', 'Cape Town', '2432', 'South Africa', '1973-12-01', '5579086060243', 'Discovery Health', '7450343690', 'Contact 22', '+27 88 322 6392', 1, NULL, '2025-07-31 13:24:14', 19, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21045, 'client.032@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 32', '+27 26 749 1493', '22 Main Street', 'Durban', '6214', 'South Africa', '1975-10-01', '1226471513720', 'None', '7445942109', 'Contact 32', '+27 80 817 6560', 1, NULL, '2025-09-06 13:24:14', 21, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21046, 'client.042@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 42', '+27 57 438 3498', '53 Main Street', 'Durban', '6191', 'South Africa', '1987-06-05', '6499449505935', 'Bonitas', '2271115592', 'Contact 42', '+27 50 847 8204', 1, NULL, '2025-08-11 13:24:14', 26, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21047, 'client.003@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 3', '+27 32 388 8565', '49 Main Street', 'Gqeberha', '7563', 'South Africa', '1983-03-04', '7046957978091', 'Bonitas', '1732856843', 'Contact 3', '+27 81 779 4360', 1, NULL, '2025-09-12 13:24:14', 27, 1, 0, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21048, 'client.013@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 13', '+27 86 246 3091', '135 Main Street', 'Pretoria', '3816', 'South Africa', '1983-05-26', '9034542200187', 'Fedhealth', '4878599414', 'Contact 13', '+27 74 375 4355', 0, NULL, '2025-10-16 13:24:14', 5, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21049, 'client.023@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 23', '+27 17 207 4163', '81 Main Street', 'Bloemfontein', '5782', 'South Africa', '1973-01-27', '5451942638914', 'Discovery Health', '1181190779', 'Contact 23', '+27 32 282 3292', 1, NULL, '2025-07-11 13:24:14', 19, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21050, 'client.033@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 33', '+27 17 750 4117', '113 Main Street', 'Gqeberha', '2660', 'South Africa', '1982-01-21', '4974521897709', 'Medicover', '4750176906', 'Contact 33', '+27 89 296 4702', 1, NULL, '2025-07-15 13:24:14', 38, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21051, 'client.043@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 43', '+27 49 628 6607', '71 Main Street', 'Bloemfontein', '4622', 'South Africa', '1992-10-22', '5305512835556', 'Medicover', '2721176393', 'Contact 43', '+27 59 289 4445', 1, NULL, '2025-10-01 13:24:14', 20, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21052, 'client.004@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 4', '+27 59 205 9221', '43 Main Street', 'Cape Town', '1117', 'South Africa', '2003-09-20', '3867717399138', 'Medicover', NULL, 'Contact 4', '+27 58 938 1100', 1, NULL, '2025-10-25 13:24:14', 6, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21053, 'client.014@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 14', '+27 63 950 9337', '160 Main Street', 'Cape Town', '6768', 'South Africa', '1982-08-16', '1031237836024', 'Momentum Health', '7322928343', 'Contact 14', '+27 65 994 1990', 1, NULL, '2025-07-20 13:24:14', 26, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21054, 'client.024@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 24', '+27 22 276 6146', '55 Main Street', 'Pretoria', '4327', 'South Africa', '1968-01-20', '6321048667953', 'Discovery Health', NULL, 'Contact 24', '+27 20 905 1976', 0, NULL, '2025-09-22 13:24:14', 7, 1, 0, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21055, 'client.034@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 34', '+27 91 715 7135', '72 Main Street', 'Gqeberha', '6503', 'South Africa', '1971-10-09', '4717285790217', 'Bonitas', '2519514390', 'Contact 34', '+27 82 602 4194', 1, NULL, '2025-10-26 13:24:14', 36, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21056, 'client.044@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 44', '+27 40 708 4202', '151 Main Street', 'Gqeberha', '2601', 'South Africa', '1972-06-30', '6627491207446', 'Bonitas', '5492463466', 'Contact 44', '+27 68 825 1454', 0, NULL, '2025-09-28 13:24:14', 31, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21057, 'client.005@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 5', '+27 88 680 6288', '1 Main Street', 'Cape Town', '3544', 'South Africa', '1980-07-15', '3988960530598', 'Fedhealth', '6131915741', 'Contact 5', '+27 59 156 6851', 1, NULL, '2025-10-10 13:24:14', 39, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21058, 'client.015@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 15', '+27 75 327 1402', '94 Main Street', 'Cape Town', '6432', 'South Africa', '1989-08-08', '3389894854640', 'Discovery Health', '8362056609', 'Contact 15', '+27 32 824 3437', 0, NULL, '2025-08-21 13:24:14', 10, 1, 0, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21059, 'client.025@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 25', '+27 13 571 5384', '173 Main Street', 'Bloemfontein', '7424', 'South Africa', '1966-03-22', '8035432324777', 'None', '3480367281', 'Contact 25', '+27 18 680 9472', 0, NULL, '2025-08-15 13:24:14', 9, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21060, 'client.035@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 35', '+27 40 875 3536', '165 Main Street', 'Cape Town', '9185', 'South Africa', '1977-03-06', '8747198330003', 'Discovery Health', '4571131709', 'Contact 35', '+27 52 266 5516', 0, NULL, '2025-08-16 13:24:14', 15, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21061, 'client.045@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 45', '+27 89 697 6665', '31 Main Street', 'Bloemfontein', '9694', 'South Africa', '1999-08-11', '9640794017017', 'Momentum Health', '8919345631', 'Contact 45', '+27 77 234 5246', 0, NULL, '2025-08-25 13:24:14', 19, 0, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21062, 'client.006@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 6', '+27 23 276 5578', '192 Main Street', 'Cape Town', '4497', 'South Africa', '1998-11-10', '8054561018063', 'Medicover', '9374319074', 'Contact 6', '+27 51 580 3472', 0, NULL, '2025-09-24 13:24:14', 6, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21063, 'client.016@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 16', '+27 89 661 4972', '68 Main Street', 'Durban', '7932', 'South Africa', '1974-06-13', '6497252223544', 'Fedhealth', '3360087080', 'Contact 16', '+27 35 705 5381', 1, NULL, '2025-08-10 13:24:14', 17, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21064, 'client.026@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 26', '+27 93 734 7470', '97 Main Street', 'Cape Town', '8084', 'South Africa', '1998-07-06', '6674050399730', 'Bonitas', NULL, 'Contact 26', '+27 81 390 2873', 1, NULL, '2025-07-14 13:24:14', 11, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21065, 'client.036@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 36', '+27 61 325 5610', '163 Main Street', 'Pretoria', '2338', 'South Africa', '1998-08-18', '5781486818363', 'Discovery Health', '6102658859', 'Contact 36', '+27 60 229 1112', 1, NULL, '2025-08-30 13:24:14', 4, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21066, 'client.046@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 46', '+27 17 956 5662', '147 Main Street', 'Johannesburg', '4543', 'South Africa', '1981-08-10', '8816342091016', 'Bonitas', NULL, 'Contact 46', '+27 32 434 2014', 1, NULL, '2025-09-28 13:24:14', 6, 1, 0, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21067, 'client.007@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 7', '+27 78 188 2715', '132 Main Street', 'Gqeberha', '6740', 'South Africa', '2005-10-29', '2689309461684', 'None', NULL, 'Contact 7', '+27 54 106 5887', 1, NULL, '2025-09-18 13:24:14', 5, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21068, 'client.017@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 17', '+27 28 139 6373', '172 Main Street', 'Durban', '8748', 'South Africa', '1971-10-24', '6937147318327', 'Fedhealth', '7026340960', 'Contact 17', '+27 98 951 7804', 0, NULL, '2025-10-05 13:24:14', 27, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21069, 'client.027@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 27', '+27 89 897 7889', '34 Main Street', 'Pretoria', '3147', 'South Africa', '1984-04-02', '1105823505768', 'Medicover', '2006177378', 'Contact 27', '+27 39 392 6636', 1, NULL, '2025-09-28 13:24:14', 28, 1, 0, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21070, 'client.037@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 37', '+27 20 873 9457', '25 Main Street', 'Gqeberha', '6150', 'South Africa', '1986-02-17', '8041245660783', 'Medicover', '3999398715', 'Contact 37', '+27 56 659 5838', 0, NULL, '2025-07-27 13:24:14', 22, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21071, 'client.047@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 47', '+27 26 639 5025', '88 Main Street', 'Bloemfontein', '9309', 'South Africa', '2003-08-12', '6409315112428', 'Fedhealth', '2840181531', 'Contact 47', '+27 71 856 2163', 1, NULL, '2025-09-09 13:24:14', 14, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21072, 'client.008@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 8', '+27 91 402 9556', '149 Main Street', 'Bloemfontein', '2090', 'South Africa', '1965-11-22', '6397631912862', 'Discovery Health', '5161177720', 'Contact 8', '+27 45 637 8158', 1, NULL, '2025-10-18 13:24:14', 4, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21073, 'client.018@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 18', '+27 18 318 9313', '178 Main Street', 'Gqeberha', '7326', 'South Africa', '1986-06-07', '4184172004632', 'Momentum Health', '1950730712', 'Contact 18', '+27 40 445 9085', 1, NULL, '2025-09-16 13:24:14', 3, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21074, 'client.028@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 28', '+27 53 462 5907', '104 Main Street', 'Bloemfontein', '3231', 'South Africa', '1992-01-12', '1332563877415', 'Discovery Health', '1017742812', 'Contact 28', '+27 63 992 2440', 0, NULL, '2025-07-23 13:24:14', 32, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21075, 'client.038@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 38', '+27 61 521 6583', '140 Main Street', 'Pretoria', '1204', 'South Africa', '1996-07-28', '2438749816677', 'Discovery Health', NULL, 'Contact 38', '+27 97 455 1342', 1, NULL, '2025-09-27 13:24:14', 39, 1, 0, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21076, 'client.048@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 48', '+27 40 974 8336', '33 Main Street', 'Durban', '3870', 'South Africa', '1985-07-29', '6487678145512', 'Bonitas', '1409384070', 'Contact 48', '+27 17 357 2583', 1, NULL, '2025-07-13 13:24:14', 19, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21077, 'client.009@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 9', '+27 98 362 5324', '106 Main Street', 'Cape Town', '4646', 'South Africa', '1988-11-18', '9535624928339', 'Medicover', '5797666950', 'Contact 9', '+27 58 222 1351', 0, NULL, '2025-07-22 13:24:14', 8, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21078, 'client.019@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 19', '+27 50 872 9222', '199 Main Street', 'Cape Town', '2296', 'South Africa', '2004-10-04', '8190923434860', 'None', NULL, 'Contact 19', '+27 85 653 5607', 0, NULL, '2025-07-13 13:24:14', 8, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21079, 'client.029@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 29', '+27 78 262 6409', '93 Main Street', 'Pretoria', '2817', 'South Africa', '1988-01-31', '6902603022663', 'None', '5075265517', 'Contact 29', '+27 39 372 5650', 1, NULL, '2025-10-12 13:24:14', 3, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21080, 'client.039@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 39', '+27 40 404 6955', '59 Main Street', 'Durban', '5936', 'South Africa', '1995-01-02', '7960502209105', 'Discovery Health', '7676959339', 'Contact 39', '+27 48 960 5223', 1, NULL, '2025-09-15 13:24:14', 23, 1, 0, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21081, 'client.049@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 49', '+27 12 171 3857', '71 Main Street', 'Gqeberha', '9452', 'South Africa', '1994-03-30', '7245060580079', 'Bonitas', '9719542307', 'Contact 49', '+27 67 410 7946', 0, NULL, '2025-07-25 13:24:14', 20, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21082, 'client.010@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 10', '+27 44 637 8330', '57 Main Street', 'Bloemfontein', '9747', 'South Africa', '1966-10-28', '9407890105068', 'Fedhealth', '7576879616', 'Contact 10', '+27 67 131 3137', 1, NULL, '2025-09-16 13:24:14', 9, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21083, 'client.020@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 20', '+27 12 210 5687', '48 Main Street', 'Pretoria', '4444', 'South Africa', '2004-03-29', '2313594792330', 'Bonitas', '9854512532', 'Contact 20', '+27 93 741 7893', 1, NULL, '2025-09-11 13:24:14', 4, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21084, 'client.030@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 30', '+27 63 883 5827', '15 Main Street', 'Gqeberha', '5671', 'South Africa', '1992-01-17', '2998745170420', 'Discovery Health', '6497378304', 'Contact 30', '+27 46 298 8824', 1, NULL, '2025-07-13 13:24:14', 32, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21085, 'client.040@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 40', '+27 10 419 7877', '152 Main Street', 'Durban', '2627', 'South Africa', '1988-11-04', '6637917825614', 'Fedhealth', '8941248172', 'Contact 40', '+27 62 358 7076', 1, NULL, '2025-07-11 13:24:14', 11, 1, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14'),
(21086, 'client.050@example.com', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Client 50', '+27 67 561 6645', '120 Main Street', 'Johannesburg', '7804', 'South Africa', '1988-03-10', '9820939916950', 'Bonitas', NULL, 'Contact 50', '+27 48 735 3143', 1, NULL, '2025-10-11 13:24:14', 0, 0, 1, NULL, 0, NULL, 'client', '2025-10-27 13:24:14', '2025-10-27 13:24:14');

-- --------------------------------------------------------

--
-- Table structure for table `user_medical_history`
--

CREATE TABLE `user_medical_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `condition_name` varchar(255) NOT NULL,
  `diagnosis_date` date DEFAULT NULL,
  `treating_doctor` varchar(190) DEFAULT NULL,
  `hospital_facility` varchar(190) DEFAULT NULL,
  `current_medication` varchar(255) DEFAULT NULL,
  `severity` enum('mild','moderate','severe','critical') DEFAULT NULL,
  `is_ongoing` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_medical_history`
--

INSERT INTO `user_medical_history` (`id`, `user_id`, `condition_name`, `diagnosis_date`, `treating_doctor`, `hospital_facility`, `current_medication`, `severity`, `is_ongoing`, `notes`, `created_at`, `updated_at`) VALUES
(1, 21001, 'Hypertension', '2025-07-14', 'Dr. Patel', 'Steve Biko Academic', 'Amlodipine', 'moderate', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(2, 21002, 'Anxiety', '2017-11-19', 'Dr. Williams', 'Life Vincent Pallotti', 'Insulin', 'mild', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(3, 21003, 'Anxiety', '2021-05-15', 'Dr. Naidoo', 'Chris Hani Baragwanath', 'Amlodipine', 'severe', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(4, 21004, 'Fracture', '2025-09-09', 'Dr. Patel', 'Netcare Milpark', NULL, 'moderate', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(5, 21005, 'Fracture', '2023-05-28', 'Dr. Patel', 'Steve Biko Academic', 'Insulin', 'moderate', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(6, 21006, 'Diabetes Type 2', '2022-04-27', 'Dr. Naidoo', 'Steve Biko Academic', NULL, 'severe', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(7, 21010, 'Post-op Infection', '2022-10-28', 'Dr. Jacobs', 'Steve Biko Academic', NULL, 'severe', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(8, 21038, 'Diabetes Type 2', '2020-04-17', 'Dr. Smith', 'Chris Hani Baragwanath', 'Amlodipine', 'moderate', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(9, 21039, 'Post-op Infection', '2020-07-22', 'Dr. Jacobs', 'Groote Schuur', 'Omeprazole', 'severe', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(10, 21040, 'Whiplash', '2024-10-16', 'Dr. Patel', 'Steve Biko Academic', NULL, 'mild', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(11, 21041, 'Post-op Infection', '2020-11-07', 'Dr. Jacobs', 'Groote Schuur', 'Omeprazole', 'severe', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(12, 21043, 'Diabetes Type 2', '2024-03-24', 'Dr. Botha', 'Groote Schuur', 'Amlodipine', 'severe', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(13, 21044, 'Back Pain', '2022-11-18', 'Dr. Naidoo', 'Life Vincent Pallotti', NULL, 'severe', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(14, 21045, 'Whiplash', '2019-08-13', 'Dr. Botha', 'Chris Hani Baragwanath', 'Omeprazole', 'moderate', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(15, 21047, 'Anxiety', '2018-08-04', 'Dr. Botha', 'Chris Hani Baragwanath', 'Metformin', 'critical', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(16, 21048, 'Whiplash', '2016-03-09', 'Dr. Smith', 'Life Vincent Pallotti', 'Amlodipine', 'critical', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(17, 21049, 'Whiplash', '2023-03-02', 'Dr. Smith', 'Groote Schuur', 'Ibuprofen', 'severe', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(18, 21052, 'Anxiety', '2020-09-04', 'Dr. Patel', 'Chris Hani Baragwanath', 'Ibuprofen', 'critical', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(19, 21053, 'Anxiety', '2021-06-04', 'Dr. Naidoo', 'Chris Hani Baragwanath', 'Metformin', 'mild', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(20, 21054, 'Concussion', '2018-03-28', 'Dr. Smith', 'Groote Schuur', 'Paracetamol', 'severe', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(21, 21057, 'Diabetes Type 2', '2022-03-21', 'Dr. Patel', 'Netcare Milpark', NULL, 'moderate', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(22, 21058, 'Hypertension', '2017-10-07', 'Dr. Williams', 'Groote Schuur', NULL, 'critical', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(23, 21059, 'Post-op Infection', '2023-04-15', 'Dr. Botha', 'Life Vincent Pallotti', 'Omeprazole', 'severe', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(24, 21062, 'Hypertension', '2022-06-06', 'Dr. Botha', 'Netcare Milpark', NULL, 'severe', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(25, 21064, 'Concussion', '2015-05-13', 'Dr. Jacobs', 'Netcare Milpark', 'Ibuprofen', 'severe', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(26, 21068, 'Anxiety', '2019-05-17', 'Dr. Williams', 'Netcare Milpark', NULL, 'mild', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(27, 21069, 'Hypertension', '2019-04-17', 'Dr. Naidoo', 'Steve Biko Academic', 'Paracetamol', 'critical', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(28, 21073, 'Whiplash', '2020-03-13', 'Dr. Patel', 'Life Vincent Pallotti', 'Insulin', 'severe', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(29, 21074, 'Fracture', '2018-06-26', 'Dr. Botha', 'Groote Schuur', 'Omeprazole', 'severe', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(30, 21075, 'Whiplash', '2024-03-26', 'Dr. Botha', 'Steve Biko Academic', 'Paracetamol', 'severe', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(31, 21076, 'Whiplash', '2023-03-17', 'Dr. Naidoo', 'Chris Hani Baragwanath', 'Insulin', 'mild', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(32, 21077, 'Anxiety', '2019-03-09', 'Dr. Smith', 'Netcare Milpark', NULL, 'severe', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(33, 21078, 'Post-op Infection', '2016-09-13', 'Dr. Patel', 'Chris Hani Baragwanath', NULL, 'moderate', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(34, 21081, 'Anxiety', '2016-02-26', 'Dr. Botha', 'Life Vincent Pallotti', 'Ibuprofen', 'moderate', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(35, 21083, 'Diabetes Type 2', '2023-10-06', 'Dr. Patel', 'Chris Hani Baragwanath', NULL, 'critical', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(36, 21084, 'Anxiety', '2020-02-09', 'Dr. Williams', 'Steve Biko Academic', 'Ibuprofen', 'moderate', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(37, 21085, 'Concussion', '2020-01-23', 'Dr. Botha', 'Groote Schuur', NULL, 'severe', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(38, 21001, 'Anxiety', '2024-09-11', 'Dr. Williams', 'Mediclinic Sandton', 'Metformin', 'severe', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(39, 21002, 'Diabetes Type 2', '2019-06-25', 'Dr. Botha', 'Netcare Milpark', 'Omeprazole', 'severe', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(40, 21003, 'Fracture', '2018-07-12', 'Dr. Williams', 'Life Vincent Pallotti', 'Amlodipine', 'moderate', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(41, 21004, 'Concussion', '2015-05-06', 'Dr. Botha', 'Netcare Milpark', NULL, 'severe', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(42, 21005, 'Anxiety', '2021-07-12', 'Dr. Williams', 'Steve Biko Academic', NULL, 'critical', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(43, 21006, 'Anxiety', '2019-11-25', 'Dr. Williams', 'Chris Hani Baragwanath', 'Ibuprofen', 'moderate', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(44, 21007, 'Anxiety', '2020-07-18', 'Dr. Patel', 'Steve Biko Academic', NULL, 'mild', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(45, 21008, 'Hypertension', '2016-10-11', 'Dr. Patel', 'Groote Schuur', 'Omeprazole', 'severe', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(46, 21009, 'Concussion', '2022-10-07', 'Dr. Botha', 'Steve Biko Academic', 'Ibuprofen', 'moderate', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(47, 21010, 'Hypertension', '2015-06-28', 'Dr. Jacobs', 'Chris Hani Baragwanath', NULL, 'severe', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(48, 21037, 'Diabetes Type 2', '2025-01-12', 'Dr. Williams', 'Chris Hani Baragwanath', NULL, 'mild', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(49, 21039, 'Fracture', '2020-02-12', 'Dr. Williams', 'Steve Biko Academic', 'Paracetamol', 'moderate', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(50, 21040, 'Hypertension', '2024-11-08', 'Dr. Botha', 'Life Vincent Pallotti', 'Ibuprofen', 'severe', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(51, 21041, 'Post-op Infection', '2021-01-20', 'Dr. Botha', 'Chris Hani Baragwanath', 'Ibuprofen', 'severe', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(52, 21042, 'Fracture', '2017-09-21', 'Dr. Naidoo', 'Netcare Milpark', 'Metformin', 'critical', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(53, 21044, 'Concussion', '2019-08-31', 'Dr. Jacobs', 'Steve Biko Academic', 'Metformin', 'severe', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(54, 21045, 'Back Pain', '2023-07-21', 'Dr. Botha', 'Mediclinic Sandton', NULL, 'critical', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(55, 21047, 'Concussion', '2025-03-16', 'Dr. Patel', 'Steve Biko Academic', 'Paracetamol', 'mild', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(56, 21048, 'Whiplash', '2025-04-25', 'Dr. Naidoo', 'Life Vincent Pallotti', 'Omeprazole', 'severe', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(57, 21049, 'Whiplash', '2019-02-21', 'Dr. Jacobs', 'Netcare Milpark', NULL, 'severe', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(58, 21052, 'Post-op Infection', '2017-01-14', 'Dr. Smith', 'Netcare Milpark', 'Amlodipine', 'critical', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(59, 21053, 'Back Pain', '2025-03-31', 'Dr. Jacobs', 'Life Vincent Pallotti', 'Ibuprofen', 'critical', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(60, 21054, 'Concussion', '2022-01-04', 'Dr. Botha', 'Netcare Milpark', 'Omeprazole', 'severe', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(61, 21055, 'Concussion', '2025-04-30', 'Dr. Smith', 'Steve Biko Academic', 'Amlodipine', 'mild', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(62, 21056, 'Hypertension', '2018-01-02', 'Dr. Jacobs', 'Groote Schuur', 'Metformin', 'critical', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(63, 21057, 'Diabetes Type 2', '2023-03-22', 'Dr. Jacobs', 'Life Vincent Pallotti', 'Amlodipine', 'moderate', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(64, 21059, 'Back Pain', '2016-09-01', 'Dr. Williams', 'Mediclinic Sandton', NULL, 'severe', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(65, 21062, 'Anxiety', '2018-04-24', 'Dr. Botha', 'Mediclinic Sandton', 'Metformin', 'moderate', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(66, 21063, 'Diabetes Type 2', '2019-03-19', 'Dr. Botha', 'Groote Schuur', NULL, 'mild', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(67, 21066, 'Concussion', '2024-08-02', 'Dr. Smith', 'Groote Schuur', 'Omeprazole', 'moderate', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(68, 21067, 'Concussion', '2017-08-25', 'Dr. Williams', 'Mediclinic Sandton', 'Paracetamol', 'moderate', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(69, 21071, 'Back Pain', '2018-01-08', 'Dr. Williams', 'Steve Biko Academic', NULL, 'severe', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(70, 21076, 'Back Pain', '2019-10-26', 'Dr. Smith', 'Steve Biko Academic', 'Insulin', 'severe', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(71, 21078, 'Diabetes Type 2', '2021-12-25', 'Dr. Patel', 'Groote Schuur', 'Paracetamol', 'critical', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(72, 21079, 'Post-op Infection', '2022-12-06', 'Dr. Jacobs', 'Steve Biko Academic', 'Ibuprofen', 'mild', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(73, 21080, 'Concussion', '2025-04-01', 'Dr. Naidoo', 'Life Vincent Pallotti', NULL, 'critical', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(74, 21081, 'Whiplash', '2020-05-11', 'Dr. Smith', 'Mediclinic Sandton', 'Metformin', 'critical', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(75, 21082, 'Concussion', '2019-10-28', 'Dr. Patel', 'Steve Biko Academic', NULL, 'severe', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(76, 21083, 'Anxiety', '2017-10-19', 'Dr. Williams', 'Steve Biko Academic', 'Amlodipine', 'severe', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(77, 21084, 'Back Pain', '2016-12-13', 'Dr. Patel', 'Mediclinic Sandton', 'Insulin', 'mild', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(78, 21085, 'Anxiety', '2016-08-02', 'Dr. Patel', 'Chris Hani Baragwanath', NULL, 'critical', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(79, 21001, 'Anxiety', '2018-10-09', 'Dr. Patel', 'Mediclinic Sandton', NULL, 'mild', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(80, 21002, 'Whiplash', '2023-05-25', 'Dr. Jacobs', 'Netcare Milpark', NULL, 'moderate', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(81, 21004, 'Whiplash', '2019-06-13', 'Dr. Botha', 'Mediclinic Sandton', 'Paracetamol', 'moderate', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(82, 21008, 'Diabetes Type 2', '2023-08-23', 'Dr. Jacobs', 'Life Vincent Pallotti', NULL, 'critical', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(83, 21010, 'Hypertension', '2017-06-14', 'Dr. Jacobs', 'Groote Schuur', NULL, 'severe', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(84, 21038, 'Hypertension', '2018-06-29', 'Dr. Patel', 'Netcare Milpark', 'Ibuprofen', 'critical', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(85, 21039, 'Whiplash', '2022-11-08', 'Dr. Smith', 'Life Vincent Pallotti', 'Ibuprofen', 'moderate', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(86, 21040, 'Hypertension', '2016-10-03', 'Dr. Smith', 'Life Vincent Pallotti', 'Ibuprofen', 'mild', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(87, 21041, 'Concussion', '2023-12-15', 'Dr. Jacobs', 'Groote Schuur', 'Insulin', 'critical', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(88, 21042, 'Anxiety', '2017-03-19', 'Dr. Smith', 'Netcare Milpark', 'Metformin', 'critical', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(89, 21043, 'Fracture', '2015-05-18', 'Dr. Patel', 'Netcare Milpark', 'Amlodipine', 'moderate', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(90, 21044, 'Hypertension', '2014-11-16', 'Dr. Williams', 'Life Vincent Pallotti', NULL, 'severe', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(91, 21045, 'Back Pain', '2021-11-05', 'Dr. Botha', 'Chris Hani Baragwanath', NULL, 'mild', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(92, 21046, 'Whiplash', '2017-04-08', 'Dr. Botha', 'Mediclinic Sandton', 'Insulin', 'mild', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(93, 21048, 'Fracture', '2025-04-26', 'Dr. Jacobs', 'Chris Hani Baragwanath', NULL, 'critical', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(94, 21051, 'Fracture', '2021-06-28', 'Dr. Patel', 'Netcare Milpark', 'Ibuprofen', 'moderate', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(95, 21052, 'Post-op Infection', '2016-12-30', 'Dr. Patel', 'Life Vincent Pallotti', NULL, 'mild', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(96, 21055, 'Post-op Infection', '2018-03-16', 'Dr. Williams', 'Steve Biko Academic', NULL, 'moderate', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(97, 21057, 'Concussion', '2016-01-11', 'Dr. Botha', 'Mediclinic Sandton', NULL, 'mild', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(98, 21059, 'Whiplash', '2016-03-19', 'Dr. Smith', 'Groote Schuur', 'Metformin', 'critical', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(99, 21060, 'Hypertension', '2021-07-26', 'Dr. Naidoo', 'Chris Hani Baragwanath', 'Metformin', 'critical', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(100, 21062, 'Post-op Infection', '2018-09-02', 'Dr. Botha', 'Groote Schuur', 'Paracetamol', 'moderate', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(101, 21063, 'Hypertension', '2017-01-01', 'Dr. Naidoo', 'Groote Schuur', NULL, 'critical', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(102, 21066, 'Hypertension', '2017-04-23', 'Dr. Jacobs', 'Steve Biko Academic', 'Omeprazole', 'critical', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(103, 21068, 'Post-op Infection', '2025-03-17', 'Dr. Botha', 'Chris Hani Baragwanath', 'Paracetamol', 'critical', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(104, 21069, 'Concussion', '2016-10-05', 'Dr. Naidoo', 'Netcare Milpark', 'Insulin', 'mild', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(105, 21071, 'Post-op Infection', '2015-01-06', 'Dr. Smith', 'Life Vincent Pallotti', 'Metformin', 'severe', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(106, 21072, 'Hypertension', '2018-10-07', 'Dr. Patel', 'Life Vincent Pallotti', 'Insulin', 'severe', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(107, 21073, 'Fracture', '2023-03-25', 'Dr. Jacobs', 'Steve Biko Academic', NULL, 'critical', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(108, 21076, 'Concussion', '2016-07-14', 'Dr. Smith', 'Chris Hani Baragwanath', 'Metformin', 'moderate', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(109, 21077, 'Whiplash', '2017-04-24', 'Dr. Botha', 'Life Vincent Pallotti', 'Paracetamol', 'critical', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(110, 21078, 'Hypertension', '2017-08-24', 'Dr. Jacobs', 'Netcare Milpark', NULL, 'mild', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(111, 21079, 'Back Pain', '2017-05-28', 'Dr. Botha', 'Life Vincent Pallotti', NULL, 'moderate', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(112, 21081, 'Fracture', '2022-04-22', 'Dr. Naidoo', 'Steve Biko Academic', 'Paracetamol', 'moderate', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(113, 21084, 'Fracture', '2017-03-04', 'Dr. Botha', 'Chris Hani Baragwanath', NULL, 'moderate', 0, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15'),
(114, 21085, 'Concussion', '2016-06-20', 'Dr. Smith', 'Netcare Milpark', 'Amlodipine', 'mild', 1, 'Auto-seeded medical history entry', '2025-10-27 13:24:15', '2025-10-27 13:24:15');

-- --------------------------------------------------------

--
-- Table structure for table `user_notifications`
--

CREATE TABLE `user_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('info','success','warning','error','case_update','service_approved','service_rejected','document_uploaded','system') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `action_url` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_dismissed` tinyint(1) DEFAULT 0,
  `expires_at` datetime DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_notifications`
--

INSERT INTO `user_notifications` (`id`, `user_id`, `type`, `title`, `message`, `action_url`, `is_read`, `is_dismissed`, `expires_at`, `metadata`, `created_at`) VALUES
(36001, 21001, 'case_update', 'Case 24001 Updated', 'Your case status was updated to Active.', NULL, 0, 0, NULL, NULL, '2025-10-27 13:24:10'),
(36002, 20007, 'system', 'New Document Assigned', 'A new document requires your review.', NULL, 0, 0, NULL, NULL, '2025-10-27 13:24:10'),
(36003, 1, 'service_approved', 'Action Required', 'Service request requires attention.', '/app/cases/index.php', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36004, 20002, 'document_uploaded', 'Action Required', 'Appointment confirmed.', '/app/cases/', 1, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36005, 20003, 'document_uploaded', 'Notice', 'Profile updated successfully.', '/app/cases/', 1, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36006, 20005, 'case_update', 'Reminder', 'Your case was updated.', '/app/cases/index.php', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36007, 20006, 'case_update', 'Notice', 'Your case was updated.', '/app/cases/', 0, 0, '2025-11-17 13:24:15', NULL, '2025-10-27 13:24:15'),
(36008, 20007, 'case_update', 'Notice', 'Profile updated successfully.', '/app/cases/view.php?id=', 0, 1, NULL, NULL, '2025-10-27 13:24:15'),
(36009, 20011, 'case_update', 'Action Required', 'Appointment confirmed.', '/app/cases/view.php?id=', 1, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36010, 20012, 'success', 'Action Required', 'Your case was updated.', '/app/cases/index.php', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36011, 21001, 'error', 'Notice', 'Your case was updated.', '/app/cases/', 1, 0, '2025-11-06 13:24:15', NULL, '2025-10-27 13:24:15'),
(36012, 21002, 'info', 'Update', 'Service request requires attention.', '/app/cases/view.php?id=', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36013, 21003, 'case_update', 'Notice', 'A new document was added.', '/app/cases/', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36014, 21004, 'success', 'Update', 'A new document was added.', '/app/cases/index.php', 0, 1, NULL, NULL, '2025-10-27 13:24:15'),
(36015, 21005, 'error', 'Update', 'A new document was added.', '/app/cases/view.php?id=', 0, 1, NULL, NULL, '2025-10-27 13:24:15'),
(36016, 21007, 'info', 'Update', 'Your case was updated.', '/app/cases/', 1, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36017, 21008, 'case_update', 'Notice', 'Service request requires attention.', '/app/cases/', 1, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36018, 21009, 'error', 'Update', 'Appointment confirmed.', '/app/cases/index.php', 1, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36019, 21010, 'info', 'Update', 'Your case was updated.', '/app/cases/', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36020, 21011, 'success', 'Update', 'A new document was added.', '/app/cases/', 1, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36021, 21012, 'error', 'Reminder', 'A new document was added.', '/app/cases/', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36022, 21013, 'info', 'Reminder', 'A new document was added.', '/app/cases/index.php', 0, 1, NULL, NULL, '2025-10-27 13:24:15'),
(36023, 21015, 'success', 'Action Required', 'Service request requires attention.', '/app/cases/view.php?id=', 1, 0, '2025-11-22 13:24:15', NULL, '2025-10-27 13:24:15'),
(36024, 21016, 'success', 'Reminder', 'Service request requires attention.', '/app/cases/index.php', 1, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36025, 21018, 'service_rejected', 'Notice', 'Your case was updated.', '/app/cases/', 1, 1, NULL, NULL, '2025-10-27 13:24:15'),
(36026, 21019, 'warning', 'Success', 'Your case was updated.', '/app/cases/index.php', 1, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36027, 21020, 'case_update', 'Reminder', 'Profile updated successfully.', '/app/cases/view.php?id=', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36028, 21021, 'case_update', 'Update', 'Appointment confirmed.', '/app/cases/', 1, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36029, 21022, 'warning', 'Notice', 'Appointment confirmed.', '/app/cases/index.php', 1, 0, '2025-11-18 13:24:15', NULL, '2025-10-27 13:24:15'),
(36030, 21023, 'info', 'Reminder', 'Your case was updated.', '/app/cases/index.php', 0, 1, NULL, NULL, '2025-10-27 13:24:15'),
(36031, 21024, 'service_rejected', 'Update', 'Profile updated successfully.', '/app/cases/index.php', 1, 1, NULL, NULL, '2025-10-27 13:24:15'),
(36032, 21026, 'document_uploaded', 'Update', 'Your case was updated.', '/app/cases/', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36033, 21027, 'case_update', 'Notice', 'Service request requires attention.', '/app/cases/view.php?id=', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36034, 21028, 'service_rejected', 'Reminder', 'Your case was updated.', '/app/cases/index.php', 1, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36035, 21029, 'case_update', 'Update', 'A new document was added.', '/app/cases/index.php', 0, 1, NULL, NULL, '2025-10-27 13:24:15'),
(36036, 21030, 'document_uploaded', 'Success', 'Your case was updated.', '/app/cases/', 0, 1, NULL, NULL, '2025-10-27 13:24:15'),
(36037, 21031, 'success', 'Success', 'Appointment confirmed.', '/app/cases/', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36038, 21034, 'success', 'Notice', 'Your case was updated.', '/app/cases/index.php', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36039, 21035, 'success', 'Success', 'Service request requires attention.', '/app/cases/', 1, 0, '2025-10-28 13:24:15', NULL, '2025-10-27 13:24:15'),
(36040, 21036, 'error', 'Action Required', 'Profile updated successfully.', '/app/cases/', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36041, 21037, 'case_update', 'Notice', 'Service request requires attention.', '/app/cases/index.php', 1, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36042, 21038, 'error', 'Notice', 'A new document was added.', '/app/cases/view.php?id=', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36043, 21040, 'case_update', 'Reminder', 'Your case was updated.', '/app/cases/view.php?id=', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36044, 21042, 'info', 'Action Required', 'Service request requires attention.', '/app/cases/view.php?id=', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36045, 21043, 'system', 'Update', 'Appointment confirmed.', '/app/cases/index.php', 0, 0, '2025-11-05 13:24:15', NULL, '2025-10-27 13:24:15'),
(36046, 21045, 'info', 'Notice', 'A new document was added.', '/app/cases/index.php', 1, 0, '2025-10-29 13:24:15', NULL, '2025-10-27 13:24:15'),
(36047, 21046, 'case_update', 'Notice', 'Profile updated successfully.', '/app/cases/index.php', 0, 0, '2025-11-11 13:24:15', NULL, '2025-10-27 13:24:15'),
(36048, 21047, 'service_approved', 'Reminder', 'Your case was updated.', '/app/cases/view.php?id=', 1, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36049, 21048, 'case_update', 'Update', 'Appointment confirmed.', '/app/cases/', 1, 1, NULL, NULL, '2025-10-27 13:24:15'),
(36050, 21049, 'service_approved', 'Action Required', 'Service request requires attention.', '/app/cases/', 0, 0, '2025-11-23 13:24:15', NULL, '2025-10-27 13:24:15'),
(36051, 21050, 'success', 'Notice', 'Your case was updated.', '/app/cases/index.php', 0, 0, '2025-11-01 13:24:15', NULL, '2025-10-27 13:24:15'),
(36052, 21053, 'info', 'Success', 'Service request requires attention.', '/app/cases/index.php', 1, 0, '2025-11-06 13:24:15', NULL, '2025-10-27 13:24:15'),
(36053, 21054, 'info', 'Action Required', 'Your case was updated.', '/app/cases/', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36054, 21055, 'case_update', 'Reminder', 'Service request requires attention.', '/app/cases/index.php', 0, 0, '2025-11-03 13:24:15', NULL, '2025-10-27 13:24:15'),
(36055, 21057, 'info', 'Success', 'Appointment confirmed.', '/app/cases/', 1, 0, '2025-10-30 13:24:15', NULL, '2025-10-27 13:24:15'),
(36056, 21059, 'success', 'Update', 'Your case was updated.', '/app/cases/', 1, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36057, 21062, 'service_rejected', 'Action Required', 'Appointment confirmed.', '/app/cases/view.php?id=', 0, 0, '2025-11-10 13:24:15', NULL, '2025-10-27 13:24:15'),
(36058, 21063, 'case_update', 'Success', 'Profile updated successfully.', '/app/cases/', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36059, 21064, 'case_update', 'Success', 'A new document was added.', '/app/cases/', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36060, 21065, 'document_uploaded', 'Action Required', 'Profile updated successfully.', '/app/cases/', 1, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36061, 21066, 'case_update', 'Update', 'Appointment confirmed.', '/app/cases/', 0, 1, '2025-11-16 13:24:15', NULL, '2025-10-27 13:24:15'),
(36062, 21067, 'case_update', 'Action Required', 'Service request requires attention.', '/app/cases/view.php?id=', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36063, 21068, 'service_approved', 'Reminder', 'Your case was updated.', '/app/cases/view.php?id=', 1, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36064, 21069, 'service_approved', 'Update', 'Your case was updated.', '/app/cases/', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36065, 21070, 'success', 'Success', 'A new document was added.', '/app/cases/view.php?id=', 1, 0, '2025-11-25 13:24:15', NULL, '2025-10-27 13:24:15'),
(36066, 21071, 'case_update', 'Success', 'Profile updated successfully.', '/app/cases/view.php?id=', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36067, 21073, 'service_rejected', 'Notice', 'Your case was updated.', '/app/cases/view.php?id=', 1, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36068, 21074, 'info', 'Success', 'Service request requires attention.', '/app/cases/', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36069, 21075, 'document_uploaded', 'Reminder', 'Your case was updated.', '/app/cases/view.php?id=', 0, 0, '2025-11-18 13:24:15', NULL, '2025-10-27 13:24:15'),
(36070, 21076, 'case_update', 'Notice', 'Service request requires attention.', '/app/cases/view.php?id=', 1, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36071, 21077, 'success', 'Reminder', 'Your case was updated.', '/app/cases/view.php?id=', 1, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36072, 21078, 'case_update', 'Notice', 'Service request requires attention.', '/app/cases/index.php', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36073, 21079, 'warning', 'Reminder', 'A new document was added.', '/app/cases/index.php', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36074, 21080, 'success', 'Notice', 'A new document was added.', '/app/cases/view.php?id=', 1, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36075, 21081, 'warning', 'Action Required', 'Profile updated successfully.', '/app/cases/', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36076, 21082, 'success', 'Action Required', 'Service request requires attention.', '/app/cases/', 1, 0, '2025-11-22 13:24:15', NULL, '2025-10-27 13:24:15'),
(36077, 21083, 'error', 'Update', 'Service request requires attention.', '/app/cases/', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36078, 21084, 'case_update', 'Reminder', 'Profile updated successfully.', '/app/cases/index.php', 0, 0, NULL, NULL, '2025-10-27 13:24:15'),
(36079, 21085, 'success', 'Notice', 'Service request requires attention.', '/app/cases/index.php', 0, 1, NULL, NULL, '2025-10-27 13:24:15');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `data` mediumtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_activity` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `analytics_events`
--
ALTER TABLE `analytics_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ae_user` (`user_id`),
  ADD KEY `idx_ae_type_time` (`event_type`,`created_at`);

--
-- Indexes for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_api_key` (`api_key`),
  ADD KEY `idx_key_type_active` (`key_type`,`is_active`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `fk_api_key_created_by` (`created_by`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_appt_case` (`case_id`),
  ADD KEY `idx_appt_assigned` (`assigned_to`),
  ADD KEY `idx_appt_start` (`start_time`),
  ADD KEY `fk_appt_created_by` (`created_by`);

--
-- Indexes for table `attorney_profiles`
--
ALTER TABLE `attorney_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_attorney_user` (`user_id`);

--
-- Indexes for table `backup_logs`
--
ALTER TABLE `backup_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedule_status` (`schedule_id`,`status`),
  ADD KEY `idx_started_at` (`started_at`),
  ADD KEY `fk_backup_log_executed_by` (`executed_by`);

--
-- Indexes for table `backup_schedules`
--
ALTER TABLE `backup_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedule_active` (`is_active`),
  ADD KEY `idx_next_run` (`next_run`),
  ADD KEY `fk_backup_created_by` (`created_by`);

--
-- Indexes for table `cases`
--
ALTER TABLE `cases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cases_user` (`user_id`),
  ADD KEY `idx_cases_status` (`status`),
  ADD KEY `idx_cases_assigned_to` (`assigned_to`);

--
-- Indexes for table `case_activities`
--
ALTER TABLE `case_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activities_case` (`case_id`),
  ADD KEY `idx_activities_user` (`user_id`),
  ADD KEY `idx_activities_type` (`activity_type`);

--
-- Indexes for table `case_documents`
--
ALTER TABLE `case_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_documents_case` (`case_id`),
  ADD KEY `idx_documents_uploaded_by` (`uploaded_by`),
  ADD KEY `idx_documents_version` (`case_id`,`version`),
  ADD KEY `idx_documents_current` (`is_current`),
  ADD KEY `fk_documents_parent` (`parent_document_id`);

--
-- Indexes for table `compensations`
--
ALTER TABLE `compensations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_compensations_case` (`case_id`),
  ADD KEY `idx_compensations_case` (`case_id`),
  ADD KEY `idx_compensations_status` (`status`),
  ADD KEY `idx_compensations_entered_by` (`entered_by`),
  ADD KEY `idx_compensations_approved_by` (`approved_by`),
  ADD KEY `idx_compensations_invoice` (`invoice_id`);

--
-- Indexes for table `compliance_logs`
--
ALTER TABLE `compliance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_request_action` (`request_id`,`action_type`),
  ADD KEY `idx_performed_at` (`performed_at`),
  ADD KEY `fk_compliance_log_performed_by` (`performed_by`);

--
-- Indexes for table `compliance_requests`
--
ALTER TABLE `compliance_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status_priority` (`status`,`priority`),
  ADD KEY `idx_request_type` (`request_type`),
  ADD KEY `idx_due_date` (`due_date`),
  ADD KEY `fk_compliance_assigned_to` (`assigned_to`);

--
-- Indexes for table `compliance_templates`
--
ALTER TABLE `compliance_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_template_type_active` (`template_type`,`is_active`),
  ADD KEY `fk_compliance_template_created_by` (`created_by`);

--
-- Indexes for table `compliance_verification`
--
ALTER TABLE `compliance_verification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_request_status` (`request_id`,`status`),
  ADD KEY `fk_verification_verified_by` (`verified_by`);

--
-- Indexes for table `connection_tests`
--
ALTER TABLE `connection_tests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_provider_status` (`provider_id`,`status`),
  ADD KEY `idx_test_type` (`test_type`),
  ADD KEY `idx_tested_at` (`tested_at`),
  ADD KEY `fk_connection_test_tested_by` (`tested_by`);

--
-- Indexes for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_assigned_to` (`assigned_to`);

--
-- Indexes for table `data_retention_holds`
--
ALTER TABLE `data_retention_holds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status_dates` (`status`,`hold_start_date`,`hold_end_date`),
  ADD KEY `idx_hold_type` (`hold_type`),
  ADD KEY `fk_retention_hold_created_by` (`created_by`);

--
-- Indexes for table `financial_requests`
--
ALTER TABLE `financial_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_financial_requests_case` (`case_id`),
  ADD KEY `idx_financial_requests_status` (`status`),
  ADD KEY `idx_financial_requests_requested_by` (`requested_by`),
  ADD KEY `idx_financial_requests_approved_by` (`approved_by`);

--
-- Indexes for table `health_check_results`
--
ALTER TABLE `health_check_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_check_status` (`check_id`,`status`),
  ADD KEY `idx_checked_at` (`checked_at`);

--
-- Indexes for table `integration_status`
--
ALTER TABLE `integration_status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status_type` (`status`,`integration_type`),
  ADD KEY `idx_last_check` (`last_check`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `idx_invoices_case` (`case_id`),
  ADD KEY `idx_invoices_status` (`status`),
  ADD KEY `idx_invoices_due_date` (`due_date`),
  ADD KEY `idx_invoices_created_by` (`created_by`),
  ADD KEY `idx_invoices_client` (`client_id`),
  ADD KEY `idx_invoices_paid_at` (`paid_at`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_items_invoice` (`invoice_id`),
  ADD KEY `idx_invoice_items_service` (`service_id`),
  ADD KEY `idx_invoice_items_sort` (`invoice_id`,`sort_order`);

--
-- Indexes for table `invoice_payments`
--
ALTER TABLE `invoice_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_payments_invoice` (`invoice_id`),
  ADD KEY `idx_invoice_payments_payfast` (`payfast_payment_id`),
  ADD KEY `fk_invoice_payments_user` (`created_by`);

--
-- Indexes for table `job_queue`
--
ALTER TABLE `job_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status_priority` (`status`,`priority`),
  ADD KEY `idx_scheduled_at` (`scheduled_at`),
  ADD KEY `fk_job_created_by` (`created_by`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_message_thread` (`thread_id`),
  ADD KEY `idx_message_sender` (`sender_id`),
  ADD KEY `idx_messages_recipient` (`recipient_id`),
  ADD KEY `idx_messages_is_read` (`is_read`),
  ADD KEY `idx_messages_recipient_read` (`recipient_id`,`is_read`);

--
-- Indexes for table `message_participants`
--
ALTER TABLE `message_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_participant_thread_user` (`thread_id`,`user_id`),
  ADD KEY `idx_participants_thread` (`thread_id`),
  ADD KEY `idx_participants_user` (`user_id`),
  ADD KEY `idx_participants_active` (`is_active`),
  ADD KEY `idx_participants_joined` (`joined_at`),
  ADD KEY `fk_participants_last_read` (`last_read_message_id`),
  ADD KEY `idx_participants_thread_active` (`thread_id`,`is_active`),
  ADD KEY `idx_participants_user_active` (`user_id`,`is_active`);

--
-- Indexes for table `message_read_status`
--
ALTER TABLE `message_read_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_message_user_read` (`message_id`,`user_id`),
  ADD KEY `idx_read_status_message` (`message_id`),
  ADD KEY `idx_read_status_user` (`user_id`),
  ADD KEY `idx_read_status_read_at` (`read_at`);

--
-- Indexes for table `message_threads`
--
ALTER TABLE `message_threads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_thread_case` (`case_id`),
  ADD KEY `idx_thread_type` (`thread_type`),
  ADD KEY `idx_thread_assigned_to` (`assigned_to`),
  ADD KEY `fk_thread_created_by` (`created_by`);

--
-- Indexes for table `news_articles`
--
ALTER TABLE `news_articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_news_slug` (`slug`);

--
-- Indexes for table `notification_campaigns`
--
ALTER TABLE `notification_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status_scheduled` (`status`,`scheduled_at`),
  ADD KEY `fk_campaign_template` (`template_id`),
  ADD KEY `fk_campaign_created_by` (`created_by`);

--
-- Indexes for table `notification_delivery`
--
ALTER TABLE `notification_delivery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_campaign_status` (`campaign_id`,`status`),
  ADD KEY `idx_user_type` (`user_id`,`notification_type`),
  ADD KEY `idx_sent_at` (`sent_at`);

--
-- Indexes for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `notification_role_targeting`
--
ALTER TABLE `notification_role_targeting`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_campaign_role` (`campaign_id`,`target_role`);

--
-- Indexes for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_template_type_active` (`template_type`,`is_active`),
  ADD KEY `fk_notification_template_created_by` (`created_by`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_pages_slug` (`slug`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_resets_token` (`token`),
  ADD KEY `idx_resets_user` (`user_id`);

--
-- Indexes for table `payfast_transactions`
--
ALTER TABLE `payfast_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_pf_payment_id` (`pf_payment_id`),
  ADD KEY `idx_pf_invoice` (`invoice_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payments_invoice` (`invoice_id`),
  ADD KEY `idx_payments_status` (`status`),
  ADD KEY `idx_payments_processed_by` (`processed_by`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_permission_name` (`name`),
  ADD KEY `idx_permission_module` (`module`);

--
-- Indexes for table `provider_configs`
--
ALTER TABLE `provider_configs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_provider_type_active` (`provider_type`,`is_active`),
  ADD KEY `idx_is_primary` (`is_primary`),
  ADD KEY `fk_provider_created_by` (`created_by`);

--
-- Indexes for table `restore_audit`
--
ALTER TABLE `restore_audit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_requested_at` (`requested_at`),
  ADD KEY `fk_restore_backup_log` (`backup_log_id`),
  ADD KEY `fk_restore_requested_by` (`requested_by`),
  ADD KEY `fk_restore_approved_by` (`approved_by`);

--
-- Indexes for table `retention_policies`
--
ALTER TABLE `retention_policies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_policy_type_active` (`policy_type`,`is_active`),
  ADD KEY `fk_retention_created_by` (`created_by`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role`,`permission_id`),
  ADD KEY `idx_role_permission` (`permission_id`);

--
-- Indexes for table `security_logs`
--
ALTER TABLE `security_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sl_event_type_time` (`event_type`,`created_at`),
  ADD KEY `idx_sl_user` (`user_id`);

--
-- Indexes for table `server_uptime`
--
ALTER TABLE `server_uptime`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_server_recorded` (`server_name`,`recorded_at`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_services_category` (`category`),
  ADD KEY `idx_services_active` (`is_active`);

--
-- Indexes for table `service_gallery`
--
ALTER TABLE `service_gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service_gallery_service` (`service_id`),
  ADD KEY `idx_service_gallery_active` (`is_active`),
  ADD KEY `idx_service_gallery_sort` (`sort_order`);

--
-- Indexes for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_requests_case` (`case_id`),
  ADD KEY `idx_requests_service` (`service_id`),
  ADD KEY `idx_requests_status` (`status`),
  ADD KEY `idx_requests_processed_by` (`processed_by`);

--
-- Indexes for table `settings_audit`
--
ALTER TABLE `settings_audit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_setting_type_id` (`setting_type`,`setting_id`),
  ADD KEY `idx_changed_at` (`changed_at`),
  ADD KEY `fk_settings_audit_changed_by` (`changed_by`);

--
-- Indexes for table `smtp_logs`
--
ALTER TABLE `smtp_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status_sent` (`status`,`sent_at`),
  ADD KEY `idx_message_type` (`message_type`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_support_tickets_user` (`user_id`),
  ADD KEY `idx_support_tickets_status` (`status`),
  ADD KEY `idx_support_tickets_assigned_to` (`assigned_to`),
  ADD KEY `idx_support_tickets_priority` (`priority`);

--
-- Indexes for table `system_config`
--
ALTER TABLE `system_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_config_key` (`config_key`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `fk_config_updated_by` (`updated_by`);

--
-- Indexes for table `system_health_checks`
--
ALTER TABLE `system_health_checks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_check_type_active` (`check_type`,`is_active`),
  ADD KEY `idx_last_check` (`last_check`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tasks_assigned_to` (`assigned_to`),
  ADD KEY `idx_tasks_status` (`status`),
  ADD KEY `idx_tasks_due_date` (`due_date`),
  ADD KEY `idx_tasks_case_id` (`case_id`),
  ADD KEY `idx_tasks_priority` (`priority`),
  ADD KEY `idx_tasks_task_type` (`task_type`),
  ADD KEY `idx_tasks_created_by` (`created_by`),
  ADD KEY `idx_tasks_completed_by` (`completed_by`),
  ADD KEY `idx_tasks_assigned_status` (`assigned_to`,`status`),
  ADD KEY `idx_tasks_due_status` (`due_date`,`status`),
  ADD KEY `idx_tasks_case_status` (`case_id`,`status`),
  ADD KEY `idx_tasks_type_status` (`task_type`,`status`);

--
-- Indexes for table `trust_accounts`
--
ALTER TABLE `trust_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_trust_accounts_case` (`case_id`),
  ADD KEY `idx_trust_accounts_status` (`status`),
  ADD KEY `idx_trust_accounts_created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_users_email` (`email`),
  ADD KEY `idx_users_active` (`is_active`),
  ADD KEY `idx_users_role_active` (`role`,`is_active`);

--
-- Indexes for table `user_medical_history`
--
ALTER TABLE `user_medical_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_umh_user` (`user_id`),
  ADD KEY `idx_umh_created_at` (`created_at`);

--
-- Indexes for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_us_user` (`user_id`),
  ADD KEY `idx_us_last_activity` (`last_activity`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `analytics_events`
--
ALTER TABLE `analytics_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT for table `api_keys`
--
ALTER TABLE `api_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28132;

--
-- AUTO_INCREMENT for table `attorney_profiles`
--
ALTER TABLE `attorney_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30006;

--
-- AUTO_INCREMENT for table `backup_logs`
--
ALTER TABLE `backup_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `backup_schedules`
--
ALTER TABLE `backup_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cases`
--
ALTER TABLE `cases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24138;

--
-- AUTO_INCREMENT for table `case_activities`
--
ALTER TABLE `case_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=512;

--
-- AUTO_INCREMENT for table `case_documents`
--
ALTER TABLE `case_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=287;

--
-- AUTO_INCREMENT for table `compensations`
--
ALTER TABLE `compensations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `compliance_logs`
--
ALTER TABLE `compliance_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `compliance_requests`
--
ALTER TABLE `compliance_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `compliance_templates`
--
ALTER TABLE `compliance_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `compliance_verification`
--
ALTER TABLE `compliance_verification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `connection_tests`
--
ALTER TABLE `connection_tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `data_retention_holds`
--
ALTER TABLE `data_retention_holds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `financial_requests`
--
ALTER TABLE `financial_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `health_check_results`
--
ALTER TABLE `health_check_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `integration_status`
--
ALTER TABLE `integration_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_payments`
--
ALTER TABLE `invoice_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_queue`
--
ALTER TABLE `job_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39516;

--
-- AUTO_INCREMENT for table `message_participants`
--
ALTER TABLE `message_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=255;

--
-- AUTO_INCREMENT for table `message_read_status`
--
ALTER TABLE `message_read_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message_threads`
--
ALTER TABLE `message_threads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38130;

--
-- AUTO_INCREMENT for table `news_articles`
--
ALTER TABLE `news_articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34006;

--
-- AUTO_INCREMENT for table `notification_campaigns`
--
ALTER TABLE `notification_campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_delivery`
--
ALTER TABLE `notification_delivery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_role_targeting`
--
ALTER TABLE `notification_role_targeting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payfast_transactions`
--
ALTER TABLE `payfast_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `provider_configs`
--
ALTER TABLE `provider_configs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `restore_audit`
--
ALTER TABLE `restore_audit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `retention_policies`
--
ALTER TABLE `retention_policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `security_logs`
--
ALTER TABLE `security_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `server_uptime`
--
ALTER TABLE `server_uptime`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22026;

--
-- AUTO_INCREMENT for table `service_gallery`
--
ALTER TABLE `service_gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30133;

--
-- AUTO_INCREMENT for table `settings_audit`
--
ALTER TABLE `settings_audit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `smtp_logs`
--
ALTER TABLE `smtp_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_config`
--
ALTER TABLE `system_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_health_checks`
--
ALTER TABLE `system_health_checks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `trust_accounts`
--
ALTER TABLE `trust_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21102;

--
-- AUTO_INCREMENT for table `user_medical_history`
--
ALTER TABLE `user_medical_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT for table `user_notifications`
--
ALTER TABLE `user_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36130;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `analytics_events`
--
ALTER TABLE `analytics_events`
  ADD CONSTRAINT `fk_ae_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD CONSTRAINT `fk_api_key_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appt_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_appt_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appt_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attorney_profiles`
--
ALTER TABLE `attorney_profiles`
  ADD CONSTRAINT `fk_attorney_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `backup_logs`
--
ALTER TABLE `backup_logs`
  ADD CONSTRAINT `fk_backup_log_executed_by` FOREIGN KEY (`executed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_backup_log_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `backup_schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `backup_schedules`
--
ALTER TABLE `backup_schedules`
  ADD CONSTRAINT `fk_backup_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cases`
--
ALTER TABLE `cases`
  ADD CONSTRAINT `fk_cases_assigned_to_users` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cases_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `case_activities`
--
ALTER TABLE `case_activities`
  ADD CONSTRAINT `fk_activities_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_activities_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `case_documents`
--
ALTER TABLE `case_documents`
  ADD CONSTRAINT `fk_documents_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_documents_parent` FOREIGN KEY (`parent_document_id`) REFERENCES `case_documents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_documents_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `compensations`
--
ALTER TABLE `compensations`
  ADD CONSTRAINT `fk_compensations_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_compensations_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_compensations_entered_by` FOREIGN KEY (`entered_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_compensations_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `compliance_logs`
--
ALTER TABLE `compliance_logs`
  ADD CONSTRAINT `fk_compliance_log_performed_by` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_compliance_log_request` FOREIGN KEY (`request_id`) REFERENCES `compliance_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `compliance_requests`
--
ALTER TABLE `compliance_requests`
  ADD CONSTRAINT `fk_compliance_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `compliance_templates`
--
ALTER TABLE `compliance_templates`
  ADD CONSTRAINT `fk_compliance_template_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `compliance_verification`
--
ALTER TABLE `compliance_verification`
  ADD CONSTRAINT `fk_verification_request` FOREIGN KEY (`request_id`) REFERENCES `compliance_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_verification_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `connection_tests`
--
ALTER TABLE `connection_tests`
  ADD CONSTRAINT `fk_connection_test_provider` FOREIGN KEY (`provider_id`) REFERENCES `provider_configs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_connection_test_tested_by` FOREIGN KEY (`tested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  ADD CONSTRAINT `contact_submissions_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `data_retention_holds`
--
ALTER TABLE `data_retention_holds`
  ADD CONSTRAINT `fk_retention_hold_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `financial_requests`
--
ALTER TABLE `financial_requests`
  ADD CONSTRAINT `fk_financial_requests_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_financial_requests_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_financial_requests_requested_by` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `health_check_results`
--
ALTER TABLE `health_check_results`
  ADD CONSTRAINT `fk_health_result_check` FOREIGN KEY (`check_id`) REFERENCES `system_health_checks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_invoices_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_invoices_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_invoices_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `fk_invoice_items_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_invoice_items_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `invoice_payments`
--
ALTER TABLE `invoice_payments`
  ADD CONSTRAINT `fk_invoice_payments_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_invoice_payments_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `job_queue`
--
ALTER TABLE `job_queue`
  ADD CONSTRAINT `fk_job_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_message_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_message_thread` FOREIGN KEY (`thread_id`) REFERENCES `message_threads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_messages_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `message_participants`
--
ALTER TABLE `message_participants`
  ADD CONSTRAINT `fk_participants_last_read` FOREIGN KEY (`last_read_message_id`) REFERENCES `messages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_participants_thread` FOREIGN KEY (`thread_id`) REFERENCES `message_threads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_participants_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_read_status`
--
ALTER TABLE `message_read_status`
  ADD CONSTRAINT `fk_read_status_message` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_read_status_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_threads`
--
ALTER TABLE `message_threads`
  ADD CONSTRAINT `fk_thread_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_thread_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_thread_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_campaigns`
--
ALTER TABLE `notification_campaigns`
  ADD CONSTRAINT `fk_campaign_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_campaign_template` FOREIGN KEY (`template_id`) REFERENCES `notification_templates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_delivery`
--
ALTER TABLE `notification_delivery`
  ADD CONSTRAINT `fk_delivery_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `notification_campaigns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_delivery_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD CONSTRAINT `fk_notif_prefs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_role_targeting`
--
ALTER TABLE `notification_role_targeting`
  ADD CONSTRAINT `fk_targeting_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `notification_campaigns` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD CONSTRAINT `fk_notification_template_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payfast_transactions`
--
ALTER TABLE `payfast_transactions`
  ADD CONSTRAINT `fk_pf_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payments_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `provider_configs`
--
ALTER TABLE `provider_configs`
  ADD CONSTRAINT `fk_provider_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `restore_audit`
--
ALTER TABLE `restore_audit`
  ADD CONSTRAINT `fk_restore_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_restore_backup_log` FOREIGN KEY (`backup_log_id`) REFERENCES `backup_logs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_restore_requested_by` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `retention_policies`
--
ALTER TABLE `retention_policies`
  ADD CONSTRAINT `fk_retention_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `security_logs`
--
ALTER TABLE `security_logs`
  ADD CONSTRAINT `fk_sl_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `service_gallery`
--
ALTER TABLE `service_gallery`
  ADD CONSTRAINT `fk_service_gallery_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD CONSTRAINT `fk_requests_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_requests_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_requests_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `settings_audit`
--
ALTER TABLE `settings_audit`
  ADD CONSTRAINT `fk_settings_audit_changed_by` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `fk_support_tickets_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_support_tickets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `system_config`
--
ALTER TABLE `system_config`
  ADD CONSTRAINT `fk_config_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_tasks_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tasks_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tasks_completed_by` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_tasks_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trust_accounts`
--
ALTER TABLE `trust_accounts`
  ADD CONSTRAINT `fk_trust_accounts_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_trust_accounts_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_medical_history`
--
ALTER TABLE `user_medical_history`
  ADD CONSTRAINT `fk_umh_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `fk_us_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
