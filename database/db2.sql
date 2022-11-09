
ALTER TABLE `tbl_abandoned_cart`
  ADD PRIMARY KEY (`abandonedcart_id`);

--
-- Indexes for table `tbl_abusive_words`
--
ALTER TABLE `tbl_abusive_words`
  ADD PRIMARY KEY (`abusive_id`),
  ADD UNIQUE KEY `abusive_word` (`abusive_keyword`,`abusive_lang_id`);

--
-- Indexes for table `tbl_addresses`
--
ALTER TABLE `tbl_addresses`
  ADD PRIMARY KEY (`addr_id`);

--
-- Indexes for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `tbl_admin_auth_token`
--
ALTER TABLE `tbl_admin_auth_token`
  ADD PRIMARY KEY (`admauth_token`),
  ADD KEY `admrm_admin_id` (`admauth_admin_id`);

--
-- Indexes for table `tbl_admin_permissions`
--
ALTER TABLE `tbl_admin_permissions`
  ADD PRIMARY KEY (`admperm_admin_id`,`admperm_section_id`);

--
-- Indexes for table `tbl_ads_batches`
--
ALTER TABLE `tbl_ads_batches`
  ADD PRIMARY KEY (`adsbatch_id`);

--
-- Indexes for table `tbl_ads_batch_products`
--
ALTER TABLE `tbl_ads_batch_products`
  ADD PRIMARY KEY (`abprod_adsbatch_id`,`abprod_selprod_id`);

--
-- Indexes for table `tbl_affiliate_commission_settings`
--
ALTER TABLE `tbl_affiliate_commission_settings`
  ADD PRIMARY KEY (`afcommsetting_id`),
  ADD UNIQUE KEY `afcommsetting_prodcat_id` (`afcommsetting_prodcat_id`,`afcommsetting_user_id`);

--
-- Indexes for table `tbl_affiliate_commission_setting_history`
--
ALTER TABLE `tbl_affiliate_commission_setting_history`
  ADD PRIMARY KEY (`acsh_id`);

--
-- Indexes for table `tbl_attached_files`
--
ALTER TABLE `tbl_attached_files`
  ADD PRIMARY KEY (`afile_id`),
  ADD KEY `afile_type` (`afile_type`,`afile_record_id`,`afile_record_subid`,`afile_lang_id`) USING BTREE;

--
-- Indexes for table `tbl_attached_files_temp`
--
ALTER TABLE `tbl_attached_files_temp`
  ADD PRIMARY KEY (`afile_id`),
  ADD KEY `afile_type` (`afile_type`,`afile_record_id`,`afile_record_subid`,`afile_lang_id`) USING BTREE;

--
-- Indexes for table `tbl_attribute_groups`
--
ALTER TABLE `tbl_attribute_groups`
  ADD PRIMARY KEY (`attrgrp_id`);

--
-- Indexes for table `tbl_attribute_groups_lang`
--
ALTER TABLE `tbl_attribute_groups_lang`
  ADD PRIMARY KEY (`attrgrplang_attrgrp_id`,`attrgrplang_lang_id`);

--
-- Indexes for table `tbl_attribute_group_attributes`
--
ALTER TABLE `tbl_attribute_group_attributes`
  ADD PRIMARY KEY (`attr_id`),
  ADD UNIQUE KEY `attr_attrgrp_id_2` (`attr_attrgrp_id`,`attr_fld_name`,`attr_prodcat_id`) USING BTREE,
  ADD KEY `attr_attrgrp_id` (`attr_attrgrp_id`);

--
-- Indexes for table `tbl_attribute_group_attributes_lang`
--
ALTER TABLE `tbl_attribute_group_attributes_lang`
  ADD PRIMARY KEY (`attrlang_attr_id`,`attrlang_lang_id`);

--
-- Indexes for table `tbl_banners`
--
ALTER TABLE `tbl_banners`
  ADD PRIMARY KEY (`banner_id`);

--
-- Indexes for table `tbl_banners_clicks`
--
ALTER TABLE `tbl_banners_clicks`
  ADD UNIQUE KEY `pbhistory_id` (`bclick_id`),
  ADD UNIQUE KEY `pclick_promotion_id` (`bclick_banner_id`,`bclick_ip`,`bclick_session_id`);

--
-- Indexes for table `tbl_banners_lang`
--
ALTER TABLE `tbl_banners_lang`
  ADD PRIMARY KEY (`bannerlang_banner_id`,`bannerlang_lang_id`);

--
-- Indexes for table `tbl_banners_logs`
--
ALTER TABLE `tbl_banners_logs`
  ADD UNIQUE KEY `lprom_id` (`lbanner_banner_id`,`lbanner_date`);

--
-- Indexes for table `tbl_banner_locations`
--
ALTER TABLE `tbl_banner_locations`
  ADD PRIMARY KEY (`blocation_id`);

--
-- Indexes for table `tbl_banner_locations_lang`
--
ALTER TABLE `tbl_banner_locations_lang`
  ADD PRIMARY KEY (`blocationlang_blocation_id`,`blocationlang_lang_id`);

--
-- Indexes for table `tbl_banner_location_dimensions`
--
ALTER TABLE `tbl_banner_location_dimensions`
  ADD PRIMARY KEY (`bldimension_blocation_id`,`bldimension_device_type`);

--
-- Indexes for table `tbl_blog_contributions`
--
ALTER TABLE `tbl_blog_contributions`
  ADD PRIMARY KEY (`bcontributions_id`);

--
-- Indexes for table `tbl_blog_post`
--
ALTER TABLE `tbl_blog_post`
  ADD PRIMARY KEY (`post_id`);

--
-- Indexes for table `tbl_blog_post_categories`
--
ALTER TABLE `tbl_blog_post_categories`
  ADD PRIMARY KEY (`bpcategory_id`);

--
-- Indexes for table `tbl_blog_post_categories_lang`
--
ALTER TABLE `tbl_blog_post_categories_lang`
  ADD PRIMARY KEY (`bpcategorylang_bpcategory_id`,`bpcategorylang_lang_id`);

--
-- Indexes for table `tbl_blog_post_comments`
--
ALTER TABLE `tbl_blog_post_comments`
  ADD PRIMARY KEY (`bpcomment_id`);

--
-- Indexes for table `tbl_blog_post_lang`
--
ALTER TABLE `tbl_blog_post_lang`
  ADD PRIMARY KEY (`postlang_post_id`,`postlang_lang_id`);

--
-- Indexes for table `tbl_blog_post_to_category`
--
ALTER TABLE `tbl_blog_post_to_category`
  ADD PRIMARY KEY (`ptc_bpcategory_id`,`ptc_post_id`);

--
-- Indexes for table `tbl_brands`
--
ALTER TABLE `tbl_brands`
  ADD PRIMARY KEY (`brand_id`),
  ADD UNIQUE KEY `brand_identifier` (`brand_identifier`);

--
-- Indexes for table `tbl_brands_lang`
--
ALTER TABLE `tbl_brands_lang`
  ADD PRIMARY KEY (`brandlang_brand_id`,`brandlang_lang_id`),
  ADD UNIQUE KEY `brandlang_lang_id` (`brandlang_lang_id`,`brand_name`);

--
-- Indexes for table `tbl_buyer_late_charges_history`
--
ALTER TABLE `tbl_buyer_late_charges_history`
  ADD PRIMARY KEY (`charge_op_id`);

--
-- Indexes for table `tbl_catalog_request_messages`
--
ALTER TABLE `tbl_catalog_request_messages`
  ADD PRIMARY KEY (`scatrequestmsg_id`);

--
-- Indexes for table `tbl_collections`
--
ALTER TABLE `tbl_collections`
  ADD PRIMARY KEY (`collection_id`),
  ADD UNIQUE KEY `collection_identifier` (`collection_identifier`);

--
-- Indexes for table `tbl_collections_lang`
--
ALTER TABLE `tbl_collections_lang`
  ADD PRIMARY KEY (`collectionlang_collection_id`,`collectionlang_lang_id`);

--
-- Indexes for table `tbl_collection_to_records`
--
ALTER TABLE `tbl_collection_to_records`
  ADD PRIMARY KEY (`ctr_collection_id`,`ctr_record_id`);

--
-- Indexes for table `tbl_commission_settings`
--
ALTER TABLE `tbl_commission_settings`
  ADD PRIMARY KEY (`commsetting_id`),
  ADD UNIQUE KEY `commsetting_id` (`commsetting_id`),
  ADD UNIQUE KEY `commsetting_product_id` (`commsetting_product_id`,`commsetting_user_id`,`commsetting_prodcat_id`,`commsetting_type`);

--
-- Indexes for table `tbl_commission_setting_history`
--
ALTER TABLE `tbl_commission_setting_history`
  ADD PRIMARY KEY (`csh_id`);

--
-- Indexes for table `tbl_configurations`
--
ALTER TABLE `tbl_configurations`
  ADD PRIMARY KEY (`conf_name`);

--
-- Indexes for table `tbl_content_block_sections`
--
ALTER TABLE `tbl_content_block_sections`
  ADD PRIMARY KEY (`cbs_id`);

--
-- Indexes for table `tbl_content_block_sections_lang`
--
ALTER TABLE `tbl_content_block_sections_lang`
  ADD UNIQUE KEY `cbslang_cbs_id` (`cbslang_cbs_id`,`cbslang_lang_id`);

--
-- Indexes for table `tbl_content_block_to_category`
--
ALTER TABLE `tbl_content_block_to_category`
  ADD PRIMARY KEY (`cbtc_prodcat_id`,`cbtc_cpage_id`);

--
-- Indexes for table `tbl_content_pages`
--
ALTER TABLE `tbl_content_pages`
  ADD PRIMARY KEY (`cpage_id`);

--
-- Indexes for table `tbl_content_pages_block_lang`
--
ALTER TABLE `tbl_content_pages_block_lang`
  ADD PRIMARY KEY (`cpblocklang_id`),
  ADD UNIQUE KEY `cpblocklang_lang_id` (`cpblocklang_lang_id`,`cpblocklang_cpage_id`,`cpblocklang_block_id`);

--
-- Indexes for table `tbl_content_pages_lang`
--
ALTER TABLE `tbl_content_pages_lang`
  ADD PRIMARY KEY (`cpagelang_cpage_id`,`cpagelang_lang_id`);

--
-- Indexes for table `tbl_counter_offers`
--
ALTER TABLE `tbl_counter_offers`
  ADD PRIMARY KEY (`counter_offer_id`);

--
-- Indexes for table `tbl_countries`
--
ALTER TABLE `tbl_countries`
  ADD PRIMARY KEY (`country_id`),
  ADD UNIQUE KEY `country_code` (`country_code`);

--
-- Indexes for table `tbl_countries_lang`
--
ALTER TABLE `tbl_countries_lang`
  ADD PRIMARY KEY (`countrylang_country_id`,`countrylang_lang_id`),
  ADD UNIQUE KEY `countrylang_lang_id` (`countrylang_lang_id`,`country_name`);

