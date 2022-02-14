<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$defaultDimenssion = $dimensions;

$frm->setFormTagAttribute('class', 'web_form');
$frm->setFormTagAttribute('onsubmit', 'setupBanners(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;


$extUrlField = $frm->getField('banner_url');
$extUrlField->addFieldTagAttribute('placeholder', 'http://');

$fld = $frm->getField('auto_update_other_langs_data');
if (null != $fld) {
    $fld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
    $fld->developerTags['cbHtmlAfterCheckbox'] = '';
}

$bannerLangFld = $frm->getField('banner_lang_id');
$bannerLangFld->addFieldTagAttribute('class', 'banner-language-js');

$screenFld = $frm->getField('banner_screen');
$screenFld->addFieldTagAttribute('class', 'prefDimensions-js');

$checkBannerPos = 0;
if ($collection_layout_type == Collections::TYPE_BANNER_LAYOUT3) {
    $screenFld->setFieldTagAttribute('disabled', 'disabled');
}
if ($collection_layout_type == Collections::TYPE_BANNER_LAYOUT4) {
    $posFld = $frm->getField('banner_position');
	$checkBannerPos = $posFld->value;
	$defaultDimenssion = $dimensions[$checkBannerPos];
	if ($bannerId > 0) {
		$posFld->setFieldTagAttribute('disabled', 'disabled');
	}
	
}


$bannerFld = $frm->getField('banner');
$bannerFld->addFieldTagAttribute('class', 'btn btn-brand btn-sm');
$bannerFld->addFieldTagAttribute('onChange', 'bannerPopupImage(this, ' . $checkBannerPos . ')');
$bannerFld->htmlAfterField = '<small class="text--small preferredDimensions-js">' . sprintf(Labels::getLabel('LBL_Preferred_Dimensions_%s', $adminLangId), '1350 x 405') . '</small>';


$siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
?>
<div id="cropperBox-js"></div>
<div id="mediaForm-js">
    <div class="sectionhead" style=" padding-bottom:20px">
        <h4><?php echo Labels::getLabel('LBL_Banner_Setup', $adminLangId); ?>
        </h4>
        <a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="reloadBannersList(<?php echo $collection_id; ?>)";><?php echo Labels::getLabel('LBL_Back', $adminLangId); ?></a>		
    </div>
    <div class="tabs_panel">
        <?php echo $frm->getFormTag(); ?>
        <div class="row">
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label">
                            <?php
                            $fld = $frm->getField('banner_title[' . $siteDefaultLangId . ']');
                            echo $fld->getCaption();
                            ?>
                            <span class="spn_must_field">*</span></label>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $frm->getFieldHtml('banner_title[' . $siteDefaultLangId . ']'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label">
                            <?php
                            $fld = $frm->getField('banner_url');
                            echo $fld->getCaption();
                            ?>
                        </label>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $frm->getFieldHtml('banner_url'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label">
                            <?php
                            $fld = $frm->getField('banner_target');
                            echo $fld->getCaption();
                            ?>
                            <span class="spn_must_field">*</span></label>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $frm->getFieldHtml('banner_target'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
            if (!empty($translatorSubscriptionKey) && count($otherLangData) > 0) {
                ?>
                <div class="col-md-6">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <label class="field_label">
                            </label>
                        </div>
                        <div class="field-wraper w-auto">
                            <div class="field_cover">
                                <?php echo $frm->getFieldHtml('auto_update_other_langs_data'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="p-4 mb-4 border rounded">
            <h3 class="mb-4"><?php echo Labels::getLabel('LBL_Banner', $adminLangId); ?></h3>
            <div class="row">
                <div class="col-md-12">
                    <?php  
                    /* [ MEDIA INSTRUCTIONS START HERE */
                    $tpl = new FatTemplate('', '');
                    $tpl->set('adminLangId', $adminLangId);
                    echo $tpl->render(false, false, '_partial/imageUploadInstructions.php', true, true);
                    /* ] */    
                    ?>
                </div>
            
                <?php if ($collection_layout_type == Collections::TYPE_BANNER_LAYOUT4) { ?>
                    <div class="col-md-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                    <?php
                                    $fld = $frm->getField('banner_position');
                                    echo $fld->getCaption();
                                    ?>
                                </label>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHtml('banner_position'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div class="col-md-4">
                    <div class="field-set">
                        <div class="caption-wraper"><label class="field_label">
                                <?php
                                $fld = $frm->getField('banner_lang_id');
                                echo $fld->getCaption();
                                ?>
                            </label></div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $frm->getFieldHtml('banner_lang_id'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="field-set">
                        <div class="caption-wraper"><label class="field_label">
                                <?php
                                $fld = $frm->getField('banner_screen');
                                echo $fld->getCaption();
                                ?>
                            </label></div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $frm->getFieldHtml('banner_screen'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="field-set">
                        <div class="caption-wraper"><label class="field_label">
                            </label></div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $frm->getFieldHtml('banner'); ?>
                                <?php
                                foreach ($mediaLanguages as $key => $data) {
                                    foreach ($screenArr as $key1 => $screen) {
                                        echo $frm->getFieldHtml('banner_image_id[' . $key . '_' . $key1 . ']');
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6" id="banner-image-listing">

                </div>
            </div>
        </div>
        <?php
        if (!empty($otherLangData)) {
            foreach ($otherLangData as $langId => $data) {
                ?>
                <div class="accordians_container accordians_container-categories" defaultLang= "<?php echo $siteDefaultLangId; ?>" language="<?php echo $langId; ?>" id="accordion-language_<?php echo $langId; ?>" onClick="translateBannerData(this)">
                    <div class="accordian_panel">
                        <span class="accordian_title accordianhead accordian_title" id="collapse_<?php echo $langId; ?>">
                            <?php
                            echo $data . " ";
                            echo Labels::getLabel('LBL_Language_Data', $adminLangId);
                            ?>
                        </span>
                        <div class="accordian_body accordiancontent" style="display: none;">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="field-set">
                                        <div class="caption-wraper">
                                            <label class="field_label">
                                                <?php
                                                $fld = $frm->getField('banner_title[' . $langId . ']');
                                                echo $fld->getCaption();
                                                ?>
                                            </label>
                                        </div>
                                        <div class="field-wraper">
                                            <div class="field_cover">
                                                <?php echo $frm->getFieldHtml('banner_title[' . $langId . ']'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="field-set d-flex align-items-center">
                    <div class="field-wraper w-auto">
                        <div class="field_cover">
                            <?php echo $frm->getFieldHtml('btn_submit'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        echo $frm->getFieldHtml('collection_layout_type');
        echo $frm->getFieldHtml('banner_min_width');
        echo $frm->getFieldHtml('banner_min_height');
        echo $frm->getFieldHtml('collection_id');
        echo $frm->getFieldHtml('banner_id');
        ?>
        </form>
        <?php echo $frm->getExternalJS(); ?>
    </div>
</div>

<script>
    var width = <?php echo $defaultDimenssion[applicationConstants::SCREEN_DESKTOP]['width']; ?>;
    var height = <?php echo $defaultDimenssion[applicationConstants::SCREEN_DESKTOP]['height']; ?>;
    var aspectRatio = <?php echo $defaultDimenssion[applicationConstants::SCREEN_DESKTOP]['width']; ?> / <?php echo $defaultDimenssion[applicationConstants::SCREEN_DESKTOP]['height']; ?>;

    $('input[name=banner_min_width]').val(width);
    $('input[name=banner_min_height]').val(height);

    $(document).on('change', 'select[name="banner_position"]', function () {
        var bannerPosition = parseInt($(this).val());
        var bannerLayout = parseInt($('input[name="collection_layout_type"]').val());
        var bannerHeight = <?php echo $defaultDimenssion[applicationConstants::SCREEN_DESKTOP]['height']; ?>;
        var bannerWidth = <?php echo $defaultDimenssion[applicationConstants::SCREEN_DESKTOP]['width']; ?>;

		<?php if ($collection_layout_type == Collections::TYPE_BANNER_LAYOUT4) { ?>
        if (bannerPosition == <?php echo Collections::BANNER_POSITION_RIGHT; ?>) {
			<?php $otherDimenssions = $dimensions[Collections::BANNER_POSITION_RIGHT];?>
			var bannerHeight = <?php echo $otherDimenssions[applicationConstants::SCREEN_DESKTOP]['height'];?>;
            var bannerWidth = <?php echo $otherDimenssions[applicationConstants::SCREEN_DESKTOP]['width'];?>;
		}
		<?php } ?>
        $('.preferredDimensions-js').html((langLbl.preferredDimensions).replace(/%s/g, bannerWidth + ' x ' + bannerHeight));

        $('input[name=banner_min_width]').val(bannerWidth);
        $('input[name=banner_min_height]').val(bannerHeight);
        aspectRatio = bannerWidth / bannerHeight;
    });


    $(document).on('change', '.prefDimensions-js', function () {
        var screenDesktop = <?php echo applicationConstants::SCREEN_DESKTOP ?>;
        var screenIpad = <?php echo applicationConstants::SCREEN_IPAD ?>;
        if ($(this).val() == screenDesktop) {
            width = <?php echo $defaultDimenssion[applicationConstants::SCREEN_DESKTOP]['width']; ?>;
            height = <?php echo $defaultDimenssion[applicationConstants::SCREEN_DESKTOP]['height']; ?>;
        } else if ($(this).val() == screenIpad) {
            width = <?php echo $defaultDimenssion[applicationConstants::SCREEN_IPAD]['width']; ?>;
            height = <?php echo $defaultDimenssion[applicationConstants::SCREEN_IPAD]['height']; ?>;
        } else {
            width = <?php echo $defaultDimenssion[applicationConstants::SCREEN_MOBILE]['width']; ?>;
            height = <?php echo $defaultDimenssion[applicationConstants::SCREEN_MOBILE]['height']; ?>;
        }
        $('.preferredDimensions-js').html((langLbl.preferredDimensions).replace(/%s/g, width + ' x ' + height));
        $('input[name=banner_min_width]').val(width);
        $('input[name=banner_min_height]').val(height);
        aspectRatio = width / height;
    });
    $("document").ready(function () {
        $(".prefDimensions-js").trigger('change');
        $('select[name="banner_position"]').trigger('change');
    });
</script>
