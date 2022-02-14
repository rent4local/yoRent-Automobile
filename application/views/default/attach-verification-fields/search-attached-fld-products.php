<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="scroll scroll-x js-scrollable table-wrap">
    <?php 
    $arr_flds = array(
        // 'select_all'=>'',
        'product_name' => Labels::getLabel('LBL_Product_Name', $siteLangId),
        'attached_fields' => Labels::getLabel('LBL_Attached_Fields', $siteLangId)
    );

    $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table'));
    $thead = $tbl->appendElement('thead');
    $th = $thead->appendElement('tr', array('class' => ''));

    foreach ($arr_flds as $key => $val) {
        if ('product_name' == $key) {
            $th->appendElement('th', array('width' => '25%'), $val);
        } else {
            $th->appendElement('th', array('width' => '75%'), $val);
        }
    }

    foreach ($arrListing as $productId => $attachedFlds) {
        $tr = $tbl->appendElement('tr', array());
        foreach ($arr_flds as $key => $val) {
            $tr->setAttribute('id', 'row-' . $productId);
            if ($key == 'product_name') {
                $title = ($canEdit) ? Labels::getLabel('LBL_Click_Here_For_Edit', $siteLangId) : '';
                $td = $tr->appendElement('td', array('class' => (($canEdit) ? 'js-product-edit cursor-pointer' : ''), 'row-id' => $productId, 'title' => $title));
            } else {
                $td = $tr->appendElement('td');
            }
            switch ($key) {
                case 'product_name':
                    // last Param of getProductDisplayTitle function used to get title in html form.
                    $productName = "<span class='js-prod-name'>" . $attachedFlds[0]['product_name'] . "</span>";
                    $td->appendElement('plaintext', array(), $productName, true);
                    break;
                case 'attached_fields':
                    $div = $td->appendElement('div', array("class" => "list-tag-wrapper", "data-scroll-height" => "150", "data-simplebar" => ""));
                    $ul = $div->appendElement("ul", array("class" => "list-tags"));
                    $fldTypeArr = VerificationFields::getFldTypeArr($siteLangId);
                    foreach ($attachedFlds as $attFlds) {
                        $is_required = ($attFlds['vflds_required'])? '<span class="spn_must_field">*</span>': '';
                        $is_active = ($attFlds['vflds_active'])? '1': '0';
                        $vflds_name = strip_tags(html_entity_decode($attFlds['vflds_name'] .' '. $is_required  .' [ '. $fldTypeArr[$attFlds['vflds_type']] .' ]', ENT_QUOTES, 'UTF-8'));
                        $li = $ul->appendElement("li", array('class' => ($is_active)?"is_active":"inactive" ));
                        
                        $removeIcon = '';
                        if ($canEdit) {
                            $removeIcon = '<i class="remove_param fa fa-times" onClick="deleteVerificationField(' . $productId . ', ' . $attFlds['vflds_id'] . ')"></i>';
                        }
                        $li->appendElement('plaintext', array(), '<span>' . $vflds_name . ' ' . $removeIcon . '</span>', true);
                        $li->appendElement('plaintext', array(), '<input type="hidden" name="attached_fields[]" value="' . $attFlds['vflds_id'] . '">', true);

}
                    break;
                default:
                    break;
            }
        }
    }
    if (count($arrListing) == 0) {
        $tbl->appendElement('tr', array('class' => 'noResult--js'))->appendElement(
            'td',
            array('colspan' => count($arr_flds)),
            Labels::getLabel('LBL_No_Record_Found', $siteLangId)
        );
    }
    
    $frm = new Form('frmAttachedVerificationFldsListing', array('id' => 'frmAttachedVerificationFldsListing'));
    $frm->setFormTagAttribute('class', 'form');
    
    echo $frm->getFormTag();
    echo $tbl->getHtml(); ?>
    </form>
    <?php
    $postedData['page'] = $page;
    echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmAttachedVerificationFldsPaging'));
    
    $pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToSearchPage', 'siteLangId' => $siteLangId, 'pageSize' => $pageSize, 'removePageCentClass' => true);
    $this->includeTemplate('_partial/pagination.php', $pagingArr, false);
    