--
-- Indexes for table `tbl_coupons`
--
ALTER TABLE `tbl_coupons`
  ADD PRIMARY KEY (`coupon_id`),
  ADD UNIQUE KEY `coupon_code` (`coupon_code`);

--
-- Indexes for table `tbl_coupons_history`
--
ALTER TABLE `tbl_coupons_history`
  ADD PRIMARY KEY (`couponhistory_id`);

--
-- Indexes for table `tbl_coupons_hold`
--
ALTER TABLE `tbl_coupons_hold`
  ADD PRIMARY KEY (`couponhold_id`),
  ADD UNIQUE KEY `couponhold_coupon_id` (`couponhold_coupon_id`,`couponhold_user_id`);

--
-- Indexes for table `tbl_coupons_hold_pending_order`
--
ALTER TABLE `tbl_coupons_hold_pending_order`
  ADD PRIMARY KEY (`ochold_order_id`,`ochold_coupon_id`);

--
-- Indexes for table `tbl_coupons_lang`
--
ALTER TABLE `tbl_coupons_lang`
  ADD PRIMARY KEY (`couponlang_coupon_id`,`couponlang_lang_id`);

--
-- Indexes for table `tbl_coupon_to_brands`
--
ALTER TABLE `tbl_coupon_to_brands`
  ADD UNIQUE KEY `ctp_brand_id` (`ctb_brand_id`,`ctb_coupon_id`);

--
-- Indexes for table `tbl_coupon_to_category`
--
ALTER TABLE `tbl_coupon_to_category`
  ADD PRIMARY KEY (`ctc_prodcat_id`,`ctc_coupon_id`);

--
-- Indexes for table `tbl_coupon_to_plan`
--
ALTER TABLE `tbl_coupon_to_plan`
  ADD PRIMARY KEY (`ctplan_spplan_id`,`ctplan_coupon_id`);

--
-- Indexes for table `tbl_coupon_to_products`
--
ALTER TABLE `tbl_coupon_to_products`
  ADD PRIMARY KEY (`ctp_product_id`,`ctp_coupon_id`);

--
-- Indexes for table `tbl_coupon_to_seller`
--
ALTER TABLE `tbl_coupon_to_seller`
  ADD PRIMARY KEY (`cts_user_id`,`cts_coupon_id`);

--
-- Indexes for table `tbl_coupon_to_shops`
--
ALTER TABLE `tbl_coupon_to_shops`
  ADD PRIMARY KEY (`cts_shop_id`,`cts_coupon_id`);

--
-- Indexes for table `tbl_coupon_to_users`
--
ALTER TABLE `tbl_coupon_to_users`
  ADD PRIMARY KEY (`ctu_user_id`,`ctu_coupon_id`);

--
-- Indexes for table `tbl_cron_log`
--
ALTER TABLE `tbl_cron_log`
  ADD PRIMARY KEY (`cronlog_id`),
  ADD KEY `cronlog_cron_id` (`cronlog_cron_id`);

--
-- Indexes for table `tbl_cron_schedules`
--
ALTER TABLE `tbl_cron_schedules`
  ADD PRIMARY KEY (`cron_id`);

--
-- Indexes for table `tbl_currency`
--
ALTER TABLE `tbl_currency`
  ADD PRIMARY KEY (`currency_id`),
  ADD UNIQUE KEY `currency_code` (`currency_code`);

--
-- Indexes for table `tbl_currency_lang`
--
ALTER TABLE `tbl_currency_lang`
  ADD PRIMARY KEY (`currencylang_currency_id`,`currencylang_lang_id`);

--
-- Indexes for table `tbl_email_archives`
--
ALTER TABLE `tbl_email_archives`
  ADD PRIMARY KEY (`emailarchive_id`);

--
-- Indexes for table `tbl_email_templates`
--
ALTER TABLE `tbl_email_templates`
  ADD PRIMARY KEY (`etpl_code`,`etpl_lang_id`);

--
-- Indexes for table `tbl_empty_cart_items`
--
ALTER TABLE `tbl_empty_cart_items`
  ADD PRIMARY KEY (`emptycartitem_id`);

--
-- Indexes for table `tbl_empty_cart_items_lang`
--
ALTER TABLE `tbl_empty_cart_items_lang`
  ADD UNIQUE KEY `emptycartitemlang_emptycartitem_id` (`emptycartitemlang_emptycartitem_id`,`emptycartitemlang_lang_id`);

--
-- Indexes for table `tbl_extra_attributes`
--
ALTER TABLE `tbl_extra_attributes`
  ADD PRIMARY KEY (`eattribute_id`),
  ADD KEY `eattribute_eattrgroup_id` (`eattribute_eattrgroup_id`);

--
-- Indexes for table `tbl_extra_attributes_lang`
--
ALTER TABLE `tbl_extra_attributes_lang`
  ADD UNIQUE KEY `eattribute_eattribute_id` (`eattributelang_eattribute_id`,`eattributelang_lang_id`);

--
-- Indexes for table `tbl_extra_attribute_groups`
--
ALTER TABLE `tbl_extra_attribute_groups`
  ADD PRIMARY KEY (`eattrgroup_id`);

--
-- Indexes for table `tbl_extra_attribute_groups_lang`
--
ALTER TABLE `tbl_extra_attribute_groups_lang`
  ADD UNIQUE KEY `eattrgrouplang_eattrgroup_id` (`eattrgrouplang_eattrgroup_id`,`eattrgrouplang_lang_id`);

--
-- Indexes for table `tbl_extra_pages`
--
ALTER TABLE `tbl_extra_pages`
  ADD PRIMARY KEY (`epage_id`);

--
-- Indexes for table `tbl_extra_pages_lang`
--
ALTER TABLE `tbl_extra_pages_lang`
  ADD PRIMARY KEY (`epagelang_epage_id`,`epagelang_lang_id`);

--
-- Indexes for table `tbl_faqs`
--
ALTER TABLE `tbl_faqs`
  ADD PRIMARY KEY (`faq_id`);

--
-- Indexes for table `tbl_faqs_lang`
--
ALTER TABLE `tbl_faqs_lang`
  ADD PRIMARY KEY (`faqlang_faq_id`,`faqlang_lang_id`);

--
-- Indexes for table `tbl_faq_categories`
--
ALTER TABLE `tbl_faq_categories`
  ADD PRIMARY KEY (`faqcat_id`);

--
-- Indexes for table `tbl_faq_categories_lang`
--
ALTER TABLE `tbl_faq_categories_lang`
  ADD PRIMARY KEY (`faqcatlang_faqcat_id`,`faqcatlang_lang_id`);

--
-- Indexes for table `tbl_filters`
--
ALTER TABLE `tbl_filters`
  ADD PRIMARY KEY (`filter_id`);

--
-- Indexes for table `tbl_filters_lang`
--
ALTER TABLE `tbl_filters_lang`
  ADD PRIMARY KEY (`filterlang_filter_id`,`filterlang_lang_id`);

--
-- Indexes for table `tbl_filter_groups`
--
ALTER TABLE `tbl_filter_groups`
  ADD PRIMARY KEY (`filtergroup_id`);

--
-- Indexes for table `tbl_filter_groups_lang`
--
ALTER TABLE `tbl_filter_groups_lang`
  ADD UNIQUE KEY `filtergrouplang_filtergroup_id` (`filtergrouplang_filtergroup_id`,`filtergrouplang_lang_id`);

--
-- Indexes for table `tbl_google_fonts`
--
ALTER TABLE `tbl_google_fonts`
  ADD PRIMARY KEY (`gfont_id`);

--
-- Indexes for table `tbl_google_font_variants`
--
ALTER TABLE `tbl_google_font_variants`
  ADD PRIMARY KEY (`fvariant_gfont_id`,`fvariant_name`);

--
-- Indexes for table `tbl_import_export_settings`
--
ALTER TABLE `tbl_import_export_settings`
  ADD PRIMARY KEY (`impexp_setting_key`,`impexp_setting_user_id`);

