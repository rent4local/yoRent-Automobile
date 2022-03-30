<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="scroll scroll-x js-scrollable table-wrap">
    <?php 
    $arr_flds = array(
        // 'select_all'=>'',
        'address_name' => Labels::getLabel('LBL_Address', $siteLangId),
        'linked_selprod' => Labels::getLabel('LBL_Seller_Products', $siteLangId)
    );

    $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table'));
    $thead = $tbl->appendElement('thead');
    $th = $thead->appendElement('tr', array('class' => ''));

    foreach ($arr_flds as $key => $val) {
        if ('address_name' == $key) {
            $th->appendElement('th', array('width' => '40%'), $val);
        } else {
            $th->appendElement('th', array('width' => '60%'), $val);
        }
    }

    foreach ($arrListing as $addrId => $attachedSelprods) {
        $tr = $tbl->appendElement('tr', array());
        foreach ($arr_flds as $key => $val) {
            $tr->setAttribute('id', 'row-' . $addrId);
            if ($key == 'address_name') {
                $title = ($canEdit) ? Labels::getLabel('LBL_Click_Here_For_Edit', $siteLangId) : '';
                $td = $tr->appendElement('td', array('class' => (($canEdit) ? 'js-addr-edit cursor-pointer' : ''), 'row-id' => $addrId, 'title' => $title));
            } else {
                $td = $tr->appendElement('td');
            }
            switch ($key) {
                case 'address_name':
                    // last Param of getProductDisplayTitle function used to get title in html form.
                    $addrArr = Address::getAttributesById($addrId, array('addr_name','addr_address1','addr_address2','addr_city','addr_state_id','addr_country_id', 'addr_dial_code', 'addr_phone','addr_zip'));

                    $address = $addrArr['addr_name'];
                    $address .= '<br>' .$addrArr['addr_address1'];
                    $address .= '<br>' .$addrArr['addr_address2'];
                    $address .= '<br>' .$addrArr['addr_city'];
                    $address .= '<br>' .Labels::getLabel('LBL_Zip', $siteLangId) . " - " . $addrArr['addr_zip'];
                    $address .= ', ' .States::getAttributesByLangId($siteLangId,$addrArr['addr_state_id'],'state_name');
                    $address .= ', ' .Countries::getAttributesByLangId($siteLangId,$addrArr['addr_country_id'],'country_name');
                    $address .= '<br>' .Labels::getLabel('LBL_Phone', $siteLangId) . " - " . $addrArr['addr_dial_code'] . ' ' . $addrArr['addr_phone'];

                    $productName = "<span class='js-addr-name'>" . $address ."</span>";
                    $td->appendElement('plaintext', array(), $productName, true);
                    break;
                case 'linked_selprod':
                    $div = $td->appendElement('div', array("class" => "list-tag-wrapper scroll scroll-y", "data-scroll-height" => "150"));
                    $ul = $div->appendElement("ul", array("class" => "list-tags"));
                    
                    foreach ($attachedSelprods as $attprods) {
                        
                        $selprod_name = strip_tags(html_entity_decode($attprods['selprod_title'] , ENT_QUOTES, 'UTF-8'));
                        $li = $ul->appendElement("li");
                        $removeIcon = '';
                        if ($canEdit) {
                            $removeIcon = '<i class="remove_param fa fa-times" onClick="deleteLinkedProduct(' . $addrId . ', ' . $attprods['selprod_id'] . ')"></i>';
                        }
                        $li->appendElement('plaintext', array(), '<span>' . $selprod_name . ' ' . $removeIcon . '</span>', true);
                        $li->appendElement('plaintext', array(), '<input type="hidden" name="attached_fields[]" value="' . $attprods['selprod_id'] . '">', true);

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
    
    $frm = new Form('frmLinkedAddressListing', array('id' => 'frmLinkedAddressListing'));
    $frm->setFormTagAttribute('class', 'form');
    
    echo $frm->getFormTag();
    echo $tbl->getHtml(); ?>
    </form>
    <?php
    $postedData['page'] = $page;
    echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmLinkedAddressPaging'));
    
    $pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToSearchPage', 'siteLangId' => $siteLangId, 'pageSize' => $pageSize, 'removePageCentClass' => true);
    $this->includeTemplate('_partial/pagination.php', $pagingArr, false);
    