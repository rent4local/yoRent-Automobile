<?php  if( !empty($bannerImgArr) ){ ?>
	<ul class="grids--onefifth">
	<?php
    $basePath = UrlHelper::generateFullFileUrl('', '', [], CONF_WEBROOT_FRONT_URL);
    $uploadPath = $basePath . CONF_UPLOADS_FOLDER_NAME .'/'. AttachedFile::FILETYPE_BANNER_PATH; 
		foreach( $bannerImgArr as $afile_id => $bannerImg ){
			$imgUrl =  '';
			switch($promotionType){
				case Promotion::TYPE_BANNER:
                    if (!file_exists(CONF_UPLOADS_PATH . '/'.AttachedFile::FILETYPE_BANNER_PATH . $bannerImg['afile_physical_path'])) { 
                        $imgUrl = $basePath.'/images/defaults/3/slider-default.png';
                    } else {
                        $imgUrl = $uploadPath. $bannerImg['afile_physical_path']; 
                    }
                break;
				case Promotion::TYPE_SLIDES:
					$imgUrl = UrlHelper::generateFullUrl('Image','Slide',array($bannerImg['afile_record_id'],$bannerImg['afile_screen'],$bannerImg['afile_lang_id'],'THUMB'),CONF_WEBROOT_FRONT_URL);
				break;
			}
		?>
		<li id="<?php echo $bannerImg['afile_id']; ?>">

			<div class="logoWrap">
				<div class="logothumb">
					<img src="<?php echo $imgUrl; ?>" title="<?php echo $bannerImg['afile_name'];?>" alt="<?php echo $bannerImg['afile_name'];?>">
					<?php if($canEdit){ ?>
					<a class="deleteLink white" href="javascript:void(0);" title="Delete <?php echo $bannerImg['afile_name'];?>" onclick="removePromotionBanner(<?php echo  $promotionId;?>, <?php echo $bannerImg['afile_record_id']; ?>, <?php echo $bannerImg['afile_lang_id']; ?>,<?php echo $bannerImg['afile_screen'];?>);" class="delete"><i class="ion-close-round"></i></a>
					<?php } ?>
				</div>
				<?php if(!empty($imgTypesArr[$bannerImg['afile_record_subid']])){
					echo '<small class=""><strong>'.Labels::getLabel('LBL_Type',$adminLangId).':</strong> '.$imgTypesArr[$bannerImg['afile_record_subid']].'</small><br/>';
				}

				$lang_name = Labels::getLabel('LBL_All',$adminLangId);
				if( $bannerImg['afile_lang_id'] > 0 ){
					$lang_name = $language[$bannerImg['afile_lang_id']];
				?>
				<?php } ?>
				<small class=""><strong> <?php echo Labels::getLabel('LBL_Language',$adminLangId); ?>:</strong> <?php echo $lang_name; ?></small><br/><small class=""><strong> <?php echo Labels::getLabel('LBL_Screen',$adminLangId); ?>:</strong> <?php echo $screenTypeArr[$bannerImg['afile_screen']]; ?></small>
			</div>
		</li>
		<?php }
		?>
	</ul>
<?php }	?>