--
-- Indexes for table `tbl_invoices`
--
ALTER TABLE `tbl_invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD UNIQUE KEY `invoice_order_id` (`invoice_order_id`);

--
-- Indexes for table `tbl_invoice_requests`
--
ALTER TABLE `tbl_invoice_requests`
  ADD PRIMARY KEY (`inreq_order_id`);

--
-- Indexes for table `tbl_languages`
--
ALTER TABLE `tbl_languages`
  ADD PRIMARY KEY (`language_id`);

--
-- Indexes for table `tbl_language_labels`
--
ALTER TABLE `tbl_language_labels`
  ADD PRIMARY KEY (`label_id`),
  ADD UNIQUE KEY `label_key` (`label_key`,`label_lang_id`);

--
-- Indexes for table `tbl_late_charges_profile`
--
ALTER TABLE `tbl_late_charges_profile`
  ADD PRIMARY KEY (`lcp_id`);

--
-- Indexes for table `tbl_late_charges_profile_to_product`
--
ALTER TABLE `tbl_late_charges_profile_to_product`
  ADD UNIQUE KEY `lcptp_product_id` (`lcptp_product_id`,`lcptp_user_id`,`lcptp_product_type`);

--
-- Indexes for table `tbl_layout_templates`
--
ALTER TABLE `tbl_layout_templates`
  ADD PRIMARY KEY (`ltemplate_id`);

--
-- Indexes for table `tbl_manual_shipping_api`
--
ALTER TABLE `tbl_manual_shipping_api`
  ADD PRIMARY KEY (`mshipapi_id`);

--
-- Indexes for table `tbl_manual_shipping_api_lang`
--
ALTER TABLE `tbl_manual_shipping_api_lang`
  ADD PRIMARY KEY (`mshipapilang_mshipapi_id`,`mshipapilang_lang_id`);

--
-- Indexes for table `tbl_meta_tags`
--
ALTER TABLE `tbl_meta_tags`
  ADD PRIMARY KEY (`meta_id`),
  ADD UNIQUE KEY `meta_controller` (`meta_controller`,`meta_action`,`meta_record_id`,`meta_subrecord_id`) USING BTREE;

--
-- Indexes for table `tbl_meta_tags_lang`
--
ALTER TABLE `tbl_meta_tags_lang`
  ADD PRIMARY KEY (`metalang_meta_id`,`metalang_lang_id`);

--
-- Indexes for table `tbl_navigations`
--
ALTER TABLE `tbl_navigations`
  ADD PRIMARY KEY (`nav_id`);

--
-- Indexes for table `tbl_navigations_lang`
--
ALTER TABLE `tbl_navigations_lang`
  ADD PRIMARY KEY (`navlang_nav_id`,`navlang_lang_id`);

--
-- Indexes for table `tbl_navigation_links`
--
ALTER TABLE `tbl_navigation_links`
  ADD PRIMARY KEY (`nlink_id`);

--
-- Indexes for table `tbl_navigation_links_lang`
--
ALTER TABLE `tbl_navigation_links_lang`
  ADD PRIMARY KEY (`nlinklang_nlink_id`,`nlinklang_lang_id`);

--
-- Indexes for table `tbl_notifications`
--
ALTER TABLE `tbl_notifications`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `tbl_options`
--
ALTER TABLE `tbl_options`
  ADD PRIMARY KEY (`option_id`),
  ADD UNIQUE KEY `option_identifier` (`option_identifier`);

--
-- Indexes for table `tbl_options_lang`
--
ALTER TABLE `tbl_options_lang`
  ADD PRIMARY KEY (`optionlang_option_id`,`optionlang_lang_id`);

--
-- Indexes for table `tbl_option_values`
--
ALTER TABLE `tbl_option_values`
  ADD PRIMARY KEY (`optionvalue_id`),
  ADD UNIQUE KEY `optionvalue_option_id` (`optionvalue_option_id`,`optionvalue_identifier`);

--
-- Indexes for table `tbl_option_values_lang`
--
ALTER TABLE `tbl_option_values_lang`
  ADD PRIMARY KEY (`optionvaluelang_optionvalue_id`,`optionvaluelang_lang_id`);

--
-- Indexes for table `tbl_orders`
--
ALTER TABLE `tbl_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_order_id` (`order_order_id`),
  ADD KEY `Index` (`order_user_id`);

--
-- Indexes for table `tbl_orders_lang`
--
ALTER TABLE `tbl_orders_lang`
  ADD PRIMARY KEY (`orderlang_order_id`,`orderlang_lang_id`);

--
-- Indexes for table `tbl_orders_status`
--
ALTER TABLE `tbl_orders_status`
  ADD PRIMARY KEY (`orderstatus_id`),
  ADD UNIQUE KEY `orderstatus_identifier` (`orderstatus_identifier`);

--
-- Indexes for table `tbl_orders_status_history`
--
ALTER TABLE `tbl_orders_status_history`
  ADD PRIMARY KEY (`oshistory_id`);

--
-- Indexes for table `tbl_orders_status_lang`
--
ALTER TABLE `tbl_orders_status_lang`
  ADD PRIMARY KEY (`orderstatuslang_orderstatus_id`,`orderstatuslang_lang_id`);

--
-- Indexes for table `tbl_order_cancel_reasons`
--
ALTER TABLE `tbl_order_cancel_reasons`
  ADD PRIMARY KEY (`ocreason_id`),
  ADD UNIQUE KEY `ocreason_identifier` (`ocreason_identifier`);

--
-- Indexes for table `tbl_order_cancel_reasons_lang`
--
ALTER TABLE `tbl_order_cancel_reasons_lang`
  ADD PRIMARY KEY (`ocreasonlang_ocreason_id`,`ocreasonlang_lang_id`);

--
-- Indexes for table `tbl_order_cancel_requests`
--
ALTER TABLE `tbl_order_cancel_requests`
  ADD PRIMARY KEY (`ocrequest_id`);

--
-- Indexes for table `tbl_order_cancel_rules`
--
ALTER TABLE `tbl_order_cancel_rules`
  ADD PRIMARY KEY (`ocrule_id`);

--
-- Indexes for table `tbl_order_extras`
--
ALTER TABLE `tbl_order_extras`
  ADD PRIMARY KEY (`oextra_order_id`);

--
-- Indexes for table `tbl_order_payments`
--
ALTER TABLE `tbl_order_payments`
  ADD PRIMARY KEY (`opayment_id`),
  ADD KEY `op_order_id` (`opayment_order_id`),
  ADD KEY `op_gateway_txn_id` (`opayment_gateway_txn_id`);

--
-- Indexes for table `tbl_order_products`
--
ALTER TABLE `tbl_order_products`
  ADD PRIMARY KEY (`op_id`),
  ADD UNIQUE KEY `op_invoice_number` (`op_invoice_number`);

--
-- Indexes for table `tbl_order_products_data`
--
ALTER TABLE `tbl_order_products_data`
  ADD PRIMARY KEY (`opd_op_id`);

--
-- Indexes for table `tbl_order_products_lang`
--
ALTER TABLE `tbl_order_products_lang`
  ADD PRIMARY KEY (`oplang_op_id`,`oplang_lang_id`);

--
-- Indexes for table `tbl_order_product_charges`
--
ALTER TABLE `tbl_order_product_charges`
  ADD PRIMARY KEY (`opcharge_id`),
  ADD KEY `opcharge_op_id_2` (`opcharge_op_id`);

--
-- Indexes for table `tbl_order_product_charges_lang`
--
ALTER TABLE `tbl_order_product_charges_lang`
  ADD PRIMARY KEY (`opchargelang_opcharge_id`,`opchargelang_lang_id`);

--
-- Indexes for table `tbl_order_product_digital_download_links`
--
ALTER TABLE `tbl_order_product_digital_download_links`
  ADD PRIMARY KEY (`opddl_link_id`);

--
-- Indexes for table `tbl_order_product_settings`
--
ALTER TABLE `tbl_order_product_settings`
  ADD UNIQUE KEY `opsetting_op_id` (`opsetting_op_id`);

--
-- Indexes for table `tbl_order_product_shipment`
--
ALTER TABLE `tbl_order_product_shipment`
  ADD PRIMARY KEY (`opship_op_id`);

--
-- Indexes for table `tbl_order_product_shipping`
--
ALTER TABLE `tbl_order_product_shipping`
  ADD UNIQUE KEY `opshipping_op_id` (`opshipping_op_id`);

--
-- Indexes for table `tbl_order_product_specifics`
--
ALTER TABLE `tbl_order_product_specifics`
  ADD PRIMARY KEY (`ops_op_id`);

--
-- Indexes for table `tbl_order_product_to_shipping_users`
--
ALTER TABLE `tbl_order_product_to_shipping_users`
  ADD PRIMARY KEY (`optsu_op_id`,`optsu_user_id`);

--
-- Indexes for table `tbl_order_product_to_verification_fld`
--
ALTER TABLE `tbl_order_product_to_verification_fld`
  ADD PRIMARY KEY (`optvf_op_id`,`optvf_ovd_vflds_id`),
  ADD UNIQUE KEY `optvf_op_id` (`optvf_op_id`,`optvf_ovd_vflds_id`);

--
-- Indexes for table `tbl_order_prod_charges_logs`
--
ALTER TABLE `tbl_order_prod_charges_logs`
  ADD PRIMARY KEY (`opchargelog_id`);

--
-- Indexes for table `tbl_order_return_reasons`
--
ALTER TABLE `tbl_order_return_reasons`
  ADD PRIMARY KEY (`orreason_id`),
  ADD UNIQUE KEY `orreason_identifier` (`orreason_identifier`);

--
-- Indexes for table `tbl_order_return_reasons_lang`
--
ALTER TABLE `tbl_order_return_reasons_lang`
  ADD PRIMARY KEY (`orreasonlang_orreason_id`,`orreasonlang_lang_id`);

--
-- Indexes for table `tbl_order_return_requests`
--
ALTER TABLE `tbl_order_return_requests`
  ADD PRIMARY KEY (`orrequest_id`),
  ADD KEY `orrequest_user_id` (`orrequest_user_id`);

--
-- Indexes for table `tbl_order_return_request_messages`
--
ALTER TABLE `tbl_order_return_request_messages`
  ADD PRIMARY KEY (`orrmsg_id`);

--
-- Indexes for table `tbl_order_seller_subscriptions`
--
ALTER TABLE `tbl_order_seller_subscriptions`
  ADD PRIMARY KEY (`ossubs_id`),
  ADD KEY `ossubs_order_id` (`ossubs_order_id`),
  ADD KEY `ossubs_invoice_number` (`ossubs_invoice_number`);

--
-- Indexes for table `tbl_order_seller_subscriptions_lang`
--
ALTER TABLE `tbl_order_seller_subscriptions_lang`
  ADD PRIMARY KEY (`ossubslang_ossubs_id`,`ossubslang_lang_id`),
  ADD UNIQUE KEY `ossubslang_ossubs_id` (`ossubslang_ossubs_id`,`ossubslang_lang_id`,`ossubslang_order_id`);

--
-- Indexes for table `tbl_order_user_address`
--
ALTER TABLE `tbl_order_user_address`
  ADD PRIMARY KEY (`oua_order_id`,`oua_op_id`,`oua_type`);

--
-- Indexes for table `tbl_order_verification_data`
--
ALTER TABLE `tbl_order_verification_data`
  ADD PRIMARY KEY (`ovd_order_id`,`ovd_vfld_id`),
  ADD UNIQUE KEY `ovd_order_id` (`ovd_order_id`,`ovd_vfld_id`);

--
-- Indexes for table `tbl_plugins`
--
ALTER TABLE `tbl_plugins`
  ADD PRIMARY KEY (`plugin_id`),
  ADD UNIQUE KEY `plugin_identifier` (`plugin_identifier`),
  ADD UNIQUE KEY `plugin_code` (`plugin_code`);

--
-- Indexes for table `tbl_plugins_lang`
--
ALTER TABLE `tbl_plugins_lang`
  ADD PRIMARY KEY (`pluginlang_plugin_id`,`pluginlang_lang_id`);

--
-- Indexes for table `tbl_plugin_settings`
--
ALTER TABLE `tbl_plugin_settings`
  ADD PRIMARY KEY (`pluginsetting_plugin_id`,`pluginsetting_key`);

--
-- Indexes for table `tbl_policy_points`
--
ALTER TABLE `tbl_policy_points`
  ADD PRIMARY KEY (`ppoint_id`),
  ADD UNIQUE KEY `ppoint_identifier` (`ppoint_identifier`);

--
-- Indexes for table `tbl_policy_points_lang`
--
ALTER TABLE `tbl_policy_points_lang`
  ADD PRIMARY KEY (`ppointlang_ppoint_id`,`ppointlang_lang_id`);

--
-- Indexes for table `tbl_products`
--
ALTER TABLE `tbl_products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `product_identifier` (`product_identifier`),
  ADD KEY `product_seller_id` (`product_seller_id`),
  ADD KEY `product_brand_id` (`product_brand_id`);

--
-- Indexes for table `tbl_products_browsing_history`
--
ALTER TABLE `tbl_products_browsing_history`
  ADD PRIMARY KEY (`pbhistory_id`),
  ADD UNIQUE KEY `pbhistory_sessionid` (`pbhistory_sessionid`,`pbhistory_selprod_code`,`pbhistory_swsetting_key`);

--
-- Indexes for table `tbl_products_lang`
--
ALTER TABLE `tbl_products_lang`
  ADD UNIQUE KEY `productlang_product_id` (`productlang_product_id`,`productlang_lang_id`);

--
-- Indexes for table `tbl_products_min_price`
--
ALTER TABLE `tbl_products_min_price`
  ADD UNIQUE KEY `pmp_product_id` (`pmp_product_id`,`pmp_price_type`);

