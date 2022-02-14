<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="scroll scroll-x js-scrollable table-wrap">
    <?php if (count($arr_listing) == 0) {
        $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId));
    } else {
        $arr_flds = array(
            'listserial' => '#',
            'shipprofile_name' => Labels::getLabel('LBL_Name', $siteLangId),
            'totalProducts' => Labels::getLabel('LBL_Products', $siteLangId),
            'rates' => Labels::getLabel('LBL_Rates_for', $siteLangId),
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
                    case 'shipprofile_name':
                        $badge = '';
                        if ($row['shipprofile_default'] == 1) {
                            $badge = ' <span class="badge badge--unified-brand badge--inline badge--pill">' . Labels::getLabel('LBL_Default', $siteLangId) . '</span>';
                        }
                        $td->appendElement('plaintext', array(), $row[$key] . $badge, true);
                        break;
                    case 'rates':
                        $str = '';
                        $profileId = $row['shipprofile_id'];
                        $zoneData = (isset($zones[$profileId])) ? $zones[$profileId] : array();
                        if (!empty($zoneData)) {
                            $str = '<ul class="list-tags">';
                            //$str .= '<li class="font-bold">' . Labels::getLabel('LBL_Rates_for', $siteLangId) . '</li>';
                            foreach ($zoneData as $data) {
                                $str .= '<li><span>' . $data['shipzone_name'] . '</span></li>';
                            }
                            $str .= '</ul>';
                        }
                        $td->appendElement('plaintext', array(), $str, true);
                        break;
                    case 'action':
                        $ul = $td->appendElement("ul", array("class" => "actions"), '', true);
                        $li = $ul->appendElement("li");

                        if ($canEdit) {
                            $li->appendElement('a', array('href' => UrlHelper::generateUrl('shippingProfile', 'form', array($row['shipprofile_id'])),  'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $siteLangId)), '<i class="fa fa-edit"></i>', true);
                            if ($row['shipprofile_default'] != applicationConstants::YES) {
                                $li = $ul->appendElement("li");
                                $li->appendElement('a', array('href' => 'javascript::void(0)',  'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $siteLangId), "onclick" => "deleteRecord(" . $row['shipprofile_id'] . ")"), '<i class="fa fa-trash"></i>', true);
                            }
                        } else {
                            $li->appendElement('a', array('href' => UrlHelper::generateUrl('shippingProfile', 'form', array($row['shipprofile_id'])),  'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_View', $siteLangId)), '<i class="fa fa-eye"></i>', true);
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
