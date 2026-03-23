ALTER TABLE `sales` CHANGE `status` `status` ENUM('pending','paid','partial','return') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending';

CREATE TABLE suppliers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    company VARCHAR(255) NULL,
    contact VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE product_supplier (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    supplier_id BIGINT UNSIGNED NOT NULL,
    buy_price DECIMAL(10,2) NULL,
    qty INT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    CONSTRAINT fk_product_supplier_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_product_supplier_supplier
        FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
        ON DELETE CASCADE
);

ALTER TABLE products
    DROP COLUMN supplier,
    DROP COLUMN company,
    DROP COLUMN contact;

ALTER TABLE `products` ADD `company` VARCHAR(255) NULL AFTER `name`;
ALTER TABLE `products` ADD `is_box` TINYINT(1) NOT NULL DEFAULT '0' AFTER `sell_price`, ADD `items_per_box` INT NULL AFTER `is_box`, ADD `unit_sell_price` DECIMAL(11,2) NULL AFTER `items_per_box`;
ALTER TABLE `products` ADD `batch_no` VARCHAR(150) NULL AFTER `company`; 
ALTER TABLE `products` ADD `gst` DECIMAL(5,2) NULL AFTER `unit_sell_price`; 
ALTER TABLE `sales` CHANGE `status` `status` ENUM('pending','paid','partial','return','partial_return') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending'; 