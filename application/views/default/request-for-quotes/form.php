<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'form form--horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupRequestForQuote(this); return(false);');

$keyFld = $frm->getField('rfq_request_type');
$keyFld->setFieldTagAttribute('onChange', 'toggleRequestType(this.value);');

$keyFld = $frm->getField('rfq_fulfilment_type');
$keyFld->setFieldTagAttribute('onChange', 'toggleFulfilmentStatues(this.value);');

$btnFld = $frm->getField('btn_submit');
$btnFld->setFieldTagAttribute('class', 'btn btn-brand btn-block');

?>

<div class="modal-dialog modal-lg modal-dialog-centered" role="document" id="sign-in">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">
                <?php echo Labels::getLabel('LBL_Request_For_Quote_For:', $siteLangId); ?>&nbsp;<span
                    class="primary-color"><?php echo $productData['selprod_title']; ?></span></h5>

            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <?php echo $frm->getFormTag(); ?>
            <div class="row">

                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <label class="field_label">
                                <?php
								$fld = $frm->getField('rfq_quantity');
								echo $fld->getCaption();
								?>
                                <span class="spn_must_field">*</span>
                            </label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $frm->getFieldHtml('rfq_quantity'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <label class="field_label">
                                <?php
								$fld = $frm->getField('rfq_request_type');
								echo $fld->getCaption();
								?>
                                <span class="spn_must_field">*</span>
                            </label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $frm->getFieldHtml('rfq_request_type'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <label class="field_label">
                                <?php
								$fld = $frm->getField('rfq_fulfilment_type');
								echo $fld->getCaption();
								?>
								<span class="spn_must_field">*</span>
							</label>
						</div>
						<div class="field-wraper">
							<div class="field_cover">
								<?php echo $frm->getFieldHtml('rfq_fulfilment_type'); ?>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-6 col-md-6 col-sm-6">
					<div class="field-set">
						<div class="caption-wraper">
							<label class="field_label">
								<?php
								$fld = $frm->getField('rfq_date_range');
								echo $fld->getCaption();
								?>
							</label>
						</div>
						<div class="field-wraper date-selector field--calender-daterange--js">
							<div class="field_cover">
								<?php echo $frm->getFieldHtml('rfq_date_range'); ?>
							</div>
						</div>
					</div>
				</div>
				<!-- <div class="col-lg-6 col-md-6 col-sm-6">
					<div class="field-set">
						<div class="caption-wraper">
							<label class="field_label">
								<?php
								// $fld = $frm->getField('rfq_to_date');
								// echo $fld->getCaption();
								?>
							</label>
						</div>
						<div class="field-wraper">
							<div class="field_cover">
								<?php // echo $frm->getFieldHtml('rfq_to_date'); ?>
							</div>
						</div>
					</div>
				</div> -->



                <?php
				$commentFldClass = 'col-lg-12 col-md-12 col-sm-12';
				if ($productData['selprod_type'] == SellerProduct::PRODUCT_TYPE_ADDON && $productData['selprod_document_required'] == applicationConstants::YES) {
					$commentFldClass = 'col-lg-6 col-md-6 col-sm-6';
				}
				?>
                <div class="<?php echo $commentFldClass; ?>">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <label class="field_label">
                                <?php
								$fld = $frm->getField('rfq_comments');
								echo $fld->getCaption();
								?>
                            </label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $frm->getFieldHtml('rfq_comments'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
				if ($productData['selprod_type'] == SellerProduct::PRODUCT_TYPE_ADDON && $productData['selprod_document_required'] == applicationConstants::YES) {
					?>
                <div class="col-lg-6 col-md-6 col-sm-6" id="document-fld-js">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <label class="field_label">
                                <?php echo Labels::getLabel('LBL_Upload_documents', $siteLangId); ?>
                                <span class="spn_must_field">*</span>
                            </label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <input type="file" name="rfq_documents" onchange="uploadDocument()" />
                            </div>
                            <div id="uploaded-documents-js" class="uploaded--documents"></div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>

            <?php if (!empty($services)) { ?>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <table class="table table-services">
                        <tbody>
                            <?php foreach ($services as $serviceId => $val) { ?>
                            <tr class="<?php echo ($val['selprod_document_required']) ? "no-border-row" : ""; ?>">
                                <td>
                                    <h6><span class="primary-color"><?php echo $val['selprod_title']; ?></span></h6>
                                </td>
                                <td>
                                    <div class="field-set">
                                        <div class="caption-wraper">
                                            <label class="field_label">
                                                <?php
												$fld = $frm->getField('rfq_required_capacity_service[' . $serviceId . ']');
												echo $fld->getCaption();
												?>
                                            </label>
                                        </div>
                                        <div class="field-wraper">
                                            <div class="field_cover">
                                                <?php echo $frm->getFieldHtml('rfq_required_capacity_service[' . $serviceId . ']'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="field-set">
                                        <div class="caption-wraper">
                                            <label class="field_label">
                                                <?php
												$fld = $frm->getField('rfq_quantity_service[' . $serviceId . ']');
												echo $fld->getCaption();
												?>
                                            </label>
                                        </div>
                                        <div class="field-wraper">
                                            <div class="field_cover">
                                                <?php echo $frm->getFieldHtml('rfq_quantity_service[' . $serviceId . ']'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
							<?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php } ?>


			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12">
					<div class="field-set">
						<div class="caption-wraper">
							<h6 id="billing" style="display: none;"><?php echo Labels::getLabel("LBL_Billing_Address", $siteLangId); ?></h6>
							<h6 id="shipping"><?php echo Labels::getLabel("LBL_Shipping_&_Billling_Address", $siteLangId); ?></h6>
						</div>
					</div>
				</div>
				<?php if ($addresses) { ?>
					<div class="col-lg-12 col-md-12 col-sm-12" id="shipping-address">
					<ul class="my-addresses">
					<?php foreach ($addresses as $address) { ?>
						<li class="<?php echo ($address['addr_is_default'] == applicationConstants::YES) ? 'is--selected' : ''; ?>">
							<label id="address_<?php echo $address['addr_id']; ?>" class="my-addresses__body address-billing  ">
								<div class="address-inner">
									<span class="radio">
										<input <?php echo ($address['addr_is_default'] == applicationConstants::YES) ? 'checked="checked"' : ''; ?> name="rfq_ship_address_id" value="<?php echo $address['addr_id']; ?>" type="radio"><i class="input-helper"></i>
									</span>
									<address class="delivery-address">
										<h5><span><?php echo ($address['addr_title'] != '') ?  $address['addr_name'].'</span>' . '<span class="tag">'.$address['addr_title'] . ' </span> '  : $address['addr_name']; ?></h5>
										<?php echo $address['addr_address1']; ?><br>
										<?php echo (strlen($address['addr_address2']) > 0) ? $address['addr_address2'] . '<br>' : ''; ?>
										<?php echo (strlen($address['addr_city']) > 0) ? $address['addr_city'] . ',' : ''; ?>
										<?php echo (strlen($address['state_name']) > 0) ? $address['state_name'] . '<br>' : ''; ?>
										<?php echo (strlen($address['country_name']) > 0) ? $address['country_name'] . '<br>' : ''; ?>
										<?php echo (strlen($address['addr_zip']) > 0) ? Labels::getLabel('LBL_Zip:', $siteLangId) . ' ' . $address['addr_zip'] . '<br>' : ''; ?>
										<p class="phone-txt"><?php echo (strlen($address['addr_phone']) > 0) ? '<i class="fas fa-mobile-alt"></i> ' . $address['addr_dial_code'] . ' ' . $address['addr_phone'] . '<br>' : ''; ?></p>
									</address>
								</div>
							</label>
						</li>
					<?php } ?>
					</div>
				<?php } else { ?>
				<div class="col-lg-12 col-md-12 col-xs-12">
					<p class="mb-3"><?php echo sprintf(Labels::getLabel("LBL_To_submit_rfq_you_need_to_add_delivery_address_%s_click_here_to_add_new_address_%s", $siteLangId), ' <a class="link" href="'.CommonHelper::generateUrl('account', 'MyAddresses').'" target="_blank">', '</a> '); ?></p>
				</div>    
				<?php
					}
				?>
            </div>

            <div class="row" id="pickup">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <h6><?php echo Labels::getLabel("LBL_Pickup_address", $siteLangId); ?></h6>
                        </div>
                    </div>
                </div>
                <?php if ($shopAddress) { ?>
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <ul class="my-addresses">
                        <?php foreach ($shopAddress as $address) { ?>
                        <li class="">
                            <label id="address_<?php echo $address['addr_id']; ?>"
                                class="my-addresses__body address-billing">
                                <div class="address-inner">
                                    <span class="radio">
                                        <input
                                            <?php echo ($address['addr_is_default'] == applicationConstants::YES) ? 'checked="checked"' : ''; ?>
                                            name="rfq_pickup_address_id" value="<?php echo $address['addr_id']; ?>"
                                            type="radio">
                                    </span>
                                    <address class="delivery-address">
                                        <h5><span><?php echo ($address['addr_title'] != '') ?  $address['addr_name'] .'</span>'. '<span class="tag">'.$address['addr_title'] . ' </span> '  : $address['addr_name']; ?>
                                        </h5>
                                        <?php echo $address['addr_address1']; ?><br>
                                        <?php echo (strlen($address['addr_address2']) > 0) ? $address['addr_address2'] . '<br>' : ''; ?>
                                        <?php echo (strlen($address['addr_city']) > 0) ? $address['addr_city'] . ',' : ''; ?>
                                        <?php echo (strlen($address['state_name']) > 0) ? $address['state_name'] . '<br>' : ''; ?>
                                        <?php echo (strlen($address['country_name']) > 0) ? $address['country_name'] . '<br>' : ''; ?>
                                        <?php echo (strlen($address['addr_zip']) > 0) ? Labels::getLabel('LBL_Zip:', $siteLangId) . ' ' . $address['addr_zip'] . '<br>' : ''; ?>
                                        <p class="phone-txt">
                                            <?php echo (strlen($address['addr_phone']) > 0) ? '<i class="fas fa-mobile-alt"></i> ' . $address['addr_dial_code'] . ' ' . $address['addr_phone'] . '<br>' : ''; ?>
                                        </p>
                                    </address>
                                </div>
                            </label>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
                <?php } else { ?>
                <div class="col-lg-12 col-md-12 col-xs-12">
                    <div class="info p-3 mb-3">
                    <span>
                        <svg class="svg">
                            <use xlink:href="<?php echo CONF_WEBROOT_FRONT_URL; ?>images/retina/sprite.svg#info" >
                            </use>
                        </svg><?php echo sprintf(Labels::getLabel("LBL_Pickup_Address_Not_Available", $siteLangId)); ?>
                    </span>
                    
                    </div>
                </div>
                <?php
					}
				?>
            </div>

			<div class="row justify-content-center">
				<div class="col-lg-4 col-md-4 col-sm-4">
					<div class="field-set">
						<div class="field-wraper">
							<div class="field_cover">
								<?php
								echo $frm->getFieldHtml('rfq_from_date');
								echo $frm->getFieldHtml('rfq_to_date');
								echo $frm->getFieldHtml('selprod_id');
								echo $frm->getFieldHtml('shop_id');
								echo $frm->getFieldHtml('group_id');
								echo $frm->getFieldHtml('parent_id');
								echo $frm->getFieldHtml('btn_submit');
								?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo $frm->getExternalJs(); ?>
            </form>
        </div>
    </div>
</div>

<script>
$('input[name="rfq_pickup_address_id"]').on('change', function() {
    $('#pickup li').removeClass('active');
    $(this).parents('li').addClass('active');
});
$('input[name="rfq_ship_address_id"]').on('change', function() {
    $('#shipping-address li').removeClass('active');
    $(this).parents('li').addClass('active');
});
</script>


<script>
    $(document).ready(function () {
        $("#pickup").hide();

		var datepickerOption = {
			autoClose: true,
			minDate: new Date(),
            dateFormat: "yy/mm/dd",
            startDate : new Date(),
			showShortcuts: false,
            inline:true,
            container : ".field--calender-daterange--js",
            customArrowPrevSymbol: '<i class="fa fa-arrow-circle-left"></i>',
            customArrowNextSymbol: '<i class="fa fa-arrow-circle-right"></i>',
            stickyMonths: true,
		};
		$('.rfq_dates--js').dateRangePicker(datepickerOption).bind('datepicker-change',function(event, obj) {
			var selectedDates = obj.value;
			var datesArr = selectedDates.split(" to ");
            $('input[name="rfq_date_range"]').val(obj.value);
			$('input[name="rfq_from_date"]').val(datesArr[0]);
			$('input[name="rfq_to_date"]').val(datesArr[1]);
		});
    });

    $(window).on('scroll', function(){
        console.log("working add space");
    $('.date-picker-wrapper').css('top', '100px');

});



var FULFILMENT_TYPE_PICK = <?php echo Shipping::FULFILMENT_PICKUP; ?>;
</script>
<style>
    .disabled-input{
        pointer-events: none;
    }
    .disabled-input input{
       color: rgba(0, 0, 0, 0.38) !important;
        background-color: rgba(0, 0, 0, 0.12) !important;
        box-shadow: none;
        cursor: initial;
        border-color: transparent !important;
    }
</style>    