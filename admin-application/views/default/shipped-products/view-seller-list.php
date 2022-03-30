<div class="delivery-term">
    <div id="catalogToolTip">
    <h2 class="block-title"><?php echo Labels::getLabel('LBL_Sellers_List', $adminLangId); ?></h2>
        <?php $arr_flds = array(
            'listserial'    =>    Labels::getLabel('LBL_#', $adminLangId),
            'user_name' => Labels::getLabel('LBL_Seller_name', $adminLangId),
            'shop_identifier' => Labels::getLabel('LBL_shop_name', $adminLangId),
        );
        $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table--hovered table-responsive'));
        $th = $tbl->appendElement('thead')->appendElement('tr');
        foreach ($arr_flds as $val) {
            $e = $th->appendElement('th', array(), $val);
        }
        $sr_no = $page == 1 ? 0 : $pageSize * ($page - 1);
        foreach ($arrListing as $sn => $row) {
            $sr_no++;
            $tr = $tbl->appendElement('tr');
            foreach ($arr_flds as $key => $val) {
                $td = $tr->appendElement('td');
                switch ($key) {
                    case 'listserial':
                        $td->appendElement('plaintext', array(), $sr_no);
                        break;
                    case 'user_name':
                        $td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("' . UrlHelper::generateUrl('Users') . '", ' . $row['user_id'] . ')'), $row['user_name'], true);
                        break;
                    case 'shop_identifier':
                        $td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("' . UrlHelper::generateUrl('Shops') . '", ' . $row['shop_id'] . ')'), $row['shop_identifier'], true);
                        break;
                    default:
                        $td->appendElement('plaintext', array(), $row[$key], true);
                        break;
                }
            }           
        }
        if (count($arrListing) == 0) {
            $tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
        }
        echo $tbl->getHtml(); 
        $postedData['page'] = $page;
        echo FatUtility::createHiddenFormFromData($postedData, array(
            'name' => 'frmShippedProductsPaging'
        ));
        $pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'pageSize' => $pageSize, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
        $this->includeTemplate('_partial/pagination.php', $pagingArr, false);
        
        ?>
    </div>
</div>