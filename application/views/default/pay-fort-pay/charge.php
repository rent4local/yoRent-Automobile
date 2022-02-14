<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$btn = $frm->getField('btn_submit');
$btn->addFieldTagAttribute('class', 'btn btn-brand');
$btn->addFieldTagAttribute('data-processing-text', Labels::getLabel('LBL_PLEASE_WAIT..', $siteLangId));

if (null != $btn) {
    $btn->setFieldTagAttribute('class', "d-none");
}

$btn = $frm->getField('btn_cancel');
if (null != $btn) {
    $btn->setFieldTagAttribute('class', "d-none");
}
?>
<section class="payment-section">
    <div class="payable-amount">            
        <div class="payable-amount__head">
            <div class="payable-amount--header">              
                <?php $this->includeTemplate('_partial/paymentPageLogo.php', array('siteLangId' => $siteLangId)); ?>
            </div>
            <div class="payable-amount--decription">
                <h2><?php echo CommonHelper::displayMoneyFormat($paymentAmount) ?></h2>
                <p><?php echo Labels::getLabel('LBL_Total_Payable', $siteLangId); ?></p>
                <p><?php echo Labels::getLabel('LBL_Order_Invoice', $siteLangId); ?>: <?php echo $orderInfo["invoice"]; ?></p>
            </div>
        </div>
        <div class="payable-amount__body payment-from">      
            <div class="payable-form__body">
                <?php if (!isset($error)) : ?>
                    <h6><?php echo Labels::getLabel('LBL_REDIRECTING_TO_PAYMENT_PAGE...', $siteLangId); ?></h6>
                    <?php echo $frm->getFormHtml(); ?>
                <?php else : ?>
                    <div class="alert alert--danger"><?php echo $error ?></div>
                <?php endif; ?>
            </div>  
        </div>
    </div>
</section>
<script type="text/javascript">
    $(document).ready(function () {
        setTimeout(function () {
            $('form[name="frmPayFort"]').submit();
        }, 2000);
    });
</script>