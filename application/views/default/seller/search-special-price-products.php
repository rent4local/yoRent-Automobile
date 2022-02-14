<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="scroll scroll-x js-scrollable table-wrap">
<?php 
    $taxLbl = Labels::getLabel('LBL_Excluding_Tax', $siteLangId);
    if (FatApp::getConfig('CONF_PRODUCT_INCLUSIVE_TAX', FatUtility::VAR_INT, 0)) {
        $taxLbl = Labels::getLabel('LBL_Including_Tax', $siteLangId);
    }
    
    $arr_flds = array(
        'select_all' => '',
        'product_name' => Labels::getLabel('LBL_Name', $siteLangId),
        'selprod_price' => Labels::getLabel('LBL_Original_Price', $siteLangId). ' <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="" data-original-title="'. $taxLbl .'"></i>',
        'splprice_start_date' => Labels::getLabel('LBL_Start_Date', $siteLangId),
        'splprice_end_date' => Labels::getLabel('LBL_End_Date', $siteLangId),
        'splprice_price' => Labels::getLabel('LBL_Updated_Price', $siteLangId). ' <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="" data-original-title="'. $taxLbl .'"></i>'
    );
    if ($canEdit) {
        $arr_flds['action']    = '';
    }
    if (!$canEdit || 1 > count($arrListing)) {
        unset($arr_flds['select_all']);
    }
    $tableClass = '';
    if (0 < count($arrListing)) {
        $tableClass = "table-justified";
    }
    $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table splPriceList-js '.$tableClass));
    $th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
    foreach ($arr_flds as $column => $lblTitle) {
        if ('select_all' == $column) {
            $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="' . $lblTitle . '" type="checkbox" onclick="selectAll($(this))" class="selectAll-js"></label>', true);
        } else {
            $th->appendElement('th', array(), $lblTitle, true);
        }
    } 

foreach ($arrListing as $sn => $row) {
    $tr = $tbl->appendElement('tr', array());
    $splPriceId = $row['splprice_id'];
    $selProdId = $row['selprod_id'];
    $editListingFrm = new Form('editListingFrm-' . $splPriceId, array('id' => 'editListingFrm-' . $splPriceId));
    foreach ($arr_flds as $column => $lblTitle) {
        $tr->setAttribute('id', 'row-' . $splPriceId);
        $td = $tr->appendElement('td');
        switch ($column) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="selprod_ids[' . $splPriceId . ']" value=' . $selProdId . '></label>', true);
                break;
            case 'product_name':
                // last Param of getProductDisplayTitle function used to get title in html form.
                $txt = '<div class="item__description">';
                $productName = SellerProduct::getProductDisplayTitle($selProdId, $siteLangId, true);
                $txt .= '<div class="item__title">' . $productName . '</div>';
                $txt .= '</div>';
                $td->appendElement('plaintext', array(), $txt, true);
                break;
            case 'selprod_price':
				if($productFor == Product::PRODUCT_FOR_RENT) {
					$price = CommonHelper::displayMoneyFormat($row['sprodata_rental_price'], true, true);
				} else {
					$price = CommonHelper::displayMoneyFormat($row[$column], true, true);
				}
                $td->appendElement('plaintext', array(), $price, true);
                break;
            case 'splprice_start_date':
            case 'splprice_end_date':
                $date = date('Y-m-d', strtotime($row[$column]));
                $attr = array(
                    'readonly' => 'readonly',
                    'placeholder' => $lblTitle,
                    'data-selprodid' => $selProdId,
                    'data-id' => $splPriceId,
                    'data-oldval' => $date,
                    'id' => $column . '-' . $splPriceId,
                    'class' => 'date_js js--splPriceCol hidden sp-input field--calender',
					'data-productfor' => $productFor,
                );
                $editListingFrm->addDateField($lblTitle, $column, $date, $attr);

                $editClass = ($canEdit) ? "js--editCol edit-hover" : "";
                
                $td->appendElement('div', array("class" => $editClass, "title" => Labels::getLabel('LBL_Click_To_Edit', $siteLangId)), $date, true);
                $td->appendElement('plaintext', array(), $editListingFrm->getFieldHtml($column), true);
                break;
            case 'splprice_price':
                $selprodPrice = ($productFor == Product::PRODUCT_FOR_RENT)?$row['sprodata_rental_price']:$row['selprod_price'];
                $editClass = ($canEdit) ? "js--editCol edit-hover" : "";
                
                
                $input = '<input type="text" data-id="' . $splPriceId . '" value="' . $row[$column] . '" data-productfor="'.$productFor.'" data-price="'.$selprodPrice.'" data-selprodid="' . $selProdId . '" name="' . $column . '" data-oldval="' . $row[$column] . '" data-displayoldval="' . CommonHelper::displayMoneyFormat($row[$column], true, true) . '" class="js--splPriceCol hidden sp-input"/>';
                $td->appendElement('div', array("class" => $editClass, "title" => Labels::getLabel('LBL_Click_To_Edit', $siteLangId)), CommonHelper::displayMoneyFormat($row[$column], true, true), true);
                $td->appendElement('plaintext', array(), $input, true);
				
                $discountText = '';
				if(($productFor == Product::PRODUCT_FOR_RENT && $row['sprodata_rental_price'] > $row[$column]) || ($productFor == Product::PRODUCT_FOR_SALE && $row['selprod_price'] > $row[$column]))  {
					$discountPrice = $selprodPrice - $row[$column];
					$discountPercentage = round(($discountPrice/$selprodPrice)*100, 2);
					$discountText = $discountPercentage."% ".Labels::getLabel('LBL_off', $siteLangId);	
				}

                if(($productFor == Product::PRODUCT_FOR_RENT && $row['sprodata_rental_price'] < $row[$column]) || ($productFor == Product::PRODUCT_FOR_SALE && $row['selprod_price'] < $row[$column]))  {
					$discountValue = $selprodPrice - $row[$column];
					$discountValue = abs($discountValue);
					$discountText = Labels::getLabel('LBL_Extra_charges', $siteLangId) .": ".CommonHelper::displayMoneyFormat($discountValue, true, true);
				}

                $td->appendElement('div', array("class" => 'extracharges js--percentVal'), $discountText, true);

                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class" => "actions actions--centered"), '', true);

                $li = $ul->appendElement('li');
                $li->appendElement(
                    'a',
                    array('href' => 'javascript:void(0)', 'class' => '',
                    'title' => Labels::getLabel('LBL_Delete', $siteLangId), "onclick" => "deleteSellerProductSpecialPrice(" . $splPriceId . ")"),
                    '<i class="fa fa-trash"></i>',
                    true
                );
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$column], true);
                break;
        }
    }
}

$frm = new Form('frmSplPriceListing', array('id' => 'frmSplPriceListing'));
$frm->setFormTagAttribute('class', 'form');

echo $frm->getFormTag();
echo $tbl->getHtml();
?>
</form>
</div>
<?php
if (count($arrListing) == 0) {
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
} ?>
<?php $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSearchSpecialPricePaging'));

$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToSearchPage', 'adminLangId' => $siteLangId, 'siteLangId' => $siteLangId, 'pageSize' => $pageSize, 'removePageCentClass' => true);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
