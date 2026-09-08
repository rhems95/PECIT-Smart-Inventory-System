-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 08, 2026 at 04:35 PM
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
(28, 3, 'purchase.released', 'App\\Models\\PurchaseRequest', 4, NULL, '{\"deducted\":[\"Bottled Water 500ml x1\"]}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 20:48:48', '2026-07-24 20:48:48'),
(29, 3, 'student.updated', 'App\\Models\\User', 7, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-05 20:15:48', '2026-08-05 20:15:48'),
(30, 7, 'purchase.created', 'App\\Models\\PurchaseRequest', 5, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-26 05:17:06', '2026-08-26 05:17:06'),
(31, 2, 'purchase.payment_verified', 'App\\Models\\PurchaseRequest', 5, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-26 05:23:42', '2026-08-26 05:23:42'),
(32, 7, 'purchase.created', 'App\\Models\\PurchaseRequest', 6, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-28 01:19:20', '2026-08-28 01:19:20'),
(33, 7, 'purchase.created', 'App\\Models\\PurchaseRequest', 7, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-28 01:40:11', '2026-08-28 01:40:11'),
(34, 2, 'purchase.payment_verified', 'App\\Models\\PurchaseRequest', 7, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-28 01:42:34', '2026-08-28 01:42:34'),
(35, 7, 'purchase.created', 'App\\Models\\PurchaseRequest', 8, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-31 20:28:12', '2026-08-31 20:28:12'),
(36, 2, 'purchase.payment_verified', 'App\\Models\\PurchaseRequest', 8, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-31 20:31:11', '2026-08-31 20:31:11'),
(37, 3, 'purchase.released', 'App\\Models\\PurchaseRequest', 8, NULL, '{\"deducted\":[\"Computer Studies Uniform (Exclusive) (M) x1\"]}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-31 20:32:51', '2026-08-31 20:32:51'),
(38, 4, 'supply_request.created', 'App\\Models\\SupplyRequest', 5, NULL, '{\"request_number\":\"REQ-STEWWTLH\",\"user_id\":4,\"department_id\":3,\"type\":\"faculty\",\"status\":\"pending\",\"purpose\":\"school use\",\"updated_at\":\"2026-09-03T12:51:41.000000Z\",\"created_at\":\"2026-09-03T12:51:41.000000Z\",\"id\":5,\"total_amount\":\"35.00\",\"items\":[{\"id\":5,\"request_id\":5,\"inventory_id\":3,\"quantity_requested\":1,\"quantity_approved\":null,\"quantity_released\":0,\"unit_price\":\"35.00\",\"subtotal\":\"35.00\",\"created_at\":\"2026-09-03T12:51:41.000000Z\",\"updated_at\":\"2026-09-03T12:51:41.000000Z\"}]}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-03 04:52:08', '2026-09-03 04:52:08'),
(39, 2, 'supply_request.accounting_review', 'App\\Models\\SupplyRequest', 5, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 04:53:14', '2026-09-03 04:53:14'),
(40, 9, 'supply_request.approved', 'App\\Models\\SupplyRequest', 5, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 04:54:15', '2026-09-03 04:54:15'),
(41, 4, 'supply_request.created', 'App\\Models\\SupplyRequest', 6, NULL, '{\"request_number\":\"REQ-C9211TOC\",\"user_id\":4,\"department_id\":3,\"type\":\"faculty\",\"status\":\"pending\",\"purpose\":\"for student\",\"updated_at\":\"2026-09-04T07:11:39.000000Z\",\"created_at\":\"2026-09-04T07:11:38.000000Z\",\"id\":6,\"total_amount\":\"2750.00\",\"items\":[{\"id\":6,\"request_id\":6,\"inventory_id\":10,\"quantity_requested\":5,\"quantity_approved\":null,\"quantity_released\":0,\"unit_price\":\"550.00\",\"subtotal\":\"2750.00\",\"created_at\":\"2026-09-04T07:11:39.000000Z\",\"updated_at\":\"2026-09-04T07:11:39.000000Z\"}]}', '192.168.110.165', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '2026-09-03 23:11:59', '2026-09-03 23:11:59'),
(42, 2, 'supply_request.accounting_review', 'App\\Models\\SupplyRequest', 6, NULL, NULL, '192.168.110.177', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 23:13:33', '2026-09-03 23:13:33'),
(43, 4, 'supply_request.created', 'App\\Models\\SupplyRequest', 7, NULL, '{\"request_number\":\"REQ-G4DVMT9Y\",\"user_id\":4,\"department_id\":3,\"type\":\"faculty\",\"status\":\"pending\",\"purpose\":\"for students\",\"updated_at\":\"2026-09-08T08:04:34.000000Z\",\"created_at\":\"2026-09-08T08:04:34.000000Z\",\"id\":7,\"total_amount\":\"1950.00\",\"items\":[{\"id\":7,\"request_id\":7,\"inventory_id\":9,\"quantity_requested\":3,\"quantity_approved\":null,\"quantity_released\":0,\"unit_price\":\"650.00\",\"subtotal\":\"1950.00\",\"created_at\":\"2026-09-08T08:04:34.000000Z\",\"updated_at\":\"2026-09-08T08:04:34.000000Z\"}]}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 00:04:46', '2026-09-08 00:04:46'),
(44, 2, 'supply_request.accounting_review', 'App\\Models\\SupplyRequest', 7, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 00:05:28', '2026-09-08 00:05:28'),
(45, 3, 'auth.login', 'App\\Models\\User', 3, NULL, '{\"login_as\":\"staff\"}', '127.0.0.1', NULL, '2026-09-08 00:54:20', '2026-09-08 00:54:20'),
(46, 3, 'auth.login', 'App\\Models\\User', 3, NULL, '{\"login_as\":\"staff\"}', '127.0.0.1', NULL, '2026-09-08 00:54:43', '2026-09-08 00:54:43'),
(47, 3, 'inventory.created', 'App\\Models\\Inventory', 17, NULL, '{\"item_code\":\"TMP-06c300fc\",\"item_name\":\"Temporary Add Item Check\",\"quantity\":2,\"size\":null}', '127.0.0.1', NULL, '2026-09-08 00:54:45', '2026-09-08 00:54:45'),
(48, 3, 'auth.login', 'App\\Models\\User', 3, NULL, '{\"login_as\":\"staff\"}', '127.0.0.1', NULL, '2026-09-08 01:12:24', '2026-09-08 01:12:24'),
(49, 5, 'auth.login', 'App\\Models\\User', 5, NULL, '{\"login_as\":\"student\"}', '127.0.0.1', NULL, '2026-09-08 01:12:26', '2026-09-08 01:12:26'),
(50, 8, 'auth.login', 'App\\Models\\User', 8, NULL, '{\"login_as\":\"student\"}', '127.0.0.1', NULL, '2026-09-08 01:12:29', '2026-09-08 01:12:29'),
(51, 9, 'auth.login', 'App\\Models\\User', 9, NULL, '{\"login_as\":\"staff\"}', '127.0.0.1', NULL, '2026-09-08 01:12:32', '2026-09-08 01:12:32'),
(52, 3, 'auth.login', 'App\\Models\\User', 3, NULL, '{\"login_as\":\"staff\"}', '127.0.0.1', NULL, '2026-09-08 01:24:35', '2026-09-08 01:24:35'),
(53, 5, 'auth.login', 'App\\Models\\User', 5, NULL, '{\"login_as\":\"student\"}', '127.0.0.1', NULL, '2026-09-08 01:24:38', '2026-09-08 01:24:38'),
(54, 8, 'auth.login', 'App\\Models\\User', 8, NULL, '{\"login_as\":\"student\"}', '127.0.0.1', NULL, '2026-09-08 01:24:41', '2026-09-08 01:24:41'),
(55, 9, 'auth.login', 'App\\Models\\User', 9, NULL, '{\"login_as\":\"staff\"}', '127.0.0.1', NULL, '2026-09-08 01:24:44', '2026-09-08 01:24:44'),
(56, 4, 'auth.login', 'App\\Models\\User', 4, NULL, '{\"login_as\":\"staff\"}', '127.0.0.1', NULL, '2026-09-08 01:24:49', '2026-09-08 01:24:49'),
(57, 7, 'purchase.created', 'App\\Models\\PurchaseRequest', 9, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 01:27:45', '2026-09-08 01:27:45'),
(58, 2, 'purchase.payment_verified', 'App\\Models\\PurchaseRequest', 9, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 01:28:53', '2026-09-08 01:28:53'),
(59, 3, 'purchase.released', 'App\\Models\\PurchaseRequest', 9, NULL, '{\"deducted\":[\"Lanyard for ID x1\"]}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 01:34:15', '2026-09-08 01:34:15'),
(60, 2, 'auth.login', 'App\\Models\\User', 2, NULL, '{\"login_as\":\"staff\"}', '127.0.0.1', NULL, '2026-09-08 01:36:11', '2026-09-08 01:36:11'),
(61, 7, 'auth.logout', 'App\\Models\\User', 7, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 01:36:19', '2026-09-08 01:36:19'),
(62, 7, 'auth.login', 'App\\Models\\User', 7, NULL, '{\"login_as\":\"student\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 01:36:40', '2026-09-08 01:36:40'),
(63, 7, 'auth.logout', 'App\\Models\\User', 7, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 01:36:47', '2026-09-08 01:36:47'),
(64, 7, 'auth.login', 'App\\Models\\User', 7, NULL, '{\"login_as\":\"student\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 01:37:43', '2026-09-08 01:37:43'),
(65, 3, 'inventory.updated', 'App\\Models\\Inventory', 16, '{\"item_code\":\"UNI-CCS\",\"item_name\":\"Computer Studies Uniform (Exclusive)\",\"unit_price\":\"1200.00\",\"minimum_stock\":10,\"student_shop\":true,\"department_id\":9,\"status\":\"available\"}', '{\"item_code\":\"UNI-CCS\",\"item_name\":\"Computer Studies Type A Uniform (Exclusive)\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 02:47:39', '2026-09-08 02:47:39'),
(66, 3, 'inventory.created', 'App\\Models\\Inventory', 18, NULL, '{\"item_code\":\"UNI-CCS-B\",\"item_name\":\"Computer Studies Type B Uniform (Exclusive)\",\"quantity\":5,\"size\":\"M\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 02:49:53', '2026-09-08 02:49:53'),
(67, 3, 'inventory.updated', 'App\\Models\\Inventory', 18, '{\"item_code\":\"UNI-CCS-B\",\"item_name\":\"Computer Studies Type B Uniform (Exclusive)\",\"unit_price\":\"350.00\",\"minimum_stock\":10,\"student_shop\":true,\"department_id\":9,\"status\":\"low_stock\"}', '{\"item_code\":\"UNI-CCS-B\",\"item_name\":\"Computer Studies Type B Uniform (Exclusive)\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 04:11:00', '2026-09-08 04:11:00'),
(68, 7, 'purchase.created', 'App\\Models\\PurchaseRequest', 10, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 04:11:50', '2026-09-08 04:11:50'),
(69, 2, 'auth.login', 'App\\Models\\User', 2, NULL, '{\"login_as\":\"staff\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 04:12:32', '2026-09-08 04:12:32'),
(70, 2, 'purchase.payment_verified', 'App\\Models\\PurchaseRequest', 10, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 04:13:32', '2026-09-08 04:13:32'),
(71, 2, 'purchase.payment_verified', 'App\\Models\\PurchaseRequest', 6, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 04:18:27', '2026-09-08 04:18:27'),
(72, 7, 'purchase.created', 'App\\Models\\PurchaseRequest', 11, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 04:19:16', '2026-09-08 04:19:16'),
(73, NULL, 'auth.login_failed', NULL, NULL, NULL, '{\"login_as\":\"staff\",\"email\":\"faculty@pecit.edu.ph\",\"student_id\":null}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 04:37:21', '2026-09-08 04:37:21'),
(74, 4, 'auth.login', 'App\\Models\\User', 4, NULL, '{\"login_as\":\"staff\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 04:37:27', '2026-09-08 04:37:27'),
(75, 4, 'supply_request.created', 'App\\Models\\SupplyRequest', 8, NULL, '{\"request_number\":\"REQ-YBCVUPA1\",\"user_id\":4,\"department_id\":3,\"type\":\"faculty\",\"status\":\"pending\",\"purpose\":\"for new computer\",\"updated_at\":\"2026-09-08T12:38:29.000000Z\",\"created_at\":\"2026-09-08T12:38:29.000000Z\",\"id\":8,\"total_amount\":\"120.00\",\"items\":[{\"id\":8,\"request_id\":8,\"inventory_id\":5,\"quantity_requested\":1,\"quantity_approved\":null,\"quantity_released\":0,\"unit_price\":\"120.00\",\"subtotal\":\"120.00\",\"created_at\":\"2026-09-08T12:38:29.000000Z\",\"updated_at\":\"2026-09-08T12:38:29.000000Z\"}]}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 04:38:35', '2026-09-08 04:38:35'),
(76, 2, 'supply_request.accounting_review', 'App\\Models\\SupplyRequest', 8, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 04:39:29', '2026-09-08 04:39:29'),
(77, 9, 'auth.login', 'App\\Models\\User', 9, NULL, '{\"login_as\":\"staff\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 04:39:45', '2026-09-08 04:39:45'),
(78, 7, 'auth.logout', 'App\\Models\\User', 7, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 04:46:03', '2026-09-08 04:46:03'),
(79, 7, 'auth.login', 'App\\Models\\User', 7, NULL, '{\"login_as\":\"student\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 04:46:16', '2026-09-08 04:46:16'),
(80, 3, 'inventory.created', 'App\\Models\\Inventory', 19, NULL, '{\"item_code\":\"UNI-CCS-C\",\"item_name\":\"Computer Studies Type C Uniform (Exclusive)\",\"quantity\":10,\"size\":\"S\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 04:50:34', '2026-09-08 04:50:34'),
(81, 3, 'inventory.adjusted', 'App\\Models\\Inventory', 19, '{\"quantity\":0}', '{\"item\":\"Computer Studies Type C Uniform (Exclusive)\",\"quantity\":10,\"size\":\"XS\",\"notes\":\"Updated on-hand by size from inventory edit. (Size: XS)\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 04:51:49', '2026-09-08 04:51:49'),
(82, 3, 'inventory.adjusted', 'App\\Models\\Inventory', 19, '{\"quantity\":0}', '{\"item\":\"Computer Studies Type C Uniform (Exclusive)\",\"quantity\":10,\"size\":\"M\",\"notes\":\"Updated on-hand by size from inventory edit. (Size: M)\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 04:51:49', '2026-09-08 04:51:49'),
(83, 3, 'inventory.adjusted', 'App\\Models\\Inventory', 19, '{\"quantity\":0}', '{\"item\":\"Computer Studies Type C Uniform (Exclusive)\",\"quantity\":10,\"size\":\"L\",\"notes\":\"Updated on-hand by size from inventory edit. (Size: L)\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 04:51:49', '2026-09-08 04:51:49'),
(84, 3, 'inventory.updated', 'App\\Models\\Inventory', 19, '{\"item_code\":\"UNI-CCS-C\",\"item_name\":\"Computer Studies Type C Uniform (Exclusive)\",\"unit_price\":\"1500.00\",\"minimum_stock\":10,\"student_shop\":true,\"department_id\":9,\"status\":\"low_stock\"}', '{\"item_code\":\"UNI-CCS-C\",\"item_name\":\"Computer Studies Type C Uniform (Exclusive)\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 04:51:49', '2026-09-08 04:51:49'),
(85, 4, 'supply_request.created', 'App\\Models\\SupplyRequest', 9, NULL, '{\"request_number\":\"REQ-VNJKFCK9\",\"user_id\":4,\"department_id\":3,\"type\":\"faculty\",\"status\":\"pending\",\"purpose\":\"for office\",\"updated_at\":\"2026-09-08T12:52:30.000000Z\",\"created_at\":\"2026-09-08T12:52:30.000000Z\",\"id\":9,\"total_amount\":\"3500.00\",\"items\":[{\"id\":9,\"request_id\":9,\"inventory_id\":8,\"quantity_requested\":1,\"quantity_approved\":null,\"quantity_released\":0,\"unit_price\":\"3500.00\",\"subtotal\":\"3500.00\",\"created_at\":\"2026-09-08T12:52:30.000000Z\",\"updated_at\":\"2026-09-08T12:52:30.000000Z\"}]}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 04:52:36', '2026-09-08 04:52:36'),
(86, 2, 'supply_request.accounting_review', 'App\\Models\\SupplyRequest', 9, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 04:53:08', '2026-09-08 04:53:08'),
(87, 9, 'supply_request.approved', 'App\\Models\\SupplyRequest', 9, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 04:53:35', '2026-09-08 04:53:35'),
(88, 3, 'supply_request.released', 'App\\Models\\SupplyRequest', 9, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 04:54:35', '2026-09-08 04:54:35'),
(89, 3, 'inventory.deleted', 'App\\Models\\Inventory', 19, '{\"item_code\":\"UNI-CCS-C\",\"item_name\":\"Computer Studies Type C Uniform (Exclusive)\",\"quantity\":40}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 05:05:03', '2026-09-08 05:05:03'),
(90, 3, 'inventory.updated', 'App\\Models\\Inventory', 18, '{\"item_code\":\"UNI-CCS-B\",\"item_name\":\"Computer Studies Type B Uniform (Exclusive)\",\"unit_price\":\"1200.00\",\"minimum_stock\":10,\"student_shop\":true,\"department_id\":9,\"status\":\"low_stock\"}', '{\"item_code\":\"UNI-CCS-C\",\"item_name\":\"Computer Studies Type C Uniform (Exclusive)\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 05:05:52', '2026-09-08 05:05:52'),
(91, 3, 'inventory.created', 'App\\Models\\Inventory', 20, NULL, '{\"item_code\":\"UNI-CCS-B\",\"item_name\":\"Computer Studies Type B Uniform (Exclusive)\",\"quantity\":10,\"size\":\"S\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 05:08:05', '2026-09-08 05:08:05'),
(92, 4, 'supply_request.created', 'App\\Models\\SupplyRequest', 10, NULL, '{\"request_number\":\"REQ-XGJYTT8T\",\"user_id\":4,\"department_id\":3,\"type\":\"faculty\",\"status\":\"pending\",\"purpose\":\"for office\",\"updated_at\":\"2026-09-08T13:08:48.000000Z\",\"created_at\":\"2026-09-08T13:08:48.000000Z\",\"id\":10,\"total_amount\":\"180.00\",\"items\":[{\"id\":10,\"request_id\":10,\"inventory_id\":7,\"quantity_requested\":1,\"quantity_approved\":null,\"quantity_released\":0,\"unit_price\":\"180.00\",\"subtotal\":\"180.00\",\"created_at\":\"2026-09-08T13:08:48.000000Z\",\"updated_at\":\"2026-09-08T13:08:48.000000Z\"}]}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 05:08:54', '2026-09-08 05:08:54'),
(93, 2, 'supply_request.accounting_review', 'App\\Models\\SupplyRequest', 10, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 05:09:27', '2026-09-08 05:09:27'),
(94, 9, 'supply_request.approved', 'App\\Models\\SupplyRequest', 10, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 05:09:54', '2026-09-08 05:09:54'),
(95, 7, 'auth.logout', 'App\\Models\\User', 7, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 05:12:05', '2026-09-08 05:12:05'),
(96, 7, 'auth.login', 'App\\Models\\User', 7, NULL, '{\"login_as\":\"student\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 05:12:17', '2026-09-08 05:12:17');

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
('pecit-smart-inventory-system-cache-20231-00245|127.0.0.1', 'i:3;', 1787746939),
('pecit-smart-inventory-system-cache-20231-00245|127.0.0.1:timer', 'i:1787746939;', 1787746939),
('pecit-smart-inventory-system-cache-admin@edu.ph|127.0.0.1', 'i:1;', 1784906065),
('pecit-smart-inventory-system-cache-admin@edu.ph|127.0.0.1:timer', 'i:1784906065;', 1784906065),
('pecit-smart-inventory-system-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:12:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:14:\"inventory.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:16:\"inventory.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:15:\"requests.submit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:15:\"requests.review\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:16:\"requests.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:16:\"requests.release\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:18:\"purchases.checkout\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:16:\"purchases.verify\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:12:\"users.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:12:\"reports.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:10:\"audit.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:20:\"announcements.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:6:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:13:\"Administrator\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:10:\"Accounting\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:16:\"Supply Personnel\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:7:\"Faculty\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";i:5;s:1:\"b\";s:7:\"Student\";s:1:\"c\";s:3:\"web\";}i:5;a:3:{s:1:\"a\";i:6;s:1:\"b\";s:9:\"Admission\";s:1:\"c\";s:3:\"web\";}}}', 1788940479);

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
(10, 'Shirts', 'shirts', NULL, 1, '2026-07-24 07:47:35', '2026-07-24 07:47:35'),
(11, 'Uniforms', 'uniforms', 'Student uniforms and related items for the shop.', 1, '2026-08-05 19:57:20', '2026-08-05 19:57:20');

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
  `student_shop` tinyint(1) NOT NULL DEFAULT 0,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `item_code`, `item_name`, `description`, `category_id`, `unit`, `unit_price`, `quantity`, `reserved_quantity`, `minimum_stock`, `location`, `status`, `student_shop`, `department_id`, `created_at`, `updated_at`) VALUES
(1, 'PECIT-BONDPAPE', 'Bond Paper A4', 'PECIT standard Bond Paper A4', 1, 'ream', 285.00, 124, 0, 30, 'Supply Room A', 'available', 0, NULL, '2026-07-24 06:43:04', '2026-08-28 02:23:52'),
(2, 'PECIT-BOARDMAR', 'Board Marker (Black)', 'PECIT standard Board Marker (Black)', 2, 'piece', 45.00, 80, 0, 25, 'Supply Room A', 'available', 0, NULL, '2026-07-24 06:43:04', '2026-07-24 08:18:00'),
(3, 'PECIT-WHITEBOA', 'Whiteboard Eraser', 'PECIT standard Whiteboard Eraser', 2, 'piece', 35.00, 39, 1, 15, 'Supply Room A', 'available', 0, NULL, '2026-07-24 06:43:04', '2026-09-03 04:54:00'),
(4, 'PECIT-BOTTLEDW', 'Bottled Water 500ml', 'PECIT standard Bottled Water 500ml', 6, 'case', 250.00, 58, 0, 20, 'Pantry', 'available', 0, NULL, '2026-07-24 06:43:04', '2026-07-24 20:48:44'),
(5, 'PECIT-ETHERNET', 'Ethernet Cable Cat6', 'PECIT standard Ethernet Cable Cat6', 4, 'piece', 120.00, 35, 0, 10, 'IT Stock Room', 'available', 0, NULL, '2026-07-24 06:43:04', '2026-07-24 06:43:04'),
(6, 'PECIT-DISINFEC', 'Disinfectant Spray', 'PECIT standard Disinfectant Spray', 5, 'bottle', 95.00, 27, 0, 12, 'Janitorial', 'available', 0, NULL, '2026-07-24 06:43:04', '2026-07-24 08:26:28'),
(7, 'PECIT-LABORATO', 'Laboratory Gloves', 'PECIT standard Laboratory Gloves', 3, 'box', 180.00, 10, 1, 8, 'Lab Store', 'available', 0, NULL, '2026-07-24 06:43:04', '2026-09-08 05:09:44'),
(8, 'PECIT-OFFICECH', 'Office Chair', 'PECIT standard Office Chair', 8, 'unit', 3500.00, 4, 0, 2, 'Warehouse', 'available', 0, NULL, '2026-07-24 06:43:04', '2026-09-08 04:54:19'),
(9, 'UNI-PE', 'Uniform P.E.', 'Physical Education uniform — available to all students.', 11, 'piece', 650.00, 80, 0, 15, 'Uniform Store', 'available', 1, NULL, '2026-08-05 19:57:21', '2026-08-05 20:32:59'),
(10, 'UNI-NSTP', 'Uniform NSTP', 'NSTP uniform — available to all students.', 11, 'piece', 550.00, 60, 1, 15, 'Uniform Store', 'available', 1, NULL, '2026-08-05 19:57:21', '2026-08-26 05:23:30'),
(11, 'UNI-LANYARD', 'Lanyard for ID', 'ID lanyard — available to all students.', 11, 'piece', 80.00, 199, 1, 30, 'Uniform Store', 'available', 1, NULL, '2026-08-05 19:57:21', '2026-09-08 04:18:17'),
(12, 'UNI-CIT', 'IT Uniform (Exclusive)', 'Exclusive to College of Information Technology students only.', 11, 'set', 1200.00, 40, 0, 10, 'Uniform Store', 'available', 1, 4, '2026-08-05 19:57:21', '2026-08-05 20:32:59'),
(13, 'UNI-COE', 'Engineering Uniform (Exclusive)', 'Exclusive to College of Engineering students only.', 11, 'set', 1200.00, 40, 0, 10, 'Uniform Store', 'available', 1, 3, '2026-08-05 19:57:21', '2026-08-05 20:32:59'),
(14, 'UNI-COB', 'Business Uniform (Exclusive)', 'Exclusive to College of Business students only.', 11, 'set', 1200.00, 40, 0, 10, 'Uniform Store', 'available', 1, 5, '2026-08-05 19:57:21', '2026-08-05 20:32:59'),
(15, 'UNI-SHS', 'SHS Uniform (Exclusive)', 'Exclusive to Senior High School students only.', 11, 'set', 1200.00, 40, 0, 10, 'Uniform Store', 'available', 1, 6, '2026-08-05 19:57:21', '2026-08-05 20:32:59'),
(16, 'UNI-CCS', 'Computer Studies Type A Uniform (Exclusive)', 'Exclusive to College of Computer Studies students only.', 11, 'set', 1200.00, 45, 1, 10, 'Uniform Store', 'available', 1, 9, '2026-08-05 20:32:59', '2026-09-08 02:47:39'),
(18, 'UNI-CCS-C', 'Computer Studies Type C Uniform (Exclusive)', NULL, 11, 'piece', 1200.00, 5, 1, 10, NULL, 'low_stock', 1, 9, '2026-09-08 02:49:53', '2026-09-08 05:05:52'),
(20, 'UNI-CCS-B', 'Computer Studies Type B Uniform (Exclusive)', NULL, 11, 'piece', 1500.00, 10, 0, 10, NULL, 'low_stock', 1, 9, '2026-09-08 05:08:05', '2026-09-08 05:08:05');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_size_stocks`
--

CREATE TABLE `inventory_size_stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `inventory_id` bigint(20) UNSIGNED NOT NULL,
  `size` varchar(10) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `reserved_quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_size_stocks`
--

INSERT INTO `inventory_size_stocks` (`id`, `inventory_id`, `size`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES
(8, 9, 'XS', 12, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(9, 9, 'S', 12, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(10, 9, 'M', 12, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(11, 9, 'L', 11, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(12, 9, 'XL', 11, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(13, 9, '2XL', 11, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(14, 9, '3XL', 11, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(15, 10, 'XS', 9, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(16, 10, 'S', 9, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(17, 10, 'M', 9, 1, '2026-08-26 04:58:56', '2026-08-26 05:23:30'),
(18, 10, 'L', 9, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(19, 10, 'XL', 8, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(20, 10, '2XL', 8, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(21, 10, '3XL', 8, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(22, 13, 'XS', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(23, 13, 'S', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(24, 13, 'M', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(25, 13, 'L', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(26, 13, 'XL', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(27, 13, '2XL', 5, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(28, 13, '3XL', 5, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(29, 12, 'XS', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(30, 12, 'S', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(31, 12, 'M', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(32, 12, 'L', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(33, 12, 'XL', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(34, 12, '2XL', 5, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(35, 12, '3XL', 5, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(36, 16, 'XS', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(37, 16, 'S', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(38, 16, 'M', 9, 1, '2026-08-26 04:58:56', '2026-08-31 20:32:45'),
(39, 16, 'L', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(40, 16, 'XL', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(41, 16, '2XL', 5, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(42, 16, '3XL', 7, 0, '2026-08-26 04:58:56', '2026-08-28 02:22:35'),
(43, 14, 'XS', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(44, 14, 'S', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(45, 14, 'M', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(46, 14, 'L', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(47, 14, 'XL', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(48, 14, '2XL', 5, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(49, 14, '3XL', 5, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(50, 15, 'XS', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(51, 15, 'S', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(52, 15, 'M', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(53, 15, 'L', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(54, 15, 'XL', 6, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(55, 15, '2XL', 5, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(56, 15, '3XL', 5, 0, '2026-08-26 04:58:56', '2026-08-26 04:58:56'),
(57, 18, 'M', 5, 1, '2026-09-08 02:49:53', '2026-09-08 04:13:23'),
(62, 20, 'S', 10, 0, '2026-09-08 05:08:05', '2026-09-08 05:08:05');

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
(8, '2026_07_24_150004_create_suppliers_table', 2),
(9, '2026_07_24_150005_create_inventory_table', 2),
(10, '2026_07_24_150006_create_requests_table', 2),
(11, '2026_07_24_150007_create_request_items_table', 2),
(12, '2026_07_24_150008_create_purchase_requests_table', 2),
(13, '2026_07_24_150009_create_payments_table', 2),
(14, '2026_07_24_150010_create_transactions_table', 2),
(15, '2026_07_24_150011_create_stock_logs_table', 2),
(16, '2026_07_24_150012_create_psis_notifications_table', 2),
(17, '2026_07_24_150013_create_audit_logs_table', 2),
(18, '2026_07_24_150014_create_announcements_table', 2),
(19, '2026_07_24_150015_add_users_department_foreign_key', 2),
(20, '2026_08_06_000001_drop_suppliers_from_inventory', 3),
(21, '2026_08_06_120000_add_student_shop_and_last_name', 4),
(22, '2026_08_26_200000_add_size_to_purchase_request_items', 5),
(23, '2026_08_26_205000_create_inventory_size_stocks_table', 6),
(24, '2026_09_08_170000_drop_barcode_from_inventory', 7);

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
(5, 'App\\Models\\User', 7),
(5, 'App\\Models\\User', 8),
(6, 'App\\Models\\User', 9);

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
(4, 'PAY-GFM1XOMHD4', 4, 7, 250.00, 'verified', 'over_the_counter', 'receipts/hteSJRIKADev5q5MROhyBF9NMjcVCF50rP9WCxqi.pdf', NULL, 2, '2026-07-24 20:47:26', '2026-07-24 20:46:25', '2026-07-24 20:47:26'),
(5, 'PAY-56A6DLL2OC', 5, 7, 550.00, 'verified', 'over_the_counter', 'receipts/GpAsVxLCvRfpnSDYU88ogZ71GW6wBAZfgRAqNrWD.png', NULL, 2, '2026-08-26 05:23:30', '2026-08-26 05:16:49', '2026-08-26 05:23:30'),
(6, 'PAY-R1DQV2QKUO', 6, 7, 80.00, 'verified', 'over_the_counter', NULL, NULL, 2, '2026-09-08 04:18:17', '2026-08-28 01:18:57', '2026-09-08 04:18:17'),
(7, 'PAY-QF4QV86KGN', 7, 7, 1200.00, 'verified', 'over_the_counter', 'receipts/K9EdVD49sT52dgtmZjsLEwDBgJszvvAuIEDAa6sJ.png', NULL, 2, '2026-08-28 01:42:26', '2026-08-28 01:40:06', '2026-08-28 01:42:26'),
(8, 'PAY-QU0DSBWFNK', 8, 7, 1200.00, 'verified', 'over_the_counter', 'receipts/bi9cEeQ6F9BEFUUDYInB4x4jcIv610Ha8y6qzyhj.jpg', NULL, 2, '2026-08-31 20:30:59', '2026-08-31 20:27:43', '2026-08-31 20:30:59'),
(9, 'PAY-J1KETCMNQR', 9, 7, 80.00, 'verified', 'over_the_counter', NULL, NULL, 2, '2026-09-08 01:28:44', '2026-09-08 01:27:34', '2026-09-08 01:28:44'),
(10, 'PAY-5DIVNBZXNU', 10, 7, 1200.00, 'verified', 'over_the_counter', NULL, NULL, 2, '2026-09-08 04:13:23', '2026-09-08 04:11:38', '2026-09-08 04:13:23'),
(11, 'PAY-J2MFLMYRZM', 11, 7, 1200.00, 'pending', 'over_the_counter', 'receipts/3k0z2vVQrLl2jqhMe5cSmv9Xzk9f2Yt1zmJnFExZ.jpg', NULL, NULL, NULL, '2026-09-08 04:19:10', '2026-09-08 04:20:12');

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
(22, 3, 'request_approved', 'Approved request pending release', 'Request REQ-JYDE6HY8 is ready for release.', 'http://127.0.0.1:8000/supply/releases/3', 1, '2026-07-24 08:43:38', '2026-08-31 20:34:58'),
(23, 6, 'item_released', 'Items released', 'Items for request REQ-JYDE6HY8 have been released.', 'http://127.0.0.1:8000/requests/3', 1, '2026-07-24 08:44:31', '2026-07-24 20:16:17'),
(24, 2, 'payment_submitted', 'New student purchase', 'Joy Tienes submitted purchase PUR-PD8IZ03C.', 'http://127.0.0.1:8000/accounting/payments/3', 0, '2026-07-24 19:08:19', '2026-07-24 19:08:19'),
(25, 7, 'payment_verified', 'Payment verified', 'Payment for PUR-PD8IZ03C has been verified.', 'http://127.0.0.1:8000/purchases/3', 1, '2026-07-24 19:10:43', '2026-08-28 01:42:17'),
(26, 3, 'purchase_verified', 'Purchase ready for release', 'Purchase PUR-PD8IZ03C is ready for release.', 'http://127.0.0.1:8000/supply/purchases/3', 1, '2026-07-24 19:10:48', '2026-08-31 20:34:58'),
(27, 7, 'item_released', 'Purchase released', 'Your purchase PUR-PD8IZ03C has been released.', 'http://127.0.0.1:8000/purchases/3', 1, '2026-07-24 19:11:50', '2026-08-28 01:42:17'),
(28, 2, 'new_request', 'New supply request', 'Vea Villaver submitted request REQ-2YCLVJ4C.', 'http://127.0.0.1:8000/accounting/requests/4', 0, '2026-07-24 20:05:07', '2026-07-24 20:05:07'),
(29, 1, 'request_reviewed', 'Request ready for approval', 'Request REQ-2YCLVJ4C was reviewed by accounting.', 'http://127.0.0.1:8000/admin/requests/4', 0, '2026-07-24 20:10:30', '2026-07-24 20:10:30'),
(30, 6, 'request_approved', 'Request approved', 'Your request REQ-2YCLVJ4C has been approved.', 'http://127.0.0.1:8000/requests/4', 1, '2026-07-24 20:11:56', '2026-07-24 20:16:17'),
(31, 3, 'request_approved', 'Approved request pending release', 'Request REQ-2YCLVJ4C is ready for release.', 'http://127.0.0.1:8000/supply/releases/4', 1, '2026-07-24 20:12:01', '2026-08-31 20:34:58'),
(32, 6, 'item_released', 'Items released', 'Items for request REQ-2YCLVJ4C have been released.', 'http://127.0.0.1:8000/requests/4', 1, '2026-07-24 20:13:53', '2026-07-24 20:16:17'),
(33, 2, 'payment_submitted', 'New student purchase', 'Joy Tienes submitted purchase PUR-MWGYFIIS.', 'http://127.0.0.1:8000/accounting/payments/4', 0, '2026-07-24 20:46:25', '2026-07-24 20:46:25'),
(34, 7, 'payment_verified', 'Payment verified', 'Payment for PUR-MWGYFIIS has been verified.', 'http://127.0.0.1:8000/purchases/4', 1, '2026-07-24 20:47:26', '2026-08-28 01:42:17'),
(35, 3, 'purchase_verified', 'Purchase ready for release', 'Purchase PUR-MWGYFIIS is ready for release.', 'http://127.0.0.1:8000/supply/purchases/4', 1, '2026-07-24 20:47:31', '2026-08-31 20:34:58'),
(36, 7, 'item_released', 'Purchase released', 'Your purchase PUR-MWGYFIIS has been released.', 'http://127.0.0.1:8000/purchases/4', 1, '2026-07-24 20:48:44', '2026-08-28 01:42:17'),
(37, 2, 'payment_submitted', 'New student purchase', 'Joy Tienes submitted purchase PUR-WSMAXFXO.', 'http://127.0.0.1:8000/accounting/payments/5', 0, '2026-08-26 05:16:49', '2026-08-26 05:16:49'),
(38, 7, 'payment_verified', 'Payment verified', 'Payment for PUR-WSMAXFXO has been verified.', 'http://127.0.0.1:8000/purchases/5', 1, '2026-08-26 05:23:30', '2026-08-28 01:19:56'),
(39, 3, 'purchase_verified', 'Purchase ready for release', 'Purchase PUR-WSMAXFXO is ready for release.', 'http://127.0.0.1:8000/supply/purchases/5', 1, '2026-08-26 05:23:35', '2026-08-31 20:34:58'),
(40, 2, 'payment_submitted', 'New student purchase', 'Joy Tienes submitted purchase PUR-AOH8WO4Q.', 'http://127.0.0.1:8000/accounting/payments/6', 0, '2026-08-28 01:18:57', '2026-08-28 01:18:57'),
(41, 2, 'payment_submitted', 'New student purchase', 'Joy Tienes submitted purchase PUR-FCP8AXGS.', 'http://127.0.0.1:8000/accounting/payments/7', 0, '2026-08-28 01:40:06', '2026-08-28 01:40:06'),
(42, 7, 'payment_verified', 'Payment verified', 'Payment for PUR-FCP8AXGS has been verified.', 'http://127.0.0.1:8000/purchases/7', 1, '2026-08-28 01:42:26', '2026-08-28 01:51:55'),
(43, 3, 'purchase_verified', 'Purchase ready for release', 'Purchase PUR-FCP8AXGS is ready for release.', 'http://127.0.0.1:8000/supply/purchases/7', 1, '2026-08-28 01:42:30', '2026-08-31 20:34:58'),
(44, 2, 'payment_submitted', 'New student purchase', 'Joy Tienes submitted purchase PUR-NOKIY96X.', 'http://127.0.0.1:8000/accounting/payments/8', 1, '2026-08-31 20:27:43', '2026-08-31 20:30:48'),
(45, 7, 'payment_verified', 'Payment verified', 'Payment for PUR-NOKIY96X has been verified.', 'http://127.0.0.1:8000/purchases/8', 0, '2026-08-31 20:30:59', '2026-08-31 20:30:59'),
(46, 3, 'purchase_verified', 'Purchase ready for release', 'Purchase PUR-NOKIY96X is ready for release.', 'http://127.0.0.1:8000/supply/purchases/8', 1, '2026-08-31 20:31:05', '2026-08-31 20:32:09'),
(47, 7, 'item_released', 'Purchase released', 'Your purchase PUR-NOKIY96X has been released.', 'http://127.0.0.1:8000/purchases/8', 0, '2026-08-31 20:32:45', '2026-08-31 20:32:45'),
(48, 2, 'new_request', 'New supply request', 'Prof. Juan Dela Cruz submitted request REQ-STEWWTLH.', 'http://127.0.0.1:8000/accounting/requests/5', 0, '2026-09-03 04:51:41', '2026-09-03 04:51:41'),
(49, 1, 'request_reviewed', 'Request ready for approval', 'Request REQ-STEWWTLH was reviewed by accounting.', 'http://127.0.0.1:8000/admin/requests/5', 0, '2026-09-03 04:53:00', '2026-09-03 04:53:00'),
(50, 9, 'request_reviewed', 'Request ready for approval', 'Request REQ-STEWWTLH was reviewed by accounting.', 'http://127.0.0.1:8000/admin/requests/5', 0, '2026-09-03 04:53:07', '2026-09-03 04:53:07'),
(51, 4, 'request_approved', 'Request approved', 'Your request REQ-STEWWTLH has been approved.', 'http://127.0.0.1:8000/requests/5', 0, '2026-09-03 04:54:00', '2026-09-03 04:54:00'),
(52, 3, 'request_approved', 'Approved request pending release', 'Request REQ-STEWWTLH is ready for release.', 'http://127.0.0.1:8000/supply/releases/5', 0, '2026-09-03 04:54:07', '2026-09-03 04:54:07'),
(53, 2, 'new_request', 'New supply request', 'Prof. Juan Dela Cruz submitted request REQ-C9211TOC.', 'http://192.168.110.177:8000/accounting/requests/6', 0, '2026-09-03 23:11:39', '2026-09-03 23:11:39'),
(54, 1, 'request_reviewed', 'Request ready for approval', 'Request REQ-C9211TOC was reviewed by accounting.', 'http://192.168.110.177:8000/admin/requests/6', 0, '2026-09-03 23:13:23', '2026-09-03 23:13:23'),
(55, 9, 'request_reviewed', 'Request ready for approval', 'Request REQ-C9211TOC was reviewed by accounting.', 'http://192.168.110.177:8000/admin/requests/6', 0, '2026-09-03 23:13:28', '2026-09-03 23:13:28'),
(56, 2, 'new_request', 'New supply request', 'Prof. Juan Dela Cruz submitted request REQ-G4DVMT9Y.', 'http://127.0.0.1:8000/accounting/requests/7', 0, '2026-09-08 00:04:34', '2026-09-08 00:04:34'),
(57, 1, 'request_reviewed', 'Request ready for approval', 'Request REQ-G4DVMT9Y was reviewed by accounting.', 'http://127.0.0.1:8000/admin/requests/7', 0, '2026-09-08 00:05:18', '2026-09-08 00:05:18'),
(58, 9, 'request_reviewed', 'Request ready for approval', 'Request REQ-G4DVMT9Y was reviewed by accounting.', 'http://127.0.0.1:8000/admin/requests/7', 0, '2026-09-08 00:05:23', '2026-09-08 00:05:23'),
(59, 2, 'payment_submitted', 'New student purchase', 'Joy Tienes submitted purchase PUR-DI58BZAM.', 'http://127.0.0.1:8000/accounting/payments/9', 1, '2026-09-08 01:27:34', '2026-09-08 01:28:15'),
(60, 7, 'payment_verified', 'Payment verified', 'Payment for PUR-DI58BZAM has been verified.', 'http://127.0.0.1:8000/purchases/9', 0, '2026-09-08 01:28:44', '2026-09-08 01:28:44'),
(61, 3, 'purchase_verified', 'Purchase ready for release', 'Purchase PUR-DI58BZAM is ready for release.', 'http://127.0.0.1:8000/supply/purchases/9', 0, '2026-09-08 01:28:49', '2026-09-08 01:28:49'),
(62, 7, 'item_released', 'Purchase released', 'Your purchase PUR-DI58BZAM has been released.', 'http://127.0.0.1:8000/purchases/9', 0, '2026-09-08 01:34:09', '2026-09-08 01:34:09'),
(63, 2, 'payment_submitted', 'New student purchase', 'Joy Tienes submitted purchase PUR-VBRVJLDT.', 'http://127.0.0.1:8000/accounting/payments/10', 0, '2026-09-08 04:11:38', '2026-09-08 04:11:38'),
(64, 7, 'payment_verified', 'Payment verified', 'Payment for PUR-VBRVJLDT has been verified.', 'http://127.0.0.1:8000/purchases/10', 0, '2026-09-08 04:13:23', '2026-09-08 04:13:23'),
(65, 3, 'purchase_verified', 'Purchase ready for release', 'Purchase PUR-VBRVJLDT is ready for release.', 'http://127.0.0.1:8000/supply/purchases/10', 0, '2026-09-08 04:13:28', '2026-09-08 04:13:28'),
(66, 7, 'payment_verified', 'Payment verified', 'Payment for PUR-AOH8WO4Q has been verified.', 'http://127.0.0.1:8000/purchases/6', 0, '2026-09-08 04:18:17', '2026-09-08 04:18:17'),
(67, 3, 'purchase_verified', 'Purchase ready for release', 'Purchase PUR-AOH8WO4Q is ready for release.', 'http://127.0.0.1:8000/supply/purchases/6', 0, '2026-09-08 04:18:23', '2026-09-08 04:18:23'),
(68, 2, 'payment_submitted', 'New student purchase', 'Joy Tienes submitted purchase PUR-R6EBCSPC.', 'http://127.0.0.1:8000/accounting/payments/11', 0, '2026-09-08 04:19:10', '2026-09-08 04:19:10'),
(69, 2, 'new_request', 'New supply request', 'Prof. Juan Dela Cruz submitted request REQ-YBCVUPA1.', 'http://127.0.0.1:8000/accounting/requests/8', 0, '2026-09-08 04:38:29', '2026-09-08 04:38:29'),
(70, 1, 'request_reviewed', 'Request ready for approval', 'Request REQ-YBCVUPA1 was reviewed by accounting.', 'http://127.0.0.1:8000/admin/requests/8', 0, '2026-09-08 04:39:20', '2026-09-08 04:39:20'),
(71, 9, 'request_reviewed', 'Request ready for approval', 'Request REQ-YBCVUPA1 was reviewed by accounting.', 'http://127.0.0.1:8000/admin/requests/8', 0, '2026-09-08 04:39:24', '2026-09-08 04:39:24'),
(72, 2, 'new_request', 'New supply request', 'Prof. Juan Dela Cruz submitted request REQ-VNJKFCK9.', 'http://127.0.0.1:8000/accounting/requests/9', 0, '2026-09-08 04:52:30', '2026-09-08 04:52:30'),
(73, 1, 'request_reviewed', 'Request ready for approval', 'Request REQ-VNJKFCK9 was reviewed by accounting.', 'http://127.0.0.1:8000/admin/requests/9', 0, '2026-09-08 04:52:58', '2026-09-08 04:52:58'),
(74, 9, 'request_reviewed', 'Request ready for approval', 'Request REQ-VNJKFCK9 was reviewed by accounting.', 'http://127.0.0.1:8000/admin/requests/9', 0, '2026-09-08 04:53:03', '2026-09-08 04:53:03'),
(75, 4, 'request_approved', 'Request approved', 'Your request REQ-VNJKFCK9 has been approved.', 'http://127.0.0.1:8000/requests/9', 0, '2026-09-08 04:53:25', '2026-09-08 04:53:25'),
(76, 3, 'request_approved', 'Approved request pending release', 'Request REQ-VNJKFCK9 is ready for release.', 'http://127.0.0.1:8000/supply/releases/9', 0, '2026-09-08 04:53:30', '2026-09-08 04:53:30'),
(77, 4, 'item_released', 'Items released', 'Items for request REQ-VNJKFCK9 have been released.', 'http://127.0.0.1:8000/requests/9', 0, '2026-09-08 04:54:19', '2026-09-08 04:54:19'),
(78, 2, 'new_request', 'New supply request', 'Prof. Juan Dela Cruz submitted request REQ-XGJYTT8T.', 'http://127.0.0.1:8000/accounting/requests/10', 0, '2026-09-08 05:08:48', '2026-09-08 05:08:48'),
(79, 1, 'request_reviewed', 'Request ready for approval', 'Request REQ-XGJYTT8T was reviewed by accounting.', 'http://127.0.0.1:8000/admin/requests/10', 0, '2026-09-08 05:09:17', '2026-09-08 05:09:17'),
(80, 9, 'request_reviewed', 'Request ready for approval', 'Request REQ-XGJYTT8T was reviewed by accounting.', 'http://127.0.0.1:8000/admin/requests/10', 0, '2026-09-08 05:09:22', '2026-09-08 05:09:22'),
(81, 4, 'request_approved', 'Request approved', 'Your request REQ-XGJYTT8T has been approved.', 'http://127.0.0.1:8000/requests/10', 0, '2026-09-08 05:09:44', '2026-09-08 05:09:44'),
(82, 3, 'request_approved', 'Approved request pending release', 'Request REQ-XGJYTT8T is ready for release.', 'http://127.0.0.1:8000/supply/releases/10', 0, '2026-09-08 05:09:49', '2026-09-08 05:09:49');

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
(4, 'PUR-MWGYFIIS', 7, 'released', 250.00, NULL, 2, 3, '2026-07-24 20:47:26', '2026-07-24 20:48:44', '2026-07-24 20:46:25', '2026-07-24 20:48:44'),
(5, 'PUR-WSMAXFXO', 7, 'payment_verified', 550.00, NULL, 2, NULL, '2026-08-26 05:23:30', NULL, '2026-08-26 05:16:49', '2026-08-26 05:23:30'),
(6, 'PUR-AOH8WO4Q', 7, 'payment_verified', 80.00, NULL, 2, NULL, '2026-09-08 04:18:17', NULL, '2026-08-28 01:18:57', '2026-09-08 04:18:17'),
(7, 'PUR-FCP8AXGS', 7, 'payment_verified', 1200.00, NULL, 2, NULL, '2026-08-28 01:42:26', NULL, '2026-08-28 01:40:06', '2026-08-28 01:42:26'),
(8, 'PUR-NOKIY96X', 7, 'released', 1200.00, NULL, 2, 3, '2026-08-31 20:30:59', '2026-08-31 20:32:45', '2026-08-31 20:27:43', '2026-08-31 20:32:45'),
(9, 'PUR-DI58BZAM', 7, 'released', 80.00, NULL, 2, 3, '2026-09-08 01:28:44', '2026-09-08 01:34:09', '2026-09-08 01:27:34', '2026-09-08 01:34:09'),
(10, 'PUR-VBRVJLDT', 7, 'payment_verified', 1200.00, NULL, 2, NULL, '2026-09-08 04:13:23', NULL, '2026-09-08 04:11:38', '2026-09-08 04:13:23'),
(11, 'PUR-R6EBCSPC', 7, 'payment_submitted', 1200.00, NULL, NULL, NULL, NULL, NULL, '2026-09-08 04:19:10', '2026-09-08 04:19:10');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_request_items`
--

CREATE TABLE `purchase_request_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_request_id` bigint(20) UNSIGNED NOT NULL,
  `inventory_id` bigint(20) UNSIGNED NOT NULL,
  `size` varchar(10) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_request_items`
--

INSERT INTO `purchase_request_items` (`id`, `purchase_request_id`, `inventory_id`, `size`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 4, NULL, 1, 250.00, 250.00, '2026-07-24 08:03:43', '2026-07-24 08:03:43'),
(2, 2, 6, NULL, 1, 95.00, 95.00, '2026-07-24 08:14:26', '2026-07-24 08:14:26'),
(3, 3, 3, NULL, 1, 35.00, 35.00, '2026-07-24 19:08:19', '2026-07-24 19:08:19'),
(4, 4, 4, NULL, 1, 250.00, 250.00, '2026-07-24 20:46:25', '2026-07-24 20:46:25'),
(5, 5, 10, 'M', 1, 550.00, 550.00, '2026-08-26 05:16:49', '2026-08-26 05:16:49'),
(6, 6, 11, NULL, 1, 80.00, 80.00, '2026-08-28 01:18:57', '2026-08-28 01:18:57'),
(7, 7, 16, 'M', 1, 1200.00, 1200.00, '2026-08-28 01:40:06', '2026-08-28 01:40:06'),
(8, 8, 16, 'M', 1, 1200.00, 1200.00, '2026-08-31 20:27:43', '2026-08-31 20:27:43'),
(9, 9, 11, NULL, 1, 80.00, 80.00, '2026-09-08 01:27:34', '2026-09-08 01:27:34'),
(10, 10, 18, 'M', 1, 1200.00, 1200.00, '2026-09-08 04:11:38', '2026-09-08 04:11:38'),
(11, 11, 16, 'L', 1, 1200.00, 1200.00, '2026-09-08 04:19:10', '2026-09-08 04:19:10');

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
(4, 'REQ-2YCLVJ4C', 6, 9, 'faculty', 'released', 'for visitors.', NULL, NULL, 10500.00, 2, 1, 3, '2026-07-24 20:10:30', '2026-07-24 20:11:56', '2026-07-24 20:13:53', '2026-07-24 20:05:07', '2026-07-24 20:13:53'),
(5, 'REQ-STEWWTLH', 4, 3, 'faculty', 'approved', 'school use', NULL, NULL, 35.00, 2, 9, NULL, '2026-09-03 04:53:00', '2026-09-03 04:54:00', NULL, '2026-09-03 04:51:41', '2026-09-03 04:54:00'),
(6, 'REQ-C9211TOC', 4, 3, 'faculty', 'admin_review', 'for student', NULL, NULL, 2750.00, 2, NULL, NULL, '2026-09-03 23:13:23', NULL, NULL, '2026-09-03 23:11:38', '2026-09-03 23:13:23'),
(7, 'REQ-G4DVMT9Y', 4, 3, 'faculty', 'admin_review', 'for students', NULL, NULL, 2250.00, 2, NULL, NULL, '2026-09-08 00:05:18', NULL, NULL, '2026-09-08 00:04:34', '2026-09-08 00:05:18'),
(8, 'REQ-YBCVUPA1', 4, 3, 'faculty', 'admin_review', 'for new computer', NULL, NULL, 150.00, 2, NULL, NULL, '2026-09-08 04:39:20', NULL, NULL, '2026-09-08 04:38:29', '2026-09-08 04:39:20'),
(9, 'REQ-VNJKFCK9', 4, 3, 'faculty', 'released', 'for office', NULL, NULL, 4500.00, 2, 9, 3, '2026-09-08 04:52:58', '2026-09-08 04:53:25', '2026-09-08 04:54:19', '2026-09-08 04:52:30', '2026-09-08 04:54:19'),
(10, 'REQ-XGJYTT8T', 4, 3, 'faculty', 'approved', 'for office', NULL, NULL, 200.00, 2, 9, NULL, '2026-09-08 05:09:17', '2026-09-08 05:09:44', NULL, '2026-09-08 05:08:48', '2026-09-08 05:09:44');

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
(4, 4, 8, 3, 3, 3, 3500.00, 10500.00, '2026-07-24 20:05:07', '2026-07-24 20:13:53'),
(5, 5, 3, 1, 1, 0, 35.00, 35.00, '2026-09-03 04:51:41', '2026-09-03 04:53:00'),
(6, 6, 10, 5, 5, 0, 550.00, 2750.00, '2026-09-03 23:11:39', '2026-09-03 23:13:23'),
(7, 7, 9, 3, 3, 0, 750.00, 2250.00, '2026-09-08 00:04:34', '2026-09-08 00:05:18'),
(8, 8, 5, 1, 1, 0, 150.00, 150.00, '2026-09-08 04:38:29', '2026-09-08 04:39:20'),
(9, 9, 8, 1, 1, 1, 4500.00, 4500.00, '2026-09-08 04:52:30', '2026-09-08 04:54:19'),
(10, 10, 7, 1, 1, 0, 200.00, 200.00, '2026-09-08 05:08:48', '2026-09-08 05:09:17');

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
(5, 'Student', 'web', '2026-07-24 06:41:04', '2026-07-24 06:41:04'),
(6, 'Admission', 'web', '2026-08-26 05:29:07', '2026-08-26 05:29:07');

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
(1, 6),
(2, 1),
(2, 3),
(3, 1),
(3, 4),
(4, 1),
(4, 2),
(5, 1),
(5, 6),
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
('8oqLdZDELaA826tEBoV0aqEm8qGkf22JQthoTEkO', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiQmdWZlZqdG9Rd0pUanRSaUk3TE9BaGoyYmNxSEZUNE9BWGxHdjlGRiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hY2NvdW50aW5nL3JlcXVlc3RzIjtzOjU6InJvdXRlIjtzOjE5OiJhY2NvdW50aW5nLnJlcXVlc3RzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MjtzOjE2OiJsYXN0X2FjdGl2aXR5X2F0IjtpOjE3ODg4NzI5Njc7fQ==', 1788872967),
('AmYFwdO0w6fluoYdunMHM8eyC0lkSxQrzG1YqQg6', 9, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoidXRnb3ZaUHlWdzI0VTRqbUZlVWJmYmhQZm11cDVUSjE3SFlaOG1QSCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM2OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vcmVxdWVzdHMiO3M6NToicm91dGUiO3M6MTQ6ImFkbWluLnJlcXVlc3RzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6OTtzOjE2OiJsYXN0X2FjdGl2aXR5X2F0IjtpOjE3ODg4NzMwNjQ7fQ==', 1788873064),
('cZyHQtDGw5fuAWcj1Tk2vcrS6ZqC39iEGihYgZms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.17.8 Chrome/144.0.7559.236 Electron/40.10.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoib0ZJeHlTVmo4aVNmYkJNZ2dKTWwweUtqY2M1VzhBUlBtSnFHTGo3TCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly9sb2NhbGhvc3QvcGVjaXQtc2lzL3B1YmxpYy9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1788867051),
('tZSG4HyMAjIEnj5ZqWpdrJXuwsDUkUs3m62qdTq4', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiTHM1T0tyZU1GYkc5NlJZY0VSMEtMd2FtdlJaR2R6a2szRUJrVHkwVCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi91c2VycyI7czo1OiJyb3V0ZSI7czoxNzoiYWRtaW4udXNlcnMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTozO3M6MTY6Imxhc3RfYWN0aXZpdHlfYXQiO2k6MTc4ODg3MzcxMDt9', 1788873710),
('Wg2dT3q5rCsJ168p6rI6UdsLx9sVfxPQfmUuhwGx', 7, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoibGpJM3BmaFpYYTRsUFo2NTA5UkdqRVFUU0JGS2tNVlM2OThqVkRQcSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9zaG9wIjtzOjU6InJvdXRlIjtzOjEwOiJzaG9wLmluZGV4Ijt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NztzOjE2OiJsYXN0X2FjdGl2aXR5X2F0IjtpOjE3ODg4NzMxNDU7czo0OiJjYXJ0IjthOjA6e319', 1788873145),
('xaIVJPWarvqIu9OoLEASD44T2TOeSqnLaOCtDyhO', 4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiT0p4ZExaOXVBZWk5MXJ5TDRCVnh4UHh5dmpsQ2NQR3VLcFN5RURTQSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjMzOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvcmVxdWVzdHMvMTAiO3M6NToicm91dGUiO3M6MTM6InJlcXVlc3RzLnNob3ciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo0O3M6MTY6Imxhc3RfYWN0aXZpdHlfYXQiO2k6MTc4ODg3MjkzNDt9', 1788872934);

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
(8, 4, 'delivery', 1, 58, 'Joy Tienes', 'Student purchase PUR-MWGYFIIS', 3, '2026-07-24 20:48:44', '2026-07-24 20:48:44'),
(9, 7, 'stock_in', 3, 5, NULL, NULL, 3, '2026-08-05 19:38:35', '2026-08-05 19:38:35'),
(10, 7, 'stock_in', 5, 10, NULL, NULL, 3, '2026-08-05 19:39:03', '2026-08-05 19:39:03'),
(11, 16, 'adjustment', 2, 42, NULL, 'Updated on-hand by size from inventory edit. (Size: 3XL)', 3, '2026-08-28 02:22:35', '2026-08-28 02:22:35'),
(12, 1, 'stock_in', 5, 124, NULL, NULL, 3, '2026-08-28 02:23:52', '2026-08-28 02:23:52'),
(13, 16, 'stock_in', 4, 46, NULL, 'Size: M', 3, '2026-08-28 02:25:07', '2026-08-28 02:25:07'),
(14, 16, 'delivery', 1, 45, 'Joy Tienes', 'Student purchase PUR-NOKIY96X (Size: M)', 3, '2026-08-31 20:32:45', '2026-08-31 20:32:45'),
(15, 11, 'delivery', 1, 199, 'Joy Tienes', 'Student purchase PUR-DI58BZAM', 3, '2026-09-08 01:34:09', '2026-09-08 01:34:09'),
(19, 8, 'delivery', 1, 4, 'Prof. Juan Dela Cruz', 'Released for REQ-VNJKFCK9', 3, '2026-09-08 04:54:19', '2026-09-08 04:54:19');

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
(16, 'TXN-JAHNCNBZLP', 4, 'release', 1, 59, 58, 'App\\Models\\PurchaseRequest', 4, 'Student purchase PUR-MWGYFIIS', 3, '2026-07-24 20:48:44', '2026-07-24 20:48:44'),
(17, 'TXN-5I3MYBNXVS', 7, 'stock_in', 3, 2, 5, NULL, NULL, NULL, 3, '2026-08-05 19:38:35', '2026-08-05 19:38:35'),
(18, 'TXN-F8PX9NLJCR', 7, 'stock_in', 5, 5, 10, NULL, NULL, NULL, 3, '2026-08-05 19:39:03', '2026-08-05 19:39:03'),
(19, 'TXN-CVC4MQHFST', 10, 'reserve', 1, 9, 60, 'App\\Models\\PurchaseRequest', 5, 'Reserved for PUR-WSMAXFXO (Size: M)', 2, '2026-08-26 05:23:30', '2026-08-26 05:23:30'),
(20, 'TXN-EFC8BEZUTW', 16, 'reserve', 1, 6, 40, 'App\\Models\\PurchaseRequest', 7, 'Reserved for PUR-FCP8AXGS (Size: M)', 2, '2026-08-28 01:42:26', '2026-08-28 01:42:26'),
(21, 'TXN-GM1UB4KXAI', 16, 'adjustment', 2, 5, 7, NULL, NULL, 'Updated on-hand by size from inventory edit. (Size: 3XL)', 3, '2026-08-28 02:22:35', '2026-08-28 02:22:35'),
(22, 'TXN-B568UWDXJO', 1, 'stock_in', 5, 119, 124, NULL, NULL, NULL, 3, '2026-08-28 02:23:52', '2026-08-28 02:23:52'),
(23, 'TXN-0EYTDQ1KWV', 16, 'stock_in', 4, 6, 10, NULL, NULL, 'Size: M', 3, '2026-08-28 02:25:07', '2026-08-28 02:25:07'),
(24, 'TXN-FJUGP8W6R3', 16, 'reserve', 1, 10, 46, 'App\\Models\\PurchaseRequest', 8, 'Reserved for PUR-NOKIY96X (Size: M)', 2, '2026-08-31 20:30:59', '2026-08-31 20:30:59'),
(25, 'TXN-X6NDROODZE', 16, 'release', 1, 10, 9, 'App\\Models\\PurchaseRequest', 8, 'Student purchase PUR-NOKIY96X (Size: M)', 3, '2026-08-31 20:32:45', '2026-08-31 20:32:45'),
(26, 'TXN-JXLXWD7X1S', 3, 'reserve', 1, 39, 39, 'App\\Models\\SupplyRequest', 5, 'Reserved for REQ-STEWWTLH', 9, '2026-09-03 04:54:00', '2026-09-03 04:54:00'),
(27, 'TXN-4SJUIREAEI', 11, 'reserve', 1, 200, 200, 'App\\Models\\PurchaseRequest', 9, 'Reserved for PUR-DI58BZAM', 2, '2026-09-08 01:28:44', '2026-09-08 01:28:44'),
(28, 'TXN-GZCLKWYXQP', 11, 'release', 1, 200, 199, 'App\\Models\\PurchaseRequest', 9, 'Student purchase PUR-DI58BZAM', 3, '2026-09-08 01:34:09', '2026-09-08 01:34:09'),
(29, 'TXN-LVICF3YEIP', 18, 'reserve', 1, 5, 5, 'App\\Models\\PurchaseRequest', 10, 'Reserved for PUR-VBRVJLDT (Size: M)', 2, '2026-09-08 04:13:23', '2026-09-08 04:13:23'),
(30, 'TXN-LUIZ5J5LDL', 11, 'reserve', 1, 199, 199, 'App\\Models\\PurchaseRequest', 6, 'Reserved for PUR-AOH8WO4Q', 2, '2026-09-08 04:18:17', '2026-09-08 04:18:17'),
(34, 'TXN-KQYXE1PAFT', 8, 'reserve', 1, 5, 5, 'App\\Models\\SupplyRequest', 9, 'Reserved for REQ-VNJKFCK9', 9, '2026-09-08 04:53:25', '2026-09-08 04:53:25'),
(35, 'TXN-HWA3JKQUHQ', 8, 'release', 1, 5, 4, 'App\\Models\\SupplyRequest', 9, 'Released for REQ-VNJKFCK9', 3, '2026-09-08 04:54:19', '2026-09-08 04:54:19'),
(36, 'TXN-SMYPT4IEJO', 7, 'reserve', 1, 10, 10, 'App\\Models\\SupplyRequest', 10, 'Reserved for REQ-XGJYTT8T', 9, '2026-09-08 05:09:44', '2026-09-08 05:09:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `last_name` varchar(255) DEFAULT NULL,
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

INSERT INTO `users` (`id`, `employee_id`, `name`, `last_name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `department_id`, `phone`, `is_active`, `last_activity_at`) VALUES
(1, 'ADM-001', 'PECIT Admin', NULL, 'admin@pecit.edu.ph', '2026-07-24 06:43:05', '$2y$12$uBKLRahlwQGoB//1TcUWhu1MoxC5s0KXFbrosSwz/QcuWv458SlG2', NULL, '2026-07-24 06:43:05', '2026-09-03 04:50:07', 7, NULL, 1, '2026-09-03 04:50:07'),
(2, 'ACC-001', 'PECIT Accounting', NULL, 'accounting@pecit.edu.ph', '2026-07-24 06:43:05', '$2y$12$k/qp1KIBk9Xk/eeW.D52cO01p3rC/z8cv2diKZ0z3sNeDnfsnD2VW', NULL, '2026-07-24 06:43:05', '2026-09-08 05:09:27', 7, NULL, 1, '2026-09-08 05:09:27'),
(3, 'SUP-001', 'Supply Officer', NULL, 'supply@pecit.edu.ph', '2026-07-24 06:43:05', '$2y$12$PdnxkklDtWHi3Dmj3UZUd.PCTqf1GXk5ptoYk1hoawRZHAmwO/vdS', NULL, '2026-07-24 06:43:05', '2026-09-08 05:21:50', 8, NULL, 1, '2026-09-08 05:21:50'),
(4, 'FAC-001', 'Prof. Juan Dela Cruz', NULL, 'faculty@pecit.edu.ph', '2026-07-24 06:43:06', '$2y$12$PAQWnYmtyK6MjCLINX8j0uOgqq9t2CjmSV0vjFux7pRCqrYuhv5H6', NULL, '2026-07-24 06:43:06', '2026-09-08 05:08:54', 3, NULL, 1, '2026-09-08 05:08:54'),
(5, 'STU-001', 'Maria Santos', 'Santos', 'student@pecit.edu.ph', '2026-07-24 06:43:06', '$2y$12$GG/Hx2.Pq//baFYJ/8zQl./eqcxDpoLT67kloWblECrxdFUjtkjUy', NULL, '2026-07-24 06:43:06', '2026-09-08 01:24:40', 4, NULL, 1, '2026-09-08 01:24:40'),
(6, '20231-00245', 'Vea Villaver', NULL, 'veapecit.edu@gmail.com', '2026-07-24 08:40:15', '$2y$12$Vcn7obbxA.yEmD/MIsUMgu2muLnXMXWUESZ.0N6f9ebXt7vfSFMI2', NULL, '2026-07-24 08:40:15', '2026-07-24 20:16:18', 9, '09123456789', 1, '2026-07-24 20:16:18'),
(7, '20231-00246', 'Joy Tienes', 'Tienes', 'tienesmaryjoy6@gmail.com', '2026-07-24 19:07:21', '$2y$12$k4YyeQ8njXjJ50N1Lqir9Os0/zrr8Ls4G.KDbWnLkNGYGnzC7J7gS', NULL, '2026-07-24 19:07:21', '2026-09-08 05:12:25', 9, '09123456789', 1, '2026-09-08 05:12:25'),
(8, 'STU-COE-001', 'Carlos Mendoza', 'Mendoza', 'engineering.student@pecit.edu.ph', '2026-08-05 20:33:00', '$2y$12$86hoJzI9M9FJynZgJefvT.MwMPgzj1np.bvYgGoHuKPe1AOg0zyFa', NULL, '2026-08-05 20:33:00', '2026-09-08 01:24:42', 3, NULL, 1, '2026-09-08 01:24:42'),
(9, 'ADN-001', 'PECIT Admission', NULL, 'admission@pecit.edu.ph', '2026-08-26 05:29:08', '$2y$12$3gg4d3OxnbTA66Y8XwLDEOmfmrCMXH.1sVfnCCYcdjfUx80QDVOku', NULL, '2026-08-26 05:29:08', '2026-09-08 05:11:04', 7, NULL, 1, '2026-09-08 05:11:04');

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
  ADD KEY `inventory_category_id_foreign` (`category_id`),
  ADD KEY `inventory_department_id_foreign` (`department_id`);

--
-- Indexes for table `inventory_size_stocks`
--
ALTER TABLE `inventory_size_stocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inventory_size_stocks_inventory_id_size_unique` (`inventory_id`,`size`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `inventory_size_stocks`
--
ALTER TABLE `inventory_size_stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `psis_notifications`
--
ALTER TABLE `psis_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `request_items`
--
ALTER TABLE `request_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `stock_logs`
--
ALTER TABLE `stock_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
  ADD CONSTRAINT `inventory_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_size_stocks`
--
ALTER TABLE `inventory_size_stocks`
  ADD CONSTRAINT `inventory_size_stocks_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE;

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
