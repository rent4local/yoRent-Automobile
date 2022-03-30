<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
$isUrlCompare = isset($isUrlCompare) ? $isUrlCompare : 0;

$moreseller = [];
if (!empty($moreSellersProd)) {
    foreach ($moreSellersProd as $shopKey => $moreSellerProd) {
        foreach ($prodArr as $selProd => $prod) {
            if (!empty($productsDetail[$selProd])) {
                if (!empty($moreSellerProd[$productsDetail[$selProd]['selprod_code']])) {
                    $selProdId = $moreSellerProd[$productsDetail[$selProd]['selprod_code']]['selprod_id'];
                    $price = CommonHelper::displayMoneyFormat($moreSellerProd[$productsDetail[$selProd]['selprod_code']]['theprice']);
                    $sprodata_rental_available_from = $moreSellerProd[$productsDetail[$selProd]['selprod_code']]['sprodata_rental_available_from'];
                    $sprodata_rental_active = $moreSellerProd[$productsDetail[$selProd]['selprod_code']]['sprodata_rental_active'];
                    $selprod_available_from = $moreSellerProd[$productsDetail[$selProd]['selprod_code']]['selprod_available_from'];
                    $selprod_active = $moreSellerProd[$productsDetail[$selProd]['selprod_code']]['selprod_active'];

                    $moreseller[$prod][$selProdId]['shop_id'] = $shopKey;
                    $moreseller[$prod][$selProdId]['price'] = $price;
                    $moreseller[$prod][$selProdId]['sprodata_rental_available_from'] = $sprodata_rental_available_from;
                    $moreseller[$prod][$selProdId]['sprodata_rental_active'] = $sprodata_rental_active;
                    $moreseller[$prod][$selProdId]['selprod_available_from'] = $selprod_available_from;
                    $moreseller[$prod][$selProdId]['selprod_active'] = $selprod_active;
                }
            }
        }
    }
}

$attributesArray = [];
if (!empty($attrGrpArr)) {
    foreach ($attrGrpArr as $grpKey => $attrGrp) {
        foreach ($attrGrp['attributes'] as $attribute) {
            foreach ($prodArr as $selProd => $prod) {
                $attributesArray[$selProd][$attrGrp['attr_grp_name']] = '';
                if ($attribute['attr_type'] == AttrGroupAttribute::ATTRTYPE_SELECT_BOX) {
                    $attrOpt = explode("\n", $attribute['attr_options']);

                    $optArr = [];
                    if (isset($infoAttrArr[$prod][$attribute['attr_attrgrp_id']][$attribute['attr_fld_name']])) {
                        $optionKey = $infoAttrArr[$prod][$attribute['attr_attrgrp_id']][$attribute['attr_fld_name']];
                        $optArr = explode(",", $optionKey);
                    }

                    $checkboxVals = [];
                    if (!empty($optArr)) {
                        $attrCount = count($optArr);

                        foreach ($optArr as $key => $val) {
                            $val = ($val == '') ? 0 : $val;
                            $checkboxVals[] = $attrOpt[$val];
                        }
                    } else {
                        $checkboxVals = Labels::getLabel('LBL_N/A', $siteLangId);
                    }
                    $attrVal = $checkboxVals;
                } else if ($attribute['attr_type'] == AttrGroupAttribute::ATTRTYPE_NUMBER) {
                    if (!empty($infoAttrArr[$prod][$attribute['attr_attrgrp_id']][$attribute['attr_fld_name']])) {
                        $attrVal = ($attribute['attr_prefix'] != '') ? $attribute['attr_prefix'] : '';
                        $attrVal .= intval($infoAttrArr[$prod][$attribute['attr_attrgrp_id']][$attribute['attr_fld_name']]);
                        $attrVal .= ($attribute['attr_postfix'] != '') ? $attribute['attr_postfix'] : '';
                    } else {
                        $attrVal = Labels::getLabel('LBL_N/A', $siteLangId);
                    }
                } else {
                    if (!empty($infoAttrArr[$prod][$attribute['attr_attrgrp_id']][$attribute['attr_fld_name']])) {
                        $attrVal = ($attribute['attr_prefix'] != '') ? $attribute['attr_prefix'] : '';
                        $attrVal .= trim($infoAttrArr[$prod][$attribute['attr_attrgrp_id']][$attribute['attr_fld_name']]);
                        $attrVal .= ($attribute['attr_postfix'] != '') ? $attribute['attr_postfix'] : '';
                    } else {
                        $attrVal = Labels::getLabel('LBL_N/A', $siteLangId);
                    }
                }
                $attributesArray[$selProd][] = $attrVal;
            }
        }
    }
}

