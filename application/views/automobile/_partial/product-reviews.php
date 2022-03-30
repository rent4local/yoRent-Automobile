<?php
defined('SYSTEM_INIT') or die('Invalid usage');
$totReviews = $avgRating = $pixelToFillRight = 0;
$sellerId = (isset($sellerId)) ? $sellerId : 0;
if (!empty($reviews)) {
    $totReviews = (!empty($reviews['totReviews'])) ? FatUtility::int($reviews['totReviews']) : 0;
}
$rated_type_1_width = $rated_type_2_width = $rated_type_3_width = $rated_type_4_width = 0;
$rated_type_1 = $rated_type_2 = $rated_type_3 = $rated_type_4 = 0;
if (!empty($ratingAspects)) {
    $rated_type_1 = (isset($ratingAspects[SelProdRating::TYPE_PRODUCT])) ? $ratingAspects[SelProdRating::TYPE_PRODUCT]['prod_rating'] : 0;
    $rated_type_2 = (isset($ratingAspects[SelProdRating::TYPE_SELLER_SHIPPING_QUALITY])) ? $ratingAspects[SelProdRating::TYPE_SELLER_SHIPPING_QUALITY]['prod_rating'] : 0;
    $rated_type_3 = (isset($ratingAspects[SelProdRating::TYPE_SELLER_STOCK_AVAILABILITY])) ? $ratingAspects[SelProdRating::TYPE_SELLER_STOCK_AVAILABILITY]['prod_rating'] : 0;
    $rated_type_4 = (isset($ratingAspects[SelProdRating::TYPE_SELLER_PACKAGING_QUALITY])) ? $ratingAspects[SelProdRating::TYPE_SELLER_PACKAGING_QUALITY]['prod_rating'] : 0;

    $rated_type_1_width = round(FatUtility::convertToType($rated_type_1 / 5 * 100, FatUtility::VAR_FLOAT), 2);
    $rated_type_2_width = round(FatUtility::convertToType($rated_type_2 / 5 * 100, FatUtility::VAR_FLOAT), 2);
    $rated_type_3_width = round(FatUtility::convertToType($rated_type_3 / 5 * 100, FatUtility::VAR_FLOAT), 2);
    $rated_type_4_width = round(FatUtility::convertToType($rated_type_4 / 5 * 100, FatUtility::VAR_FLOAT), 2);

    $avgRating = ($rated_type_1 + $rated_type_2 + $rated_type_3 + $rated_type_4) / 4;
    $pixelToFillRight = $avgRating / 5 * 160;
    $pixelToFillRight = FatUtility::convertToType($pixelToFillRight, FatUtility::VAR_FLOAT);
}
?>
<section class="section bg-reviews" id="itemRatings">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-8">
                <div class="section__title mb-4">
                    <h2><?php echo Labels::getLabel('LBl_Rating_&_Reviews', $siteLangId); ?></h2>
                </div>

                <div class="rating-review">
                    <?php if ($totReviews == 0) { ?>
                        <div class="no-data-found">
                            <div class="no-data-found-img">
                                <img src="<?php echo CONF_WEBROOT_URL; ?>images/retina/empty/empty-state-no-reviews.svg">
                            </div>
                            <?php if ($canSubmitFeedback) { ?>
                                <div class="data">
                                    <h4><?php echo  Labels::getLabel('Lbl_Be_the_first_one_to_write_a_Review!', $siteLangId) ?>
                                    </h4>
                                    <div class="action mt-3">
                                        <a href="<?php echo UrlHelper::generateUrl('Reviews', 'write', array($product_id, $sellerId)); ?>" class="btn btn-outline-brand"><?php echo  Labels::getLabel('Lbl_Write_a_review', $siteLangId) ?></a>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="data">
                                    <h4><?php echo  Labels::getLabel('Lbl_Reviews_Not_Posted_Yet!', $siteLangId) ?>
                                    </h4>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    <?php if ($totReviews > 0) { ?>
                        <div class="rating-review-wrapper">
                            <div class="rating-points">
                                <h3><?php echo round($avgRating, 1); ?></h3>
                                <div class="product-rating ">
                                    <ul>
                                        <?php for ($ii = 0; $ii < 5; $ii++) {
                                            $liClass = '';
                                            if ($ii < round($avgRating)) {
                                                $liClass = 'active';
                                            }
                                        ?>
                                            <li class="<?php echo $liClass; ?>"></li>
                                        <?php } ?>
                                    </ul>
                                    <p><?php echo Labels::getLabel('Lbl_Based_on', $siteLangId), ' ', $totReviews, ' ', Labels::getLabel('Lbl_reviews', $siteLangId); ?>
                                </div>

                                <?php if ($canSubmitFeedback) { ?>
                                    <div class=" <?php echo ($totReviews > 0) ? 'col-auto' : ''; ?>">
                                        <a onClick="rateAndReviewProduct(<?php echo $product_id; ?>, <?php echo $sellerId;?>)" href="javascript:void(0)" class="btn btn-brand <?php echo ($totReviews > 0) ? 'btn-block' : ''; ?>"><?php echo Labels::getLabel('Lbl_Add_Review', $siteLangId); ?></a>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="listing--progress-wrapper ">
                                <ul class="listing--progress">
                                    <li>
                                        <span class="progress__lbl"><?php echo Labels::getLabel('LBL_Product', $siteLangId); ?></span>
                                        <div class="progress__bar">
                                            <div title="<?php echo $rated_type_1_width, '% ', Labels::getLabel('LBL_Number_of_reviews_have_5_stars', $siteLangId); ?>" style="width: <?php echo $rated_type_1_width; ?>%" class="progress__fill"></div>
                                        </div>
                                        <span class="progress_count"><?php echo $rated_type_1; ?></span>
                                    </li>
                                    <li>
                                        <span class="progress__lbl"><?php echo Labels::getLabel('LBL_Shipping_Quality', $siteLangId); ?></span>
                                        <div class="progress__bar">
                                            <div title="<?php echo $rated_type_2_width, '% ', Labels::getLabel('LBL_Number_of_reviews_have_4_stars', $siteLangId); ?>" style="width: <?php echo $rated_type_2_width; ?>%" class="progress__fill"></div>
                                        </div>
                                        <span class="progress_count"><?php echo $rated_type_2; ?></span>
                                    </li>
                                    <li>
                                        <span class="progress__lbl"><?php echo Labels::getLabel('LBL_Stock_Availability', $siteLangId); ?></span>
                                        <div class="progress__bar">
                                            <div title="<?php echo $rated_type_3_width, '% ', Labels::getLabel('LBL_Number_of_reviews_have_3_stars', $siteLangId); ?>" style="width: <?php echo $rated_type_3_width; ?>%" class="progress__fill"></div>
                                        </div>
                                        <span class="progress_count"><?php echo $rated_type_3; ?></span>
                                    </li>
                                    <li>
                                        <span class="progress__lbl"><?php echo Labels::getLabel('LBL_PACKAGING_QUALITY', $siteLangId); ?></span>
                                        <div class="progress__bar">
                                            <div title="<?php echo $rated_type_4_width, '% ', Labels::getLabel('LBL_Number_of_reviews_have_2_stars', $siteLangId); ?>" style="width: <?php echo $rated_type_4_width; ?>%" class="progress__fill"></div>
                                        </div>
                                        <span class="progress_count"><?php echo $rated_type_4; ?></span>
                                    </li>

                                </ul>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php if ($totReviews > 0) { ?>
                    <div class="review_wrapper">
                        <div class="review-head">
                            <h2><?php echo Labels::getLabel('Lbl_Customer_Reviews', $siteLangId) ?>(<?php echo $totReviews; ?>)
                            </h2>
                            <?php if ($totReviews > 1) { ?>
                                <div>
                                    <div class="dropdown">
                                        <button class="link-arrow-down" type="button" data-toggle="dropdown" data-display="static" aria-haspopup="true" aria-expanded="false">
                                            <span><?php echo Labels::getLabel('Lbl_Most_Recent', $siteLangId) ?></span>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-anim">
                                            <ul class="drop nav nav-block">
                                                <li class="nav__item selected"><a class="dropdown-item nav__link" href="javascript:void(0);" data-sort="most_recent" onclick="getSortedReviews(this);return false;"><?php echo Labels::getLabel('Lbl_Most_Recent', $siteLangId) ?></a>
                                                </li>
                                                <li class="nav__item selected"><a class="dropdown-item nav__link" href="javascript:void(0);" data-sort="most_helpful" onclick="getSortedReviews(this);return false;"><?php echo Labels::getLabel('Lbl_Most_Helpful', $siteLangId) ?></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="listing__all"></div>
                        <div id="loadMoreReviewsBtnDiv" class="align--center"></div>
                    </div>
                <?php } ?>
            </div>
        </div>
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