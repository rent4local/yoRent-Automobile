<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$rewardPoints = UserRewardBreakup::rewardPointBalance(UserAuthentication::getLoggedUserId());
?>

<div class="step">
    <div class="step_section">

        <div class="step_body">
            <ul class="review-block">
                <li>
                    <div class="review-block__label">
                        <?php
                    if ($fulfillmentType == Shipping::FULFILMENT_PICKUP || $cartHasPhysicalProduct == false) {
                        echo Labels::getLabel('LBL_Billing_to:', $siteLangId);
                        $address = $billingAddressArr;
                    } else {
                        echo Labels::getLabel('LBL_Shipping_to:', $siteLangId);
                        $address = $shippingAddressArr;
                    }
                    ?></div>

                    <div class="review-block__content">
                        <div class="delivery-address">
                            <p><?php echo $address['addr_name'] . ', ' . $address['addr_address1']; ?>
                                <?php
                            if (strlen($address['addr_address2']) > 0) {
                                echo ", " . $address['addr_address2'];
                                ?>
                                <?php } ?>
                            </p>
                            <p><?php echo $address['addr_city'] . ", " . $address['state_name'] . ", " . $address['country_name'] . ", " . $address['addr_zip']; ?>
                            </p>
                            <?php if (strlen($address['addr_phone']) > 0) { ?>
                            <p class="phone-txt"><i class="fas fa-mobile-alt"></i><?php echo $address['addr_phone']; ?>
                            </p>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="review-block__link">
                        <?php
                    if ($fulfillmentType == Shipping::FULFILMENT_PICKUP || $cartHasPhysicalProduct == false) {
                        $onclick = 'loadAddressDiv(' . Address::ADDRESS_TYPE_BILLING . ');';
                    } else {
                        $onclick = 'loadAddressDiv();';
                    }
                    ?>
                        <a class="" href="javascript:void(0);" onClick="<?php echo $onclick; ?>"><svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#edit">
                                </use>
                            </svg><span class="txt"><?php echo Labels::getLabel('LBL_Edit', $siteLangId); ?></span></a>
                    </div>
                </li>

                <?php if ($fulfillmentType == Shipping::FULFILMENT_PICKUP && !empty($orderPickUpData)) { ?>
                <li>
                    <div class="review-block__label">
                        <?php echo Labels::getLabel('LBL_Pickup_Address', $siteLangId); ?>
                    </div>
                    <div class="review-block__content">
                        <div class="delivery-address">
                            <?php foreach ($orderPickUpData as $address) { ?>
                            <p><strong><?php echo $address['op_selprod_title'];?></strong></p>
                            <p><strong><?php echo ($address['opshipping_by_seller_user_id'] > 0) ? $address['op_shop_name'] : FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, null, ''); ?></strong>
                            </p>
                            <p><?php echo $address['oua_name'] . ', ' . $address['oua_address1']; ?>
                                <?php
                                    if (strlen($address['oua_address2']) > 0) {
                                        echo ", " . $address['oua_address2'];
                                        ?>
                                <?php } ?>
                            </p>
                            <p><?php echo $address['oua_city'] . ", " . $address['oua_state'] . ", " . $address['oua_country'] . ", " . $address['oua_zip']; ?>
                            </p>
                            <?php if (strlen($address['oua_phone']) > 0) { ?>
                            <p class="phone-txt"><i class="fas fa-mobile-alt"></i><?php echo $address['oua_phone']; ?>
                            </p>
                            <?php } ?>

                            <?php
                                //$fromTime = isset($address["opshipping_time_slot_from"]) && !empty($address["opshipping_time_slot_from"]) ? date('H:i', strtotime($address["opshipping_time_slot_from"])) : '';
                                //$toTime = isset($address["opshipping_time_slot_to"]) && !empty($address["opshipping_time_slot_to"]) ? date('H:i', strtotime($address["opshipping_time_slot_to"])) : '';
                                ?>
                            <!--p class="time-txt">
                                <i class="fas fa-calendar-day"></i>
                                <?php
                                    //$opshippingDate = isset($address["opshipping_date"]) ? FatDate::format($address["opshipping_date"]) : '';
                                    //echo $opshippingDate . ' ' . $fromTime . ' - ' . $toTime;
                                    ?>
                            </p-->
                            <?php if (count($orderPickUpData) > 1) { ?>
                            <a class="link plus-more" href="javascript:void(0);"
                                onClick="orderPickUpData('<?php echo $orderId; ?>')"><?php echo '+ ' . (count($orderPickUpData) - 1) . ' ' . Labels::getLabel('LBL_More', $siteLangId); ?></a>
                            <?php
                                    break;
                                }
                                ?>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="review-block__link">
                        <a class="" href="javascript:void(0);" onClick="loadShippingSummaryDiv();"><svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#edit">
                                </use>
                            </svg><span><?php echo Labels::getLabel('LBL_Edit', $siteLangId); ?></span></a>
                    </div>
                </li>
                <?php } ?>

                <?php if ($cartHasPhysicalProduct && $fulfillmentType == Shipping::FULFILMENT_SHIP && !empty($orderShippingData)) { ?>
                <li>
                    <div class="review-block__label">
                        <?php echo Labels::getLabel('LBL_Shipping:', $siteLangId); ?>
                    </div>
                    <div class="review-block__content">
                        <div class="shipping-data">
                            <ul class="media-more media-more-sm show">
                                <?php foreach ($orderShippingData as $shipData) { ?>
                                <?php
                                    foreach ($shipData as $data) {
                                        if ($data['opd_product_type'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                                            $imgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'addonProduct', array($data['op_selprod_id'], "THUMB", 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                                        } else {
                                            $imgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($data['selprod_product_id'], "THUMB", $data['op_selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                                        }
                                        ?>
                                <li>
                                    <span class="circle" data-toggle="tooltip" data-placement="top"
                                        title="<?php echo $data['op_selprod_title']; ?>"
                                        data-original-title="<?php echo $data['op_selprod_title']; ?>">
                                        <img src="<?php echo $imgUrl; ?>"
                                            alt="<?php echo $data['op_selprod_title']; ?>">
                                    </span>
                                </li>
                                <?php } ?>
                                <?php if (count($orderShippingData) > 1) { ?>
                                <li> <span class="circle plus-more"
                                        onClick="orderShippingData('<?php echo $orderId; ?>')"><?php echo '+ ' . (count($orderShippingData) - 1); ?></span>
                                </li>
                                <?php } ?>
                                <?php
                                    break;
                                }
                                ?>
                            </ul>

                            <div class="shipping-data_title"><?php echo $data['opshipping_label']; ?></div>

                        </div>
                    </div>
                    <div class="review-block__link">
                        <a class="" href="javascript:void(0);" onClick="loadShippingSummaryDiv();"><svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#edit">
                                </use>
                            </svg><span class="txt"><?php echo Labels::getLabel('LBL_Edit', $siteLangId); ?></span></a>
                    </div>
                </li>
                <?php } ?>

                <?php if ($cartHasPhysicalProduct && $fulfillmentType == Shipping::FULFILMENT_SHIP && $shippingAddressId != $billingAddressId) { ?>
                <li>
                    <div class="review-block__label">
                        <?php echo Labels::getLabel('LBL_Billing_to:', $siteLangId); ?>
                    </div>
                    <div class="review-block__content">
                        <p><?php echo $billingAddressArr['addr_name'] . ', ' . $billingAddressArr['addr_address1']; ?>
                            <?php
                            if (strlen($billingAddressArr['addr_address2']) > 0) {
                                echo ", " . $billingAddressArr['addr_address2'];
                                ?>
                            <?php } ?>
                        </p>
                        <p><?php echo $billingAddressArr['addr_city'] . ", " . $billingAddressArr['state_name']; ?></p>
                        <p><?php echo $billingAddressArr['country_name'] . ", " . $billingAddressArr['addr_zip']; ?></p>
                        <?php if (strlen($billingAddressArr['addr_phone']) > 0) { ?>
                        <p class="phone-txt"><i
                                class="fas fa-mobile-alt"></i><?php echo $billingAddressArr['addr_phone']; ?>
                        </p>
                        <?php } ?>
                    </div>
                    <div class="review-block__link">
                        <a class="" href="javascript:void(0);"
                            onClick="loadAddressDiv(<?php echo Address::ADDRESS_TYPE_BILLING; ?>)"><svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#edit">
                                </use>
                            </svg><span class="txt"><?php echo Labels::getLabel('LBL_Edit', $siteLangId); ?></span></a>
                    </div>
                </li>
                <?php } ?>

                <?php if (!empty($verificationFldsData)) { ?>
                <li>
                    <div class="review-block__label">
                        <?php echo Labels::getLabel('LBL_Verification_Data:', $siteLangId); ?>
                    </div>
                    <div class="review-block__content">
                        <div class="shipping-data">
                            <ul class="verification-data-list">
                            <?php 
                            foreach ($verificationFldsData as $vfldData) { 
                                if (1 > $vfldData['ovd_vfld_id']) {
                                    continue;
                                }
                                if ($vfldData['ovd_vflds_type'] == VerificationFields::FLD_TYPE_TEXTBOX) { ?>
                                    <li>
                                        <span class="lable"><?php 
                                            $fldVal = (trim($vfldData['ovd_value']) != '') ? $vfldData['ovd_value'] : Labels::getLabel('LBL_N/A', $siteLangId);
                                            echo $vfldData['ovd_vflds_name'] . '</span> : ' . $fldVal;?>
                                    </li>
                                <?php } else {
                                    $downloadUrl = UrlHelper::generateUrl('Checkout', 'downloadAttachedFile', array($orderOrderId, $vfldData['ovd_vfld_id']));
                                    $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_VERIFICATION_ATTACHMENT, $orderOrderId, $vfldData['ovd_vfld_id']);
                                ?>
                                <li>
                                    <span class="lable"><?php echo $vfldData['ovd_vflds_name'] . '</span> : <a href="'. $downloadUrl .'"  download >'. $file_row['afile_name'] .'</a> ';?>
                                </li>
                                <?php }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                    <div class="review-block__link">
                        <a class="" href="javascript:void(0);" onClick="loadVerificationDiv();">
                            <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#edit">
                                </use>
                            </svg>
                            <span class="txt"><?php echo Labels::getLabel('LBL_Edit', $siteLangId); ?></span>
                        </a>
                    </div>
                </li>
                <?php } ?>
            </ul>
        </div>
    </div>


    <div class="step_section">
        <div class="step_head">
            <h5 class="step_title"><?php echo Labels::getLabel('LBL_Payment_Summary', $siteLangId); ?></h5>
        </div>


        <?php if ($fulfillmentType == Shipping::FULFILMENT_SHIP && $shippingAddressId == $billingAddressId) { ?>
        <label class="checkbox mb-4">
            <input onClick="billingAddress(this);" type="checkbox" checked="checked" name="isShippingSameAsBilling" value="1" /><?php echo Labels::getLabel('LBL_MY_BILLING_IS_SAME_AS_SHIPPING_ADDRESS', $siteLangId); ?> 
            <i class="input-helper"></i>
        </label>
        <?php } ?>

        <div class="step_body">

            <?php if (empty($cartSummary['cartRewardPoints'])) { ?>
            <?php if ($rewardPoints > 0) { ?>
            <div class="rewards">
                <div class="rewards__points">
                    <ul>
                        <li>
                            <p><?php echo Labels::getLabel('LBL_AVAILABLE_REWARDS_POINTS', $siteLangId); ?></p>
                            <span class="count"><?php echo $rewardPoints; ?></span>
                        </li>
                        <li>
                            <p><?php echo Labels::getLabel('LBL_POINTS_WORTH', $siteLangId); ?></p>
                            <span
                                class="count"><?php echo CommonHelper::displayMoneyFormat(CommonHelper::convertRewardPointToCurrency($rewardPoints), true, false, true, false, true); ?></span>
                        </li>
                    </ul>
                </div>
                <div class="info">
                    <span>
                        <svg class="svg">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info"
                                href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info"></use>
                        </svg>
                        <?php
                                $cartTotal = isset($cartSummary['cartTotal']) ? $cartSummary['cartTotal'] : 0;
                                $cartDiscounts = isset($cartSummary['cartDiscounts']["coupon_discount_total"]) ? $cartSummary['cartDiscounts']["coupon_discount_total"] : 0;
                                $canBeUsed = min(min($rewardPoints, CommonHelper::convertCurrencyToRewardPoint($cartTotal - $cartDiscounts)), FatApp::getConfig('CONF_MAX_REWARD_POINT', FatUtility::VAR_INT, 0));
                                $minRewardPoints = FatApp::getConfig('CONF_MIN_REWARD_POINT', FatUtility::VAR_INT, 0);
                                if($minRewardPoints == $canBeUsed) {
                                    $str = Labels::getLabel('LBL_You_can_redeem_{MIN}_reward_points.', $siteLangId);
                                }else if($minRewardPoints > $canBeUsed){
                                    $str = Labels::getLabel('LBL_You_need_minimum_{MIN}_reward_points_to_redeem.', $siteLangId);
                                }else{
                                    $str = Labels::getLabel('LBL_Redeem_reward_points_within_the_range_of_{MIN}_to_{MAX}.', $siteLangId);
                                }
                                echo CommonHelper::replaceStringData($str, ['{MIN}' => $minRewardPoints ,'{MAX}' => $canBeUsed]);
                                ?>
                    </span>
                </div>
                <?php
                        $redeemRewardFrm->setFormTagAttribute('class', 'form form-inline');
                        $redeemRewardFrm->setFormTagAttribute('onsubmit', 'useRewardPoints(this); return false;');
                        /* $redeemRewardFrm->setJsErrorDisplay('afterfield'); */
                        $fld = $redeemRewardFrm->getField('redeem_rewards');
                        $fld->setFieldTagAttribute('class', 'form-control');
                        $fld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Enter_Points_To_Redeem', $siteLangId));
                        $fld = $redeemRewardFrm->getField('btn_submit');
                        $fld->setFieldTagAttribute('class', 'btn btn-submit');
                        echo $redeemRewardFrm->getFormTag();
                        ?>
                <?php echo $redeemRewardFrm->getFieldHtml('redeem_rewards'); ?>
                <?php echo $redeemRewardFrm->getFieldHtml('btn_submit'); ?>
                </form>
                <?php echo $redeemRewardFrm->getExternalJs(); ?>
            </div>
            <?php } ?>
            <?php } else { ?>
            <div class="info">
                <span> <svg class="svg">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info"
                            href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info">
                        </use>
                    </svg> <?php echo Labels::getLabel('LBL_Reward_Points', $siteLangId); ?>
                    <strong><?php echo $cartSummary['cartRewardPoints']; ?>
                        (<?php echo CommonHelper::displayMoneyFormat(CommonHelper::convertRewardPointToCurrency($cartSummary['cartRewardPoints']), true, false, true, false, true); ?>)</strong>
                    <?php echo Labels::getLabel('LBL_Successfully_Used', $siteLangId); ?></span>
                <ul class="list-actions">
                    <li>
                        <a href="javascript:void(0)" onClick="removeRewardPoints()"><svg class="svg" width="24px"
                                height="24px">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove"
                                    href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove">
                                </use>
                            </svg>
                        </a>
                    </li>
                </ul>
            </div>
            <?php } ?>
            <?php if ($userWalletBalance > 0 && $cartSummary['orderNetAmount'] > 0 && $canUseWalletForPayment) { ?>
            <div class="wallet-balance">
                <label class="checkbox wallet">
                    <input onChange="walletSelection(this)" type="checkbox"
                        <?php echo ($cartSummary["cartWalletSelected"]) ? 'checked="checked"' : ''; ?>
                        name="pay_from_wallet" id="pay_from_wallet" value="1">
                    
                    <span class="wallet__txt">
                        <svg class="svg">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#wallet"
                                href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#wallet">
                            </use>
                        </svg>
                        <div class="">
                            <p><?php echo Labels::getLabel('LBL_AVAILABLE_BALANCE', $siteLangId); ?></p>
                            <span class="currency-value"
                                dir="ltr"><?php echo CommonHelper::displayMoneyFormat($userWalletBalance, true, false, true, false, true); ?></span>
                        </div>
                    </span>
                </label>
                <?php
        if ($cartSummary["cartWalletSelected"] && $userWalletBalance >= $cartSummary['orderNetAmount']) {
            $btnSubmitFld = $WalletPaymentForm->getField('btn_submit');
            $btnSubmitFld->addFieldTagAttribute('class', 'btn btn-brand btn-wide');
            $btnSubmitFld->value = Labels::getLabel('LBL_PAY', $siteLangId) . ' ' . CommonHelper::displayMoneyFormat($cartSummary['orderNetAmount'], true, false, true, false, false);
            $WalletPaymentForm->developerTags['colClassPrefix'] = 'col-md-';
            $WalletPaymentForm->developerTags['fld_default_col'] = 12;
            echo $WalletPaymentForm->getFormHtml();
            ?>
                <script type="text/javascript">
                function confirmOrder(frm) {
                    var data = fcom.frmData(frm);
                    var action = $(frm).attr('action');
                    fcom.updateWithAjax(fcom.makeUrl('Checkout', 'confirmOrder'), data, function(ans) {
                        $(location).attr("href", action);
                    });
                }
                </script>
                <?php } else { ?>
                <div class="wallet-balance_info">
                    <?php echo Labels::getLabel('LBL_USE_MY_WALLET_BALANCE_TO_PAY_FOR_MY_ORDER', $siteLangId); ?></div>
                <?php } ?>
            </div>

            <?php } ?>


            <div id="payment" class="">
                <div class="align-items-center mb-4">
                    <?php if ($cartSummary['orderNetAmount'] <= 0) { ?>
                    <div class="gap"></div>
                    <div id="wallet">
                        <h6><?php echo Labels::getLabel('LBL_Payment_to_be_made', $siteLangId); ?>
                            <strong><?php echo CommonHelper::displayMoneyFormat($cartSummary['orderNetAmount'], true, false, true, false, true); ?></strong>
                        </h6> 
                        <?php
                        $btnSubmitFld = $confirmForm->getField('btn_submit');
                        $btnSubmitFld->addFieldTagAttribute('class', 'btn btn-brand btn-sm');
        
                        $confirmForm->developerTags['colClassPrefix'] = 'col-md-';
                        $confirmForm->developerTags['fld_default_col'] = 12;
                        echo $confirmForm->getFormHtml();
                        ?>
                        <div class="gap"></div>
                    </div>
                    <?php } ?>
                </div>
                <?php if ($cartSummary['orderPaymentGatewayCharges']) { ?>
                <div class="payment-area"
                    <?php echo ($cartSummary['orderPaymentGatewayCharges'] <= 0) ? 'is--disabled' : ''; ?>>
                    <?php if ($cartSummary['orderPaymentGatewayCharges'] && 0 < count($paymentMethods)) { ?>
                    <ul class="nav nav-payments <?php echo 1 == count($paymentMethods) ? 'd-none' : ''; ?>"
                        role="tablist" id="payment_methods_tab">
                        <?php
                    foreach ($paymentMethods as $key => $val) {
                        $pmethodCode = $val['plugin_code'];
                        if ($cartHasDigitalProduct && in_array(strtolower($pmethodCode), ['cashondelivery', 'payatstore'])) {
                            continue;
                        }
                        $pmethodId = $val['plugin_id'];
                        $pmethodName = $val['plugin_name'];

                        if (in_array($pmethodCode, $excludePaymentGatewaysArr[applicationConstants::CHECKOUT_PRODUCT])) {
                            continue;
                        }
                        ?>
                        <li class="nav-item">
                            <a class="nav-link" aria-selected="true"
                                href="<?php echo UrlHelper::generateUrl('Checkout', 'PaymentTab', array($orderInfo['order_id'], $pmethodId)); ?>"
                                data-paymentmethod="<?php echo $pmethodCode; ?>">
                                <div class="payment-box">
                                    <span><?php echo $pmethodName; ?></span>
                                </div>
                            </a>
                        </li>
                        <?php }
                    ?>
                    </ul>
                    <div class="tab-content" id="tabs-container"></div>
                    <?php
            } else {
                echo Labels::getLabel("LBL_PAYMENT_METHOD_IS_NOT_AVAILABLE._PLEASE_CONTACT_YOUR_ADMINISTRATOR.", $siteLangId);
            }
            ?>
                </div>
                <?php } ?>

            </div>

        </div>

    </div>
    <script>
    var ORDER_ID = '<?php echo $orderId; ?>';
    </script>


    <script>
    var enableGcaptcha = false;
    </script>
    <?php
$siteKey = FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '');
$secretKey = FatApp::getConfig('CONF_RECAPTCHA_SECRETKEY', FatUtility::VAR_STRING, '');
$paymentMethods = new PaymentMethods();
if (!empty($siteKey) && !empty($secretKey) && true === $paymentMethods->cashOnDeliveryIsActive()) {
    ?>
    <script src='https://www.google.com/recaptcha/api.js?onload=googleCaptcha&render=<?php echo $siteKey; ?>'></script>
    <script>
    var enableGcaptcha = true;
    </script>
    <?php } ?>

    <?php if ($cartSummary['orderPaymentGatewayCharges']) { ?>
    <script type="text/javascript">
    var tabsId = '#payment_methods_tab';
    $(document).ready(function() {
        $(tabsId + " li:first a").addClass('active');
        if ($(tabsId + ' li a.active').length > 0) {
            loadTab($(tabsId + ' li a.active'));
        }
        $(tabsId + ' a').click(function() {
            if ($(this).hasClass('active')) {
                return false;
            }
            $(tabsId + ' li a.active').removeClass('active');
            $(this).addClass('active');
            loadTab($(this));
            return false;
        });
    });

    function loadTab(tabObj) {
        if (isUserLogged() == 0) {
            loginPopUpBox();
            return false;
        }
        if (!tabObj || !tabObj.length) {
            return;
        }

        fcom.ajax(tabObj.attr('href'), '', function(response) {
            var paymentMethod = tabObj.data('paymentmethod');
            if ('paypal' != paymentMethod.toLowerCase() && 0 < $("#paypal-buttons").length) {
                $("#paypal-buttons").html("");
            }

            $('#tabs-container').html(response);
            if ('cashondelivery' == paymentMethod.toLowerCase() || 'payatstore' == paymentMethod
                .toLowerCase()) {
                if (true == enableGcaptcha) {
                    googleCaptcha();
                }
                $.mbsmessage.close();
            } else {
                var form = '#tabs-container form';
                if (0 < $(form).length) {
                    $('#tabs-container').append(fcom.getLoader());
                    if (0 < $(form + " input[type='submit']").length) {
                        $(form + " input[type='submit']").val(langLbl.requestProcessing);
                    }
                    setTimeout(function() {
                        $(form).submit()
                    }, 100);
                }
            }
        });
    }
    </script>
    <?php
}