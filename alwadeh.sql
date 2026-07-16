-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 13, 2026 at 08:47 PM
-- Server version: 8.4.3
-- PHP Version: 8.1.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+03:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `alwadeh`
--

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `registration_name` varchar(255) DEFAULT NULL,
  `commercial_registration_number` varchar(100) DEFAULT NULL,
  `vat_number` varchar(15) DEFAULT NULL,
  `company_type` enum('seller','buyer','both') DEFAULT 'seller',
  `currency_code` varchar(3) DEFAULT 'SAR',
  `country_code` char(2) DEFAULT 'SA',
  `status` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_address`
--

CREATE TABLE `company_address` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `street_name` varchar(255) DEFAULT NULL,
  `building_number` varchar(50) DEFAULT NULL,
  `plot_identification` varchar(50) DEFAULT NULL,
  `city_name` varchar(100) DEFAULT NULL,
  `postal_zone` varchar(20) DEFAULT NULL,
  `country_subentity` varchar(100) DEFAULT NULL,
  `country_code` char(2) DEFAULT 'SA',
  `address_type` enum('main','billing','shipping') DEFAULT 'main',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_bank_account`
--

CREATE TABLE `company_bank_account` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `iban` varchar(50) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `swift_code` varchar(50) DEFAULT NULL,
  `currency_code` char(3) DEFAULT 'SAR',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_certificates`
--

CREATE TABLE `company_certificates` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `certificate_type` enum('production','simulation','compliance') COLLATE utf8mb4_unicode_ci DEFAULT 'production',
  `certificate_file_id` bigint UNSIGNED DEFAULT NULL,
  `certificate_serial` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_contact`
--

CREATE TABLE `company_contact` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `electronic_mail` varchar(255) DEFAULT NULL,
  `note` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_legal_entity`
--

CREATE TABLE `company_legal_entity` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `registration_name` varchar(255) NOT NULL,
  `company_id_value` varchar(100) DEFAULT NULL,
  `company_id_scheme` varchar(50) DEFAULT NULL,
  `registration_address` text,
  `company_status` varchar(50) DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_party`
--

CREATE TABLE `company_party` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `party_identification_id` varchar(100) DEFAULT NULL,
  `party_identification_scheme` varchar(50) DEFAULT NULL,
  `endpoint_id` varchar(100) DEFAULT NULL,
  `endpoint_scheme` varchar(50) DEFAULT NULL,
  `party_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_sequences`
--

CREATE TABLE `company_sequences` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `sequence_name` varchar(100) NOT NULL,
  `current_value` bigint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_storage_settings`
--

CREATE TABLE `company_storage_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `certificates_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT 'Certificates/',
  `storage_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT 'Storage/Companies/',
  `xml_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT 'XML/',
  `signed_xml_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT 'XML/Signed/',
  `qr_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT 'QR/',
  `documents_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT 'Documents/',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_tax_scheme`
--

CREATE TABLE `company_tax_scheme` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `tax_scheme_id` varchar(50) DEFAULT 'VAT',
  `vat_number` varchar(50) NOT NULL,
  `tax_category_id` varchar(10) DEFAULT 'S',
  `tax_percent` decimal(5,2) DEFAULT '15.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_zatca_certificates`
--

CREATE TABLE `company_zatca_certificates` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `certificate_type` enum('compliance','production') NOT NULL,
  `certificate_name` varchar(255) DEFAULT NULL,
  `certificate_serial` varchar(255) DEFAULT NULL,
  `certificate_content` longtext,
  `private_key_content` longtext,
  `csr_content` longtext,
  `secret_key` varchar(255) DEFAULT NULL,
  `environment` enum('sandbox','simulation','production') DEFAULT 'sandbox',
  `status` enum('generated','submitted','approved','expired','revoked') DEFAULT 'generated',
  `valid_from` date DEFAULT NULL,
  `valid_to` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_zatca_credentials`
--

CREATE TABLE `company_zatca_credentials` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `certificate_id` bigint UNSIGNED DEFAULT NULL,
  `request_id` varchar(255) DEFAULT NULL,
  `binary_security_token` text,
  `secret` varchar(255) DEFAULT NULL,
  `access_token` text,
  `environment` enum('sandbox','simulation','production') DEFAULT 'sandbox',
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_zatca_settings`
--

CREATE TABLE `company_zatca_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `environment` enum('simulation','compliance','production') DEFAULT 'simulation',
  `certificate_path` varchar(500) DEFAULT NULL,
  `private_key_path` varchar(500) DEFAULT NULL,
  `certificate_serial` varchar(255) DEFAULT NULL,
  `zatca_client_id` varchar(255) DEFAULT NULL,
  `zatca_secret` varchar(255) DEFAULT NULL,
  `compliance_request_id` varchar(255) DEFAULT NULL,
  `production_csid` varchar(255) DEFAULT NULL,
  `production_secret` varchar(255) DEFAULT NULL,
  `last_invoice_hash` text,
  `last_invoice_uuid` char(36) DEFAULT NULL,
  `last_icv` bigint DEFAULT '0',
  `last_pih` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `customer_code` varchar(50) DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `registration_name` varchar(255) DEFAULT NULL,
  `customer_type` enum('individual','company') DEFAULT 'company',
  `vat_number` varchar(15) DEFAULT NULL,
  `commercial_registration_number` varchar(100) DEFAULT NULL,
  `country_code` char(2) DEFAULT 'SA',
  `currency_code` char(3) DEFAULT 'SAR',
  `payment_terms` varchar(255) DEFAULT NULL,
  `credit_limit` decimal(15,2) DEFAULT '0.00',
  `status` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_address`
--

CREATE TABLE `customer_address` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `address_type` enum('main','billing','shipping') DEFAULT 'main',
  `street_name` varchar(255) DEFAULT NULL,
  `building_number` varchar(50) DEFAULT NULL,
  `plot_identification` varchar(50) DEFAULT NULL,
  `city_name` varchar(100) DEFAULT NULL,
  `postal_zone` varchar(20) DEFAULT NULL,
  `country_subentity` varchar(100) DEFAULT NULL,
  `country_code` char(2) DEFAULT 'SA',
  `additional_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_bank_account`
--

CREATE TABLE `customer_bank_account` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `iban` varchar(50) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `swift_code` varchar(50) DEFAULT NULL,
  `currency_code` char(3) DEFAULT 'SAR',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_contact`
--

CREATE TABLE `customer_contact` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `electronic_mail` varchar(255) DEFAULT NULL,
  `note` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_legal_entity`
