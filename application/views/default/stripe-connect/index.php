<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="card">
    <div class="card-body">
        <div class="row justify-content-center my-5">
            <div class="col-lg-8">
                <?php if (empty($accountId)) { ?>
                    <div class="features-block">
                        <div class="features-block_head">
                            <h4><?php echo Labels::getLabel('LBL_FEATURES', $siteLangId); ?></h4>
                            <p><?php echo Labels::getLabel('API_CONNECT_FLEXIBLE_SET_OF_FEATURES_INCLUDES', $siteLangId); ?></p>
                        </div>
                        <ul class="features-block_list">
                            <li>
                                <p><strong><?php echo Labels::getLabel('LBL_PAYOUTS', $siteLangId); ?></strong>:</p>
                                <p><?php echo Labels::getLabel('API_ROUTE_FUNDS_TO_YOUR_RECIPIENTS', $siteLangId); ?></p>
                            </li>

                            <li>
                                <p> <strong><?php echo Labels::getLabel('LBL_FEE_COLLECTION', $siteLangId); ?></strong>: </p>
                                <p> <?php echo Labels::getLabel('API_DRIVE_REVENUE_FOR_YOUR_BUSINESS', $siteLangId); ?> </p>
                            </li>
                            <li>
                                <p> <strong><?php echo Labels::getLabel('API_ONBOARDING', $siteLangId); ?></strong>:</p>
                                <p> <?php echo Labels::getLabel('API_MOBILE_FRIENDLY_AND_CONVERSION_OPTIMIZED_UI', $siteLangId); ?> </p>
                            </li>
                        </ul>
                        <div class="text-center"> <a class="btn btn-outline-brand btn-sm mr-2" onClick="register(this)" href="javascript:void(0)" data-href="<?php echo UrlHelper::generateUrl($keyName, 'register'); ?>">
                                <?php echo Labels::getLabel('LBL_REGISTER', $siteLangId); ?>
                            </a>
                            <a class="btn btn-brand btn-sm" href="<?php echo UrlHelper::generateUrl($keyName, 'login') ?>" title="<?php echo Labels::getLabel('MSG_LOGIN', $siteLangId); ?>">
                                <?php echo Labels::getLabel('LBL_ALREADY_HAVE_ACCOUNT_?', $siteLangId); ?>
                            </a>
                        </div>
                    </div>
                    <?php } else { ?>
                        <div class="text-center">
                            <h5>
                                <?php 
                                    echo Labels::getLabel('LBL_ACCOUNT_ID', $siteLangId); ?> : <?php echo $accountId; 
                                    if ('custom' == $stripeAccountType) {
                                ?>
                                    <a class="btn btn-brand btn-sm" onClick="deleteAccount(this)" href="javascript:void(0)" data-href="<?php echo UrlHelper::generateUrl($keyName, 'deleteAccount') ?>" title="<?php echo Labels::getLabel('LBL_DELETE_ACCOUNT', $siteLangId); ?>">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                <?php } ?>
                            </h5>
                        </div>
                <?php } ?>
                <?php if (!empty($loginUrl)) { ?>
                    <a class="btn btn-brand btn-sm" href="<?php echo $loginUrl; ?>" target="_blank">
                        <?php echo Labels::getLabel('LBL_STRIPE_DASHBOARD', $siteLangId); ?>
                    </a>
                <?php } ?>
            </div>
        </div>
        <?php if (!empty($requiredFields) && !empty($accountId)) { ?>
            <div class="row">
                <div class="col-md-12 requiredFieldsForm-js"></div>
            </div>
            <script>
                requiredFieldsForm();
            </script>
        <?php } elseif (!empty($accountId) && !empty($stripeUserData)) { ?>
            <ul class="stripe-stats">
                <li>
                    <div class="stats">
                        <span class="title"><?php echo Labels::getLabel('MSG_BUSINESS_PROFILE_NAME', $siteLangId); ?></span>
                        <p><?php echo $stripeUserData['business_profile']['name']; ?></p>
                    </div>
                </li>
                <li>
                    <div class="stats">
                        <p>
                            <?php echo Labels::getLabel('MSG_CHARGES', $siteLangId); ?> :
                            <?php echo 0 < $stripeUserData['charges_enabled'] ? Labels::getLabel('MSG_ENABLED', $siteLangId) : Labels::getLabel('MSG_NOT_ENABLED', $siteLangId); ?>
                        </p>
                        <p>
                            <?php echo Labels::getLabel('MSG_PAYOUTS', $siteLangId); ?> :
                            <?php echo ucwords($stripeUserData['settings']['payouts']['schedule']['interval']); ?>
                        </p>
                    </div>
                </li>
                <li>
                    <div class="stats">
                        <span class="title"><?php echo Labels::getLabel('MSG_SUPPORT_EMAIL', $siteLangId); ?></span>
                        <p><?php echo $stripeUserData['business_profile']['support_email']; ?></p>
                    </div>
                </li>
                <li>
                    <div class="stats">
                        <span class="title"><?php echo Labels::getLabel('MSG_SUPPORT_PHONE', $siteLangId); ?></span>
                        <p><?php echo $stripeUserData['business_profile']['support_phone']; ?></p>
                    </div>
                </li>
                <li>
                    <div class="stats">
                        <span class="title"><?php echo Labels::getLabel('MSG_SUPPORT_ADDRESS', $siteLangId); ?></span>
                        <?php $address = $stripeUserData['business_profile']['support_address']; ?>
                        <p><?php echo $address['line1']; ?></p>
                        <p><?php echo $address['line2']; ?></p>
                        <p>
                            <?php echo $address['city'] . ', ' .
                                $address['state'] . ', ' .
                                $address['country'] . ' ' . $address['postal_code'] . ''; ?>
                        </p>
                    </div>
                </li>
                <li>
                    <div class="stats">
                        <span class="title"><?php echo Labels::getLabel('MSG_BANK_DETAIL', $siteLangId); ?></span>
                        <?php foreach ($stripeUserData['external_accounts']['data'] as $index => $bank) { ?>
                            <p><?php echo Labels::getLabel('MSG_BANK_NAME', $siteLangId); ?> : <?php echo $bank['bank_name']; ?>
                            </p>
                            <p><?php echo Labels::getLabel('MSG_ACCOUNT_HOLDER_NAME', $siteLangId); ?> :
                                <?php echo $bank['account_holder_name']; ?></p>
                            <p><?php echo Labels::getLabel('MSG_ACCOUNT_NUMBER', $siteLangId); ?> :
                                <?php echo '****' . $bank['last4']; ?></p>
                            <p><?php echo Labels::getLabel('MSG_ROUTING_NUMBER', $siteLangId); ?> :
                                <?php echo $bank['routing_number']; ?></p>
                            <?php if (($index + 1) < count($stripeUserData['external_accounts']['data'])) { ?>

                            <?php } ?>
                        <?php } ?>
                    </div>
                </li>
            </ul>
        <?php } ?>
    </div>
</div>
<script>
    var keyName = '<?php echo $keyName; ?>';
    $(document).on('keyup', ".mcc-js", function() {
        var currObj = $(this);
        var valueFld = currObj.data('valfld');
        console.log(valueFld);
        if ('' != currObj.val()) {
            currObj.siblings('ul.dropdown-menu').remove();
            currObj.autocomplete({
                'classes': {
                    "ui-autocomplete": "custom-ui-autocomplete"
                },
                'source': function(request, response) {
                    $.ajax({
                        url: fcom.makeUrl(keyName, 'getMerchantCategory'),
                        data: {
                            fIsAjax: 1,
                            keyword: currObj.val()
                        },
                        dataType: 'json',
                        type: 'post',
                        success: function(json) {
                            response($.map(json, function(value, index) {
                                return {
                                    label: value,
                                    value: value,
                                    id: index
                                };
                            }));
                        },
                    });
                },
                select: function(event, ui) {
                    $("." + valueFld).val(ui.item.id);
                }
            });
        } else {
            $("." + valueFld).val('');
        }
    });
</script>