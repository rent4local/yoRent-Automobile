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

$desktop_url = UrlHelper::generateFileUrl().'images/defaults/3/slider-default.png';
$tablet_url = UrlHelper::generateFileUrl().'images/defaults/3/slider-default.png';
$mobile_url = UrlHelper::generateFileUrl().'images/defaults/3/slider-default.png';
$compProdCount = (!isset($compProdCount)) ? 0 : $compProdCount;
$compProdCount = (!isset($compProdCount)) ? 0 : $compProdCount;

if (!isset($postedData['shop_id']) || (isset($postedData['shop_id']) && 1 > FatUtility::int($postedData['shop_id']))) {
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
        
        }
    } else if(isset($banner) && !empty($banner)) {
        $uploadPath = UrlHelper::generateFileUrl().CONF_UPLOADS_FOLDER_NAME .'/'. AttachedFile::FILETYPE_BANNER_PATH;
        $slideArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BANNER, $banner['banner_id'], 0, $siteLangId);
        if (!empty($slideArr)) {
            foreach ($slideArr as $slideScreen) {
                $uploadedTime = AttachedFile::setTimeParam($slideScreen['afile_updated_at']); 
                switch ($slideScreen['afile_screen']) {
                    case applicationConstants::SCREEN_MOBILE:
                        /* $mobile_url = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Banner', 'image', array($banner['banner_id'], Collections::TYPE_BANNER_LAYOUT_DETAIL, $siteLangId, applicationConstants::SCREEN_MOBILE, $banner['banner_position'])) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg') . ""; */
                        
                        if (!file_exists(CONF_UPLOADS_PATH.'/'.AttachedFile::FILETYPE_BANNER_PATH.$slideScreen['afile_physical_path'])) {
                            $mobile_url = UrlHelper::generateFullFileUrl().'/images/defaults/3/slider-default.png';
                        } else {
                            $mobile_url = $uploadPath. $slideScreen['afile_physical_path']; 
                        }
                        
                        
                        break;
                    case applicationConstants::SCREEN_IPAD:
                        /* $tablet_url = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Banner', 'image', array($banner['banner_id'], Collections::TYPE_BANNER_LAYOUT_DETAIL, $siteLangId, applicationConstants::SCREEN_IPAD, $banner['banner_position'])) . $uploadedTime) . ","; */
                        
                        if (!file_exists(CONF_UPLOADS_PATH.'/'.AttachedFile::FILETYPE_BANNER_PATH.$slideScreen['afile_physical_path'])) {
                            $tablet_url = UrlHelper::generateFullFileUrl().'/images/defaults/3/slider-default.png';
                        } else {
                            $tablet_url = $uploadPath. $slideScreen['afile_physical_path']; 
                        }
                        
                        
                        break;
                    case applicationConstants::SCREEN_DESKTOP:
                        /* $defaultImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Banner', 'image', array($banner['banner_id'], Collections::TYPE_BANNER_LAYOUT_DETAIL, $siteLangId, applicationConstants::SCREEN_DESKTOP, $banner['banner_position'])) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                        $desktop_url = $defaultImgUrl . ""; */
                        if (!file_exists(CONF_UPLOADS_PATH.'/'.AttachedFile::FILETYPE_BANNER_PATH.$slideScreen['afile_physical_path'])) {
                            $desktop_url = UrlHelper::generateFullFileUrl().'/images/defaults/3/slider-default.png';
                        } else {
                            $desktop_url = $uploadPath. $slideScreen['afile_physical_path']; 
                        }
                        break;
                }
            }
            $screenTypeArr = array_column($slideArr, 'afile_screen');
            if (!in_array(applicationConstants::SCREEN_MOBILE, $screenTypeArr)) {
                $mobile_url = $desktop_url;
            }
            if (!in_array(applicationConstants::SCREEN_IPAD, $screenTypeArr)) {
                $tablet_url = $desktop_url;
            }
            
        }
    } else if(array_key_exists('brand_id', $postedData) && $postedData['brand_id'] > 0) {
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
        }
    }
?>
    <section class="bg-shop">
        <picture class="shop-banner">
            <source data-aspect-ratio="4:3" srcset="<?php echo $mobile_url; ?>" media="(max-width: 767px)">
            <source data-aspect-ratio="4:3" srcset="<?php echo $tablet_url; ?>" media="(max-width: 1024px)">
            <source data-aspect-ratio="4:1" srcset="<?php echo $desktop_url; ?>">
            <img data-aspect-ratio="4:1" src="<?php echo $desktop_url; ?>">
        </picture>
        <?php
        /* $dataToSend = ['searchForm' => $searchForm, 'siteLangId' => $siteLangId, 'postedData' => $postedData];
        echo $this->includeTemplate('_partial/header/site-search-form.php', $dataToSend); */
        ?>
    </section>
<?php } ?>
<?php $vtype = $postedData['vtype'] ?? false; ?>

