-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 07, 2025 at 08:49 AM
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
-- Database: `nexo_banking`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `account_type` enum('checking','savings','credit','loan') NOT NULL,
  `account_number` varchar(20) NOT NULL,
  `balance` decimal(15,2) DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'USD',
  `status` enum('active','inactive','frozen','closed') DEFAULT 'active',
  `credit_limit` decimal(15,2) DEFAULT NULL,
  `interest_rate` decimal(5,4) DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`account_id`, `user_id`, `account_type`, `account_number`, `balance`, `currency`, `status`, `credit_limit`, `interest_rate`, `last_activity`, `created_at`, `updated_at`) VALUES
(1, 1, 'checking', '1234567890', 185420.50, 'INR', 'active', NULL, NULL, '2025-11-07 18:38:23', '2025-11-06 11:59:08', '2025-11-07 20:00:23'),
(2, 1, 'savings', '1234567891', 545000.00, 'INR', 'active', NULL, 0.0350, '2025-11-07 11:59:08', '2025-11-06 11:59:08', '2025-11-07 11:59:08'),
(3, 1, 'credit', '1234567892', 125000.00, 'INR', 'active', 500000.00, NULL, '2025-11-07 11:59:08', '2025-11-06 11:59:08', '2025-11-07 11:59:08'),
(4, 2, 'checking', '1070353153', 325680.00, 'INR', 'active', NULL, NULL, '2025-11-07 15:41:15', '2025-11-06 18:12:03', '2025-11-07 15:41:15'),
(5, 2, 'savings', '1034686196', 892000.00, 'INR', 'active', NULL, 0.0400, '2025-11-07 18:12:03', '2025-11-06 18:12:03', '2025-11-07 13:07:30'),
(6, 3, 'checking', '2345678901', 456789.25, 'INR', 'active', NULL, NULL, '2025-11-07 10:20:00', '2025-11-01 10:15:00', '2025-11-07 10:20:00'),
(7, 3, 'savings', '2345678902', 1250000.00, 'INR', 'active', NULL, 0.0450, '2025-11-07 10:15:00', '2025-11-01 10:15:00', '2025-11-07 10:15:00'),
(8, 3, 'credit', '2345678903', 85000.00, 'INR', 'active', 1000000.00, NULL, '2025-11-06 14:20:00', '2025-11-01 10:15:00', '2025-11-06 14:20:00'),
(9, 4, 'checking', '3456789012', 78540.00, 'INR', 'active', NULL, NULL, '2025-11-05 16:30:00', '2025-11-02 10:45:00', '2025-11-05 16:30:00'),
(10, 4, 'savings', '3456789013', 185000.00, 'INR', 'active', NULL, 0.0300, '2025-11-05 16:30:00', '2025-11-02 10:45:00', '2025-11-05 16:30:00'),
(11, 5, 'checking', '4567890123', 542300.00, 'INR', 'active', NULL, NULL, '2025-11-07 10:15:00', '2025-10-28 08:45:00', '2025-11-07 10:15:00'),
(12, 5, 'savings', '4567890124', 1850000.00, 'INR', 'active', NULL, 0.0400, '2025-11-07 10:15:00', '2025-10-28 08:45:00', '2025-11-07 10:15:00'),
(13, 5, 'credit', '4567890125', 150000.00, 'INR', 'active', 750000.00, NULL, '2025-11-07 10:15:00', '2025-10-28 08:45:00', '2025-11-07 10:15:00'),
(14, 7, 'checking', '5678901234', 895600.00, 'INR', 'active', NULL, NULL, '2025-11-06 09:45:00', '2025-10-25 13:45:00', '2025-11-06 09:45:00'),
(15, 7, 'savings', '5678901235', 2450000.00, 'INR', 'active', NULL, 0.0500, '2025-11-06 09:45:00', '2025-10-25 13:45:00', '2025-11-06 09:45:00'),
(16, 7, 'credit', '5678901236', 225000.00, 'INR', 'active', 1500000.00, NULL, '2025-11-06 09:45:00', '2025-10-25 13:45:00', '2025-11-06 09:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `admin_sessions`
--

CREATE TABLE `admin_sessions` (
  `session_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_sessions`
--

INSERT INTO `admin_sessions` (`session_id`, `admin_id`, `session_token`, `ip_address`, `user_agent`, `created_at`, `expires_at`, `last_activity`) VALUES
(1, 1, 'ba43f65e1e65dd1f6d78bfc938ea65bfbfcc8fec86372712ca7d8f3953f21224', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 18:17:29', '2025-11-07 13:47:29', '2025-11-06 19:05:02'),
(2, 1, '98cedd96516faf41717ec84b0ed187db6b6effd52adf6d6754891996f79354cc', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 20:19:31', '2025-11-07 15:49:31', NULL),
(3, 1, '348a6d86ca643cc070ed71c849aefe6b39ef52475b4d93283786a7f94c74916b', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 03:09:06', '2025-11-07 22:39:06', NULL),
(4, 1, 'cce20ae030757a11e0108eeb783aec8f0ab263ce490e90794e836dab071ba830', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 03:09:10', '2025-11-07 22:39:10', NULL),
(5, 1, '86d4a83cafd76b121088719821cd183765903d1f2c66df519b8ff407c6ddfe8c', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 03:09:14', '2025-11-07 22:39:14', NULL),
(6, 1, 'd4ab29cf59afa500b4a8dc4b18b5ced2d8dc95df076e40c038422853a280b576', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 03:09:23', '2025-11-07 22:39:23', NULL),
(7, 1, '863438d0e4a4f26df35171f8400a9ae45902ee0ab4d5e1afd3318c36ff80bef0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 03:33:53', '2025-11-07 23:03:53', NULL),
(8, 1, 'b12805b22bc3a71c367f4c2dafc54bebcd78c50fb511a2f3daffaa455b0ef73d', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 03:34:05', '2025-11-07 23:04:05', NULL),
(9, 1, 'da08d5871f90f8cecd2f53c65b59f0337b9cb893d01b42bdd032e986cc671360', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 03:36:32', '2025-11-07 23:06:32', '2025-11-07 05:29:14'),
(10, 1, 'bc146abd162a585ab17eaa66886ef906e39c82353f591c5d4fbe1cfe8a9b0a7b', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 07:14:32', '2025-11-08 02:44:32', '2025-11-07 07:46:35');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` enum('super_admin','admin','moderator') DEFAULT 'admin',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`admin_id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `role`, `status`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'admin', 'admin@nexo.com', '$2y$10$U0Q22oeU1HyT/Wcgjft5weJ7qPKsu8nr3iW93bopunem2wFHkLmyu', 'System', 'Admin', 'super_admin', 'active', '2025-11-06 17:54:08', '2025-11-07 07:14:32', '2025-11-07 07:14:32'),
(2, 'suresh.kumar', 'suresh.kumar@nexo.com', '$2y$10$U0Q22oeU1HyT/Wcgjft5weJ7qPKsu8nr3iW93bopunem2wFHkLmyu', 'Suresh', 'Kumar', 'admin', 'active', '2025-10-15 10:00:00', '2025-11-07 09:30:00', '2025-11-07 09:30:00'),
(3, 'meera.nair', 'meera.nair@nexo.com', '$2y$10$U0Q22oeU1HyT/Wcgjft5weJ7qPKsu8nr3iW93bopunem2wFHkLmyu', 'Meera', 'Nair', 'moderator', 'active', '2025-10-20 14:30:00', '2025-11-06 16:45:00', '2025-11-06 16:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `user_id`, `action_type`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 2, 'USER_REGISTRATION', 'users', 2, NULL, '{\"username\":\"23241101140054\",\"email\":\"rsardhara451@rku.ac.in\",\"member_type\":\"premium\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 18:12:03'),
(2, NULL, 'LOGIN_FAILED', NULL, NULL, NULL, '{\"account_number\":\"rsardhara451@rku.ac.in\",\"reason\":\"Invalid account number\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 18:19:25'),
(3, 1, 'USER_APPROVED', 'users', 2, '{\"status\":\"pending\"}', '{\"status\":\"active\",\"approved_by\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 18:22:35'),
(4, 2, 'LOGIN_SUCCESS', 'users', 2, NULL, '{\"account_number\":\"1070353153\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 18:22:44'),
(5, 2, 'LOGIN_SUCCESS', 'users', 2, NULL, '{\"account_number\":\"1070353153\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 18:23:02'),
(6, 2, 'LOGIN_SUCCESS', 'users', 2, NULL, '{\"account_number\":\"1070353153\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 18:24:44'),
(7, 2, 'LOGIN_SUCCESS', 'users', 2, NULL, '{\"account_number\":\"1070353153\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 18:47:08'),
(8, 2, 'LOGIN_SUCCESS', 'users', 2, NULL, '{\"account_number\":\"1070353153\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 19:04:08'),
(9, 2, 'LOGIN_SUCCESS', 'users', 2, NULL, '{\"account_number\":\"1070353153\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 20:19:10'),
(10, 2, 'LOGIN_SUCCESS', 'users', 2, NULL, '{\"account_number\":\"1070353153\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 20:19:26'),
(11, NULL, 'LOGIN_FAILED', NULL, NULL, NULL, '{\"account_number\":\"admin@nexo.com\",\"reason\":\"Invalid account number\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 20:20:24'),
(12, 2, 'LOGIN_SUCCESS', 'users', 2, NULL, '{\"account_number\":\"1070353153\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 20:20:37'),
(13, NULL, 'LOGIN_FAILED', NULL, NULL, NULL, '{\"account_number\":\"admin@nexo.com\",\"reason\":\"Invalid account number\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 20:20:39'),
(14, 2, 'LOGIN_SUCCESS', 'users', 2, NULL, '{\"account_number\":\"1070353153\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 20:21:46'),
(15, 2, 'LOGIN_SUCCESS', 'users', 2, NULL, '{\"account_number\":\"1070353153\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 03:06:49'),
(16, 2, 'create_loan_application', 'loans', 1, NULL, '{\"loan_type\":\"auto\",\"principal\":20000,\"term_months\":12}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 03:27:41'),
(17, 2, 'create_loan_application', 'loans', 2, NULL, '{\"loan_type\":\"business\",\"principal\":75000,\"term_months\":84}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 03:29:54'),
(18, 1, 'approve_loan', 'loans', 2, '{\"loan_id\":2,\"user_id\":2,\"loan_type\":\"business\",\"principal\":\"75000.00\",\"outstanding\":\"75000.00\",\"term_months\":84,\"apr\":\"0.00\",\"monthly_payment\":\"892.86\",\"status\":\"pending\",\"purpose\":\"\",\"created_at\":\"2025-11-07 08:59:54\",\"updated_at\":\"2025-11-07 08:59:54\"}', '{\"status\":\"approved\",\"note\":\"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 03:37:25'),
(19, 1, 'approve_loan', 'loans', 1, '{\"loan_id\":1,\"user_id\":2,\"loan_type\":\"auto\",\"principal\":\"20000.00\",\"outstanding\":\"20000.00\",\"term_months\":12,\"apr\":\"0.00\",\"monthly_payment\":\"1666.67\",\"status\":\"pending\",\"purpose\":\"\",\"created_at\":\"2025-11-07 08:57:41\",\"updated_at\":\"2025-11-07 08:57:41\"}', '{\"status\":\"approved\",\"note\":\"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 03:37:29'),
(20, 2, 'create_loan_application', 'loans', 3, NULL, '{\"loan_type\":\"home\",\"principal\":200000,\"term_months\":48}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 03:48:35'),
(21, 1, 'approve_loan', 'loans', 3, '{\"loan_id\":3,\"user_id\":2,\"loan_type\":\"home\",\"principal\":\"200000.00\",\"outstanding\":\"200000.00\",\"term_months\":48,\"apr\":\"0.00\",\"monthly_payment\":\"4166.67\",\"disbursed_account_id\":null,\"disbursed_at\":null,\"status\":\"pending\",\"purpose\":\"\",\"created_at\":\"2025-11-07 09:18:35\",\"updated_at\":\"2025-11-07 09:18:35\"}', '{\"status\":\"active_disbursed\",\"note\":\"\",\"transaction_id\":\"52\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 04:17:30'),
(22, 2, 'loan_payment', 'loan_payments', NULL, NULL, '{\"loan_id\":3,\"amount\":200000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 05:28:34'),
(23, 2, 'create_loan_application', 'loans', 4, NULL, '{\"loan_type\":\"home\",\"principal\":450000,\"term_months\":12}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 05:29:00'),
(24, 1, 'approve_loan', 'loans', 4, '{\"loan_id\":4,\"user_id\":2,\"loan_type\":\"home\",\"principal\":\"450000.00\",\"outstanding\":\"450000.00\",\"term_months\":12,\"apr\":\"0.00\",\"monthly_payment\":\"37500.00\",\"disbursed_account_id\":null,\"disbursed_at\":null,\"status\":\"pending\",\"purpose\":\"\",\"created_at\":\"2025-11-07 10:59:00\",\"updated_at\":\"2025-11-07 10:59:00\"}', '{\"status\":\"active_disbursed\",\"note\":\"\",\"transaction_id\":\"54\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 05:29:13'),
(25, 2, 'loan_payment', 'loan_payments', NULL, NULL, '{\"loan_id\":4,\"amount\":15000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 05:34:41'),
(26, 2, 'loan_payment', 'loan_payments', NULL, NULL, '{\"loan_id\":4,\"amount\":35000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 05:41:15'),
(27, 2, 'LOGIN_SUCCESS', 'users', 2, NULL, '{\"account_number\":\"1070353153\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 07:02:08');

-- --------------------------------------------------------

--
-- Table structure for table `beneficiaries`
--

CREATE TABLE `beneficiaries` (
  `beneficiary_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `beneficiary_name` varchar(255) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `routing_number` varchar(20) DEFAULT NULL,
  `account_type` enum('checking','savings') DEFAULT 'checking',
  `email` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive','deleted') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `beneficiaries`
--

INSERT INTO `beneficiaries` (`beneficiary_id`, `user_id`, `beneficiary_name`, `account_number`, `bank_name`, `routing_number`, `account_type`, `email`, `phone_number`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Madhav Nair', '9876543210', 'HDFC Bank', 'HDFC0001234', 'checking', 'madhav.nair@example.com', '+91-98765-43211', 'active', '2025-11-05 10:30:00', '2025-11-05 10:30:00'),
(2, 1, 'Pooja Agarwal', '8765432109', 'ICICI Bank', 'ICIC0002345', 'savings', 'pooja.agarwal@example.com', '+91-97654-32100', 'active', '2025-11-05 11:00:00', '2025-11-05 11:00:00'),
(3, 2, 'Vivek Chopra', '7654321098', 'State Bank of India', 'SBIN0003456', 'checking', 'vivek.chopra@example.com', '+91-96543-21099', 'active', '2025-11-06 14:20:00', '2025-11-06 14:20:00'),
(4, 2, 'Priya Sharma', '1234567890', 'Nexo Banking', 'NEXO0001234', 'checking', 'priya.sharma@example.com', '+91-98765-43210', 'active', '2025-11-06 19:50:09', '2025-11-06 19:50:09'),
(5, 2, 'Rahul Khanna', '1234567891', 'Nexo Banking', 'NEXO0001235', 'savings', 'rahul.khanna@example.com', '+91-95432-10988', 'active', '2025-11-06 20:00:23', '2025-11-06 20:00:23'),
(6, 3, 'Meera Jain', '6543210987', 'Axis Bank', 'UTIB0004567', 'savings', 'meera.jain@example.com', '+91-94321-09877', 'active', '2025-11-02 09:15:00', '2025-11-02 09:15:00'),
(7, 5, 'Aditya Rao', '5432109876', 'Punjab National Bank', 'PUNB0005678', 'checking', 'aditya.rao@example.com', '+91-93210-98766', 'active', '2025-10-29 12:30:00', '2025-10-29 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `bill_payments`
--

CREATE TABLE `bill_payments` (
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `biller_name` varchar(100) NOT NULL,
  `bill_type` enum('utilities','credit_card','loan','subscription','mobile_recharge','insurance') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `due_date` date DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('scheduled','processing','completed','failed','cancelled') DEFAULT 'scheduled',
  `reference_number` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bill_payments`
--

INSERT INTO `bill_payments` (`payment_id`, `user_id`, `account_id`, `biller_name`, `bill_type`, `amount`, `due_date`, `payment_date`, `status`, `reference_number`, `created_at`) VALUES
(1, 1, 1, 'Tata Power Mumbai', 'utilities', 3250.00, '2025-11-15', '2025-11-10 10:30:00', 'completed', 'TPOW1762460295', '2025-11-05 20:18:15'),
(2, 1, 1, 'Airtel Postpaid', 'mobile_recharge', 899.00, '2025-11-20', '2025-11-15 14:20:00', 'scheduled', 'AIRT1762560400', '2025-11-07 15:30:00'),
(3, 2, 4, 'Reliance Jio', 'mobile_recharge', 599.00, '2025-11-18', '2025-11-12 09:15:00', 'completed', 'RJIO1762470350', '2025-11-06 10:45:00'),
(4, 2, 4, 'HDFC Credit Card', 'credit_card', 15750.00, '2025-11-25', '2025-11-20 18:30:00', 'scheduled', 'HDFC1762580450', '2025-11-07 12:00:00'),
(5, 3, 6, 'Torrent Power Ahmedabad', 'utilities', 4120.00, '2025-11-12', '2025-11-08 11:00:00', 'completed', 'TRNT1762380200', '2025-11-03 09:30:00'),
(6, 5, 11, 'CESC Kolkata', 'utilities', 5680.00, '2025-11-14', '2025-11-09 16:45:00', 'completed', 'CESC1762430180', '2025-11-04 14:20:00'),
(7, 7, 14, 'LIC Premium', 'insurance', 12500.00, '2025-11-30', '2025-11-25 10:00:00', 'scheduled', 'LICP1762690500', '2025-11-07 08:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `budgets`
--

CREATE TABLE `budgets` (
  `budget_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `month_year` varchar(7) NOT NULL,
  `total_budget` decimal(15,2) NOT NULL,
  `spent_amount` decimal(15,2) DEFAULT 0.00,
  `status` enum('active','completed','exceeded') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `budget_categories`
--

CREATE TABLE `budget_categories` (
  `category_id` int(11) NOT NULL,
  `budget_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `allocated_amount` decimal(15,2) NOT NULL,
  `spent_amount` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `contact_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `contact_name` varchar(100) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `relationship` varchar(50) DEFAULT NULL,
  `avatar_initials` varchar(5) DEFAULT NULL,
  `is_favorite` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`contact_id`, `user_id`, `contact_name`, `phone_number`, `email`, `relationship`, `avatar_initials`, `is_favorite`, `created_at`, `updated_at`) VALUES
(1, 1, 'Arjun Verma', '+91-98765-12345', 'arjun.verma@example.com', 'Friend', 'AV', 1, '2025-11-06 11:59:08', '2025-11-06 11:59:08'),
(2, 1, 'Kavita Desai', '+91-97654-32198', 'kavita.desai@example.com', 'Colleague', 'KD', 0, '2025-11-06 11:59:08', '2025-11-06 11:59:08'),
(3, 1, 'Rohit Malhotra', '+91-96543-21087', 'rohit.malhotra@example.com', 'Family', 'RM', 1, '2025-11-06 11:59:08', '2025-11-06 11:59:08'),
(4, 2, 'Neha Kapoor', '+91-95432-10987', 'neha.kapoor@example.com', 'Friend', 'NK', 1, '2025-11-06 12:00:00', '2025-11-06 12:00:00'),
(5, 2, 'Sanjay Gupta', '+91-94321-09876', 'sanjay.gupta@example.com', 'Business Partner', 'SG', 1, '2025-11-06 12:00:00', '2025-11-06 12:00:00'),
(6, 3, 'Divya Iyer', '+91-93210-98765', 'divya.iyer@example.com', 'Family', 'DI', 0, '2025-11-01 10:30:00', '2025-11-01 10:30:00'),
(7, 3, 'Karan Bhatia', '+91-92109-87654', 'karan.bhatia@example.com', 'Friend', 'KB', 1, '2025-11-01 10:30:00', '2025-11-01 10:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `credit_scores`
--

CREATE TABLE `credit_scores` (
  `score_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score_value` int(11) NOT NULL,
  `score_range` varchar(20) DEFAULT NULL,
  `factors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`factors`)),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `loan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `loan_type` varchar(50) NOT NULL,
  `principal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `outstanding` decimal(15,2) NOT NULL DEFAULT 0.00,
  `term_months` int(11) NOT NULL DEFAULT 0,
  `apr` decimal(5,2) DEFAULT 0.00,
  `monthly_payment` decimal(15,2) DEFAULT NULL,
  `disbursed_account_id` int(11) DEFAULT NULL,
  `disbursed_at` timestamp NULL DEFAULT NULL,
  `status` varchar(30) DEFAULT 'pending',
  `purpose` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loans`
--

INSERT INTO `loans` (`loan_id`, `user_id`, `loan_type`, `principal`, `outstanding`, `term_months`, `apr`, `monthly_payment`, `disbursed_account_id`, `disbursed_at`, `status`, `purpose`, `created_at`, `updated_at`) VALUES
(1, 1, 'home', 3500000.00, 3200000.00, 240, 8.50, 30456.78, 1, '2025-10-15 10:00:00', 'active', 'Purchase of 2BHK apartment in Andheri', '2025-10-10 09:30:00', '2025-11-07 10:00:00'),
(2, 2, 'auto', 850000.00, 650000.00, 60, 9.25, 17890.50, 4, '2025-10-20 14:30:00', 'active', 'Purchase of Maruti Suzuki Swift', '2025-10-18 11:00:00', '2025-11-07 15:30:00'),
(3, 2, 'business', 1500000.00, 1500000.00, 84, 11.00, 22450.00, NULL, NULL, 'approved', 'Expansion of retail electronics business', '2025-11-05 10:00:00', '2025-11-06 14:30:00'),
(4, 3, 'home', 5000000.00, 4200000.00, 300, 8.25, 38725.60, 6, '2025-09-01 12:00:00', 'active', 'Purchase of 3BHK villa in Satellite', '2025-08-25 09:00:00', '2025-11-07 11:00:00'),
(5, 3, 'personal', 500000.00, 0.00, 36, 12.50, 16789.45, 6, '2025-07-10 10:30:00', 'paid', 'Daughter wedding expenses', '2025-07-05 08:00:00', '2025-10-15 16:00:00'),
(6, 5, 'business', 2500000.00, 2100000.00, 120, 10.75, 34560.25, 11, '2025-06-15 11:00:00', 'active', 'Purchase of commercial property in Kolkata', '2025-06-10 10:00:00', '2025-11-07 14:00:00'),
(7, 7, 'auto', 1200000.00, 850000.00, 60, 9.00, 24850.00, 14, '2025-08-20 15:00:00', 'active', 'Purchase of Honda City', '2025-08-15 12:30:00', '2025-11-07 09:00:00'),
(8, 4, 'personal', 300000.00, 180000.00, 24, 13.00, 14256.80, 9, '2025-09-10 13:00:00', 'active', 'Home renovation', '2025-09-05 11:00:00', '2025-11-05 17:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `loan_payments`
--

CREATE TABLE `loan_payments` (
  `payment_id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_payments`
--

INSERT INTO `loan_payments` (`payment_id`, `loan_id`, `user_id`, `account_id`, `transaction_id`, `amount`, `created_at`) VALUES
(1, 3, 2, 4, 53, 200000.00, '2025-11-07 05:28:34'),
(2, 4, 2, 4, 55, 15000.00, '2025-11-07 05:34:41'),
(3, 4, 2, 4, 56, 35000.00, '2025-11-07 05:41:15');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text DEFAULT NULL,
  `type` enum('transaction_alert','low_balance','payment_due','goal_achieved','security_alert','system_maintenance') NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`, `read_at`) VALUES
(8, 1, 'Salary deposited', 'Your salary of $3,500.00 has been deposited', '', 0, '2025-11-06 18:38:23', NULL),
(9, 1, 'Payment to Amazon', 'Payment of $89.99 to Amazon was successful', '', 0, '2025-11-06 18:38:23', NULL),
(10, 1, 'Password changed', 'Your password was recently changed', '', 1, '2025-11-06 18:38:23', NULL),
(11, 2, 'Salary deposited', 'Your salary of $3,500.00 has been deposited', '', 0, '2025-11-06 16:45:11', NULL),
(12, 2, 'Payment to Amazon', 'Payment of $89.99 to Amazon was successful', '', 0, '2025-11-06 14:45:11', NULL),
(13, 2, 'Password changed', 'Your password was recently changed', '', 1, '2025-11-05 18:45:11', NULL),
(14, 2, 'Welcome to Nexo Banking!', 'Your account has been successfully activated. Start exploring our features.', 'system_maintenance', 0, '2025-11-06 18:47:08', NULL),
(15, 2, 'Welcome to Nexo Banking!', 'Your account has been successfully activated. Start exploring our features.', 'system_maintenance', 0, '2025-11-06 19:04:08', NULL),
(16, 2, 'Transfer Initiated', 'Transfer of $50 to Test Recipient has been initiated', '', 0, '2025-11-06 19:30:34', NULL),
(17, 2, 'Transfer Initiated', 'Transfer of $1000 to Bhadani Tushal has been completed', '', 0, '2025-11-06 19:33:01', NULL),
(18, 2, 'Transfer Initiated', 'Transfer of $1000 to Bhadani Tushal has been completed', '', 0, '2025-11-06 19:36:21', NULL),
(19, 2, 'Transfer Initiated', 'Transfer of $50 to Test Recipient has been initiated', '', 0, '2025-11-06 19:40:07', NULL),
(20, 2, 'Transfer Initiated', 'Transfer of $50 to Test Recipient has been initiated', '', 0, '2025-11-06 19:40:15', NULL),
(21, 2, 'Transfer Initiated', 'Transfer of $1000 to john_doe has been completed', '', 0, '2025-11-06 19:50:09', NULL),
(22, 2, 'Transfer Initiated', 'Transfer of $1000 to john_doe has been completed', '', 0, '2025-11-06 19:58:11', NULL),
(23, 2, 'Transfer Initiated', 'Transfer of $2500 to john_doe has been completed', '', 0, '2025-11-06 19:58:39', NULL),
(24, 2, 'Transfer Initiated', 'Transfer of $1000 to john_doe has been completed', '', 0, '2025-11-06 19:59:06', NULL),
(25, 2, 'Transfer Initiated', 'Transfer of $1500 to john_doe has been completed', '', 0, '2025-11-06 19:59:32', NULL),
(26, 1, 'Money Received', 'You received $1000 from Ravi Sardhara', '', 0, '2025-11-06 20:00:23', NULL),
(27, 2, 'Transfer Initiated', 'Transfer of $1000 to John Doe has been completed', '', 0, '2025-11-06 20:00:23', NULL),
(28, 2, 'Welcome to Nexo Banking!', 'Your account has been successfully activated. Start exploring our features.', 'system_maintenance', 0, '2025-11-06 20:19:10', NULL),
(29, 2, 'Welcome to Nexo Banking!', 'Your account has been successfully activated. Start exploring our features.', 'system_maintenance', 0, '2025-11-06 20:19:26', NULL),
(30, 2, 'Welcome to Nexo Banking!', 'Your account has been successfully activated. Start exploring our features.', 'system_maintenance', 0, '2025-11-06 20:20:37', NULL),
(31, 2, 'Welcome to Nexo Banking!', 'Your account has been successfully activated. Start exploring our features.', 'system_maintenance', 0, '2025-11-06 20:21:46', NULL),
(32, 2, 'Welcome to Nexo Banking!', 'Your account has been successfully activated. Start exploring our features.', 'system_maintenance', 0, '2025-11-07 03:06:49', NULL),
(33, 2, 'Transfer Initiated', 'Transfer of $1000 to John Doe has been completed', '', 0, '2025-11-07 03:07:30', NULL),
(34, 2, 'Loan application approved', 'Your loan application #2 has been approved.', 'system_maintenance', 0, '2025-11-07 03:37:25', NULL),
(35, 2, 'Loan application approved', 'Your loan application #1 has been approved.', 'system_maintenance', 0, '2025-11-07 03:37:29', NULL),
(36, 2, 'Loan disbursed', 'Your loan #3 has been disbursed to your account ending 4', 'system_maintenance', 0, '2025-11-07 04:17:30', NULL),
(37, 2, 'Loan payment received', 'We received your payment of $200,000.00 for loan #3', '', 0, '2025-11-07 05:28:34', NULL),
(38, 2, 'Loan disbursed', 'Your loan #4 has been disbursed to your account ending 4', 'system_maintenance', 0, '2025-11-07 05:29:13', NULL),
(39, 2, 'Loan payment received', 'We received your payment of $15,000.00 for loan #4', '', 0, '2025-11-07 05:34:41', NULL),
(40, 2, 'Loan payment received', 'We received your payment of $35,000.00 for loan #4', '', 0, '2025-11-07 05:41:15', NULL),
(41, 2, 'Welcome to Nexo Banking!', 'Your account has been successfully activated. Start exploring our features.', 'system_maintenance', 0, '2025-11-07 07:02:08', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `savings_goals`
--

CREATE TABLE `savings_goals` (
  `goal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `goal_name` varchar(100) NOT NULL,
  `target_amount` decimal(15,2) NOT NULL,
  `current_amount` decimal(15,2) DEFAULT 0.00,
  `target_date` date DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `status` enum('active','completed','paused','cancelled') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `savings_goals`
--

INSERT INTO `savings_goals` (`goal_id`, `user_id`, `goal_name`, `target_amount`, `current_amount`, `target_date`, `category`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Goa Trip 2026', 150000.00, 85000.00, '2026-03-15', 'travel', 'active', '2025-10-01 10:00:00', '2025-11-07 12:00:00'),
(2, 1, 'Emergency Fund', 500000.00, 285000.00, '2026-12-31', 'emergency', 'active', '2025-09-15 09:00:00', '2025-11-07 12:00:00'),
(3, 1, 'New Car Purchase', 1200000.00, 450000.00, '2027-06-30', 'vehicle', 'active', '2025-08-20 11:30:00', '2025-11-07 12:00:00'),
(4, 2, 'Daughter Education', 800000.00, 320000.00, '2028-04-01', 'education', 'active', '2025-10-10 10:00:00', '2025-11-07 14:00:00'),
(5, 2, 'Dubai Vacation', 250000.00, 180000.00, '2026-01-10', 'travel', 'active', '2025-09-05 11:00:00', '2025-11-07 14:00:00'),
(6, 3, 'Home Renovation', 1500000.00, 650000.00, '2026-08-15', 'home', 'active', '2025-07-15 09:30:00', '2025-11-07 10:30:00'),
(7, 3, 'Emergency Fund', 1000000.00, 1000000.00, '2025-11-30', 'emergency', 'completed', '2025-01-01 08:00:00', '2025-10-20 16:00:00'),
(8, 4, 'Wedding Savings', 600000.00, 245000.00, '2026-11-30', 'event', 'active', '2025-10-01 12:00:00', '2025-11-05 15:00:00'),
(9, 5, 'Retirement Fund', 2500000.00, 820000.00, '2030-12-31', 'retirement', 'active', '2025-06-01 10:00:00', '2025-11-07 11:00:00'),
(10, 5, 'Singapore Trip', 300000.00, 165000.00, '2026-07-20', 'travel', 'active', '2025-09-20 11:00:00', '2025-11-07 11:00:00'),
(11, 7, 'Son Higher Education', 1800000.00, 520000.00, '2027-06-01', 'education', 'active', '2025-08-10 09:00:00', '2025-11-06 10:00:00'),
(12, 7, 'Gold Investment', 500000.00, 350000.00, '2026-02-15', 'investment', 'active', '2025-10-05 10:30:00', '2025-11-06 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`session_id`, `user_id`, `session_token`, `ip_address`, `user_agent`, `created_at`, `expires_at`, `last_activity`) VALUES
(1, 2, 'dc1ae39e56e4b8037afb00176d8f6165d14b72c51306345d552bb4c218da50bf', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 18:22:44', '2025-11-07 13:52:44', '2025-11-06 18:22:44'),
(2, 2, 'c404e6e2147d4c0e0fccfc44979c6dbcb227e25702b48253c7d9fdc0ac74df6d', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 18:23:02', '2025-11-07 13:53:02', '2025-11-06 18:23:02'),
(3, 2, 'a239a9c1b831ae9d57b1faa53c48820af001d9fe800382b1ae33584de0ee9fc1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 18:24:44', '2025-11-07 13:54:44', '2025-11-06 18:47:01'),
(4, 2, '5f6e75e9e23a8f49bd6ab9cba2e568e833ea27b7f08c85ffabe13a3ae9018bc9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 18:47:08', '2025-11-07 14:17:08', '2025-11-06 18:59:21'),
(5, 2, 'e25d95926b7de4702322f80cf6c4a43cdfd9b393c0070e57561c3f331f5ca842', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 19:04:08', '2025-11-07 14:34:08', '2025-11-06 20:11:35'),
(6, 2, '6e46765e4af56c9fa701f80d517d7215ceb57720ac46078174581b2e7b06de75', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 20:19:10', '2025-11-07 15:49:10', '2025-11-06 20:19:10'),
(7, 2, '837bb16600fecc539bd482229ebd32361939f4b5d83c2866b8a5138a25cb6a64', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 20:19:26', '2025-11-07 15:49:26', '2025-11-06 20:19:26'),
(8, 2, '9cec110132bf5e8feb1cbcd3f533434477ca5f8eb9ee2b8e312843d7a856312f', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 20:20:37', '2025-11-07 15:50:37', '2025-11-06 20:20:37'),
(9, 2, 'cf7e9d9aa27257188cc301c8d5ff4e7a0e7d9183d5261a9323f444e8cbc13e80', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 20:21:46', '2025-11-07 15:51:46', '2025-11-06 20:21:46'),
(10, 2, 'ec85a5cc919fc9f1e25ee03cd64bdbf8d1b157624ccf1d8db70f707c8584d267', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 03:06:49', '2025-11-07 22:36:49', '2025-11-07 05:41:47'),
(11, 2, '89268a75a19b726fbbc076e86e7f8130e6ecbd4db7890236932fa7cfdf6bada0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 07:02:08', '2025-11-08 02:32:08', '2025-11-07 07:47:44');

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('open','pending','closed') NOT NULL DEFAULT 'open',
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'transfer_fee_internal', '0.00', 'Fee for internal transfers between user accounts', 1, '2025-11-06 11:59:08', '2025-11-06 11:59:08'),
(2, 'transfer_fee_external', '5.00', 'Fee for external transfers to other users (in INR)', 1, '2025-11-06 11:59:08', '2025-11-06 11:59:08'),
(3, 'min_transfer_amount', '10.00', 'Minimum transfer amount (in INR)', 1, '2025-11-06 11:59:08', '2025-11-06 11:59:08'),
(4, 'max_transfer_amount', '500000.00', 'Maximum transfer amount per transaction (in INR)', 1, '2025-11-06 11:59:08', '2025-11-06 11:59:08'),
(5, 'daily_transfer_limit', '1000000.00', 'Daily transfer limit per user (in INR)', 1, '2025-11-06 11:59:08', '2025-11-06 11:59:08'),
(6, 'low_balance_threshold', '5000.00', 'Threshold for low balance notifications (in INR)', 1, '2025-11-06 11:59:08', '2025-11-06 11:59:08'),
(7, 'currency_default', 'INR', 'Default currency for new accounts', 1, '2025-11-06 11:59:08', '2025-11-07 10:00:00'),
(8, 'interest_rate_savings', '3.50', 'Default interest rate for savings accounts (%)', 1, '2025-11-06 11:59:08', '2025-11-06 11:59:08'),
(9, 'credit_score_update_interval', '30', 'Days between credit score updates', 1, '2025-11-06 11:59:08', '2025-11-06 11:59:08'),
(10, 'gst_percentage', '18.00', 'GST percentage applicable on services', 1, '2025-11-06 11:59:08', '2025-11-06 11:59:08'),
(11, 'upi_enabled', 'true', 'Enable UPI payment integration', 1, '2025-11-06 11:59:08', '2025-11-06 11:59:08'),
(12, 'neft_rtgs_enabled', 'true', 'Enable NEFT/RTGS transfers', 1, '2025-11-06 11:59:08', '2025-11-06 11:59:08');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_replies`
--

CREATE TABLE `ticket_replies` (
  `reply_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `transaction_type` enum('deposit','withdrawal','transfer','payment','refund','fee','interest') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `recipient_info` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `reference_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','completed','failed','cancelled') DEFAULT 'pending',
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `recipient_name` varchar(255) DEFAULT NULL,
  `recipient_account` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `account_id`, `transaction_type`, `amount`, `description`, `recipient_info`, `category`, `reference_id`, `status`, `transaction_date`, `created_at`, `updated_at`, `recipient_name`, `recipient_account`) VALUES
(1, 1, 'deposit', 85000.00, 'Salary Credit - TCS Ltd', NULL, 'salary', 'SAL202311001', 'completed', '2025-11-01 10:30:00', '2025-11-01 10:30:00', '2025-11-01 10:30:00', NULL, NULL),
(2, 1, 'payment', 3250.00, 'Tata Power - Electricity Bill', NULL, 'utilities', 'TPOW1762460295', 'completed', '2025-11-02 14:20:00', '2025-11-02 14:20:00', '2025-11-02 14:20:00', NULL, NULL),
(3, 1, 'payment', 2450.00, 'Amazon India - Electronics', NULL, 'shopping', 'AMZN2025110301', 'completed', '2025-11-03 16:45:00', '2025-11-03 16:45:00', '2025-11-03 16:45:00', NULL, NULL),
(4, 1, 'payment', 1850.00, 'Indian Oil Petrol Pump', NULL, 'transport', 'IOCL2025110401', 'completed', '2025-11-04 11:30:00', '2025-11-04 11:30:00', '2025-11-04 11:30:00', NULL, NULL),
(5, 1, 'payment', 650.00, 'Cafe Coffee Day - Andheri', NULL, 'food', 'CCD2025110501', 'completed', '2025-11-05 09:15:00', '2025-11-05 09:15:00', '2025-11-05 09:15:00', NULL, NULL),
(6, 1, 'transfer', 15000.00, 'Transfer to Arjun Verma', NULL, 'transfer', 'TRF2025110601', 'completed', '2025-11-06 13:20:00', '2025-11-06 13:20:00', '2025-11-06 13:20:00', 'Arjun Verma', '9876543210'),
(7, 1, 'payment', 799.00, 'Netflix Premium Subscription', NULL, 'entertainment', 'NFLX2025110701', 'completed', '2025-11-07 10:00:00', '2025-11-07 10:00:00', '2025-11-07 10:00:00', NULL, NULL),
(8, 1, 'payment', 4850.00, 'Big Bazaar - Monthly Groceries', NULL, 'groceries', 'BBAZ2025110501', 'completed', '2025-11-05 18:30:00', '2025-11-05 18:30:00', '2025-11-05 18:30:00', NULL, NULL),
(9, 1, 'payment', 30456.78, 'Home Loan EMI - November', NULL, 'loan_payment', 'HLOAN001NOV', 'completed', '2025-11-05 08:00:00', '2025-11-05 08:00:00', '2025-11-05 08:00:00', NULL, NULL),
(10, 2, 'deposit', 2500.00, 'Interest Credit', NULL, 'interest', 'INT2025110101', 'completed', '2025-11-01 00:01:00', '2025-11-01 00:01:00', '2025-11-01 00:01:00', NULL, NULL),
(11, 4, 'deposit', 95000.00, 'Salary Credit - Infosys Ltd', NULL, 'salary', 'SAL202311002', 'completed', '2025-11-01 11:00:00', '2025-11-01 11:00:00', '2025-11-01 11:00:00', NULL, NULL),
(12, 4, 'payment', 3890.00, 'Flipkart - Clothing Purchase', NULL, 'shopping', 'FLKT2025110201', 'completed', '2025-11-02 15:30:00', '2025-11-02 15:30:00', '2025-11-02 15:30:00', NULL, NULL),
(13, 4, 'payment', 2150.00, 'HP Petrol Pump - Noida', NULL, 'transport', 'HPET2025110301', 'completed', '2025-11-03 12:15:00', '2025-11-03 12:15:00', '2025-11-03 12:15:00', NULL, NULL),
(14, 4, 'payment', 1250.00, 'Dominos Pizza - Sector 15', NULL, 'food', 'DOMZ2025110401', 'completed', '2025-11-04 20:30:00', '2025-11-04 20:30:00', '2025-11-04 20:30:00', NULL, NULL),
(15, 4, 'transfer', 25000.00, 'Transfer to Neha Kapoor', NULL, 'transfer', 'TRF2025110501', 'completed', '2025-11-05 14:45:00', '2025-11-05 14:45:00', '2025-11-05 14:45:00', 'Neha Kapoor', '9543210987'),
(16, 4, 'payment', 599.00, 'Jio Recharge - Postpaid', NULL, 'mobile_recharge', 'RJIO1762470350', 'completed', '2025-11-06 10:20:00', '2025-11-06 10:20:00', '2025-11-06 10:20:00', NULL, NULL),
(17, 4, 'payment', 35000.00, 'Monthly Rent - Flat Owner', NULL, 'utilities', 'RENT202511', 'completed', '2025-11-01 09:00:00', '2025-11-01 09:00:00', '2025-11-01 09:00:00', NULL, NULL),
(18, 4, 'payment', 1499.00, 'ACT Fibernet - Monthly Bill', NULL, 'utilities', 'ACTF2025110101', 'completed', '2025-11-01 08:30:00', '2025-11-01 08:30:00', '2025-11-01 08:30:00', NULL, NULL),
(19, 4, 'payment', 1850.00, 'Barbeque Nation - Dinner', NULL, 'food', 'BBQN2025110601', 'completed', '2025-11-06 21:00:00', '2025-11-06 21:00:00', '2025-11-06 21:00:00', NULL, NULL),
(20, 4, 'payment', 8500.00, 'Westside - Apparel Shopping', NULL, 'shopping', 'WSTD2025110701', 'completed', '2025-11-07 17:30:00', '2025-11-07 17:30:00', '2025-11-07 17:30:00', NULL, NULL),
(21, 4, 'payment', 17890.50, 'Auto Loan EMI - November', NULL, 'loan_payment', 'ALOAN002NOV', 'completed', '2025-11-05 08:00:00', '2025-11-05 08:00:00', '2025-11-05 08:00:00', NULL, NULL),
(22, 5, 'deposit', 3500.00, 'Interest Credit - Savings', NULL, 'interest', 'INT2025110102', 'completed', '2025-11-01 00:01:00', '2025-11-01 00:01:00', '2025-11-01 00:01:00', NULL, NULL),
(23, 6, 'deposit', 185000.00, 'Salary Credit - Adani Group', NULL, 'salary', 'SAL202311003', 'completed', '2025-11-01 10:00:00', '2025-11-01 10:00:00', '2025-11-01 10:00:00', NULL, NULL),
(24, 6, 'payment', 4120.00, 'Torrent Power - Electricity', NULL, 'utilities', 'TRNT1762380200', 'completed', '2025-11-03 11:00:00', '2025-11-03 11:00:00', '2025-11-03 11:00:00', NULL, NULL),
(25, 6, 'payment', 5680.00, 'Reliance Digital - TV Purchase', NULL, 'shopping', 'RDIG2025110401', 'completed', '2025-11-04 16:20:00', '2025-11-04 16:20:00', '2025-11-04 16:20:00', NULL, NULL),
(26, 6, 'transfer', 50000.00, 'Transfer to Divya Iyer', NULL, 'transfer', 'TRF2025110601', 'completed', '2025-11-06 12:30:00', '2025-11-06 12:30:00', '2025-11-06 12:30:00', 'Divya Iyer', '9321098765'),
(27, 6, 'payment', 38725.60, 'Home Loan EMI - November', NULL, 'loan_payment', 'HLOAN004NOV', 'completed', '2025-11-05 08:00:00', '2025-11-05 08:00:00', '2025-11-05 08:00:00', NULL, NULL),
(28, 9, 'deposit', 65000.00, 'Salary Credit - Wipro Ltd', NULL, 'salary', 'SAL202311004', 'completed', '2025-11-01 11:30:00', '2025-11-01 11:30:00', '2025-11-01 11:30:00', NULL, NULL),
(29, 9, 'payment', 2850.00, 'More Megastore - Groceries', NULL, 'groceries', 'MORE2025110201', 'completed', '2025-11-02 19:00:00', '2025-11-02 19:00:00', '2025-11-02 19:00:00', NULL, NULL),
(30, 9, 'payment', 1450.00, 'Uber - Monthly Rides', NULL, 'transport', 'UBER2025110401', 'completed', '2025-11-04 18:00:00', '2025-11-04 18:00:00', '2025-11-04 18:00:00', NULL, NULL),
(31, 9, 'payment', 14256.80, 'Personal Loan EMI - November', NULL, 'loan_payment', 'PLOAN008NOV', 'completed', '2025-11-05 08:00:00', '2025-11-05 08:00:00', '2025-11-05 08:00:00', NULL, NULL),
(32, 11, 'deposit', 142000.00, 'Salary Credit - Business Income', NULL, 'salary', 'SAL202311005', 'completed', '2025-11-01 09:30:00', '2025-11-01 09:30:00', '2025-11-01 09:30:00', NULL, NULL),
(33, 11, 'payment', 5680.00, 'CESC - Electricity Bill', NULL, 'utilities', 'CESC1762430180', 'completed', '2025-11-04 14:20:00', '2025-11-04 14:20:00', '2025-11-04 14:20:00', NULL, NULL),
(34, 11, 'transfer', 75000.00, 'Transfer to Aditya Rao', NULL, 'transfer', 'TRF2025110501', 'completed', '2025-11-05 15:30:00', '2025-11-05 15:30:00', '2025-11-05 15:30:00', 'Aditya Rao', '5432109876'),
(35, 11, 'payment', 34560.25, 'Business Loan EMI - November', NULL, 'loan_payment', 'BLOAN006NOV', 'completed', '2025-11-05 08:00:00', '2025-11-05 08:00:00', '2025-11-05 08:00:00', NULL, NULL),
(36, 12, 'deposit', 7500.00, 'Interest Credit - Savings', NULL, 'interest', 'INT2025110103', 'completed', '2025-11-01 00:01:00', '2025-11-01 00:01:00', '2025-11-01 00:01:00', NULL, NULL),
(37, 14, 'deposit', 225000.00, 'Salary Credit - Oracle India', NULL, 'salary', 'SAL202311006', 'completed', '2025-11-01 10:45:00', '2025-11-01 10:45:00', '2025-11-01 10:45:00', NULL, NULL),
(38, 14, 'payment', 12500.00, 'LIC Premium Payment', NULL, 'insurance', 'LICP1762690500', 'completed', '2025-11-03 10:00:00', '2025-11-03 10:00:00', '2025-11-03 10:00:00', NULL, NULL),
(39, 14, 'payment', 8950.00, 'DMart - Monthly Shopping', NULL, 'shopping', 'DMAT2025110501', 'completed', '2025-11-05 19:30:00', '2025-11-05 19:30:00', '2025-11-05 19:30:00', NULL, NULL),
(40, 14, 'payment', 24850.00, 'Auto Loan EMI - November', NULL, 'loan_payment', 'ALOAN007NOV', 'completed', '2025-11-05 08:00:00', '2025-11-05 08:00:00', '2025-11-05 08:00:00', NULL, NULL),
(41, 15, 'deposit', 12000.00, 'Interest Credit - Savings', NULL, 'interest', 'INT2025110104', 'completed', '2025-11-01 00:01:00', '2025-11-01 00:01:00', '2025-11-01 00:01:00', NULL, NULL),
(42, 1, 'payment', 899.00, 'Swiggy - Food Delivery', NULL, 'food', 'SWGY2025110701', 'completed', '2025-11-07 21:15:00', '2025-11-07 21:15:00', '2025-11-07 21:15:00', NULL, NULL),
(43, 4, 'payment', 1599.00, 'BookMyShow - Movie Tickets', NULL, 'entertainment', 'BMS2025110701', 'completed', '2025-11-07 19:00:00', '2025-11-07 19:00:00', '2025-11-07 19:00:00', NULL, NULL),
(44, 6, 'refund', 2340.00, 'Flipkart Order Refund', NULL, 'refund', 'RFLKT2025110601', 'completed', '2025-11-06 14:30:00', '2025-11-06 14:30:00', '2025-11-06 14:30:00', NULL, NULL),
(45, 1, 'transfer', 10000.00, 'Transfer to Kavita Desai', NULL, 'transfer', 'TRF2025110801', 'completed', '2025-11-07 16:20:00', '2025-11-07 16:20:00', '2025-11-07 16:20:00', 'Kavita Desai', '7654321098');

--
-- Triggers `transactions`
--
DELIMITER $$
CREATE TRIGGER `update_account_balance_after_transaction` AFTER INSERT ON `transactions` FOR EACH ROW BEGIN
    IF NEW.status = 'completed' THEN
        IF NEW.transaction_type IN ('deposit', 'refund', 'interest') THEN
            UPDATE accounts 
            SET balance = balance + NEW.amount,
                last_activity = NEW.transaction_date
            WHERE account_id = NEW.account_id;
        ELSEIF NEW.transaction_type IN ('withdrawal', 'payment', 'fee') THEN
            UPDATE accounts 
            SET balance = balance - NEW.amount,
                last_activity = NEW.transaction_date
            WHERE account_id = NEW.account_id;
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_budget_spent_amount` AFTER INSERT ON `transactions` FOR EACH ROW BEGIN
    DECLARE budget_exists INT DEFAULT 0;
    DECLARE current_month_year VARCHAR(7);
    
    IF NEW.status = 'completed' AND NEW.transaction_type IN ('withdrawal', 'payment') THEN
        SET current_month_year = DATE_FORMAT(NEW.transaction_date, '%Y-%m');
        
        SELECT COUNT(*) INTO budget_exists
        FROM budgets b 
        JOIN accounts a ON a.user_id = b.user_id
        WHERE a.account_id = NEW.account_id 
        AND b.month_year = current_month_year;
        
        IF budget_exists > 0 THEN
            UPDATE budgets b
            JOIN accounts a ON a.user_id = b.user_id
            SET b.spent_amount = b.spent_amount + NEW.amount
            WHERE a.account_id = NEW.account_id 
            AND b.month_year = current_month_year;
            
            -- Update category spending if category matches
            IF NEW.category IS NOT NULL THEN
                UPDATE budget_categories bc
                JOIN budgets b ON b.budget_id = bc.budget_id
                JOIN accounts a ON a.user_id = b.user_id
                SET bc.spent_amount = bc.spent_amount + NEW.amount
                WHERE a.account_id = NEW.account_id 
                AND b.month_year = current_month_year
                AND bc.category_name = NEW.category;
            END IF;
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `transfers`
--

CREATE TABLE `transfers` (
  `transfer_id` int(11) NOT NULL,
  `from_account_id` int(11) NOT NULL,
  `to_account_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `transfer_type` enum('internal','external','wire','ach') NOT NULL,
  `recipient_name` varchar(100) DEFAULT NULL,
  `recipient_phone` varchar(20) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `fee_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','processing','completed','failed','cancelled') DEFAULT 'pending',
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `member_type` enum('basic','premium','platinum') DEFAULT 'basic',
  `status` enum('pending','active','inactive','suspended','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `phone_number`, `date_of_birth`, `address`, `profile_image`, `member_type`, `status`, `approved_by`, `approved_at`, `rejection_reason`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'priya_sharma', 'priya.sharma@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Priya', 'Sharma', '+91-98765-43210', '1992-05-15', 'B-204, Maple Apartments, Andheri West, Mumbai, Maharashtra 400058', NULL, 'premium', 'active', NULL, NULL, NULL, '2025-11-06 11:59:08', '2025-11-06 11:59:08', NULL),
(2, 'ravi_kumar', 'ravi.kumar@example.com', '$2y$10$rq3Gh.eh5uYwR5WWbhGlAudkNiXV6Y/jpkGBEKkD11jiB4jcqjTZG', 'Ravi', 'Kumar', '+91-96628-55314', '1995-11-28', 'Flat 601, Sunrise Tower, Sector 15, Noida, Uttar Pradesh 201301', NULL, 'premium', 'active', 1, '2025-11-06 18:22:35', NULL, '2025-11-06 18:12:03', '2025-11-07 07:02:08', '2025-11-07 07:02:08'),
(3, 'amit_patel', 'amit.patel@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Amit', 'Patel', '+91-98234-56789', '1988-03-22', 'C-12, Satellite Road, Ahmedabad, Gujarat 380015', NULL, 'platinum', 'active', 1, '2025-11-01 10:30:00', NULL, '2025-11-01 10:15:00', '2025-11-06 14:20:00', '2025-11-06 14:20:00'),
(4, 'sneha_reddy', 'sneha.reddy@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sneha', 'Reddy', '+91-99876-54321', '1990-07-18', '45, Jubilee Hills, Hyderabad, Telangana 500033', NULL, 'basic', 'active', 1, '2025-11-02 11:00:00', NULL, '2025-11-02 10:45:00', '2025-11-05 16:30:00', '2025-11-05 16:30:00'),
(5, 'rajesh_mehta', 'rajesh.mehta@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rajesh', 'Mehta', '+91-97654-32109', '1985-12-10', '12/A, Park Street, Kolkata, West Bengal 700016', NULL, 'premium', 'active', 1, '2025-10-28 09:00:00', NULL, '2025-10-28 08:45:00', '2025-11-07 10:15:00', '2025-11-07 10:15:00'),
(6, 'ananya_singh', 'ananya.singh@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ananya', 'Singh', '+91-91234-56780', '1993-09-25', 'Plot 89, Banjara Hills, Hyderabad, Telangana 500034', NULL, 'basic', 'pending', NULL, NULL, NULL, '2025-11-07 12:30:00', '2025-11-07 12:30:00', NULL),
(7, 'vikram_joshi', 'vikram.joshi@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Vikram', 'Joshi', '+91-98765-12340', '1987-06-14', 'E-305, Viman Nagar, Pune, Maharashtra 411014', NULL, 'platinum', 'active', 1, '2025-10-25 14:00:00', NULL, '2025-10-25 13:45:00', '2025-11-06 09:45:00', '2025-11-06 09:45:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD UNIQUE KEY `account_number` (`account_number`),
  ADD KEY `idx_user_accounts` (`user_id`),
  ADD KEY `idx_account_number` (`account_number`);

--
-- Indexes for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `idx_session_token` (`session_token`),
  ADD KEY `idx_admin_sessions` (`admin_id`),
  ADD KEY `idx_session_expiry` (`expires_at`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_user_logs` (`user_id`),
  ADD KEY `idx_action_type` (`action_type`),
  ADD KEY `idx_table_logs` (`table_name`,`record_id`);

--
-- Indexes for table `beneficiaries`
--
ALTER TABLE `beneficiaries`
  ADD PRIMARY KEY (`beneficiary_id`),
  ADD KEY `idx_user_beneficiaries` (`user_id`,`status`);

--
-- Indexes for table `bill_payments`
--
ALTER TABLE `bill_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `idx_user_bills` (`user_id`),
  ADD KEY `idx_bill_status` (`status`),
  ADD KEY `idx_due_date` (`due_date`);

--
-- Indexes for table `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`budget_id`),
  ADD UNIQUE KEY `unique_user_month` (`user_id`,`month_year`),
  ADD KEY `idx_user_budgets` (`user_id`);

--
-- Indexes for table `budget_categories`
--
ALTER TABLE `budget_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `idx_budget_categories` (`budget_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`contact_id`),
  ADD KEY `idx_user_contacts` (`user_id`),
  ADD KEY `idx_favorites` (`user_id`,`is_favorite`);

--
-- Indexes for table `credit_scores`
--
ALTER TABLE `credit_scores`
  ADD PRIMARY KEY (`score_id`),
  ADD KEY `idx_user_credit` (`user_id`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`loan_id`),
  ADD KEY `user_id_idx` (`user_id`);

--
-- Indexes for table `loan_payments`
--
ALTER TABLE `loan_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `loan_id_idx` (`loan_id`),
  ADD KEY `user_id_idx` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_user_notifications` (`user_id`),
  ADD KEY `idx_unread_notifications` (`user_id`,`is_read`);

--
-- Indexes for table `savings_goals`
--
ALTER TABLE `savings_goals`
  ADD PRIMARY KEY (`goal_id`),
  ADD KEY `idx_user_goals` (`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `idx_session_token` (`session_token`),
  ADD KEY `idx_user_sessions` (`user_id`),
  ADD KEY `idx_session_expiry` (`expires_at`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_assigned` (`assigned_to`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  ADD PRIMARY KEY (`reply_id`),
  ADD KEY `idx_ticket` (`ticket_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `idx_account_transactions` (`account_id`),
  ADD KEY `idx_transaction_date` (`transaction_date`),
  ADD KEY `idx_transaction_status` (`status`);

--
-- Indexes for table `transfers`
--
ALTER TABLE `transfers`
  ADD PRIMARY KEY (`transfer_id`),
  ADD KEY `idx_from_account` (`from_account_id`),
  ADD KEY `idx_to_account` (`to_account_id`),
  ADD KEY `idx_transfer_status` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `approved_by` (`approved_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `beneficiaries`
--
ALTER TABLE `beneficiaries`
  MODIFY `beneficiary_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `bill_payments`
--
ALTER TABLE `bill_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `budgets`
--
ALTER TABLE `budgets`
  MODIFY `budget_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `budget_categories`
--
ALTER TABLE `budget_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `credit_scores`
--
ALTER TABLE `credit_scores`
  MODIFY `score_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `loan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `loan_payments`
--
ALTER TABLE `loan_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `savings_goals`
--
ALTER TABLE `savings_goals`
  MODIFY `goal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  MODIFY `reply_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `transfers`
--
ALTER TABLE `transfers`
  MODIFY `transfer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  ADD CONSTRAINT `admin_sessions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `beneficiaries`
--
ALTER TABLE `beneficiaries`
  ADD CONSTRAINT `beneficiaries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `bill_payments`
--
ALTER TABLE `bill_payments`
  ADD CONSTRAINT `bill_payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bill_payments_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE;

--
-- Constraints for table `budgets`
--
ALTER TABLE `budgets`
  ADD CONSTRAINT `budgets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `budget_categories`
--
ALTER TABLE `budget_categories`
  ADD CONSTRAINT `budget_categories_ibfk_1` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`budget_id`) ON DELETE CASCADE;

--
-- Constraints for table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `credit_scores`
--
ALTER TABLE `credit_scores`
  ADD CONSTRAINT `credit_scores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `savings_goals`
--
ALTER TABLE `savings_goals`
  ADD CONSTRAINT `savings_goals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE;

--
-- Constraints for table `transfers`
--
ALTER TABLE `transfers`
  ADD CONSTRAINT `transfers_ibfk_1` FOREIGN KEY (`from_account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transfers_ibfk_2` FOREIGN KEY (`to_account_id`) REFERENCES `accounts` (`account_id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
