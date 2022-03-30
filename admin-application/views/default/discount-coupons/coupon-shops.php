<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<ul class="columlist links--vertical" id="coupon-shop">
<?php if(count($couponShops)>0){
	$lis = '';
	foreach($couponShops as $shop){
		$shopName = "<a href='javascript:void(0)' onclick='redirectfunc(\"" . UrlHelper::generateUrl('Shops') . "\", " . $shop['shop_id'] . ")'>" . $shop['shop_name'] . "</a>";

		$lis .= '<li><span class="left"><a href="javascript:void(0)" title="Remove" onClick="removeCouponShop('.$coupon_id.','.$shop['shop_id'].');"><i class="icon ion-close-round" data-prod-id="' . $shop['shop_id'] . '"></i></a></span>';
		$lis .= '<span>' . $shopName.' ('.$shop['shop_identifier'].')'.'<input type="hidden" value="'.$shop['shop_id'].'"  name="shops[]"></span></li>';
	}
	echo $lis;
} ?>
</ul>