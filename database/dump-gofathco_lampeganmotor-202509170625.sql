/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.7.2-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: gofathco_lampeganmotor
-- ------------------------------------------------------
-- Server version	11.4.8-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `additional_costs`
--

DROP TABLE IF EXISTS `additional_costs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `additional_costs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` bigint(20) unsigned NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `cost_date` date NOT NULL DEFAULT '2025-08-27',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `additional_costs_vehicle_id_foreign` (`vehicle_id`),
  CONSTRAINT `additional_costs_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `additional_costs`
--

LOCK TABLES `additional_costs` WRITE;
/*!40000 ALTER TABLE `additional_costs` DISABLE KEYS */;
/*!40000 ALTER TABLE `additional_costs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `brands` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brands_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brands`
--

LOCK TABLES `brands` WRITE;
/*!40000 ALTER TABLE `brands` DISABLE KEYS */;
INSERT INTO `brands` VALUES
(1,'HONDA','2025-08-27 18:32:53','2025-08-31 08:13:21'),
(2,'YAMAHA','2025-09-10 01:02:25','2025-09-10 01:02:25'),
(3,'SUZUKI','2025-09-10 01:02:32','2025-09-10 01:02:32'),
(4,'KAWASAKI','2025-09-10 01:02:36','2025-09-10 01:02:36'),
(5,'HARLEY DAVIDSON','2025-09-10 03:11:20','2025-09-10 19:34:10'),
(6,'PIAGGIO','2025-09-10 16:43:19','2025-09-10 16:43:19');
/*!40000 ALTER TABLE `brands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES
('lampegan-motor-apps-cache-c1dfd96eea8cc2b62785275bca38ac261256e278','i:1;',1758000104),
('lampegan-motor-apps-cache-c1dfd96eea8cc2b62785275bca38ac261256e278:timer','i:1758000104;',1758000104),
('lampegan-motor-apps-cache-da4b9237bacccdf19c0760cab7aec4a8359010b0','i:1;',1757896026),
('lampegan-motor-apps-cache-da4b9237bacccdf19c0760cab7aec4a8359010b0:timer','i:1757896026;',1757896026),
('lampegan-motor-apps-cache-livewire-rate-limiter:03d79fc8d61b30e7f4fecb40d72363d482557617','i:1;',1757497821),
('lampegan-motor-apps-cache-livewire-rate-limiter:03d79fc8d61b30e7f4fecb40d72363d482557617:timer','i:1757497821;',1757497821),
('lampegan-motor-apps-cache-livewire-rate-limiter:115d3a4c0c65c8433e6fd82a4bd5810e2e117ecf','i:1;',1757595757),
('lampegan-motor-apps-cache-livewire-rate-limiter:115d3a4c0c65c8433e6fd82a4bd5810e2e117ecf:timer','i:1757595757;',1757595757),
('lampegan-motor-apps-cache-livewire-rate-limiter:12266466a1db8ebb8d54edf68b66eeca7988933a','i:1;',1757501877),
('lampegan-motor-apps-cache-livewire-rate-limiter:12266466a1db8ebb8d54edf68b66eeca7988933a:timer','i:1757501877;',1757501877),
('lampegan-motor-apps-cache-livewire-rate-limiter:1b56f807d53b0b0f4c87ee8c5c7a9bb73a916e55','i:1;',1757547836),
('lampegan-motor-apps-cache-livewire-rate-limiter:1b56f807d53b0b0f4c87ee8c5c7a9bb73a916e55:timer','i:1757547836;',1757547836),
('lampegan-motor-apps-cache-livewire-rate-limiter:1efb35dfc409ee783f977139faf0c9e30b03a3b8','i:1;',1757500405),
('lampegan-motor-apps-cache-livewire-rate-limiter:1efb35dfc409ee783f977139faf0c9e30b03a3b8:timer','i:1757500405;',1757500405),
('lampegan-motor-apps-cache-livewire-rate-limiter:24bbfff1891bb152dfe31ba0299c906c81348c3c','i:1;',1757499278),
('lampegan-motor-apps-cache-livewire-rate-limiter:24bbfff1891bb152dfe31ba0299c906c81348c3c:timer','i:1757499278;',1757499278),
('lampegan-motor-apps-cache-livewire-rate-limiter:2ee18e67dcdb35faff401d524694a57c6e24470a','i:2;',1757495322),
('lampegan-motor-apps-cache-livewire-rate-limiter:2ee18e67dcdb35faff401d524694a57c6e24470a:timer','i:1757495322;',1757495322),
('lampegan-motor-apps-cache-livewire-rate-limiter:32eb33e627fa94ff5e7f15f0be0bb135b817ae33','i:1;',1757499311),
('lampegan-motor-apps-cache-livewire-rate-limiter:32eb33e627fa94ff5e7f15f0be0bb135b817ae33:timer','i:1757499311;',1757499311),
('lampegan-motor-apps-cache-livewire-rate-limiter:3f1447c88c1fddc929ca550d3a7a1103dc138aa5','i:1;',1757910784),
('lampegan-motor-apps-cache-livewire-rate-limiter:3f1447c88c1fddc929ca550d3a7a1103dc138aa5:timer','i:1757910784;',1757910784),
('lampegan-motor-apps-cache-livewire-rate-limiter:42cb01f7519dd9bad78ba92a4f3343b1489f3c1b','i:1;',1757496256),
('lampegan-motor-apps-cache-livewire-rate-limiter:42cb01f7519dd9bad78ba92a4f3343b1489f3c1b:timer','i:1757496256;',1757496256),
('lampegan-motor-apps-cache-livewire-rate-limiter:4974b85524001ad54803c255440e7c85b9b95e33','i:1;',1757497346),
('lampegan-motor-apps-cache-livewire-rate-limiter:4974b85524001ad54803c255440e7c85b9b95e33:timer','i:1757497346;',1757497346),
('lampegan-motor-apps-cache-livewire-rate-limiter:4d4d4d9a7b813252a83ff0e6dfc98a2e3a8de1e5','i:1;',1757994560),
('lampegan-motor-apps-cache-livewire-rate-limiter:4d4d4d9a7b813252a83ff0e6dfc98a2e3a8de1e5:timer','i:1757994560;',1757994560),
('lampegan-motor-apps-cache-livewire-rate-limiter:5047f8b03da4078cb0bb150167b217f85f235819','i:1;',1757987563),
('lampegan-motor-apps-cache-livewire-rate-limiter:5047f8b03da4078cb0bb150167b217f85f235819:timer','i:1757987563;',1757987563),
('lampegan-motor-apps-cache-livewire-rate-limiter:53b265646361f0c99fede06302941197ccaa9d0c','i:1;',1757921842),
('lampegan-motor-apps-cache-livewire-rate-limiter:53b265646361f0c99fede06302941197ccaa9d0c:timer','i:1757921842;',1757921842),
('lampegan-motor-apps-cache-livewire-rate-limiter:675e7f67533027f36f1c99e5e83aefe907172161','i:1;',1757948507),
('lampegan-motor-apps-cache-livewire-rate-limiter:675e7f67533027f36f1c99e5e83aefe907172161:timer','i:1757948507;',1757948507),
('lampegan-motor-apps-cache-livewire-rate-limiter:69a81b3f1fd95bd1e506c56fee38ef43d8deec0d','i:1;',1757494649),
('lampegan-motor-apps-cache-livewire-rate-limiter:69a81b3f1fd95bd1e506c56fee38ef43d8deec0d:timer','i:1757494649;',1757494649),
('lampegan-motor-apps-cache-livewire-rate-limiter:7248f889c185d837d0d4e0a1412180ba418faf00','i:1;',1757898045),
('lampegan-motor-apps-cache-livewire-rate-limiter:7248f889c185d837d0d4e0a1412180ba418faf00:timer','i:1757898045;',1757898045),
('lampegan-motor-apps-cache-livewire-rate-limiter:83d900f7ae379c0bff88ac33295f2a78e908d1c3','i:1;',1757641974),
('lampegan-motor-apps-cache-livewire-rate-limiter:83d900f7ae379c0bff88ac33295f2a78e908d1c3:timer','i:1757641974;',1757641974),
('lampegan-motor-apps-cache-livewire-rate-limiter:87688e16f909e2f3ef202c1411bbfab55ad3efe6','i:1;',1757557594),
('lampegan-motor-apps-cache-livewire-rate-limiter:87688e16f909e2f3ef202c1411bbfab55ad3efe6:timer','i:1757557594;',1757557594),
('lampegan-motor-apps-cache-livewire-rate-limiter:910e1d221db6fd4aa254cb065782f874885ed9d0','i:1;',1758001468),
('lampegan-motor-apps-cache-livewire-rate-limiter:910e1d221db6fd4aa254cb065782f874885ed9d0:timer','i:1758001468;',1758001468),
('lampegan-motor-apps-cache-livewire-rate-limiter:9324f2ad6cdac581a7bbb749a98afcab0a9c9dc0','i:1;',1757726916),
('lampegan-motor-apps-cache-livewire-rate-limiter:9324f2ad6cdac581a7bbb749a98afcab0a9c9dc0:timer','i:1757726916;',1757726916),
('lampegan-motor-apps-cache-livewire-rate-limiter:9743329889f148f87755e6e751fed8b24d38a6cd','i:1;',1757564088),
('lampegan-motor-apps-cache-livewire-rate-limiter:9743329889f148f87755e6e751fed8b24d38a6cd:timer','i:1757564088;',1757564088),
('lampegan-motor-apps-cache-livewire-rate-limiter:98220740af4f3f7722a0959ef7dc82a5ce055ec5','i:1;',1757898226),
('lampegan-motor-apps-cache-livewire-rate-limiter:98220740af4f3f7722a0959ef7dc82a5ce055ec5:timer','i:1757898226;',1757898226),
('lampegan-motor-apps-cache-livewire-rate-limiter:9b2cc3a4fa86edf1d175008a0a618e04f1c095b1','i:1;',1757669178),
('lampegan-motor-apps-cache-livewire-rate-limiter:9b2cc3a4fa86edf1d175008a0a618e04f1c095b1:timer','i:1757669178;',1757669178),
('lampegan-motor-apps-cache-livewire-rate-limiter:9c1519fdc0a4443793dab2f51e47dd71a04a841e','i:1;',1757661780),
('lampegan-motor-apps-cache-livewire-rate-limiter:9c1519fdc0a4443793dab2f51e47dd71a04a841e:timer','i:1757661780;',1757661780),
('lampegan-motor-apps-cache-livewire-rate-limiter:a645ab5c2ed871fba107074dd21dc0f873817565','i:1;',1757558004),
('lampegan-motor-apps-cache-livewire-rate-limiter:a645ab5c2ed871fba107074dd21dc0f873817565:timer','i:1757558004;',1757558004),
('lampegan-motor-apps-cache-livewire-rate-limiter:ab1b1e287b06926a26e7888f6a6982fabed4585a','i:1;',1757498270),
('lampegan-motor-apps-cache-livewire-rate-limiter:ab1b1e287b06926a26e7888f6a6982fabed4585a:timer','i:1757498270;',1757498270),
('lampegan-motor-apps-cache-livewire-rate-limiter:ba14cda21509118028c2405d1ece5805a9fa2e06','i:1;',1757572207),
('lampegan-motor-apps-cache-livewire-rate-limiter:ba14cda21509118028c2405d1ece5805a9fa2e06:timer','i:1757572207;',1757572207),
('lampegan-motor-apps-cache-livewire-rate-limiter:c3d8f2dc1716c87beff9eb6c4c7675acac508c33','i:1;',1757500971),
('lampegan-motor-apps-cache-livewire-rate-limiter:c3d8f2dc1716c87beff9eb6c4c7675acac508c33:timer','i:1757500971;',1757500971),
('lampegan-motor-apps-cache-livewire-rate-limiter:c4b24c766531f74f9783eecedfca03d095fee9a7','i:1;',1757494682),
('lampegan-motor-apps-cache-livewire-rate-limiter:c4b24c766531f74f9783eecedfca03d095fee9a7:timer','i:1757494682;',1757494682),
('lampegan-motor-apps-cache-livewire-rate-limiter:e8fc75c9b53797a82d70e7518394e899e4b5e06c','i:1;',1757595661),
('lampegan-motor-apps-cache-livewire-rate-limiter:e8fc75c9b53797a82d70e7518394e899e4b5e06c:timer','i:1757595661;',1757595661),
('lampegan-motor-apps-cache-livewire-rate-limiter:ed57e562dc0fee0587fc8d365dde1fbb2abe4edc','i:1;',1757494607),
('lampegan-motor-apps-cache-livewire-rate-limiter:ed57e562dc0fee0587fc8d365dde1fbb2abe4edc:timer','i:1757494607;',1757494607),
('lampegan-motor-apps-cache-livewire-rate-limiter:f62e55b866645c06522164f85beb7938b347d20a','i:1;',1757640338),
('lampegan-motor-apps-cache-livewire-rate-limiter:f62e55b866645c06522164f85beb7938b347d20a:timer','i:1757640338;',1757640338),
('lampegan-motor-apps-cache-livewire-rate-limiter:f6bd13b623aa2597b5ff8d680f45aac8f6d6a1c6','i:1;',1757555739),
('lampegan-motor-apps-cache-livewire-rate-limiter:f6bd13b623aa2597b5ff8d680f45aac8f6d6a1c6:timer','i:1757555739;',1757555739),
('lampegan-motor-apps-cache-livewire-rate-limiter:fa12b99d97bebb5757a6e3a6f76a16925d654501','i:1;',1757498357),
('lampegan-motor-apps-cache-livewire-rate-limiter:fa12b99d97bebb5757a6e3a6f76a16925d654501:timer','i:1757498357;',1757498357),
('lampegan-motor-apps-cache-spatie.translation-loader.(and :count more error).en','a:0:{}',2072860608),
('lampegan-motor-apps-cache-spatie.translation-loader.(and :count more error).id','a:0:{}',2072860608),
('lampegan-motor-apps-cache-spatie.translation-loader.(and :count more errors).en','a:0:{}',2072859632),
('lampegan-motor-apps-cache-spatie.translation-loader.(and :count more errors).id','a:0:{}',2072859632),
('lampegan-motor-apps-cache-spatie.translation-loader.*.en','a:0:{}',2072856813),
('lampegan-motor-apps-cache-spatie.translation-loader.*.id','a:0:{}',2072851998),
('lampegan-motor-apps-cache-spatie.translation-loader.alamat.en','a:0:{}',2072861379),
('lampegan-motor-apps-cache-spatie.translation-loader.alamat.id','a:0:{}',2072861379),
('lampegan-motor-apps-cache-spatie.translation-loader.available.en','a:0:{}',2072909348),
('lampegan-motor-apps-cache-spatie.translation-loader.available.id','a:0:{}',2072909348),
('lampegan-motor-apps-cache-spatie.translation-loader.dashboard.id','a:0:{}',2072856143),
('lampegan-motor-apps-cache-spatie.translation-loader.Forbidden.en','a:0:{}',2072851998),
('lampegan-motor-apps-cache-spatie.translation-loader.Forbidden.id','a:0:{}',2072851998),
('lampegan-motor-apps-cache-spatie.translation-loader.Go to page :page.en','a:0:{}',2073017853),
('lampegan-motor-apps-cache-spatie.translation-loader.Go to page :page.id','a:0:{}',2073017853),
('lampegan-motor-apps-cache-spatie.translation-loader.hold.en','a:0:{}',2072909348),
('lampegan-motor-apps-cache-spatie.translation-loader.hold.id','a:0:{}',2072909348),
('lampegan-motor-apps-cache-spatie.translation-loader.in_repair.en','a:0:{}',2072909348),
('lampegan-motor-apps-cache-spatie.translation-loader.in_repair.id','a:0:{}',2072909348),
('lampegan-motor-apps-cache-spatie.translation-loader.months.id','a:0:{}',2072856145),
('lampegan-motor-apps-cache-spatie.translation-loader.navigation.en','a:0:{}',2072856308),
('lampegan-motor-apps-cache-spatie.translation-loader.navigation.id','a:0:{}',2072856143),
('lampegan-motor-apps-cache-spatie.translation-loader.Not Found.en','a:0:{}',2072852339),
('lampegan-motor-apps-cache-spatie.translation-loader.Not Found.id','a:0:{}',2072852339),
('lampegan-motor-apps-cache-spatie.translation-loader.of.en','a:0:{}',2073017853),
('lampegan-motor-apps-cache-spatie.translation-loader.of.id','a:0:{}',2073017853),
('lampegan-motor-apps-cache-spatie.translation-loader.Page Expired.en','a:0:{}',2072854593),
('lampegan-motor-apps-cache-spatie.translation-loader.Page Expired.id','a:0:{}',2072854593),
('lampegan-motor-apps-cache-spatie.translation-loader.Pagination Navigation.en','a:0:{}',2073017853),
('lampegan-motor-apps-cache-spatie.translation-loader.Pagination Navigation.id','a:0:{}',2073017853),
('lampegan-motor-apps-cache-spatie.translation-loader.pagination.en','a:0:{}',2073017853),
('lampegan-motor-apps-cache-spatie.translation-loader.pagination.id','a:0:{}',2073017853),
('lampegan-motor-apps-cache-spatie.translation-loader.results.en','a:0:{}',2073017853),
('lampegan-motor-apps-cache-spatie.translation-loader.results.id','a:0:{}',2073017853),
('lampegan-motor-apps-cache-spatie.translation-loader.Server Error.en','a:0:{}',2072925310),
('lampegan-motor-apps-cache-spatie.translation-loader.Server Error.id','a:0:{}',2072925310),
('lampegan-motor-apps-cache-spatie.translation-loader.Showing.en','a:0:{}',2073017853),
('lampegan-motor-apps-cache-spatie.translation-loader.Showing.id','a:0:{}',2073017853),
('lampegan-motor-apps-cache-spatie.translation-loader.sold.en','a:0:{}',2072909348),
('lampegan-motor-apps-cache-spatie.translation-loader.sold.id','a:0:{}',2072909348),
('lampegan-motor-apps-cache-spatie.translation-loader.status_available.en','a:0:{}',2072909348),
('lampegan-motor-apps-cache-spatie.translation-loader.status_available.id','a:0:{}',2072909348),
('lampegan-motor-apps-cache-spatie.translation-loader.status_converted.en','a:0:{}',2072909348),
('lampegan-motor-apps-cache-spatie.translation-loader.status_converted.id','a:0:{}',2072909348),
('lampegan-motor-apps-cache-spatie.translation-loader.status_hold.en','a:0:{}',2072909348),
('lampegan-motor-apps-cache-spatie.translation-loader.status_hold.id','a:0:{}',2072909348),
('lampegan-motor-apps-cache-spatie.translation-loader.status_in_repair.en','a:0:{}',2072909348),
('lampegan-motor-apps-cache-spatie.translation-loader.status_in_repair.id','a:0:{}',2072909348),
('lampegan-motor-apps-cache-spatie.translation-loader.status_rejected.en','a:0:{}',2072909348),
('lampegan-motor-apps-cache-spatie.translation-loader.status_rejected.id','a:0:{}',2072909348),
('lampegan-motor-apps-cache-spatie.translation-loader.status_sold.en','a:0:{}',2072909348),
('lampegan-motor-apps-cache-spatie.translation-loader.status_sold.id','a:0:{}',2072909348),
('lampegan-motor-apps-cache-spatie.translation-loader.tables.en','a:0:{}',2072856318),
('lampegan-motor-apps-cache-spatie.translation-loader.tables.id','a:0:{}',2072856211),
('lampegan-motor-apps-cache-spatie.translation-loader.to.en','a:0:{}',2073017853),
('lampegan-motor-apps-cache-spatie.translation-loader.to.id','a:0:{}',2073017853),
('lampegan-motor-apps-cache-spatie.translation-loader.validation.en','a:0:{}',2072858078),
('lampegan-motor-apps-cache-spatie.translation-loader.validation.id','a:0:{}',2072858078),
('lampegan-motor-apps-cache-spatie.translation-loader.widgets.id','a:0:{}',2072856145);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
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
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES
(1,'Bagi Hasil Usaha','income','2025-08-27 18:35:52','2025-08-27 18:35:52'),
(2,'Pajak','income','2025-09-09 18:35:25','2025-09-09 18:35:25'),
(3,'Rekondisi','income','2025-09-09 18:36:10','2025-09-09 18:36:10'),
(4,'OPERASIONAL','income','2025-09-10 03:04:45','2025-09-10 03:04:45'),
(5,'BIAYA DAPUR','income','2025-09-15 00:46:34','2025-09-15 00:47:58'),
(6,'GAJIAN','income','2025-09-15 00:58:31','2025-09-15 00:58:31');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `colors`
--

DROP TABLE IF EXISTS `colors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `colors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `colors_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `colors`
--

LOCK TABLES `colors` WRITE;
/*!40000 ALTER TABLE `colors` DISABLE KEYS */;
INSERT INTO `colors` VALUES
(1,'HITAM','2025-08-27 18:36:13','2025-09-10 02:53:40'),
(2,'HIJAU','2025-09-10 02:54:14','2025-09-10 02:54:14'),
(3,'ABU','2025-09-10 02:54:45','2025-09-10 02:54:45'),
(4,'MERAH','2025-09-10 02:55:14','2025-09-10 02:55:14'),
(5,'UNGU MUDA','2025-09-10 02:55:33','2025-09-10 02:55:33'),
(6,'ORANGE','2025-09-10 02:57:14','2025-09-10 02:57:52'),
(7,'NAVY','2025-09-10 02:58:18','2025-09-10 02:58:18'),
(8,'PUTIH','2025-09-10 02:58:32','2025-09-10 02:58:32'),
(9,'BIRU PUTIH','2025-09-10 02:59:50','2025-09-10 02:59:50'),
(10,'MERAH HITAM','2025-09-10 03:00:01','2025-09-10 03:00:01'),
(11,'HIJAU HITAM','2025-09-10 03:00:22','2025-09-10 03:00:22'),
(12,'SILVER','2025-09-10 03:01:06','2025-09-10 03:01:06'),
(13,'BIRU','2025-09-11 19:54:46','2025-09-11 19:54:46'),
(14,'BIRU HITAM','2025-09-12 00:02:25','2025-09-12 00:02:25'),
(15,'HITAM ABU','2025-09-12 00:38:31','2025-09-12 00:38:31'),
(16,'PERAK','2025-09-12 00:53:29','2025-09-12 00:53:29'),
(17,'PERAK BIRU','2025-09-12 01:30:56','2025-09-12 01:30:56');
/*!40000 ALTER TABLE `colors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `nik` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `instagram` varchar(191) DEFAULT NULL,
  `tiktok` varchar(191) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_nik_unique` (`nik`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES
(1,'DENDI SETIADI SAPUTRA',NULL,'085643792736',NULL,NULL,'KP SUKARAME RT03/02 DS PACET \n','2025-09-10 03:36:04','2025-09-10 03:36:04'),
(2,'RATNA NINGSIH',NULL,'089662725562',NULL,NULL,'KP.CILOLONGOKAN RT04/07 DESA.LIMBANGAN BARAT,KEC.BL LIMBANGAN','2025-09-15 01:25:23','2025-09-15 01:25:23'),
(3,'HENDRI',NULL,'08811816751',NULL,NULL,'KP.PANYAWEUYAN RT03/01 DESA.LAMPEGAN,KEC.IBUN','2025-09-15 19:10:38','2025-09-15 19:10:38'),
(4,'OKI SAPPUDIN',NULL,'082224324447',NULL,NULL,'KP.SUKAMANAH RT02/12 DESA.SUKAMANAH,KEC.PASEH','2025-09-15 19:18:25','2025-09-15 19:18:25'),
(5,'MIPTAHUL ULUM',NULL,'0895323381647',NULL,NULL,'KP.SITUMUNGKAL RT04/05 DESA.SINDANGSARI,KEC.PASEH','2025-09-15 21:19:57','2025-09-15 21:19:57'),
(6,'NITA',NULL,'081316318398',NULL,NULL,'KP.CIERI RT03/02 DESA.LAMPEGAN,KEC.IBUN','2025-09-15 22:04:36','2025-09-15 22:04:36'),
(7,'AYI CAHAYATI',NULL,'08122096906',NULL,NULL,'SADANG RT01/03 DESA.SUKAMANTRI,KEC.PASEH','2025-09-15 22:10:33','2025-09-15 22:10:33');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `expense_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sale_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_category_id_foreign` (`category_id`),
  KEY `expenses_sale_id_foreign` (`sale_id`),
  CONSTRAINT `expenses_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `expenses_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
INSERT INTO `expenses` VALUES
(1,'UANG JALA',4,150000.00,'2025-09-09','2025-09-10 03:06:21','2025-09-10 03:06:21',NULL),
(2,'BELI TAHU/TEMPE',5,50000.00,'2025-09-01','2025-09-15 00:50:16','2025-09-15 00:50:16',NULL),
(3,'ACONG',3,300000.00,'2025-09-01','2025-09-15 00:52:14','2025-09-15 00:52:14',NULL),
(4,'ARIF',3,76000.00,'2025-09-01','2025-09-15 00:52:46','2025-09-15 00:52:46',NULL),
(5,'PA UDI',1,200000.00,'2025-09-01','2025-09-15 00:53:19','2025-09-15 00:53:19',NULL),
(6,'DENDRA',3,30000.00,'2025-09-02','2025-09-15 00:55:05','2025-09-15 00:55:05',NULL),
(7,'MANG ENDANG',4,100000.00,'2025-09-02','2025-09-15 00:55:44','2025-09-15 00:55:44',NULL),
(8,'WIFI',4,385000.00,'2025-09-15','2025-09-15 07:09:03','2025-09-15 07:09:03',NULL),
(9,'BELI GAS',5,120000.00,'2025-09-15','2025-09-15 08:01:56','2025-09-15 08:02:13',NULL),
(10,'PAKET COD IBU',4,208000.00,'2025-09-15','2025-09-15 08:03:23','2025-09-15 08:03:23',NULL),
(11,'Pa UDI/BELI KOPI',5,100000.00,'2025-09-15','2025-09-15 08:04:47','2025-09-15 08:04:47',NULL);
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
-- Table structure for table `hero_slides`
--

DROP TABLE IF EXISTS `hero_slides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `hero_slides` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_column` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hero_slides`
--

LOCK TABLES `hero_slides` WRITE;
/*!40000 ALTER TABLE `hero_slides` DISABLE KEYS */;
INSERT INTO `hero_slides` VALUES
(1,0,'hero-slides/01K4S2EY02H0CMF2A3ZQ24FHAT.jpg','Welcome Lampegan Motor','Wilujeung Sumping ','2025-09-09 22:44:31','2025-09-09 22:44:31'),
(2,0,'hero-slides/01K4S2X8GQRPYF442QQ0R3754Q.jpg','Dapatkan Harga Promo','Hanya Di Lampegan Motor','2025-09-09 22:52:21','2025-09-09 22:52:21');
/*!40000 ALTER TABLE `hero_slides` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incomes`
--

DROP TABLE IF EXISTS `incomes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `incomes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `income_date` date NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sale_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `incomes_category_id_foreign` (`category_id`),
  KEY `incomes_customer_id_foreign` (`customer_id`),
  KEY `incomes_sale_id_foreign` (`sale_id`),
  CONSTRAINT `incomes_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `incomes_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `incomes_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incomes`
--

LOCK TABLES `incomes` WRITE;
/*!40000 ALTER TABLE `incomes` DISABLE KEYS */;
INSERT INTO `incomes` VALUES
(1,'Jasa Pengurusan Pajak',1,100000.00,'2025-09-02',NULL,NULL,'2025-09-01 21:39:38','2025-09-01 21:39:38',NULL);
/*!40000 ALTER TABLE `incomes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
-- Table structure for table `language_lines`
--

DROP TABLE IF EXISTS `language_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `language_lines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(255) NOT NULL,
  `key` text NOT NULL,
  `text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`text`)),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `namespace` varchar(255) NOT NULL DEFAULT '*',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `language_lines_group_index` (`group`),
  KEY `language_lines_namespace_index` (`namespace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `language_lines`
--

LOCK TABLES `language_lines` WRITE;
/*!40000 ALTER TABLE `language_lines` DISABLE KEYS */;
/*!40000 ALTER TABLE `language_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'2025_08_27_064728_create_brands_table',1),
(5,'2025_08_27_064820_create_types_table',1),
(6,'2025_08_27_065236_create_vehicle_models_table',1),
(7,'2025_08_27_065426_create_colors_table',1),
(8,'2025_08_27_065431_create_years_table',1),
(9,'2025_08_27_065440_create_suppliers_table',1),
(10,'2025_08_27_065450_create_customers_table',1),
(11,'2025_08_27_065451_create_vehicles_table',1),
(12,'2025_08_27_065458_create_purchases_table',1),
(13,'2025_08_27_065507_create_sales_table',1),
(14,'2025_08_27_065521_create_categories_table',1),
(15,'2025_08_27_065527_create_expenses_table',1),
(16,'2025_08_27_065543_create_other_assets_table',1),
(17,'2025_08_27_070540_create_incomes_table',1),
(18,'2025_08_27_071547_create_additional_costs_table',1),
(19,'2025_08_27_072854_create_vehicle_photos_table',1),
(20,'2025_08_27_074039_add_role_to_users_table',1),
(21,'2025_08_28_024002_create_personal_access_tokens_table',2),
(22,'2025_08_28_034626_add_year_column_to_years_table',3),
(23,'2025_08_31_142900_add_fields_to_vehicles_table',4),
(24,'2025_09_01_082828_make_vehicle_id_nullable_in_vehicle_photos_table',5),
(25,'2025_09_01_142901_create_requests_table',6),
(26,'2025_09_01_142902_add_license_plate_to_requests_table',6),
(27,'2025_09_01_142902_add_request_id_to_vehicle_photos_table',6),
(28,'2025_09_01_151858_add_deleted_at_to_vehicles_table',6),
(29,'2025_09_02_073313_create_permission_tables',7),
(30,'2022_01_25_010712_create_language_lines_table',8),
(31,'2025_09_10_020825_add_odometer_to_vehicles_table',9),
(32,'2025_09_10_020949_add_dealer_to_suppliers_table',10),
(33,'2025_09_10_024506_create_purchase_additional_costs_table',10),
(34,'2025_09_10_042619_create_hero_slides_table',10),
(35,'2025_09_10_044650_add_order_to_hero_slides_table',10),
(36,'2025_09_10_045045_rename_order_column_on_hero_slides_table',10),
(37,'2025_09_10_073324_add_fields_to_sales_table',10),
(38,'2025_09_10_130000_link_income_expense_to_sales',10);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `other_assets`
--

DROP TABLE IF EXISTS `other_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `other_assets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `value` decimal(15,2) NOT NULL,
  `acquisition_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `other_assets`
--

LOCK TABLES `other_assets` WRITE;
/*!40000 ALTER TABLE `other_assets` DISABLE KEYS */;
INSERT INTO `other_assets` VALUES
(1,'Ruko','Ruko Majalaya 2Lantai',1000000000.00,'2025-01-01','2025-08-30 06:32:44','2025-08-30 06:32:44');
/*!40000 ALTER TABLE `other_assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_additional_costs`
--

DROP TABLE IF EXISTS `purchase_additional_costs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_additional_costs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_additional_costs_purchase_id_foreign` (`purchase_id`),
  KEY `purchase_additional_costs_category_id_foreign` (`category_id`),
  CONSTRAINT `purchase_additional_costs_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_additional_costs_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_additional_costs`
--

LOCK TABLES `purchase_additional_costs` WRITE;
/*!40000 ALTER TABLE `purchase_additional_costs` DISABLE KEYS */;
INSERT INTO `purchase_additional_costs` VALUES
(1,1,3,100000.00,'2025-09-10 16:58:19','2025-09-10 16:58:19'),
(2,2,3,1000.00,'2025-09-14 17:27:53','2025-09-14 17:27:53'),
(3,2,3,100.00,'2025-09-14 17:27:53','2025-09-14 17:27:53');
/*!40000 ALTER TABLE `purchase_additional_costs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchases` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` bigint(20) unsigned NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `purchase_date` date NOT NULL,
  `total_price` decimal(15,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchases_vehicle_id_unique` (`vehicle_id`),
  KEY `purchases_supplier_id_foreign` (`supplier_id`),
  CONSTRAINT `purchases_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `purchases_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchases`
--

LOCK TABLES `purchases` WRITE;
/*!40000 ALTER TABLE `purchases` DISABLE KEYS */;
INSERT INTO `purchases` VALUES
(1,3,1,'2025-09-10',20000000.00,'Lm','2025-09-10 16:58:19','2025-09-10 16:58:19'),
(2,34,8,'2025-09-15',1502000.00,NULL,'2025-09-14 17:27:53','2025-09-14 17:27:53');
/*!40000 ALTER TABLE `purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `requests`
--

DROP TABLE IF EXISTS `requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `brand_id` bigint(20) unsigned DEFAULT NULL,
  `vehicle_model_id` bigint(20) unsigned DEFAULT NULL,
  `year_id` bigint(20) unsigned DEFAULT NULL,
  `odometer` int(10) unsigned DEFAULT NULL,
  `license_plate` varchar(20) DEFAULT NULL,
  `type` enum('buy','sell') NOT NULL DEFAULT 'sell',
  `status` enum('available','sold','in_repair','hold') NOT NULL DEFAULT 'hold',
  `notes` text DEFAULT NULL,
  `vehicle_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `requests_brand_id_foreign` (`brand_id`),
  KEY `requests_vehicle_model_id_foreign` (`vehicle_model_id`),
  KEY `requests_year_id_foreign` (`year_id`),
  KEY `requests_vehicle_id_foreign` (`vehicle_id`),
  KEY `requests_supplier_id_status_index` (`supplier_id`,`status`),
  CONSTRAINT `requests_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `requests_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `requests_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `requests_vehicle_model_id_foreign` FOREIGN KEY (`vehicle_model_id`) REFERENCES `vehicle_models` (`id`) ON DELETE SET NULL,
  CONSTRAINT `requests_year_id_foreign` FOREIGN KEY (`year_id`) REFERENCES `years` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requests`
--

LOCK TABLES `requests` WRITE;
/*!40000 ALTER TABLE `requests` DISABLE KEYS */;
INSERT INTO `requests` VALUES
(1,5,1,12,24,2000,'D 2556 ABC','sell','hold','Sedang dalam perbaikan',NULL,'2025-09-10 17:09:51','2025-09-10 17:09:51');
/*!40000 ALTER TABLE `requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `sale_date` date NOT NULL,
  `sale_price` decimal(15,2) NOT NULL,
  `payment_method` varchar(255) NOT NULL DEFAULT 'cash',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `remaining_payment` decimal(15,2) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('proses','kirim','selesai') NOT NULL DEFAULT 'proses',
  `cmo` varchar(255) DEFAULT NULL,
  `cmo_fee` decimal(15,2) DEFAULT NULL,
  `direct_commission` decimal(15,2) DEFAULT NULL,
  `order_source` enum('fb','ig','tiktok','walk_in') DEFAULT NULL,
  `result` enum('ACC','CASH','CANCEL') DEFAULT NULL,
  `branch_name` varchar(255) DEFAULT NULL,
  `dp_po` decimal(15,2) DEFAULT NULL,
  `dp_real` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_vehicle_id_unique` (`vehicle_id`),
  KEY `sales_customer_id_foreign` (`customer_id`),
  KEY `sales_user_id_foreign` (`user_id`),
  CONSTRAINT `sales_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `sales_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
INSERT INTO `sales` VALUES
(3,27,2,'2025-09-15',30700000.00,'credit','Acc','2025-09-15 01:50:52','2025-09-15 01:50:52',5,NULL,NULL,'selesai','Dhani',200000.00,50000.00,'walk_in','ACC','AF MAJALAYA',10200000.00,1000000.00),
(4,38,3,'2025-09-15',14700000.00,'credit','ACC','2025-09-15 19:13:16','2025-09-15 19:15:39',5,NULL,NULL,'selesai','DHANI',NULL,NULL,'walk_in','ACC','AF MAJALAYA',3700000.00,1800000.00),
(5,28,4,'2025-09-15',22600000.00,'cash','Cash','2025-09-15 19:30:59','2025-09-15 19:30:59',2,NULL,NULL,'selesai','-',NULL,NULL,'walk_in','CASH','-',NULL,NULL),
(6,44,1,'2025-09-09',29900000.00,'tukartambah','TUKAR TAMBAH','2025-09-15 21:17:09','2025-09-15 21:17:09',5,NULL,NULL,'selesai',NULL,NULL,NULL,'walk_in','CASH',NULL,NULL,NULL),
(7,43,5,'2025-09-06',30800000.00,'cash','CASH','2025-09-15 21:21:57','2025-09-15 21:21:57',5,NULL,NULL,'selesai',NULL,NULL,NULL,'walk_in','CASH',' CASH',NULL,NULL),
(8,45,6,'2025-09-06',13300000.00,'cash','CASH','2025-09-15 22:06:10','2025-09-15 22:06:10',5,NULL,NULL,'selesai',NULL,NULL,NULL,'walk_in','CASH','Cash',NULL,NULL);
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
INSERT INTO `sessions` VALUES
('3mq9d6sfNwyCZhoyufr6NY840UbfNTov9Fpg4hai',NULL,'103.194.173.99','Mozilla/5.0 (iPhone; CPU iPhone OS 17_0_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 Safari/604.1 trill_41.5.0 BytedanceWebview/d8a21c6','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMnVlVTYwU2xpUGxXM1FzUmg3NXN3MG5EaUpVZnh6NmJMbVFVU2dwbiI7czo2OiJsb2NhbGUiO3M6MjoiaWQiO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vbGFtcGVnYW5tb3RvcmJkZy5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1758063707),
('kZujXDWGZ3gvHarquudnTMOqh6JVdBOCdoRwjV57',NULL,'103.194.173.99','Mozilla/5.0 (iPhone; CPU iPhone OS 17_0_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 Safari/604.1 trill_41.5.0 BytedanceWebview/d8a21c6','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiY3RrcTJWdnRyNmNZdlJOZ1phdkpjSXM3Z1NSYXg1MGFlRmc1ZEhreCI7czo2OiJsb2NhbGUiO3M6MjoiaWQiO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vbGFtcGVnYW5tb3RvcmJkZy5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1758063707),
('nDbjQkIXAuzdPPevMD2OqliXJqjY869AIktkziiD',NULL,'103.194.173.99','Mozilla/5.0 (iPhone; CPU iPhone OS 17_0_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/140.0.7339.122 Mobile/15E148 Safari/604.1','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQTBSYWo0aktSdUFGS3Z2Q3lrMmZEUnlUckZzZmJGcllMMDlxRTlPWSI7czo2OiJsb2NhbGUiO3M6MjoiaWQiO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQwOiJodHRwczovL2xhbXBlZ2FubW90b3JiZGcuY29tL3ZlaGljbGVzLzQ2Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1758063952),
('PJbAfPWZmHLsE24rbhf2PWxQGzM1kVrFi8beRJnQ',NULL,'2404:c0:2910::a:fe8d','Mozilla/5.0 (Linux; Android 13; SM-A325F Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/139.0.7258.143 Mobile Safari/537.36 trill_410103 AppName/trill ByteLocale/id-ID','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiOTN4MDJkcndKbUVBanUwMWRPT014TllrbkFjTThXQXY0YWtqTTJ6UiI7czo2OiJsb2NhbGUiO3M6MjoiaWQiO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjY2OiJodHRwOi8vbGFtcGVnYW5tb3RvcmJkZy5jb20vP2JyYW5kPTEmcHJpY2U9MC00MDAwMDAwMCZ0eXBlPTEmeWVhcj0iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1758064119);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `dealer` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES
(1,'Asep',NULL,'0821312312','Majalaya','2025-09-09 18:31:05','2025-09-09 18:31:05'),
(2,'DENDI SETIADI SAPUTRA','PERSONAL','085643792736','KP SUKARAME RT03/02 DS PACET \n','2025-09-10 03:38:17','2025-09-10 03:38:45'),
(3,'CUSTOMER','Walk in ','081394510605','Kp lampegan ','2025-09-10 17:03:10','2025-09-10 17:03:10'),
(4,'MITRA SOLUSI LELANG','MITRA SOLUSI LELANG ','08111111111','Jl soekarno hatta bandung','2025-09-10 17:06:26','2025-09-10 17:06:32'),
(5,'ACEP HENDRA IRAWAN','HOLIS MOTOR','+62 896-5736-6031','Jalan holis bandung','2025-09-10 17:07:14','2025-09-10 17:07:14'),
(6,'ROBBY PRIMA WARDHANA','5758 MOTOR','+62 819-5959-4590','Kp cibolerang','2025-09-10 17:07:49','2025-09-10 17:07:49'),
(7,'OTTO SUHERMAN','Ato leader motor','+62 821-3070-4655','Kp panyadap','2025-09-10 17:08:24','2025-09-10 17:08:24'),
(8,'Alvin abdurahman','Abr mtr sukabumi ','+62 858-6361-9407','Sukabumi','2025-09-14 17:23:51','2025-09-14 17:23:51');
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `types`
--

DROP TABLE IF EXISTS `types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `types_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `types`
--

LOCK TABLES `types` WRITE;
/*!40000 ALTER TABLE `types` DISABLE KEYS */;
INSERT INTO `types` VALUES
(1,'MATIC','2025-08-27 20:40:39','2025-09-10 03:01:29'),
(2,'SPORT','2025-08-30 06:17:43','2025-09-10 03:01:41'),
(3,'BEBEK','2025-09-10 03:02:43','2025-09-10 03:02:43'),
(4,'TRAIL','2025-09-10 03:03:13','2025-09-10 03:16:21'),
(5,'KUPLING','2025-09-12 00:37:49','2025-09-12 00:37:49');
/*!40000 ALTER TABLE `types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('owner','admin','marketing') NOT NULL DEFAULT 'admin',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'cecep','cecep@fathforce.com','2025-08-27 07:58:15','$2y$12$5RMP5MG6Vn/qnKallrMsr.0Z3gHgZ8bXFoKIyY1RajBz2tLdbBke.','owner',NULL,'2025-08-27 00:52:07','2025-08-27 00:58:34'),
(2,'IQBAL','iqbalaza45@gmail.com',NULL,'$2y$12$R6z2HuGY/2X9swgW.3I4Je8sqaUkqepeh4to7Le0VAByOutBKBnHm','owner','nUwZtpL2dyhQQwkjepCz2RAZb4hWFaQn36EYfV8CAvWHkb6DXZ1A4TZqD4Xm','2025-08-27 00:57:02','2025-09-10 03:14:06'),
(3,'admin','admin@fathforce.com',NULL,'12345678','admin',NULL,NULL,NULL),
(4,'FELIA','felianafisamarwa@gmail.com','2008-08-06 01:00:00','$2y$12$IDG7L4IuEYu8a8Ot6lvQYuwIpVPRMiO0t7Vnpq92uvyDOMt2uYckq','admin',NULL,'2025-09-10 02:46:38','2025-09-10 02:46:38'),
(5,'NURUL','nurulsapitri666@gmail.com','2004-11-15 21:04:00','$2y$12$S3XI0CW9b7Ja0BWglpnjOO1Z.7M6KfBAYQ4WtJQxzEvq8js6k2voW','admin','0wnAsoFFgfIsHWDXXHWbxQueaD41RdJpxJUlct38lYFfHPGrxlF2xPYc6sZv','2025-09-10 02:48:12','2025-09-10 02:48:12'),
(6,'MUTIA','wulandarim023@gmail.com','2000-04-14 01:08:00','$2y$12$uLnG45CDWSX5W9nVgNnM6eEPmuIPi2ib0/DCgX999biC3KW34Vgui','admin','hVxihfWnZeLCCBBlGLwAkTomPPdz3Ytp2e9bgB3Ty9KBIOhodqvKf9lXclkS','2025-09-10 02:55:33','2025-09-10 02:55:33');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicle_models`
--

DROP TABLE IF EXISTS `vehicle_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicle_models` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `brand_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vehicle_models_brand_id_name_unique` (`brand_id`,`name`),
  CONSTRAINT `vehicle_models_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicle_models`
--

LOCK TABLES `vehicle_models` WRITE;
/*!40000 ALTER TABLE `vehicle_models` DISABLE KEYS */;
INSERT INTO `vehicle_models` VALUES
(1,1,'BEAT FI SPORTY CW ','2025-08-27 20:42:18','2025-09-10 17:01:19'),
(2,1,'ALL NEW VARIO 160 ABS','2025-08-31 06:37:39','2025-09-10 16:59:28'),
(3,1,'BEAT Fi POP','2025-09-10 02:54:48','2025-09-10 03:33:28'),
(4,1,'BEAT FI SPORTY CBS','2025-09-10 02:56:21','2025-09-10 03:32:52'),
(5,2,'GRAND FILLANO NEO','2025-09-10 03:13:40','2025-09-10 17:00:11'),
(6,1,'ALL NEW VARIO 160 CBS','2025-09-10 03:17:44','2025-09-10 17:00:45'),
(7,1,'SUPRA GTR 150 SPORTY','2025-09-10 03:38:52','2025-09-10 03:38:52'),
(8,2,'ALL NEW NMAX NEO','2025-09-10 16:44:01','2025-09-10 16:44:01'),
(9,1,'NEW PCX 160 CBS ','2025-09-10 16:55:26','2025-09-10 16:55:26'),
(10,1,'VARIO 125 ESP CBS ','2025-09-10 16:55:39','2025-09-10 16:55:39'),
(11,2,'N-MAX OLD ABS ','2025-09-10 17:01:48','2025-09-10 17:01:48'),
(12,1,'ALL NEW CBR 150 R STANDAR','2025-09-10 17:02:15','2025-09-10 17:02:15'),
(13,1,'NEW SCOOPY SPORTY','2025-09-10 18:37:56','2025-09-10 18:37:56'),
(14,1,'NEW SCOOPY PRESTIGE ','2025-09-10 18:38:51','2025-09-10 18:38:51'),
(15,1,'ADV 150 CBS ','2025-09-10 18:41:57','2025-09-10 18:41:57'),
(18,1,'CRF 150 L ','2025-09-10 20:36:12','2025-09-10 21:04:58'),
(19,2,'ALL NEW NMAX 155 CON ABS ','2025-09-10 21:03:42','2025-09-10 21:04:19'),
(20,4,'ALL NEW NINJA 250 FI ABS','2025-09-10 21:14:14','2025-09-10 21:14:14'),
(21,4,'ALL NEW NINJA 250 FI ','2025-09-10 21:19:22','2025-09-10 21:19:22'),
(22,6,'VESPA SPRINT 150 S I-GET ABS ','2025-09-10 21:20:06','2025-09-10 21:20:06'),
(23,2,'N-MAX OLD','2025-09-10 21:20:32','2025-09-10 21:31:04'),
(24,2,'ALL VIXION 155','2025-09-10 21:21:03','2025-09-10 21:31:26'),
(25,2,'ALL NEW R15 VVA','2025-09-10 21:21:55','2025-09-10 21:21:55'),
(26,2,'ALL NEW R15 M ABS','2025-09-10 21:23:17','2025-09-10 21:23:17'),
(27,2,'NEW AEROX ALPHA CIBERCITY','2025-09-10 21:23:52','2025-09-10 21:23:52'),
(28,2,'NEW AEROX 155 CIBERCITY','2025-09-10 21:24:13','2025-09-10 21:24:49'),
(29,2,'XMAX','2025-09-10 21:25:53','2025-09-10 21:25:53'),
(30,2,'ALL NEW NMAX 155 CON','2025-09-10 21:27:48','2025-09-10 21:29:11'),
(31,2,'R15 V2','2025-09-11 20:17:16','2025-09-11 20:17:16'),
(32,4,'NINJA RR 150','2025-09-11 21:09:15','2025-09-11 21:09:15'),
(33,2,'XABRE','2025-09-11 23:31:55','2025-09-11 23:31:55'),
(34,3,'GSX 150 S','2025-09-11 23:46:26','2025-09-11 23:46:26'),
(35,3,'SATRIA FU 150','2025-09-12 00:17:27','2025-09-12 00:17:27'),
(36,2,'GRAND FILANO LUX','2025-09-12 00:52:30','2025-09-12 00:52:30'),
(41,1,'NEW SCOOPY ENERGETIC','2025-09-13 00:59:21','2025-09-13 00:59:21'),
(42,2,'NEW AEROX C 155 ABS','2025-09-14 18:40:04','2025-09-14 18:40:04'),
(43,1,'CB VERZA 150 ','2025-09-14 19:09:16','2025-09-14 19:09:16'),
(44,1,'FORZA','2025-09-15 19:32:48','2025-09-15 19:33:27'),
(45,1,'ADV 160 CBS','2025-09-15 20:09:12','2025-09-15 20:09:12'),
(46,1,'GENIO ISS','2025-09-15 22:11:50','2025-09-15 22:11:50');
/*!40000 ALTER TABLE `vehicle_models` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicle_photos`
--

DROP TABLE IF EXISTS `vehicle_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicle_photos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned DEFAULT NULL,
  `vehicle_id` bigint(20) unsigned DEFAULT NULL,
  `path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `photo_order` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vehicle_photos_vehicle_id_foreign` (`vehicle_id`),
  KEY `vehicle_photos_request_id_vehicle_id_index` (`request_id`,`vehicle_id`),
  CONSTRAINT `vehicle_photos_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vehicle_photos_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=190 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicle_photos`
--

LOCK TABLES `vehicle_photos` WRITE;
/*!40000 ALTER TABLE `vehicle_photos` DISABLE KEYS */;
INSERT INTO `vehicle_photos` VALUES
(1,NULL,2,'vehicle-photos/01K40989HM0YHNCCSS2E0BAJS3.jpg',NULL,0,'2025-08-31 07:42:13','2025-08-31 07:42:13'),
(2,NULL,2,'vehicle-photos/01K40989HTATGRW5HWPMP9C3T3.jpg',NULL,0,'2025-08-31 07:42:13','2025-08-31 07:42:13'),
(4,NULL,3,'vehicle-photos/01K4SK0XHHTSF3DFDG868FC0V3.jpeg',NULL,0,'2025-09-10 03:33:58','2025-09-10 03:33:58'),
(5,NULL,5,'vehicle-photos/01K4VKVMDJJN7GB6FHE2DTRV4E.jpeg',NULL,0,'2025-09-10 22:27:02','2025-09-10 22:27:02'),
(6,NULL,5,'vehicle-photos/01K4VKVMDP5AJ0YY2EGP8EBESJ.jpeg',NULL,0,'2025-09-10 22:27:02','2025-09-10 22:27:02'),
(7,NULL,5,'vehicle-photos/01K4VKVMDRQ4KMJSQTEWG4VA6J.jpeg',NULL,0,'2025-09-10 22:27:02','2025-09-10 22:27:02'),
(8,NULL,5,'vehicle-photos/01K4VKVMDSE5MYQWBR141CE2J2.jpeg',NULL,0,'2025-09-10 22:27:02','2025-09-10 22:27:02'),
(9,NULL,6,'vehicle-photos/01K4VW8JY18Q5SPKA6DK6EDWWS.jpeg',NULL,0,'2025-09-11 00:53:55','2025-09-11 00:53:55'),
(10,NULL,6,'vehicle-photos/01K4VW8JY50DA7RQ16WBHDMMQ6.jpeg',NULL,0,'2025-09-11 00:53:55','2025-09-11 00:53:55'),
(11,NULL,6,'vehicle-photos/01K4VW8JY75NK33X0XDKP2TFN2.jpeg',NULL,0,'2025-09-11 00:53:55','2025-09-11 00:53:55'),
(12,NULL,6,'vehicle-photos/01K4VW8JY8H5QG0ESY1ATFB39K.jpeg',NULL,0,'2025-09-11 00:53:55','2025-09-11 00:53:55'),
(13,NULL,6,'vehicle-photos/01K4VW8JYACR2BCV5ARH2K0G8S.jpeg',NULL,0,'2025-09-11 00:53:55','2025-09-11 00:53:55'),
(14,NULL,7,'vehicle-photos/01K4XRMZ3494EMSYK8JBASYMT3.jpg',NULL,0,'2025-09-11 18:29:15','2025-09-11 18:29:15'),
(15,NULL,7,'vehicle-photos/01K4XRMZ3774T4291ABKZ8892Y.jpg',NULL,0,'2025-09-11 18:29:15','2025-09-11 18:29:15'),
(16,NULL,7,'vehicle-photos/01K4XRMZ39TBT6PBHRBZ81BABE.jpg',NULL,0,'2025-09-11 18:29:15','2025-09-11 18:29:15'),
(17,NULL,7,'vehicle-photos/01K4XRMZ3A6FVB2330XNHCNQ18.jpg',NULL,0,'2025-09-11 18:29:15','2025-09-11 18:29:15'),
(18,NULL,7,'vehicle-photos/01K4XRMZ3CY5VVG24V2VSFTJWZ.jpg',NULL,0,'2025-09-11 18:29:15','2025-09-11 18:29:15'),
(19,NULL,8,'vehicle-photos/01K4XT8MAY7EZ3H4KBSKTND259.jpg',NULL,0,'2025-09-11 18:57:28','2025-09-11 18:57:28'),
(20,NULL,8,'vehicle-photos/01K4XT8MB1RTVXG0YXZQM4PBDY.jpg',NULL,0,'2025-09-11 18:57:28','2025-09-11 18:57:28'),
(21,NULL,8,'vehicle-photos/01K4XT8MB30TXHXGAJHFNKJS46.jpg',NULL,0,'2025-09-11 18:57:28','2025-09-11 18:57:28'),
(22,NULL,8,'vehicle-photos/01K4XT8MB4SBN1HEXX9G42XR52.jpg',NULL,0,'2025-09-11 18:57:28','2025-09-11 18:57:28'),
(23,NULL,8,'vehicle-photos/01K4XT8MB6C010ZJ2WC7T4KQGC.jpg',NULL,0,'2025-09-11 18:57:28','2025-09-11 18:57:28'),
(24,NULL,9,'vehicle-photos/01K4XVG16334CD94VPN3AKZY4S.jpeg',NULL,0,'2025-09-11 19:18:59','2025-09-11 19:18:59'),
(25,NULL,9,'vehicle-photos/01K4XVG167Q5S1AZW2JPNWQH5K.jpeg',NULL,0,'2025-09-11 19:18:59','2025-09-11 19:18:59'),
(26,NULL,9,'vehicle-photos/01K4XVG168M27KKZMFC2S03YK0.jpeg',NULL,0,'2025-09-11 19:18:59','2025-09-11 19:18:59'),
(27,NULL,9,'vehicle-photos/01K4XVG169SYQG40BPFZDMPMN2.jpeg',NULL,0,'2025-09-11 19:18:59','2025-09-11 19:18:59'),
(28,NULL,9,'vehicle-photos/01K4XVG16BCRBN1F5AVC9N4AV0.jpeg',NULL,0,'2025-09-11 19:18:59','2025-09-11 19:18:59'),
(29,NULL,10,'vehicle-photos/01K4XW37P7GBWMR1T9515S1H2C.jpeg',NULL,0,'2025-09-11 19:29:28','2025-09-11 19:29:28'),
(30,NULL,10,'vehicle-photos/01K4XW37PBGB0SXXPE30K7PSS3.jpeg',NULL,0,'2025-09-11 19:29:28','2025-09-11 19:29:28'),
(31,NULL,10,'vehicle-photos/01K4XW37QDJCN7XT95WZ69CA76.jpeg',NULL,0,'2025-09-11 19:29:28','2025-09-11 19:29:28'),
(32,NULL,10,'vehicle-photos/01K4XW37QG4C44DAET18S2VKHK.jpeg',NULL,0,'2025-09-11 19:29:28','2025-09-11 19:29:28'),
(33,NULL,10,'vehicle-photos/01K4XW37QHA5HX103AVKX9CZME.jpeg',NULL,0,'2025-09-11 19:29:28','2025-09-11 19:29:28'),
(34,NULL,11,'vehicle-photos/01K4XXMGGKF8BEQWBHVE6PYPD9.jpeg',NULL,0,'2025-09-11 19:56:23','2025-09-11 19:56:23'),
(35,NULL,11,'vehicle-photos/01K4XXMGGQK9MYDXV7ZMA1GW66.jpeg',NULL,0,'2025-09-11 19:56:23','2025-09-11 19:56:23'),
(36,NULL,11,'vehicle-photos/01K4XXMGGTKCW8PJFV0B34CMAJ.jpeg',NULL,0,'2025-09-11 19:56:23','2025-09-11 19:56:23'),
(37,NULL,11,'vehicle-photos/01K4XXMGGW1RTS3WNB0X3V8692.jpeg',NULL,0,'2025-09-11 19:56:23','2025-09-11 19:56:23'),
(38,NULL,11,'vehicle-photos/01K4XXMGGYYN79781GRHRMAQ0P.jpeg',NULL,0,'2025-09-11 19:56:23','2025-09-11 19:56:23'),
(39,NULL,12,'vehicle-photos/01K4XZ02B83M2XEFVSAACXMB8M.jpeg',NULL,0,'2025-09-11 20:20:10','2025-09-11 20:20:10'),
(40,NULL,12,'vehicle-photos/01K4XZ02BB76D9TFCM6WT8XDDG.jpeg',NULL,0,'2025-09-11 20:20:10','2025-09-11 20:20:10'),
(41,NULL,12,'vehicle-photos/01K4XZ02BDHEB3NJD4A1331BR7.jpeg',NULL,0,'2025-09-11 20:20:10','2025-09-11 20:20:10'),
(42,NULL,12,'vehicle-photos/01K4XZ02BFTZS4GQV1W8YRDYM8.jpeg',NULL,0,'2025-09-11 20:20:10','2025-09-11 20:20:10'),
(43,NULL,12,'vehicle-photos/01K4XZ02BGWKAZN10NH83P848S.jpeg',NULL,0,'2025-09-11 20:20:10','2025-09-11 20:20:10'),
(44,NULL,13,'vehicle-photos/01K4XZG31FRGSNKJHWGRSM0B9V.jpeg',NULL,0,'2025-09-11 20:28:55','2025-09-11 20:28:55'),
(45,NULL,13,'vehicle-photos/01K4XZG31KNM4RJBTYARK3HEHG.jpeg',NULL,0,'2025-09-11 20:28:55','2025-09-11 20:28:55'),
(46,NULL,13,'vehicle-photos/01K4XZG31NY8TQKJMW3HXV5PPB.jpeg',NULL,0,'2025-09-11 20:28:55','2025-09-11 20:28:55'),
(47,NULL,13,'vehicle-photos/01K4XZG31PZ6KP8F8YRDSGFPRY.jpeg',NULL,0,'2025-09-11 20:28:55','2025-09-11 20:28:55'),
(48,NULL,13,'vehicle-photos/01K4XZG31RDGBJ36HV581CH7T8.jpeg',NULL,0,'2025-09-11 20:28:55','2025-09-11 20:28:55'),
(49,NULL,14,'vehicle-photos/01K4Y0MHWQG5VW7H4X5VM2XMFM.jpeg',NULL,0,'2025-09-11 20:48:50','2025-09-11 20:48:50'),
(50,NULL,14,'vehicle-photos/01K4Y0MHWVTDKYY346FTW2RGTA.jpeg',NULL,0,'2025-09-11 20:48:50','2025-09-11 20:48:50'),
(51,NULL,14,'vehicle-photos/01K4Y0MHWW3D3651MT7YJG3HT5.jpeg',NULL,0,'2025-09-11 20:48:50','2025-09-11 20:48:50'),
(52,NULL,14,'vehicle-photos/01K4Y0MHWY0J6SDXK9JB3NQPKC.jpeg',NULL,0,'2025-09-11 20:48:50','2025-09-11 20:48:50'),
(53,NULL,14,'vehicle-photos/01K4Y0MHWZMKWGQKPRQSXFYT4W.jpeg',NULL,0,'2025-09-11 20:48:50','2025-09-11 20:48:50'),
(54,NULL,15,'vehicle-photos/01K4Y16JBN5G61N80ZS29NQ4P9.jpeg',NULL,0,'2025-09-11 20:58:41','2025-09-11 20:58:41'),
(55,NULL,15,'vehicle-photos/01K4Y16JBSKWGEQGW9QFFHVXG3.jpeg',NULL,0,'2025-09-11 20:58:41','2025-09-11 20:58:41'),
(56,NULL,15,'vehicle-photos/01K4Y16JBWN0XX06BKMBHECAQA.jpeg',NULL,0,'2025-09-11 20:58:41','2025-09-11 20:58:41'),
(57,NULL,15,'vehicle-photos/01K4Y16JBYAJ6M1YT5YQ1B2ZDA.jpeg',NULL,0,'2025-09-11 20:58:41','2025-09-11 20:58:41'),
(58,NULL,15,'vehicle-photos/01K4Y16JC08F0JY0TNGMVSKGH6.jpeg',NULL,0,'2025-09-11 20:58:41','2025-09-11 20:58:41'),
(59,NULL,16,'vehicle-photos/01K4Y1KRRE9GJHXVWVV9VZMSTQ.jpeg',NULL,0,'2025-09-11 21:05:53','2025-09-11 21:05:53'),
(60,NULL,16,'vehicle-photos/01K4Y1KRRHXJZ09TJCVYZKSTNN.jpeg',NULL,0,'2025-09-11 21:05:53','2025-09-11 21:05:53'),
(61,NULL,16,'vehicle-photos/01K4Y1KRRKVFGX10B12VSQMKRS.jpeg',NULL,0,'2025-09-11 21:05:53','2025-09-11 21:05:53'),
(62,NULL,16,'vehicle-photos/01K4Y1KRS0MSKDD2QKFVB2QC5Y.jpeg',NULL,0,'2025-09-11 21:05:53','2025-09-11 21:05:53'),
(63,NULL,16,'vehicle-photos/01K4Y1KRSC0T5RTHY0881PFHAN.jpeg',NULL,0,'2025-09-11 21:05:53','2025-09-11 21:05:53'),
(64,NULL,17,'vehicle-photos/01K4Y22WMPJE3TTKY5XC2DS1Y0.jpeg',NULL,0,'2025-09-11 21:14:09','2025-09-11 21:14:09'),
(65,NULL,17,'vehicle-photos/01K4Y22WMT6EQNXVG0F96XMX0T.jpeg',NULL,0,'2025-09-11 21:14:09','2025-09-11 21:14:09'),
(66,NULL,17,'vehicle-photos/01K4Y22WMX664QJGBTAZ042M30.jpeg',NULL,0,'2025-09-11 21:14:09','2025-09-11 21:14:09'),
(67,NULL,17,'vehicle-photos/01K4Y22WN09WAYACKA3G3G5J1D.jpeg',NULL,0,'2025-09-11 21:14:09','2025-09-11 21:14:09'),
(68,NULL,17,'vehicle-photos/01K4Y22WN235DVPVCBQZZBX5ES.jpeg',NULL,0,'2025-09-11 21:14:09','2025-09-11 21:14:09'),
(69,NULL,18,'vehicle-photos/01K4Y9WWZ19MX4P4JJGSMVY0FD.jpeg',NULL,0,'2025-09-11 23:30:41','2025-09-11 23:30:41'),
(70,NULL,18,'vehicle-photos/01K4Y9WWZ5TPG5NW64Z9Y92PKP.jpeg',NULL,0,'2025-09-11 23:30:41','2025-09-11 23:30:41'),
(71,NULL,18,'vehicle-photos/01K4Y9WWZ6T21YRHTXZP8CZEWR.jpeg',NULL,0,'2025-09-11 23:30:41','2025-09-11 23:30:41'),
(72,NULL,18,'vehicle-photos/01K4Y9WWZ88KVZ976ZP6ZPND79.jpeg',NULL,0,'2025-09-11 23:30:41','2025-09-11 23:30:41'),
(73,NULL,18,'vehicle-photos/01K4Y9WWZ9A5VJR0NEVRJSW5MC.jpeg',NULL,0,'2025-09-11 23:30:41','2025-09-11 23:30:41'),
(74,NULL,19,'vehicle-photos/01K4YAQD6PVBRGAEVKJZ6FHBSC.jpeg',NULL,0,'2025-09-11 23:45:10','2025-09-11 23:45:10'),
(75,NULL,19,'vehicle-photos/01K4YAQD6SEBF1FTSW5GD8JK5W.jpeg',NULL,0,'2025-09-11 23:45:10','2025-09-11 23:45:10'),
(76,NULL,19,'vehicle-photos/01K4YAQD6VK0MSQPS9FERWB54Y.jpeg',NULL,0,'2025-09-11 23:45:10','2025-09-11 23:45:10'),
(77,NULL,19,'vehicle-photos/01K4YAQD6XZJQW14XAKETXTEFR.jpeg',NULL,0,'2025-09-11 23:45:10','2025-09-11 23:45:10'),
(78,NULL,19,'vehicle-photos/01K4YAQD6YGASSW4NB9BYC10XJ.jpeg',NULL,0,'2025-09-11 23:45:10','2025-09-11 23:45:10'),
(79,NULL,20,'vehicle-photos/01K4YCBMTWCTT78V0H1AWC6W1A.jpeg',NULL,0,'2025-09-12 00:13:41','2025-09-12 00:13:41'),
(80,NULL,20,'vehicle-photos/01K4YCBMV0NSSF66C9DZK4QKRZ.jpeg',NULL,0,'2025-09-12 00:13:41','2025-09-12 00:13:41'),
(81,NULL,20,'vehicle-photos/01K4YCBMV2ZPX5D9G1WSND17B9.jpeg',NULL,0,'2025-09-12 00:13:41','2025-09-12 00:13:41'),
(82,NULL,20,'vehicle-photos/01K4YCBMV3VWK9F7HVGA4G37GN.jpeg',NULL,0,'2025-09-12 00:13:41','2025-09-12 00:13:41'),
(83,NULL,20,'vehicle-photos/01K4YCBMV50V0FBVM2DKFFA652.jpeg',NULL,0,'2025-09-12 00:13:41','2025-09-12 00:13:41'),
(84,NULL,21,'vehicle-photos/01K4YEGB2ZW3G089KSSEK2439B.jpeg',NULL,0,'2025-09-12 00:51:12','2025-09-12 00:51:12'),
(85,NULL,21,'vehicle-photos/01K4YEGB32G7GDWB3V14W9KRAQ.jpeg',NULL,0,'2025-09-12 00:51:12','2025-09-12 00:51:12'),
(86,NULL,21,'vehicle-photos/01K4YEGB34DTWXCD7NTN05D2DF.jpeg',NULL,0,'2025-09-12 00:51:12','2025-09-12 00:51:12'),
(87,NULL,21,'vehicle-photos/01K4YEGB362V4ZYVSJ9282KJT0.jpeg',NULL,0,'2025-09-12 00:51:12','2025-09-12 00:51:12'),
(88,NULL,21,'vehicle-photos/01K4YEGB37TFTY3623BQR9Z9HY.jpeg',NULL,0,'2025-09-12 00:51:12','2025-09-12 00:51:12'),
(89,NULL,22,'vehicle-photos/01K4YF4DVGA2QQASMX9G2XJWS1.jpeg',NULL,0,'2025-09-12 01:02:10','2025-09-12 01:02:10'),
(90,NULL,22,'vehicle-photos/01K4YF4DVMPV9N19ZSCDTSKGV5.jpeg',NULL,0,'2025-09-12 01:02:11','2025-09-12 01:02:11'),
(91,NULL,22,'vehicle-photos/01K4YF4DVPPTVYCB550B2GW2T8.jpeg',NULL,0,'2025-09-12 01:02:11','2025-09-12 01:02:11'),
(92,NULL,22,'vehicle-photos/01K4YF4DVSPTRAJSEVX65V18QF.jpeg',NULL,0,'2025-09-12 01:02:11','2025-09-12 01:02:11'),
(93,NULL,22,'vehicle-photos/01K4YF4DVV4YSB8VADQTFJ06G9.jpeg',NULL,0,'2025-09-12 01:02:11','2025-09-12 01:02:11'),
(94,NULL,23,'vehicle-photos/01K4YFTSHN4NFJEDYK1GS8TNKT.jpeg',NULL,0,'2025-09-12 01:14:23','2025-09-12 01:14:23'),
(95,NULL,23,'vehicle-photos/01K4YFTSHT4JZ26VQ5JDKAFF5Q.jpeg',NULL,0,'2025-09-12 01:14:23','2025-09-12 01:14:23'),
(96,NULL,23,'vehicle-photos/01K4YFTSHWTRMEQKJWP5HBK0ND.jpeg',NULL,0,'2025-09-12 01:14:23','2025-09-12 01:14:23'),
(97,NULL,23,'vehicle-photos/01K4YFTSHZ6YQ4WB81CQADV6TE.jpeg',NULL,0,'2025-09-12 01:14:23','2025-09-12 01:14:23'),
(98,NULL,23,'vehicle-photos/01K4YFTSJ1ADBWRVTJRTMVBJSM.jpeg',NULL,0,'2025-09-12 01:14:23','2025-09-12 01:14:23'),
(99,NULL,24,'vehicle-photos/01K4YGFYJ1EVFPDGCC8FZGE8A3.jpeg',NULL,0,'2025-09-12 01:25:57','2025-09-12 01:25:57'),
(100,NULL,24,'vehicle-photos/01K4YGFYJ47M2G2CJ2SCZNPJZN.jpeg',NULL,0,'2025-09-12 01:25:57','2025-09-12 01:25:57'),
(101,NULL,24,'vehicle-photos/01K4YGFYJ6Q107FM7NHVWMF1GW.jpeg',NULL,0,'2025-09-12 01:25:57','2025-09-12 01:25:57'),
(102,NULL,24,'vehicle-photos/01K4YGFYJC3K25F40DCASKSMZH.jpeg',NULL,0,'2025-09-12 01:25:57','2025-09-12 01:25:57'),
(103,NULL,24,'vehicle-photos/01K4YGFYJEWMJMXHT22RGFR7Y0.jpeg',NULL,0,'2025-09-12 01:25:57','2025-09-12 01:25:57'),
(104,NULL,25,'vehicle-photos/01K4YJBBMGW4C7N056XTYWMJ2A.jpeg',NULL,0,'2025-09-12 01:58:23','2025-09-12 01:58:23'),
(105,NULL,25,'vehicle-photos/01K4YJBBMK370GBHB5VYTE5F8X.jpeg',NULL,0,'2025-09-12 01:58:23','2025-09-12 01:58:23'),
(106,NULL,25,'vehicle-photos/01K4YJBBMMSEDWEV6G5A4M0NT6.jpeg',NULL,0,'2025-09-12 01:58:23','2025-09-12 01:58:23'),
(107,NULL,25,'vehicle-photos/01K4YJBBMP2X5QFZYQ5T90MRAM.jpeg',NULL,0,'2025-09-12 01:58:23','2025-09-12 01:58:23'),
(108,NULL,25,'vehicle-photos/01K4YJBBMR3FQ2VRJ0XS0HR5TQ.jpeg',NULL,0,'2025-09-12 01:58:23','2025-09-12 01:58:23'),
(109,NULL,26,'vehicle-photos/01K4YMF5MQADA4CB0DKW6HN4C3.jpeg',NULL,0,'2025-09-12 02:35:25','2025-09-12 02:35:25'),
(110,NULL,26,'vehicle-photos/01K4YMF5MT8A23YPQE3JMSGE8A.jpeg',NULL,0,'2025-09-12 02:35:25','2025-09-12 02:35:25'),
(111,NULL,26,'vehicle-photos/01K4YMF5MW0ANAF3K0YK8T57ZG.jpeg',NULL,0,'2025-09-12 02:35:25','2025-09-12 02:35:25'),
(112,NULL,26,'vehicle-photos/01K4YMF5MXSW4N5MJMXKA6M75D.jpeg',NULL,0,'2025-09-12 02:35:25','2025-09-12 02:35:25'),
(113,NULL,26,'vehicle-photos/01K4YMF5MZZMTPMNERYADKMJH0.jpeg',NULL,0,'2025-09-12 02:35:25','2025-09-12 02:35:25'),
(114,NULL,27,'vehicle-photos/01K50QCTHV20ADNW50HB15B5WS.jpeg',NULL,0,'2025-09-12 22:05:03','2025-09-12 22:05:03'),
(115,NULL,27,'vehicle-photos/01K50QCTHZT1PQ2WVPXYD7FSC4.jpeg',NULL,0,'2025-09-12 22:05:03','2025-09-12 22:05:03'),
(116,NULL,27,'vehicle-photos/01K50QCTJ24M6W26G7KRKFNBS5.jpeg',NULL,0,'2025-09-12 22:05:03','2025-09-12 22:05:03'),
(117,NULL,27,'vehicle-photos/01K50QCTJ5BJTG28A369R2G6DW.jpeg',NULL,0,'2025-09-12 22:05:03','2025-09-12 22:05:03'),
(118,NULL,27,'vehicle-photos/01K50QCTJ8ND79AVMCJ98BV81Y.jpeg',NULL,0,'2025-09-12 22:05:03','2025-09-12 22:05:03'),
(119,NULL,28,'vehicle-photos/01K50TFHADTHBD7B1RTNR7KPQ4.jpeg',NULL,0,'2025-09-12 22:58:58','2025-09-12 22:58:58'),
(120,NULL,28,'vehicle-photos/01K50TFHAJJ5864JEH8CA1DY16.jpeg',NULL,0,'2025-09-12 22:58:58','2025-09-12 22:58:58'),
(121,NULL,28,'vehicle-photos/01K50TFHAKZX5A2WAB8DZ579FJ.jpeg',NULL,0,'2025-09-12 22:58:58','2025-09-12 22:58:58'),
(122,NULL,28,'vehicle-photos/01K50TFHAN7AS7ET82RG7V9ZD1.jpeg',NULL,0,'2025-09-12 22:58:58','2025-09-12 22:58:58'),
(123,NULL,28,'vehicle-photos/01K50TFHAPK0ZT7GVZGAB5H947.jpeg',NULL,0,'2025-09-12 22:58:58','2025-09-12 22:58:58'),
(124,NULL,29,'vehicle-photos/01K50V822DF3NVYCCXP7WZKFB9.jpeg',NULL,0,'2025-09-12 23:12:21','2025-09-12 23:12:21'),
(125,NULL,29,'vehicle-photos/01K50V822JG4J46R6KDNN80GET.jpeg',NULL,0,'2025-09-12 23:12:21','2025-09-12 23:12:21'),
(126,NULL,29,'vehicle-photos/01K50V822M2RRHCVP3Q3X1S39N.jpeg',NULL,0,'2025-09-12 23:12:21','2025-09-12 23:12:21'),
(127,NULL,29,'vehicle-photos/01K50V822RHGAPDKT85N6P4FP3.jpeg',NULL,0,'2025-09-12 23:12:21','2025-09-12 23:12:21'),
(128,NULL,29,'vehicle-photos/01K50V822V95ADVZ2V64ZTE62D.jpeg',NULL,0,'2025-09-12 23:12:21','2025-09-12 23:12:21'),
(129,NULL,30,'vehicle-photos/01K50VNKQH51TR3TH93G27PJC1.jpeg',NULL,0,'2025-09-12 23:19:45','2025-09-12 23:19:45'),
(130,NULL,30,'vehicle-photos/01K50VNKQMQZ7YZVAJC7PVSCNR.jpeg',NULL,0,'2025-09-12 23:19:45','2025-09-12 23:19:45'),
(131,NULL,30,'vehicle-photos/01K50VNKQPZ2RXZC9MGCDK8TT2.jpeg',NULL,0,'2025-09-12 23:19:45','2025-09-12 23:19:45'),
(132,NULL,30,'vehicle-photos/01K50VNKQRQ5SQKDETWMJ6SFBM.jpeg',NULL,0,'2025-09-12 23:19:45','2025-09-12 23:19:45'),
(133,NULL,30,'vehicle-photos/01K50VNKQSGMVXWACN6Z3PHK6X.jpeg',NULL,0,'2025-09-12 23:19:45','2025-09-12 23:19:45'),
(134,NULL,31,'vehicle-photos/01K50ZH2ZRWZH58Y05NCMM36SA.jpeg',NULL,0,'2025-09-13 00:27:11','2025-09-13 00:27:11'),
(135,NULL,31,'vehicle-photos/01K50ZH305BJR0XZVH7B034N4S.jpeg',NULL,0,'2025-09-13 00:27:11','2025-09-13 00:27:11'),
(136,NULL,31,'vehicle-photos/01K50ZH3086CGE4PZH2CD0HJWN.jpeg',NULL,0,'2025-09-13 00:27:11','2025-09-13 00:27:11'),
(137,NULL,31,'vehicle-photos/01K50ZH309GCPZEK7D5QSEV13N.jpeg',NULL,0,'2025-09-13 00:27:11','2025-09-13 00:27:11'),
(138,NULL,31,'vehicle-photos/01K50ZH30B17BV1CW2FFMAKN55.jpeg',NULL,0,'2025-09-13 00:27:11','2025-09-13 00:27:11'),
(139,NULL,32,'vehicle-photos/01K511TGHWHZ1N75P7NBXRTJ9G.jpeg',NULL,0,'2025-09-13 01:07:17','2025-09-13 01:07:17'),
(140,NULL,32,'vehicle-photos/01K511TGJ1HP06HJ3C4PA0E0EM.jpeg',NULL,0,'2025-09-13 01:07:17','2025-09-13 01:07:17'),
(141,NULL,32,'vehicle-photos/01K511TGJ284SFE0CWJ9K6QVNK.jpeg',NULL,0,'2025-09-13 01:07:17','2025-09-13 01:07:17'),
(142,NULL,32,'vehicle-photos/01K511TGJ425H0F623Z97BMPHJ.jpeg',NULL,0,'2025-09-13 01:07:17','2025-09-13 01:07:17'),
(143,NULL,32,'vehicle-photos/01K511TGJ56RJD20W1TG2KPX3W.jpeg',NULL,0,'2025-09-13 01:07:17','2025-09-13 01:07:17'),
(144,NULL,33,'vehicle-photos/01K513A18X1FZJ2PBNM5Q9RD5V.jpeg',NULL,0,'2025-09-13 01:33:15','2025-09-13 01:33:15'),
(145,NULL,33,'vehicle-photos/01K513A190ERZ52EEY0KA6VDAN.jpeg',NULL,0,'2025-09-13 01:33:15','2025-09-13 01:33:15'),
(146,NULL,33,'vehicle-photos/01K513A192CFCK2D8EV865TZKR.jpeg',NULL,0,'2025-09-13 01:33:15','2025-09-13 01:33:15'),
(147,NULL,33,'vehicle-photos/01K513A1945NJNHG48FA3FSFC0.jpeg',NULL,0,'2025-09-13 01:33:15','2025-09-13 01:33:15'),
(148,NULL,33,'vehicle-photos/01K513A195DVSV1MCZ61N88765.jpeg',NULL,0,'2025-09-13 01:33:15','2025-09-13 01:33:15'),
(149,NULL,34,'vehicle-photos/01K55C7MNYG6MZABX1G1QTEJWY.jpeg',NULL,0,'2025-09-14 17:26:11','2025-09-14 17:26:11'),
(150,NULL,35,'vehicle-photos/01K55HR6H4013R995M8MN6RAG1.jpeg',NULL,0,'2025-09-14 19:02:37','2025-09-14 19:02:37'),
(151,NULL,35,'vehicle-photos/01K55HR6HA78N0BT7SQCEQS7F7.jpeg',NULL,0,'2025-09-14 19:02:37','2025-09-14 19:02:37'),
(152,NULL,35,'vehicle-photos/01K55HR6HCT66SR4GZ2W1CYXCD.jpeg',NULL,0,'2025-09-14 19:02:37','2025-09-14 19:02:37'),
(153,NULL,35,'vehicle-photos/01K55HR6HEE7W7G3V5HVFXSPTN.jpeg',NULL,0,'2025-09-14 19:02:37','2025-09-14 19:02:37'),
(154,NULL,35,'vehicle-photos/01K55HR6HFFQGNFNG26KXYNBRC.jpeg',NULL,0,'2025-09-14 19:02:37','2025-09-14 19:02:37'),
(155,NULL,36,'vehicle-photos/01K55JK62SGG600TBW8M99048V.jpeg',NULL,0,'2025-09-14 19:17:21','2025-09-14 19:17:21'),
(156,NULL,36,'vehicle-photos/01K55JK64JQX0ZJGRXKEVDDTE6.jpeg',NULL,0,'2025-09-14 19:17:21','2025-09-14 19:17:21'),
(157,NULL,36,'vehicle-photos/01K55JK64MCT8BR6CJEH97W8TF.jpeg',NULL,0,'2025-09-14 19:17:21','2025-09-14 19:17:21'),
(158,NULL,36,'vehicle-photos/01K55JK64NPS20P8Z4PVT93ZAB.jpeg',NULL,0,'2025-09-14 19:17:21','2025-09-14 19:17:21'),
(159,NULL,36,'vehicle-photos/01K55JK64QQ82AA6HKRQTVJWYE.jpeg',NULL,0,'2025-09-14 19:17:21','2025-09-14 19:17:21'),
(160,NULL,37,'vehicle-photos/01K55K1J0PECM6PC8KERXEFGK8.jpeg',NULL,0,'2025-09-14 19:25:12','2025-09-14 19:25:12'),
(161,NULL,37,'vehicle-photos/01K55K1J0S10HYJX34PQ1FTHS0.jpeg',NULL,0,'2025-09-14 19:25:12','2025-09-14 19:25:12'),
(162,NULL,37,'vehicle-photos/01K55K1J0VCZR18SYAR8XSPC9H.jpeg',NULL,0,'2025-09-14 19:25:12','2025-09-14 19:25:12'),
(163,NULL,37,'vehicle-photos/01K55K1J0X6ZF89QT2FTFMQW9A.jpeg',NULL,0,'2025-09-14 19:25:12','2025-09-14 19:25:12'),
(164,NULL,37,'vehicle-photos/01K55K1J0ZFHFM34XJ073A8V2S.jpeg',NULL,0,'2025-09-14 19:25:12','2025-09-14 19:25:12'),
(165,NULL,38,'vehicle-photos/01K584H46F6Z4RM4J2VP17A8H1.jpeg',NULL,0,'2025-09-15 19:09:17','2025-09-15 19:09:17'),
(166,NULL,38,'vehicle-photos/01K584H46M9T0Q9MJBR8ZCKNQP.jpeg',NULL,0,'2025-09-15 19:09:17','2025-09-15 19:09:17'),
(167,NULL,38,'vehicle-photos/01K584H46QK7S6NPBM56XTBMTP.jpeg',NULL,0,'2025-09-15 19:09:17','2025-09-15 19:09:17'),
(168,NULL,38,'vehicle-photos/01K584H46S3EPEQDAPEB7RRH6Y.jpeg',NULL,0,'2025-09-15 19:09:17','2025-09-15 19:09:17'),
(169,NULL,38,'vehicle-photos/01K584H46V4R666627YYBTQX2C.jpeg',NULL,0,'2025-09-15 19:09:17','2025-09-15 19:09:17'),
(170,NULL,43,'vehicle-photos/01K58A2PQFTR4HJ5F6H69W64F3.jpeg',NULL,0,'2025-09-15 20:46:15','2025-09-15 20:46:15'),
(171,NULL,43,'vehicle-photos/01K58A2PQJ87HH010BA636HN0X.jpeg',NULL,0,'2025-09-15 20:46:15','2025-09-15 20:46:15'),
(172,NULL,43,'vehicle-photos/01K58A2PQMSKEHTCWHDJBJYY2J.jpeg',NULL,0,'2025-09-15 20:46:15','2025-09-15 20:46:15'),
(173,NULL,43,'vehicle-photos/01K58A2PQNW8ZF75MFV56H3EWC.jpeg',NULL,0,'2025-09-15 20:46:15','2025-09-15 20:46:15'),
(174,NULL,43,'vehicle-photos/01K58A2PQQKCABF5NEGC37HM32.jpeg',NULL,0,'2025-09-15 20:46:15','2025-09-15 20:46:15'),
(175,NULL,44,'vehicle-photos/01K58BQDGFCSQY50Y2CWJMGKWE.jpeg',NULL,0,'2025-09-15 21:15:03','2025-09-15 21:15:03'),
(176,NULL,44,'vehicle-photos/01K58BQDGJZ6X88483KACJ0CEJ.jpeg',NULL,0,'2025-09-15 21:15:03','2025-09-15 21:15:03'),
(177,NULL,44,'vehicle-photos/01K58BQDGKJP49GWVS5A33MSXD.jpeg',NULL,0,'2025-09-15 21:15:03','2025-09-15 21:15:03'),
(178,NULL,44,'vehicle-photos/01K58BQDGMJTKJGNHM890NQVZY.jpeg',NULL,0,'2025-09-15 21:15:03','2025-09-15 21:15:03'),
(179,NULL,44,'vehicle-photos/01K58BQDGP3598YG1XRRAQNM1T.jpeg',NULL,0,'2025-09-15 21:15:03','2025-09-15 21:15:03'),
(180,NULL,45,'vehicle-photos/01K58E389FB3B2FJ36WJMS5F79.jpeg',NULL,0,'2025-09-15 21:56:28','2025-09-15 21:56:28'),
(181,NULL,45,'vehicle-photos/01K58E389KS9JV8V5G2FDBWGS4.jpeg',NULL,0,'2025-09-15 21:56:28','2025-09-15 21:56:28'),
(182,NULL,45,'vehicle-photos/01K58E389NCHT922MZX7WF1X71.jpeg',NULL,0,'2025-09-15 21:56:28','2025-09-15 21:56:28'),
(183,NULL,45,'vehicle-photos/01K58E389RNRBQYWMR160ZF67W.jpeg',NULL,0,'2025-09-15 21:56:28','2025-09-15 21:56:28'),
(184,NULL,45,'vehicle-photos/01K58E389TM6VMZXN84CH1AXRZ.jpeg',NULL,0,'2025-09-15 21:56:28','2025-09-15 21:56:28'),
(185,NULL,46,'vehicle-photos/01K58FJM9B95HFKK3DRNRVDV6T.jpeg',NULL,0,'2025-09-15 22:22:20','2025-09-15 22:22:20'),
(186,NULL,46,'vehicle-photos/01K58FJM9GFGJ2TEYM724JB66H.jpeg',NULL,0,'2025-09-15 22:22:20','2025-09-15 22:22:20'),
(187,NULL,46,'vehicle-photos/01K58FJM9HJQEPMCMPP3ERYK82.jpeg',NULL,0,'2025-09-15 22:22:20','2025-09-15 22:22:20'),
(188,NULL,46,'vehicle-photos/01K58FJM9KHSZ2YB0EH7XB3YT1.jpeg',NULL,0,'2025-09-15 22:22:20','2025-09-15 22:22:20'),
(189,NULL,46,'vehicle-photos/01K58FJM9M8Q7Q73ZF7Z9YJWVM.jpeg',NULL,0,'2025-09-15 22:22:20','2025-09-15 22:22:20');
/*!40000 ALTER TABLE `vehicle_photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicles`
--

DROP TABLE IF EXISTS `vehicles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_model_id` bigint(20) unsigned NOT NULL,
  `type_id` bigint(20) unsigned NOT NULL,
  `color_id` bigint(20) unsigned NOT NULL,
  `year_id` bigint(20) unsigned NOT NULL,
  `odometer` int(10) unsigned DEFAULT NULL,
  `vin` varchar(255) NOT NULL,
  `engine_number` varchar(255) NOT NULL,
  `license_plate` varchar(255) DEFAULT NULL,
  `bpkb_number` varchar(255) DEFAULT NULL,
  `purchase_price` decimal(15,2) NOT NULL,
  `sale_price` decimal(15,2) DEFAULT NULL,
  `status` enum('available','sold','in_repair','hold') NOT NULL DEFAULT 'hold',
  `description` text DEFAULT NULL,
  `dp_percentage` decimal(5,2) DEFAULT NULL,
  `engine_specification` text DEFAULT NULL,
  `location` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vehicles_vin_unique` (`vin`),
  UNIQUE KEY `vehicles_engine_number_unique` (`engine_number`),
  UNIQUE KEY `vehicles_license_plate_unique` (`license_plate`),
  UNIQUE KEY `vehicles_bpkb_number_unique` (`bpkb_number`),
  KEY `vehicles_vehicle_model_id_foreign` (`vehicle_model_id`),
  KEY `vehicles_type_id_foreign` (`type_id`),
  KEY `vehicles_color_id_foreign` (`color_id`),
  KEY `vehicles_year_id_foreign` (`year_id`),
  CONSTRAINT `vehicles_color_id_foreign` FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`),
  CONSTRAINT `vehicles_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`),
  CONSTRAINT `vehicles_vehicle_model_id_foreign` FOREIGN KEY (`vehicle_model_id`) REFERENCES `vehicle_models` (`id`),
  CONSTRAINT `vehicles_year_id_foreign` FOREIGN KEY (`year_id`) REFERENCES `years` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicles`
--

LOCK TABLES `vehicles` WRITE;
/*!40000 ALTER TABLE `vehicles` DISABLE KEYS */;
INSERT INTO `vehicles` VALUES
(2,2,1,1,1,1,'123','123','123','123',111.00,24900000.00,'available','<p><strong>Honda Vario 160 ABS - Tenaga dan Keamanan dalam Satu Paket.</strong></p><p>Tingkatkan pengalaman berkendara Anda dengan Honda Vario 160 ABS. Nikmati akselerasi bertenaga dari mesin 160cc eSP+ dan rasa percaya diri penuh berkat pengereman canggih ABS. Desainnya yang sporty dan fitur modern seperti Smart Key System menjadikan setiap perjalanan lebih istimewa. Unit dalam kondisi prima dan terawat, siap untuk Anda bawa pulang. Segera hubungi kami untuk penawaran terbaik!</p>',10.00,'160','Majalaya','<p>OK</p>','2025-08-31 07:42:13','2025-09-09 19:10:31',NULL),
(3,5,1,5,25,3000,'MH3SEK610RJ140364','E34KE0140368','D 3546 ZFQ','U-07788158',2000000.00,2500000.00,'available','<p></p>',20.00,'MESIN MULUS','BANDUNG MAJALAYA','<p></p>','2025-09-10 03:33:58','2025-09-10 16:58:19',NULL),
(5,24,2,10,18,55000,'MH3RG4610HK031408','G3E7E0407459','D 2387 ABD','N-07994832',8500000.00,13500000.00,'available','<p>All New Vixion menjanjikan Durable Engine yang didukung mesin baru. Motor ini mengusung mesin 150cc LC4V yang bertenaga dan responsif. Keunggulan utama dari mesin baru All New Vixion adalah fitur assist &amp; slipper clutch. Fitur assist membuat pengoperasian kopling menjadi lebih ringan, sedangkan slipper clutch membuat perpindahan gigi lebih halus sehingga akselerasi semakin cepat. Mesin All New Vixion berteknologi DiASil Cylinder dan Forged Piston yang membuat daya tahan mesin 3x lebih awet, 3x lebih kuat dan 3x lebih ringan.</p><p>Naked bike 150cc ini menawarkan Good Stability bagi pengendaranya. Menggunakan rangka Deltabox yang sudah teruji, All New Vixion menawarkan kesempurnaan bermanuver. Ditambah dengan suspensi monocross semakin menunjang kestabilan dalam berkendara. Untuk pengereman, motor ini menjanjikan pengereman yang optimal karena dilengkapi cakram pada roda depan dan belakang.</p>',20.00,'150 CC','BANDUNG MAJALAYA','<p>YAMAHA ALL NEW VIXION 155 2017</p>','2025-09-10 22:27:02','2025-09-11 06:05:19',NULL),
(6,6,1,1,23,29000,'MH1KF0116NK103735','KF01E1104385','D 2052 ADU','S-04812932',16800000.00,20900000.00,'available','<p>meluncurkan All New Honda Vario 160 dengan perubahan menyeluruh pada mesin, rangka, desain dan fitur yang semakin canggih. Motor produksi anak bangsa ini hadir di Tanah Air sebagai jawaban bagi pecinta motor skutik premium sporti berperforma tinggi yang memberikan kebanggaan dan menjadi cerminan gaya hidup pengendara yang menyenangi sensasi berkendara nyaman dengan teknologi tinggi.</p><p>All New Honda Vario 160 memberikan sebuah energi dan gaya baru dalam revolusi desain, teknologi dan fitur canggih yang disematkan sesuai gaya berkendara masa kini. Model skutik ini mengekspresikan premium sporti dengan tampilan desain layaknya matik besar yang mampu memberikan kebanggaan bagi pengendaranya. Berbekal mesin performa tinggi kapasitas 160cc 4 katup enhanced Smart Power Plus (eSP+), serta memiliki teknologi minim gesekan menjadikan skutik premium sporti ini memberikan keseimbangan antara performa yang optimal dan kesempurnaan puncak saat dikendarai harian.</p>',20.00,'160 CC','BANDUNG MAJALAYA','<p>HONDA VARIO 160 CBS 2022</p>','2025-09-11 00:53:55','2025-09-11 06:05:52',NULL),
(7,18,4,11,24,8000,'MH1KD1111PK437685','KD11E1436762','D 2874 ZFM','U-00837256',25800000.00,29000000.00,'available','<p>All New Honda CRF150L memiliki desain dan konsep mesin motor on-off sport yang siap dapat memberikan petualangan menyenangkan. Motor ini mampu menaklukan tantangan di segala kondisi medan sehingga memberikan kebebasan pengendaranya untuk menikmati segala petualangan.</p><p>Dengan mengusung konsep terbaru yaitu “Take You to Off Fun Ride” All New Honda CRF150L hadir melalui penyematan desain terbaru, fitur unggulan baru dan mesin baru yang nyaman digunakan untuk berkendara di jalan raya dan menaklukan berbagai rintangan. Ketangguhan All New Honda CRF150L dicapai berkat penyematan mesin 150cc SOHC PGM-FI berperforma tinggi yang mendukung kemampuan jelajah optimal dengan nyaman dan mudah dikendalikan di berbagai kondisi jalan.</p>',20.00,'150 CC','BANDUNG MAJALAYA','<p>HONDA CRF 150 L 2023</p>','2025-09-11 18:29:15','2025-09-11 18:29:15',NULL),
(8,21,2,2,19,5800,'MH4EX250SJJP01890','EX250PEA06122','D 3829 UEC','P-07873677',33000000.00,42500000.00,'available','<p>Pada Indonesian Motorcycle Show (IMOS) 2018, Kawasaki memperkenalkan Ninja 250 Model Year (MY) 2019. Sepeda motor sport 250cc berfairing ini mendapat fitur baru, yaitu Smart Key. Kunci pintar berbentuk key FOB miliknya ini dilengkapi dengan fitur Kawasaki Intelligent Proximity Activation Start System (KIPASS). Fitur ini memungkinkan pengendara menghidupkan dan memadamkan mesin kendaraan dengan lebih mudah.</p>',20.00,'250 cc','BANDUNG MAJALAYA','<p>KAWASAKI NEW NINJA 250 FI</p>','2025-09-11 18:57:28','2025-09-11 18:57:28',NULL),
(9,25,2,12,22,1800,'MH3RG4710MK149338','G3J6E0306950','Z 3580 DAV','R00771932',18500000.00,23500000.00,'available','<p>Yamaha R15 VVA merupakan produk teranyar dari Yamaha untuk kelas motor sport fairing 150 cc. Pertama muncul sejak 2017 silam, satu diantara line up keluarga R series ini mengalami penyegaran dengan memunculkan warna terbaru. Penyegaran yang dilakukan selaras dengan perkembangan produk sport saat ini.</p><p>Tiga warna baru yang terdiri dari Metallic Blue, Matte Silver, dan Matte Black, disuguhkan sebagai bagian dari inovasi yang kian memperkuat karakter R15. Metallic Blue dengan velg berwarna biru mewakili kebanggaan menunjukkan identitas warna biru Yamaha. Ini pun merepresentasikan kecintaan akan pabrikan Yamaha yang tak berhenti menghadirkan produk sport terbaik kepada pelanggannya.</p><p>Menciptakan tren roda dua di Indonesia, Yamaha menghadirkan warna Matte Silver yang dipadu dengan velg berwarna kuning membuat penampilan semakin menarik. Kamu juga bisa memilih tampilan yang sporty namun tetap elegan, Matte Black dikombinasikan dengan suspensi depan upside down warna emas menjadi pilihan tepat agar makin percaya diri.</p>',20.00,'155 CC','BANDUNG MAJALAYA','<p>YAMAHA ALL NEW R15 155 VVA 2021</p>','2025-09-11 19:18:59','2025-09-11 19:18:59',NULL),
(10,21,2,3,23,12000,'MH4EX250SNJP07047','EX250PEA28622','D 3601 VFC','S00599840',39100000.00,47500000.00,'available','<p>Kawasaki dengan bangga memperkenalkan sebuah model sport di arena yang sangat kompetitif, yaitu kelas sport 250 cc. Dibentuk dengan gaya Ninja baru yang tajam, Ninja 250 baru memberikan performa lebih hebat dibanding pendahulunya dengan mesin baru yang lebih bertenaga, dan juga dengan bobot yang lebih ringan.</p>',20.00,'250 CC','BANDUNG MAJALAYA','<p>KAWASAKI ALL NEW NINJA 250 FI 2022</p>','2025-09-11 19:29:28','2025-09-11 23:47:56',NULL),
(11,28,1,13,25,1000,'MH3SG6410RJ433191','G3P2E0507197','D 5662 UFJ','V01954037',23500000.00,26750000.00,'available','<p>Yamaha All New Aerox Cyber City dibangun dengan desain futuristik dengan konsep teknologi canggih dan kenyamanan dalam setiap inci. Dirancang khusus untuk menghadapi tantangan lalu lintas perkotaan, Aerox Cyber City adalah rekan setia untuk menjelajahi kota dengan gaya dan keyakinan.</p>',20.00,'155 CC','BANDUNG MAJALAYA','<p>YAMAHA NEW AEROX CIBERCITY 2024</p>','2025-09-11 19:56:23','2025-09-11 20:12:42',NULL),
(12,25,2,9,16,40000,'MH32PK001FK055927','2PK055947','D 4335 VCZ','S06126222',9500000.00,11500000.00,'available','<p>YAMAHA R15 V2 2015</p>',20.00,'150 CC','BANDUNG MAJALAYA','<p>YAMAHA R15 2015</p>','2025-09-11 20:20:10','2025-09-11 20:20:10',NULL),
(13,15,1,1,20,30000,'MH1KF5110KK015787','KF51E1015027','D 4265 ACR','P04827114',18250000.00,25500000.00,'available','<p>Dikarenakan sangat kental aura adventure, tak salah jika Anda mengatakan Honda ADV 150 adalah perwujudan dari Honda X-ADV dalam versi kecil. Walau begitu, kesan tangguh sekaligus berotot tetap mampu ditampilkannya secara maksimal. Apalagi dengan penambahan windscreen dan setang model tapered handle bar.</p><p>Bukan saja soal desain. Kepantasannya sebagai motor untuk bertualang juga tertuang lewat penyajian peredam kejut dengan jarak main panjang. Ground clearance Honda ADV 150 pun lumayan tinggi. Ditambah lagi penggunaan ban dual purpose yang artinya cocok untuk penggunaan di aspal maupun tanah.</p>',20.00,'150 CC','BANDUNG MAJALAYA','<p>HONDA ADV 150 CBS 2019</p>','2025-09-11 20:28:55','2025-09-11 20:28:55',NULL),
(14,7,3,1,20,45000,'MH1KB2110KK097191','KB21E1095405','D 4897 VEK','V07509269',10000000.00,14800000.00,'available','<p>AHM dengan resmi memperkenalkan All New Honda Supra GTR150 yang diklaim mampu melahap seluruh medan. AHM ingin menunjukkan bahwa konsep sepeda motor ini tak hanya tangguh sebagai sepeda motor perkotaan, tetapi juga bisa menaklukkan segala kondisi jalan dengan nyaman.</p>',20.00,'150 CC','BANDUNG MAJALAYA','<p>HONDA SUPRA GTR 150 SPORTY 2019</p>','2025-09-11 20:48:50','2025-09-11 20:51:29',NULL),
(15,22,1,8,24,14300,'RP8M82221PV104451','M828M5249207','D 4482 ZFK','T06192991',40000000.00,48500000.00,'available','<p>Berani dan ekstrovert adalah penjiwaan karakter Vespa yang telah melekat selama lebih dari 60 tahun lamanya. Vespa Sprint memiliki karakter yang bernyali, cepat, sangat lincah dan tak gentar, layaknya mentalitas mereka yang mengendarainya. Terlahir dengan rasa percaya diri, penuh determinasi, bernyali, dan diperkaya dengan fitur teknis terdepan, dan didesain untuk mendapatkan tingkat kestabilan dan handling yang tinggi. Vespa Sprint lahir untuk mendefinisikan perjalanan harian menjadi kegiatan yang menyenangkan.</p>',20.00,'155 CC','BANDUNG MAJALAYA','<p>PIAGGIO VESPA SPRINT 150 S I-GET ABS 2023</p>','2025-09-11 20:58:41','2025-09-11 20:58:41',NULL),
(16,27,1,13,26,1000,'MH3SG9750SJ003009','G3W1E0012010','D 4157 ZFW','V05880933',26000000.00,30500000.00,'available','<p>Yamaha All New Aerox alpha Cyber City dibangun dengan desain futuristik dengan konsep teknologi canggih dan kenyamanan dalam setiap inci. Dirancang khusus untuk menghadapi tantangan lalu lintas perkotaan, Aerox alpha Cyber City adalah rekan setia untuk menjelajahi kota dengan gaya dan keyakinan.</p>',20.00,'155 CC','BANDUNG MAJALAYA','<p>YAMAHA NEW AEROX ALPHA CIBERCITY 2025</p>','2025-09-11 21:05:53','2025-09-11 21:05:53',NULL),
(17,32,2,1,16,8000,'MH4KR150PFKPC1175','KR150KEPK2806','Z 4840 GF','T05567706',10500000.00,26000000.00,'available','<p>KAWASAKI NINJA RR 2015</p>',20.00,'150 CC','BANDUNG MAJALAYA','<p>KAWASAKI NINJA RR 2015</p>','2025-09-11 21:14:09','2025-09-11 21:18:54',NULL),
(18,3,1,1,17,42000,'MH1JFS118GK336249','JFS1E1332283','D 4557 AAG','M08453751',6000000.00,10000000.00,'available','<p>HONDA BEAT POP 2016</p>',20.00,'110 CC','BANDUNG MAJALAYA','<p>HONDA BEAT POP 2016</p>','2025-09-11 23:30:41','2025-09-11 23:32:57',NULL),
(19,33,2,12,19,41000,'MH3RG3710JK028511','G3C8E0038634','D 4823 VDZ','O03941917',11750000.00,16500000.00,'available','<p>YAMAHA XABRE 2018</p>',20.00,'150 CC','BANDUNG MAJALAYA','<p>YAMAHA XABRE 2018</p>','2025-09-11 23:45:10','2025-09-11 23:45:10',NULL),
(20,4,1,14,24,17500,'MH1JM8129PK548417','JM81E2549749','D 4963 AEF','T06177207',13500000.00,15800000.00,'available','<p>HONDA BEAT FI SPORTY CBS</p>',20.00,'110 CC','BANDUNG MAJALAYA','<p>HONDA BEAT FI SPORTY CBS 2023</p>','2025-09-12 00:13:41','2025-09-12 00:13:41',NULL),
(21,35,5,15,16,40000,'MH8BG41FAFJ113915','G4281D113766','D 6214 VBZ','Q02644254',5000000.00,10500000.00,'available','<p>Suzuki Satria F150 adalah sepeda motor kategori underbone 4-Tak berkapasitas 150cc. Motor ini diproduksi oleh Suzuki Motor Corporation menggantikan model Suzuki FXR150. Mengusung mesin berteknologi tinggi dengan volume silinder bersih 150 cc, 4 klep digerakkan oleh Camshaft ganda. Konfigurasi mesin seperti ini juga disebut DOHC yang biasa ditemui pada mesin Mobil. Ditunjang dengan transmisi 6 percepatan, gigi rasio pendek, dan konstruksi sasis ringan menjadikan motor ini mampu melesat dengan kecepatan lebih dari 150 km/jam sekaligus menjadikan motor ini merupakan salah satu motor 150cc 4-Tak tercepat di Indonesia, baik di kelas Underbone atau di kelas motor sport sekalipun.</p>',20.00,'150 CC','BANDUNG MAJALAYA','<p>SUZUKI SATRIA FU 150 2015</p>','2025-09-12 00:51:12','2025-09-12 00:51:12',NULL),
(22,36,1,16,25,6000,'MH3SEK610RJ185325','E34KE0185329','D 6286 VFO','V02330404',20750000.00,24500000.00,'available','<p>Yamaha Grand Filano Lux adalah skuter matik bergaya mewah dan elegan yang dilengkapi mesin Blue Core Hybrid 125cc, fitur Smart Key System, lampu LED di seluruh bagian, jok premium dengan tekstur bordir, dan bagasi luas.</p>',20.00,'125 CC','BANDUNG MAJALAYA','<p>YAMAHA GRAND FILANO LUX 2024</p>','2025-09-12 01:02:10','2025-09-12 01:02:10',NULL),
(23,14,1,8,24,17000,'MH1JM041XPK234411','JM04E1234076','D 6055 YUN','T04880131',15500000.00,19500000.00,'available','<p>HONDA NEW SCOOPY PRESTIGE 2023</p>',20.00,'110 CC','BANDUNG MAJALAYA','<p>HONDA NEW SCOOPY PRESTIGE 2023</p>','2025-09-12 01:14:23','2025-09-12 01:14:23',NULL),
(24,23,1,1,20,75000,'MH3SG3190KK482520','G3E4E1326247','T 6596 RI','O05547746',14600000.00,21000000.00,'available','<p>YAMAHA NMAX OLD 2019</p>',20.00,'155 CC','BANDUNG MAJALAYA','<p>YAMAHA NMAX OLD 2019</p>','2025-09-12 01:25:57','2025-09-12 01:25:57',NULL),
(25,26,2,17,23,7000,'MH3RG7810NK002339','G3S7E0017229','D 6061 ZMR','S06257611',24300000.00,30500000.00,'available','<p>All New Yamaha R15 V4 dengan fitur ABS adalah motor sport fairing yang hadir dalam beberapa varian, termasuk tipe R15M Connected/ABS. Varian ini dilengkapi mesin 155cc, VVA, dan fitur keselamatan seperti Dual Channel ABS dan Traction Control System (TCS)</p>',20.00,'155 CC','BANDUNG MAJALAYA','<p>YAMAHA ALL NEW R15 V4 ABS</p><p></p>','2025-09-12 01:58:23','2025-09-12 01:58:23',NULL),
(26,7,5,10,24,3500,'MH1KB2215PK038160','KB22E1038128','D 6156 UFC','U01929382',13200000.00,17900000.00,'available','<p>HONDA SUPRA GTR 150 SPORTY 2023</p><p></p>',20.00,'150 CC','BANDUNG MAJALAYA','<p>HONDA SUPRA GTR 150 SPORTY 2023</p>','2025-09-12 02:35:25','2025-09-12 02:35:25',NULL),
(27,9,1,13,25,10000,'MH1KF7112RK984495','KF71E1984897','D 6148 AEZ','V05517247',27400000.00,30500000.00,'available','<p>HONDA NEW PC0 CBS</p>',20.00,'160 CC','BANDUNG MAJALAYA','<p>HONDA NEW PCX 160 CBS 2024</p>','2025-09-12 22:05:03','2025-09-12 22:05:03',NULL),
(28,23,1,13,20,27000,'MH3SG3190KJ886421','G3E4E1881233','D 6239 VEL','P07903752',18800000.00,23500000.00,'hold','<p>YAMAHA NMAX 2019</p>',20.00,'155 CC','BANDUNG MAJALAYA','<p>YAMAHA NMAX 2019</p>','2025-09-12 22:58:58','2025-09-12 22:58:58',NULL),
(29,12,2,10,24,1800,'MH1KCB11XPK047600','KCB1E1047591','D 6183 ZFN','U05638587',220000000.00,24800000.00,'available','<p>HONDA ALL NEW CBR 150 R 2023</p>',20.00,'150 CC','BANDUNG MAJALAYA','<p>HONDA ALL NEW CBR 150 R 2023</p>','2025-09-12 23:12:21','2025-09-12 23:12:21',NULL),
(30,32,2,1,16,20000,'MH4KR150PFKPA8847','KR150KEPJ2369','D 6466 ZCF','M08413308',23000000.00,26500000.00,'available','<p>KAWASAKI NINJA RR 150 2015</p>',20.00,'150 CC','BANDUNG MAJALAYA','<p>KAWASAKI NINJA RR 150 2015</p>','2025-09-12 23:19:45','2025-09-12 23:19:45',NULL),
(31,28,1,1,23,1500,'MH3SG6410NJ146251','G3P2E0171012','D 6862 UEP','S05805526',20500000.00,26500000.00,'available','<p>YAMAHA NEW AEROX 155 C 2022</p>',20.00,'155 CC','BANDUNG MAJALAYA','<p>YAMAHA NEW AEROX 155 C 2022</p>','2025-09-13 00:27:11','2025-09-13 00:27:11',NULL),
(32,41,1,10,26,2600,'MH1JMH113SK132248','JMH1E1131152','D 6206 ZFW','V07511206',18800000.00,21500000.00,'available','<p>Honda Scoopy Energetic tersedia dengan harga Rp 22,88 Juta di Indonesia. The Scoopy Energetic is powered by a 109.5 cc engine, and has a Variable Kecepatan gearbox. Honda Scoopy Energetic memiliki tinggi jok 746 mm dengan bobot 94 kg . Rem depan menggunakan Disc , sedangkan di belakang Drum . Lebih dari 319 pengguna telah memberikan penilaian untuk Scoopy Energetic berdasarkan fitur, jarak tempuh, kenyamanan tempat duduk dan kinerja mesin.</p>',20.00,'110 CC','BANDUNG MAJALAYA','<p>HONDA NEW SCOOPY ENERGETIC 2025</p>','2025-09-13 01:07:17','2025-09-13 01:07:17',NULL),
(33,34,2,10,18,20000,'MH8DL22ANHJ102285','CGA2ID602063','Z 4234 CN','N10131692',9350000.00,12500000.00,'available','<p>Suzuki GSX-S150 dikenal sebagai sports street bike yang gesit dan ringan. GSX-S150 akan menjadi favorit para pengendara untuk transportasi harian dan hobi</p>',20.00,'150 CC','BANDUNG MAJALAYA','<p>SUZUKI GSX 150 S 2017</p>','2025-09-13 01:33:15','2025-09-13 01:33:15',NULL),
(34,13,1,10,24,15000,'4','-','D 3013 UEY','-',15200000.00,17800000.00,'in_repair','<p>Lampeganmotorbdg</p>',20.00,'110 cc','Majalaya','<p>posisi unit belum sampai showroom</p>','2025-09-14 17:26:11','2025-09-14 17:26:11',NULL),
(35,42,1,1,24,9700,'MH3SG6420PJ072762','G3P4E0234567','Z 3658 AAT','U06155176',23500000.00,26500000.00,'available','<p>YAMAHA NEW AEROX C 155 ABS 2023</p>',20.00,'155 CC','BANDUNG MAJALAYA','<p>YAMAHA NEW AEROX C 155 ABS 2023</p>','2025-09-14 19:02:36','2025-09-14 19:02:36',NULL),
(36,43,2,1,20,25000,'MH1KC0217KK058906','KC02E1059107','Z 3117 AAB','P00906165',10500000.00,14500000.00,'available','<p>HONDA CB VERZA 150 2019</p>',20.00,'150 CC','BANDUNG MAJALAYA','<p>HONDA CB VERZA 150 2019</p>','2025-09-14 19:17:21','2025-09-14 19:17:21',NULL),
(37,23,1,8,22,42500,'MH3SG3192MK034602','G3E4E2098722','D 3930 VET','Q07281694',2000000.00,24500000.00,'available','<p>YAMAHA NMAX OLD 2021</p>',20.00,'155 CC','BANDUNG MAJALAYA','<p>YAMAHA NMAX OLD 2021</p>','2025-09-14 19:25:12','2025-09-14 19:25:12',NULL),
(38,4,1,1,22,50000,'MH1JM8117MK645425','JM81E1647198','D 3615 ADL','R01006922',11000000.00,13700000.00,'available','<p>HONDA BEAT FI SPORTY CBS 2021</p>',20.00,'110 CC','BANDUNG MAJALAYA','<p>HONDA BEAT FI SPORTY CBS 2021</p>','2025-09-15 19:09:17','2025-09-15 19:09:17',NULL),
(43,45,1,3,25,10000,'MH1KFB11RK071665','KFB1E1071616','D 3665 VFM','P00263767',28500000.00,31500000.00,'available','<p>HONDA ADV 160 CBS 2024</p>',20.00,'160 CC','BANDUNG MAJALAYA','<p>HONDA ADV 160 CBS 2024</p>','2025-09-15 20:42:02','2025-09-15 20:47:55',NULL),
(44,45,1,1,24,14000,'MH1KFB119PK032660','KFB1E1032605','D 4379 AEC','P05674380',27300000.00,30500000.00,'available','<p>HONDA ADV 160 CBS 2023</p>',20.00,'160 CC','BANDUNG MAJALAYA','<p>HONDA ADV 160 CBS 2023</p>','2025-09-15 21:15:03','2025-09-15 21:15:03',NULL),
(45,4,1,1,22,43000,'MH1JM8115MK476294','JM81E1478007','D 3281 ADI','P01098574',9800000.00,13750000.00,'available','<p>HONDA BEAT FI SPORTY CBS 2021</p>',20.00,'110 CC','BANDUNG MAJALAYA','<p>HONDA BEAT FI SPORTY CBS 2021</p>','2025-09-15 21:56:28','2025-09-15 21:56:28',NULL),
(46,46,1,13,25,27000,'MH1JMB115SK205648','JMB1E1205359','D 6550 ZFX','P08335563',15300000.00,17800000.00,'available','<p>HONDA GENIO ISS 2024</p>',20.00,'110 CC','BANDUNG MAJALAYA','<p>HONDA GENIO ISS 2024</p>','2025-09-15 22:22:20','2025-09-15 22:22:20',NULL);
/*!40000 ALTER TABLE `vehicles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `years`
--

DROP TABLE IF EXISTS `years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `years` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `year` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `years_year_unique` (`year`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `years`
--

LOCK TABLES `years` WRITE;
/*!40000 ALTER TABLE `years` DISABLE KEYS */;
INSERT INTO `years` VALUES
(1,'2000','2025-08-27 20:51:31','2025-09-10 03:13:18'),
(11,'2010','2025-09-10 03:14:57','2025-09-10 03:14:57'),
(12,'2011','2025-09-10 03:15:04','2025-09-10 03:15:04'),
(13,'2012','2025-09-10 03:17:05','2025-09-10 03:17:05'),
(14,'2013','2025-09-10 03:17:11','2025-09-10 03:17:11'),
(15,'2014','2025-09-10 03:17:16','2025-09-10 03:17:16'),
(16,'2015','2025-09-10 03:17:21','2025-09-10 03:17:21'),
(17,'2016','2025-09-10 03:17:25','2025-09-10 03:17:25'),
(18,'2017','2025-09-10 03:17:29','2025-09-10 03:17:29'),
(19,'2018','2025-09-10 03:17:33','2025-09-10 03:17:33'),
(20,'2019','2025-09-10 03:17:38','2025-09-10 03:17:38'),
(21,'2020','2025-09-10 03:17:42','2025-09-10 03:17:42'),
(22,'2021','2025-09-10 03:17:49','2025-09-10 03:17:49'),
(23,'2022','2025-09-10 03:17:53','2025-09-10 03:17:53'),
(24,'2023','2025-09-10 03:17:58','2025-09-10 03:17:58'),
(25,'2024','2025-09-10 03:18:03','2025-09-10 03:18:03'),
(26,'2025','2025-09-10 03:18:07','2025-09-10 03:18:07');
/*!40000 ALTER TABLE `years` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'gofathco_lampeganmotor'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-09-17  6:25:28
