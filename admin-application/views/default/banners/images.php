<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php /* CommonHelper::printArray($images); die; */ if (!empty($images)) { ?>
<ul class="grids--onethird" id="<?php if ($canEdit) { ?>sortable<?php } ?>">
<?php $count = 1;
    $basePath = UrlHelper::generateFullFileUrl('', '', [], CONF_WEBROOT_FRONT_URL);
    $uploadPath = $basePath . CONF_UPLOADS_FOLDER_NAME .'/'. AttachedFile::FILETYPE_BANNER_PATH;
    foreach ($images as $afile_id => $row) {
        $uploadedTime = AttachedFile::setTimeParam($row['afile_updated_at']); 
        if (!file_exists(CONF_UPLOADS_PATH . '/'.AttachedFile::FILETYPE_BANNER_PATH . $row['afile_physical_path'])) { 
            $imgUrl = $basePath.'/images/defaults/3/slider-default.png';
        } else {
            $imgUrl = $uploadPath. $row['afile_physical_path']; 
        }
        ?>
        <li id="<?php echo $row['afile_id']; ?>">
            <div class="logoWrap">
                <div class="logothumb"> 
                    <img src="<?php echo $imgUrl; ?>" title="<?php echo $row['afile_name']; ?>" alt="<?php echo $row['afile_name']; ?>" style="max-height:80px;"> 
                    <?php if ($canEdit) { ?> 
                        <a class="deleteLink white" href="javascript:void(0);" title="Delete <?php echo $row['afile_name'];?>" onclick="removeBanner(<?php echo $blocation_id; ?>, <?php echo $row['afile_record_id']; ?>, <?php echo $row['afile_lang_id']; ?>, <?php echo $row['afile_screen']; ?>);" class="delete"><i class="ion-close-round"></i></a>
                    <?php } ?>
                </div>
                <?php if (isset($screenTypeArr) && !empty($screenTypeArr[$row['afile_screen']])) {
                    echo '<small class=""><strong>'.Labels::getLabel('LBL_Screen', $adminLangId).': </strong> '.$screenTypeArr[$row['afile_screen']].'</small><br/>';
                }

                $lang_name = Labels::getLabel('LBL_All', $adminLangId);
                if ($row['afile_lang_id'] > 0) {
                    $lang_name = $languages[$row['afile_lang_id']]; ?>
                <?php
                } ?>
                <small class=""><strong> <?php echo Labels::getLabel('LBL_Language', $adminLangId); ?>:</strong> <?php echo $lang_name; ?></small>
            </div>
        </li>
        <?php $count++;
    } ?>
</ul>
<?php }    ?>
