<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$btn = $PromoCouponsFrm->getField('btn_submit');
$btn->addFieldTagAttribute('class', 'btn btn-brand');
?>
<!-- <span class="gap"></span> -->
<div class="modal-dialog modal-dialog-centered" role="document" id="coupon-form-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?php echo Labels::getLabel('LBL_Coupons:', $siteLangId); ?></h5>

            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <?php if (!empty($cartSummary['cartDiscounts']['coupon_code'])) { ?>
            <div class="alert alert--success">
                <a href="javascript:void(0)" class="close" onClick="removePromoCode()"></a>
                <p><?php echo Labels::getLabel('LBL_Promo_Code', $siteLangId); ?>
                    <strong><?php echo $cartSummary['cartDiscounts']['coupon_code']; ?></strong>
                    <?php echo Labels::getLabel('LBL_Successfully_Applied', $siteLangId); ?>
                </p>
            </div>
            <?php } ?>
            <div class="my-5" id="coupons-modaal">
                <?php
                $PromoCouponsFrm->setFormTagAttribute('class', 'form apply--coupon--form custom-form');
                $PromoCouponsFrm->setFormTagAttribute('onsubmit', 'applyPromoCode(this); return false;');
                $PromoCouponsFrm->getField('onsubmit', 'applyPromoCode(this); return false;');
                $PromoCouponsFrm->developerTags['colClassPrefix'] = 'col-lg-6 col-md-6 col-sm-';
                $PromoCouponsFrm->developerTags['fld_default_col'] = 6;
                $PromoCouponsFrm->setJsErrorDisplay('afterfield');
                echo $PromoCouponsFrm->getFormTag();
                echo $PromoCouponsFrm->getFieldHtml('coupon_code');
                echo $PromoCouponsFrm->getFieldHtml('btn_submit');
                echo $PromoCouponsFrm->getExternalJs();
                ?>
                </form>
            </div>


            <div>
                <?php if ($couponsList) { ?>

                <h6 class="h6">
                    <?php echo Labels::getLabel("LBL_Available_Coupons", $siteLangId); ?></h6>
                <ul class="coupon-offers">
                    <?php $counter = 1;
                            foreach ($couponsList as $coupon_id => $coupon) { ?>
                    <li>
                        <div class="coupon-code" onClick="triggerApplyCoupon('<?php echo $coupon['coupon_code']; ?>');"
                            title="<?php echo Labels::getLabel("LBL_Click_to_apply_coupon", $siteLangId); ?>">
                            <?php echo $coupon['coupon_code']; ?></div>
                        <?php if ($coupon['coupon_description'] != '') { ?>
                        <p><?php echo $coupon['coupon_description']; ?> </p>
                        <?php } ?>
                    </li>
                    <?php $counter++;
                            } ?>
                </ul>

                <?php } else {
                    echo Labels::getLabel("LBL_No_Copons_offer_is_available_now.", $siteLangId);
                } ?>
            </div>

        </div>
    </div>
</div>