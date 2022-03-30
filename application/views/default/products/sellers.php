<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$displayProductNotAvailableLable = false;
if (trim(FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, '')) != '') {
    $displayProductNotAvailableLable = true;
}

$selectedFullfillmentType = (isset($_COOKIE['locationCheckoutType'])) ? FatUtility::int($_COOKIE['locationCheckoutType']) : Shipping::FULFILMENT_SHIP;
?>
<div id="body" class="body">
    <div class="bg-brand pt-3 pb-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="prod-info">
                        <div class="prod-info__left">
                            <div class="product-avtar">
                                <a title="<?php echo $product['selprod_title']; ?>" href="<?php echo UrlHelper::generateUrl('products', 'view', array($product['selprod_id'])); ?>">
                                    <img alt="<?php echo $product['selprod_title']; ?>" src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], "SMALL", $product['selprod_id'], 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg'); ?>">
                                </a>
                            </div>
                        </div>
                        <div class="prod-info__right">
                            <div class="avtar__info">
                                <h5>
                                    <a title="<?php echo $product['selprod_title']; ?>" href="<?php echo UrlHelper::generateUrl('products', 'view', array($product['selprod_id'])); ?>"><?php echo $product['selprod_title']; ?></a>
                                </h5>
                                <?php if (round($product['prod_rating']) > 0 && FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) { ?> 
                                    <div class="products__rating">
                                        <i class="icn">
                                            <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"
                                                 href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow">
                                            </use>
                                            </svg> 
                                        </i>
                                        <span class="rate"><?php echo round($product['prod_rating'], 1); ?></span> 
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4"></div>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><?php echo Labels::getLabel('LBL_Compare_price_with_other_Sellers', $siteLangId); ?></h5>
                </div>
                <div class="card-body">
                    <div class="scroll scroll-x js-scrollable table-wrap"> 
                        <?php
                        $arr_flds = array(
                            'shop_name' => Labels::getLabel('LBL_Seller', $siteLangId),
                            'rent_price' => Labels::getLabel('LBL_Rental_Price', $siteLangId),
                            'selprod_price' => Labels::getLabel('LBL_Selling_Price', $siteLangId),
                            'COD' => Labels::getLabel('LBL_COD_AVAILABLE', $siteLangId),
                            'Action' => '',
                        );
                        $tbl = new HtmlElement('table', array('class' => 'table table-justified'));
                        $th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
                        foreach ($arr_flds as $val) {
                            $e = $th->appendElement('th', array(), $val);
                        }

                        $sr_no = 0;
                        //echo "<pre>"; print_r($product['moreSellersArr']); echo "</pre>"; exit;
                        
                        foreach ($product['moreSellersArr'] as $sn => $moresellers) {
                            $sr_no++;
                            $tr = $tbl->appendElement('tr', array('class' => ''));
                            foreach ($arr_flds as $key => $val) {
                                $td = $tr->appendElement('td');
                                switch ($key) {
                                    case 'shop_name':
                                        $labelTxt = ($selectedFullfillmentType == Shipping::FULFILMENT_PICKUP) ? Labels::getLabel('LBL_Check_Pickup_Locations', $siteLangId) : Labels::getLabel('LBL_Check_Shipping_Locations', $siteLangId);
                                        $locationAction = '<div class="item__brand mt-2"><a href="javascript:void(0);" class="link" onClick="getFullfillmentData('. $selectedFullfillmentType .', '. applicationConstants::PRODUCT_FOR_RENT .', '. $product['selprod_id'] .')">'. $labelTxt .'</a></div>';
                                    
                                    
                                        $txt = '<div class="item">
                                <figure class="item__pic item__pic-seller">
                                    <a title="' . $moresellers[$key] . '" href="' . UrlHelper::generateUrl('shops', 'view', array($moresellers['shop_id'])) . '">
                                        <img data-ratio="1:1 (150x150)" src="' . UrlHelper::generateUrl('image', 'shopLogo', array($moresellers['shop_id'], $siteLangId, 'SMALL')) . '" alt="' . $moresellers['shop_name'] . '">
                                    </a>
                                </figure>
                                <div class="item__description">
                                    <div class="item__title">
                                        <a title="' . $moresellers[$key] . '" href="' . UrlHelper::generateUrl('shops', 'view', array($moresellers['shop_id'])) . '">
                                            ' . $moresellers[$key] . '
                                        </a>
                                    </div>
                                    <div class="item__brand">
                                        <a href="' . UrlHelper::generateUrl('shops', 'view', array($moresellers['shop_id'])) . '">
                                            ' . $moresellers['shop_state_name'] . "," . $moresellers['shop_country_name'] . '
                                        </a>
                                    </div>';
                                        if (isset($product['rating'][$moresellers['selprod_user_id']]) && $product['rating'][$moresellers['selprod_user_id']] > 0) {
                                            $txt .= '<div class="products__rating">
                                                    <i class="icn">
                                                        <svg class="svg">
                                                            <use xlink:href="' . CONF_WEBROOT_URL . 'images/retina/sprite.svg#star-yellow" href="' . CONF_WEBROOT_URL . 'images/retina/sprite.svg#star-yellow">
                                                            </use>
                                                        </svg>
                                                    </i>
                                                    <span class="rate">
                                                        ' . round($product['rating'][$moresellers['selprod_user_id']], 1) . '
                                                    </span>
                                                </div>';
                                        }
                                        $txt .= $locationAction. '</div></div>';
                                        $td->appendElement('plaintext', array(), $txt, true);
                                        break;

                                    case 'selprod_price':
                                        if ($moresellers['is_sell']) {
                                            $txt = ' <div class=""><div class="item__price">' . CommonHelper::displayMoneyFormat($moresellers['selprod_price']). '</div></div>';
                                        } else {
                                            $txt = Labels::getLabel('LBL_N/A', $siteLangId);
                                        }
                                        $td->appendElement('plaintext', array(), $txt, true);
                                        break;
                                    case 'rent_price':
                                        if ($moresellers['is_rent']) {
                                            $txt = ' <div class=""><div class="item__price">' . CommonHelper::displayMoneyFormat($moresellers['rent_price']);
                                            if ($moresellers['sprodata_rental_price'] != $moresellers['rent_price']) {
                                                $txt .= '  <span class="item__price_old"><strike>' . CommonHelper::displayMoneyFormat($moresellers['sprodata_rental_price']) . '</strike></span>';
                                            }
                                            $txt .= '</div></div>';
                                        } else {
                                            $txt = Labels::getLabel('LBL_N/A', $siteLangId);
                                        }
                                        $td->appendElement('plaintext', array(), $txt, true);
                                        break;
                                    case 'COD':
                                        $codAvailableTxt = Labels::getLabel('LBL_N/A', $siteLangId);
                                        if (!empty($product['cod'][$moresellers['selprod_user_id']]) && $product['cod'][$moresellers['selprod_user_id']]) {
                                            $codAvailableTxt = Labels::getLabel('LBL_Cash_on_delivery_available', $siteLangId);
                                        }
                                        $td->appendElement('plaintext', array(), $codAvailableTxt, true);
                                        break;
                                    case 'Action':
                                        $txt = '';
                                        $txt .= '<a href="' . UrlHelper::generateUrl('products', 'view', array($moresellers['selprod_id'])) . '" class=" btn btn-brand btn-sm">  ' . Labels::getLabel('LBL_View_Details', $siteLangId) . '</a>';
                                        
                                        if (true == $displayProductNotAvailableLable && array_key_exists('availableInLocation', $product) && 0 == $product['availableInLocation']) {
                                            /* $txt .= '<span class="text-danger">'.Labels::getLabel('LBL_NOT_AVAILABLE', $siteLangId).'</span>'; */
                                        } else {
                                            if ($moresellers['sprodata_rental_active'] == applicationConstants::ACTIVE) {
                                                $txt .= '<a onclick="quickDetail(' . $moresellers['selprod_id'] . ')" href="javascript:void(0)" class=" btn btn-brand btn-sm">  ' . Labels::getLabel('LBL_Rent_Now', $siteLangId) . '</a>';
                                            }
                                            if (strtotime($moresellers['selprod_available_from']) <= strtotime(date('Y-m-d h:i:s')) && $moresellers['selprod_active'] == applicationConstants::ACTIVE && $moresellers['selprod_stock'] > 0) {
                                                $txt .= '<a data-id="' . $moresellers['selprod_id'] . '" data-min-qty="' . $moresellers['selprod_min_order_qty'] . '"  href="javascript:void(0)" class=" btn btn-brand btnAddToCart--js btn-sm">  ' . Labels::getLabel('LBL_Add_To_Cart', $siteLangId) . '</a>';
                                            }
                                        }
                                        $td->appendElement('plaintext', array(), $txt, true);
                                        break;
                                    default:
                                        $td->appendElement('plaintext', array(), $moresellers[$key], true);
                                        break;
                                }
                            }
                        }
                        echo $tbl->getHtml();
                        ?> 
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>