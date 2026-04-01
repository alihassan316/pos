ALTER TABLE `purchase_invoices_temp` ADD `sequnce` INT NULL DEFAULT '1' AFTER `invoice_id`;

ALTER TABLE `products_invoice` ADD `sequnce` INT NOT NULL DEFAULT '1' AFTER `category_id`;

