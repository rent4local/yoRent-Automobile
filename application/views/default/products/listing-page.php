<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');

$frmProductSearch->setFormTagAttribute('onSubmit', 'searchProducts(this); return(false);');
$keywordFld = $frmProductSearch->getField('keyword');
$keywordFld->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Search', $siteLangId));
$keywordFld = $frmProductSearch->getField('keyword');
$keywordFld->overrideFldType("hidden");

$sortByFld = $frmProductSearch->getField('sortBy');
$sortByFld->addFieldTagAttribute('class', 'custom-select sorting-select');

$pageSizeFld = $frmProductSearch->getField('pageSize');
$pageSizeFld->addFieldTagAttribute('class', 'custom-select sorting-select');

$desktop_url = '';
$tablet_url = '';
$mobile_url = '';
$category['banner'] = isset($category['banner']) ? (array) $category['banner'] : array();
if (!empty($category['banner'])) {
    $desktop_url = UrlHelper::generateFileUrl('Category', 'Banner', array($category['prodcat_id'], $siteLangId, 'DESKTOP', applicationConstants::SCREEN_DESKTOP));
    $tablet_url = UrlHelper::generateFileUrl('Category', 'Banner', array($category['prodcat_id'], $siteLangId, 'TABLET', applicationConstants::SCREEN_MOBILE));
    $mobile_url = UrlHelper::generateFileUrl('Category', 'Banner', array($category['prodcat_id'], $siteLangId, 'MOBILE', applicationConstants::SCREEN_IPAD));
    $catBannerArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_CATEGORY_BANNER, $category['prodcat_id'], 0, $siteLangId);
    foreach ($catBannerArr as $slideScreen) {
        $uploadedTime = AttachedFile::setTimeParam($slideScreen['afile_updated_at']);
        switch ($slideScreen['afile_screen']) {
            case applicationConstants::SCREEN_MOBILE:
                $fileRow = CommonHelper::getImageAttributes(AttachedFile::FILETYPE_CATEGORY_BANNER, $category['prodcat_id'], 0, 0, applicationConstants::SCREEN_MOBILE);
                $mobile_url = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Category', 'Banner', array($category['prodcat_id'], $siteLangId, 'MOBILE', applicationConstants::SCREEN_MOBILE)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg') . ",";
                break;
            case applicationConstants::SCREEN_IPAD:
                $fileRow = CommonHelper::getImageAttributes(AttachedFile::FILETYPE_CATEGORY_BANNER, $category['prodcat_id'], 0, 0, applicationConstants::SCREEN_IPAD);
                $tablet_url = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Category', 'Banner', array($category['prodcat_id'], $siteLangId, 'TABLET', applicationConstants::SCREEN_IPAD)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg') . ",";
                break;
            case applicationConstants::SCREEN_DESKTOP:
                $fileRow = CommonHelper::getImageAttributes(AttachedFile::FILETYPE_CATEGORY_BANNER, $category['prodcat_id'], 0, 0, applicationConstants::SCREEN_DESKTOP);
                $desktop_url = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Category', 'Banner', array($category['prodcat_id'], $siteLangId, 'DESKTOP', applicationConstants::SCREEN_DESKTOP)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg') . ",";
                break;
        }
?>
<?php } ?>
<section class="bg-shop">

    <picture class="shop-banner">
        <source data-aspect-ratio="4:3" srcset="<?php echo $mobile_url; ?>" media="(max-width: 767px)">
        <source data-aspect-ratio="4:3" srcset="<?php echo $tablet_url; ?>" media="(max-width: 1024px)">
        <source data-aspect-ratio="4:1" srcset="<?php echo $desktop_url; ?>">
        <img data-aspect-ratio="4:1" src="<?php echo $desktop_url; ?>"
            alt="<?php echo (!empty($fileRow['afile_attribute_alt'])) ? $fileRow['afile_attribute_alt'] : $pageTitle; ?>"
            title="<?php echo (!empty($fileRow['afile_attribute_title'])) ? $fileRow['afile_attribute_title'] : $pageTitle; ?>">
    </picture>

</section>
<?php
}

$vtype = $postedData['vtype'] ?? false;

