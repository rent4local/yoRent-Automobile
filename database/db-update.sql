ALTER TABLE `tbl_product_specifications` ADD `prodspec_identifier` VARCHAR(100) NULL DEFAULT NULL AFTER `prodspec_product_id`, ADD UNIQUE (`prodspec_identifier`);

ALTER TABLE `tbl_product_specifications_lang` DROP `prodspec_is_file`;
ALTER TABLE `tbl_product_specifications` ADD `prodspec_is_file` TINYINT(4) NOT NULL DEFAULT '0' AFTER `prodspec_product_id`;

ALTER TABLE `tbl_product_specifications` DROP INDEX `prodspec_identifier`, ADD UNIQUE `prodspec_identifier` (`prodspec_identifier`, `prodspec_product_id`);
ALTER TABLE `tbl_seller_products` ADD `selprod_avg_rating` INT(11) NOT NULL DEFAULT '0' AFTER `selprod_is_eligible_refund`;
ALTER TABLE `tbl_seller_products` ADD `selprod_review_count` INT(11) NOT NULL AFTER `selprod_avg_rating`;

ALTER TABLE `tbl_order_user_address` ADD `oua_dial_code` VARCHAR(20) NULL AFTER `oua_country_code_alpha3`;
ALTER TABLE `tbl_order_products` ADD `op_shop_owner_phone_code` VARCHAR(10) NULL AFTER `op_shop_owner_email`;

/*  22-03-2022 */

ALTER TABLE `tbl_order_product_shipping` ADD `opshipping_type` INT(11) NULL DEFAULT NULL AFTER `opshipping_ship_duration`;
