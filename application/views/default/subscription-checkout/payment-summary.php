<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="step">
    <div class="step_section">
        <div class="step_head">
            <h5 class="step_title"><?php echo Labels::getLabel('LBL_Payment_Summary', $siteLangId); ?></h5>
        </div>
        <div class="step_body">
            <?php if ($userWalletBalance > 0 && $cartSummary['orderNetAmount'] > 0 && $canUseWalletForPayment) { ?>
            <div class="wallet-balance">
                <label class="checkbox wallet">
                    <input onChange="walletSelection(this)" type="checkbox" <?php echo ($cartSummary["cartWalletSelected"]) ? 'checked="checked"' : ''; ?> name="pay_from_wallet" id="pay_from_wallet" value="1">
                    
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
                            fcom.updateWithAjax(fcom.makeUrl('SubscriptionCheckout', 'confirmOrder'), data, function(ans) {
                                $(location).attr("href", action);
                            });
                        }
                        </script>
                <?php } else { ?>
                    <div class="wallet-balance_info">
                        <?php echo Labels::getLabel('LBL_USE_MY_WALLET_BALANCE_TO_PAY_FOR_MY_ORDER', $siteLangId); ?>
                    </div>
                <?php } ?>
            </div>
            <?php } ?>


            <div id="payment" class="">
                <div class="align-items-center mb-4">
                    <?php if ($cartSummary['orderNetAmount'] <= 0) { ?>
                    <ul class="list-cart list-shippings" data-list="SHIPPING SUMMARY">
                        <li>
                            <div class="cell cell_product">
                                <div class="product-profile">
                                    <div class="product-profile__data">
                                        <div class="title"> <?php echo Labels::getLabel('LBL_Payment_to_be_made', $siteLangId); ?> </div>
                                    </div>
                                </div>
                            </div>
                            <div class="cell cell_price">
                                <div class="product-price"><?php echo CommonHelper::displayMoneyFormat($cartSummary['orderNetAmount'], true, false, true, false, true); ?></div>
                            </div>
                            <div class="cell cell_action hide-caption">
                                <ul class="actions">
                                    <li>
                                        <?php
                                        $btnSubmitFld = $confirmPaymentFrm->getField('btn_submit');
                                        $btnSubmitFld->addFieldTagAttribute('class', 'btn btn-brand btn-sm');
                        
                                        $confirmPaymentFrm->developerTags['colClassPrefix'] = 'col-md-';
                                        $confirmPaymentFrm->developerTags['fld_default_col'] = 12;
                                        echo $confirmPaymentFrm->getFormHtml();
                                        ?>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                    <?php /* <div class="gap"></div>
                    <div id="wallet">
                        <h6><?php echo Labels::getLabel('LBL_Payment_to_be_made', $siteLangId); ?>
                            <strong><?php echo CommonHelper::displayMoneyFormat($cartSummary['orderNetAmount'], true, false, true, false, true); ?></strong>
                        </h6> 
                        <?php
                        $btnSubmitFld = $confirmPaymentFrm->getField('btn_submit');
                        $btnSubmitFld->addFieldTagAttribute('class', 'btn btn-brand btn-sm');
        
                        $confirmPaymentFrm->developerTags['colClassPrefix'] = 'col-md-';
                        $confirmPaymentFrm->developerTags['fld_default_col'] = 12;
                        echo $confirmPaymentFrm->getFormHtml();
                        ?>
                        <div class="gap"></div>
                    </div> */ ?>
                    <script type="text/javascript">
                        function confirmOrder(frm) {
                            var data = fcom.frmData(frm);
                            var action = $(frm).attr('action');
                            fcom.updateWithAjax(fcom.makeUrl('SubscriptionCheckout', 'confirmOrder'), data, function(ans) {
                                $(location).attr("href", action);
                            });
                        }
                    </script>
                    <?php } ?>
                </div>
                <?php if ($cartSummary['orderPaymentGatewayCharges']) { ?>
                <div class="payment-area"
                    <?php echo ($cartSummary['orderPaymentGatewayCharges'] <= 0) ? 'is--disabled' : ''; ?>>
                    <?php if ($cartSummary['orderPaymentGatewayCharges'] && 0 < count($paymentMethods)) { ?>
                    <ul class="nav nav-payments <?php echo 1 == count($paymentMethods) ? 'd-none' : ''; ?>"
                        role="tablist" id="payment_methods_tab">
                        <?php foreach ($paymentMethods as $key => $val) {
                        if (in_array($val['plugin_code'], $excludePaymentGatewaysArr[applicationConstants::CHECKOUT_SUBSCRIPTION])) {
                            continue;
                        }
                    
                        $pmethodCode = $val['plugin_code'];
                        $pmethodId = $val['plugin_id'];
                        $pmethodName = $val['plugin_name'];

                        if (in_array($pmethodCode, $excludePaymentGatewaysArr[applicationConstants::CHECKOUT_PRODUCT])) {
                            continue;
                        }
                        ?>
                        <li class="nav-item">
                            <a class="nav-link" aria-selected="true"
                                href="<?php echo UrlHelper::generateUrl('SubscriptionCheckout', 'PaymentTab', array($orderInfo['order_id'], $pmethodId)); ?>"
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
} ?>
<style>
    .hide-caption .caption-wraper {display : none; }
</style>    