<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'select_all' => Labels::getLabel('LBL_Select_all', $adminLangId),
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'post_title' => Labels::getLabel('LBL_Post_Title', $adminLangId),
    'categories' => Labels::getLabel('LBL_Category', $adminLangId),
    'post_published_on' => Labels::getLabel('LBL_Published_Date', $adminLangId),
    'post_published' => Labels::getLabel('LBL_Post_Status', $adminLangId),
    'action' => '',
);
if (!$canEdit) {
    unset($arr_flds['select_all'], $arr_flds['action']);
}
$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive table--hovered', 'id' => 'post'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    if ('select_all' == $key) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="' . $val . '" type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"></label>', true);
    } else {
        $e = $th->appendElement('th', array(), $val);
    }
}


$sr_no = $page == 1 ? 0 : $pageSize * ($page - 1);

foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr');
    if ($row['post_published'] == 1) {
        $tr->setAttribute("id", $row['post_id']);
    }

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="post_ids[]" value=' . $row['post_id'] . '></label>', true);
                break;
            case 'post_published_on':
                $td->appendElement('plaintext', array(), FatDate::format($row['post_published_on'], true));
                break;
            case 'post_added_on':
                $td->appendElement('plaintext', array(), FatDate::format($row['post_added_on'], true));
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'post_title':
                if ($row['post_title'] != '') {
                    $td->appendElement('plaintext', array(), $row['post_title'], true);
                    $td->appendElement('br', array());
                    $td->appendElement('plaintext', array(), '(' . $row[$key] . ')', true);
                } else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }
                break;
            case 'post_published':
                $postStatusArr = applicationConstants::getBlogPostStatusArr($adminLangId);
                $td->appendElement('plaintext', array(), $postStatusArr[$row[$key]], true);
                break;
            case 'child_count':
                if ($row[$key] == 0) {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                } else {
                    $td->appendElement('a', array('href' => UrlHelper::generateUrl('BlogPostCategories', 'index', array($row['post_id'])), 'title' => Labels::getLabel('LBL_View_Categories', $adminLangId)), $row[$key]);
                }
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "addBlogPostForm(" . $row['post_id'] . ")"), "<i class='far fa-edit icon'></i>", true);
                    $td->appendElement('a', array('href' => "javascript:void(0)", 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Delete', $adminLangId), "onclick" => "deleteRecord(" . $row['post_id'] . ")"), "<i class='fa fa-trash  icon'></i>", true);
                }
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
if (count($arr_listing) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
}

$frm = new Form('frmBlogPostListing', array('id' => 'frmBlogPostListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap actionButtons-js');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml(); ?>
</form>
<?php $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array(
    'name' => 'frmSearchPaging'
));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