--
-- Indexes for table `tbl_products_shipped_by_seller`
--
ALTER TABLE `tbl_products_shipped_by_seller`
  ADD PRIMARY KEY (`psbs_product_id`,`psbs_user_id`);

--
-- Indexes for table `tbl_products_shipping`
--
ALTER TABLE `tbl_products_shipping`
  ADD PRIMARY KEY (`ps_product_id`,`ps_user_id`);

--
-- Indexes for table `tbl_products_temp_ids`
--
ALTER TABLE `tbl_products_temp_ids`
  ADD PRIMARY KEY (`pti_product_temp_id`,`pti_user_id`);

--
-- Indexes for table `tbl_product_categories`
--
ALTER TABLE `tbl_product_categories`
  ADD PRIMARY KEY (`prodcat_id`),
  ADD UNIQUE KEY `prodcat_identifier` (`prodcat_identifier`),
  ADD KEY `prodcat_parent` (`prodcat_parent`),
  ADD KEY `prodcat_code` (`prodcat_code`);

--
-- Indexes for table `tbl_product_categories_lang`
--
ALTER TABLE `tbl_product_categories_lang`
  ADD UNIQUE KEY `prodcatlang_prodcat_id` (`prodcatlang_prodcat_id`,`prodcatlang_lang_id`);

--
-- Indexes for table `tbl_product_category_relations`
--
ALTER TABLE `tbl_product_category_relations`
  ADD PRIMARY KEY (`pcr_prodcat_id`,`pcr_parent_id`);

--
-- Indexes for table `tbl_product_duration_discount`
--
ALTER TABLE `tbl_product_duration_discount`
  ADD PRIMARY KEY (`produr_id`);

--
-- Indexes for table `tbl_product_groups`
--
ALTER TABLE `tbl_product_groups`
  ADD PRIMARY KEY (`prodgroup_id`);

--
-- Indexes for table `tbl_product_groups_lang`
--
ALTER TABLE `tbl_product_groups_lang`
  ADD PRIMARY KEY (`prodgrouplang_prodgroup_id`,`prodgrouplang_lang_id`);

--
-- Indexes for table `tbl_product_numeric_attributes`
--
ALTER TABLE `tbl_product_numeric_attributes`
  ADD PRIMARY KEY (`prodnumattr_product_id`,`prodnumattr_attrgrp_id`) USING BTREE;

--
-- Indexes for table `tbl_product_product_recommendation`
--
ALTER TABLE `tbl_product_product_recommendation`
  ADD PRIMARY KEY (`ppr_viewing_product_id`,`ppr_recommended_product_id`);

--
-- Indexes for table `tbl_product_requests`
--
ALTER TABLE `tbl_product_requests`
  ADD PRIMARY KEY (`preq_id`);

--
-- Indexes for table `tbl_product_requests_lang`
--
ALTER TABLE `tbl_product_requests_lang`
  ADD PRIMARY KEY (`preqlang_preq_id`,`preqlang_lang_id`);

--
-- Indexes for table `tbl_product_saved_search`
--
ALTER TABLE `tbl_product_saved_search`
  ADD PRIMARY KEY (`pssearch_id`);

--
-- Indexes for table `tbl_product_special_prices`
--
ALTER TABLE `tbl_product_special_prices`
  ADD PRIMARY KEY (`splprice_id`),
  ADD KEY `price_selprod_id` (`splprice_selprod_id`);

--
-- Indexes for table `tbl_product_specifications`
--
ALTER TABLE `tbl_product_specifications`
  ADD PRIMARY KEY (`prodspec_id`),
  ADD UNIQUE KEY `prodspec_identifier` (`prodspec_identifier`,`prodspec_product_id`),
  ADD KEY `prodspec_product_id` (`prodspec_product_id`);

--
-- Indexes for table `tbl_product_specifications_lang`
--
ALTER TABLE `tbl_product_specifications_lang`
  ADD PRIMARY KEY (`prodspeclang_prodspec_id`,`prodspeclang_lang_id`);

--
-- Indexes for table `tbl_product_specifics`
--
ALTER TABLE `tbl_product_specifics`
  ADD PRIMARY KEY (`ps_product_id`);

--
-- Indexes for table `tbl_product_stock_hold`
--
ALTER TABLE `tbl_product_stock_hold`
  ADD PRIMARY KEY (`pshold_id`),
  ADD UNIQUE KEY `pshold_selprod_id` (`pshold_selprod_id`,`pshold_user_id`,`pshold_prodgroup_id`);

--
-- Indexes for table `tbl_product_text_attributes`
--
ALTER TABLE `tbl_product_text_attributes`
  ADD PRIMARY KEY (`prodtxtattr_product_id`,`prodtxtattr_lang_id`,`prodtxtattr_attrgrp_id`) USING BTREE;

--
-- Indexes for table `tbl_product_to_category`
--
ALTER TABLE `tbl_product_to_category`
  ADD PRIMARY KEY (`ptc_product_id`,`ptc_prodcat_id`),
  ADD KEY `ptc_product_id` (`ptc_product_id`),
  ADD KEY `ptc_prodcat_id` (`ptc_prodcat_id`);

--
-- Indexes for table `tbl_product_to_groups`
--
ALTER TABLE `tbl_product_to_groups`
  ADD PRIMARY KEY (`ptg_prodgroup_id`,`ptg_selprod_id`);

--
-- Indexes for table `tbl_product_to_options`
--
ALTER TABLE `tbl_product_to_options`
  ADD PRIMARY KEY (`prodoption_product_id`,`prodoption_option_id`),
  ADD KEY `prodoption_product_id` (`prodoption_product_id`);

--
-- Indexes for table `tbl_product_to_tags`
--
ALTER TABLE `tbl_product_to_tags`
  ADD PRIMARY KEY (`ptt_product_id`,`ptt_tag_id`);

--
-- Indexes for table `tbl_product_to_tax`
--
ALTER TABLE `tbl_product_to_tax`
  ADD PRIMARY KEY (`ptt_product_id`,`ptt_seller_user_id`,`ptt_type`),
  ADD KEY `ptstax_product_id` (`ptt_product_id`);

--
-- Indexes for table `tbl_product_to_verification_field`
--
ALTER TABLE `tbl_product_to_verification_field`
  ADD UNIQUE KEY `ptvf_product_id` (`ptvf_product_id`,`ptvf_vflds_id`,`ptvf_user_id`);

--
-- Indexes for table `tbl_product_volume_discount`
--
ALTER TABLE `tbl_product_volume_discount`
  ADD PRIMARY KEY (`voldiscount_id`),
  ADD UNIQUE KEY `voldiscount_selprod_id` (`voldiscount_selprod_id`,`voldiscount_min_qty`);

--
-- Indexes for table `tbl_prod_unavailable_rental_durations`
--
ALTER TABLE `tbl_prod_unavailable_rental_durations`
  ADD PRIMARY KEY (`pu_id`);

--
-- Indexes for table `tbl_promotions`
--
ALTER TABLE `tbl_promotions`
  ADD PRIMARY KEY (`promotion_id`);

--
-- Indexes for table `tbl_promotions_charges`
--
ALTER TABLE `tbl_promotions_charges`
  ADD PRIMARY KEY (`pcharge_id`);

--
-- Indexes for table `tbl_promotions_clicks`
--
ALTER TABLE `tbl_promotions_clicks`
  ADD PRIMARY KEY (`pclick_id`),
  ADD UNIQUE KEY `pclick_promotion_id` (`pclick_promotion_id`,`pclick_ip`,`pclick_session_id`);

--
-- Indexes for table `tbl_promotions_lang`
--
ALTER TABLE `tbl_promotions_lang`
  ADD UNIQUE KEY `promotionlang_promotion_id` (`promotionlang_promotion_id`,`promotionlang_lang_id`);

--
-- Indexes for table `tbl_promotions_logs`
--
ALTER TABLE `tbl_promotions_logs`
  ADD PRIMARY KEY (`plog_promotion_id`,`plog_date`);

--
-- Indexes for table `tbl_promotion_item_charges`
--
ALTER TABLE `tbl_promotion_item_charges`
  ADD PRIMARY KEY (`picharge_id`);

--
-- Indexes for table `tbl_push_notifications`
--
ALTER TABLE `tbl_push_notifications`
  ADD PRIMARY KEY (`pnotification_id`);

--
-- Indexes for table `tbl_push_notification_to_users`
--
ALTER TABLE `tbl_push_notification_to_users`
  ADD PRIMARY KEY (`pntu_pnotification_id`,`pntu_user_id`);

--
-- Indexes for table `tbl_recommendation_activity_browsing`
--
ALTER TABLE `tbl_recommendation_activity_browsing`
  ADD PRIMARY KEY (`rab_session_id`,`rab_user_id`,`rab_record_id`,`rab_record_type`,`rab_weightage_key`);

--
-- Indexes for table `tbl_related_products`
--
ALTER TABLE `tbl_related_products`
  ADD PRIMARY KEY (`related_sellerproduct_id`,`related_recommend_sellerproduct_id`),
  ADD KEY `related_sellerproduct_id` (`related_sellerproduct_id`);

--
-- Indexes for table `tbl_rental_order_status_data`
--
ALTER TABLE `tbl_rental_order_status_data`
  ADD UNIQUE KEY `rentop_op_id` (`rentop_op_id`);

--
-- Indexes for table `tbl_rental_product_booked_stock`
--
ALTER TABLE `tbl_rental_product_booked_stock`
  ADD UNIQUE KEY `date` (`pbs_selprod_id`,`pbs_date`);

--
-- Indexes for table `tbl_rental_product_stock_hold`
--
ALTER TABLE `tbl_rental_product_stock_hold`
  ADD PRIMARY KEY (`rentpshold_id`),
  ADD UNIQUE KEY `rentpshold_selprod_id` (`rentpshold_selprod_id`,`rentpshold_user_id`,`rentpshold_rental_start_date`,`rentpshold_rental_end_date`);

--
-- Indexes for table `tbl_report_reasons`
--
ALTER TABLE `tbl_report_reasons`
  ADD PRIMARY KEY (`reportreason_id`),
  ADD UNIQUE KEY `reportreason_identifier` (`reportreason_identifier`);

--
-- Indexes for table `tbl_report_reasons_lang`
--
ALTER TABLE `tbl_report_reasons_lang`
  ADD PRIMARY KEY (`reportreasonlang_reportreason_id`,`reportreasonlang_lang_id`);

--
-- Indexes for table `tbl_request_for_quotes`
--
ALTER TABLE `tbl_request_for_quotes`
  ADD PRIMARY KEY (`rfq_id`);

--
-- Indexes for table `tbl_rewards_on_purchase`
--
ALTER TABLE `tbl_rewards_on_purchase`
  ADD PRIMARY KEY (`rop_id`);

--
-- Indexes for table `tbl_rfq_attached_services`
--
ALTER TABLE `tbl_rfq_attached_services`
  ADD PRIMARY KEY (`rfqattser_rfq_id`,`rfqattser_selprod_id`);

