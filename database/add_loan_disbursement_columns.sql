-- Migration: add disbursed_account_id and disbursed_at to loans table
-- Run in nexo_banking database

-- Use IF NOT EXISTS to avoid duplicate column errors on re-run (MySQL 8.0.16+)
ALTER TABLE `loans`
  ADD COLUMN IF NOT EXISTS `disbursed_account_id` INT(11) NULL AFTER `monthly_payment`,
  ADD COLUMN IF NOT EXISTS `disbursed_at` TIMESTAMP NULL AFTER `disbursed_account_id`;
