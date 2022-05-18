<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<ul>
    <li class="<?php echo $action == 'view' ? 'is--active' : '' ?>"><a href="<?php echo UrlHelper::generateUrl('shops', 'view', array($shop_id));?>" class="ripplelink"><?php echo Labels::getLabel('LBL_SHOP_STORE_HOME', $siteLangId); ?></a></li>
    
    <li class="<?php echo $action == 'featuredProducts' ? 'is--active' : '' ?>"><a href="<?php echo UrlHelper::generateUrl('shops', 'featuredProducts', array($shop_id));?>" class="ripplelink"><?php echo Labels::getLabel('LBL_SHOP_FEATURED_PRODUCTS', $siteLangId); ?></a></li>
    
    <?php if (0 < FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) { ?>
    <li class="<?php echo $action == 'topProducts' ? 'is--active' : '' ?>"><a href="<?php echo UrlHelper::generateUrl('shops', 'topProducts', array($shop_id));?>" class="ripplelink"><?php echo Labels::getLabel('LBL_SHOP_TOP_PRODUCTS', $siteLangId); ?></a></li>
    
    <li class="<?php echo $action == 'shop' ? 'is--active' : '' ?>"><a href="<?php echo UrlHelper::generateUrl('reviews', 'shop', array($shop_id));?>" class="ripplelink"><?php echo Labels::getLabel('LBL_SHOP_REVIEW', $siteLangId); ?></a></li>
    <?php } ?>
    <?php if (!UserAuthentication::isUserLogged() || (UserAuthentication::isUserLogged() && ((User::isBuyer()) || (User::isSeller() )) && (UserAuthentication::getLoggedUserId() != $shop_user_id))) { ?>
    <li class="<?php echo $action == 'sendMessage' ? 'is--active' : '' ?>"><a href="<?php echo UrlHelper::generateUrl('shops', 'sendMessage', array($shop_id));?>" class="ripplelink"><?php echo Labels::getLabel('LBL_SHOP_CONTACT', $siteLangId); ?></a></li>
    <?php } ?>
    
    <li class="<?php echo $action == 'policy' ? 'is--active' : '' ?>"><a href="<?php echo UrlHelper::generateUrl('shops', 'policy', array($shop_id));?>" class="ripplelink"><?php echo Labels::getLabel('LBL_SHOP_DETAILS', $siteLangId); ?></a></li>
</ul>
