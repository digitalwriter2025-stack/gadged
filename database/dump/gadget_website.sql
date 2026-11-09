-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: gadget website
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
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) unsigned DEFAULT NULL,
  `attribute_changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attribute_changes`)),
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addresses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `label` varchar(50) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address_line` varchar(500) NOT NULL,
  `area` varchar(150) DEFAULT NULL,
  `district_id` bigint(20) unsigned DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_district_id_foreign` (`district_id`),
  KEY `addresses_user_id_is_default_index` (`user_id`,`is_default`),
  CONSTRAINT `addresses_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attribute_value_product_variant`
--

DROP TABLE IF EXISTS `attribute_value_product_variant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attribute_value_product_variant` (
  `product_variant_id` bigint(20) unsigned NOT NULL,
  `attribute_value_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`product_variant_id`,`attribute_value_id`),
  KEY `attribute_value_product_variant_attribute_value_id_index` (`attribute_value_id`),
  CONSTRAINT `attribute_value_product_variant_attribute_value_id_foreign` FOREIGN KEY (`attribute_value_id`) REFERENCES `attribute_values` (`id`),
  CONSTRAINT `attribute_value_product_variant_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attribute_value_product_variant`
--

LOCK TABLES `attribute_value_product_variant` WRITE;
/*!40000 ALTER TABLE `attribute_value_product_variant` DISABLE KEYS */;
INSERT INTO `attribute_value_product_variant` VALUES (46,1),(46,6),(46,10),(47,1),(47,7),(47,10),(48,1),(48,7),(48,11),(49,2),(49,7),(49,10);
/*!40000 ALTER TABLE `attribute_value_product_variant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attribute_values`
--

DROP TABLE IF EXISTS `attribute_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attribute_values` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `attribute_id` bigint(20) unsigned NOT NULL,
  `value` varchar(100) NOT NULL,
  `value_bn` varchar(100) DEFAULT NULL,
  `slug` varchar(120) NOT NULL,
  `color_hex` varchar(7) DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attribute_values_attribute_id_slug_unique` (`attribute_id`,`slug`),
  CONSTRAINT `attribute_values_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attribute_values`
--

LOCK TABLES `attribute_values` WRITE;
/*!40000 ALTER TABLE `attribute_values` DISABLE KEYS */;
INSERT INTO `attribute_values` VALUES (1,1,'8GB',NULL,'8gb',NULL,1,'2026-11-08 07:33:34','2026-11-08 07:33:34'),(2,1,'12GB',NULL,'12gb',NULL,2,'2026-11-08 07:33:34','2026-11-08 07:33:34'),(3,1,'16GB',NULL,'16gb',NULL,3,'2026-11-08 07:33:34','2026-11-08 07:33:34'),(4,1,'24GB',NULL,'24gb',NULL,4,'2026-11-08 07:33:34','2026-11-08 07:33:34'),(5,1,'32GB',NULL,'32gb',NULL,5,'2026-11-08 07:33:34','2026-11-08 07:33:34'),(6,2,'128GB',NULL,'128gb',NULL,1,'2026-11-08 07:33:34','2026-11-08 07:33:34'),(7,2,'256GB',NULL,'256gb',NULL,2,'2026-11-08 07:33:34','2026-11-08 07:33:34'),(8,2,'512GB',NULL,'512gb',NULL,3,'2026-11-08 07:33:34','2026-11-08 07:33:34'),(9,2,'1TB',NULL,'1tb',NULL,4,'2026-11-08 07:33:34','2026-11-08 07:33:34'),(10,3,'Awesome Graphite',NULL,'awesome-graphite','#3b3b3f',1,'2026-11-08 07:33:34','2026-11-08 07:33:34'),(11,3,'Awesome Lightgray',NULL,'awesome-lightgray','#d7d7d9',2,'2026-11-08 07:33:34','2026-11-08 07:33:34'),(12,3,'Cloud Grey',NULL,'cloud-grey','#9aa0a6',3,'2026-11-08 07:33:34','2026-11-08 07:33:34'),(13,3,'Abyss Blue',NULL,'abyss-blue','#2b3a55',4,'2026-11-08 07:33:34','2026-11-08 07:33:34');
/*!40000 ALTER TABLE `attribute_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attributes`
--

DROP TABLE IF EXISTS `attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attributes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `name_bn` varchar(100) DEFAULT NULL,
  `slug` varchar(120) NOT NULL,
  `display_type` varchar(20) NOT NULL DEFAULT 'button',
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attributes_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attributes`
--

LOCK TABLES `attributes` WRITE;
/*!40000 ALTER TABLE `attributes` DISABLE KEYS */;
INSERT INTO `attributes` VALUES (1,'RAM','র‍্যাম','ram','button',0,'2026-11-08 07:33:34','2026-11-09 00:57:27'),(2,'Storage','স্টোরেজ','storage','button',0,'2026-11-08 07:33:34','2026-11-09 00:57:27'),(3,'Color','রং','color','swatch',0,'2026-11-08 07:33:34','2026-11-09 00:57:27');
/*!40000 ALTER TABLE `attributes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banners`
--

DROP TABLE IF EXISTS `banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banners` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `placement` varchar(30) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `title_bn` varchar(150) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `subtitle_bn` varchar(255) DEFAULT NULL,
  `cta_label` varchar(50) DEFAULT NULL,
  `cta_label_bn` varchar(50) DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `open_in_new_tab` tinyint(1) NOT NULL DEFAULT 0,
  `alt_text` varchar(255) DEFAULT NULL,
  `alt_text_bn` varchar(255) DEFAULT NULL,
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `banners_placement_is_active_sort_order_index` (`placement`,`is_active`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banners`
--

LOCK TABLES `banners` WRITE;
/*!40000 ALTER TABLE `banners` DISABLE KEYS */;
INSERT INTO `banners` VALUES (1,'hero_main','Smartphone mega sale — save up to 25%','স্মার্টফোন মেগা সেল — ২৫% পর্যন্ত সাশ্রয়',NULL,NULL,NULL,NULL,'/category/smart-phone',0,'Smartphone mega sale — save up to 25%','স্মার্টফোন মেগা সেল — ২৫% পর্যন্ত সাশ্রয়',NULL,NULL,1,1,'2026-11-08 07:33:38','2026-11-09 00:57:28'),(5,'hero_main','Smart living essentials','স্মার্ট জীবনের প্রয়োজনীয় সব',NULL,NULL,NULL,NULL,'/trending',0,'Smart living essentials','স্মার্ট জীবনের প্রয়োজনীয় সব',NULL,NULL,1,5,'2026-11-08 07:33:38','2026-11-09 00:57:28'),(6,'hero_side_top','Power bank offers: power for every day','পাওয়ার ব্যাংক অফার: প্রতিদিনের পাওয়ার',NULL,NULL,NULL,NULL,'/category/powerbanks',0,'Power bank offers: power for every day','পাওয়ার ব্যাংক অফার: প্রতিদিনের পাওয়ার',NULL,NULL,1,6,'2026-11-08 07:33:38','2026-11-09 00:57:28'),(7,'hero_side_bottom','Combo deals: your everyday tech in one bundle','কম্বো অফার: প্রতিদিনের টেক এক বান্ডেলে',NULL,NULL,NULL,NULL,'/combos',0,'Combo deals: your everyday tech in one bundle','কম্বো অফার: প্রতিদিনের টেক এক বান্ডেলে',NULL,NULL,1,7,'2026-11-08 07:33:38','2026-11-09 00:57:28'),(8,'promo','Message us for product, stock, price and delivery info','পণ্য, স্টক, দাম ও ডেলিভারির তথ্যের জন্য আমাদের মেসেজ করুন',NULL,NULL,NULL,NULL,'#',0,'Message us for product, stock, price and delivery info','পণ্য, স্টক, দাম ও ডেলিভারির তথ্যের জন্য আমাদের মেসেজ করুন',NULL,NULL,1,8,'2026-11-08 07:33:38','2026-11-09 00:57:28');
/*!40000 ALTER TABLE `banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brands` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brands_slug_unique` (`slug`),
  KEY `brands_is_active_is_featured_sort_order_index` (`is_active`,`is_featured`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brands`
--

LOCK TABLES `brands` WRITE;
/*!40000 ALTER TABLE `brands` DISABLE KEYS */;
INSERT INTO `brands` VALUES (1,'Apple','apple',NULL,'https://www.apple.com',1,1,1,NULL,NULL,'2026-11-08 07:33:34','2026-11-08 07:33:34',NULL),(2,'Samsung','samsung',NULL,'https://www.samsung.com',1,1,2,NULL,NULL,'2026-11-08 07:33:34','2026-11-08 07:33:34',NULL),(3,'Google','google',NULL,'https://store.google.com',1,1,3,NULL,NULL,'2026-11-08 07:33:34','2026-11-08 07:33:34',NULL),(4,'HONOR','honor',NULL,'https://www.honor.com',1,1,4,NULL,NULL,'2026-11-08 07:33:34','2026-11-08 07:33:34',NULL),(5,'Xiaomi','xiaomi',NULL,'https://www.mi.com',1,1,5,NULL,NULL,'2026-11-08 07:33:34','2026-11-08 07:33:34',NULL),(6,'Anker','anker',NULL,'https://www.anker.com',1,1,6,NULL,NULL,'2026-11-08 07:33:34','2026-11-08 07:33:34',NULL),(7,'JBL','jbl',NULL,'https://www.jbl.com',1,1,7,NULL,NULL,'2026-11-08 07:33:34','2026-11-08 07:33:34',NULL),(8,'Amazfit','amazfit',NULL,'https://www.amazfit.com',1,1,8,NULL,NULL,'2026-11-08 07:33:34','2026-11-08 07:33:34',NULL),(9,'CMF by Nothing','cmf-by-nothing',NULL,'https://nothing.tech',1,1,9,NULL,NULL,'2026-11-08 07:33:34','2026-11-08 07:33:34',NULL),(10,'KOSPET','kospet',NULL,NULL,1,1,10,NULL,NULL,'2026-11-08 07:33:34','2026-11-08 07:33:34',NULL),(11,'Haylou','haylou',NULL,NULL,1,1,11,NULL,NULL,'2026-11-08 07:33:34','2026-11-08 07:33:34',NULL),(12,'Kieslect','kieslect',NULL,NULL,1,1,12,NULL,NULL,'2026-11-08 07:33:34','2026-11-08 07:33:34',NULL),(13,'SoundPEATS','soundpeats',NULL,NULL,1,0,13,NULL,NULL,'2026-11-08 07:33:34','2026-11-08 07:33:34',NULL),(14,'QCY','qcy',NULL,NULL,1,0,14,NULL,NULL,'2026-11-08 07:33:34','2026-11-08 07:33:34',NULL),(15,'Redragon','redragon',NULL,'https://www.redragonzone.com',1,0,15,NULL,NULL,'2026-11-08 07:33:34','2026-11-08 07:33:34',NULL);
/*!40000 ALTER TABLE `brands` ENABLE KEYS */;
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
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
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
  `expiration` bigint(20) NOT NULL,
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
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `product_variant_id` bigint(20) unsigned NOT NULL,
  `quantity` smallint(5) unsigned NOT NULL,
  `price_when_added` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cart_items_cart_id_product_variant_id_unique` (`cart_id`,`product_variant_id`),
  KEY `cart_items_product_id_foreign` (`product_id`),
  KEY `cart_items_product_variant_id_foreign` (`product_variant_id`),
  CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_items`
--

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `token_hash` char(64) DEFAULT NULL,
  `coupon_id` bigint(20) unsigned DEFAULT NULL,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `carts_user_id_unique` (`user_id`),
  UNIQUE KEY `carts_token_hash_unique` (`token_hash`),
  KEY `carts_coupon_id_foreign` (`coupon_id`),
  KEY `carts_last_activity_at_index` (`last_activity_at`),
  CONSTRAINT `carts_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL,
  CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carts`
--

LOCK TABLES `carts` WRITE;
/*!40000 ALTER TABLE `carts` DISABLE KEYS */;
/*!40000 ALTER TABLE `carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `name_bn` varchar(150) DEFAULT NULL,
  `slug` varchar(191) NOT NULL,
  `path` varchar(255) NOT NULL DEFAULT '/',
  `depth` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `description_bn` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `show_in_menu` tinyint(1) NOT NULL DEFAULT 1,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_title_bn` varchar(255) DEFAULT NULL,
  `seo_description` varchar(500) DEFAULT NULL,
  `seo_description_bn` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_parent_id_sort_order_index` (`parent_id`,`sort_order`),
  KEY `categories_is_active_is_featured_sort_order_index` (`is_active`,`is_featured`,`sort_order`),
  KEY `categories_path_index` (`path`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,NULL,'Smart Watch','স্মার্ট ওয়াচ','smart-watch','/1/',0,NULL,NULL,'smartwatch',1,1,1,1,NULL,NULL,NULL,NULL,'2026-11-08 07:33:34','2026-11-09 00:57:27',NULL),(2,NULL,'Headphones','হেডফোন','headphones','/2/',0,NULL,NULL,'headphone',2,1,1,1,NULL,NULL,NULL,NULL,'2026-11-08 07:33:34','2026-11-09 00:57:27',NULL),(3,2,'Earbuds','ইয়ারবাডস','earbuds','/2/3/',1,NULL,NULL,'earbuds',1,1,1,1,NULL,NULL,NULL,NULL,'2026-11-08 07:33:34','2026-11-09 00:57:27',NULL),(4,2,'Over-Ear Headphones','ওভার-ইয়ার হেডফোন','over-ear-headphones','/2/4/',1,NULL,NULL,'headphone',2,1,0,1,NULL,NULL,NULL,NULL,'2026-11-08 07:33:34','2026-11-09 00:57:27',NULL),(5,NULL,'Speaker','স্পিকার','speaker','/5/',0,NULL,NULL,'speaker',3,1,1,1,NULL,NULL,NULL,NULL,'2026-11-08 07:33:34','2026-11-09 00:57:27',NULL),(6,NULL,'Powerbanks','পাওয়ার ব্যাংক','powerbanks','/6/',0,NULL,NULL,'powerbank',4,1,1,1,NULL,NULL,NULL,NULL,'2026-11-08 07:33:34','2026-11-09 00:57:27',NULL),(7,NULL,'Charger & Cable','চার্জার ও ক্যাবল','charger-cable','/7/',0,NULL,NULL,'charger',5,1,1,1,NULL,NULL,NULL,NULL,'2026-11-08 07:33:34','2026-11-09 00:57:27',NULL),(8,NULL,'Smart Phone','স্মার্টফোন','smart-phone','/8/',0,NULL,NULL,'smartphone',6,1,1,1,NULL,NULL,NULL,NULL,'2026-11-08 07:33:34','2026-11-09 00:57:27',NULL),(9,NULL,'sourov',NULL,'sourov','/9/',0,NULL,NULL,NULL,2,1,1,1,NULL,NULL,NULL,NULL,'2026-11-09 00:57:06','2026-11-09 00:58:23','2026-11-09 00:58:23');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `category_coupon`
--

DROP TABLE IF EXISTS `category_coupon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category_coupon` (
  `category_id` bigint(20) unsigned NOT NULL,
  `coupon_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`category_id`,`coupon_id`),
  KEY `category_coupon_coupon_id_index` (`coupon_id`),
  CONSTRAINT `category_coupon_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `category_coupon_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category_coupon`
--

LOCK TABLES `category_coupon` WRITE;
/*!40000 ALTER TABLE `category_coupon` DISABLE KEYS */;
/*!40000 ALTER TABLE `category_coupon` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `category_product`
--

DROP TABLE IF EXISTS `category_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category_product` (
  `category_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`category_id`,`product_id`),
  KEY `category_product_product_id_index` (`product_id`),
  CONSTRAINT `category_product_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `category_product_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category_product`
--

LOCK TABLES `category_product` WRITE;
/*!40000 ALTER TABLE `category_product` DISABLE KEYS */;
INSERT INTO `category_product` VALUES (1,5),(1,11),(1,12),(1,13),(1,14),(1,15),(1,44),(2,1),(2,2),(2,3),(2,16),(2,17),(2,18),(2,19),(2,20),(3,16),(3,17),(3,18),(3,19),(3,20),(4,1),(4,2),(4,3),(5,4),(5,47),(5,48),(5,49),(5,50),(5,51),(5,52),(5,53),(5,54),(5,55),(6,21),(6,22),(6,23),(6,24),(6,25),(6,26),(6,27),(6,28),(6,29),(6,30),(7,31),(7,32),(7,33),(7,34),(7,35),(7,36),(7,37),(7,38),(7,39),(7,40),(7,43),(7,45),(8,6),(8,7),(8,8),(8,9),(8,10),(8,41),(8,42),(8,46);
/*!40000 ALTER TABLE `category_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `category_specification`
--

DROP TABLE IF EXISTS `category_specification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category_specification` (
  `category_id` bigint(20) unsigned NOT NULL,
  `specification_id` bigint(20) unsigned NOT NULL,
  `is_filterable` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`category_id`,`specification_id`),
  KEY `category_specification_specification_id_index` (`specification_id`),
  CONSTRAINT `category_specification_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `category_specification_specification_id_foreign` FOREIGN KEY (`specification_id`) REFERENCES `specifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category_specification`
--

LOCK TABLES `category_specification` WRITE;
/*!40000 ALTER TABLE `category_specification` DISABLE KEYS */;
INSERT INTO `category_specification` VALUES (1,1,1,1),(1,4,1,2),(1,27,1,3),(1,33,1,4),(1,47,1,5),(3,27,1,3),(3,29,1,1),(3,30,1,2),(3,33,1,4),(3,37,1,5),(3,47,1,6),(4,29,1,1),(4,30,1,2),(4,31,1,3),(4,37,1,4),(4,47,1,5),(5,27,1,3),(5,29,1,1),(5,32,1,2),(5,33,1,4),(5,37,1,5),(5,39,1,6),(5,47,1,7),(6,28,1,4),(6,29,1,5),(6,32,1,2),(6,33,1,1),(6,34,1,3),(6,37,1,6),(6,39,1,7),(6,47,1,8),(7,28,1,3),(7,29,1,4),(7,32,1,1),(7,34,1,2),(7,47,1,5),(8,1,1,1),(8,2,1,2),(8,3,1,3),(8,4,1,4),(8,6,1,5),(8,7,1,6),(8,11,1,7),(8,13,1,8),(8,33,1,11),(8,34,1,12),(8,35,1,9),(8,36,1,10),(8,47,1,13);
/*!40000 ALTER TABLE `category_specification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `combo_items`
--

DROP TABLE IF EXISTS `combo_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `combo_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `combo_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` smallint(5) unsigned NOT NULL DEFAULT 1,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `combo_items_combo_id_product_id_unique` (`combo_id`,`product_id`),
  KEY `combo_items_product_id_foreign` (`product_id`),
  CONSTRAINT `combo_items_combo_id_foreign` FOREIGN KEY (`combo_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `combo_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `combo_items`
--

LOCK TABLES `combo_items` WRITE;
/*!40000 ALTER TABLE `combo_items` DISABLE KEYS */;
INSERT INTO `combo_items` VALUES (1,41,8,1,1,'2026-11-08 07:33:37','2026-11-08 07:33:37'),(2,41,16,1,2,'2026-11-08 07:33:37','2026-11-08 07:33:37'),(3,41,37,1,3,'2026-11-08 07:33:37','2026-11-08 07:33:37'),(4,42,7,1,1,'2026-11-08 07:33:37','2026-11-08 07:33:37'),(5,42,22,1,2,'2026-11-08 07:33:37','2026-11-08 07:33:37'),(6,42,31,1,3,'2026-11-08 07:33:37','2026-11-08 07:33:37'),(7,43,21,1,1,'2026-11-08 07:33:37','2026-11-08 07:33:37'),(8,43,36,1,2,'2026-11-08 07:33:37','2026-11-08 07:33:37'),(9,43,34,1,3,'2026-11-08 07:33:37','2026-11-08 07:33:37'),(10,43,33,1,4,'2026-11-08 07:33:37','2026-11-08 07:33:37'),(11,44,12,1,1,'2026-11-08 07:33:37','2026-11-08 07:33:37'),(12,44,17,1,2,'2026-11-08 07:33:37','2026-11-08 07:33:37'),(13,45,39,1,1,'2026-11-08 07:33:37','2026-11-08 07:33:37'),(14,45,38,1,2,'2026-11-08 07:33:37','2026-11-08 07:33:37'),(15,45,31,1,3,'2026-11-08 07:33:37','2026-11-08 07:33:37');
/*!40000 ALTER TABLE `combo_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupon_product`
--

DROP TABLE IF EXISTS `coupon_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupon_product` (
  `coupon_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`coupon_id`,`product_id`),
  KEY `coupon_product_product_id_index` (`product_id`),
  CONSTRAINT `coupon_product_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_product_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupon_product`
--

LOCK TABLES `coupon_product` WRITE;
/*!40000 ALTER TABLE `coupon_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupon_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupon_usages`
--

DROP TABLE IF EXISTS `coupon_usages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupon_usages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `coupon_id` bigint(20) unsigned NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `discount_amount` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupon_usages_order_id_unique` (`order_id`),
  KEY `coupon_usages_user_id_foreign` (`user_id`),
  KEY `coupon_usages_coupon_id_user_id_index` (`coupon_id`,`user_id`),
  KEY `coupon_usages_coupon_id_customer_phone_index` (`coupon_id`,`customer_phone`),
  CONSTRAINT `coupon_usages_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`),
  CONSTRAINT `coupon_usages_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_usages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupon_usages`
--

LOCK TABLES `coupon_usages` WRITE;
/*!40000 ALTER TABLE `coupon_usages` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupon_usages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `type` varchar(20) NOT NULL,
  `value` decimal(12,2) NOT NULL,
  `min_subtotal` decimal(12,2) DEFAULT NULL,
  `max_discount` decimal(12,2) DEFAULT NULL,
  `usage_limit` int(10) unsigned DEFAULT NULL,
  `usage_limit_per_customer` int(10) unsigned DEFAULT NULL,
  `used_count` int(10) unsigned NOT NULL DEFAULT 0,
  `scope` varchar(20) NOT NULL DEFAULT 'all',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupons_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `districts`
--

DROP TABLE IF EXISTS `districts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `districts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `name_bn` varchar(100) DEFAULT NULL,
  `division` varchar(50) NOT NULL,
  `shipping_zone_id` bigint(20) unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `districts_name_unique` (`name`),
  KEY `districts_shipping_zone_id_foreign` (`shipping_zone_id`),
  CONSTRAINT `districts_shipping_zone_id_foreign` FOREIGN KEY (`shipping_zone_id`) REFERENCES `shipping_zones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `districts`
--

LOCK TABLES `districts` WRITE;
/*!40000 ALTER TABLE `districts` DISABLE KEYS */;
INSERT INTO `districts` VALUES (1,'Dhaka','ঢাকা','Dhaka',1,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(2,'Faridpur','ফরিদপুর','Dhaka',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(3,'Gazipur','গাজীপুর','Dhaka',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(4,'Gopalganj','গোপালগঞ্জ','Dhaka',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(5,'Kishoreganj','কিশোরগঞ্জ','Dhaka',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(6,'Madaripur','মাদারীপুর','Dhaka',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(7,'Manikganj','মানিকগঞ্জ','Dhaka',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(8,'Munshiganj','মুন্সিগঞ্জ','Dhaka',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(9,'Narayanganj','নারায়ণগঞ্জ','Dhaka',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(10,'Narsingdi','নরসিংদী','Dhaka',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(11,'Rajbari','রাজবাড়ী','Dhaka',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(12,'Shariatpur','শরীয়তপুর','Dhaka',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(13,'Tangail','টাঙ্গাইল','Dhaka',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(14,'Bandarban','বান্দরবান','Chattogram',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(15,'Brahmanbaria','ব্রাহ্মণবাড়িয়া','Chattogram',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(16,'Chandpur','চাঁদপুর','Chattogram',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(17,'Chattogram','চট্টগ্রাম','Chattogram',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(18,'Cox\'s Bazar','কক্সবাজার','Chattogram',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(19,'Cumilla','কুমিল্লা','Chattogram',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(20,'Feni','ফেনী','Chattogram',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(21,'Khagrachhari','খাগড়াছড়ি','Chattogram',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(22,'Lakshmipur','লক্ষ্মীপুর','Chattogram',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(23,'Noakhali','নোয়াখালী','Chattogram',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(24,'Rangamati','রাঙ্গামাটি','Chattogram',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(25,'Bogura','বগুড়া','Rajshahi',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(26,'Chapainawabganj','চাঁপাইনবাবগঞ্জ','Rajshahi',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(27,'Joypurhat','জয়পুরহাট','Rajshahi',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(28,'Naogaon','নওগাঁ','Rajshahi',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(29,'Natore','নাটোর','Rajshahi',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(30,'Pabna','পাবনা','Rajshahi',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(31,'Rajshahi','রাজশাহী','Rajshahi',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(32,'Sirajganj','সিরাজগঞ্জ','Rajshahi',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(33,'Bagerhat','বাগেরহাট','Khulna',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(34,'Chuadanga','চুয়াডাঙ্গা','Khulna',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(35,'Jashore','যশোর','Khulna',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(36,'Jhenaidah','ঝিনাইদহ','Khulna',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(37,'Khulna','খুলনা','Khulna',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(38,'Kushtia','কুষ্টিয়া','Khulna',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(39,'Magura','মাগুরা','Khulna',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(40,'Meherpur','মেহেরপুর','Khulna',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(41,'Narail','নড়াইল','Khulna',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(42,'Satkhira','সাতক্ষীরা','Khulna',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(43,'Barguna','বরগুনা','Barishal',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(44,'Barishal','বরিশাল','Barishal',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(45,'Bhola','ভোলা','Barishal',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(46,'Jhalokati','ঝালকাঠি','Barishal',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(47,'Patuakhali','পটুয়াখালী','Barishal',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(48,'Pirojpur','পিরোজপুর','Barishal',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(49,'Habiganj','হবিগঞ্জ','Sylhet',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(50,'Moulvibazar','মৌলভীবাজার','Sylhet',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(51,'Sunamganj','সুনামগঞ্জ','Sylhet',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(52,'Sylhet','সিলেট','Sylhet',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(53,'Dinajpur','দিনাজপুর','Rangpur',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(54,'Gaibandha','গাইবান্ধা','Rangpur',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(55,'Kurigram','কুড়িগ্রাম','Rangpur',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(56,'Lalmonirhat','লালমনিরহাট','Rangpur',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(57,'Nilphamari','নীলফামারী','Rangpur',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(58,'Panchagarh','পঞ্চগড়','Rangpur',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(59,'Rangpur','রংপুর','Rangpur',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(60,'Thakurgaon','ঠাকুরগাঁও','Rangpur',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(61,'Jamalpur','জামালপুর','Mymensingh',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(62,'Mymensingh','ময়মনসিংহ','Mymensingh',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(63,'Netrokona','নেত্রকোনা','Mymensingh',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(64,'Sherpur','শেরপুর','Mymensingh',2,1,'2026-11-08 07:33:33','2026-11-08 07:33:33');
/*!40000 ALTER TABLE `districts` ENABLE KEYS */;
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
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
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
-- Table structure for table `flash_sale_products`
--

DROP TABLE IF EXISTS `flash_sale_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flash_sale_products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `flash_sale_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `product_variant_id` bigint(20) unsigned DEFAULT NULL,
  `discount_type` varchar(20) NOT NULL,
  `discount_value` decimal(12,2) NOT NULL,
  `quantity_limit` int(10) unsigned DEFAULT NULL,
  `sold_quantity` int(10) unsigned NOT NULL DEFAULT 0,
  `per_order_limit` smallint(5) unsigned DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `flash_sale_product_unique` (`flash_sale_id`,`product_id`,`product_variant_id`),
  KEY `flash_sale_products_product_variant_id_foreign` (`product_variant_id`),
  KEY `flash_sale_products_product_id_index` (`product_id`),
  CONSTRAINT `flash_sale_products_flash_sale_id_foreign` FOREIGN KEY (`flash_sale_id`) REFERENCES `flash_sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `flash_sale_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `flash_sale_products_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flash_sale_products`
--

LOCK TABLES `flash_sale_products` WRITE;
/*!40000 ALTER TABLE `flash_sale_products` DISABLE KEYS */;
INSERT INTO `flash_sale_products` VALUES (1,1,1,NULL,'fixed_price',2949.00,10,2,2,1,'2026-11-08 07:33:37','2026-11-08 08:50:00'),(2,1,2,NULL,'fixed_price',999.00,10,2,2,2,'2026-11-08 07:33:37','2026-11-08 08:38:57'),(3,1,3,NULL,'fixed_price',1620.00,10,0,2,3,'2026-11-08 07:33:37','2026-11-08 07:33:37'),(4,1,13,NULL,'fixed_price',6490.00,10,0,2,4,'2026-11-08 08:15:38','2026-11-08 08:15:38'),(5,1,14,NULL,'fixed_price',4590.00,10,0,2,5,'2026-11-08 08:15:38','2026-11-08 08:15:38'),(6,1,18,NULL,'fixed_price',2890.00,10,0,2,6,'2026-11-08 08:15:38','2026-11-08 08:15:38'),(7,1,19,NULL,'fixed_price',2790.00,10,0,2,7,'2026-11-08 08:15:38','2026-11-08 08:15:38'),(8,1,20,NULL,'fixed_price',2190.00,10,0,2,8,'2026-11-08 08:15:38','2026-11-08 08:15:38'),(9,1,22,NULL,'fixed_price',3290.00,10,0,2,9,'2026-11-08 08:15:38','2026-11-08 08:15:38'),(10,1,25,NULL,'fixed_price',1990.00,10,0,2,10,'2026-11-08 08:15:38','2026-11-08 08:15:38'),(11,1,36,NULL,'fixed_price',1050.00,10,0,2,11,'2026-11-08 08:15:38','2026-11-08 08:15:38'),(12,1,49,NULL,'fixed_price',1990.00,10,0,2,12,'2026-11-08 08:15:38','2026-11-08 08:15:38');
/*!40000 ALTER TABLE `flash_sale_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flash_sales`
--

DROP TABLE IF EXISTS `flash_sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flash_sales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `title_bn` varchar(150) DEFAULT NULL,
  `slug` varchar(191) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `description_bn` varchar(500) DEFAULT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `flash_sales_slug_unique` (`slug`),
  KEY `flash_sales_is_active_starts_at_ends_at_index` (`is_active`,`starts_at`,`ends_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flash_sales`
--

LOCK TABLES `flash_sales` WRITE;
/*!40000 ALTER TABLE `flash_sales` DISABLE KEYS */;
INSERT INTO `flash_sales` VALUES (1,'Weekly Flash Sale','সাপ্তাহিক ফ্ল্যাশ সেল','weekly-flash-sale','Limited time deals','সীমিত সময়ের অফার','2026-11-08 13:00:00','2026-11-15 13:00:00',1,0,'2026-11-08 07:33:37','2026-11-09 00:57:28');
/*!40000 ALTER TABLE `flash_sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `home_section_items`
--

DROP TABLE IF EXISTS `home_section_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `home_section_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `home_section_id` bigint(20) unsigned NOT NULL,
  `itemable_type` varchar(255) NOT NULL,
  `itemable_id` bigint(20) unsigned NOT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `home_section_items_itemable_type_itemable_id_index` (`itemable_type`,`itemable_id`),
  KEY `home_section_items_home_section_id_sort_order_index` (`home_section_id`,`sort_order`),
  CONSTRAINT `home_section_items_home_section_id_foreign` FOREIGN KEY (`home_section_id`) REFERENCES `home_sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `home_section_items`
--

LOCK TABLES `home_section_items` WRITE;
/*!40000 ALTER TABLE `home_section_items` DISABLE KEYS */;
INSERT INTO `home_section_items` VALUES (1,18,'product',22,4),(2,18,'product',23,1),(3,18,'product',28,2),(4,18,'product',30,3),(5,18,'product',34,7),(6,18,'product',36,5),(7,18,'product',38,6),(8,18,'product',39,8),(9,21,'product',1,8),(10,21,'product',4,1),(11,21,'product',5,2),(12,21,'product',6,3),(13,21,'product',7,6),(14,21,'product',11,5),(15,21,'product',16,4),(16,21,'product',25,7);
/*!40000 ALTER TABLE `home_section_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `home_sections`
--

DROP TABLE IF EXISTS `home_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `home_sections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(30) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `title_bn` varchar(150) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `subtitle_bn` varchar(255) DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `home_sections_is_active_sort_order_index` (`is_active`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `home_sections`
--

LOCK TABLES `home_sections` WRITE;
/*!40000 ALTER TABLE `home_sections` DISABLE KEYS */;
INSERT INTO `home_sections` VALUES (10,'hero',NULL,NULL,NULL,NULL,'[]',1,1,'2026-11-08 07:33:37','2026-11-08 07:33:37'),(11,'service_highlights',NULL,NULL,NULL,NULL,'[]',0,2,'2026-11-08 07:33:37','2026-11-08 07:33:37'),(12,'featured_categories','Featured Categories','জনপ্রিয় ক্যাটাগরি','Explore our wide range of gadgets','আমাদের বিশাল গ্যাজেট সংগ্রহ দেখুন','{\"limit\":16}',1,3,'2026-11-08 07:33:37','2026-11-09 00:57:28'),(13,'flash_sale','Flash Sale','ফ্ল্যাশ সেল','Limited time deals','সীমিত সময়ের অফার','{\"limit\":12}',1,4,'2026-11-08 07:33:37','2026-11-09 00:57:28'),(14,'product_collection','Trending Now','এখন ট্রেন্ডিং','The gadgets everyone is talking about this week','এই সপ্তাহে সবাই যে গ্যাজেটগুলো নিয়ে কথা বলছে','{\"source\":\"trending\",\"limit\":10}',1,5,'2026-11-08 07:33:37','2026-11-09 00:57:28'),(15,'product_collection','Mobile Phones','মোবাইল ফোন','Latest flagship and everyday smartphones from top brands','সেরা ব্র্যান্ডের নতুন ফ্ল্যাগশিপ ও দৈনন্দিন স্মার্টফোন','{\"source\":\"category\",\"category_id\":8,\"limit\":10}',1,6,'2026-11-08 07:33:37','2026-11-09 00:57:28'),(16,'product_collection','Smart Watches','স্মার্ট ওয়াচ','Track your fitness and stay connected on the go','ফিটনেস ট্র্যাক করুন, চলার পথেও থাকুন কানেক্টেড','{\"source\":\"category\",\"category_id\":1,\"limit\":10}',1,7,'2026-11-08 07:33:37','2026-11-09 00:57:28'),(17,'product_collection','Earbuds','ইয়ারবাডস','True wireless sound for music, calls and gaming','গান, কল ও গেমিংয়ের জন্য ট্রু ওয়্যারলেস সাউন্ড','{\"source\":\"category\",\"category_id\":3,\"limit\":10}',1,8,'2026-11-08 07:33:37','2026-11-09 00:57:28'),(18,'showcase','Stay charged on the go with power banks and chargers from ৳199','৳১৯৯ থেকে পাওয়ার ব্যাংক ও চার্জার, চলার পথেও থাকুন ফুল চার্জে',NULL,NULL,'{\"theme\":\"midnight\",\"source\":\"manual\",\"cta_label\":\"Shop all\",\"view_all_url\":\"\\/category\\/powerbanks\",\"limit\":8,\"cta_label_bn\":\"\\u09b8\\u09ac \\u09a6\\u09c7\\u0996\\u09c1\\u09a8\"}',1,9,'2026-11-08 07:33:37','2026-11-09 00:57:28'),(19,'product_collection','Combo Deals','কম্বো অফার','Bundle up and save more on gadgets that go together','একসাথে মানানসই গ্যাজেট বান্ডেলে কিনে আরও সাশ্রয় করুন','{\"source\":\"combos\",\"limit\":10}',1,10,'2026-11-08 07:33:38','2026-11-09 00:57:28'),(20,'product_collection','Featured Products','বিশেষ পণ্য','Discover our handpicked selection of premium tech products','বাছাই করা প্রিমিয়াম টেক পণ্যগুলো দেখুন','{\"source\":\"featured\",\"limit\":12}',1,11,'2026-11-08 07:33:38','2026-11-09 00:57:28'),(21,'showcase','New and Noteworthy','নতুন ও উল্লেখযোগ্য','Explore the latest finds from innovative tech and trending product releases.','উদ্ভাবনী প্রযুক্তি আর ট্রেন্ডিং নতুন পণ্যের সর্বশেষ সংগ্রহ দেখুন।','{\"theme\":\"aurora\",\"source\":\"manual\",\"cta_label\":\"Shop now\",\"view_all_url\":\"\\/trending\",\"limit\":8,\"cta_label_bn\":\"\\u098f\\u0996\\u09a8\\u0987 \\u0995\\u09bf\\u09a8\\u09c1\\u09a8\"}',1,12,'2026-11-08 07:33:38','2026-11-09 00:57:28'),(22,'product_collection','Latest Products','নতুন পণ্য','Check out our newest arrivals','আমাদের একদম নতুন পণ্যগুলো দেখুন','{\"source\":\"latest\",\"limit\":12}',1,13,'2026-11-08 07:33:38','2026-11-09 00:57:28'),(23,'promo_banners',NULL,NULL,NULL,NULL,'[]',1,14,'2026-11-08 07:33:38','2026-11-08 07:33:38'),(24,'product_collection','Best Sellers','সর্বাধিক বিক্রিত','Top-rated products loved by our customers','গ্রাহকদের প্রিয় সেরা রেটিংয়ের পণ্য','{\"source\":\"best_sellers\",\"limit\":16}',1,15,'2026-11-08 07:33:38','2026-11-09 00:57:28'),(25,'product_grid','All Product','সব পণ্য','Browse our complete collection','আমাদের পুরো সংগ্রহ দেখুন','{\"source\":\"latest\",\"limit\":60,\"initial_count\":5}',1,16,'2026-11-08 07:33:38','2026-11-09 00:57:28');
/*!40000 ALTER TABLE `home_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_transactions`
--

DROP TABLE IF EXISTS `inventory_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `product_variant_id` bigint(20) unsigned NOT NULL,
  `type` varchar(30) NOT NULL,
  `quantity` int(11) NOT NULL COMMENT 'signed change',
  `quantity_before` int(10) unsigned NOT NULL,
  `quantity_after` int(10) unsigned NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `performed_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `inventory_transactions_reference_type_reference_id_index` (`reference_type`,`reference_id`),
  KEY `inventory_transactions_performed_by_foreign` (`performed_by`),
  KEY `inventory_transactions_product_variant_id_created_at_index` (`product_variant_id`,`created_at`),
  KEY `inventory_transactions_product_id_created_at_index` (`product_id`,`created_at`),
  CONSTRAINT `inventory_transactions_performed_by_foreign` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_transactions_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `inventory_transactions_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_transactions`
--

LOCK TABLES `inventory_transactions` WRITE;
/*!40000 ALTER TABLE `inventory_transactions` DISABLE KEYS */;
INSERT INTO `inventory_transactions` VALUES (1,1,1,'stock_in',25,0,25,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:34'),(2,2,2,'stock_in',40,0,40,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:34'),(3,3,3,'stock_in',18,0,18,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:34'),(4,4,4,'stock_in',3,0,3,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:34'),(5,5,5,'stock_in',15,0,15,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:34'),(6,6,6,'stock_in',4,0,4,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:34'),(7,7,7,'stock_in',5,0,5,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:34'),(8,8,8,'stock_in',6,0,6,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:35'),(9,9,9,'stock_in',3,0,3,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:35'),(10,10,10,'stock_in',12,0,12,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:35'),(11,11,11,'stock_in',4,0,4,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:35'),(12,12,12,'stock_in',7,0,7,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:35'),(13,13,13,'stock_in',10,0,10,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:35'),(14,14,14,'stock_in',14,0,14,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:35'),(15,15,15,'stock_in',16,0,16,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:35'),(16,16,16,'stock_in',8,0,8,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:35'),(17,17,17,'stock_in',20,0,20,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:35'),(18,18,18,'stock_in',18,0,18,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:35'),(19,19,19,'stock_in',22,0,22,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:35'),(20,20,20,'stock_in',25,0,25,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:35'),(21,21,21,'stock_in',30,0,30,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:35'),(22,22,22,'stock_in',15,0,15,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:35'),(23,23,23,'stock_in',8,0,8,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:35'),(24,24,24,'stock_in',18,0,18,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:35'),(25,25,25,'stock_in',20,0,20,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:36'),(26,26,26,'stock_in',35,0,35,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:36'),(27,27,27,'stock_in',25,0,25,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:36'),(28,28,28,'stock_in',6,0,6,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:36'),(29,29,29,'stock_in',10,0,10,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:36'),(30,30,30,'stock_in',16,0,16,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:36'),(31,31,31,'stock_in',60,0,60,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:36'),(32,32,32,'stock_in',50,0,50,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:36'),(33,33,33,'stock_in',80,0,80,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:36'),(34,34,34,'stock_in',30,0,30,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:36'),(35,35,35,'stock_in',80,0,80,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:36'),(36,36,36,'stock_in',35,0,35,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:36'),(37,37,37,'stock_in',45,0,45,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:36'),(38,38,38,'stock_in',20,0,20,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:36'),(39,39,39,'stock_in',15,0,15,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:37'),(40,40,40,'stock_in',60,0,60,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:37'),(41,41,41,'stock_in',3,0,3,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:37'),(42,42,42,'stock_in',3,0,3,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:37'),(43,43,43,'stock_in',15,0,15,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:37'),(44,44,44,'stock_in',6,0,6,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:37'),(45,45,45,'stock_in',10,0,10,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:37'),(46,46,46,'stock_in',8,0,8,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:37'),(47,46,47,'stock_in',5,0,5,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:37'),(48,46,48,'stock_in',3,0,3,'Opening stock',NULL,NULL,1,'2026-11-08 07:33:37'),(49,47,50,'stock_in',30,0,30,'Opening stock',NULL,NULL,1,'2026-11-08 07:52:57'),(50,48,51,'stock_in',15,0,15,'Opening stock',NULL,NULL,1,'2026-11-08 07:52:57'),(51,49,52,'stock_in',25,0,25,'Opening stock',NULL,NULL,1,'2026-11-08 07:52:57'),(52,50,53,'stock_in',12,0,12,'Opening stock',NULL,NULL,1,'2026-11-08 07:52:57'),(53,51,54,'stock_in',10,0,10,'Opening stock',NULL,NULL,1,'2026-11-08 07:52:57'),(54,52,55,'stock_in',18,0,18,'Opening stock',NULL,NULL,1,'2026-11-08 07:52:57'),(55,53,56,'stock_in',8,0,8,'Opening stock',NULL,NULL,1,'2026-11-08 07:52:57'),(56,54,57,'stock_in',14,0,14,'Opening stock',NULL,NULL,1,'2026-11-08 07:52:57'),(57,55,58,'stock_in',6,0,6,'Opening stock',NULL,NULL,1,'2026-11-08 07:52:57'),(58,2,2,'order',-1,40,39,NULL,'order',1,1,'2026-11-08 08:15:19'),(59,21,21,'order',-1,30,29,NULL,'order',2,1,'2026-11-08 08:27:34'),(60,2,2,'order',-1,39,38,NULL,'order',3,1,'2026-11-08 08:38:57'),(61,1,1,'order',-1,25,24,NULL,'order',4,1,'2026-11-08 08:44:37'),(62,1,1,'order',-1,24,23,NULL,'order',5,1,'2026-11-08 08:50:00');
/*!40000 ALTER TABLE `inventory_transactions` ENABLE KEYS */;
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
  `attempts` smallint(5) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
INSERT INTO `jobs` VALUES (80,'default','{\"uuid\":\"b4da9fbc-4a2a-46e2-a057-0a5937ec9139\",\"displayName\":\"Spatie\\\\MediaLibrary\\\\Conversions\\\\Jobs\\\\PerformConversionsJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":true,\"data\":{\"commandName\":\"Spatie\\\\MediaLibrary\\\\Conversions\\\\Jobs\\\\PerformConversionsJob\",\"command\":\"O:58:\\\"Spatie\\\\MediaLibrary\\\\Conversions\\\\Jobs\\\\PerformConversionsJob\\\":6:{s:14:\\\"\\u0000*\\u0000conversions\\\";O:52:\\\"Spatie\\\\MediaLibrary\\\\Conversions\\\\ConversionCollection\\\":2:{s:8:\\\"\\u0000*\\u0000items\\\";a:2:{i:0;O:42:\\\"Spatie\\\\MediaLibrary\\\\Conversions\\\\Conversion\\\":12:{s:12:\\\"\\u0000*\\u0000fileNamer\\\";O:54:\\\"Spatie\\\\MediaLibrary\\\\Support\\\\FileNamer\\\\DefaultFileNamer\\\":0:{}s:28:\\\"\\u0000*\\u0000extractVideoFrameAtSecond\\\";d:0;s:16:\\\"\\u0000*\\u0000manipulations\\\";O:45:\\\"Spatie\\\\MediaLibrary\\\\Conversions\\\\Manipulations\\\":1:{s:16:\\\"\\u0000*\\u0000manipulations\\\";a:4:{s:8:\\\"optimize\\\";a:1:{i:0;O:36:\\\"Spatie\\\\ImageOptimizer\\\\OptimizerChain\\\":4:{s:13:\\\"\\u0000*\\u0000optimizers\\\";a:7:{i:0;O:42:\\\"Spatie\\\\ImageOptimizer\\\\Optimizers\\\\Jpegoptim\\\":5:{s:7:\\\"options\\\";a:4:{i:0;s:4:\\\"-m85\\\";i:1;s:7:\\\"--force\\\";i:2;s:11:\\\"--strip-all\\\";i:3;s:17:\\\"--all-progressive\\\";}s:9:\\\"imagePath\\\";s:0:\\\"\\\";s:10:\\\"binaryPath\\\";s:0:\\\"\\\";s:7:\\\"tmpPath\\\";N;s:10:\\\"binaryName\\\";s:9:\\\"jpegoptim\\\";}i:1;O:41:\\\"Spatie\\\\ImageOptimizer\\\\Optimizers\\\\Pngquant\\\":5:{s:7:\\\"options\\\";a:1:{i:0;s:7:\\\"--force\\\";}s:9:\\\"imagePath\\\";s:0:\\\"\\\";s:10:\\\"binaryPath\\\";s:0:\\\"\\\";s:7:\\\"tmpPath\\\";N;s:10:\\\"binaryName\\\";s:8:\\\"pngquant\\\";}i:2;O:40:\\\"Spatie\\\\ImageOptimizer\\\\Optimizers\\\\Optipng\\\":5:{s:7:\\\"options\\\";a:3:{i:0;s:3:\\\"-i0\\\";i:1;s:3:\\\"-o2\\\";i:2;s:6:\\\"-quiet\\\";}s:9:\\\"imagePath\\\";s:0:\\\"\\\";s:10:\\\"binaryPath\\\";s:0:\\\"\\\";s:7:\\\"tmpPath\\\";N;s:10:\\\"binaryName\\\";s:7:\\\"optipng\\\";}i:3;O:37:\\\"Spatie\\\\ImageOptimizer\\\\Optimizers\\\\Svgo\\\":5:{s:7:\\\"options\\\";a:1:{i:0;s:20:\\\"--disable=cleanupIDs\\\";}s:9:\\\"imagePath\\\";s:0:\\\"\\\";s:10:\\\"binaryPath\\\";s:0:\\\"\\\";s:7:\\\"tmpPath\\\";N;s:10:\\\"binaryName\\\";s:4:\\\"svgo\\\";}i:4;O:41:\\\"Spatie\\\\ImageOptimizer\\\\Optimizers\\\\Gifsicle\\\":5:{s:7:\\\"options\\\";a:2:{i:0;s:2:\\\"-b\\\";i:1;s:3:\\\"-O3\\\";}s:9:\\\"imagePath\\\";s:0:\\\"\\\";s:10:\\\"binaryPath\\\";s:0:\\\"\\\";s:7:\\\"tmpPath\\\";N;s:10:\\\"binaryName\\\";s:8:\\\"gifsicle\\\";}i:5;O:38:\\\"Spatie\\\\ImageOptimizer\\\\Optimizers\\\\Cwebp\\\":5:{s:7:\\\"options\\\";a:4:{i:0;s:4:\\\"-m 6\\\";i:1;s:8:\\\"-pass 10\\\";i:2;s:3:\\\"-mt\\\";i:3;s:5:\\\"-q 90\\\";}s:9:\\\"imagePath\\\";s:0:\\\"\\\";s:10:\\\"binaryPath\\\";s:0:\\\"\\\";s:7:\\\"tmpPath\\\";N;s:10:\\\"binaryName\\\";s:5:\\\"cwebp\\\";}i:6;O:40:\\\"Spatie\\\\ImageOptimizer\\\\Optimizers\\\\Avifenc\\\":6:{s:7:\\\"options\\\";a:8:{i:0;s:14:\\\"-a cq-level=23\\\";i:1;s:6:\\\"-j all\\\";i:2;s:7:\\\"--min 0\\\";i:3;s:8:\\\"--max 63\\\";i:4;s:12:\\\"--minalpha 0\\\";i:5;s:13:\\\"--maxalpha 63\\\";i:6;s:14:\\\"-a end-usage=q\\\";i:7;s:12:\\\"-a tune=ssim\\\";}s:9:\\\"imagePath\\\";s:0:\\\"\\\";s:10:\\\"binaryPath\\\";s:0:\\\"\\\";s:7:\\\"tmpPath\\\";N;s:10:\\\"binaryName\\\";s:7:\\\"avifenc\\\";s:16:\\\"decodeBinaryName\\\";s:7:\\\"avifdec\\\";}}s:9:\\\"\\u0000*\\u0000logger\\\";O:33:\\\"Spatie\\\\ImageOptimizer\\\\DummyLogger\\\":0:{}s:10:\\\"\\u0000*\\u0000timeout\\\";i:60;s:15:\\\"\\u0000*\\u0000errorHandler\\\";N;}}s:6:\\\"format\\\";a:1:{i:0;s:4:\\\"webp\\\";}s:5:\\\"width\\\";a:1:{i:0;i:480;}s:7:\\\"quality\\\";a:1:{i:0;i:82;}}}s:23:\\\"\\u0000*\\u0000performOnCollections\\\";a:1:{i:0;s:6:\\\"poster\\\";}s:17:\\\"\\u0000*\\u0000performOnQueue\\\";b:1;s:18:\\\"\\u0000*\\u0000performDeferred\\\";b:0;s:26:\\\"\\u0000*\\u0000keepOriginalImageFormat\\\";b:0;s:27:\\\"\\u0000*\\u0000generateResponsiveImages\\\";b:0;s:18:\\\"\\u0000*\\u0000widthCalculator\\\";N;s:24:\\\"\\u0000*\\u0000loadingAttributeValue\\\";N;s:16:\\\"\\u0000*\\u0000pdfPageNumber\\\";i:1;s:7:\\\"\\u0000*\\u0000name\\\";s:4:\\\"card\\\";}i:1;O:42:\\\"Spatie\\\\MediaLibrary\\\\Conversions\\\\Conversion\\\":12:{s:12:\\\"\\u0000*\\u0000fileNamer\\\";O:54:\\\"Spatie\\\\MediaLibrary\\\\Support\\\\FileNamer\\\\DefaultFileNamer\\\":0:{}s:28:\\\"\\u0000*\\u0000extractVideoFrameAtSecond\\\";d:0;s:16:\\\"\\u0000*\\u0000manipulations\\\";O:45:\\\"Spatie\\\\MediaLibrary\\\\Conversions\\\\Manipulations\\\":1:{s:16:\\\"\\u0000*\\u0000manipulations\\\";a:4:{s:8:\\\"optimize\\\";a:1:{i:0;O:36:\\\"Spatie\\\\ImageOptimizer\\\\OptimizerChain\\\":4:{s:13:\\\"\\u0000*\\u0000optimizers\\\";a:7:{i:0;O:42:\\\"Spatie\\\\ImageOptimizer\\\\Optimizers\\\\Jpegoptim\\\":5:{s:7:\\\"options\\\";a:4:{i:0;s:4:\\\"-m85\\\";i:1;s:7:\\\"--force\\\";i:2;s:11:\\\"--strip-all\\\";i:3;s:17:\\\"--all-progressive\\\";}s:9:\\\"imagePath\\\";s:0:\\\"\\\";s:10:\\\"binaryPath\\\";s:0:\\\"\\\";s:7:\\\"tmpPath\\\";N;s:10:\\\"binaryName\\\";s:9:\\\"jpegoptim\\\";}i:1;O:41:\\\"Spatie\\\\ImageOptimizer\\\\Optimizers\\\\Pngquant\\\":5:{s:7:\\\"options\\\";a:1:{i:0;s:7:\\\"--force\\\";}s:9:\\\"imagePath\\\";s:0:\\\"\\\";s:10:\\\"binaryPath\\\";s:0:\\\"\\\";s:7:\\\"tmpPath\\\";N;s:10:\\\"binaryName\\\";s:8:\\\"pngquant\\\";}i:2;O:40:\\\"Spatie\\\\ImageOptimizer\\\\Optimizers\\\\Optipng\\\":5:{s:7:\\\"options\\\";a:3:{i:0;s:3:\\\"-i0\\\";i:1;s:3:\\\"-o2\\\";i:2;s:6:\\\"-quiet\\\";}s:9:\\\"imagePath\\\";s:0:\\\"\\\";s:10:\\\"binaryPath\\\";s:0:\\\"\\\";s:7:\\\"tmpPath\\\";N;s:10:\\\"binaryName\\\";s:7:\\\"optipng\\\";}i:3;O:37:\\\"Spatie\\\\ImageOptimizer\\\\Optimizers\\\\Svgo\\\":5:{s:7:\\\"options\\\";a:1:{i:0;s:20:\\\"--disable=cleanupIDs\\\";}s:9:\\\"imagePath\\\";s:0:\\\"\\\";s:10:\\\"binaryPath\\\";s:0:\\\"\\\";s:7:\\\"tmpPath\\\";N;s:10:\\\"binaryName\\\";s:4:\\\"svgo\\\";}i:4;O:41:\\\"Spatie\\\\ImageOptimizer\\\\Optimizers\\\\Gifsicle\\\":5:{s:7:\\\"options\\\";a:2:{i:0;s:2:\\\"-b\\\";i:1;s:3:\\\"-O3\\\";}s:9:\\\"imagePath\\\";s:0:\\\"\\\";s:10:\\\"binaryPath\\\";s:0:\\\"\\\";s:7:\\\"tmpPath\\\";N;s:10:\\\"binaryName\\\";s:8:\\\"gifsicle\\\";}i:5;O:38:\\\"Spatie\\\\ImageOptimizer\\\\Optimizers\\\\Cwebp\\\":5:{s:7:\\\"options\\\";a:4:{i:0;s:4:\\\"-m 6\\\";i:1;s:8:\\\"-pass 10\\\";i:2;s:3:\\\"-mt\\\";i:3;s:5:\\\"-q 90\\\";}s:9:\\\"imagePath\\\";s:0:\\\"\\\";s:10:\\\"binaryPath\\\";s:0:\\\"\\\";s:7:\\\"tmpPath\\\";N;s:10:\\\"binaryName\\\";s:5:\\\"cwebp\\\";}i:6;O:40:\\\"Spatie\\\\ImageOptimizer\\\\Optimizers\\\\Avifenc\\\":6:{s:7:\\\"options\\\";a:8:{i:0;s:14:\\\"-a cq-level=23\\\";i:1;s:6:\\\"-j all\\\";i:2;s:7:\\\"--min 0\\\";i:3;s:8:\\\"--max 63\\\";i:4;s:12:\\\"--minalpha 0\\\";i:5;s:13:\\\"--maxalpha 63\\\";i:6;s:14:\\\"-a end-usage=q\\\";i:7;s:12:\\\"-a tune=ssim\\\";}s:9:\\\"imagePath\\\";s:0:\\\"\\\";s:10:\\\"binaryPath\\\";s:0:\\\"\\\";s:7:\\\"tmpPath\\\";N;s:10:\\\"binaryName\\\";s:7:\\\"avifenc\\\";s:16:\\\"decodeBinaryName\\\";s:7:\\\"avifdec\\\";}}s:9:\\\"\\u0000*\\u0000logger\\\";O:33:\\\"Spatie\\\\ImageOptimizer\\\\DummyLogger\\\":0:{}s:10:\\\"\\u0000*\\u0000timeout\\\";i:60;s:15:\\\"\\u0000*\\u0000errorHandler\\\";N;}}s:6:\\\"format\\\";a:1:{i:0;s:4:\\\"webp\\\";}s:5:\\\"width\\\";a:1:{i:0;i:900;}s:7:\\\"quality\\\";a:1:{i:0;i:82;}}}s:23:\\\"\\u0000*\\u0000performOnCollections\\\";a:1:{i:0;s:6:\\\"poster\\\";}s:17:\\\"\\u0000*\\u0000performOnQueue\\\";b:1;s:18:\\\"\\u0000*\\u0000performDeferred\\\";b:0;s:26:\\\"\\u0000*\\u0000keepOriginalImageFormat\\\";b:0;s:27:\\\"\\u0000*\\u0000generateResponsiveImages\\\";b:0;s:18:\\\"\\u0000*\\u0000widthCalculator\\\";N;s:24:\\\"\\u0000*\\u0000loadingAttributeValue\\\";N;s:16:\\\"\\u0000*\\u0000pdfPageNumber\\\";i:1;s:7:\\\"\\u0000*\\u0000name\\\";s:5:\\\"large\\\";}}s:28:\\\"\\u0000*\\u0000escapeWhenCastingToString\\\";b:0;}s:8:\\\"\\u0000*\\u0000media\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Media\\\";s:2:\\\"id\\\";i:80;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:14:\\\"\\u0000*\\u0000onlyMissing\\\";b:0;s:10:\\\"connection\\\";s:8:\\\"database\\\";s:5:\\\"queue\\\";s:0:\\\"\\\";s:11:\\\"afterCommit\\\";b:1;}\",\"batchId\":null},\"createdAt\":1794147068,\"delay\":null}',0,NULL,1794147068,1794147068);
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ledger_entries`
--

DROP TABLE IF EXISTS `ledger_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ledger_entries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(10) NOT NULL,
  `category` varchar(30) NOT NULL,
  `title` varchar(150) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `entry_date` date NOT NULL,
  `account` varchar(20) NOT NULL DEFAULT 'cash',
  `reference` varchar(100) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `recorded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ledger_entries_recorded_by_foreign` (`recorded_by`),
  KEY `ledger_entries_type_entry_date_index` (`type`,`entry_date`),
  KEY `ledger_entries_category_entry_date_index` (`category`,`entry_date`),
  CONSTRAINT `ledger_entries_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ledger_entries`
--

LOCK TABLES `ledger_entries` WRITE;
/*!40000 ALTER TABLE `ledger_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `ledger_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media`
--

DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  `uuid` char(36) DEFAULT NULL,
  `collection_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `disk` varchar(255) NOT NULL,
  `conversions_disk` varchar(255) DEFAULT NULL,
  `size` bigint(20) unsigned NOT NULL,
  `manipulations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`manipulations`)),
  `custom_properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`custom_properties`)),
  `generated_conversions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`generated_conversions`)),
  `responsive_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`responsive_images`)),
  `order_column` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_uuid_unique` (`uuid`),
  KEY `media_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `media_order_column_index` (`order_column`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media`
--

LOCK TABLES `media` WRITE;
/*!40000 ALTER TABLE `media` DISABLE KEYS */;
INSERT INTO `media` VALUES (3,'home_section',18,'1442154c-72ca-4b9a-ab90-6e188e17ede1','images','power-bank-graphite-laptop-30000mah','power-bank-graphite-laptop-30000mah.webp','image/webp','public','public',62790,'[]','[]','{\"card\":true}','[]',1,'2026-11-08 07:33:38','2026-11-08 07:59:25'),(4,'home_section',18,'bcafcae2-ee65-41e1-9093-2213f58828e3','images','charger-cable-wall-charger-dual-port-35w','charger-cable-wall-charger-dual-port-35w.webp','image/webp','public','public',46320,'[]','[]','{\"card\":true}','[]',2,'2026-11-08 07:33:38','2026-11-08 07:59:26'),(5,'banner',1,'691a4e94-d1ae-4bee-8275-45da4f75fc31','desktop','slide-1','slide-1.webp','image/webp','public','public',90016,'[]','[]','{\"desktop\":true}','{\"desktop\":{\"urls\":[\"slide-1___desktop_1920_1081.webp\",\"slide-1___desktop_1606_904.webp\",\"slide-1___desktop_1344_757.webp\",\"slide-1___desktop_1124_633.webp\",\"slide-1___desktop_940_529.webp\",\"slide-1___desktop_787_443.webp\",\"slide-1___desktop_658_370.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTkyMCAxMDgxIj4KCTxpbWFnZSB3aWR0aD0iMTkyMCIgaGVpZ2h0PSIxMDgxIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FFZ0FnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE4dXM5UHM1YmNPOGdEVmJqdExWUDlXK1RXTkRiSHlkM200OXMxZHMvbFZpRGtpdXlLY2RXYzExTFJHM1lXbHZLejcrZ3JRdVBDbHZjV25teFBpc0NDN1BsdHRPQ0szTFBYbGowbDBrWWJxMW03NnNjRXJNOCtCT09wcTNhazRQSm9vckNXdzRsdTIvaXFyZHNSR2NFMFVVNWZDaW9uLy9aIj4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 07:33:38','2026-11-08 07:59:32'),(9,'banner',5,'d018d776-1667-4f97-85c9-dc41ca25663a','desktop','slide-5','slide-5.webp','image/webp','public','public',118884,'[]','[]','{\"desktop\":true}','{\"desktop\":{\"urls\":[\"slide-5___desktop_1920_1081.webp\",\"slide-5___desktop_1606_904.webp\",\"slide-5___desktop_1344_757.webp\",\"slide-5___desktop_1124_633.webp\",\"slide-5___desktop_940_529.webp\",\"slide-5___desktop_787_443.webp\",\"slide-5___desktop_658_370.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTkyMCAxMDgxIj4KCTxpbWFnZSB3aWR0aD0iMTkyMCIgaGVpZ2h0PSIxMDgxIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FFZ0FnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE0elpiM2tHNXBRRFZjNmJGSCs4aWJjUjZVbGhwc2M4UVZKL25QYk5kem8vaGRWMGlWblpXa0k0NXJ2bFUwdWNFYWNuS3h4OXBjL3Z0c2g0NlZyV2s4Vm5JV0F5YWtzdkNyL2FtZWM4QnVLM3BOSHNZU0gzREpGYzNOSkoyNm5TNmFhc2VaYVV4RjJNRTE2TG8waitWamUyUHJSUldqRkUyVSs2YXd0UkozbmswVVV5ai85az0iPgoJPC9pbWFnZT4KPC9zdmc+\"}}',1,'2026-11-08 07:33:38','2026-11-08 08:00:02'),(12,'banner',8,'d4dcfd8f-6d18-453b-9fc9-b5142a7616c3','desktop','messenger-support','messenger-support.webp','image/webp','public','public',49336,'[]','[]','{\"desktop\":true}','{\"desktop\":{\"urls\":[\"messenger-support___desktop_1920_724.webp\",\"messenger-support___desktop_1606_606.webp\",\"messenger-support___desktop_1343_506.webp\",\"messenger-support___desktop_1124_424.webp\",\"messenger-support___desktop_940_354.webp\",\"messenger-support___desktop_787_297.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTkyMCA3MjQiPgoJPGltYWdlIHdpZHRoPSIxOTIwIiBoZWlnaHQ9IjcyNCIgeGxpbms6aHJlZj0iZGF0YTppbWFnZS9qcGVnO2Jhc2U2NCwvOWovNEFBUVNrWkpSZ0FCQVFFQVlBQmdBQUQvL2dBK1ExSkZRVlJQVWpvZ1oyUXRhbkJsWnlCMk1TNHdJQ2gxYzJsdVp5QkpTa2NnU2xCRlJ5QjJPREFwTENCa1pXWmhkV3gwSUhGMVlXeHBkSGtLLzlzQVF3QUlCZ1lIQmdVSUJ3Y0hDUWtJQ2d3VURRd0xDd3daRWhNUEZCMGFIeDRkR2h3Y0lDUXVKeUFpTENNY0hDZzNLU3d3TVRRME5COG5PVDA0TWp3dU16UXkvOXNBUXdFSkNRa01Dd3dZRFEwWU1pRWNJVEl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeS84QUFFUWdBREFBZ0F3RWlBQUlSQVFNUkFmL0VBQjhBQUFFRkFRRUJBUUVCQUFBQUFBQUFBQUFCQWdNRUJRWUhDQWtLQy8vRUFMVVFBQUlCQXdNQ0JBTUZCUVFFQUFBQmZRRUNBd0FFRVFVU0lURkJCaE5SWVFjaWNSUXlnWkdoQ0NOQ3NjRVZVdEh3SkROaWNvSUpDaFlYR0JrYUpTWW5LQ2txTkRVMk56ZzVPa05FUlVaSFNFbEtVMVJWVmxkWVdWcGpaR1ZtWjJocGFuTjBkWFozZUhsNmc0U0Zob2VJaVlxU2s1U1ZscGVZbVpxaW82U2xwcWVvcWFxeXM3UzF0cmU0dWJyQ3c4VEZ4c2ZJeWNyUzA5VFYxdGZZMmRyaDR1UGs1ZWJuNk9ucThmTHo5UFgyOS9qNSt2L0VBQjhCQUFNQkFRRUJBUUVCQVFFQUFBQUFBQUFCQWdNRUJRWUhDQWtLQy8vRUFMVVJBQUlCQWdRRUF3UUhCUVFFQUFFQ2R3QUJBZ01SQkFVaE1RWVNRVkVIWVhFVElqS0JDQlJDa2FHeHdRa2pNMUx3RldKeTBRb1dKRFRoSmZFWEdCa2FKaWNvS1NvMU5qYzRPVHBEUkVWR1IwaEpTbE5VVlZaWFdGbGFZMlJsWm1kb2FXcHpkSFYyZDNoNWVvS0RoSVdHaDRpSmlwS1RsSldXbDVpWm1xS2pwS1dtcDZpcHFyS3p0TFcydDdpNXVzTER4TVhHeDhqSnl0TFQxTlhXMTlqWjJ1TGo1T1htNStqcDZ2THo5UFgyOS9qNSt2L2FBQXdEQVFBQ0VRTVJBRDhBOVh1ZFFsamxJRGdDcmRqY1RTOHR5UFd1YTFaQjlxNm44NjZYU0ZDMkM0OUs3S2xKUmdwR01aWGRpcHJOOU5aMms4Z0h5cXB4WHkvNGkxRTZsck54SkwxM0hGZlQvaVJRMmtUZzlDcHI1ZzhTMjBVRjdJWXhnbGpUb3kxMEtuNW4vOWs9Ij4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 07:33:38','2026-11-08 08:00:15'),(13,'product',1,'9dc38c55-cfac-46d2-ae83-73b72f5a09b2','main','redragon-headphone-headsets-h320-lamia','redragon-headphone-headsets-h320-lamia.webp','image/webp','public','public',13502,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"redragon-headphone-headsets-h320-lamia___large_1400_1400.webp\",\"redragon-headphone-headsets-h320-lamia___large_1171_1171.webp\",\"redragon-headphone-headsets-h320-lamia___large_980_980.webp\",\"redragon-headphone-headsets-h320-lamia___large_819_819.webp\",\"redragon-headphone-headsets-h320-lamia___large_686_686.webp\",\"redragon-headphone-headsets-h320-lamia___large_573_573.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5OGxjUm9XSndCWEtYUGl0VGZHMnQxM3NEeml0anhESThla1R0R2ZtMm11SzhFUjJvbmxtdXBGTXpNZUdOQUhYV1d1TExKNWN3MlA3MXRxd1lBaXVIOFYzZHJhRkpvSFh6TTlBYTZUUUx4cnpUSTVHNjRvQW8rTTdtUzMwR1pveGs0cjVzbTFyVmJiVmZNaWxkVjNjZ0d2cWpVdE9UVUxjeFA4QWROY1RjZkMvVDVwekpnYzBBZU56YTNmWDg2TTh6RSs1cjNyd0ROUExvY2ZuQTVBcm1MYjRYd3JxVy9QN3RUMHIwclRyQ0xUN1ZJSWhnS01VQmMvLzJRPT0iPgoJPC9pbWFnZT4KPC9zdmc+\"}}',1,'2026-11-08 07:33:38','2026-11-08 08:00:23'),(14,'product',2,'1369de70-a9cf-4c5c-a150-e72612e20572','main','redragon-headphone-headsets-h130','redragon-headphone-headsets-h130.webp','image/webp','public','public',4680,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"redragon-headphone-headsets-h130___large_1400_1400.webp\",\"redragon-headphone-headsets-h130___large_1171_1171.webp\",\"redragon-headphone-headsets-h130___large_980_980.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5L3JOMVRXTGZTb3c5d3dWYTBqWGxQeFB1OHpRd21UYWdPVHpRQjZKcHVzMnVwcHVna0JyU3J5SDRlNnZCTnFaZ2lmZ0RHTTE2Nk9sQUFlbGVLL0UvUnRTdkw4TkNqc2g5SzlycUNhMWluR0pFRGZVVUFlUGZETHdWZmFkZS9iYm5jb1BPRFhzNDRGUnhRcEVvVlZBSHRVdEFILy8yUT09Ij4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 07:33:38','2026-11-08 08:00:28'),(15,'product',3,'21e0d91d-612d-41be-8d0d-7526a83a4041','main','redragon-headphone-headsets-h231-scream','redragon-headphone-headsets-h231-scream.webp','image/webp','public','public',6902,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"redragon-headphone-headsets-h231-scream___large_1400_1400.webp\",\"redragon-headphone-headsets-h231-scream___large_1171_1171.webp\",\"redragon-headphone-headsets-h231-scream___large_979_979.webp\",\"redragon-headphone-headsets-h231-scream___large_819_819.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5L3FPV1pZbDNNY0NwS3d0YWtrTHBFaHhrODBBalVpdkk1VGhUVmdHdWZLZlpmS0t0a25yVzdDMjZNSDJwWEtrdExvZWE1ZnhLczI5V2lCL0N1b05RdGJwSjk4QS9XbVNjenA0bGtDYjhsdmV1cGhHSXdENlZISGF4eHRsVkFxY0NnRC8yUT09Ij4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 07:33:38','2026-11-08 08:00:35'),(16,'product',4,'c03b899a-70a2-41fe-a8cf-e21ecf5506c3','main','jbl-bluetooth-speaker-partybox-720','jbl-bluetooth-speaker-partybox-720.jpg','image/jpeg','public','public',3250,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"jbl-bluetooth-speaker-partybox-720___large_1400_1400.webp\",\"jbl-bluetooth-speaker-partybox-720___large_1171_1171.webp\",\"jbl-bluetooth-speaker-partybox-720___large_980_980.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5L1BTdWIxM3hmWTZGS0k3aHdDYTZNOUs4SitMOExQcVVlR0l6VFN2b1ZGWGRqMWZSZkZkaHJUYmJlUUZ2U3VnRmVDZkNWWkUxZGxZbjhhOTZIU2lTczdEbkJ3ZG1CNlY0bjhYY0M5aWJCNDcxN2FhNS94QjRXczlmakMzQ0FrZDZUOGhRbHl5VFBLL2hTc2o2dXovQU1OZTVqcFhQNkI0VXN0QlgvUjBHZld1aEZKWDZsMXFudEpjeC8vWiI+Cgk8L2ltYWdlPgo8L3N2Zz4=\"}}',1,'2026-11-08 07:33:38','2026-11-08 08:00:40'),(17,'product',5,'07089892-213f-4892-8206-b17d3178b5bf','main','kospet-smart-watch-tank-t3-ultra-2','kospet-smart-watch-tank-t3-ultra-2.jpg','image/jpeg','public','public',9020,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"kospet-smart-watch-tank-t3-ultra-2___large_1400_1400.webp\",\"kospet-smart-watch-tank-t3-ultra-2___large_1171_1171.webp\",\"kospet-smart-watch-tank-t3-ultra-2___large_979_979.webp\",\"kospet-smart-watch-tank-t3-ultra-2___large_819_819.webp\",\"kospet-smart-watch-tank-t3-ultra-2___large_685_685.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5K0pxamU2cmJXSXpOSUIrTlNhak9iYXlsbEhWVkpyNTU4UWVLNzY3MW1SV2tPd05qYlNic1ZHTjJlNzJuaWZUcnlmeVk1MTMrbWEyUVFSa1Y4dkhVcnEwMXkxbmhkaHVJeU0xOUlhSE85enBVRWovQUhpb3pRbmNUVm5ZdDNVSXVMZDQyNk1NVjRMNHY4UEpwT3RtWm9tTWJObklGZlFOWitvYVBaNmttMjVoVi9xS2RnVHNmUFVGbC9hbXUycTJzYmJRUm5pdm9mU29EYmFkREVlcXFCVk94OE1hYnA4dm1RUUtHOWNWc2dZRkFqLy8yUT09Ij4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 07:33:38','2026-11-08 08:00:46'),(18,'product',6,'dfe8ba51-fffb-446a-a6fc-50097adcadc5','main','apple-mobile-iphone-17-pro','apple-mobile-iphone-17-pro.webp','image/webp','public','public',9054,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"apple-mobile-iphone-17-pro___large_1400_1400.webp\",\"apple-mobile-iphone-17-pro___large_1171_1171.webp\",\"apple-mobile-iphone-17-pro___large_979_979.webp\",\"apple-mobile-iphone-17-pro___large_819_819.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5bDhSNm0rbWFlMHlEa1Z3OEhqdWFaR0JHQ0s3RHhYRXMybmxHNkd2T1cwNkZFSVVZcnk4YmlIQ2ZLajJNQmhZMUljMGpudGErS0dwMmVwR0tNNVhOZXVlQmRjbTF6UjB1SnZ2RVY4NmVKeEZCcXBYWm5tdmR2aFN3Ync4aEE0cnJ3ODNLS2JPUEZVNHdrMGpvZkZMYmJFMTUzTE5nSG12Uy9FRmpKZTJaU1ByWEN5ZUZyODV3SzgvSFVaenFYaWowOHRyMDRVclNaNHI0dnUyL3RRcUV6ejFyM2I0UlNHVHcyaE5jQnEvdzYxVzh2QzZ4Z2duclhxL2dIUVp0QzBkWUp2dlY2T0hpNHdTYVBLeFVsS28ybWYvMlE9PSI+Cgk8L2ltYWdlPgo8L3N2Zz4=\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:00:53'),(19,'product',7,'e1d2352f-b6c1-4123-ab4d-22657303582f','main','samsung-mobile-galaxy-s26-ultra','samsung-mobile-galaxy-s26-ultra.webp','image/webp','public','public',7344,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"samsung-mobile-galaxy-s26-ultra___large_1400_1400.webp\",\"samsung-mobile-galaxy-s26-ultra___large_1171_1171.webp\",\"samsung-mobile-galaxy-s26-ultra___large_979_979.webp\",\"samsung-mobile-galaxy-s26-ultra___large_819_819.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5dTFlNmF6MCtXWmVxak5lWDIvank4bnVKRUp3QTJLNy93QVdUK1hvOHFqcXlrVjRYRVBzOXkrVHl6VnJUVnhIb3orSzdvS3UwNUpyYTBIV3JpNnVBc2g0TmMvcFdtSmNXU1NFY2tWdjZUYXJCZUtCWFZVZEhrc2xxYnloN3R5MTR2ZmRZeWowV3ZCTGk4eGZFWjZOWDBOcmVtUzN0bk9pL2VaY0N2RzUvaHhxelhiT0J4dXpYTlRhVzVnZFRwV3N4d2FiRmtqT0syOUYxUmJ6VVZDbk5jekQ0TjFJV3lSa0hnVjB2aGZ3N2MyRjBIa0ZkdFNOQlU3cDZsdW8yckgvMlE9PSI+Cgk8L2ltYWdlPgo8L3N2Zz4=\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:00:59'),(20,'product',8,'1db7f79c-fdd6-4e9c-adea-bbbef05047b4','main','apple-mobile-iphone-17','apple-mobile-iphone-17.webp','image/webp','public','public',6058,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"apple-mobile-iphone-17___large_1400_1400.webp\",\"apple-mobile-iphone-17___large_1171_1171.webp\",\"apple-mobile-iphone-17___large_979_979.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5aThTYXBKcGRpWll4azFEcEdxUzM5Z3N6Y0VpdERWN09LOGc4dVVaV3FOdkJIWncrVkdNS0t1SzBLU0tOeE5mQzh5ci91NjI5S3VYbWpPODVJcml0VDhSeDI5OGJYK0luRmRUNGJjeVc1WTk2MG5UNVkzSk5TOE9Gck5mdldwZFJtUmVLb05heVk2Vm5GbEpuRTZwNGNXNXZ6ZGZ4QTExM2hxTm9yWW9lMU5lMmxJSTJWb2FYYlBDaDNER2FxZFJ5alpqazFZLy85az0iPgoJPC9pbWFnZT4KPC9zdmc+\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:01:04'),(21,'product',9,'eb2acbf0-d3b0-4555-a2d3-edbfaa97b1f6','main','google-mobile-pixel-9-pro','google-mobile-pixel-9-pro.webp','image/webp','public','public',7894,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"google-mobile-pixel-9-pro___large_1400_1400.webp\",\"google-mobile-pixel-9-pro___large_1171_1171.webp\",\"google-mobile-pixel-9-pro___large_980_980.webp\",\"google-mobile-pixel-9-pro___large_819_819.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5VThjYTNMb21rUFBGOTRWNDVGOFVkVnVKL0xqeVRtdlR2aXBqL2hIMnpYZ0drU3hRYWtydGdBR3FnazVKTVQyUFQ5TytKZDhKZkl1Y0svdlhxbmh6VTIxT3hXVnlDYStZZGN1bGJVbGx0enhudFh2bnd2a2VYUUVaeVNjVnRYakdMY1VpWTNlcEY4VnpqdzgxZk9DUCs5UFBldnF6eGo0ZmJ4QnBiV3luQk5lU1A4QUJ1K1FsbGV1ZEZubjF2TkVwMlNKdXowTmZRbnd3Yk9oTGdZRmVhcjhKOVVFZ09lQlhzSGd2UTVkRTBwWUpUODFhem56S3dyV1ovL1oiPgoJPC9pbWFnZT4KPC9zdmc+\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:01:12'),(22,'product',10,'a175e5d2-7647-48f5-b92b-f0df9eb2fcab','main','honor-mobile-x7d','honor-mobile-x7d.webp','image/webp','public','public',8902,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"honor-mobile-x7d___large_1400_1400.webp\",\"honor-mobile-x7d___large_1171_1171.webp\",\"honor-mobile-x7d___large_979_979.webp\",\"honor-mobile-x7d___large_819_819.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5ZzhSNncyazI0a1VacXRZYTIxN1pyTDB6VTNpT3dTL2pFYjlLcHdXRWRyYUNKT0FLMFNYTGNpL3ZXTGozNUdBSDVyUnNKMms0WTF6YXdCSmNrNXJlMHNrbXVhTW0yZWhYcFFoQk9JdXAvZUZaN3Q4dUsxTlFoa2tJS1Zudlp6c09sZGNiY3VwNXJ1bmRHTmZUU1EvT3ZOYkhoMjdhNVVsbHdSVUUybXl5cHRLMW82Ull0YWc1R0tweHBLbnB1UjdXdk9yNzJ4Ly85az0iPgoJPC9pbWFnZT4KPC9zdmc+\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:01:20'),(23,'product',11,'ba70f014-422c-4de1-adf8-07e51020d842','main','apple-smart-watch-series-11','apple-smart-watch-series-11.webp','image/webp','public','public',10756,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"apple-smart-watch-series-11___large_1400_1400.webp\",\"apple-smart-watch-series-11___large_1171_1171.webp\",\"apple-smart-watch-series-11___large_979_979.webp\",\"apple-smart-watch-series-11___large_819_819.webp\",\"apple-smart-watch-series-11___large_685_685.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5eTFEVWJmVHJkcFo1QXFnZHpYbWVyZkVyemI3N05aTXB5Y0E1cFBqRGN6Slp4eFJ5RlFldURYaWRtang2aERJSlNXM2p2VXVWbll1SzZucmtueEUxRFNyOUV1MStScTlSOFA2N0JybGlzOFRBNUhOZUhlTWRKbG0wdTF2TUhKVVYyL3dpTExwekl4T1I2MDBWSkp4dVZmak5henlXQ1N4SVNGNjRyd25URE5McTBDSE9kNDRyN0YxTFRMZlU3Wm9iaU1NcEhjVjVWcTN3dSt6Nm1MdXdVQUE1eFNhSWoyRytKN3FLMThPMmFPb1p0ZzRyZjhBaGpBd3NYbktiUXg0ckprOEhhbHE5eEVMczRqVEhGZWs2TnBjZWxXS1c4WUF3S2FLazBvMlAvL1oiPgoJPC9pbWFnZT4KPC9zdmc+\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:01:27'),(24,'product',12,'cd6fff6f-17e4-49b8-8085-5b85338482c3','main','amazfit-smart-watch-active-2-square','amazfit-smart-watch-active-2-square.webp','image/webp','public','public',13376,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"amazfit-smart-watch-active-2-square___large_1400_1400.webp\",\"amazfit-smart-watch-active-2-square___large_1171_1171.webp\",\"amazfit-smart-watch-active-2-square___large_979_979.webp\",\"amazfit-smart-watch-active-2-square___large_819_819.webp\",\"amazfit-smart-watch-active-2-square___large_685_685.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5bTEzeERaNkZiR2E2Y0tLOHgxWDR2RjVHV3hqTEFkNmkrTTEweXRGRnVPRDJyelRUNVk0YlhCUUV0M29BNlNiNHI2eEJlaDNYQ1pyMm53VjRsLzRTUFNsdUNNTmptdm5UeERZT2JPR1JZOFpHYzE2YjhGcmlZVzhrRGZkRkFNcWZHZXp1SG5qbFdObVFkU0JYa2NOd3htampBSU9SeFgxOXFta1d1cXdORmN4SzRJN2l2SlBFUHdsY1hadU5Qd0JuT0tCVzF1Y3ZyKzMrd2JTUGp6Q0JYYy9DSFRYaHRwSjI2R3VYYndEcjE3Y3d4em45Mm5GZXplRjlFWFJkTGpnQStiSE5Bei8yUT09Ij4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:01:35'),(25,'product',13,'5cad1af1-b511-4bfe-afa6-d76602673003','main','cmf-smart-watch-pro-2','cmf-smart-watch-pro-2.webp','image/webp','public','public',12680,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"cmf-smart-watch-pro-2___large_1400_1400.webp\",\"cmf-smart-watch-pro-2___large_1171_1171.webp\",\"cmf-smart-watch-pro-2___large_979_979.webp\",\"cmf-smart-watch-pro-2___large_819_819.webp\",\"cmf-smart-watch-pro-2___large_685_685.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5bzF2WExiUnJScHAzQXdPQlhsR3AvRmFXUzRaTGJBR2NDcTN4VjFlWnRRRnZ1SVN2TEhaUk11MXNrbW1CNkUzeFUxU3h2Rk1vekdUWHJuZzd4aGIrSkxNT3BBa3h5SytmZFIwa1Bvc2QwVHo3MTFYd2ZhU1BWbVRjZHZwUXhIUmZGVHd1MXl2MnVMZzE1UHBHamJ0VGppbGZKWnNWOVZhcnBrT3AyandTcUNDSzhmMVg0YmFoYWFsOXBzVzREWkZDQXErTjlLdDlQOEFEOXRieHVOK09jVW53cTB1NlRVak9GT3oxcXpMNE0xN1dybU1YamZJdkZlcCtHZkQwV2gyQ3hLQnV4eWFBUC9aIj4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:01:43'),(26,'product',14,'5fd86364-4325-4dc1-9d9c-59e5b32a5cc2','main','haylou-smart-watch-solar-pro','haylou-smart-watch-solar-pro.webp','image/webp','public','public',10928,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"haylou-smart-watch-solar-pro___large_1400_1400.webp\",\"haylou-smart-watch-solar-pro___large_1171_1171.webp\",\"haylou-smart-watch-solar-pro___large_980_980.webp\",\"haylou-smart-watch-solar-pro___large_819_819.webp\",\"haylou-smart-watch-solar-pro___large_686_686.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5dDFmVjdmU0xKN2lkd29VWjVyeGZXL2pLelhqUVdnK1VIR2FzL0dyVzVvQWxvakVJM1hGZUR5dXBtWGJ5U2FRN2FYUFo0UGlycUZuZVJOY1lNVEhyWHMzaDN4RGJhN1lKUEM0Skk1RmZMK29hZXg4UFEzTEFnNDRKcjBuNEpYTHY1a2JTRWdkQm1tSTF2akQ0WGsxT3lGMUN1V1hyaXZEZEg4UFNYV3N4VzdubmRnMTlqWHRuRmUyN3d5cUNyREhOZVE2MzhOTHExMWI3ZnByWXcyY1VBWlBqN1RiUFN2Q3R0WnFSNW0wVmUrREdrelE3N2tnaERVTjE0SDF6eERmeEcrYytXbU9LOWE4T2FGRG9XbXgyMFlHUU9UUUIvL1oiPgoJPC9pbWFnZT4KPC9zdmc+\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:01:49'),(27,'product',15,'a848e0f3-ba4c-4ed9-a37e-d11a55df8167','main','kieslect-smart-watch-ks3','kieslect-smart-watch-ks3.webp','image/webp','public','public',12304,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"kieslect-smart-watch-ks3___large_1400_1400.webp\",\"kieslect-smart-watch-ks3___large_1171_1171.webp\",\"kieslect-smart-watch-ks3___large_979_979.webp\",\"kieslect-smart-watch-ks3___large_819_819.webp\",\"kieslect-smart-watch-ks3___large_685_685.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5YzhVK0tJUEQxa1paQ04yT0JYa0Z4OFZyKysxRHlJQnRESEFyVCtNVndKTG1PRXk3UjlhOGkwMEZkWmkySGVOd3BFZTl6ZVI2bS94RTFuUWIySTN5Ym9INzE2MTRaOFMydmlHeFdlRmhramtWNC80KzA5cC9DMXBQc3dRbzdWYitEQ1hRa2NGajVZN1V5eHZ4aTBTN2x1VnVrVW1QRmNINEcwU2E4MStGU01nTnpYMDVydWl3NnpZUGJ5cURrY0d2S29QQTJyZUhkWGE2c1R1VFBBb0F2OEF4T2xndFBEc05wa2I4QVlwdndldEowdG5sWWZJZWxaOS93Q0Q5ZDhVYW9rbDh4V0lIcFhxdmg3UklkRTA2TzJqQTRISm9BLy8yUT09Ij4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:01:55'),(28,'product',16,'22d77f9a-b28b-4db7-a269-be8f45c61fd0','main','apple-earbuds-airpods-4','apple-earbuds-airpods-4.webp','image/webp','public','public',4054,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"apple-earbuds-airpods-4___large_1400_1400.webp\",\"apple-earbuds-airpods-4___large_1171_1171.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5OGtPRkpyTkdvRkovTEtrMW95bkNHdWZsdWZLdWlkdE1DK2RRTHorV0ZJNXJTUTVVR3VlaXVmTXVnZHRkQkVjb0tHQVMvZE5ZMXhiSThoSnJhY1pGVUpJWDNkS0VJcVc5c2lTQWl0bUw3b3FqSEMrN2tWZlFZQW9ZSC8vMlE9PSI+Cgk8L2ltYWdlPgo8L3N2Zz4=\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:02:00'),(29,'product',17,'f1b98141-52e1-4fc0-ab04-27138165ff8e','main','soundpeats-earbuds-life-anc','soundpeats-earbuds-life-anc.webp','image/webp','public','public',8102,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"soundpeats-earbuds-life-anc___large_1400_1400.webp\",\"soundpeats-earbuds-life-anc___large_1171_1171.webp\",\"soundpeats-earbuds-life-anc___large_979_979.webp\",\"soundpeats-earbuds-life-anc___large_819_819.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5OVpnb0pKd0s1M1dQR09uYVRrU3lxU1Blc2o0a2VKMzBEU0NZamgyNHJ3QjlhbjFpNEwzTXJFTWZXZ0QzSFUvSDYzRm9zMm5uZHp6WFMrRk5ZbjFXeEVrOFpWdmV2SGZEaytseHhRMnZuQXlGaGtHdmRkSWlnU3hqOGtMamFPbEFIUCtPL0NxZUl0TktrNEtqTmZPTjlwVDJHclBab0Q4cllyNjhrUU9oVTlDSzhuOFhmRDZhZS9hOXNRTnpISkZBSG4ybmFKYjJ5SmN1NUV2VUROZTRlQjVaNU5LVXk1eDJ6WEdlSFBBRjVKT3Ntb044aW43dGVxMlZuRlpXNnd4S0FxakZBSC8yUT09Ij4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:02:06'),(30,'product',18,'0c3c73c4-af47-4c71-bad3-b738956a3934','main','xiaomi-earbuds-redmi-buds-6','xiaomi-earbuds-redmi-buds-6.webp','image/webp','public','public',7166,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"xiaomi-earbuds-redmi-buds-6___large_1400_1400.webp\",\"xiaomi-earbuds-redmi-buds-6___large_1171_1171.webp\",\"xiaomi-earbuds-redmi-buds-6___large_979_979.webp\",\"xiaomi-earbuds-redmi-buds-6___large_819_819.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5dTFuVUJwdW55WEJHZG96WEE2UjQrKzFhbVJPNnBGbnVhN1h4UkdaTkR1UUZ5ZGg0cjVhMUs2dUd2SllZaXlNckhwUUI5VTIzaUhUcmtoWTdtTWsrOWFpdUhBS25JTmZJTmhkNnBiU0NWTHRodFA5NnZvcjRkNnpjYXJvcW1ja3NveG1nQzM0NTFnNlhva3BVWkxERmZOWW1hODFDWmltR2NubXZxWHhGb2tldFdEd09PU09LOGp1L2h4ZlcwN0dCQVJuZzBBZVppeG1XL1NIY2NNZWxmVEhnWFRVc1BEOEFWY0VxQ2E4NjBMNGJYejZvbHplSENxYzRyMmF5dGx0TFpJVUhDakZBSC8vMlE9PSI+Cgk8L2ltYWdlPgo8L3N2Zz4=\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:02:13'),(31,'product',19,'9e614ff2-4005-48f6-8fa8-ef4783d4c4cc','main','anker-earbuds-soundcore-r50i-nc','anker-earbuds-soundcore-r50i-nc.webp','image/webp','public','public',6772,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"anker-earbuds-soundcore-r50i-nc___large_1400_1400.webp\",\"anker-earbuds-soundcore-r50i-nc___large_1171_1171.webp\",\"anker-earbuds-soundcore-r50i-nc___large_979_979.webp\",\"anker-earbuds-soundcore-r50i-nc___large_819_819.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5L3JGMTdYWXRGZ0VzZ0pGYkpJSFd1ZDhWUVdkenA1Rnc2akhJSm9BeXRSOFUzRnhvaHU3R001OTZ2ZUR0UXZ0UnNmTnZGd1QwcklzdFowTmJCYkI1a0hhdXgwcGJaYk5CYWtGTzJLQUtYaUMrbHQ0bGpoNGRqak5jYjR5aXVSbzhiUEl4ejF4WGQ2cnAvMnhBUjk1ZWxaYTZXOTZwdDd0ZDBmVG1nRDU0dmthS2JmSEsyYzlqWHVYd3h1TG1mUTE4L2R4MHpWbi9oWE9qbVhlWWdlYzEwK242ZEJwdHVJYmRBcWowb0EvLzlrPSI+Cgk8L2ltYWdlPgo8L3N2Zz4=\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:02:18'),(32,'product',20,'95857334-afee-43b4-9fb9-3f85957dccbc','main','qcy-earbuds-ailybuds-pro-plus','qcy-earbuds-ailybuds-pro-plus.webp','image/webp','public','public',5140,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"qcy-earbuds-ailybuds-pro-plus___large_1400_1400.webp\",\"qcy-earbuds-ailybuds-pro-plus___large_1171_1171.webp\",\"qcy-earbuds-ailybuds-pro-plus___large_979_979.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5N2xiYWpONkN2Tzc3eFZmRFh4YnBHVmpVODE2SFBLa1VaZVFnS091YTg4MWpWZEhPcUNRU0psZXVLdUFtZEpwL2lxMHVybjdNV0FrSEdEWFJLY2pJcnlEU2JqUzUvRVptTTJ6bmpuclhyZHU2UENwUnNyamcwVFZnVHVjdDhRTDJXejhQeXRFU0NSMUZlT2FHOEUrNlM0YmN4UGMxNzVyK2tKck9teVdyL3hEaXZHcnY0YmF2WlhEL1pueWhQRmFVWnhqdVROTm1ScmJRVzVXUzNPMXdleHIyYndEZVMzbmgrRnBTU2NkVFhtTm44TnRXdmJsUHRUNFFIbXZaZEIwbE5IMDJPMlQrRVlvclRqTFlJSm8vOWs9Ij4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:02:23'),(33,'product',21,'eff01495-91bd-4a4c-bbbd-e33b88eb9920','main','power-bank-compact-ivory-10000mah','power-bank-compact-ivory-10000mah.webp','image/webp','public','public',36548,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"power-bank-compact-ivory-10000mah___large_1400_1400.webp\",\"power-bank-compact-ivory-10000mah___large_1171_1171.webp\",\"power-bank-compact-ivory-10000mah___large_979_979.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5OXpTYng2MW1YbXBDQ1R5K2xWMXUyYzdnMUs0RzVtbHJOaXZUMGFya2N5eWREVEFwWCtscGRqUFJ2V3VkdUxhODA5OGdGa3J0Y1ZISkVrZ3d5Z2lsWURsN1M3YWRNQlR1clYwKzNuV1F2SWVEMnE5SFl3eE51UkFEVmdERkZobi85az0iPgoJPC9pbWFnZT4KPC9zdmc+\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:02:29'),(34,'product',22,'882db213-3bd7-449e-bfbd-1668db068129','main','power-bank-brushed-silver-20000mah','power-bank-brushed-silver-20000mah.webp','image/webp','public','public',53672,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"power-bank-brushed-silver-20000mah___large_1400_1400.webp\",\"power-bank-brushed-silver-20000mah___large_1171_1171.webp\",\"power-bank-brushed-silver-20000mah___large_980_980.webp\",\"power-bank-brushed-silver-20000mah___large_819_819.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5NWVkSXhsbUFxbWRZdFJLRTh3WnJBMVM0bVM1S09TRk5ZODF1eGJ6RVltZ0QwV09SWkZES2NnMCt1TDByV1pMY2lPWWtyWFdXOTFIT2daR0JvQXFhbnBpWGtSNCtic2E0KzRMYVl6TGNENUIzTmVoRVZrYTFvc1dxMmp4TU1FanJRQjVMcS9qYTB0M1pMY2JtOXF0ZUMvRW1yNmpxd1h5M0VIdlhRYWY4TExDR2N5ei9Qem5tdTAwN1FyTFRWQXQ0VlhIY0NnRC85az0iPgoJPC9pbWFnZT4KPC9zdmc+\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:02:37'),(35,'product',23,'6f6c22c6-7349-493b-a8ac-bf2aed484fef','main','power-bank-graphite-laptop-30000mah','power-bank-graphite-laptop-30000mah.webp','image/webp','public','public',62790,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"power-bank-graphite-laptop-30000mah___large_1400_1400.webp\",\"power-bank-graphite-laptop-30000mah___large_1171_1171.webp\",\"power-bank-graphite-laptop-30000mah___large_980_980.webp\",\"power-bank-graphite-laptop-30000mah___large_819_819.webp\",\"power-bank-graphite-laptop-30000mah___large_686_686.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5K3FLZTRqdDR5OGpCVkhyVmJVZFNnMDYzYWFad29BenpYaS9pLzRodmZTUGIya20yTWNaQm9BOVFrOGI2VEhPWWpPdVI3MXAyT3VXV29IRUV5c2ZRR3ZsNHp6VFNidk1Zazk4MTZUOE5yVzdONzVoWmludlFBMzRvYXZmUjNmMmRkd2lJN1Y1TklGYk9TUVRYMVhxL2htdzFnZjZSRUNmWEZjZGYvQ2JUNXlURjh0QUhsSGhuUjVOU3VZNG93V0dlVFgwSjRiME9MU2JGRkNqZmprMW4rR1BCZHRvQzVBRE42MTF3R0JRQi8vWiI+Cgk8L2ltYWdlPgo8L3N2Zz4=\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:02:44'),(36,'product',24,'4fda7e32-42f2-4dfb-b345-dc7c80da721f','main','power-bank-forest-green-20000mah','power-bank-forest-green-20000mah.webp','image/webp','public','public',35812,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"power-bank-forest-green-20000mah___large_1400_1400.webp\",\"power-bank-forest-green-20000mah___large_1171_1171.webp\",\"power-bank-forest-green-20000mah___large_979_979.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5M3U1Zkl0M2svdWpOZUwrSVBpVmZRYWhMYnc0QVU0cjJQVlArUWZOL3Vtdmx2eEdUL2JseC92R3BrN0VUazBkbm92anpVcHRXaUVrbVZac1lyM1d4bU05cEhJZXJMbXZsbnc5enJGdi92Q3ZxSFN1Tk9oLzNSUkYzRkNUZTR1cWY4ZzZiL2ROZkxYaVQva04zSCs4YStxYjJJeldza1k2a0VWNEZyM2dQVXJqV3BXaVRLczJjMFNDb216bC9EZk90Vy8rOEsrb3RNNDArSC9kRmVNK0hQaHRmMjkvRmNUSEFVNXIycTFpOG0zU1ArNk1VUlZncHByYy85az0iPgoJPC9pbWFnZT4KPC9zdmc+\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:02:50'),(37,'product',25,'44e758f0-81d8-4288-b1dd-093eb1a706ec','main','power-bank-warm-white-magnetic-5000mah','power-bank-warm-white-magnetic-5000mah.webp','image/webp','public','public',15668,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"power-bank-warm-white-magnetic-5000mah___large_1400_1400.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5N1p3dlUxQzEyaTk2clg3TXA5cXBoZ1J6U3VPeHJ4VHJMME5UMWhRVEdPYmpwVzFHNGRRUlFtSWd1NHZNaVByV1B3TWc4RVYwTERJckV2OU1rbGszUk5qTkRRMFZHbmpqNnNNMXFhWmNlYXBBNkNzK0h3L3VjTk01TmJWdGFKYkp0UVVrZ1ovLzlrPSI+Cgk8L2ltYWdlPgo8L3N2Zz4=\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:02:54'),(38,'product',26,'ea13ec9d-f551-4481-a7ba-80d6e2490fff','main','power-bank-slim-charcoal-10000mah','power-bank-slim-charcoal-10000mah.webp','image/webp','public','public',27658,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"power-bank-slim-charcoal-10000mah___large_1400_1400.webp\",\"power-bank-slim-charcoal-10000mah___large_1171_1171.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5NmVaSXhsbUErdFZXMWF6VndoblRQcG12TnZpVnFlcjJEZzJ4Wll1NUZlV0xyMm9OTUpHdUgzQSt0QUgxT2tpeUtHVWdnMCt2SVBCM3hEeHN0YjUvWU1hOVh0YnlLN2hXU0p3eWtkcUFLV3RhTmI2dlpQRE1nT1J3Y1Y4KytMUEN0eG9OKzVDRXdrOEhGZlM5WldyYUhhYXZENWR4R0cvQ2dENWp0NFpuY2VVckZ1MkJYc3Z3NE9xckhzdWxjUkFjYnE2aXc4R2FWWUhLUUtUN2l0NkcyaWdYYkdnVWV3b0EvL1oiPgoJPC9pbWFnZT4KPC9zdmc+\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:02:59'),(39,'product',27,'95ff9da1-c1e7-4ab2-ae4f-4a9527aaeb13','main','power-bank-deep-navy-20000mah','power-bank-deep-navy-20000mah.webp','image/webp','public','public',64444,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"power-bank-deep-navy-20000mah___large_1400_1400.webp\",\"power-bank-deep-navy-20000mah___large_1171_1171.webp\",\"power-bank-deep-navy-20000mah___large_979_979.webp\",\"power-bank-deep-navy-20000mah___large_819_819.webp\",\"power-bank-deep-navy-20000mah___large_685_685.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5NGx1b1lSbVNSVitwcW1OY3NESUl4Y0p1OU0xNDc4U3RTMW16dno1YnVzUHRYblVPdTZnczRrKzBQdUI5YTJqU3Vya09kajYyU1JYVUZTQ0Q2VSt2RXZDUHhOZUVwYjM3WlhwdU5ldTZicTFycVVDeVFTS3dJN0dzNVFjZHlrN21mNGs4T1cydDJUeHlJTitPRGl2bmJ4UjRZdU5CdlhWa1BsNTRPSytxQ0t3OWQ4TTJXdVE3TGlNRSt1S3FFM0VVbzNQbGFFeU93RWFNVDdDdlV2aHEyc0xmaFhFZ2c5NjlCMDc0ZGFQWWtONUtzZmNWMDlycHRyWnFCQkNxL1FWVXF0MVlTalkvOWs9Ij4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:03:07'),(40,'product',28,'12b2d922-53d1-4780-865f-2eca1a424b0f','main','power-bank-black-high-capacity-40000mah','power-bank-black-high-capacity-40000mah.webp','image/webp','public','public',115406,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"power-bank-black-high-capacity-40000mah___large_1400_1400.webp\",\"power-bank-black-high-capacity-40000mah___large_1171_1171.webp\",\"power-bank-black-high-capacity-40000mah___large_979_979.webp\",\"power-bank-black-high-capacity-40000mah___large_819_819.webp\",\"power-bank-black-high-capacity-40000mah___large_685_685.webp\",\"power-bank-black-high-capacity-40000mah___large_573_573.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5NHVwREZiTzQ2Z1pyNS84VGVQTDYzMXVWRWZBUnNDdmZOUi80OFpmOTAxOG5lTVQvd0FUKzUvM2pRQjZSNFYrSjkzYzZoRmF6Zk1HT0s5dXQ1UE5nUi83d3pYeUw0T1AvRlIyMys4Syt0ZFAvd0NQR0gvZEZBQnFQL0hqTi91bXZrcnhpMy9FL3VmOTQxOWMzTVhuUVBIL0FIaGl2QnZHUHd3djU5UW11N2Y1Z3hKeFFCNTU0TlAvQUJVZHQvdkN2cmpUL3dEanhoLzNSWHpSNFU4SDZqYmVKSWZNandGYm12cG15UXgyc2FIcUZBb0EvOWs9Ij4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:03:16'),(41,'product',29,'ac484c24-eeea-406a-993f-97e80b522e20','main','power-bank-space-gray-laptop-25000mah','power-bank-space-gray-laptop-25000mah.webp','image/webp','public','public',67116,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"power-bank-space-gray-laptop-25000mah___large_1400_1400.webp\",\"power-bank-space-gray-laptop-25000mah___large_1171_1171.webp\",\"power-bank-space-gray-laptop-25000mah___large_979_979.webp\",\"power-bank-space-gray-laptop-25000mah___large_819_819.webp\",\"power-bank-space-gray-laptop-25000mah___large_685_685.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5d3V0VnRiUUh6WlZYOGF6b3ZGbW15M0FoV2RkeDk2ODk4Wngza04rZDdPSXowTmNjOFU4VWdtaWRzam5PYUFQbytPVlpGREtRUWFmWGtuaFR4eEpic3R0ZXR4MHlhOVJ0TDJHOGhFa1RoZ1Iyb0F6OWUwU0hWYk5rWlJ1eHdhOGp2YkNUU2J1UzN1Vk96c3hyM1VpdWY4QUVQaHFEV29DQ0FIOWFBUG5qVXBHUzhieXBPTThZcnYvQUllNjlxRFhLV3BWMmo5VFhRNmY4TGJLR1h6TGc3em52WFk2YjRlc05NQThpRlZJNzRvQS85az0iPgoJPC9pbWFnZT4KPC9zdmc+\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:03:24'),(42,'product',30,'790768df-4d5f-4682-b2d2-d90ec515b71a','main','power-bank-lavender-magnetic-10000mah','power-bank-lavender-magnetic-10000mah.webp','image/webp','public','public',26414,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"power-bank-lavender-magnetic-10000mah___large_1400_1400.webp\",\"power-bank-lavender-magnetic-10000mah___large_1171_1171.webp\",\"power-bank-lavender-magnetic-10000mah___large_979_979.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5eHU5UWl0Qjg1ck5rOFF4ajdvelVQaU9CaW9rR2NWemF0V3NJcG9wSTdYVDlVUzdPT2hyVEZjRnA5eVlMbFNEeG11NHQ1UkxFckE5UlV6allUUlgxS0ZaclYxSTdWd2NvRWNyS2V4cjBhUkE2a0h2WElhcjRkbm51ZDhMWUJvaEt3MHpHRXFxZXRkYjRmdkRORHN6bkZZMEhoQ1ptRFN5bXVsMHpTMDArUGFweWFjcEpvRzBmLy9aIj4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:03:30'),(43,'product',31,'16f1b080-39cb-4fc5-896b-a8c0e21880fe','main','charger-cable-braided-usb-c-to-usb-c-60w','charger-cable-braided-usb-c-to-usb-c-60w.webp','image/webp','public','public',87984,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"charger-cable-braided-usb-c-to-usb-c-60w___large_1400_1400.webp\",\"charger-cable-braided-usb-c-to-usb-c-60w___large_1171_1171.webp\",\"charger-cable-braided-usb-c-to-usb-c-60w___large_979_979.webp\",\"charger-cable-braided-usb-c-to-usb-c-60w___large_819_819.webp\",\"charger-cable-braided-usb-c-to-usb-c-60w___large_685_685.webp\",\"charger-cable-braided-usb-c-to-usb-c-60w___large_573_573.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5OFp3b3lUZ1ZTbTFpeWdPSG5RSDYxelBpUFdwV3ZVMCszZll6OEUwdHQ0TlNkQkpjVHM3SG5yUUIwc09yMmM1d2s2ay9XcnFzR0dRY2l1S3ZmQi8yYUpwYldkMVpSbnJTZUZkZmxrdW5zTGx0em9jWm9BbytMOUtrVy9TNmhKVnV4cUswMXpYclNGUVlUS25ZaXU4MURUbzcrTFk0b3N0T2p0b0JHUUdBOVJRQndsMXJldTMwTEtJREVoSEpOU2VFTkxiN2U5dy9MZHpYY1hsaEhjUUdNS0Z6NkNtNmRwc2RoSHRYcWU5QUgvLzJRPT0iPgoJPC9pbWFnZT4KPC9zdmc+\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:03:39'),(44,'product',32,'c0feefcf-a492-47ed-ba81-d51cfe64af27','main','charger-cable-usb-c-otg-adapter-silver','charger-cable-usb-c-otg-adapter-silver.webp','image/webp','public','public',72216,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"charger-cable-usb-c-otg-adapter-silver___large_1400_1400.webp\",\"charger-cable-usb-c-otg-adapter-silver___large_1171_1171.webp\",\"charger-cable-usb-c-otg-adapter-silver___large_980_980.webp\",\"charger-cable-usb-c-otg-adapter-silver___large_819_819.webp\",\"charger-cable-usb-c-otg-adapter-silver___large_686_686.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5K3ByT0ZHU2NDb2JpNmp0MUpkZ1ByWE1hbnJVazdHT0RwNmlnRHBYdjdkQVNaRi9PbGd2SXAvdU1EWEFUNy9LSmVRN2oyelc5NFlobE1XK1FuSGFnRG5QaVZMcXR2NWN0bHUyRHJpdVo4UGVLWkoyOGk0alBtRDFyMm03c29ieU1wTWdZSDFGYzRQQTJuSmUvYUk0d3B6bmluZFdGWjNNblR0T24xTzREdUNJODEzVnBhcmJRcWlqb0tMYTFqdG93aUtBQlZta00vL1oiPgoJPC9pbWFnZT4KPC9zdmc+\"}}',1,'2026-11-08 07:33:39','2026-11-08 08:03:47'),(45,'product',33,'4a985e34-a493-45b2-a69e-9d75249f5e15','main','charger-cable-usb-a-to-usb-c-white','charger-cable-usb-a-to-usb-c-white.webp','image/webp','public','public',25102,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"charger-cable-usb-a-to-usb-c-white___large_1400_1400.webp\",\"charger-cable-usb-a-to-usb-c-white___large_1171_1171.webp\",\"charger-cable-usb-a-to-usb-c-white___large_979_979.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5OExBRGsxQzEzRXB3V0ZWcnAyZC9MVTRwRTA1U01zeEpvQXRyY3h2MFlWTUd6V2U5Z3FES05nMFdzekJ6R3h6aWdCTHpDTnVKeFVjTThzZytRNUZYcm0yVzRqS21vck95RnFEZzV6UUJDNW1JK2M0Rk90a0JmSTU5Nm51cmN6cUFHeFQ0SVJFZ1dnRC85az0iPgoJPC9pbWFnZT4KPC9zdmc+\"}}',1,'2026-11-08 07:33:40','2026-11-08 08:03:54'),(46,'product',34,'60bce003-a3a1-43be-89fc-905ad9584aea','main','charger-cable-car-charger-dual-port-38w','charger-cable-car-charger-dual-port-38w.webp','image/webp','public','public',66590,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"charger-cable-car-charger-dual-port-38w___large_1400_1400.webp\",\"charger-cable-car-charger-dual-port-38w___large_1171_1171.webp\",\"charger-cable-car-charger-dual-port-38w___large_979_979.webp\",\"charger-cable-car-charger-dual-port-38w___large_819_819.webp\",\"charger-cable-car-charger-dual-port-38w___large_685_685.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5K3JIMS9Xb3RHc1huY2pJSEFyV2RncWtrNEFyeEw0b2VJQk5laTFqbCtSZnZBR2dEV3N2aXVyMzNsekppTW5HYTlNMDdVSWRSdFVuaFlGV0dhK1VJR2ErdWZMZzY1NHhYMFQ4UExPNnM5RVJibk9jY1pwSy9VcDI2R3Q0cTFBNmZvczhxOWRweFh6RHE5M05xZW9zQVMwa2pWOVNlSU5PL3RUVFpMZis4SzgzMGo0WEMyMVA3Vk44Mkd5QlRKR2ZEejRlUXhXOGQ3ZHJsenpnMTY3QkNrTVlSQUFvcXJZMnYyV0ZZeHdBTVlxOEtBUC8yUT09Ij4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 07:33:40','2026-11-08 08:04:01'),(47,'product',35,'ba913778-15f2-4bf2-a5d1-d0fc78a7fa22','main','charger-cable-usb-a-to-micro-usb-black','charger-cable-usb-a-to-micro-usb-black.webp','image/webp','public','public',54538,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"charger-cable-usb-a-to-micro-usb-black___large_1400_1400.webp\",\"charger-cable-usb-a-to-micro-usb-black___large_1171_1171.webp\",\"charger-cable-usb-a-to-micro-usb-black___large_979_979.webp\",\"charger-cable-usb-a-to-micro-usb-black___large_819_819.webp\",\"charger-cable-usb-a-to-micro-usb-black___large_685_685.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5MnVidUswaU1rcmhWSHJYS1gzeEQwdTBjcUgza2VsWTNpelZHdTlhajAxNVRIRVR5YzFyNmY0SzBVd3F6QlpTUjFKb0FTeitKR2xYTWdSbjJaOWE2MjB2WUwySVNRT0dVK2xjVnJmZ2JSamFPOGUySndNZ2cxaWVBdFNudE5WbDA1cERKRXB3cG9BaitKbW15UjNVZDFDRG4xRlV2RDBIaUc1dFFiZTViNkUxNnhxbWxRYWxEc2xRSDB6VkxTdEQvcytYS25DOWhRQndXcTZkcmNWcTB1b1hiQ01Eb0QxcTc4UHRPV1NkN2tLY0RvVDNydU5aMGROV3QvS2Y3dFM2UnBNT2xXd2hpVUFVQWYvWiI+Cgk8L2ltYWdlPgo8L3N2Zz4=\"}}',1,'2026-11-08 07:33:40','2026-11-08 08:04:07'),(48,'product',36,'a8254bbd-9281-4145-93bc-5967a6ae9b8f','main','charger-cable-wall-charger-dual-port-35w','charger-cable-wall-charger-dual-port-35w.webp','image/webp','public','public',46320,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"charger-cable-wall-charger-dual-port-35w___large_1400_1400.webp\",\"charger-cable-wall-charger-dual-port-35w___large_1171_1171.webp\",\"charger-cable-wall-charger-dual-port-35w___large_979_979.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5N2VSVUdXWUFWU20xbXloKy9PZy9HdWI4ZDNrMWpwVWtzTXUwZ2RLOEtsOFVYY2tqQ1NWano2MEFmVFVPcjJjL3dCeWREK05YRWtWeGxXQkh0WHk3RjRudW91WXAzQit0ZW9mRFh4TGQ2cEkwVTdsZ1BXZ0RzZkZtaHJxK21TUjg3c2NWODI2NW9kMXBsN0pHOFRiUWV1Syt0R1VFWU5ZdXBlR05PMU1Iem9GSlBmRkFIeWdnY1pHRG12WHZoQmJUQzRlUmxJV3VxaytGZWx0Y2VZRndNOUs2dlJkQXRORmg4dTNRRDNvQS8vWiI+Cgk8L2ltYWdlPgo8L3N2Zz4=\"}}',1,'2026-11-08 07:33:40','2026-11-08 08:04:13'),(49,'product',37,'817f5f14-a51f-4ec5-8a08-fe47e9dba4d7','main','charger-cable-wall-charger-usb-c-20w','charger-cable-wall-charger-usb-c-20w.webp','image/webp','public','public',22840,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"charger-cable-wall-charger-usb-c-20w___large_1400_1400.webp\",\"charger-cable-wall-charger-usb-c-20w___large_1171_1171.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5OUpwaGxWZXBxcmRYQmpPS3FHY0VaSm9BMDJ1RVVaSnAwVW9sWEk2Vmh2Y2JqanRWNjNXVmlwWGhhQUlOVVV0a0E0UHJXWUEwTVh6dG11a210MWxIUFdzMmJTUE5iRzdpZ0ROdHBQT21DcU0xMHNDN1l3S3IybW5SV3crVWMrdFhnS0FQLy9aIj4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 07:33:40','2026-11-08 08:04:18'),(50,'product',38,'59c6f44a-17e4-4e9f-b1d3-881a190b4612','main','charger-cable-magnetic-wireless-charging-pad-15w','charger-cable-magnetic-wireless-charging-pad-15w.webp','image/webp','public','public',26140,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"charger-cable-magnetic-wireless-charging-pad-15w___large_1400_1400.webp\",\"charger-cable-magnetic-wireless-charging-pad-15w___large_1171_1171.webp\",\"charger-cable-magnetic-wireless-charging-pad-15w___large_979_979.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5OUxZcUpwMVh2VmE0bU83YURWZk9hQU5FWENIdlVnWUhwV1FmclZpMW5PL1lUUUJUdnZNZ24zODdLcmpVcmZ1NEZiODBDVEtWY1pGWXQxNFpnbllrRXI5S0FHdGV4YkN5c0NQYXBkT0x6emVaZ2hhVzEwQ0tBWUpMRDNyV2hnU0ZRcURBb0EvLzlrPSI+Cgk8L2ltYWdlPgo8L3N2Zz4=\"}}',1,'2026-11-08 07:33:40','2026-11-08 08:04:23'),(51,'product',39,'bdcfa0cf-bb09-4433-b6fb-1da7ff526037','main','charger-cable-usb-c-hub-5-in-1','charger-cable-usb-c-hub-5-in-1.webp','image/webp','public','public',67098,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"charger-cable-usb-c-hub-5-in-1___large_1400_1400.webp\",\"charger-cable-usb-c-hub-5-in-1___large_1171_1171.webp\",\"charger-cable-usb-c-hub-5-in-1___large_979_979.webp\",\"charger-cable-usb-c-hub-5-in-1___large_819_819.webp\",\"charger-cable-usb-c-hub-5-in-1___large_685_685.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5K3BDd0hXczdVTlp0YkNNdExJTWp0bXVGMWp4eExNV2p0QmdldEFIb3YydUhkdDh4Yy9XbnJQRzV3cmcxNHRhNnJmejNneEs3TXg2Q3Uwak56WXJESjVyRm02cVRRQnkzaUsyMU1hbVJLanZHZW1LYnAzaGUrMUtRZklZNHU5ZXVTMmNNeHpKR3JmVVUrT0ZJbHdpZ0Qyb3VCeHE2UFplSExZT1l3OHZxUlZ2U2JTWFVadnRWd01KL0N0ZERjMk1OMFI1cWc0cWFHRklVQ0lBQUtBUC85az0iPgoJPC9pbWFnZT4KPC9zdmc+\"}}',1,'2026-11-08 07:33:40','2026-11-08 08:04:31'),(52,'product',40,'7e835ee6-b39d-4ce7-8dfc-e5ff97d93344','main','charger-cable-usb-a-to-lightning-white','charger-cable-usb-a-to-lightning-white.webp','image/webp','public','public',30734,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"charger-cable-usb-a-to-lightning-white___large_1400_1400.webp\",\"charger-cable-usb-a-to-lightning-white___large_1171_1171.webp\",\"charger-cable-usb-a-to-lightning-white___large_979_979.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5NmVSVUdTYXJQZm9wNDVxT1krYlBzSndLblMxaUE2QTBBTVRVSTJPRHhWcEhEaklOVnByS0psSkF3YWd0SE1jcGpKeUtBRXU0OFNiZ2NHb283bTRIQUc0VmJ2N1pwby9rT0RVT24yOHNXZk01RkFCNWx6SU9SdEZQdDRzU1o2bXJOeEd6UjRYZzBsdENZMCtZNU5BSC8vWiI+Cgk8L2ltYWdlPgo8L3N2Zz4=\"}}',1,'2026-11-08 07:33:40','2026-11-08 08:04:37'),(53,'category',1,'5403df3b-974e-44f1-b43b-1837f56abc78','image','smart-watch','smart-watch.png','image/png','public','public',4381,'[]','[]','{\"thumb\":true,\"card\":true}','[]',1,'2026-11-08 07:33:40','2026-11-08 08:04:38'),(54,'category',2,'600e5356-c97b-4e1b-903a-8321ae0e1123','image','headphones','headphones.png','image/png','public','public',3810,'[]','[]','{\"thumb\":true,\"card\":true}','[]',1,'2026-11-08 07:33:40','2026-11-08 08:04:38'),(55,'category',3,'d03898b2-b8e0-4dc2-b9a3-e6017ec8629b','image','earbuds','earbuds.png','image/png','public','public',2922,'[]','[]','{\"thumb\":true,\"card\":true}','[]',1,'2026-11-08 07:33:40','2026-11-08 08:04:39'),(56,'category',4,'efbf35a5-5a33-4196-9c61-75c333f9c2be','image','over-ear-headphones','over-ear-headphones.png','image/png','public','public',3810,'[]','[]','{\"thumb\":true,\"card\":true}','[]',1,'2026-11-08 07:33:40','2026-11-08 08:04:40'),(57,'category',5,'517ab3b2-7dfb-4758-a786-160de79287e6','image','speaker','speaker.png','image/png','public','public',5929,'[]','[]','{\"thumb\":true,\"card\":true}','[]',1,'2026-11-08 07:33:40','2026-11-08 08:04:40'),(58,'category',6,'aa040162-6cf0-4b1a-b159-563bbfc05ebd','image','powerbanks','powerbanks.png','image/png','public','public',3735,'[]','[]','{\"thumb\":true,\"card\":true}','[]',1,'2026-11-08 07:33:40','2026-11-08 08:04:41'),(59,'category',7,'45aa967d-1e4b-4477-b9b4-5d7528b52edd','image','charger-cable','charger-cable.png','image/png','public','public',3914,'[]','[]','{\"thumb\":true,\"card\":true}','[]',1,'2026-11-08 07:33:40','2026-11-08 08:04:42'),(60,'category',8,'e253994f-67da-47e6-9aa6-fadf484ba83b','image','smart-phone','smart-phone.png','image/png','public','public',1733,'[]','[]','{\"thumb\":true,\"card\":true}','[]',1,'2026-11-08 07:33:40','2026-11-08 08:04:42'),(61,'product',47,'5caa2809-3e80-4153-9cb7-dd31394cb875','main','speaker-portable-charcoal-10w','speaker-portable-charcoal-10w.webp','image/webp','public','public',109942,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"speaker-portable-charcoal-10w___large_1400_1400.webp\",\"speaker-portable-charcoal-10w___large_1171_1171.webp\",\"speaker-portable-charcoal-10w___large_980_980.webp\",\"speaker-portable-charcoal-10w___large_819_819.webp\",\"speaker-portable-charcoal-10w___large_686_686.webp\",\"speaker-portable-charcoal-10w___large_573_573.webp\",\"speaker-portable-charcoal-10w___large_480_480.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5OUp3TTF5V3VlTjdQUjdnd3NRWEhhdXJsLzFiZlN2bVQ0azNjc2ZpZVVCampOQUhxaitORHJrcVcxbVBtSjVydk5MamtpczBXVTViSE5mUC93QUxyaDV0ZlFPYzE5R0lQbEgwb0FTWC9WdDlLK1h2aVoveU5FdjFyNmljWlVpdkVQaUY0RHZkUjFkcnEyWElhZ0RuZmhSL3lIMHI2U1g3b3J4MzRjZUNidlNyMzdUY2pHSzlpWGdVQWYvWiI+Cgk8L2ltYWdlPgo8L3N2Zz4=\"}}',1,'2026-11-08 07:52:58','2026-11-08 08:04:51'),(62,'product',48,'00dc0317-c33d-4ffa-996b-a6709ec946e3','main','speaker-retro-cream-15w','speaker-retro-cream-15w.webp','image/webp','public','public',209510,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"speaker-retro-cream-15w___large_1400_1400.webp\",\"speaker-retro-cream-15w___large_1171_1171.webp\",\"speaker-retro-cream-15w___large_979_979.webp\",\"speaker-retro-cream-15w___large_819_819.webp\",\"speaker-retro-cream-15w___large_685_685.webp\",\"speaker-retro-cream-15w___large_573_573.webp\",\"speaker-retro-cream-15w___large_480_480.webp\",\"speaker-retro-cream-15w___large_401_401.webp\",\"speaker-retro-cream-15w___large_336_336.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5N2R3b3lhakVwYm9LbzM5L0ZCSUZrY0NvbDF5MUF3SEZRNUs1WEt6VDg3SDNoVXFzR0dSWFBTNjZqTVZHQ0swTk51aE1DTTBsTk4yQnhhVnpqdkY4anBkakJJRmNzYnFRZnhHdlZkVjBTSFVSbGh6V0RKNEtRL2ROWnpweWIwTGpKVzFPSWl2SnZOWDV6MXIwVHczdVpReE9jaXNyL2hDV1Z3UTNTdW8wblRUWXhCU2NtaUZPU2QyT1UxYlEvL1oiPgoJPC9pbWFnZT4KPC9zdmc+\"}}',1,'2026-11-08 07:52:58','2026-11-08 08:05:02'),(63,'product',49,'c5ec002d-4624-49a2-9996-469b99b4e954','main','speaker-rugged-navy-20w','speaker-rugged-navy-20w.webp','image/webp','public','public',105656,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"speaker-rugged-navy-20w___large_1400_1400.webp\",\"speaker-rugged-navy-20w___large_1171_1171.webp\",\"speaker-rugged-navy-20w___large_979_979.webp\",\"speaker-rugged-navy-20w___large_819_819.webp\",\"speaker-rugged-navy-20w___large_685_685.webp\",\"speaker-rugged-navy-20w___large_573_573.webp\",\"speaker-rugged-navy-20w___large_480_480.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5K3BNaXVFOFhlUG9kQWw4aEJ1a3J6NjYrTWQyR0lWUUt0VTI5U1hKSTk4M0w2aWpJTmZOODN4YzFOMitWOFY2TjhQdkhiNjhQSnVEOC9yVGROcFhCU1RNUDRtZUVyMjh2QmRXa2JQOUs4MWkrSG12WGt1ZnM3QUgxRmZWenhKSjk1UWZyVFZ0NGwrNmlqOEtGVWFWZzVVZkw4bnd2MVMyMnRNcHdmUVY2WjRBOEp2cHNpdVVLNEhKTmVweVdzTXYzMFUvaFRvNFVpR0VVRDZVZTBkZzVULy8yUT09Ij4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 07:52:59','2026-11-08 08:05:11'),(64,'product',50,'ed0d96ae-502f-4eee-84e0-ab5f540c552d','main','speaker-soundbar-slim-black-40w','speaker-soundbar-slim-black-40w.webp','image/webp','public','public',61800,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"speaker-soundbar-slim-black-40w___large_1400_1400.webp\",\"speaker-soundbar-slim-black-40w___large_1171_1171.webp\",\"speaker-soundbar-slim-black-40w___large_979_979.webp\",\"speaker-soundbar-slim-black-40w___large_819_819.webp\",\"speaker-soundbar-slim-black-40w___large_685_685.webp\",\"speaker-soundbar-slim-black-40w___large_573_573.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5K29yS3Y4QVg3RFQ4K2RPcW4weldTM2pqVEIveTNUODZBT3Jvcmk1UGlGcGNmSm5YODZ0YVQ0NTB2VlovSmlsRy8wb0E0anhiNEYxclZ0VE1rRTdDTTlzMWp3L0NYVm54NWwwdy9HdmVNQ2pBb0E4WGgrRGt4LzF0MHgvR3VqOFBmRE9EUnJ3WEhtc3hGZWpZb29BLzlrPSI+Cgk8L2ltYWdlPgo8L3N2Zz4=\"}}',1,'2026-11-08 07:52:59','2026-11-08 08:05:19'),(65,'product',51,'e63340d3-92d7-46e8-8517-af55f0904123','main','speaker-smart-silver-gray-20w','speaker-smart-silver-gray-20w.webp','image/webp','public','public',191492,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"speaker-smart-silver-gray-20w___large_1400_1400.webp\",\"speaker-smart-silver-gray-20w___large_1171_1171.webp\",\"speaker-smart-silver-gray-20w___large_979_979.webp\",\"speaker-smart-silver-gray-20w___large_819_819.webp\",\"speaker-smart-silver-gray-20w___large_685_685.webp\",\"speaker-smart-silver-gray-20w___large_573_573.webp\",\"speaker-smart-silver-gray-20w___large_480_480.webp\",\"speaker-smart-silver-gray-20w___large_401_401.webp\",\"speaker-smart-silver-gray-20w___large_336_336.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5NmxrRWFGaWVCV2NsOTlvZHRyQUtLZHJVaFN5YkJ4WElKcWNjUzdTK0tBT3FhL052TUF6QXFlOWFzYmlSQXdQQnJ6UzgxZFdBQ1BrNXJ1ZEJ1RGNhYkd4UE9LQUdlSUVaN0I5dllWNUxmVHZIS3dKUFd2YkxpSVRSTWhIQkZlZmF6NE1udUxobmhPQVRRQnhWdE04czZxT2Ntdll2RGNMUmFaR0dHRGl1TzBYd1RQYjNheVQ4Z0d2UmJlSVJSS2dHQUJRQi8vWiI+Cgk8L2ltYWdlPgo8L3N2Zz4=\"}}',1,'2026-11-08 07:52:59','2026-11-08 08:05:29'),(66,'product',52,'79ac1ad8-7d14-4625-b127-b12fd6d1181a','main','speaker-gaming-rgb-matte-black-25w','speaker-gaming-rgb-matte-black-25w.webp','image/webp','public','public',117508,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"speaker-gaming-rgb-matte-black-25w___large_1400_1400.webp\",\"speaker-gaming-rgb-matte-black-25w___large_1171_1171.webp\",\"speaker-gaming-rgb-matte-black-25w___large_980_980.webp\",\"speaker-gaming-rgb-matte-black-25w___large_819_819.webp\",\"speaker-gaming-rgb-matte-black-25w___large_686_686.webp\",\"speaker-gaming-rgb-matte-black-25w___large_573_573.webp\",\"speaker-gaming-rgb-matte-black-25w___large_480_480.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5OGRncWtub0s4MDEvd0FXYnRhTm9KZ2tTbmsxM2V1WFgyVFM1NWM0d3ByNWE4UWF6Sk5xczdxeCs4ZWFwTHF5SlRzN0k5cXN2R0Z0QnFVTnZIY0NRTWNIbXZTWVpCTEVyam9SbXZrSFI3cWIrMVlKdDU0Y2Q2K3JQRHR4OXAwZTNmT2ZsRkVsWXN6UEhjanA0ZW5LZWxmTU41RUd1WFlucWVhK3NmRU5pZFEwbWVBREpaVGl2blRWUGgvclAyeVRaR2R1NDRvaTdiaXNyM09adE1SM1Vhb2NuTmZUL2dZdWZEMEcvd0R1aXZFTkErSGVxdmZSdEttRkI1cjZHMFd5K3dhYkZCM1ZRS1VuZGxOM1AvL1oiPgoJPC9pbWFnZT4KPC9zdmc+\"}}',1,'2026-11-08 07:52:59','2026-11-08 08:05:37'),(67,'product',53,'267a3513-254e-4bac-9663-cf6f10762320','main','speaker-party-blue-led-80w','speaker-party-blue-led-80w.webp','image/webp','public','public',155780,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"speaker-party-blue-led-80w___large_1400_1400.webp\",\"speaker-party-blue-led-80w___large_1171_1171.webp\",\"speaker-party-blue-led-80w___large_979_979.webp\",\"speaker-party-blue-led-80w___large_819_819.webp\",\"speaker-party-blue-led-80w___large_685_685.webp\",\"speaker-party-blue-led-80w___large_573_573.webp\",\"speaker-party-blue-led-80w___large_480_480.webp\",\"speaker-party-blue-led-80w___large_401_401.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5NHVwZkp0M2svdWpOZUVlTGZIVTh1cHlSS2NKR2NZcjNIVlArUWRQL3VtdmxQeEhsOWJ1UUQvRWF1RVU5eE5uWCtFUEdNNytJSTAvaFk0eFgwSmJ2NWtDUDZqTmZKM2c4Z2VJcmNaL2pGZlZ0aC94NVJmN29xWkt6QkVlcS84QUlObi9BTncxOG5lSlN3MXk1Qy8zelgxdGZSZWRhU3hqK0pTSytadkZ2aGJVWWRidUhqanlyTVRWUWxZR3JtTDRPQkhpSzNKL3ZpdnJMVC8rUEtML0FIUlh6WDRGOExYOG11eFN5SVFxdGsxOUxXcWVWYm9ub01VcE83dUNQLy9aIj4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 07:52:59','2026-11-08 08:05:46'),(68,'product',54,'97a3128b-0c36-4d14-96c8-4330f2b528a6','main','speaker-portable-olive-40w','speaker-portable-olive-40w.webp','image/webp','public','public',131412,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"speaker-portable-olive-40w___large_1400_1400.webp\",\"speaker-portable-olive-40w___large_1171_1171.webp\",\"speaker-portable-olive-40w___large_980_980.webp\",\"speaker-portable-olive-40w___large_819_819.webp\",\"speaker-portable-olive-40w___large_686_686.webp\",\"speaker-portable-olive-40w___large_573_573.webp\",\"speaker-portable-olive-40w___large_480_480.webp\",\"speaker-portable-olive-40w___large_401_401.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5OUp4VWJYRVM5WFVmalhMK0t2RmtHalFNZ1llYVJ4WER3K01ZWmxMenp0dVBiTlE1cGFBZXdMY3hPY0s2ay9XcEFjMTR4SjQwaGdrVm9KV0p6MHpYZWVHL0Z0dnF5TEVXSG1ZNlVLYWVnSG4vd0FSOUQxT2UvOEFPaFJuVDJyenc2VnFxOWJhVDhxK3FaYmFLWVlrUU45UlZjNlJaSHJicCtWSjAwM2NWajViRmpxU3VNMjhuNVY2UDhPOUt2VHFBbmtqWkZBNzE2MGREMDl1dHRIK1ZXWUxLQzJHSW8xWDZDa3FhVHVGai8vWiI+Cgk8L2ltYWdlPgo8L3N2Zz4=\"}}',1,'2026-11-08 07:52:59','2026-11-08 08:05:54'),(69,'product',55,'d158a038-3030-424f-836e-c210076a1320','main','speaker-bookshelf-walnut-copper-50w','speaker-bookshelf-walnut-copper-50w.webp','image/webp','public','public',67922,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"speaker-bookshelf-walnut-copper-50w___large_1400_1400.webp\",\"speaker-bookshelf-walnut-copper-50w___large_1171_1171.webp\",\"speaker-bookshelf-walnut-copper-50w___large_979_979.webp\",\"speaker-bookshelf-walnut-copper-50w___large_819_819.webp\",\"speaker-bookshelf-walnut-copper-50w___large_685_685.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5dzFQVUl0TnRtbmxPRkZlZlgveEdzeGNFTTJFcnBQSHBJMENYSHBYemxxek5qcjNyR1VtcHBHOElKd2NtZXQyWHhOdElMNHFXekVUWHBta2FwRnExbXR4RDkxcStSRkRxeWs1NjE5TmZEYzU4TXdmU3RFWlBZbThlLzhBSUFsK2xmTzErdStWVlBjMTlNZUo5TmZVOUtrZ2orOFJYaWVwZkQ3Vlhtd2k5RDFyS2NXNXBtOU9hVUdqbXJyU3pGWkpNNVVlbGU5L0RmOEE1RnFINlY1VVBoOXJseXFSeU1kbzdWN1A0UTBtVFNOR2l0cFB2S0txbkZ4SXFUVWovOWs9Ij4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 07:52:59','2026-11-08 08:06:02'),(70,'product',41,'12339216-524f-4a26-831e-c078856686db','main','combo-iphone-17-essentials','combo-iphone-17-essentials.webp','image/webp','public','public',27284,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"combo-iphone-17-essentials___large_1400_1400.webp\",\"combo-iphone-17-essentials___large_1171_1171.webp\",\"combo-iphone-17-essentials___large_979_979.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5OWJwWG5YaVBXcnEyMXlLR000UW5tdlJHUHltdUsxbTBoa3ZqSzZnc3A0clNtcnNxSnFhYmV5TzZBbnFLNkplbGNocFVnYTRYSEFycjErNktkVldZU002OHVKSTJ3dlN1ZnZ5U1M3RHJYUVhtZDNUTlpkM1p5M2FoVUdLbURzeExjcDZVcE55ckFjVjFpU3J3TTgxejhGbkxhS005YTBiUkhkdHpVVkpjekJ1NS8vWiI+Cgk8L2ltYWdlPgo8L3N2Zz4=\"}}',1,'2026-11-08 07:59:04','2026-11-08 08:06:08'),(71,'product',42,'f62d824b-388c-4988-ac57-29d4e8b42c2f','main','combo-galaxy-power','combo-galaxy-power.webp','image/webp','public','public',61830,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"combo-galaxy-power___large_1400_1400.webp\",\"combo-galaxy-power___large_1171_1171.webp\",\"combo-galaxy-power___large_979_979.webp\",\"combo-galaxy-power___large_819_819.webp\",\"combo-galaxy-power___large_685_685.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE2WHg5cjkxcCtvSkZFMkFhcmVFOVZudTlYaUR0bk5RL0VhM0VtcUt4UFNzL3dYT2lhM0h1T0FLM1M5MHRiSHV5ZmNIMHAxVUl0U3QySVFPTTFkVnd3eURtc0NEeGY0anlsZFJ3TTF6L0FJVFhmcW9NcEtMNm12WnRVOEoyZXFYUG5UcmsxekdyZUQyRXdTd2pDQWR4V25QcFlxK2d5YlZvTGFYWjVtVDJJcnE5QXZKN2xBU2NwMk5jRGU2WGVXbHZ0ZTJETjAzVjIzZ3V5dUxiVGcwNSs5eUJXWkovLzlrPSI+Cgk8L2ltYWdlPgo8L3N2Zz4=\"}}',1,'2026-11-08 07:59:04','2026-11-08 08:06:16'),(72,'product',43,'097cba34-f9d7-4723-9b96-32075d79db9a','main','combo-travel-charging','combo-travel-charging.webp','image/webp','public','public',47866,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"combo-travel-charging___large_1400_1400.webp\",\"combo-travel-charging___large_1171_1171.webp\",\"combo-travel-charging___large_979_979.webp\",\"combo-travel-charging___large_819_819.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5eWE5UWREVWtWeXN2ZXNxNmdhM2ZjUHUweExsVUlJYW91N2xXUnMzRjFGYlJsNVdDcVBXczYxOFEyTjNNWTQ1UVdIdlhJZVA3dTliU3diWGQ3NHJtZkFWanFGMWZDYVVPRkhYTldTZFo0eDhjeGFOTDltS1pZMW0rRXRadWRZdVdra2pQazlzMTBmaUh3UGFhN2RMUEwxRmFGajRlaDB5MFdHMlVManFhQUdTMnNOMnBTUWpIb2FrMHkyaXM1ZkxpVmNld3FSOU9jTU1INjFvVzFta0l6M29BLy9aIj4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 07:59:04','2026-11-08 08:06:24'),(73,'product',44,'d0fbdae8-1f9a-425c-8421-3c949941f7c9','main','combo-fitness','combo-fitness.webp','image/webp','public','public',53188,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"combo-fitness___large_1400_1400.webp\",\"combo-fitness___large_1171_1171.webp\",\"combo-fitness___large_979_979.webp\",\"combo-fitness___large_819_819.webp\",\"combo-fitness___large_685_685.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5djFQVmJmUzdjelhEaFZIclhOMnZ4RTBtNXZCYmlVQWs0SE5lYWZFL3dBVXpUM3pXTWNoVlI3MTVyYTNNc2QxRzRjNVZnYzVwWEJIMkpGS3NzYXVoeXBHUlVsZVAydnhTdE5IMHUzaGxKZVFLTW11aDhOZkV1dzE2NThoZmxhbUI1OThXdkN6d1hvdjRnZHA2NHJ6N1RiTVhFOFVhOHNUelgxVHIyaHdhM1pOYnpLQ0NLNC9TdmhaWmFmZmZhTTV3Y2dVcmEzSGM4SThTV3pSWElpMmtGUlhSZkMyMTh6WDE0T0JYcm10ZkRLdzFXZnpUOHBxMzRaOEEybmg2Y3l4OHQ2MHhILy8yUT09Ij4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 07:59:04','2026-11-08 08:06:33'),(74,'product',45,'522d3039-cee3-411e-a6b0-a05324572881','main','combo-desk-setup','combo-desk-setup.webp','image/webp','public','public',112720,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"combo-desk-setup___large_1400_1400.webp\",\"combo-desk-setup___large_1171_1171.webp\",\"combo-desk-setup___large_979_979.webp\",\"combo-desk-setup___large_819_819.webp\",\"combo-desk-setup___large_685_685.webp\",\"combo-desk-setup___large_573_573.webp\",\"combo-desk-setup___large_480_480.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5OUp4V1ZxR3UyMWcyMlJodTlLemZFMnN6YWFvRWZHZTllZjMycW05azN1eFp2YW5ZRDBGdkZFWkdVd0I3MWUwL1dWdTNDSEdhOGZrbXVaR0NydVVlOWQ5NE1zM0MrYlBJQzNZWnBBY2I0ejhiZWZPMXVzZkE3MWtlSGZFVUxUR0o3Y094NkhGZDlyM3czZzFHWXl4ZktUU2FMOE9JZE1ield3empwUUJqV2RyYWFoTTczVndJVG5oZWxTZmIxMGZVRWpzYm95Zy93QU9hNk9Qd1ZIUFBKTk54bm9CVGRMOEZSVytxTk80eW82Wm9BLy8yUT09Ij4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 07:59:04','2026-11-08 08:06:43'),(77,'product',46,'da2a8cf4-9855-4407-91f6-658b98884bfa','main','samsung-mobile-galaxy-a56','samsung-mobile-galaxy-a56.webp','image/webp','public','public',15304,'[]','[]','{\"thumb\":true,\"card\":true,\"medium\":true,\"large\":true,\"og\":true}','{\"large\":{\"urls\":[\"samsung-mobile-galaxy-a56___large_1400_1400.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTQwMCAxNDAwIj4KCTxpbWFnZSB3aWR0aD0iMTQwMCIgaGVpZ2h0PSIxNDAwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUVBWUFCZ0FBRC8vZ0ErUTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9EQXBMQ0JrWldaaGRXeDBJSEYxWVd4cGRIa0svOXNBUXdBSUJnWUhCZ1VJQndjSENRa0lDZ3dVRFF3TEN3d1pFaE1QRkIwYUh4NGRHaHdjSUNRdUp5QWlMQ01jSENnM0tTd3dNVFEwTkI4bk9UMDRNand1TXpReS85c0FRd0VKQ1FrTUN3d1lEUTBZTWlFY0lUSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5LzhBQUVRZ0FJQUFnQXdFaUFBSVJBUU1SQWYvRUFCOEFBQUVGQVFFQkFRRUJBQUFBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUUFBSUJBd01DQkFNRkJRUUVBQUFCZlFFQ0F3QUVFUVVTSVRGQkJoTlJZUWNpY1JReWdaR2hDQ05Dc2NFVlV0SHdKRE5pY29JSkNoWVhHQmthSlNZbktDa3FORFUyTnpnNU9rTkVSVVpIU0VsS1UxUlZWbGRZV1ZwalpHVm1aMmhwYW5OMGRYWjNlSGw2ZzRTRmhvZUlpWXFTazVTVmxwZVltWnFpbzZTbHBxZW9xYXF5czdTMXRyZTR1YnJDdzhURnhzZkl5Y3JTMDlUVjF0ZlkyZHJoNHVQazVlYm42T25xOGZMejlQWDI5L2o1K3YvRUFCOEJBQU1CQVFFQkFRRUJBUUVBQUFBQUFBQUJBZ01FQlFZSENBa0tDLy9FQUxVUkFBSUJBZ1FFQXdRSEJRUUVBQUVDZHdBQkFnTVJCQVVoTVFZU1FWRUhZWEVUSWpLQkNCUkNrYUd4d1Frak0xTHdGV0p5MFFvV0pEVGhKZkVYR0JrYUppY29LU28xTmpjNE9UcERSRVZHUjBoSlNsTlVWVlpYV0ZsYVkyUmxabWRvYVdwemRIVjJkM2g1ZW9LRGhJV0doNGlKaXBLVGxKV1dsNWlabXFLanBLV21wNmlwcXJLenRMVzJ0N2k1dXNMRHhNWEd4OGpKeXRMVDFOWFcxOWpaMnVMajVPWG01K2pwNnZMejlQWDI5L2o1K3YvYUFBd0RBUUFDRVFNUkFEOEE5N2xPSTJQdFhoSGkvd0FZMzlqNGxhM2lmQ2cxN3ROL3FtK2xmTnZqeTNCOFRTeWR3YUFPeThJZU1iNjkxYU8zbGJLbXZZa09VQjlxK2N2QVRaOFJRL1d2b3lML0FGYS9TZ0JKdjlTMzByNXc4ZU5qeEJOOWEra0pSbU5oNml2Q3ZHUGhMVUwzVzVaWWt5cE5BR0g0QWIvaW80ZnJYMGpGL3FsK2xlSWVDL0JtbzJPdFIzRXk0VUd2YjR4aEFQUVVBZi9aIj4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 08:03:22','2026-11-08 08:06:48'),(78,'banner',6,'a31b88e0-a46b-42e0-9af9-3a48d7973bd0','desktop','power-bank-offers','power-bank-offers.webp','image/webp','public','public',81522,'[]','[]','{\"desktop\":true}','{\"desktop\":{\"urls\":[\"power-bank-offers___desktop_1920_992.webp\",\"power-bank-offers___desktop_1606_830.webp\",\"power-bank-offers___desktop_1343_694.webp\",\"power-bank-offers___desktop_1124_581.webp\",\"power-bank-offers___desktop_940_486.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTkyMCA5OTIiPgoJPGltYWdlIHdpZHRoPSIxOTIwIiBoZWlnaHQ9Ijk5MiIgeGxpbms6aHJlZj0iZGF0YTppbWFnZS9qcGVnO2Jhc2U2NCwvOWovNEFBUVNrWkpSZ0FCQVFFQVlBQmdBQUQvL2dBK1ExSkZRVlJQVWpvZ1oyUXRhbkJsWnlCMk1TNHdJQ2gxYzJsdVp5QkpTa2NnU2xCRlJ5QjJPREFwTENCa1pXWmhkV3gwSUhGMVlXeHBkSGtLLzlzQVF3QUlCZ1lIQmdVSUJ3Y0hDUWtJQ2d3VURRd0xDd3daRWhNUEZCMGFIeDRkR2h3Y0lDUXVKeUFpTENNY0hDZzNLU3d3TVRRME5COG5PVDA0TWp3dU16UXkvOXNBUXdFSkNRa01Dd3dZRFEwWU1pRWNJVEl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeS84QUFFUWdBRVFBZ0F3RWlBQUlSQVFNUkFmL0VBQjhBQUFFRkFRRUJBUUVCQUFBQUFBQUFBQUFCQWdNRUJRWUhDQWtLQy8vRUFMVVFBQUlCQXdNQ0JBTUZCUVFFQUFBQmZRRUNBd0FFRVFVU0lURkJCaE5SWVFjaWNSUXlnWkdoQ0NOQ3NjRVZVdEh3SkROaWNvSUpDaFlYR0JrYUpTWW5LQ2txTkRVMk56ZzVPa05FUlVaSFNFbEtVMVJWVmxkWVdWcGpaR1ZtWjJocGFuTjBkWFozZUhsNmc0U0Zob2VJaVlxU2s1U1ZscGVZbVpxaW82U2xwcWVvcWFxeXM3UzF0cmU0dWJyQ3c4VEZ4c2ZJeWNyUzA5VFYxdGZZMmRyaDR1UGs1ZWJuNk9ucThmTHo5UFgyOS9qNSt2L0VBQjhCQUFNQkFRRUJBUUVCQVFFQUFBQUFBQUFCQWdNRUJRWUhDQWtLQy8vRUFMVVJBQUlCQWdRRUF3UUhCUVFFQUFFQ2R3QUJBZ01SQkFVaE1RWVNRVkVIWVhFVElqS0JDQlJDa2FHeHdRa2pNMUx3RldKeTBRb1dKRFRoSmZFWEdCa2FKaWNvS1NvMU5qYzRPVHBEUkVWR1IwaEpTbE5VVlZaWFdGbGFZMlJsWm1kb2FXcHpkSFYyZDNoNWVvS0RoSVdHaDRpSmlwS1RsSldXbDVpWm1xS2pwS1dtcDZpcHFyS3p0TFcydDdpNXVzTER4TVhHeDhqSnl0TFQxTlhXMTlqWjJ1TGo1T1htNStqcDZ2THo5UFgyOS9qNSt2L2FBQXdEQVFBQ0VRTVJBRDhBOUx1dkVpUVM3Q3BOUzJtdUpkU0JBcHlheWIzU2JxVzQzSWd4V2xZYWViZUl1NkR6SzZySTVpbjRuOFFIU0VRcU03alY3VHJ6N2JZeHpuZ3NNMXl2aVhSTDNXSlYrYkNxYzF0YVhEUFoyTWNCSEtqR2FpN3VWWldPbU5OZnBSUlZrbEdicFRWKzdSUlRFZi9aIj4KCTwvaW1hZ2U+Cjwvc3ZnPg==\"}}',1,'2026-11-08 08:04:44','2026-11-08 08:06:54'),(79,'banner',7,'49c6be62-db71-43cf-9bd0-3ca4d7759f67','desktop','combo-deals','combo-deals.webp','image/webp','public','public',54908,'[]','[]','{\"desktop\":true}','{\"desktop\":{\"urls\":[\"combo-deals___desktop_1920_992.webp\",\"combo-deals___desktop_1606_830.webp\",\"combo-deals___desktop_1344_694.webp\",\"combo-deals___desktop_1124_581.webp\"],\"base64svg\":\"data:image\\/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTkyMCA5OTIiPgoJPGltYWdlIHdpZHRoPSIxOTIwIiBoZWlnaHQ9Ijk5MiIgeGxpbms6aHJlZj0iZGF0YTppbWFnZS9qcGVnO2Jhc2U2NCwvOWovNEFBUVNrWkpSZ0FCQVFFQVlBQmdBQUQvL2dBK1ExSkZRVlJQVWpvZ1oyUXRhbkJsWnlCMk1TNHdJQ2gxYzJsdVp5QkpTa2NnU2xCRlJ5QjJPREFwTENCa1pXWmhkV3gwSUhGMVlXeHBkSGtLLzlzQVF3QUlCZ1lIQmdVSUJ3Y0hDUWtJQ2d3VURRd0xDd3daRWhNUEZCMGFIeDRkR2h3Y0lDUXVKeUFpTENNY0hDZzNLU3d3TVRRME5COG5PVDA0TWp3dU16UXkvOXNBUXdFSkNRa01Dd3dZRFEwWU1pRWNJVEl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeS84QUFFUWdBRVFBZ0F3RWlBQUlSQVFNUkFmL0VBQjhBQUFFRkFRRUJBUUVCQUFBQUFBQUFBQUFCQWdNRUJRWUhDQWtLQy8vRUFMVVFBQUlCQXdNQ0JBTUZCUVFFQUFBQmZRRUNBd0FFRVFVU0lURkJCaE5SWVFjaWNSUXlnWkdoQ0NOQ3NjRVZVdEh3SkROaWNvSUpDaFlYR0JrYUpTWW5LQ2txTkRVMk56ZzVPa05FUlVaSFNFbEtVMVJWVmxkWVdWcGpaR1ZtWjJocGFuTjBkWFozZUhsNmc0U0Zob2VJaVlxU2s1U1ZscGVZbVpxaW82U2xwcWVvcWFxeXM3UzF0cmU0dWJyQ3c4VEZ4c2ZJeWNyUzA5VFYxdGZZMmRyaDR1UGs1ZWJuNk9ucThmTHo5UFgyOS9qNSt2L0VBQjhCQUFNQkFRRUJBUUVCQVFFQUFBQUFBQUFCQWdNRUJRWUhDQWtLQy8vRUFMVVJBQUlCQWdRRUF3UUhCUVFFQUFFQ2R3QUJBZ01SQkFVaE1RWVNRVkVIWVhFVElqS0JDQlJDa2FHeHdRa2pNMUx3RldKeTBRb1dKRFRoSmZFWEdCa2FKaWNvS1NvMU5qYzRPVHBEUkVWR1IwaEpTbE5VVlZaWFdGbGFZMlJsWm1kb2FXcHpkSFYyZDNoNWVvS0RoSVdHaDRpSmlwS1RsSldXbDVpWm1xS2pwS1dtcDZpcHFyS3p0TFcydDdpNXVzTER4TVhHeDhqSnl0TFQxTlhXMTlqWjJ1TGo1T1htNStqcDZ2THo5UFgyOS9qNSt2L2FBQXdEQVFBQ0VRTVJBRDhBOUt1dGVGdE9ZeXA0cTVZYXREZDRIUStsWkdvYWRkVFhKWkl3UlYzVE5OYU1iNVZBWWVsZEpycFl2YWhkdkF5Qk9RZXRXSTVQTWlCcXBlUXRJQUZIU3BvUXlJRklwcTFpU2VuRHBSUlNBYWFScUtLRUIvL1oiPgoJPC9pbWFnZT4KPC9zdmc+\"}}',1,'2026-11-08 08:04:45','2026-11-08 08:06:59');
/*!40000 ALTER TABLE `media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_items`
--

DROP TABLE IF EXISTS `menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `menu_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `label` varchar(100) NOT NULL,
  `label_bn` varchar(100) DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `linkable_type` varchar(255) DEFAULT NULL,
  `linkable_id` bigint(20) unsigned DEFAULT NULL,
  `open_in_new_tab` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_items_parent_id_foreign` (`parent_id`),
  KEY `menu_items_linkable_type_linkable_id_index` (`linkable_type`,`linkable_id`),
  KEY `menu_items_menu_id_parent_id_sort_order_index` (`menu_id`,`parent_id`,`sort_order`),
  CONSTRAINT `menu_items_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_items_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_items`
--

LOCK TABLES `menu_items` WRITE;
/*!40000 ALTER TABLE `menu_items` DISABLE KEYS */;
INSERT INTO `menu_items` VALUES (1,1,NULL,'About Us','আমাদের সম্পর্কে',NULL,'page',1,0,1,1,'2026-11-08 07:33:33','2026-11-09 00:57:28'),(3,1,NULL,'Affiliation','অ্যাফিলিয়েশন',NULL,'page',2,0,1,3,'2026-11-08 07:33:33','2026-11-09 00:57:28'),(4,1,NULL,'Service Centers','সার্ভিস সেন্টার',NULL,'page',3,0,1,4,'2026-11-08 07:33:33','2026-11-09 00:57:28'),(5,1,NULL,'EMI Information','ইএমআই তথ্য',NULL,'page',4,0,1,5,'2026-11-08 07:33:33','2026-11-09 00:57:28'),(6,2,NULL,'Privacy Policy','গোপনীয়তা নীতি',NULL,'page',5,0,1,1,'2026-11-08 07:33:34','2026-11-09 00:57:28'),(7,2,NULL,'Warranty Policy','ওয়ারেন্টি নীতি',NULL,'page',6,0,1,2,'2026-11-08 07:33:34','2026-11-09 00:57:28'),(8,2,NULL,'Payment Terms','পেমেন্টের শর্তাবলি',NULL,'page',7,0,1,3,'2026-11-08 07:33:34','2026-11-09 00:57:28'),(9,2,NULL,'Delivery Policy','ডেলিভারি নীতি',NULL,'page',8,0,1,4,'2026-11-08 07:33:34','2026-11-09 00:57:28'),(10,2,NULL,'Terms & Conditions','শর্তাবলি',NULL,'page',9,0,1,5,'2026-11-08 07:33:34','2026-11-09 00:57:28'),(11,2,NULL,'Refund and Return Policy','রিফান্ড ও রিটার্ন নীতি',NULL,'page',10,0,1,6,'2026-11-08 07:33:34','2026-11-09 00:57:28');
/*!40000 ALTER TABLE `menu_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menus` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `name_bn` varchar(100) DEFAULT NULL,
  `location` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menus_location_unique` (`location`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menus`
--

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;
INSERT INTO `menus` VALUES (1,'Information','তথ্য','footer_information','2026-11-08 07:33:33','2026-11-09 00:57:28'),(2,'Policies','নীতিমালা','footer_policies','2026-11-08 07:33:33','2026-11-09 00:57:28');
/*!40000 ALTER TABLE `menus` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_09_17_090104_create_permission_tables',1),(5,'2026_09_17_090106_create_media_table',1),(6,'2026_09_17_090108_create_activity_log_table',1),(7,'2026_09_17_091120_create_notifications_table',1),(8,'2026_09_17_100001_create_shipping_tables',1),(9,'2026_09_17_100002_create_addresses_table',1),(10,'2026_09_17_100003_create_catalog_taxonomy_tables',1),(11,'2026_09_17_100004_create_attribute_and_specification_tables',1),(12,'2026_09_17_100005_create_products_tables',1),(13,'2026_09_17_100006_create_inventory_transactions_table',1),(14,'2026_09_17_100007_create_promotion_tables',1),(15,'2026_09_17_100008_create_cart_and_wishlist_tables',1),(16,'2026_09_17_100009_create_order_tables',1),(17,'2026_09_17_100010_create_reviews_table',1),(18,'2026_09_17_100011_create_content_tables',1),(19,'2026_09_17_102811_add_offer_text_and_faqs_to_products_table',1),(20,'2026_09_19_064513_create_offers_table',1),(21,'2026_11_08_125441_add_pricing_to_shipping_methods_table',1),(22,'2026_11_08_130345_add_trending_and_combo_to_products_table',1),(23,'2026_11_09_062437_add_bangla_translation_columns',2),(24,'2026_11_09_083057_create_ledger_entries_table',3),(25,'2026_11_09_083058_add_unit_cost_to_order_items_table',3),(26,'2026_11_09_084622_add_username_to_users_table',4);
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
INSERT INTO `model_has_permissions` VALUES (4,'user',2),(5,'user',2),(6,'user',2),(7,'user',2),(8,'user',2),(9,'user',2),(10,'user',2),(11,'user',2),(12,'user',2),(13,'user',2),(14,'user',2),(15,'user',2),(16,'user',2);
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
INSERT INTO `model_has_roles` VALUES (1,'user',1);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offer_product`
--

DROP TABLE IF EXISTS `offer_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offer_product` (
  `offer_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`offer_id`,`product_id`),
  KEY `offer_product_product_id_index` (`product_id`),
  CONSTRAINT `offer_product_offer_id_foreign` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `offer_product_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offer_product`
--

LOCK TABLES `offer_product` WRITE;
/*!40000 ALTER TABLE `offer_product` DISABLE KEYS */;
INSERT INTO `offer_product` VALUES (10,1),(10,2),(10,3),(10,6),(10,7),(10,8),(10,9),(10,10),(10,41),(10,42),(10,46),(11,5),(11,11),(11,12),(11,13),(11,14),(11,15),(11,16),(11,17),(11,18),(11,19),(11,20),(11,44),(12,21),(12,22),(12,23),(12,24),(12,25),(12,26),(12,27),(12,28),(12,29),(12,30),(12,31),(12,32),(13,1),(13,2),(13,3),(13,4),(13,47),(13,48),(13,49),(13,50),(13,51),(13,52),(13,53),(13,54);
/*!40000 ALTER TABLE `offer_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offers`
--

DROP TABLE IF EXISTS `offers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `title_bn` varchar(150) DEFAULT NULL,
  `slug` varchar(191) NOT NULL,
  `summary` varchar(255) DEFAULT NULL,
  `summary_bn` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_bn` text DEFAULT NULL,
  `availability` varchar(100) NOT NULL DEFAULT 'Online',
  `is_mobile_deal` tinyint(1) NOT NULL DEFAULT 0,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `offers_slug_unique` (`slug`),
  KEY `offers_is_active_starts_at_ends_at_index` (`is_active`,`starts_at`,`ends_at`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offers`
--

LOCK TABLES `offers` WRITE;
/*!40000 ALTER TABLE `offers` DISABLE KEYS */;
INSERT INTO `offers` VALUES (10,'Mobile Deal','মোবাইল ডিল','mobile-deal','Buy Mobile & Get Exciting Discount with Free Delivery!','মোবাইল কিনুন, পান আকর্ষণীয় ছাড় ও ফ্রি ডেলিভারি!','Buy Mobile & Get Exciting Discount with Free Delivery!\n\nOffer valid while stock lasts. Terms and conditions apply.','মোবাইল কিনুন, পান আকর্ষণীয় ছাড় ও ফ্রি ডেলিভারি!\n\nস্টক থাকা পর্যন্ত অফার প্রযোজ্য। শর্তাবলি প্রযোজ্য।','Online',1,'2026-11-07 00:00:00','2026-11-19 23:59:59',1,0,'2026-11-08 08:11:08','2026-11-09 00:57:28'),(11,'Smartwatch & Earbuds Deal','স্মার্টওয়াচ ও ইয়ারবাডস ডিল','smartwatch-earbuds-deal','Grab smartwatches and earbuds with special mobile deal prices!','বিশেষ দামে স্মার্টওয়াচ ও ইয়ারবাডস লুফে নিন!','Grab smartwatches and earbuds with special mobile deal prices!\n\nOffer valid while stock lasts. Terms and conditions apply.','বিশেষ দামে স্মার্টওয়াচ ও ইয়ারবাডস লুফে নিন!\n\nস্টক থাকা পর্যন্ত অফার প্রযোজ্য। শর্তাবলি প্রযোজ্য।','Online',1,'2026-11-06 00:00:00','2026-11-19 23:59:59',1,1,'2026-11-08 08:11:08','2026-11-09 00:57:28'),(12,'Power Bank & Charger Deal','পাওয়ার ব্যাংক ও চার্জার ডিল','power-bank-charger-deal','Stay charged for less: save on power banks, chargers and cables!','কম খরচে থাকুন ফুল চার্জে: পাওয়ার ব্যাংক, চার্জার ও ক্যাবলে সাশ্রয়!','Stay charged for less: save on power banks, chargers and cables!\n\nOffer valid while stock lasts. Terms and conditions apply.','কম খরচে থাকুন ফুল চার্জে: পাওয়ার ব্যাংক, চার্জার ও ক্যাবলে সাশ্রয়!\n\nস্টক থাকা পর্যন্ত অফার প্রযোজ্য। শর্তাবলি প্রযোজ্য।','Online',0,'2026-11-05 00:00:00','2026-11-19 23:59:59',1,2,'2026-11-08 08:11:08','2026-11-09 00:57:28'),(13,'Speaker & Headphone Deal','স্পিকার ও হেডফোন ডিল','speaker-headphone-deal','Buy speakers and headphones and get exciting discounts!','স্পিকার ও হেডফোন কিনুন, পান আকর্ষণীয় ছাড়!','Buy speakers and headphones and get exciting discounts!\n\nOffer valid while stock lasts. Terms and conditions apply.','স্পিকার ও হেডফোন কিনুন, পান আকর্ষণীয় ছাড়!\n\nস্টক থাকা পর্যন্ত অফার প্রযোজ্য। শর্তাবলি প্রযোজ্য।','All Outlet',0,'2026-11-04 00:00:00','2026-11-19 23:59:59',1,3,'2026-11-08 08:11:08','2026-11-09 00:57:28');
/*!40000 ALTER TABLE `offers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `product_variant_id` bigint(20) unsigned DEFAULT NULL,
  `flash_sale_product_id` bigint(20) unsigned DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `sku` varchar(64) NOT NULL,
  `variant_name` varchar(255) DEFAULT NULL,
  `variant_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variant_options`)),
  `image_path` varchar(500) DEFAULT NULL,
  `warranty` varchar(100) DEFAULT NULL,
  `regular_price` decimal(12,2) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `unit_discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `unit_cost` decimal(12,2) DEFAULT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `line_total` decimal(12,2) NOT NULL,
  `returned_quantity` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  KEY `order_items_product_variant_id_foreign` (`product_variant_id`),
  KEY `order_items_flash_sale_product_id_foreign` (`flash_sale_product_id`),
  CONSTRAINT `order_items_flash_sale_product_id_foreign` FOREIGN KEY (`flash_sale_product_id`) REFERENCES `flash_sale_products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,2,2,2,'Redragon H130 Pelias Wired Gaming Headphone','GD-01002',NULL,NULL,'14/redragon-headphone-headsets-h130.webp','6 Months',1450.00,999.00,451.00,NULL,1,999.00,0,'2026-11-08 08:15:19','2026-11-08 08:15:19'),(2,2,21,21,NULL,'Compact Ivory 10000mAh 20W Fast Charging Power Bank','GD-PB-01',NULL,NULL,'33/power-bank-compact-ivory-10000mah.webp','6 Months',1890.00,1590.00,300.00,NULL,1,1590.00,0,'2026-11-08 08:27:34','2026-11-08 08:27:34'),(3,3,2,2,2,'Redragon H130 Pelias Wired Gaming Headphone','GD-01002',NULL,NULL,'14/redragon-headphone-headsets-h130.webp','6 Months',1450.00,999.00,451.00,NULL,1,999.00,0,'2026-11-08 08:38:57','2026-11-08 08:38:57'),(4,4,1,1,1,'Redragon H320 Lamia RGB Virtual 7.1 Surround Sound Gaming Headset','GD-01001',NULL,NULL,'13/redragon-headphone-headsets-h320-lamia.webp','1 Year',3600.00,2949.00,651.00,NULL,1,2949.00,0,'2026-11-08 08:44:37','2026-11-08 08:44:37'),(5,5,1,1,1,'Redragon H320 Lamia RGB Virtual 7.1 Surround Sound Gaming Headset','GD-01001',NULL,NULL,'13/redragon-headphone-headsets-h320-lamia.webp','1 Year',3600.00,2949.00,651.00,NULL,1,2949.00,0,'2026-11-08 08:50:00','2026-11-08 08:50:00');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_notes`
--

DROP TABLE IF EXISTS `order_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_notes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `body` text NOT NULL,
  `is_customer_visible` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_notes_order_id_foreign` (`order_id`),
  KEY `order_notes_user_id_foreign` (`user_id`),
  CONSTRAINT `order_notes_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_notes`
--

LOCK TABLES `order_notes` WRITE;
/*!40000 ALTER TABLE `order_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_sequences`
--

DROP TABLE IF EXISTS `order_sequences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_sequences` (
  `date` date NOT NULL,
  `last_number` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_sequences`
--

LOCK TABLES `order_sequences` WRITE;
/*!40000 ALTER TABLE `order_sequences` DISABLE KEYS */;
INSERT INTO `order_sequences` VALUES ('2026-11-08',5);
/*!40000 ALTER TABLE `order_sequences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_status_histories`
--

DROP TABLE IF EXISTS `order_status_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_status_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'status',
  `from_status` varchar(30) DEFAULT NULL,
  `to_status` varchar(30) NOT NULL,
  `note` text DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_status_histories_user_id_foreign` (`user_id`),
  KEY `order_status_histories_order_id_created_at_index` (`order_id`,`created_at`),
  CONSTRAINT `order_status_histories_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_status_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_status_histories`
--

LOCK TABLES `order_status_histories` WRITE;
/*!40000 ALTER TABLE `order_status_histories` DISABLE KEYS */;
INSERT INTO `order_status_histories` VALUES (1,1,'status',NULL,'pending','Order placed on the website.',1,'2026-11-08 08:15:19'),(2,2,'status',NULL,'pending','Order placed on the website.',1,'2026-11-08 08:27:34'),(3,3,'status',NULL,'pending','Order placed on the website.',1,'2026-11-08 08:38:57'),(4,4,'status',NULL,'pending','Order placed on the website.',1,'2026-11-08 08:44:37'),(5,5,'status',NULL,'pending','Order placed on the website.',1,'2026-11-08 08:50:00');
/*!40000 ALTER TABLE `order_status_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(32) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `payment_status` varchar(20) NOT NULL DEFAULT 'unpaid',
  `payment_method` varchar(30) NOT NULL,
  `source` varchar(20) NOT NULL DEFAULT 'web',
  `customer_name` varchar(150) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_email` varchar(191) DEFAULT NULL,
  `shipping_address` varchar(500) NOT NULL,
  `shipping_area` varchar(150) DEFAULT NULL,
  `district_id` bigint(20) unsigned DEFAULT NULL,
  `shipping_district` varchar(100) DEFAULT NULL,
  `shipping_zone_id` bigint(20) unsigned DEFAULT NULL,
  `shipping_zone_name` varchar(100) DEFAULT NULL,
  `shipping_method_id` bigint(20) unsigned DEFAULT NULL,
  `shipping_method_name` varchar(100) DEFAULT NULL,
  `customer_note` text DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `coupon_id` bigint(20) unsigned DEFAULT NULL,
  `coupon_code` varchar(50) DEFAULT NULL,
  `coupon_discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `manual_discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(12,2) NOT NULL,
  `paid_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `refunded_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `currency` char(3) NOT NULL DEFAULT 'BDT',
  `courier_name` varchar(100) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `cancellation_reason` varchar(500) DEFAULT NULL,
  `payment_due_at` timestamp NULL DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `shipped_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `orders_district_id_foreign` (`district_id`),
  KEY `orders_shipping_zone_id_foreign` (`shipping_zone_id`),
  KEY `orders_shipping_method_id_foreign` (`shipping_method_id`),
  KEY `orders_coupon_id_foreign` (`coupon_id`),
  KEY `orders_created_by_foreign` (`created_by`),
  KEY `orders_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `orders_status_created_at_index` (`status`,`created_at`),
  KEY `orders_payment_status_created_at_index` (`payment_status`,`created_at`),
  KEY `orders_customer_phone_index` (`customer_phone`),
  KEY `orders_created_at_index` (`created_at`),
  CONSTRAINT `orders_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_shipping_method_id_foreign` FOREIGN KEY (`shipping_method_id`) REFERENCES `shipping_methods` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_shipping_zone_id_foreign` FOREIGN KEY (`shipping_zone_id`) REFERENCES `shipping_zones` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'GD261108-0001',1,'pending','unpaid','cod','web','Lionel Ortega','01712345678','duxadevuk@mailinator.com','Amet esse dicta cumque eos aut','In et magni laudantium elit quos amet hic incidunt molestias velit aut elit est mollit rerum ad',53,'Dinajpur',2,'Outside Dhaka',1,'Inside Dhaka','Labore odit placeat nostrud asperiores culpa proident dolorem sit et qui enim quae illum placeat et dolor',999.00,NULL,NULL,0.00,0.00,0.00,60.00,0.00,1059.00,0.00,0.00,'BDT','Steadfast','SFR261108STEE551C6BD',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-08 08:15:19','2026-11-08 08:40:45'),(2,'GD261108-0002',1,'pending','unpaid','cod','web','Genevieve Sellers','01766366638','myzimaz@mailinator.com','Minim qui excepturi nihil officiis','Minus qui incidunt fuga Sint magni omnis aperiam in nulla sit hic in iste',17,'Chattogram',2,'Outside Dhaka',1,'Inside Dhaka','Ut ad aut nostrum reiciendis incididunt sit pariatur Ea est labore sed non',1590.00,NULL,NULL,0.00,0.00,0.00,60.00,0.00,1650.00,0.00,0.00,'BDT','Steadfast','SFR261108ST7C71BE9BD',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-08 08:27:34','2026-11-08 08:40:35'),(3,'GD261108-0003',1,'pending','unpaid','cod','web','Xavier Davidson','01766366638','cybi@mailinator.com','Distinctio Consequuntur nostrum natus eius deserunt aut nobis odio id voluptatibus eu ea voluptas','Dolores velit adipisicing in voluptatum omnis est amet assumenda dolore et consequuntur',34,'Chuadanga',2,'Outside Dhaka',1,'Inside Dhaka','Fugit ut necessitatibus est voluptatum nemo obcaecati sint quisquam dolore vel quis',999.00,NULL,NULL,0.00,0.00,0.00,60.00,0.00,1059.00,0.00,0.00,'BDT','Steadfast','SFR261108STC20E16ABD',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-08 08:38:57','2026-11-08 08:38:58'),(4,'GD261108-0004',1,'pending','unpaid','cod','web','Harper Everett','01766366638','lexovoda@mailinator.com','Et quia temporibus vel fugiat odit quia et quo sit','Esse voluptatem Cumque sed architecto est nisi qui a',53,'Dinajpur',2,'Outside Dhaka',1,'Inside Dhaka','Autem reiciendis pariatur Dolor autem ut quam non cupidatat quisquam veniam sit',2949.00,NULL,NULL,0.00,0.00,0.00,60.00,0.00,3009.00,0.00,0.00,'BDT','Steadfast','SFR261108STD9D2AF6BD',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-08 08:44:37','2026-11-08 08:44:38'),(5,'GD261108-0005',1,'pending','unpaid','cod','web','John Lester','01766366638','buquqygo@mailinator.com','Rerum ad dolor aliquip impedit similique dolor blanditiis ratione eaque exercitation pariatur Lorem ipsum culpa et corporis nostrud duis','Iste ea iure facere architecto sint aspernatur sint ut id non quis',23,'Noakhali',2,'Outside Dhaka',2,'Outside Dhaka','Dolore eos ullam fugiat ratione ut et nesciunt similique doloremque animi molestiae qui et',2949.00,NULL,NULL,0.00,0.00,0.00,120.00,0.00,3069.00,0.00,0.00,'BDT',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-08 08:50:00','2026-11-08 08:50:00');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `title_bn` varchar(150) DEFAULT NULL,
  `slug` varchar(191) NOT NULL,
  `content` longtext DEFAULT NULL,
  `content_bn` longtext DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_title_bn` varchar(255) DEFAULT NULL,
  `seo_description` varchar(500) DEFAULT NULL,
  `seo_description_bn` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (1,'About Us','আমাদের সম্পর্কে','about-us','<p>This is the About Us page. Replace this text from Content → Pages in the admin panel.</p>','<p>এটি আমাদের সম্পর্কে পেজ। অ্যাডমিন প্যানেলের Content → Pages থেকে এই লেখাটি বদলে দিন।</p>',1,NULL,NULL,NULL,NULL,'2026-11-08 07:33:33','2026-11-09 00:57:28'),(2,'Affiliation','অ্যাফিলিয়েশন','affiliation','<p>This is the Affiliation page. Replace this text from Content → Pages in the admin panel.</p>','<p>এটি অ্যাফিলিয়েশন পেজ। অ্যাডমিন প্যানেলের Content → Pages থেকে এই লেখাটি বদলে দিন।</p>',1,NULL,NULL,NULL,NULL,'2026-11-08 07:33:33','2026-11-09 00:57:28'),(3,'Service Centers','সার্ভিস সেন্টার','service-centers','<p>This is the Service Centers page. Replace this text from Content → Pages in the admin panel.</p>','<p>এটি সার্ভিস সেন্টার পেজ। অ্যাডমিন প্যানেলের Content → Pages থেকে এই লেখাটি বদলে দিন।</p>',1,NULL,NULL,NULL,NULL,'2026-11-08 07:33:33','2026-11-09 00:57:28'),(4,'EMI Information','ইএমআই তথ্য','emi-information','<p>This is the EMI Information page. Replace this text from Content → Pages in the admin panel.</p>','<p>এটি ইএমআই তথ্য পেজ। অ্যাডমিন প্যানেলের Content → Pages থেকে এই লেখাটি বদলে দিন।</p>',1,NULL,NULL,NULL,NULL,'2026-11-08 07:33:33','2026-11-09 00:57:28'),(5,'Privacy Policy','গোপনীয়তা নীতি','privacy-policy','<p>This is the Privacy Policy page. Replace this text from Content → Pages in the admin panel.</p>','<p>এটি গোপনীয়তা নীতি পেজ। অ্যাডমিন প্যানেলের Content → Pages থেকে এই লেখাটি বদলে দিন।</p>',1,NULL,NULL,NULL,NULL,'2026-11-08 07:33:34','2026-11-09 00:57:28'),(6,'Warranty Policy','ওয়ারেন্টি নীতি','warranty-policy','<p>This is the Warranty Policy page. Replace this text from Content → Pages in the admin panel.</p>','<p>এটি ওয়ারেন্টি নীতি পেজ। অ্যাডমিন প্যানেলের Content → Pages থেকে এই লেখাটি বদলে দিন।</p>',1,NULL,NULL,NULL,NULL,'2026-11-08 07:33:34','2026-11-09 00:57:28'),(7,'Payment Terms','পেমেন্টের শর্তাবলি','payment-terms','<p>This is the Payment Terms page. Replace this text from Content → Pages in the admin panel.</p>','<p>এটি পেমেন্টের শর্তাবলি পেজ। অ্যাডমিন প্যানেলের Content → Pages থেকে এই লেখাটি বদলে দিন।</p>',1,NULL,NULL,NULL,NULL,'2026-11-08 07:33:34','2026-11-09 00:57:28'),(8,'Delivery Policy','ডেলিভারি নীতি','delivery-policy','<p>This is the Delivery Policy page. Replace this text from Content → Pages in the admin panel.</p>','<p>এটি ডেলিভারি নীতি পেজ। অ্যাডমিন প্যানেলের Content → Pages থেকে এই লেখাটি বদলে দিন।</p>',1,NULL,NULL,NULL,NULL,'2026-11-08 07:33:34','2026-11-09 00:57:28'),(9,'Terms & Conditions','শর্তাবলি','terms-and-conditions','<p>This is the Terms & Conditions page. Replace this text from Content → Pages in the admin panel.</p>','<p>এটি শর্তাবলি পেজ। অ্যাডমিন প্যানেলের Content → Pages থেকে এই লেখাটি বদলে দিন।</p>',1,NULL,NULL,NULL,NULL,'2026-11-08 07:33:34','2026-11-09 00:57:28'),(10,'Refund and Return Policy','রিফান্ড ও রিটার্ন নীতি','refund-and-return-policy','<p>This is the Refund and Return Policy page. Replace this text from Content → Pages in the admin panel.</p>','<p>এটি রিফান্ড ও রিটার্ন নীতি পেজ। অ্যাডমিন প্যানেলের Content → Pages থেকে এই লেখাটি বদলে দিন।</p>',1,NULL,NULL,NULL,NULL,'2026-11-08 07:33:34','2026-11-09 00:57:28');
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
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
  `order_id` bigint(20) unsigned NOT NULL,
  `method` varchar(30) NOT NULL,
  `gateway` varchar(50) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'BDT',
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `sender_number` varchar(20) DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `paid_at` timestamp NULL DEFAULT NULL,
  `recorded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_method_transaction_id_unique` (`method`,`transaction_id`),
  KEY `payments_order_id_foreign` (`order_id`),
  KEY `payments_recorded_by_foreign` (`recorded_by`),
  KEY `payments_status_created_at_index` (`status`,`created_at`),
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `payments_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'dashboard.view','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(2,'reports.view','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(3,'activity_log.view','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(4,'products.view','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(5,'products.create','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(6,'products.update','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(7,'products.delete','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(8,'products.view_cost_price','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(9,'categories.view','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(10,'categories.create','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(11,'categories.update','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(12,'categories.delete','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(13,'brands.view','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(14,'brands.create','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(15,'brands.update','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(16,'brands.delete','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(17,'attributes.manage','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(18,'specifications.manage','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(19,'inventory.view','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(20,'inventory.manage','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(21,'orders.view','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(22,'orders.create','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(23,'orders.update','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(24,'orders.cancel','web','2026-11-08 07:33:31','2026-11-08 07:33:31'),(25,'orders.refund','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(26,'payments.view','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(27,'payments.manage','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(28,'coupons.view','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(29,'coupons.manage','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(30,'flash_sales.view','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(31,'flash_sales.manage','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(32,'reviews.view','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(33,'reviews.moderate','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(34,'customers.view','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(35,'customers.update','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(36,'customers.delete','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(37,'content.manage','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(38,'shipping.manage','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(39,'settings.manage','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(40,'staff.manage','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(41,'roles.manage','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(42,'finance.view','web','2026-11-09 02:37:49','2026-11-09 02:37:49'),(43,'finance.manage','web','2026-11-09 02:37:50','2026-11-09 02:37:50');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_specification_values`
--

DROP TABLE IF EXISTS `product_specification_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_specification_values` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `specification_id` bigint(20) unsigned NOT NULL,
  `specification_option_id` bigint(20) unsigned DEFAULT NULL,
  `value` varchar(500) DEFAULT NULL,
  `value_numeric` decimal(14,4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_spec_value_unique` (`product_id`,`specification_id`,`specification_option_id`),
  KEY `product_spec_option_filter_index` (`specification_option_id`,`product_id`),
  KEY `product_spec_numeric_filter_index` (`specification_id`,`value_numeric`),
  CONSTRAINT `product_specification_values_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_specification_values_specification_id_foreign` FOREIGN KEY (`specification_id`) REFERENCES `specifications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_specification_values_specification_option_id_foreign` FOREIGN KEY (`specification_option_id`) REFERENCES `specification_options` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=235 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_specification_values`
--

LOCK TABLES `product_specification_values` WRITE;
/*!40000 ALTER TABLE `product_specification_values` DISABLE KEYS */;
INSERT INTO `product_specification_values` VALUES (1,1,29,45,NULL,NULL),(2,1,30,NULL,'53',53.0000),(3,1,31,NULL,'Virtual 7.1',NULL),(4,1,37,NULL,'Black',NULL),(5,2,29,45,NULL,NULL),(6,2,30,NULL,'50',50.0000),(7,2,37,NULL,'Black',NULL),(8,3,29,45,NULL,NULL),(9,3,30,NULL,'50',50.0000),(10,3,37,NULL,'White',NULL),(11,4,29,47,NULL,NULL),(12,4,32,NULL,'800',800.0000),(13,4,27,NULL,'5.4',NULL),(14,5,1,NULL,'1.43',1.4300),(15,5,4,18,NULL,NULL),(16,5,27,NULL,'5.3',NULL),(17,5,33,NULL,'480',480.0000),(18,6,1,NULL,'6.3',6.3000),(19,6,3,10,NULL,NULL),(20,6,4,17,NULL,NULL),(21,6,6,22,NULL,NULL),(22,6,7,NULL,'A19 Pro',NULL),(23,6,11,NULL,'12',12.0000),(24,6,13,32,NULL,NULL),(25,6,35,NULL,'48MP + 48MP + 48MP',NULL),(26,6,36,NULL,'18MP',NULL),(27,7,1,NULL,'6.9',6.9000),(28,7,3,10,NULL,NULL),(29,7,4,18,NULL,NULL),(30,7,6,24,NULL,NULL),(31,7,11,NULL,'12',12.0000),(32,7,13,32,NULL,NULL),(33,7,33,NULL,'5000',5000.0000),(34,8,1,NULL,'6.3',6.3000),(35,8,3,10,NULL,NULL),(36,8,4,17,NULL,NULL),(37,8,6,22,NULL,NULL),(38,8,7,NULL,'A19',NULL),(39,8,11,NULL,'8',8.0000),(40,8,13,32,NULL,NULL),(41,8,35,NULL,'48MP + 48MP',NULL),(42,8,36,NULL,'18MP',NULL),(43,9,1,NULL,'6.3',6.3000),(44,9,3,10,NULL,NULL),(45,9,4,17,NULL,NULL),(46,9,7,NULL,'Google Tensor G4',NULL),(47,9,11,NULL,'16',16.0000),(48,9,13,32,NULL,NULL),(49,10,11,NULL,'8',8.0000),(50,10,13,32,NULL,NULL),(51,11,4,17,NULL,NULL),(52,12,4,18,NULL,NULL),(53,13,4,18,NULL,NULL),(54,14,4,18,NULL,NULL),(55,15,4,18,NULL,NULL),(56,16,29,46,NULL,NULL),(57,16,37,NULL,'White',NULL),(58,17,29,46,NULL,NULL),(59,17,37,NULL,'Black',NULL),(60,18,29,46,NULL,NULL),(61,18,37,NULL,'Black',NULL),(62,19,29,46,NULL,NULL),(63,19,37,NULL,'Black',NULL),(64,20,29,46,NULL,NULL),(65,20,37,NULL,'Blue',NULL),(66,21,33,NULL,'10000',10000.0000),(67,21,32,NULL,'20',20.0000),(68,21,34,NULL,'PD 20W, QC 3.0',NULL),(69,21,28,NULL,'1x USB-C, 1x USB-A',NULL),(70,21,29,45,NULL,NULL),(71,21,37,NULL,'Ivory',NULL),(72,21,39,NULL,'210g',NULL),(73,22,33,NULL,'20000',20000.0000),(74,22,32,NULL,'65',65.0000),(75,22,34,NULL,'PD 3.0 65W, QC 3.0',NULL),(76,22,28,NULL,'2x USB-C, 1x USB-A',NULL),(77,22,29,45,NULL,NULL),(78,22,37,NULL,'Silver',NULL),(79,22,39,NULL,'420g',NULL),(80,23,33,NULL,'30000',30000.0000),(81,23,32,NULL,'140',140.0000),(82,23,34,NULL,'PD 3.1 140W',NULL),(83,23,28,NULL,'3x USB-C, 1x USB-A',NULL),(84,23,29,45,NULL,NULL),(85,23,37,NULL,'Graphite',NULL),(86,23,39,NULL,'640g',NULL),(87,24,33,NULL,'20000',20000.0000),(88,24,32,NULL,'30',30.0000),(89,24,34,NULL,'PD 30W, QC 3.0',NULL),(90,24,28,NULL,'1x USB-C, 2x USB-A',NULL),(91,24,29,45,NULL,NULL),(92,24,37,NULL,'Forest Green',NULL),(93,24,39,NULL,'390g',NULL),(94,25,33,NULL,'5000',5000.0000),(95,25,32,NULL,'15',15.0000),(96,25,34,NULL,'15W Magnetic Wireless, PD 20W',NULL),(97,25,28,NULL,'1x USB-C',NULL),(98,25,29,47,NULL,NULL),(99,25,37,NULL,'Warm White',NULL),(100,25,39,NULL,'120g',NULL),(101,26,33,NULL,'10000',10000.0000),(102,26,32,NULL,'20',20.0000),(103,26,34,NULL,'PD 20W, QC 3.0',NULL),(104,26,28,NULL,'1x USB-C, 1x USB-A',NULL),(105,26,29,45,NULL,NULL),(106,26,37,NULL,'Charcoal',NULL),(107,26,39,NULL,'190g',NULL),(108,27,33,NULL,'20000',20000.0000),(109,27,32,NULL,'22.5',22.5000),(110,27,34,NULL,'PD 20W, QC 22.5W',NULL),(111,27,28,NULL,'1x USB-C, 2x USB-A',NULL),(112,27,29,45,NULL,NULL),(113,27,37,NULL,'Navy Blue',NULL),(114,27,39,NULL,'400g',NULL),(115,28,33,NULL,'40000',40000.0000),(116,28,32,NULL,'100',100.0000),(117,28,34,NULL,'PD 3.0 100W, QC 3.0',NULL),(118,28,28,NULL,'2x USB-C, 2x USB-A',NULL),(119,28,29,45,NULL,NULL),(120,28,37,NULL,'Black',NULL),(121,28,39,NULL,'820g',NULL),(122,29,33,NULL,'25000',25000.0000),(123,29,32,NULL,'100',100.0000),(124,29,34,NULL,'PD 3.0 100W',NULL),(125,29,28,NULL,'2x USB-C, 1x USB-A',NULL),(126,29,29,45,NULL,NULL),(127,29,37,NULL,'Space Gray',NULL),(128,29,39,NULL,'560g',NULL),(129,30,33,NULL,'10000',10000.0000),(130,30,32,NULL,'15',15.0000),(131,30,34,NULL,'15W Magnetic Wireless, PD 20W',NULL),(132,30,28,NULL,'1x USB-C',NULL),(133,30,29,47,NULL,NULL),(134,30,37,NULL,'Lavender',NULL),(135,30,39,NULL,'200g',NULL),(136,31,32,NULL,'60',60.0000),(137,31,34,NULL,'PD 60W',NULL),(138,31,28,NULL,'USB-C to USB-C',NULL),(139,31,29,45,NULL,NULL),(140,32,28,NULL,'USB-C (male) to USB-A 3.0 (female)',NULL),(141,32,29,45,NULL,NULL),(142,33,32,NULL,'15',15.0000),(143,33,34,NULL,'QC 3.0, 3A',NULL),(144,33,28,NULL,'USB-A to USB-C',NULL),(145,33,29,45,NULL,NULL),(146,34,32,NULL,'38',38.0000),(147,34,34,NULL,'PD 20W + QC 3.0 18W',NULL),(148,34,28,NULL,'1x USB-C, 1x USB-A',NULL),(149,34,29,45,NULL,NULL),(150,35,32,NULL,'12',12.0000),(151,35,34,NULL,'2.4A',NULL),(152,35,28,NULL,'USB-A to Micro-USB',NULL),(153,35,29,45,NULL,NULL),(154,36,32,NULL,'35',35.0000),(155,36,34,NULL,'PD 3.0, QC 3.0',NULL),(156,36,28,NULL,'1x USB-C, 1x USB-A',NULL),(157,36,29,45,NULL,NULL),(158,37,32,NULL,'20',20.0000),(159,37,34,NULL,'PD 20W',NULL),(160,37,28,NULL,'1x USB-C',NULL),(161,37,29,45,NULL,NULL),(162,38,32,NULL,'15',15.0000),(163,38,34,NULL,'Qi 15W',NULL),(164,38,28,NULL,'1x USB-C (input)',NULL),(165,38,29,46,NULL,NULL),(166,39,28,NULL,'1x HDMI 4K, 2x USB-A 3.0, SD, microSD',NULL),(167,39,29,45,NULL,NULL),(168,40,32,NULL,'12',12.0000),(169,40,34,NULL,'2.4A',NULL),(170,40,28,NULL,'USB-A to Lightning',NULL),(171,40,29,45,NULL,NULL),(172,46,1,NULL,'6.7',6.7000),(173,46,2,6,NULL,NULL),(174,46,3,10,NULL,NULL),(175,46,4,19,NULL,NULL),(176,46,6,25,NULL,NULL),(177,46,7,NULL,'Exynos 1580',NULL),(178,46,35,NULL,'50MP + 12MP + 5MP',NULL),(179,46,36,NULL,'12MP',NULL),(180,46,33,NULL,'5000',5000.0000),(181,46,34,NULL,'45W',NULL),(182,47,29,46,NULL,NULL),(183,47,32,NULL,'10',10.0000),(184,47,27,NULL,'5.3',NULL),(185,47,33,NULL,'1800',1800.0000),(186,47,37,NULL,'Charcoal',NULL),(187,47,39,NULL,'380g',NULL),(188,48,29,46,NULL,NULL),(189,48,32,NULL,'15',15.0000),(190,48,27,NULL,'5.3',NULL),(191,48,33,NULL,'2400',2400.0000),(192,48,37,NULL,'Cream',NULL),(193,48,39,NULL,'720g',NULL),(194,49,29,46,NULL,NULL),(195,49,32,NULL,'20',20.0000),(196,49,27,NULL,'5.3',NULL),(197,49,33,NULL,'3600',3600.0000),(198,49,37,NULL,'Navy Blue',NULL),(199,49,39,NULL,'560g',NULL),(200,50,29,47,NULL,NULL),(201,50,32,NULL,'40',40.0000),(202,50,27,NULL,'5.3',NULL),(203,50,33,NULL,'2000',2000.0000),(204,50,37,NULL,'Black',NULL),(205,50,39,NULL,'1.1kg',NULL),(206,51,29,46,NULL,NULL),(207,51,32,NULL,'20',20.0000),(208,51,27,NULL,'5.3',NULL),(209,51,33,NULL,'5200',5200.0000),(210,51,37,NULL,'Silver-Gray',NULL),(211,51,39,NULL,'950g',NULL),(212,52,29,47,NULL,NULL),(213,52,32,NULL,'25',25.0000),(214,52,27,NULL,'5.3',NULL),(215,52,33,NULL,'3000',3000.0000),(216,52,37,NULL,'Matte Black',NULL),(217,52,39,NULL,'1.2kg',NULL),(218,53,29,47,NULL,NULL),(219,53,32,NULL,'80',80.0000),(220,53,27,NULL,'5.3',NULL),(221,53,33,NULL,'6000',6000.0000),(222,53,37,NULL,'Black',NULL),(223,53,39,NULL,'4.8kg',NULL),(224,54,29,46,NULL,NULL),(225,54,32,NULL,'40',40.0000),(226,54,27,NULL,'5.3',NULL),(227,54,33,NULL,'7200',7200.0000),(228,54,37,NULL,'Olive Green',NULL),(229,54,39,NULL,'1.6kg',NULL),(230,55,29,47,NULL,NULL),(231,55,32,NULL,'50',50.0000),(232,55,27,NULL,'5.3',NULL),(233,55,37,NULL,'Walnut',NULL),(234,55,39,NULL,'3.2kg',NULL);
/*!40000 ALTER TABLE `product_specification_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_tag`
--

DROP TABLE IF EXISTS `product_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_tag` (
  `product_id` bigint(20) unsigned NOT NULL,
  `tag_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`tag_id`),
  KEY `product_tag_tag_id_index` (`tag_id`),
  CONSTRAINT `product_tag_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_tag`
--

LOCK TABLES `product_tag` WRITE;
/*!40000 ALTER TABLE `product_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_variants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `sku` varchar(64) NOT NULL,
  `barcode` varchar(64) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `combination_key` varchar(191) NOT NULL DEFAULT '',
  `price` decimal(12,2) NOT NULL,
  `sale_price` decimal(12,2) DEFAULT NULL,
  `sale_starts_at` timestamp NULL DEFAULT NULL,
  `sale_ends_at` timestamp NULL DEFAULT NULL,
  `cost_price` decimal(12,2) DEFAULT NULL,
  `stock_quantity` int(10) unsigned NOT NULL DEFAULT 0,
  `low_stock_threshold` int(10) unsigned DEFAULT NULL,
  `weight` decimal(8,3) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_variants_product_id_combination_key_unique` (`product_id`,`combination_key`),
  UNIQUE KEY `product_variants_sku_unique` (`sku`),
  UNIQUE KEY `product_variants_barcode_unique` (`barcode`),
  KEY `product_variants_product_id_is_active_index` (`product_id`,`is_active`),
  KEY `product_variants_stock_quantity_index` (`stock_quantity`),
  CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variants`
--

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
INSERT INTO `product_variants` VALUES (1,1,'GD-01001',NULL,NULL,'',3600.00,NULL,NULL,NULL,2880.00,23,NULL,NULL,1,1,0,'2026-11-08 07:33:34','2026-11-08 08:50:00',NULL),(2,2,'GD-01002',NULL,NULL,'',1450.00,NULL,NULL,NULL,1160.00,38,NULL,NULL,1,1,0,'2026-11-08 07:33:34','2026-11-08 08:38:57',NULL),(3,3,'GD-01003',NULL,NULL,'',2150.00,NULL,NULL,NULL,1720.00,18,NULL,NULL,1,1,0,'2026-11-08 07:33:34','2026-11-08 07:33:34',NULL),(4,4,'GD-01004',NULL,NULL,'',118800.00,102500.00,NULL,NULL,95040.00,3,NULL,NULL,1,1,0,'2026-11-08 07:33:34','2026-11-08 07:33:34',NULL),(5,5,'GD-01005',NULL,NULL,'',10499.00,9790.00,NULL,NULL,8399.20,15,NULL,NULL,1,1,0,'2026-11-08 07:33:34','2026-11-08 07:33:34',NULL),(6,6,'GD-01006',NULL,NULL,'',175000.00,165000.00,NULL,NULL,140000.00,4,NULL,NULL,1,1,0,'2026-11-08 07:33:34','2026-11-08 07:33:34',NULL),(7,7,'GD-01007',NULL,NULL,'',169999.00,158000.00,NULL,NULL,135999.20,5,NULL,NULL,1,1,0,'2026-11-08 07:33:34','2026-11-08 07:33:34',NULL),(8,8,'GD-01008',NULL,NULL,'',125000.00,118000.00,NULL,NULL,100000.00,6,NULL,NULL,1,1,0,'2026-11-08 07:33:35','2026-11-08 07:33:35',NULL),(9,9,'GD-01009',NULL,NULL,'',112000.00,98500.00,NULL,NULL,89600.00,3,NULL,NULL,1,1,0,'2026-11-08 07:33:35','2026-11-08 07:33:35',NULL),(10,10,'GD-01010',NULL,NULL,'',26999.00,23999.00,NULL,NULL,21599.20,12,NULL,NULL,1,1,0,'2026-11-08 07:33:35','2026-11-08 07:33:35',NULL),(11,11,'GD-01011',NULL,NULL,'',56000.00,52500.00,NULL,NULL,44800.00,4,NULL,NULL,1,1,0,'2026-11-08 07:33:35','2026-11-08 07:33:35',NULL),(12,12,'GD-01012',NULL,NULL,'',19900.00,17500.00,NULL,NULL,15920.00,7,NULL,NULL,1,1,0,'2026-11-08 07:33:35','2026-11-08 07:33:35',NULL),(13,13,'GD-01013',NULL,NULL,'',8999.00,7490.00,NULL,NULL,7199.20,10,NULL,NULL,1,1,0,'2026-11-08 07:33:35','2026-11-08 07:33:35',NULL),(14,14,'GD-01014',NULL,NULL,'',6500.00,5490.00,NULL,NULL,5200.00,14,NULL,NULL,1,1,0,'2026-11-08 07:33:35','2026-11-08 07:33:35',NULL),(15,15,'GD-01015',NULL,NULL,'',5990.00,4790.00,NULL,NULL,4792.00,16,NULL,NULL,1,1,0,'2026-11-08 07:33:35','2026-11-08 07:33:35',NULL),(16,16,'GD-01016',NULL,NULL,'',18500.00,16500.00,NULL,NULL,14800.00,8,NULL,NULL,1,1,0,'2026-11-08 07:33:35','2026-11-08 07:33:35',NULL),(17,17,'GD-01017',NULL,NULL,'',4290.00,3490.00,NULL,NULL,3432.00,20,NULL,NULL,1,1,0,'2026-11-08 07:33:35','2026-11-08 07:33:35',NULL),(18,18,'GD-01018',NULL,NULL,'',3990.00,3490.00,NULL,NULL,3192.00,18,NULL,NULL,1,1,0,'2026-11-08 07:33:35','2026-11-08 07:33:35',NULL),(19,19,'GD-01019',NULL,NULL,'',3990.00,3290.00,NULL,NULL,3192.00,22,NULL,NULL,1,1,0,'2026-11-08 07:33:35','2026-11-08 07:33:35',NULL),(20,20,'GD-01020',NULL,NULL,'',3200.00,2690.00,NULL,NULL,2560.00,25,NULL,NULL,1,1,0,'2026-11-08 07:33:35','2026-11-08 07:33:35',NULL),(21,21,'GD-PB-01',NULL,NULL,'',1890.00,1590.00,NULL,NULL,1512.00,29,NULL,NULL,1,1,0,'2026-11-08 07:33:35','2026-11-08 08:27:34',NULL),(22,22,'GD-PB-02',NULL,NULL,'',4490.00,3890.00,NULL,NULL,3592.00,15,NULL,NULL,1,1,0,'2026-11-08 07:33:35','2026-11-08 07:33:35',NULL),(23,23,'GD-PB-03',NULL,NULL,'',8990.00,7990.00,NULL,NULL,7192.00,8,NULL,NULL,1,1,0,'2026-11-08 07:33:35','2026-11-08 07:33:35',NULL),(24,24,'GD-PB-04',NULL,NULL,'',3290.00,2790.00,NULL,NULL,2632.00,18,NULL,NULL,1,1,0,'2026-11-08 07:33:35','2026-11-08 07:33:35',NULL),(25,25,'GD-PB-05',NULL,NULL,'',2990.00,2490.00,NULL,NULL,2392.00,20,NULL,NULL,1,1,0,'2026-11-08 07:33:36','2026-11-08 07:33:36',NULL),(26,26,'GD-PB-06',NULL,NULL,'',1990.00,1690.00,NULL,NULL,1592.00,35,NULL,NULL,1,1,0,'2026-11-08 07:33:36','2026-11-08 07:33:36',NULL),(27,27,'GD-PB-07',NULL,NULL,'',2790.00,2390.00,NULL,NULL,2232.00,25,NULL,NULL,1,1,0,'2026-11-08 07:33:36','2026-11-08 07:33:36',NULL),(28,28,'GD-PB-08',NULL,NULL,'',9990.00,8790.00,NULL,NULL,7992.00,6,NULL,NULL,1,1,0,'2026-11-08 07:33:36','2026-11-08 07:33:36',NULL),(29,29,'GD-PB-09',NULL,NULL,'',7490.00,6590.00,NULL,NULL,5992.00,10,NULL,NULL,1,1,0,'2026-11-08 07:33:36','2026-11-08 07:33:36',NULL),(30,30,'GD-PB-10',NULL,NULL,'',3490.00,2890.00,NULL,NULL,2792.00,16,NULL,NULL,1,1,0,'2026-11-08 07:33:36','2026-11-08 07:33:36',NULL),(31,31,'GD-CC-01',NULL,NULL,'',690.00,550.00,NULL,NULL,552.00,60,NULL,NULL,1,1,0,'2026-11-08 07:33:36','2026-11-08 07:33:36',NULL),(32,32,'GD-CC-02',NULL,NULL,'',450.00,350.00,NULL,NULL,360.00,50,NULL,NULL,1,1,0,'2026-11-08 07:33:36','2026-11-08 07:33:36',NULL),(33,33,'GD-CC-03',NULL,NULL,'',350.00,290.00,NULL,NULL,280.00,80,NULL,NULL,1,1,0,'2026-11-08 07:33:36','2026-11-08 07:33:36',NULL),(34,34,'GD-CC-04',NULL,NULL,'',1290.00,990.00,NULL,NULL,1032.00,30,NULL,NULL,1,1,0,'2026-11-08 07:33:36','2026-11-08 07:33:36',NULL),(35,35,'GD-CC-05',NULL,NULL,'',250.00,199.00,NULL,NULL,200.00,80,NULL,NULL,1,1,0,'2026-11-08 07:33:36','2026-11-08 07:33:36',NULL),(36,36,'GD-CC-06',NULL,NULL,'',1590.00,1290.00,NULL,NULL,1272.00,35,NULL,NULL,1,1,0,'2026-11-08 07:33:36','2026-11-08 07:33:36',NULL),(37,37,'GD-CC-07',NULL,NULL,'',990.00,790.00,NULL,NULL,792.00,45,NULL,NULL,1,1,0,'2026-11-08 07:33:36','2026-11-08 07:33:36',NULL),(38,38,'GD-CC-08',NULL,NULL,'',1890.00,1490.00,NULL,NULL,1512.00,20,NULL,NULL,1,1,0,'2026-11-08 07:33:36','2026-11-08 07:33:36',NULL),(39,39,'GD-CC-09',NULL,NULL,'',2490.00,1990.00,NULL,NULL,1992.00,15,NULL,NULL,1,1,0,'2026-11-08 07:33:36','2026-11-08 07:33:37',NULL),(40,40,'GD-CC-10',NULL,NULL,'',590.00,450.00,NULL,NULL,472.00,60,NULL,NULL,1,1,0,'2026-11-08 07:33:37','2026-11-08 07:33:37',NULL),(41,41,'GD-CB-01',NULL,NULL,'',144490.00,132990.00,NULL,NULL,115592.00,3,NULL,NULL,1,1,0,'2026-11-08 07:33:37','2026-11-08 07:33:37',NULL),(42,42,'GD-CB-02',NULL,NULL,'',175179.00,162990.00,NULL,NULL,140143.20,3,NULL,NULL,1,1,0,'2026-11-08 07:33:37','2026-11-08 07:33:37',NULL),(43,43,'GD-CB-03',NULL,NULL,'',5120.00,3990.00,NULL,NULL,4096.00,15,NULL,NULL,1,1,0,'2026-11-08 07:33:37','2026-11-08 07:33:37',NULL),(44,44,'GD-CB-04',NULL,NULL,'',24190.00,19490.00,NULL,NULL,19352.00,6,NULL,NULL,1,1,0,'2026-11-08 07:33:37','2026-11-08 07:33:37',NULL),(45,45,'GD-CB-05',NULL,NULL,'',5070.00,3990.00,NULL,NULL,4056.00,10,NULL,NULL,1,1,0,'2026-11-08 07:33:37','2026-11-08 07:33:37',NULL),(46,46,'GD-PH-A56-1',NULL,'8GB / 128GB / Awesome Graphite','1-6-10',52999.00,NULL,NULL,NULL,45049.15,8,NULL,NULL,1,1,1,'2026-11-08 07:33:37','2026-11-08 07:33:37',NULL),(47,46,'GD-PH-A56-2',NULL,'8GB / 256GB / Awesome Graphite','1-7-10',57999.00,55999.00,NULL,NULL,49299.15,5,NULL,NULL,0,1,2,'2026-11-08 07:33:37','2026-11-08 07:33:37',NULL),(48,46,'GD-PH-A56-3',NULL,'8GB / 256GB / Awesome Lightgray','1-7-11',57999.00,55999.00,NULL,NULL,49299.15,3,NULL,NULL,0,1,3,'2026-11-08 07:33:37','2026-11-08 07:33:37',NULL),(49,46,'GD-PH-A56-4',NULL,'12GB / 256GB / Awesome Graphite','2-7-10',62999.00,NULL,NULL,NULL,53549.15,0,NULL,NULL,0,1,4,'2026-11-08 07:33:37','2026-11-08 07:33:37',NULL),(50,47,'GD-SP-01',NULL,NULL,'',1990.00,1590.00,NULL,NULL,1592.00,30,NULL,NULL,1,1,0,'2026-11-08 07:52:57','2026-11-08 07:52:57',NULL),(51,48,'GD-SP-02',NULL,NULL,'',3490.00,2890.00,NULL,NULL,2792.00,15,NULL,NULL,1,1,0,'2026-11-08 07:52:57','2026-11-08 07:52:57',NULL),(52,49,'GD-SP-03',NULL,NULL,'',2990.00,2490.00,NULL,NULL,2392.00,25,NULL,NULL,1,1,0,'2026-11-08 07:52:57','2026-11-08 07:52:57',NULL),(53,50,'GD-SP-04',NULL,NULL,'',4990.00,4290.00,NULL,NULL,3992.00,12,NULL,NULL,1,1,0,'2026-11-08 07:52:57','2026-11-08 07:52:57',NULL),(54,51,'GD-SP-05',NULL,NULL,'',5990.00,4990.00,NULL,NULL,4792.00,10,NULL,NULL,1,1,0,'2026-11-08 07:52:57','2026-11-08 07:52:57',NULL),(55,52,'GD-SP-06',NULL,NULL,'',3990.00,3390.00,NULL,NULL,3192.00,18,NULL,NULL,1,1,0,'2026-11-08 07:52:57','2026-11-08 07:52:57',NULL),(56,53,'GD-SP-07',NULL,NULL,'',9990.00,8490.00,NULL,NULL,7992.00,8,NULL,NULL,1,1,0,'2026-11-08 07:52:57','2026-11-08 07:52:57',NULL),(57,54,'GD-SP-08',NULL,NULL,'',5490.00,4690.00,NULL,NULL,4392.00,14,NULL,NULL,1,1,0,'2026-11-08 07:52:57','2026-11-08 07:52:57',NULL),(58,55,'GD-SP-09',NULL,NULL,'',8490.00,7490.00,NULL,NULL,6792.00,6,NULL,NULL,1,1,0,'2026-11-08 07:52:57','2026-11-08 07:52:57',NULL);
/*!40000 ALTER TABLE `product_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL DEFAULT 'simple',
  `name` varchar(255) NOT NULL,
  `name_bn` varchar(255) DEFAULT NULL,
  `slug` varchar(191) NOT NULL,
  `sku` varchar(64) NOT NULL,
  `barcode` varchar(64) DEFAULT NULL,
  `model` varchar(120) DEFAULT NULL,
  `brand_id` bigint(20) unsigned DEFAULT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `short_description` text DEFAULT NULL,
  `short_description_bn` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `description_bn` longtext DEFAULT NULL,
  `faqs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`faqs`)),
  `faqs_bn` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`faqs_bn`)),
  `warranty` varchar(100) DEFAULT NULL,
  `warranty_bn` varchar(100) DEFAULT NULL,
  `offer_text` varchar(255) DEFAULT NULL,
  `offer_text_bn` varchar(255) DEFAULT NULL,
  `condition` varchar(20) NOT NULL DEFAULT 'new',
  `weight` decimal(8,3) DEFAULT NULL COMMENT 'kg',
  `length` decimal(8,2) DEFAULT NULL COMMENT 'cm',
  `width` decimal(8,2) DEFAULT NULL COMMENT 'cm',
  `height` decimal(8,2) DEFAULT NULL COMMENT 'cm',
  `manage_stock` tinyint(1) NOT NULL DEFAULT 1,
  `stock_status` varchar(20) NOT NULL DEFAULT 'in_stock',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_latest` tinyint(1) NOT NULL DEFAULT 0,
  `is_best_seller` tinyint(1) NOT NULL DEFAULT 0,
  `is_trending` tinyint(1) NOT NULL DEFAULT 0,
  `is_combo` tinyint(1) NOT NULL DEFAULT 0,
  `flash_sale_eligible` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `min_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `max_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `regular_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_percent` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `total_stock` int(10) unsigned NOT NULL DEFAULT 0,
  `sales_count` int(10) unsigned NOT NULL DEFAULT 0,
  `view_count` int(10) unsigned NOT NULL DEFAULT 0,
  `rating_avg` decimal(3,2) NOT NULL DEFAULT 0.00,
  `rating_count` int(10) unsigned NOT NULL DEFAULT 0,
  `search_keywords` varchar(500) DEFAULT NULL,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_title_bn` varchar(255) DEFAULT NULL,
  `seo_description` varchar(500) DEFAULT NULL,
  `seo_description_bn` varchar(500) DEFAULT NULL,
  `canonical_url` varchar(500) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  UNIQUE KEY `products_barcode_unique` (`barcode`),
  KEY `products_created_by_foreign` (`created_by`),
  KEY `products_status_published_at_index` (`status`,`published_at`),
  KEY `products_category_id_status_min_price_index` (`category_id`,`status`,`min_price`),
  KEY `products_brand_id_status_index` (`brand_id`,`status`),
  KEY `products_status_is_featured_index` (`status`,`is_featured`),
  KEY `products_status_sales_count_index` (`status`,`sales_count`),
  KEY `products_is_trending_status_index` (`is_trending`,`status`),
  KEY `products_is_combo_status_index` (`is_combo`,`status`),
  FULLTEXT KEY `products_name_sku_model_search_keywords_fulltext` (`name`,`sku`,`model`,`search_keywords`),
  CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `products_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'simple','Redragon H320 Lamia RGB Virtual 7.1 Surround Sound Gaming Headset','Redragon H320 Lamia RGB ভার্চুয়াল 7.1 সারাউন্ড সাউন্ড গেমিং হেডসেট','redragon-h320-lamia-rgb-virtual-71-surround-sound-gaming-headset','GD-01001',NULL,'H320',15,4,'Redragon H320 Lamia RGB Virtual 7.1 Surround Sound Gaming Headset. 1 Year official warranty.','Redragon H320 Lamia RGB ভার্চুয়াল 7.1 সারাউন্ড সাউন্ড গেমিং হেডসেট। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-09-24 07:33:34',3600.00,3600.00,3600.00,0,23,0,7,0.00,0,'H320',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:34','2026-11-09 00:57:27',NULL),(2,'simple','Redragon H130 Pelias Wired Gaming Headphone','Redragon H130 Pelias তারযুক্ত গেমিং হেডফোন','redragon-h130-pelias-wired-gaming-headphone','GD-01002',NULL,'H130',15,4,'Redragon H130 Pelias Wired Gaming Headphone. 6 Months official warranty.','Redragon H130 Pelias তারযুক্ত গেমিং হেডফোন। ৬ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'6 Months','৬ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,1,0,0,1,'active','2026-09-25 07:33:34',1450.00,1450.00,1450.00,0,38,0,12,0.00,0,'H130',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:34','2026-11-09 00:57:27',NULL),(3,'simple','Redragon H231 Scream Wired RGB Gaming Headphone (White)','Redragon H231 Scream তারযুক্ত RGB গেমিং হেডফোন (সাদা)','redragon-h231-scream-wired-rgb-gaming-headphone-white','GD-01003',NULL,'H231',15,4,'Redragon H231 Scream Wired RGB Gaming Headphone (White). 1 Year official warranty.','Redragon H231 Scream তারযুক্ত RGB গেমিং হেডফোন (সাদা)। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-09-26 07:33:34',2150.00,2150.00,2150.00,0,18,0,8,0.00,0,'H231',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:34','2026-11-09 00:57:27',NULL),(4,'simple','JBL PartyBox 720 800W Bluetooth Party Speaker','JBL PartyBox 720 800W ব্লুটুথ পার্টি স্পিকার','jbl-partybox-720-800w-bluetooth-party-speaker','GD-01004',NULL,'PartyBox 720',7,5,'JBL PartyBox 720 800W Bluetooth Party Speaker. 1 Year official warranty.','JBL PartyBox 720 800W ব্লুটুথ পার্টি স্পিকার। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,1,0,1,0,1,'active','2026-09-27 07:33:34',102500.00,102500.00,118800.00,13,3,0,9,5.00,1,'PartyBox 720',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:34','2026-11-09 02:35:24',NULL),(5,'simple','KOSPET TANK T3 ULTRA 2 1.43 Inch AMOLED GPS BT Calling Smartwatch','KOSPET TANK T3 ULTRA 2 1.43 ইঞ্চি AMOLED GPS ব্লুটুথ কলিং স্মার্টওয়াচ','kospet-tank-t3-ultra-2-143-inch-amoled-gps-bt-calling-smartwatch','GD-01005',NULL,'TANK T3 ULTRA 2',10,1,'KOSPET TANK T3 ULTRA 2 1.43 Inch AMOLED GPS BT Calling Smartwatch. 1 Year official warranty.','KOSPET TANK T3 ULTRA 2 1.43 ইঞ্চি AMOLED GPS ব্লুটুথ কলিং স্মার্টওয়াচ। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,1,0,1,0,1,'active','2026-09-28 07:33:34',9790.00,9790.00,10499.00,6,15,0,11,0.00,0,'TANK T3 ULTRA 2',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:34','2026-11-09 00:57:27',NULL),(6,'simple','Apple iPhone 17 Pro 256GB Cosmic Orange','Apple iPhone 17 Pro 256GB কসমিক অরেঞ্জ','apple-iphone-17-pro-256gb-cosmic-orange','GD-01006',NULL,'iPhone 17 Pro',1,8,'Apple iPhone 17 Pro 256GB Cosmic Orange. 1 Year official warranty.','Apple iPhone 17 Pro 256GB কসমিক অরেঞ্জ। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,1,0,1,'active','2026-09-29 07:33:34',165000.00,165000.00,175000.00,5,4,0,7,0.00,0,'iPhone 17 Pro',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:34','2026-11-09 00:57:27',NULL),(7,'simple','Samsung Galaxy S26 Ultra 12GB/256GB Cobalt Violet','Samsung Galaxy S26 Ultra 12GB/256GB কোবাল্ট ভায়োলেট','samsung-galaxy-s26-ultra-12gb256gb-cobalt-violet','GD-01007',NULL,'Galaxy S26 Ultra',2,8,'Samsung Galaxy S26 Ultra 12GB/256GB Cobalt Violet. 1 Year official warranty.','Samsung Galaxy S26 Ultra 12GB/256GB কোবাল্ট ভায়োলেট। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,1,0,1,'active','2026-09-30 07:33:34',158000.00,158000.00,169999.00,7,5,0,1,0.00,0,'Galaxy S26 Ultra',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:34','2026-11-09 00:57:27',NULL),(8,'simple','Apple iPhone 17 256GB Lavender','Apple iPhone 17 256GB ল্যাভেন্ডার','apple-iphone-17-256gb-lavender','GD-01008',NULL,'iPhone 17',1,8,'Apple iPhone 17 256GB Lavender. 1 Year official warranty.','Apple iPhone 17 256GB ল্যাভেন্ডার। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-10-01 07:33:34',118000.00,118000.00,125000.00,5,6,0,1,0.00,0,'iPhone 17',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:34','2026-11-09 00:57:27',NULL),(9,'simple','Google Pixel 9 Pro 16GB/256GB Obsidian','Google Pixel 9 Pro 16GB/256GB অবসিডিয়ান','google-pixel-9-pro-16gb256gb-obsidian','GD-01009',NULL,'Pixel 9 Pro',3,8,'Google Pixel 9 Pro 16GB/256GB Obsidian. 1 Year official warranty.','Google Pixel 9 Pro 16GB/256GB অবসিডিয়ান। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-10-02 07:33:35',98500.00,98500.00,112000.00,12,3,0,4,0.00,0,'Pixel 9 Pro',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:35','2026-11-09 00:57:27',NULL),(10,'simple','HONOR X7d 8GB/256GB Smartphone','HONOR X7d 8GB/256GB স্মার্টফোন','honor-x7d-8gb256gb-smartphone','GD-01010',NULL,'X7d',4,8,'HONOR X7d 8GB/256GB Smartphone. 1 Year official warranty.','HONOR X7d 8GB/256GB স্মার্টফোন। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,1,0,0,1,'active','2026-10-03 07:33:35',23999.00,23999.00,26999.00,11,12,0,7,0.00,0,'X7d',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:35','2026-11-09 00:57:27',NULL),(11,'simple','Apple Watch Series 11 GPS 46mm Aluminum Case with Sport Band','Apple Watch Series 11 GPS 46mm অ্যালুমিনিয়াম কেস ও স্পোর্ট ব্যান্ড','apple-watch-series-11-gps-46mm-aluminum-case-with-sport-band','GD-01011',NULL,'Watch Series 11',1,1,'Apple Watch Series 11 GPS 46mm Aluminum Case with Sport Band. 1 Year official warranty.','Apple Watch Series 11 GPS 46mm অ্যালুমিনিয়াম কেস ও স্পোর্ট ব্যান্ড। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-10-04 07:33:35',52500.00,52500.00,56000.00,6,4,0,2,0.00,0,'Watch Series 11',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:35','2026-11-09 00:57:27',NULL),(12,'simple','Amazfit Active 2 Square AMOLED GPS Smart Watch','Amazfit Active 2 Square AMOLED GPS স্মার্ট ওয়াচ','amazfit-active-2-square-amoled-gps-smart-watch','GD-01012',NULL,'Active 2 Square',8,1,'Amazfit Active 2 Square AMOLED GPS Smart Watch. 1 Year official warranty.','Amazfit Active 2 Square AMOLED GPS স্মার্ট ওয়াচ। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-10-05 07:33:35',17500.00,17500.00,19900.00,12,7,0,2,0.00,0,'Active 2 Square',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:35','2026-11-09 00:57:27',NULL),(13,'simple','CMF by Nothing Watch Pro 2 AMOLED Bluetooth Calling Smart Watch','CMF by Nothing Watch Pro 2 AMOLED ব্লুটুথ কলিং স্মার্ট ওয়াচ','cmf-by-nothing-watch-pro-2-amoled-bluetooth-calling-smart-watch','GD-01013',NULL,'Watch Pro 2',9,1,'CMF by Nothing Watch Pro 2 AMOLED Bluetooth Calling Smart Watch. 1 Year official warranty.','CMF by Nothing Watch Pro 2 AMOLED ব্লুটুথ কলিং স্মার্ট ওয়াচ। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,1,0,1,'active','2026-10-06 07:33:35',7490.00,7490.00,8999.00,16,10,0,10,0.00,0,'Watch Pro 2',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:35','2026-11-09 00:57:27',NULL),(14,'simple','Haylou Solar Pro AMOLED Bluetooth Calling Smart Watch','Haylou Solar Pro AMOLED ব্লুটুথ কলিং স্মার্ট ওয়াচ','haylou-solar-pro-amoled-bluetooth-calling-smart-watch','GD-01014',NULL,'Solar Pro',11,1,'Haylou Solar Pro AMOLED Bluetooth Calling Smart Watch. 1 Year official warranty.','Haylou Solar Pro AMOLED ব্লুটুথ কলিং স্মার্ট ওয়াচ। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,1,0,0,1,'active','2026-10-07 07:33:35',5490.00,5490.00,6500.00,15,14,0,7,0.00,0,'Solar Pro',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:35','2026-11-09 00:57:27',NULL),(15,'simple','Kieslect KS3 AMOLED Bluetooth Calling Smart Watch','Kieslect KS3 AMOLED ব্লুটুথ কলিং স্মার্ট ওয়াচ','kieslect-ks3-amoled-bluetooth-calling-smart-watch','GD-01015',NULL,'KS3',12,1,'Kieslect KS3 AMOLED Bluetooth Calling Smart Watch. 1 Year official warranty.','Kieslect KS3 AMOLED ব্লুটুথ কলিং স্মার্ট ওয়াচ। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-10-08 07:33:35',4790.00,4790.00,5990.00,20,16,0,7,0.00,0,'KS3',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:35','2026-11-09 00:57:27',NULL),(16,'simple','Apple AirPods 4 Wireless Earbuds with USB-C Charging Case','Apple AirPods 4 ওয়্যারলেস ইয়ারবাডস, USB-C চার্জিং কেসসহ','apple-airpods-4-wireless-earbuds-with-usb-c-charging-case','GD-01016',NULL,'AirPods 4',1,3,'Apple AirPods 4 Wireless Earbuds with USB-C Charging Case. 1 Year official warranty.','Apple AirPods 4 ওয়্যারলেস ইয়ারবাডস, USB-C চার্জিং কেসসহ। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,1,0,1,'active','2026-10-09 07:33:35',16500.00,16500.00,18500.00,10,8,0,3,0.00,0,'AirPods 4',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:35','2026-11-09 00:57:27',NULL),(17,'simple','SoundPEATS Life ANC Wireless Earbuds','SoundPEATS Life ANC ওয়্যারলেস ইয়ারবাডস','soundpeats-life-anc-wireless-earbuds','GD-01017',NULL,'Life ANC',13,3,'SoundPEATS Life ANC Wireless Earbuds. 6 Months official warranty.','SoundPEATS Life ANC ওয়্যারলেস ইয়ারবাডস। ৬ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'6 Months','৬ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-10-10 07:33:35',3490.00,3490.00,4290.00,18,20,0,7,0.00,0,'Life ANC',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:35','2026-11-09 00:57:27',NULL),(18,'simple','Xiaomi Redmi Buds 6 ANC TWS Earbuds','Xiaomi Redmi Buds 6 ANC TWS ইয়ারবাডস','xiaomi-redmi-buds-6-anc-tws-earbuds','GD-01018',NULL,'Redmi Buds 6',5,3,'Xiaomi Redmi Buds 6 ANC TWS Earbuds. 6 Months official warranty.','Xiaomi Redmi Buds 6 ANC TWS ইয়ারবাডস। ৬ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'6 Months','৬ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,1,0,0,1,'active','2026-10-11 07:33:35',3490.00,3490.00,3990.00,12,18,0,6,0.00,0,'Redmi Buds 6',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:35','2026-11-09 00:57:27',NULL),(19,'simple','Anker Soundcore R50i NC Wireless Earbuds','Anker Soundcore R50i NC ওয়্যারলেস ইয়ারবাডস','anker-soundcore-r50i-nc-wireless-earbuds','GD-01019',NULL,'Soundcore R50i NC',6,3,'Anker Soundcore R50i NC Wireless Earbuds. 18 Months official warranty.','Anker Soundcore R50i NC ওয়্যারলেস ইয়ারবাডস। ১৮ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'18 Months','১৮ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-10-12 07:33:35',3290.00,3290.00,3990.00,17,22,0,6,0.00,0,'Soundcore R50i NC',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:35','2026-11-09 00:57:27',NULL),(20,'simple','QCY AilyBuds Pro+ ANC Wireless Earbuds','QCY AilyBuds Pro+ ANC ওয়্যারলেস ইয়ারবাডস','qcy-ailybuds-pro-anc-wireless-earbuds','GD-01020',NULL,'AilyBuds Pro+',14,3,'QCY AilyBuds Pro+ ANC Wireless Earbuds. 6 Months official warranty.','QCY AilyBuds Pro+ ANC ওয়্যারলেস ইয়ারবাডস। ৬ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'6 Months','৬ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-10-13 07:33:35',2690.00,2690.00,3200.00,15,25,0,4,0.00,0,'AilyBuds Pro+',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:35','2026-11-09 00:57:27',NULL),(21,'simple','Compact Ivory 10000mAh 20W Fast Charging Power Bank','কমপ্যাক্ট আইভরি 10000mAh 20W ফাস্ট চার্জিং পাওয়ার ব্যাংক','compact-ivory-10000mah-20w-fast-charging-power-bank','GD-PB-01',NULL,'PB-10K20',NULL,6,'Compact Ivory 10000mAh 20W Fast Charging Power Bank. 6 Months official warranty.','কমপ্যাক্ট আইভরি 10000mAh 20W ফাস্ট চার্জিং পাওয়ার ব্যাংক। ৬ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'6 Months','৬ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,1,0,0,1,'active','2026-10-14 07:33:35',1590.00,1590.00,1890.00,15,29,0,3,0.00,0,'powerbank, portable charger, 10000mAh, ivory',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:35','2026-11-09 00:57:27',NULL),(22,'simple','Premium Brushed Silver 20000mAh 65W PD Power Bank','প্রিমিয়াম ব্রাশড সিলভার 20000mAh 65W PD পাওয়ার ব্যাংক','premium-brushed-silver-20000mah-65w-pd-power-bank','GD-PB-02',NULL,'PB-20K65',NULL,6,'Premium Brushed Silver 20000mAh 65W PD Power Bank. 1 Year official warranty.','প্রিমিয়াম ব্রাশড সিলভার 20000mAh 65W PD পাওয়ার ব্যাংক। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-10-15 07:33:35',3890.00,3890.00,4490.00,13,15,0,3,0.00,0,'powerbank, portable charger, 20000mAh, 65W, silver, aluminium',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:35','2026-11-09 00:57:27',NULL),(23,'simple','Premium Graphite 30000mAh 140W Laptop Power Bank','প্রিমিয়াম গ্রাফাইট 30000mAh 140W ল্যাপটপ পাওয়ার ব্যাংক','premium-graphite-30000mah-140w-laptop-power-bank','GD-PB-03',NULL,'PB-30K140',NULL,6,'Premium Graphite 30000mAh 140W Laptop Power Bank. 1 Year official warranty.','প্রিমিয়াম গ্রাফাইট 30000mAh 140W ল্যাপটপ পাওয়ার ব্যাংক। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',1,0,0,1,0,1,'active','2026-10-16 07:33:35',7990.00,7990.00,8990.00,11,8,0,10,0.00,0,'powerbank, laptop power bank, macbook charger, 30000mAh, 140W, digital display',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:35','2026-11-09 00:57:27',NULL),(24,'simple','Premium Forest Green 20000mAh 30W Power Bank','প্রিমিয়াম ফরেস্ট গ্রিন 20000mAh 30W পাওয়ার ব্যাংক','premium-forest-green-20000mah-30w-power-bank','GD-PB-04',NULL,'PB-20K30',NULL,6,'Premium Forest Green 20000mAh 30W Power Bank. 1 Year official warranty.','প্রিমিয়াম ফরেস্ট গ্রিন 20000mAh 30W পাওয়ার ব্যাংক। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-10-17 07:33:35',2790.00,2790.00,3290.00,15,18,0,2,0.00,0,'powerbank, portable charger, 20000mAh, green',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:35','2026-11-09 00:57:27',NULL),(25,'simple','Warm White 5000mAh Magnetic Wireless Power Bank','ওয়ার্ম হোয়াইট 5000mAh ম্যাগনেটিক ওয়্যারলেস পাওয়ার ব্যাংক','warm-white-5000mah-magnetic-wireless-power-bank','GD-PB-05',NULL,'PB-5KMW',NULL,6,'Warm White 5000mAh Magnetic Wireless Power Bank. 6 Months official warranty.','ওয়ার্ম হোয়াইট 5000mAh ম্যাগনেটিক ওয়্যারলেস পাওয়ার ব্যাংক। ৬ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'6 Months','৬ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-10-18 07:33:36',2490.00,2490.00,2990.00,16,20,0,3,0.00,0,'powerbank, magsafe, magnetic, wireless charger, iphone, 5000mAh',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:36','2026-11-09 00:57:27',NULL),(26,'simple','Slim Matte Charcoal 10000mAh 20W Power Bank','স্লিম ম্যাট চারকোল 10000mAh 20W পাওয়ার ব্যাংক','slim-matte-charcoal-10000mah-20w-power-bank','GD-PB-06',NULL,'PB-10KS',NULL,6,'Slim Matte Charcoal 10000mAh 20W Power Bank. 6 Months official warranty.','স্লিম ম্যাট চারকোল 10000mAh 20W পাওয়ার ব্যাংক। ৬ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'6 Months','৬ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-10-19 07:33:36',1690.00,1690.00,1990.00,15,35,0,1,0.00,0,'powerbank, slim, portable charger, 10000mAh, black',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:36','2026-11-09 00:57:27',NULL),(27,'simple','Deep Navy 20000mAh 22.5W Fast Charging Power Bank','ডিপ নেভি 20000mAh 22.5W ফাস্ট চার্জিং পাওয়ার ব্যাংক','deep-navy-20000mah-225w-fast-charging-power-bank','GD-PB-07',NULL,'PB-20K22',NULL,6,'Deep Navy 20000mAh 22.5W Fast Charging Power Bank. 1 Year official warranty.','ডিপ নেভি 20000mAh 22.5W ফাস্ট চার্জিং পাওয়ার ব্যাংক। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,1,0,0,1,'active','2026-10-20 07:33:36',2390.00,2390.00,2790.00,14,25,0,2,0.00,0,'powerbank, portable charger, 20000mAh, blue',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:36','2026-11-09 00:57:27',NULL),(28,'simple','Premium Black 40000mAh 100W High-Capacity Power Bank','প্রিমিয়াম ব্ল্যাক 40000mAh 100W হাই-ক্যাপাসিটি পাওয়ার ব্যাংক','premium-black-40000mah-100w-high-capacity-power-bank','GD-PB-08',NULL,'PB-40K100',NULL,6,'Premium Black 40000mAh 100W High-Capacity Power Bank. 1 Year official warranty.','প্রিমিয়াম ব্ল্যাক 40000mAh 100W হাই-ক্যাপাসিটি পাওয়ার ব্যাংক। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',1,0,0,0,0,1,'active','2026-10-21 07:33:36',8790.00,8790.00,9990.00,12,6,0,8,0.00,0,'powerbank, high capacity, laptop power bank, 40000mAh, 100W',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:36','2026-11-09 00:57:27',NULL),(29,'simple','Premium Space Gray 25000mAh 100W Laptop Power Bank','প্রিমিয়াম স্পেস গ্রে 25000mAh 100W ল্যাপটপ পাওয়ার ব্যাংক','premium-space-gray-25000mah-100w-laptop-power-bank','GD-PB-09',NULL,'PB-25K100',NULL,6,'Premium Space Gray 25000mAh 100W Laptop Power Bank. 1 Year official warranty.','প্রিমিয়াম স্পেস গ্রে 25000mAh 100W ল্যাপটপ পাওয়ার ব্যাংক। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-10-22 07:33:36',6590.00,6590.00,7490.00,12,10,0,4,0.00,0,'powerbank, laptop power bank, macbook charger, 25000mAh, 100W, gray',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:36','2026-11-09 00:57:27',NULL),(30,'simple','Lavender 10000mAh Magnetic Wireless Power Bank','ল্যাভেন্ডার 10000mAh ম্যাগনেটিক ওয়্যারলেস পাওয়ার ব্যাংক','lavender-10000mah-magnetic-wireless-power-bank','GD-PB-10',NULL,'PB-10KMW',NULL,6,'Lavender 10000mAh Magnetic Wireless Power Bank. 1 Year official warranty.','ল্যাভেন্ডার 10000mAh ম্যাগনেটিক ওয়্যারলেস পাওয়ার ব্যাংক। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,1,0,1,'active','2026-10-23 07:33:36',2890.00,2890.00,3490.00,17,16,0,6,0.00,0,'powerbank, magsafe, magnetic, wireless charger, iphone, 10000mAh, purple',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:36','2026-11-09 00:57:27',NULL),(31,'simple','Braided USB-C to USB-C 60W Fast Charging Cable 1m','ব্রেইডেড USB-C টু USB-C 60W ফাস্ট চার্জিং ক্যাবল 1m','braided-usb-c-to-usb-c-60w-fast-charging-cable-1m','GD-CC-01',NULL,'CB-CC60',NULL,7,'Braided USB-C to USB-C 60W Fast Charging Cable 1m. 6 Months official warranty.','ব্রেইডেড USB-C টু USB-C 60W ফাস্ট চার্জিং ক্যাবল 1m। ৬ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'6 Months','৬ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-10-24 07:33:36',550.00,550.00,690.00,20,60,0,1,0.00,0,'type c cable, usb c cable, charging cable, data cable, braided, 60W',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:36','2026-11-09 00:57:27',NULL),(32,'simple','Brushed Silver USB-C to USB-A 3.0 OTG Adapter','ব্রাশড সিলভার USB-C টু USB-A 3.0 OTG অ্যাডাপ্টার','brushed-silver-usb-c-to-usb-a-30-otg-adapter','GD-CC-02',NULL,'AD-OTG3',NULL,7,'Brushed Silver USB-C to USB-A 3.0 OTG Adapter. 3 Months official warranty.','ব্রাশড সিলভার USB-C টু USB-A 3.0 OTG অ্যাডাপ্টার। ৩ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'3 Months','৩ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-10-25 07:33:36',350.00,350.00,450.00,22,50,0,1,0.00,0,'otg, type c adapter, usb c to usb, converter, pendrive adapter',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:36','2026-11-09 00:57:27',NULL),(33,'simple','White USB-A to USB-C 3A Fast Charging Cable 1m','সাদা USB-A টু USB-C 3A ফাস্ট চার্জিং ক্যাবল 1m','white-usb-a-to-usb-c-3a-fast-charging-cable-1m','GD-CC-03',NULL,'CB-AC3A',NULL,7,'White USB-A to USB-C 3A Fast Charging Cable 1m. 3 Months official warranty.','সাদা USB-A টু USB-C 3A ফাস্ট চার্জিং ক্যাবল 1m। ৩ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'3 Months','৩ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,1,0,0,1,'active','2026-10-26 07:33:36',290.00,290.00,350.00,17,80,0,4,0.00,0,'type c cable, usb c cable, charging cable, data cable',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:36','2026-11-09 00:57:27',NULL),(34,'simple','Compact Black 38W Dual-Port Car Charger','কমপ্যাক্ট কালো 38W ডুয়াল-পোর্ট কার চার্জার','compact-black-38w-dual-port-car-charger','GD-CC-04',NULL,'CC-38D',NULL,7,'Compact Black 38W Dual-Port Car Charger. 6 Months official warranty.','কমপ্যাক্ট কালো 38W ডুয়াল-পোর্ট কার চার্জার। ৬ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'6 Months','৬ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-10-27 07:33:36',990.00,990.00,1290.00,23,30,0,1,0.00,0,'car charger, car adapter, fast charger, usb c, 38W',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:36','2026-11-09 00:57:27',NULL),(35,'simple','Matte Black USB-A to Micro-USB 2.4A Charging Cable 1m','ম্যাট কালো USB-A টু Micro-USB 2.4A চার্জিং ক্যাবল 1m','matte-black-usb-a-to-micro-usb-24a-charging-cable-1m','GD-CC-05',NULL,'CB-AM24',NULL,7,'Matte Black USB-A to Micro-USB 2.4A Charging Cable 1m. 3 Months official warranty.','ম্যাট কালো USB-A টু Micro-USB 2.4A চার্জিং ক্যাবল 1m। ৩ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'3 Months','৩ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-10-28 07:33:36',199.00,199.00,250.00,20,80,0,1,0.00,0,'micro usb cable, android cable, charging cable, data cable',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:36','2026-11-09 00:57:27',NULL),(36,'simple','Matte Charcoal 35W Dual-Port Wall Charger','ম্যাট চারকোল 35W ডুয়াল-পোর্ট ওয়াল চার্জার','matte-charcoal-35w-dual-port-wall-charger','GD-CC-06',NULL,'WC-35D',NULL,7,'Matte Charcoal 35W Dual-Port Wall Charger. 6 Months official warranty.','ম্যাট চারকোল 35W ডুয়াল-পোর্ট ওয়াল চার্জার। ৬ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'6 Months','৬ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,1,0,1,'active','2026-10-29 07:33:36',1290.00,1290.00,1590.00,18,35,0,4,0.00,0,'wall charger, adapter, fast charger, usb c charger, 35W',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:36','2026-11-09 00:57:27',NULL),(37,'simple','Satin White 20W USB-C PD Wall Charger','স্যাটিন হোয়াইট 20W USB-C PD ওয়াল চার্জার','satin-white-20w-usb-c-pd-wall-charger','GD-CC-07',NULL,'WC-20C',NULL,7,'Satin White 20W USB-C PD Wall Charger. 6 Months official warranty.','স্যাটিন হোয়াইট 20W USB-C PD ওয়াল চার্জার। ৬ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'6 Months','৬ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,1,0,0,1,'active','2026-10-30 07:33:36',790.00,790.00,990.00,20,45,0,4,0.00,0,'wall charger, iphone charger, adapter, usb c charger, 20W',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:36','2026-11-09 00:57:27',NULL),(38,'simple','Slim 15W Magnetic Wireless Charging Pad','স্লিম 15W ম্যাগনেটিক ওয়্যারলেস চার্জিং প্যাড','slim-15w-magnetic-wireless-charging-pad','GD-CC-08',NULL,'WP-15M',NULL,7,'Slim 15W Magnetic Wireless Charging Pad. 6 Months official warranty.','স্লিম 15W ম্যাগনেটিক ওয়্যারলেস চার্জিং প্যাড। ৬ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'6 Months','৬ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,1,0,1,'active','2026-10-31 07:33:36',1490.00,1490.00,1890.00,21,20,0,7,0.00,0,'wireless charger, magsafe, qi charger, charging pad, iphone',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:36','2026-11-09 00:57:27',NULL),(39,'simple','Space Gray 5-in-1 USB-C Hub with 4K HDMI','স্পেস গ্রে 5-ইন-1 USB-C হাব, 4K HDMIসহ','space-gray-5-in-1-usb-c-hub-with-4k-hdmi','GD-CC-09',NULL,'HB-5C4K',NULL,7,'Space Gray 5-in-1 USB-C Hub with 4K HDMI. 6 Months official warranty.','স্পেস গ্রে 5-ইন-1 USB-C হাব, 4K HDMIসহ। ৬ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'6 Months','৬ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-11-01 07:33:36',1990.00,1990.00,2490.00,20,15,0,3,0.00,0,'usb c hub, type c hub, multiport adapter, hdmi adapter, docking, macbook',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:36','2026-11-09 00:57:27',NULL),(40,'simple','White USB-A to Lightning 2.4A Charging Cable 1m','সাদা USB-A টু Lightning 2.4A চার্জিং ক্যাবল 1m','white-usb-a-to-lightning-24a-charging-cable-1m','GD-CC-10',NULL,'CB-AL24',NULL,7,'White USB-A to Lightning 2.4A Charging Cable 1m. 3 Months official warranty.','সাদা USB-A টু Lightning 2.4A চার্জিং ক্যাবল 1m। ৩ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'3 Months','৩ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-11-02 07:33:37',450.00,450.00,590.00,23,60,0,2,0.00,0,'lightning cable, iphone cable, charging cable, data cable',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:37','2026-11-09 00:57:27',NULL),(41,'simple','iPhone 17 Essentials Combo: iPhone 17 + AirPods 4 + 20W Charger','iPhone 17 এসেনশিয়ালস কম্বো: iPhone 17 + AirPods 4 + 20W চার্জার','iphone-17-essentials-combo-iphone-17-airpods-4-20w-charger','GD-CB-01',NULL,'Combo iPhone 17 Essentials',NULL,8,'iPhone 17 Essentials Combo: iPhone 17 + AirPods 4 + 20W Charger. 1 Year official warranty.','iPhone 17 এসেনশিয়ালস কম্বো: iPhone 17 + AirPods 4 + 20W চার্জার। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,1,1,1,'active','2026-11-03 07:33:37',132990.00,132990.00,144490.00,7,3,0,10,0.00,0,'combo, bundle, iphone combo, airpods, charger',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:37','2026-11-09 00:57:27',NULL),(42,'simple','Galaxy Power Combo: Galaxy S26 Ultra + 20000mAh 65W Power Bank + USB-C Cable','গ্যালাক্সি পাওয়ার কম্বো: Galaxy S26 Ultra + 20000mAh 65W পাওয়ার ব্যাংক + USB-C ক্যাবল','galaxy-power-combo-galaxy-s26-ultra-20000mah-65w-power-bank-usb-c-cable','GD-CB-02',NULL,'Combo Galaxy Power',NULL,8,'Galaxy Power Combo: Galaxy S26 Ultra + 20000mAh 65W Power Bank + USB-C Cable. 1 Year official warranty.','গ্যালাক্সি পাওয়ার কম্বো: Galaxy S26 Ultra + 20000mAh 65W পাওয়ার ব্যাংক + USB-C ক্যাবল। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,1,1,'active','2026-11-04 07:33:37',162990.00,162990.00,175179.00,6,3,0,11,0.00,0,'combo, bundle, samsung combo, power bank, cable',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:37','2026-11-09 00:57:27',NULL),(43,'simple','Travel Charging Combo: 10000mAh Power Bank + 35W Wall Charger + Car Charger + Cable','ট্রাভেল চার্জিং কম্বো: 10000mAh পাওয়ার ব্যাংক + 35W ওয়াল চার্জার + কার চার্জার + ক্যাবল','travel-charging-combo-10000mah-power-bank-35w-wall-charger-car-charger-cable','GD-CB-03',NULL,'Combo Travel Charging',NULL,7,'Travel Charging Combo: 10000mAh Power Bank + 35W Wall Charger + Car Charger + Cable. 6 Months official warranty.','ট্রাভেল চার্জিং কম্বো: 10000mAh পাওয়ার ব্যাংক + 35W ওয়াল চার্জার + কার চার্জার + ক্যাবল। ৬ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'6 Months','৬ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,1,1,1,'active','2026-11-05 07:33:37',3990.00,3990.00,5120.00,22,15,0,9,0.00,0,'combo, bundle, travel kit, power bank, charger, car charger',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:37','2026-11-09 00:57:27',NULL),(44,'simple','Fitness Combo: Amazfit Active 2 Smart Watch + SoundPEATS Life ANC Earbuds','ফিটনেস কম্বো: Amazfit Active 2 স্মার্ট ওয়াচ + SoundPEATS Life ANC ইয়ারবাডস','fitness-combo-amazfit-active-2-smart-watch-soundpeats-life-anc-earbuds','GD-CB-04',NULL,'Combo Fitness',NULL,1,'Fitness Combo: Amazfit Active 2 Smart Watch + SoundPEATS Life ANC Earbuds. 1 Year official warranty.','ফিটনেস কম্বো: Amazfit Active 2 স্মার্ট ওয়াচ + SoundPEATS Life ANC ইয়ারবাডস। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,1,1,'active','2026-11-06 07:33:37',19490.00,19490.00,24190.00,19,6,0,4,0.00,0,'combo, bundle, smart watch, earbuds, fitness, gym',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:37','2026-11-09 00:57:27',NULL),(45,'simple','Desk Setup Combo: 5-in-1 USB-C Hub + Wireless Charging Pad + Braided Cable','ডেস্ক সেটআপ কম্বো: 5-ইন-1 USB-C হাব + ওয়্যারলেস চার্জিং প্যাড + ব্রেইডেড ক্যাবল','desk-setup-combo-5-in-1-usb-c-hub-wireless-charging-pad-braided-cable','GD-CB-05',NULL,'Combo Desk Setup',NULL,7,'Desk Setup Combo: 5-in-1 USB-C Hub + Wireless Charging Pad + Braided Cable. 6 Months official warranty.','ডেস্ক সেটআপ কম্বো: 5-ইন-1 USB-C হাব + ওয়্যারলেস চার্জিং প্যাড + ব্রেইডেড ক্যাবল। ৬ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'6 Months','৬ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,1,1,'active','2026-11-07 07:33:37',3990.00,3990.00,5070.00,21,10,0,5,0.00,0,'combo, bundle, desk setup, usb c hub, wireless charger, macbook',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:37','2026-11-09 00:57:27',NULL),(46,'variable','Samsung Galaxy A56 5G Smartphone','Samsung Galaxy A56 5G স্মার্টফোন','samsung-galaxy-a56-5g-smartphone','GD-PH-A56',NULL,'SM-A566E',2,8,'Samsung Galaxy A56 5G Smartphone. 1 Year official warranty.','Samsung Galaxy A56 5G স্মার্টফোন। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',1,1,0,0,0,1,'active','2026-11-08 07:33:37',52999.00,62999.00,52999.00,3,16,0,5,0.00,0,'SM-A566E',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:33:37','2026-11-09 00:57:28',NULL),(47,'simple','Charcoal 10W Portable Bluetooth Speaker','চারকোল 10W পোর্টেবল ব্লুটুথ স্পিকার','charcoal-10w-portable-bluetooth-speaker','GD-SP-01',NULL,'SP-10P',NULL,5,'Charcoal 10W Portable Bluetooth Speaker. 6 Months official warranty.','চারকোল 10W পোর্টেবল ব্লুটুথ স্পিকার। ৬ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'6 Months','৬ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,1,0,0,1,'active','2026-10-25 07:52:57',1590.00,1590.00,1990.00,20,30,0,4,0.00,0,'bluetooth speaker, portable speaker, mini speaker, wireless speaker, black',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:52:57','2026-11-09 00:57:28',NULL),(48,'simple','Elegant Cream Retro 15W Bluetooth Speaker','এলিগ্যান্ট ক্রিম রেট্রো 15W ব্লুটুথ স্পিকার','elegant-cream-retro-15w-bluetooth-speaker','GD-SP-02',NULL,'SP-15R',NULL,5,'Elegant Cream Retro 15W Bluetooth Speaker. 6 Months official warranty.','এলিগ্যান্ট ক্রিম রেট্রো 15W ব্লুটুথ স্পিকার। ৬ মাসের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'6 Months','৬ মাস',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,1,0,1,'active','2026-10-26 07:52:57',2890.00,2890.00,3490.00,17,15,0,3,0.00,0,'retro speaker, vintage speaker, bluetooth speaker, leather, cream, gold',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:52:57','2026-11-09 00:57:28',NULL),(49,'simple','Rugged Navy 20W Waterproof Portable Bluetooth Speaker','রাগেড নেভি 20W ওয়াটারপ্রুফ পোর্টেবল ব্লুটুথ স্পিকার','rugged-navy-20w-waterproof-portable-bluetooth-speaker','GD-SP-03',NULL,'SP-20W',NULL,5,'Rugged Navy 20W Waterproof Portable Bluetooth Speaker. 1 Year official warranty.','রাগেড নেভি 20W ওয়াটারপ্রুফ পোর্টেবল ব্লুটুথ স্পিকার। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,1,0,0,1,'active','2026-10-27 07:52:57',2490.00,2490.00,2990.00,16,25,0,1,0.00,0,'bluetooth speaker, waterproof speaker, outdoor speaker, ipx7, blue',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:52:57','2026-11-09 00:57:28',NULL),(50,'simple','Slim Black 40W Bluetooth Soundbar','স্লিম কালো 40W ব্লুটুথ সাউন্ডবার','slim-black-40w-bluetooth-soundbar','GD-SP-04',NULL,'SB-40S',NULL,5,'Slim Black 40W Bluetooth Soundbar. 1 Year official warranty.','স্লিম কালো 40W ব্লুটুথ সাউন্ডবার। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,0,0,1,'active','2026-10-28 07:52:57',4290.00,4290.00,4990.00,14,12,0,1,0.00,0,'soundbar, tv speaker, computer speaker, bluetooth soundbar, aux',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:52:57','2026-11-09 00:57:28',NULL),(51,'simple','Premium Silver-Gray 20W Smart Speaker with Voice Assistant','প্রিমিয়াম সিলভার-গ্রে 20W স্মার্ট স্পিকার, ভয়েস অ্যাসিস্ট্যান্টসহ','premium-silver-gray-20w-smart-speaker-with-voice-assistant','GD-SP-05',NULL,'SP-20S',NULL,5,'Premium Silver-Gray 20W Smart Speaker with Voice Assistant. 1 Year official warranty.','প্রিমিয়াম সিলভার-গ্রে 20W স্মার্ট স্পিকার, ভয়েস অ্যাসিস্ট্যান্টসহ। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',1,0,0,0,0,1,'active','2026-10-29 07:52:57',4990.00,4990.00,5990.00,16,10,0,4,0.00,0,'smart speaker, voice assistant, wifi speaker, home speaker, gray',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:52:57','2026-11-09 00:57:28',NULL),(52,'simple','Matte Black 25W RGB Gaming Bluetooth Speaker','ম্যাট কালো 25W RGB গেমিং ব্লুটুথ স্পিকার','matte-black-25w-rgb-gaming-bluetooth-speaker','GD-SP-06',NULL,'SP-25G',NULL,5,'Matte Black 25W RGB Gaming Bluetooth Speaker. 1 Year official warranty.','ম্যাট কালো 25W RGB গেমিং ব্লুটুথ স্পিকার। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,0,0,1,0,1,'active','2026-10-30 07:52:57',3390.00,3390.00,3990.00,15,18,0,4,0.00,0,'gaming speaker, rgb speaker, pc speaker, bluetooth speaker, led',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:52:57','2026-11-09 00:57:28',NULL),(53,'simple','Portable 80W Party Speaker with Blue LED Rings','পোর্টেবল 80W পার্টি স্পিকার, নীল LED রিংসহ','portable-80w-party-speaker-with-blue-led-rings','GD-SP-07',NULL,'PS-80L',NULL,5,'Portable 80W Party Speaker with Blue LED Rings. 1 Year official warranty.','পোর্টেবল 80W পার্টি স্পিকার, নীল LED রিংসহ। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',1,0,0,1,0,1,'active','2026-10-31 07:52:57',8490.00,8490.00,9990.00,15,8,0,9,0.00,0,'party speaker, karaoke speaker, dj speaker, led speaker, dual woofer, mic input',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:52:57','2026-11-09 00:57:28',NULL),(54,'simple','Premium Olive 40W Portable Bluetooth Boombox Speaker','প্রিমিয়াম অলিভ 40W পোর্টেবল ব্লুটুথ বুমবক্স স্পিকার','premium-olive-40w-portable-bluetooth-boombox-speaker','GD-SP-08',NULL,'SP-40B',NULL,5,'Premium Olive 40W Portable Bluetooth Boombox Speaker. 1 Year official warranty.','প্রিমিয়াম অলিভ 40W পোর্টেবল ব্লুটুথ বুমবক্স স্পিকার। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,1,0,0,0,1,'active','2026-11-01 07:52:57',4690.00,4690.00,5490.00,14,14,0,4,0.00,0,'boombox, bluetooth speaker, outdoor speaker, portable speaker, green',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:52:57','2026-11-09 00:57:28',NULL),(55,'simple','Walnut 50W Bluetooth Bookshelf Speaker with Copper Woofer','ওয়ালনাট 50W ব্লুটুথ বুকশেলফ স্পিকার, কপার উফারসহ','walnut-50w-bluetooth-bookshelf-speaker-with-copper-woofer','GD-SP-09',NULL,'BS-50W',NULL,5,'Walnut 50W Bluetooth Bookshelf Speaker with Copper Woofer. 1 Year official warranty.','ওয়ালনাট 50W ব্লুটুথ বুকশেলফ স্পিকার, কপার উফারসহ। ১ বছরের অফিসিয়াল ওয়ারেন্টি।',NULL,NULL,NULL,NULL,'1 Year','১ বছর',NULL,NULL,'new',NULL,NULL,NULL,NULL,1,'in_stock',0,1,0,0,0,1,'active','2026-11-02 07:52:57',7490.00,7490.00,8490.00,11,6,0,5,0.00,0,'bookshelf speaker, wooden speaker, hifi speaker, home audio, walnut',NULL,NULL,NULL,NULL,NULL,1,'2026-11-08 07:52:57','2026-11-09 00:57:28',NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refunds`
--

DROP TABLE IF EXISTS `refunds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `refunds` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `payment_id` bigint(20) unsigned DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `method` varchar(30) DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `reason` varchar(500) NOT NULL,
  `restock` tinyint(1) NOT NULL DEFAULT 0,
  `processed_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `refunds_order_id_foreign` (`order_id`),
  KEY `refunds_payment_id_foreign` (`payment_id`),
  KEY `refunds_processed_by_foreign` (`processed_by`),
  CONSTRAINT `refunds_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `refunds_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `refunds_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refunds`
--

LOCK TABLES `refunds` WRITE;
/*!40000 ALTER TABLE `refunds` DISABLE KEYS */;
/*!40000 ALTER TABLE `refunds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `order_item_id` bigint(20) unsigned DEFAULT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `is_verified_purchase` tinyint(1) NOT NULL DEFAULT 0,
  `rejection_reason` varchar(255) DEFAULT NULL,
  `moderated_by` bigint(20) unsigned DEFAULT NULL,
  `moderated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reviews_user_id_product_id_unique` (`user_id`,`product_id`),
  KEY `reviews_order_item_id_foreign` (`order_item_id`),
  KEY `reviews_moderated_by_foreign` (`moderated_by`),
  KEY `reviews_product_id_status_created_at_index` (`product_id`,`status`,`created_at`),
  KEY `reviews_status_created_at_index` (`status`,`created_at`),
  CONSTRAINT `reviews_moderated_by_foreign` FOREIGN KEY (`moderated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,4,1,NULL,5,'good product','I like it very good','approved',0,NULL,1,'2026-11-09 02:35:24','2026-11-09 02:35:02','2026-11-09 02:35:24');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
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
INSERT INTO `role_has_permissions` VALUES (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(2,1),(2,2),(2,3),(3,1),(3,2),(3,3),(4,1),(4,2),(4,3),(4,4),(4,5),(4,6),(5,1),(5,2),(5,3),(5,4),(6,1),(6,2),(6,3),(6,4),(7,1),(7,2),(7,4),(8,1),(8,2),(8,4),(9,1),(9,2),(9,3),(9,4),(9,6),(10,1),(10,2),(10,3),(10,4),(11,1),(11,2),(11,3),(11,4),(12,1),(12,2),(12,4),(13,1),(13,2),(13,3),(13,4),(13,6),(14,1),(14,2),(14,3),(14,4),(15,1),(15,2),(15,3),(15,4),(16,1),(16,2),(16,4),(17,1),(17,2),(17,3),(17,4),(18,1),(18,2),(18,3),(18,4),(19,1),(19,2),(19,3),(19,4),(19,5),(20,1),(20,2),(20,3),(20,4),(21,1),(21,2),(21,3),(21,5),(22,1),(22,2),(22,3),(22,5),(23,1),(23,2),(23,3),(23,5),(24,1),(24,2),(24,3),(24,5),(25,1),(25,2),(25,3),(25,5),(26,1),(26,2),(26,3),(26,5),(27,1),(27,2),(27,3),(27,5),(28,1),(28,2),(28,3),(28,4),(28,5),(29,1),(29,2),(29,3),(30,1),(30,2),(30,3),(30,4),(30,6),(31,1),(31,2),(31,3),(32,1),(32,2),(32,3),(32,4),(32,6),(33,1),(33,2),(33,3),(33,4),(33,6),(34,1),(34,2),(34,3),(34,5),(35,1),(35,2),(35,3),(35,5),(36,1),(36,2),(37,1),(37,2),(37,3),(37,6),(38,1),(38,2),(39,1),(39,2),(40,1),(40,2),(41,1),(42,1),(42,2),(43,1),(43,2);
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
INSERT INTO `roles` VALUES (1,'super_admin','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(2,'admin','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(3,'manager','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(4,'product_manager','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(5,'order_manager','web','2026-11-08 07:33:32','2026-11-08 07:33:32'),(6,'content_manager','web','2026-11-08 07:33:32','2026-11-08 07:33:32');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_highlights`
--

DROP TABLE IF EXISTS `service_highlights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_highlights` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `icon` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `title_bn` varchar(100) DEFAULT NULL,
  `subtitle` varchar(150) DEFAULT NULL,
  `subtitle_bn` varchar(150) DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_highlights`
--

LOCK TABLES `service_highlights` WRITE;
/*!40000 ALTER TABLE `service_highlights` DISABLE KEYS */;
INSERT INTO `service_highlights` VALUES (1,'cash','0% EMI','০% ইএমআই',NULL,NULL,NULL,1,1,'2026-11-08 07:33:33','2026-11-09 00:57:28'),(2,'support','24/7 Online Support','২৪/৭ অনলাইন সাপোর্ট',NULL,NULL,NULL,1,2,'2026-11-08 07:33:33','2026-11-09 00:57:28'),(3,'card','No charge on card payment','কার্ড পেমেন্টে কোনো চার্জ নেই',NULL,NULL,NULL,1,3,'2026-11-08 07:33:33','2026-11-09 00:57:28'),(4,'truck','Cash on delivery in 64 districts','৬৪ জেলায় ক্যাশ অন ডেলিভারি',NULL,NULL,NULL,1,4,'2026-11-08 07:33:33','2026-11-09 00:57:28');
/*!40000 ALTER TABLE `service_highlights` ENABLE KEYS */;
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
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(50) NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`value`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_group_key_unique` (`group`,`key`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'general','site_name','\"Tech Craze BD\"','2026-11-08 07:33:38','2026-11-08 07:33:38'),(2,'contact','hotline','\"+8801711727860\"','2026-11-08 07:33:38','2026-11-08 07:33:38'),(3,'contact','email','\"techcrazebd@gmail.com\"','2026-11-08 07:33:38','2026-11-08 07:33:38'),(4,'contact','whatsapp','\"8801711727860\"','2026-11-08 07:33:38','2026-11-08 07:33:38'),(5,'contact','address','\"Head Office: Your address here, Dhaka\"','2026-11-08 07:33:38','2026-11-08 07:33:38'),(6,'contact','map_url','\"https:\\/\\/maps.google.com\\/?q=Dhaka\"','2026-11-08 07:33:38','2026-11-08 07:33:38'),(7,'contact','complain_url','\"mailto:techcrazebd@gmail.com\"','2026-11-08 07:33:38','2026-11-08 07:33:38'),(8,'social','facebook','\"https:\\/\\/www.facebook.com\\/\"','2026-11-08 07:33:38','2026-11-08 07:33:38'),(9,'social','messenger','\"https:\\/\\/m.me\\/\"','2026-11-08 07:33:38','2026-11-08 07:33:38'),(10,'social','youtube','\"https:\\/\\/www.youtube.com\\/\"','2026-11-08 07:33:38','2026-11-08 07:33:38'),(11,'social','instagram','\"https:\\/\\/www.instagram.com\\/\"','2026-11-08 07:33:38','2026-11-08 07:33:38'),(12,'social','linkedin','\"https:\\/\\/www.linkedin.com\\/\"','2026-11-08 07:33:38','2026-11-08 07:33:38'),(13,'footer','developer_name','\"Digital Webars\"','2026-11-08 07:33:38','2026-11-08 07:33:38'),(14,'footer','developer_url','\"https:\\/\\/www.digitalwebars.com\\/\"','2026-11-08 07:33:38','2026-11-08 07:33:38'),(15,'general','logo','\"settings\\/tcb-logo-horizontal.png\"','2026-11-08 07:33:38','2026-11-08 07:33:38'),(16,'general','favicon','\"settings\\/tcb-favicon.png\"','2026-11-08 07:33:38','2026-11-08 07:33:38'),(17,'footer','payment_methods_image','\"settings\\/payment-methods.webp\"','2026-11-08 07:33:38','2026-11-08 07:33:38'),(18,'footer','affiliations','[{\"image\":\"settings\\/affiliations\\/basis.png\",\"name\":\"BASIS\",\"url\":null},{\"image\":\"settings\\/affiliations\\/bcs.png\",\"name\":\"BCS\",\"url\":null},{\"image\":\"settings\\/affiliations\\/ecs.png\",\"name\":\"ECS\",\"url\":null},{\"image\":\"settings\\/affiliations\\/ecab.png\",\"name\":\"e-CAB\",\"url\":null}]','2026-11-08 07:33:38','2026-11-08 07:33:38'),(19,'steadfast','enabled','false','2026-11-08 08:02:33','2026-11-08 08:55:09'),(20,'steadfast','base_url','\"https:\\/\\/portal.packzy.com\\/api\\/v1\"','2026-11-08 08:02:33','2026-11-08 08:02:33'),(21,'steadfast','api_key',NULL,'2026-11-08 08:02:33','2026-11-08 08:55:09'),(22,'steadfast','secret_key',NULL,'2026-11-08 08:02:33','2026-11-08 08:55:09'),(23,'steadfast','balance',NULL,'2026-11-08 08:02:33','2026-11-08 08:55:09'),(24,'steadfast','balance_checked_at',NULL,'2026-11-08 08:02:33','2026-11-08 08:55:09'),(25,'steadfast','auto_send','true','2026-11-08 08:34:17','2026-11-08 08:34:17'),(26,'contact','address_bn','\"\\u09aa\\u09cd\\u09b0\\u09a7\\u09be\\u09a8 \\u0995\\u09be\\u09b0\\u09cd\\u09af\\u09be\\u09b2\\u09af\\u09bc: \\u0986\\u09aa\\u09a8\\u09be\\u09b0 \\u09a0\\u09bf\\u0995\\u09be\\u09a8\\u09be \\u098f\\u0996\\u09be\\u09a8\\u09c7, \\u09a2\\u09be\\u0995\\u09be\"','2026-11-09 00:57:28','2026-11-09 00:57:28');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipping_methods`
--

DROP TABLE IF EXISTS `shipping_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_methods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `name_bn` varchar(100) DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `description_bn` varchar(255) DEFAULT NULL,
  `charge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `free_shipping_threshold` decimal(12,2) DEFAULT NULL,
  `delivery_estimate` varchar(100) DEFAULT NULL,
  `delivery_estimate_bn` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shipping_methods_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipping_methods`
--

LOCK TABLES `shipping_methods` WRITE;
/*!40000 ALTER TABLE `shipping_methods` DISABLE KEYS */;
INSERT INTO `shipping_methods` VALUES (1,'Inside Dhaka','ঢাকার ভিতরে','inside_dhaka','Delivered by our courier partner within the city','শহরের ভিতরে আমাদের কুরিয়ার পার্টনার পৌঁছে দেবে',60.00,NULL,'1-2 working days','১-২ কর্মদিবস',1,0,'2026-11-08 07:33:33','2026-11-09 00:57:28'),(2,'Outside Dhaka','ঢাকার বাইরে','outside_dhaka','Nationwide delivery anywhere in Bangladesh','বাংলাদেশের যেকোনো জায়গায় দেশব্যাপী ডেলিভারি',120.00,NULL,'2-5 working days','২-৫ কর্মদিবস',1,1,'2026-11-08 07:33:33','2026-11-09 00:57:28');
/*!40000 ALTER TABLE `shipping_methods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipping_zones`
--

DROP TABLE IF EXISTS `shipping_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_zones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `name_bn` varchar(100) DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shipping_zones_code_unique` (`code`),
  KEY `shipping_zones_is_active_sort_order_index` (`is_active`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipping_zones`
--

LOCK TABLES `shipping_zones` WRITE;
/*!40000 ALTER TABLE `shipping_zones` DISABLE KEYS */;
INSERT INTO `shipping_zones` VALUES (1,'Inside Dhaka','ঢাকার ভিতরে','inside_dhaka','Dhaka district',1,1,'2026-11-08 07:33:33','2026-11-09 00:57:28'),(2,'Outside Dhaka','ঢাকার বাইরে','outside_dhaka','All other districts',1,2,'2026-11-08 07:33:33','2026-11-09 00:57:28');
/*!40000 ALTER TABLE `shipping_zones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `specification_groups`
--

DROP TABLE IF EXISTS `specification_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `specification_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `name_bn` varchar(100) DEFAULT NULL,
  `slug` varchar(120) NOT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `specification_groups_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `specification_groups`
--

LOCK TABLES `specification_groups` WRITE;
/*!40000 ALTER TABLE `specification_groups` DISABLE KEYS */;
INSERT INTO `specification_groups` VALUES (1,'Display','ডিসপ্লে','display',1,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(2,'Processor','প্রসেসর','processor',2,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(3,'Memory','মেমোরি','memory',3,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(4,'Storage','স্টোরেজ','storage',4,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(5,'Graphics','গ্রাফিক্স','graphics',5,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(6,'Connectivity','কানেক্টিভিটি','connectivity',6,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(7,'Audio','অডিও','audio',7,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(8,'Battery','ব্যাটারি','battery',8,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(9,'Camera','ক্যামেরা','camera',9,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(10,'Physical','বাহ্যিক বৈশিষ্ট্য','physical',10,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(11,'Power','পাওয়ার','power',11,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(12,'Warranty','ওয়ারেন্টি','warranty',12,'2026-11-08 07:33:33','2026-11-09 00:57:27');
/*!40000 ALTER TABLE `specification_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `specification_options`
--

DROP TABLE IF EXISTS `specification_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `specification_options` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `specification_id` bigint(20) unsigned NOT NULL,
  `value` varchar(150) NOT NULL,
  `value_bn` varchar(150) DEFAULT NULL,
  `slug` varchar(170) NOT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `specification_options_specification_id_slug_unique` (`specification_id`,`slug`),
  CONSTRAINT `specification_options_specification_id_foreign` FOREIGN KEY (`specification_id`) REFERENCES `specifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `specification_options`
--

LOCK TABLES `specification_options` WRITE;
/*!40000 ALTER TABLE `specification_options` DISABLE KEYS */;
INSERT INTO `specification_options` VALUES (1,2,'1366x768 (HD)',NULL,'1366x768-hd',1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(2,2,'1920x1080 (FHD)',NULL,'1920x1080-fhd',2,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(3,2,'2560x1440 (QHD)',NULL,'2560x1440-qhd',3,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(4,2,'3440x1440 (WQHD)',NULL,'3440x1440-wqhd',4,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(5,2,'3840x2160 (4K UHD)',NULL,'3840x2160-4k-uhd',5,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(6,2,'1080x2340 (FHD+)',NULL,'1080x2340-fhd',6,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(7,3,'60Hz',NULL,'60hz',1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(8,3,'75Hz',NULL,'75hz',2,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(9,3,'100Hz',NULL,'100hz',3,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(10,3,'120Hz',NULL,'120hz',4,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(11,3,'144Hz',NULL,'144hz',5,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(12,3,'165Hz',NULL,'165hz',6,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(13,3,'240Hz',NULL,'240hz',7,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(14,4,'IPS',NULL,'ips',1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(15,4,'VA',NULL,'va',2,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(16,4,'TN',NULL,'tn',3,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(17,4,'OLED',NULL,'oled',4,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(18,4,'AMOLED',NULL,'amoled',5,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(19,4,'Super AMOLED',NULL,'super-amoled',6,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(20,6,'Intel',NULL,'intel',1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(21,6,'AMD',NULL,'amd',2,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(22,6,'Apple',NULL,'apple',3,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(23,6,'MediaTek',NULL,'mediatek',4,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(24,6,'Qualcomm',NULL,'qualcomm',5,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(25,6,'Samsung Exynos',NULL,'samsung-exynos',6,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(26,12,'DDR4',NULL,'ddr4',1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(27,12,'DDR5',NULL,'ddr5',2,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(28,12,'LPDDR4X',NULL,'lpddr4x',3,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(29,12,'LPDDR5',NULL,'lpddr5',4,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(30,12,'LPDDR5X',NULL,'lpddr5x',5,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(31,13,'128GB',NULL,'128gb',1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(32,13,'256GB',NULL,'256gb',2,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(33,13,'512GB',NULL,'512gb',3,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(34,13,'1TB',NULL,'1tb',4,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(35,13,'2TB',NULL,'2tb',5,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(36,14,'SSD',NULL,'ssd',1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(37,14,'NVMe SSD',NULL,'nvme-ssd',2,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(38,14,'HDD',NULL,'hdd',3,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(39,14,'UFS',NULL,'ufs',4,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(40,14,'eMMC',NULL,'emmc',5,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(41,17,'GDDR5',NULL,'gddr5',1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(42,17,'GDDR6',NULL,'gddr6',2,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(43,17,'GDDR6X',NULL,'gddr6x',3,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(44,17,'GDDR7',NULL,'gddr7',4,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(45,29,'Wired','তারযুক্ত','wired',1,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(46,29,'Wireless','ওয়্যারলেস','wireless',2,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(47,29,'Wired & Wireless','তারযুক্ত ও ওয়্যারলেস','wired-wireless',3,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(48,40,'ATX',NULL,'atx',1,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(49,40,'Micro ATX',NULL,'micro-atx',2,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(50,40,'Mini ITX',NULL,'mini-itx',3,'2026-11-08 07:33:33','2026-11-08 07:33:33'),(51,40,'Mini PC','মিনি পিসি','mini-pc',4,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(52,40,'Tower','টাওয়ার','tower',5,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(53,42,'1 Ton','১ টন','1-ton',1,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(54,42,'1.5 Ton','১.৫ টন','15-ton',2,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(55,42,'2 Ton','২ টন','2-ton',3,'2026-11-08 07:33:33','2026-11-09 00:57:27');
/*!40000 ALTER TABLE `specification_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `specifications`
--

DROP TABLE IF EXISTS `specifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `specifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `specification_group_id` bigint(20) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `name_bn` varchar(150) DEFAULT NULL,
  `slug` varchar(170) NOT NULL,
  `unit` varchar(20) DEFAULT NULL,
  `input_type` varchar(20) NOT NULL DEFAULT 'text',
  `is_filterable` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `specifications_slug_unique` (`slug`),
  KEY `specifications_specification_group_id_sort_order_index` (`specification_group_id`,`sort_order`),
  CONSTRAINT `specifications_specification_group_id_foreign` FOREIGN KEY (`specification_group_id`) REFERENCES `specification_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `specifications`
--

LOCK TABLES `specifications` WRITE;
/*!40000 ALTER TABLE `specifications` DISABLE KEYS */;
INSERT INTO `specifications` VALUES (1,1,'Screen Size','স্ক্রিন সাইজ','screen-size','inch','number',1,1,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(2,1,'Resolution','রেজোলিউশন','resolution',NULL,'select',1,2,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(3,1,'Refresh Rate','রিফ্রেশ রেট','refresh-rate',NULL,'select',1,3,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(4,1,'Panel Type','প্যানেলের ধরন','panel-type',NULL,'select',1,4,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(5,1,'Curved Display','কার্ভড ডিসপ্লে','curved-display',NULL,'boolean',0,5,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(6,2,'Processor Brand','প্রসেসর ব্র্যান্ড','processor-brand',NULL,'select',1,1,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(7,2,'Processor Model','প্রসেসর মডেল','processor-model',NULL,'text',0,2,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(8,2,'Generation','জেনারেশন','generation',NULL,'text',0,3,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(9,2,'Cores','কোর','cores',NULL,'number',0,4,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(10,2,'Threads','থ্রেড','threads',NULL,'number',0,5,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(11,3,'RAM','র‍্যাম','ram','GB','number',1,1,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(12,3,'RAM Type','র‍্যামের ধরন','ram-type',NULL,'select',1,2,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(13,4,'Storage Capacity','স্টোরেজ ক্ষমতা','storage-capacity',NULL,'select',1,1,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(14,4,'Storage Type','স্টোরেজের ধরন','storage-type',NULL,'select',1,2,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(15,5,'Graphics Chipset','গ্রাফিক্স চিপসেট','graphics-chipset',NULL,'text',0,1,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(16,5,'Video Memory','ভিডিও মেমোরি','video-memory','GB','number',1,2,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(17,5,'Video Memory Type','ভিডিও মেমোরির ধরন','video-memory-type',NULL,'select',0,3,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(18,5,'Memory Clock','মেমোরি ক্লক','memory-clock',NULL,'text',0,4,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(19,5,'Memory Bus','মেমোরি বাস','memory-bus','bit','number',0,5,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(20,5,'CUDA Cores','CUDA কোর','cuda-cores',NULL,'number',0,6,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(21,5,'Bus Interface','বাস ইন্টারফেস','bus-interface',NULL,'text',0,7,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(22,5,'DirectX','DirectX','directx',NULL,'text',0,8,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(23,5,'OpenGL','OpenGL','opengl',NULL,'text',0,9,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(24,5,'Max Resolution','সর্বোচ্চ রেজোলিউশন','max-resolution',NULL,'text',0,10,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(25,5,'Multi Display','মাল্টি ডিসপ্লে','multi-display',NULL,'number',0,11,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(26,6,'Wi-Fi','ওয়াই-ফাই','wi-fi',NULL,'text',0,1,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(27,6,'Bluetooth','ব্লুটুথ','bluetooth',NULL,'text',0,2,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(28,6,'Ports','পোর্ট','ports',NULL,'text',0,3,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(29,6,'Connection Type','সংযোগের ধরন','connection-type',NULL,'select',1,4,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(30,7,'Driver Size','ড্রাইভার সাইজ','driver-size','mm','number',0,1,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(31,7,'Surround Sound','সারাউন্ড সাউন্ড','surround-sound',NULL,'text',0,2,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(32,7,'Output Power','আউটপুট পাওয়ার','output-power','W','number',0,3,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(33,8,'Battery Capacity','ব্যাটারি ক্ষমতা','battery-capacity','mAh','number',0,1,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(34,8,'Fast Charging','ফাস্ট চার্জিং','fast-charging',NULL,'text',0,2,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(35,9,'Rear Camera','পেছনের ক্যামেরা','rear-camera',NULL,'text',0,1,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(36,9,'Front Camera','সামনের ক্যামেরা','front-camera',NULL,'text',0,2,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(37,10,'Color','রং','color',NULL,'text',0,1,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(38,10,'Dimensions','মাপ','dimensions',NULL,'text',0,2,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(39,10,'Weight','ওজন','weight',NULL,'text',0,3,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(40,10,'Form Factor','ফর্ম ফ্যাক্টর','form-factor',NULL,'select',1,4,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(41,11,'Capacity','ক্ষমতা','capacity',NULL,'text',0,1,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(42,11,'Cooling Capacity','কুলিং ক্ষমতা','cooling-capacity',NULL,'select',1,2,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(43,11,'Inverter','ইনভার্টার','inverter',NULL,'boolean',0,3,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(44,11,'Power Consumption','বিদ্যুৎ খরচ','power-consumption','W','number',0,4,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(45,11,'Recommended PSU','প্রস্তাবিত PSU','recommended-psu','W','number',0,5,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(46,11,'Power Connector','পাওয়ার কানেক্টর','power-connector',NULL,'text',0,6,'2026-11-08 07:33:33','2026-11-09 00:57:27'),(47,12,'Warranty Details','ওয়ারেন্টির বিবরণ','warranty-details',NULL,'text',0,1,'2026-11-08 07:33:33','2026-11-09 00:57:27');
/*!40000 ALTER TABLE `specifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tags_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `app_authentication_secret` text DEFAULT NULL,
  `app_authentication_recovery_codes` text DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_phone_unique` (`phone`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_created_at_index` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Super Admin',NULL,'admin@example.com',NULL,'2026-11-08 07:33:32','$2y$12$rKpEzxYbGDnAUkXa43LJcul7E2DGFqGOGTKjSwrifUnElzBhP7FiS','active',NULL,NULL,'aSfjp6w4ARm62wKLvrCwFOOB7QLFVMJn5iDJunaQzavycl04fXXq1E8hdnSW',NULL,'2026-11-08 07:33:33','2026-11-08 07:56:04',NULL),(2,'sourov','sourov','sourov@gmail.com',NULL,'2026-11-09 03:13:24','$2y$12$Ql2AmZdt4uJ2rMjU8v795Ok3efzqP9V2HzJQ4SA185Cdwt7kg48YO','active',NULL,NULL,NULL,NULL,'2026-11-09 03:13:24','2026-11-09 03:13:24',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlists`
--

DROP TABLE IF EXISTS `wishlists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wishlists` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wishlists_user_id_product_id_unique` (`user_id`,`product_id`),
  KEY `wishlists_product_id_foreign` (`product_id`),
  CONSTRAINT `wishlists_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlists`
--

LOCK TABLES `wishlists` WRITE;
/*!40000 ALTER TABLE `wishlists` DISABLE KEYS */;
/*!40000 ALTER TABLE `wishlists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'gadget website'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed
