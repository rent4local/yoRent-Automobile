<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmOrderCancel->setFormTagAttribute('class', 'form form--horizontal');
$frmOrderCancel->setFormTagAttribute('onsubmit', 'setupOrderCancelRequest(this); return(false);');
$frmOrderCancel->developerTags['colClassPrefix'] = 'col-md-';
$frmOrderCancel->developerTags['fld_default_col'] = 12;
$btnSubmit = $frmOrderCancel->getField('btn_submit');
$btnSubmit->setFieldTagAttribute('class', "btn btn-brand");
?>
<?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Order_Cancellation_Request', $siteLangId); ?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="card">
                <div class="card-body">
                    <?php if (FatApp::getConfig('CONF_ALLOW_PENALTY_ON_RENTAL_ORDER_CANCEL_FROM_BUYER', FatUtility::VAR_INT, 0) && !empty($orderCancelPenaltyRules)) { ?>
                    <div class="alert alert-primary show">
                        
                            <?php $maxDurationLabel = ($orderCancelPenaltyRules['ocrule_duration_max'] == -1) ? Labels::getLabel('LBL_Infinity', $siteLangId) : $orderCancelPenaltyRules['ocrule_duration_max'];  
                               echo sprintf(Labels::getLabel('LBL_if_Order_cancel_between_%s_-_%s_hours_then_%s_amount_will_be_refunded', $siteLangId), $orderCancelPenaltyRules['ocrule_duration_min'], $maxDurationLabel, $orderCancelPenaltyRules['ocrule_refund_amount'] . '%'); ?>
                        
                    </div>
                    <?php } ?>
                    <?php echo $frmOrderCancel->getFormHtml(); ?>
                </div>
            </div>
        </div>
    </div>
</main>