$specfArr = [];
foreach ($specificationArr as $specKey => $specValue) {
    foreach ($prodArr as $selProd => $prod) {
        if (!empty($specValue[$prod])) {
            $spec = $specValue[$prod];
            $specficationArr = [];
            foreach ($specValue[$prod] as $skey => $sval) {
                $specficationArr[] = $sval['prodspec_name'] . " - " . $sval['prodspec_value'];
            }
            $spec = $specficationArr;
        } else {
            $spec = Labels::getLabel('LBL_N/A', $siteLangId);
        }
        $specfArr[$selProd][] = $spec;
    }
}

$productForRent = max(array_column($productsDetail, 'sprodata_is_for_rent'));

$rentalArrFlds = array(
    'rent_price' => Labels::getLabel('LBL_Rental_Price', $siteLangId),
    'sprodata_rental_security' => Labels::getLabel('LBL_Rental_security', $siteLangId),
    'selprodRentalTerms' => Labels::getLabel('LBL_Rental_Terms_&_Conditions', $siteLangId)
);
?>

<div class="body" id="body">
    <section class="">
        <div class="container">
            <div class="cd-products-comparison-table">
                <div class="cd-products-comparison-table-head">
                    <?php $lbl = Labels::getLabel('LBL_{productTitle}_vs_Others', $siteLangId); ?>
                    <h2 class="title">
                        <?php echo CommonHelper::replaceStringData($lbl, ['{productTitle}' => ($productsDetail[array_keys($prodArr)[0]]['selprod_title'])]); ?>
                    </h2>
                    <?php if (isset($shareUrl)) { ?>
                        <div class="clipboard">
                            <input class="clipboard_url" type="text" value="<?php echo $shareUrl; ?>" id="shareInput">
                            <a class="clipboard_btn" onclick="copyContent()" href="javascript:void(0)"><i class="far fa-copy"></i></a>
                        </div>
                    <?php } ?>
                </div>
                <div class="cd-products-table">
                    <div class="features">
                        <div class="top-info">
                            <p class="vsText" style="display:none;">
                                <?php
                                echo CommonHelper::replaceStringData($lbl, ['{productTitle}' => ($productsDetail[array_keys($prodArr)[0]]['selprod_title'])]);
                                ;
                                ?>
                            </p>
                        </div>
                        <ul class="cd-features-list heading-list--js">
                            <li><?php echo Labels::getLabel('LBL_Variants', $siteLangId); ?></li>
                            <li><?php echo Labels::getLabel('LBL_Brand', $siteLangId); ?></li>
                            <?php if (!empty($rentalArrFlds)) { ?>
                                <?php foreach ($rentalArrFlds as $rentKey => $rentalArrFld) { ?>
                                    <li><?php echo $rentalArrFld; ?></li>
                                    <?php
                                }
                            }
                            ?>
                            <?php if (FatApp::getConfig("CONF_ALLOW_SALE", FatUtility::VAR_INT, 0)) { ?>
                                <li><?php echo Labels::getLabel('LBL_Price', $siteLangId); ?></li>
                            <?php } ?>

                            <?php if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) { ?>
                                <li><?php echo Labels::getLabel('LBL_Customer_Rating', $siteLangId); ?></li>
                            <?php } ?>
                            <li><?php echo Labels::getLabel('LBL_Seller', $siteLangId); ?></li>
                            <li><?php echo Labels::getLabel('LBL_Seller_Info', $siteLangId); ?></li>

                            <?php if (!empty($specificationArr)) { ?>
                                <li class="compSectionHead">
                                    <?php echo Labels::getLabel('LBL_Products_Specifications', $siteLangId); ?></li>
                                <?php foreach ($specificationArr as $specKey => $specValue) { ?>
                                    <li><?php
                                        if (!empty($specKey)) {
                                            echo $specKey;
                                        } else {
                                            echo "others";
                                        }
                                        ?></li>
                                    <?php
                                }
                            }
                            
                            if (!empty($attrGrpArr)) {
                                foreach ($attrGrpArr as $grpKey => $attrGrp) {
                                    ?>
                                    <li class="compSectionHead"><?php echo $attrGrp['attr_grp_name']; ?></li>
                                    <?php foreach ($attrGrp['attributes'] as $attribute) { ?>
                                        <li><?php echo ($attribute['attr_name'] != '') ? $attribute['attr_name'] : $attribute['attr_identifier']; ?>
                                        </li>
                                        <?php
                                    }
                                }
                            }
                            
                            if (!empty($moreseller)) { ?>
                                <li><?php echo Labels::getLabel('LBL_More_seller', $siteLangId); ?></li>
                            <?php }
                            if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0) && !empty($prodReviewArr)) { ?>
                                <li> <?php echo Labels::getLabel('LBL_Top_Reviews', $siteLangId); ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                    <!-- .features -->
                    <div class="cd-products-wrapper scroll scroll-y">
                        <ul class="cd-products-columns level-1--js">
                            <?php foreach ($prodArr as $selProd => $prod) { ?>
                                <li class="product level-2--js">
                                    <div class="top-info">
                                        <div class="close-layer close-layer-lg" onclick="removeFromCompareList('<?php echo $selProd; ?>', 1,<?php echo $isUrlCompare; ?>)">
                                        </div>

                                        <div class="prod" href="<?php echo UrlHelper::generateUrl('Products', 'View', array($selProd)); ?>" tabindex="0">
                                            <img class="prod-img" src="<?php echo UrlHelper::generateUrl('image', 'product', array($prod, "CLAYOUT3", $selProd, 0, $siteLangId)); ?>">
                                        </div>
                                        <h3>
                                            <a href="<?php echo UrlHelper::generateUrl('Products', 'View', array($productsDetail[$selProd]['selprod_id'])); ?>" tabindex="0"><?php echo substr($productsDetail[$selProd]['selprod_title'], 0, 50) . '...'; ?></a>
                                        </h3>

                                        <div class="action <?php echo ($cartType == applicationConstants::PRODUCT_FOR_SALE) ? "disabled" : ""; ?>">
                                            <?php if ($productsDetail[$selProd]['sprodata_rental_active'] == applicationConstants::ACTIVE) { ?>
                                                <a href="javascript:void(0)" class="btn btn-brand" onclick="quickDetail('<?php echo $selProd; ?>')"><?php echo Labels::getLabel('LBL_Rent_Now', $siteLangId); ?></a>
                                                <?php
                                            } else {
                                                echo '<span class="note"><small>' . Labels::getLabel('LBL_Not_Available_For_Rent', $siteLangId) . '</small></span>';
                                            }
                                            ?>

                                            <i class="icn sale-rent-only" data-toggle="tooltip" data-placement="top" style="display: <?php echo ($cartType == applicationConstants::PRODUCT_FOR_SALE) ? "block" : "none"; ?>;" title="<?php echo Labels::getLabel('LBL_You_have_sale_item(s)_in_your_cart_you_cannot_add_items_for_Rent', $siteLangId); ?>">
                                                <svg class="svg" style="height:20px; width: 20px;">
                                                <use
                                                    xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/fashion/retina/sprite-front.svg#info">
                                                </use>
                                                </svg>
                                            </i>
                                        </div>
                                    </div>
                                    <!-- .top-info -->

                                    <ul class="cd-features-list">
                                        <li>
                                            <?php
                                            if (!empty($prodOptionsArr[$prod])) {
                                                foreach ($prodOptionsArr[$prod] as $key => $option) {
                                                    $selectedOptionValue = $option['values'][$selectedOptionsArr[$selProd][$key]]['optionvalue_name'];
                                                    $selectedOptionColor = $option['values'][$selectedOptionsArr[$selProd][$key]]['optionvalue_color_code'];

                                                    if ($option['values']) {
                                                        ?>
                                                        <h6><?php echo $option['option_name'] . "(" . count($option['values']) . ")"; ?>
                                                        </h6>
                                                        <ul class="options">
                                                            <?php
                                                            foreach ($option['values'] as $opVal) {
                                                                $sellerProductID = 0;
                                                                $isAvailable = true;
                                                                if (in_array($opVal['optionvalue_id'], $selectedOptionsArr[$selProd])) {
                                                                    $optionUrl = CommonHelper::generateUrl('Products', 'view', array($selProd));
                                                                } else {
                                                                    $sellerProductID = Product::generateProductOptionsUrl($selProd, $selectedOptionsArr[$selProd], $option['option_id'], $opVal['optionvalue_id'], $prod, true);

                                                                    $optionUrl = Product::generateProductOptionsUrl($selProd, $selectedOptionsArr[$selProd], $option['option_id'], $opVal['optionvalue_id'], $prod);
                                                                    $optionUrlArr = explode("::", $optionUrl);
                                                                    if (is_array($optionUrlArr) && count($optionUrlArr) == 2) {
                                                                        $optionUrl = $optionUrlArr[0];
                                                                        $isAvailable = false;
                                                                    }
                                                                }
                                                                ?>

                                                                <li <?php if (!in_array($opVal['optionvalue_id'], $selectedOptionsArr[$selProd])) { ?> onclick="changeOption('<?php echo $sellerProductID; ?>', '<?php echo $selProd; ?>')" <?php } ?> class="<?php
                                                                    echo (in_array($opVal['optionvalue_id'], $selectedOptionsArr[$selProd])) ? 'selected' : ' ';
                                                                    echo (!$optionUrl) ? ' is-disabled' : '';
                                                                    echo (!$isAvailable) ? 'not--available' : '';
                                                                    ?>">
                                                                        <?php if ($option['option_is_color'] && $opVal['optionvalue_color_code'] != '') { ?>
                                                                        <a optionValueId="<?php echo $opVal['optionvalue_id']; ?>"
                                                                           selectedOptionValues="<?php echo implode("_", $selectedOptionsArr[$selProd]); ?>"
                                                                           title="<?php
                                                                           echo $opVal['optionvalue_name'];
                                                                           echo (!$isAvailable) ? ' ' . Labels::getLabel('LBL_Not_Available', $siteLangId) : '';
                                                                           ?>"
                                                                           class="<?php
                                                                           echo (!$option['option_is_color']) ? 'selector__link' : '';
                                                                           echo (in_array($opVal['optionvalue_id'], $selectedOptionsArr[$selProd])) ? ' ' : ' ';
                                                                           echo (!$optionUrl) ? ' is-disabled' : '';
                                                                           ?> "
                                                                           href="javascript:void(0)"> <span class="colors"
                                                                                                         style="background-color:#<?php echo $opVal['optionvalue_color_code']; ?>;"></span><?php echo $opVal['optionvalue_name']; ?></a>
                                                                        <?php } else { ?>
                                                                        <a optionValueId="<?php echo $opVal['optionvalue_id']; ?>"
                                                                           selectedOptionValues="<?php echo implode("_", $selectedOptionsArr[$selProd]); ?>"
                                                                           title="<?php
                                                                           echo $opVal['optionvalue_name'];

                                                                           echo (!$isAvailable) ? ' ' . Labels::getLabel('LBL_Not_Available', $siteLangId) : '';
                                                                           ?> "
                                                                           class="<?php
                                                                           echo (in_array($opVal['optionvalue_id'], $selectedOptionsArr[$selProd])) ? '' : ' ';
                                                                           echo (!$optionUrl) ? ' is-disabled' : ''
                                                                           ?>"
                                                                           href="javascript:void(0);">
                                                                            <?php echo $opVal['optionvalue_name']; ?> </a>
                                                                    <?php } ?>
                                                                </li>

                                                            <?php } ?>
                                                        </ul>
                                                    <?php } ?>
                                                    <?php
                                                }
                                            } else {
                                                echo Labels::getLabel('LBL_N/A', $siteLangId);
                                            }
                                            ?>
                                        </li>

                                        <li>
                                            <a href="<?php echo CommonHelper::generateUrl('brands', 'View', array($productsDetail[$selProd]['brand_id'])); ?>" tabindex="0"><?php echo $productsDetail[$selProd]['brand_name']; ?>
                                            </a>
                                        </li>
                                        <?php
                                        if (!empty($rentalArrFlds)) {
                                            foreach ($rentalArrFlds as $rentKey => $rentalArrFld) {
                                                echo '<li>';
                                                if ($productsDetail[$selProd]['sprodata_is_for_rent'] != 1) {
                                                    echo Labels::getLabel('LBL_N/A', $siteLangId);
                                                    echo '</li>';
                                                    continue;
                                                }

                                                switch ($rentKey) {
                                                    case 'rent_price':
                                                        echo CommonHelper::displayMoneyFormat($productsDetail[$selProd][$rentKey]);
                                                        if (!empty($rentalTypeArr)) {
                                                            if (!empty($productsDetail[$selProd]['sprodata_duration_type'])) {
                                                                echo ' / ' . $rentalTypeArr[$productsDetail[$selProd]['sprodata_duration_type']];
                                                            }
                                                        }
                                                        break;
                                                    case 'selprodRentalTerms':
                                                        if ($productsDetail[$selProd][$rentKey] != '') {
                                                            if (strlen($productsDetail[$selProd][$rentKey]) > 100) {
                                                                echo substr($productsDetail[$selProd][$rentKey], 0, 100);
                                                                echo '... ' . '<a href="javascript:void(0)" onclick="moreDetail(' . $selProd . ')">' . Labels::getLabel('LBL_More', $siteLangId) . '</a>';
                                                                ?>
                                                                <div style="display: none;" id="term-and-condition-<?php echo $selProd; ?>">
                                                                    <h2><?php echo $productsDetail[$selProd]["product_name"]; ?></h2>
                                                                    <?php echo $productsDetail[$selProd][$rentKey]; ?>
                                                                </div>
                                                                <?php
                                                            } else {
                                                                echo $productsDetail[$selProd][$rentKey];
                                                            }
                                                        } else {
                                                            echo Labels::getLabel('LBL_N/A', $siteLangId);
                                                        }

                                                        break;
                                                    case 'sprodata_rental_security':
                                                        echo CommonHelper::displayMoneyFormat($productsDetail[$selProd][$rentKey]);
                                                        echo ' / ' . Labels::getLabel('LBL_Per_product', $siteLangId);
                                                        break;
                                                    default:
                                                        echo $productsDetail[$selProd][$rentKey];
                                                        break;
                                                }

                                                echo '</li>';
                                            }
                                        }
                                        ?>
                                        <?php if (FatApp::getConfig("CONF_ALLOW_SALE", FatUtility::VAR_INT, 0)) { ?>
                                            <li><?php
                                                if ($productsDetail[$selProd]['selprod_active']) {

                                                    echo CommonHelper::displayMoneyFormat($productsDetail[$selProd]['theprice']);
                                                } else {

                                                    echo Labels::getLabel('LBL_N/A', $siteLangId);
                                                }
                                                ?></li>
                                        <?php } ?>

                                        <?php if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) { ?>
                                            <li>
                                                <div class="product-rating">
                                                    <ul>
                                                        <?php
                                                        for ($ii = 0; $ii < 5; $ii++) {
                                                            $liClass = "";
                                                            if ($ii < $productsDetail[$selProd]['prod_rating']) {
                                                                $liClass = "active";
                                                            }
                                                            ?>
                                                            <li class="<?php echo $liClass; ?>"></li>
                                                        <?php } ?>
                                                    </ul>
                                                    <p>(
                                                        <?php
                                                        echo round($productsDetail[$selProd]['prod_rating'], 1) . ' ';
                                                        echo ($productsDetail[$selProd]['prod_rating'] > 1) ? Labels::getLabel('LBL_Ratings', $siteLangId) : Labels::getLabel('LBL_Rating', $siteLangId);

                                                        echo ' '. $productsDetail[$selProd]['totalReview'] . ' ';
                                                        echo ($productsDetail[$selProd]['totalReview'] > 1) ? Labels::getLabel('LBL_Reviews', $siteLangId) : Labels::getLabel('LBL_Review', $siteLangId);
                                                        ?>
                                                        )
                                                    </p>
                                                </div>
                                            </li>
                                        <?php } ?>
                                        <li><?php echo $productsDetail[$selProd]['shop_name']; ?></li>
                                        <li>
                                            <?php
                                            echo $productsDetail[$selProd]['user_name'];
                                            if (!empty($productsDetail[$selProd]['user_phone'])) {
                                                echo "</br>" . $productsDetail[$selProd]['user_dial_code'] . ' ' . $productsDetail[$selProd]['user_phone'];
                                            }
                                            if (!empty($productsDetail[$selProd]['user_city'])) {
                                                echo "</br>" . $productsDetail[$selProd]['user_city'];
                                            }
                                            ?>
                                        </li>
                                        <?php
                                        if (!empty($specfArr)) {
                                            echo "<li class='compSectionHead'></li>";
                                            foreach ($specfArr[$selProd] as $value) {
                                                echo "<li>";
                                                if (is_array($value)) {
                                                    foreach ($value as $val) {
                                                        echo $val . "<br>";
                                                    }
                                                } else {
                                                    echo $value;
                                                }
                                                echo "</li>";
                                            }
                                        }
                                        if (!empty($attributesArray)) {
                                            foreach ($attributesArray[$selProd] as $key => $value) {
                                                if (is_array($value)) {
                                                    ?>
                                                    <li> 
                                                        <?php
                                                        foreach ($value as $val) {
                                                            echo $val . '<br>';
                                                        }
                                                        ?>
                                                    </li>
                                                <?php } else { ?>
                                                    <li class="<?php echo (is_int($key)) ? '' : 'compSectionHead'; ?>">
                                                        <?php echo $value; ?> 
                                                    </li>
                                                    <?php
                                                }
                                            }
                                        }
                                        if (!empty($moreseller)) {
                                            echo "<li>";
                                            if (isset($moreseller[$prod])) {
                                                foreach ($moreseller[$prod] as $key => $value) {
                                                    ?>
                                                    <div class='moreseller'>
                                                        <a class="moreseller_img" href="<?php echo CommonHelper::generateUrl('shops', 'view', array($value['shop_id'])); ?>">
                                                            <img src="<?php echo CommonHelper::generateUrl('image', 'shopLogo', array($value['shop_id'], $siteLangId, 'SMALL')); ?>">
                                                        </a>
                                                        <div class="moreseller_detail">
                                                            <div class="moreseller_price"><?php echo $value['price']; ?></div>
                                                            <div class="moreseller_action">
                                                                <?php if (strtotime($value['sprodata_rental_available_from']) <= strtotime(date('Y-m-d h:i:s')) && $value['sprodata_rental_active'] == applicationConstants::ACTIVE) { ?>
                                                                    <a href="javascript:void(0)" class="link" onclick="quickDetail('<?php echo $key; ?>')"><?php echo Labels::getLabel('LBL_Rent_Now', $siteLangId); ?></a>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
                                            } else {
                                                echo Labels::getLabel('LBL_N/A', $siteLangId);
                                            }
                                            echo "</li>";
                                        }
                                        ?>

                                        <?php if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0) && !empty($prodReviewArr)) { ?>
                                            <li>
                                                <?php
                                                $catalogReviewKey = $productsDetail[$selProd]['selprod_user_id'].'_'. $prod;
                                                
                                                if (!empty($prodReviewArr[$catalogReviewKey])) {
                                                    $cnt = 1;
                                                    foreach ($prodReviewArr[$catalogReviewKey] as $reviewKey => $review) {
                                                        ?>
                                                        <div class="compare-reviews">
                                                            <h6 class="title"><?php echo $review['spreview_title']; ?></h6>
                                                            <div class="product-rating">
                                                                <ul>
                                                                    <?php
                                                                    for ($ii = 0; $ii < 5; $ii++) {
                                                                        $liClass = "";
                                                                        if ($ii < round($review["prod_rating"])) {
                                                                            $liClass = "active";
                                                                        }
                                                                        ?>
                                                                        <li class="<?php echo $liClass; ?>">

                                                                        </li>
                                                                    <?php } ?>
                                                                </ul>
                                                                <p>
                                                                    <?php echo Labels::getLabel('LBL_By', $siteLangId); ?>
                                                                    <?php echo CommonHelper::displayName($review['user_name']); ?>
                                                                    <?php echo Labels::getLabel('LBL_On', $siteLangId); ?>
                                                                    <?php echo FatDate::format($review['spreview_posted_on']); ?>
                                                                </p>
                                                            </div>

                                                            <p class="description">
                                                                <?php echo CommonHelper::truncateCharacters($review['spreview_description'], 200, '', '', true); ?>
                                                            </p>

                                                        </div>
                                                        <?php
                                                        if ($cnt == 2) {
                                                            break;
                                                        }
                                                        $cnt++;
                                                    }
                                                    ?>
                                                </li>
                                                <?php
                                            } else {
                                                echo Labels::getLabel('LBL_N/A', $siteLangId);
                                            }
                                        }
                                        ?>
                                    </ul>
                                </li>
                                <?php
                            }
                            if (count($prodArr) < CompareProduct::COMPARE_PRODUCTS_LIMIT) {
                                ?>
                                <li class="product level-2--js">
                                    <div class="top-info add-compare-field">
                                        <input class="form-control search-magni" placeholder="<?php echo Labels::getLabel('LBL_Add_new_product', $siteLangId); ?>" type="text" name="search_product" id="search_product_js" />
                                    </div>
                                </li>
                            <?php } ?>
                            <!-- .product -->
                        </ul>
                        <!-- .cd-products-columns -->
                    </div>
                    <!-- .cd-products-wrapper -->
                </div>
                <!-- .cd-products-table -->
                <!-- </div>
            </div> -->
            </div>
        </div>
    </section>
