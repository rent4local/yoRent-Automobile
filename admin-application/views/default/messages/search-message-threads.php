<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
        'message_sent_by_username'=> Labels::getLabel('LBL_From', $adminLangId),
        'message_sent_to_name'=> Labels::getLabel('LBL_To', $adminLangId),
        'thread_subject' => Labels::getLabel('LBL_Subject', $adminLangId),
        'message_text' => Labels::getLabel('LBL_Message', $adminLangId),
        'message_date' => Labels::getLabel('LBL_Date', $adminLangId),
        'action' => '',
    );

$tbl = new HtmlElement('table', array('class'=>'table table-responsive table--hovered','id'=>'post'));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class'=>'tr--first'));
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr');

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'message_sent_by_username':
                $div_about_me = $td->appendElement('div', array('class'=>'avtar avtar--small'));
                $sentByUserName = "<a href='javascript:void(0)' onclick='redirectfunc(\"" . UrlHelper::generateUrl('Users') . "\", " . $row['message_sent_by'] . ")'>" . $row['message_sent_by_username'] . "</a>";

                if ($row['message_from_shop_name'] != '') {
                    $sentByShopName = "<a href='javascript:void(0)' onclick='redirectfunc(\"" . UrlHelper::generateUrl('Shops') . "\", " . $row['message_from_shop_id'] . ")'>" . $row['message_from_shop_name'] . "</a>";

                    $div_about_me->appendElement('img', array('src'=>UrlHelper::generateUrl('Image', 'shopLogo', array($row['message_from_shop_id'], $adminLangId,'MINI'), CONF_WEBROOT_FRONT_URL)));

                    $span = $td->appendElement('span', array('class'=>'avtar__name'), $sentByShopName . ' (' . $sentByUserName . ')', true);
                } else {
                    /* $name =  $row['message_sent_by_username']; */
                    $div_about_me->appendElement('img', array('src'=>UrlHelper::generateUrl('Image', 'user', array($row['message_sent_by'],'MINI',true), CONF_WEBROOT_FRONT_URL)));

                    $span = $td->appendElement('span', array('class'=>'avtar__name'), $sentByUserName, true);
                }
      

                break;
            case 'message_sent_to_name':
                $figure = $td->appendElement('figure', array('class'=>'avtar bgm-purple'));
                $sentToUserName = "<a href='javascript:void(0)' onclick='redirectfunc(\"" . UrlHelper::generateUrl('Users') . "\", " . $row['message_sent_to'] . ")'>" . $row['message_sent_to_name'] . "</a>";
                
                if ($row['message_to_shop_name'] != '') {
                    $sentByShopName = "<a href='javascript:void(0)' onclick='redirectfunc(\"" . UrlHelper::generateUrl('Shops') . "\", " . $row['message_to_shop_id'] . ")'>" . $row['message_to_shop_name'] . "</a>";

                    $figure->appendElement('img', array('src'=>UrlHelper::generateUrl('Image', 'shopLogo', array($row['message_to_shop_id'], $adminLangId,'MINI'), CONF_WEBROOT_FRONT_URL)));

                    $span = $td->appendElement('span', array('class'=>'avtar__name'), $sentByShopName . ' (' . $sentToUserName . ')', true);
                } else {
                    $figure->appendElement('img', array('src'=>UrlHelper::generateUrl('Image', 'user', array($row['message_sent_to'],'MINI',true), CONF_WEBROOT_FRONT_URL)));
                    /* $name =  $row['message_sent_to_name']; */

                    $span = $td->appendElement('span', array('class'=>'avtar__name'), $sentToUserName, true);
                }

                break;
            case 'message_text':
                $div = $td->appendElement('div', array('class'=>'listing__desc'));
                // $anchor = $div->appendElement('a', array('href'=>'#'));
                $div->appendElement('plaintext', array(), $row['message_text']);
                //$td->appendElement('plaintext', array(), FatDate::format($row['message_text'] , true));
                break;
            case 'message_date':
                $td->appendElement('span', array('class'=>'date'), FatDate::format($row['message_date'], true));
                break;
            case 'action':
                $td->appendElement('a', array('href'=>UrlHelper::generateUrl('Messages', 'view', array($row['thread_id'])),'class'=>'btn btn-clean btn-sm btn-icon','title'=>Labels::getLabel('LBL_View', $adminLangId)), "<i class='ion-eye'></i>", true);

                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
if (count($arr_listing) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
}
echo $tbl->getHtml();

$postedData['page']=$page;

echo FatUtility::createHiddenFormFromData($postedData, array(
        'name' => 'frmSearchPaging'
));

$pagingArr = array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);

$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
