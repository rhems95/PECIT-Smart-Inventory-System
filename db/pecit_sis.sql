-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 25, 2026 at 07:30 AM
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
-- Database: `pecit_sis`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `priority` enum('low','normal','high') NOT NULL DEFAULT 'normal',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `published_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `priority`, `is_active`, `published_at`, `expires_at`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Welcome to PECIT Smart Inventory System', 'Use this portal to request supplies, manage inventory, and track purchases. Contact Supply Personnel for stock questions.', 'high', 1, '2026-07-24 07:10:30', NULL, 1, '2026-07-24 07:10:30', '2026-07-24 07:10:30');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `model_type` varchar(255) DEFAULT NULL,
  `model_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `model_type`, `model_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 4, 'supply_request.created', 'App\\Models\\SupplyRequest', 1, NULL, '{\"request_number\":\"REQ-PCR3LYIW\",\"user_id\":4,\"department_id\":1,\"type\":\"faculty\",\"status\":\"pending\",\"purpose\":\"pang sulat lang po. matsalam\",\"updated_at\":\"2026-07-24T15:55:11.000000Z\",\"created_at\":\"2026-07-24T15:55:11.000000Z\",\"id\":1,\"total_amount\":\"225.00\",\"items\":[{\"id\":1,\"request_id\":1,\"inventory_id\":2,\"quantity_requested\":5,\"quantity_approved\":null,\"quantity_released\":0,\"unit_price\":\"45.00\",\"subtotal\":\"225.00\",\"created_at\":\"2026-07-24T15:55:11.000000Z\",\"updated_at\":\"2026-07-24T15:55:11.000000Z\"}]}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 07:55:11', '2026-07-24 07:55:11'),
(2, 5, 'purchase.created', 'App\\Models\\PurchaseRequest', 1, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 08:03:43', '2026-07-24 08:03:43'),
(3, 2, 'purchase.payment_verified', 'App\\Models\\PurchaseRequest', 1, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 08:05:05', '2026-07-24 08:05:05'),
(4, 2, 'supply_request.accounting_review', 'App\\Models\\SupplyRequest', 1, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 08:05:14', '2026-07-24 08:05:14'),
(5, 1, 'supply_request.approved', 'App\\Models\\SupplyRequest', 1, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 08:08:49', '2026-07-24 08:08:49'),
(6, 5, 'purchase.created', 'App\\Models\\PurchaseRequest', 2, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 08:14:26', '2026-07-24 08:14:26'),
(7, 2, 'purchase.payment_verified', 'App\\Models\\PurchaseRequest', 2, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 08:15:54', '2026-07-24 08:15:54'),
(8, 3, 'supply_request.released', 'App\\Models\\SupplyRequest', 1, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 08:18:00', '2026-07-24 08:18:00'),
(9, 3, 'purchase.released', 'App\\Models\\PurchaseRequest', 2, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 08:26:28', '2026-07-24 08:26:28'),
(10, 3, 'purchase.released', 'App\\Models\\PurchaseRequest', 1, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 08:26:55', '2026-07-24 08:26:55'),
(11, 4, 'supply_request.created', 'App\\Models\\SupplyRequest', 2, NULL, '{\"request_number\":\"REQ-GMWTCZAW\",\"user_id\":4,\"department_id\":1,\"type\":\"faculty\",\"status\":\"pending\",\"purpose\":\"please hatag\",\"updated_at\":\"2026-07-24T16:31:44.000000Z\",\"created_at\":\"2026-07-24T16:31:44.000000Z\",\"id\":2,\"total_amount\":\"285.00\",\"items\":[{\"id\":2,\"request_id\":2,\"inventory_id\":1,\"quantity_requested\":1,\"quantity_approved\":null,\"quantity_released\":0,\"unit_price\":\"285.00\",\"subtotal\":\"285.00\",\"created_at\":\"2026-07-24T16:31:44.000000Z\",\"updated_at\":\"2026-07-24T16:31:44.000000Z\"}]}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 08:31:45', '2026-07-24 08:31:45'),
(12, 2, 'supply_request.accounting_review', 'App\\Models\\SupplyRequest', 2, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 08:32:19', '2026-07-24 08:32:19'),
(13, 1, 'supply_request.approved', 'App\\Models\\SupplyRequest', 2, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 08:33:58', '2026-07-24 08:33:58'),
(14, 3, 'supply_request.released', 'App\\Models\\SupplyRequest', 2, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 08:35:16', '2026-07-24 08:35:16'),
(15, 6, 'supply_request.created', 'App\\Models\\SupplyRequest', 3, NULL, '{\"request_number\":\"REQ-JYDE6HY8\",\"user_id\":6,\"department_id\":9,\"type\":\"faculty\",\"status\":\"pending\",\"purpose\":\"mang guna me\",\"updated_at\":\"2026-07-24T16:41:10.000000Z\",\"created_at\":\"2026-07-24T16:41:10.000000Z\",\"id\":3,\"total_amount\":\"3600.00\",\"items\":[{\"id\":3,\"request_id\":3,\"inventory_id\":7,\"quantity_requested\":20,\"quantity_approved\":null,\"quantity_released\":0,\"unit_price\":\"180.00\",\"subtotal\":\"3600.00\",\"created_at\":\"2026-07-24T16:41:10.000000Z\",\"updated_at\":\"2026-07-24T16:41:10.000000Z\"}]}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 08:41:10', '2026-07-24 08:41:10'),
(16, 2, 'supply_request.accounting_review', 'App\\Models\\SupplyRequest', 3, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 08:42:44', '2026-07-24 08:42:44'),
(17, 1, 'supply_request.approved', 'App\\Models\\SupplyRequest', 3, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 08:43:38', '2026-07-24 08:43:38'),
(18, 3, 'supply_request.released', 'App\\Models\\SupplyRequest', 3, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 08:44:31', '2026-07-24 08:44:31'),
(19, 7, 'purchase.created', 'App\\Models\\PurchaseRequest', 3, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 19:08:39', '2026-07-24 19:08:39'),
(20, 2, 'purchase.payment_verified', 'App\\Models\\PurchaseRequest', 3, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 19:10:50', '2026-07-24 19:10:50'),
(21, 3, 'purchase.released', 'App\\Models\\PurchaseRequest', 3, NULL, '{\"deducted\":[\"Whiteboard Eraser x1\"]}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 19:11:56', '2026-07-24 19:11:56'),
(22, 6, 'supply_request.created', 'App\\Models\\SupplyRequest', 4, NULL, '{\"request_number\":\"REQ-2YCLVJ4C\",\"user_id\":6,\"department_id\":9,\"type\":\"faculty\",\"status\":\"pending\",\"purpose\":\"for visitors.\",\"updated_at\":\"2026-07-25T04:05:07.000000Z\",\"created_at\":\"2026-07-25T04:05:07.000000Z\",\"id\":4,\"total_amount\":\"10500.00\",\"items\":[{\"id\":4,\"request_id\":4,\"inventory_id\":8,\"quantity_requested\":3,\"quantity_approved\":null,\"quantity_released\":0,\"unit_price\":\"3500.00\",\"subtotal\":\"10500.00\",\"created_at\":\"2026-07-25T04:05:07.000000Z\",\"updated_at\":\"2026-07-25T04:05:07.000000Z\"}]}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 20:05:15', '2026-07-24 20:05:15'),
(23, 2, 'supply_request.accounting_review', 'App\\Models\\SupplyRequest', 4, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 20:10:35', '2026-07-24 20:10:35'),
(24, 1, 'supply_request.approved', 'App\\Models\\SupplyRequest', 4, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 20:12:02', '2026-07-24 20:12:02'),
(25, 3, 'supply_request.released', 'App\\Models\\SupplyRequest', 4, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 20:13:57', '2026-07-24 20:13:57'),
(26, 7, 'purchase.created', 'App\\Models\\PurchaseRequest', 4, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 20:46:30', '2026-07-24 20:46:30'),
(27, 2, 'purchase.payment_verified', 'App\\Models\\PurchaseRequest', 4, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 20:47:33', '2026-07-24 20:47:33'),
(28, 3, 'purchase.released', 'App\\Models\\PurchaseRequest', 4, NULL, '{\"deducted\":[\"Bottled Water 500ml x1\"]}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 20:48:48', '2026-07-24 20:48:48');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('pecit-smart-inventory-system-cache-admin@edu.ph|127.0.0.1', 'i:1;', 1784906065),
('pecit-smart-inventory-system-cache-admin@edu.ph|127.0.0.1:timer', 'i:1784906065;', 1784906065),
('pecit-smart-inventory-system-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:12:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:14:\"inventory.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:16:\"inventory.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:15:\"requests.submit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:15:\"requests.review\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:16:\"requests.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:16:\"requests.release\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:18:\"purchases.checkout\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:16:\"purchases.verify\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:12:\"users.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:12:\"reports.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:10:\"audit.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:20:\"announcements.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:5:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:13:\"Administrator\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:10:\"Accounting\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:16:\"Supply Personnel\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:7:\"Faculty\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";i:5;s:1:\"b\";s:7:\"Student\";s:1:\"c\";s:3:\"web\";}}}', 1784993573);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Office Supplies', 'office-supplies', 'Office Supplies for PECIT campuses.', 1, '2026-07-24 06:42:41', '2026-07-24 06:42:41'),
(2, 'Classroom Supplies', 'classroom-supplies', 'Classroom Supplies for PECIT campuses.', 1, '2026-07-24 06:42:41', '2026-07-24 06:42:41'),
(3, 'Laboratory Supplies', 'laboratory-supplies', 'Laboratory Supplies for PECIT campuses.', 1, '2026-07-24 06:42:41', '2026-07-24 06:42:41'),
(4, 'Computer Supplies', 'computer-supplies', 'Computer Supplies for PECIT campuses.', 1, '2026-07-24 06:42:41', '2026-07-24 06:42:41'),
(5, 'Cleaning Supplies', 'cleaning-supplies', 'Cleaning Supplies for PECIT campuses.', 1, '2026-07-24 06:42:41', '2026-07-24 06:42:41'),
(6, 'Pantry Supplies', 'pantry-supplies', 'Pantry Supplies for PECIT campuses.', 1, '2026-07-24 06:42:41', '2026-07-24 06:42:41'),
(7, 'Maintenance Supplies', 'maintenance-supplies', 'Maintenance Supplies for PECIT campuses.', 1, '2026-07-24 06:42:41', '2026-07-24 06:42:41'),
(8, 'Furniture', 'furniture', 'Furniture for PECIT campuses.', 1, '2026-07-24 06:42:41', '2026-07-24 06:42:41'),
(9, 'Others', 'others', 'Others for PECIT campuses.', 1, '2026-07-24 06:42:41', '2026-07-24 06:42:41'),
(10, 'Shirts', 'shirts', NULL, 1, '2026-07-24 07:47:35', '2026-07-24 07:47:35');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `code`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'College of Engineering', 'COLLEG', NULL, 1, '2026-07-24 06:42:41', '2026-07-24 06:42:41'),
(3, 'College of Engineering', 'COE', NULL, 1, '2026-07-24 06:43:04', '2026-07-24 06:43:04'),
(4, 'College of Information Technology', 'CIT', NULL, 1, '2026-07-24 06:43:04', '2026-07-24 06:43:04'),
(5, 'College of Business', 'COB', NULL, 1, '2026-07-24 06:43:04', '2026-07-24 06:43:04'),
(6, 'Senior High School', 'SHS', NULL, 1, '2026-07-24 06:43:04', '2026-07-24 06:43:04'),
(7, 'Administration', 'ADMIN', NULL, 1, '2026-07-24 06:43:04', '2026-07-24 06:43:04'),
(8, 'Supply Office', 'SUPPLY', NULL, 1, '2026-07-24 06:43:04', '2026-07-24 06:43:04'),
(9, 'College of Computer Studies', 'CCS', NULL, 1, '2026-07-24 07:53:35', '2026-07-24 07:53:35');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
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
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_code` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `unit` varchar(255) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `reserved_quantity` int(11) NOT NULL DEFAULT 0,
  `minimum_stock` int(11) NOT NULL DEFAULT 10,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('available','low_stock','out_of_stock','discontinued') NOT NULL DEFAULT 'available',
  `barcode` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `item_code`, `item_name`, `description`, `category_id`, `unit`, `unit_price`, `quantity`, `reserved_quantity`, `minimum_stock`, `location`, `status`, `barcode`, `created_at`, `updated_at`) VALUES
