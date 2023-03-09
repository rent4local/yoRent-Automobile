<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="scroll scroll-x js-scrollable table-wrap">
    <?php
    $arr_flds = array();
    if (count($arrListing) > 0 && $canEdit) {
        $arr_flds['select_all'] = '';
    }
    $arr_flds['listserial'] = Labels::getLabel('LBL_#', $siteLangId);
    $arr_flds['name'] = Labels::getLabel('LBL_Name', $siteLangId);
    $isSale = 0;
    $deleteFunction = "sellerProductDelete";
    
    $taxLbl = Labels::getLabel('LBL_Excluding_Tax', $siteLangId);
    if (FatApp::getConfig('CONF_PRODUCT_INCLUSIVE_TAX', FatUtility::VAR_INT, 0)) {
        $taxLbl = Labels::getLabel('LBL_Including_Tax', $siteLangId);
    }
    
    if ($prodType == applicationConstants::PRODUCT_FOR_RENT) {
        $arr_flds['rental_price'] = Labels::getLabel('LBL_Rental_Price', $siteLangId). ' <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="" data-original-title="'. $taxLbl .'"></i>';
        $arr_flds['sprodata_rental_stock'] = Labels::getLabel('LBL_Rental_Quantity', $siteLangId);
        $arr_flds['sprodata_rental_available_from'] = Labels::getLabel('LBL_Available_From', $siteLangId);
        if ($canEdit) {
            $arr_flds['sprodata_rental_active'] = Labels::getLabel('LBL_Status', $siteLangId);
        }
    } else {
        $arr_flds['selprod_price'] = Labels::getLabel('LBL_Price', $siteLangId) . ' <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="" data-original-title="'. $taxLbl .'"></i>';
        $arr_flds['selprod_stock'] = Labels::getLabel('LBL_Retail_Quantity', $siteLangId);
        $arr_flds['selprod_available_from'] = Labels::getLabel('LBL_Available_From', $siteLangId);
        if ($canEdit) {
            $arr_flds['selprod_active'] = Labels::getLabel('LBL_Status', $siteLangId);
        }
        $isSale = 1;
        $deleteFunction = "sellerProductDeleteSale";
    }
    if ($canEdit) {
        $arr_flds['action'] = '';
    }
    
    $tableClass = '';
    
    if (0 < count($arrListing)) {
        $tableClass = "table-justified";
    }
    
    $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table ' . $tableClass));
    $th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
    foreach ($arr_flds as $key => $val) {
        if ('select_all' == $key) {
            if (count($arrListing) > 0) {
                $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input type="checkbox" onclick="selectAll( $(this) )" title="' . $val . '" class="selectAll-js"></label>', true);
            }
        } else if ('rental_price' == $key) {
            $e = $th->appendElement('th', array('class' => 'rental_price_column'), $val, true);
        } else {
            $e = $th->appendElement('th', array(), $val, true);
        }
    }
    $page = isset($page) ? $page : 1;
    $recordCount = isset($recordCount) ? $recordCount : 1;
    $sr_no = ($page >= 1 && !$product_id) ? $recordCount - (($page - 1) * $pageSize) : count($arrListing);
	
    foreach ($arrListing as $sn => $row) {
        $tr = $tbl->appendElement('tr', array('class' => ($row['selprod_active'] != applicationConstants::ACTIVE) ? '' : ''));
        foreach ($arr_flds as $key => $val) {
            $td = $tr->appendElement('td');
            switch ($key) {
                case 'select_all':
                    $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="selprod_ids[]" value=' . $row['selprod_id'] . '></label>', true);
                    break;
                case 'listserial':
                    $td->appendElement('plaintext', array(), $sr_no, true);
                    break;
                case 'name':
                    $variantStr = '<div class="item"><figure class="item__pic"><img src="' . UrlHelper::getCachedUrl(CommonHelper::generateUrl('image', 'product', array($row['selprod_product_id'], "SMALL", $row['selprod_id'], 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg') . '" title="' . $row['product_name'] . '" alt="' . $row['product_name'] . '"></figure><div class="item__description">
				<div class="item__title">' . wordwrap($row['product_name'], 150, "<br>\n") . '</div>';
                    $variantStr .= ($row['selprod_title'] != '') ? '<div class="item__sub_title">' . wordwrap($row['selprod_title'], 150, "<br>\n") . '</div>' : '';
                    if (is_array($row['options']) && count($row['options'])) {
                        $variantStr .= '<div class="item__specification">';
                        $count = count($row['options']);
                        foreach ($row['options'] as $op) {
                            $variantStr .= '' . wordwrap($op['optionvalue_name'], 150, "<br>\n");
                            if ($count != 1) {
                                $variantStr .= ' | ';
                            }
                            $count--;
                        }
                        $variantStr .= '</div>';
                    }
                    $variantStr .= '</div></div>';
                    $td->appendElement('plaintext', array(), $variantStr, true);
                    break;
                case 'selprod_price':
                    $priceLbl = ($row['sprodata_is_for_sell'] == applicationConstants::NO) ? Labels::getLabel('LBL_N/A', $siteLangId) : CommonHelper::displayMoneyFormat($row[$key], true, true);
                    $td->appendElement('plaintext', array(), $priceLbl , true);
                    break;
                case 'rental_price' :
                    if ($row['sprodata_is_for_rent'] == applicationConstants::YES) {
                        $priceHtml = CommonHelper::displayMoneyFormat($row['sprodata_rental_price'], true, true) . '/' . $rentalDurationTypes[$row['sprodata_duration_type']];
                    } else {
                        $priceHtml = Labels::getLabel('LBL_N/A', $siteLangId);
                    }
                    $td->appendElement('plaintext', array(), $priceHtml, true);
                    break;
                case 'sprodata_rental_stock' :
                    if ($row['sprodata_is_for_rent'] == applicationConstants::YES) {
                        $stockHtml = $row['sprodata_rental_stock'];
                    } else {
                        $stockHtml = Labels::getLabel('LBL_N/A', $siteLangId);
                    }
                    $td->appendElement('plaintext', array(), $stockHtml, true);
                    break;
                    
                case 'selprod_stock':
                    $stockLbl = ($row['sprodata_is_for_sell'] == applicationConstants::NO) ? Labels::getLabel('LBL_N/A', $siteLangId) : $row[$key];
                
                    $td->appendElement('plaintext', array(), $stockLbl, true);
                    if ($row['selprod_track_inventory'] && ($row['selprod_stock'] <= $row['selprod_threshold_stock_level'])) {
                        $td->appendElement('plaintext', array(), " <i  class='fa fa-info-circle spn_must_field' data-toggle='tooltip' data-placement='top' title='" . Labels::getLabel('MSG_Product_stock_qty_below_or_equal_to_threshold_level', $siteLangId) . "'></i>", true);
                    }
                    break;
                case 'selprod_available_from':
                case 'sprodata_rental_available_from':
                    $dateLbl = FatDate::format($row[$key], false);
                    if ($row['sprodata_is_for_sell'] == applicationConstants::NO && $key == 'selprod_available_from') {
                        $dateLbl = Labels::getLabel('LBL_N/A', $siteLangId);
                    } 
                
                    $td->appendElement('plaintext', array(), $dateLbl, true);
                    break;
                case 'selprod_active':
                    /* $td->appendElement( 'plaintext', array(), $activeInactiveArr[$row[$key]],true ); */
                    if ($row['sprodata_is_for_sell'] == applicationConstants::NO) {
                        $str = Labels::getLabel('LBL_N/A', $siteLangId);
                    } else {
                        $active = "";
                        if (applicationConstants::ACTIVE == $row['selprod_active']) {
                            $active = 'checked';
                        }
                        $str = '<label class="toggle-switch" for="switch' . $row['selprod_id'] . '"><input ' . $active . ' type="checkbox" value="' . $row['selprod_id'] . '" id="switch' . $row['selprod_id'] . '" onclick="toggleSellerProductStatus(event,this,'.$prodType.')"/><div class="slider round"></div></label>';
                    }

                    $td->appendElement('plaintext', array(), $str, true);
                    break;
                case 'sprodata_rental_active':
                    /* $td->appendElement( 'plaintext', array(), $activeInactiveArr[$row[$key]],true ); */
                    $active = "";
                    if (applicationConstants::ACTIVE == $row['sprodata_rental_active']) {
                        $active = 'checked';
                    }
                    $str = '<label class="toggle-switch" for="switch' . $row['selprod_id'] . '"><input ' . $active . ' type="checkbox" value="' . $row['selprod_id'] . '" id="switch' . $row['selprod_id'] . '" onclick="toggleSellerProductStatus(event,this,'.$prodType.')"/><div class="slider round"></div></label>';

                    $td->appendElement('plaintext', array(), $str, true);
                    break;
                case 'action':
                    $ul = $td->appendElement("ul", array("class" => "actions"), '', true);
                    if ($prodType == applicationConstants::PRODUCT_FOR_RENT || ($row['sprodata_is_for_sell'] == applicationConstants::YES && $prodType == applicationConstants::PRODUCT_FOR_SALE)) {
                        $li = $ul->appendElement("li");
                        $li->appendElement(
                                'a', array('href' => UrlHelper::generateUrl('SellerInventories', 'sellerProductForm', array($row['selprod_product_id'], $row['selprod_id'], $isSale)), 'title' => Labels::getLabel('LBL_Edit', $siteLangId)), '<i class="fa fa-edit"></i>', true
                        );
                    
                        $li = $ul->appendElement("li");
                        $li->appendElement(
                                'a', array('href' => 'javascript:void(0)', 'title' => Labels::getLabel('LBL_Delete', $siteLangId), "onclick" => $deleteFunction."(" . $row['selprod_id'] . ")"), '<i class="fa fa-trash"></i>', true
                        );
                    } else {
                        $li = $ul->appendElement("li");
                        $li->appendElement(
                                'a', array('href' => UrlHelper::generateUrl('SellerInventories', 'sellerProductForm', array($row['selprod_product_id'], $row['selprod_id'], $isSale)), 'title' => Labels::getLabel('LBL_Add', $siteLangId)), '<i class="fa fa-plus"></i>', true
                        );
                    }
                    
                    if ($prodType == applicationConstants::PRODUCT_FOR_RENT) {
                        /* $productOptions = Product::getProductOptions($row['selprod_product_id'], $siteLangId); */
                        $productOptions = Product::getProductOptions($row['selprod_product_id'], $siteLangId, true);
                        $optionCombinations = CommonHelper::combinationOfElementsOfArr($productOptions, 'optionValues', '_');
                        $validOptionsForSeller = CommonHelper::validOptionsForSeller($row['selprod_product_id'], $optionCombinations, $userParentId, $siteLangId);
                        
                        if (is_array($validOptionsForSeller) && count($validOptionsForSeller)) {
                            $li = $ul->appendElement("li");
                            $li->appendElement(
                                    'a', array('href' => 'javascript:void(0)', 'title' => Labels::getLabel('LBL_Clone', $siteLangId), "onclick" => "sellerProductCloneForm(" . $row['selprod_product_id'] . "," . $row['selprod_id'] . ")"), '<i class="fa fa-clone"></i>', true
                            );
                        }
                    }
                    
                    break;
                default:
                    $td->appendElement('plaintext', array(), $row[$key], true);
                    break;
            }
        }

        $sr_no--;
    }
    if (count($arrListing) == 0) {
        echo $tbl->getHtml();
        $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
        $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
        // $tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_products_found_under_your_publication', $siteLangId));
        //$this->includeTemplate('_partial/no-record-found.php' , array('siteLangId'=>$siteLangId));
    } else {
        $frm = new Form('frmSellerProductsListing', array('id' => 'frmSellerProductsListing'));
        $frm->setFormTagAttribute('class', 'form actionButtons-js');
        $frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
        // $frm->setFormTagAttribute('action', UrlHelper::generateUrl('Seller', 'deleteBulkSellerProducts'));
        $frm->setFormTagAttribute('action', UrlHelper::generateUrl('SellerInventories', 'toggleBulkStatuses'));
        $frm->addHiddenField('', 'status');
        $frm->addHiddenField('', 'productType', $prodType);

        echo $frm->getFormTag();
        echo $frm->getFieldHtml('status');
        echo $tbl->getHtml();
        ?>
    </form>
    </div>
    <?php
}

if (!$product_id) {
    $postedData['page'] = $page;
    echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSellerProductSearchPaging'));
    $pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToSellerProductSearchPage', 'siteLangId' => $siteLangId, 'pageSize' => $pageSize, 'removePageCentClass' => true);
    $this->includeTemplate('_partial/pagination.php', $pagingArr, false);
}
?>
<style>
    .rental_price_column {
        width : 150px;
        min-width: 150px;
    }

    .item__description {
        max-width: 260px;
    }
</style>