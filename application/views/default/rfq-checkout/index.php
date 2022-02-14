<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<button class="order-summary-toggle" data-trigger="order-summary">
    <span class="order-summary-toggle__inner">
        <span class="order-summary-toggle__icon-wrapper mr-2">
            <svg width="20" height="19" xmlns="http://www.w3.org/2000/svg" class="order-summary-toggle__icon">
                <path
                    d="M17.178 13.088H5.453c-.454 0-.91-.364-.91-.818L3.727 1.818H0V0h4.544c.455 0 .91.364.91.818l.09 1.272h13.45c.274 0 .547.09.73.364.18.182.27.454.18.727l-1.817 9.18c-.09.455-.455.728-.91.728zM6.27 11.27h10.09l1.454-7.362H5.634l.637 7.362zm.092 7.715c1.004 0 1.818-.813 1.818-1.817s-.814-1.818-1.818-1.818-1.818.814-1.818 1.818.814 1.817 1.818 1.817zm9.18 0c1.004 0 1.817-.813 1.817-1.817s-.814-1.818-1.818-1.818-1.818.814-1.818 1.818.814 1.817 1.818 1.817z">
                </path>
            </svg>
        </span>
        <span class="order-summary-toggle__text">
            <span><?php echo Labels::getLabel('LBL_ORDER_SUMMARY', $siteLangId); ?>
                <i class="arrow">
                    <svg class="svg">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#arrow-right"
                            href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#arrow-right"></use>
                    </svg>
                </i>
            </span>
        </span>
        <span class="order-summary-toggle__total-recap total-recap">
            <span class="total-recap__final-price" id="netAmountSummary"></span>
        </span>
    </span>
</button>
<section class="section">
    <div class="container">
        <div class="checkout-page">
            <main class="main checkout-content-js"></main>
            <aside class="sidebar" id="order-summary" data-close-on-click-outside="order-summary">
                <div class="sidebar__content">
                    <div id="order-summary" class="order-summary summary-listing-js"></div>
                    <?php echo FatUtility::decodeHtmlEntities($pageData['epage_content']); ?>
                </div>
            </aside>
            <div id="verification-section"></div>
        </div>
    </div>
</section>

<script type="text/javascript">
$orderId = "<?php echo $orderInfo['order_id']; ?>";
var ORDER_NUMERIC_ID = "<?php echo $orderInfo['order_order_id']; ?>";
$defaultAddress = 1;
$("document").ready(function() {
    <?php if ($isLoadVerificationSection) { ?>
    loadVerificationSection();
    <?php } else { ?>
    loadPaymentSummary();
    <?php } ?>
    loadFinancialSummary();
});
</script>