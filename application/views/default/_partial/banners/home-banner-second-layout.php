<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
$bCount = 1;
$uploadPath = CONF_UPLOADS_FOLDER_NAME .'/'. AttachedFile::FILETYPE_BANNER_PATH;
if (!empty($bannerLayout1['banners']) && $bannerLayout1['blocation_active']) { ?>
<section class="section" >
    <div class="container">
        <div class="banner-wrappers">
        <?php foreach ($bannerLayout1['banners'] as $val) {
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
            } ?>
            
            <div class="banner-ppc">
                <a  target="<?php echo $val['banner_target']; ?>" href="<?php echo UrlHelper::generateUrl('Banner', 'url', array($val['banner_id'])); ?>" title="<?php echo $val['banner_title']; ?>">
                    <picture>
                        <source data-aspect-ratio="4:3" srcset="<?php echo $mobile_url; ?>" media="(max-width: 767px)">
                        <source data-aspect-ratio="4:3" srcset="<?php echo $tablet_url; ?>" media="(max-width: 1024px)">
                        <source data-aspect-ratio="10:3" srcset="<?php echo $desktop_url; ?>">
                        <img data-aspect-ratio="10:3" src="<?php echo $desktop_url; ?>" alt="">
                    </picture>
                </a>
            </div>  
        <?php 
        $bCount++;
        }?>
        </div>
    </div>
</section>
<?php } ?>