(1, 'PECIT-BONDPAPE', 'Bond Paper A4', 'PECIT standard Bond Paper A4', 1, 'ream', 285.00, 119, 0, 30, 'Supply Room A', 'available', NULL, '2026-07-24 06:43:04', '2026-07-24 08:35:16'),
(2, 'PECIT-BOARDMAR', 'Board Marker (Black)', 'PECIT standard Board Marker (Black)', 2, 'piece', 45.00, 80, 0, 25, 'Supply Room A', 'available', NULL, '2026-07-24 06:43:04', '2026-07-24 08:18:00'),
(3, 'PECIT-WHITEBOA', 'Whiteboard Eraser', 'PECIT standard Whiteboard Eraser', 2, 'piece', 35.00, 39, 0, 15, 'Supply Room A', 'available', NULL, '2026-07-24 06:43:04', '2026-07-24 19:11:50'),
(4, 'PECIT-BOTTLEDW', 'Bottled Water 500ml', 'PECIT standard Bottled Water 500ml', 6, 'case', 250.00, 58, 0, 20, 'Pantry', 'available', NULL, '2026-07-24 06:43:04', '2026-07-24 20:48:44'),
(5, 'PECIT-ETHERNET', 'Ethernet Cable Cat6', 'PECIT standard Ethernet Cable Cat6', 4, 'piece', 120.00, 35, 0, 10, 'IT Stock Room', 'available', NULL, '2026-07-24 06:43:04', '2026-07-24 06:43:04'),
(6, 'PECIT-DISINFEC', 'Disinfectant Spray', 'PECIT standard Disinfectant Spray', 5, 'bottle', 95.00, 27, 0, 12, 'Janitorial', 'available', NULL, '2026-07-24 06:43:04', '2026-07-24 08:26:28'),
(7, 'PECIT-LABORATO', 'Laboratory Gloves', 'PECIT standard Laboratory Gloves', 3, 'box', 180.00, 2, 0, 8, 'Lab Store', 'low_stock', NULL, '2026-07-24 06:43:04', '2026-07-24 08:44:31'),
(8, 'PECIT-OFFICECH', 'Office Chair', 'PECIT standard Office Chair', 8, 'unit', 3500.00, 5, 0, 2, 'Warehouse', 'available', NULL, '2026-07-24 06:43:04', '2026-07-24 20:13:53');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
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
-- Table structure for table `job_batches`
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
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_07_24_141456_create_permission_tables', 1),
(5, '2026_07_24_150001_add_fields_to_users_table', 2),
(6, '2026_07_24_150002_create_departments_table', 2),
(7, '2026_07_24_150003_create_categories_table', 2),
(8, '2026_07_24_150005_create_inventory_table', 2),
(9, '2026_07_24_150006_create_requests_table', 2),
(10, '2026_07_24_150007_create_request_items_table', 2),
(11, '2026_07_24_150008_create_purchase_requests_table', 2),
(12, '2026_07_24_150009_create_payments_table', 2),
(13, '2026_07_24_150010_create_transactions_table', 2),
(14, '2026_07_24_150011_create_stock_logs_table', 2),
(15, '2026_07_24_150012_create_psis_notifications_table', 2),
(16, '2026_07_24_150013_create_audit_logs_table', 2),
(17, '2026_07_24_150014_create_announcements_table', 2),
(18, '2026_07_24_150015_add_users_department_foreign_key', 2),
(19, '2026_08_06_000001_drop_suppliers_from_inventory', 3);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(3, 'App\\Models\\User', 3),
(4, 'App\\Models\\User', 4),
(4, 'App\\Models\\User', 6),
(5, 'App\\Models\\User', 5),
(5, 'App\\Models\\User', 7);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_number` varchar(255) NOT NULL,
  `purchase_request_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(255) NOT NULL DEFAULT 'cash',
  `receipt_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `reference_number`, `purchase_request_id`, `user_id`, `amount`, `status`, `payment_method`, `receipt_path`, `notes`, `verified_by`, `verified_at`, `created_at`, `updated_at`) VALUES
(1, 'PAY-NR7GEID78G', 1, 5, 250.00, 'verified', 'over_the_counter', NULL, NULL, 2, '2026-07-24 08:05:05', '2026-07-24 08:03:43', '2026-07-24 08:05:05'),
(2, 'PAY-UQSNV6OLIK', 2, 5, 95.00, 'verified', 'over_the_counter', 'receipts/rsQndrd2mnb4MYSbAoivlz3hEdr2fQJj3QDVwQ5z.png', NULL, 2, '2026-07-24 08:15:54', '2026-07-24 08:14:26', '2026-07-24 08:15:54'),
(3, 'PAY-N9UC5YVLSH', 3, 7, 35.00, 'verified', 'over_the_counter', 'receipts/EK9RRpZOqe9TvVQTVdwf3xoww0YAGFnsIvLwvjcf.png', NULL, 2, '2026-07-24 19:10:43', '2026-07-24 19:08:19', '2026-07-24 19:10:43'),
(4, 'PAY-GFM1XOMHD4', 4, 7, 250.00, 'verified', 'over_the_counter', 'receipts/hteSJRIKADev5q5MROhyBF9NMjcVCF50rP9WCxqi.pdf', NULL, 2, '2026-07-24 20:47:26', '2026-07-24 20:46:25', '2026-07-24 20:47:26');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'inventory.view', 'web', '2026-07-24 06:41:04', '2026-07-24 06:41:04'),
(2, 'inventory.manage', 'web', '2026-07-24 06:41:04', '2026-07-24 06:41:04'),
(3, 'requests.submit', 'web', '2026-07-24 06:41:04', '2026-07-24 06:41:04'),
(4, 'requests.review', 'web', '2026-07-24 06:41:04', '2026-07-24 06:41:04'),
(5, 'requests.approve', 'web', '2026-07-24 06:41:04', '2026-07-24 06:41:04'),
(6, 'requests.release', 'web', '2026-07-24 06:41:04', '2026-07-24 06:41:04'),
(7, 'purchases.checkout', 'web', '2026-07-24 06:41:04', '2026-07-24 06:41:04'),
(8, 'purchases.verify', 'web', '2026-07-24 06:41:04', '2026-07-24 06:41:04'),
(9, 'users.manage', 'web', '2026-07-24 06:41:04', '2026-07-24 06:41:04'),
(10, 'reports.view', 'web', '2026-07-24 06:41:04', '2026-07-24 06:41:04'),
(11, 'audit.view', 'web', '2026-07-24 06:41:04', '2026-07-24 06:41:04'),
(12, 'announcements.manage', 'web', '2026-07-24 06:41:04', '2026-07-24 06:41:04');

-- --------------------------------------------------------

--
-- Table structure for table `psis_notifications`
--

CREATE TABLE `psis_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `psis_notifications`
--

