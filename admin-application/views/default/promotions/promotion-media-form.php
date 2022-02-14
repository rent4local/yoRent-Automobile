<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$mediaFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$mediaFrm->setFormTagAttribute('onsubmit', 'setupPromotion(this); return(false);');
$mediaFrm->developerTags['colClassPrefix'] = 'col-md-';
$mediaFrm->developerTags['fld_default_col'] = 12;	

$fld1 = $mediaFrm->getField('banner_image');
$fld1->addFieldTagAttribute('onChange', 'popupImage(this)');
$fld1->htmlAfterField = '<div class="img-crop--js"></div>';


$langFld = $mediaFrm->getField('lang_id');
$langFld->addFieldTagAttribute('class','language-js');
$screenFld = $mediaFrm->getField('banner_screen');
$screenFld->addFieldTagAttribute('class','display-js');

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
    $htmlAfterField = '<span class="form-text text-muted uploadimage--info" > '. Labels::getLabel('LBL_Preferred_Dimensions', $adminLangId) .  ' '. $width . ' * ' . $height.'</span>';
}
$fld1->htmlAfterField = $htmlAfterField;
?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Promotion_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">      
		<div class="tabs_nav_container responsive flat">
			<ul class="tabs_nav">
			
				<li><a href="javascript:void(0);" onClick="addPromotionForm(<?php echo $promotionId;?>)"><?php echo Labels::getLabel('LBL_General',$adminLangId);?></a></li>	
                <li class="<?php echo ($promotionId == 0) ? 'fat-inactive' : ''; ?>">
                    <a href="javascript:void(0);" <?php echo ($promotionId) ? "onclick='promotionLangForm(" . $promotionId . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                        <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                    </a>
                </li>
				<?php $inactive = ($promotionId==0)?'fat-inactive':'';?>
				
				<?php if($promotionType == Promotion::TYPE_BANNER || $promotionType == Promotion::TYPE_SLIDES){?>
				<li ><a  class="<?php echo $inactive; ?> active" href="javascript:void(0)" <?php if($promotionId>0){ ?> onClick="promotionMediaForm(<?php echo $promotionId;?>)" <?php }?>><?php echo Labels::getLabel('LBL_Media',$adminLangId); ?></a></li>		
				<?php }?>			
			</ul>
			<div class="tabs_panel_wrap">
				<div class="tabs_panel">
					<?php 
                    /* [ MEDIA INSTRUCTIONS START HERE */
                    $tpl = new FatTemplate('', '');
                    $tpl->set('adminLangId', $adminLangId);
                    echo $tpl->render(false, false, '_partial/imageUploadInstructions.php', true, true);
                    /* ] */
                    echo $mediaFrm->getFormHtml(); ?>
                    <div id="image-listing-js"></div>	
				</div>
			</div>	
				
		</div>
	</div>						
</section>

<script>
    $('input[name=banner_min_width]').val(<?php echo $width;?>);
    $('input[name=banner_min_height]').val(<?php echo $height;?>);
    var aspectRatio = <?php echo $width / $height;?>;

    $(document).on('change','.display-js',function() {
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
