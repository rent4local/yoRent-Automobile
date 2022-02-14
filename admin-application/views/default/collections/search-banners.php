<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="sectionhead" style=" padding-bottom:20px">
    <h4><?php echo Labels::getLabel('LBL_Banners_Listing', $adminLangId); ?>
    </h4>
    <a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="bannerForm(<?php echo $collection_id; ?>, 0)" ;>
        <?php echo Labels::getLabel('LBL_ADD_NEW', $adminLangId); ?>
    </a>
</div>
<?php
$basePath = UrlHelper::generateFullFileUrl('', '', [], CONF_WEBROOT_FRONT_URL);
$uploadPath = $basePath . CONF_UPLOADS_FOLDER_NAME .'/'. AttachedFile::FILETYPE_BANNER_PATH; 
$arr_flds = array(
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'banner_title' => Labels::getLabel('LBL_Title', $adminLangId),
    'banner_img' => Labels::getLabel('LBL_Image', $adminLangId),
    'banner_target' => Labels::getLabel('LBL_Target', $adminLangId),
    'banner_active' => Labels::getLabel('LBL_Status', $adminLangId),
    'action' => '',
);
if (!$canEdit) {
    unset($arr_flds['action']);
}

$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive table--hovered'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}
$sr_no = 0;
/* $sr_no = $page==1?0:$pageSize*($page-1); */
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array());
    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'banner_target':
                $td->appendElement('plaintext', array(), $linkTargetsArr[$row[$key]], true);
                break;
            case 'banner_title':
                $title = ($row['banner_title'] != '') ? $row['banner_title'] : $row['promotion_name'];
                $td->appendElement('plaintext', array(), $title, true);
                break;
            case 'banner_active':
                /* $td->appendElement('plaintext', array(), $activeInactiveArr[$row[$key]], true); */
                $active = "";
                if ($row['banner_active']) {
                    $active = 'checked';
                }
                $statusAct = ($canEdit === true) ? 'toggleBannerStatus(event,this,' . applicationConstants::YES . ')' : 'toggleBannerStatus(event,this,' . applicationConstants::NO . ')';
                $statusClass = ($canEdit === false) ? 'disabled' : '';
                $str = '<label class="statustab -txt-uppercase">
                     <input ' . $active . ' type="checkbox" id="switch' . $row['banner_id'] . '" value="' . $row['banner_id'] . '" onclick="' . $statusAct . '" class="switch-labels"/>
                     <i class="switch-handles ' . $statusClass . '"></i></label>';
                $td->appendElement('plaintext', array(), $str, true);
                break;
            case 'banner_img':
                $desktop_url = '';
                $tablet_url = '';
                $mobile_url = '';
                $disUrl = '';
                if (!AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BANNER, $row['banner_id'], 0, $adminLangId)) {
                    continue 2;
                } else {
                    $slideArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BANNER, $row['banner_id'], 0, $adminLangId);
                    foreach ($slideArr as $slideScreen) {
                        $uploadedTime = AttachedFile::setTimeParam($slideScreen['afile_updated_at']);
                        switch ($slideScreen['afile_screen']) {
                            case applicationConstants::SCREEN_MOBILE:
                                if (!file_exists(CONF_UPLOADS_PATH.'/'.AttachedFile::FILETYPE_BANNER_PATH . $slideScreen['afile_physical_path'])) { 
                                    $mobile_url = $basePath.'images/defaults/3/slider-default.png';
                                } else {
                                    $mobile_url = $uploadPath. $slideScreen['afile_physical_path']; 
                                }
                                break;
                            case applicationConstants::SCREEN_IPAD:
                                if (!file_exists(CONF_UPLOADS_PATH.'/'.AttachedFile::FILETYPE_BANNER_PATH . $slideScreen['afile_physical_path'])) { 
                                    $tablet_url = $basePath.'images/defaults/3/slider-default.png';
                                } else {
                                    $tablet_url = $uploadPath. $slideScreen['afile_physical_path']; 
                                }
                                break;
                            case applicationConstants::SCREEN_DESKTOP:
                                if (!file_exists(CONF_UPLOADS_PATH.'/'.AttachedFile::FILETYPE_BANNER_PATH . $slideScreen['afile_physical_path'])) { 
                                    $desktop_url = $basePath.'images/defaults/3/slider-default.png';
                                } else {
                                    $desktop_url = $uploadPath. $slideScreen['afile_physical_path']; 
                                }
                                $disUrl = $desktop_url;
                                break;
                        }
                    }
                }

                $uploadedTime = AttachedFile::setTimeParam($row['banner_updated_on']);
                $img = '<img src="' . $disUrl . '"  style="max-height: 50px; "/>';
                $td->appendElement('plaintext', array(), $img, true);
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "bannerForm(" . $collection_id . "," . $row['banner_id'] . ")"), "<i class='far fa-edit icon'></i>", true);
                    /* $li = $ul->appendElement("li");
                    $li->appendElement('a', array('href'=>"javascript:void(0)", 'class'=>'button small green', 'title'=>'Delete',"onclick"=>"deleteBanner(".$row['banner_id'].")"),'<i class="fa fa-trash  icon"></i>', true); */
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
echo $tbl->getHtml();
/* $postedData['page']=$page;
$postedData['collection_id']=$collection_id;
echo FatUtility::createHiddenFormFromData($postedData, array(
        'name' => 'frmListingSearchPaging'
));
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'pageSize'=>$pageSize,'recordCount'=>$recordCount,'callBackJsFunc'=>'goToBannerSearchPage','adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false); */
?>