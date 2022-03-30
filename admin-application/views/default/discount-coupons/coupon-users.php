<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<ul class="columlist links--vertical" id="coupon-user">
<?php if(count($couponUsers)>0){
	$lis = '';
	foreach($couponUsers as $user){
		$userName = "<a href='javascript:void(0)' onclick='redirectfunc(\"" . UrlHelper::generateUrl('Users') . "\", " . $user['user_id'] . ")'>" . $user['user_name'] . "</a>";

		$lis .= '<li><span class="left"><a href="javascript:void(0)" title="Remove" onClick="removeCouponUser('.$coupon_id.','.$user['user_id'].');"><i class="icon ion-close-round" data-prod-id="' . $user['user_id'] . '"></i></a></span>';
		$lis .= '<span >' . $userName.' ('.$user['credential_username'].')'.'<input type="hidden" value="'.$user['user_id'].'"  name="products[]"></span></li>';
	}
	echo $lis;
} ?>
</ul>
