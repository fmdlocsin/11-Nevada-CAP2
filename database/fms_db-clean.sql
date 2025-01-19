-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2024 at 07:11 PM
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
-- Database: `fms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `agreement_contract`
--

CREATE TABLE `agreement_contract` (
  `ac_id` bigint(250) NOT NULL,
  `franchisee` text NOT NULL,
  `classification` text NOT NULL,
  `rights_granted` text NOT NULL,
  `franchise_term` text NOT NULL,
  `agreement_date` date NOT NULL,
  `location` text NOT NULL,
  `franchise_fee` bigint(250) NOT NULL,
  `ff_note` text NOT NULL,
  `franchise_package` bigint(250) NOT NULL,
  `fp_note` text NOT NULL,
  `bond` text NOT NULL,
  `b_note` text NOT NULL,
  `extra_note` text NOT NULL,
  `notarization_fr` text NOT NULL,
  `notarization_fr_rb` text NOT NULL,
  `notarization_fe` text NOT NULL,
  `notarization_fe_rb` text NOT NULL,
  `notary_public_seal` varchar(250) NOT NULL,
  `status` varchar(120) NOT NULL,
  `datetime_added` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agreement_contract`
--

INSERT INTO `agreement_contract` (`ac_id`, `franchisee`, `classification`, `rights_granted`, `franchise_term`, `agreement_date`, `location`, `franchise_fee`, `ff_note`, `franchise_package`, `fp_note`, `bond`, `b_note`, `extra_note`, `notarization_fr`, `notarization_fr_rb`, `notarization_fe`, `notarization_fe_rb`, `notary_public_seal`, `status`, `datetime_added`) VALUES
(261467, 'auntie-anne', 'Inline Store ', 'non-exclusive,use-trademarks,sell-products', 'undefined', '2025-02-11', 'Greenbelt Mall', 700000, 'Covers rights to use branding, training, and marketing support.', 2000000, 'Includes equipment, initial supplies, and store construction.', '250000', 'Refundable upon franchise termination, subject to conditions.', 'Franchisees are required to undergo training and adhere to Auntie Annes global standards. Fees are subject to applicable taxes.', 'Auntie Annes Philippines', 'Josephine Gomez', 'Maria Cruz', 'Carlos Santos', 'notarySealAgreement-20241118175240.png', 'active', '2024-11-19'),
(293063, 'potato-corner', 'Stall', 'non-exclusive,use-trademarks,sell-products', 'undefined', '2024-11-30', 'Glorietta Mall', 600000, 'Includes training, branding rights, and marketing support', 1500000, 'Covers equipment, initial inventory, and setup costs', '200000', 'Refundable upon franchise termination, subject to conditions', 'All fees are exclusive of applicable taxes. Additional costs may apply for site-specific requirements and upgrades.', 'Potato Corner', 'Kevin Tan', 'Maria Lopez', 'Ricardo Villanueva', 'notarySealAgreement-20241118180148.png', 'active', '2024-11-19'),
(799557, 'macao-imperial', 'Store', 'non-exclusive,use-trademarks,sell-products', 'undefined', '2024-11-17', 'Robinsons Galleria', 800000, 'Covered brand usage, training, and marketing support', 2500000, 'Included equipment, initial ingredients, and store construction', '300000', 'Refundable upon franchise termination, subject to conditions', 'This franchise term has expired. Renewal terms or further agreements must be negotiated with Macao Imperial Tea Philippines Inc.', 'Macao Imperial Tea', 'Jennifer Lopez', 'Anna Marie', 'Michael Cruz', 'notarySealAgreement-20241118175831.png', 'active', '2024-11-19'),
(808563, 'potato-corner', 'Kiosk', 'non-exclusive,use-trademarks,sell-products', 'undefined', '2025-02-09', 'SM Fairview, Quezon City', 600000, 'Covers training, branding rights, and marketing support', 1800000, 'Includes equipment, initial inventory, and store construction', '200000', 'Refundable upon franchise termination, provided conditions are met', 'All fees are exclusive of applicable taxes. Additional charges may apply for location-specific adjustments.', 'Potato Corner Philippines', 'Elena Cruz', 'Gabriel Reyes', 'Gabriel Reyes', 'notarySealAgreement-20241118183655.png', 'active', '2024-11-19'),
(946864, 'macao-imperial', 'Store', 'non-exclusive,use-trademarks,sell-products', 'undefined', '2024-12-17', 'Ayala Malls Manila Bay', 800000, 'Covers brand usage, training, and marketing support', 3000000, 'Includes equipment, initial ingredients, and store construction', '300000', 'Refundable upon franchise termination, subject to conditions', 'Franchise fees are exclusive of VAT. Additional charges may apply for renovations, location-specific needs, and compliance with Macao Imperial Tea design standards.', 'Macao Imperial Tea', 'Amanda Villanueva', 'Miguel Rosa', 'Luis Gonzales', 'notarySealAgreement-20241118175537.png', 'active', '2024-11-19'),
(953464, 'potato-corner', 'Kiosk', 'non-exclusive,use-trademarks,sell-products', 'undefined', '2025-11-19', 'SM Mall of Asia', 500000, 'Includes initial training, franchise setup support, and marketing materials.', 1200000, 'Covers equipment, initial supplies, and store construction.', '200000', 'Refundable upon franchise termination, subject to conditions.', 'All fees are subject to applicable taxes. Additional costs may apply for renovations or site-specific adjustments.', 'Potato Corner Philippines', 'Anna Santos', 'Juan Dela Cruz', 'Mark Reyes', 'notarySealAgreement-20241118175058.png', 'active', '2024-11-19');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `ex_id` bigint(250) NOT NULL,
  `encoder_id` bigint(250) NOT NULL,
  `franchisee` varchar(120) NOT NULL,
  `location` text NOT NULL,
  `expense_catergory` varchar(120) NOT NULL,
  `expense_type` text NOT NULL,
  `expense_purpose` text NOT NULL,
  `expense_amount` text NOT NULL,
  `expense_description` text NOT NULL,
  `date_added` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`ex_id`, `encoder_id`, `franchisee`, `location`, `expense_catergory`, `expense_type`, `expense_purpose`, `expense_amount`, `expense_description`, `date_added`) VALUES
