<?php if (count($arrListing) > 0) {
    foreach ($arrListing as $row) { ?>
        <li>
            <div class="msg_db">
                <?php if ($row['message_from_shop_name'] != '' && $row['message_from_shop_id'] > 0) {
                    $userImgUpdatedOn = Shop::getAttributesById($row['message_from_shop_id'], 'shop_updated_on');
                    $uploadedTime = AttachedFile::setTimeParam($userImgUpdatedOn);
                ?>
                    <img src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'shopLogo', array($row['message_from_shop_id'], $siteLangId, 'thumb')) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo $row['message_from_name']; ?>">
                <?php } else {
                    $userImgUpdatedOn = User::getAttributesById($row['message_from_user_id'], 'user_updated_on');
                    $uploadedTime = AttachedFile::setTimeParam($userImgUpdatedOn);
                ?>
                    <img src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'user', array($row['message_from_user_id'], 'thumb', true)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo $row['message_from_name']; ?>">
                <?php } ?>

            </div>
            <div class="msg__desc">
                <span class="msg__title">
                    <?php if ($row['message_from_shop_name'] != '') {
                        echo $row['message_from_shop_name'] . ' (' . $row['message_from_name'] . ')';
                    } else {
                        echo  $row['message_from_name'];
                    }
                    ?>
                </span>
                <span class="msg__date"><?php echo FatDate::format($row['message_date'], true); ?></span>
                <div class="msg__detail"><?php echo nl2br($row['message_text']); ?> </div>
                <?php
                $attachedFile =  AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_MESSAGE_ATTACHMENTS, $row['message_id']);
                if (!empty($attachedFile)) {
                    foreach ($attachedFile as $val) {
                ?>
                        <span class="msg__detail"><a class="btn btn-outline-brand btn-sm" href="<?php echo UrlHelper::generateUrl('Account', 'downloadAttachedFileMsg', array($val['afile_id'])); ?>"><?php echo $val['afile_name']; ?></a></span>
                <?php }
                } ?>


            </div>
        </li>
<?php }
}
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmMessageSrchPaging'));
