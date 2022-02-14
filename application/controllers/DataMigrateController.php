<?php

class DataMigrateController extends MyAppController
{

    

    public function updateUserData()
    {
        $mysqli = new mysqli('localhost', 'root', '', 'db-migration-v2-db');
        $mysqli2 = new mysqli('localhost', 'root', '', 'db-migration-v3-blank');
        
        $sql = "SELECT * FROM `tbl_users`";
        $rs = $mysqli->query($sql);
        if (!$rs) {
            return false;
        }

        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0) {
            /* echo "<pre>"; print_r($rows); echo "</pre>"; exit; */
            $db = FatApp::getDb();
            /* SAVE USER BASIC INFO */
            $sql = [];
            $metaQry = [];
            foreach($rows as $row) {
                /* $dataToSave = $row;
                unset($dataToSave['user_facebook_id']);
                unset($dataToSave['user_img_updated_on']);
                unset($dataToSave['user_city_id']);
                unset($dataToSave['user_googleplus_id']); */
                
                $userFacebookId = trim($row['user_facebook_id']);
                $userGoogleId = trim($row['user_googleplus_id']);
                $phoneNumber = (trim($row['user_phone']) == "") ? "NULL" : $row['user_phone'];
                
                $str = $row['user_id'].', "'. $mysqli->real_escape_string($row['user_name']).'", '. $phoneNumber .', "'.$row['user_dob'].'", "'. $row['user_profile_info'].'", "'. $mysqli->real_escape_string($row['user_address1']) .'", "'. $mysqli->real_escape_string($row['user_address2']) .'", "'. $row['user_zip'] .'", '. $row['user_country_id'] .', '. $row['user_state_id'] .', '. $row['user_is_buyer'] .', '. $row['user_is_supplier'] .', '. $row['user_is_advertiser'] .', '. $row['user_is_affiliate'] .', '. $row['user_is_shipping_company'] .', '. $row['user_autorenew_subscription'] .', "'. $row['user_fb_access_token'] .'", "'. $row['user_referral_code'] .'", '. $row['user_referrer_user_id'] .', '. $row['user_affiliate_referrer_user_id'] .', '. $row['user_preferred_dashboard'] .', "'. $row['user_regdate'] .'", "'. $row['user_company'] .'", "'. $row['user_products_services'] .'", '. $row['user_affiliate_commission'] .', '. $row['user_registered_initially_for'] .', "'. $row['user_order_tracking_url'] .'", "'. $row['user_img_updated_on'] .'", '. $row['user_deleted']. ', ""';
                
                $sql[] = '('. $str .')';
                if ($userFacebookId != '') {
                    $metaQry[] = '("'. $row['user_id'] .'", "facebooklogin_account_id", "'. $userGoogleId .'")';
                }
                if ($userGoogleId != '') {
                    $metaQry[] = '("'. $row['user_id'] .'", "googlelogin_account_id", "'. $userGoogleId .'")';
                }
            }
            
            /* SAVE USER GENERAL DATA */
                $qry = "INSERT  INTO tbl_users (`user_id`, `user_name`, `user_phone`, `user_dob`, `user_profile_info`, `user_address1`, `user_address2`, `user_zip`, `user_country_id`, `user_state_id`, `user_is_buyer`, `user_is_supplier`, `user_is_advertiser`, `user_is_affiliate`, `user_is_shipping_company`, `user_autorenew_subscription`, `user_fb_access_token`, `user_referral_code`, `user_referrer_user_id`, `user_affiliate_referrer_user_id`, `user_preferred_dashboard`, `user_regdate`, `user_company`, `user_products_services`, `user_affiliate_commission`, `user_registered_initially_for`, `user_order_tracking_url`, `user_updated_on`, `user_deleted`, `user_dial_code`) VALUES ". implode(',', $sql);
            
                if(!$mysqli2->query($qry)) {
                    echo "tbl_users". $mysqli2->error; die();    
                }
            /* ] */
            /* $rs = $mysqli2->query("ALTER TABLE `tbl_users` ADD UNIQUE `user_dial_code` (`user_dial_code`, `user_phone`)"); */
            
            /* [ SAVE USER META DETAILS */
            if (!empty($metaQry)) {
                $qry = "INSERT  INTO tbl_user_meta (`usermeta_user_id`, `usermeta_key`, `usermeta_value`) VALUES ". implode(',', $metaQry);
                
                if (!$mysqli2->query($qry)) {
                    echo "tbl_user_meta". $mysqli2->error; die();    
                }
            }
            /*  ] */
            
            /* SAVE USER CREDENTIALS */
            $sql = "SELECT * FROM `tbl_user_credentials`";
            $rs = $mysqli->query($sql);
            $rows = $rs->fetch_all(MYSQLI_ASSOC);
            if (count($rows) > 0) {
                foreach($rows as $row) {
                    if (!$db->insertFromArray('tbl_user_credentials', $row, false, array(), $row)) {
                        die("tbl_user_credentials". $db->getError()); 
                    }
                }
            }
            /* SAVE USER EMAIL VERIFICATION DATA */ 
            $sql = "SELECT * FROM `tbl_user_email_verification`";
            $rs = $mysqli->query($sql);
            $rows = $rs->fetch_all(MYSQLI_ASSOC);
            if (count($rows) > 0) {
                foreach($rows as $row) {
                    if (!$db->insertFromArray('tbl_user_email_verification', $row, false, array(), $row)) {
                        die("tbl_user_email_verification". $db->getError()); 
                    }
                }
            }
            /* SAVE SUPPLIER REQUEST DETAIL */ 
            $sql = "SELECT * FROM `tbl_user_supplier_requests`";
            $rs = $mysqli->query($sql);
            $rows = $rs->fetch_all(MYSQLI_ASSOC);
            if (count($rows) > 0) {
                foreach($rows as $row) {
                    if (!$db->insertFromArray('tbl_user_supplier_requests', $row, false, array(), $row)) {
                        die("tbl_user_supplier_requests". $db->getError()); 
                    }
                }
            }
            
            $sql = "SELECT * FROM `tbl_user_supplier_request_values`";
            $rs = $mysqli->query($sql);
            $rows = $rs->fetch_all(MYSQLI_ASSOC);
            if (count($rows) > 0) {
                foreach($rows as $row) {
                    if (!$db->insertFromArray('tbl_user_supplier_request_values', $row, false, array(), $row)) {
                        die("tbl_user_supplier_request_values". $db->getError()); 
                    }
                }
            }
            
            $sql = "SELECT * FROM `tbl_user_supplier_request_values_lang`";
            $rs = $mysqli->query($sql);
            $rows = $rs->fetch_all(MYSQLI_ASSOC);
            if (count($rows) > 0) {
                foreach($rows as $row) {
                    if (!$db->insertFromArray('tbl_user_supplier_request_values_lang', $row, false, array(), $row)) {
                        die("tbl_user_supplier_request_values_lang". $db->getError()); 
                    }
                }
            }
            
            /* ] */
            
            die("user data import done");
        }
    }
        
    public function updateShopData()
    {
        $mysqli = new mysqli('localhost', 'root', '', 'db-migration-v2-db');
        $mysqli2 = new mysqli('localhost', 'root', '', 'db-migration-v3-blank');
        
        $sql = "SELECT * FROM `tbl_shops`";
        $rs = $mysqli->query($sql);
        if (!$rs) {
            return false;
        }
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {
            $qryRows = [];
            foreach ($rows as $row) {
                $isFreeShip = ($row['shop_free_ship_upto'] > 0) ? 1 : 0;
            
                $qryRows[] = '('. $row['shop_id']. ', '. $row['shop_user_id']. ', "'. $mysqli->real_escape_string($row['shop_identifier']) .'", '. $row['shop_postalcode'] . ', '. $row['shop_country_id']. ', '. $row['shop_state_id']. ', "'. $row['shop_latitude']. '", "'. $row['shop_longitude']. '", "'. $row['shop_phone']. '", "'. $row['shop_custom_color_status']. '", "'. $row['shop_theme_background_color'] . '", "'. $row['shop_theme_header_color'] . '", "'. $row['shop_theme_text_color']. '", "'. $row['shop_theme_button_text_color']. '", '. $row['shop_active']. ', '. $row['shop_featured']. ', '. $row['shop_cod_min_wallet_balance']. ', '. $row['shop_supplier_display_status']. ', "'. $row['shop_created_on']. '", "'. $row['shop_updated_on']. '", '. $isFreeShip. ', '. $row['shop_free_ship_upto']. ')';
            }
            
            $qry = "INSERT INTO tbl_shops (`shop_id`, `shop_user_id`, `shop_identifier`, `shop_postalcode`, `shop_country_id`, `shop_state_id`, `shop_lat`, `shop_lng`, `shop_phone`, `shop_custom_color_status`, `shop_theme_background_color`, `shop_theme_header_color`, `shop_theme_text_color`, `shop_theme_button_text_color`, `shop_active`, `shop_featured`, `shop_cod_min_wallet_balance`, `shop_supplier_display_status`, `shop_created_on`, `shop_updated_on`, `shop_is_free_ship_active`, `shop_free_shipping_amount`) VALUES ". implode(',' , $qryRows);
            
            if (!$mysqli2->query($qry)) {
                echo $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_shops_lang`";
        $rs = $mysqli->query($sql);
        if (!$rs) {
            return false;
        }
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '('. $row['shoplang_shop_id']. ', '. $row['shoplang_lang_id'] .', "'. $mysqli->real_escape_string($row['shop_name']) .'", "'. $row['shop_contact_person'] .'", "'. $mysqli->real_escape_string($row['shop_description']) .'", "'. $mysqli->real_escape_string($row['shop_address_line_1']) .'", "'. $mysqli->real_escape_string($row['shop_address_line_2']) .'", "'. $row['shop_city'] .'", "'. $mysqli->real_escape_string($row['shop_payment_policy']) .'", "'. $mysqli->real_escape_string($row['shop_delivery_policy']) .'", "'. $mysqli->real_escape_string($row['shop_refund_policy']) .'", "'. $mysqli->real_escape_string($row['shop_additional_info']) .'", "'. $mysqli->real_escape_string($row['shop_seller_info']) .'")';
            }
            
            $qry = "INSERT INTO tbl_shops_lang (`shoplang_shop_id`, `shoplang_lang_id`, `shop_name`, `shop_contact_person`, `shop_description`, `shop_address_line_1`, `shop_address_line_2`, `shop_city`, `shop_payment_policy`, `shop_delivery_policy`, `shop_refund_policy`, `shop_additional_info`, `shop_seller_info`) VALUES ". implode(',' , $qryRows);
            
            if (!$mysqli2->query($qry)) {
                echo $mysqli2->error; die();    
            }
        }
        
        die("SHOP data import done");
        
    }
    
    public function updateCategoryData()
    {
        $mysqli = new mysqli('localhost', 'root', '', 'db-migration-v2-db');
        $mysqli2 = new mysqli('localhost', 'root', '', 'db-migration-v3-blank');
        
        $sql = "SELECT * FROM `tbl_product_categories`";
        $rs = $mysqli->query($sql);
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  { /* CATEGORIES GENERAL DATA */
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['prodcat_id'] .'", "'. $mysqli->real_escape_string($row['prodcat_identifier']) .'", "'. $row['prodcat_parent'] .'", "'. $row['prodcat_display_order'] .'", "'. $row['prodcat_featured'] .'", "'. $row['prodcat_active'] .'", "'. $row['prodcat_deleted'] .'", "'. $row['prodcat_code'] .'", "'. $row['prodcat_ordercode'] .'", "'. $row['prodcat_img_updated_on'] .'", "'. $row['prodcat_img_updated_on'] .'", "1")';
            }
            
            $qry = "INSERT INTO tbl_product_categories (`prodcat_id`, `prodcat_identifier`, `prodcat_parent`, `prodcat_display_order`, `prodcat_featured`, `prodcat_active`, `prodcat_deleted`, `prodcat_code`, `prodcat_ordercode`, `prodcat_updated_on`, `prodcat_status_updated_on`, `prodcat_status`) VALUES ". implode(',' , $qryRows);
            
            if (!$mysqli2->query($qry)) {
                echo $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_product_categories_lang`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {  /* CATEGORIES LANGUAGE DATA */
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['prodcatlang_prodcat_id'] .'", "'. $row['prodcatlang_lang_id'] .'", "'. $mysqli->real_escape_string($row['prodcat_name']) .'", "'. $mysqli->real_escape_string($row['prodcat_content_block']) .'", "'. $mysqli->real_escape_string($row['prodcat_description']). '")';
            }
            $qry = "INSERT INTO tbl_product_categories_lang (`prodcatlang_prodcat_id`, `prodcatlang_lang_id`, `prodcat_name`, `prodcat_content_block`, `prodcat_description`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_product_to_category`";
        $rs = $mysqli->query($sql);
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  { /* CATEGORIES TO PRODUCT LINKING */
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['ptc_product_id'] .'", "'. $row['ptc_prodcat_id'] .'")';
            }
            $qry = "INSERT INTO tbl_product_to_category (`ptc_product_id`, `ptc_prodcat_id`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo $mysqli2->error; die();    
            }
        }
        
        //tbl_product_category_relations => need to update this table [updateCategoryRelations()]
        
         die("CATEGORIES data import done");
    }
    
    public function updateCatalogData()
    {
        /* SPECIFICATION AND MEDIA DATA PENDING */ // tbl_product_specifications // tbl_product_specifications_lang
        $mysqli = new mysqli('localhost', 'root', '', 'db-migration-v2-db');
        $mysqli2 = new mysqli('localhost', 'root', '', 'db-migration-v3-blank');
        
        $sql = "SELECT * FROM `tbl_products`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  { /* CATALOG GENERAL DATA */
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['product_id'] .'", "'. $mysqli->real_escape_string($row['product_identifier']) .'", "'. $row['product_attrgrp_id'] .'", "'. $row['product_type'] .'", "'. $mysqli->real_escape_string($row['product_model']) .'", "'. $row['product_brand_id'] .'", "'. $row['product_added_by_admin_id'] .'", "'. $row['product_seller_id'] .'", "'. $row['product_length'] .'", "'. $row['product_width'] .'", "'. $row['product_height'] .'", "'. $row['product_dimension_unit'] .'", "'. $row['product_weight'] .'", "'. $row['product_weight_unit'] .'", "'. $row['product_added_on'] .'", "'. $row['product_image_updated_on'] .'", "'. $row['product_featured'] .'", "'. $row['product_active'] .'", "'. $row['product_approved'] .'", "'. $row['product_upc'] .'", "'. $row['product_isbn'] .'", "'. $row['product_ship_country'] .'", "'. $row['product_ship_free'] .'", "'. $row['product_cod_enabled'] .'", "'. $row['product_min_selling_price'] .'", "'. $row['product_deleted'] .'", "'. $row['product_ship_package'] .'", "'. $row['product_added_on'] .'", "1", "-1")';
            }
            
            $qry = "INSERT INTO tbl_products (`product_id`, `product_identifier`, `product_attrgrp_id`, `product_type`, `product_model`, `product_brand_id`, `product_added_by_admin_id`, `product_seller_id`, `product_length`, `product_width`, `product_height`, `product_dimension_unit`, `product_weight`, `product_weight_unit`, `product_added_on`, `product_img_updated_on`, `product_featured`, `product_active`, `product_approved`, `product_upc`, `product_isbn`, `product_ship_country`, `product_ship_free`, `product_cod_enabled`, `product_min_selling_price`, `product_deleted`, `product_ship_package`, `product_updated_on`, `product_enable_rfq`, `product_fulfillment_type`) VALUES ". implode(',' , $qryRows);
            
            if (!$mysqli2->query($qry)) {
                echo "tbl_products".  $mysqli2->error; die();    
            } 
        }
        
        $sql = "SELECT * FROM `tbl_products_lang`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {  /* CATALOG LANGUAGE DATA */
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['productlang_product_id'] .'", "'. $row['productlang_lang_id'] .'", "'. $mysqli->real_escape_string($row['product_name']) .'", "'. $mysqli->real_escape_string($row['product_short_description']) .'", "'. $mysqli->real_escape_string($row['product_description']) .'", "'. $mysqli->real_escape_string($row['product_tags_string']) .'", "'. $mysqli->real_escape_string($row['product_youtube_video']) .'")';
            }
            $qry = "INSERT INTO tbl_products_lang (`productlang_product_id`, `productlang_lang_id`, `product_name`, `product_short_description`, `product_description`, `product_tags_string`, `product_youtube_video`) VALUES ". implode(',' , $qryRows);
            
            if (!$mysqli2->query($qry)) {
                echo "tbl_products_lang". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_products_shipped_by_seller`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {  /* CATALOG SHIPPIED BY DATA */
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['psbs_product_id'] .'", "'. $row['psbs_user_id'] .'")';
            }
            $qry = "INSERT INTO tbl_products_shipped_by_seller (`psbs_product_id`, `psbs_user_id`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_products_shipped_by_seller". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_products_shipping`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {  /* CATALOG SHIPPING GENERAL DATA */
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['ps_product_id'] .'", "'. $row['ps_user_id'] .'", "'. $row['ps_from_country_id'] .'", "'. $row['ps_free'] .'", "-1")';
            }
            $qry = "INSERT INTO tbl_products_shipping (`ps_product_id`, `ps_user_id`, `ps_from_country_id`, `ps_free`, `ps_fullfillment_type`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_products_shipping". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_options`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {  /* OPTIONS GENERAL DATA */
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['option_id'] .'", "'. $mysqli->real_escape_string($row['option_identifier']) .'", "'. $row['option_seller_id'] .'", "'. $row['option_type'] .'", "'. $row['option_deleted'] .'", "'. $row['option_is_separate_images'] .'", "'. $row['option_is_color'] .'", "'. $row['option_display_in_filter'] .'")';
            }
            $qry = "INSERT INTO tbl_options (`option_id`, `option_identifier`, `option_seller_id`, `option_type`, `option_deleted`, `option_is_separate_images`, `option_is_color`, `option_display_in_filter`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo  "tbl_options". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_options_lang`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {  /* OPTIONS lANGUAGE DATA */
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['optionlang_option_id'] .'", "'. $row['optionlang_lang_id'] .'", "'. $mysqli->real_escape_string($row['option_name']) .'")';
            }
            $qry = "INSERT INTO tbl_options_lang (`optionlang_option_id`, `optionlang_lang_id`, `option_name`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_options_lang". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_option_values`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {  /* OPTIONS lANGUAGE DATA */
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['optionvalue_id'] .'", "'. $row['optionvalue_option_id'] .'", "'. $mysqli->real_escape_string($row['optionvalue_identifier']) .'", "'. $row['optionvalue_color_code'] .'", "'. $row['optionvalue_display_order'] .'")';
            }
            $qry = "INSERT INTO tbl_option_values (`optionvalue_id`, `optionvalue_option_id`, `optionvalue_identifier`, `optionvalue_color_code`, `optionvalue_display_order`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_option_values". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_option_values_lang`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {  /* OPTIONS lANGUAGE DATA */
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['optionvaluelang_optionvalue_id'] .'", "'. $row['optionvaluelang_lang_id'] .'", "'. $mysqli->real_escape_string($row['optionvalue_name']) .'")';
            }
            $qry = "INSERT INTO tbl_option_values_lang (`optionvaluelang_optionvalue_id`, `optionvaluelang_lang_id`, `optionvalue_name`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_option_values_lang". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_product_to_options`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {  /* OPTIONS lANGUAGE DATA */
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['prodoption_product_id'] .'", "'. $row['prodoption_option_id'] .'")';
            }
            $qry = "INSERT INTO tbl_product_to_options (`prodoption_product_id`, `prodoption_option_id`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_product_to_options". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_tags`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {  /* OPTIONS lANGUAGE DATA */   
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['tag_id'] .'", "'. $mysqli->real_escape_string($row['tag_identifier']) .'", "'. $row['tag_user_id'] .'", "'. $row['tag_admin_id'] .'")';
            }
            $qry = "INSERT INTO tbl_tags (`tag_id`, `tag_identifier`, `tag_user_id`, `tag_admin_id`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_tags". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_tags_lang`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {  /* OPTIONS lANGUAGE DATA */
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['taglang_tag_id'] .'", "'. $row['taglang_lang_id'] .'", "'. $mysqli->real_escape_string($row['tag_name']) .'")';
            }
            $qry = "INSERT INTO tbl_tags_lang (`taglang_tag_id`, `taglang_lang_id`, `tag_name`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_tags_lang". $mysqli2->error; die();    
            }
        }
        
        
        $sql = "SELECT * FROM `tbl_product_to_tags`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {  /* OPTIONS lANGUAGE DATA */
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['ptt_product_id'] .'", "'. $row['ptt_tag_id'] .'")';
            }
            $qry = "INSERT INTO tbl_product_to_tags (`ptt_product_id`, `ptt_tag_id`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_product_to_tags". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_tax_categories`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {  /* TAX CATEGORY GENERAL DATA */
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['taxcat_id'] .'", "'. $mysqli->real_escape_string($row['taxcat_identifier']) .'", "'. $row['taxcat_active'] .'", "'. $row['taxcat_deleted'] .'", "'. $row['taxcat_last_updated'] .'", "'.  $mysqli->real_escape_string($row['taxcat_identifier'])  .'")';
            }
            $qry = "INSERT INTO tbl_tax_categories (`taxcat_id`, `taxcat_identifier`, `taxcat_active`, `taxcat_deleted`, `taxcat_last_updated`, `taxcat_code`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_tax_categories". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_tax_categories_lang`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {  /* TAX CATEGORY LANGUAGE DATA */
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['taxcatlang_taxcat_id'] .'", "'. $row['taxcatlang_lang_id'] .'", "'. $mysqli->real_escape_string($row['taxcat_name']) .'")';
            }
            $qry = "INSERT INTO tbl_tax_categories_lang (`taxcatlang_taxcat_id`, `taxcatlang_lang_id`, `taxcat_name`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_tax_categories_lang". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_product_to_tax`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {  /* TAX CATEGORY ATTACH DATA */
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['ptt_product_id'] .'", "'. $row['ptt_taxcat_id'] .'", "'. $row['ptt_seller_user_id'] .'", 1, "'. $row['ptt_taxcat_id'] .'")';
            }
            $qry = "INSERT INTO tbl_product_to_tax (`ptt_product_id`, `ptt_taxcat_id`, `ptt_seller_user_id`, `ptt_type`, `ptt_taxcat_id_rent`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_product_to_tax". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_brands`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {  /* BRANDS GENERAL DATA */
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['brand_id'] .'", "'. $mysqli->real_escape_string($row['brand_identifier']) .'", "'. $row['brand_seller_id'] .'", "'. $row['brand_featured'] .'", "'. $row['brand_active'] .'", "'. $row['brand_status'] .'", "'. $row['brand_deleted'] .'", "'. $mysqli->real_escape_string($row['brand_comments']) .'")';
                //== Extra column in new db : brand_updated_on, brand_requested_on, brand_status_updated_on
                
            }
            $qry = "INSERT INTO tbl_brands (`brand_id`, `brand_identifier`, `brand_seller_id`, `brand_featured`, `brand_active`, `brand_status`, `brand_deleted`, `brand_comments`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_brands". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_brands_lang`";
        // Need to check tbl_seller_brand_requests_lang, tbl_seller_brand_requests
        
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {  /* BRANDS LANGUAGE DATA */
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['brandlang_brand_id'] .'", "'. $row['brandlang_lang_id'] .'", "'. $mysqli->real_escape_string($row['brand_name']) .'", "'. $mysqli->real_escape_string($row['brand_short_description']) .'")';
            }
            $qry = "INSERT INTO tbl_brands_lang (`brandlang_brand_id`, `brandlang_lang_id`, `brand_name`, `brand_short_description`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_brands_lang". $mysqli2->error; die();    
            }
        }
        
        die("Catalog import data done");
    }
    
    public function updateSellerInventoryData()
    {
        //=> url and meta data pending
        $mysqli = new mysqli('localhost', 'root', '', 'db-migration-v2-db');
        $mysqli2 = new mysqli('localhost', 'root', '', 'db-migration-v3-blank');
    
        $sql = "SELECT * FROM `tbl_seller_products`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['selprod_id'] .'", "'. $row['selprod_user_id'] .'", "'. $row['selprod_product_id'] .'", "'. $row['selprod_code'] .'", "'. $row['selprod_price'] .'", "'. $row['selprod_cost'] .'", "'. $row['selprod_stock'] .'", "'. $row['selprod_min_order_qty'] .'", "'. $row['selprod_subtract_stock'] .'", "'. $row['selprod_track_inventory'] .'", "'. $row['selprod_threshold_stock_level'] .'", "'. $row['selprod_sku'] .'", "'. $row['selprod_condition'] .'", "'. $row['selprod_added_on'] .'", "'. $row['selprod_available_from'] .'", "'. $mysqli->real_escape_string($row['selprod_comments']) .'", "'. $row['selprod_active'] .'", "'. $row['selprod_cod_enabled'] .'", "'. $row['selprod_sold_count'] .'", "'. $row['selprod_url_keyword'] .'", "'. $row['selprod_max_download_times'] .'", "'. $row['selprod_downloadable_link'] .'", "'. $row['selprod_download_validity_in_days'] .'", "'. $row['selprod_urlrewrite_id'] .'", "'. $row['selprod_deleted'] .'", "", 1, -1)';
            }
            $qry = "INSERT INTO tbl_seller_products (`selprod_id`, `selprod_user_id`, `selprod_product_id`, `selprod_code`, `selprod_price`, `selprod_cost`, `selprod_stock`, `selprod_min_order_qty`, `selprod_subtract_stock`, `selprod_track_inventory`, `selprod_threshold_stock_level`, `selprod_sku`, `selprod_condition`, `selprod_added_on`, `selprod_available_from`, `selprod_comments`, `selprod_active`, `selprod_cod_enabled`, `selprod_sold_count`, `selprod_url_keyword`, `selprod_max_download_times`, `selprod_downloadable_link`, `selprod_download_validity_in_days`, `selprod_urlrewrite_id`, `selprod_deleted`, `selprod_identifier`, `selprod_type`, `selprod_fulfillment_type`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_seller_products". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_seller_products_data`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        $termData = [];
        if (count($rows) > 0)  {  
            $qryRows = [];
            foreach ($rows as $row) {
                $type = ($row['sprodata_rental_type'] == 1) ? 2 : $row['sprodata_rental_type'];
                $qryRows[] = '("'. $row['sprodata_selprod_id'] .'", "'. $row['sprodata_is_for_sell'] .'", "'. $row['sprodata_is_for_rent'] .'", "'. $row['sprodata_rental_price'] .'", "'. $row['sprodata_rental_security'] .'", "'. $type .'", "'. $mysqli->real_escape_string($row['sprodata_rental_terms']) .'", "'. $row['sprodata_rental_stock'] .'", "'. $row['sprodata_rental_buffer_days'] .'", "'. $row['sprodata_minimum_rental_duration'] .'", 1, 1, "'. date('Y-m-d 00:00:00') .'", "1", "-1", "1")';
                $termData[$row['sprodata_selprod_id']] = $row['sprodata_rental_terms'];
            }
            
            $qry = "INSERT INTO tbl_seller_products_data (`sprodata_selprod_id`, `sprodata_is_for_sell`, `sprodata_is_for_rent`, `sprodata_rental_price`, `sprodata_rental_security`, `sprodata_duration_type`, `sprodata_rental_terms`, `sprodata_rental_stock`, `sprodata_rental_buffer_days`, `sprodata_minimum_rental_duration`, `sprodata_is_rental_data_updated`, `sprodata_rental_active`, `sprodata_rental_available_from`, `sprodata_minimum_rental_quantity`, `sprodata_fullfillment_type`, `sprodata_rental_condition`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_seller_products_data". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_seller_products_lang`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        
        if (count($rows) > 0)  {
            $qryRows = [];
            foreach ($rows as $row) {
                $termLangData = (isset($termData[$row['selprodlang_selprod_id']])) ? $termData[$row['selprodlang_selprod_id']] : "";
                $qryRows[] = '("'. $row['selprodlang_selprod_id'] .'", "'. $row['selprodlang_lang_id'] .'", "'. $mysqli->real_escape_string($row['selprod_title']) .'", "'. $mysqli->real_escape_string($row['selprod_features']) .'", "'. $mysqli->real_escape_string($row['selprod_warranty']) .'", "'. $mysqli->real_escape_string($row['selprod_return_policy']) .'", "'. $mysqli->real_escape_string($row['selprod_comments']) .'", "'. $mysqli->real_escape_string($termLangData) .'")';
            }
            
            $qry = "INSERT INTO tbl_seller_products_lang (`selprodlang_selprod_id`, `selprodlang_lang_id`, `selprod_title`, `selprod_features`, `selprod_warranty`, `selprod_return_policy`, `selprod_comments`, `selprod_rental_terms`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_seller_products_lang". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_seller_product_options`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['selprodoption_selprod_id'] .'", "'. $row['selprodoption_option_id'] .'", "'. $row['selprodoption_optionvalue_id'] .'")';
            }
            
            $qry = "INSERT INTO tbl_seller_product_options (`selprodoption_selprod_id`, `selprodoption_option_id`, `selprodoption_optionvalue_id`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_seller_product_options". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_product_special_prices`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['splprice_id'] .'", "'. $row['splprice_selprod_id'] .'", "'. $row['splprice_start_date'] .'", "'. $row['splprice_end_date'] .'", "'. $row['splprice_price'] .'", "'. $row['splprice_display_dis_type'] .'", "'. $row['splprice_display_dis_val'] .'", "'. $row['splprice_display_list_price'] .'", 1)';
            }
            $qry = "INSERT INTO tbl_product_special_prices (`splprice_id`, `splprice_selprod_id`, `splprice_start_date`, `splprice_end_date`, `splprice_price`, `splprice_display_dis_type`, `splprice_display_dis_val`, `splprice_display_list_price`, `splprice_type`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_product_special_prices". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_seller_product_policies`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['sppolicy_selprod_id'] .'", "'. $row['sppolicy_ppoint_id'] .'")';
            }
            
            $qry = "INSERT INTO tbl_seller_product_policies (`sppolicy_selprod_id`, `sppolicy_ppoint_id`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_seller_product_policies". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_upsell_products`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['upsell_sellerproduct_id'] .'", "'. $row['upsell_recommend_sellerproduct_id'] .'")';
            }
            
            $qry = "INSERT INTO tbl_upsell_products (`upsell_sellerproduct_id`, `upsell_recommend_sellerproduct_id`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_upsell_products". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_related_products`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['related_sellerproduct_id'] .'", "'. $row['related_recommend_sellerproduct_id'] .'")';
            }
            
            $qry = "INSERT INTO tbl_related_products (`related_sellerproduct_id`, `related_recommend_sellerproduct_id`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_related_products". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_product_volume_discount`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['voldiscount_id'] .'", "'. $row['voldiscount_selprod_id'] .'", "'. $row['voldiscount_min_qty'] .'", "'. $row['voldiscount_percentage'] .'")';
            }
            
            $qry = "INSERT INTO tbl_product_volume_discount (`voldiscount_id`, `voldiscount_selprod_id`, `voldiscount_min_qty`, `voldiscount_percentage`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_product_volume_discount". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_prod_unavailable_rental_durations`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['pu_id'] .'", "'. $row['pu_selprod_id'] .'", "'. $row['pu_start_date'] .'", "'. $row['pu_end_date'] .'", "'. $row['pu_quantity'] .'")';
            }
            
            $qry = "INSERT INTO tbl_prod_unavailable_rental_durations (`pu_id`, `pu_selprod_id`, `pu_start_date`, `pu_end_date`, `pu_quantity`) VALUES ". implode(',' , $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_prod_unavailable_rental_durations". $mysqli2->error; die();    
            }
        }
        
        $sql = "SELECT * FROM `tbl_product_duration_discount`";
        $rs = $mysqli->query($sql); 
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        if (count($rows) > 0)  {
            $qryRows = [];
            foreach ($rows as $row) {
                $qryRows[] = '("'. $row['produr_id'] .'", "'. $row['produr_selprod_id'] .'", "'. $row['produr_rental_duration'] .'", "'. $row['produr_discount_percent'] .'", "'. $row['produr_added_date'] .'")';
            }
            
            $qry = "INSERT INTO tbl_product_duration_discount (`produr_id`, `produr_selprod_id`, `produr_rental_duration`, `produr_discount_percent`, `produr_added_date`) VALUES ". implode(',', $qryRows);
            if (!$mysqli2->query($qry)) {
                echo "tbl_product_duration_discount". $mysqli2->error; die();    
            }
        }
        
        die("seller inventory data import done");
    }
    
    
}