</div>
<?php
$attrGrpId = isset($attrGrpId) ? $attrGrpId : 0;
?>
<style>
    .disabled a {
        pointer-events: none;
    }
</style>

<script>
    var isUrlCompare = <?php echo $isUrlCompare; ?>;
    $('input[name=\'search_product\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
        'source': function (request, response) {
            $.ajax({
                url: fcom.makeUrl('CompareProducts', 'autoComplete'),
                data: {
                    keyword: request['term'],
                    attrgrpid: <?php echo $attrGrpId; ?>,
                    fIsAjax: 1
                },
                dataType: 'json',
                type: 'post',
                success: function (json) {
                    response($.map(json, function (item) {
                        return {
                            label: item['name'],
                            value: item['name'],
                            id: item['id']
                        };
                    }));
                },
            });
        },
        select: function (event, ui) {
            addToCompareList(ui.item.id, 1, isUrlCompare);
        }
    });

    $(document).ready(function () {
        var heightArr = [];
        $(".level-1--js .level-2--js").each(function (parentIndex, element) {
            $(element).find('.cd-features-list>li').each(function (childIndex, childElement) {
                var height = $(childElement).outerHeight();
                if (heightArr[childIndex] != undefined) {
                    if (heightArr[childIndex] < height) {
                        heightArr[childIndex] = height;
                    }
                } else {
                    heightArr.push(height);
                }
            })
        });
        $(".level-1--js .level-2--js").each(function (parentIndex, element) {
            $(element).find('.cd-features-list>li').each(function (childIndex, childElement) {
                var minHeight = heightArr[childIndex];
                $(childElement).css('min-height', minHeight + 'px');
            });
        });
        $('.heading-list--js li').each(function (childIndex, childElement) {
            var minHeight = heightArr[childIndex];
            $(childElement).css('min-height', minHeight + 'px');
        });

        $("#target").click(function () {
            alert("Handler for .click() called.");
        });

    });

    function copyContent() {
        var copyText = $('#shareInput').val();
        document.addEventListener('copy', function (e) {
            e.clipboardData.setData('text/plain', copyText);
            e.preventDefault();
        }, true);
        document.execCommand('copy');
        alert('Copied Url: ' + copyText);
    }
</script>