--
-- Indexes for table `tbl_search_items`
--
ALTER TABLE `tbl_search_items`
  ADD PRIMARY KEY (`searchitem_id`),
  ADD UNIQUE KEY `searchitem_keyword` (`searchitem_keyword`,`searchitem_date`);

--
-- Indexes for table `tbl_seller_brand_requests`
--
ALTER TABLE `tbl_seller_brand_requests`
  ADD PRIMARY KEY (`sbrandreq_id`);

--
-- Indexes for table `tbl_seller_brand_requests_lang`
--
ALTER TABLE `tbl_seller_brand_requests_lang`
  ADD UNIQUE KEY `sbrandreqlang_sbrandreq_id` (`sbrandreqlang_sbrandreq_id`,`sbrandreqlang_lang_id`);

--
-- Indexes for table `tbl_seller_catalog_requests`
--
ALTER TABLE `tbl_seller_catalog_requests`
  ADD PRIMARY KEY (`scatrequest_id`);

--
-- Indexes for table `tbl_seller_packages`
--
ALTER TABLE `tbl_seller_packages`
  ADD PRIMARY KEY (`spackage_id`),
  ADD UNIQUE KEY `Package Identifier` (`spackage_identifier`);

--
-- Indexes for table `tbl_seller_packages_lang`
--
ALTER TABLE `tbl_seller_packages_lang`
  ADD PRIMARY KEY (`spackagelang_spackage_id`,`spackagelang_lang_id`);

--
-- Indexes for table `tbl_seller_packages_plan`
--
ALTER TABLE `tbl_seller_packages_plan`
  ADD PRIMARY KEY (`spplan_id`);

--
-- Indexes for table `tbl_seller_products`
--
ALTER TABLE `tbl_seller_products`
  ADD PRIMARY KEY (`selprod_id`),
  ADD KEY `selprod_product_id` (`selprod_product_id`),
  ADD KEY `selprod_user_id` (`selprod_user_id`);

--
-- Indexes for table `tbl_seller_products_addon`
--
ALTER TABLE `tbl_seller_products_addon`
  ADD PRIMARY KEY (`spa_seller_prod_id`,`spa_addon_product_id`);

--
-- Indexes for table `tbl_seller_products_data`
--
ALTER TABLE `tbl_seller_products_data`
  ADD PRIMARY KEY (`sprodata_selprod_id`);

--
-- Indexes for table `tbl_seller_products_lang`
--
ALTER TABLE `tbl_seller_products_lang`
  ADD PRIMARY KEY (`selprodlang_selprod_id`,`selprodlang_lang_id`);

--
-- Indexes for table `tbl_seller_products_temp_ids`
--
ALTER TABLE `tbl_seller_products_temp_ids`
  ADD PRIMARY KEY (`spti_selprod_temp_id`,`spti_user_id`);

--
-- Indexes for table `tbl_seller_products_to_pickup_address`
--
ALTER TABLE `tbl_seller_products_to_pickup_address`
  ADD PRIMARY KEY (`sptpa_selprod_id`,`sptpa_addr_id`),
  ADD UNIQUE KEY `sptpa_selprod_id` (`sptpa_selprod_id`,`sptpa_addr_id`);

--
-- Indexes for table `tbl_seller_product_options`
--
ALTER TABLE `tbl_seller_product_options`
  ADD PRIMARY KEY (`selprodoption_selprod_id`,`selprodoption_option_id`),
  ADD KEY `selprodoption_selprod_id` (`selprodoption_selprod_id`);

--
-- Indexes for table `tbl_seller_product_policies`
--
ALTER TABLE `tbl_seller_product_policies`
  ADD PRIMARY KEY (`sppolicy_selprod_id`,`sppolicy_ppoint_id`);

--
-- Indexes for table `tbl_seller_product_rating`
--
ALTER TABLE `tbl_seller_product_rating`
  ADD PRIMARY KEY (`sprating_spreview_id`,`sprating_rating_type`,`sprating_rating`);

--
-- Indexes for table `tbl_seller_product_reviews`
--
ALTER TABLE `tbl_seller_product_reviews`
  ADD PRIMARY KEY (`spreview_id`),
  ADD UNIQUE KEY `spreview_order_id` (`spreview_order_id`,`spreview_selprod_id`);

--
-- Indexes for table `tbl_seller_product_reviews_abuse`
--
ALTER TABLE `tbl_seller_product_reviews_abuse`
  ADD PRIMARY KEY (`spra_spreview_id`,`spra_user_id`);

--
-- Indexes for table `tbl_seller_product_reviews_helpful`
--
ALTER TABLE `tbl_seller_product_reviews_helpful`
  ADD PRIMARY KEY (`sprh_spreview_id`,`sprh_user_id`);

--
-- Indexes for table `tbl_seller_product_specifics`
--
ALTER TABLE `tbl_seller_product_specifics`
  ADD PRIMARY KEY (`sps_selprod_id`,`selprod_specific_type`);

--
-- Indexes for table `tbl_seller_product_to_membership`
--
ALTER TABLE `tbl_seller_product_to_membership`
  ADD UNIQUE KEY `primary_key` (`spm_selprod_id`,`spm_membership_id`);

--
-- Indexes for table `tbl_shippingapi_settings`
--
ALTER TABLE `tbl_shippingapi_settings`
  ADD PRIMARY KEY (`shipsetting_shippingapi_id`,`shipsetting_key`);

--
-- Indexes for table `tbl_shipping_apis`
--
ALTER TABLE `tbl_shipping_apis`
  ADD PRIMARY KEY (`shippingapi_id`),
  ADD UNIQUE KEY `shippingapi_identifier` (`shippingapi_identifier`);

--
-- Indexes for table `tbl_shipping_apis_lang`
--
ALTER TABLE `tbl_shipping_apis_lang`
  ADD PRIMARY KEY (`shippingapilang_shippingapi_id`,`shippingapilang_lang_id`);

--
-- Indexes for table `tbl_shipping_company`
--
ALTER TABLE `tbl_shipping_company`
  ADD PRIMARY KEY (`scompany_id`),
  ADD UNIQUE KEY `scompany_identifier` (`scompany_identifier`);

--
-- Indexes for table `tbl_shipping_company_lang`
--
ALTER TABLE `tbl_shipping_company_lang`
  ADD PRIMARY KEY (`scompanylang_scompany_id`,`scompanylang_lang_id`);

--
-- Indexes for table `tbl_shipping_durations`
--
ALTER TABLE `tbl_shipping_durations`
  ADD PRIMARY KEY (`sduration_id`),
  ADD UNIQUE KEY `sduration_identifier` (`sduration_identifier`);

--
-- Indexes for table `tbl_shipping_durations_lang`
--
ALTER TABLE `tbl_shipping_durations_lang`
  ADD PRIMARY KEY (`sdurationlang_sduration_id`,`sdurationlang_lang_id`);

--
-- Indexes for table `tbl_shipping_locations`
--
ALTER TABLE `tbl_shipping_locations`
  ADD UNIQUE KEY `shiploc_shipzone_id` (`shiploc_shipzone_id`,`shiploc_zone_id`,`shiploc_country_id`,`shiploc_state_id`);

--
-- Indexes for table `tbl_shipping_packages`
--
ALTER TABLE `tbl_shipping_packages`
  ADD PRIMARY KEY (`shippack_id`),
  ADD UNIQUE KEY `shippack_name` (`shippack_name`);

--
-- Indexes for table `tbl_shipping_profile`
--
ALTER TABLE `tbl_shipping_profile`
  ADD PRIMARY KEY (`shipprofile_id`),
  ADD UNIQUE KEY `shipprofile_name` (`shipprofile_identifier`,`shipprofile_user_id`);

--
-- Indexes for table `tbl_shipping_profile_lang`
--
ALTER TABLE `tbl_shipping_profile_lang`
  ADD UNIQUE KEY `shipprofilelang_shipprofile_id` (`shipprofilelang_shipprofile_id`,`shipprofilelang_lang_id`);

--
-- Indexes for table `tbl_shipping_profile_products`
--
ALTER TABLE `tbl_shipping_profile_products`
  ADD PRIMARY KEY (`shippro_product_id`,`shippro_user_id`);

--
-- Indexes for table `tbl_shipping_profile_zones`
--
ALTER TABLE `tbl_shipping_profile_zones`
  ADD PRIMARY KEY (`shipprozone_id`),
  ADD UNIQUE KEY `shipprozone_shipzone_id` (`shipprozone_shipzone_id`,`shipprozone_shipprofile_id`);

--
-- Indexes for table `tbl_shipping_rates`
--
ALTER TABLE `tbl_shipping_rates`
  ADD PRIMARY KEY (`shiprate_id`);

--
-- Indexes for table `tbl_shipping_rates_lang`
--
ALTER TABLE `tbl_shipping_rates_lang`
  ADD PRIMARY KEY (`shipratelang_shiprate_id`,`shipratelang_lang_id`),
  ADD UNIQUE KEY `ratelang_lang_id` (`shipratelang_lang_id`,`shiprate_name`);

--
-- Indexes for table `tbl_shipping_zone`
--
ALTER TABLE `tbl_shipping_zone`
  ADD PRIMARY KEY (`shipzone_id`),
  ADD UNIQUE KEY `shipzone_name` (`shipzone_name`,`shipzone_user_id`);

--
-- Indexes for table `tbl_shops`
--
ALTER TABLE `tbl_shops`
  ADD PRIMARY KEY (`shop_id`),
  ADD UNIQUE KEY `shop_user_id` (`shop_user_id`);

--
-- Indexes for table `tbl_shops_lang`
--
ALTER TABLE `tbl_shops_lang`
  ADD PRIMARY KEY (`shoplang_shop_id`,`shoplang_lang_id`);

--
-- Indexes for table `tbl_shops_to_theme`
--
ALTER TABLE `tbl_shops_to_theme`
  ADD PRIMARY KEY (`stt_id`),
  ADD UNIQUE KEY `stt_shop_id` (`stt_shop_id`);

--
-- Indexes for table `tbl_shop_collections`
--
ALTER TABLE `tbl_shop_collections`
  ADD PRIMARY KEY (`scollection_id`),
  ADD UNIQUE KEY `scollection_identifier` (`scollection_identifier`);

--
-- Indexes for table `tbl_shop_collections_lang`
--
ALTER TABLE `tbl_shop_collections_lang`
  ADD PRIMARY KEY (`scollectionlang_scollection_id`,`scollectionlang_lang_id`);

--
-- Indexes for table `tbl_shop_collection_products`
--
ALTER TABLE `tbl_shop_collection_products`
  ADD PRIMARY KEY (`scp_scollection_id`,`scp_selprod_id`),
  ADD KEY `scp_shop_id` (`scp_scollection_id`);

--
-- Indexes for table `tbl_shop_reports`
--
ALTER TABLE `tbl_shop_reports`
  ADD PRIMARY KEY (`sreport_id`),
  ADD UNIQUE KEY `sreport_shop_id` (`sreport_shop_id`,`sreport_user_id`);

