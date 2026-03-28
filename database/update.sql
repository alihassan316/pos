CREATE TABLE purchase_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255),
    contact VARCHAR(255),
    invoice_number VARCHAR(255),
    invoice_date DATE,
    total_amount DECIMAL(10,2),
    notes TEXT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE products 
ADD COLUMN ingredient VARCHAR(255) NULL,
ADD COLUMN per_pack INT NULL,
ADD COLUMN expiry_alert_months INT NULL,
ADD COLUMN discount_percent DECIMAL(10,2) NULL,
ADD COLUMN discount_flat DECIMAL(10,2) NULL,
ADD COLUMN gst_flat DECIMAL(10,2) NULL,
ADD COLUMN final_buy_price DECIMAL(10,2) NULL,
ADD COLUMN purchase_invoice_id INT NULL,
ADD CONSTRAINT fk_purchase_invoice FOREIGN KEY (purchase_invoice_id) REFERENCES purchase_invoices(id);

ALTER TABLE `purchase_invoices` ADD `total_items` INT NOT NULL AFTER `invoice_date`, ADD `gross_amount` DECIMAL(11,2) NOT NULL AFTER `total_items`, ADD `discount_percent_amount` DECIMAL(11,2) NULL AFTER `gross_amount`, ADD `discount_flat_amount` DECIMAL(11,2) NULL AFTER `discount_percent_amount`, ADD `gst_percent_amount` DECIMAL(11,2) NULL AFTER `discount_flat_amount`, ADD `gst_flat_amount` DECIMAL(11,2) NULL AFTER `gst_percent_amount`;

CREATE TABLE IF NOT EXISTS `products_invoice` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `shop_id` bigint UNSIGNED NOT NULL DEFAULT '1',
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `company` varchar(255) DEFAULT NULL,
  `batch_no` varchar(150) DEFAULT NULL,
  `sku` varchar(191) DEFAULT NULL,
  `barcode` varchar(191) DEFAULT NULL,
  `buy_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `sell_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_box` tinyint(1) NOT NULL DEFAULT '0',
  `items_per_box` int DEFAULT NULL,
  `unit_sell_price` decimal(11,2) DEFAULT NULL,
  `gst` decimal(5,2) DEFAULT NULL,
  `discount` decimal(11,2) DEFAULT NULL,
  `current_stock` int NOT NULL DEFAULT '0',
  `expiry` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ingredient` varchar(255) DEFAULT NULL,
  `per_pack` int DEFAULT NULL,
  `expiry_alert_months` int DEFAULT NULL,
  `discount_percent` decimal(10,2) DEFAULT NULL,
  `discount_flat` decimal(10,2) DEFAULT NULL,
  `gst_flat` decimal(10,2) DEFAULT NULL,
  `final_buy_price` decimal(10,2) DEFAULT NULL,
  `purchase_invoice_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_purchase_invoice` (`purchase_invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE products_invoice ENGINE=InnoDB;

ALTER TABLE `products_invoice` ADD `qty` INT NOT NULL DEFAULT '0' AFTER `batch_no`, ADD `bonus` INT NOT NULL DEFAULT '0' AFTER `qty`;

CREATE TABLE sale_returns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sale_id BIGINT UNSIGNED NOT NULL,
    refund_amount DECIMAL(10,2) NOT NULL,
    return_note VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE sale_return_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sale_return_id BIGINT UNSIGNED NOT NULL,
    sale_item_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NULL,
    qty INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE `products_invoice` ADD `expiry_action` INT NOT NULL DEFAULT '0' AFTER `expiry_alert_months`; 