(9, 0, 'macao-imperial', 'Ayala Malls Manila Bay', 'controllable-expenses', 'royaltyFees', '', '3000', 'Monthly royalty fee for franchisor support and brand usage.', '2024-11-18'),
(10, 0, 'potato-corner', 'Glorietta Mall', 'non-controllable-expenses', 'rentalsFees', '', '7500', 'Monthly lease payment for store location.', '2024-11-18'),
(11, 0, 'auntie-anne', 'Greenbelt Mall', 'other-expenses', 'Agency Fees', 'Store Cleaning Services', '2500', 'Monthly cleaning services for maintaining store hygiene.', '2024-11-18');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(250) NOT NULL,
  `item_name` text NOT NULL,
  `franchisee` varchar(150) NOT NULL,
  `uo` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `item_name`, `franchisee`, `uo`) VALUES
(1, 'Generic Icetea', 'potato-corner', 'PAC'),
(2, 'Softdrinks In Can - Coke', 'potato-corner', 'CAN'),
(3, 'Softdrinks In Can - Royal', 'potato-corner', 'CAN'),
(4, 'Softdrinks In Can - Sprite', 'potato-corner', 'CAN'),
(5, 'Softdrinks In Can - Coke Zero', 'potato-corner', 'CAN'),
(6, 'Water - Bottled 500ml', 'potato-corner', 'BT'),
(7, 'Powder - Cheese', 'potato-corner', 'PAC'),
(8, 'Powder - Sour Cream', 'potato-corner', 'PAC'),
(9, 'Powder - BBQ', 'potato-corner', 'PAC'),
(10, 'Powder - White Cheddar', 'potato-corner', 'PAC'),
(11, 'Powder - Wasabi', 'potato-corner', 'PAC'),
(12, 'Shoestring Fries 1kg', 'potato-corner', 'PAC'),
(13, 'Packaging - Regular', 'potato-corner', 'PC'),
(14, 'Packaging - Large', 'potato-corner', 'PC'),
(15, 'Packaging - Jumbo', 'potato-corner', 'PC'),
(16, 'Packaging - Mega', 'potato-corner', 'PC'),
(17, 'Packaging - Giga', 'potato-corner', 'PC'),
(18, 'Packaging - Tera', 'potato-corner', 'PC'),
(19, 'Water - Bottled 500ml', 'macao-imperial', 'BT'),
(20, 'Tea - Jasmine', 'macao-imperial', 'PAC'),
(21, 'Tea - Black', 'macao-imperial', 'PAC'),
(22, 'Boba Pack', 'macao-imperial', 'PAC'),
(23, 'Milk 1L', 'macao-imperial', 'PC'),
(24, 'Yakult 6pcs/Pack', 'macao-imperial', 'PAC'),
(25, 'Cream Cheese', 'macao-imperial', 'PAC'),
(26, 'Pudding', 'macao-imperial', 'PAC'),
(27, 'Black Pearl', 'macao-imperial', 'PAC'),
(28, 'Grass Jelly', 'macao-imperial', 'PAC'),
(29, 'Generic Icetea', 'auntie-anne', 'PAC'),
(30, 'Generic Coffee', 'auntie-anne', 'PAC'),
(31, 'Generic Lemonade', 'auntie-anne', 'PAC'),
(32, 'Water - Bottled 500ml', 'auntie-anne', 'BT'),
(33, 'Premade Dough 1kg', 'auntie-anne', 'BOX'),
(34, 'Ap Flour 1kg', 'auntie-anne', 'PC'),
(35, 'Hotdog - (12pcs/Pack)', 'auntie-anne', 'PAC'),
(36, 'Cream Cheese Pack', 'auntie-anne', 'PAC'),
(37, 'Cinnamon Sugar 1kg', 'auntie-anne', 'PAC'),
(38, 'Choco Chip 1kg', 'auntie-anne', 'PAC'),
(39, 'Almond 1kg', 'auntie-anne', 'PAC'),
(40, 'Sour Cream & Onion 1kg', 'auntie-anne', 'PAC'),
(41, 'Dip - Chocolate', 'auntie-anne', 'PC'),
(42, 'Dip - Caramel', 'auntie-anne', 'PC'),
(43, 'Dip - Cream Cheese', 'auntie-anne', 'PC');

