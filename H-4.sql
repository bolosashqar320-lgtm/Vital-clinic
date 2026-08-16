-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Aug 16, 2026 at 04:21 PM
-- Server version: 8.0.44
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `H`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int NOT NULL,
  `doctor_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `app_date` date DEFAULT NULL,
  `app_time` varchar(20) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `request_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `doctor_id`, `user_id`, `app_date`, `app_time`, `type`, `status`, `request_id`) VALUES
(5, 20, 3, '2026-07-15', '13:30', 'video', 1, NULL),
(6, 20, 3, '2026-07-15', '', 'video', 1, NULL),
(7, 20, 3, '2026-07-12', '', 'video', 1, NULL),
(8, 20, 3, '2026-07-16', '', 'video', 1, NULL),
(9, 20, 3, '2026-07-13', '', 'video', 1, NULL),
(10, 20, 3, '2026-07-20', '12:30', 'video', 1, NULL),
(11, 20, 3, '2026-07-14', '09:00', 'physical', 1, NULL),
(12, 2, 3, '2026-07-15', '11:30', 'video', 1, NULL),
(13, 2, 3, '2026-07-15', '12:00', 'video', 1, NULL),
(14, 20, 3, '2026-07-14', '10:30', 'physical', 1, NULL),
(15, 2, 17, '2026-07-17', '11:00', 'physical', 1, NULL),
(16, 20, 17, '2026-07-15', '10:00', 'physical', 1, NULL),
(17, 2, 3, '2026-07-14', '11:00', 'physical', 1, NULL),
(18, 20, 21, '2026-07-14', '09:30', 'physical', 1, NULL),
(19, 2, 21, '2026-07-16', '10:30', 'video', 1, NULL),
(20, 2, 21, '2026-07-14', '17:00', 'physical', 1, NULL),
(21, 2, 3, '2026-07-10', '10:00', 'video', 2, NULL),
(22, 2, 3, '2026-07-01', '12:00', 'physical', 2, NULL),
(23, 2, 3, '2026-07-21', '13:00', 'video', 2, NULL),
(24, 2, 3, '2026-07-02', '12:00', 'physical', 2, NULL),
(25, 2, 3, '2026-07-15', '04:25', 'video', 1, NULL),
(26, 20, 3, '2026-07-15', '04:25', 'physical', 2, NULL),
(27, 20, 3, '2026-07-23', '09:30', 'video', 2, NULL),
(28, 2, 3, '2026-07-16', '02:15', 'video', 2, NULL),
(29, 2, 3, '2026-07-23', '11:00', 'video', 2, NULL),
(30, 2, 3, '2026-07-21', '10:00', 'video', 2, NULL),
(31, 2, 3, '2026-07-22', '09:00', 'video', 2, NULL),
(32, 2, 3, '2026-08-31', '09:00', 'physical', 1, NULL),
(33, 2, 3, '2026-08-10', '11:00', 'video', 1, NULL),
(34, 20, 30, '2026-07-30', '11:00', 'video', 1, NULL),
(35, 2, 30, '2026-07-30', '12:00', 'physical', 1, 8),
(36, 2, 30, '2026-08-02', '10:00', 'physical', 1, 9),
(37, 20, 29, '2026-07-30', '10:30', 'physical', 1, NULL),
(38, 20, 30, '2026-07-30', '14:30', 'physical', 1, 12),
(39, 20, 30, '2026-08-02', '11:00', 'video', 2, 13),
(40, 20, 30, '2026-08-06', '10:30', 'physical', 2, 14),
(41, 20, 30, '2026-08-10', '12:00', 'physical', 2, 16),
(42, 2, 30, '2026-08-03', '11:00', 'video', 1, 20),
(43, 3, 30, '2026-08-03', '10:00', 'physical', 1, 36),
(44, 3, 3, '2026-08-11', '09:30', 'physical', 1, 37),
(45, 20, 4, '2026-08-18', '10:00', 'video', 1, 38),
(46, 20, 4, '2026-08-05', '10:00', 'video', 1, 39),
(47, 20, 4, '2026-09-06', '10:00', 'video', 1, 40),
(48, 20, 4, '2026-08-11', '11:30', 'physical', 2, 41),
(49, 20, 30, '2026-08-10', '10:30', 'video', 1, 42),
(50, 20, 30, '2026-08-11', '11:30', 'physical', 1, 43),
(51, 20, 30, '2026-08-19', '10:30', 'video', 2, 44),
(52, 20, 30, '2026-08-20', '09:30', 'video', 2, 45),
(53, 20, 33, '2026-08-16', '12:00', 'physical', 2, 47),
(54, 20, 33, '2026-08-27', '11:00', 'video', 1, 48),
(55, 2, 30, '2026-08-10', '10:00', 'physical', 1, 50),
(56, 20, 30, '2026-08-09', '10:30', 'video', 1, 52),
(57, 2, 33, '2026-08-10', '11:30', 'physical', 1, 54),
(58, 2, 33, '2026-08-11', '09:00', 'physical', 1, 56),
(59, 3, 33, '2026-08-16', '10:00', 'physical', 1, 57),
(60, 2, 33, '2026-08-26', '10:00', 'physical', 1, 58),
(61, 3, 33, '2026-08-26', '10:30', 'physical', 2, 59),
(62, 20, 33, '2026-08-26', '11:30', 'physical', 1, 60),
(63, 2, 33, '2026-08-20', '12:30', 'physical', 2, 66);

-- --------------------------------------------------------

--
-- Table structure for table `appointment_requests`
--

