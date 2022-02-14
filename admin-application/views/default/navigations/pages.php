<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="sectionhead">
    <h4><?php echo Labels::getLabel('LBL_navigation_Pages_Listing', $adminLangId); ?></h4>
    <?php
    $ul = new HtmlElement("ul", array("class" => "actions actions--centered"));
    $li = $ul->appendElement("li", array('class' => 'droplink'));
    $li->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'button small green', 'title' => Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
    $innerDiv = $li->appendElement('div', array('class' => 'dropwrap'));
    $innerUl = $innerDiv->appendElement('ul', array('class' => 'linksvertical'));
    
    if (isset($isParentNav) && $isParentNav && $canEdit) {
        $innerLiBack = $innerUl->appendElement('li');
        $innerLiBack->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'button small green', 'title' => Labels::getLabel('LBL_back_To_Navigations', $adminLangId), "onclick" => "searchNavigations()"), Labels::getLabel('LBL_back_To_Navigations', $adminLangId), true);
        $innerLiAdd = $innerUl->appendElement('li');
        $innerLiAdd->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'button small green', 'title' => Labels::getLabel('LBL_Add_Navigation_Page', $adminLangId), "onclick" => "addNavigationLinkForm(" . $nav_id . ",0)"), Labels::getLabel('LBL_Add_Navigation_Page', $adminLangId), true);
    }elseif($canEdit){
        $innerLiAdd = $innerUl->appendElement('li');
        $innerLiAdd->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'button small green', 'title' => Labels::getLabel('LBL_Add_Navigation_Page', $adminLangId), "onclick" => "pages(" . $nav_id . ")"), Labels::getLabel('LBL_back_To_Navigations', $adminLangId), true);
        $innerLiAdd = $innerUl->appendElement('li');
        $innerLiAdd->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'button small green', 'title' => Labels::getLabel('LBL_Add_Navigation_Page', $adminLangId), "onclick" => "addChildNavigationLinkForm(" . $nav_id . ",".$nlink_id.")"), Labels::getLabel('LBL_Add_Navigation_Page', $adminLangId), true);
    }
    echo $ul->getHtml();
    ?>

</div>
<div class="sectionbody">
    <div class="tablewrap">
        <?php /* if( isset($arrListing) && count($arrListing) ){ */
        $arr_flds = array(
            'dragdrop' => '',
            'listserial' => Labels::getLabel('LBL_#', $adminLangId),
            'nlink_identifier' => Labels::getLabel('LBL_caption', $adminLangId),
            'action' => '',
        );
        if (!$canEdit) {
            unset($arr_flds['dragdrop'], $arr_flds['action']);
        }
        $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive table--hovered', 'id' => 'pageList'));
        $th = $tbl->appendElement('thead')->appendElement('tr');
        foreach ($arr_flds as $val) {
            $e = $th->appendElement('th', array(), $val);
        }

        $sr_no = 0;
        foreach ($arrListing as $sn => $row) {
            $sr_no++;
            $tr = $tbl->appendElement('tr');
            $tr->setAttribute("id", $row['nlink_id']);
            foreach ($arr_flds as $key => $val) {
                $td = $tr->appendElement('td');
                switch ($key) {
                    case 'dragdrop':
                        $td->appendElement('i', array('class' => 'ion-arrow-move icon'));
                        $td->setAttribute("class", 'dragHandle');
                        break;
                    case 'listserial':
                        $td->appendElement('plaintext', array(), $sr_no);
                        break;
                    case 'nlink_identifier':
                        if ($row['nlink_caption'] != '') {
                            $td->appendElement('plaintext', array(), $row['nlink_caption'], true);
                            $td->appendElement('br', array());
                            $td->appendElement('plaintext', array(), '(' . $row[$key] . ')', true);
                        } else {
                            $td->appendElement('plaintext', array(), $row[$key], true);
                        }
                        break;
                    case 'action':
                        if ($canEdit) {
                            $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "addNavigationLinkForm(" . $row['nlink_nav_id'] . ", " . $row['nlink_id'] . ")"), "<i class='far fa-edit icon'></i>", true);
                            $td->appendElement('a', array('href' => "javascript:void(0)", 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Delete', $adminLangId), "onclick" => "deleteNavigationLink(" . $row['nlink_nav_id'] . ", " . $row['nlink_id'] . ")"), "<i class='fa fa-trash  icon'></i>", true);
                            if( isset($isParentNav) && $isParentNav && $row['nav_type'] == Navigations::NAVTYPE_HEADER && $row['nlink_type'] != NavigationLinks::NAVLINK_TYPE_CATEGORY_PAGE){
                                $td->appendElement('a', array('href' => "javascript:void(0)", 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Pages', $adminLangId), "onclick" => "subPages(" . $row['nlink_id'] .",". $nav_id . ")"), "<i class='ion-ios-paper icon'></i>", true);
                            }
                        }
                        break;
                    default:
                        $td->appendElement('plaintext', array(), $row[$key], true);
                        break;
                }
            }
        }
        /* } */



        if (count($arrListing) == 0) {
            $tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
        }

        echo $tbl->getHtml();
        ?>
    </div>
</div>


<script>
    $(document).ready(function() {
        $('#pageList').tableDnD({
            onDrop: function(table, row) {
                fcom.displayProcessing();
                var order = $.tableDnD.serialize('id');
                fcom.ajax(fcom.makeUrl('Navigations', 'updateNlinkOrder'), order, function(res) {
                    var ans = $.parseJSON(res);
                    if (ans.status == 1) {
                        fcom.displaySuccessMessage(ans.msg);
                    } else {
                        fcom.displayErrorMessage(ans.msg);

                    }
                });
            },
            dragHandle: ".dragHandle",
        });
    });
</script>