INSERT INTO `psis_notifications` (`id`, `user_id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 2, 'new_request', 'New supply request', 'Prof. Juan Dela Cruz submitted request REQ-PCR3LYIW.', 'http://127.0.0.1:8000/accounting/requests/1', 0, '2026-07-24 07:55:11', '2026-07-24 07:55:11'),
(2, 2, 'payment_submitted', 'New student purchase', 'Maria Santos submitted purchase PUR-F27D0VVL.', 'http://127.0.0.1:8000/accounting/payments/1', 0, '2026-07-24 08:03:43', '2026-07-24 08:03:43'),
(3, 5, 'payment_verified', 'Payment verified', 'Payment for PUR-F27D0VVL has been verified.', 'http://127.0.0.1:8000/purchases/1', 1, '2026-07-24 08:05:05', '2026-07-24 08:13:09'),
(4, 3, 'purchase_verified', 'Purchase ready for release', 'Purchase PUR-F27D0VVL is ready for release.', 'http://127.0.0.1:8000/supply/purchases/1', 1, '2026-07-24 08:05:05', '2026-07-24 08:36:00'),
(5, 1, 'request_reviewed', 'Request ready for approval', 'Request REQ-PCR3LYIW was reviewed by accounting.', 'http://127.0.0.1:8000/admin/requests/1', 0, '2026-07-24 08:05:14', '2026-07-24 08:05:14'),
(6, 4, 'request_approved', 'Request approved', 'Your request REQ-PCR3LYIW has been approved.', 'http://127.0.0.1:8000/requests/1', 1, '2026-07-24 08:08:49', '2026-07-24 08:16:54'),
(7, 3, 'request_approved', 'Approved request pending release', 'Request REQ-PCR3LYIW is ready for release.', 'http://127.0.0.1:8000/supply/releases/1', 1, '2026-07-24 08:08:49', '2026-07-24 08:36:00'),
(8, 2, 'payment_submitted', 'New student purchase', 'Maria Santos submitted purchase PUR-J0B7AXVI.', 'http://127.0.0.1:8000/accounting/payments/2', 0, '2026-07-24 08:14:26', '2026-07-24 08:14:26'),
(9, 5, 'payment_verified', 'Payment verified', 'Payment for PUR-J0B7AXVI has been verified.', 'http://127.0.0.1:8000/purchases/2', 0, '2026-07-24 08:15:54', '2026-07-24 08:15:54'),
(10, 3, 'purchase_verified', 'Purchase ready for release', 'Purchase PUR-J0B7AXVI is ready for release.', 'http://127.0.0.1:8000/supply/purchases/2', 1, '2026-07-24 08:15:54', '2026-07-24 08:36:00'),
(11, 4, 'item_released', 'Items released', 'Items for request REQ-PCR3LYIW have been released.', 'http://127.0.0.1:8000/requests/1', 0, '2026-07-24 08:18:00', '2026-07-24 08:18:00'),
(12, 5, 'item_released', 'Purchase released', 'Your purchase PUR-J0B7AXVI has been released.', 'http://127.0.0.1:8000/purchases/2', 0, '2026-07-24 08:26:28', '2026-07-24 08:26:28'),
(13, 5, 'item_released', 'Purchase released', 'Your purchase PUR-F27D0VVL has been released.', 'http://127.0.0.1:8000/purchases/1', 0, '2026-07-24 08:26:55', '2026-07-24 08:26:55'),
(14, 2, 'new_request', 'New supply request', 'Prof. Juan Dela Cruz submitted request REQ-GMWTCZAW.', 'http://127.0.0.1:8000/accounting/requests/2', 0, '2026-07-24 08:31:45', '2026-07-24 08:31:45'),
(15, 1, 'request_reviewed', 'Request ready for approval', 'Request REQ-GMWTCZAW was reviewed by accounting.', 'http://127.0.0.1:8000/admin/requests/2', 0, '2026-07-24 08:32:19', '2026-07-24 08:32:19'),
(16, 4, 'request_approved', 'Request approved', 'Your request REQ-GMWTCZAW has been approved.', 'http://127.0.0.1:8000/requests/2', 0, '2026-07-24 08:33:58', '2026-07-24 08:33:58'),
(17, 3, 'request_approved', 'Approved request pending release', 'Request REQ-GMWTCZAW is ready for release.', 'http://127.0.0.1:8000/supply/releases/2', 1, '2026-07-24 08:33:58', '2026-07-24 08:36:00'),
(18, 4, 'item_released', 'Items released', 'Items for request REQ-GMWTCZAW have been released.', 'http://127.0.0.1:8000/requests/2', 0, '2026-07-24 08:35:16', '2026-07-24 08:35:16'),
(19, 2, 'new_request', 'New supply request', 'Vea Villaver submitted request REQ-JYDE6HY8.', 'http://127.0.0.1:8000/accounting/requests/3', 0, '2026-07-24 08:41:10', '2026-07-24 08:41:10'),
(20, 1, 'request_reviewed', 'Request ready for approval', 'Request REQ-JYDE6HY8 was reviewed by accounting.', 'http://127.0.0.1:8000/admin/requests/3', 0, '2026-07-24 08:42:44', '2026-07-24 08:42:44'),
(21, 6, 'request_approved', 'Request approved', 'Your request REQ-JYDE6HY8 has been approved.', 'http://127.0.0.1:8000/requests/3', 1, '2026-07-24 08:43:38', '2026-07-24 20:16:17'),
(22, 3, 'request_approved', 'Approved request pending release', 'Request REQ-JYDE6HY8 is ready for release.', 'http://127.0.0.1:8000/supply/releases/3', 0, '2026-07-24 08:43:38', '2026-07-24 08:43:38'),
(23, 6, 'item_released', 'Items released', 'Items for request REQ-JYDE6HY8 have been released.', 'http://127.0.0.1:8000/requests/3', 1, '2026-07-24 08:44:31', '2026-07-24 20:16:17'),
(24, 2, 'payment_submitted', 'New student purchase', 'Joy Tienes submitted purchase PUR-PD8IZ03C.', 'http://127.0.0.1:8000/accounting/payments/3', 0, '2026-07-24 19:08:19', '2026-07-24 19:08:19'),
(25, 7, 'payment_verified', 'Payment verified', 'Payment for PUR-PD8IZ03C has been verified.', 'http://127.0.0.1:8000/purchases/3', 0, '2026-07-24 19:10:43', '2026-07-24 19:10:43'),
(26, 3, 'purchase_verified', 'Purchase ready for release', 'Purchase PUR-PD8IZ03C is ready for release.', 'http://127.0.0.1:8000/supply/purchases/3', 0, '2026-07-24 19:10:48', '2026-07-24 19:10:48'),
(27, 7, 'item_released', 'Purchase released', 'Your purchase PUR-PD8IZ03C has been released.', 'http://127.0.0.1:8000/purchases/3', 0, '2026-07-24 19:11:50', '2026-07-24 19:11:50'),
(28, 2, 'new_request', 'New supply request', 'Vea Villaver submitted request REQ-2YCLVJ4C.', 'http://127.0.0.1:8000/accounting/requests/4', 0, '2026-07-24 20:05:07', '2026-07-24 20:05:07'),
(29, 1, 'request_reviewed', 'Request ready for approval', 'Request REQ-2YCLVJ4C was reviewed by accounting.', 'http://127.0.0.1:8000/admin/requests/4', 0, '2026-07-24 20:10:30', '2026-07-24 20:10:30'),
(30, 6, 'request_approved', 'Request approved', 'Your request REQ-2YCLVJ4C has been approved.', 'http://127.0.0.1:8000/requests/4', 1, '2026-07-24 20:11:56', '2026-07-24 20:16:17'),
(31, 3, 'request_approved', 'Approved request pending release', 'Request REQ-2YCLVJ4C is ready for release.', 'http://127.0.0.1:8000/supply/releases/4', 0, '2026-07-24 20:12:01', '2026-07-24 20:12:01'),
(32, 6, 'item_released', 'Items released', 'Items for request REQ-2YCLVJ4C have been released.', 'http://127.0.0.1:8000/requests/4', 1, '2026-07-24 20:13:53', '2026-07-24 20:16:17'),
(33, 2, 'payment_submitted', 'New student purchase', 'Joy Tienes submitted purchase PUR-MWGYFIIS.', 'http://127.0.0.1:8000/accounting/payments/4', 0, '2026-07-24 20:46:25', '2026-07-24 20:46:25'),
(34, 7, 'payment_verified', 'Payment verified', 'Payment for PUR-MWGYFIIS has been verified.', 'http://127.0.0.1:8000/purchases/4', 0, '2026-07-24 20:47:26', '2026-07-24 20:47:26'),
(35, 3, 'purchase_verified', 'Purchase ready for release', 'Purchase PUR-MWGYFIIS is ready for release.', 'http://127.0.0.1:8000/supply/purchases/4', 0, '2026-07-24 20:47:31', '2026-07-24 20:47:31'),
(36, 7, 'item_released', 'Purchase released', 'Your purchase PUR-MWGYFIIS has been released.', 'http://127.0.0.1:8000/purchases/4', 0, '2026-07-24 20:48:44', '2026-07-24 20:48:44');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_number` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','payment_submitted','payment_verified','approved','released','cancelled','rejected') NOT NULL DEFAULT 'pending',
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `released_by` bigint(20) UNSIGNED DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `released_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_requests`
--

INSERT INTO `purchase_requests` (`id`, `purchase_number`, `user_id`, `status`, `total_amount`, `remarks`, `verified_by`, `released_by`, `verified_at`, `released_at`, `created_at`, `updated_at`) VALUES
(1, 'PUR-F27D0VVL', 5, 'released', 250.00, NULL, 2, 3, '2026-07-24 08:05:05', '2026-07-24 08:26:55', '2026-07-24 08:03:43', '2026-07-24 08:26:55'),
(2, 'PUR-J0B7AXVI', 5, 'released', 95.00, NULL, 2, 3, '2026-07-24 08:15:54', '2026-07-24 08:26:28', '2026-07-24 08:14:26', '2026-07-24 08:26:28'),
(3, 'PUR-PD8IZ03C', 7, 'released', 35.00, NULL, 2, 3, '2026-07-24 19:10:43', '2026-07-24 19:11:50', '2026-07-24 19:08:19', '2026-07-24 19:11:50'),
(4, 'PUR-MWGYFIIS', 7, 'released', 250.00, NULL, 2, 3, '2026-07-24 20:47:26', '2026-07-24 20:48:44', '2026-07-24 20:46:25', '2026-07-24 20:48:44');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_request_items`
--

CREATE TABLE `purchase_request_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_request_id` bigint(20) UNSIGNED NOT NULL,
  `inventory_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_request_items`
--

INSERT INTO `purchase_request_items` (`id`, `purchase_request_id`, `inventory_id`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 4, 1, 250.00, 250.00, '2026-07-24 08:03:43', '2026-07-24 08:03:43'),
(2, 2, 6, 1, 95.00, 95.00, '2026-07-24 08:14:26', '2026-07-24 08:14:26'),
(3, 3, 3, 1, 35.00, 35.00, '2026-07-24 19:08:19', '2026-07-24 19:08:19'),
(4, 4, 4, 1, 250.00, 250.00, '2026-07-24 20:46:25', '2026-07-24 20:46:25');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_number` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('faculty','restock') NOT NULL DEFAULT 'faculty',
  `status` enum('pending','accounting_review','admin_review','approved','rejected','reserved','released','cancelled') NOT NULL DEFAULT 'pending',
  `purpose` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `released_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `released_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `request_number`, `user_id`, `department_id`, `type`, `status`, `purpose`, `remarks`, `rejection_reason`, `total_amount`, `reviewed_by`, `approved_by`, `released_by`, `reviewed_at`, `approved_at`, `released_at`, `created_at`, `updated_at`) VALUES
