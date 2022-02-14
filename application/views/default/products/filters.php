<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$currencySymbolLeft = isset($currencySymbolLeft) ? $currencySymbolLeft : CommonHelper::getCurrencySymbolLeft();
$currencySymbolRight = isset($currencySymbolRight) ? $currencySymbolRight : CommonHelper::getCurrencySymbolRight();

$catCodeArr = array();

if (isset($prodcat_code)) {
    $currentCategoryCode = substr($prodcat_code, 0, -1);
    $catCodeArr = explode("_", $currentCategoryCode);
    array_walk($catCodeArr, function (&$n) {
        $n = FatUtility::int($n);
    });
}
?>
<?php
if ($shopCatFilters) {
    $searchFrm->setFormTagAttribute('onSubmit', 'searchProducts(this); return(false);');
    $keywordFld = $searchFrm->getField('keyword');
    $keywordFld->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Shop_Search', $siteLangId));
    $keywordFld->htmlAfterField = '<input name="btnSrchSubmit" value="" type="submit" class="input-submit">';
    /* $keywordFld = $frmProductSearch->getField('keyword');
      $keywordFld->overrideFldType("hidden"); */
    ?>
<div class="product-search">
    <!--<form class="form">
            <input placeholder="Search" class="input-field nofocus" value="" type="text">
            <input name="btnSrchSubmit" value="" class="input-submit" type="submit">
        </form>-->
    <?php
        $searchFrm->addFormTagAttribute('class', ' ');
        echo $searchFrm->getFormTag();
        $fld = $searchFrm->getField('keyword');
        $fld->addFieldTagAttribute("class", "input-field nofocus search-magni");
        echo $searchFrm->getFieldHTML('keyword');
        echo $searchFrm->getFieldHTML('shop_id');
        echo $searchFrm->getFieldHTML('join_price');
        echo '</form>';
        echo $searchFrm->getExternalJS();
        ?>
</div>
<?php }
?>
<!--Filters[ -->
<div class="filters_head">
    <div class="widgets-head">
        <div class="filter-head filter-head-js">
            <h6 class="m-0"><?php echo Labels::getLabel('LBL_FILTERS', $siteLangId); ?></h6>
            <a href="javascript:void(0)" class="resetAll link" id="resetAll" onClick="resetListingFilter()"
                style="display:none;">
                <?php echo Labels::getLabel('LBL_Clear_All', $siteLangId); ?>
            </a>
        </div>
    </div>
    <div class="selected-filters" id="filters"></div>
</div>
<!-- ] -->
<div class="filters_body" id="filters_body--js">
    <?php
$productTypeArr = array();
if (ALLOW_RENT > 0) {
    $productTypeArr[2] = 'For Rent';
}
if (ALLOW_SALE > 0) {
    $productTypeArr[1] = 'For Sale';
}


