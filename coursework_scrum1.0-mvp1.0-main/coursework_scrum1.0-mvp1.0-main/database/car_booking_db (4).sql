-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 01, 2026 at 08:04 AM
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
-- Database: `car_booking_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `pickup_datetime` datetime NOT NULL,
  `dropoff_datetime` datetime NOT NULL,
  `service_type` enum('self-drive','with-driver') NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `car_id`, `pickup_datetime`, `dropoff_datetime`, `service_type`, `total_price`, `status`, `created_at`) VALUES
(2, 1, 2, '2026-03-19 13:43:00', '2026-03-21 13:43:00', 'self-drive', 2400000.00, 'cancelled', '2026-03-19 06:43:48'),
(4, 1, 2, '2026-03-19 13:52:00', '2026-03-21 13:52:00', 'with-driver', 3060000.00, 'cancelled', '2026-03-19 06:52:26'),
(5, 1, 3, '2026-03-19 13:52:00', '2026-03-21 13:52:00', 'with-driver', 2000000.00, 'cancelled', '2026-03-19 06:52:50'),
(6, 1, 3, '2026-03-22 13:53:00', '2026-03-24 13:53:00', 'with-driver', 2000000.00, 'cancelled', '2026-03-19 06:53:23'),
(9, 1, 2, '2026-03-19 14:19:00', '2026-03-21 14:19:00', 'with-driver', 3400000.00, 'completed', '2026-03-19 07:20:00'),
(10, 1, 5, '2026-03-19 14:49:00', '2026-03-20 14:49:00', 'with-driver', 1300000.00, 'completed', '2026-03-19 07:49:24'),
(11, 1, 6, '2026-03-19 15:12:00', '2026-03-21 15:12:00', 'self-drive', 1700000.00, 'completed', '2026-03-19 08:12:31'),
(13, 1, 8, '2026-03-19 15:21:00', '2026-03-21 15:21:00', 'with-driver', 2430000.00, 'completed', '2026-03-19 08:21:10'),
(14, 1, 8, '2026-03-19 15:22:00', '2026-03-21 15:22:00', 'with-driver', 2700000.00, 'completed', '2026-03-19 08:22:53'),
(15, 1, 8, '2026-03-20 09:38:00', '2026-03-21 11:38:00', 'self-drive', 1700000.00, 'completed', '2026-03-20 03:39:13'),
(16, 1, 25, '2026-03-21 17:35:00', '2026-03-21 21:35:00', 'with-driver', 558000.00, 'cancelled', '2026-03-21 10:35:23'),
(17, 1, 6, '2026-03-21 18:59:00', '2026-03-22 18:59:00', 'with-driver', 1215000.00, 'cancelled', '2026-03-21 11:59:57'),
(19, 9, 22, '2026-03-21 22:20:00', '2026-03-23 22:20:00', 'with-driver', 6000000.00, 'completed', '2026-03-21 15:22:50'),
(20, 9, 13, '2026-03-21 22:34:00', '2026-03-22 22:34:00', 'with-driver', 2000000.00, 'completed', '2026-03-21 15:34:57'),
(21, 1, 25, '2026-03-26 18:00:00', '2026-04-01 09:00:00', 'self-drive', 1200000.00, 'cancelled', '2026-03-25 18:22:02'),
(22, 1, 5, '2026-03-26 11:00:00', '2026-03-28 09:00:00', 'with-driver', 2600000.00, 'cancelled', '2026-03-25 18:42:20'),
(23, 1, 5, '2026-04-02 10:00:00', '2026-04-10 09:00:00', 'with-driver', 10400000.00, 'cancelled', '2026-03-25 18:43:30'),
(24, 1, 25, '2026-03-26 10:12:00', '2026-03-26 10:13:00', 'self-drive', 30000.00, 'completed', '2026-03-26 03:08:18'),
(28, 9, 5, '2026-03-26 09:00:00', '2026-03-28 09:00:00', 'with-driver', 1950000.00, 'completed', '2026-03-26 05:04:12'),
(29, 1, 5, '2026-03-29 09:00:00', '2026-03-31 09:00:00', 'self-drive', 1200000.00, 'completed', '2026-03-26 05:25:36'),
(30, 1, 25, '2026-04-05 09:00:00', '2026-04-06 09:00:00', 'with-driver', 700000.00, 'confirmed', '2026-03-26 08:18:37'),
(31, 1, 25, '2026-04-12 09:00:00', '2026-04-14 09:00:00', 'with-driver', 1400000.00, 'confirmed', '2026-03-26 08:19:55'),
(33, 1, 25, '2026-04-01 12:00:00', '2026-04-04 09:00:00', 'with-driver', 1575000.00, 'confirmed', '2026-04-01 04:56:28');

-- --------------------------------------------------------

--
-- Table structure for table `callback_requests`
--

CREATE TABLE `callback_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `incident_id` bigint(20) UNSIGNED DEFAULT NULL,
  `phone_number` varchar(20) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `preferred_time` datetime DEFAULT NULL,
  `status` enum('new','called','no_answer','completed') NOT NULL DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `callback_requests`
