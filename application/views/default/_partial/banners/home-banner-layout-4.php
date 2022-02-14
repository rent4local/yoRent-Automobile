<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
$bCount = 1;
$uploadPath = CONF_UPLOADS_FOLDER_NAME .'/'. AttachedFile::FILETYPE_BANNER_PATH;
if (!empty($bannerLayout1['banners']) && $bannerLayout1['blocation_active']) { ?>
    <section class="section collection-banner" id="banner_layout_3_<?php echo $bannerLayout1['blocation_collection_id']; ?>">
        <?php foreach ($bannerLayout1['banners'] as $val) {
            $bannerClass = 'ad_media-left';
            if ($val['banner_position'] == Collections::BANNER_POSITION_RIGHT) {
                $bannerClass = 'ad_media-right';
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

            <div class="container">
                <div class="ads-wrapper">
                    <div class="ads-wrapper__banner">
                        <div class="banner-img">
                            <img src="<?php echo $desktop_url; ?>" alt="<?php echo $val['banner_title'];?>">
                        </div>
                    </div>
                    <div class="ads-wrapper__content">
                        <div class="content-wrapp">
                            <h2><?php echo $collectionData['collection_name']; ?></h2>
                            <p><?php echo $collectionData['collection_description']; ?></p>
                            <div class="btn-layout mt-4">
                                <a target="<?php echo $val['banner_target']; ?>" href="<?php echo UrlHelper::generateUrl('Banner', 'url', array($val['banner_id'])); ?>" title="<?php echo $val['banner_title']; ?>" class="btn btn-outline-white btn-theme ">
                                    <?php echo $val['banner_title']; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </section>
<?php } ?>