--

CREATE TABLE `customer_legal_entity` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `registration_name` varchar(255) NOT NULL,
  `company_id_value` varchar(100) DEFAULT NULL,
  `company_id_scheme` varchar(50) DEFAULT NULL,
  `registration_address` text,
  `company_status` varchar(50) DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_party`
--

CREATE TABLE `customer_party` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `endpoint_id` varchar(100) DEFAULT NULL,
  `endpoint_scheme` varchar(50) DEFAULT NULL,
  `party_identification_id` varchar(100) DEFAULT NULL,
  `party_identification_scheme` varchar(50) DEFAULT NULL,
  `party_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_tax_scheme`
--

CREATE TABLE `customer_tax_scheme` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `tax_scheme_id` varchar(50) DEFAULT 'VAT',
  `vat_number` varchar(50) DEFAULT NULL,
  `tax_category_id` varchar(10) DEFAULT 'S',
  `tax_percent` decimal(5,2) DEFAULT '15.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED DEFAULT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `invoice_uuid` char(36) DEFAULT NULL,
  `invoice_type` enum('invoice','credit_note','debit_note') DEFAULT 'invoice',
  `invoice_kind` enum('standard','simplified') DEFAULT 'simplified',
  `issue_date` datetime NOT NULL,
  `issue_time` datetime NOT NULL,
  `supply_date` date DEFAULT NULL,
  `currency_code` char(3) DEFAULT 'SAR',
  `document_currency_code` char(3) DEFAULT 'SAR',
  `tax_currency_code` char(3) DEFAULT 'SAR',
  `payment_status` enum('unpaid','partial','paid') DEFAULT 'unpaid',
  `invoice_status` enum('draft','signed','reported','cleared','rejected') DEFAULT 'draft',
  `icv` bigint DEFAULT '0',
  `previous_invoice_hash` text,
  `qr_code` text,
  `xml_file_path` varchar(500) DEFAULT NULL,
  `pdf_file_path` varchar(500) DEFAULT NULL,
  `billing_reference` varchar(100) DEFAULT NULL,
  `original_invoice_id` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_allowances`
--

CREATE TABLE `invoice_allowances` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `charge_indicator` tinyint(1) DEFAULT '0',
  `reason_code` varchar(50) DEFAULT NULL,
  `reason` text,
  `amount` decimal(15,2) DEFAULT '0.00',
  `base_amount` decimal(15,2) DEFAULT '0.00',
  `percentage` decimal(5,2) DEFAULT '0.00',
  `tax_category_id` varchar(10) DEFAULT 'S',
  `tax_percent` decimal(5,2) DEFAULT '15.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_attachments`
--

CREATE TABLE `invoice_attachments` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `file_type` enum('xml','pdf','qr','other') NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_customer_party`
--

CREATE TABLE `invoice_customer_party` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED DEFAULT NULL,
  `party_name` varchar(255) NOT NULL,
  `endpoint_id` varchar(100) DEFAULT NULL,
  `endpoint_scheme` varchar(50) DEFAULT NULL,
  `party_identification_id` varchar(100) DEFAULT NULL,
  `party_identification_scheme` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_delivery`
--

CREATE TABLE `invoice_delivery` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `actual_delivery_date` date DEFAULT NULL,
  `delivery_location` varchar(255) DEFAULT NULL,
  `delivery_note` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_documents`
--

CREATE TABLE `invoice_documents` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `storage_file_id` bigint UNSIGNED NOT NULL,
  `document_type` enum('original','signed','submitted','cleared','reported','archive') COLLATE utf8mb4_unicode_ci DEFAULT 'original',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_events`
--

CREATE TABLE `invoice_events` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `event_message` text,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_lines`
--

CREATE TABLE `invoice_lines` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED DEFAULT NULL,
  `line_number` int NOT NULL,
  `item_code` varchar(100) DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_description` text,
  `item_type` enum('product','service') DEFAULT 'product',
  `quantity` decimal(15,3) DEFAULT '1.000',
  `unit_code` varchar(20) DEFAULT 'PCE',
  `unit_name` varchar(100) DEFAULT NULL,
  `unit_price` decimal(15,4) DEFAULT '0.0000',
  `line_extension_amount` decimal(15,2) DEFAULT '0.00',
  `currency_code` char(3) DEFAULT 'SAR',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_line_taxes`
--

CREATE TABLE `invoice_line_taxes` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_line_id` bigint UNSIGNED NOT NULL,
  `taxable_amount` decimal(15,2) DEFAULT '0.00',
  `tax_amount` decimal(15,2) DEFAULT '0.00',
  `tax_category_id` varchar(10) DEFAULT 'S',
  `tax_percent` decimal(5,2) DEFAULT '15.00',
  `tax_scheme_id` varchar(50) DEFAULT 'VAT',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_notes`
--

CREATE TABLE `invoice_notes` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `note` text NOT NULL,
  `language_code` varchar(10) DEFAULT 'ar',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_party_additional_identification`
--

CREATE TABLE `invoice_party_additional_identification` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `party_type` enum('supplier','customer') NOT NULL,
  `party_id` bigint UNSIGNED NOT NULL,
  `identification_id` varchar(100) NOT NULL,
  `identification_scheme` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_party_address`
--

CREATE TABLE `invoice_party_address` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `party_type` enum('supplier','customer') NOT NULL,
  `party_id` bigint UNSIGNED NOT NULL,
  `street_name` varchar(255) DEFAULT NULL,
  `building_number` varchar(50) DEFAULT NULL,
  `plot_identification` varchar(50) DEFAULT NULL,
  `city_name` varchar(100) DEFAULT NULL,
  `postal_zone` varchar(20) DEFAULT NULL,
  `country_subentity` varchar(100) DEFAULT NULL,
  `country_code` char(2) DEFAULT 'SA',
  `additional_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_party_contact`
--

CREATE TABLE `invoice_party_contact` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `party_type` enum('supplier','customer') NOT NULL,
  `party_id` bigint UNSIGNED NOT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `electronic_mail` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_party_legal_entity`
--