$productTypeCheckedArr = (!empty($productTypeCheckedArr)) ? $productTypeCheckedArr : array();
?>
    <!--Product Type Filters[ -->
    <div class="sidebar-widget">
        <div class="sidebar-widget__head" data-toggle="collapse" data-target="#product-type--js" aria-expanded="true">
            <?php echo Labels::getLabel('LBL_Product_Type', $siteLangId); ?> </div>
        <div class="sidebar-widget__body collapse show" id="product-type--js">
            <ul class="list-vertical">
                <?php
            foreach ($productTypeArr as $key => $productType) {
                if ((ALLOW_SALE && !empty($prodTypeArr) ) || (ALLOW_RENT && !empty($prodTypeArr) )) {
                    ?>
                <li>
                    <label class="radio radioinputs" id="producttype_<?php echo $key; ?>">

                        <span class="lb-txt"><?php echo $productType; ?></span>
                        <input name="producttype"
                            <?php echo (in_array($key, $productTypeCheckedArr)) ? "checked='true'" : ""; ?>
                            data-id="producttype_<?php echo $key; ?>" value="<?php echo $key; ?>"
                            data-title=" <?php echo $productType; ?> " type="radio">
                    </label>
                </li>
                <?php
                }
            }
            ?>
            </ul>
        </div>
    </div>
    <!-- ] -->
    <?php if (isset($categoriesArr) && $categoriesArr) { ?>
    <div class="sidebar-widget">
        <div class="sidebar-widget__head" data-toggle="collapse" data-target="#category" aria-expanded="true"
            aria-controls="category">
            <?php echo Labels::getLabel('LBL_Categories', $siteLangId); ?>
        </div>
        <div class="sidebar-widget__body collapse show" id="category">
            <?php if (!$shopCatFilters) { ?>
            <div id="accordian" class="cat-accordion toggle-target scrollbar-filters scroll scroll-y">
                <ul>
                    <?php
                        foreach ($categoriesArr as $cat) {
                            $catUrl = UrlHelper::generateUrl('category', 'view', array($cat['prodcat_id']));
                            ?>
                    <li>
                        <?php
                                if (count($cat['children']) > 0) {
                                    echo '<span class="acc-trigger" ripple="ripple" ripple-color="#000"></span>';
                                }
                                ?>
                        <a class="filter_categories" data-id="<?php echo $cat['prodcat_id']; ?>"
                            href="<?php echo $catUrl; ?>"><?php echo $cat['prodcat_name']; ?></a>
                        <?php
                                   if (count($cat['children']) > 0) {
                                       echo '<ul>';
                                       foreach ($cat['children'] as $children) {
                                           ?>
                    <li>
                        <?php
                                        if (isset($children['children']) && count($children['children']) > 0) {
                                            echo '<span class="acc-trigger" ripple="ripple" ripple-color="#000"></span>';
                                        }
                                        ?>
                        <a class="filter_categories" data-id="<?php echo $children['prodcat_id']; ?>"
                            href="<?php echo UrlHelper::generateUrl('category', 'view', array($children['prodcat_id'])); ?>"><?php echo $children['prodcat_name']; ?></a>
                        <?php
                                           if (isset($children['children']) && count($children['children']) > 0) {
                                               echo '<ul>';
                                               foreach ($children['children'] as $subChildren) {
                                                   ?>
                    <li>
                        <?php
                                                if (isset($subChildren['children']) && count($subChildren['children']) > 0) {
                                                    echo '<span class="acc-trigger" ripple="ripple" ripple-color="#000"></span>';
                                                }
                                                ?>
                        <a class="filter_categories" data-id="<?php echo $subChildren['prodcat_id']; ?>"
                            href="<?php echo UrlHelper::generateUrl('category', 'view', array($subChildren['prodcat_id'])); ?>"><?php echo $subChildren['prodcat_name']; ?></a>

                        <?php
                                                if (isset($subChildren['children']) && count($subChildren['children']) > 0) {
                                                    echo '<ul>';
                                                    foreach ($subChildren['children'] as $subSubChildren) {
                                                        ?>

                    <li>
                        <?php
                                                        if (isset($subSubChildren['children']) && count($subSubChildren['children']) > 0) {
                                                            echo '<span class="acc-trigger" ripple="ripple" ripple-color="#000"></span>';
                                                        }
                                                        ?>
                        <a class="filter_categories" data-id="<?php echo $subSubChildren['prodcat_id']; ?>"
                            href="<?php echo UrlHelper::generateUrl('category', 'view', array($subSubChildren['prodcat_id'])); ?>"><?php echo $subSubChildren['prodcat_name']; ?></a>
                    </li>
                    <?php
                                                }
                                                echo '</ul>';
                                            }
                                            ?>
                    </li>
                    <?php
                                        }
                                        echo '</ul>';
                                    }
                                    ?>
                    </li>
                    <?php
                                }
                                echo '</ul>';
                            }
                            ?>

                    </li>
                    <?php }
                        ?>
                </ul>
                <!--<a onClick="alert('Pending')" class="btn btn--link ripplelink"><?php echo Labels::getLabel('LBL_View_more', $siteLangId); ?> </a> -->
            </div>
            <?php } else { ?>
            <div class="scrollbar-filters scroll scroll-y" id="scrollbar-filters">
                <ul class="list-vertical">
                    <?php
                        $seprator = '&raquo;&raquo;&nbsp;&nbsp;';
                        foreach ($categoriesArr as $cat) {
                            $catName = $cat['prodcat_name'];
                            $productCatCode = explode("_", $cat['prodcat_code']);
                            $productCatName = '';
                            $seprator = '';
                            foreach ($productCatCode as $code) {
                                $code = FatUtility::int($code);
                                if ($code) {
                                    if (isset($categoriesArr[$code]['prodcat_name'])) {
                                        $productCatName .= $seprator . $categoriesArr[$code]['prodcat_name'];
                                        $seprator = '&raquo;&raquo;&nbsp;&nbsp;';
                                    }
                                }
                            }
                            ?>
                    <li>
                        <label class="checkbox brand" id="prodcat_<?php echo $cat['prodcat_id']; ?>">
                            <span class="lb-txt"><?php echo $productCatName; ?></span>
                            <input name="category" data-id="prodcat_<?php echo $cat['prodcat_id']; ?>"
                                value="<?php echo $cat['prodcat_id']; ?>" data-title="<?php echo $productCatName; ?>"
                                type="checkbox"
                                <?php echo (in_array($cat['prodcat_id'], $prodcatArr)) ? "checked" : ""; ?>>
                        </label>
                    </li>
                    <?php }
                        ?>
                </ul>
                <!--<a onClick="alert('Pending')" class="btn btn--link ripplelink"><?php echo Labels::getLabel('LBL_View_More', $siteLangId); ?> </a> -->
            </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <!--Price Filters[ -->
    <?php if (isset($priceArr) && $priceArr) { ?>
    <div class="sidebar-widget">
        <div class="sidebar-widget__head" data-toggle="collapse" data-target="#price" aria-expanded="true"
            aria-controls="price">
            <?php echo Labels::getLabel('LBL_Price', $siteLangId) . ' (' . (CommonHelper::getCurrencySymbolRight() ? CommonHelper::getCurrencySymbolRight() : CommonHelper::getCurrencySymbolLeft()) . ')'; ?>
        </div>
        <div class="sidebar-widget__body collapse show" id="price">
            <div class="filter-content toggle-target">
                <div class="prices" id="perform_price">
                    <div id="rangeSlider"></div>
                </div>
                <div class="clear"></div>
                <div class="slide__fields">
                    <?php $symbol = CommonHelper::getCurrencySymbolRight() ? CommonHelper::getCurrencySymbolRight() : CommonHelper::getCurrencySymbolLeft(); ?>
                    <div class="price-input">
                        <div class="price-text-box input-group">
                            <div class="input-group-prepend"><span
                                    class="input-group-text"><?php echo $symbol; ?></span></div>
                            <input class="input-filter form-control" value="<?php echo floor($priceArr['minPrice']); ?>"
                                data-defaultvalue="<?php echo $filterDefaultMinValue; ?>" name="priceFilterMinValue"
                                type="text" id="priceFilterMinValue">

                        </div>
                    </div>
                    <span class="dash"> - </span>
                    <div class="price-input">
                        <div class="price-text-box input-group">
                            <div class="input-group-prepend"><span
                                    class="input-group-text"><?php echo $symbol; ?></span></div>
                            <input class="input-filter form-control" value="<?php echo ceil($priceArr['maxPrice']); ?>"
                                data-defaultvalue="<?php echo $filterDefaultMaxValue; ?>" name="priceFilterMaxValue"
                                type="text" id="priceFilterMaxValue">

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php }
?>
    <!-- ] -->

    <!--Brand Filters[ -->
    <?php
if (isset($brandsArr) && count($brandsArr) > 1) {
    $brandsCheckedArr = (isset($brandsCheckedArr) && !empty($brandsCheckedArr)) ? $brandsCheckedArr : array();
    ?>

    <div class="sidebar-widget">
        <div class="sidebar-widget__head" data-toggle="collapse" data-target="#brand" aria-expanded="true">
            <?php echo Labels::getLabel('LBL_Brand', $siteLangId); ?></div>
        <div class="sidebar-widget__body collapse show" id="brand">
            <div class="scrollbar-filters scroll scroll-y" id="scrollbar-filters">
                <ul class="list-vertical brandFilter-js">
                    <?php
                    foreach ($brandsArr as $brand) {
                        if ($brand['brand_id'] == null) {
                            continue;
                        }
                        ?>
                    <li>
                        <label class="checkbox brand" id="brand_<?php echo $brand['brand_id']; ?>">
                            <span class="lb-txt"><?php echo $brand['brand_name']; ?></span>
                            <input name="brands" data-id="brand_<?php echo $brand['brand_id']; ?>"
                                value="<?php echo $brand['brand_id']; ?>"
                                data-title="<?php echo $brand['brand_name']; ?>" type="checkbox">
                        </label>
                    </li>
                    <?php }
                    ?>
                </ul>
            </div>
            <?php if (count($brandsArr) >= 10) { ?>
            <div class="text-right mt-4">
                <a href="javascript:void(0)" onClick="brandFilters()"
                    class="link-plus"><?php echo Labels::getLabel('LBL_View_More', $siteLangId); ?> </a>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php }
?>
    <!-- ] -->

    <!-- Option Filters[ -->
    <?php
$optionIds = array();
$optionValueCheckedArr = (isset($optionValueCheckedArr) && !empty($optionValueCheckedArr)) ? $optionValueCheckedArr : array();

if (isset($options) && $options) {
    ?>
    <?php

    function sortByOrder($a, $b)
    {
        return $a['option_id'] - $b['option_id'];
    }

    usort($options, 'sortByOrder');
    $optionName = '';
    $liData = '';

    foreach ($options as $optionRow) {
        if ($optionName != $optionRow['option_name']) {
            if ($optionName != '') {
                echo "</ul></div> </div>";
            }
            $optionName = ($optionRow['option_name']) ? $optionRow['option_name'] : $optionRow['option_identifier'];
            ?>

    <div class="sidebar-widget">
        <div class="sidebar-widget__head" data-toggle="collapse"
            data-target="#option<?php echo $optionRow['option_id']; ?>" aria-expanded="true">
            <?php echo ($optionRow['option_name']) ? $optionRow['option_name'] : $optionRow['option_identifier']; ?>
        </div>
        <div class="sidebar-widget__body collapse show" id="option<?php echo $optionRow['option_id']; ?>">
            <ul class="list-vertical">
                <?php
                    }
                    $optionValueId = $optionRow['option_id'] . '_' . $optionRow['optionvalue_id'];
                    ?>
                <li>
                    <label class="checkbox optionvalue" id="optionvalue_<?php echo $optionRow['optionvalue_id']; ?>">
                        <span
                            class="lb-txt"><?php echo ($optionRow['optionvalue_name']) ? $optionRow['optionvalue_name'] : $optionRow['optionvalue_identifier']; ?></span>
                        <input name="optionvalues" data-id="optionvalue_<?php echo $optionRow['optionvalue_id']; ?>"
                            value="<?php echo $optionValueId; ?>"
                            data-title="<?php echo ($optionRow['optionvalue_name']) ? $optionRow['optionvalue_name'] : $optionRow['optionvalue_identifier']; ?>"
                            type="checkbox"
                            <?php echo (in_array($optionRow['optionvalue_id'], $optionValueCheckedArr)) ? "checked='true'" : ""; ?>>
                    </label>
                </li>
                <?php
                }
                echo "</ul></div> </div>";
            }
            ?>
                <!-- ]-->

                <!-- Condition Filters[ -->

                <?php
            if (isset($conditionsArr) && count($conditionsArr) > 1) {
                $conditionsCheckedArr = (isset($conditionsCheckedArr) && !empty($conditionsCheckedArr)) ? $conditionsCheckedArr : array();
                ?>

                <div class="sidebar-widget">
                    <div class="sidebar-widget__head" data-toggle="collapse" data-target="#condition"
                        aria-expanded="true">
                        <?php echo Labels::getLabel('LBL_Condition', $siteLangId); ?></div>
                    <div class="sidebar-widget__body collapse show" id="condition">
                        <ul class="list-vertical">
                            <?php
                            foreach ($conditionsArr as $condition) {
                                if (empty($condition) || $condition['selprod_condition'] == 0) {
                                    continue;
                                }
                                ?>
                            <li>
                                <label class="checkbox condition"
                                    id="condition_<?php echo $condition['selprod_condition']; ?>">
                                    <span
                                        class="lb-txt"><?php echo Product::getConditionArr($siteLangId)[$condition['selprod_condition']]; ?></span>
                                    <input name="conditions"
                                        data-id="condition_<?php echo $condition['selprod_condition']; ?>"
                                        value="<?php echo $condition['selprod_condition']; ?>"
                                        data-title="<?php echo Product::getConditionArr($siteLangId)[$condition['selprod_condition']]; ?>"
                                        type="checkbox"
                                        <?php echo (in_array($condition['selprod_condition'], $conditionsCheckedArr)) ? "checked='true'" : ""; ?>>
                                </label>
                            </li>
                            <?php }
                            ?>
                        </ul>
                    </div>
                </div>

                <?php }
            ?>
                <!-- ] -->
                <!--Availability Filters[ -->
                <?php
            if (isset($availabilityArr) && count($availabilityArr) > 1) {
                $availability = isset($availability) ? $availability : 0;
                ?>

                <div class="sidebar-widget">
                    <div class="sidebar-widget__head  collapsed" data-toggle="collapse" data-target="#availability"
                        aria-expanded="true">
                        <?php echo Labels::getLabel('LBL_Availability', $siteLangId); ?>
                    </div>
                    <div class="sidebar-widget__body collapse show" id="availability">
                        <div class="toggle-target">
                            <ul class="listing--vertical listing--vertical-chcek">
                                <li>
                                    <label class="checkbox availability" id="availability_1">
                                        <span
                                            class="lb-txt"><?php echo Labels::getLabel('LBL_Exclude_out_of_stock', $siteLangId); ?></span>
                                        <input value="1" name="out_of_stock" type="checkbox"
                                            <?php echo ($availability == 1) ? "checked='true'" : ""; ?>>
                                    </label>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php }
            ?>

        </div>
        <!-- ] -->
        <!-- BOC for Custom Fields/Attributes Filters -->
        <?php
    if (isset($attrList) && count($attrList) > 0 && FatApp::getConfig('CONF_USE_CUSTOM_FIELDS', FatUtility::VAR_INT, 0) == applicationConstants::YES) {
        $attrCheckedArr = (isset($attrCheckedArr) && !empty($attrCheckedArr)) ? $attrCheckedArr : array();
        foreach ($attrList as $attKey => $attr) {
            $attrOpts = explode("\n", $attr['attr_options']);
            if (!empty($attrOpts) && $attr['attr_display_in_filter'] == applicationConstants::YES) {
                $attrFldName = str_replace("prodnumattr_", "", $attr['attr_fld_name']);
                $attrFldName = $attrFldName . '_' . $attr['attr_attrgrp_id'];
                ?>
        <div class="sidebar-widget">
            <div class="sidebar-widget__head  collapsed" data-toggle="collapse"
                data-target="#attributes_<?php echo $attKey; ?>" aria-expanded="true">
                <?php echo $attr['attr_name']; ?>
            </div>
            <div class="sidebar-widget__body collapse show" id="attributes_<?php echo $attKey; ?>"
                data-parent="#collection-sidebar">
                <div class="toggle-target">
                    <ul class="listing--vertical listing--vertical-chcek">
                        <?php
                                foreach ($attrOpts as $key => $attrOpt) {
                                    $attrId = $attrFldName . '_' . $key;
                                    ?>
                        <li>
                            <label class="checkbox" id="<?php echo $attrId; ?>"
                                group_name="<?php echo $attr['attr_name']; ?>">
                                <input name="attributes" value="1" type="checkbox" <?php
                                            if (in_array($attrId, $attrCheckedArr)) {
                                                echo "checked='checked'";
                                            }
                                            ?>>
                                <i
                                    class="input-helper"></i><?php echo $attr['attr_prefix'] . $attrOpt . $attr['attr_postfix']; ?>
                            </label>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php
            }
        }
    }
    ?>
        <!-- EOC for Custom Fields/Attributes Filters -->
        <script language="javascript">
        var catCodeArr = <?php echo json_encode($catCodeArr); ?>;
        $.each(catCodeArr, function(key, value) {
            if ($("ul li a[data-id='" + value + "']").parent().find('span')) {
                $("ul li a[data-id='" + value + "']").parent().find('span:first').addClass('is--active');
                $("ul li a[data-id='" + value + "']").parent().find('ul:first').css('display', 'block');
            }
        });
        $("document").ready(function() {
            <?php if (FatApp::getConfig('CONF_FILTERS_LAYOUT', FatUtility::VAR_INT, 1) == FilterHelper::LAYOUT_TOP  ||  $headerFormParamsAssocArr['vtype'] == 'map') { ?>
            $(window).resize(function() {
                var windowSize = $(window).width();
                if (windowSize > 992) {
                    $('#filters_body--js .sidebar-widget__body').removeClass('show');
                    $('#filters_body--js .sidebar-widget__head').attr('aria-expanded', false);
                    $('#filters_body--js .sidebar-widget__body').attr('data-parent',
                        '#collection-sidebar');
                } else {
                    $('#filters_body--js .sidebar-widget__body').addClass('show');
                    $('#filters_body--js .sidebar-widget__head').attr('aria-expanded', true);
                    $('#filters_body--js .sidebar-widget__body').removeAttr('data-parent');
                    }
            });
            $(window).trigger('resize');
            <?php } ?>

            var min = 0;
            var max = 0;
            <?php if (isset($priceArr) && $priceArr) { ?>
            var $from = $('input[name="priceFilterMinValue"]');
            var $to = $('input[name="priceFilterMaxValue"]');
            var range,
                min = Math.floor(<?php echo $filterDefaultMinValue; ?>),
                max = Math.floor(<?php echo $filterDefaultMaxValue; ?>),
                from,
                to;
            const len = 4;
            var step = (max - min) / (len - 1);
            var steps = Array(len).fill().map((_, idx) => min + (idx * step));
            var rangeSlider = document.getElementById('rangeSlider');
            noUiSlider.create(rangeSlider, {
                start: [$from.val(), $to.val()],
                step: Math.floor(step / len),
                range: {
                    'min': [min],
                    'max': [max]
                },
                connect: true,
                tooltips: true,
                direction: '<?php echo $layoutDirection; ?>',
                pips: {
                    mode: 'values',
                    values: steps,
                    density: 4
                }
            });
            rangeSlider.noUiSlider.on('change', function(values, handle) {
                var value = values[handle];
                /* handle return 0,1(min hanle and max handle) in RTL it return opposite */
                if (handle) {
                    to = value;
                } else {
                    from = value;
                }
                updateValues();
                addPricefilter(true);
            });
            var updateRange = function() {
                rangeSlider.noUiSlider.set([from, to]);
                updateValues();
                addPricefilter();
            };
            $from.on("change", function() {
                from = $(this).prop("value");
                if (!$.isNumeric(from)) {
                    from = 0;
                }
                if (from < min) {
                    from = min;
                }
                if (from >= max) {
                    from = (max - 1);
                }
                updateRange();
            });
            $to.on("change", function() {
                to = $(this).prop("value");
                if (!$.isNumeric(to)) {
                    to = 0;
                }
                if (to > max) {
                    to = max;
                }
                if (to < min) {
                    to = min;
                }
                updateRange();
            });
            var updateValues = function() {
                $from.prop("value", from);
                $to.prop("value", to);
            };
            <?php } ?>

            /* left side filters scroll bar[ */

            <?php if (true === $shopCatFilters) { ?>
            // new SimpleBar(document.getElementById('accordian'));
            <?php } ?>
            var x = document.getElementsByClassName("scrollbar-filters");
            var i;
            for (i = 0; i < x.length; i++) {
                new SimpleBar(x[i]);
            }
            /* ] */

            /* left side filters expand-collapse functionality [ */
            $('.span--expand').bind('click', function() {
                $(this).parent('li.level').toggleClass('is-active');
                $(this).toggleClass('is--active');
                $(this).next('ul').toggle("");
            });
            $('.span--expand').click();
            /* ] */

            var min_price = <?php echo (isset($priceArr['minPrice'])) ? $priceArr['minPrice'] : 0; ?>;
            var max_price = <?php echo (isset($priceArr['minPrice'])) ? $priceArr['maxPrice'] : 0; ?>;
            updatePriceFilter(min_price, max_price);
            
            if ('rtl' == langLbl.layoutDirection && 0 < $("[data-simplebar]").length) {
                $("[data-simplebar]").attr('data-simplebar-direction', 'rtl');
            }
        });
        $("#accordian li span.acc-trigger").click(function() {
            var link = $(this);
            var closest_ul = link.siblings("ul");
            if (link.hasClass("is--active")) {
                closest_ul.slideUp();
                link.removeClass("is--active");
            } else {
                closest_ul.slideDown();
                link.addClass("is--active");
            }
        });
        $('.dropdown-menu').on('click', function(e) {
            e.stopPropagation();
        });
        </script>
    </div>