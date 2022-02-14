<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="row d-flex">
	<div class="col-6">
		<p class="option-box">
			<a href="javascript:void(0);" onclick="markOrderDelivered(<?php echo $op_id; ?>)" class="btn btn-brand btn-sm" > 
				<?php echo Labels::getLabel('LBL_Mark_As_Delivered', $siteLangId); ?>
			</a> <br />
			<small class="note"><?php echo Labels::getLabel('LBL_You_Can_not_Place_Return_Request_is_you_marked_order_as_delivered', $siteLangId); ?></small>
		</p>
	</div>
	<?php if($opDetails['op_status_id'] == FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS")) { ?>
	<div class="col-6">
		<p class="option-box">
			<a href="<?php echo UrlHelper::generateUrl('Buyer', 'orderReturnRequest', array($op_id)); ?>" class="btn btn-brand btn-sm">
				<?php echo Labels::getLabel('LBL_Return_Order', $siteLangId); ?>
			</a> <br />
			<small class="note"><?php echo Labels::getLabel('LBL_Place_Return_Request_for_this_order', $siteLangId); ?></small>
		</p>
	</div>
	<?php } ?>
</div>
<style>
.option-box{
	padding : 15px;
	border : 1px solid #dcdcdc;
	height : 100%;
	
}
</style>