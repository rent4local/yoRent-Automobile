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
        <?php
        $searchFrm->addFormTagAttribute('class', '');
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
<?php } ?>

<div class="filter-horizontal">
    <ul>
        <?php
        $productTypeArr = array();
        if (ALLOW_RENT > 0) {
            $productTypeArr[2] = Labels::getLabel('LBL_For_Rent', $siteLangId);
        }
        if (ALLOW_SALE > 0) {
            $productTypeArr[1] = Labels::getLabel('LBL_For_Sale', $siteLangId);
        }

        $productTypeCheckedArr = (!empty($productTypeCheckedArr)) ? $productTypeCheckedArr : array();
        ?>

        <li class="dropdown">
            <button class="filter-trigger  dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                <?php echo Labels::getLabel('LBL_Product_Type', $siteLangId); ?> </button>
            <div class="filter-target  dropdown-menu dropdown-menu-anim dropdown-menu-fit">
                <ul class="type-selection">
                    <?php foreach ($productTypeArr as $key => $productType) { ?>
                            <li style="border: none;">
                                <input name="producttype"
                                <?php echo (in_array($key, $productTypeCheckedArr)) ? "checked='true'" : ""; ?>
                                       data-id="producttype_<?php echo $key; ?>" value="<?php echo $key; ?>"
                                       data-title=" <?php echo $productType; ?> " type="radio" id="label_<?php echo $key; ?>" />
                                <label class="type-selection_label" id="producttype_<?php echo $key; ?>"
                                       for="label_<?php echo $key; ?>">
                                    <span><?php echo $productType; ?></span>
                                </label>
                            </li>
                            <?php
                        
                    }
                    ?>
                </ul>
            </div>
        </li>

        <?php if (isset($categoriesArr) && $categoriesArr) { ?>
            <li class="dropdown">
                <button class="filter-trigger  dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?php echo Labels::getLabel('LBL_Categories', $siteLangId); ?>
                </button>
                <?php if (!$shopCatFilters) { ?>
                    <div class="filter-target  dropdown-menu dropdown-menu-anim dropdown-menu-fit">
                        <div id="accordian" class="cat-accordion scrollbar-filters scroll scroll-y">
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
                                        <a class="filter_categories" data-id="<?php echo $cat['prodcat_id']; ?>" href="<?php echo $catUrl; ?>"><?php echo $cat['prodcat_name']; ?></a>
                                        <?php if (count($cat['children']) > 0) { ?>
                                            <ul>
                                                <?php
                                                foreach ($cat['children'] as $children) {
                                                    ?>
                                                    <li>
                                                        <?php
                                                        if (isset($children['children']) && count($children['children']) > 0) {
                                                            echo '<span class="acc-trigger" ripple="ripple" ripple-color="#000"></span>';
                                                        }
                                                        ?>
                                                        <a class="filter_categories" data-id="<?php echo $children['prodcat_id']; ?>" href="<?php echo UrlHelper::generateUrl('category', 'view', array($children['prodcat_id'])); ?>"><?php echo $children['prodcat_name']; ?></a>
                                                        <?php if (isset($children['children']) && count($children['children']) > 0) {
                                                            ?>
                                                            <ul>
                                                                <?php
                                                                foreach ($children['children'] as $subChildren) {
                                                                    ?>
                                                                    <li>
                                                                        <?php
                                                                        if (isset($subChildren['children']) && count($subChildren['children']) > 0) {
                                                                            echo '<span class="acc-trigger" ripple="ripple" ripple-color="#000"></span>';
                                                                        }
                                                                        ?>
                                                                        <a class="filter_categories" data-id="<?php echo $subChildren['prodcat_id']; ?>" href="<?php echo UrlHelper::generateUrl('category', 'view', array($subChildren['prodcat_id'])); ?>"><?php echo $subChildren['prodcat_name']; ?></a>

                                                                        <?php if (isset($subChildren['children']) && count($subChildren['children']) > 0) {
                                                                            ?>
                                                                            <ul>
                                                                                <?php
                                                                                foreach ($subChildren['children'] as $subSubChildren) {
                                                                                    ?>

                                                                                    <li>
                                                                                        <?php
                                                                                        if (isset($subSubChildren['children']) && count($subSubChildren['children']) > 0) {
                                                                                            echo '<span class="acc-trigger" ripple="ripple" ripple-color="#000"></span>';
                                                                                        }
                                                                                        ?>
                                                                                        <a class="filter_categories" data-id="<?php echo $subSubChildren['prodcat_id']; ?>" href="<?php echo UrlHelper::generateUrl('category', 'view', array($subSubChildren['prodcat_id'])); ?>"><?php echo $subSubChildren['prodcat_name']; ?></a>
                                                                                    </li>
                                                                                <?php }
                                                                                ?>
                                                                            </ul>
                                                                        <?php } ?>
                                                                    </li>
                                                                <?php } ?>
                                                            </ul> <?php } ?>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        <?php } ?>

                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="filter-target dropdown-menu dropdown-menu-anim dropdown-menu-fit">
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
                                        <input name="category" value="<?php echo $cat['prodcat_id']; ?>" type="checkbox" data-title="<?php echo $catName; ?>" <?php
                                        if (in_array($cat['prodcat_id'], $prodcatArr)) {
                                            echo "checked";
                                        }
                                        ?>><?php echo $productCatName; ?></label></a>
                                </li>

                            <?php }
                            ?>
                        </ul>
                    </div>
                <?php } ?>
            </li>
        <?php } ?>
        <?php if (!empty($priceArr) && isset($priceArr['maxPrice']) && ceil($priceArr['maxPrice']) > 0) { ?>
            <li class="dropdown">
                <button class="filter-trigger  dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo Labels::getLabel('LBL_Price', $siteLangId) . ' (' . (CommonHelper::getCurrencySymbolRight() ? CommonHelper::getCurrencySymbolRight() : CommonHelper::getCurrencySymbolLeft()) . ')'; ?>
                </button>
                <div class="filter-target  dropdown-menu dropdown-menu-anim dropdown-menu-fit">
                    <div class="price-range filter-content">
                        <div class="prices" id="perform_price">
                            <div id="rangeSlider"></div>
                        </div>
                        <div class="clear"></div>
                        <div class="slide__fields">
                            <?php $symbol = CommonHelper::getCurrencySymbolRight() ? CommonHelper::getCurrencySymbolRight() : CommonHelper::getCurrencySymbolLeft(); ?>
                            <div class="price-input">
                                <div class="price-text-box input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><?php echo $symbol; ?></span>
                                    </div>
                                    <input class="input-filter form-control" value="<?php echo floor($priceArr['minPrice']); ?>" data-defaultvalue="<?php echo $filterDefaultMinValue; ?>" name="priceFilterMinValue" type="text" id="priceFilterMinValue">

                                </div>
                            </div>
                            <span class="dash"> - </span>
                            <div class="price-input">
                                <div class="price-text-box input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><?php echo $symbol; ?></span>
                                    </div>
                                    <input class="input-filter form-control" value="<?php echo ceil($priceArr['maxPrice']); ?>" data-defaultvalue="<?php echo $filterDefaultMaxValue; ?>" name="priceFilterMaxValue" type="text" id="priceFilterMaxValue">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        <?php } ?>
        <?php
        if (isset($brandsArr) && count($brandsArr) > 1) {
            $brandsCheckedArr = (isset($brandsCheckedArr) && !empty($brandsCheckedArr)) ? $brandsCheckedArr : array();
            ?>
            <li class="dropdown">
                <button class="filter-trigger  dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo Labels::getLabel('LBL_Brand', $siteLangId); ?></button>
                <div class="filter-target dropdown-menu dropdown-menu-anim dropdown-menu-fit">
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
                                    <input name="brands" data-id="brand_<?php echo $brand['brand_id']; ?>" value="<?php echo $brand['brand_id']; ?>" data-title="<?php echo $brand['brand_name']; ?>" type="checkbox" <?php echo (in_array($brand['brand_id'], $brandsCheckedArr)) ? "checked" : ""; ?>> 
                                </label>
                            </li>
                        <?php } ?>
                    </ul>
                    <?php if (count($brandsArr) >= 10) { ?>
                        <div class="pt-3">
                            <a href="javascript:void(0)" onClick="brandFilters()" class="link"><?php echo Labels::getLabel('LBL_View_More', $siteLangId); ?> </a>
                        </div>
                    <?php } ?>
                </div>
            </li>
        <?php } ?>


        <!-- [ OPTION FILTER START -->
        <?php
        $optionIds = array();
        $optionValueCheckedArr = (isset($optionValueCheckedArr) && !empty($optionValueCheckedArr)) ? $optionValueCheckedArr : array();
        if (isset($options) && $options) {
            $optionName = '';
            $liData = '';
            $optionsArr = [];
            foreach ($options as $optionRow) {
                $optionsArr[$optionRow['option_id']][] = $optionRow;
            }
            foreach ($optionsArr as $optionRows) {
                if (empty($optionRows)) {
                    continue;
                }
                $optionName = $optionRows[0]['option_name'];
                ?>
                <li class="dropdown">
                    <button class="filter-trigger  dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <?php echo $optionName; ?></button>
                    <div class="filter-target dropdown-menu dropdown-menu-anim dropdown-menu-fit">
                        <ul class="list-vertical brandFilter-js">
                            <?php
                            foreach ($optionRows as $optionRow) {
                                $optionValName = ($optionRow['optionvalue_name']) ? $optionRow['optionvalue_name'] : $optionRow['optionvalue_identifier'];
                                $optionValueId = $optionRow['option_id'] . '_' . $optionRow['optionvalue_id'];
                                ?>
                                <li>
                                    <label class="checkbox optionvalue" id="optionvalue_<?php echo $optionRow['optionvalue_id']; ?>" data-groupname="<?php echo $optionName; ?>">
                                        <span class="lb-txt"><?php echo $optionValName; ?></span>
                                        <input name="optionvalues" data-id="option_<?php echo $optionValueId; ?>" value="<?php echo $optionValueId; ?>" data-title="<?php echo $optionName . ' - ' . $optionValName; ?>" type="checkbox" <?php echo (in_array($optionRow['optionvalue_id'], $optionValueCheckedArr)) ? "checked" : ""; ?>> 
                                    </label>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </li>
                <?php
            }
        }
        ?>
        <!-- OPTION FILTER ENDS ]--->
        <?php
        if (isset($conditionsArr) && count($conditionsArr) > 1) {
            $conditionsCheckedArr = (isset($conditionsCheckedArr) && !empty($conditionsCheckedArr)) ? $conditionsCheckedArr : array();
            ?>
            <li class="dropdown">
                <button class="filter-trigger  dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo Labels::getLabel('LBL_Condition', $siteLangId); ?></button>
                <div class="filter-target  dropdown-menu dropdown-menu-anim dropdown-menu-fit">
                    <ul class="list-vertical">
                        <?php
                        foreach ($conditionsArr as $condition) {
                            if (empty($condition) || $condition['selprod_condition'] == 0) {
                                continue;
                            }
                            ?>
                            <li>
                                <label class="checkbox condition" id="condition_<?php echo $condition['selprod_condition']; ?>">
                                    <span class="lb-txt">
                                        <?php echo Product::getConditionArr($siteLangId)[$condition['selprod_condition']]; ?>
                                    </span>
                                    <input value="<?php echo $condition['selprod_condition']; ?>" name="conditions" type="checkbox" <?php echo (in_array($condition['selprod_condition'], $conditionsCheckedArr)) ? "checked" : "";?>>
                                </label>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </li>
        <?php } ?>

        <?php if (isset($availabilityArr) && count($availabilityArr) > 1) {
            $availability = isset($availability) ? $availability : 0;
            ?>
            <li class="dropdown">
                <button class="filter-trigger  dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?php echo Labels::getLabel('LBL_Availability', $siteLangId); ?>
                </button>
                <div class="filter-target  dropdown-menu dropdown-menu-anim dropdown-menu-fit">
                    <ul class="listing--vertical listing--vertical-chcek">
                        <li>
                            <label class="checkbox condition" id="availability_1">
                                <span class="lb-txt">
                                    <?php echo Labels::getLabel('LBL_Exclude_out_of_stock', $siteLangId); ?>
                                </span>
                                <input value="1" name="out_of_stock" type="checkbox" <?php echo  ($availability == 1)  ? "checked" : ""; ?>>
                            </label>
                        </li>
                    </ul>
                </div>
            </li>
        <?php } ?>
        
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
                    <li class="dropdown">
                        <button class="filter-trigger  dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?php echo $attr['attr_name']; ?>
                        </button>
                        <div class="filter-target  dropdown-menu dropdown-menu-anim dropdown-menu-fit">
                            <ul class="list-vertical">
                                <?php
                                $attrOpts = array_filter($attrOpts);
                                foreach ($attrOpts as $key => $attrOpt) {
                                    $attrId = $attrFldName . '_' . $key;
                                    ?>
                                    <li>
                                        <label class="checkbox condition" id="<?php echo $attrId; ?>" data-groupname="<?php echo $attr['attr_name']; ?>">
                                            <span class="lb-txt"><?php echo $attr['attr_prefix'] . $attrOpt . $attr['attr_postfix']; ?></span>
                                            <input name="attributes" value="1" type="checkbox" <?php echo (in_array($attrId, $attrCheckedArr)) ? "checked" : ""; ?>>
                                        </label>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </li>
                    <?php
                }
            }
        }
        ?>
        <!-- BOC for Custom Fields/Attributes Filters End -->
        
    </ul>
