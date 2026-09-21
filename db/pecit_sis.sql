-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: pecit_sis
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `priority` enum('low','normal','high') NOT NULL DEFAULT 'normal',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `published_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `announcements_created_by_foreign` (`created_by`),
  CONSTRAINT `announcements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (1,'Welcome to PECIT Smart Inventory System','Use this portal to request supplies, manage inventory, and track purchases. Contact Supply Personnel for stock questions.','high',1,'2026-07-24 07:10:30',NULL,1,'2026-07-24 07:10:30','2026-07-24 07:10:30');
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `model_type` varchar(255) DEFAULT NULL,
  `model_id` bigint(20) unsigned DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=136 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,4,'supply_request.created','App\\Models\\SupplyRequest',1,NULL,'{\"request_number\":\"REQ-PCR3LYIW\",\"user_id\":4,\"department_id\":1,\"type\":\"faculty\",\"status\":\"pending\",\"purpose\":\"pang sulat lang po. matsalam\",\"updated_at\":\"2026-07-24T15:55:11.000000Z\",\"created_at\":\"2026-07-24T15:55:11.000000Z\",\"id\":1,\"total_amount\":\"225.00\",\"items\":[{\"id\":1,\"request_id\":1,\"inventory_id\":2,\"quantity_requested\":5,\"quantity_approved\":null,\"quantity_released\":0,\"unit_price\":\"45.00\",\"subtotal\":\"225.00\",\"created_at\":\"2026-07-24T15:55:11.000000Z\",\"updated_at\":\"2026-07-24T15:55:11.000000Z\"}]}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 07:55:11','2026-07-24 07:55:11'),(2,5,'purchase.created','App\\Models\\PurchaseRequest',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 08:03:43','2026-07-24 08:03:43'),(3,2,'purchase.payment_verified','App\\Models\\PurchaseRequest',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 08:05:05','2026-07-24 08:05:05'),(4,2,'supply_request.accounting_review','App\\Models\\SupplyRequest',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 08:05:14','2026-07-24 08:05:14'),(5,1,'supply_request.approved','App\\Models\\SupplyRequest',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 08:08:49','2026-07-24 08:08:49'),(6,5,'purchase.created','App\\Models\\PurchaseRequest',2,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 08:14:26','2026-07-24 08:14:26'),(7,2,'purchase.payment_verified','App\\Models\\PurchaseRequest',2,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 08:15:54','2026-07-24 08:15:54'),(8,3,'supply_request.released','App\\Models\\SupplyRequest',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 08:18:00','2026-07-24 08:18:00'),(9,3,'purchase.released','App\\Models\\PurchaseRequest',2,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 08:26:28','2026-07-24 08:26:28'),(10,3,'purchase.released','App\\Models\\PurchaseRequest',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 08:26:55','2026-07-24 08:26:55'),(11,4,'supply_request.created','App\\Models\\SupplyRequest',2,NULL,'{\"request_number\":\"REQ-GMWTCZAW\",\"user_id\":4,\"department_id\":1,\"type\":\"faculty\",\"status\":\"pending\",\"purpose\":\"please hatag\",\"updated_at\":\"2026-07-24T16:31:44.000000Z\",\"created_at\":\"2026-07-24T16:31:44.000000Z\",\"id\":2,\"total_amount\":\"285.00\",\"items\":[{\"id\":2,\"request_id\":2,\"inventory_id\":1,\"quantity_requested\":1,\"quantity_approved\":null,\"quantity_released\":0,\"unit_price\":\"285.00\",\"subtotal\":\"285.00\",\"created_at\":\"2026-07-24T16:31:44.000000Z\",\"updated_at\":\"2026-07-24T16:31:44.000000Z\"}]}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 08:31:45','2026-07-24 08:31:45'),(12,2,'supply_request.accounting_review','App\\Models\\SupplyRequest',2,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 08:32:19','2026-07-24 08:32:19'),(13,1,'supply_request.approved','App\\Models\\SupplyRequest',2,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 08:33:58','2026-07-24 08:33:58'),(14,3,'supply_request.released','App\\Models\\SupplyRequest',2,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 08:35:16','2026-07-24 08:35:16'),(15,6,'supply_request.created','App\\Models\\SupplyRequest',3,NULL,'{\"request_number\":\"REQ-JYDE6HY8\",\"user_id\":6,\"department_id\":9,\"type\":\"faculty\",\"status\":\"pending\",\"purpose\":\"mang guna me\",\"updated_at\":\"2026-07-24T16:41:10.000000Z\",\"created_at\":\"2026-07-24T16:41:10.000000Z\",\"id\":3,\"total_amount\":\"3600.00\",\"items\":[{\"id\":3,\"request_id\":3,\"inventory_id\":7,\"quantity_requested\":20,\"quantity_approved\":null,\"quantity_released\":0,\"unit_price\":\"180.00\",\"subtotal\":\"3600.00\",\"created_at\":\"2026-07-24T16:41:10.000000Z\",\"updated_at\":\"2026-07-24T16:41:10.000000Z\"}]}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 08:41:10','2026-07-24 08:41:10'),(16,2,'supply_request.accounting_review','App\\Models\\SupplyRequest',3,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 08:42:44','2026-07-24 08:42:44'),(17,1,'supply_request.approved','App\\Models\\SupplyRequest',3,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 08:43:38','2026-07-24 08:43:38'),(18,3,'supply_request.released','App\\Models\\SupplyRequest',3,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 08:44:31','2026-07-24 08:44:31'),(19,7,'purchase.created','App\\Models\\PurchaseRequest',3,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 19:08:39','2026-07-24 19:08:39'),(20,2,'purchase.payment_verified','App\\Models\\PurchaseRequest',3,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 19:10:50','2026-07-24 19:10:50'),(21,3,'purchase.released','App\\Models\\PurchaseRequest',3,NULL,'{\"deducted\":[\"Whiteboard Eraser x1\"]}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 19:11:56','2026-07-24 19:11:56'),(22,6,'supply_request.created','App\\Models\\SupplyRequest',4,NULL,'{\"request_number\":\"REQ-2YCLVJ4C\",\"user_id\":6,\"department_id\":9,\"type\":\"faculty\",\"status\":\"pending\",\"purpose\":\"for visitors.\",\"updated_at\":\"2026-07-25T04:05:07.000000Z\",\"created_at\":\"2026-07-25T04:05:07.000000Z\",\"id\":4,\"total_amount\":\"10500.00\",\"items\":[{\"id\":4,\"request_id\":4,\"inventory_id\":8,\"quantity_requested\":3,\"quantity_approved\":null,\"quantity_released\":0,\"unit_price\":\"3500.00\",\"subtotal\":\"10500.00\",\"created_at\":\"2026-07-25T04:05:07.000000Z\",\"updated_at\":\"2026-07-25T04:05:07.000000Z\"}]}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 20:05:15','2026-07-24 20:05:15'),(23,2,'supply_request.accounting_review','App\\Models\\SupplyRequest',4,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 20:10:35','2026-07-24 20:10:35'),(24,1,'supply_request.approved','App\\Models\\SupplyRequest',4,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 20:12:02','2026-07-24 20:12:02'),(25,3,'supply_request.released','App\\Models\\SupplyRequest',4,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 20:13:57','2026-07-24 20:13:57'),(26,7,'purchase.created','App\\Models\\PurchaseRequest',4,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 20:46:30','2026-07-24 20:46:30'),(27,2,'purchase.payment_verified','App\\Models\\PurchaseRequest',4,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 20:47:33','2026-07-24 20:47:33'),(28,3,'purchase.released','App\\Models\\PurchaseRequest',4,NULL,'{\"deducted\":[\"Bottled Water 500ml x1\"]}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-24 20:48:48','2026-07-24 20:48:48'),(29,3,'student.updated','App\\Models\\User',7,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-05 20:15:48','2026-08-05 20:15:48'),(30,7,'purchase.created','App\\Models\\PurchaseRequest',5,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0','2026-08-26 05:17:06','2026-08-26 05:17:06'),(31,2,'purchase.payment_verified','App\\Models\\PurchaseRequest',5,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0','2026-08-26 05:23:42','2026-08-26 05:23:42'),(32,7,'purchase.created','App\\Models\\PurchaseRequest',6,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-28 01:19:20','2026-08-28 01:19:20'),(33,7,'purchase.created','App\\Models\\PurchaseRequest',7,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-28 01:40:11','2026-08-28 01:40:11'),(34,2,'purchase.payment_verified','App\\Models\\PurchaseRequest',7,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-28 01:42:34','2026-08-28 01:42:34'),(35,7,'purchase.created','App\\Models\\PurchaseRequest',8,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-31 20:28:12','2026-08-31 20:28:12'),(36,2,'purchase.payment_verified','App\\Models\\PurchaseRequest',8,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-31 20:31:11','2026-08-31 20:31:11'),(37,3,'purchase.released','App\\Models\\PurchaseRequest',8,NULL,'{\"deducted\":[\"Computer Studies Uniform (Exclusive) (M) x1\"]}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','2026-08-31 20:32:51','2026-08-31 20:32:51'),(38,4,'supply_request.created','App\\Models\\SupplyRequest',5,NULL,'{\"request_number\":\"REQ-STEWWTLH\",\"user_id\":4,\"department_id\":3,\"type\":\"faculty\",\"status\":\"pending\",\"purpose\":\"school use\",\"updated_at\":\"2026-09-03T12:51:41.000000Z\",\"created_at\":\"2026-09-03T12:51:41.000000Z\",\"id\":5,\"total_amount\":\"35.00\",\"items\":[{\"id\":5,\"request_id\":5,\"inventory_id\":3,\"quantity_requested\":1,\"quantity_approved\":null,\"quantity_released\":0,\"unit_price\":\"35.00\",\"subtotal\":\"35.00\",\"created_at\":\"2026-09-03T12:51:41.000000Z\",\"updated_at\":\"2026-09-03T12:51:41.000000Z\"}]}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0','2026-09-03 04:52:08','2026-09-03 04:52:08'),(39,2,'supply_request.accounting_review','App\\Models\\SupplyRequest',5,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-03 04:53:14','2026-09-03 04:53:14'),(40,9,'supply_request.approved','App\\Models\\SupplyRequest',5,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-03 04:54:15','2026-09-03 04:54:15'),(41,4,'supply_request.created','App\\Models\\SupplyRequest',6,NULL,'{\"request_number\":\"REQ-C9211TOC\",\"user_id\":4,\"department_id\":3,\"type\":\"faculty\",\"status\":\"pending\",\"purpose\":\"for student\",\"updated_at\":\"2026-09-04T07:11:39.000000Z\",\"created_at\":\"2026-09-04T07:11:38.000000Z\",\"id\":6,\"total_amount\":\"2750.00\",\"items\":[{\"id\":6,\"request_id\":6,\"inventory_id\":10,\"quantity_requested\":5,\"quantity_approved\":null,\"quantity_released\":0,\"unit_price\":\"550.00\",\"subtotal\":\"2750.00\",\"created_at\":\"2026-09-04T07:11:39.000000Z\",\"updated_at\":\"2026-09-04T07:11:39.000000Z\"}]}','192.168.110.165','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36','2026-09-03 23:11:59','2026-09-03 23:11:59'),(42,2,'supply_request.accounting_review','App\\Models\\SupplyRequest',6,NULL,NULL,'192.168.110.177','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-03 23:13:33','2026-09-03 23:13:33'),(43,4,'supply_request.created','App\\Models\\SupplyRequest',7,NULL,'{\"request_number\":\"REQ-G4DVMT9Y\",\"user_id\":4,\"department_id\":3,\"type\":\"faculty\",\"status\":\"pending\",\"purpose\":\"for students\",\"updated_at\":\"2026-09-08T08:04:34.000000Z\",\"created_at\":\"2026-09-08T08:04:34.000000Z\",\"id\":7,\"total_amount\":\"1950.00\",\"items\":[{\"id\":7,\"request_id\":7,\"inventory_id\":9,\"quantity_requested\":3,\"quantity_approved\":null,\"quantity_released\":0,\"unit_price\":\"650.00\",\"subtotal\":\"1950.00\",\"created_at\":\"2026-09-08T08:04:34.000000Z\",\"updated_at\":\"2026-09-08T08:04:34.000000Z\"}]}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0','2026-09-08 00:04:46','2026-09-08 00:04:46'),(44,2,'supply_request.accounting_review','App\\Models\\SupplyRequest',7,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 00:05:28','2026-09-08 00:05:28'),(45,3,'auth.login','App\\Models\\User',3,NULL,'{\"login_as\":\"staff\"}','127.0.0.1',NULL,'2026-09-08 00:54:20','2026-09-08 00:54:20'),(46,3,'auth.login','App\\Models\\User',3,NULL,'{\"login_as\":\"staff\"}','127.0.0.1',NULL,'2026-09-08 00:54:43','2026-09-08 00:54:43'),(47,3,'inventory.created','App\\Models\\Inventory',17,NULL,'{\"item_code\":\"TMP-06c300fc\",\"item_name\":\"Temporary Add Item Check\",\"quantity\":2,\"size\":null}','127.0.0.1',NULL,'2026-09-08 00:54:45','2026-09-08 00:54:45'),(48,3,'auth.login','App\\Models\\User',3,NULL,'{\"login_as\":\"staff\"}','127.0.0.1',NULL,'2026-09-08 01:12:24','2026-09-08 01:12:24'),(49,5,'auth.login','App\\Models\\User',5,NULL,'{\"login_as\":\"student\"}','127.0.0.1',NULL,'2026-09-08 01:12:26','2026-09-08 01:12:26'),(50,8,'auth.login','App\\Models\\User',8,NULL,'{\"login_as\":\"student\"}','127.0.0.1',NULL,'2026-09-08 01:12:29','2026-09-08 01:12:29'),(51,9,'auth.login','App\\Models\\User',9,NULL,'{\"login_as\":\"staff\"}','127.0.0.1',NULL,'2026-09-08 01:12:32','2026-09-08 01:12:32'),(52,3,'auth.login','App\\Models\\User',3,NULL,'{\"login_as\":\"staff\"}','127.0.0.1',NULL,'2026-09-08 01:24:35','2026-09-08 01:24:35'),(53,5,'auth.login','App\\Models\\User',5,NULL,'{\"login_as\":\"student\"}','127.0.0.1',NULL,'2026-09-08 01:24:38','2026-09-08 01:24:38'),(54,8,'auth.login','App\\Models\\User',8,NULL,'{\"login_as\":\"student\"}','127.0.0.1',NULL,'2026-09-08 01:24:41','2026-09-08 01:24:41'),(55,9,'auth.login','App\\Models\\User',9,NULL,'{\"login_as\":\"staff\"}','127.0.0.1',NULL,'2026-09-08 01:24:44','2026-09-08 01:24:44'),(56,4,'auth.login','App\\Models\\User',4,NULL,'{\"login_as\":\"staff\"}','127.0.0.1',NULL,'2026-09-08 01:24:49','2026-09-08 01:24:49'),(57,7,'purchase.created','App\\Models\\PurchaseRequest',9,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 01:27:45','2026-09-08 01:27:45'),(58,2,'purchase.payment_verified','App\\Models\\PurchaseRequest',9,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 01:28:53','2026-09-08 01:28:53'),(59,3,'purchase.released','App\\Models\\PurchaseRequest',9,NULL,'{\"deducted\":[\"Lanyard for ID x1\"]}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 01:34:15','2026-09-08 01:34:15'),(60,2,'auth.login','App\\Models\\User',2,NULL,'{\"login_as\":\"staff\"}','127.0.0.1',NULL,'2026-09-08 01:36:11','2026-09-08 01:36:11'),(61,7,'auth.logout','App\\Models\\User',7,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 01:36:19','2026-09-08 01:36:19'),(62,7,'auth.login','App\\Models\\User',7,NULL,'{\"login_as\":\"student\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 01:36:40','2026-09-08 01:36:40'),(63,7,'auth.logout','App\\Models\\User',7,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 01:36:47','2026-09-08 01:36:47'),(64,7,'auth.login','App\\Models\\User',7,NULL,'{\"login_as\":\"student\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 01:37:43','2026-09-08 01:37:43'),(65,3,'inventory.updated','App\\Models\\Inventory',16,'{\"item_code\":\"UNI-CCS\",\"item_name\":\"Computer Studies Uniform (Exclusive)\",\"unit_price\":\"1200.00\",\"minimum_stock\":10,\"student_shop\":true,\"department_id\":9,\"status\":\"available\"}','{\"item_code\":\"UNI-CCS\",\"item_name\":\"Computer Studies Type A Uniform (Exclusive)\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 02:47:39','2026-09-08 02:47:39'),(66,3,'inventory.created','App\\Models\\Inventory',18,NULL,'{\"item_code\":\"UNI-CCS-B\",\"item_name\":\"Computer Studies Type B Uniform (Exclusive)\",\"quantity\":5,\"size\":\"M\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 02:49:53','2026-09-08 02:49:53'),(67,3,'inventory.updated','App\\Models\\Inventory',18,'{\"item_code\":\"UNI-CCS-B\",\"item_name\":\"Computer Studies Type B Uniform (Exclusive)\",\"unit_price\":\"350.00\",\"minimum_stock\":10,\"student_shop\":true,\"department_id\":9,\"status\":\"low_stock\"}','{\"item_code\":\"UNI-CCS-B\",\"item_name\":\"Computer Studies Type B Uniform (Exclusive)\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 04:11:00','2026-09-08 04:11:00'),(68,7,'purchase.created','App\\Models\\PurchaseRequest',10,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 04:11:50','2026-09-08 04:11:50'),(69,2,'auth.login','App\\Models\\User',2,NULL,'{\"login_as\":\"staff\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 04:12:32','2026-09-08 04:12:32'),(70,2,'purchase.payment_verified','App\\Models\\PurchaseRequest',10,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 04:13:32','2026-09-08 04:13:32'),(71,2,'purchase.payment_verified','App\\Models\\PurchaseRequest',6,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 04:18:27','2026-09-08 04:18:27'),(72,7,'purchase.created','App\\Models\\PurchaseRequest',11,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 04:19:16','2026-09-08 04:19:16'),(73,NULL,'auth.login_failed',NULL,NULL,NULL,'{\"login_as\":\"staff\",\"email\":\"faculty@pecit.edu.ph\",\"student_id\":null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0','2026-09-08 04:37:21','2026-09-08 04:37:21'),(74,4,'auth.login','App\\Models\\User',4,NULL,'{\"login_as\":\"staff\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0','2026-09-08 04:37:27','2026-09-08 04:37:27'),(75,4,'supply_request.created','App\\Models\\SupplyRequest',8,NULL,'{\"request_number\":\"REQ-YBCVUPA1\",\"user_id\":4,\"department_id\":3,\"type\":\"faculty\",\"status\":\"pending\",\"purpose\":\"for new computer\",\"updated_at\":\"2026-09-08T12:38:29.000000Z\",\"created_at\":\"2026-09-08T12:38:29.000000Z\",\"id\":8,\"total_amount\":\"120.00\",\"items\":[{\"id\":8,\"request_id\":8,\"inventory_id\":5,\"quantity_requested\":1,\"quantity_approved\":null,\"quantity_released\":0,\"unit_price\":\"120.00\",\"subtotal\":\"120.00\",\"created_at\":\"2026-09-08T12:38:29.000000Z\",\"updated_at\":\"2026-09-08T12:38:29.000000Z\"}]}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0','2026-09-08 04:38:35','2026-09-08 04:38:35'),(76,2,'supply_request.accounting_review','App\\Models\\SupplyRequest',8,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 04:39:29','2026-09-08 04:39:29'),(77,9,'auth.login','App\\Models\\User',9,NULL,'{\"login_as\":\"staff\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0','2026-09-08 04:39:45','2026-09-08 04:39:45'),(78,7,'auth.logout','App\\Models\\User',7,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 04:46:03','2026-09-08 04:46:03'),(79,7,'auth.login','App\\Models\\User',7,NULL,'{\"login_as\":\"student\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 04:46:16','2026-09-08 04:46:16'),(80,3,'inventory.created','App\\Models\\Inventory',19,NULL,'{\"item_code\":\"UNI-CCS-C\",\"item_name\":\"Computer Studies Type C Uniform (Exclusive)\",\"quantity\":10,\"size\":\"S\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 04:50:34','2026-09-08 04:50:34'),(81,3,'inventory.adjusted','App\\Models\\Inventory',19,'{\"quantity\":0}','{\"item\":\"Computer Studies Type C Uniform (Exclusive)\",\"quantity\":10,\"size\":\"XS\",\"notes\":\"Updated on-hand by size from inventory edit. (Size: XS)\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 04:51:49','2026-09-08 04:51:49'),(82,3,'inventory.adjusted','App\\Models\\Inventory',19,'{\"quantity\":0}','{\"item\":\"Computer Studies Type C Uniform (Exclusive)\",\"quantity\":10,\"size\":\"M\",\"notes\":\"Updated on-hand by size from inventory edit. (Size: M)\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 04:51:49','2026-09-08 04:51:49'),(83,3,'inventory.adjusted','App\\Models\\Inventory',19,'{\"quantity\":0}','{\"item\":\"Computer Studies Type C Uniform (Exclusive)\",\"quantity\":10,\"size\":\"L\",\"notes\":\"Updated on-hand by size from inventory edit. (Size: L)\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 04:51:49','2026-09-08 04:51:49'),(84,3,'inventory.updated','App\\Models\\Inventory',19,'{\"item_code\":\"UNI-CCS-C\",\"item_name\":\"Computer Studies Type C Uniform (Exclusive)\",\"unit_price\":\"1500.00\",\"minimum_stock\":10,\"student_shop\":true,\"department_id\":9,\"status\":\"low_stock\"}','{\"item_code\":\"UNI-CCS-C\",\"item_name\":\"Computer Studies Type C Uniform (Exclusive)\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 04:51:49','2026-09-08 04:51:49'),(85,4,'supply_request.created','App\\Models\\SupplyRequest',9,NULL,'{\"request_number\":\"REQ-VNJKFCK9\",\"user_id\":4,\"department_id\":3,\"type\":\"faculty\",\"status\":\"pending\",\"purpose\":\"for office\",\"updated_at\":\"2026-09-08T12:52:30.000000Z\",\"created_at\":\"2026-09-08T12:52:30.000000Z\",\"id\":9,\"total_amount\":\"3500.00\",\"items\":[{\"id\":9,\"request_id\":9,\"inventory_id\":8,\"quantity_requested\":1,\"quantity_approved\":null,\"quantity_released\":0,\"unit_price\":\"3500.00\",\"subtotal\":\"3500.00\",\"created_at\":\"2026-09-08T12:52:30.000000Z\",\"updated_at\":\"2026-09-08T12:52:30.000000Z\"}]}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0','2026-09-08 04:52:36','2026-09-08 04:52:36'),(86,2,'supply_request.accounting_review','App\\Models\\SupplyRequest',9,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 04:53:08','2026-09-08 04:53:08'),(87,9,'supply_request.approved','App\\Models\\SupplyRequest',9,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0','2026-09-08 04:53:35','2026-09-08 04:53:35'),(88,3,'supply_request.released','App\\Models\\SupplyRequest',9,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 04:54:35','2026-09-08 04:54:35'),(89,3,'inventory.deleted','App\\Models\\Inventory',19,'{\"item_code\":\"UNI-CCS-C\",\"item_name\":\"Computer Studies Type C Uniform (Exclusive)\",\"quantity\":40}',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 05:05:03','2026-09-08 05:05:03'),(90,3,'inventory.updated','App\\Models\\Inventory',18,'{\"item_code\":\"UNI-CCS-B\",\"item_name\":\"Computer Studies Type B Uniform (Exclusive)\",\"unit_price\":\"1200.00\",\"minimum_stock\":10,\"student_shop\":true,\"department_id\":9,\"status\":\"low_stock\"}','{\"item_code\":\"UNI-CCS-C\",\"item_name\":\"Computer Studies Type C Uniform (Exclusive)\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 05:05:52','2026-09-08 05:05:52'),(91,3,'inventory.created','App\\Models\\Inventory',20,NULL,'{\"item_code\":\"UNI-CCS-B\",\"item_name\":\"Computer Studies Type B Uniform (Exclusive)\",\"quantity\":10,\"size\":\"S\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 05:08:05','2026-09-08 05:08:05'),(92,4,'supply_request.created','App\\Models\\SupplyRequest',10,NULL,'{\"request_number\":\"REQ-XGJYTT8T\",\"user_id\":4,\"department_id\":3,\"type\":\"faculty\",\"status\":\"pending\",\"purpose\":\"for office\",\"updated_at\":\"2026-09-08T13:08:48.000000Z\",\"created_at\":\"2026-09-08T13:08:48.000000Z\",\"id\":10,\"total_amount\":\"180.00\",\"items\":[{\"id\":10,\"request_id\":10,\"inventory_id\":7,\"quantity_requested\":1,\"quantity_approved\":null,\"quantity_released\":0,\"unit_price\":\"180.00\",\"subtotal\":\"180.00\",\"created_at\":\"2026-09-08T13:08:48.000000Z\",\"updated_at\":\"2026-09-08T13:08:48.000000Z\"}]}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0','2026-09-08 05:08:54','2026-09-08 05:08:54'),(93,2,'supply_request.accounting_review','App\\Models\\SupplyRequest',10,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 05:09:27','2026-09-08 05:09:27'),(94,9,'supply_request.approved','App\\Models\\SupplyRequest',10,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0','2026-09-08 05:09:54','2026-09-08 05:09:54'),(95,7,'auth.logout','App\\Models\\User',7,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 05:12:05','2026-09-08 05:12:05'),(96,7,'auth.login','App\\Models\\User',7,NULL,'{\"login_as\":\"student\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 05:12:17','2026-09-08 05:12:17'),(97,3,'auth.login','App\\Models\\User',3,NULL,'{\"login_as\":\"staff\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-10 07:38:41','2026-09-10 07:38:41'),(98,3,'inventory.deleted','App\\Models\\Inventory',20,'{\"item_code\":\"UNI-CCS-B\",\"item_name\":\"Computer Studies Type B Uniform (Exclusive)\",\"quantity\":10}',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-10 07:56:05','2026-09-10 07:56:05'),(99,3,'auth.logout','App\\Models\\User',3,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-10 08:16:48','2026-09-10 08:16:48'),(100,7,'auth.login','App\\Models\\User',7,NULL,'{\"login_as\":\"student\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-10 08:16:55','2026-09-10 08:16:55'),(101,7,'auth.login','App\\Models\\User',7,NULL,'{\"login_as\":\"student\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-12 07:10:51','2026-09-12 07:10:51'),(102,2,'auth.login','App\\Models\\User',2,NULL,'{\"login_as\":\"staff\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-12 07:11:34','2026-09-12 07:11:34'),(103,7,'purchase.created','App\\Models\\PurchaseRequest',12,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-12 07:12:03','2026-09-12 07:12:03'),(104,NULL,'auth.login_failed',NULL,NULL,NULL,'{\"login_as\":\"staff\",\"email\":\"supply@pecit.edu.ph\",\"student_id\":null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-13 18:47:24','2026-09-13 18:47:24'),(105,3,'auth.login','App\\Models\\User',3,NULL,'{\"login_as\":\"staff\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-13 18:47:32','2026-09-13 18:47:32'),(106,3,'inventory.stock_in','App\\Models\\Inventory',2,NULL,'{\"item\":\"Board Marker (Black)\",\"quantity\":5,\"size\":null,\"notes\":\"TRY LNG\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-13 18:59:37','2026-09-13 18:59:37'),(107,3,'inventory.stock_in','App\\Models\\Inventory',4,NULL,'{\"item\":\"Bottled Water 500ml\",\"quantity\":50,\"size\":null,\"notes\":\"for kids\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-13 19:03:06','2026-09-13 19:03:06'),(108,3,'supplier.created','App\\Models\\Supplier',5,NULL,'{\"name\":\"Joy Tienes\",\"supplier_code\":\"SUP-0005\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-13 19:05:25','2026-09-13 19:05:25'),(109,3,'supplier.toggled','App\\Models\\Supplier',5,NULL,'{\"name\":\"Joy Tienes\",\"is_active\":false}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-13 19:05:40','2026-09-13 19:05:40'),(110,3,'supplier.toggled','App\\Models\\Supplier',5,NULL,'{\"name\":\"Joy Tienes\",\"is_active\":true}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-13 19:06:02','2026-09-13 19:06:02'),(111,3,'inventory.stock_out','App\\Models\\Inventory',11,NULL,'{\"item\":\"Lanyard for ID\",\"quantity\":98,\"size\":null,\"notes\":\"damage\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-13 19:09:05','2026-09-13 19:09:05'),(112,3,'inventory.adjusted','App\\Models\\Inventory',11,'{\"quantity\":101}','{\"item\":\"Lanyard for ID\",\"quantity\":99,\"size\":null,\"notes\":\"lost\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-13 19:10:52','2026-09-13 19:10:52'),(113,3,'auth.logout','App\\Models\\User',3,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-13 20:58:09','2026-09-13 20:58:09'),(114,7,'auth.login','App\\Models\\User',7,NULL,'{\"login_as\":\"student\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-13 20:58:16','2026-09-13 20:58:16'),(115,7,'purchase.created','App\\Models\\PurchaseRequest',13,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-13 20:58:40','2026-09-13 20:58:40'),(116,7,'auth.logout','App\\Models\\User',7,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-13 21:00:12','2026-09-13 21:00:12'),(117,3,'auth.login','App\\Models\\User',3,NULL,'{\"login_as\":\"staff\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-13 21:00:31','2026-09-13 21:00:31'),(118,3,'auth.login','App\\Models\\User',3,NULL,'{\"login_as\":\"staff\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-14 05:36:06','2026-09-14 05:36:06'),(119,4,'auth.login','App\\Models\\User',4,NULL,'{\"login_as\":\"staff\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-14 17:42:30','2026-09-14 17:42:30'),(120,3,'auth.login','App\\Models\\User',3,NULL,'{\"login_as\":\"staff\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-14 17:46:13','2026-09-14 17:46:13'),(121,4,'auth.logout','App\\Models\\User',4,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-14 17:50:52','2026-09-14 17:50:52'),(122,7,'auth.login','App\\Models\\User',7,NULL,'{\"login_as\":\"student\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-14 17:51:16','2026-09-14 17:51:16'),(123,7,'purchase.created','App\\Models\\PurchaseRequest',14,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-14 17:51:49','2026-09-14 17:51:49'),(124,7,'auth.logout','App\\Models\\User',7,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-14 17:52:14','2026-09-14 17:52:14'),(125,2,'auth.login','App\\Models\\User',2,NULL,'{\"login_as\":\"staff\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-14 17:52:27','2026-09-14 17:52:27'),(126,2,'purchase.payment_verified','App\\Models\\PurchaseRequest',14,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-14 17:52:51','2026-09-14 17:52:51'),(127,3,'supply_request.released','App\\Models\\SupplyRequest',10,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-14 17:53:29','2026-09-14 17:53:29'),(128,2,'auth.logout','App\\Models\\User',2,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-14 18:07:42','2026-09-14 18:07:42'),(129,2,'auth.login','App\\Models\\User',2,NULL,'{\"login_as\":\"staff\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-14 18:07:59','2026-09-14 18:07:59'),(130,2,'auth.logout','App\\Models\\User',2,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-14 18:08:10','2026-09-14 18:08:10'),(131,NULL,'auth.login_failed',NULL,NULL,NULL,'{\"login_as\":\"staff\",\"email\":\"faculty@pecit.edu.ph\",\"student_id\":null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-14 18:08:22','2026-09-14 18:08:22'),(132,NULL,'auth.login_failed',NULL,NULL,NULL,'{\"login_as\":\"staff\",\"email\":\"faculty@pecit.edu.ph\",\"student_id\":null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-14 18:08:28','2026-09-14 18:08:28'),(133,NULL,'auth.login_failed',NULL,NULL,NULL,'{\"login_as\":\"staff\",\"email\":\"faculty@pecit.edu.ph\",\"student_id\":null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-14 18:08:43','2026-09-14 18:08:43'),(134,4,'auth.login','App\\Models\\User',4,NULL,'{\"login_as\":\"staff\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-14 18:08:51','2026-09-14 18:08:51'),(135,4,'auth.logout','App\\Models\\User',4,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-14 18:09:27','2026-09-14 18:09:27');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('pecit-smart-inventory-system-cache-20231-00245|127.0.0.1','i:3;',1787746939),('pecit-smart-inventory-system-cache-20231-00245|127.0.0.1:timer','i:1787746939;',1787746939),('pecit-smart-inventory-system-cache-admin@edu.ph|127.0.0.1','i:1;',1784906065),('pecit-smart-inventory-system-cache-admin@edu.ph|127.0.0.1:timer','i:1784906065;',1784906065),('pecit-smart-inventory-system-cache-spatie.permission.cache','a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:12:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:14:\"inventory.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:16:\"inventory.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:15:\"requests.submit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:15:\"requests.review\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:16:\"requests.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:16:\"requests.release\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:18:\"purchases.checkout\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:16:\"purchases.verify\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:12:\"users.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:12:\"reports.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:10:\"audit.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:20:\"announcements.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:6:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:13:\"Administrator\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:10:\"Accounting\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:16:\"Supply Personnel\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:7:\"Faculty\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";i:5;s:1:\"b\";s:7:\"Student\";s:1:\"c\";s:3:\"web\";}i:5;a:3:{s:1:\"a\";i:6;s:1:\"b\";s:9:\"Admission\";s:1:\"c\";s:3:\"web\";}}}',1789440455);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Office Supplies','office-supplies','Office Supplies for PECIT campuses.',1,'2026-07-24 06:42:41','2026-07-24 06:42:41'),(2,'Classroom Supplies','classroom-supplies','Classroom Supplies for PECIT campuses.',1,'2026-07-24 06:42:41','2026-07-24 06:42:41'),(3,'Laboratory Supplies','laboratory-supplies','Laboratory Supplies for PECIT campuses.',1,'2026-07-24 06:42:41','2026-07-24 06:42:41'),(4,'Computer Supplies','computer-supplies','Computer Supplies for PECIT campuses.',1,'2026-07-24 06:42:41','2026-07-24 06:42:41'),(5,'Cleaning Supplies','cleaning-supplies','Cleaning Supplies for PECIT campuses.',1,'2026-07-24 06:42:41','2026-07-24 06:42:41'),(6,'Pantry Supplies','pantry-supplies','Pantry Supplies for PECIT campuses.',1,'2026-07-24 06:42:41','2026-07-24 06:42:41'),(7,'Maintenance Supplies','maintenance-supplies','Maintenance Supplies for PECIT campuses.',1,'2026-07-24 06:42:41','2026-07-24 06:42:41'),(8,'Furniture','furniture','Furniture for PECIT campuses.',1,'2026-07-24 06:42:41','2026-07-24 06:42:41'),(9,'Others','others','Others for PECIT campuses.',1,'2026-07-24 06:42:41','2026-07-24 06:42:41'),(10,'Shirts','shirts',NULL,1,'2026-07-24 07:47:35','2026-07-24 07:47:35'),(11,'Uniforms','uniforms','Student uniforms and related items for the shop.',1,'2026-08-05 19:57:20','2026-08-05 19:57:20');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `faculty_budget_limit` decimal(12,2) NOT NULL DEFAULT 10000.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (6,'Senior High School','SHS',NULL,1,'2026-07-24 06:43:04','2026-07-24 06:43:04',10000.00),(7,'Administration','ADMIN',NULL,1,'2026-07-24 06:43:04','2026-07-24 06:43:04',10000.00),(8,'Supply Office','SUPPLY',NULL,1,'2026-07-24 06:43:04','2026-07-24 06:43:04',10000.00),(9,'College of Computer Studies','CCS',NULL,1,'2026-07-24 07:53:35','2026-07-24 07:53:35',10000.00),(10,'College of Criminology','CC',NULL,1,'2026-09-10 07:48:14','2026-09-10 07:48:14',10000.00),(11,'College of Tourism and Hospitality Management','CTHM',NULL,1,'2026-09-10 07:48:14','2026-09-10 07:48:14',10000.00),(12,'College of Teacher Education','CTE',NULL,1,'2026-09-10 07:48:14','2026-09-10 07:48:14',10000.00),(13,'College of Business Administration','CBA',NULL,1,'2026-09-10 07:48:14','2026-09-10 07:48:14',10000.00);
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_code` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `unit` varchar(255) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `reserved_quantity` int(11) NOT NULL DEFAULT 0,
  `minimum_stock` int(11) NOT NULL DEFAULT 10,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('available','low_stock','out_of_stock','discontinued') NOT NULL DEFAULT 'available',
  `student_shop` tinyint(1) NOT NULL DEFAULT 0,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `unit_of_measurement_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_item_code_unique` (`item_code`),
  KEY `inventory_category_id_foreign` (`category_id`),
  KEY `inventory_department_id_foreign` (`department_id`),
  KEY `inventory_unit_of_measurement_id_foreign` (`unit_of_measurement_id`),
  CONSTRAINT `inventory_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_unit_of_measurement_id_foreign` FOREIGN KEY (`unit_of_measurement_id`) REFERENCES `units_of_measurement` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory`
--

LOCK TABLES `inventory` WRITE;
/*!40000 ALTER TABLE `inventory` DISABLE KEYS */;
INSERT INTO `inventory` VALUES (1,'PECIT-BONDPAPE','Bond Paper A4','PECIT standard Bond Paper A4',1,'ream',285.00,124,0,30,'Supply Room A','available',0,NULL,'2026-07-24 06:43:04','2026-08-28 02:23:52',4),(2,'PECIT-BOARDMAR','Board Marker (Black)','PECIT standard Board Marker (Black)',2,'piece',45.00,85,0,25,'Supply Room A','available',0,NULL,'2026-07-24 06:43:04','2026-09-13 18:59:37',1),(3,'PECIT-WHITEBOA','Whiteboard Eraser','PECIT standard Whiteboard Eraser',2,'piece',35.00,39,1,15,'Supply Room A','available',0,NULL,'2026-07-24 06:43:04','2026-09-03 04:54:00',1),(4,'PECIT-BOTTLEDW','Bottled Water 500ml','PECIT standard Bottled Water 500ml',6,'case',250.00,108,0,20,'Pantry','available',0,NULL,'2026-07-24 06:43:04','2026-09-13 19:03:06',9),(5,'PECIT-ETHERNET','Ethernet Cable Cat6','PECIT standard Ethernet Cable Cat6',4,'piece',120.00,35,0,10,'IT Stock Room','available',0,NULL,'2026-07-24 06:43:04','2026-07-24 06:43:04',1),(6,'PECIT-DISINFEC','Disinfectant Spray','PECIT standard Disinfectant Spray',5,'bottle',95.00,27,0,12,'Janitorial','available',0,NULL,'2026-07-24 06:43:04','2026-07-24 08:26:28',8),(7,'PECIT-LABORATO','Laboratory Gloves','PECIT standard Laboratory Gloves',3,'box',180.00,9,0,8,'Lab Store','available',0,NULL,'2026-07-24 06:43:04','2026-09-14 17:53:23',2),(8,'PECIT-OFFICECH','Office Chair','PECIT standard Office Chair',8,'unit',3500.00,4,0,2,'Warehouse','available',0,NULL,'2026-07-24 06:43:04','2026-09-08 04:54:19',7),(9,'UNI-PE','Uniform P.E.','Physical Education uniform — available to all students.',11,'piece',650.00,80,1,15,'Uniform Store','available',1,NULL,'2026-08-05 19:57:21','2026-09-14 17:52:41',1),(10,'UNI-NSTP','Uniform NSTP','NSTP uniform — available to all students.',11,'piece',550.00,60,1,15,'Uniform Store','available',1,NULL,'2026-08-05 19:57:21','2026-08-26 05:23:30',1),(11,'UNI-LANYARD','Lanyard for ID','ID lanyard — available to all students.',11,'piece',80.00,99,1,30,'Uniform Store','available',1,NULL,'2026-08-05 19:57:21','2026-09-13 19:10:52',1),(12,'UNI-CIT','IT Uniform (Exclusive)','Exclusive to College of Information Technology students only.',11,'set',1200.00,40,0,10,'Uniform Store','discontinued',0,9,'2026-08-05 19:57:21','2026-09-10 07:48:14',10),(13,'UNI-CC','Criminology Uniform (Exclusive)','Exclusive to College of Criminology students only.',11,'set',1200.00,40,0,10,'Uniform Store','available',1,10,'2026-08-05 19:57:21','2026-09-10 07:48:14',10),(14,'UNI-CBA','Business Administration Uniform (Exclusive)','Exclusive to College of Business Administration students only.',11,'set',1200.00,40,0,10,'Uniform Store','available',1,13,'2026-08-05 19:57:21','2026-09-10 07:48:14',10),(15,'UNI-SHS','SHS Uniform (Exclusive)','Exclusive to Senior High School students only.',11,'set',1200.00,40,0,10,'Uniform Store','available',1,6,'2026-08-05 19:57:21','2026-08-05 20:32:59',10),(16,'UNI-CCS','Computer Studies Uniform (Exclusive)','Exclusive to College of Computer Studies students only.',11,'set',1200.00,45,1,10,'Uniform Store','available',1,9,'2026-08-05 20:32:59','2026-09-10 07:48:14',10),(18,'UNI-CCS-C','Computer Studies Type C Uniform (Exclusive)',NULL,11,'piece',1200.00,5,1,10,NULL,'low_stock',1,9,'2026-09-08 02:49:53','2026-09-08 05:05:52',1);
/*!40000 ALTER TABLE `inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_price_adjustments`
--

DROP TABLE IF EXISTS `inventory_price_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_price_adjustments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `inventory_id` bigint(20) unsigned NOT NULL,
  `old_unit_price` decimal(12,2) NOT NULL,
  `new_unit_price` decimal(12,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `adjusted_by` bigint(20) unsigned NOT NULL,
  `adjusted_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_price_adjustments_inventory_id_foreign` (`inventory_id`),
  KEY `inventory_price_adjustments_adjusted_by_foreign` (`adjusted_by`),
  CONSTRAINT `inventory_price_adjustments_adjusted_by_foreign` FOREIGN KEY (`adjusted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_price_adjustments_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_price_adjustments`
--

LOCK TABLES `inventory_price_adjustments` WRITE;
/*!40000 ALTER TABLE `inventory_price_adjustments` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_price_adjustments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_size_stocks`
--

DROP TABLE IF EXISTS `inventory_size_stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_size_stocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `inventory_id` bigint(20) unsigned NOT NULL,
  `size` varchar(10) NOT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 0,
  `reserved_quantity` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_size_stocks_inventory_id_size_unique` (`inventory_id`,`size`),
  CONSTRAINT `inventory_size_stocks_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_size_stocks`
--

LOCK TABLES `inventory_size_stocks` WRITE;
/*!40000 ALTER TABLE `inventory_size_stocks` DISABLE KEYS */;
INSERT INTO `inventory_size_stocks` VALUES (8,9,'XS',12,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(9,9,'S',12,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(10,9,'M',12,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(11,9,'L',11,1,'2026-08-26 04:58:56','2026-09-14 17:52:41'),(12,9,'XL',11,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(13,9,'2XL',11,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(14,9,'3XL',11,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(15,10,'XS',9,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(16,10,'S',9,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(17,10,'M',9,1,'2026-08-26 04:58:56','2026-08-26 05:23:30'),(18,10,'L',9,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(19,10,'XL',8,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(20,10,'2XL',8,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(21,10,'3XL',8,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(22,13,'XS',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(23,13,'S',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(24,13,'M',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(25,13,'L',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(26,13,'XL',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(27,13,'2XL',5,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(28,13,'3XL',5,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(29,12,'XS',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(30,12,'S',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(31,12,'M',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(32,12,'L',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(33,12,'XL',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(34,12,'2XL',5,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(35,12,'3XL',5,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(36,16,'XS',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(37,16,'S',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(38,16,'M',9,1,'2026-08-26 04:58:56','2026-08-31 20:32:45'),(39,16,'L',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(40,16,'XL',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(41,16,'2XL',5,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(42,16,'3XL',7,0,'2026-08-26 04:58:56','2026-08-28 02:22:35'),(43,14,'XS',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(44,14,'S',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(45,14,'M',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(46,14,'L',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(47,14,'XL',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(48,14,'2XL',5,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(49,14,'3XL',5,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(50,15,'XS',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(51,15,'S',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(52,15,'M',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(53,15,'L',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(54,15,'XL',6,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(55,15,'2XL',5,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(56,15,'3XL',5,0,'2026-08-26 04:58:56','2026-08-26 04:58:56'),(57,18,'M',5,1,'2026-09-08 02:49:53','2026-09-08 04:13:23');
/*!40000 ALTER TABLE `inventory_size_stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_07_24_141456_create_permission_tables',1),(5,'2026_07_24_150001_add_fields_to_users_table',2),(6,'2026_07_24_150002_create_departments_table',2),(7,'2026_07_24_150003_create_categories_table',2),(8,'2026_07_24_150004_create_suppliers_table',2),(9,'2026_07_24_150005_create_inventory_table',2),(10,'2026_07_24_150006_create_requests_table',2),(11,'2026_07_24_150007_create_request_items_table',2),(12,'2026_07_24_150008_create_purchase_requests_table',2),(13,'2026_07_24_150009_create_payments_table',2),(14,'2026_07_24_150010_create_transactions_table',2),(15,'2026_07_24_150011_create_stock_logs_table',2),(16,'2026_07_24_150012_create_psis_notifications_table',2),(17,'2026_07_24_150013_create_audit_logs_table',2),(18,'2026_07_24_150014_create_announcements_table',2),(19,'2026_07_24_150015_add_users_department_foreign_key',2),(20,'2026_08_06_000001_drop_suppliers_from_inventory',3),(21,'2026_08_06_120000_add_student_shop_and_last_name',4),(22,'2026_08_26_200000_add_size_to_purchase_request_items',5),(23,'2026_08_26_205000_create_inventory_size_stocks_table',6),(24,'2026_09_08_170000_drop_barcode_from_inventory',7),(25,'2026_09_10_230000_add_stock_card_foundation',8),(26,'2026_09_10_233500_replace_academic_departments',9),(27,'2026_09_15_093000_add_faculty_budget_and_receiving_inspection',10),(28,'2026_09_15_094800_add_purchased_by_to_transactions',11),(29,'2026_09_15_100000_add_inspection_to_purchase_request_items',12),(30,'2026_09_15_103000_add_inspection_to_request_items',13);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(2,'App\\Models\\User',2),(3,'App\\Models\\User',3),(4,'App\\Models\\User',4),(4,'App\\Models\\User',6),(5,'App\\Models\\User',5),(5,'App\\Models\\User',7),(5,'App\\Models\\User',8),(6,'App\\Models\\User',9);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reference_number` varchar(255) NOT NULL,
  `purchase_request_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(255) NOT NULL DEFAULT 'cash',
  `receipt_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_reference_number_unique` (`reference_number`),
  KEY `payments_purchase_request_id_foreign` (`purchase_request_id`),
  KEY `payments_user_id_foreign` (`user_id`),
  KEY `payments_verified_by_foreign` (`verified_by`),
  CONSTRAINT `payments_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,'PAY-NR7GEID78G',1,5,250.00,'verified','over_the_counter',NULL,NULL,2,'2026-07-24 08:05:05','2026-07-24 08:03:43','2026-07-24 08:05:05'),(2,'PAY-UQSNV6OLIK',2,5,95.00,'verified','over_the_counter','receipts/rsQndrd2mnb4MYSbAoivlz3hEdr2fQJj3QDVwQ5z.png',NULL,2,'2026-07-24 08:15:54','2026-07-24 08:14:26','2026-07-24 08:15:54'),(3,'PAY-N9UC5YVLSH',3,7,35.00,'verified','over_the_counter','receipts/EK9RRpZOqe9TvVQTVdwf3xoww0YAGFnsIvLwvjcf.png',NULL,2,'2026-07-24 19:10:43','2026-07-24 19:08:19','2026-07-24 19:10:43'),(4,'PAY-GFM1XOMHD4',4,7,250.00,'verified','over_the_counter','receipts/hteSJRIKADev5q5MROhyBF9NMjcVCF50rP9WCxqi.pdf',NULL,2,'2026-07-24 20:47:26','2026-07-24 20:46:25','2026-07-24 20:47:26'),(5,'PAY-56A6DLL2OC',5,7,550.00,'verified','over_the_counter','receipts/GpAsVxLCvRfpnSDYU88ogZ71GW6wBAZfgRAqNrWD.png',NULL,2,'2026-08-26 05:23:30','2026-08-26 05:16:49','2026-08-26 05:23:30'),(6,'PAY-R1DQV2QKUO',6,7,80.00,'verified','over_the_counter',NULL,NULL,2,'2026-09-08 04:18:17','2026-08-28 01:18:57','2026-09-08 04:18:17'),(7,'PAY-QF4QV86KGN',7,7,1200.00,'verified','over_the_counter','receipts/K9EdVD49sT52dgtmZjsLEwDBgJszvvAuIEDAa6sJ.png',NULL,2,'2026-08-28 01:42:26','2026-08-28 01:40:06','2026-08-28 01:42:26'),(8,'PAY-QU0DSBWFNK',8,7,1200.00,'verified','over_the_counter','receipts/bi9cEeQ6F9BEFUUDYInB4x4jcIv610Ha8y6qzyhj.jpg',NULL,2,'2026-08-31 20:30:59','2026-08-31 20:27:43','2026-08-31 20:30:59'),(9,'PAY-J1KETCMNQR',9,7,80.00,'verified','over_the_counter',NULL,NULL,2,'2026-09-08 01:28:44','2026-09-08 01:27:34','2026-09-08 01:28:44'),(10,'PAY-5DIVNBZXNU',10,7,1200.00,'verified','over_the_counter',NULL,NULL,2,'2026-09-08 04:13:23','2026-09-08 04:11:38','2026-09-08 04:13:23'),(11,'PAY-J2MFLMYRZM',11,7,1200.00,'pending','over_the_counter','receipts/3k0z2vVQrLl2jqhMe5cSmv9Xzk9f2Yt1zmJnFExZ.jpg',NULL,NULL,NULL,'2026-09-08 04:19:10','2026-09-08 04:20:12'),(12,'PAY-WQCSCTVZO4',12,7,1200.00,'pending','over_the_counter','receipts/l7xxXAEwKBbi55ZcdOxe3GjOmeFjxzcWICSlUFFn.png',NULL,NULL,NULL,'2026-09-12 07:11:50','2026-09-12 07:12:25'),(13,'PAY-CQCZTTRPH9',13,7,80.00,'pending','over_the_counter','receipts/yeWvynbKwPQiMHMXbecHDjvB0zf4i177CzTQPVfZ.png',NULL,NULL,NULL,'2026-09-13 20:58:28','2026-09-13 20:59:15'),(14,'PAY-DIHR4XNU52',14,7,650.00,'verified','over_the_counter','receipts/R3gJwEubWJLmQGlheLOOmmRc9JXPGAZSFTJTmdtX.jpg',NULL,2,'2026-09-14 17:52:41','2026-09-14 17:51:37','2026-09-14 17:52:41');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'inventory.view','web','2026-07-24 06:41:04','2026-07-24 06:41:04'),(2,'inventory.manage','web','2026-07-24 06:41:04','2026-07-24 06:41:04'),(3,'requests.submit','web','2026-07-24 06:41:04','2026-07-24 06:41:04'),(4,'requests.review','web','2026-07-24 06:41:04','2026-07-24 06:41:04'),(5,'requests.approve','web','2026-07-24 06:41:04','2026-07-24 06:41:04'),(6,'requests.release','web','2026-07-24 06:41:04','2026-07-24 06:41:04'),(7,'purchases.checkout','web','2026-07-24 06:41:04','2026-07-24 06:41:04'),(8,'purchases.verify','web','2026-07-24 06:41:04','2026-07-24 06:41:04'),(9,'users.manage','web','2026-07-24 06:41:04','2026-07-24 06:41:04'),(10,'reports.view','web','2026-07-24 06:41:04','2026-07-24 06:41:04'),(11,'audit.view','web','2026-07-24 06:41:04','2026-07-24 06:41:04'),(12,'announcements.manage','web','2026-07-24 06:41:04','2026-07-24 06:41:04');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `psis_notifications`
--

DROP TABLE IF EXISTS `psis_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `psis_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `psis_notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `psis_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `psis_notifications`
--

LOCK TABLES `psis_notifications` WRITE;
/*!40000 ALTER TABLE `psis_notifications` DISABLE KEYS */;
INSERT INTO `psis_notifications` VALUES (1,2,'new_request','New supply request','Prof. Juan Dela Cruz submitted request REQ-PCR3LYIW.','http://127.0.0.1:8000/accounting/requests/1',1,'2026-07-24 07:55:11','2026-09-14 18:03:17'),(2,2,'payment_submitted','New student purchase','Maria Santos submitted purchase PUR-F27D0VVL.','http://127.0.0.1:8000/accounting/payments/1',1,'2026-07-24 08:03:43','2026-09-14 18:03:17'),(3,5,'payment_verified','Payment verified','Payment for PUR-F27D0VVL has been verified.','http://127.0.0.1:8000/purchases/1',1,'2026-07-24 08:05:05','2026-07-24 08:13:09'),(4,3,'purchase_verified','Purchase ready for release','Purchase PUR-F27D0VVL is ready for release.','http://127.0.0.1:8000/supply/purchases/1',1,'2026-07-24 08:05:05','2026-07-24 08:36:00'),(5,1,'request_reviewed','Request ready for approval','Request REQ-PCR3LYIW was reviewed by accounting.','http://127.0.0.1:8000/admin/requests/1',0,'2026-07-24 08:05:14','2026-07-24 08:05:14'),(6,4,'request_approved','Request approved','Your request REQ-PCR3LYIW has been approved.','http://127.0.0.1:8000/requests/1',1,'2026-07-24 08:08:49','2026-07-24 08:16:54'),(7,3,'request_approved','Approved request pending release','Request REQ-PCR3LYIW is ready for release.','http://127.0.0.1:8000/supply/releases/1',1,'2026-07-24 08:08:49','2026-07-24 08:36:00'),(8,2,'payment_submitted','New student purchase','Maria Santos submitted purchase PUR-J0B7AXVI.','http://127.0.0.1:8000/accounting/payments/2',1,'2026-07-24 08:14:26','2026-09-14 18:03:17'),(9,5,'payment_verified','Payment verified','Payment for PUR-J0B7AXVI has been verified.','http://127.0.0.1:8000/purchases/2',0,'2026-07-24 08:15:54','2026-07-24 08:15:54'),(10,3,'purchase_verified','Purchase ready for release','Purchase PUR-J0B7AXVI is ready for release.','http://127.0.0.1:8000/supply/purchases/2',1,'2026-07-24 08:15:54','2026-07-24 08:36:00'),(11,4,'item_released','Items released','Items for request REQ-PCR3LYIW have been released.','http://127.0.0.1:8000/requests/1',0,'2026-07-24 08:18:00','2026-07-24 08:18:00'),(12,5,'item_released','Purchase released','Your purchase PUR-J0B7AXVI has been released.','http://127.0.0.1:8000/purchases/2',0,'2026-07-24 08:26:28','2026-07-24 08:26:28'),(13,5,'item_released','Purchase released','Your purchase PUR-F27D0VVL has been released.','http://127.0.0.1:8000/purchases/1',0,'2026-07-24 08:26:55','2026-07-24 08:26:55'),(14,2,'new_request','New supply request','Prof. Juan Dela Cruz submitted request REQ-GMWTCZAW.','http://127.0.0.1:8000/accounting/requests/2',1,'2026-07-24 08:31:45','2026-09-14 18:03:17'),(15,1,'request_reviewed','Request ready for approval','Request REQ-GMWTCZAW was reviewed by accounting.','http://127.0.0.1:8000/admin/requests/2',0,'2026-07-24 08:32:19','2026-07-24 08:32:19'),(16,4,'request_approved','Request approved','Your request REQ-GMWTCZAW has been approved.','http://127.0.0.1:8000/requests/2',0,'2026-07-24 08:33:58','2026-07-24 08:33:58'),(17,3,'request_approved','Approved request pending release','Request REQ-GMWTCZAW is ready for release.','http://127.0.0.1:8000/supply/releases/2',1,'2026-07-24 08:33:58','2026-07-24 08:36:00'),(18,4,'item_released','Items released','Items for request REQ-GMWTCZAW have been released.','http://127.0.0.1:8000/requests/2',0,'2026-07-24 08:35:16','2026-07-24 08:35:16'),(19,2,'new_request','New supply request','Vea Villaver submitted request REQ-JYDE6HY8.','http://127.0.0.1:8000/accounting/requests/3',1,'2026-07-24 08:41:10','2026-09-14 18:03:17'),(20,1,'request_reviewed','Request ready for approval','Request REQ-JYDE6HY8 was reviewed by accounting.','http://127.0.0.1:8000/admin/requests/3',0,'2026-07-24 08:42:44','2026-07-24 08:42:44'),(21,6,'request_approved','Request approved','Your request REQ-JYDE6HY8 has been approved.','http://127.0.0.1:8000/requests/3',1,'2026-07-24 08:43:38','2026-07-24 20:16:17'),(22,3,'request_approved','Approved request pending release','Request REQ-JYDE6HY8 is ready for release.','http://127.0.0.1:8000/supply/releases/3',1,'2026-07-24 08:43:38','2026-08-31 20:34:58'),(23,6,'item_released','Items released','Items for request REQ-JYDE6HY8 have been released.','http://127.0.0.1:8000/requests/3',1,'2026-07-24 08:44:31','2026-07-24 20:16:17'),(24,2,'payment_submitted','New student purchase','Joy Tienes submitted purchase PUR-PD8IZ03C.','http://127.0.0.1:8000/accounting/payments/3',1,'2026-07-24 19:08:19','2026-09-14 18:03:17'),(25,7,'payment_verified','Payment verified','Payment for PUR-PD8IZ03C has been verified.','http://127.0.0.1:8000/purchases/3',1,'2026-07-24 19:10:43','2026-08-28 01:42:17'),(26,3,'purchase_verified','Purchase ready for release','Purchase PUR-PD8IZ03C is ready for release.','http://127.0.0.1:8000/supply/purchases/3',1,'2026-07-24 19:10:48','2026-08-31 20:34:58'),(27,7,'item_released','Purchase released','Your purchase PUR-PD8IZ03C has been released.','http://127.0.0.1:8000/purchases/3',1,'2026-07-24 19:11:50','2026-08-28 01:42:17'),(28,2,'new_request','New supply request','Vea Villaver submitted request REQ-2YCLVJ4C.','http://127.0.0.1:8000/accounting/requests/4',1,'2026-07-24 20:05:07','2026-09-14 18:03:17'),(29,1,'request_reviewed','Request ready for approval','Request REQ-2YCLVJ4C was reviewed by accounting.','http://127.0.0.1:8000/admin/requests/4',0,'2026-07-24 20:10:30','2026-07-24 20:10:30'),(30,6,'request_approved','Request approved','Your request REQ-2YCLVJ4C has been approved.','http://127.0.0.1:8000/requests/4',1,'2026-07-24 20:11:56','2026-07-24 20:16:17'),(31,3,'request_approved','Approved request pending release','Request REQ-2YCLVJ4C is ready for release.','http://127.0.0.1:8000/supply/releases/4',1,'2026-07-24 20:12:01','2026-08-31 20:34:58'),(32,6,'item_released','Items released','Items for request REQ-2YCLVJ4C have been released.','http://127.0.0.1:8000/requests/4',1,'2026-07-24 20:13:53','2026-07-24 20:16:17'),(33,2,'payment_submitted','New student purchase','Joy Tienes submitted purchase PUR-MWGYFIIS.','http://127.0.0.1:8000/accounting/payments/4',1,'2026-07-24 20:46:25','2026-09-14 18:03:17'),(34,7,'payment_verified','Payment verified','Payment for PUR-MWGYFIIS has been verified.','http://127.0.0.1:8000/purchases/4',1,'2026-07-24 20:47:26','2026-08-28 01:42:17'),(35,3,'purchase_verified','Purchase ready for release','Purchase PUR-MWGYFIIS is ready for release.','http://127.0.0.1:8000/supply/purchases/4',1,'2026-07-24 20:47:31','2026-08-31 20:34:58'),(36,7,'item_released','Purchase released','Your purchase PUR-MWGYFIIS has been released.','http://127.0.0.1:8000/purchases/4',1,'2026-07-24 20:48:44','2026-08-28 01:42:17'),(37,2,'payment_submitted','New student purchase','Joy Tienes submitted purchase PUR-WSMAXFXO.','http://127.0.0.1:8000/accounting/payments/5',1,'2026-08-26 05:16:49','2026-09-14 18:03:17'),(38,7,'payment_verified','Payment verified','Payment for PUR-WSMAXFXO has been verified.','http://127.0.0.1:8000/purchases/5',1,'2026-08-26 05:23:30','2026-08-28 01:19:56'),(39,3,'purchase_verified','Purchase ready for release','Purchase PUR-WSMAXFXO is ready for release.','http://127.0.0.1:8000/supply/purchases/5',1,'2026-08-26 05:23:35','2026-08-31 20:34:58'),(40,2,'payment_submitted','New student purchase','Joy Tienes submitted purchase PUR-AOH8WO4Q.','http://127.0.0.1:8000/accounting/payments/6',1,'2026-08-28 01:18:57','2026-09-14 18:03:17'),(41,2,'payment_submitted','New student purchase','Joy Tienes submitted purchase PUR-FCP8AXGS.','http://127.0.0.1:8000/accounting/payments/7',1,'2026-08-28 01:40:06','2026-09-14 18:03:17'),(42,7,'payment_verified','Payment verified','Payment for PUR-FCP8AXGS has been verified.','http://127.0.0.1:8000/purchases/7',1,'2026-08-28 01:42:26','2026-08-28 01:51:55'),(43,3,'purchase_verified','Purchase ready for release','Purchase PUR-FCP8AXGS is ready for release.','http://127.0.0.1:8000/supply/purchases/7',1,'2026-08-28 01:42:30','2026-08-31 20:34:58'),(44,2,'payment_submitted','New student purchase','Joy Tienes submitted purchase PUR-NOKIY96X.','http://127.0.0.1:8000/accounting/payments/8',1,'2026-08-31 20:27:43','2026-08-31 20:30:48'),(45,7,'payment_verified','Payment verified','Payment for PUR-NOKIY96X has been verified.','http://127.0.0.1:8000/purchases/8',1,'2026-08-31 20:30:59','2026-09-10 08:18:29'),(46,3,'purchase_verified','Purchase ready for release','Purchase PUR-NOKIY96X is ready for release.','http://127.0.0.1:8000/supply/purchases/8',1,'2026-08-31 20:31:05','2026-08-31 20:32:09'),(47,7,'item_released','Purchase released','Your purchase PUR-NOKIY96X has been released.','http://127.0.0.1:8000/purchases/8',1,'2026-08-31 20:32:45','2026-09-10 08:18:29'),(48,2,'new_request','New supply request','Prof. Juan Dela Cruz submitted request REQ-STEWWTLH.','http://127.0.0.1:8000/accounting/requests/5',1,'2026-09-03 04:51:41','2026-09-14 18:03:17'),(49,1,'request_reviewed','Request ready for approval','Request REQ-STEWWTLH was reviewed by accounting.','http://127.0.0.1:8000/admin/requests/5',0,'2026-09-03 04:53:00','2026-09-03 04:53:00'),(50,9,'request_reviewed','Request ready for approval','Request REQ-STEWWTLH was reviewed by accounting.','http://127.0.0.1:8000/admin/requests/5',0,'2026-09-03 04:53:07','2026-09-03 04:53:07'),(51,4,'request_approved','Request approved','Your request REQ-STEWWTLH has been approved.','http://127.0.0.1:8000/requests/5',0,'2026-09-03 04:54:00','2026-09-03 04:54:00'),(52,3,'request_approved','Approved request pending release','Request REQ-STEWWTLH is ready for release.','http://127.0.0.1:8000/supply/releases/5',1,'2026-09-03 04:54:07','2026-09-10 07:56:15'),(53,2,'new_request','New supply request','Prof. Juan Dela Cruz submitted request REQ-C9211TOC.','http://192.168.110.177:8000/accounting/requests/6',1,'2026-09-03 23:11:39','2026-09-14 18:03:17'),(54,1,'request_reviewed','Request ready for approval','Request REQ-C9211TOC was reviewed by accounting.','http://192.168.110.177:8000/admin/requests/6',0,'2026-09-03 23:13:23','2026-09-03 23:13:23'),(55,9,'request_reviewed','Request ready for approval','Request REQ-C9211TOC was reviewed by accounting.','http://192.168.110.177:8000/admin/requests/6',0,'2026-09-03 23:13:28','2026-09-03 23:13:28'),(56,2,'new_request','New supply request','Prof. Juan Dela Cruz submitted request REQ-G4DVMT9Y.','http://127.0.0.1:8000/accounting/requests/7',1,'2026-09-08 00:04:34','2026-09-14 18:03:17'),(57,1,'request_reviewed','Request ready for approval','Request REQ-G4DVMT9Y was reviewed by accounting.','http://127.0.0.1:8000/admin/requests/7',0,'2026-09-08 00:05:18','2026-09-08 00:05:18'),(58,9,'request_reviewed','Request ready for approval','Request REQ-G4DVMT9Y was reviewed by accounting.','http://127.0.0.1:8000/admin/requests/7',0,'2026-09-08 00:05:23','2026-09-08 00:05:23'),(59,2,'payment_submitted','New student purchase','Joy Tienes submitted purchase PUR-DI58BZAM.','http://127.0.0.1:8000/accounting/payments/9',1,'2026-09-08 01:27:34','2026-09-08 01:28:15'),(60,7,'payment_verified','Payment verified','Payment for PUR-DI58BZAM has been verified.','http://127.0.0.1:8000/purchases/9',1,'2026-09-08 01:28:44','2026-09-10 08:18:29'),(61,3,'purchase_verified','Purchase ready for release','Purchase PUR-DI58BZAM is ready for release.','http://127.0.0.1:8000/supply/purchases/9',1,'2026-09-08 01:28:49','2026-09-10 07:56:15'),(62,7,'item_released','Purchase released','Your purchase PUR-DI58BZAM has been released.','http://127.0.0.1:8000/purchases/9',1,'2026-09-08 01:34:09','2026-09-10 08:18:29'),(63,2,'payment_submitted','New student purchase','Joy Tienes submitted purchase PUR-VBRVJLDT.','http://127.0.0.1:8000/accounting/payments/10',1,'2026-09-08 04:11:38','2026-09-14 18:03:17'),(64,7,'payment_verified','Payment verified','Payment for PUR-VBRVJLDT has been verified.','http://127.0.0.1:8000/purchases/10',1,'2026-09-08 04:13:23','2026-09-10 08:18:29'),(65,3,'purchase_verified','Purchase ready for release','Purchase PUR-VBRVJLDT is ready for release.','http://127.0.0.1:8000/supply/purchases/10',1,'2026-09-08 04:13:28','2026-09-10 07:56:15'),(66,7,'payment_verified','Payment verified','Payment for PUR-AOH8WO4Q has been verified.','http://127.0.0.1:8000/purchases/6',1,'2026-09-08 04:18:17','2026-09-10 08:18:29'),(67,3,'purchase_verified','Purchase ready for release','Purchase PUR-AOH8WO4Q is ready for release.','http://127.0.0.1:8000/supply/purchases/6',1,'2026-09-08 04:18:23','2026-09-10 07:56:15'),(68,2,'payment_submitted','New student purchase','Joy Tienes submitted purchase PUR-R6EBCSPC.','http://127.0.0.1:8000/accounting/payments/11',1,'2026-09-08 04:19:10','2026-09-14 18:03:17'),(69,2,'new_request','New supply request','Prof. Juan Dela Cruz submitted request REQ-YBCVUPA1.','http://127.0.0.1:8000/accounting/requests/8',1,'2026-09-08 04:38:29','2026-09-14 18:03:17'),(70,1,'request_reviewed','Request ready for approval','Request REQ-YBCVUPA1 was reviewed by accounting.','http://127.0.0.1:8000/admin/requests/8',0,'2026-09-08 04:39:20','2026-09-08 04:39:20'),(71,9,'request_reviewed','Request ready for approval','Request REQ-YBCVUPA1 was reviewed by accounting.','http://127.0.0.1:8000/admin/requests/8',0,'2026-09-08 04:39:24','2026-09-08 04:39:24'),(72,2,'new_request','New supply request','Prof. Juan Dela Cruz submitted request REQ-VNJKFCK9.','http://127.0.0.1:8000/accounting/requests/9',1,'2026-09-08 04:52:30','2026-09-14 18:03:17'),(73,1,'request_reviewed','Request ready for approval','Request REQ-VNJKFCK9 was reviewed by accounting.','http://127.0.0.1:8000/admin/requests/9',0,'2026-09-08 04:52:58','2026-09-08 04:52:58'),(74,9,'request_reviewed','Request ready for approval','Request REQ-VNJKFCK9 was reviewed by accounting.','http://127.0.0.1:8000/admin/requests/9',0,'2026-09-08 04:53:03','2026-09-08 04:53:03'),(75,4,'request_approved','Request approved','Your request REQ-VNJKFCK9 has been approved.','http://127.0.0.1:8000/requests/9',0,'2026-09-08 04:53:25','2026-09-08 04:53:25'),(76,3,'request_approved','Approved request pending release','Request REQ-VNJKFCK9 is ready for release.','http://127.0.0.1:8000/supply/releases/9',1,'2026-09-08 04:53:30','2026-09-10 07:56:15'),(77,4,'item_released','Items released','Items for request REQ-VNJKFCK9 have been released.','http://127.0.0.1:8000/requests/9',0,'2026-09-08 04:54:19','2026-09-08 04:54:19'),(78,2,'new_request','New supply request','Prof. Juan Dela Cruz submitted request REQ-XGJYTT8T.','http://127.0.0.1:8000/accounting/requests/10',1,'2026-09-08 05:08:48','2026-09-14 18:03:17'),(79,1,'request_reviewed','Request ready for approval','Request REQ-XGJYTT8T was reviewed by accounting.','http://127.0.0.1:8000/admin/requests/10',0,'2026-09-08 05:09:17','2026-09-08 05:09:17'),(80,9,'request_reviewed','Request ready for approval','Request REQ-XGJYTT8T was reviewed by accounting.','http://127.0.0.1:8000/admin/requests/10',0,'2026-09-08 05:09:22','2026-09-08 05:09:22'),(81,4,'request_approved','Request approved','Your request REQ-XGJYTT8T has been approved.','http://127.0.0.1:8000/requests/10',0,'2026-09-08 05:09:44','2026-09-08 05:09:44'),(82,3,'request_approved','Approved request pending release','Request REQ-XGJYTT8T is ready for release.','http://127.0.0.1:8000/supply/releases/10',1,'2026-09-08 05:09:49','2026-09-10 07:56:15'),(83,2,'payment_submitted','New student purchase','Joy Tienes submitted purchase PUR-IACB3ATX.','http://127.0.0.1:8000/accounting/payments/12',1,'2026-09-12 07:11:50','2026-09-14 18:03:17'),(84,2,'payment_submitted','New student purchase','Joy Tienes submitted purchase PUR-LIRFVD8D.','http://127.0.0.1:8000/accounting/payments/13',1,'2026-09-13 20:58:28','2026-09-14 18:03:17'),(85,2,'payment_submitted','New student purchase','Joy Tienes submitted purchase PUR-ZZVDVSJW.','http://127.0.0.1:8000/accounting/payments/14',1,'2026-09-14 17:51:37','2026-09-14 18:03:17'),(86,7,'payment_verified','Payment verified','Payment for PUR-ZZVDVSJW has been verified.','http://127.0.0.1:8000/purchases/14',0,'2026-09-14 17:52:41','2026-09-14 17:52:41'),(87,3,'purchase_verified','Purchase ready for release','Purchase PUR-ZZVDVSJW is ready for release.','http://127.0.0.1:8000/supply/purchases/14',0,'2026-09-14 17:52:46','2026-09-14 17:52:46'),(88,4,'item_released','Items released','Items for request REQ-XGJYTT8T have been released.','http://127.0.0.1:8000/requests/10',0,'2026-09-14 17:53:23','2026-09-14 17:53:23');
/*!40000 ALTER TABLE `psis_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_request_items`
--

DROP TABLE IF EXISTS `purchase_request_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_request_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_request_id` bigint(20) unsigned NOT NULL,
  `inventory_id` bigint(20) unsigned NOT NULL,
  `size` varchar(10) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `inspection_status` varchar(20) DEFAULT NULL,
  `inspection_notes` text DEFAULT NULL,
  `inspected_by` bigint(20) unsigned DEFAULT NULL,
  `inspected_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_request_items_purchase_request_id_foreign` (`purchase_request_id`),
  KEY `purchase_request_items_inventory_id_foreign` (`inventory_id`),
  KEY `purchase_request_items_inspected_by_foreign` (`inspected_by`),
  CONSTRAINT `purchase_request_items_inspected_by_foreign` FOREIGN KEY (`inspected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_request_items_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_request_items_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_request_items`
--

LOCK TABLES `purchase_request_items` WRITE;
/*!40000 ALTER TABLE `purchase_request_items` DISABLE KEYS */;
INSERT INTO `purchase_request_items` VALUES (1,1,4,NULL,1,250.00,250.00,'2026-07-24 08:03:43','2026-07-24 08:03:43','pending',NULL,NULL,NULL),(2,2,6,NULL,1,95.00,95.00,'2026-07-24 08:14:26','2026-07-24 08:14:26','pending',NULL,NULL,NULL),(3,3,3,NULL,1,35.00,35.00,'2026-07-24 19:08:19','2026-07-24 19:08:19','pending',NULL,NULL,NULL),(4,4,4,NULL,1,250.00,250.00,'2026-07-24 20:46:25','2026-07-24 20:46:25','pending',NULL,NULL,NULL),(5,5,10,'M',1,550.00,550.00,'2026-08-26 05:16:49','2026-08-26 05:16:49','pending',NULL,NULL,NULL),(6,6,11,NULL,1,80.00,80.00,'2026-08-28 01:18:57','2026-08-28 01:18:57','pending',NULL,NULL,NULL),(7,7,16,'M',1,1200.00,1200.00,'2026-08-28 01:40:06','2026-08-28 01:40:06','pending',NULL,NULL,NULL),(8,8,16,'M',1,1200.00,1200.00,'2026-08-31 20:27:43','2026-08-31 20:27:43','pending',NULL,NULL,NULL),(9,9,11,NULL,1,80.00,80.00,'2026-09-08 01:27:34','2026-09-08 01:27:34','pending',NULL,NULL,NULL),(10,10,18,'M',1,1200.00,1200.00,'2026-09-08 04:11:38','2026-09-08 04:11:38','pending',NULL,NULL,NULL),(11,11,16,'L',1,1200.00,1200.00,'2026-09-08 04:19:10','2026-09-08 04:19:10','pending',NULL,NULL,NULL),(12,12,18,'M',1,1200.00,1200.00,'2026-09-12 07:11:50','2026-09-12 07:11:50','pending',NULL,NULL,NULL),(13,13,11,NULL,1,80.00,80.00,'2026-09-13 20:58:28','2026-09-13 20:58:28','pending',NULL,NULL,NULL),(14,14,9,'L',1,650.00,650.00,'2026-09-14 17:51:37','2026-09-14 17:51:37','pending',NULL,NULL,NULL);
/*!40000 ALTER TABLE `purchase_request_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_requests`
--

DROP TABLE IF EXISTS `purchase_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_number` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `status` enum('pending','payment_submitted','payment_verified','approved','released','cancelled','rejected') NOT NULL DEFAULT 'pending',
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `released_by` bigint(20) unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `released_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_requests_purchase_number_unique` (`purchase_number`),
  KEY `purchase_requests_user_id_foreign` (`user_id`),
  KEY `purchase_requests_verified_by_foreign` (`verified_by`),
  KEY `purchase_requests_released_by_foreign` (`released_by`),
  CONSTRAINT `purchase_requests_released_by_foreign` FOREIGN KEY (`released_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_requests_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_requests`
--

LOCK TABLES `purchase_requests` WRITE;
/*!40000 ALTER TABLE `purchase_requests` DISABLE KEYS */;
INSERT INTO `purchase_requests` VALUES (1,'PUR-F27D0VVL',5,'released',250.00,NULL,2,3,'2026-07-24 08:05:05','2026-07-24 08:26:55','2026-07-24 08:03:43','2026-07-24 08:26:55'),(2,'PUR-J0B7AXVI',5,'released',95.00,NULL,2,3,'2026-07-24 08:15:54','2026-07-24 08:26:28','2026-07-24 08:14:26','2026-07-24 08:26:28'),(3,'PUR-PD8IZ03C',7,'released',35.00,NULL,2,3,'2026-07-24 19:10:43','2026-07-24 19:11:50','2026-07-24 19:08:19','2026-07-24 19:11:50'),(4,'PUR-MWGYFIIS',7,'released',250.00,NULL,2,3,'2026-07-24 20:47:26','2026-07-24 20:48:44','2026-07-24 20:46:25','2026-07-24 20:48:44'),(5,'PUR-WSMAXFXO',7,'payment_verified',550.00,NULL,2,NULL,'2026-08-26 05:23:30',NULL,'2026-08-26 05:16:49','2026-08-26 05:23:30'),(6,'PUR-AOH8WO4Q',7,'payment_verified',80.00,NULL,2,NULL,'2026-09-08 04:18:17',NULL,'2026-08-28 01:18:57','2026-09-08 04:18:17'),(7,'PUR-FCP8AXGS',7,'payment_verified',1200.00,NULL,2,NULL,'2026-08-28 01:42:26',NULL,'2026-08-28 01:40:06','2026-08-28 01:42:26'),(8,'PUR-NOKIY96X',7,'released',1200.00,NULL,2,3,'2026-08-31 20:30:59','2026-08-31 20:32:45','2026-08-31 20:27:43','2026-08-31 20:32:45'),(9,'PUR-DI58BZAM',7,'released',80.00,NULL,2,3,'2026-09-08 01:28:44','2026-09-08 01:34:09','2026-09-08 01:27:34','2026-09-08 01:34:09'),(10,'PUR-VBRVJLDT',7,'payment_verified',1200.00,NULL,2,NULL,'2026-09-08 04:13:23',NULL,'2026-09-08 04:11:38','2026-09-08 04:13:23'),(11,'PUR-R6EBCSPC',7,'payment_submitted',1200.00,NULL,NULL,NULL,NULL,NULL,'2026-09-08 04:19:10','2026-09-08 04:19:10'),(12,'PUR-IACB3ATX',7,'payment_submitted',1200.00,NULL,NULL,NULL,NULL,NULL,'2026-09-12 07:11:50','2026-09-12 07:11:50'),(13,'PUR-LIRFVD8D',7,'payment_submitted',80.00,NULL,NULL,NULL,NULL,NULL,'2026-09-13 20:58:28','2026-09-13 20:58:28'),(14,'PUR-ZZVDVSJW',7,'payment_verified',650.00,NULL,2,NULL,'2026-09-14 17:52:41',NULL,'2026-09-14 17:51:37','2026-09-14 17:52:41');
/*!40000 ALTER TABLE `purchase_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `request_items`
--

DROP TABLE IF EXISTS `request_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `request_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `inventory_id` bigint(20) unsigned NOT NULL,
  `quantity_requested` int(11) NOT NULL,
  `quantity_approved` int(11) DEFAULT NULL,
  `quantity_released` int(11) NOT NULL DEFAULT 0,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `inspection_status` varchar(20) DEFAULT NULL,
  `inspection_notes` text DEFAULT NULL,
  `inspected_by` bigint(20) unsigned DEFAULT NULL,
  `inspected_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `request_items_request_id_foreign` (`request_id`),
  KEY `request_items_inventory_id_foreign` (`inventory_id`),
  KEY `request_items_inspected_by_foreign` (`inspected_by`),
  CONSTRAINT `request_items_inspected_by_foreign` FOREIGN KEY (`inspected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `request_items_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE,
  CONSTRAINT `request_items_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `request_items`
--

LOCK TABLES `request_items` WRITE;
/*!40000 ALTER TABLE `request_items` DISABLE KEYS */;
INSERT INTO `request_items` VALUES (1,1,2,5,5,5,45.00,225.00,'2026-07-24 07:55:11','2026-07-24 08:18:00','pending',NULL,NULL,NULL),(2,2,1,1,1,1,285.00,285.00,'2026-07-24 08:31:44','2026-07-24 08:35:16','pending',NULL,NULL,NULL),(3,3,7,20,20,20,180.00,3600.00,'2026-07-24 08:41:10','2026-07-24 08:44:31','pending',NULL,NULL,NULL),(4,4,8,3,3,3,3500.00,10500.00,'2026-07-24 20:05:07','2026-07-24 20:13:53','pending',NULL,NULL,NULL),(5,5,3,1,1,0,35.00,35.00,'2026-09-03 04:51:41','2026-09-03 04:53:00',NULL,NULL,NULL,NULL),(6,6,10,5,5,0,550.00,2750.00,'2026-09-03 23:11:39','2026-09-03 23:13:23',NULL,NULL,NULL,NULL),(7,7,9,3,3,0,750.00,2250.00,'2026-09-08 00:04:34','2026-09-08 00:05:18',NULL,NULL,NULL,NULL),(8,8,5,1,1,0,150.00,150.00,'2026-09-08 04:38:29','2026-09-08 04:39:20',NULL,NULL,NULL,NULL),(9,9,8,1,1,1,4500.00,4500.00,'2026-09-08 04:52:30','2026-09-08 04:54:19','pending',NULL,NULL,NULL),(10,10,7,1,1,1,200.00,200.00,'2026-09-08 05:08:48','2026-09-14 17:53:23','pending',NULL,NULL,NULL);
/*!40000 ALTER TABLE `request_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `requests`
--

DROP TABLE IF EXISTS `requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_number` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `type` enum('faculty','restock') NOT NULL DEFAULT 'faculty',
  `status` enum('pending','accounting_review','admin_review','approved','rejected','reserved','released','cancelled') NOT NULL DEFAULT 'pending',
  `purpose` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `released_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `released_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `requests_request_number_unique` (`request_number`),
  KEY `requests_user_id_foreign` (`user_id`),
  KEY `requests_department_id_foreign` (`department_id`),
  KEY `requests_reviewed_by_foreign` (`reviewed_by`),
  KEY `requests_approved_by_foreign` (`approved_by`),
  KEY `requests_released_by_foreign` (`released_by`),
  CONSTRAINT `requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `requests_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `requests_released_by_foreign` FOREIGN KEY (`released_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `requests_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requests`
--

LOCK TABLES `requests` WRITE;
/*!40000 ALTER TABLE `requests` DISABLE KEYS */;
INSERT INTO `requests` VALUES (1,'REQ-PCR3LYIW',4,9,'faculty','released','pang sulat lang po. matsalam',NULL,NULL,225.00,2,1,3,'2026-07-24 08:05:14','2026-07-24 08:08:49','2026-07-24 08:18:00','2026-07-24 07:55:11','2026-09-10 07:48:14'),(2,'REQ-GMWTCZAW',4,9,'faculty','released','please hatag',NULL,NULL,285.00,2,1,3,'2026-07-24 08:32:19','2026-07-24 08:33:58','2026-07-24 08:35:16','2026-07-24 08:31:44','2026-09-10 07:48:14'),(3,'REQ-JYDE6HY8',6,9,'faculty','released','mang guna me',NULL,NULL,3600.00,2,1,3,'2026-07-24 08:42:44','2026-07-24 08:43:38','2026-07-24 08:44:31','2026-07-24 08:41:10','2026-07-24 08:44:31'),(4,'REQ-2YCLVJ4C',6,9,'faculty','released','for visitors.',NULL,NULL,10500.00,2,1,3,'2026-07-24 20:10:30','2026-07-24 20:11:56','2026-07-24 20:13:53','2026-07-24 20:05:07','2026-07-24 20:13:53'),(5,'REQ-STEWWTLH',4,10,'faculty','approved','school use',NULL,NULL,35.00,2,9,NULL,'2026-09-03 04:53:00','2026-09-03 04:54:00',NULL,'2026-09-03 04:51:41','2026-09-10 07:48:14'),(6,'REQ-C9211TOC',4,10,'faculty','admin_review','for student',NULL,NULL,2750.00,2,NULL,NULL,'2026-09-03 23:13:23',NULL,NULL,'2026-09-03 23:11:38','2026-09-10 07:48:14'),(7,'REQ-G4DVMT9Y',4,10,'faculty','admin_review','for students',NULL,NULL,2250.00,2,NULL,NULL,'2026-09-08 00:05:18',NULL,NULL,'2026-09-08 00:04:34','2026-09-10 07:48:14'),(8,'REQ-YBCVUPA1',4,10,'faculty','admin_review','for new computer',NULL,NULL,150.00,2,NULL,NULL,'2026-09-08 04:39:20',NULL,NULL,'2026-09-08 04:38:29','2026-09-10 07:48:14'),(9,'REQ-VNJKFCK9',4,10,'faculty','released','for office',NULL,NULL,4500.00,2,9,3,'2026-09-08 04:52:58','2026-09-08 04:53:25','2026-09-08 04:54:19','2026-09-08 04:52:30','2026-09-10 07:48:14'),(10,'REQ-XGJYTT8T',4,10,'faculty','released','for office',NULL,NULL,200.00,2,9,3,'2026-09-08 05:09:17','2026-09-08 05:09:44','2026-09-14 17:53:23','2026-09-08 05:08:48','2026-09-14 17:53:23');
/*!40000 ALTER TABLE `requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(2,1),(2,3),(3,1),(3,4),(4,1),(4,2),(5,1),(5,6),(6,1),(6,3),(7,1),(7,5),(8,1),(8,2),(9,1),(10,1),(10,2),(10,3),(11,1),(12,1);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrator','web','2026-07-24 06:41:04','2026-07-24 06:41:04'),(2,'Accounting','web','2026-07-24 06:41:04','2026-07-24 06:41:04'),(3,'Supply Personnel','web','2026-07-24 06:41:04','2026-07-24 06:41:04'),(4,'Faculty','web','2026-07-24 06:41:04','2026-07-24 06:41:04'),(5,'Student','web','2026-07-24 06:41:04','2026-07-24 06:41:04'),(6,'Admission','web','2026-08-26 05:29:07','2026-08-26 05:29:07');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('18kQYNnZYpcfBa9B8Hg8L7K5BNcuSKrPN0tiuKqH',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNzJ4M0tMa3dtS1pMQVAwZ21XWjZ4cW1VWHl2am5PYnQ1NW1CMGM0dyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789436530),('1rBTewbYBno51BJuVyMS3k1v2iMz4qtQWc9hUHWw',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiaGNKVWJjaTBJWk1YSWFXak1lSUNiVUtPNndLeUIxWVgyOHFqQmh1UyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789436524),('2eoPYB5FFNbDnixajLP1f9kVLZQunD2mR8lHCt65',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTVBJdXVOdG1VbzhNZlBDdURERkh2REJmeHJjMTlaME8xYmYwd2V1RyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789436530),('2xdl5rH48MUEzzpsHMc3b5WJ0p3wxxuguZEWdUEC',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTGNPdEhNemxsUFBwT3NRQmJpTnRaT0Zmc3BhQzB3MGdLek1ISTBWZiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789392953),('3HK0NARKkgO7y1Uuft6FbIfj7c2afIHCj02wORRu',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidDRMVklpYzZ4U3FCZDYxSjJmZGMxRFpKT1ZSWEV3cEx6TW9MN0hiUCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789436519),('3iVIredjz1sUHmEm8HihfJlmcaKkOgcRCFEzqlsZ',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRVpjWXFmZU4zZFBRRnFzMGZlbHRpRmF4NE00WnprVDNVTHpUeFdnZCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789436533),('45G3fLEThrowgnNq28Rogr74mM4RUXTZPveuYUaU',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZlFhWXZYbGlOU0gyV21YcEFsclBCOGZSaTdPeXh1SFBlY0VleVN4YyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly9sb2NhbGhvc3QvcGVjaXQtc2lzL3B1YmxpYy9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789354097),('6Q1Bhc52WpdHqajIRzv2cKpoykihXyh6e6NKnsHH',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoic0pRMkp2eE5xdXZnMW5EaVpjcDFuQ1ZrTENQMkpOcFZ5T3R1alJuOSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789436537),('72kO5jUBs4PmLbcezjbRWWs3jBRGNgfvpqU6N3Do',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiY3pDUUNKUkpxUUZmUERURmUzSUJpY2FEZnlrcWh4cHhveTR6N1kyUSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789392952),('7eSuse45Ff8GueyJhG0bHdAuducWF2OjOJvYjlX7',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiT0k3UnFVa09scWtBRllmOHlGRkh4ZEtxN2dFUlZXSE9GNWtlS01XdCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789436532),('8NCXRExFFTsOy0gzabw7ICB4CpKcRHxfCSUjOiON',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNjRYUFc3b216TVBMaFQ3WjRmZWZPYkJLNndJMzl6UTh6UmZrSTNVcyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789392944),('8SSNyVJuzCNdukRCh4EFlhRD7TnIdS5A2zIpP0cR',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoic1BRWmpKRFNtbWcxVXNXcUlJQW5aRGIxVVFFM01CbndwaTBtZHhoMiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789354045),('8vNCGCB8jJD6GCBtjYwoAcnqFpDmRCJuvTSgA0bZ',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoic0JTYUlOSlRNNnpZRDR1dnhpSkU3RlJ1T29oREN6VjNybGtVeHVLayI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789354013),('9FW0TqnK2n1hH4HbN3K3jgZeKV0XUKZD4rmKq1LV',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUko2V0dmWmhieGxzTlVhUU9ReGdEYzlFVFh3WUNXZlRSSHpqckhDaCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789354008),('9u1uaU7dyb934OnGQN6eMHUTzyR8fOgwLnBb551j',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOVBlSnpaWXN5Y3hMNVUyaXdnVnlrSmJVaUpaRzQ2T204NU9uVVVOeCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789392942),('aexYzOgz58sgraTUc6TnxbnA3Vburz0v1ncSjl3z',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWXBKZndmNzRNM1lzUmVSSTUzangyeVVtaXBtUkFEZ2F1N2M3WFpHZCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789392956),('AzD9ucwuK1u1zcTwSHSFREc1ihYXxX6MsszXkQ40',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZHQyNkpKeHVyeFFvZGNRUFhKMnJXbHBySEE1WmY2ZlVGckNCZ2h0NiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789436528),('b4sRcMyZ2hMMFA6LZ2WLcNBnW8Rimy41YZH29rZu',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidGxGR0tueWpzcXJiOG9DRllyaXNvT1ZkdG01cnFrQ0tnSUVhWlZvUyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789392962),('b5CixtWrGUqxGJBokfkGYUUniR6BOQBtsm41lgSf',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiS1Nld2poZmUwUGdQa3IwZEp1T05tVVo5V0ZDNTN0QU1KWjUwRzFSSyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789436536),('CJCz9wGi3qciabSfOIJHE2CNfKWvUNt6Qv4eexRx',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTmNVV1IwdkVyQjhHS1BuY0FMbVpHdFU0c1FpM2s5dWl0alNxVjFCVCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789436535),('CJfPakXbGeXEhZWegox0V9rQJy6GToILbk3Vsfy9',3,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoibGR4MWtHTk40dm0zUUVraVltU2p0cXJzWEwyRjJ4V3hZSDNrdmVkWCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9hdWRpdC1sb2dzIjtzOjU6InJvdXRlIjtzOjE2OiJhZG1pbi5hdWRpdC1sb2dzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MztzOjE2OiJsYXN0X2FjdGl2aXR5X2F0IjtpOjE3ODkzOTMwMDk7fQ==',1789393010),('CUrfybAkbzXYTszUgXNQKtqvRO2inpQbSmFkHQmd',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieWdjUENVcFZPUUFsNEcyNzdaM052WXMzR3Y3QUg3SldGem5GT3dJUSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789436520),('d2kiu8OPN18K6Tg05j9GVkygfwbYFJ9lICacAP6h',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWmU3UzVrQjRqWVN5RzBhY2ZGUzNHdFo0WFd4T0dqM1B5bXBnazc1ViI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789354031),('dkqkBoLGWuaTYSQIBdwKEBKFtbuztG8lSFlty78h',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoibnJPeWpnUzZseW9lQmk5ZVZhTTRjTTJmVHFqUjY3bTVSQVFxUlZSMSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789392961),('E8xlJXTUEvmSiX4Q2l1U5YMKysql4vOdxoLZs5TQ',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiS09uUDY4cjlEQVFrVmxGVUpsb2xaU1hpSnRQMTRvcWp4OFVmdmxRRCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789392950),('ELP7QzIcjYmIbBfLE9AxNDNJY1LineZ7F5wseZLj',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiendzVkhmOTY4ekZ2YVp3MVR5N1ZJd2NLMkRlWGFVRk50aWxPMHFGYyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789392934),('F0W15LJ67aEVVnRu5Qa2MkM09Te5wfYAqurkSLXH',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSnlMbXd2Zmhncmx5SEtsRHVsNWR3SmU5ZU52ZG1Zbmk4d3NRa0VndyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789354039),('GDUx4uYF2EmPFEEpZt68sryCOweEwKbqGqLl25Sp',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZ3hieFg5UXVPeW1XMGs3aXJiU1RmMXh2cFNDVUJ0QjRCVmduRDZoUyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789354037),('GPcZ1Q2FsxODRwxhzgdkraD1l3KiDQnZ0sRkPrBV',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZjVYYU5uSnBaTXdtUVJ4Y3VjT2F5R0pESTdXMXFEYTFQV3NxcFBUcCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789354012),('GsDJtlK9i6vHMEp0WDYjcltlg9pS8aPhhNdHV6LB',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoicXJzaWR1Q01mWWVjTzhFQ1RhYlhtOFNmTWpQUlBrWk1kbUJTMlk5diI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789392957),('HezOPqswD0NdrUQXyNeJuK7jrJCQ5nehFDrQAH2u',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSG0yMGNkbTI5M1hmaTlvRW5PVFNUWVY2ZWVVTkNMcm5RMmhnekp4NCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly9sb2NhbGhvc3QvcGVjaXQtc2lzL3B1YmxpYy9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789372605),('hFntnVhTizwqbAXl7dRYTkRCsyD6e7TTsqDSgpRj',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiS2FIMllod2s2NmlONE5vU3A1ZVV3QnozVjZZcERyV1BKZ0dzNEhHOSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789392949),('I2MxSu1srwArDwh0b8RohGdqY4wMyVv41SmMHzTJ',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoid0NIbk5ud0JBTGFYaXZtYXd5UHhFWm4zM1ZoUmV1cEI4UDZabEdrZiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789392947),('IvdPjUNKrPxiCULkkaYLyHcRUVayF82hWFTvsm8B',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoia2hJSzVJRzV4ZldyRzhydXF4SjRjSllHOHdRU2dCcTJSVXhLZEhmSyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789354019),('JYg3Mk3i7x8ao90E6AusOMFEYXzaTzsVynxyhZJb',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTUJucXNCZnZuRVZmNEFOVW9ZSXF6eVVTNENjV09GRUZ3UTlOMlE5NyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789392943),('jyX5bqyMH2xc6SHijrI3Q7NicEGdfMM71NMO5LoQ',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUmNzYVl6c0VEOHhBUER5QjZjMm1wOFBiNmZwSjBDZTJGSnk0QjVJZyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789354027),('kE75p4wohCcNG6qcCW7Bkg21XxfGCDkbvhSsIT6B',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiV3FXaEZnaXRmSTRNajBwYnc0amQwQXBWWm9rak5iZlpqUTAzYkRZZiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789436518),('lnGkATFxR5GI4ESCcDApRAitBGyas1mWmJMWUeUC',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVHRjbFFiU3dORVhSNllTSDVlOUlGRFNPY0g2M1UycThLVEtXekh4WSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789436522),('lVTZP8keeoayqu7BXpfbMmuLFOCQyE7Si8PswqY3',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRWRGT0Q4c0hMWUN2ZlFBeUxkQ01LS0xGbXFXSzZRTGhtTVlzUHdXeCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789392936),('MBR5atgrvV6lw37TJzw4Bp1SlE0KpykmxmTEKRKb',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRFQyZU13THBURElGUHFHU2haQzdEVTN3WEo2TXRrN3FqVXpiY1hpTyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789392937),('miSZjviEX9BTnldtlPqyINDaxZwdeX5qfcmXbfet',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieEZMQ3kyaVJGb1lkdFVqQkw4Z3JCZDZuVENOM2xqUEpFd1VjN3dVTCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789436518),('MJHgIYyvtPEdEl01hCtxT8u4RJGCQkzxHC1XSbnS',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiV0F6dXFjUkFRbWx5NHpneWlERnlvTFpEOXpNUW5qa1NRTlkzd04yMCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789354030),('opu0FDaTlVAscqpendZcdgaK7yWaOIkqh5dfp6yL',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSTJHcExPamE4M3BDRVNSdnk3dmg2ZERWSDU4d3N1alRVSkJWNHUzZCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789392958),('OqIcIoXA9Hfro4kpDAs9R9mUJVgIpSnx2FAiPGF1',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQ29lcUdrOGZiS2RoNDFyRmY3VThyT3U2R3ppZjFXRVd2RDhHenZrNyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789436526),('OVM0xnARMxJiB623kZ7czmAd2UMPZwNhIQLIFcBx',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiblNGaWNNMnhNaWRucmhGaUJkazljWDc0ZnVzalV2bU8xcHF0NFJBeiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789392941),('P84oG7uZP359ni3XGr57WDltVgSsp1rIvQefnpWJ',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiN1R6ZUNJczYyeVN3TTQ5T2lpZXlwekJvdDE3bEtkd05ISFV6UHJrZiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789392951),('po9i5dYSfyfgoezMbvbteMDgR6wQyCYIgPmEhppL',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieGhxaDhrVXdjY2hoMDluTHJKbUhRV0dlS2EzRDM5Z1haNGs5MlBuSCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789392956),('pqi0us9zerlidqIdqXbZrREgjlIbfCeXKnCbpZi5',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiR1FaRlFsRUFmWHcwTmNrOVVGeExIZGRjRjJsOExtZzl3SVlEeWk1aCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789436523),('pv5o6cXVGrql0WTWDqgitgDaEWjw6Kmnfb52N7W9',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZTZ6cGhnU21YQUJsUjRLYlNka0FQNlFFN2J0eFVtZlk3RHFWdGluSCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789436527),('PXUm7DgcxpnpkwnkyNf3X5WfbQI2XUYaX31rjirX',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoicEZRV3hnUUtkYjFsRjNLZEtWcklud3JmS0ZFdmpDSmowM0tQQk9iaCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789436531),('QjBHoar4993J21jXgF3OdBuF1CMixwZyKZ7xfFNW',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWXRveERSSnhleUJKRmwxMk1jbEphSUNQMXFMcktRVXoxb2lLekd2bSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789392941),('QjQyp3GLVe5meLV9cb0boWV8k314mFSs1ubH0MGY',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUXRQb3FvM09VTk9YaThSb3EwdXo3ankweWRGZ2hZZTdjTU9kekdzZyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789354021),('qk8d7pLASQf58MHeLi7LMnmA4mc5tBSqlY5Ve3jc',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNWRpbWFvU3dVTWhlVzVQcDE5MVRwYXhaUHQ3MmZydGlhU1RyVjVCQyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789354038),('QLKT3QSaOVuEFW7InWiKKctWTnTToTckZjYdYbeM',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieDFFdkJVYjdpOU9IY3dka2tCdVpMMzhIQUJQRG81NFJDZlpzdGpNSSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789392943),('QTvFd68zpe0wuLCHrpDzVQQonE6PaCheAVmYAVA5',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTlR1S1FoWm9IRkViNm1FSm9kMmJxVGZpQlpOODlXbTBCQlZLZVNnUiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789354034),('Qvrc0AW0M6X7AqWEup5pAYYGOXeO0z7meq633mun',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRWJKYzBsbndhQVY4Y0lwRW1rdDRYWlZtbW41QlNrd0FQYWpVck0xeSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789392935),('R49zjlDjbvlvPtophFkYOaKUxYKM5OruMlMpowYb',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRjJlRFBOY2hiUE45WVhlSGZSRW9NUFRERUdEOU5DamxaMzhNOGFhNyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789354028),('rZIl96Dx0OYs4p6lXX5P2bpwq1yS6dhDXhngFDyT',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMTFxMWx3M3VNbzBpc0E2eWZ4WmZJYTVJdDlDVVl3NTdTYzVuaFZRWSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789392960),('sHhsCxtL98FLG5Jnt5uyxegQC9GZzriqjWOUAIVk',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMWJManY2SHJ1RkJSMzNMd2lXVW5NNXM1ZFRRVTN4d08yT1JvbGRNMyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789354020),('SrrIRoLR86TzT2L23OR5YlTVIV0oT7Ot2DDrQKDb',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiaU1aNHpZU2ZaMlB2THRxNTYxWGgyQW5mVUtXVFlHTXUyUE9KeTdNTiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789354022),('sVrr5n0bFuKZrKhMextDLkhKqA3Rtzhd5QAWz4eu',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUHN6bGd6RlltYmJJY3lsT1pTbFdTQUZUZEp4QU9RT0R0a043T2QyOCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789392955),('tBTOyxlE7H9uOOLBEBIlFcrvH6sgmCYjlxAAc45z',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoielZBVVkzQUNMaUcyRXhtTkJEb2lraTd2Z2NXekk0eU5LZnUyUGM4TyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789354033),('tdpQR4yBC3mQfBnV5PRa5zCMoryM9AQraVqULjxb',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoic0lhaGdJTTF4ZWQ3UjgzV253WEEwSGJCdklnVlk4aW9GMWg5SU9VOCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789436533),('Te3KlroMFTXLmTAbSYr7CKAynA9OJCr022wQwvDy',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZFJoR1BMZUZqTTVMdEVTWjhaRlNpZUVzRk5UY21pbWk0TG9RRVhvWSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789354017),('TlBbTxzA039KGF4uAYoWnSrrGHOE723KNROBOccb',3,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiYTd5MG15WXNRWjJFbVRkSjVvTmp0Ym9wWXJydDBRSGs3OU5TS2hpbiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MztzOjE2OiJsYXN0X2FjdGl2aXR5X2F0IjtpOjE3ODkzNjIxNDg7fQ==',1789362148),('tLJSmW2THu9N3eCVW9bcw16ameNhtUNPAktdqBjf',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiR0xtWDBmMFdXRjRMZk50dFVpWG9VM0Z5SmtocmZjOUhaN2Y4VHN6QyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789392948),('tQih1TO0l02Qer1V8KtP8oLEW8OFzusYEEEFpWVK',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSUIxeGNud2wwUmY0RjNCZDJIZ3lEblFadm45VHpHOU9sQ1J6VHJlTCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789392959),('trkzKbQPl9h3NgPkRwO7seo84AyLpkp460A6hzRc',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYzM0VGxCUEZDOEo1SmRGd0g4dVVsczZMV3doWGR3N3dRRW1DeERUVCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789436529),('twXV0r6Bgt7oARic53SQ66Fh8HopvnLaI8eyFxe3',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNDBlZkQyellnUG45VDFZckV4d2Z4RFBpSUxwVFlqMUV5V1p1SmtCQSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789392933),('UBNH89XLLnEtHe1M4iioNdh1QEilgRL1X9oPZnkj',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOVgxZDZ2alNiYWdKU2pOSnp5MzF0Q2RkOUt4Mkx0ZWlhT3BRdDM5eiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789436525),('UmeiQsK8BFVhbFi9PD0M7knqLZ3NDekLKBfItMtu',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOWlFcmljZ05VVGRKMFYzWk5KUlExOE9YTUVwUGNHTzNhODlhcHNkYiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789436520),('uPkAAEzilUUqyjxizrnkmIbCEOC8CV6OSmEHCe5a',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYjJDY0hhMmtUUnVTQUdJVUx3WTU0ZzBMcWhUNjJ3bXMyOTUyM2EzTSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=',1789438168),('UR6ATZyMWqUVkZFPkwgvsHM49xm3Nq6fILYivrJ3',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiM0Q3SHB2ak1nNUJpeWZrWDJtcnhzTHdqdlVyRHp0Y0Y3MlBSOUozOSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789436519),('VpBSwatqW0flVViP13OAxQVC7gpehcG9UNv0FzT3',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNmpNdWF3Q0oxNkdiUEdTNVNoc2psdWNiT1JraVNPbUpmSUp0eERWdiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789392963),('VxAJGMyKZaOcNinlkexYiTJjfriOuSENpEc3jnM5',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVW03ZThkdkxCRzZITjI3TzREa1JDUVpuZVJOdW1uQjRRamlwNEpJdCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789436536),('VZXTLQkNWgMmtWKCkSScNIs96uk1CQh59qgXYsXf',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNFE0RkNDbjFGWHg3N0Nad1RqdVdDQTU0dUZXZnc1djNoSVFSOXZYVSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789392934),('Wap8mcyFIx5acjJdGbEKz0WF7K4QveBRrfrQo7bb',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZkp0cXplVW5TTTFGbzZ5dDdkbHlEY2FxVHBzb3Z4NTNVcmloRkdTZSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789392945),('wDCDwbtulvfebeHXiZoQIyT82cNiOgM9rN25NzLp',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNVF4bzRHY2RuVGNUMHhEWmRETWxHYlRKcTd2VmhqSFVoblhHdzFsYyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789354040),('wJmygFpXszlUx0Hb67kjbXhbndqk10yvn8zGlZVk',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUmw0YjZJdjBYa1I1bE9ERDFHZkQ2VkxmRTlyMGRlUE0zbVJKcVhBYiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789392946),('xAzom4jlqvkgDspowiysvPOTsQ6Sk3Yho1wI5UZg',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZXFseE1JeTdlOW9UZUZsWEVRU1c4OXVkenBjek5tS2kwaW5QSGxmTSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789354024),('xjG7BBq7rTfMb7QmIjfbMuXOmScflHZ8mdZoyjzE',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRTltVnRCVWt2NkUxYXdGaE5kQmxKVFZRUWZrUGlPVG5tNVdWTGNlViI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789354025),('XnMadDIOHM4lL6CR1iy86lG1WT3qKdPQ0VGKbEG5',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoib2lsTE1Cbk5yM1lPbmZWTFZOR09sSklvNHpRUUpMM3Jscjg1VlJLUCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789436534),('XOTP5bFohyy2C6pnbjVbU1AT30K8oHKa9tDCwKvI',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoia1BwNjRqWUpJb1Yxd3RvZjF2RElSZ2I4dFVVVGpFU1Y3U1lOaTZveiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789354036),('Y4SxlmGYUGIv1C9WoAe3ECAFDeiW7EMwTpxx8Jx6',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOHVJdmN2VzJhU0RBOWFHWXhsM0xTWGUyY0ptTFBEa1RSSXJWR01udSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789392938),('z5h9O8pFxP7fUhIGa8ND3kom1cmoIaPmGbc2IEo5',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.20.17 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRTdaZ0ExN1gwTmVlbDUxZlcyTWI3NzZrV0NDQXNCQmJzN05SaHdtMCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789436521),('ZataJHV1LTI8gdhWLxTaFK2MroM15Cq5hdXfsKhG',3,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiMzdldjZ1ek9hNzc5R1hIWjhuVm1waUxROUFvcnp2VDA4WWl3UUxFWCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NzY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9zdXBwbHkvcHVyY2hhc2UtaGlzdG9yeT9pbnNwZWN0aW9uPSZraW5kPWRlcGFydG1lbnQmcT0iO3M6NToicm91dGUiO3M6MjM6InN1cHBseS5wdXJjaGFzZS1oaXN0b3J5Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MztzOjE2OiJsYXN0X2FjdGl2aXR5X2F0IjtpOjE3ODk0MzkzMTM7fQ==',1789439313);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_logs`
--

DROP TABLE IF EXISTS `stock_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `inventory_id` bigint(20) unsigned NOT NULL,
  `action` enum('stock_in','stock_out','adjustment','delivery') NOT NULL,
  `quantity` int(11) NOT NULL,
  `balance_after` int(11) NOT NULL,
  `delivery_recipient` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `performed_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_logs_inventory_id_foreign` (`inventory_id`),
  KEY `stock_logs_performed_by_foreign` (`performed_by`),
  CONSTRAINT `stock_logs_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_logs_performed_by_foreign` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_logs`
--

LOCK TABLES `stock_logs` WRITE;
/*!40000 ALTER TABLE `stock_logs` DISABLE KEYS */;
INSERT INTO `stock_logs` VALUES (1,2,'delivery',5,80,'Prof. Juan Dela Cruz','Released for REQ-PCR3LYIW',3,'2026-07-24 08:18:00','2026-07-24 08:18:00'),(2,6,'delivery',1,27,'Maria Santos','Student purchase PUR-J0B7AXVI',3,'2026-07-24 08:26:28','2026-07-24 08:26:28'),(3,4,'delivery',1,59,'Maria Santos','Student purchase PUR-F27D0VVL',3,'2026-07-24 08:26:55','2026-07-24 08:26:55'),(4,1,'delivery',1,119,'Prof. Juan Dela Cruz','Released for REQ-GMWTCZAW',3,'2026-07-24 08:35:16','2026-07-24 08:35:16'),(5,7,'delivery',20,2,'Vea Villaver','Released for REQ-JYDE6HY8',3,'2026-07-24 08:44:31','2026-07-24 08:44:31'),(6,3,'delivery',1,39,'Joy Tienes','Student purchase PUR-PD8IZ03C',3,'2026-07-24 19:11:50','2026-07-24 19:11:50'),(7,8,'delivery',3,5,'Vea Villaver','Released for REQ-2YCLVJ4C',3,'2026-07-24 20:13:53','2026-07-24 20:13:53'),(8,4,'delivery',1,58,'Joy Tienes','Student purchase PUR-MWGYFIIS',3,'2026-07-24 20:48:44','2026-07-24 20:48:44'),(9,7,'stock_in',3,5,NULL,NULL,3,'2026-08-05 19:38:35','2026-08-05 19:38:35'),(10,7,'stock_in',5,10,NULL,NULL,3,'2026-08-05 19:39:03','2026-08-05 19:39:03'),(11,16,'adjustment',2,42,NULL,'Updated on-hand by size from inventory edit. (Size: 3XL)',3,'2026-08-28 02:22:35','2026-08-28 02:22:35'),(12,1,'stock_in',5,124,NULL,NULL,3,'2026-08-28 02:23:52','2026-08-28 02:23:52'),(13,16,'stock_in',4,46,NULL,'Size: M',3,'2026-08-28 02:25:07','2026-08-28 02:25:07'),(14,16,'delivery',1,45,'Joy Tienes','Student purchase PUR-NOKIY96X (Size: M)',3,'2026-08-31 20:32:45','2026-08-31 20:32:45'),(15,11,'delivery',1,199,'Joy Tienes','Student purchase PUR-DI58BZAM',3,'2026-09-08 01:34:09','2026-09-08 01:34:09'),(19,8,'delivery',1,4,'Prof. Juan Dela Cruz','Released for REQ-VNJKFCK9',3,'2026-09-08 04:54:19','2026-09-08 04:54:19'),(20,2,'stock_in',5,85,NULL,'TRY LNG',3,'2026-09-13 18:59:37','2026-09-13 18:59:37'),(21,4,'stock_in',50,108,NULL,'for kids',3,'2026-09-13 19:03:06','2026-09-13 19:03:06'),(22,11,'delivery',98,101,NULL,'damage',3,'2026-09-13 19:09:05','2026-09-13 19:09:05'),(23,11,'adjustment',2,99,NULL,'lost',3,'2026-09-13 19:10:52','2026-09-13 19:10:52'),(24,7,'delivery',1,9,'Prof. Juan Dela Cruz','Released for REQ-XGJYTT8T',3,'2026-09-14 17:53:23','2026-09-14 17:53:23');
/*!40000 ALTER TABLE `stock_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_code` varchar(30) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `suppliers_supplier_code_unique` (`supplier_code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'SUP-0001','Cebu Paper & Office Supply','Maria Reyes','032-255-1001','sales@cpos-demo.pecit.local','Colon St., Cebu City',1,'2026-09-13 18:53:42','2026-09-13 18:53:42'),(2,'SUP-0002','Visayas Uniform House','Jose Tan','032-255-1002','orders@vuh-demo.pecit.local','Mandaue City, Cebu',1,'2026-09-13 18:53:42','2026-09-13 18:53:42'),(3,'SUP-0003','Island Tech Computer Trading','Ana Cruz','032-255-1003','support@itct-demo.pecit.local','IT Park, Lahug, Cebu City',1,'2026-09-13 18:53:42','2026-09-13 18:53:42'),(4,'SUP-0004','Campus Care Janitorial Supply','Pedro Santos','032-255-1004','hello@ccjs-demo.pecit.local','Talisay City, Cebu',1,'2026-09-13 18:53:42','2026-09-13 18:53:42'),(5,'SUP-0005','Joy Tienes','Joy Tienes','09123456789','joytienes9@gmail.com','bagdad',1,'2026-09-13 19:05:25','2026-09-13 19:06:02');
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_number` varchar(255) NOT NULL,
  `inventory_id` bigint(20) unsigned NOT NULL,
  `type` varchar(40) NOT NULL,
  `quantity` int(11) NOT NULL,
  `quantity_before` int(11) NOT NULL,
  `quantity_after` int(11) NOT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `performed_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `source_type` varchar(40) DEFAULT NULL,
  `quantity_in` int(10) unsigned NOT NULL DEFAULT 0,
  `quantity_out` int(10) unsigned NOT NULL DEFAULT 0,
  `balance_after` int(11) DEFAULT NULL,
  `unit_cost` decimal(12,2) DEFAULT NULL,
  `total_cost` decimal(12,2) DEFAULT NULL,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `delivery_receipt_number` varchar(255) DEFAULT NULL,
  `size` varchar(10) DEFAULT NULL,
  `transaction_date` timestamp NULL DEFAULT NULL,
  `inspection_status` varchar(20) DEFAULT NULL,
  `inspection_notes` text DEFAULT NULL,
  `inspected_by` bigint(20) unsigned DEFAULT NULL,
  `inspected_at` timestamp NULL DEFAULT NULL,
  `purchased_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transactions_transaction_number_unique` (`transaction_number`),
  KEY `transactions_inventory_id_foreign` (`inventory_id`),
  KEY `transactions_performed_by_foreign` (`performed_by`),
  KEY `transactions_supplier_id_foreign` (`supplier_id`),
  KEY `transactions_inspected_by_foreign` (`inspected_by`),
  KEY `transactions_purchased_by_foreign` (`purchased_by`),
  CONSTRAINT `transactions_inspected_by_foreign` FOREIGN KEY (`inspected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transactions_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_performed_by_foreign` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_purchased_by_foreign` FOREIGN KEY (`purchased_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transactions_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,'TXN-6TA87EUGJT',4,'reserve',1,60,60,'App\\Models\\PurchaseRequest',1,'Reserved for PUR-F27D0VVL',2,'2026-07-24 08:05:05','2026-07-24 08:05:05',NULL,0,0,60,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 08:05:05',NULL,NULL,NULL,NULL,NULL),(2,'TXN-VGVLAZ7ZXY',2,'reserve',5,85,85,'App\\Models\\SupplyRequest',1,'Reserved for REQ-PCR3LYIW',1,'2026-07-24 08:08:49','2026-07-24 08:08:49',NULL,0,0,85,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 08:08:49',NULL,NULL,NULL,NULL,NULL),(3,'TXN-J2NPTUMNLY',6,'reserve',1,28,28,'App\\Models\\PurchaseRequest',2,'Reserved for PUR-J0B7AXVI',2,'2026-07-24 08:15:54','2026-07-24 08:15:54',NULL,0,0,28,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 08:15:54',NULL,NULL,NULL,NULL,NULL),(4,'TXN-ICCRTICSHZ',2,'release',5,85,80,'App\\Models\\SupplyRequest',1,'Released for REQ-PCR3LYIW',3,'2026-07-24 08:18:00','2026-07-24 08:18:00',NULL,0,5,80,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 08:18:00',NULL,NULL,NULL,NULL,NULL),(5,'TXN-MRFNDD07RC',6,'release',1,28,27,'App\\Models\\PurchaseRequest',2,'Student purchase PUR-J0B7AXVI',3,'2026-07-24 08:26:28','2026-07-24 08:26:28',NULL,0,1,27,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 08:26:28',NULL,NULL,NULL,NULL,NULL),(6,'TXN-O0RP8JJWWB',4,'release',1,60,59,'App\\Models\\PurchaseRequest',1,'Student purchase PUR-F27D0VVL',3,'2026-07-24 08:26:55','2026-07-24 08:26:55',NULL,0,1,59,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 08:26:55',NULL,NULL,NULL,NULL,NULL),(7,'TXN-OMCOZFJF0J',1,'reserve',1,120,120,'App\\Models\\SupplyRequest',2,'Reserved for REQ-GMWTCZAW',1,'2026-07-24 08:33:57','2026-07-24 08:33:57',NULL,0,0,120,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 08:33:57',NULL,NULL,NULL,NULL,NULL),(8,'TXN-7JEY4TQND9',1,'release',1,120,119,'App\\Models\\SupplyRequest',2,'Released for REQ-GMWTCZAW',3,'2026-07-24 08:35:16','2026-07-24 08:35:16',NULL,0,1,119,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 08:35:16',NULL,NULL,NULL,NULL,NULL),(9,'TXN-VDQOC3CBKE',7,'reserve',20,22,22,'App\\Models\\SupplyRequest',3,'Reserved for REQ-JYDE6HY8',1,'2026-07-24 08:43:38','2026-07-24 08:43:38',NULL,0,0,22,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 08:43:38',NULL,NULL,NULL,NULL,NULL),(10,'TXN-BDIIV8MQPY',7,'release',20,22,2,'App\\Models\\SupplyRequest',3,'Released for REQ-JYDE6HY8',3,'2026-07-24 08:44:31','2026-07-24 08:44:31',NULL,0,20,2,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 08:44:31',NULL,NULL,NULL,NULL,NULL),(11,'TXN-OPD3TCMHLD',3,'reserve',1,40,40,'App\\Models\\PurchaseRequest',3,'Reserved for PUR-PD8IZ03C',2,'2026-07-24 19:10:43','2026-07-24 19:10:43',NULL,0,0,40,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 19:10:43',NULL,NULL,NULL,NULL,NULL),(12,'TXN-0XPRCHBAMH',3,'release',1,40,39,'App\\Models\\PurchaseRequest',3,'Student purchase PUR-PD8IZ03C',3,'2026-07-24 19:11:50','2026-07-24 19:11:50',NULL,0,1,39,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 19:11:50',NULL,NULL,NULL,NULL,NULL),(13,'TXN-RTGGLV7UX0',8,'reserve',3,8,8,'App\\Models\\SupplyRequest',4,'Reserved for REQ-2YCLVJ4C',1,'2026-07-24 20:11:56','2026-07-24 20:11:56',NULL,0,0,8,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 20:11:56',NULL,NULL,NULL,NULL,NULL),(14,'TXN-TY86WEMWOD',8,'release',3,8,5,'App\\Models\\SupplyRequest',4,'Released for REQ-2YCLVJ4C',3,'2026-07-24 20:13:53','2026-07-24 20:13:53',NULL,0,3,5,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 20:13:53',NULL,NULL,NULL,NULL,NULL),(15,'TXN-KFO8OKEHNH',4,'reserve',1,59,59,'App\\Models\\PurchaseRequest',4,'Reserved for PUR-MWGYFIIS',2,'2026-07-24 20:47:26','2026-07-24 20:47:26',NULL,0,0,59,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 20:47:26',NULL,NULL,NULL,NULL,NULL),(16,'TXN-JAHNCNBZLP',4,'release',1,59,58,'App\\Models\\PurchaseRequest',4,'Student purchase PUR-MWGYFIIS',3,'2026-07-24 20:48:44','2026-07-24 20:48:44',NULL,0,1,58,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 20:48:44',NULL,NULL,NULL,NULL,NULL),(17,'TXN-5I3MYBNXVS',7,'stock_in',3,2,5,NULL,NULL,NULL,3,'2026-08-05 19:38:35','2026-08-05 19:38:35',NULL,3,0,5,NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-05 19:38:35','pending',NULL,NULL,NULL,3),(18,'TXN-F8PX9NLJCR',7,'stock_in',5,5,10,NULL,NULL,NULL,3,'2026-08-05 19:39:03','2026-08-05 19:39:03',NULL,5,0,10,NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-05 19:39:03','pending',NULL,NULL,NULL,3),(19,'TXN-CVC4MQHFST',10,'reserve',1,9,60,'App\\Models\\PurchaseRequest',5,'Reserved for PUR-WSMAXFXO (Size: M)',2,'2026-08-26 05:23:30','2026-08-26 05:23:30',NULL,0,0,60,NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-26 05:23:30',NULL,NULL,NULL,NULL,NULL),(20,'TXN-EFC8BEZUTW',16,'reserve',1,6,40,'App\\Models\\PurchaseRequest',7,'Reserved for PUR-FCP8AXGS (Size: M)',2,'2026-08-28 01:42:26','2026-08-28 01:42:26',NULL,0,0,40,NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-28 01:42:26',NULL,NULL,NULL,NULL,NULL),(21,'TXN-GM1UB4KXAI',16,'adjustment',2,5,7,NULL,NULL,'Updated on-hand by size from inventory edit. (Size: 3XL)',3,'2026-08-28 02:22:35','2026-08-28 02:22:35',NULL,2,0,7,NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-28 02:22:35',NULL,NULL,NULL,NULL,NULL),(22,'TXN-B568UWDXJO',1,'stock_in',5,119,124,NULL,NULL,NULL,3,'2026-08-28 02:23:52','2026-08-28 02:23:52',NULL,5,0,124,NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-28 02:23:52','pending',NULL,NULL,NULL,3),(23,'TXN-0EYTDQ1KWV',16,'stock_in',4,6,10,NULL,NULL,'Size: M',3,'2026-08-28 02:25:07','2026-08-28 02:25:07',NULL,4,0,10,NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-28 02:25:07','pending',NULL,NULL,NULL,3),(24,'TXN-FJUGP8W6R3',16,'reserve',1,10,46,'App\\Models\\PurchaseRequest',8,'Reserved for PUR-NOKIY96X (Size: M)',2,'2026-08-31 20:30:59','2026-08-31 20:30:59',NULL,0,0,46,NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-31 20:30:59',NULL,NULL,NULL,NULL,NULL),(25,'TXN-X6NDROODZE',16,'release',1,10,9,'App\\Models\\PurchaseRequest',8,'Student purchase PUR-NOKIY96X (Size: M)',3,'2026-08-31 20:32:45','2026-08-31 20:32:45',NULL,0,1,9,NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-31 20:32:45',NULL,NULL,NULL,NULL,NULL),(26,'TXN-JXLXWD7X1S',3,'reserve',1,39,39,'App\\Models\\SupplyRequest',5,'Reserved for REQ-STEWWTLH',9,'2026-09-03 04:54:00','2026-09-03 04:54:00',NULL,0,0,39,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-03 04:54:00',NULL,NULL,NULL,NULL,NULL),(27,'TXN-4SJUIREAEI',11,'reserve',1,200,200,'App\\Models\\PurchaseRequest',9,'Reserved for PUR-DI58BZAM',2,'2026-09-08 01:28:44','2026-09-08 01:28:44',NULL,0,0,200,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-08 01:28:44',NULL,NULL,NULL,NULL,NULL),(28,'TXN-GZCLKWYXQP',11,'release',1,200,199,'App\\Models\\PurchaseRequest',9,'Student purchase PUR-DI58BZAM',3,'2026-09-08 01:34:09','2026-09-08 01:34:09',NULL,0,1,199,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-08 01:34:09',NULL,NULL,NULL,NULL,NULL),(29,'TXN-LVICF3YEIP',18,'reserve',1,5,5,'App\\Models\\PurchaseRequest',10,'Reserved for PUR-VBRVJLDT (Size: M)',2,'2026-09-08 04:13:23','2026-09-08 04:13:23',NULL,0,0,5,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-08 04:13:23',NULL,NULL,NULL,NULL,NULL),(30,'TXN-LUIZ5J5LDL',11,'reserve',1,199,199,'App\\Models\\PurchaseRequest',6,'Reserved for PUR-AOH8WO4Q',2,'2026-09-08 04:18:17','2026-09-08 04:18:17',NULL,0,0,199,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-08 04:18:17',NULL,NULL,NULL,NULL,NULL),(34,'TXN-KQYXE1PAFT',8,'reserve',1,5,5,'App\\Models\\SupplyRequest',9,'Reserved for REQ-VNJKFCK9',9,'2026-09-08 04:53:25','2026-09-08 04:53:25',NULL,0,0,5,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-08 04:53:25',NULL,NULL,NULL,NULL,NULL),(35,'TXN-HWA3JKQUHQ',8,'release',1,5,4,'App\\Models\\SupplyRequest',9,'Released for REQ-VNJKFCK9',3,'2026-09-08 04:54:19','2026-09-08 04:54:19',NULL,0,1,4,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-08 04:54:19',NULL,NULL,NULL,NULL,NULL),(36,'TXN-SMYPT4IEJO',7,'reserve',1,10,10,'App\\Models\\SupplyRequest',10,'Reserved for REQ-XGJYTT8T',9,'2026-09-08 05:09:44','2026-09-08 05:09:44',NULL,0,0,10,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-08 05:09:44',NULL,NULL,NULL,NULL,NULL),(37,'TXN-SM0VFMCE8Q',2,'stock_in',5,80,85,NULL,NULL,'TRY LNG',3,'2026-09-13 18:59:37','2026-09-13 18:59:37','manual_external',5,0,85,250.00,1250.00,4,'PO-001',NULL,NULL,'2026-09-13 18:59:37','pending',NULL,NULL,NULL,3),(38,'TXN-HJQTUWKJCX',4,'stock_in',50,58,108,NULL,NULL,'for kids',3,'2026-09-13 19:03:06','2026-09-13 19:03:06','donation',50,0,108,20.00,1000.00,NULL,NULL,NULL,NULL,'2026-09-13 19:03:06','pending',NULL,NULL,NULL,3),(39,'TXN-ND6CKUI7DX',11,'stock_out',98,199,101,NULL,NULL,'damage',3,'2026-09-13 19:09:05','2026-09-13 19:09:05','other',0,98,101,NULL,NULL,4,'PO-002',NULL,NULL,'2026-09-13 19:09:05',NULL,NULL,NULL,NULL,NULL),(40,'TXN-0JVCCYKPEX',11,'adjustment_out',2,101,99,NULL,NULL,'lost',3,'2026-09-13 19:10:52','2026-09-13 19:10:52','adjustment',0,2,99,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-13 19:10:52',NULL,NULL,NULL,NULL,NULL),(41,'TXN-NSCGMEXAYF',9,'reserve',1,11,80,'App\\Models\\PurchaseRequest',14,'Reserved for PUR-ZZVDVSJW (Size: L)',2,'2026-09-14 17:52:41','2026-09-14 17:52:41',NULL,0,0,80,NULL,NULL,NULL,NULL,NULL,'L','2026-09-14 17:52:41',NULL,NULL,NULL,NULL,NULL),(42,'TXN-PKMNMG3NSW',7,'release',1,10,9,'App\\Models\\SupplyRequest',10,'Released for REQ-XGJYTT8T',3,'2026-09-14 17:53:23','2026-09-14 17:53:23',NULL,0,1,9,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-14 17:53:23',NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `units_of_measurement`
--

DROP TABLE IF EXISTS `units_of_measurement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `units_of_measurement` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `symbol` varchar(20) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `units_of_measurement_symbol_unique` (`symbol`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `units_of_measurement`
--

LOCK TABLES `units_of_measurement` WRITE;
/*!40000 ALTER TABLE `units_of_measurement` DISABLE KEYS */;
INSERT INTO `units_of_measurement` VALUES (1,'Piece','pcs','Individual pieces','2026-09-10 07:18:11','2026-09-10 07:18:11'),(2,'Box','box','Boxes','2026-09-10 07:18:11','2026-09-10 07:18:11'),(3,'Pack','pack','Packs','2026-09-10 07:18:11','2026-09-10 07:18:11'),(4,'Ream','ream','Paper reams','2026-09-10 07:18:11','2026-09-10 07:18:11'),(5,'Kilogram','kg','Kilograms','2026-09-10 07:18:11','2026-09-10 07:18:11'),(6,'Liter','L','Liters','2026-09-10 07:18:11','2026-09-10 07:18:11'),(7,'Unit','unit','Generic units','2026-09-10 07:18:11','2026-09-10 07:18:11'),(8,'Bottle','bottle','Bottles','2026-09-10 07:18:11','2026-09-10 07:18:11'),(9,'Case','case','Cases','2026-09-10 07:18:11','2026-09-10 07:18:11'),(10,'Set','set','Sets','2026-09-10 07:18:11','2026-09-10 07:18:11');
/*!40000 ALTER TABLE `units_of_measurement` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_department_id_foreign` (`department_id`),
  CONSTRAINT `users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'ADM-001','PECIT Admin',NULL,'admin@pecit.edu.ph','2026-07-24 06:43:05','$2y$12$uBKLRahlwQGoB//1TcUWhu1MoxC5s0KXFbrosSwz/QcuWv458SlG2',NULL,'2026-07-24 06:43:05','2026-09-03 04:50:07',7,NULL,1,'2026-09-03 04:50:07'),(2,'ACC-001','PECIT Accounting',NULL,'accounting@pecit.edu.ph','2026-07-24 06:43:05','$2y$12$k/qp1KIBk9Xk/eeW.D52cO01p3rC/z8cv2diKZ0z3sNeDnfsnD2VW',NULL,'2026-07-24 06:43:05','2026-09-14 18:07:59',7,NULL,1,'2026-09-14 18:07:59'),(3,'SUP-001','Supply Officer',NULL,'supply@pecit.edu.ph','2026-07-24 06:43:05','$2y$12$PdnxkklDtWHi3Dmj3UZUd.PCTqf1GXk5ptoYk1hoawRZHAmwO/vdS',NULL,'2026-07-24 06:43:05','2026-09-14 18:28:33',8,NULL,1,'2026-09-14 18:28:33'),(4,'FAC-001','Prof. Juan Dela Cruz',NULL,'faculty@pecit.edu.ph','2026-07-24 06:43:06','$2y$12$PAQWnYmtyK6MjCLINX8j0uOgqq9t2CjmSV0vjFux7pRCqrYuhv5H6',NULL,'2026-07-24 06:43:06','2026-09-14 18:09:02',10,NULL,1,'2026-09-14 18:09:02'),(5,'STU-001','Maria Santos','Santos','student@pecit.edu.ph','2026-07-24 06:43:06','$2y$12$GG/Hx2.Pq//baFYJ/8zQl./eqcxDpoLT67kloWblECrxdFUjtkjUy',NULL,'2026-07-24 06:43:06','2026-09-10 07:48:14',9,NULL,1,'2026-09-08 01:24:40'),(6,'20231-00245','Vea Villaver',NULL,'veapecit.edu@gmail.com','2026-07-24 08:40:15','$2y$12$Vcn7obbxA.yEmD/MIsUMgu2muLnXMXWUESZ.0N6f9ebXt7vfSFMI2',NULL,'2026-07-24 08:40:15','2026-07-24 20:16:18',9,'09123456789',1,'2026-07-24 20:16:18'),(7,'20231-00246','Joy Tienes','Tienes','tienesmaryjoy6@gmail.com','2026-07-24 19:07:21','$2y$12$k4YyeQ8njXjJ50N1Lqir9Os0/zrr8Ls4G.KDbWnLkNGYGnzC7J7gS',NULL,'2026-07-24 19:07:21','2026-09-14 17:52:07',9,'09123456789',1,'2026-09-14 17:52:07'),(8,'STU-CC-001','Carlos Mendoza','Mendoza','engineering.student@pecit.edu.ph','2026-08-05 20:33:00','$2y$12$86hoJzI9M9FJynZgJefvT.MwMPgzj1np.bvYgGoHuKPe1AOg0zyFa',NULL,'2026-08-05 20:33:00','2026-09-10 07:48:14',10,NULL,1,'2026-09-08 01:24:42'),(9,'ADN-001','PECIT Admission',NULL,'admission@pecit.edu.ph','2026-08-26 05:29:08','$2y$12$3gg4d3OxnbTA66Y8XwLDEOmfmrCMXH.1sVfnCCYcdjfUx80QDVOku',NULL,'2026-08-26 05:29:08','2026-09-08 05:11:04',7,NULL,1,'2026-09-08 05:11:04');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-15 10:31:06
