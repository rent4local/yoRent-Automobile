<?php
defined('SYSTEM_INIT') or die('Invalid usage');
/* reviews processing */
$totReviews = FatUtility::int($reviews['totReviews']);
$avgRating = FatUtility::convertToType($reviews['prod_rating'], FatUtility::VAR_FLOAT);
$rated_1 = FatUtility::int($reviews['rated_1']);
$rated_2 = FatUtility::int($reviews['rated_2']);
$rated_3 = FatUtility::int($reviews['rated_3']);
$rated_4 = FatUtility::int($reviews['rated_4']);
$rated_5 = FatUtility::int($reviews['rated_5']);

$pixelToFillRight = $avgRating / 5 * 160;
$pixelToFillRight = FatUtility::convertToType($pixelToFillRight, FatUtility::VAR_FLOAT);

$rate_5_width = $rate_4_width = $rate_3_width = $rate_2_width = $rate_1_width = 0;

if ($totReviews) {
    $rate_5_width = round(FatUtility::convertToType($rated_5 / $totReviews * 100, FatUtility::VAR_FLOAT), 2);
    $rate_4_width = round(FatUtility::convertToType($rated_4 / $totReviews * 100, FatUtility::VAR_FLOAT), 2);
    $rate_3_width = round(FatUtility::convertToType($rated_3 / $totReviews * 100, FatUtility::VAR_FLOAT), 2);
    $rate_2_width = round(FatUtility::convertToType($rated_2 / $totReviews * 100, FatUtility::VAR_FLOAT), 2);
    $rate_1_width = round(FatUtility::convertToType($rated_1 / $totReviews * 100, FatUtility::VAR_FLOAT), 2);
}
?>
<div class="listings">
    <div class="listings__head">
        <div class="row">
            <div class="col-md-8">
                <div class="ratings--overall">
                    <div class="row">
                        <div class="col-md-5 column">
                            <div class="shop-reviews-wrapper">
                                <div class="shop-reviews">
                                    <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#rating-star" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#rating-star"></use>
                                    </svg>

                                    <div class="shop-reviews-points"><?php echo round($avgRating, 1); ?></div>
                                </div>
                                <div class="total-reviews"><?php echo Labels::getLabel('Lbl_Based_on', $siteLangId), ' ', $totReviews, ' ', Labels::getLabel('Lbl_ratings', $siteLangId); ?></div>
                                <div class="d-xl-flex no-gutters">
                                    <div class="col center-down-xl">
                                        <a href="javascript:void(0);" class="btn btn-outline-bottom " data-sort='most_recent' onclick="getSortedReviews(this);return false;"><?php echo Labels::getLabel('Lbl_Most_Recent', $siteLangId); ?></a>
                                    </div>
                                    <div class="col center-down-xl">
                                        <a href="javascript:void(0);" class="btn btn-outline-bottom" data-sort='most_helpful' onclick="getSortedReviews(this);return false;"><?php echo Labels::getLabel('Lbl_Most_Helpful', $siteLangId); ?> </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7 column">
                            <div class="listing--progress-wrapper">
                                <ul class="listing--progress">
                                    <li>
                                        <span class="progress_count">5 <?php echo Labels::getLabel('Lbl_Star', $siteLangId); ?></span>
                                        <div class="progress__bar">
                                            <div title="<?php echo $rate_5_width, '% ', Labels::getLabel('LBL_Number_of_reviews_have_5_stars', $siteLangId); ?>" style="width: <?php echo $rate_5_width; ?>%" class="progress__fill">
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <span class="progress_count">4 <?php echo Labels::getLabel('Lbl_Star', $siteLangId); ?></span>
                                        <div class="progress__bar">
                                            <div title="<?php echo $rate_4_width, '% ', Labels::getLabel('LBL_Number_of_reviews_have_4_stars', $siteLangId); ?>" style="width: <?php echo $rate_4_width; ?>%" class="progress__fill">
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <span class="progress_count">3 <?php echo Labels::getLabel('Lbl_Star', $siteLangId); ?></span>
                                        <div class="progress__bar">
                                            <div title="<?php echo $rate_3_width, '% ', Labels::getLabel('LBL_Number_of_reviews_have_3_stars', $siteLangId); ?>" style="width: <?php echo $rate_3_width; ?>%" class="progress__fill">
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <span class="progress_count">2 <?php echo Labels::getLabel('Lbl_Star', $siteLangId); ?></span>
                                        <div class="progress__bar">
                                            <div title="<?php echo $rate_2_width, '% ', Labels::getLabel('LBL_Number_of_reviews_have_2_stars', $siteLangId); ?>" style="width: <?php echo $rate_2_width; ?>%" class="progress__fill">
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <span class="progress_count">1 <?php echo Labels::getLabel('Lbl_Star', $siteLangId); ?></span>
                                        <div class="progress__bar">
                                            <div title="<?php echo $rate_1_width, '% ', Labels::getLabel('LBL_Number_of_reviews_have_1_stars', $siteLangId); ?>" style="width: <?php echo $rate_1_width; ?>%" class="progress__fill">
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if ($canSubmitFeedback) { ?>
                <div class="col-md-4">
                    <div class="box box--white rounded">
                        <h5><?php echo Labels::getLabel('Lbl_Share_your_thoughts', $siteLangId); ?></h5>
                        <p><?php echo Labels::getLabel('Lbl_With_other_customers', $siteLangId); ?></p>
                        <a class="btn btn-outline-bottom" href="<?php echo UrlHelper::generateUrl('Reviews', 'write', array($product_id)); ?>"><?php echo Labels::getLabel('Lbl_Write_a_Review', $siteLangId); ?></a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="listings__body">
        <div class="row">
            <div class="col-md-6 col-sm-6"><span id='reviews-pagination-strip--js' hidden><?php echo Labels::getLabel('Lbl_Displaying_Reviews', $siteLangId); ?> <span id='reviewStartIndex'></span>-<span id='reviewEndIndex'></span>
                    <?php echo Labels::getLabel('Lbl_of', $siteLangId); ?> <span id='reviewsTotal'></span></span></div>
        </div>

        <div class="listing__all review_wrapper"></div>
        <div id="loadMoreReviewsBtnDiv" class="align--center"></div>
        <!--<a class="loadmore text--uppercase" href="javascript:alert('Pending');">Load More</a>-->
    </div>
</div>
<script>
    var $linkMoreText = '<?php echo Labels::getLabel('Lbl_SHOW_MORE', $siteLangId); ?>';
    var $linkLessText = '<?php echo Labels::getLabel('Lbl_SHOW_LESS', $siteLangId); ?>';

    $('#itemRatings div.progress__fill').css({
        'clip': 'rect(0px, <?php echo $pixelToFillRight; ?>px, 160px, 0px)'
    });
</script>
