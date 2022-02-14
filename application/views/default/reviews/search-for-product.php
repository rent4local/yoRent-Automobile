<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if ($reviewsList) { ?>

    <?php foreach ($reviewsList as $review) { ?>
        <div class="review-block">
            <?php if (!empty($recordRatings)) { ?>
                <ul class="ratedby-list">
                    <?php foreach ($recordRatings as $val) {
                        if ($review['spreview_id'] != $val['sprating_spreview_id']) {
                            continue;
                        }
                    ?>
                        <li>
                            <div class="rating flex-column">
                                <span class="rating__text"><?php echo $ratingAspectArr[$val['sprating_rating_type']]; ?></span>
                                <div class="rating-view" data-rating="<?php echo $val['sprating_rating']; ?>">
                                    <?php for ($i = 5; $i >= 1; $i--) { ?>
                                        <svg class="icon" width="24" height="24">
                                            <use xlink:href="/images/retina/sprite.svg#star"></use>
                                        </svg>
                                    <?php } ?>
                                </div>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            <?php } ?>
            <h5> <?php echo $review['spreview_title']; ?> </h5>
            <p class="review-comment">
                <span class='lessText'>
                    <?php echo CommonHelper::truncateCharacters($review['spreview_description'], 300, '', '', true); ?>
                    <br>
                </span>
                <?php if (strlen($review['spreview_description']) > 300) { ?>
                    <span class='moreText hidden'>
                        <?php echo nl2br($review['spreview_description']); ?>
                    </span>
                    <a class="readMore link-plus" href="javascript:void(0);">
                        <?php echo Labels::getLabel('Lbl_READ_MORE', $siteLangId); ?> </a>
                <?php } ?>
            </p>

            <?php /* <ul class="thumb-list has-more">
              <li>
              <a href="#"><img src="/images/review-thumbs/review-thumb.png"></a>
              </li>
              </ul> */ ?>
            <div class="review-detail">
                <div class="name-and-date">
                    <p><?php echo CommonHelper::displayName($review['user_name']); ?></p>
                    <p class="date"><?php echo FatDate::format($review['spreview_posted_on']); ?></p>
                </div>
                <div class="like-dislike">
                    <p class="like">
                        <a href="javascript:void();" onclick="markReviewHelpful(<?php echo FatUtility::int($review['spreview_id']); ?>, 1);return false;" class="yes">
                            <i class="icn icn-thumb-like">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#thumb-like" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#thumb-like"></use>
                                </svg>
                            </i>
                            <span><?php echo $review['helpful']; ?></span>
                        </a>
                    </p>
                    <p class="dislike">
                        <a href="javascript:void();" onclick="markReviewHelpful(<?php echo $review['spreview_id']; ?>, 0);return false;" class="no">
                            <i class="icn icn-thumb-dislike">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#thumb-dislike" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#thumb-dislike"></use>
                                </svg>
                            </i>
                            <span><?php echo $review['notHelpful']; ?></span>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    <?php } ?>
    <?php /* <div class="align--center  mt-4">
        <a href="<?php echo UrlHelper::generateUrl('Reviews', 'Product', array($selprod_id)); ?>"
class="link-plus"><?php echo Labels::getLabel('Lbl_Showing_All', $siteLangId) . ' ' . count($reviewsList) . ' ' . Labels::getLabel('Lbl_Reviews', $siteLangId); ?>
</a>
</div> */ ?>
    <?php echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSearchReviewsPaging')); ?>
<?php
} else {
    // $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId), false);
}
?>