CREATE TABLE `appointment_requests` (
  `request_id` int NOT NULL,
  `user_id` int NOT NULL,
  `branch_id` int DEFAULT NULL,
  `specialty_id` int DEFAULT NULL,
  `symptoms` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `appointment_requests`
--

INSERT INTO `appointment_requests` (`request_id`, `user_id`, `branch_id`, `specialty_id`, `symptoms`, `status`, `created_at`) VALUES
(5, 30, 1, 2, 'im very sick', 0, '2026-07-29 22:10:05'),
(7, 30, 1, 4, 'head migrants and extreme amount of pain', 0, '2026-07-29 22:45:28'),
(8, 30, 1, 1, 'COORDING TO FAMILY DOCTOR I HAVE TO GET SOME CHECKS', 1, '2026-07-29 22:46:16'),
(9, 30, 1, 1, 'very very tired', 1, '2026-07-29 23:04:30'),
(11, 17, 2, 2, 'hahah', 0, '2026-07-30 04:50:12'),
(12, 30, 1, 3, 'feeling dizzy and sleepy all day', 1, '2026-07-30 14:01:01'),
(13, 30, 1, 3, 'aa', 1, '2026-07-30 14:18:08'),
(14, 30, 1, 3, 'hhhhh', 1, '2026-07-30 22:20:21'),
(15, 30, 2, 2, 'very bad rash', 0, '2026-07-30 22:29:51'),
(16, 30, 1, 3, 'hhh', 1, '2026-07-30 22:29:58'),
(17, 30, 2, 2, 'lol', 0, '2026-07-30 23:33:05'),
(18, 30, 1, 10, 'a', 0, '2026-07-30 23:34:10'),
(19, 30, 1, 4, 'a', 0, '2026-07-30 23:34:22'),
(20, 30, 1, 1, 'a', 1, '2026-07-30 23:34:29'),
(21, 30, 2, 2, 'a', 0, '2026-07-31 20:22:08'),
(22, 30, 2, 2, 'a', 0, '2026-07-31 20:25:00'),
(23, 30, 1, 3, 'g', 0, '2026-07-31 20:26:18'),
(24, 30, 1, 3, 'g', 0, '2026-08-01 00:55:10'),
(25, 30, 2, 2, 'ff', 0, '2026-08-01 00:55:16'),
(26, 30, 1, 1, 'ww', 0, '2026-08-01 00:55:24'),
(27, 30, 1, 4, 'll', 0, '2026-08-01 00:55:35'),
(28, 30, 2, 10, 'qq', 0, '2026-08-01 00:56:30'),
(29, 30, 2, 10, 'qq', 0, '2026-08-01 00:56:49'),
(30, 30, 2, 10, 'qq', 0, '2026-08-01 00:58:26'),
(31, 30, 2, 10, 'qq', 0, '2026-08-01 01:22:42'),
(32, 30, 2, 10, 'qq', 0, '2026-08-01 01:23:04'),
(33, 30, 2, 10, 'qq', 0, '2026-08-01 02:25:30'),
(34, 30, 1, 1, 'aaa', 0, '2026-08-01 04:51:19'),
(35, 30, 1, 1, 'qq', 0, '2026-08-01 04:51:58'),
(36, 30, 1, 1, 'qq', 1, '2026-08-01 04:52:27'),
(37, 3, 1, 1, 'aa', 1, '2026-08-01 05:10:06'),
(38, 4, 1, 3, '12', 1, '2026-08-01 05:13:05'),
(39, 4, 1, 3, 'aa', 1, '2026-08-01 06:09:33'),
(40, 4, 1, 3, 'aaaaa', 1, '2026-08-01 06:11:51'),
(41, 4, 1, 3, 'aaa', 1, '2026-08-01 06:16:07'),
(42, 30, 1, 3, 'pdf', 1, '2026-08-01 07:11:24'),
(43, 30, 1, 3, 'aa', 1, '2026-08-01 07:55:45'),
(44, 30, 1, 3, 'very sick', 1, '2026-08-01 16:59:21'),
(45, 30, 1, 3, 'jj', 1, '2026-08-01 23:27:52'),
(46, 33, 2, 3, 'aaaa', 0, '2026-08-02 06:43:07'),
(47, 33, 1, 3, 'aa', 1, '2026-08-02 06:43:14'),
(48, 33, 1, 3, 'kk', 1, '2026-08-02 06:44:11'),
(49, 17, 1, 3, 'aa', 0, '2026-08-02 18:58:49'),
(50, 30, 1, 1, 'אני לא מרגיש טוב לגמריייי', 1, '2026-08-02 19:53:50'),
(51, 30, 1, 3, 'aa', 0, '2026-08-03 05:59:48'),
(52, 30, 1, 3, 'feeling sick', 1, '2026-08-03 06:01:02'),
(53, 30, 1, 1, 'I have a very very very bad heartache', 0, '2026-08-03 21:27:23'),
(54, 33, 1, 1, 'gggg', 1, '2026-08-04 10:34:15'),
(55, 33, 1, 1, 'aaa', 0, '2026-08-04 12:43:52'),
(56, 33, 1, 1, 'aaa', 1, '2026-08-07 07:33:38'),
(57, 33, 1, 1, 'aaa', 1, '2026-08-07 07:34:34'),
(58, 33, 1, 1, 'h', 1, '2026-08-07 07:37:08'),
(59, 33, 1, 1, 'aaa', 1, '2026-08-07 07:37:45'),
(60, 33, 1, 3, 'aaa', 1, '2026-08-07 07:38:26'),
(61, 33, 1, 10, 'aaa', 0, '2026-08-08 18:56:21'),
(62, 33, 1, 1, 'aa', 0, '2026-08-08 18:56:27'),
(63, 33, 1, 1, 'aa', 0, '2026-08-08 18:57:25'),
(64, 33, 1, 1, 'aa', 0, '2026-08-08 18:57:39'),
(65, 33, 1, 1, 'aa', 0, '2026-08-08 19:21:39'),
(66, 33, 1, 1, 'not good', 1, '2026-08-09 12:51:42'),
(67, 33, 1, NULL, 'very tired', 0, '2026-08-11 19:44:26'),
(68, 33, 1, 1, 'very tired', 0, '2026-08-11 19:45:00'),
(69, 33, NULL, 1, 'very tired', 0, '2026-08-11 19:45:03'),
(70, 33, NULL, 2, 'very tired', 0, '2026-08-11 19:45:06'),
(71, 33, NULL, 3, 'very tired', 0, '2026-08-11 19:45:08'),
(72, 33, NULL, 3, 'very tired', 0, '2026-08-11 19:54:58'),
(73, 33, NULL, 3, 'very tired', 0, '2026-08-11 19:55:01'),
(74, 33, NULL, 3, 'very tired', 0, '2026-08-11 19:55:44'),
(75, 33, NULL, 3, 'very tired', 0, '2026-08-11 19:55:51'),
(76, 33, NULL, 1, 'a', 0, '2026-08-12 09:52:07'),
(77, 33, 2, NULL, 'a', 0, '2026-08-12 09:52:19'),
(78, 33, 1, NULL, 'a', 0, '2026-08-12 09:52:25'),
(79, 33, 1, NULL, 'a', 0, '2026-08-12 09:52:38'),
(80, 33, 1, NULL, 'a', 0, '2026-08-12 09:59:53'),
(81, 33, 1, NULL, 'a', 0, '2026-08-12 10:01:20');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int NOT NULL,
  `branch_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `branch_city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `branch_street` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `branch_phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `branch_name`, `branch_city`, `branch_street`, `branch_phone`, `latitude`, `longitude`) VALUES
(1, 'Nazareth Branch', 'Nazareth', 'Main Street 10', '04-0000000', 32.7036110, 35.2955560),
(2, 'Haifa Branch', 'Haifa', 'Herzl Street 25', '04-5551234', 32.8130300, 34.9992800);

-- --------------------------------------------------------

--
-- Table structure for table `branch_stock`
--

CREATE TABLE `branch_stock` (
  `branch_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `branch_stock`
--

INSERT INTO `branch_stock` (`branch_id`, `product_id`, `quantity`) VALUES
(1, 1, 19),
(1, 2, 2),
(1, 3, 19),
(1, 4, 14),
(1, 5, 50),
(1, 11, 17),
(1, 16, 19),
(2, 1, 0),
(2, 2, 3),
(2, 3, 0),
(2, 4, 5),
(2, 5, 2),
(2, 11, 0),
(2, 16, 0);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `uid` int NOT NULL,
  `pid` int NOT NULL,
  `quantity` int NOT NULL,
  `branch_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`uid`, `pid`, `quantity`, `branch_id`) VALUES
(1, 3, 1, NULL),
(4, 3, 1, NULL),
(7, 1, 4, NULL),
(8, 1, 12, NULL),
(8, 2, 1, NULL),
(30, 2, 3, 2),
(33, 1, 2, 1),
(33, 3, 2, 1),
(33, 4, 1, 1),
(33, 11, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `id` int NOT NULL,
  `userid` int NOT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `chat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint NOT NULL,
  `opened` datetime DEFAULT CURRENT_TIMESTAMP,
  `lastr` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`id`, `userid`, `subject`, `chat`, `status`, `opened`, `lastr`) VALUES
