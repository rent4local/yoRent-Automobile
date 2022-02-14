<?php
defined('SYSTEM_INIT') or die('Invalid usage');
$totReviews = $avgRating = $pixelToFillRight = 0;
if (!empty($reviews)) {
    $totReviews = (!empty($reviews['totReviews'])) ? FatUtility::int($reviews['totReviews']) : 0;
    $avgRating = (!empty($reviews['prod_rating'])) ? FatUtility::convertToType($reviews['prod_rating'], FatUtility::VAR_FLOAT) : 0;

    $pixelToFillRight = $avgRating / 5 * 160;
    $pixelToFillRight = FatUtility::convertToType($pixelToFillRight, FatUtility::VAR_FLOAT);

    $rate_5_width = $rate_4_width = $rate_3_width = $rate_2_width = $rate_1_width = 0;

    if ($totReviews) {
        $rated_1 = FatUtility::int($reviews['rated_1']);
        $rated_2 = FatUtility::int($reviews['rated_2']);
        $rated_3 = FatUtility::int($reviews['rated_3']);
        $rated_4 = FatUtility::int($reviews['rated_4']);
        $rated_5 = FatUtility::int($reviews['rated_5']);

        $rate_5_width = round(FatUtility::convertToType($rated_5 / $totReviews * 100, FatUtility::VAR_FLOAT), 2);
        $rate_4_width = round(FatUtility::convertToType($rated_4 / $totReviews * 100, FatUtility::VAR_FLOAT), 2);
        $rate_3_width = round(FatUtility::convertToType($rated_3 / $totReviews * 100, FatUtility::VAR_FLOAT), 2);
        $rate_2_width = round(FatUtility::convertToType($rated_2 / $totReviews * 100, FatUtility::VAR_FLOAT), 2);
        $rate_1_width = round(FatUtility::convertToType($rated_1 / $totReviews * 100, FatUtility::VAR_FLOAT), 2);
    }
}
?>
<section class="section bg-brand-light" id="itemRatings">
    <div class="container container--narrow">
        <div class="rating-review">
            <?php if ($totReviews == 0) { ?>
            <div class="no-data-found">
                <div class="no-data-found-img">
                    <img src="<?php echo CONF_WEBROOT_URL; ?>images/retina/empty/empty-state-no-reviews.svg">
                </div>
                <div class="data">
                    <h6><?php echo  Labels::getLabel('Lbl_Be_the_first_one_to_write_a_Review!', $siteLangId)?></h6>
                    <div class="action">
                        <a href="<?php echo UrlHelper::generateUrl('Reviews', 'write', array($product_id)); ?>"
                            class="btn btn-outline-bottom"><?php echo  Labels::getLabel('Lbl_Write_a_review', $siteLangId)?></a>
                    </div>
                </div>
            </div>
            <?php } ?>
            <?php if ($totReviews > 0) { ?>
            <h2><?php echo Labels::getLabel('LBl_Rating_&_Reviews', $siteLangId); ?></h2>
            <div class="rating-review-wrapper">
                <div class="rating-points">
                    <h3><?php echo round($avgRating, 1); ?></h3>
                    <div class="product-rating">
                        <ul>
                            <?php for ($star = 0; $star < round($avgRating, 1); $star++) { ?>
                            <li>

                            </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <p class="rating-count">
                        <span>
                            <i class="icn icn-rating-user">
                                <svg class="svg">
                                    <use
                                        xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#rating-user">
                                    </use>
                                </svg>
                            </i>
                        </span>
                        <?php echo Labels::getLabel('Lbl_Based_on', $siteLangId), ' ', $totReviews, ' ', Labels::getLabel('Lbl_ratings', $siteLangId); ?>
                    </p>
                    <?php if ($canSubmitFeedback) { ?>
                    <div class="col-auto <?php echo ($totReviews > 0) ? 'col-auto' : ''; ?>">
                        <a onClick="rateAndReviewProduct(<?php echo $product_id; ?>)" href="javascript:void(0)"
                            class="btn btn-brand <?php echo ($totReviews > 0) ? 'btn-block' : ''; ?>"><?php echo Labels::getLabel('Lbl_Add_Review', $siteLangId); ?></a>
                    </div>
                    <?php } ?>
                </div>
                <div class="listing--progress-wrapper ">
                    <ul class="listing--progress">
                        <li>
                            <div class="progress__bar">
                                <div title="<?php echo $rate_5_width, '% ', Labels::getLabel('LBL_Number_of_reviews_have_5_stars', $siteLangId); ?>"
                                    style="width: <?php echo $rate_5_width; ?>%" class="progress__fill"></div>
                            </div>
                            <span
                                class="progress_count"><?php echo Labels::getLabel('LBL_Excellent', $siteLangId); ?></span>
                        </li>
                        <li>
                            <div class="progress__bar">
                                <div title="<?php echo $rate_4_width, '% ', Labels::getLabel('LBL_Number_of_reviews_have_4_stars', $siteLangId); ?>"
                                    style="width: <?php echo $rate_4_width; ?>%" class="progress__fill"></div>
                            </div>
                            <span class="progress_count"><?php echo Labels::getLabel('LBL_Good', $siteLangId); ?></span>
                        </li>
                        <li>
                            <div class="progress__bar">
                                <div title="<?php echo $rate_3_width, '% ', Labels::getLabel('LBL_Number_of_reviews_have_3_stars', $siteLangId); ?>"
                                    style="width: <?php echo $rate_3_width; ?>%" class="progress__fill"></div>
                            </div>
                            <span
                                class="progress_count"><?php echo Labels::getLabel('LBL_Average', $siteLangId); ?></span>
                        </li>
                        <li>
                            <div class="progress__bar">
                                <div title="<?php echo $rate_2_width, '% ', Labels::getLabel('LBL_Number_of_reviews_have_2_stars', $siteLangId); ?>"
                                    style="width: <?php echo $rate_2_width; ?>%" class="progress__fill"></div>
                            </div>
                            <span
                                class="progress_count"><?php echo Labels::getLabel('LBL_Below_Average', $siteLangId); ?></span>
                        </li>
                        <li>
                            <div class="progress__bar">
                                <div title="<?php echo $rate_1_width, '% ', Labels::getLabel('LBL_Number_of_reviews_have_1_stars', $siteLangId); ?>"
                                    style="width: <?php echo $rate_1_width; ?>%" class="progress__fill"></div>
                            </div>
                            <span class="progress_count"><?php echo Labels::getLabel('LBL_Poor', $siteLangId); ?></span>
                        </li>
                    </ul>
                </div>
            </div>
            <?php } ?>

        </div>
        <?php if ($totReviews > 0) { ?>
        <div class="review_wrapper">
            <div class="review-head">
                <h2><?php echo Labels::getLabel('Lbl_Customer_Reviews', $siteLangId) ?>(<?php echo $totReviews; ?>)</h2>
                <div>
                    <div class="dropdown">
                        <button class="link-arrow-down" type="button" data-toggle="dropdown" data-display="static"
                            aria-haspopup="true" aria-expanded="false">
                            <span><?php echo Labels::getLabel('Lbl_Most_Recent', $siteLangId) ?></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-anim">
                            <ul class="drop nav nav-block">
                                <li class="nav__item selected"><a class="dropdown-item nav__link"
                                        href="javascript:void(0);" data-sort="most_recent"
                                        onclick="getSortedReviews(this);return false;"><?php echo Labels::getLabel('Lbl_Most_Recent', $siteLangId) ?></a>
                                </li>
                                <li class="nav__item selected"><a class="dropdown-item nav__link"
                                        href="javascript:void(0);" data-sort="most_helpful"
                                        onclick="getSortedReviews(this);return false;"><?php echo Labels::getLabel('Lbl_Most_Helpful', $siteLangId) ?></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="listing__all"></div>
            <!--<div id="loadMoreReviewsBtnDiv" class="align--center"></div>-->
        </div>
        <?php } ?>
    </div>
</section>
<script>
var $linkMoreText = '<?php echo Labels::getLabel('Lbl_Read_More', $siteLangId); ?>';
var $linkLessText = '<?php echo Labels::getLabel('Lbl_Read_Less', $siteLangId); ?>';
$('#itemRatings div.progress__fill').css({
    'clip': 'rect(0px, <?php echo $pixelToFillRight; ?>px, 160px, 0px)'
});

$(document).ready(function() {
    function DropDown(el) {
        this.dd = el;
        this.placeholder = this.dd.children('span');
        this.opts = this.dd.find('ul.drop li');
        this.val = '';
        this.index = -1;
        this.initEvents();
    }

    DropDown.prototype = {
        initEvents: function() {
            var obj = this;
            obj.dd.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).toggleClass('active');
            });
            obj.opts.on('click', function() {
                var opt = $(this);
                obj.val = opt.text();
                obj.index = opt.index();
                obj.placeholder.text(obj.val);
                opt.siblings().removeClass('selected');
                opt.filter(':contains("' + obj.val + '")').addClass('selected');
            }).change();
        },
        getValue: function() {
            return this.val;
        },
        getIndex: function() {
            return this.index;
        }
    };

    $(function() {
        var dd1 = new DropDown($('.js-wrap-drop-reviews'));
        $(document).click(function() {
            $('.wrap-drop').removeClass('active');
        });
    });
});
</script>