--
-- Indexes for table `tbl_shop_specifics`
--
ALTER TABLE `tbl_shop_specifics`
  ADD PRIMARY KEY (`ss_shop_id`);

--
-- Indexes for table `tbl_slides`
--
ALTER TABLE `tbl_slides`
  ADD PRIMARY KEY (`slide_id`);

--
-- Indexes for table `tbl_slides_lang`
--
ALTER TABLE `tbl_slides_lang`
  ADD PRIMARY KEY (`slidelang_slide_id`,`slidelang_lang_id`);

--
-- Indexes for table `tbl_smart_log_actions`
--
ALTER TABLE `tbl_smart_log_actions`
  ADD PRIMARY KEY (`slog_id`);

--
-- Indexes for table `tbl_smart_remommended_products`
--
ALTER TABLE `tbl_smart_remommended_products`
  ADD PRIMARY KEY (`tsrp_source_product_id`,`tsrp_recommended_product_id`);

--
-- Indexes for table `tbl_smart_user_activity_browsing`
--
ALTER TABLE `tbl_smart_user_activity_browsing`
  ADD PRIMARY KEY (`uab_id`),
  ADD UNIQUE KEY `uab_user_id` (`uab_user_id`,`uab_record_id`,`uab_record_type`,`uab_sub_record_code`);

--
-- Indexes for table `tbl_smart_weightage_settings`
--
ALTER TABLE `tbl_smart_weightage_settings`
  ADD PRIMARY KEY (`swsetting_key`),
  ADD UNIQUE KEY `swsetting_key` (`swsetting_name`);

--
-- Indexes for table `tbl_sms_archives`
--
ALTER TABLE `tbl_sms_archives`
  ADD PRIMARY KEY (`smsarchive_id`);

--
-- Indexes for table `tbl_sms_templates`
--
ALTER TABLE `tbl_sms_templates`
  ADD PRIMARY KEY (`stpl_code`,`stpl_lang_id`);

--
-- Indexes for table `tbl_social_platforms`
--
ALTER TABLE `tbl_social_platforms`
  ADD PRIMARY KEY (`splatform_id`);

--
-- Indexes for table `tbl_social_platforms_lang`
--
ALTER TABLE `tbl_social_platforms_lang`
  ADD PRIMARY KEY (`splatformlang_splatform_id`,`splatformlang_lang_id`);

--
-- Indexes for table `tbl_states`
--
ALTER TABLE `tbl_states`
  ADD PRIMARY KEY (`state_id`),
  ADD UNIQUE KEY `state_country_id` (`state_country_id`,`state_identifier`);

--
-- Indexes for table `tbl_states_lang`
--
ALTER TABLE `tbl_states_lang`
  ADD PRIMARY KEY (`statelang_state_id`,`statelang_lang_id`);

--
-- Indexes for table `tbl_system_logs`
--
ALTER TABLE `tbl_system_logs`
  ADD PRIMARY KEY (`slog_id`);

--
-- Indexes for table `tbl_tags`
--
ALTER TABLE `tbl_tags`
  ADD PRIMARY KEY (`tag_id`),
  ADD UNIQUE KEY `tag_identifier` (`tag_identifier`);

--
-- Indexes for table `tbl_tags_lang`
--
ALTER TABLE `tbl_tags_lang`
  ADD PRIMARY KEY (`taglang_tag_id`,`taglang_lang_id`);

--
-- Indexes for table `tbl_tag_product_recommendation`
--
ALTER TABLE `tbl_tag_product_recommendation`
  ADD PRIMARY KEY (`tpr_tag_id`,`tpr_product_id`);

--
-- Indexes for table `tbl_tax_categories`
--
ALTER TABLE `tbl_tax_categories`
  ADD PRIMARY KEY (`taxcat_id`),
  ADD UNIQUE KEY `taxcat_identifier` (`taxcat_identifier`,`taxcat_plugin_id`);

--
-- Indexes for table `tbl_tax_categories_lang`
--
ALTER TABLE `tbl_tax_categories_lang`
  ADD PRIMARY KEY (`taxcatlang_taxcat_id`,`taxcatlang_lang_id`);

--
-- Indexes for table `tbl_tax_rules`
--
ALTER TABLE `tbl_tax_rules`
  ADD PRIMARY KEY (`taxrule_id`);

--
-- Indexes for table `tbl_tax_rule_details`
--
ALTER TABLE `tbl_tax_rule_details`
  ADD UNIQUE KEY `taxruledet_taxrule_id` (`taxruledet_taxrule_id`,`taxruledet_taxstr_id`,`taxruledet_user_id`);

--
-- Indexes for table `tbl_tax_rule_locations`
--
ALTER TABLE `tbl_tax_rule_locations`
  ADD UNIQUE KEY `taxruleloc_taxcat_id` (`taxruleloc_taxcat_id`,`taxruleloc_from_country_id`,`taxruleloc_from_state_id`,`taxruleloc_to_country_id`,`taxruleloc_to_state_id`,`taxruleloc_type`) USING BTREE;

--
-- Indexes for table `tbl_tax_rule_rates`
--
ALTER TABLE `tbl_tax_rule_rates`
  ADD PRIMARY KEY (`trr_taxrule_id`,`trr_user_id`);

--
-- Indexes for table `tbl_tax_structure`
--
ALTER TABLE `tbl_tax_structure`
  ADD PRIMARY KEY (`taxstr_id`);

--
-- Indexes for table `tbl_tax_structure_lang`
--
ALTER TABLE `tbl_tax_structure_lang`
  ADD PRIMARY KEY (`taxstrlang_taxstr_id`,`taxstrlang_lang_id`);

--
-- Indexes for table `tbl_testimonials`
--
ALTER TABLE `tbl_testimonials`
  ADD PRIMARY KEY (`testimonial_id`);

--
-- Indexes for table `tbl_testimonials_lang`
--
ALTER TABLE `tbl_testimonials_lang`
  ADD PRIMARY KEY (`testimoniallang_testimonial_id`,`testimoniallang_lang_id`);

--
-- Indexes for table `tbl_theme`
--
ALTER TABLE `tbl_theme`
  ADD PRIMARY KEY (`theme_id`);

--
-- Indexes for table `tbl_threads`
--
ALTER TABLE `tbl_threads`
  ADD PRIMARY KEY (`thread_id`);

--
-- Indexes for table `tbl_thread_messages`
--
ALTER TABLE `tbl_thread_messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `tbl_time_slots`
--
ALTER TABLE `tbl_time_slots`
  ADD PRIMARY KEY (`tslot_id`),
  ADD UNIQUE KEY `tslot_type` (`tslot_type`,`tslot_record_id`,`tslot_subrecord_id`,`tslot_day`,`tslot_from_time`,`tslot_to_time`);

--
-- Indexes for table `tbl_tracking_courier_code_relation`
--
ALTER TABLE `tbl_tracking_courier_code_relation`
  ADD UNIQUE KEY `UNIQUE` (`tccr_shipapi_plugin_id`,`tccr_shipapi_courier_code`,`tccr_tracking_plugin_id`);

--
-- Indexes for table `tbl_transactions_failure_log`
--
ALTER TABLE `tbl_transactions_failure_log`
  ADD PRIMARY KEY (`txnlog_id`);

--
-- Indexes for table `tbl_upc_codes`
--
ALTER TABLE `tbl_upc_codes`
  ADD PRIMARY KEY (`upc_code_id`);

--
-- Indexes for table `tbl_updated_record_log`
--
ALTER TABLE `tbl_updated_record_log`
  ADD PRIMARY KEY (`urlog_id`),
  ADD UNIQUE KEY `urlog_record_id` (`urlog_record_id`,`urlog_subrecord_id`,`urlog_record_type`);

--
-- Indexes for table `tbl_upsell_products`
--
ALTER TABLE `tbl_upsell_products`
  ADD PRIMARY KEY (`upsell_sellerproduct_id`,`upsell_recommend_sellerproduct_id`),
  ADD KEY `upsell_sellerproduct_id` (`upsell_sellerproduct_id`);

--
-- Indexes for table `tbl_url_rewrite`
--
ALTER TABLE `tbl_url_rewrite`
  ADD PRIMARY KEY (`urlrewrite_id`),
  ADD UNIQUE KEY `urlrewrite_original` (`urlrewrite_original`,`urlrewrite_lang_id`),
  ADD UNIQUE KEY `urlrewrite_custom` (`urlrewrite_custom`,`urlrewrite_lang_id`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_referral_code` (`user_referral_code`),
  ADD UNIQUE KEY `user_dial_code` (`user_dial_code`,`user_phone`);

--
-- Indexes for table `tbl_user_auth_token`
--
ALTER TABLE `tbl_user_auth_token`
  ADD PRIMARY KEY (`uauth_token`),
  ADD KEY `urm_user_id` (`uauth_user_id`);

--
-- Indexes for table `tbl_user_bank_details`
--
ALTER TABLE `tbl_user_bank_details`
  ADD PRIMARY KEY (`ub_user_id`);

--
-- Indexes for table `tbl_user_cart`
--
ALTER TABLE `tbl_user_cart`
  ADD UNIQUE KEY `usercart_user_id` (`usercart_user_id`,`usercart_type`);

--
-- Indexes for table `tbl_user_credentials`
--
ALTER TABLE `tbl_user_credentials`
  ADD PRIMARY KEY (`credential_user_id`),
  ADD UNIQUE KEY `credential_username` (`credential_email`);

--
-- Indexes for table `tbl_user_email_verification`
--
ALTER TABLE `tbl_user_email_verification`
  ADD UNIQUE KEY `uev_user_id` (`uev_user_id`);

--
-- Indexes for table `tbl_user_extras`
--
ALTER TABLE `tbl_user_extras`
  ADD PRIMARY KEY (`uextra_id`),
  ADD UNIQUE KEY `uextra_user_id_2` (`uextra_user_id`),
  ADD KEY `uextra_user_id` (`uextra_user_id`);

--
-- Indexes for table `tbl_user_favourite_products`
--
ALTER TABLE `tbl_user_favourite_products`
  ADD PRIMARY KEY (`ufp_id`),
  ADD UNIQUE KEY `ufp_user_id` (`ufp_user_id`,`ufp_selprod_id`);

--
-- Indexes for table `tbl_user_favourite_shops`
--
ALTER TABLE `tbl_user_favourite_shops`
  ADD PRIMARY KEY (`ufs_id`),
  ADD UNIQUE KEY `ufs_user_id` (`ufs_user_id`,`ufs_shop_id`);

--
-- Indexes for table `tbl_user_meta`
--
ALTER TABLE `tbl_user_meta`
  ADD PRIMARY KEY (`usermeta_user_id`,`usermeta_key`);

--
-- Indexes for table `tbl_user_notifications`
--
ALTER TABLE `tbl_user_notifications`
  ADD PRIMARY KEY (`unotification_id`);