(1, 'REQ-PCR3LYIW', 4, 1, 'faculty', 'released', 'pang sulat lang po. matsalam', NULL, NULL, 225.00, 2, 1, 3, '2026-07-24 08:05:14', '2026-07-24 08:08:49', '2026-07-24 08:18:00', '2026-07-24 07:55:11', '2026-07-24 08:18:00'),
(2, 'REQ-GMWTCZAW', 4, 1, 'faculty', 'released', 'please hatag', NULL, NULL, 285.00, 2, 1, 3, '2026-07-24 08:32:19', '2026-07-24 08:33:58', '2026-07-24 08:35:16', '2026-07-24 08:31:44', '2026-07-24 08:35:16'),
(3, 'REQ-JYDE6HY8', 6, 9, 'faculty', 'released', 'mang guna me', NULL, NULL, 3600.00, 2, 1, 3, '2026-07-24 08:42:44', '2026-07-24 08:43:38', '2026-07-24 08:44:31', '2026-07-24 08:41:10', '2026-07-24 08:44:31'),
(4, 'REQ-2YCLVJ4C', 6, 9, 'faculty', 'released', 'for visitors.', NULL, NULL, 10500.00, 2, 1, 3, '2026-07-24 20:10:30', '2026-07-24 20:11:56', '2026-07-24 20:13:53', '2026-07-24 20:05:07', '2026-07-24 20:13:53');

