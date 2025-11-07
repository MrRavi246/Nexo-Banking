-- Migration: create loans and loan_payments tables
-- Run this SQL in your `nexo_banking` database

CREATE TABLE IF NOT EXISTS `loans` (
  `loan_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `loan_type` VARCHAR(50) NOT NULL,
  `principal` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `outstanding` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `term_months` INT(11) NOT NULL DEFAULT 0,
  `apr` DECIMAL(5,2) DEFAULT 0.00,
  `monthly_payment` DECIMAL(15,2) DEFAULT NULL,
  `status` VARCHAR(30) DEFAULT 'pending',
  `purpose` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`loan_id`),
  KEY `user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `loan_payments` (
  `payment_id` INT(11) NOT NULL AUTO_INCREMENT,
  `loan_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `account_id` INT(11) NOT NULL,
  `transaction_id` INT(11) DEFAULT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `loan_id_idx` (`loan_id`),
  KEY `user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optionally add foreign keys if your schema supports them
-- ALTER TABLE loans ADD CONSTRAINT fk_loans_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE;
-- ALTER TABLE loan_payments ADD CONSTRAINT fk_lp_loan FOREIGN KEY (loan_id) REFERENCES loans(loan_id) ON DELETE CASCADE;
