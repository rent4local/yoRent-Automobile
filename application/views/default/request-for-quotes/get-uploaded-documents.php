<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
if (!empty($attachments)) {
    $isSeller = false;
    if ($rfqDetail['selprod_user_id'] == UserAuthentication::getLoggedUserId()) {
        $isSeller = true;
    }
    $imageType = array('png', 'jpeg', 'jpg', 'gif', 'bmp', 'ico', 'tiff', 'tif');
    foreach ($attachments as $sn => $row) {
        $rfqId = $row['afile_record_id'];
        $attachmentId = $row['afile_id'];
        $ext = pathinfo($row['afile_name'], PATHINFO_EXTENSION);

        //$icon = '<a target="_blank" href="' . CommonHelper::generateUrl('CounterOffer', 'downloadDigitalFile', array($rfqId, $attachmentId, AttachedFile::FILETYPE_QUOTED_DOCUMENT)) . '"><span id="document-js-' . $attachmentId . '">' . $row["afile_name"] . '<i class="fa fa-download"></i>' . '</span></a>';
        $documentUrl = CommonHelper::generateUrl('CounterOffer', 'downloadDigitalFile', [$rfqId, $attachmentId, AttachedFile::FILETYPE_QUOTED_DOCUMENT, true, 70, 70]);
        echo "<div class='uploaded--documents-item' id='document-js-". $attachmentId ."'>";
        if (in_array($ext, $imageType)) {
            ?>
            <a href="<?php echo CommonHelper::generateUrl('CounterOffer', 'downloadDigitalFile', [$rfqId, $attachmentId, AttachedFile::FILETYPE_QUOTED_DOCUMENT]); ?>" title="<?php echo Labels::getLabel('LBL_Download_file', $siteLangId); ?>">
                <img src="<?php echo $documentUrl; ?>" alt="<?php echo $row['afile_name']; ?>" title="<?php echo $row['afile_name']; ?>" />
            </a>
        <?php } else { ?>
            <a href="<?php echo CommonHelper::generateUrl('CounterOffer', 'downloadDigitalFile', [$rfqId, $attachmentId, AttachedFile::FILETYPE_QUOTED_DOCUMENT]); ?>" title="<?php echo Labels::getLabel('LBL_Download_file', $siteLangId); ?>">
                <i class="icn rfq-doc-file-icon">
                    <svg class="svg">
                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-my-subscriptions" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-my-subscriptions"></use>
                    </svg>
                </i>
            </a>
            <?php
        }
        if ($isSeller && ((!isset($rfqDetail['rfq_status'])) || isset($rfqDetail['rfq_status']) && $rfqDetail['rfq_status'] == RequestForQuote::REQUEST_INPROGRESS)) {
            echo '<p class="doc-title"><span>' . $icon = $row["afile_name"] . '</span><i class="fa fa-trash" onclick="removeRfqDocument(' . $rfqId . ', ' . $attachmentId . ')"></i></p>';
        } else {
            echo '<p class="doc-title"><a href="' . CommonHelper::generateUrl('CounterOffer', 'downloadDigitalFile', [$rfqId, $attachmentId, AttachedFile::FILETYPE_QUOTED_DOCUMENT]) . '"><span>' . $icon = $row["afile_name"] . '</span></a></p>';
        }
        echo "</div>";
    }
} else {
    if (!$hideNoRecordMsg) {
        echo Labels::getLabel('LBL_No_document_uploaded', $siteLangId);
    }
}