-- --------------------------------------------------------

--
-- Table structure for table `item_inventory`
--

CREATE TABLE `item_inventory` (
  `inventory_id` bigint(250) NOT NULL,
  `item_id` bigint(250) NOT NULL,
  `franchisee` varchar(150) NOT NULL,
  `branch` text NOT NULL,
  `delivery` bigint(250) NOT NULL,
  `beginning` bigint(250) NOT NULL,
  `waste` bigint(250) NOT NULL,
  `sold` bigint(250) NOT NULL,
  `remarks` text NOT NULL,
  `filled_by` bigint(250) NOT NULL,
  `datetime_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_inventory`
--

INSERT INTO `item_inventory` (`inventory_id`, `item_id`, `franchisee`, `branch`, `delivery`, `beginning`, `waste`, `sold`, `remarks`, `filled_by`, `datetime_added`) VALUES
(355, 1, 'potato-corner', 'SM Mall of Asia', 0, 50, 0, 0, 'Initial stock', 11, '2024-11-18 18:40:34'),
(356, 2, 'potato-corner', 'SM Mall of Asia', 0, 100, 0, 0, 'Initial stock', 11, '2024-11-18 18:40:34'),
(357, 3, 'potato-corner', 'SM Mall of Asia', 0, 100, 0, 0, 'Initial stock', 11, '2024-11-18 18:40:34'),
(358, 4, 'potato-corner', 'SM Mall of Asia', 0, 100, 0, 0, 'Initial stock', 11, '2024-11-18 18:40:34'),
(359, 5, 'potato-corner', 'SM Mall of Asia', 0, 100, 0, 0, 'Initial stock', 11, '2024-11-18 18:40:34'),
(360, 6, 'potato-corner', 'SM Mall of Asia', 0, 200, 0, 0, 'Initial stock', 11, '2024-11-18 18:40:34'),
(361, 7, 'potato-corner', 'SM Mall of Asia', 0, 10, 0, 0, 'Initial stock', 11, '2024-11-18 18:40:34'),
(362, 8, 'potato-corner', 'SM Mall of Asia', 0, 10, 0, 0, 'Initial stock', 11, '2024-11-18 18:40:34'),
(363, 9, 'potato-corner', 'SM Mall of Asia', 0, 10, 0, 0, 'Initial stock', 11, '2024-11-18 18:40:34'),
(364, 10, 'potato-corner', 'SM Mall of Asia', 0, 10, 0, 0, 'Initial stock', 11, '2024-11-18 18:40:34'),
(365, 11, 'potato-corner', 'SM Mall of Asia', 0, 5, 0, 0, 'Initial stock', 11, '2024-11-18 18:40:34'),
(366, 12, 'potato-corner', 'SM Mall of Asia', 0, 50, 0, 0, 'Initial stock', 11, '2024-11-18 18:40:34'),
(367, 13, 'potato-corner', 'SM Mall of Asia', 0, 500, 0, 0, 'Initial stock', 11, '2024-11-18 18:40:34'),
(368, 14, 'potato-corner', 'SM Mall of Asia', 0, 400, 0, 0, 'Initial stock', 11, '2024-11-18 18:40:34'),
(369, 15, 'potato-corner', 'SM Mall of Asia', 0, 300, 0, 0, 'Initial stock', 11, '2024-11-18 18:40:34'),
(370, 16, 'potato-corner', 'SM Mall of Asia', 0, 200, 0, 0, 'Initial stock', 11, '2024-11-18 18:40:34'),
(371, 17, 'potato-corner', 'SM Mall of Asia', 0, 100, 0, 0, 'Initial stock', 11, '2024-11-18 18:40:34'),
(372, 18, 'potato-corner', 'SM Mall of Asia', 0, 50, 0, 0, 'Initial stock', 11, '2024-11-18 18:40:34'),
(373, 29, 'auntie-anne', 'Greenbelt Mall', 0, 50, 0, 0, 'Initial stock', 11, '2024-11-18 18:44:53'),
(374, 30, 'auntie-anne', 'Greenbelt Mall', 0, 50, 0, 0, 'Initial stock', 11, '2024-11-18 18:44:53'),
(375, 31, 'auntie-anne', 'Greenbelt Mall', 0, 50, 0, 0, 'Initial stock', 11, '2024-11-18 18:44:53'),
(376, 32, 'auntie-anne', 'Greenbelt Mall', 0, 200, 0, 0, 'Initial stock', 11, '2024-11-18 18:44:53'),
(377, 33, 'auntie-anne', 'Greenbelt Mall', 0, 20, 0, 0, 'Initial stock', 11, '2024-11-18 18:44:53'),
(378, 34, 'auntie-anne', 'Greenbelt Mall', 0, 15, 0, 0, 'Initial stock', 11, '2024-11-18 18:44:53'),
(379, 35, 'auntie-anne', 'Greenbelt Mall', 0, 50, 0, 0, 'Initial stock', 11, '2024-11-18 18:44:53'),
(380, 36, 'auntie-anne', 'Greenbelt Mall', 0, 10, 0, 0, 'Initial stock', 11, '2024-11-18 18:44:53'),
(381, 37, 'auntie-anne', 'Greenbelt Mall', 0, 10, 0, 0, 'Initial stock', 11, '2024-11-18 18:44:53'),
(382, 38, 'auntie-anne', 'Greenbelt Mall', 0, 10, 0, 0, 'Initial stock', 11, '2024-11-18 18:44:53'),
(383, 39, 'auntie-anne', 'Greenbelt Mall', 0, 5, 0, 0, 'Initial stock', 11, '2024-11-18 18:44:53'),
(384, 40, 'auntie-anne', 'Greenbelt Mall', 0, 10, 0, 0, 'Initial stock', 11, '2024-11-18 18:44:53'),
(385, 41, 'auntie-anne', 'Greenbelt Mall', 0, 100, 0, 0, 'Initial stock', 11, '2024-11-18 18:44:53'),
(386, 42, 'auntie-anne', 'Greenbelt Mall', 0, 100, 0, 0, 'Initial stock', 11, '2024-11-18 18:44:53'),
(387, 43, 'auntie-anne', 'Greenbelt Mall', 0, 100, 0, 0, 'Initial stock', 11, '2024-11-18 18:44:53'),
(388, 19, 'macao-imperial', 'Ayala Malls Manila Bay', 0, 200, 0, 0, 'Initial stock', 11, '2024-11-18 18:47:14'),
(389, 20, 'macao-imperial', 'Ayala Malls Manila Bay', 0, 10, 0, 0, 'Initial stock', 11, '2024-11-18 18:47:14'),
(390, 21, 'macao-imperial', 'Ayala Malls Manila Bay', 0, 10, 0, 0, 'Initial stock', 11, '2024-11-18 18:47:14'),
(391, 22, 'macao-imperial', 'Ayala Malls Manila Bay', 0, 50, 0, 0, 'Initial stock', 11, '2024-11-18 18:47:14'),
(392, 23, 'macao-imperial', 'Ayala Malls Manila Bay', 0, 100, 0, 0, 'Initial stock', 11, '2024-11-18 18:47:14'),
(393, 24, 'macao-imperial', 'Ayala Malls Manila Bay', 0, 30, 0, 0, 'Initial stock', 11, '2024-11-18 18:47:14'),
(394, 25, 'macao-imperial', 'Ayala Malls Manila Bay', 0, 20, 0, 0, 'Initial stock', 11, '2024-11-18 18:47:14'),
(395, 26, 'macao-imperial', 'Ayala Malls Manila Bay', 0, 30, 0, 0, 'Initial stock', 11, '2024-11-18 18:47:14'),
(396, 27, 'macao-imperial', 'Ayala Malls Manila Bay', 0, 20, 0, 0, 'Initial stock', 11, '2024-11-18 18:47:14'),
(397, 28, 'macao-imperial', 'Ayala Malls Manila Bay', 0, 30, 0, 0, 'Initial stock', 11, '2024-11-18 18:47:14'),
(398, 19, 'macao-imperial', 'Robinsons Galleria', 0, 250, 0, 0, 'Initial stock', 11, '2024-11-18 18:49:09'),
(399, 20, 'macao-imperial', 'Robinsons Galleria', 0, 15, 0, 0, 'Initial stock', 11, '2024-11-18 18:49:09'),
(400, 21, 'macao-imperial', 'Robinsons Galleria', 0, 15, 0, 0, 'Initial stock', 11, '2024-11-18 18:49:09'),
(401, 22, 'macao-imperial', 'Robinsons Galleria', 0, 60, 0, 0, 'Initial stock', 11, '2024-11-18 18:49:09'),
(402, 23, 'macao-imperial', 'Robinsons Galleria', 0, 120, 0, 0, 'Initial stock', 11, '2024-11-18 18:49:09'),
(403, 24, 'macao-imperial', 'Robinsons Galleria', 0, 40, 0, 0, 'Initial stock', 11, '2024-11-18 18:49:09'),
(404, 25, 'macao-imperial', 'Robinsons Galleria', 0, 25, 0, 0, 'Initial stock', 11, '2024-11-18 18:49:09'),
(405, 26, 'macao-imperial', 'Robinsons Galleria', 0, 40, 0, 0, 'Initial stock', 11, '2024-11-18 18:49:09'),
(406, 27, 'macao-imperial', 'Robinsons Galleria', 0, 25, 0, 0, 'Initial stock', 11, '2024-11-18 18:49:09'),
(407, 28, 'macao-imperial', 'Robinsons Galleria', 0, 40, 0, 0, 'Initial stock', 11, '2024-11-18 18:49:09'),
(408, 1, 'potato-corner', 'Glorietta Mall', 0, 50, 0, 0, 'Initial stock', 11, '2024-11-18 18:51:14'),
(409, 2, 'potato-corner', 'Glorietta Mall', 0, 100, 0, 0, 'Initial stock', 11, '2024-11-18 18:51:14'),
(410, 3, 'potato-corner', 'Glorietta Mall', 0, 100, 0, 0, 'Initial stock', 11, '2024-11-18 18:51:14'),
(411, 4, 'potato-corner', 'Glorietta Mall', 0, 100, 0, 0, 'Initial stock', 11, '2024-11-18 18:51:14'),
(412, 5, 'potato-corner', 'Glorietta Mall', 0, 100, 0, 0, 'Initial stock', 11, '2024-11-18 18:51:14'),
(413, 6, 'potato-corner', 'Glorietta Mall', 0, 200, 0, 0, 'Initial stock', 11, '2024-11-18 18:51:14'),
(414, 7, 'potato-corner', 'Glorietta Mall', 0, 10, 0, 0, 'Initial stock', 11, '2024-11-18 18:51:14'),
(415, 8, 'potato-corner', 'Glorietta Mall', 0, 10, 0, 0, 'Initial stock', 11, '2024-11-18 18:51:14'),
(416, 9, 'potato-corner', 'Glorietta Mall', 0, 10, 0, 0, 'Initial stock', 11, '2024-11-18 18:51:14'),
(417, 10, 'potato-corner', 'Glorietta Mall', 0, 10, 0, 0, 'Initial stock', 11, '2024-11-18 18:51:14'),
(418, 11, 'potato-corner', 'Glorietta Mall', 0, 5, 0, 0, 'Initial stock', 11, '2024-11-18 18:51:14'),
(419, 12, 'potato-corner', 'Glorietta Mall', 0, 50, 0, 0, 'Initial stock', 11, '2024-11-18 18:51:14'),
(420, 13, 'potato-corner', 'Glorietta Mall', 0, 500, 0, 0, 'Initial stock', 11, '2024-11-18 18:51:14'),
(421, 14, 'potato-corner', 'Glorietta Mall', 0, 400, 0, 0, 'Initial stock', 11, '2024-11-18 18:51:14'),
(422, 15, 'potato-corner', 'Glorietta Mall', 0, 300, 0, 0, 'Initial stock', 11, '2024-11-18 18:51:14'),
(423, 16, 'potato-corner', 'Glorietta Mall', 0, 200, 0, 0, 'Initial stock', 11, '2024-11-18 18:51:14'),
(424, 17, 'potato-corner', 'Glorietta Mall', 0, 100, 0, 0, 'Initial stock', 11, '2024-11-18 18:51:14'),
(425, 18, 'potato-corner', 'Glorietta Mall', 0, 50, 0, 0, 'Initial stock', 11, '2024-11-18 18:51:14'),
(426, 1, 'potato-corner', 'SM Fairview, Quezon City', 0, 60, 0, 0, 'Initial stock', 11, '2024-11-18 18:56:23'),
(427, 2, 'potato-corner', 'SM Fairview, Quezon City', 0, 120, 0, 0, 'Initial stock', 11, '2024-11-18 18:56:23'),
(428, 3, 'potato-corner', 'SM Fairview, Quezon City', 0, 120, 0, 0, 'Initial stock', 11, '2024-11-18 18:56:23'),
(429, 4, 'potato-corner', 'SM Fairview, Quezon City', 0, 120, 0, 0, 'Initial stock', 11, '2024-11-18 18:56:23'),
(430, 5, 'potato-corner', 'SM Fairview, Quezon City', 0, 120, 0, 0, 'Initial stock', 11, '2024-11-18 18:56:23'),
(431, 6, 'potato-corner', 'SM Fairview, Quezon City', 0, 250, 0, 0, 'Initial stock', 11, '2024-11-18 18:56:23'),
(432, 7, 'potato-corner', 'SM Fairview, Quezon City', 0, 12, 0, 0, 'Initial stock', 11, '2024-11-18 18:56:23'),
(433, 8, 'potato-corner', 'SM Fairview, Quezon City', 0, 12, 0, 0, 'Initial stock', 11, '2024-11-18 18:56:23'),
(434, 9, 'potato-corner', 'SM Fairview, Quezon City', 0, 12, 0, 0, 'Initial stock', 11, '2024-11-18 18:56:23'),
(435, 10, 'potato-corner', 'SM Fairview, Quezon City', 0, 12, 0, 0, 'Initial stock', 11, '2024-11-18 18:56:23'),
(436, 11, 'potato-corner', 'SM Fairview, Quezon City', 0, 6, 0, 0, 'Initial stock', 11, '2024-11-18 18:56:23'),
(437, 12, 'potato-corner', 'SM Fairview, Quezon City', 0, 60, 0, 0, 'Initial stock', 11, '2024-11-18 18:56:23'),
(438, 13, 'potato-corner', 'SM Fairview, Quezon City', 0, 600, 0, 0, 'Initial stock', 11, '2024-11-18 18:56:23'),
(439, 14, 'potato-corner', 'SM Fairview, Quezon City', 0, 500, 0, 0, 'Initial stock', 11, '2024-11-18 18:56:23'),
(440, 15, 'potato-corner', 'SM Fairview, Quezon City', 0, 400, 0, 0, 'Initial stock', 11, '2024-11-18 18:56:23'),
(441, 16, 'potato-corner', 'SM Fairview, Quezon City', 0, 300, 0, 0, 'Initial stock', 11, '2024-11-18 18:56:23'),
(442, 17, 'potato-corner', 'SM Fairview, Quezon City', 0, 200, 0, 0, 'Initial stock', 11, '2024-11-18 18:56:23'),
(443, 18, 'potato-corner', 'SM Fairview, Quezon City', 0, 100, 0, 0, 'Initial stock', 11, '2024-11-18 18:56:23');

-- --------------------------------------------------------

--
-- Table structure for table `lease_contract`
--

CREATE TABLE `lease_contract` (
  `lease_id` bigint(250) NOT NULL,
  `franchisee` text NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `space_number` text NOT NULL,
  `area` text NOT NULL,
  `classification` text NOT NULL,
  `rent` text NOT NULL,
  `percentage_rent` text NOT NULL,
  `minimum_rent` text NOT NULL,
  `additional_fee` text NOT NULL,
  `af_note` text NOT NULL,
  `total_monthly_dues` text NOT NULL,
  `tmd_note` text NOT NULL,
  `lease_deposit` text NOT NULL,
  `ld_note` text NOT NULL,
  `lessor_name1` text NOT NULL,
  `lessor_address1` text NOT NULL,
  `lessor_name2` text NOT NULL,
  `lessor_address2` text NOT NULL,
  `extra_note` text NOT NULL,
  `notary_public_seal` varchar(150) NOT NULL,
  `status` varchar(120) NOT NULL,
  `datetime_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lease_contract`
--

INSERT INTO `lease_contract` (`lease_id`, `franchisee`, `start_date`, `end_date`, `space_number`, `area`, `classification`, `rent`, `percentage_rent`, `minimum_rent`, `additional_fee`, `af_note`, `total_monthly_dues`, `tmd_note`, `lease_deposit`, `ld_note`, `lessor_name1`, `lessor_address1`, `lessor_name2`, `lessor_address2`, `extra_note`, `notary_public_seal`, `status`, `datetime_added`) VALUES
(8, 'potato-corner', '2024-11-19', '2028-10-09', 'K1-23', '15', 'Kiosk', '2000', '5', '1800', '5000', 'undefined', '35000', 'Subject to adjustment based on annual reviews.', '70000', 'Equivalent to two months rent, refundable upon contract termination.', 'SM Prime Holdings Inc.', 'SM Mall of Asia', 'Juan Dela Cruz', 'Barangay Maginhawa, Quezon City', 'All terms are subject to the mall management’s policies. Delayed payments may incur penalties.', 'notarySeal-20241118180500.png', 'active', '2024-11-19 01:05:00'),
(9, 'auntie-anne', '2024-11-19', '2025-03-21', 'INL-45', '20', 'Inline Store ', '2500', '7', '2200', '6000', 'undefined', '56000', 'Subject to updates based on mall management guidelines.', '112000', 'Refundable upon contract termination, subject to compliance.', 'Ayala Land Inc.', 'Glorietta Mall', 'Maria Lopez', 'Barangay Poblacion, Makati City', 'Rent increases by 5 percent annually per agreement.', 'notarySeal-20241118180754.png', 'active', '2024-11-19 01:07:54'),
(10, 'macao-imperial', '2024-11-19', '2025-09-29', 'STND-12', '30', 'Standalone Outlet', '3000', '8', '2800', '8000', 'undefined', '98000', 'Includes VAT and other related costs.', '196000', 'Refundable upon contract termination if no outstanding dues exist.', 'Robinsons Land Corporation', 'Robinsons Galleria', 'Luis Santos', 'Barangay Commonwealth, Quezon City', 'All operations must align with mall regulations and agreed operating hours.', 'notarySeal-20241118180943.png', 'active', '2024-11-19 01:09:43');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` bigint(250) NOT NULL,
  `user_id` bigint(250) NOT NULL,
  `activity_type` varchar(250) NOT NULL,
  `datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `activity_type`, `datetime`) VALUES
(2250532, 11, 'manpower_employee_added', '2024-11-19 02:10:40'),
(2343240, 11, 'manpower_employee_added', '2024-11-19 01:28:06'),
(2661405, 11, 'manpower_employee_added', '2024-11-19 01:18:52'),
(4288837, 11, 'manpower_employee_added', '2024-11-19 01:34:12'),
(5022294, 11, 'manpower_employee_added', '2024-11-19 01:32:56'),
(8288284, 11, 'manpower_employee_added', '2024-11-19 01:29:30'),
(9123045, 11, 'manpower_employee_added', '2024-11-19 01:24:06');

-- --------------------------------------------------------

--
-- Table structure for table `sales_report`
--

CREATE TABLE `sales_report` (
  `report_id` bigint(250) NOT NULL,
  `ac_id` bigint(250) NOT NULL,
  `encoder_id` bigint(250) NOT NULL,
  `franchisee` varchar(150) NOT NULL,
  `services` varchar(120) NOT NULL,
  `transactions` text NOT NULL,
  `grand_total` text NOT NULL,
  `date_added` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_report`
--

INSERT INTO `sales_report` (`report_id`, `ac_id`, `encoder_id`, `franchisee`, `services`, `transactions`, `grand_total`, `date_added`) VALUES
(28, 293063, 11, 'potato-corner', 'dine-in', '15000, 5000, 3000, 2000', '25000.00', '2024-11-18'),
(29, 261467, 3272372, 'auntie-anne', 'take-out', '18000, 6000, 4000, 1500', '29500.00', '2024-11-18'),
(30, 799557, 3272372, 'macao-imperial', 'delivery', '12000, 8000, 2500', '22500.00', '2024-11-18');

-- --------------------------------------------------------

--
-- Table structure for table `users_accounts`
--

CREATE TABLE `users_accounts` (
  `user_id` bigint(250) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_photo` varchar(150) NOT NULL DEFAULT 'default-profile.png',
  `user_email` varchar(200) NOT NULL,
  `user_password` varchar(200) NOT NULL,
  `user_phone_number` int(15) NOT NULL,
  `user_address` text DEFAULT NULL,
  `user_birthdate` date DEFAULT NULL,
  `user_type` varchar(100) NOT NULL DEFAULT 'user',
  `user_status` varchar(100) NOT NULL DEFAULT 'active',
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_accounts`
--

INSERT INTO `users_accounts` (`user_id`, `user_name`, `user_photo`, `user_email`, `user_password`, `user_phone_number`, `user_address`, `user_birthdate`, `user_type`, `user_status`, `date_created`) VALUES
(11, 'Administrator', 'default-profile.png', 'admin@gmail.com', 'YWRtaW4=', 947326212, 'San Pedro, Minalin, Pampanga', '2024-07-17', 'admin', 'active', '2024-07-04 13:05:09'),
(1754908, 'Sofia Hernandez', '', 'sofiahernandez@gmail.com', '', 2147483647, 'Diosdado Macapagal Boulevard', '1997-09-20', 'user', 'active', '2024-11-19 01:34:12'),
(2504548, 'Carlos Reyes', '', 'carlosreyes@gmail.com', '', 2147483647, 'Legazpi Street, Makati City', '2001-06-10', 'user', 'active', '2024-11-19 01:28:06'),
(2983296, 'John Santos', '', 'johnsantos@gmail.com', '', 2147483647, 'Barangay Maligaya, Quezon City', '1995-03-15', 'user', 'active', '2024-11-19 01:18:52'),
(3083139, 'Brian Salangsang', '', 'brian@gmail.com', 'Y29udHJhY3Q=', 2147483647, 'Laguna', '2001-04-03', 'business_development', 'active', '2024-11-11 23:12:28'),
(3272372, 'Julia Dalipe', '', 'julia@gmail.com', 'c2FsZXM=', 2147483647, 'Manila', '2001-11-21', 'sales', 'active', '2024-11-11 23:10:26'),
(5135376, 'Matthew Florendo', '', 'matthew@gmail.com', 'aW52ZW50b3J5', 2147483647, 'Cainta', '2024-10-26', 'inventory', 'active', '2024-10-26 15:38:34'),
(6053864, 'Maria Lopez', '', 'marialopez@gmail.com', '', 2147483647, 'Pioneer Street, Mandaluyong City', '1999-04-22', 'user', 'active', '2024-11-19 01:24:06'),
(6647418, 'Anna Cruz', '', 'annacruz@gmail.com', '', 2147483647, 'Paseo de Roxas, Makati City', '2002-05-05', 'user', 'active', '2024-11-19 01:29:30'),
(7172351, 'Matteo Locsin', '', 'matteo@gmail.com', 'bWFucG93ZXI=', 2147483647, 'Makati', '2001-03-14', 'manpower', 'active', '2024-11-11 23:11:46'),
(9095846, 'Mark Villanueva', '', 'markvillanueva@gmail.com', '', 2147483647, 'Aurora Boulevard, Quezon City', '1999-02-12', 'user', 'active', '2024-11-19 02:10:40'),
(9794496, 'Lorenzo Bautista', '', 'lorenzobautista@gmail.com', '', 2147483647, 'Macapagal Boulevard, Pasay City', '1998-08-12', 'user', 'active', '2024-11-19 01:32:56');

-- --------------------------------------------------------

--
-- Table structure for table `user_information`
--

CREATE TABLE `user_information` (
  `user_id` bigint(250) NOT NULL,
  `assigned_at` bigint(250) NOT NULL,
  `employee_status` varchar(100) NOT NULL,
  `franchisee` varchar(250) NOT NULL,
  `branch` varchar(250) NOT NULL,
  `user_shift` varchar(120) NOT NULL,
  `certification_name` text NOT NULL,
  `certification_date` date NOT NULL,
  `certificate_file_name` text NOT NULL,
  `certificate_status` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_information`
--

INSERT INTO `user_information` (`user_id`, `assigned_at`, `employee_status`, `franchisee`, `branch`, `user_shift`, `certification_name`, `certification_date`, `certificate_file_name`, `certificate_status`) VALUES
(11, 0, 'unassigned', '0', '', 'Full Time', '', '0000-00-00', '', ''),
(1754908, 946864, 'assigned', 'macao-imperial', 'Ayala Malls Manila Bay', 'Afternoon Shift', '', '0000-00-00', '', ''),
(2504548, 261467, 'assigned', 'auntie-anne', 'Greenbelt Mall', 'Full Time', '', '0000-00-00', '', ''),
(2983296, 799557, 'assigned', 'macao-imperial', 'Robinsons Galleria', 'Full Time', '', '0000-00-00', '', ''),
(3083139, 261467, 'assigned', '', 'Greenbelt Mall', 'Full Time', '', '0000-00-00', '', ''),
(3272372, 293063, 'assigned', '', 'Glorietta Mall', 'Full Time', 'Philhealth', '0000-00-00', '', ''),
(5135376, 946864, 'assigned', '', 'Ayala Malls Manila Bay', 'Full Time', '', '0000-00-00', '', ''),
(6053864, 799557, 'assigned', 'macao-imperial', 'Robinsons Galleria', 'Full Time', '', '0000-00-00', '', ''),
(6647418, 293063, 'assigned', 'potato-corner', 'Glorietta Mall', 'Full Time', '', '0000-00-00', '', ''),
(7172351, 953464, 'assigned', '', 'SM Mall of Asia', 'Full Time', '', '0000-00-00', '', ''),
(9095846, 0, 'unassigned', '0', '', '', '', '0000-00-00', '', ''),
(9794496, 953464, 'assigned', 'potato-corner', 'SM Mall of Asia', 'Full Time', '', '0000-00-00', '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agreement_contract`
--
ALTER TABLE `agreement_contract`
  ADD PRIMARY KEY (`ac_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`ex_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `item_inventory`
--
ALTER TABLE `item_inventory`
  ADD PRIMARY KEY (`inventory_id`);

--
-- Indexes for table `lease_contract`
--
ALTER TABLE `lease_contract`
  ADD PRIMARY KEY (`lease_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `sales_report`
--
ALTER TABLE `sales_report`
  ADD PRIMARY KEY (`report_id`);

--
-- Indexes for table `users_accounts`
--
ALTER TABLE `users_accounts`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_information`
--
ALTER TABLE `user_information`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agreement_contract`
--
ALTER TABLE `agreement_contract`
  MODIFY `ac_id` bigint(250) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=996724;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `ex_id` bigint(250) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(250) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `item_inventory`
--
ALTER TABLE `item_inventory`
  MODIFY `inventory_id` bigint(250) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=444;

--
-- AUTO_INCREMENT for table `lease_contract`
--
ALTER TABLE `lease_contract`
  MODIFY `lease_id` bigint(250) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` bigint(250) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9999422;

--
-- AUTO_INCREMENT for table `sales_report`
--
ALTER TABLE `sales_report`
  MODIFY `report_id` bigint(250) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `users_accounts`
--
ALTER TABLE `users_accounts`
  MODIFY `user_id` bigint(250) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31231314;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
