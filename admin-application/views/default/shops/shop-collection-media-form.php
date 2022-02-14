<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('onsubmit', 'uploadCollectionImage(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 6;
$fld = $frm->getField('collection_image');
$fld->addFieldTagAttribute('class', 'btn btn-brand btn-sm');
$fld->addFieldTagAttribute('onChange', 'collectionPopupImage(this)');
$fld->htmlAfterField = '<span class="text--small"> ' . sprintf(Labels::getLabel('MSG_Preferred_Dimenssions_is_%s', $adminLangId), '610*343') . '</span>';
if (isset($scollection_id) && $scollection_id > 0) {
    $scollection_id = $scollection_id;
} else {
    $scollection_id = 0;
} ?>
<div id="cropperBox-js"></div>
<div id="mediaForm-js">
<div class="sectionhead" style=" padding-bottom:20px">
    <h4><?php echo Labels::getLabel('LBL_Collection_Setup', $adminLangId); ?>
    </h4>
    <a href="javascript:void(0)" class="btn-clean btn-sm btn-icon btn-secondary" onClick="shopCollections(<?php echo $shop_id;?>)" ;><i class="fas fa-arrow-left"></i></a>
</div>
    <ul class="tabs_nav tabs_nav--internal">
        <li><a onclick="getShopCollectionGeneralForm(<?php echo $shop_id; ?>, <?php echo $scollection_id; ?>);"
                href="javascript:void(0)"><?php echo Labels::getLabel('TXT_GENERAL_media', $adminLangId);?></a></li>
        <li class="<?php echo (0 == $scollection_id) ? 'fat-inactive' : ''; ?>">
            <a href="javascript:void(0);"
                <?php echo (0 < $scollection_id) ? "onclick='editShopCollectionLangForm(" . $shop_id . "," . $scollection_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
            </a>
        </li>

        <li><a onclick="sellerCollectionProducts(<?php echo $scollection_id; ?>,<?php echo $shop_id; ?>);"
                href="javascript:void(0);"><?php echo Labels::getLabel('TXT_LINK', $adminLangId);?></a></li>
        <li>
            <a class="active" onclick="collectionMediaForm(<?php echo $shop_id; ?>,<?php echo $scollection_id ?>)"
                href="javascript:void(0);"> <?php echo Labels::getLabel('TXT_MEDIA', $adminLangId);?> </a>
        </li>
    </ul>
    <div class="tabs_panel_wrap">
        <div class="form__subcontent">
            <div class="preview" id="shopFormBlock">
                <div class="col-sm-12">
                    <?php  
                    /* [ MEDIA INSTRUCTIONS START HERE */
                    $tpl = new FatTemplate('', '');
                    $tpl->set('adminLangId', $adminLangId);
                    echo $tpl->render(false, false, '_partial/imageUploadInstructions.php', true, true);
                    /* ] */  ?>
                </div>
                <?php echo $frm->getFormHtml();?>
                <div id="imageListing" class="row"></div>
            </div>
        </div>
    </div>
</div>
<script>
var collectionMediaWidth = '610';
var collectionMediaHeight = '343';
</script>