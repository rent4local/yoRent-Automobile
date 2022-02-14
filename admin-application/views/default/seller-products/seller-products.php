<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');

if (count($arrListing) == 0) {
    $this->includeTemplate('_partial/no-record-found.php', array('adminLangId' => $adminLangId));
} else {
    $arr_flds = array(
        'select_all' => Labels::getLabel('LBL_Select_all', $adminLangId),
        'listserial' => Labels::getLabel('LBL_#', $adminLangId),
        'name' => Labels::getLabel('LBL_Name', $adminLangId),
        'user' => Labels::getLabel('LBL_Seller', $adminLangId),
    );
    $deleteFunction = "sellerProductDelete";
    if ($productType == applicationConstants::PRODUCT_FOR_SALE) {
        $arr_flds['selprod_price'] = Labels::getLabel('LBL_Price', $adminLangId);
        $arr_flds['selprod_stock'] = Labels::getLabel('LBL_Retail_Quantity', $adminLangId);
        $arr_flds['selprod_available_from'] = Labels::getLabel('LBL_Available_From', $adminLangId);
        $arr_flds['selprod_active'] = Labels::getLabel('LBL_Status', $adminLangId);
        $deleteFunction = "sellerProductDeleteSale";
    } else {
        $arr_flds['rental_price'] = Labels::getLabel('LBL_Rental_Price', $adminLangId);
        $arr_flds['sprodata_rental_stock'] = Labels::getLabel('LBL_Rental_Quantity', $adminLangId);
        $arr_flds['sprodata_rental_available_from'] = Labels::getLabel('LBL_Available_From', $adminLangId);
        $arr_flds['sprodata_rental_active'] = Labels::getLabel('LBL_Status', $adminLangId);
    }
    $arr_flds['action'] = '';
    if (!$canEdit) {
        unset($arr_flds['select_all'], $arr_flds['action']);
    }
    
    $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive table--hovered'));
    $th = $tbl->appendElement('thead')->appendElement('tr', array('class' => 'hide--mobile'));
    foreach ($arr_flds as $key => $val) {
        if ('select_all' == $key) {
            $th->appendElement('th', array('width' => '3%'))->appendElement('plaintext', array(''), '<label class="checkbox"><input title="' . $val . '" type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"></label>', true);
        } else if ('user' == $key || 'selprod_price' == $key || 'selprod_stock' == $key || 'selprod_available_from' == $key || 'sprodata_rental_stock' == $key || 'sprodata_rental_available_from' == $key || 'selprod_stock' == $key) {
            $e = $th->appendElement('th', array('width' => '10%'), $val);
        } else if ('selprod_active' == $key || 'sprodata_rental_active' == $key) {
            $e = $th->appendElement('th', array('width' => '7%'), $val);
        } else if ('action' == $key) {
            $e = $th->appendElement('th', array('width' => '40%', 'class' => 'action_column'), $val);
        } else if ('rental_price' == $key) {
            $e = $th->appendElement('th', array('width' => '20%'), $val);
        } else if ('listserial' == $key) {
            $e = $th->appendElement('th', array('width' => '5%'), $val);
        } else if ('name' == $key) {
            $e = $th->appendElement('th', array('width' => '30%'), $val);
        }
    }

    $sr_no = ($page > 1) ? $recordCount - (($page - 1) * $pageSize) : $recordCount;
    foreach ($arrListing as $sn => $row) {
        $tr = $tbl->appendElement('tr', array());

        foreach ($arr_flds as $key => $val) {
            $td = $tr->appendElement('td');
            switch ($key) {
                case 'select_all':
                    $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="selprod_ids[]" value=' . $row['selprod_id'] . '></label>', true);
                    break;
                case 'listserial':
                    $td->appendElement('plaintext', array(), $sr_no);
                    break;
                case 'name':
                    $variantStr = ($row['selprod_title'] != '') ? $row['selprod_title'] . '<br/>' : '';
                    if (is_array($row['options']) && count($row['options'])) {
                        foreach ($row['options'] as $op) {
                            $variantStr .= $op['option_name'] . ': ' . $op['optionvalue_name'] . '<br/>';
                        }
                    }
                    $td->appendElement('plaintext', array(), $variantStr, true);
                    if ($canViewProducts) {
                        $td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("' . UrlHelper::generateUrl('Products') . '", ' . $row['selprod_product_id'] . ')'), $row['product_name'], true);
                    } else {
                        $td->appendElement('plaintext', array(), $row['product_name'], true);
                    }
                    break;
                case 'user':
                    if ($canViewUsers) {
                        $td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("' . UrlHelper::generateUrl('Users') . '", ' . $row['selprod_user_id'] . ')'), '<strong>' . Labels::getLabel('LBL_N:', $adminLangId) . ' </strong>' . $row['user_name'], true);
                    } else {
                        $td->appendElement('plaintext', array(), '<strong>' . Labels::getLabel('LBL_N:', $adminLangId) . ' </strong>' . $row['user_name'], true);
                    }
                    $userDetail = '<br/><strong>' . Labels::getLabel('LBL_Email:', $adminLangId) . ' </strong>' . $row['credential_email'] . '<br/>';
                    $td->appendElement('plaintext', array(), $userDetail, true);
                    break;
                case 'selprod_price':
                    $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row[$key], true, true), true);
                    break;
                case 'rental_price' :
                    if ($row['sprodata_is_for_rent'] == applicationConstants::YES) {
                        $priceHtml = CommonHelper::displayMoneyFormat($row['sprodata_rental_price'], true, true) . '/' . $durationTypes[$row['sprodata_duration_type']];
                    } else {
                        $priceHtml = Labels::getLabel('LBL_N/A', $adminLangId);
                    }
                    $td->appendElement('plaintext', array(), $priceHtml, true);
                    break;
                case 'sprodata_rental_stock' :
                    if ($row['sprodata_is_for_rent'] == applicationConstants::YES) {
                        $stockHtml = $row['sprodata_rental_stock'];
                    } else {
                        $stockHtml = Labels::getLabel('LBL_N/A', $adminLangId);
                    }
                    $td->appendElement('plaintext', array(), $stockHtml, true);
                    break;
                case 'sprodata_available_from':
                case 'sprodata_rental_available_from':
                    $td->appendElement('plaintext', array(), FatDate::format($row[$key], false), true);
                    break;
                case 'selprod_active':
                case 'sprodata_rental_active':
                    $active = "";
                    if ($row[$key]) {
                        $active = 'checked';
                    }
                    $statusAct = ($canEdit === true) ? 'toggleStatus(event,this,' . applicationConstants::YES . ')' : 'toggleStatus(event,this,' . applicationConstants::NO . ')';
                    $statusClass = ($canEdit === false) ? 'disabled' : '';
                    $str = '<label class="statustab -txt-uppercase">
					   <input ' . $active . ' type="checkbox" id="switch' . $row['selprod_id'] . '" value="' . $row['selprod_id'] . '" onclick="' . $statusAct . '" class="switch-labels"/>
                       <i class="switch-handles ' . $statusClass . '"></i></label>';
                    $td->appendElement('plaintext', array(), $str, true);
                    break;
                case 'action':
                    if ($canEdit) {
                        /* if ($productType == applicationConstants::PRODUCT_FOR_SALE) {
                            $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "addSellerProductSaleForm(" . $row['selprod_product_id'] . ", " . $row['selprod_id'] . ", " . $row['selprod_user_id'] . ")"), "<i class='fa fa-edit icon'></i>", true);
                        } else {
                            $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "addSellerProductForm(" . $row['selprod_product_id'] . "," . $row['selprod_id'] . ")"), "<i class='far fa-edit icon'></i>", true);
                        } */
                        $td->appendElement('a', array('href' => UrlHelper::generateUrl('sellerProducts', 'form', [$row['selprod_product_id'], $row['selprod_id'], $productType]), 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId)), "<i class='fa fa-edit icon'></i>", true);
                        
                        
                        $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Delete_Product', $adminLangId), "onclick" => $deleteFunction."(" . $row['selprod_id'] . ")"), "<i class='fa fa-trash  icon'></i>", true);
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
        $tbl->appendElement('tr')->appendElement(
                'td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_Record_Found', $adminLangId)
        );
    }

    $frm = new Form('frmSelProdListing', array('id' => 'frmSelProdListing'));
    $frm->setFormTagAttribute('class', 'web_form last_td_nowrap actionButtons-js');
    $frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
    $frm->setFormTagAttribute('action', UrlHelper::generateUrl('SellerProducts', 'toggleBulkStatuses'));
    $frm->addHiddenField('', 'status');
    $frm->addHiddenField('', 'productType', $productType);

    echo $frm->getFormTag();
    echo $frm->getFieldHtml('status');
    echo $frm->getFieldHtml('productType');
    ?>
    <?php echo $tbl->getHtml(); ?>
    </form>
    <?php
    if (!$product_id) {
        $postedData['page'] = $page;
        echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmProductSearchPaging'));
        $pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToSearchPage', 'adminLangId' => $adminLangId);
        $this->includeTemplate('_partial/pagination.php', $pagingArr, false);
    }
}
?>

<style>
    .action_column {min-width: 130px;}
    .table .actions.actions--centered{margin-top: 0px;}
</style>
