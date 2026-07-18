SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `company_zatca_certificates` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `certificate_type` enum('compliance','production') NOT NULL,
  `certificate_name` varchar(255) DEFAULT NULL,
  `certificate_serial` varchar(255) DEFAULT NULL,
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

CREATE TABLE `company_zatca_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `environment` enum('nonprod','simulation','production') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'nonprod',
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
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `vat_number` varchar(15) DEFAULT NULL,
  `crn` varchar(20) DEFAULT NULL,
  `organization_name` varchar(255) DEFAULT NULL,
  `branch_name` varchar(255) DEFAULT NULL,
  `address` text,
  `street` varchar(255) DEFAULT NULL,
  `building_number` varchar(50) DEFAULT NULL,
  `subdivision` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postal_zone` varchar(20) DEFAULT NULL,
  `business_category` varchar(255) DEFAULT NULL,
  `invoice_type` varchar(50) DEFAULT NULL,
  `common_name` varchar(255) DEFAULT NULL,
  `serial_1` varchar(255) DEFAULT NULL,
  `serial_2` varchar(255) DEFAULT NULL,
  `generated_uuid` char(36) DEFAULT NULL,
  `generated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


ALTER TABLE `company_zatca_certificates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_certificate_company` (`company_id`);

ALTER TABLE `company_zatca_credentials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_credentials_certificate` (`certificate_id`),
  ADD KEY `idx_credentials_company` (`company_id`);

ALTER TABLE `company_zatca_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_company_zatca` (`company_id`),
  ADD KEY `idx_company_zatca_settings_company` (`company_id`);


ALTER TABLE `company_zatca_certificates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `company_zatca_credentials`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `company_zatca_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;


ALTER TABLE `company_zatca_certificates`
  ADD CONSTRAINT `fk_zatca_certificate_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

ALTER TABLE `company_zatca_credentials`
  ADD CONSTRAINT `fk_credentials_certificate` FOREIGN KEY (`certificate_id`) REFERENCES `company_zatca_certificates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_credentials_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

ALTER TABLE `company_zatca_settings`
  ADD CONSTRAINT `fk_company_zatca_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
