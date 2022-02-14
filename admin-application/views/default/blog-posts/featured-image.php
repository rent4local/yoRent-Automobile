<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (!empty($images)) { ?>
    <ul class="grids--onethird" id="<?php if ($canEdit) { ?>sortable<?php } ?>">
        <li id="<?php echo $images['afile_id']; ?>">
            <div class="logoWrap">
                <div class="logothumb"> 
                    <img src="<?php echo UrlHelper::generateUrl('Image', 'blogPostAdmin', array($images['afile_record_id'], $images['afile_lang_id'], "THUMB", 0, $images['afile_id'], true), CONF_WEBROOT_FRONT_URL); ?>" title="<?php echo $images['afile_name']; ?>" alt="<?php echo $images['afile_name']; ?>"> 
                </div>
                <?php
                if (isset($imgTypesArr) && !empty($imgTypesArr[$images['afile_record_subid']])) {
                    echo '<small class=""><strong>' . Labels::getLabel('LBL_Type', $adminLangId) . ': </strong> ' . $imgTypesArr[$images['afile_record_subid']] . '</small><br/>';
                }

                $lang_name = Labels::getLabel('LBL_All', $adminLangId);
                if ($images['afile_lang_id'] > 0) {
                    $lang_name = $languages[$images['afile_lang_id']];
                    ?>
                <?php } ?>
                <small class="">
                    <strong> <?php echo Labels::getLabel('LBL_Language', $adminLangId); ?>:</strong> 
                    <?php echo $lang_name; ?>
                </small> 
            </div>
        </li>
    </ul>
<?php } ?>