-- --------------------------------------------------------

--
-- Table structure for table `request_items`
--

CREATE TABLE `request_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_id` bigint(20) UNSIGNED NOT NULL,
  `inventory_id` bigint(20) UNSIGNED NOT NULL,
  `quantity_requested` int(11) NOT NULL,
  `quantity_approved` int(11) DEFAULT NULL,
  `quantity_released` int(11) NOT NULL DEFAULT 0,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `request_items`
--

INSERT INTO `request_items` (`id`, `request_id`, `inventory_id`, `quantity_requested`, `quantity_approved`, `quantity_released`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 5, 5, 5, 45.00, 225.00, '2026-07-24 07:55:11', '2026-07-24 08:18:00'),
(2, 2, 1, 1, 1, 1, 285.00, 285.00, '2026-07-24 08:31:44', '2026-07-24 08:35:16'),
(3, 3, 7, 20, 20, 20, 180.00, 3600.00, '2026-07-24 08:41:10', '2026-07-24 08:44:31'),
(4, 4, 8, 3, 3, 3, 3500.00, 10500.00, '2026-07-24 20:05:07', '2026-07-24 20:13:53');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'web', '2026-07-24 06:41:04', '2026-07-24 06:41:04'),
(2, 'Accounting', 'web', '2026-07-24 06:41:04', '2026-07-24 06:41:04'),
(3, 'Supply Personnel', 'web', '2026-07-24 06:41:04', '2026-07-24 06:41:04'),
(4, 'Faculty', 'web', '2026-07-24 06:41:04', '2026-07-24 06:41:04'),
(5, 'Student', 'web', '2026-07-24 06:41:04', '2026-07-24 06:41:04');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(2, 1),
(2, 3),
(3, 1),
(3, 4),
(4, 1),
(4, 2),
(5, 1),
(6, 1),
(6, 3),
(7, 1),
(7, 5),
(8, 1),
(8, 2),
(9, 1),
(10, 1),
(10, 2),
(10, 3),
(11, 1),
(12, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('2JaaCTts2LtwwzTO2RoG92EcN6rXXv5NiXq5eKA2', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSUQxTkh1UFlRM1NTeEFFSVN1ckFYb0p4VmxjWHJQU3M4N1pRdnlmRCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMjoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL3JlcXVlc3RzLzQiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1784952871),
('8OszwDN5R7DKrIxa7uQEn0w6LGmv4VgSrLJ178Vm', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.130.0 Chrome/148.0.7778.280 Electron/42.6.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWWFzU2FQcUZCOE1ERUlXakpxc0tkUzh1dXNXVXR5RnZibHBFeW8xTCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1784948568),
('p0FhtMVbJ07GHxIhrrQNOzBNFAB9OeDMGzZQeDRx', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiTUlrWENQeHBpV3NNbFZJTU1oaXZPazVCM2IzTTlES3J0aHdzd3ZiYyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE2OiJsYXN0X2FjdGl2aXR5X2F0IjtpOjE3ODQ5NTE5OTI7fQ==', 1784951992),
('QkDWCNQbUhTWJtO2tc2C5z4E3N9vW4C9QKby915v', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiZzZ6VVdIVnRTNzhLRjY4emFsTW5tSk1PMFprWlhMYk1IalY5R3pTNCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MztzOjE2OiJsYXN0X2FjdGl2aXR5X2F0IjtpOjE3ODQ5NTQ5NjQ7fQ==', 1784954964),
('Zjgb693Y8BfGzV3SOvLeTc2x21dynAwVNZ9zu1jN', NULL, '127.0.0.1', 'Symfony', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWGtFaFpRbDFxR1RsTWZoU1N3S1Fuejh5ZHNPb3VaRWxQbU9URE5PRCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHA6Ly9sb2NhbGhvc3QvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1784955882);

-- --------------------------------------------------------

--
-- Table structure for table `stock_logs`
--

CREATE TABLE `stock_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `inventory_id` bigint(20) UNSIGNED NOT NULL,
  `action` enum('stock_in','stock_out','adjustment','delivery') NOT NULL,
  `quantity` int(11) NOT NULL,
  `balance_after` int(11) NOT NULL,
  `delivery_recipient` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `performed_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_logs`
--

INSERT INTO `stock_logs` (`id`, `inventory_id`, `action`, `quantity`, `balance_after`, `delivery_recipient`, `notes`, `performed_by`, `created_at`, `updated_at`) VALUES
(1, 2, 'delivery', 5, 80, 'Prof. Juan Dela Cruz', 'Released for REQ-PCR3LYIW', 3, '2026-07-24 08:18:00', '2026-07-24 08:18:00'),
(2, 6, 'delivery', 1, 27, 'Maria Santos', 'Student purchase PUR-J0B7AXVI', 3, '2026-07-24 08:26:28', '2026-07-24 08:26:28'),
(3, 4, 'delivery', 1, 59, 'Maria Santos', 'Student purchase PUR-F27D0VVL', 3, '2026-07-24 08:26:55', '2026-07-24 08:26:55'),
(4, 1, 'delivery', 1, 119, 'Prof. Juan Dela Cruz', 'Released for REQ-GMWTCZAW', 3, '2026-07-24 08:35:16', '2026-07-24 08:35:16'),
(5, 7, 'delivery', 20, 2, 'Vea Villaver', 'Released for REQ-JYDE6HY8', 3, '2026-07-24 08:44:31', '2026-07-24 08:44:31'),
(6, 3, 'delivery', 1, 39, 'Joy Tienes', 'Student purchase PUR-PD8IZ03C', 3, '2026-07-24 19:11:50', '2026-07-24 19:11:50'),
(7, 8, 'delivery', 3, 5, 'Vea Villaver', 'Released for REQ-2YCLVJ4C', 3, '2026-07-24 20:13:53', '2026-07-24 20:13:53'),
(8, 4, 'delivery', 1, 58, 'Joy Tienes', 'Student purchase PUR-MWGYFIIS', 3, '2026-07-24 20:48:44', '2026-07-24 20:48:44');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transaction_number` varchar(255) NOT NULL,
  `inventory_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('stock_in','stock_out','adjustment','reserve','release','restore') NOT NULL,
  `quantity` int(11) NOT NULL,
  `quantity_before` int(11) NOT NULL,
  `quantity_after` int(11) NOT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `performed_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `transaction_number`, `inventory_id`, `type`, `quantity`, `quantity_before`, `quantity_after`, `reference_type`, `reference_id`, `notes`, `performed_by`, `created_at`, `updated_at`) VALUES
(1, 'TXN-6TA87EUGJT', 4, 'reserve', 1, 60, 60, 'App\\Models\\PurchaseRequest', 1, 'Reserved for PUR-F27D0VVL', 2, '2026-07-24 08:05:05', '2026-07-24 08:05:05'),
(2, 'TXN-VGVLAZ7ZXY', 2, 'reserve', 5, 85, 85, 'App\\Models\\SupplyRequest', 1, 'Reserved for REQ-PCR3LYIW', 1, '2026-07-24 08:08:49', '2026-07-24 08:08:49'),
(3, 'TXN-J2NPTUMNLY', 6, 'reserve', 1, 28, 28, 'App\\Models\\PurchaseRequest', 2, 'Reserved for PUR-J0B7AXVI', 2, '2026-07-24 08:15:54', '2026-07-24 08:15:54'),
(4, 'TXN-ICCRTICSHZ', 2, 'release', 5, 85, 80, 'App\\Models\\SupplyRequest', 1, 'Released for REQ-PCR3LYIW', 3, '2026-07-24 08:18:00', '2026-07-24 08:18:00'),
(5, 'TXN-MRFNDD07RC', 6, 'release', 1, 28, 27, 'App\\Models\\PurchaseRequest', 2, 'Student purchase PUR-J0B7AXVI', 3, '2026-07-24 08:26:28', '2026-07-24 08:26:28'),
(6, 'TXN-O0RP8JJWWB', 4, 'release', 1, 60, 59, 'App\\Models\\PurchaseRequest', 1, 'Student purchase PUR-F27D0VVL', 3, '2026-07-24 08:26:55', '2026-07-24 08:26:55'),
(7, 'TXN-OMCOZFJF0J', 1, 'reserve', 1, 120, 120, 'App\\Models\\SupplyRequest', 2, 'Reserved for REQ-GMWTCZAW', 1, '2026-07-24 08:33:57', '2026-07-24 08:33:57'),
(8, 'TXN-7JEY4TQND9', 1, 'release', 1, 120, 119, 'App\\Models\\SupplyRequest', 2, 'Released for REQ-GMWTCZAW', 3, '2026-07-24 08:35:16', '2026-07-24 08:35:16'),
(9, 'TXN-VDQOC3CBKE', 7, 'reserve', 20, 22, 22, 'App\\Models\\SupplyRequest', 3, 'Reserved for REQ-JYDE6HY8', 1, '2026-07-24 08:43:38', '2026-07-24 08:43:38'),
(10, 'TXN-BDIIV8MQPY', 7, 'release', 20, 22, 2, 'App\\Models\\SupplyRequest', 3, 'Released for REQ-JYDE6HY8', 3, '2026-07-24 08:44:31', '2026-07-24 08:44:31'),
(11, 'TXN-OPD3TCMHLD', 3, 'reserve', 1, 40, 40, 'App\\Models\\PurchaseRequest', 3, 'Reserved for PUR-PD8IZ03C', 2, '2026-07-24 19:10:43', '2026-07-24 19:10:43'),
(12, 'TXN-0XPRCHBAMH', 3, 'release', 1, 40, 39, 'App\\Models\\PurchaseRequest', 3, 'Student purchase PUR-PD8IZ03C', 3, '2026-07-24 19:11:50', '2026-07-24 19:11:50'),
(13, 'TXN-RTGGLV7UX0', 8, 'reserve', 3, 8, 8, 'App\\Models\\SupplyRequest', 4, 'Reserved for REQ-2YCLVJ4C', 1, '2026-07-24 20:11:56', '2026-07-24 20:11:56'),
(14, 'TXN-TY86WEMWOD', 8, 'release', 3, 8, 5, 'App\\Models\\SupplyRequest', 4, 'Released for REQ-2YCLVJ4C', 3, '2026-07-24 20:13:53', '2026-07-24 20:13:53'),
(15, 'TXN-KFO8OKEHNH', 4, 'reserve', 1, 59, 59, 'App\\Models\\PurchaseRequest', 4, 'Reserved for PUR-MWGYFIIS', 2, '2026-07-24 20:47:26', '2026-07-24 20:47:26'),
(16, 'TXN-JAHNCNBZLP', 4, 'release', 1, 59, 58, 'App\\Models\\PurchaseRequest', 4, 'Student purchase PUR-MWGYFIIS', 3, '2026-07-24 20:48:44', '2026-07-24 20:48:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_activity_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `employee_id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `department_id`, `phone`, `is_active`, `last_activity_at`) VALUES
(1, 'ADM-001', 'PECIT Admin', 'admin@pecit.edu.ph', '2026-07-24 06:43:05', '$2y$12$uBKLRahlwQGoB//1TcUWhu1MoxC5s0KXFbrosSwz/QcuWv458SlG2', NULL, '2026-07-24 06:43:05', '2026-07-24 20:12:16', 7, NULL, 1, '2026-07-24 20:12:16'),
(2, 'ACC-001', 'PECIT Accounting', 'accounting@pecit.edu.ph', '2026-07-24 06:43:05', '$2y$12$k/qp1KIBk9Xk/eeW.D52cO01p3rC/z8cv2diKZ0z3sNeDnfsnD2VW', NULL, '2026-07-24 06:43:05', '2026-07-24 20:47:46', 7, NULL, 1, '2026-07-24 20:47:46'),
(3, 'SUP-001', 'Supply Officer', 'supply@pecit.edu.ph', '2026-07-24 06:43:05', '$2y$12$PdnxkklDtWHi3Dmj3UZUd.PCTqf1GXk5ptoYk1hoawRZHAmwO/vdS', NULL, '2026-07-24 06:43:05', '2026-07-24 20:49:24', 8, NULL, 1, '2026-07-24 20:49:24'),
(4, 'FAC-001', 'Prof. Juan Dela Cruz', 'faculty@pecit.edu.ph', '2026-07-24 06:43:06', '$2y$12$PAQWnYmtyK6MjCLINX8j0uOgqq9t2CjmSV0vjFux7pRCqrYuhv5H6', NULL, '2026-07-24 06:43:06', '2026-07-24 08:31:45', 1, NULL, 1, '2026-07-24 08:31:45'),
(5, 'STU-001', 'Maria Santos', 'student@pecit.edu.ph', '2026-07-24 06:43:06', '$2y$12$GG/Hx2.Pq//baFYJ/8zQl./eqcxDpoLT67kloWblECrxdFUjtkjUy', NULL, '2026-07-24 06:43:06', '2026-07-24 08:25:31', 4, NULL, 1, '2026-07-24 08:25:31'),
(6, '20231-00245', 'Vea Villaver', 'veapecit.edu@gmail.com', '2026-07-24 08:40:15', '$2y$12$Vcn7obbxA.yEmD/MIsUMgu2muLnXMXWUESZ.0N6f9ebXt7vfSFMI2', NULL, '2026-07-24 08:40:15', '2026-07-24 20:16:18', 9, '09123456789', 1, '2026-07-24 20:16:18'),
(7, '20231-00246', 'Joy Tienes', 'tienesmaryjoy6@gmail.com', '2026-07-24 19:07:21', '$2y$12$k4YyeQ8njXjJ50N1Lqir9Os0/zrr8Ls4G.KDbWnLkNGYGnzC7J7gS', NULL, '2026-07-24 19:07:21', '2026-07-24 20:46:53', 9, '09123456789', 1, '2026-07-24 20:46:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcements_created_by_foreign` (`created_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departments_code_unique` (`code`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inventory_item_code_unique` (`item_code`),
  ADD KEY `inventory_category_id_foreign` (`category_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payments_reference_number_unique` (`reference_number`),
  ADD KEY `payments_purchase_request_id_foreign` (`purchase_request_id`),
  ADD KEY `payments_user_id_foreign` (`user_id`),
  ADD KEY `payments_verified_by_foreign` (`verified_by`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `psis_notifications`
--
ALTER TABLE `psis_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `psis_notifications_user_id_foreign` (`user_id`);

--
-- Indexes for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_requests_purchase_number_unique` (`purchase_number`),
  ADD KEY `purchase_requests_user_id_foreign` (`user_id`),
  ADD KEY `purchase_requests_verified_by_foreign` (`verified_by`),
  ADD KEY `purchase_requests_released_by_foreign` (`released_by`);

--
-- Indexes for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_request_items_purchase_request_id_foreign` (`purchase_request_id`),
  ADD KEY `purchase_request_items_inventory_id_foreign` (`inventory_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `requests_request_number_unique` (`request_number`),
  ADD KEY `requests_user_id_foreign` (`user_id`),
  ADD KEY `requests_department_id_foreign` (`department_id`),
  ADD KEY `requests_reviewed_by_foreign` (`reviewed_by`),
  ADD KEY `requests_approved_by_foreign` (`approved_by`),
  ADD KEY `requests_released_by_foreign` (`released_by`);

--
-- Indexes for table `request_items`
--
ALTER TABLE `request_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_items_request_id_foreign` (`request_id`),
  ADD KEY `request_items_inventory_id_foreign` (`inventory_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `stock_logs`
--
ALTER TABLE `stock_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_logs_inventory_id_foreign` (`inventory_id`),
  ADD KEY `stock_logs_performed_by_foreign` (`performed_by`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transactions_transaction_number_unique` (`transaction_number`),
  ADD KEY `transactions_inventory_id_foreign` (`inventory_id`),
  ADD KEY `transactions_performed_by_foreign` (`performed_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_department_id_foreign` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `psis_notifications`
--
ALTER TABLE `psis_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `request_items`
--
ALTER TABLE `request_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `stock_logs`
--
ALTER TABLE `stock_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `psis_notifications`
--
ALTER TABLE `psis_notifications`
  ADD CONSTRAINT `psis_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD CONSTRAINT `purchase_requests_released_by_foreign` FOREIGN KEY (`released_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_requests_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  ADD CONSTRAINT `purchase_request_items_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_request_items_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `requests_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `requests_released_by_foreign` FOREIGN KEY (`released_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `requests_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `request_items`
--
ALTER TABLE `request_items`
  ADD CONSTRAINT `request_items_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `request_items_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_logs`
--
ALTER TABLE `stock_logs`
  ADD CONSTRAINT `stock_logs_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_logs_performed_by_foreign` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_performed_by_foreign` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