if (array_key_exists('brand_id', $postedData) && $postedData['brand_id'] > 0) {
    $brandImgArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BRAND_IMAGE, $postedData['brand_id'], 0, $siteLangId);
    if (!empty($brandImgArr)) {
        foreach ($brandImgArr as $slideScreen) {
            $uploadedTime = AttachedFile::setTimeParam($slideScreen['afile_updated_at']);
            switch ($slideScreen['afile_screen']) {
                case applicationConstants::SCREEN_MOBILE:
                    $fileRow = CommonHelper::getImageAttributes(AttachedFile::FILETYPE_BRAND_IMAGE, $postedData['brand_id'], 0, 0, applicationConstants::SCREEN_MOBILE);
                    $mobile_url = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'BrandImage', array($postedData['brand_id'], $siteLangId, 'MOBILE', 0, applicationConstants::SCREEN_MOBILE)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg') . ",";
                    break;
                case applicationConstants::SCREEN_IPAD:
                    $fileRow = CommonHelper::getImageAttributes(AttachedFile::FILETYPE_BRAND_IMAGE, $postedData['brand_id'], 0, 0, applicationConstants::SCREEN_IPAD);
                    $tablet_url = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'BrandImage', array($postedData['brand_id'], $siteLangId, 'TABLET', 0, applicationConstants::SCREEN_IPAD)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg') . ",";
                    break;
                case applicationConstants::SCREEN_DESKTOP:
                    $fileRow = CommonHelper::getImageAttributes(AttachedFile::FILETYPE_BRAND_IMAGE, $postedData['brand_id'], 0, 0, applicationConstants::SCREEN_DESKTOP);
                    $desktop_url = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'BrandImage', array($postedData['brand_id'], $siteLangId, 'DESKTOP', 0, applicationConstants::SCREEN_DESKTOP)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg') . ",";
                    break;
            }
        }
    ?>
<section class="bg-shop">

    <picture class="shop-banner">
        <source data-aspect-ratio="4:3" srcset="<?php echo $mobile_url; ?>" media="(max-width: 767px)">
        <source data-aspect-ratio="4:3" srcset="<?php echo $tablet_url; ?>" media="(max-width: 1024px)">
        <source data-aspect-ratio="4:1" srcset="<?php echo $desktop_url; ?>">
        <img data-aspect-ratio="4:1" srcset="<?php echo $desktop_url; ?>"
            alt="<?php echo (!empty($fileRow['afile_attribute_alt'])) ? $fileRow['afile_attribute_alt'] : $pageTitle; ?>"
            title="<?php echo (!empty($fileRow['afile_attribute_title'])) ? $fileRow['afile_attribute_title'] : $pageTitle; ?>">
    </picture>


</section>
<?php } ?>
<?php } ?>

