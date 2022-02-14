<div class="not-found">
    <img src="<?php echo CONF_WEBROOT_URL; ?>images/retina/errors/error-404.svg" alt="">
    <h3><?php echo Labels::getLabel('LBL_404_PAGE_NOT_FOUND', $siteLangId); ?></h3>
    <p><?php echo Labels::getLabel('LBL_Check_that_you_typed_the_address_correctly,_go_back_to_your_previous_page_or_try_using_our_site_search_to_find_something_specific.', $siteLangId); ?></p>
    <div class="action">
        <a href="<?php echo UrlHelper::generateUrl();?>" class="btn btn-outline-brand "><?php echo Labels::getLabel('LBL_Back_To_Home', $siteLangId); ?></a>
    </div>
</div>