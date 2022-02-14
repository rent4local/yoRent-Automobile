<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
        'listserial'=>'#',
        'option_identifier'=>Labels::getLabel('LBL_Option_Name', $siteLangId)
    );
if ($canEdit) {
    $arr_flds = array_merge($arr_flds, array('action'=>''));
}
if (count($arr_listing) > 0 && $canEdit) {
    $arr_flds = array_merge(array('select_all'=>''), $arr_flds);
}

$tableClass = '';
if (0 < count($arr_listing)) {
	$tableClass = "table-justified";
}

$tbl = new HtmlElement(
    'table',
    array('width'=>'100%', 'class'=>'table '.$tableClass, 'id'=>'options')
);

$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    if ('select_all' == $key) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js">'.$val.'</label>', true);
    } else {
        $th->appendElement('th', array(), $val);
    }
}

$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr');
    $tr->setAttribute("id", $row['option_id']);

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="option_id[]" value='.$row['option_id'].'></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
			case 'option_identifier':
				$optionName = (!empty($row['option_name'])) ? $row['option_name'] : $row['option_identifier'];
				$html = '<div class="item">
				<div class="item__description">
					<div class="item__title">'.$optionName.'</div>
					<div class="item__brand"> (' . $row[$key] . ') </div>
				</div></div>';
				$td->appendElement('plaintext', array(), $html, true);
				break;
            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions"));
                /* if(!in_array($row['option_type'],$ignoreOptionValues)){
                    $li = $ul->appendElement("li");
                    $li->appendElement('a',array(
                    'href'=>UrlHelper::generateUrl('OptionValues',
                    'index',array($row['option_id'])),
                    'class'=>'button small green',
                    'title'=>'Option Values'
                    ),
                    '<i class="ion-navicon-round icon"></i>', true);
                } */
                $li = $ul->appendElement("li");
                $li->appendElement(
                    'a',
                    array(
                    'href'=>'javascript:void(0)',
                    'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Edit', $siteLangId),
                    "onclick"=>"optionForm(".$row['option_id'].")"),
                    '<i class="fa fa-edit"></i>',
                    true
                );

                $li = $ul->appendElement("li");
                $li->appendElement(
                    'a',
                    array(
                    'href'=>"javascript:void(0)", 'class'=>'button small green',
                    'title'=>Labels::getLabel('LBL_Delete', $siteLangId),"onclick"=>"deleteOptionRecord(".$row['option_id'].")"),
                    '<i class="fa fa-trash"></i>',
                    true
                );
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
$frm = new Form('frmOptionListing', array('id'=>'frmOptionListing'));
$frm->setFormTagAttribute('class', 'form actionButtons-js');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', UrlHelper::generateUrl('Seller', 'bulkOptionsDelete'));
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml();
if (count($arr_listing) == 0) {
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>$message));
}
?>
</form>
<?php

$postedData['page']=$page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmOptionsSearchPaging'));

$pagingArr=array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'siteLangId' => $siteLangId, 'pageSize' => $pageSize, 'removePageCentClass' => true);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
