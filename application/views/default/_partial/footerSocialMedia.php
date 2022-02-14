<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if ($rows) { ?>
    <div class="social-icons-footer">
        <ul>
            <?php
            foreach ($rows as $row) {
                $img = AttachedFile::getAttachment(AttachedFile::FILETYPE_SOCIAL_PLATFORM_IMAGE, $row['splatform_id']);
                $title = ($row['splatform_title'] != '') ? $row['splatform_title'] : $row['splatform_identifier'];
                ?>
                <li>
                    <a <?php if ($row['splatform_url'] != '') { ?>target="_blank" <?php } ?> href="<?php echo ($row['splatform_url'] != '') ? $row['splatform_url'] : 'javascript:void(0)'; ?>"  title="<?php echo $title; ?>">
                        <?php
                        if (isset($img['afile_id']) && 0 < $img['afile_id']) {
                            echo '<img alt="' . $title . '" src = "' . UrlHelper::generateUrl('Image', 'SocialPlatform', array($row['splatform_id'])) . '"/>';
                        } elseif ($row['splatform_icon_class'] != '') {
                            ?>
                            <i class="icn fab fa-<?php echo $row['splatform_icon_class']; ?>"></i>
                        <?php } ?>
                    </a>
                </li>
            <?php }
            ?>
        </ul>
    </div>
<?php } ?>