<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (!empty($images)) {
    $uploadedTime = AttachedFile::setTimeParam($images['afile_updated_at']); 
    $basePath = UrlHelper::generateFullFileUrl('', '', [], CONF_WEBROOT_FRONT_URL);
    $uploadPath = $basePath . CONF_UPLOADS_FOLDER_NAME .'/'. AttachedFile::FILETYPE_BANNER_PATH;
    if (!file_exists(CONF_UPLOADS_PATH . '/'.AttachedFile::FILETYPE_BANNER_PATH . $images['afile_physical_path'])) { 
        $disUrl = $basePath.'/images/defaults/3/slider-default.png';
    } else {
        $disUrl = $uploadPath. $images['afile_physical_path']; 
    }

    ?>
    <ul class="grids--onethird mt-0" id="<?php if ($canEdit) { ?>sortable<?php } ?>">
        <li id="<?php echo $images['afile_id']; ?>">
            <div class="logoWrap">
                <div class="logothumb">
                    <img src="<?php echo $disUrl; ?>" title="<?php echo $images['afile_name']; ?>" alt="<?php echo $images['afile_name']; ?>">
                    <?php if ($canEdit) { ?>
                        <a class="deleteLink white" href="javascript:void(0);" title="Delete <?php echo $images['afile_name']; ?>" onclick="removeBanner(<?php echo $images['afile_id']; ?>, <?php echo $images['afile_record_id']; ?>, <?php echo $images['afile_lang_id']; ?>, <?php echo $images['afile_screen']; ?>);" class="delete">
                            <i class="ion-close-round"></i>
                        </a>
                    <?php } ?>
                </div>
                <?php if (isset($imgTypesArr) && !empty($imgTypesArr[$images['afile_record_subid']])) {
                    echo '<small class=""><strong>' . Labels::getLabel('LBL_Type', $adminLangId) . ': </strong> ' . $imgTypesArr[$images['afile_record_subid']] . '</small><br/>';
                }

                $lang_name = Labels::getLabel('LBL_All', $adminLangId);
                if ($images['afile_lang_id'] > 0) {
                    $lang_name = $languages[$images['afile_lang_id']]; ?>
                <?php } ?>
                <small class="text--small"><?php echo Labels::getLabel('LBL_Language', $adminLangId); ?>: <?php echo $lang_name; ?></small>
            </div>
        </li>
    </ul>
<?php } ?>