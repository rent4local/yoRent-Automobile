<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
    $shopLogoFrm->setFormTagAttribute('onsubmit', 'setupShopMedia(this); return(false);');
    $shopLogoFrm->developerTags['colClassPrefix'] = 'col-md-';
    $shopLogoFrm->developerTags['fld_default_col'] = 12;
    $fld = $shopLogoFrm->getField('shop_logo');
    $fld->addFieldTagAttribute('class', 'btn btn-brand btn-sm');

    $shopBannerFrm->setFormTagAttribute('onsubmit', 'setupShopMedia(this); return(false);');
    $shopBannerFrm->developerTags['colClassPrefix'] = 'col-md-';
    $shopBannerFrm->developerTags['fld_default_col'] = 12;
    $fld = $shopBannerFrm->getField('shop_banner');
    $fld->addFieldTagAttribute('class', 'btn btn-brand btn-sm');
?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title"><?php echo Labels::getLabel('LBL_Shop_Collections', $siteLangId); ?></h5>
        <div class="">
            <a href="javascript:void(0)" onClick="shopCollections(this)" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Back_to_Collections', $siteLangId);?></a>
        </div>
    </div>
    <div class="card-body">
        <div class="col-lg-12 col-md-12">
            <div class="tabs__content">
                <div class="row ">
                    <div class="row">
                        <div class="col-md-12" id="shopFormBlock">
                            <div id="mediaResponse"></div>
                            <div class="col-md-6">
                                <div class="preview">
                                    <small class="form-text text-muted"><?php echo sprintf(Labels::getLabel('MSG_Upload_shop_banner_text', $siteLangId), '2000*500')?></small>
                                    <?php echo $shopBannerFrm->getFormHtml();?>
                                    <?php foreach ($bannerAttachments as $img) {?>

                                    <div class="profile__pic">
                                        <img src="<?php echo CommonHelper::generateUrl('image', 'shopBanner', array($img['afile_record_id'],$img['afile_lang_id'],'PREVIEW'));?>" alt="<?php echo Labels::getLabel('LBL_Shop_Banner', $siteLangId);?>">
                                    </div>
                                    <small class="form-text text-muted"><?php echo $bannerTypeArr[$img['afile_lang_id']];?></small>
                                    <div class="btngroup--fix">
                                        <a class="btn btn-brand btn-sm" href="javascript:void(0);"
                                            onClick="removeShopBanner(<?php echo $img['afile_record_id']; ?>,<?php echo $img['afile_lang_id']; ?>)"><?php echo Labels::getLabel('LBL_Remove', $siteLangId);?></a>
                                    </div>

                                    <span class="gap"></span>
                                    <?php }?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="preview">
                                    <small class="form-text text-muted"><?php echo sprintf(Labels::getLabel('MSG_UPLOAD_SHOP_BANNER_TEXT', $siteLangId), '60*60')?></small>
                                    <?php echo $shopLogoFrm->getFormHtml();?>
                                    <div class="row">
                                        <?php $count = 1; foreach ($logoAttachments as $img) {?>
                                        <div class="col-md-6">
                                            <div class="profile__pic"><img src="<?php echo CommonHelper::generateUrl('image', 'shopLogo', array($img['afile_record_id'],$img['afile_lang_id'],'SMALL'));?>"
                                                    alt="<?php echo Labels::getLabel('LBL_Shop_Logo', $siteLangId);?>"></div>
                                            <small class="form-text text-muted"><?php echo $bannerTypeArr[$img['afile_lang_id']];?></small>
                                            <div class="btngroup--fix">
                                                <a class="btn btn-brand btn-sm" href="javascript:void(0);"
                                                    onClick="removeShopLogo(<?php echo $img['afile_record_id']; ?>,<?php echo $img['afile_lang_id']; ?>)"><?php echo Labels::getLabel('LBL_Remove', $siteLangId);?></a>
                                            </div>
                                        </div>
                                        <?php if ($count == 2) {
                                            $count = 1;
                                            echo "<span class='gap'></span>";
                                        }?>
                                        <?php $count++;}?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
   
</div>
