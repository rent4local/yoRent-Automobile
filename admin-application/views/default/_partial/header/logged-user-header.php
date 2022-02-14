 <div id ="wrapper">
   <!--header start here-->
	<header id="header" class="no-print">
        <div class="headerwrap">
            <div class="one_third_grid"><a href="javascript:void(0);" class="menutrigger"><span></span></a></div>
            <div class="one_third_grid logo">
                <?php
                $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_ADMIN_LOGO, 0, 0, $adminLangId, false);
                $aspectRatioArr = AttachedFile::getRatioTypeArray($adminLangId);
                ?>
                <a href="<?php echo UrlHelper::generateUrl('home'); ?>">
                    <img <?php if ($fileData['afile_aspect_ratio'] > 0) { ?> data-ratio= "<?php echo $aspectRatioArr[$fileData['afile_aspect_ratio']]; ?>" <?php } ?> src="<?php echo UrlHelper::generateUrl('Image','siteAdminLogo', array( $adminLangId )); ?>" alt="">
                </a>
            </div>
            <div class="one_third_grid">
                <ul class="iconmenus">
                    <li class="viewstore">
                        <a title="<?php echo Labels::getLabel('LBL_View_Store',$adminLangId);?>" href="<?php echo CONF_WEBROOT_FRONT_URL; ?>" target="_blank">
                        <img src="<?php echo CONF_WEBROOT_FRONT_URL; ?>images/admin/store.svg" width="20" alt=""></a>
                    </li>
					<li class="erase">
                        <a title="<?php echo Labels::getLabel('LBL_Clear_Cache',$adminLangId);?>" href="javascript:void(0)" onclick="clearCache()"><img class="iconerase" alt="" src="<?php echo CONF_WEBROOT_FRONT_URL; ?>images/admin/header_icon_2.svg"></a>
                    </li>
					<li class="togglemsg droplink">
                        <a href="javascript:void(0)" onclick="getNotifications()" title="Message"><img src="<?php echo CONF_WEBROOT_FRONT_URL; ?>images/admin/header_icon_1.svg" alt=""><span class="counts"><?php echo CommonHelper::displayBadgeCount($notifyCount); ?></span></a>
                        <div class="dropwrap">
                            <div class="head"><?php echo Labels::getLabel('LBL_Notifications',$adminLangId);?></div>
                            <div class="body" id="notificationList">

                            </div>
							<div class="foot"><a href="<?php echo UrlHelper::generateUrl('notifications','');?>" class="link redirect--js"><?php echo Labels::getLabel('LBL_View_all',$adminLangId); ?></a></div>
                        </div>
                    </li>
					<li class="droplink" >
                        <a href="javascript:void(0)" title="Language"><img src="<?php echo CONF_WEBROOT_FRONT_URL; ?>images/admin/icon_langs.svg" width="20" alt=""></a>
                        <div class="dropwrap">
                            <div class="head"><?php echo Labels::getLabel('LBL_Select_Language',$adminLangId); ?></div>
                            <div class="body">
                                <ul class="linksvertical">
								<?php  foreach($languages as $langId => $language){ ?>
									<li  <?php echo ($adminLangId==$langId)?'class="is--active"':'';?>><a href="javascript:void(0);" onClick="setSiteDefaultLang(<?php echo $langId;?>)"><?php echo $language['language_name']; ?></a></li>
									<?php } ?>
                                </ul>
                            </div>
                        </div>
                    </li>
					<!--<li class="sitemap">
                        <a title="<?php echo Labels::getLabel('LBL_Update_Sitemap',$adminLangId);?>" href="<?php echo UrlHelper::generateUrl('sitemap', 'generate'); ?>"><img src="<?php echo CONF_WEBROOT_FRONT_URL; ?>images/admin/sitemap.svg" width="20" alt=""></a>
                    </li>-->

                    <!--li class="droplink" >
                        <a href="javascript:void(0)" title="User"><img src="<?php echo CONF_WEBROOT_FRONT_URL; ?>images/admin/avatar.svg" width="20" alt=""></a>
                        <div class="dropwrap">
                            <div class="head">Select User</div>
                            <div class="body">
                                <ul class="linksvertical">
                                    <li><a href="#">Website </a></li>
                                    <li><a href="#">Merchant</a></li>
                                    <li><a href="#">User</a></li>
                                    <li><a href="#">Affiliate</a></li>
                                </ul>
                            </div>
                        </div>
                    </li-->

                    <li class="logout">
                        <a href="<?php echo UrlHelper::generateUrl('profile','logout');?>" title="<?php echo Labels::getLabel('LBL_Logout',$adminLangId);?>"><img src="<?php echo CONF_WEBROOT_FRONT_URL; ?>images/admin/header_icon_3.svg" alt=""></a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="searchwrap">
            <div class="searchform"><input type="text"></div><a href="javascript:void(0)" class="searchclose searchtoggle"></a>
        </div>

    </header>
    <!--header end here-->


    <!--body start here-->
    <div id="body">
        <?php $this->includeTemplate('_partial/header/left-navigation.php')?>