</div>

<?php /* <div class="selected-filters" id="filters" style="display:none;">
    <div class="chip filtered__item">
        <?php echo Labels::getLabel('LBL_Clear_All', $siteLangId); ?>
        <a href="javascript:void(0)" class="remove resetAll" id="resetAll" onClick="resetListingFilter()">
            <i class="fas fa-times"></i>
        </a>
    </div>
</div> */ ?>
<script language="javascript">
    var priceInFilter = '<?php echo $priceInFilter; ?>';
    var catCodeArr = <?php echo json_encode($catCodeArr); ?>;
    $.each(catCodeArr, function (key, value) {
        if ($("ul li a[data-id='" + value + "']").parent().find('span')) {
            $("ul li a[data-id='" + value + "']").parent().find('span:first').addClass('is--active');
            $("ul li a[data-id='" + value + "']").parent().find('ul:first').css('display', 'block');
        }

    });

    $("document").ready(function () {
        var min = 0;
        var max = 0;
<?php
if (isset($priceArr) && $priceArr) {
    ?>
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

            rangeSlider.noUiSlider.on('change', function (values, handle) {
                priceInFilter = 1;
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

            var updateRange = function () {
                priceInFilter = 1;
                rangeSlider.noUiSlider.set([from, to]);
                updateValues();
                addPricefilter();
            };

            $from.on("change", function () {
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

            $to.on("change", function () {
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

            var updateValues = function () {
                $from.prop("value", from);
                $to.prop("value", to);
            };
<?php }
?>

        /* left side filters expand-collapse functionality [ */
        $('.span--expand').bind('click', function () {
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

    $("#accordian li span.acc-trigger").click(function () {
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
    $('.dropdown-menu').on('click', function (e) {
        e.stopPropagation();
    });
</script>
<style>
    .filter-horizontal>ul>li {margin-bottom : 1rem;}
</style>