CREATE TABLE `invoice_party_legal_entity` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `party_type` enum('supplier','customer') NOT NULL,
  `party_id` bigint UNSIGNED NOT NULL,
  `registration_name` varchar(255) NOT NULL,
  `company_id_value` varchar(100) DEFAULT NULL,
  `company_id_scheme` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_party_payment_account`
--

CREATE TABLE `invoice_party_payment_account` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `party_type` enum('supplier','customer') NOT NULL,
  `party_id` bigint UNSIGNED NOT NULL,
  `iban` varchar(50) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `currency_code` char(3) DEFAULT 'SAR',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_party_tax_scheme`
--

CREATE TABLE `invoice_party_tax_scheme` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `party_type` enum('supplier','customer') NOT NULL,
  `party_id` bigint UNSIGNED NOT NULL,
  `tax_scheme_id` varchar(50) DEFAULT 'VAT',
  `vat_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_payment_means`
--

CREATE TABLE `invoice_payment_means` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `payment_means_code` varchar(20) DEFAULT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `instruction_note` text,
  `payment_due_date` date DEFAULT NULL,
  `iban` varchar(50) DEFAULT NULL,
  `account_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_references`
--

CREATE TABLE `invoice_references` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `reference_type` varchar(100) NOT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `reference_uuid` char(36) DEFAULT NULL,
  `reference_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_supplier_party`
--

CREATE TABLE `invoice_supplier_party` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `party_name` varchar(255) NOT NULL,
  `endpoint_id` varchar(100) DEFAULT NULL,
  `endpoint_scheme` varchar(50) DEFAULT NULL,
  `party_identification_id` varchar(100) DEFAULT NULL,
  `party_identification_scheme` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_tax_totals`
--

CREATE TABLE `invoice_tax_totals` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `tax_amount` decimal(15,2) DEFAULT '0.00',
  `taxable_amount` decimal(15,2) DEFAULT '0.00',
  `tax_category_id` varchar(10) DEFAULT 'S',
  `tax_percent` decimal(5,2) DEFAULT '15.00',
  `tax_scheme_id` varchar(50) DEFAULT 'VAT',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_totals`
--

