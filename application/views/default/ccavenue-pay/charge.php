<?php defined('SYSTEM_INIT') or die('Invalid Usage'); 
$btn = $frm->getField('btn_submit');
if (null != $btn) {
    $btn->developerTags['noCaptionTag'] = true;
    $btn->setFieldTagAttribute('class', "btn btn-brand btn-wide");
}
?>
<script>
    window.onload = function() {
        var d = new Date().getTime();
        if (document.getElementById("tid")) {
            document.getElementById("tid").value = d;
        }
    };
</script>
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
            <div class="payable-form__body" id="paymentFormElement-js">
                <?php if (!isset($error)) :
                    ?>
                    <h6><?php echo Labels::getLabel('LBL_REDIRECTING_TO_PAYMENT_PAGE...', $siteLangId); ?></h6>
                    <?php 
                    $btn = $frm->getField('btn_submit');
                    if(null != $btn){
                        $btn->setWrapperAttribute('class', 'd-none');
                    }
                    echo $frm->getFormHtml(); ?>
                <?php else : ?>
                    <div class="alert alert--danger"><?php echo $error ?></div>
                <?php endif; ?>
            </div>  
        </div>
    </div>
</section>
<script type="text/javascript">
    $(function() {
        setTimeout(function() {
            $('form[name="frm-ccavenue"]').submit()
        }, 200);
    });
</script>