--
-- Indexes for table `tbl_user_password_reset_requests`
--
ALTER TABLE `tbl_user_password_reset_requests`
  ADD UNIQUE KEY `uprr_user_id` (`uprr_user_id`);

--
-- Indexes for table `tbl_user_permissions`
--
ALTER TABLE `tbl_user_permissions`
  ADD PRIMARY KEY (`userperm_user_id`,`userperm_section_id`);

--
-- Indexes for table `tbl_user_phone_verification`
--
ALTER TABLE `tbl_user_phone_verification`
  ADD PRIMARY KEY (`upv_user_id`);

--
-- Indexes for table `tbl_user_product_recommendation`
--
ALTER TABLE `tbl_user_product_recommendation`
  ADD PRIMARY KEY (`upr_user_id`,`upr_product_id`);

--
-- Indexes for table `tbl_user_requests_history`
--
ALTER TABLE `tbl_user_requests_history`
  ADD PRIMARY KEY (`ureq_id`);

--
-- Indexes for table `tbl_user_return_address`
--
ALTER TABLE `tbl_user_return_address`
  ADD PRIMARY KEY (`ura_user_id`);

--
-- Indexes for table `tbl_user_return_address_lang`
--
ALTER TABLE `tbl_user_return_address_lang`
  ADD PRIMARY KEY (`uralang_user_id`,`uralang_lang_id`);

--
-- Indexes for table `tbl_user_reward_points`
--
ALTER TABLE `tbl_user_reward_points`
  ADD PRIMARY KEY (`urp_id`);

--
-- Indexes for table `tbl_user_reward_point_breakup`
--
ALTER TABLE `tbl_user_reward_point_breakup`
  ADD PRIMARY KEY (`urpbreakup_id`);

--
-- Indexes for table `tbl_user_supplier_form_fields`
--
ALTER TABLE `tbl_user_supplier_form_fields`
  ADD PRIMARY KEY (`sformfield_id`),
  ADD UNIQUE KEY `sformfield_identifier` (`sformfield_identifier`);

--
-- Indexes for table `tbl_user_supplier_form_fields_lang`
--
ALTER TABLE `tbl_user_supplier_form_fields_lang`
  ADD PRIMARY KEY (`sformfieldlang_sformfield_id`,`sformfieldlang_lang_id`);

--
-- Indexes for table `tbl_user_supplier_requests`
--
ALTER TABLE `tbl_user_supplier_requests`
  ADD PRIMARY KEY (`usuprequest_user_id`),
  ADD UNIQUE KEY `usuprequest_id` (`usuprequest_id`);

--
-- Indexes for table `tbl_user_supplier_request_values`
--
ALTER TABLE `tbl_user_supplier_request_values`
  ADD PRIMARY KEY (`sfreqvalue_id`);

--
-- Indexes for table `tbl_user_temp_token_requests`
--
ALTER TABLE `tbl_user_temp_token_requests`
  ADD PRIMARY KEY (`uttr_user_id`);

--
-- Indexes for table `tbl_user_transactions`
--
ALTER TABLE `tbl_user_transactions`
  ADD PRIMARY KEY (`utxn_id`);

--
-- Indexes for table `tbl_user_wish_lists`
--
ALTER TABLE `tbl_user_wish_lists`
  ADD PRIMARY KEY (`uwlist_id`);

--
-- Indexes for table `tbl_user_wish_list_products`
--
ALTER TABLE `tbl_user_wish_list_products`
  ADD PRIMARY KEY (`uwlp_uwlist_id`,`uwlp_selprod_id`);

--
-- Indexes for table `tbl_user_withdrawal_requests`
--
ALTER TABLE `tbl_user_withdrawal_requests`
  ADD PRIMARY KEY (`withdrawal_id`);

--
-- Indexes for table `tbl_user_withdrawal_requests_specifics`
--
ALTER TABLE `tbl_user_withdrawal_requests_specifics`
  ADD PRIMARY KEY (`uwrs_withdrawal_id`,`uwrs_key`);

--
-- Indexes for table `tbl_verification_flds`
--
ALTER TABLE `tbl_verification_flds`
  ADD PRIMARY KEY (`vflds_id`),
  ADD UNIQUE KEY `vflds_identifier` (`vflds_identifier`);

--
-- Indexes for table `tbl_verification_flds_lang`
--
ALTER TABLE `tbl_verification_flds_lang`
  ADD UNIQUE KEY `vfldslang_vflds_id` (`vfldslang_vflds_id`,`vfldslang_lang_id`);

--
-- Indexes for table `tbl_zones`
--
ALTER TABLE `tbl_zones`
  ADD PRIMARY KEY (`zone_id`),
  ADD UNIQUE KEY `zone_identifier` (`zone_identifier`);

