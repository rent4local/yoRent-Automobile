<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if( !empty($images) ){
    $basePath = UrlHelper::generateFullFileUrl();
    $uploadPath = $basePath . CONF_UPLOADS_FOLDER_NAME .'/'. AttachedFile::FILETYPE_BANNER_PATH;
	$htmlAfterField = '<div class="gap"></div><ul class="image-listing">';
	foreach($images as $bannerImg){
		$imgUrl =  '';
		switch ($promotionType) {
			case Promotion::TYPE_BANNER:
				if (!file_exists(CONF_UPLOADS_PATH.'/'.AttachedFile::FILETYPE_BANNER_PATH . $bannerImg['afile_physical_path'])) {
                    $imgUrl = $basePath.'/images/defaults/3/slider-default.png';
                } else {
                    $imgUrl = $uploadPath. $bannerImg['afile_physical_path']; 
                }
            break;
			case Promotion::TYPE_SLIDES:
				$imgUrl = UrlHelper::generateFullUrl('Image','Slide',array($bannerImg['afile_record_id'],$bannerImg['afile_screen'],$bannerImg['afile_lang_id'],'THUMB'),CONF_WEBROOT_FRONT_URL);
			break;
		}

		$htmlAfterField .= '<li><p>'.$bannerTypeArr[$bannerImg['afile_lang_id']].'</p><p>'.$screenTypeArr[$bannerImg['afile_screen']].'</p><img style="max-height:80px;" src="'.$imgUrl.'"> <a href="javascript:void(0);" onClick="removePromotionBanner('.$promotionId.','.$bannerImg['afile_record_id'].','.$bannerImg['afile_lang_id'].','.$bannerImg['afile_screen'].')" class="closeimg">x</a>';
	}
	$htmlAfterField.='</li></ul>';
	echo $htmlAfterField;
}
?>
