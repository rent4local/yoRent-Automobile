<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
$bCount = 1;
$basePath = UrlHelper::generateFullFileUrl();
$uploadPath = $basePath . CONF_UPLOADS_FOLDER_NAME .'/'. AttachedFile::FILETYPE_BANNER_PATH;
if (!empty($bannerLayout1['banners']) && $bannerLayout1['blocation_active']) {
?>
<section class="section section-double-ad" id="banner_layout_3_<?php echo $bannerLayout1['blocation_collection_id']; ?>">
	<div class="container">
        <div class="row">
        <?php foreach ($bannerLayout1['banners'] as $val) {
            $bannerClass = 'ad-wide-media';
			$columClass = "col-lg-8";
            if ($val['banner_position'] == Collections::BANNER_POSITION_RIGHT) {
                $bannerClass = 'ad-long-media';
				$columClass = "col-lg-4";
            }

            $desktop_url = $tablet_url = $mobile_url = $defaultImgUrl = '';

            if (!AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BANNER, $val['banner_id'], 0, $siteLangId)) {
                continue;
            } else {
                $slideArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BANNER, $val['banner_id'], 0, $siteLangId);
                foreach ($slideArr as $slideScreen) {
                    $uploadedTime = AttachedFile::setTimeParam($slideScreen['afile_updated_at']);
                    switch ($slideScreen['afile_screen']) {
                        case applicationConstants::SCREEN_MOBILE:
                            if (!file_exists(CONF_UPLOADS_PATH.'/'.AttachedFile::FILETYPE_BANNER_PATH.$slideScreen['afile_physical_path'])) {
                                $mobile_url = $basePath .'/images/defaults/3/slider-default.png';
                            } else {
                                $mobile_url = $uploadPath. $slideScreen['afile_physical_path']; 
                            }
                            break;
                        case applicationConstants::SCREEN_IPAD:
                            if (!file_exists(CONF_UPLOADS_PATH.'/'.AttachedFile::FILETYPE_BANNER_PATH.$slideScreen['afile_physical_path'])) {
                                $tablet_url = $basePath.'/images/defaults/3/slider-default.png';
                            } else {
                                $tablet_url = $uploadPath. $slideScreen['afile_physical_path']; 
                            }
                            break;
                        case applicationConstants::SCREEN_DESKTOP:
                            if (!file_exists(CONF_UPLOADS_PATH.'/'.AttachedFile::FILETYPE_BANNER_PATH.$slideScreen['afile_physical_path'])) {
                                $desktop_url = $basePath.'/images/defaults/3/slider-default.png';
                            } else {
                                $desktop_url = $uploadPath. $slideScreen['afile_physical_path']; 
                            }
                            break;
                    }  
                }
            }

            if ($val['banner_record_id'] > 0 && $val['banner_type'] == Banner::TYPE_PPC) {
                Promotion::updateImpressionData($val['banner_record_id']);
            } ?>
			<div class="<?php echo $columClass; ?>">
                <div class="<?php echo $bannerClass; ?>">
                    <a target="<?php echo $val['banner_target']; ?>" href="<?php echo UrlHelper::generateUrl('Banner', 'url', array($val['banner_id'])); ?>" title="<?php echo $val['banner_title']; ?>">
						<img src="<?php echo $desktop_url; ?>" />
					</a>
                </div>
            </div>
        <?php } ?>
		</div>
    </div>
</section>
<?php } ?>