<?php if (isset($pageTitle)) { ?>
<section class="bg-brand pt-3 pb-3">
    <div class="container">
        <div class="section-head section--white--head section--head--center mb-0">
            <div class="section__heading mb-0">
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
<section class="section pt-5">
    <div class="container container--fluid">
        <div class="collection-listing <?php echo FatApp::getConfig('CONF_FILTERS_LAYOUT', FatUtility::VAR_INT, 1) == FilterHelper::LAYOUT_TOP || (isset($postedData['vtype'])) && $postedData['vtype'] == 'map' ? 'filter-top' : 'filter-left'; ?>">
            <?php require_once('filters-layout.php'); ?>
            <main class="collection-content">
                <div class="m-tb-3">
                    <div class="filtered" id="filters" style="display:none;">
                        <div class="filtered__item">
                            <a href="javascript:void(0)" class="remove resetAll" id="resetAll" onClick="resetListingFilter()"><?php echo Labels::getLabel('LBL_Clear_All', $siteLangId); ?></a>
                        </div>
                    </div>
                </div>
                <button class="btn btn-float link__filter btn--filters-control" data-trigger="collection-sidebar">
                    <i class="icn">
                        <svg class="svg">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#filter" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#filter"></use>
                        </svg>
                    </i>
                </button>
                <div class="row justify-content-between d-md-column page-sort-wrap">
                    <div class="col m-tb-3">
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
                    <div class="col-auto m-tb-3">
                        <div id="top-filters" class="page-sort hide_on_no_product">
                            <ul>
                                <?php /* <li class="list__item">
                                    <?php if (!(UserAuthentication::isUserLogged()) || (UserAuthentication::isUserLogged() && (User::isBuyer()))) { ?>
                                    <a href="javascript:void(0)" onclick="saveProductSearch()"
                                        class="btn btn-brand btn--filters-control saveSearch-js">
                                        <i class="icn fas fa-file-download d-md-none"></i><span
                                            class="txt"><?php echo Labels::getLabel('LBL_Save_Search', $siteLangId); ?></span></a>
                                    <?php } ?>
                                </li> */ ?>
                                <li>
                                    <?php
                                    $fld = $frmProductSearch->getField('sortBy');
                                    $fld->setWrapperAttribute('class', 'custom-select sorting-select');
                                    echo $frmProductSearch->getFieldHtml('sortBy'); ?>
                                </li>
                                <li>
                                    <?php
                                    $fld = $frmProductSearch->getField('pageSize');
                                    $fld->setWrapperAttribute('class', 'custom-select sorting-select');
                                    echo $frmProductSearch->getFieldHtml('pageSize'); ?>
                                </li>
                                <li>
                                    <div class="views-switch d-flex align-items-center list-grid--toggle switch--link-js">
                                        <span class="<?php echo $vtype != 'map' && $vtype != 'list' ? 'active' : ''; ?>">
                                            <a href="javascript:void(0);" title="<?php echo Labels::getLabel('LBL_Grid_View', $siteLangId); ?>" class="listing-grid-toggle--js" data-viewtype="grid">
                                            <i class="icn">
                                                    <svg class="svg" width="28" height="28">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#grid-view"></use>
                                                    </svg>
                                                </i>
                                            </a>
                                        </span>
                                        <span class="d-block-down-lg <?php echo $vtype == 'list' ? 'active' : ''; ?>">
                                            <a href="javascript:void(0);" title="<?php echo Labels::getLabel('LBL_List_View', $siteLangId); ?>" class="listing-grid-toggle--js" data-viewtype="list">
                                            <i class="icn">
                                                    <svg class="svg" width="28" height="28">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#list-view"></use>
                                                    </svg>
                                                </i> 
                                            </a>
                                        </span>
                                        <?php  if ($vtype) { ?>
                                            <span class="<?php echo $vtype == 'map' ? 'active' : ''; ?>">
                                                <a href="javascript:void(0);" title="<?php echo Labels::getLabel('LBL_Map_View', $siteLangId); ?>" class="listing-grid-toggle--js" data-viewtype="map">
                                                    <i class="icn icn-show_list">
                                                        <svg class="svg">
                                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#location">
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
                                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="50px" height="50px" viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve">
                                    <path fill="#fff" d="M43.935,25.145c0-10.318-8.364-18.683-18.683-18.683c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615c8.072,0,14.615,6.543,14.615,14.615H43.935z">
                                        <animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="0.6s" repeatCount="indefinite">
                                        </animateTransform>
                                    </path>
                                </svg>
                            </div>
                            <div class="canvas-map" id="productMap--js"> </div>

                        </div>
                    <?php } else { ?>
                        <div class="">
                        <?php } ?>
                        <?php
                        $productsData = array(
                            'products' => $products,
                            'prodCatAttributes' => $prodCatAttributes,
                            'prodCustomFldsData' => $prodCustomFldsData,
                            'page' => $page,
                            'pageCount' => $pageCount,
                            'postedData' => $postedData,
                            'recordCount' => $recordCount,
                            'siteLangId' => $siteLangId,
                            'colMdVal' => 3,
                            'compProdCount' => $compProdCount,
                            'comparedProdSpecCatId' => (isset($comparedProdSpecCatId)) ? $comparedProdSpecCatId : 0,
                            'pageSizeArr' => $pageSizeArr,
                            'pageSize' => $pageSize,
                        );
                        $this->includeTemplate('products/products-list.php', $productsData, false);
                        ?>
                        </div>
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
<?php if (isset($recordCount) && 12 > $recordCount) { ?>
    <style>select[name="pageSizeSelect"] {display : none;}</style>
<?php } ?>

<script type="text/javascript">
    $(document).ready(function() {
        $currentPageUrl = `<?php echo html_entity_decode($canonicalUrl, ENT_QUOTES, 'utf-8'); ?>`;
        $productSearchPageType = '<?php echo $productSearchPageType; ?>';
        $recordId = <?php echo $recordId; ?>;
        /* bannerAdds('<?php echo $bannerListigUrl; ?>'); */
        loadProductListingfilters(document.frmProductSearch);
    });
</script>
<?php if (FatApp::getConfig("CONF_ENABLE_PRODUCT_COMPARISON", FatUtility::VAR_INT, 1) && 0 < $compProdCount) { ?>
    <script type="text/javascript">
        var data = 'detail_page=1';
        fcom.ajax(fcom.makeUrl('CompareProducts', 'listing'), data, function(res) {
            $("#compare_product_list_js").html(res);
            $('body').addClass('is-compare-visible');
        });
    </script>
<?php } ?>
<style>
    .remove {
        margin: 0 5px;
    }
</style>