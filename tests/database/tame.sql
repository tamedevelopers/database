-- Tamedevelopers\Database\DBExport
-- Database: `lhkadvance`
-- Date: 2025-09-07_15-26-56

SET FOREIGN_KEY_CHECKS=0;
SET AUTOCOMMIT=0;
-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 2025-09-07_15-26-56
-- Server version: 10.4.25-MariaDB
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lhkadvance`
--

-- --------------------------------------------------------

--
-- Table structure for table `ads`
--

CREATE TABLE `ads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `views` bigint(20) NOT NULL DEFAULT 0,
  `clicks` bigint(20) NOT NULL DEFAULT 0,
  `active` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ads_views_index` (`views`),
  KEY `ads_clicks_index` (`clicks`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- Table structure for table `ads_data`
--

CREATE TABLE `ads_data` (
  `ads_id` bigint(20) unsigned DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `ads_data_ads_id_index` (`ads_id`),
  CONSTRAINT `ads_data_ads_id_foreign` FOREIGN KEY (`ads_id`) REFERENCES `ads` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `views` bigint(20) NOT NULL DEFAULT 0,
  `clicks` bigint(20) NOT NULL DEFAULT 0,
  `language` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blogs_slug_unique` (`slug`),
  KEY `blogs_user_id_index` (`user_id`),
  KEY `blogs_category_slug_index` (`category_slug`),
  KEY `blogs_tags_index` (`tags`),
  KEY `blogs_title_index` (`title`),
  KEY `blogs_views_index` (`views`),
  KEY `blogs_clicks_index` (`clicks`),
  KEY `blogs_language_index` (`language`),
  KEY `blogs_date_index` (`date`),
  CONSTRAINT `blogs_category_slug_foreign` FOREIGN KEY (`category_slug`) REFERENCES `blogs_categories` (`slug`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=186 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `blogs_categories`
--

CREATE TABLE `blogs_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `language` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blogs_categories_slug_unique` (`slug`),
  KEY `blogs_categories_name_index` (`name`),
  KEY `blogs_categories_language_index` (`language`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- Dumping data for table `blogs_categories`
--

INSERT INTO `blogs_categories` (`id`, `slug`, `name`, `image`, `language`, `created_at`, `updated_at`) VALUES
(1, '香港寄台灣🇹🇼', '台灣🇹🇼', NULL, 'cn', '2022-11-30 01:28:41', '2025-04-17 14:09:03'),
(2, '香港寄英國🇬🇧', '英國🇬🇧', NULL, 'cn', '2022-11-30 01:28:49', '2025-04-17 14:09:08'),
(3, '香港寄澳洲🇳🇿', '澳洲🇳🇿', NULL, 'cn', '2022-11-30 01:28:56', '2025-04-17 14:09:12'),
(4, '香港寄美國🇺🇸', '美國🇺🇸', NULL, 'cn', '2022-11-30 01:29:13', '2025-04-17 14:09:49'),
(5, '香港寄加拿大🇨🇦', '加拿大🇨🇦', NULL, 'cn', '2022-11-30 01:29:24', '2025-04-17 14:09:54'),
(6, '香港寄紐西蘭-🇳🇿', '紐西蘭 🇳🇿', NULL, 'cn', '2022-11-30 01:29:28', '2025-04-17 14:09:58'),
(16, '香港寄歐盟🇪🇺', '歐洲🇪🇺', NULL, 'cn', '2022-11-30 01:30:29', '2025-05-17 07:13:36'),
(17, '綜合資訊', '綜合資訊', NULL, 'cn', '2022-11-30 01:30:33', '2022-11-30 01:30:33'),
(18, 'comprehensive-information', 'Comprehensive information', NULL, 'en', '2022-11-30 01:35:44', '2022-11-30 01:35:44'),
(19, 'hong-kong-to-eu', 'EU (🇪🇺)', NULL, 'en', '2022-11-30 01:36:07', '2025-04-22 15:49:50'),
(20, 'hong-kong-to-israel-🇮🇱', 'Israel 🇮🇱', NULL, 'en', '2022-11-30 01:36:25', '2025-04-22 15:49:46'),
(21, 'hong-kong-to-philippines-🇵🇭', 'Philippines (🇵🇭)', NULL, 'en', '2022-11-30 01:36:50', '2025-04-22 15:49:40'),
(22, 'hong-kong-to-vietnam-🇻🇳', 'Vietnam (🇻🇳)', NULL, 'en', '2022-11-30 01:37:07', '2025-04-22 15:49:35'),
(23, 'hong-kong-to-indonesia-🇮🇩', 'Indonesia (🇮🇩)', NULL, 'en', '2022-11-30 01:37:22', '2025-04-22 15:49:31'),
(24, 'hong-kong-to-korea-🇰🇷', 'Korea (🇰🇷)', NULL, 'en', '2022-11-30 01:37:44', '2025-04-22 15:49:26'),
(25, 'hong-kong-to-japan-🇯🇵', 'Japan (🇯🇵)', NULL, 'en', '2022-11-30 01:37:57', '2025-04-22 15:49:21'),
(26, 'hong-kong-to-thailand-🇹🇭', 'Thailand (🇹🇭)', NULL, 'en', '2022-11-30 01:38:11', '2025-04-22 15:49:16'),
(27, 'hong-kong-to-malaysia-🇲🇾', 'Malaysia (🇲🇾)', NULL, 'en', '2022-11-30 01:38:37', '2025-04-22 15:49:13'),
(28, 'hong-kong-to-singapore-🇸🇬', 'Singapore (🇸🇬)', NULL, 'en', '2022-11-30 01:38:51', '2025-04-22 15:49:04'),
(29, 'hong-kong-to-new-zealand--🇳🇿', 'New Zealand (🇳🇿)', NULL, 'en', '2022-11-30 01:39:06', '2025-04-22 15:49:00'),
(30, 'hong-kong-to-canada--🇨🇦', 'Canada (🇨🇦)', NULL, 'en', '2022-11-30 01:39:19', '2025-04-22 15:48:55'),
(32, 'hong-kong-to-usa--🇺🇸', 'USA (🇺🇸)', NULL, 'en', '2022-11-30 01:39:54', '2025-04-22 15:48:44'),
(33, 'hong-kong-to-australia--🇳🇿', 'Australia (🇳🇿)', NULL, 'en', '2022-11-30 01:40:33', '2025-04-22 15:48:41'),
(34, 'hong-kong-to-uk--🇬🇧', 'United Kingdom (🇬🇧)', NULL, 'en', '2022-11-30 01:40:46', '2025-04-22 15:48:36'),
(35, 'hong-kong-to-taiwan--🇹🇼', 'Taiwan (🇹🇼)', NULL, 'en', '2022-11-30 01:40:58', '2025-04-22 15:48:24'),
(36, 'immigration-to-the-uk-guide--🇬🇧', 'Immigration to the UK Guide (🇬🇧)', NULL, 'en', '2022-11-30 01:41:11', '2022-11-30 01:41:11'),
(37, 'tesla-資訊', 'Tesla 資訊', NULL, 'cn', '2023-01-25 05:33:01', '2023-01-25 05:33:01'),
(39, '30', '東南亞🇸🇬🇲🇾🇹🇭🇮🇩🇻🇳🇵🇭', NULL, 'cn', '2025-05-17 07:15:55', '2025-05-17 07:15:55'),
(42, '52', '日本🇯🇵韓國🇰🇷', NULL, 'cn', '2025-07-08 17:47:16', '2025-07-08 17:47:35');

-- --------------------------------------------------------

--
-- Table structure for table `blogs_data`
--

CREATE TABLE `blogs_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `blogs_data_slug_index` (`slug`),
  CONSTRAINT `blogs_data_slug_foreign` FOREIGN KEY (`slug`) REFERENCES `blogs` (`slug`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `iso` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `iso2` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dialing_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '+',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `standard_tax` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `tax_rate` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `position` tinyint(4) DEFAULT NULL,
  `active` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `countries_iso_index` (`iso`),
  KEY `countries_iso2_index` (`iso2`)
) ENGINE=InnoDB AUTO_INCREMENT=244 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `iso`, `iso2`, `dialing_code`, `name`, `standard_tax`, `tax_rate`, `position`, `active`, `created_at`, `updated_at`) VALUES
(1, 'AFG', 'AF', '+93', 'Afghanistan', '0', '0', 22, '1', NULL, '2024-08-08 14:10:30'),
(2, 'ALA', 'AX', '+358', 'Aland Islands', '0', '0', 25, '1', NULL, '2022-12-20 23:11:38'),
(3, 'ALB', 'AL', '+355', 'Albania', '0', '0', 23, '1', NULL, '2022-12-20 23:11:38'),
(4, 'DZA', 'DZ', '+213', 'Algeria', '0', '0', 24, '1', NULL, '2022-12-20 23:11:38'),
(5, 'ASM', 'AS', '+1', 'American Samoa', '0', '0', 26, '1', NULL, '2022-12-20 23:11:38'),
(6, 'AND', 'AD', '+376', 'Andorra', '0', '0', 27, '1', NULL, '2022-12-20 23:11:38'),
(7, 'AGO', 'AO', '+244', 'Angola', '0', '0', 0, '0', NULL, '2023-11-03 14:04:16'),
(8, 'AIA', 'AI', '+1', 'Anguilla', '0', '0', 28, '1', NULL, '2022-12-20 23:11:38'),
(9, 'ATA', 'AQ', '+672', 'Antarctica', '0', '0', 0, '0', NULL, '2023-11-03 14:04:21'),
(10, 'ATG', 'AG', '+1', 'Antigua and Barbuda', '0', '0', 29, '1', NULL, '2022-12-20 23:11:38'),
(11, 'ARG', 'AR', '+54', 'Argentina', '0', '0', 30, '1', NULL, '2022-12-20 23:11:38'),
(12, 'ARM', 'AM', '+374', 'Armenia', '0', '0', 31, '1', NULL, '2022-12-20 23:11:38'),
(13, 'ABW', 'AW', '+297', 'Aruba', '0', '0', 32, '1', NULL, '2022-12-20 23:11:38'),
(14, 'AUS', 'AU', '+61', 'Australia', '0', '0', 1, '1', NULL, '2022-12-20 23:11:38'),
(15, 'AUT', 'AT', '+43', 'Austria', '0', '0', 33, '1', NULL, '2022-12-20 23:11:38'),
(16, 'AZE', 'AZ', '+994', 'Azerbaijan', '0', '0', 34, '1', NULL, '2022-12-20 23:11:38'),
(17, 'BHS', 'BS', '+1', 'Bahamas', '0', '0', 35, '1', NULL, '2022-12-20 23:11:38'),
(18, 'BHR', 'BH', '+973', 'Bahrain', '0', '0', 36, '1', NULL, '2022-12-20 23:11:38'),
(19, 'BGD', 'BD', '+880', 'Bangladesh', '0', '0', 37, '1', NULL, '2022-12-20 23:11:38'),
(20, 'BRB', 'BB', '+1', 'Barbados', '0', '0', 38, '1', NULL, '2022-12-20 23:11:38'),
(21, 'BLR', 'BY', '+375', 'Belarus', '0', '0', 39, '1', NULL, '2022-12-20 23:11:38'),
(22, 'BEL', 'BE', '+32', 'Belgium', '0', '0', 41, '1', NULL, '2022-12-20 23:11:38'),
(23, 'BLZ', 'BZ', '+501', 'Belize', '0', '0', 42, '1', NULL, '2022-12-20 23:11:38'),
(24, 'BEN', 'BJ', '+229', 'Benin', '0', '0', 43, '1', NULL, '2022-12-20 23:11:38'),
(25, 'BMU', 'BM', '+1', 'Bermuda', '0', '0', 44, '1', NULL, '2022-12-20 23:11:38'),
(26, 'BTN', 'BT', '+975', 'Bhutan', '0', '0', 45, '1', NULL, '2022-12-20 23:11:38'),
(27, 'BOL', 'BO', '+591', 'Bolivia', '0', '0', 46, '1', NULL, '2022-12-20 23:11:38'),
(28, 'BIH', 'BA', '+387', 'Bosnia and Herzegovina', '0', '0', 47, '1', NULL, '2022-12-20 23:11:38'),
(29, 'BWA', 'BW', '+267', 'Botswana', '0', '0', 48, '1', NULL, '2022-12-20 23:11:38'),
(30, 'BVT', 'BV', '+55', 'Bouvet Island', '0', '0', 49, '0', NULL, '2022-12-20 23:11:38'),
(31, 'BRA', 'BR', '+55', 'Brazil', '0', '0', 50, '1', NULL, '2022-12-20 23:11:38'),
(32, 'IOT', 'IO', '+246', 'British Indian Ocean Territory', '0', '0', 51, '1', NULL, '2022-12-20 23:11:38'),
(33, 'BRN', 'BN', '+673', 'Brunei', '0', '0', 52, '1', NULL, '2022-12-20 23:11:38'),
(34, 'BGR', 'BG', '+359', 'Bulgaria', '0', '0', 53, '1', NULL, '2022-12-20 23:11:38'),
(35, 'BFA', 'BF', '+226', 'Burkina Faso', '0', '0', 54, '1', NULL, '2022-12-20 23:11:38'),
(36, 'BDI', 'BI', '+257', 'Burundi', '0', '0', 55, '1', NULL, '2022-12-20 23:11:38'),
(37, 'KHM', 'KH', '+855', 'Cambodia', '0', '0', 56, '1', NULL, '2022-12-20 23:11:38'),
(38, 'CMR', 'CM', '+237', 'Cameroon', '0', '0', 57, '1', NULL, '2022-12-20 23:11:38'),
(39, 'CAN', 'CA', '+1', 'Canada', '0', '0', 2, '1', NULL, '2022-12-20 23:11:38'),
(40, 'CPV', 'CV', '+238', 'Cape Verde', '0', '0', 58, '1', NULL, '2022-12-20 23:11:38'),
(41, 'CYM', 'KY', '+1', 'Cayman Islands', '0', '0', 59, '1', NULL, '2022-12-20 23:11:38'),
(42, 'CAF', 'CF', '+236', 'Central African Republic', '0', '0', 60, '1', NULL, '2022-12-20 23:11:38'),
(43, 'TCD', 'TD', '+235', 'Chad', '0', '0', 61, '1', NULL, '2022-12-20 23:11:38'),
(44, 'CHL', 'CL', '+56', 'Chile', '0', '0', 62, '1', NULL, '2022-12-20 23:11:38'),
(45, 'CHN', 'CN', '+86', 'China', '0', '0', 0, '0', NULL, NULL),
(46, 'CXR', 'CX', '+61', 'Christmas Island', '0', '0', 0, '0', NULL, NULL),
(47, 'CCK', 'CC', '+61', 'Cocos (Keeling) Islands', '0', '0', 0, '0', NULL, '2022-10-23 16:53:50'),
(48, 'COL', 'CO', '+57', 'Colombia', '0', '0', 64, '1', NULL, '2022-12-20 23:11:38'),
(49, 'COM', 'KM', '+269', 'Comoros', '0', '0', 65, '1', NULL, '2022-12-20 23:11:38'),
(50, 'COG', 'CG', '+242', 'Congo - Brazzaville', '0', '0', 66, '1', NULL, '2022-12-20 23:11:38'),
(51, 'COD', 'CD', '+243', 'Congo - Kinshasa', '0', '0', 67, '1', NULL, '2023-11-03 14:04:35'),
(52, 'COK', 'CK', '+682', 'Cook Islands', '0', '0', 68, '1', NULL, '2022-12-20 23:11:38'),
(53, 'CRI', 'CR', '+506', 'Costa Rica', '0', '0', 69, '1', NULL, '2022-12-20 23:11:38'),
(54, 'CIV', 'CI', '+225', 'Côte d’Ivoire', '0', '0', 70, '1', NULL, '2022-12-20 23:11:38'),
(55, 'HRV', 'HR', '+385', 'Croatia', '0', '0', 71, '1', NULL, '2022-12-20 23:11:38'),
(56, 'CUB', 'CU', '+53', 'Cuba', '0', '0', 0, '0', NULL, '2023-11-03 14:04:39'),
(57, 'CUW', 'CW', '+599', 'Curacao', '0', '0', 72, '1', NULL, '2022-12-20 23:11:38'),
(58, 'CYP', 'CY', '+357', 'Cyprus', '0', '0', 73, '1', NULL, '2022-12-20 23:11:38'),
(59, 'CZE', 'CZ', '+420', 'Czech Republic', '0', '0', 74, '1', NULL, '2022-12-20 23:11:38'),
(60, 'DNK', 'DK', '+45', 'Denmark', '0', '0', 75, '1', NULL, '2022-12-20 23:11:38'),
(61, 'DJI', 'DJ', '+253', 'Djibouti', '0', '0', 76, '1', NULL, '2022-12-20 23:11:38'),
(62, 'DMA', 'DM', '+1', 'Dominica', '0', '0', 77, '1', NULL, '2022-12-20 23:11:38'),
(63, 'DOM', 'DO', '+1', 'Dominican Republic', '0', '0', 78, '1', NULL, '2022-12-20 23:11:38'),
(64, 'ECU', 'EC', '+593', 'Ecuador', '0', '0', 79, '1', NULL, '2022-12-20 23:11:38'),
(65, 'EGY', 'EG', '+20', 'Egypt', '0', '0', 80, '1', NULL, '2022-12-20 23:11:38'),
(66, 'SLV', 'SV', '+503', 'El Salvador', '0', '0', 81, '1', NULL, '2022-12-20 23:11:38'),
(67, 'GNQ', 'GQ', '+240', 'Equatorial Guinea', '0', '0', 0, '0', NULL, NULL),
(68, 'ERI', 'ER', '+291', 'Eritrea', '0', '0', 82, '1', NULL, '2022-12-20 23:11:38'),
(69, 'EST', 'EE', '+372', 'Estonia', '0', '0', 83, '1', NULL, '2022-12-20 23:11:38'),
(70, 'ETH', 'ET', '+251', 'Ethiopia', '0', '0', 84, '1', NULL, '2022-12-20 23:11:38'),
(71, 'FLK', 'FK', '+500', 'Falkland Islands', '0', '0', 85, '1', NULL, '2022-12-20 23:11:38'),
(72, 'FRO', 'FO', '+298', 'Faroe Islands', '0', '0', 86, '0', NULL, '2022-12-20 23:11:38'),
(73, 'FJI', 'FJ', '+679', 'Fiji', '0', '0', 87, '1', NULL, '2022-12-20 23:11:38'),
(74, 'FIN', 'FI', '+358', 'Finland', '0', '0', 88, '1', NULL, '2022-12-20 23:11:38'),
(75, 'FRA', 'FR', '+33', 'France', '0', '0', 10, '1', NULL, '2022-12-20 23:11:38'),
(76, 'GUF', 'GF', '+594', 'French Guiana', '0', '0', 89, '1', NULL, '2022-12-20 23:11:38'),
(77, 'PYF', 'PF', '+689', 'French Polynesia', '0', '0', 90, '1', NULL, '2022-12-20 23:11:38'),
(78, 'ATF', 'TF', '+262 ', 'French Southern Territories', '0', '0', 0, '0', NULL, NULL),
(79, 'GAB', 'GA', '+241', 'Gabon', '0', '0', 91, '1', NULL, '2022-12-20 23:11:38'),
(80, 'GMB', 'GM', '+220', 'Gambia', '0', '0', 92, '1', NULL, '2022-12-20 23:11:38'),
(81, 'GEO', 'GE', '+995', 'Georgia', '0', '0', 21, '1', NULL, '2022-12-20 23:11:38'),
(82, 'DEU', 'DE', '+49', 'Germany', '0', '0', 11, '1', NULL, '2022-12-20 23:11:38'),
(83, 'GHA', 'GH', '+233', 'Ghana', '0', '0', 93, '1', NULL, '2022-12-20 23:11:38'),
(84, 'GIB', 'GI', '+350', 'Gibraltar', '0', '0', 94, '1', NULL, '2022-12-20 23:11:38'),
(85, 'GRC', 'GR', '+30', 'Greece', '0', '0', 95, '1', NULL, '2022-12-20 23:11:38'),
(86, 'GRL', 'GL', '+299', 'Greenland', '0', '0', 96, '1', NULL, '2022-12-20 23:11:38'),
(87, 'GRD', 'GD', '+1', 'Grenada', '0', '0', 97, '1', NULL, '2022-12-20 23:11:38'),
(88, 'GLP', 'GP', '+590', 'Guadeloupe', '0', '0', 98, '1', NULL, '2022-12-20 23:11:38'),
(89, 'GUM', 'GU', '+1', 'Guam', '0', '0', 99, '1', NULL, '2022-12-20 23:11:38'),
(90, 'GTM', 'GT', '+502', 'Guatemala', '0', '0', 100, '1', NULL, '2022-12-20 23:11:38'),
(91, 'GGY', 'GG', '+44', 'Guernsey', '0', '0', 101, '1', NULL, '2022-12-20 23:11:38'),
(92, 'GIN', 'GN', '+224', 'Guinea', '0', '0', 102, '1', NULL, '2022-12-20 23:11:38'),
(93, 'GNB', 'GW', '+245', 'Guinea-Bissau', '0', '0', 103, '1', NULL, '2022-12-20 23:11:38'),
(94, 'GUY', 'GY', '+592', 'Guyana', '0', '0', 104, '1', NULL, '2022-12-20 23:11:38'),
(95, 'HTI', 'HT', '+509', 'Haiti', '0', '0', 105, '1', NULL, '2022-12-20 23:11:38'),
(96, 'HMD', 'HM', '+61', 'Heard Island and McDonald Islands', '0', '0', 106, '1', NULL, '2022-12-20 23:11:38'),
(97, 'HND', 'HN', '+504', 'Honduras', '0', '0', 107, '1', NULL, '2022-12-20 23:11:38'),
(98, 'HKG', 'HK', '+852', 'Hong Kong', '0', '0', 12, '1', NULL, '2022-12-20 23:11:38'),
(99, 'HUN', 'HU', '+36', 'Hungary', '0', '0', 108, '1', NULL, '2022-12-20 23:11:38'),
(100, 'ISL', 'IS', '+354', 'Iceland', '0', '0', 109, '1', NULL, '2022-12-20 23:11:38');
INSERT INTO `countries` (`id`, `iso`, `iso2`, `dialing_code`, `name`, `standard_tax`, `tax_rate`, `position`, `active`, `created_at`, `updated_at`) VALUES
(101, 'IND', 'IN', '+91', 'India', '0', '0', 110, '1', NULL, '2022-12-20 23:11:38'),
(102, 'IDN', 'ID', '+62', 'Indonesia', '0', '0', 18, '1', NULL, '2022-12-20 23:11:38'),
(103, 'IRN', 'IR', '+98', 'Iran', '0', '0', 0, '0', NULL, NULL),
(104, 'IRQ', 'IQ', '+964', 'Iraq', '0', '0', 111, '1', NULL, '2022-12-20 23:11:38'),
(105, 'IRL', 'IE', '+353', 'Ireland', '0', '0', 20, '1', NULL, '2022-12-20 23:11:38'),
(106, 'IMN', 'IM', '+44', 'Isle of Man', '0', '0', 0, '0', NULL, NULL),
(107, 'ISR', 'IL', '+972', 'Israel', '0', '0', 112, '1', NULL, '2022-12-20 23:11:38'),
(108, 'ITA', 'IT', '+39', 'Italy', '0', '0', 13, '1', NULL, '2022-12-20 23:11:38'),
(109, 'JAM', 'JM', '+1', 'Jamaica', '0', '0', 0, '0', NULL, NULL),
(110, 'JPN', 'JP', '+81', 'Japan', '0', '0', 8, '1', NULL, '2022-12-20 23:11:38'),
(111, 'JEY', 'JE', '+44', 'Jersey', '0', '0', 113, '1', NULL, '2022-12-20 23:11:38'),
(112, 'JOR', 'JO', '+962', 'Jordan', '0', '0', 114, '1', NULL, '2022-12-20 23:11:38'),
(113, 'KAZ', 'KZ', '+7', 'Kazakhstan', '0', '0', 115, '1', NULL, '2022-12-20 23:11:38'),
(114, 'KEN', 'KE', '+254', 'Kenya', '0', '0', 116, '1', NULL, '2022-12-20 23:11:38'),
(115, 'KIR', 'KI', '+686', 'Kiribati', '0', '0', 120, '1', NULL, '2022-12-20 23:11:38'),
(116, 'PRK', 'KP', '+850', 'Korea (North)', '0', '0', 119, '1', NULL, '2022-12-20 23:11:38'),
(117, 'KOR', 'KR', '+82', 'Korea (South)', '0', '0', 9, '1', NULL, '2022-12-20 23:11:38'),
(118, 'KWT', 'KW', '+965', 'Kuwait', '0', '0', 118, '1', NULL, '2022-12-20 23:11:38'),
(119, 'KGZ', 'KG', '+996', 'Kyrgyzstan', '0', '0', 117, '1', NULL, '2022-12-20 23:11:38'),
(120, 'LAO', 'LA', '+856', 'Laos', '0', '0', 121, '1', NULL, '2022-12-20 23:11:38'),
(121, 'LVA', 'LV', '+371', 'Latvia', '0', '0', 122, '1', NULL, '2022-12-20 23:11:38'),
(122, 'LBN', 'LB', '+961', 'Lebanon', '0', '0', 123, '1', NULL, '2022-12-20 23:11:38'),
(123, 'LSO', 'LS', '+266', 'Lesotho', '0', '0', 124, '1', NULL, '2022-12-20 23:11:38'),
(124, 'LBR', 'LR', '+231', 'Liberia', '0', '0', 125, '1', NULL, '2022-12-20 23:11:38'),
(125, 'LBY', 'LY', '+218', 'Libya', '0', '0', 0, '0', NULL, NULL),
(126, 'LIE', 'LI', '+423', 'Liechtenstein', '0', '0', 0, '0', NULL, NULL),
(127, 'LTU', 'LT', '+370', 'Lithuania', '0', '0', 126, '1', NULL, '2022-12-20 23:11:38'),
(128, 'LUX', 'LU', '+352', 'Luxembourg', '0', '0', 127, '1', NULL, '2022-12-20 23:11:38'),
(129, 'MAC', 'MO', '+853', 'Macao', '0', '0', 0, '0', NULL, NULL),
(130, 'MKD', 'MK', '+389', 'Macedonia', '0', '0', 127, '1', NULL, '2022-12-20 23:11:38'),
(131, 'MDG', 'MG', '+261', 'Madagascar', '0', '0', 127, '1', NULL, '2022-12-20 23:11:38'),
(132, 'MWI', 'MW', '+265', 'Malawi', '0', '0', 127, '1', NULL, '2022-12-20 23:11:38'),
(133, 'MYS', 'MY', '+60', 'Malaysia', '0', '0', 13, '1', NULL, '2022-12-20 23:11:38'),
(134, 'MDV', 'MV', '+960', 'Maldives', '0', '0', 127, '1', NULL, '2022-12-20 23:11:38'),
(135, 'MLI', 'ML', '+225', 'Mali', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(136, 'MLT', 'MT', '+356', 'Malta', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(137, 'MHL', 'MH', '+692', 'Marshall Islands', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(138, 'MTQ', 'MQ', '+596', 'Martinique', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(139, 'MRT', 'MR', '+222', 'Mauritania', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(140, 'MUS', 'MU', '+230', 'Mauritius', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(141, 'MYT', 'YT', '+262', 'Mayotte', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(142, 'MEX', 'MX', '+52', 'Mexico', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(143, 'FSM', 'FM', '+691', 'Micronesia', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(144, 'MDA', 'MD', '+373', 'Moldova', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(145, 'MCO', 'MC', '+377', 'Monaco', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(146, 'MNG', 'MN', '+976', 'Mongolia', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(147, 'MNE', 'ME', '+382', 'Montenegro', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(148, 'MSR', 'MS', '+1', 'Montserrat', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(149, 'MAR', 'MA', '+212', 'Morocco', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(150, 'MOZ', 'MZ', '+238', 'Mozambique', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(151, 'MMR', 'MM', '+95', 'Myanmar [Burma]', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(152, 'NAM', 'NA', '+264', 'Namibia', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(153, 'NRU', 'NR', '+674', 'Nauru', '0', '0', 0, '0', NULL, NULL),
(154, 'NPL', 'NP', '+977', 'Nepal', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(155, 'NLD', 'NL', '+31', 'Netherlands', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(156, 'ANT', 'AN', '+599', 'Netherlands Antilles', '0', '0', 0, '0', NULL, NULL),
(157, 'NCL', 'NC', '+687', 'New Caledonia', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(158, 'NZL', 'NZ', '+64', 'New Zealand', '0', '0', 7, '1', NULL, '2022-12-20 23:11:39'),
(159, 'NIC', 'NI', '+505', 'Nicaragua', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(160, 'NER', 'NE', '+227', 'Niger', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(161, 'NGA', 'NG', '+234', 'Nigeria', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(162, 'NIU', 'NU', '+683', 'Niue', '0', '0', 0, '0', NULL, NULL),
(163, 'NFK', 'NF', '+672', 'Norfolk Island', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(164, 'MNP', 'MP', '+1', 'Northern Mariana Islands', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(165, 'NOR', 'NO', '+47', 'Norway', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(166, 'OMN', 'OM', '+968', 'Oman', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(167, 'PAK', 'PK', '+92', 'Pakistan', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(168, 'PLW', 'PW', '+680', 'Palau', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(169, 'PSE', 'PS', '+970', 'Palestinian Territory', '0', '0', 0, '0', NULL, NULL),
(170, 'PAN', 'PA', '+507', 'Panama', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(171, 'PNG', 'PG', '+675', 'Papua New Guinea', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(172, 'PRY', 'PY', '+595', 'Paraguay', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(173, 'PER', 'PE', '+51', 'Peru', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(174, 'PHL', 'PH', '+63', 'Philippines', '0', '0', 17, '1', NULL, '2022-12-20 23:11:39'),
(175, 'PCN', 'PN', '+64', 'Pitcairn Islands', '0', '0', 0, '0', NULL, NULL),
(176, 'POL', 'PL', '+48', 'Poland', '0', '0', 63, '1', NULL, '2022-12-20 23:11:39'),
(177, 'PRT', 'PT', '+351', 'Portugal', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(178, 'PRI', 'PR', '+1', 'Puerto Rico', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(179, 'QAT', 'QA', '+974', 'Qatar', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(180, 'REU', 'RE', '+262', 'Réunion', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(181, 'ROU', 'RO', '+40', 'Romania', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(182, 'RUS', 'RU', '+7', 'Russian Federation', '0', '0', 19, '1', NULL, '2022-12-20 23:11:39'),
(183, 'RWA', 'RW', '+250', 'Rwanda', '0', '0', 0, '0', NULL, NULL),
(184, 'KNA', 'KN', '+1', 'Saint Kitts and Nevis', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(185, 'LCA', 'LC', '+1', 'Saint Lucia', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(186, 'VCT', 'VC', '+1', 'Saint Vincent and the Grenadines', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(187, 'WSM', 'WS', '+685', 'Samoa', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(188, 'SMR', 'SM', '+378', 'San Marino', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(189, 'STP', 'ST', '+239', 'Sao Tome and Principe', '0', '0', 0, '0', NULL, NULL),
(190, 'SAU', 'SA', '+966', 'Saudi Arabia', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(191, 'SEN', 'SN', '+221', 'Senegal', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(192, 'SRB', 'RS', '+381', 'Serbia and Montenegro', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(193, 'SYC', 'SC', '+248', 'Seychelles', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(194, 'SLE', 'SL', '+232', 'Sierra Leone', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(195, 'SGP', 'SG', '+65', 'Singapore', '0', '0', 6, '1', NULL, '2022-12-20 23:11:39'),
(196, 'SVK', 'SK', '+421', 'Slovakia', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(197, 'SVN', 'SI', '+386', 'Slovenia', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(198, 'SLB', 'SB', '+677', 'Solomon Islands', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(199, 'SOM', 'SO', '+252', 'Somalia', '0', '0', 0, '0', NULL, NULL),
(200, 'ZAF', 'ZA', '+27', 'South Africa', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39');
INSERT INTO `countries` (`id`, `iso`, `iso2`, `dialing_code`, `name`, `standard_tax`, `tax_rate`, `position`, `active`, `created_at`, `updated_at`) VALUES
(201, 'SGS', 'GS', '+500', 'South Georgia and the South Sandwich Islands', '0', '0', 0, '0', NULL, NULL),
(202, 'SSD', 'SS', '+211', 'South Sudan', '0', '0', 0, '0', NULL, '2022-11-30 23:08:57'),
(203, 'ESP', 'ES', '+34', 'Spain', '0', '0', 40, '1', NULL, '2022-12-20 23:11:39'),
(204, 'LKA', 'LK', '+94', 'Sri Lanka', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(205, 'SDN', 'SD', '+249', 'Sudan', '0', '0', 0, '0', NULL, NULL),
(206, 'SUR', 'SR', '+597', 'Suriname', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(207, 'SWZ', 'SZ', '+568', 'Swaziland', '0', '0', 0, '0', NULL, NULL),
(208, 'SWE', 'SE', '+46', 'Sweden', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(209, 'CHE', 'CH', '+41', 'Switzerland', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(210, 'SYR', 'SY', '+963', 'Syrian Arab Republic (Syria)', '0', '0', 0, '0', NULL, NULL),
(211, 'TWN', 'TW', '+886', 'Taiwan', '5', '2.5', 5, '1', NULL, '2024-08-08 14:11:29'),
(212, 'TJK', 'TJ', '+992', 'Tajikistan', '0', '0', 0, '0', NULL, NULL),
(213, 'TZA', 'TZ', '+255', 'Tanzania', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(214, 'THA', 'TA', '+66', 'Thailand', '0', '0', 14, '1', NULL, '2022-12-20 23:11:39'),
(215, 'TLS', 'TL', '+670', 'Timor-Leste', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(216, 'TGO', 'TG', '+228', 'Togo', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(217, 'TKL', 'TK', '+690', 'Tokelau', '0', '0', 0, '0', NULL, NULL),
(218, 'TON', 'TO', '+676', 'Tonga', '0', '0', 0, '0', NULL, NULL),
(219, 'TTO', 'TT', '+1', 'Trinidad and Tobago', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(220, 'TUN', 'TN', '+216', 'Tunisia', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(221, 'TUR', 'TR', '+90', 'Turkey', '0', '0', 0, '0', NULL, NULL),
(222, 'TKM', 'TM', '+993', 'Turkmenistan', '0', '0', 0, '0', NULL, NULL),
(223, 'TCA', 'TC', '+1', 'Turks and Caicos Islands', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(224, 'TUV', 'TV', '+688', 'Tuvalu', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(225, 'UGA', 'UG', '+256', 'Uganda', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(226, 'UKR', 'UA', '+380', 'Ukraine', '0', '0', 0, '0', NULL, NULL),
(227, 'ARE', 'AE', '+971', 'United Arab Emirates', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(228, 'GBR', 'GB', '+44', 'United Kingdom', '0', '0', 3, '1', NULL, '2022-12-20 23:11:39'),
(229, 'USA', 'US', '+1', 'United States', '0', '0', 4, '1', NULL, '2022-12-20 23:11:39'),
(230, 'ZZZ', 'ZZ', '+', 'Unknown or Invalid Region', '0', '0', 0, '0', NULL, NULL),
(231, 'URY', 'UY', '+598', 'Uruguay', '0', '0', 0, '0', NULL, NULL),
(232, 'UZB', 'UZ', '+998', 'Uzbekistan', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(233, 'VUT', 'VU', '+678', 'Vanuatu', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(234, 'VAT', 'VA', '+38', 'Vatican City', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(235, 'VEN', 'VE', '+58', 'Venezuela', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(236, 'VNM', 'VN', '+84', 'Vietnam', '0', '0', 16, '1', NULL, '2022-12-20 23:11:39'),
(237, 'VGB', 'VG', '+1', 'Virgin Islands (British)', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(238, 'VIR', 'VI', '+1', 'Virgin Islands (US)', '0', '0', 0, '0', NULL, NULL),
(239, 'WLF', 'WF', '+681', 'Wallis and Futuna', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(240, 'ESH', 'EH', '+212', 'Western Sahara', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(241, 'YEM', 'YE', '+967', 'Yemen', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(242, 'ZMB', 'ZM', '+260', 'Zambia', '0', '0', 127, '1', NULL, '2022-12-20 23:11:39'),
(243, 'ZWE', 'ZW', '+263', 'Zimbabwe', '0', '0', 0, '0', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=459 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `order_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `origin` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `origin_iso2` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_dialing_code` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destination` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `destination_iso2` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receiver_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `receiver_phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receiver_dialing_code` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'dropoff',
  `shipping_amount` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `extra_charges` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `adjustment_amount` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `case` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `draft` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `date_delivered` timestamp NULL DEFAULT NULL,
  `date` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_id_unique` (`order_id`),
  KEY `orders_user_id_index` (`user_id`),
  KEY `orders_origin_index` (`origin`),
  KEY `orders_sender_name_index` (`sender_name`),
  KEY `orders_sender_phone_index` (`sender_phone`),
  KEY `orders_sender_dialing_code_index` (`sender_dialing_code`),
  KEY `orders_destination_index` (`destination`),
  KEY `orders_receiver_name_index` (`receiver_name`),
  KEY `orders_receiver_phone_index` (`receiver_phone`),
  KEY `orders_receiver_dialing_code_index` (`receiver_dialing_code`),
  KEY `orders_payment_status_index` (`payment_status`),
  KEY `orders_payment_method_index` (`payment_method`),
  KEY `orders_shipment_method_index` (`shipment_method`),
  KEY `orders_shipping_amount_index` (`shipping_amount`),
  KEY `orders_extra_charges_index` (`extra_charges`),
  KEY `orders_adjustment_amount_index` (`adjustment_amount`),
  KEY `orders_status_index` (`status`),
  KEY `orders_case_index` (`case`),
  KEY `orders_draft_index` (`draft`),
  KEY `orders_date_delivered_index` (`date_delivered`),
  KEY `orders_date_index` (`date`),
  KEY `orders_created_at_index` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders_data`
--

CREATE TABLE `orders_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `order_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rates_data` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `packages` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `packages_data` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extra_services` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_address` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receiver_address` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_price_data` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `images` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_data_order_id_unique` (`order_id`),
  KEY `orders_data_user_id_index` (`user_id`),
  CONSTRAINT `orders_data_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `uid` bigint(20) DEFAULT NULL,
  `browser` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platform` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platform_details` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_platform_index` (`platform`),
  KEY `sessions_last_activity_index` (`last_activity`),
  KEY `sessions_uid_index` (`uid`) USING BTREE,
  KEY `sessions_browser_index` (`browser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscribers_email_unique` (`email`),
  KEY `subscribers_active_index` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `member_id` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `language` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `per_page` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '50',
  `country` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_iso2` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dialing_code` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wallet` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `gender` enum('0','1','2') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `dob` timestamp NULL DEFAULT NULL,
  `activity_notification` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `email_notification` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `sms_notification` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `is_active` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `role` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `flag` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `access_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_registered` bigint(20) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_user_id_unique` (`user_id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_member_id_unique` (`member_id`),
  UNIQUE KEY `users_access_token_unique` (`access_token`),
  KEY `users_first_name_index` (`first_name`),
  KEY `users_last_name_index` (`last_name`),
  KEY `users_country_index` (`country`),
  KEY `users_phone_index` (`phone`),
  KEY `users_dob_index` (`dob`),
  KEY `users_activity_notification_index` (`activity_notification`),
  KEY `users_email_notification_index` (`email_notification`),
  KEY `users_sms_notification_index` (`sms_notification`),
  KEY `users_is_active_index` (`is_active`),
  KEY `users_flag_index` (`flag`),
  KEY `users_remember_token_index` (`remember_token`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users_address`
--

CREATE TABLE `users_address` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `address_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'receiver',
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_iso2` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tor_one` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `landmarks` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dialing_code` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_address_address_id_unique` (`address_id`),
  KEY `users_address_user_id_index` (`user_id`),
  CONSTRAINT `users_address_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- Dumping data for table `users_address`
--

INSERT INTO `users_address` (`id`, `user_id`, `address_id`, `address_type`, `first_name`, `last_name`, `email`, `country`, `country_iso2`, `country_name`, `address`, `tax_id`, `tor_one`, `id_number`, `landmarks`, `dialing_code`, `phone`, `created_at`, `updated_at`) VALUES
(1, 13113682, 'eaGIcX0w0l', 'pickup', 'Cleo', 'Levy', 'tamedevelopers@gmail.com', 'NGA', 'NG', 'Nigeria', 'omole phase 1, lagos', NULL, NULL, NULL, NULL, '+234', '9034121343', '2023-12-23 18:48:12', '2023-12-23 18:48:12'),
(2, 13113682, '6iOjCFQMr7', 'receiver', 'Michael', 'Floyd', 'damiruqah@mailinator.com', 'USA', 'US', 'United States', 'Consequuntur corpori', '', '', '', NULL, '+852', '16051981922', '2023-12-23 18:52:02', '2023-12-23 18:52:02');

-- --------------------------------------------------------
COMMIT;
SET FOREIGN_KEY_CHECKS=1;
