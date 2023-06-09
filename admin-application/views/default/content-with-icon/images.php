<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (!empty($images)) { ?>
    <ul class="grids--onefifth">
        <?php
        $count = 1;
        foreach ($images as $afile_id => $row) {
            ?>
            <li id="<?php echo $row['afile_id']; ?>">
                <div class="logoWrap">
                    <div class="logothumb"> 
                        <img src="<?php echo UrlHelper::generateUrl('image', 'contentBlockIcon', array($row['afile_record_id'], "THUMB", $row['afile_id']), CONF_WEBROOT_FRONTEND); ?>" title="<?php echo $row['afile_name']; ?>" alt="<?php echo $row['afile_name']; ?>"> 
                        <?php if ($canEdit) { ?> 
                            <a class="deleteLink white" href="javascript:void(0);" title="<?php echo Labels::getLabel('LBL_Remove', $adminLangId); ?> <?php echo $row['afile_name']; ?>" onclick="deleteRecordImage(<?php echo $row['afile_record_id']; ?>, <?php echo $row['afile_id']; ?>, <?php echo $row['afile_lang_id']; ?>);" class="delete">
                                <i class="ion-close-round"></i>
                            </a>
                        <?php } ?>
                    </div>
                    <?php
                    if (isset($imgTypesArr) && !empty($imgTypesArr[$row['afile_record_subid']])) {
                        echo '<small class=""><strong>' . Labels::getLabel('LBL_Type', $adminLangId) . ': </strong> ' . $imgTypesArr[$row['afile_record_subid']] . '</small><br/>';
                    }

                    $lang_name = Labels::getLabel('LBL_All', $adminLangId);
                    if ($row['afile_lang_id'] > 0) {
                        $lang_name = $languages[$row['afile_lang_id']];
                        ?>
                    <?php } ?>
                    <small class=""><strong> <?php echo Labels::getLabel('LBL_Language', $adminLangId); ?>:</strong> <?php echo $lang_name; ?></small> </div>
            </li>
            <?php
            $count++;
        }
        ?>
    </ul>
<?php } ?>