<?php if (isset($pageTitle)) { ?>
<section class="bg-second pt-3 pb-3">
    <div class="container">
        <div class="section-head section--white--head section--head--center mb-0">
            <div class="section__heading">
                <h1 class="mb-0">
                    <?php
                        $keywordStr = '';
                        if (isset($keyword) && !empty($keyword)) {
                            $short_keyword = (mb_strlen($keyword) > 20) ? mb_substr($keyword, 0, 20) . "..." : $keyword;
                            $keywordStr = '<span title="' . $keyword . '" class="search-results">"' . $short_keyword . '"</span>';
                        }
                        echo $pageTitle;
                        ?> <?php echo $keywordStr; ?></h1>
                <?php if (isset($showBreadcrumb) && $showBreadcrumb) { ?>
                <div class="breadcrumbs breadcrumbs--white breadcrumbs--center">
                    <?php $this->includeTemplate('_partial/custom/header-breadcrumb.php'); ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</section>
<?php } ?>

<div id="compare_product_list_js"></div>

<?php $this->includeTemplate('_partial/productsSearchForm.php', array('frmProductSearch' => $frmProductSearch, 'siteLangId' => $siteLangId, 'recordCount' => $recordCount, 'pageTitle' => (isset($pageTitle)) ? $pageTitle : 'Products'), false); ?>
<section class="section">
    <div class="container">
        <div
            class="collection-listing <?php echo FatApp::getConfig('CONF_FILTERS_LAYOUT', FatUtility::VAR_INT, 1) == FilterHelper::LAYOUT_TOP || $postedData['vtype'] == 'map' ? 'filter-top' : 'filter-left'; ?>">
            <?php require_once('filters-layout.php'); ?>
            <main class="collection-content">
                <button class="btn btn-float link__filter btn--filters-control" data-trigger="collection-sidebar">
                    <i class="icn">
                        <svg class="svg">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#filter"
                                href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#filter"></use>
                        </svg>
                    </i>
                </button>
                <div class="row align-items-center justify-content-between flex-column flex-md-row page-sort-wrap">
                    <div class="col mb-3 mb-md-0">
                        <div class="total-products">
                            <h4>
                                <?php echo isset($scollection_name) && !empty($scollection_name) ? $scollection_name : ''; ?>
                                <span class="hide_on_no_product">
                                    <small class="text-muted">
                                        <span id="total_records"><?php echo $recordCount; ?></span>
                                        <?php echo Labels::getLabel('LBL_ITEM(S)', $siteLangId); ?>
                                    </small>
                                </span>
                            </h4>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div id="top-filters" class="page-sort hide_on_no_product">
                            <ul>
                                <!-- <li class="list__item">
                                    <?php if (!(UserAuthentication::isUserLogged()) || (UserAuthentication::isUserLogged() && (User::isBuyer()))) { ?>
                                    <a href="javascript:void(0)" onclick="saveProductSearch()"
                                        class="link btn--filters-control saveSearch-js">
                                        <i class="icn fas fa-file-download d-md-none"></i><span
                                            class="txt"><?php echo Labels::getLabel('LBL_Save_Search', $siteLangId); ?></span></a>
                                    <?php } ?>
                                </li> -->
                                <li>
                                    <?php echo $frmProductSearch->getFieldHtml('sortBy'); ?></li>
                                <li>
                                    <?php echo $frmProductSearch->getFieldHtml('pageSize'); ?></li>

                                <!--<li class="d-none d-md-block">
                                    <div class="list-grid-toggle switch--link-js">
                                        <div class="icon">
                                            <div class="icon-bar"></div>
                                            <div class="icon-bar"></div>
                                            <div class="icon-bar"></div>
                                        </div>
                                    </div>
                                </li> -->
                                <li class="d-none d-md-block">
                                    <div class="views-switch switch--link-js">

                                        <span
                                            class="<?php echo $vtype != 'map' && $vtype != 'list' ? 'active' : ''; ?>">
                                            <a href="javascript:void(0);" title="<?php echo Labels::getLabel('LBL_Grid_View', $siteLangId); ?>" class="listing-grid-toggle--js"
                                                data-viewtype="grid">
                                                <i class="icn icn-show_grid">
                                                    <svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#show_grid"
                                                            href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#show_grid">
                                                        </use>
                                                    </svg>
                                                </i>
                                            </a>
                                        </span>
                                        <span class="<?php echo $vtype == 'list' ? 'active' : ''; ?>">
                                            <a href="javascript:void(0);" title="<?php echo Labels::getLabel('LBL_List_View', $siteLangId); ?>" class="listing-grid-toggle--js"
                                                data-viewtype="list">
                                                <i class="icn icn-show_list">
                                                    <svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#show_list"
                                                            href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#show_list">
                                                        </use>
                                                    </svg>
                                                </i>
                                            </a>
                                        </span>
                                        <?php if ($vtype) { ?>
                                        <span class="<?php echo $vtype == 'map' ? 'active' : ''; ?>">
                                            <a href="javascript:void(0);" title="<?php echo Labels::getLabel('LBL_Map_View', $siteLangId); ?>" class="listing-grid-toggle--js"
                                                data-viewtype="map">
                                                <i class="icn icn-show_list">
                                                    <svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#show_list"
                                                            href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#show_list">
                                                        </use>
                                                    </svg>
                                                </i>
                                            </a>
                                        </span>
                                        <?php } ?>

                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <?php if (isset($postedData['vtype']) && $postedData['vtype'] == "map") { ?>
                <div class="interactive-stores">
                    <div class="interactive-stores__map">

                        <div class="map-loader is-loading">
                            <svg version="1.1" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="50px" height="50px"
                                viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve">
                                <path fill="#fff"
                                    d="M43.935,25.145c0-10.318-8.364-18.683-18.683-18.683c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615c8.072,0,14.615,6.543,14.615,14.615H43.935z">
                                    <animateTransform attributeType="xml" attributeName="transform" type="rotate"
                                        from="0 25 25" to="360 25 25" dur="0.6s" repeatCount="indefinite">
                                    </animateTransform>
                                </path>
                            </svg>
                        </div>
                        <div class="canvas-map" id="productMap--js"> </div>

                    </div>
                    <?php } else { ?>

                    <?php } ?>
                    <?php
                        $productsData = array(	
                            'products' => $products,	
                            'page' => $page,	
                            'pageCount' => $pageCount,	
                            'postedData' => $postedData,	
                            'recordCount' => $recordCount,	
                            'siteLangId' => $siteLangId,	
                            'colMdVal' => 3,	
                            'compProdCount' => $compProdCount,	
                            'comparedProdSpecCatId' => $comparedProdSpecCatId,	
                        );
                        $this->includeTemplate('products/products-list.php', $productsData, false);
                        ?>

            </main>
        </div>
    </div>
</section>
<section>
    <div class="container">
        <div class="row">
            <div class="col-md-3 col--left col--left-adds">
                <div class="wrapper--adds">
                    <div class="grids" id="searchPageBanners">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<div class="gap"></div>
<script type="text/javascript">
$(document).ready(function() {
    $currentPageUrl = `<?php echo html_entity_decode($canonicalUrl, ENT_QUOTES, 'utf-8'); ?>`;
    $productSearchPageType = '<?php echo $productSearchPageType; ?>';
    $recordId = <?php echo $recordId; ?>;
    /* bannerAdds('<?php echo $bannerListigUrl; ?>'); */
    loadProductListingfilters(document.frmProductSearch);
});
</script>
<?php if (FatApp::getConfig("CONF_ENABLE_PRODUCT_COMPARISON", FatUtility::VAR_INT, 1) && 0 < $compProdCount) {	
?>
<script type="text/javascript">
var data = 'detail_page=1';
fcom.ajax(fcom.makeUrl('CompareProducts', 'listing'), data, function(res) {
    $("#compare_product_list_js").html(res);
    $('body').addClass('is-compare-visible');
});
</script>
<?php } ?>