CREATE TABLE `invoice_totals` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `line_extension_amount` decimal(15,2) DEFAULT '0.00',
  `allowance_total_amount` decimal(15,2) DEFAULT '0.00',
  `charge_total_amount` decimal(15,2) DEFAULT '0.00',
  `tax_exclusive_amount` decimal(15,2) DEFAULT '0.00',
  `tax_inclusive_amount` decimal(15,2) DEFAULT '0.00',
  `payable_amount` decimal(15,2) DEFAULT '0.00',
  `prepaid_amount` decimal(15,2) DEFAULT '0.00',
  `rounding_amount` decimal(15,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_zatca`
--

CREATE TABLE `invoice_zatca` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `invoice_hash` varchar(255) DEFAULT NULL,
  `previous_invoice_hash` varchar(255) DEFAULT NULL,
  `qr_code` text,
  `xml_content` longtext,
  `signed_xml` longtext,
  `clearance_status` enum('pending','cleared','rejected') DEFAULT 'pending',
  `reporting_status` enum('pending','reported','failed') DEFAULT 'pending',
  `zatca_status_code` varchar(20) DEFAULT NULL,
  `zatca_response` longtext,
  `submitted_at` datetime DEFAULT NULL,
  `cleared_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `unit_id` bigint UNSIGNED DEFAULT NULL,
  `tax_category_id` bigint UNSIGNED DEFAULT NULL,
  `item_code` varchar(100) NOT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text,
  `item_type` enum('product','service') DEFAULT 'product',
  `cost_price` decimal(15,4) DEFAULT '0.0000',
  `selling_price` decimal(15,4) DEFAULT '0.0000',
  `track_inventory` tinyint(1) DEFAULT '0',
  `status` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `item_categories`
--

CREATE TABLE `item_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `category_code` varchar(50) DEFAULT NULL,
  `category_name` varchar(255) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `item_images`
--

CREATE TABLE `item_images` (
  `id` bigint UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `item_inventory`
--

CREATE TABLE `item_inventory` (
  `id` bigint UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `quantity` decimal(15,3) DEFAULT '0.000',
  `reserved_quantity` decimal(15,3) DEFAULT '0.000',
  `minimum_stock` decimal(15,3) DEFAULT '0.000',
  `maximum_stock` decimal(15,3) DEFAULT '0.000',
  `warehouse_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `item_prices`
--

CREATE TABLE `item_prices` (
  `id` bigint UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `price_type` enum('purchase','selling','wholesale','special') DEFAULT 'selling',
  `price` decimal(15,4) NOT NULL,
  `currency_code` char(3) DEFAULT 'SAR',
  `minimum_quantity` decimal(15,3) DEFAULT '1.000',
  `valid_from` date DEFAULT NULL,
  `valid_to` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `item_tax_categories`
--

CREATE TABLE `item_tax_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `tax_category_id` varchar(10) DEFAULT 'S',
  `tax_scheme_id` varchar(50) DEFAULT 'VAT',
  `tax_percent` decimal(5,2) DEFAULT '15.00',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `item_units`
--

CREATE TABLE `item_units` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `unit_code` varchar(20) NOT NULL,
  `unit_name` varchar(100) NOT NULL,
  `ubl_unit_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `item_zatca_data`
--

CREATE TABLE `item_zatca_data` (
  `id` bigint UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `standard_item_id` varchar(100) DEFAULT NULL,
  `standard_item_scheme` varchar(50) DEFAULT NULL,
  `commodity_classification` varchar(100) DEFAULT NULL,
  `additional_item_properties` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `storage_files`
--

CREATE TABLE `storage_files` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED DEFAULT NULL,
  `file_type` enum('certificate','private_key','csr','invoice_xml','signed_xml','qr_image','invoice_pdf','attachment','zatca_response','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_extension` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint UNSIGNED DEFAULT '0',
  `checksum` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `storage_disk` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'local',
  `is_signed` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `storage_logs`
--

CREATE TABLE `storage_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `storage_file_id` bigint UNSIGNED DEFAULT NULL,
  `action` enum('created','updated','deleted','downloaded') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `setting_key` varchar(100) DEFAULT NULL,
  `setting_value` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `user_role` enum('admin','manager','accountant','viewer') NOT NULL DEFAULT 'viewer',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `email`, `user_role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$P.pfeyzVKAjAeGitUs.6dOMOgO.dyZhbpXf7bq4AfLSgdK0YBnt6G', 'Systm Administrator', 'mustafa2011@gmail.com', 'admin', 1, NULL, '2026-07-13 20:34:27', '2026-07-13 20:34:27');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_company_profile`
-- (See below for the actual view)
--
CREATE TABLE `v_company_profile` (
`company_id` bigint unsigned
,`company_name` varchar(255)
,`commercial_registration_number` varchar(100)
,`vat_number` varchar(15)
,`party_id` bigint unsigned
,`street_name` varchar(255)
,`building_number` varchar(50)
,`city_name` varchar(100)
,`postal_zone` varchar(20)
,`country_code` char(2)
,`tax_scheme_id` varchar(50)
,`registration_name` varchar(255)
,`telephone` varchar(50)
,`electronic_mail` varchar(255)
,`environment` enum('simulation','compliance','production')
,`zatca_client_id` varchar(255)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_customer_profile`
-- (See below for the actual view)
--
CREATE TABLE `v_customer_profile` (
`customer_id` bigint unsigned
,`customer_name` varchar(255)
,`vat_number` varchar(15)
,`party_id` bigint unsigned
,`street_name` varchar(255)
,`building_number` varchar(50)
,`city_name` varchar(100)
,`postal_zone` varchar(20)
,`country_code` char(2)
,`tax_scheme_id` varchar(50)
,`registration_name` varchar(255)
,`telephone` varchar(50)
,`electronic_mail` varchar(255)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_invoice_header`
-- (See below for the actual view)
--
CREATE TABLE `v_invoice_header` (
`invoice_id` bigint unsigned
,`invoice_uuid` char(36)
,`invoice_number` varchar(100)
,`invoice_type` enum('invoice','credit_note','debit_note')
,`issue_date` datetime
,`issue_time` datetime
,`currency_code` char(3)
,`company_id` bigint unsigned
,`customer_id` bigint unsigned
,`invoice_status` enum('draft','signed','reported','cleared','rejected')
,`line_extension_amount` decimal(15,2)
,`tax_exclusive_amount` decimal(15,2)
,`tax_inclusive_amount` decimal(15,2)
,`payable_amount` decimal(15,2)
,`tax_amount` decimal(15,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_invoice_lines`
-- (See below for the actual view)
--
CREATE TABLE `v_invoice_lines` (
`invoice_line_id` bigint unsigned
,`invoice_id` bigint unsigned
,`line_number` int
,`quantity` decimal(15,3)
,`unit_code` varchar(20)
,`line_extension_amount` decimal(15,2)
,`item_id` bigint unsigned
,`item_name` varchar(255)
,`item_description` text
,`tax_category_id` varchar(10)
,`tax_percent` decimal(5,2)
,`tax_amount` decimal(15,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_invoice_payment`
-- (See below for the actual view)
--
CREATE TABLE `v_invoice_payment` (
`invoice_id` bigint unsigned
,`payment_means_code` varchar(20)
,`payment_due_date` date
,`instruction_note` text
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_invoice_status_history`
-- (See below for the actual view)
--
CREATE TABLE `v_invoice_status_history` (
`invoice_id` bigint unsigned
,`event_type` varchar(100)
,`event_message` text
,`created_at` timestamp
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_invoice_zatca_documents`
-- (See below for the actual view)
--
CREATE TABLE `v_invoice_zatca_documents` (
`invoice_id` bigint unsigned
,`invoice_number` varchar(100)
,`storage_id` bigint unsigned
,`file_type` enum('certificate','private_key','csr','invoice_xml','signed_xml','qr_image','invoice_pdf','attachment','zatca_response','other')
,`file_path` varchar(500)
,`created_at` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `zatca_api_logs`
--

CREATE TABLE `zatca_api_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED DEFAULT NULL,
  `api_type` enum('csr','compliance','certificate','clearance','reporting') NOT NULL,
  `request_url` varchar(500) DEFAULT NULL,
  `request_headers` longtext,
  `request_body` longtext,
  `response_code` int DEFAULT NULL,
  `response_body` longtext,
  `success` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zatca_errors`
--

CREATE TABLE `zatca_errors` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED DEFAULT NULL,
  `error_code` varchar(100) DEFAULT NULL,
  `error_message` text,
  `error_details` longtext,
  `resolved` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_companies_user_id` (`user_id`),
  ADD KEY `idx_companies_vat_number` (`vat_number`),
  ADD KEY `idx_companies_status` (`status`);

--
-- Indexes for table `company_address`
--
ALTER TABLE `company_address`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_company_address_company` (`company_id`);

--
-- Indexes for table `company_bank_account`
--
ALTER TABLE `company_bank_account`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_company_bank_company` (`company_id`),
  ADD KEY `idx_company_bank_account_company` (`company_id`);

--
-- Indexes for table `company_certificates`
--
ALTER TABLE `company_certificates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_company_certificate_company` (`company_id`),
  ADD KEY `fk_company_certificate_file` (`certificate_file_id`);

--
-- Indexes for table `company_contact`
--
ALTER TABLE `company_contact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_company_contact_company` (`company_id`);

--
-- Indexes for table `company_legal_entity`
--
ALTER TABLE `company_legal_entity`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_company_legal_entity` (`company_id`),
  ADD KEY `idx_company_legal_entity_company` (`company_id`);

--
-- Indexes for table `company_party`
--
ALTER TABLE `company_party`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_company_party` (`company_id`),
  ADD KEY `idx_company_party_company` (`company_id`);

--
-- Indexes for table `company_sequences`
--
ALTER TABLE `company_sequences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_company_sequence` (`company_id`,`sequence_name`),
  ADD KEY `idx_company_sequences_company` (`company_id`);

--
-- Indexes for table `company_storage_settings`
--
ALTER TABLE `company_storage_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_company_storage` (`company_id`);

--
-- Indexes for table `company_tax_scheme`
--
ALTER TABLE `company_tax_scheme`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_company_vat_number` (`vat_number`),
  ADD KEY `idx_company_tax_scheme_company` (`company_id`);

--
-- Indexes for table `company_zatca_certificates`
--
ALTER TABLE `company_zatca_certificates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_certificate_company` (`company_id`);

--
-- Indexes for table `company_zatca_credentials`
--
ALTER TABLE `company_zatca_credentials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_credentials_certificate` (`certificate_id`),
  ADD KEY `idx_credentials_company` (`company_id`);

--
-- Indexes for table `company_zatca_settings`
--
ALTER TABLE `company_zatca_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_company_zatca` (`company_id`),
  ADD KEY `idx_company_zatca_settings_company` (`company_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customers_company` (`company_id`),
  ADD KEY `idx_customers_vat` (`vat_number`),
  ADD KEY `idx_customers_code` (`customer_code`),
  ADD KEY `idx_customers_vat_number` (`vat_number`),
  ADD KEY `idx_customers_status` (`status`);

--
-- Indexes for table `customer_address`
--
ALTER TABLE `customer_address`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_address_customer` (`customer_id`);

--
-- Indexes for table `customer_bank_account`
--
ALTER TABLE `customer_bank_account`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_bank_customer` (`customer_id`),
  ADD KEY `idx_customer_bank_account_customer` (`customer_id`);

--
-- Indexes for table `customer_contact`
--
ALTER TABLE `customer_contact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_contact_customer` (`customer_id`);

--
-- Indexes for table `customer_legal_entity`
--
ALTER TABLE `customer_legal_entity`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_customer_legal_entity` (`customer_id`),
  ADD KEY `idx_customer_legal_entity_customer` (`customer_id`);

--
-- Indexes for table `customer_party`
--
ALTER TABLE `customer_party`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_customer_party` (`customer_id`),
  ADD KEY `idx_customer_party_customer` (`customer_id`);

--
-- Indexes for table `customer_tax_scheme`
--
ALTER TABLE `customer_tax_scheme`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_vat_number` (`vat_number`),
  ADD KEY `idx_customer_tax_scheme_customer` (`customer_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_invoice_number_company` (`company_id`,`invoice_number`),
  ADD UNIQUE KEY `uq_invoice_uuid` (`invoice_uuid`),
  ADD KEY `fk_original_invoice` (`original_invoice_id`),
  ADD KEY `idx_invoices_company` (`company_id`),
  ADD KEY `idx_invoices_customer` (`customer_id`),
  ADD KEY `idx_invoices_uuid` (`invoice_uuid`),
  ADD KEY `idx_invoices_invoice_number` (`invoice_number`),
  ADD KEY `idx_invoices_issue_date` (`issue_date`),
  ADD KEY `idx_invoices_status` (`invoice_status`);

--
-- Indexes for table `invoice_allowances`
--
ALTER TABLE `invoice_allowances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_allowance_invoice` (`invoice_id`),
  ADD KEY `idx_invoice_allowances_invoice` (`invoice_id`);

--
-- Indexes for table `invoice_attachments`
--
ALTER TABLE `invoice_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_attachment_invoice` (`invoice_id`),
  ADD KEY `idx_invoice_attachments_invoice` (`invoice_id`);

--
-- Indexes for table `invoice_customer_party`
--
ALTER TABLE `invoice_customer_party`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_customer_party_invoice` (`invoice_id`);

--
-- Indexes for table `invoice_delivery`
--
ALTER TABLE `invoice_delivery`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_invoice_delivery` (`invoice_id`),
  ADD KEY `idx_invoice_delivery_invoice` (`invoice_id`);

--
-- Indexes for table `invoice_documents`
--
ALTER TABLE `invoice_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_invoice_documents_invoice` (`invoice_id`),
  ADD KEY `fk_invoice_documents_file` (`storage_file_id`);

--
-- Indexes for table `invoice_events`
--
ALTER TABLE `invoice_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_events_invoice` (`invoice_id`);

--
-- Indexes for table `invoice_lines`
--
ALTER TABLE `invoice_lines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_invoice_line_number` (`invoice_id`,`line_number`),
  ADD KEY `idx_invoice_lines_invoice` (`invoice_id`),
  ADD KEY `idx_invoice_lines_item` (`item_id`);

--
-- Indexes for table `invoice_line_taxes`
--
ALTER TABLE `invoice_line_taxes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_line_tax` (`invoice_line_id`),
  ADD KEY `idx_invoice_line_taxes_line` (`invoice_line_id`);

--
-- Indexes for table `invoice_notes`
--
ALTER TABLE `invoice_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_notes_invoice` (`invoice_id`);

--
-- Indexes for table `invoice_party_additional_identification`
--
ALTER TABLE `invoice_party_additional_identification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_party_identification` (`party_type`,`party_id`);

--
-- Indexes for table `invoice_party_address`
--
ALTER TABLE `invoice_party_address`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_party_address` (`party_type`,`party_id`),
  ADD KEY `idx_invoice_party_address_invoice` (`invoice_id`);

--
-- Indexes for table `invoice_party_contact`
--
ALTER TABLE `invoice_party_contact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_party_contact` (`party_type`,`party_id`),
  ADD KEY `idx_invoice_party_contact_invoice` (`invoice_id`);

--
-- Indexes for table `invoice_party_legal_entity`
--
ALTER TABLE `invoice_party_legal_entity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_party_legal_entity` (`party_type`,`party_id`),
  ADD KEY `idx_invoice_party_legal_entity_invoice` (`invoice_id`);

--
-- Indexes for table `invoice_party_payment_account`
--
ALTER TABLE `invoice_party_payment_account`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_party_payment_account` (`party_type`,`party_id`),
  ADD KEY `idx_invoice_party_payment_account_invoice` (`invoice_id`);

--
-- Indexes for table `invoice_party_tax_scheme`
--
ALTER TABLE `invoice_party_tax_scheme`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_party_tax` (`party_type`,`party_id`),
  ADD KEY `idx_invoice_party_tax_scheme_invoice` (`invoice_id`);

--
-- Indexes for table `invoice_payment_means`
--
ALTER TABLE `invoice_payment_means`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_payment_invoice` (`invoice_id`),
  ADD KEY `idx_invoice_payment_means_invoice` (`invoice_id`);

--
-- Indexes for table `invoice_references`
--
ALTER TABLE `invoice_references`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_reference` (`invoice_id`),
  ADD KEY `idx_invoice_references_invoice` (`invoice_id`);

--
-- Indexes for table `invoice_supplier_party`
--
ALTER TABLE `invoice_supplier_party`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_invoice_supplier_party` (`invoice_id`),
  ADD KEY `idx_invoice_supplier_party_invoice` (`invoice_id`);

--
-- Indexes for table `invoice_tax_totals`
--
ALTER TABLE `invoice_tax_totals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_tax_invoice` (`invoice_id`),
  ADD KEY `idx_invoice_tax_totals_invoice` (`invoice_id`);

--
-- Indexes for table `invoice_totals`
--
ALTER TABLE `invoice_totals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_invoice_totals` (`invoice_id`),
  ADD KEY `idx_invoice_totals_invoice` (`invoice_id`);

--
-- Indexes for table `invoice_zatca`
--
ALTER TABLE `invoice_zatca`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_invoice_uuid` (`uuid`),
  ADD KEY `idx_invoice_zatca_invoice` (`invoice_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_item_company_code` (`company_id`,`item_code`),
  ADD KEY `fk_items_category` (`category_id`),
  ADD KEY `fk_items_unit` (`unit_id`),
  ADD KEY `fk_items_tax_category` (`tax_category_id`),
  ADD KEY `idx_items_code` (`item_code`),
  ADD KEY `idx_items_barcode` (`barcode`),
  ADD KEY `idx_items_status` (`status`);

--
-- Indexes for table `item_categories`
--
ALTER TABLE `item_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item_category_company` (`company_id`);

--
-- Indexes for table `item_images`
--
ALTER TABLE `item_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item_images_item` (`item_id`);

--
-- Indexes for table `item_inventory`
--
ALTER TABLE `item_inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inventory_item` (`item_id`);

--
-- Indexes for table `item_prices`
--
ALTER TABLE `item_prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item_prices_item` (`item_id`);

--
-- Indexes for table `item_tax_categories`
--
ALTER TABLE `item_tax_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item_tax_company` (`company_id`);

--
-- Indexes for table `item_units`
--
ALTER TABLE `item_units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_item_unit_company` (`company_id`,`unit_code`);

--
-- Indexes for table `item_zatca_data`
--
ALTER TABLE `item_zatca_data`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_item_zatca` (`item_id`);

--
-- Indexes for table `storage_files`
--
ALTER TABLE `storage_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_storage_company` (`company_id`),
  ADD KEY `idx_storage_invoice` (`invoice_id`),
  ADD KEY `idx_storage_type` (`file_type`),
  ADD KEY `idx_storage_created` (`created_at`);

--
-- Indexes for table `storage_logs`
--
ALTER TABLE `storage_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_storage_logs_file` (`storage_file_id`),
  ADD KEY `fk_storage_logs_user` (`created_by`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_role` (`user_role`);

--
-- Indexes for table `zatca_api_logs`
--
ALTER TABLE `zatca_api_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_zatca_logs_company` (`company_id`),
  ADD KEY `idx_zatca_logs_invoice` (`invoice_id`);

--
-- Indexes for table `zatca_errors`
--
ALTER TABLE `zatca_errors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_zatca_errors_invoice` (`invoice_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_address`
--
ALTER TABLE `company_address`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_bank_account`
--
ALTER TABLE `company_bank_account`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_certificates`
--
ALTER TABLE `company_certificates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_contact`
--
ALTER TABLE `company_contact`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_legal_entity`
--
ALTER TABLE `company_legal_entity`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_party`
--
ALTER TABLE `company_party`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_sequences`
--
ALTER TABLE `company_sequences`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_storage_settings`
--
ALTER TABLE `company_storage_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_tax_scheme`
--
ALTER TABLE `company_tax_scheme`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_zatca_certificates`
--
ALTER TABLE `company_zatca_certificates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_zatca_credentials`
--
ALTER TABLE `company_zatca_credentials`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_zatca_settings`
--
ALTER TABLE `company_zatca_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_address`
--
ALTER TABLE `customer_address`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_bank_account`
--
ALTER TABLE `customer_bank_account`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_contact`
--
ALTER TABLE `customer_contact`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_legal_entity`
--
ALTER TABLE `customer_legal_entity`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_party`
--
ALTER TABLE `customer_party`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_tax_scheme`
--
ALTER TABLE `customer_tax_scheme`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_allowances`
--
ALTER TABLE `invoice_allowances`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_attachments`
--
ALTER TABLE `invoice_attachments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_customer_party`
--
ALTER TABLE `invoice_customer_party`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_delivery`
--
ALTER TABLE `invoice_delivery`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_documents`
--
ALTER TABLE `invoice_documents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_events`
--
ALTER TABLE `invoice_events`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_lines`
--
ALTER TABLE `invoice_lines`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_line_taxes`
--
ALTER TABLE `invoice_line_taxes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_notes`
--
ALTER TABLE `invoice_notes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_party_additional_identification`
--
ALTER TABLE `invoice_party_additional_identification`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_party_address`
--
ALTER TABLE `invoice_party_address`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_party_contact`
--
ALTER TABLE `invoice_party_contact`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_party_legal_entity`
--
ALTER TABLE `invoice_party_legal_entity`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_party_payment_account`
--
ALTER TABLE `invoice_party_payment_account`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_party_tax_scheme`
--
ALTER TABLE `invoice_party_tax_scheme`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_payment_means`
--
ALTER TABLE `invoice_payment_means`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_references`
--
ALTER TABLE `invoice_references`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_supplier_party`
--
ALTER TABLE `invoice_supplier_party`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_tax_totals`
--
ALTER TABLE `invoice_tax_totals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_totals`
--
ALTER TABLE `invoice_totals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_zatca`
--
ALTER TABLE `invoice_zatca`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `item_categories`
--
ALTER TABLE `item_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `item_images`
--
ALTER TABLE `item_images`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `item_inventory`
--
ALTER TABLE `item_inventory`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `item_prices`
--
ALTER TABLE `item_prices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `item_tax_categories`
--
ALTER TABLE `item_tax_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `item_units`
--
ALTER TABLE `item_units`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `item_zatca_data`
--
ALTER TABLE `item_zatca_data`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `storage_files`
--
ALTER TABLE `storage_files`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `storage_logs`
--
ALTER TABLE `storage_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `zatca_api_logs`
--
ALTER TABLE `zatca_api_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `zatca_errors`
--
ALTER TABLE `zatca_errors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Structure for view `v_company_profile`
--
DROP TABLE IF EXISTS `v_company_profile`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_company_profile`  AS SELECT `c`.`id` AS `company_id`, `c`.`company_name` AS `company_name`, `c`.`commercial_registration_number` AS `commercial_registration_number`, `c`.`vat_number` AS `vat_number`, `cp`.`id` AS `party_id`, `ca`.`street_name` AS `street_name`, `ca`.`building_number` AS `building_number`, `ca`.`city_name` AS `city_name`, `ca`.`postal_zone` AS `postal_zone`, `ca`.`country_code` AS `country_code`, `cts`.`tax_scheme_id` AS `tax_scheme_id`, `cle`.`registration_name` AS `registration_name`, `cc`.`telephone` AS `telephone`, `cc`.`electronic_mail` AS `electronic_mail`, `czs`.`environment` AS `environment`, `czs`.`zatca_client_id` AS `zatca_client_id` FROM ((((((`companies` `c` left join `company_party` `cp` on((`cp`.`company_id` = `c`.`id`))) left join `company_address` `ca` on((`ca`.`company_id` = `c`.`id`))) left join `company_tax_scheme` `cts` on((`cts`.`company_id` = `c`.`id`))) left join `company_legal_entity` `cle` on((`cle`.`company_id` = `c`.`id`))) left join `company_contact` `cc` on((`cc`.`company_id` = `c`.`id`))) left join `company_zatca_settings` `czs` on((`czs`.`company_id` = `c`.`id`))) ;

-- --------------------------------------------------------

--
-- Structure for view `v_customer_profile`
--
DROP TABLE IF EXISTS `v_customer_profile`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_customer_profile`  AS SELECT `cu`.`id` AS `customer_id`, `cu`.`customer_name` AS `customer_name`, `cu`.`vat_number` AS `vat_number`, `cp`.`id` AS `party_id`, `ca`.`street_name` AS `street_name`, `ca`.`building_number` AS `building_number`, `ca`.`city_name` AS `city_name`, `ca`.`postal_zone` AS `postal_zone`, `ca`.`country_code` AS `country_code`, `cts`.`tax_scheme_id` AS `tax_scheme_id`, `cle`.`registration_name` AS `registration_name`, `cc`.`telephone` AS `telephone`, `cc`.`electronic_mail` AS `electronic_mail` FROM (((((`customers` `cu` left join `customer_party` `cp` on((`cp`.`customer_id` = `cu`.`id`))) left join `customer_address` `ca` on((`ca`.`customer_id` = `cu`.`id`))) left join `customer_tax_scheme` `cts` on((`cts`.`customer_id` = `cu`.`id`))) left join `customer_legal_entity` `cle` on((`cle`.`customer_id` = `cu`.`id`))) left join `customer_contact` `cc` on((`cc`.`customer_id` = `cu`.`id`))) ;

-- --------------------------------------------------------

--
-- Structure for view `v_invoice_header`
--
DROP TABLE IF EXISTS `v_invoice_header`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_invoice_header`  AS SELECT `i`.`id` AS `invoice_id`, `i`.`invoice_uuid` AS `invoice_uuid`, `i`.`invoice_number` AS `invoice_number`, `i`.`invoice_type` AS `invoice_type`, `i`.`issue_date` AS `issue_date`, `i`.`issue_time` AS `issue_time`, `i`.`currency_code` AS `currency_code`, `i`.`company_id` AS `company_id`, `i`.`customer_id` AS `customer_id`, `i`.`invoice_status` AS `invoice_status`, `it`.`line_extension_amount` AS `line_extension_amount`, `it`.`tax_exclusive_amount` AS `tax_exclusive_amount`, `it`.`tax_inclusive_amount` AS `tax_inclusive_amount`, `it`.`payable_amount` AS `payable_amount`, `itt`.`tax_amount` AS `tax_amount` FROM ((`invoices` `i` left join `invoice_totals` `it` on((`it`.`invoice_id` = `i`.`id`))) left join `invoice_tax_totals` `itt` on((`itt`.`invoice_id` = `i`.`id`))) ;

-- --------------------------------------------------------

--
-- Structure for view `v_invoice_lines`
--
DROP TABLE IF EXISTS `v_invoice_lines`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_invoice_lines`  AS SELECT `il`.`id` AS `invoice_line_id`, `il`.`invoice_id` AS `invoice_id`, `il`.`line_number` AS `line_number`, `il`.`quantity` AS `quantity`, `il`.`unit_code` AS `unit_code`, `il`.`line_extension_amount` AS `line_extension_amount`, `il`.`item_id` AS `item_id`, `il`.`item_name` AS `item_name`, `il`.`item_description` AS `item_description`, `ilt`.`tax_category_id` AS `tax_category_id`, `ilt`.`tax_percent` AS `tax_percent`, `ilt`.`tax_amount` AS `tax_amount` FROM (`invoice_lines` `il` left join `invoice_line_taxes` `ilt` on((`ilt`.`invoice_line_id` = `il`.`id`))) ;

-- --------------------------------------------------------

--
-- Structure for view `v_invoice_payment`
--
DROP TABLE IF EXISTS `v_invoice_payment`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_invoice_payment`  AS SELECT `i`.`id` AS `invoice_id`, `ipm`.`payment_means_code` AS `payment_means_code`, `ipm`.`payment_due_date` AS `payment_due_date`, `ipm`.`instruction_note` AS `instruction_note` FROM (`invoices` `i` left join `invoice_payment_means` `ipm` on((`ipm`.`invoice_id` = `i`.`id`))) ;

-- --------------------------------------------------------

--
-- Structure for view `v_invoice_status_history`
--
DROP TABLE IF EXISTS `v_invoice_status_history`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_invoice_status_history`  AS SELECT `ie`.`invoice_id` AS `invoice_id`, `ie`.`event_type` AS `event_type`, `ie`.`event_message` AS `event_message`, `ie`.`created_at` AS `created_at` FROM `invoice_events` AS `ie` ;

-- --------------------------------------------------------

--
-- Structure for view `v_invoice_zatca_documents`
--
DROP TABLE IF EXISTS `v_invoice_zatca_documents`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_invoice_zatca_documents`  AS SELECT `i`.`id` AS `invoice_id`, `i`.`invoice_number` AS `invoice_number`, `s`.`id` AS `storage_id`, `s`.`file_type` AS `file_type`, `s`.`file_path` AS `file_path`, `s`.`created_at` AS `created_at` FROM (`invoices` `i` left join `storage_files` `s` on((`s`.`invoice_id` = `i`.`id`))) ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `companies`
--
ALTER TABLE `companies`
  ADD CONSTRAINT `fk_companies_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_address`
--
ALTER TABLE `company_address`
  ADD CONSTRAINT `fk_company_address_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_bank_account`
--
ALTER TABLE `company_bank_account`
  ADD CONSTRAINT `fk_company_bank_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_certificates`
--
ALTER TABLE `company_certificates`
  ADD CONSTRAINT `fk_company_certificate_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_company_certificate_file` FOREIGN KEY (`certificate_file_id`) REFERENCES `storage_files` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `company_contact`
--
ALTER TABLE `company_contact`
  ADD CONSTRAINT `fk_company_contact_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_legal_entity`
--
ALTER TABLE `company_legal_entity`
  ADD CONSTRAINT `fk_company_legal_entity_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_party`
--
ALTER TABLE `company_party`
  ADD CONSTRAINT `fk_company_party_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_sequences`
--
ALTER TABLE `company_sequences`
  ADD CONSTRAINT `fk_company_sequences_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_storage_settings`
--
ALTER TABLE `company_storage_settings`
  ADD CONSTRAINT `fk_company_storage_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_tax_scheme`
--
ALTER TABLE `company_tax_scheme`
  ADD CONSTRAINT `fk_company_tax_scheme_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_zatca_certificates`
--
ALTER TABLE `company_zatca_certificates`
  ADD CONSTRAINT `fk_zatca_certificate_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_zatca_credentials`
--
ALTER TABLE `company_zatca_credentials`
  ADD CONSTRAINT `fk_credentials_certificate` FOREIGN KEY (`certificate_id`) REFERENCES `company_zatca_certificates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_credentials_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_zatca_settings`
--
ALTER TABLE `company_zatca_settings`
  ADD CONSTRAINT `fk_company_zatca_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `fk_customers_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_address`
--
ALTER TABLE `customer_address`
  ADD CONSTRAINT `fk_customer_address_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_bank_account`
--
ALTER TABLE `customer_bank_account`
  ADD CONSTRAINT `fk_customer_bank_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_contact`
--
ALTER TABLE `customer_contact`
  ADD CONSTRAINT `fk_customer_contact_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_legal_entity`
--
ALTER TABLE `customer_legal_entity`
  ADD CONSTRAINT `fk_customer_legal_entity_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_party`
--
ALTER TABLE `customer_party`
  ADD CONSTRAINT `fk_customer_party_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_tax_scheme`
--
ALTER TABLE `customer_tax_scheme`
  ADD CONSTRAINT `fk_customer_tax_scheme_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_invoices_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_invoices_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_original_invoice` FOREIGN KEY (`original_invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `invoice_allowances`
--
ALTER TABLE `invoice_allowances`
  ADD CONSTRAINT `fk_invoice_allowance_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_attachments`
--
ALTER TABLE `invoice_attachments`
  ADD CONSTRAINT `fk_invoice_attachment_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_customer_party`
--
ALTER TABLE `invoice_customer_party`
  ADD CONSTRAINT `fk_invoice_customer_party_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_delivery`
--
ALTER TABLE `invoice_delivery`
  ADD CONSTRAINT `fk_invoice_delivery_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_documents`
--
ALTER TABLE `invoice_documents`
  ADD CONSTRAINT `fk_invoice_documents_file` FOREIGN KEY (`storage_file_id`) REFERENCES `storage_files` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_invoice_documents_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_events`
--
ALTER TABLE `invoice_events`
  ADD CONSTRAINT `fk_invoice_event_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_lines`
--
ALTER TABLE `invoice_lines`
  ADD CONSTRAINT `fk_invoice_lines_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_invoice_lines_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `invoice_line_taxes`
--
ALTER TABLE `invoice_line_taxes`
  ADD CONSTRAINT `fk_invoice_line_tax_line` FOREIGN KEY (`invoice_line_id`) REFERENCES `invoice_lines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_notes`
--
ALTER TABLE `invoice_notes`
  ADD CONSTRAINT `fk_invoice_note_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_payment_means`
--
ALTER TABLE `invoice_payment_means`
  ADD CONSTRAINT `fk_invoice_payment_means_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_references`
--
ALTER TABLE `invoice_references`
  ADD CONSTRAINT `fk_invoice_reference_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_supplier_party`
--
ALTER TABLE `invoice_supplier_party`
  ADD CONSTRAINT `fk_invoice_supplier_party_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_tax_totals`
--
ALTER TABLE `invoice_tax_totals`
  ADD CONSTRAINT `fk_invoice_tax_totals_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_totals`
--
ALTER TABLE `invoice_totals`
  ADD CONSTRAINT `fk_invoice_totals_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_zatca`
--
ALTER TABLE `invoice_zatca`
  ADD CONSTRAINT `fk_invoice_zatca_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `fk_items_category` FOREIGN KEY (`category_id`) REFERENCES `item_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_items_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_items_tax_category` FOREIGN KEY (`tax_category_id`) REFERENCES `item_tax_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_items_unit` FOREIGN KEY (`unit_id`) REFERENCES `item_units` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `item_categories`
--
ALTER TABLE `item_categories`
  ADD CONSTRAINT `fk_item_categories_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `item_images`
--
ALTER TABLE `item_images`
  ADD CONSTRAINT `fk_item_images_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `item_inventory`
--
ALTER TABLE `item_inventory`
  ADD CONSTRAINT `fk_item_inventory_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `item_prices`
--
ALTER TABLE `item_prices`
  ADD CONSTRAINT `fk_item_prices_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `item_tax_categories`
--
ALTER TABLE `item_tax_categories`
  ADD CONSTRAINT `fk_item_tax_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `item_units`
--
ALTER TABLE `item_units`
  ADD CONSTRAINT `fk_item_units_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `item_zatca_data`
--
ALTER TABLE `item_zatca_data`
  ADD CONSTRAINT `fk_item_zatca_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `storage_files`
--
ALTER TABLE `storage_files`
  ADD CONSTRAINT `fk_storage_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_storage_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `storage_logs`
--
ALTER TABLE `storage_logs`
  ADD CONSTRAINT `fk_storage_logs_file` FOREIGN KEY (`storage_file_id`) REFERENCES `storage_files` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_storage_logs_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `zatca_api_logs`
--
ALTER TABLE `zatca_api_logs`
  ADD CONSTRAINT `fk_zatca_logs_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_zatca_logs_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `zatca_errors`
--
ALTER TABLE `zatca_errors`
  ADD CONSTRAINT `fk_zatca_error_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
