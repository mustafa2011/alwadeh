SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


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

INSERT INTO `customers` (`id`, `company_id`, `customer_code`, `customer_name`, `registration_name`, `customer_type`, `vat_number`, `commercial_registration_number`, `country_code`, `currency_code`, `payment_terms`, `credit_limit`, `status`, `created_at`, `updated_at`) VALUES
(6, 101, NULL, 'Test Customer Company', NULL, 'company', NULL, NULL, 'SA', 'SAR', NULL, 0.00, 1, '2026-07-23 09:27:36', '2026-07-23 09:27:36');

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

INSERT INTO `customer_address` (`id`, `customer_id`, `address_type`, `street_name`, `building_number`, `plot_identification`, `city_name`, `postal_zone`, `country_subentity`, `country_code`, `additional_number`, `created_at`, `updated_at`) VALUES
(7, 6, 'main', 'King Fahd Road', '1234', 'Al Olaya', 'Riyadh', '12211', NULL, 'SA', NULL, '2026-07-23 09:27:36', '2026-07-23 09:27:36');

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

INSERT INTO `customer_party` (`id`, `customer_id`, `endpoint_id`, `endpoint_scheme`, `party_identification_id`, `party_identification_scheme`, `party_name`, `created_at`, `updated_at`) VALUES
(6, 6, '300000000000003', 'CRN', NULL, NULL, 'Test Customer Company', '2026-07-23 09:27:36', '2026-07-23 09:27:36');

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

INSERT INTO `customer_tax_scheme` (`id`, `customer_id`, `tax_scheme_id`, `vat_number`, `tax_category_id`, `tax_percent`, `created_at`, `updated_at`) VALUES
(2, 6, 'VAT', NULL, 'S', 15.00, '2026-07-23 09:27:36', '2026-07-23 09:27:36');


ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customers_company` (`company_id`),
  ADD KEY `idx_customers_vat` (`vat_number`),
  ADD KEY `idx_customers_code` (`customer_code`),
  ADD KEY `idx_customers_vat_number` (`vat_number`),
  ADD KEY `idx_customers_status` (`status`);

ALTER TABLE `customer_address`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_address_customer` (`customer_id`);

ALTER TABLE `customer_bank_account`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_bank_customer` (`customer_id`),
  ADD KEY `idx_customer_bank_account_customer` (`customer_id`);

ALTER TABLE `customer_contact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_contact_customer` (`customer_id`);

ALTER TABLE `customer_legal_entity`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_customer_legal_entity` (`customer_id`),
  ADD KEY `idx_customer_legal_entity_customer` (`customer_id`);

ALTER TABLE `customer_party`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_customer_party` (`customer_id`),
  ADD KEY `idx_customer_party_customer` (`customer_id`);

ALTER TABLE `customer_tax_scheme`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_vat_number` (`vat_number`),
  ADD KEY `idx_customer_tax_scheme_customer` (`customer_id`);


ALTER TABLE `customers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `customer_address`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

ALTER TABLE `customer_bank_account`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `customer_contact`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `customer_legal_entity`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `customer_party`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `customer_tax_scheme`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;


ALTER TABLE `customers`
  ADD CONSTRAINT `fk_customers_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

ALTER TABLE `customer_address`
  ADD CONSTRAINT `fk_customer_address_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

ALTER TABLE `customer_bank_account`
  ADD CONSTRAINT `fk_customer_bank_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

ALTER TABLE `customer_contact`
  ADD CONSTRAINT `fk_customer_contact_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

ALTER TABLE `customer_legal_entity`
  ADD CONSTRAINT `fk_customer_legal_entity_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

ALTER TABLE `customer_party`
  ADD CONSTRAINT `fk_customer_party_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

ALTER TABLE `customer_tax_scheme`
  ADD CONSTRAINT `fk_customer_tax_scheme_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
