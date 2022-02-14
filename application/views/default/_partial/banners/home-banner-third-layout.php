<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
$bCount = 1;
$uploadPath = CONF_UPLOADS_FOLDER_NAME .'/'. AttachedFile::FILETYPE_BANNER_PATH;
if (!empty($bannerLayout1['banners']) && $bannerLayout1['blocation_active']) {
?>
<section class="section collection-banner-2" id="banner_layout_3_<?php echo $bannerLayout1['blocation_collection_id']; ?>">
    <h2 style="display:none;"><?php echo $bannerLayout1['blocation_identifier'];?></h2>
    <div class="container">
        <div class="ad__media">
            <?php
            foreach ($bannerLayout1['banners'] as $val) {
                $bannerClass = 'ad__media_img ad__media--left';
                if ($val['banner_position'] == Collections::BANNER_POSITION_RIGHT) {
                    $bannerClass = 'ad__media_img ad__media--right';
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
                                $mobile_url = $uploadPath. $slideScreen['afile_physical_path'];
                                break;
                            case applicationConstants::SCREEN_IPAD:
                                $tablet_url = $uploadPath. $slideScreen['afile_physical_path'];
                                break;
                            case applicationConstants::SCREEN_DESKTOP:
                                $desktop_url = $uploadPath. $slideScreen['afile_physical_path'];
                                break;
                        }
                    }
                }
                if ($val['banner_record_id'] > 0 && $val['banner_type'] == Banner::TYPE_PPC) {
                    Promotion::updateImpressionData($val['banner_record_id']);
                }
                ?>
                <div class="<?php echo $bannerClass; ?>">
                    <a target="<?php echo $val['banner_target']; ?>" href="<?php echo UrlHelper::generateUrl('Banner', 'url', array($val['banner_id'])); ?>" title="<?php echo $val['banner_title']; ?>">
                        <img alt="<?php echo $val['banner_title']; ?>" src="<?php echo $desktop_url; ?>" />
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</section>
<?php } ?>