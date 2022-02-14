<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$mediaFrm->setFormTagAttribute('class', 'form form--horizontal');
$mediaFrm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$mediaFrm->developerTags['fld_default_col'] = 12;
$mediaFrm->setFormTagAttribute('onsubmit', 'setupPromotionMedia(this); return(false);');

$uploadfld = $mediaFrm->getField('banner_image');
$uploadfld->addFieldTagAttribute('onChange', 'popupImage(this)');

$langFld = $mediaFrm->getField('lang_id');
$langFld->addFieldTagAttribute('class', 'banner-language-js');

$screenFld = $mediaFrm->getField('banner_screen');
$screenFld->addFieldTagAttribute('class', 'banner-screen-js');

$htmlAfterField = '';

$width = 1350; 
$height = 405;

if (!empty($bannerSizeArr)) {
    if ($promotionType == Promotion::TYPE_BANNER) { 
        $width = $bannerSizeArr[applicationConstants::SCREEN_DESKTOP]['width'];
        $height = $bannerSizeArr[applicationConstants::SCREEN_DESKTOP]['height'];
    } elseif($promotionType == Promotion::TYPE_SLIDES) {
        $width = $bannerSizeArr['width'];
        $height = $bannerSizeArr['height'];
    }
    $htmlAfterField = '<span class="form-text text-muted uploadimage--info" > '. Labels::getLabel('LBL_Preferred_Dimensions', $siteLangId) .  ' '. $width . ' * ' . $height.'</span>';
}

$htmlAfterField.='<div id="image-listing-js"></div>';
$uploadfld->htmlAfterField = $htmlAfterField;
?>
<div class="tabs tabs--small   tabs--scroll clearfix setactive-js">
    <ul>
        <li><a href="javascript:void(0);" onClick="promotionForm(<?php echo $promotionId;?>)"><?php echo Labels::getLabel('LBL_General', $siteLangId);?></a></li>
		<li class="<?php echo (0 == $promotionId) ? 'fat-inactive' : ''; ?>">
            <a href="javascript:void(0);" <?php echo (0 < $promotionId) ? "onclick='promotionLangForm(" . $promotionId . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                <?php echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
            </a>
        </li>
        <?php if ($promotionType == Promotion::TYPE_BANNER || $promotionType == Promotion::TYPE_SLIDES) {?>
        <li class="is-active"><a href="javascript:void(0)"
            <?php if ($promotionId>0) { ?>
                onClick="promotionMediaForm(<?php echo $promotionId;?>)"
            <?php }?>><?php echo Labels::getLabel('LBL_Media', $siteLangId); ?></a></li>
        <?php }?>
    </ul>
</div>
<div class="tabs__content">
    <div class="row">
        <div class="col-md-8">
            <div> 
            <?php 
            /* [ MEDIA INSTRUCTIONS START HERE */
            $tpl = new FatTemplate('', '');
            $tpl->set('siteLangId', $siteLangId);
            echo $tpl->render(false, false, '_partial/imageUploadInstructions.php', true, true);
            /* ] */    
            ?>
            </div>
            <?php echo $mediaFrm->getFormHtml(); ?>
        </div>
    </div>
</div>

<script>
$('input[name=banner_min_width]').val(<?php echo $width;?>);
$('input[name=banner_min_height]').val(<?php echo $height;?>);
var aspectRatio = <?php echo $width / $height;?>;

$(document).on('change','.banner-screen-js',function() {
    var promotionType = <?php echo $promotionType ?>;
    var screenDesktop = <?php echo applicationConstants::SCREEN_DESKTOP ?>;
    var screenIpad = <?php echo applicationConstants::SCREEN_IPAD ?>;
    
    <?php if ($promotionType == Promotion::TYPE_SLIDES) { ?>
        if ($(this).val() == screenDesktop) {
            var swidth = <?php echo $bannerSizeArr['width']; ?>;
            var sheight = <?php echo $bannerSizeArr['height']; ?>;
        
            $('.uploadimage--info').html((langLbl.preferredDimensions).replace(/%s/g, swidth + ' * ' + sheight));
            $('input[name=banner_min_width]').val(swidth);
            $('input[name=banner_min_height]').val(sheight);
            aspectRatio = swidth / sheight;
        } else if($(this).val() == screenIpad) {
            var ipwidth = <?php echo $bannerSizeArr['width']; ?>;
            var ipheight = <?php echo $bannerSizeArr['height']; ?>;
            $('.uploadimage--info').html((langLbl.preferredDimensions).replace(/%s/g, ipwidth + ' * ' + ipheight));
            $('input[name=banner_min_width]').val(ipwidth);
            $('input[name=banner_min_height]').val(ipheight);
            aspectRatio = ipwidth / ipheight;
        } else {
            var mbwidth = <?php echo $bannerSizeArr['width']; ?>;
            var mbheight = <?php echo $bannerSizeArr['height']; ?>;
            $('.uploadimage--info').html((langLbl.preferredDimensions).replace(/%s/g, mbwidth + ' * ' + mbheight));
            $('input[name=banner_min_width]').val(mbwidth);
            $('input[name=banner_min_height]').val(mbheight);
            aspectRatio = mbwidth / mbheight;
        }
    <?php } elseif($promotionType == Promotion::TYPE_BANNER) { ?>
        if ($(this).val() == screenDesktop) {
            var swidth = <?php echo $bannerSizeArr[applicationConstants::SCREEN_DESKTOP]['width']; ?>;
            var sheight = <?php echo $bannerSizeArr[applicationConstants::SCREEN_DESKTOP]['height']; ?>;
        
            $('.uploadimage--info').html((langLbl.preferredDimensions).replace(/%s/g, swidth + ' * ' + sheight));
            $('input[name=banner_min_width]').val(swidth);
            $('input[name=banner_min_height]').val(sheight);
            aspectRatio = swidth / sheight;
        } else if($(this).val() == screenIpad) {
            var ipwidth = <?php echo $bannerSizeArr[applicationConstants::SCREEN_IPAD]['width']; ?>;
            var ipheight = <?php echo $bannerSizeArr[applicationConstants::SCREEN_IPAD]['height']; ?>;
            $('.uploadimage--info').html((langLbl.preferredDimensions).replace(/%s/g, ipwidth + ' * ' + ipheight));
            $('input[name=banner_min_width]').val(ipwidth);
            $('input[name=banner_min_height]').val(ipheight);
            aspectRatio = ipwidth / ipheight;
        } else {
            var mbwidth = <?php echo $bannerSizeArr[applicationConstants::SCREEN_MOBILE]['width']; ?>;
            var mbheight = <?php echo $bannerSizeArr[applicationConstants::SCREEN_MOBILE]['height']; ?>;
            $('.uploadimage--info').html((langLbl.preferredDimensions).replace(/%s/g, mbwidth + ' * ' + mbheight));
            $('input[name=banner_min_width]').val(mbwidth);
            $('input[name=banner_min_height]').val(mbheight);
            aspectRatio = mbwidth / mbheight;
        }
    <?php } ?>  
});
</script>
