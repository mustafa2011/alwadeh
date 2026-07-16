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

ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_companies_user_id` (`user_id`),
  ADD KEY `idx_companies_vat_number` (`vat_number`),
  ADD KEY `idx_companies_status` (`status`);


ALTER TABLE `companies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;


ALTER TABLE `companies`
  ADD CONSTRAINT `fk_companies_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

