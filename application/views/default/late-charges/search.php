<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="scroll scroll-x js-scrollable table-wrap">
    <?php if (count($arr_listing) == 0) {
        $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId));
    } else {
        $arr_flds = array(
            'listserial' => Labels::getLabel('LBL_#', $siteLangId),
			'lcp_identifier' => Labels::getLabel('LBL_Name', $siteLangId),
			'lcp_amount_type' => Labels::getLabel('LBL_Type', $siteLangId),
			'lcp_amount' => Labels::getLabel('LBL_Amount', $siteLangId),
			'totalProducts' => Labels::getLabel('LBL_Products', $siteLangId),
			'action' => Labels::getLabel('', $siteLangId)
        );
        $tableClass = '';
        if (0 < count($arr_listing)) {
            $tableClass = "table-justified";
        }
        $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table ' . $tableClass));
        $th = $tbl->appendElement('thead')->appendElement('tr');
        foreach ($arr_flds as $key => $val) {
            $th->appendElement('th', array(), $val);
        }

        $sr_no = ($page == 1) ? 0 : ($pageSize * ($page - 1));
        foreach ($arr_listing as $sn => $row) {
            $sr_no++;
            $tr = $tbl->appendElement('tr', array());
				foreach ($arr_flds as $key => $val) {
            $td = $tr->appendElement('td');
            switch ($key) {
                case 'listserial':
                    $td->appendElement('plaintext', array(), $sr_no);
                    break;
                case 'lcp_identifier':
                    $badge = '';
                    if ($row['lcp_is_default'] == 1) {
                        $badge = ' <span class="badge badge--unified-brand badge--inline badge--pill">' . Labels::getLabel('LBL_Default', $siteLangId) . '</span>';
                    }
                    $td->appendElement('plaintext', array(), $row[$key] . $badge, true);
                    break;
				case 'lcp_amount_type' :
					$typeStr = (isset($chargesType[$row[$key]])) ? $chargesType[$row[$key]] : ""; 
					$td->appendElement('plaintext', array(), $typeStr, true);
                    break;
				
                case 'action':
                    if ($canEdit) {
                        $td->appendElement('a', array('href' => UrlHelper::generateUrl('lateCharges', 'form', array($row['lcp_id'])),  'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $siteLangId)), '<i class="fa fa-edit icon"></i>', true);
                        if ($row['lcp_is_default'] != applicationConstants::YES) {
                            $td->appendElement('a', array('href' => 'javascript:void(0)',  'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $siteLangId), "onclick" => "deleteRecord(" . $row['lcp_id'] . ")"), '<i class="fa fa-trash icon"></i>', true);
                        }
                    }
                    break;
                default:
                    $td->appendElement('plaintext', array(), $row[$key], true);
                    break;
            }
        }
            
			}


        $frm = new Form('frmProfileListing', array('id' => 'frmProfileListing'));
        $frm->setFormTagAttribute('class', 'web_form last_td_nowrap actionButtons-js');
        $frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
        echo $frm->getFormTag();
        echo $tbl->getHtml(); ?>
        </form>
</div>
<?php $postedData['page'] = $page;
        echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmProfileSearchPaging'));
        $pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'siteLangId' => $siteLangId, 'pageSize' => $pageSize, 'removePageCentClass' => true);
        $this->includeTemplate('_partial/pagination.php', $pagingArr, false);
    }