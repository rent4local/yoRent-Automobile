<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$frmShopAgreement->setFormTagAttribute('class', 'form web_form form--horizontal');
$frmShopAgreement->setFormTagAttribute('id', 'frmShopAgreement');

$keyFld = $frmShopAgreement->getField('shop_agreemnet');
$keyFld->addFieldTagAttribute('class', 'btn btn-brand btn-sm'); 
$keyFld->addFieldTagAttribute('onchange', 'setupShopAgreement()'); 
?>
<div id="cropperBox-js"></div>
<section class="section" id="mediaForm-js">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Shop_Agreement_File', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li>
                            <a href="javascript:void(0)" onclick="shopForm(<?php echo $shopId ?>);">
                                <?php echo Labels::getLabel('LBL_General', $adminLangId); ?>
                            </a>
                        </li>
                        <li class="<?php echo (empty($shopId)) ? 'fat-inactive' : ''; ?>">
                            <a href="javascript:void(0);" <?php echo ($shopId) ? "onclick='addShopLangForm(" . $shopId . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </a>
                        </li>
                        <li><a href="javascript:void(0);"
                            <?php if ($shopId > 0) { ?>
                                onclick="shopMediaForm(<?php echo $shopId ?>);"
                            <?php }?>><?php echo Labels::getLabel('LBL_Media', $adminLangId); ?></a></li>
                        <li><a href="javascript:void(0);"
                            <?php if ($shopId > 0) { ?>
                                onclick="shopCollections(<?php echo $shopId ?>);"
                            <?php }?>><?php echo Labels::getLabel('LBL_Collection', $adminLangId); ?></a></li>
                        <li><a class="active" href="javascript:void(0);"
                            <?php if ($shopId > 0) { ?>
                                onclick="shopAgreement(<?php echo $shopId ?>);"
                            <?php } ?>><?php echo Labels::getLabel('LBL_Shop_Agreement', $adminLangId); ?></a></li>
                    </ul>
                    <div class="tabs__content tabs__content-js">
                        <div class="row justify-content-center">
                            <div class="col-md-6">
                                <div class="tabs_panel_wrap">
                                    <div class="row">
                                        <div class="col-md-12">
                                        <?php if(empty($attachment['afile_name'])) {
                                            echo $frmShopAgreement->getFormTag(); ?>
                                                <h4><?php echo Labels::getLabel('LBL_Upload_File', $adminLangId); ?></h4>
                                                <div class="field-set">
                                                    <div class="field-wraper">
                                                        <div class="field_cover">
                                                            <?php echo $frmShopAgreement->getFieldHtml('shop_agreemnet'); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php echo $frmShopAgreement->getFieldHtml('shop_id'); ?>
                                            </form>
                                        <?php } else { ?>
                                            <div class="tabs_panel">
                                                <div class="uploaded-files-main my-5">
                                                    <?php if(isset($attachment) && !empty($attachment['afile_name'])) { ?>
                                                    <span class="file-name">
                                                        <i class="icn fas fa-file-pdf"></i>
                                                        <a href="<?php echo CommonHelper::generateUrl('Shops', 'downloadDigitalFile', [$attachment["afile_record_id"], $attachment['afile_id'], AttachedFile::FILETYPE_SHOP_AGREEMENT]); ?>"
                                                            title="<?php echo Labels::getLabel('LBL_Download_file', $adminLangId); ?>"
                                                            download><b class="doc-title"><span><?php echo $attachment["afile_name"]; ?></span></b>
                                                        </a>
                                                    </span>
                                                    <a class="delete" href="javascript:void(0);" onClick="deleteShopAgreement(<?php echo $attachment['afile_id'] . ', ' . $shopId; ?>)">
                                                        <i class="icn fas fa-trash"></i>
                                                    </a>
                                                    <?php }else { ?>
                                                        <span><?php echo Labels::getLabel('LBL_No_Agreement_File_Attached', $adminLangId); ?></span>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>