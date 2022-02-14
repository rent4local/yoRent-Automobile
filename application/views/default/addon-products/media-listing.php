<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (!empty($addonProdImages)) { ?>
<ul id="sortable" class="inline-images">
    <?php
        foreach ($addonProdImages as $afile_id => $row) {
            ?>
    <li class="addon-added" id="<?php echo $row['afile_id']; ?>">
        <div class="addon-card">
            <img src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'addonProduct', array($row['afile_record_id'], "THUMB", $row['afile_id']), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg'); ?>"
                title="<?php echo $row['afile_name']; ?>" alt="<?php echo $row['afile_name']; ?>">
            <a class="close-layer" href="javascript:void(0);"
                title="<?php echo Labels::getLabel('LBL_Delete', $siteLangId); ?> <?php echo $row['afile_name']; ?>"
                onclick="deleteImage(<?php echo $row['afile_record_id']; ?>, <?php echo $row['afile_id']; ?>);"
                class="delete">

            </a>
            <div class="detail">
                <?php
                    $lang_name = Labels::getLabel('LBL_All', $siteLangId);
                    if ($row['afile_lang_id'] > 0) {
                        $lang_name = $languages[$row['afile_lang_id']];
                    }
                    ?>
                <p class=""><strong> <?php echo Labels::getLabel('LBL_Language', $siteLangId); ?>:</strong>
                    <?php echo $lang_name; ?></p>
            </div>
        </div>
    </li>
    <?php
        }
        ?>
</ul>
<?php } ?>