--
-- Indexes for table `tbl_zones_lang`
--
ALTER TABLE `tbl_zones_lang`
  ADD PRIMARY KEY (`zonelang_zone_id`,`zonelang_lang_id`),
  ADD UNIQUE KEY `zonelang_lang_id` (`zonelang_lang_id`,`zone_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_abandoned_cart`
--
ALTER TABLE `tbl_abandoned_cart`
  MODIFY `abandonedcart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `tbl_abusive_words`
--
ALTER TABLE `tbl_abusive_words`
  MODIFY `abusive_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_addresses`
--
ALTER TABLE `tbl_addresses`
  MODIFY `addr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_ads_batches`
--
ALTER TABLE `tbl_ads_batches`
  MODIFY `adsbatch_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_affiliate_commission_settings`
--
ALTER TABLE `tbl_affiliate_commission_settings`
  MODIFY `afcommsetting_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_affiliate_commission_setting_history`
--
ALTER TABLE `tbl_affiliate_commission_setting_history`
  MODIFY `acsh_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_attached_files`
--
ALTER TABLE `tbl_attached_files`
  MODIFY `afile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2846;

--
-- AUTO_INCREMENT for table `tbl_attached_files_temp`
--
ALTER TABLE `tbl_attached_files_temp`
  MODIFY `afile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_attribute_groups`
--
ALTER TABLE `tbl_attribute_groups`
  MODIFY `attrgrp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_attribute_group_attributes`
--
ALTER TABLE `tbl_attribute_group_attributes`
  MODIFY `attr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `tbl_banners`
--
ALTER TABLE `tbl_banners`
  MODIFY `banner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_banners_clicks`
--
ALTER TABLE `tbl_banners_clicks`
  MODIFY `bclick_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_banners_logs`
--
ALTER TABLE `tbl_banners_logs`
  MODIFY `lbanner_banner_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_banner_locations`
--
ALTER TABLE `tbl_banner_locations`
  MODIFY `blocation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_blog_contributions`
--
ALTER TABLE `tbl_blog_contributions`
  MODIFY `bcontributions_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_blog_post`
--
ALTER TABLE `tbl_blog_post`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_blog_post_categories`
--
ALTER TABLE `tbl_blog_post_categories`
  MODIFY `bpcategory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_blog_post_comments`
--
ALTER TABLE `tbl_blog_post_comments`
  MODIFY `bpcomment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_brands`
--
ALTER TABLE `tbl_brands`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `tbl_catalog_request_messages`
--
ALTER TABLE `tbl_catalog_request_messages`
  MODIFY `scatrequestmsg_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_collections`
--
ALTER TABLE `tbl_collections`
  MODIFY `collection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `tbl_commission_settings`
--
ALTER TABLE `tbl_commission_settings`
  MODIFY `commsetting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tbl_commission_setting_history`
--
ALTER TABLE `tbl_commission_setting_history`
  MODIFY `csh_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_content_block_sections`
--
ALTER TABLE `tbl_content_block_sections`
  MODIFY `cbs_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tbl_content_pages`
--
ALTER TABLE `tbl_content_pages`
  MODIFY `cpage_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_content_pages_block_lang`
--
ALTER TABLE `tbl_content_pages_block_lang`
  MODIFY `cpblocklang_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `tbl_counter_offers`
--
ALTER TABLE `tbl_counter_offers`
  MODIFY `counter_offer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_countries`
--
ALTER TABLE `tbl_countries`
  MODIFY `country_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=260;

--
-- AUTO_INCREMENT for table `tbl_coupons`
--
ALTER TABLE `tbl_coupons`
  MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_coupons_history`
--
ALTER TABLE `tbl_coupons_history`
  MODIFY `couponhistory_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_coupons_hold`
--
ALTER TABLE `tbl_coupons_hold`
  MODIFY `couponhold_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_cron_log`
--
ALTER TABLE `tbl_cron_log`
  MODIFY `cronlog_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_cron_schedules`
--
ALTER TABLE `tbl_cron_schedules`
  MODIFY `cron_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `tbl_currency`
--
ALTER TABLE `tbl_currency`
  MODIFY `currency_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_email_archives`
--
ALTER TABLE `tbl_email_archives`
  MODIFY `emailarchive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT for table `tbl_empty_cart_items`
--
ALTER TABLE `tbl_empty_cart_items`
  MODIFY `emptycartitem_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_extra_attributes`
--
ALTER TABLE `tbl_extra_attributes`
  MODIFY `eattribute_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_extra_attribute_groups`
--
ALTER TABLE `tbl_extra_attribute_groups`
  MODIFY `eattrgroup_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_extra_pages`
--
ALTER TABLE `tbl_extra_pages`
  MODIFY `epage_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `tbl_faqs`
--
ALTER TABLE `tbl_faqs`
  MODIFY `faq_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `tbl_faq_categories`
--
ALTER TABLE `tbl_faq_categories`
  MODIFY `faqcat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tbl_filters`
--
ALTER TABLE `tbl_filters`
  MODIFY `filter_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_filter_groups`
--
ALTER TABLE `tbl_filter_groups`
  MODIFY `filtergroup_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_google_fonts`
--
ALTER TABLE `tbl_google_fonts`
  MODIFY `gfont_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1359;

--
-- AUTO_INCREMENT for table `tbl_invoices`
--
ALTER TABLE `tbl_invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_languages`
--
ALTER TABLE `tbl_languages`
  MODIFY `language_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_language_labels`
--
ALTER TABLE `tbl_language_labels`
  MODIFY `label_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21146;

--
-- AUTO_INCREMENT for table `tbl_late_charges_profile`
--
ALTER TABLE `tbl_late_charges_profile`
  MODIFY `lcp_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_layout_templates`
--
ALTER TABLE `tbl_layout_templates`
  MODIFY `ltemplate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10002;

--
-- AUTO_INCREMENT for table `tbl_manual_shipping_api`
--
ALTER TABLE `tbl_manual_shipping_api`
  MODIFY `mshipapi_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_meta_tags`
--
ALTER TABLE `tbl_meta_tags`
  MODIFY `meta_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `tbl_navigations`
--
ALTER TABLE `tbl_navigations`
  MODIFY `nav_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_navigation_links`
--
ALTER TABLE `tbl_navigation_links`
  MODIFY `nlink_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `tbl_notifications`
--
ALTER TABLE `tbl_notifications`
  MODIFY `notification_id` bigint(15) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `tbl_options`
--
ALTER TABLE `tbl_options`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_option_values`
--
ALTER TABLE `tbl_option_values`
  MODIFY `optionvalue_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_orders`
--
ALTER TABLE `tbl_orders`
  MODIFY `order_order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tbl_orders_status`
--
ALTER TABLE `tbl_orders_status`
  MODIFY `orderstatus_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tbl_orders_status_history`
--
ALTER TABLE `tbl_orders_status_history`
  MODIFY `oshistory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `tbl_order_cancel_reasons`
--
ALTER TABLE `tbl_order_cancel_reasons`
  MODIFY `ocreason_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_order_cancel_requests`
--
ALTER TABLE `tbl_order_cancel_requests`
  MODIFY `ocrequest_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_order_cancel_rules`
--
ALTER TABLE `tbl_order_cancel_rules`
  MODIFY `ocrule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_order_payments`
--
ALTER TABLE `tbl_order_payments`
  MODIFY `opayment_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_order_products`
--
ALTER TABLE `tbl_order_products`
  MODIFY `op_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `tbl_order_product_charges`
--
ALTER TABLE `tbl_order_product_charges`
  MODIFY `opcharge_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `tbl_order_product_digital_download_links`
--
ALTER TABLE `tbl_order_product_digital_download_links`
  MODIFY `opddl_link_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_order_prod_charges_logs`
--
ALTER TABLE `tbl_order_prod_charges_logs`
  MODIFY `opchargelog_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `tbl_order_return_reasons`
--
ALTER TABLE `tbl_order_return_reasons`
  MODIFY `orreason_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_order_return_requests`
--
ALTER TABLE `tbl_order_return_requests`
  MODIFY `orrequest_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_order_return_request_messages`
--
ALTER TABLE `tbl_order_return_request_messages`
  MODIFY `orrmsg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_order_seller_subscriptions`
--
ALTER TABLE `tbl_order_seller_subscriptions`
  MODIFY `ossubs_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_plugins`
--
ALTER TABLE `tbl_plugins`
  MODIFY `plugin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `tbl_policy_points`
--
ALTER TABLE `tbl_policy_points`
  MODIFY `ppoint_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_products`
--
ALTER TABLE `tbl_products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `tbl_products_browsing_history`
--
ALTER TABLE `tbl_products_browsing_history`
  MODIFY `pbhistory_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_product_categories`
--
ALTER TABLE `tbl_product_categories`
  MODIFY `prodcat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `tbl_product_duration_discount`
--
ALTER TABLE `tbl_product_duration_discount`
  MODIFY `produr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_product_groups`
--
ALTER TABLE `tbl_product_groups`
  MODIFY `prodgroup_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_product_requests`
--
ALTER TABLE `tbl_product_requests`
  MODIFY `preq_id` bigint(15) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_product_saved_search`
--
ALTER TABLE `tbl_product_saved_search`
  MODIFY `pssearch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_product_special_prices`
--
ALTER TABLE `tbl_product_special_prices`
  MODIFY `splprice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_product_specifications`
--
ALTER TABLE `tbl_product_specifications`
  MODIFY `prodspec_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_product_stock_hold`
--
ALTER TABLE `tbl_product_stock_hold`
  MODIFY `pshold_id` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=880;

--
-- AUTO_INCREMENT for table `tbl_product_volume_discount`
--
ALTER TABLE `tbl_product_volume_discount`
  MODIFY `voldiscount_id` bigint(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_prod_unavailable_rental_durations`
--
ALTER TABLE `tbl_prod_unavailable_rental_durations`
  MODIFY `pu_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_promotions`
--
ALTER TABLE `tbl_promotions`
  MODIFY `promotion_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_promotions_charges`
--
ALTER TABLE `tbl_promotions_charges`
  MODIFY `pcharge_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_promotions_clicks`
--
ALTER TABLE `tbl_promotions_clicks`
  MODIFY `pclick_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_promotion_item_charges`
--
ALTER TABLE `tbl_promotion_item_charges`
  MODIFY `picharge_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_push_notifications`
--
ALTER TABLE `tbl_push_notifications`
  MODIFY `pnotification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_rental_product_stock_hold`
--
ALTER TABLE `tbl_rental_product_stock_hold`
  MODIFY `rentpshold_id` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `tbl_report_reasons`
--
ALTER TABLE `tbl_report_reasons`
  MODIFY `reportreason_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_request_for_quotes`
--
ALTER TABLE `tbl_request_for_quotes`
  MODIFY `rfq_id` bigint(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_rewards_on_purchase`
--
ALTER TABLE `tbl_rewards_on_purchase`
  MODIFY `rop_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_search_items`
--
ALTER TABLE `tbl_search_items`
  MODIFY `searchitem_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_seller_brand_requests`
--
ALTER TABLE `tbl_seller_brand_requests`
  MODIFY `sbrandreq_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_seller_catalog_requests`
--
ALTER TABLE `tbl_seller_catalog_requests`
  MODIFY `scatrequest_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_seller_packages`
--
ALTER TABLE `tbl_seller_packages`
  MODIFY `spackage_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_seller_packages_plan`
--
ALTER TABLE `tbl_seller_packages_plan`
  MODIFY `spplan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_seller_products`
--
ALTER TABLE `tbl_seller_products`
  MODIFY `selprod_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `tbl_seller_product_reviews`
--
ALTER TABLE `tbl_seller_product_reviews`
  MODIFY `spreview_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_shippingapi_settings`
--
ALTER TABLE `tbl_shippingapi_settings`
  MODIFY `shipsetting_shippingapi_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_shipping_apis`
--
ALTER TABLE `tbl_shipping_apis`
  MODIFY `shippingapi_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_shipping_company`
--
ALTER TABLE `tbl_shipping_company`
  MODIFY `scompany_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_shipping_durations`
--
ALTER TABLE `tbl_shipping_durations`
  MODIFY `sduration_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_shipping_packages`
--
ALTER TABLE `tbl_shipping_packages`
  MODIFY `shippack_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_shipping_profile`
--
ALTER TABLE `tbl_shipping_profile`
  MODIFY `shipprofile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_shipping_profile_zones`
--
ALTER TABLE `tbl_shipping_profile_zones`
  MODIFY `shipprozone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_shipping_rates`
--
ALTER TABLE `tbl_shipping_rates`
  MODIFY `shiprate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_shipping_zone`
--
ALTER TABLE `tbl_shipping_zone`
  MODIFY `shipzone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_shops`
--
ALTER TABLE `tbl_shops`
  MODIFY `shop_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_shops_to_theme`
--
ALTER TABLE `tbl_shops_to_theme`
  MODIFY `stt_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_shop_collections`
--
ALTER TABLE `tbl_shop_collections`
  MODIFY `scollection_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_shop_reports`
--
ALTER TABLE `tbl_shop_reports`
  MODIFY `sreport_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_slides`
--
ALTER TABLE `tbl_slides`
  MODIFY `slide_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tbl_smart_log_actions`
--
ALTER TABLE `tbl_smart_log_actions`
  MODIFY `slog_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_smart_user_activity_browsing`
--
ALTER TABLE `tbl_smart_user_activity_browsing`
  MODIFY `uab_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_sms_archives`
--
ALTER TABLE `tbl_sms_archives`
  MODIFY `smsarchive_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_social_platforms`
--
ALTER TABLE `tbl_social_platforms`
  MODIFY `splatform_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_states`
--
ALTER TABLE `tbl_states`
  MODIFY `state_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4752;

--
-- AUTO_INCREMENT for table `tbl_system_logs`
--
ALTER TABLE `tbl_system_logs`
  MODIFY `slog_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_tags`
--
ALTER TABLE `tbl_tags`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_tax_categories`
--
ALTER TABLE `tbl_tax_categories`
  MODIFY `taxcat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_tax_rules`
--
ALTER TABLE `tbl_tax_rules`
  MODIFY `taxrule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_tax_structure`
--
ALTER TABLE `tbl_tax_structure`
  MODIFY `taxstr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_testimonials`
--
ALTER TABLE `tbl_testimonials`
  MODIFY `testimonial_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_theme`
--
ALTER TABLE `tbl_theme`
  MODIFY `theme_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_threads`
--
ALTER TABLE `tbl_threads`
  MODIFY `thread_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_thread_messages`
--
ALTER TABLE `tbl_thread_messages`
  MODIFY `message_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_time_slots`
--
ALTER TABLE `tbl_time_slots`
  MODIFY `tslot_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_transactions_failure_log`
--
ALTER TABLE `tbl_transactions_failure_log`
  MODIFY `txnlog_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_upc_codes`
--
ALTER TABLE `tbl_upc_codes`
  MODIFY `upc_code_id` bigint(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_updated_record_log`
--
ALTER TABLE `tbl_updated_record_log`
  MODIFY `urlog_id` bigint(15) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1959;

--
-- AUTO_INCREMENT for table `tbl_url_rewrite`
--
ALTER TABLE `tbl_url_rewrite`
  MODIFY `urlrewrite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=430;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_user_extras`
--
ALTER TABLE `tbl_user_extras`
  MODIFY `uextra_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_favourite_products`
--
ALTER TABLE `tbl_user_favourite_products`
  MODIFY `ufp_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_favourite_shops`
--
ALTER TABLE `tbl_user_favourite_shops`
  MODIFY `ufs_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_notifications`
--
ALTER TABLE `tbl_user_notifications`
  MODIFY `unotification_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=418;

--
-- AUTO_INCREMENT for table `tbl_user_requests_history`
--
ALTER TABLE `tbl_user_requests_history`
  MODIFY `ureq_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_reward_points`
--
ALTER TABLE `tbl_user_reward_points`
  MODIFY `urp_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_reward_point_breakup`
--
ALTER TABLE `tbl_user_reward_point_breakup`
  MODIFY `urpbreakup_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_supplier_form_fields`
--
ALTER TABLE `tbl_user_supplier_form_fields`
  MODIFY `sformfield_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_user_supplier_requests`
--
ALTER TABLE `tbl_user_supplier_requests`
  MODIFY `usuprequest_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_user_supplier_request_values`
--
ALTER TABLE `tbl_user_supplier_request_values`
  MODIFY `sfreqvalue_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tbl_user_temp_token_requests`
--
ALTER TABLE `tbl_user_temp_token_requests`
  MODIFY `uttr_user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `tbl_user_transactions`
--
ALTER TABLE `tbl_user_transactions`
  MODIFY `utxn_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `tbl_user_wish_lists`
--
ALTER TABLE `tbl_user_wish_lists`
  MODIFY `uwlist_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_withdrawal_requests`
--
ALTER TABLE `tbl_user_withdrawal_requests`
  MODIFY `withdrawal_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_verification_flds`
--
ALTER TABLE `tbl_verification_flds`
  MODIFY `vflds_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_zones`
--
ALTER TABLE `tbl_zones`
  MODIFY `zone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

