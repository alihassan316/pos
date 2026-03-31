ALTER TABLE `purchase_invoices` ADD `status` INT NOT NULL DEFAULT '0' AFTER `notes`;

DROP TABLE IF EXISTS `purchase_invoices_temp`;
CREATE TABLE IF NOT EXISTS `purchase_invoices_temp` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` int NOT NULL,
  `name` varchar(250) NOT NULL,
  `ingrediant` varchar(455) DEFAULT NULL,
  `qty` int DEFAULT NULL,
  `bonus` int DEFAULT NULL,
  `perpack` int DEFAULT NULL,
  `batch` varchar(255) DEFAULT NULL,
  `expiry` date DEFAULT NULL,
  `expiry_alert` int DEFAULT NULL,
  `packprice` decimal(11,2) DEFAULT NULL,
  `discount_per` decimal(11,2) DEFAULT NULL,
  `discount_fix` decimal(11,2) DEFAULT NULL,
  `gst_per` decimal(11,2) DEFAULT NULL,
  `gst_fix` decimal(11,2) DEFAULT NULL,
  `final_price` decimal(11,2) DEFAULT NULL,
  `buy_price` decimal(11,2) DEFAULT NULL,
  `box_price` decimal(11,2) DEFAULT NULL,
  `sale_price` decimal(11,2) DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT CURRENT_TIMESTAMP(6),
  `updated_at` timestamp(6) NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

ALTER TABLE `purchase_invoices` CHANGE `total_items` `total_items` INT NULL; 
ALTER TABLE `purchase_invoices` CHANGE `gross_amount` `gross_amount` DECIMAL(11,2) NULL; 