--

INSERT INTO `callback_requests` (`id`, `user_id`, `incident_id`, `phone_number`, `note`, `preferred_time`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, '0907495718', NULL, '2026-03-27 18:40:00', 'completed', '2026-03-27 11:41:13', '2026-03-27 12:01:06'),
(2, 1, 2, '0907495718', 'help me', '2026-03-27 18:48:00', 'completed', '2026-03-27 11:48:41', '2026-03-27 12:01:04'),
(3, 1, 3, '0907495718', 'help me please, i dont find you card', '2026-03-27 13:00:17', 'completed', '2026-03-27 12:00:17', '2026-03-27 12:01:00');

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL,
  `model_name` varchar(150) NOT NULL,
  `category` varchar(50) NOT NULL,
  `seats` int(11) NOT NULL,
  `fuel_type` varchar(50) NOT NULL,
  `transmission` enum('auto','manual') NOT NULL,
  `price_per_day` decimal(12,2) NOT NULL,
  `price_per_hour` decimal(12,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `district` varchar(100) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `model_name`, `category`, `seats`, `fuel_type`, `transmission`, `price_per_day`, `price_per_hour`, `image_url`, `description`, `is_available`, `created_at`, `district`, `address`) VALUES
(2, 'Toyota Fortuner', 'SUV', 7, 'Diesel', 'auto', 1200000.00, 150000.00, 'https://www.toyotathanhxuan.vn/wp-content/uploads/2025/12/b4dcef_68a2ebd1dfd84e4485552cfe097037camv2-2.png', 'High-clearance 7-seat SUV, powerful engine, perfect for provincial and mountainous roads.', 1, '2026-03-19 05:35:52', 'District 1', ''),
(3, 'Kia Morning', 'Hatchback', 5, 'Gasoline', 'manual', 500000.00, 60000.00, 'https://cdn.dailyxe.com.vn/image/new-morning-mt-1-319617j.jpg', 'Small and highly flexible car for weaving through crowded urban areas.', 1, '2026-03-19 05:35:52', 'District 3', ''),
(5, 'Toyota Vios 2023', 'Sedan', 5, 'Gasoline', 'auto', 800000.00, 100000.00, 'https://i1-vnexpress.vnecdn.net/2023/05/10/Vios202310jpg-1683690295.jpg?w=750&h=450&q=100&dpr=1&fit=crop&s=BteldbQmWr_H2MzwpRG3DQ', 'National car model, highly durable and fuel-efficient. Perfect for small families on long trips.', 1, '2026-03-19 07:41:52', 'District 7', ''),
(6, 'Honda City RS', 'Sedan', 5, 'Gasoline', 'auto', 850000.00, 110000.00, 'https://otohonda.com.vn/wp-content/uploads/Honda-City-RS-2024-Gia-Lan-Banh-Moi-Nhat-va-Ly-Do-Trong-Chon-Mau-Xe-Nay.jpg', 'Sporty design, the most spacious interior in its class, and an exciting driving experience.', 1, '2026-03-19 07:41:52', 'Binh Thanh District', ''),
(7, 'Mazda 3 Luxury', 'Sedan', 5, 'Gasoline', 'auto', 900000.00, 120000.00, 'https://mazda-danang.com/storage/2021/02/z2316704705315_435d074e0b19895fb9e57cc9495aed35.jpg', 'Stunning KODO design, premium leather interior, equipped with advanced safety technologies.', 1, '2026-03-19 07:41:52', 'Tan Binh District', ''),
(8, 'Kia K3 Premium', 'Sedan', 5, 'Gasoline', 'auto', 850000.00, 110000.00, 'https://cdn.dailyxe.com.vn/image/kia-k3-premium-02-340133j.jpg', 'Youthful, dynamic, and fully loaded with options. Excellent soundproofing and deep AC cooling.', 1, '2026-03-19 07:41:52', 'Phu Nhuan District', ''),
(9, 'Hyundai Elantra', 'Sedan', 5, 'Gasoline', 'auto', 850000.00, 110000.00, 'https://hyundai-thuduc.com.vn/OTO3602400566/files/mau_xe_hyundai/elantra/do.webp', 'Sharp and aggressive appearance with a smooth engine. Newly maintained and ready to go.', 1, '2026-03-19 07:41:52', 'Go Vap District', ''),
(10, 'Honda CR-V', 'SUV', 7, 'Gasoline', 'auto', 1300000.00, 160000.00, 'https://i2-vnexpress.vnecdn.net/2023/10/23/honda-cr-v-l-2020-13-1166-2287-7510-6176-1698035261.jpg?w=1200&h=0&q=100&dpr=1&fit=crop&s=ptmHRFMISKNdyZo7ZuWs0g', 'High ground clearance, flexible 7 seats. Super large trunk space for your luggage.', 1, '2026-03-19 07:41:52', 'District 10', ''),
(13, 'Ford Everest Titanium', 'SUV', 7, 'Diesel', 'auto', 1500000.00, 180000.00, 'https://www.ford.com.vn/content/dam/ecomm/u704/release-3/vn/models/titanium/carousel-banner-1.jpg.renditions.original.png', 'Sturdy chassis, powerful 4x4 diesel engine. The off-road king challenging all terrains.', 1, '2026-03-19 07:41:52', 'District 5', ''),
(16, 'VinFast VF 8', 'SUV', 5, 'Electric', 'auto', 1500000.00, 180000.00, 'https://shop.vinfastauto.com/on/demandware.static/-/Sites-app_vinfast_vn-Library/default/dw1f936f89/reserves/VF8/vf8plus.webp', 'Smart electric car with incredible acceleration. Features Vivi virtual assistant for smart control.', 1, '2026-03-19 07:41:52', 'District 11', ''),
(17, 'VinFast VF e34', 'SUV', 5, 'Electric', 'auto', 1100000.00, 130000.00, 'https://vinfast-vn.vn/wp-content/uploads/2023/10/vinfast-vfe34-green.png', 'Compact and easy to navigate in the city. Zero emissions, zero gas smell, and whisper-quiet.', 1, '2026-03-19 07:41:52', 'District 6', ''),
(18, 'Tesla Model 3', 'Sedan', 5, 'Electric', 'auto', 2000000.00, 250000.00, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQJcdk4fJGId8OzC5CPn44ukPkoiWJTMFnOEg&s', 'The pinnacle of electric vehicles. Massive center screen and advanced AutoPilot technology.', 1, '2026-03-19 07:41:52', 'District 4', ''),
(19, 'Kia Morning GT-Line', 'Hatchback', 5, 'Gasoline', 'auto', 600000.00, 70000.00, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRinR957F8zQzpNgSiYPh9RXC20J4YnNY5Zeg&s', 'Ultra-compact car, easy to turn around and navigate through every narrow city corner.', 1, '2026-03-19 07:41:52', 'District 1', ''),
(20, 'Hyundai Grand i10', 'Hatchback', 5, 'Gasoline', 'manual', 550000.00, 60000.00, 'https://hyundaingocphat.com.vn/wp-content/uploads/2021/11/hyundai-i10-hatchback-2024-new.png', 'Powerful manual transmission. Great for carrying goods or those who love basic driving mechanics.', 1, '2026-03-19 07:41:52', 'District 3', ''),
(21, 'Toyota Wigo 2023', 'Hatchback', 5, 'Gasoline', 'auto', 650000.00, 80000.00, 'https://toyota-phumyhung.vn/wp-content/uploads/2023/06/toyota-wigo-ngoai-that-dsc-7042-copy.jpg', 'Latest generation with extreme AC cooling and the most spacious cabin in the A-segment.', 1, '2026-03-19 07:41:52', 'Binh Thanh District', ''),
(22, 'Mercedes-Benz C300 AMG', 'Sedan', 5, 'Gasoline', 'auto', 2500000.00, 300000.00, 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=800&q=80', 'Business class elegance. Built-in air balance perfume and 64-color ambient lighting.', 1, '2026-03-19 07:41:52', 'Tan Phu District', ''),
(23, 'BMW 330i M Sport', 'Sedan', 5, 'Gasoline', 'auto', 2600000.00, 320000.00, 'https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=800&q=80', 'Built for speed lovers. Features M Sport steering wheel and extremely precise handling.', 1, '2026-03-19 07:41:52', 'Binh Tan District', ''),
(24, 'Porsche Macan', 'SUV', 5, 'Gasoline', 'auto', 3000000.00, 400000.00, 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=800&q=80', 'Luxurious and sporty. Full-time AWD system providing ultimate road grip and performance.', 1, '2026-03-19 07:41:52', 'Thu Duc City', ''),
(25, 'honda vario', 'sedan', 2, 'petrol', '', 200000.00, 30000.00, 'https://cdn.motor1.com/images/mgl/G3ZJLE/s1/honda-launches-the-vario-125-scooter-in-the-malaysian-market.jpg', 'power bike, ...', 1, '2026-03-19 09:10:24', 'District 7', '');

-- --------------------------------------------------------

--
-- Table structure for table `incidents`
--

CREATE TABLE `incidents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_code` varchar(30) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `channel` enum('web_form','phone','callback','live_chat') NOT NULL DEFAULT 'web_form',
  `category` enum('booking_error','vehicle_issue','payment','app_bug','other') NOT NULL DEFAULT 'other',
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `subject` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `status` enum('new','open','in_progress','pending_user','resolved','closed') NOT NULL DEFAULT 'new',
  `assigned_admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `incidents`
--

INSERT INTO `incidents` (`id`, `ticket_code`, `user_id`, `booking_id`, `channel`, `category`, `priority`, `subject`, `description`, `status`, `assigned_admin_id`, `created_at`, `updated_at`, `resolved_at`) VALUES
(1, 'INC-2026-777612', 1, 31, 'web_form', 'other', 'medium', 'Bad sevice', 'aaaaaaaaaaaaaaaa', 'closed', 1, '2026-03-27 11:40:19', '2026-03-27 12:01:23', NULL),
(2, 'INC-2026-245305', 1, NULL, 'callback', 'other', 'medium', 'Callback request from customer', 'help me', 'closed', 1, '2026-03-27 11:48:41', '2026-03-27 12:01:45', NULL),
(3, 'INC-2026-857774', 1, NULL, 'callback', 'other', 'medium', 'Callback request from customer', 'help me please, i dont find you card', 'closed', NULL, '2026-03-27 12:00:17', '2026-03-27 12:02:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `incident_messages`
--

CREATE TABLE `incident_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `incident_id` bigint(20) UNSIGNED NOT NULL,
  `sender_type` enum('user','admin','system') NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `incident_messages`
--

INSERT INTO `incident_messages` (`id`, `incident_id`, `sender_type`, `sender_id`, `message`, `created_at`) VALUES
(1, 1, 'system', NULL, 'Incident created via web form.', '2026-03-27 11:40:19'),
(2, 1, 'system', NULL, 'Status changed from new to open.', '2026-03-27 11:46:35'),
(3, 1, 'admin', 1, 'bbbbbbbbbbbbbb', '2026-03-27 11:46:49'),
(4, 1, 'user', 1, 'cccccccccccc', '2026-03-27 11:47:30'),
(5, 1, 'user', 1, 'tttttttttt', '2026-03-27 11:47:42'),
(6, 2, 'system', NULL, 'Callback request linked to this incident.', '2026-03-27 11:48:41'),
(7, 2, 'system', NULL, 'Status changed from new to pending_user.', '2026-03-27 11:49:45'),
(8, 3, 'system', NULL, 'Callback request linked to this incident.', '2026-03-27 12:00:17'),
(9, 3, 'system', NULL, 'Callback status changed from new to completed.', '2026-03-27 12:01:00'),
(10, 2, 'system', NULL, 'Callback status changed from new to completed.', '2026-03-27 12:01:04'),
(11, 1, 'system', NULL, 'Status changed from open to closed.', '2026-03-27 12:01:23'),
(12, 2, 'system', NULL, 'Status changed from pending_user to closed.', '2026-03-27 12:01:45'),
(13, 3, 'system', NULL, 'Status changed from new to closed.', '2026-03-27 12:02:11');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_otps`
--

CREATE TABLE `password_reset_otps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `request_ip` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_otps`
--

INSERT INTO `password_reset_otps` (`id`, `user_id`, `email`, `otp_code`, `expires_at`, `used_at`, `attempts`, `created_at`, `request_ip`) VALUES
(1, 12, 'tantvgcs220674@fpt.edu.vn', '796812', '2026-03-27 07:41:18', '2026-03-27 13:37:29', 0, '2026-03-27 06:36:18', '::1'),
(2, 12, 'tantvgcs220674@fpt.edu.vn', '611075', '2026-03-27 07:42:29', '2026-03-27 13:46:44', 0, '2026-03-27 06:37:29', '::1'),
(3, 12, 'tantvgcs220674@fpt.edu.vn', '124916', '2026-03-27 07:51:44', '2026-03-27 13:49:05', 0, '2026-03-27 06:46:44', '::1'),
(4, 12, 'tantvgcs220674@fpt.edu.vn', '901265', '2026-03-27 07:54:05', '2026-03-27 13:59:19', 0, '2026-03-27 06:49:05', '::1'),
(5, 12, 'tantvgcs220674@fpt.edu.vn', '983905', '2026-03-27 14:04:19', '2026-03-27 13:59:29', 0, '2026-03-27 06:59:19', '::1'),
(6, 12, 'tantvgcs220674@fpt.edu.vn', '528258', '2026-03-27 14:05:16', '2026-03-27 14:00:40', 0, '2026-03-27 07:00:16', '::1'),
(7, 1, 'tan0979876976@gmail.com', '853300', '2026-03-27 14:08:04', '2026-03-27 14:03:50', 0, '2026-03-27 07:03:04', '::1'),
(8, 1, 'tan0979876976@gmail.com', '747835', '2026-03-27 14:23:14', '2026-03-27 14:18:32', 0, '2026-03-27 07:18:14', '::1'),
(9, 1, 'tan0979876976@gmail.com', '216044', '2026-03-27 14:26:27', '2026-03-27 14:21:40', 0, '2026-03-27 07:21:27', '::1'),
(10, 1, 'tan0979876976@gmail.com', '650665', '2026-03-27 14:27:33', '2026-03-27 14:22:50', 0, '2026-03-27 07:22:33', '::1'),
(11, 12, 'tantvgcs220674@fpt.edu.vn', '518687', '2026-03-27 14:28:24', '2026-03-27 14:23:56', 0, '2026-03-27 07:23:24', '::1'),
(12, 1, 'tan0979876976@gmail.com', '941792', '2026-03-27 14:35:54', NULL, 0, '2026-03-27 07:30:54', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `payment_method` enum('cash','bank_transfer','credit_card','momo') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_status` enum('pending','completed','failed') DEFAULT 'completed',
  `transaction_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `payment_method`, `amount`, `payment_status`, `transaction_id`, `created_at`) VALUES
(1, 2, 'bank_transfer', 2400000.00, 'completed', 'TXN69BB9B2C5F5D01773902636', '2026-03-19 06:43:56'),
(3, 4, 'cash', 3060000.00, 'completed', 'TXN69BB9D2BE7A141773903147', '2026-03-19 06:52:27'),
(4, 5, 'bank_transfer', 2000000.00, 'completed', 'TXN69BB9D458F25D1773903173', '2026-03-19 06:52:53'),
(5, 6, 'momo', 2000000.00, 'completed', 'TXN69BB9D655221F1773903205', '2026-03-19 06:53:25'),
(8, 9, 'cash', 3400000.00, 'completed', 'TXN69BBA3A13BED01773904801', '2026-03-19 07:20:01'),
(9, 10, 'cash', 1300000.00, 'completed', 'TXN69BBAA85E87D31773906565', '2026-03-19 07:49:25'),
(10, 11, 'bank_transfer', 1700000.00, 'completed', 'TXN69BBAFF0D72251773907952', '2026-03-19 08:12:32'),
(12, 13, 'bank_transfer', 2430000.00, 'completed', 'TXN69BBB1F9666E51773908473', '2026-03-19 08:21:13'),
(13, 14, 'bank_transfer', 2700000.00, 'completed', 'TXN69BBB2603AAB21773908576', '2026-03-19 08:22:56'),
(14, 15, 'cash', 99000.00, 'completed', 'TXN69BCC162DDC231773977954', '2026-03-20 03:39:14'),
(15, 16, 'credit_card', 558000.00, 'completed', 'TXN69BE747BB9FC91774089339', '2026-03-21 10:35:39'),
(16, 17, 'cash', 1215000.00, 'completed', 'TXN69BE88613FD781774094433', '2026-03-21 12:00:33'),
(18, 19, 'cash', 6750000.00, 'completed', 'TXN69BEB7D177A7C1774106577', '2026-03-21 15:22:57'),
(19, 21, 'cash', 1200000.00, 'completed', 'TXN69C427CB2F4271774462923', '2026-03-25 18:22:03'),
(20, 22, 'cash', 2400000.00, 'completed', 'TXN69C42C8E1E5A01774464142', '2026-03-25 18:42:22'),
(21, 23, 'cash', 800000.00, 'completed', 'TXN69C42CD32DE711774464211', '2026-03-25 18:43:31'),
(22, 24, 'cash', 30000.00, 'completed', 'TXN69C4A325041841774494501', '2026-03-26 03:08:21'),
(26, 28, 'cash', 1950000.00, 'completed', 'TXN69C4BE4DEFA901774501453', '2026-03-26 05:04:13'),
(28, 33, 'cash', 1575000.00, 'completed', 'TXN69CCA57C17CAB1775019388', '2026-04-01 04:56:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `is_active` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `membership_tier` enum('new','loyal','vip') DEFAULT 'new',
  `otp_code` varchar(6) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `phone`, `password_hash`, `address`, `avatar_url`, `role`, `is_active`, `created_at`, `updated_at`, `membership_tier`, `otp_code`, `is_verified`) VALUES
(1, 'Minh Hoang', 'tan0979876976@gmail.com', '0907495718', '$2y$10$WMTlvwW19Ip8SYqvXgtCpOVYikR9oQYkLqyQ.u5XwqFCryd4BujCC', NULL, NULL, 'admin', 0, '2026-03-19 05:20:41', '2026-03-27 07:23:05', 'vip', NULL, 0),
(9, 'Nguyễn Thị Như Phúc', 'nguyenthinhuphuc06@gmail.com', '0393507177', '$2y$10$uoQGsKbtmE4BXedjx0WnFOR6udtb2I716PQQT6VaLKCsyA/M550yy', NULL, NULL, 'customer', 1, '2026-03-21 15:16:53', '2026-03-21 15:22:27', 'vip', NULL, 1),
(12, 'ben ne', 'tantvgcs220674@fpt.edu.vn', '0907495714', '$2y$10$NW6Cl9IBEYowR4J1zSW3L.7luIc3sRpVMvGhMQFRIm1iz.v0JhI7.', NULL, NULL, 'customer', 1, '2026-03-27 06:31:59', '2026-03-27 07:24:09', 'new', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_percent` decimal(5,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `required_tier` enum('all','new','loyal','vip') DEFAULT 'all'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`id`, `code`, `discount_percent`, `is_active`, `created_at`, `required_tier`) VALUES
(1, 'WELCOME10', 10.00, 1, '2026-03-19 07:45:20', 'all'),
(2, 'LOYAL15', 15.00, 1, '2026-03-19 07:45:20', 'loyal'),
(3, 'VIPMAX25', 25.00, 1, '2026-03-19 07:45:20', 'vip');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `car_id` (`car_id`);

--
-- Indexes for table `callback_requests`
--
ALTER TABLE `callback_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_callback_user_id` (`user_id`),
  ADD KEY `idx_callback_status` (`status`),
  ADD KEY `fk_callback_incident` (`incident_id`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `incidents`
--
ALTER TABLE `incidents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_ticket_code` (`ticket_code`),
  ADD KEY `idx_incidents_user_id` (`user_id`),
  ADD KEY `idx_incidents_booking_id` (`booking_id`),
  ADD KEY `idx_incidents_status` (`status`),
  ADD KEY `idx_incidents_priority` (`priority`),
  ADD KEY `idx_incidents_channel` (`channel`),
  ADD KEY `idx_incidents_created_at` (`created_at`);

--
-- Indexes for table `incident_messages`
--
ALTER TABLE `incident_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_incident_messages_incident_id` (`incident_id`),
  ADD KEY `idx_incident_messages_created_at` (`created_at`);

--
-- Indexes for table `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_password_reset_otps_user_id` (`user_id`),
  ADD KEY `idx_password_reset_otps_email` (`email`),
  ADD KEY `idx_password_reset_otps_expires_at` (`expires_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `callback_requests`
--
ALTER TABLE `callback_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `incidents`
--
ALTER TABLE `incidents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `incident_messages`
--
ALTER TABLE `incident_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `callback_requests`
--
ALTER TABLE `callback_requests`
  ADD CONSTRAINT `fk_callback_incident` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_callback_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `incidents`
--
ALTER TABLE `incidents`
  ADD CONSTRAINT `fk_incidents_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `incident_messages`
--
ALTER TABLE `incident_messages`
  ADD CONSTRAINT `fk_incident_messages_incident` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  ADD CONSTRAINT `fk_password_reset_otps_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