(1, 1, 'hello', '[2026-01-09 16:53] USER: hi\n[2026-01-09 16:53] ADMIN: hello\n\n[15:27 29-01-2026] ADMIN: fuck', 1, '2026-01-09 17:53:20', '2026-01-09 17:53:59'),
(2, 1, 'hay', 'USER: how are you\nADMIN: good\n\n[17:26 16-01-2026] ADMIN: bye\n[19:15 28-01-2026] ADMIN: good morning\n[19:19 28-01-2026] ADMIN: hi', 1, '2026-01-09 18:08:52', '2026-01-10 20:21:17'),
(3, 1, 'hay', 'USER: how are you\nADMIN: hello\nADMIN: hello\n', 1, '2026-01-09 18:09:06', '2026-01-10 20:18:40'),
(4, 1, 'hi', 'USER: hi\nADMIN: hello\nADMIN: how \nADMIN: how \nADMIN: how ', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(5, 1, 'how are ', 'USER: hello\nADMIN: good\nADMIN: good\nADMIN: good', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(6, 1, 'good', 'USER: are you good\n[13:09 11-01-2026] ADMIN: yes\r\n\n[13:11 11-01-2026] ADMIN: yes\r\n', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(7, 20, 'aaa', '[19:50 04-04-2026] USER: ggg\n[20:40 05-04-2026] ADMIN: no', 1, '2026-04-04 22:50:36', '2026-04-04 22:50:36'),
(8, 20, 'hi', '[20:48 05-04-2026] USER: no no ', 1, '2026-04-05 23:48:24', '2026-04-05 23:48:24'),
(9, 20, 'bitch', '[20:56 05-04-2026] USER: what time is it now ?', 1, '2026-04-05 23:56:18', '2026-04-05 23:56:18'),
(10, 3, 'hi', '[23:55 26-05-2026] USER: hello bolos ?\n[23:34 16-07-2026] ADMIN: hi im max the admin not bolos!!', 0, '2026-05-27 02:55:58', '2026-05-27 02:55:58'),
(11, 3, 'new user', '[19:08 17-07-2026] USER: im testing', 0, '2026-07-17 22:08:33', '2026-07-17 22:08:33'),
(12, 3, 'testing2', '[19:37 17-07-2026] USER: hi hi', 0, '2026-07-17 22:37:04', '2026-07-17 22:37:04'),
(13, 3, 'testing2', '[19:49 17-07-2026] USER: hi hi\n[20:07 17-07-2026] ADMIN: hiiiiii', 0, '2026-07-17 22:49:36', '2026-07-17 22:49:36'),
(14, 3, 'testing', '[20:13 17-07-2026] USER: hi hi hello', 0, '2026-07-17 23:13:58', '2026-07-17 23:13:58'),
(15, 30, 'my freind is block in the system by admin', '[15:50 31-07-2026] USER: can you please remove the block from my friends account\n[15:51 31-07-2026] ADMIN: absolutely not.', 0, '2026-07-31 18:50:23', '2026-07-31 18:50:23'),
(16, 30, '?', '[15:53 31-07-2026] USER: why so mad\n[15:54 31-07-2026] ADMIN: shush\n[15:56 31-07-2026] ADMIN: shush\n[19:02 31-07-2026] ADMIN: no no \n[19:10 31-07-2026] USER: ??\n[19:10 31-07-2026] USER: aa\n[19:11 31-07-2026] USER: ok\n[19:12 31-07-2026] ADMIN: nooooooo', 0, '2026-07-31 18:53:21', '2026-07-31 19:12:18'),
(17, 30, 'hiiii', '[19:17 31-07-2026] USER: how are you guys\n[19:18 31-07-2026] USER: whats up u guys\n[19:50 31-07-2026] USER: whats up u guys\n[19:50 31-07-2026] USER: whats up u guys\n[19:50 31-07-2026] USER: whats up u guys\n[23:16 01-08-2026] ADMIN: hi\n', 1, '2026-07-31 19:17:47', '2026-08-01 23:16:28'),
(18, 33, 'im scared', '[21:39 11-08-2026] USER: omg', 1, '2026-08-11 21:39:07', '2026-08-11 21:39:07');

-- --------------------------------------------------------

--
-- Table structure for table `couriers`
--

CREATE TABLE `couriers` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `couriers`
--

INSERT INTO `couriers` (`id`, `name`, `phone`) VALUES
(1, 'Ahmed', '050-1111111'),
(2, 'Ali', '050-2222222'),
(3, 'Omar', '050-3333333');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_days_off`
--

CREATE TABLE `doctor_days_off` (
  `id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `off_date` date NOT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doctor_days_off`
--

INSERT INTO `doctor_days_off` (`id`, `doctor_id`, `off_date`, `reason`, `status`) VALUES
(1, 2, '2026-07-17', 'out of town', 1),
(2, 2, '2026-07-19', 'out of town', 1),
(3, 2, '2026-07-20', 'out of town', 2),
(4, 2, '2026-07-20', 'no', 1),
(5, 2, '2026-07-21', 'out of town', 1),
(6, 20, '2026-08-04', 'family trip ', 1),
(7, 20, '2026-08-18', 'I want / two days . of ', 1),
(8, 20, '2026-08-19', 'another day of ////. u know ', 2),
(9, 20, '2026-08-19', 'why???huh', 1),
(10, 20, '2026-08-10', 'aa', 1),
(11, 3, '2026-08-05', 'I cant come to work I have a important date ', 0);

-- --------------------------------------------------------

--
-- Table structure for table `doctor_zoom_links`
--

CREATE TABLE `doctor_zoom_links` (
  `id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `zoom_link` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doctor_zoom_links`
--

INSERT INTO `doctor_zoom_links` (`id`, `doctor_id`, `zoom_link`) VALUES
(1, 2, 'https://zoom.us/j/1234567890?pwd=test123'),
(2, 20, 'https://zoom.us/j/1234567890?pwd=test123'),
(3, 28, 'https://zoom.us/j/1234567890?pwd=test123');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `orderids` int NOT NULL,
  `uid` int NOT NULL,
  `orderdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `cardnumber` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `streetn` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `homen` int NOT NULL,
  `Price` int NOT NULL,
  `delivery_datetime` datetime DEFAULT NULL,
  `courier_id` int DEFAULT NULL,
  `delivery_status` tinyint NOT NULL DEFAULT '0',
  `delivery_method` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `branch_id` int DEFAULT NULL,
  `pickup_time_from` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pickup_time_to` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pickup_status` tinyint NOT NULL DEFAULT '0',
  `pickup_collected_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`orderids`, `uid`, `orderdate`, `city`, `phone`, `cardnumber`, `streetn`, `homen`, `Price`, `delivery_datetime`, `courier_id`, `delivery_status`, `delivery_method`, `branch_id`, `pickup_time_from`, `pickup_time_to`, `pickup_status`, `pickup_collected_at`) VALUES
(1, 1, '2026-01-06 09:19:33', '1', '0525252548', '123131', '', 0, 0, NULL, NULL, 0, '', NULL, NULL, NULL, 0, NULL),
(2, 1, '2026-01-06 09:26:45', '0', '0525252548', '123131', '', 0, 0, NULL, NULL, 0, '', NULL, NULL, NULL, 0, NULL),
(3, 1, '2026-01-08 14:21:06', '0', '0526494004', '2423536', '', 0, 0, NULL, NULL, 0, '', NULL, NULL, NULL, 0, NULL),
(4, 1, '2026-01-11 14:14:52', '0', '0526494004', '2423536', '', 0, 0, NULL, NULL, 0, '', NULL, NULL, NULL, 0, NULL),
(5, 1, '2026-01-11 14:15:14', '0', '0526494004', '2423536', '', 0, 0, NULL, NULL, 0, '', NULL, NULL, NULL, 0, NULL),
(6, 1, '2026-01-16 14:00:55', '0', '0526494004', '2345678923456765', '', 0, 0, NULL, NULL, 0, '', NULL, NULL, NULL, 0, NULL),
(7, 1, '2026-01-16 14:09:45', '0', '0526494004', '2345678987654', '', 0, 0, NULL, NULL, 0, '', NULL, NULL, NULL, 0, NULL),
(8, 1, '2026-01-16 17:12:31', 'Nazareth', '0526494004', '123456654', 'Nazareth israel', 12, 0, NULL, NULL, 0, '', NULL, NULL, NULL, 0, NULL),
(9, 1, '2026-01-16 17:16:49', 'Nazareth', '0526494004', '87654345', 'khanock', 22, 0, NULL, NULL, 0, '', NULL, NULL, NULL, 0, NULL),
(10, 1, '2026-01-16 17:21:31', 'Nazareth', '0526494004', '0987654', 'sfafre', 44, 195, '2026-01-23 18:10:00', 1, 1, '', NULL, NULL, NULL, 0, NULL),
(11, 17, '2026-01-16 18:31:11', 'rame', '052774839', '23456789', 'name', 15, 66, '2026-01-19 10:30:00', 2, 1, '', NULL, NULL, NULL, 0, NULL),
(12, 1, '2026-01-28 14:52:57', 'Nazareth', '0526494004', '1234567890', 'Nazareth israel', 433, 18, NULL, NULL, 0, 'pickup', NULL, NULL, NULL, 0, NULL),
(13, 1, '2026-01-28 19:34:32', 'Nazareth', '0526494004', '3445678967', 'Nazareth israel', 67, 78, NULL, NULL, 0, 'pickup', 1, '07:34', '09:35', 0, NULL),
(14, 1, '2026-01-29 11:04:02', 'Nazareth', '0526494004', '654323456', 'Nazareth israel', 432, 36, '2026-07-12 20:12:00', 2, 1, 'delivery', NULL, NULL, NULL, 0, NULL),
(17, 20, '2026-04-07 19:09:24', 'hfhf', '053828182', '6262848493938282', 'khala', 1, 234, NULL, NULL, 0, 'delivery', NULL, NULL, NULL, 0, NULL),
(18, 3, '2026-05-31 00:45:31', 'gg', '0547639786', '4657283798760000', 'gg', 1, 474, NULL, NULL, 0, 'delivery', NULL, NULL, NULL, 0, NULL),
(19, 3, '2026-05-31 00:48:25', 'dd', '7685749392', '1398329485', 'dd', 2, 43, NULL, NULL, 0, 'delivery', NULL, NULL, NULL, 0, NULL),
(20, 3, '2026-05-31 00:58:43', 'huhu', '0547685467', '2345654334566543', 'jj', 5, 108, NULL, NULL, 0, 'delivery', NULL, NULL, NULL, 0, NULL),
(21, 3, '2026-05-31 01:03:45', 'hah', '096869483', '1010101010110', 'haha', 5, 36, '2026-07-21 20:12:00', 1, 1, 'delivery', NULL, NULL, NULL, 0, NULL),
(22, 21, '2026-07-14 01:16:35', 'hh', '859843573', '121232443555', '67', 678, 217, NULL, NULL, 0, 'delivery', NULL, NULL, NULL, 0, NULL),
(23, 21, '2026-07-14 01:59:01', 'jdjdbdb', '34634636', '535353453', 'jdsncjjdcb', 34634535, 55, NULL, NULL, 0, 'delivery', NULL, NULL, NULL, 0, NULL),
(24, 21, '2026-07-14 02:03:32', 'dhcdhbd', '11223123123', '213123', 'jhsbxhsbx', 111, 18, NULL, NULL, 0, 'delivery', NULL, NULL, NULL, 0, NULL),
(25, 21, '2026-07-14 02:09:23', 'JJ', '019', '9823932', 'ijfuhdu', 58, 108, NULL, NULL, 0, 'pickup', 1, '12:33', '12:06', 0, NULL),
(26, 21, '2026-07-14 16:30:32', 'fbhcbdbc', '849858353434', '6453535353', 'bdchbdhcbd', 84282424, 18, NULL, NULL, 0, 'delivery', NULL, NULL, NULL, 0, NULL),
(27, 21, '2026-07-14 16:42:01', 'sjdnjsdn', '120329393', '4546567', 'dnsdnd', 22, 72, NULL, NULL, 0, 'delivery', NULL, NULL, NULL, 0, NULL),
(28, 21, '2026-07-14 16:52:48', 'ajajja', '8686', '86868686', 'bababa', 86868, 18, NULL, NULL, 0, 'pickup', 1, '04:30', '00:00', 0, NULL),
(29, 21, '2026-07-14 16:58:59', 'fefefefef', '121331312', '1232323', 'evevvev', 12, 52, '2026-07-17 03:10:00', 1, 1, 'delivery', NULL, NULL, NULL, 0, NULL),
(30, 3, '2026-07-17 16:19:25', 'lala', '1111111111', '123456789', 'idk', 17, 36, NULL, NULL, 0, 'pickup', 1, '19:19', '21:19', 0, NULL),
(31, 3, '2026-07-17 16:24:48', 'l', '01753', '147', 'l', 12, 25, NULL, NULL, 0, 'pickup', 1, '21:24', '20:24', 1, '2026-08-03 21:52:35'),
(32, 3, '2026-07-17 16:38:39', 'l', '10240', '147', 'l', 10, 18, NULL, NULL, 0, 'pickup', 1, '13:00', '16:00', 1, '2026-08-02 21:26:25'),
(33, 3, '2026-07-17 16:41:29', 'l', '410', '104', 'l', 10, 18, NULL, NULL, 0, 'delivery', NULL, NULL, NULL, 0, NULL),
(34, 3, '2026-07-17 16:45:36', 'l', '0', '0', 'l', 1, 22, NULL, NULL, 0, 'pickup', 1, '20:45', '22:45', 1, '2026-08-02 21:26:22'),
(35, 30, '2026-07-30 00:19:28', 'shafamro', '0587968493', '123', '34st', 21, 48, NULL, NULL, 0, 'pickup', 1, '12:00', '03:00', 1, '2026-08-02 21:26:19'),
(36, 30, '2026-07-30 00:47:23', 'aa', '0539355486', '1234567891011121', 'sxb', 21, 342, NULL, NULL, 0, 'pickup', 1, '10:30', '14:20', 1, '2026-07-30 03:02:58'),
(37, 30, '2026-08-01 07:15:48', 'ggg', '054354321', '8787878765453421', 'ggg', 6677, 469, NULL, NULL, 0, 'pickup', 1, '12:00', '17:00', 1, '2026-08-01 22:41:49'),
(38, 17, '2026-08-01 21:10:19', 'aa', '0698574832', '2424424424444442', 'aa', 122, 46, NULL, NULL, 0, 'pickup', 2, '12:15', '18:30', 1, '2026-08-02 19:51:41'),
(39, 17, '2026-08-01 21:31:35', 'aaaa', '0543213241', '1234567891234567', 'aaaa', 12, 18, NULL, NULL, 0, 'delivery', 2, NULL, NULL, 0, NULL),
(40, 30, '2026-08-02 06:47:04', 'aa', '0547389229', '2123456789876222', 'aa', 12, 36, '2026-08-04 21:36:00', 2, 1, 'delivery', 1, NULL, NULL, 0, NULL),
(41, 30, '2026-08-02 20:02:36', 'nazreth', '052187654', '9867574747474111', 'boulus 6', 6, 100, '2026-08-03 12:30:00', 1, 1, 'delivery', 1, NULL, NULL, 0, NULL),
(42, 33, '2026-08-04 11:34:40', 'gg', '0976565444', '9999999999999988', 'gg', 43, 68, NULL, NULL, 0, 'pickup', 1, '11:00', '12:30', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ordershistory`
--

CREATE TABLE `ordershistory` (
  `orderid` int NOT NULL,
  `pid` int NOT NULL,
  `quantity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ordershistory`
--

INSERT INTO `ordershistory` (`orderid`, `pid`, `quantity`) VALUES
(1, 2, 1),
(1, 3, 2),
(1, 4, 1),
(2, 1, 1),
(2, 2, 2),
(3, 1, 2),
(4, 2, 2),
(4, 3, 2),
(5, 3, 3),
(6, 3, 4),
(7, 2, 3),
(7, 3, 4),
(10, 1, 3),
(10, 2, 3),
(10, 4, 3),
(11, 2, 2),
(11, 3, 1),
(12, 2, 1),
(13, 2, 1),
(13, 3, 2),
(14, 2, 2),
(17, 3, 3),
(17, 4, 2),
(17, 11, 2),
(18, 1, 8),
(18, 2, 5),
(18, 3, 3),
(18, 4, 3),
(18, 5, 1),
(19, 1, 1),
(19, 2, 1),
(20, 2, 6),
(21, 2, 2),
(22, 1, 1),
(22, 2, 9),
(22, 3, 1),
(27, 2, 4),
(28, 2, 1),
(29, 3, 1),
(29, 4, 1),
(30, 2, 2),
(31, 1, 1),
(32, 2, 1),
(33, 2, 1),
(34, 4, 1),
(35, 2, 1),
(35, 3, 1),
(36, 2, 19),
(37, 1, 1),
(37, 4, 2),
(37, 16, 1),
(38, 2, 1),
(38, 5, 1),
(39, 2, 1),
(40, 2, 2),
(41, 11, 2),
(42, 2, 1),
(42, 11, 1);

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int NOT NULL,
  `doctor_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `expiry_date` date DEFAULT NULL,
  `quantity` int DEFAULT '0',
  `used_quantity` int DEFAULT '0',
  `appointment_id` int DEFAULT NULL,
  `diagnosis` varchar(255) DEFAULT NULL,
  `instructions` varchar(255) DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `doctor_id`, `user_id`, `product_id`, `notes`, `created_at`, `expiry_date`, `quantity`, `used_quantity`, `appointment_id`, `diagnosis`, `instructions`, `follow_up_date`) VALUES
(2, 20, 3, 4, 'he has infections ', '2026-07-13 23:31:49', NULL, 1, 1, 5, NULL, NULL, NULL),
(5, 20, 3, 1, 'hahahah', '2026-07-13 00:57:44', NULL, 1, 1, 5, NULL, NULL, NULL),
(6, 20, 21, 3, 'he has a very bad immune system', '2026-07-14 00:59:23', NULL, 1, 1, 18, NULL, NULL, NULL),
(7, 20, 21, 1, 'to relive the pain ', '2026-07-14 00:59:35', NULL, 1, 1, 18, NULL, NULL, NULL),
(8, 2, 21, 3, 'buy now ', '2026-07-14 02:05:59', NULL, 2, 2, 19, NULL, NULL, NULL),
(9, 2, 21, 4, 'has a rash ', '2026-07-14 16:56:57', NULL, 1, 1, 20, NULL, NULL, NULL),
(10, 2, 3, 1, 'one in the morning after breakfast', '2026-07-15 02:29:05', NULL, 1, 0, 23, NULL, NULL, NULL),
(11, 2, 3, NULL, 'testing', '2026-07-15 03:53:20', NULL, NULL, NULL, 23, NULL, NULL, NULL),
(12, 2, 3, NULL, 'hhh', '2026-07-15 04:24:22', NULL, NULL, NULL, 25, NULL, NULL, NULL),
(13, 2, 30, 1, 'you can have up to once a day ', '2026-07-29 22:48:11', NULL, 3, 0, 35, NULL, NULL, NULL),
(14, 2, 30, 3, 'take one pill a day for the whole month and come back if your still feeling bad ', '2026-07-29 23:19:50', '2026-08-26', 1, 1, 36, NULL, NULL, NULL),
(15, 20, 29, 1, 'once a day', '2026-07-29 23:28:45', '2026-07-29', 1, 0, 37, NULL, NULL, NULL),
(16, 2, 30, 16, 'very very good', '2026-07-31 00:46:44', '2026-07-31', 2, 0, 42, NULL, NULL, NULL),
(17, 3, 30, 4, 'very bad', '2026-08-01 04:53:49', '2026-08-10', 2, 2, 43, NULL, NULL, NULL),
(18, 3, 4, 4, 'haha', '2026-08-01 05:10:45', '2026-08-20', 3, 0, 44, NULL, NULL, NULL),
(19, 3, 4, 1, '123', '2026-08-01 05:11:12', '2026-08-25', 4, 0, 44, NULL, NULL, NULL),
(20, 20, 4, 1, 'aaqaq', '2026-08-01 05:13:54', '2026-08-27', 6, 0, 45, NULL, NULL, NULL),
(21, 20, 4, 3, 'qqq', '2026-08-01 05:14:02', '2026-08-25', 3, 0, 45, NULL, NULL, NULL),
(22, 20, 4, 16, 'eat', '2026-08-01 06:10:17', '2026-08-18', 2, 0, 46, NULL, NULL, NULL),
(23, 20, 4, 4, 'a', '2026-08-01 06:10:47', '2026-08-31', 1, 0, 46, NULL, NULL, NULL),
(24, 20, 30, 16, 'once a month', '2026-08-01 07:13:01', '2026-10-31', 4, 1, 49, NULL, NULL, NULL),
(25, 20, 30, 1, 'for', '2026-08-01 07:13:14', '2026-08-25', 2, 1, 49, NULL, NULL, NULL),
(26, 20, 30, 4, 'aa', '2026-08-01 07:57:18', '2026-08-24', 2, 0, 50, NULL, NULL, NULL),
(27, 2, 3, 16, 'aaa', '2026-08-02 06:49:05', '2026-08-11', 2, 0, 32, NULL, NULL, NULL),
(28, 2, 30, 11, 'אלי מה יש לכה אחי', '2026-08-02 19:58:10', '2026-08-31', 2, 2, 55, NULL, NULL, NULL),
(29, 20, 30, NULL, 'all good dont worry', '2026-08-03 06:02:46', NULL, NULL, NULL, 56, NULL, NULL, NULL),
(30, 2, 33, 11, 'ggg', '2026-08-04 10:37:39', '2026-08-29', 1, 1, 57, NULL, NULL, NULL),
(31, 2, 33, 11, 'a', '2026-08-07 07:41:04', '2026-08-31', 1, 0, 58, NULL, NULL, NULL),
(32, 20, 33, 11, 'aa', '2026-08-07 07:41:36', '2026-08-28', 1, 0, 62, NULL, NULL, NULL),
(33, 2, 33, 3, 'aa', '2026-08-07 11:05:41', '2026-08-19', 2, 0, 60, NULL, NULL, NULL),
(34, 2, 33, 1, '111', '2026-08-07 11:05:49', '2026-08-26', 2, 0, 60, NULL, NULL, NULL),
(35, 2, 33, 16, 'a', '2026-08-10 12:55:32', '2026-08-16', 2, 0, 63, NULL, NULL, NULL),
(36, 3, 33, 4, 'the patient came in with what appeared to be a red and big circle on hes hand', '2026-08-11 18:54:22', '2026-08-31', 1, 0, 59, 'bad infection', 'apply twice a day', '2026-08-15');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `productId` int NOT NULL,
  `productname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `productprice` int NOT NULL,
  `productamount` int NOT NULL,
  `productimage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `requires_prescription` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`productId`, `productname`, `productprice`, `productamount`, `productimage`, `requires_prescription`) VALUES
(1, 'Pain Relief Tablets', 25, 38, 'Pain-Relief.jpg', 1),
(2, 'Cough Syrup', 18, 11, 'Cough Syrup.jpg', 0),
(3, 'Vitamin C', 30, 50, 'vitamin-c.jpg', 1),
(4, 'Antibiotic Cream', 22, 27, 'Antibiotic Cream.jpg', 1),
(5, 'Allergy Pills', 28, 48, 'Allergy Pills.jpg', 0),
(11, 'PAIN PILLS', 50, 0, 'pain pills.avif', 1),
(16, 'Insulin Pen', 400, 100, 'insulin pen.webp', 1);

-- --------------------------------------------------------

--
-- Table structure for table `specialties`
--

CREATE TABLE `specialties` (
  `specialty_id` int NOT NULL,
  `specialty_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `specialties`
--

INSERT INTO `specialties` (`specialty_id`, `specialty_name`) VALUES
(1, 'Cardiology'),
(2, 'Dermatology'),
(10, 'Ear, Nose and Throat'),
(3, 'Family Medicine'),
(4, 'Neurology');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `Id` int NOT NULL,
  `fname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_number` varchar(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `blocked` tinyint(1) NOT NULL DEFAULT '0',
  `admin_blocked` tinyint DEFAULT '0',
  `pass1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pass2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pass3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `dob` date DEFAULT NULL,
  `signup_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ls` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'failed',
  `lastld` datetime DEFAULT NULL,
  `is_doctor` tinyint(1) DEFAULT '0',
  `is_pharmacist` tinyint(1) NOT NULL DEFAULT '0',
  `specialty` int DEFAULT NULL,
  `branch_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`Id`, `fname`, `lname`, `id_number`, `password`, `email`, `address`, `phone`, `blocked`, `admin_blocked`, `pass1`, `pass2`, `pass3`, `is_admin`, `dob`, `signup_date`, `ls`, `lastld`, `is_doctor`, `is_pharmacist`, `specialty`, `branch_id`) VALUES
(1, 'mohammed', 'silawy', '327680146', '333', 'silawymohammed@gmail.com', 'nazareth', '523212567', 0, 0, '333', '211', '123', 0, '2000-01-01', '2026-01-27 21:44:21', 'successful', '2026-08-01 23:15:39', 0, 0, NULL, NULL),
(2, 'bolos', 'aa', '214657538', '155', 'bolos.aa320@gmail.com', 'dir-hanna', '522436443', 0, 0, '122', '123', '4', 0, '2000-01-01', '2026-01-27 21:44:21', 'successful', '2026-08-10 12:41:41', 1, 0, 1, 1),
(3, 'lolo', 'lala', '111111111', 'new', 'areenib112@gmail.com', 'haifa', '524789654', 0, 0, 'new', '', '', 0, '2000-01-01', '2026-01-27 21:44:21', 'successful', '2026-08-11 19:11:00', 1, 0, 1, 1),
(4, 'chen', 'chen', '222222222', '123', 'chen@gmail.com', 'haifa', '529874631', 0, 0, 'chen10', '', '', 0, '2000-01-01', '2026-01-27 21:44:21', 'successful', '2026-08-01 06:15:59', 0, 0, NULL, NULL),
(5, 'max', 'alester', '333333333', 'max123', 'max@gmail.com', 'Nazareth israel', '523671892', 0, 0, '', '', '', 1, '2000-01-01', '2026-01-27 21:44:21', 'successful', '2026-08-03 21:32:27', 0, 0, NULL, NULL),
(7, 'bbb', 'cccc', '444444444', '333', 'bbb@gmail.com', 'tel aviv', '503454367', 1, 0, '', '', '', 1, '2000-01-01', '2026-01-27 21:44:21', 'failed', NULL, 0, 0, NULL, NULL),
(8, 'alex', 'wer', '555555555', '1234', 'asd@gmail.com', 'Nazareth israel', '528374621', 1, 0, '', '', '', 0, '2000-01-01', '2026-01-27 21:44:21', 'successful', '2026-07-13 05:09:16', 0, 0, NULL, NULL),
(16, 'hhh', 'qeq', '666666666', 'Msilawy14/2005', 'hhh@gmail.com', 'Nazareth israel', '526494004', 0, 0, '', '', '', 0, '2000-01-01', '2026-01-27 21:44:21', 'failed', NULL, 0, 0, NULL, NULL),
(17, 'aa', 'aaa', '777777777', '123', 'fadi@gmail.com', 'rame', '523749264', 0, 0, '', '', '', 0, '2000-01-01', '2026-01-27 21:44:21', 'successful', '2026-08-02 18:58:42', 0, 0, NULL, NULL),
(18, 'lester', 'morgan', '888888888', 'morgan12', 'lester@gmail.com', 'tel aviv', '547382548', 0, 0, '', '', '', 0, '2004-02-03', '2026-01-27 21:47:06', 'failed', NULL, 0, 0, NULL, NULL),
(19, 'BBL', 'aaaa', '999999999', 'sPtUHB', 'ivnvvbgb@gmail.com', 'efeff', '546728213', 1, 0, NULL, NULL, NULL, 0, '2026-04-14', '2026-04-03 00:55:22', 'failed', '2026-07-17 03:32:43', 0, 0, NULL, NULL),
(20, 'areen', 'ibra', '327661765', '6675', 'areen12@gmail.com', 'dier', '539355637', 0, 0, '6675', NULL, NULL, 0, '2026-04-10', '2026-04-04 00:39:26', 'successful', '2026-08-11 17:11:57', 1, 0, 3, 1),
(21, 'hahah', 'hehehe', '010101010', '121', 'hahah@gmail.com', NULL, '123456', 0, 0, NULL, NULL, NULL, 0, NULL, '2026-07-11 20:27:48', 'successful', '2026-07-28 21:47:32', 0, 0, NULL, NULL),
(22, 'testing', 't', '020202020', '1234', 'testing@gmail.com', NULL, '522436443', 0, 0, NULL, NULL, NULL, 0, NULL, '2026-07-17 01:31:06', 'failed', NULL, 0, 0, NULL, NULL),
(23, 'bolos', 'ashqar', '030303030', '123', 'bolos@gmail.com', NULL, '522436443', 0, 0, NULL, NULL, NULL, 0, NULL, '2026-07-17 01:31:50', 'failed', NULL, 0, 0, NULL, NULL),
(24, 'testing10', 'testing10', '04040404', '147', 'testing10@gmail.com', NULL, '502222222', 0, 0, NULL, NULL, NULL, 0, NULL, '2026-07-17 02:13:53', 'failed', NULL, 0, 0, NULL, NULL),
(25, 'test3', 'test3', '040404040', '159', 'test3@example.com', NULL, '544444444', 0, 0, NULL, NULL, NULL, 0, NULL, '2026-07-17 02:19:59', 'failed', NULL, 0, 0, NULL, NULL),
(26, 'doctor', 'doctor', '050505050', '145', 'doctor@example.com', NULL, '544444444', 0, 0, NULL, NULL, NULL, 0, NULL, '2026-07-17 02:21:57', 'failed', NULL, 0, 0, NULL, NULL),
(27, 'test2', 'test2', '060606060', '21vPWS', 'test2@example.com', NULL, '544444444', 1, 1, NULL, NULL, NULL, 0, NULL, '2026-07-17 02:27:46', 'failed', NULL, 0, 0, NULL, NULL),
(28, 'rami', 'levi', '070707070', '1254', 'rami@example.com', NULL, '502222222', 0, 1, NULL, NULL, NULL, 0, NULL, '2026-07-17 02:30:07', 'failed', NULL, 1, 0, 4, 1),
(29, 'tete', 'tata', '012012012', '159', 'tete@gmail.com', 'der hanna', '502222222', 0, 0, '159', NULL, NULL, 0, '2007-07-25', '2026-07-17 04:41:32', 'successful', '2026-07-29 23:27:33', 0, 0, NULL, NULL),
(30, 'eli', 'la', '215876908', '111', 'eli@gmail.com', 'haifa', '0529887643', 0, 0, '111', '223', '', 0, '2024-04-02', '2026-07-28 22:05:11', 'successful', '2026-08-03 21:15:33', 0, 0, NULL, 2),
(31, 'GG', 'AA', '924092429', '123', 'GG@gmail.com', NULL, '0547765481', 0, 0, NULL, NULL, NULL, 0, NULL, '2026-07-30 02:21:15', 'successful', '2026-08-16 16:14:14', 0, 1, NULL, 1),
(32, 'hhh', 'aaa', '123456781', '123', 'hh@gmail.com', 'haifa', '0532123344', 0, 0, '123', NULL, NULL, 0, '2025-12-01', '2026-07-31 05:51:22', 'failed', NULL, 0, 0, NULL, NULL),
(33, 'mane', 'gg', '325845123', '111', 'mane@gmail.com', 'carmiel', '0525949321', 0, 0, NULL, NULL, NULL, 0, '2002-11-05', '2026-08-01 22:56:50', 'successful', '2026-08-16 16:29:34', 0, 0, NULL, 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_appointments_request` (`request_id`);

--
-- Indexes for table `appointment_requests`
--
ALTER TABLE `appointment_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `fk_request_user` (`user_id`),
  ADD KEY `fk_request_branch` (`branch_id`),
  ADD KEY `fk_request_specialty` (`specialty_id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branch_stock`
--
ALTER TABLE `branch_stock`
  ADD PRIMARY KEY (`branch_id`,`product_id`),
  ADD KEY `fk_branch_stock_product` (`product_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`uid`,`pid`),
  ADD KEY `pid_fk` (`pid`),
  ADD KEY `fk_cart_branch` (`branch_id`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`userid`);

--
-- Indexes for table `couriers`
--
ALTER TABLE `couriers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doctor_days_off`
--
ALTER TABLE `doctor_days_off`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_days_off_doctor` (`doctor_id`);

--
-- Indexes for table `doctor_zoom_links`
--
ALTER TABLE `doctor_zoom_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`orderids`),
  ADD KEY `courierid` (`courier_id`),
  ADD KEY `fk_orders_branch` (`branch_id`);

--
-- Indexes for table `ordershistory`
--
ALTER TABLE `ordershistory`
  ADD KEY `orderid` (`orderid`),
  ADD KEY `pid` (`pid`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `fk_prescription_appointment` (`appointment_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`productId`);

--
-- Indexes for table `specialties`
--
ALTER TABLE `specialties`
  ADD PRIMARY KEY (`specialty_id`),
  ADD UNIQUE KEY `specialty_name` (`specialty_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `id_number` (`id_number`),
  ADD UNIQUE KEY `id_number_2` (`id_number`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_specialty` (`specialty`),
  ADD KEY `fk_users_branch` (`branch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `appointment_requests`
--
ALTER TABLE `appointment_requests`
  MODIFY `request_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `couriers`
--
ALTER TABLE `couriers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `doctor_days_off`
--
ALTER TABLE `doctor_days_off`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `doctor_zoom_links`
--
ALTER TABLE `doctor_zoom_links`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `orderids` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `productId` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `specialties`
--
ALTER TABLE `specialties`
  MODIFY `specialty_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `Id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`Id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`Id`),
  ADD CONSTRAINT `fk_appointments_request` FOREIGN KEY (`request_id`) REFERENCES `appointment_requests` (`request_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `appointment_requests`
--
ALTER TABLE `appointment_requests`
  ADD CONSTRAINT `fk_request_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_request_specialty` FOREIGN KEY (`specialty_id`) REFERENCES `specialties` (`specialty_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_request_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `branch_stock`
--
ALTER TABLE `branch_stock`
  ADD CONSTRAINT `fk_branch_stock_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_branch_stock_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`productId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pid_fk` FOREIGN KEY (`pid`) REFERENCES `products` (`productId`),
  ADD CONSTRAINT `uid_fk` FOREIGN KEY (`uid`) REFERENCES `users` (`Id`);

--
-- Constraints for table `contact`
--
ALTER TABLE `contact`
  ADD CONSTRAINT `uid` FOREIGN KEY (`userid`) REFERENCES `users` (`Id`);

--
-- Constraints for table `doctor_days_off`
--
ALTER TABLE `doctor_days_off`
  ADD CONSTRAINT `fk_days_off_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`Id`);

--
-- Constraints for table `doctor_zoom_links`
--
ALTER TABLE `doctor_zoom_links`
  ADD CONSTRAINT `fk_zoom_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`Id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `courierid` FOREIGN KEY (`courier_id`) REFERENCES `couriers` (`id`),
  ADD CONSTRAINT `fk_orders_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

--
-- Constraints for table `ordershistory`
--
ALTER TABLE `ordershistory`
  ADD CONSTRAINT `ordershistory_ibfk_1` FOREIGN KEY (`orderid`) REFERENCES `orders` (`orderids`) ON DELETE CASCADE,
  ADD CONSTRAINT `ordershistory_ibfk_2` FOREIGN KEY (`pid`) REFERENCES `products` (`productId`);

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `fk_prescription_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`),
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`Id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`Id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `prescriptions_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`productId`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_users_specialty` FOREIGN KEY (`specialty`) REFERENCES `specialties` (`specialty_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
