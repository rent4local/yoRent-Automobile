<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="row justify-content-center">
    <div class="col-xl-6 col-lg-6 mb-4">
        <?php if (empty($requestRow)) { ?>
            <p><?php echo Labels::getLabel('LBL_Please_Submit_Request_for_data', $siteLangId); ?></p>
        <?php } elseif ($requestRow['ureq_status'] == UserGdprRequest::STATUS_PENDING) { ?>
            <p><?php echo Labels::getLabel('LBL_Your_Request_is_pending_for_admin_approval', $siteLangId); ?></p>
        <?php } else { ?>
            <div class="card card-gdpr">
                <div class="card-header">
                    <div class="card-header__content">
                        <h5><?php echo Labels::getLabel('LBL_Data_Portability', $siteLangId); ?></h5>
                        <p><?php echo Labels::getLabel('You_can_use_the_links_below_to_download_all_the_data_we_store', $siteLangId); ?></p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mt-3">
                        <ul class="gdpr-buttons">
                            <li>
                                <a href="<?php echo UrlHelper::generateUrl('GdprRequests', 'downloadRequestData', [UserGdprRequest::REQUEST_TYPE_GDPR_REQUEST]); ?>" class="link--download">
                                    <?php echo Labels::getLabel('LBL_GDPR_Requests', $siteLangId); ?>
                                    <span class="icn">
                                        <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_FRONT_URL; ?>images/dashboard/retina/sprite.svg#icn-download">
                                            </use>
                                        </svg>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo UrlHelper::generateUrl('GdprRequests', 'downloadRequestData', [UserGdprRequest::REQUEST_TYPE_PERSONAL_INFO]); ?>" class="link--download">
                                    <?php echo Labels::getLabel('LBL_Personal_Information', $siteLangId); ?>
                                    <span class="icn">
                                        <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_FRONT_URL; ?>images/dashboard/retina/sprite.svg#icn-download">
                                            </use>
                                        </svg>
                                    </span>
                                </a>
                            </li>
                            <?php if (User::isSeller()) { ?>
                                <li><a href="<?php echo UrlHelper::generateUrl('GdprRequests', 'downloadRequestData', [UserGdprRequest::REQUEST_TYPE_SHOP_INFO]); ?>" class="link--download">
                                        <?php echo Labels::getLabel('LBL_Shop_Information', $siteLangId); ?>
                                        <span class="icn">
                                            <svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_FRONT_URL; ?>images/dashboard/retina/sprite.svg#icn-download">
                                                </use>
                                            </svg>
                                        </span>
                                    </a></li>
                                <li><a href="<?php echo UrlHelper::generateUrl('GdprRequests', 'downloadRequestData', [UserGdprRequest::REQUEST_TYPE_SOCIAL_PLATFORM]); ?>" class="link--download">
                                        <?php echo Labels::getLabel('LBL_Social_Platform_Details', $siteLangId); ?>
                                        <span class="icn">
                                            <svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_FRONT_URL; ?>images/dashboard/retina/sprite.svg#icn-download">
                                                </use>
                                            </svg>
                                        </span>
                                    </a></li>
                                <li><a href="<?php echo UrlHelper::generateUrl('GdprRequests', 'downloadRequestData', [UserGdprRequest::REQUEST_TYPE_PICKUP_ADDRESS]); ?>" class="link--download"><?php echo Labels::getLabel('LBL_Pickup_Address', $siteLangId); ?>
                                        <span class="icn">
                                            <svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_FRONT_URL; ?>images/dashboard/retina/sprite.svg#icn-download">
                                                </use>
                                            </svg>
                                        </span>
                                    </a></li>
                                <li><a href="<?php echo UrlHelper::generateUrl('GdprRequests', 'downloadRequestData', [UserGdprRequest::REQUEST_TYPE_SALES]); ?>" class="link--download">

                                        <?php echo Labels::getLabel('LBL_Sales', $siteLangId); ?>
                                        <span class="icn">
                                            <svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_FRONT_URL; ?>images/dashboard/retina/sprite.svg#icn-download">
                                                </use>
                                            </svg>
                                        </span>
                                    </a></li>
                            <?php } ?>
                            <?php if (User::isBuyer()) { ?>
                                <li><a href="<?php echo UrlHelper::generateUrl('GdprRequests', 'downloadRequestData', [UserGdprRequest::REQUEST_TYPE_PURCHASE]); ?>" class="link--download"><?php echo Labels::getLabel('LBL_Purchase', $siteLangId); ?>
                                        <span class="icn">
                                            <svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_FRONT_URL; ?>images/dashboard/retina/sprite.svg#icn-download">
                                                </use>
                                            </svg>
                                        </span>
                                    </a></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>


        <?php } ?>
    </div>
</div>