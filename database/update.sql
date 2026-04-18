ALTER TABLE `sale_items` ADD `purchase_price` DECIMAL(11,2) NULL AFTER `custom_name`; 
ALTER TABLE `sale_return_items` ADD `purchase_price` DECIMAL(11,2) NULL AFTER `qty`;