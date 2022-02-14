<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="collection-hero">
    <div class="hero-slider js--hero-slider">
        <?php
        foreach ($slides as $slide) {
            $desktop_url = '';
            $tablet_url = '';
            $mobile_url = '';
            $haveUrl = ($slide['slide_url'] != '') ? true : false;
            $defaultUrl = '';
            $slideArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_HOME_PAGE_BANNER, $slide['slide_id'], 0, $siteLangId);
            if (!$slideArr) {
                continue;
            } else {
                foreach ($slideArr as $slideScreen) {
                    $uploadedTime = AttachedFile::setTimeParam($slideScreen['afile_updated_at']);
                    switch ($slideScreen['afile_screen']) {
                        case applicationConstants::SCREEN_MOBILE:
                            $mobile_url = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'slide', array($slide['slide_id'], applicationConstants::SCREEN_MOBILE, $siteLangId, 'MOBILE')) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg') . ",";
                            break;
                        case applicationConstants::SCREEN_IPAD:
                            $tablet_url = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'slide', array($slide['slide_id'], applicationConstants::SCREEN_IPAD, $siteLangId, 'TABLET')) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg') . ",";
                            break;
                        case applicationConstants::SCREEN_DESKTOP:
                            $defaultUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'slide', array($slide['slide_id'], applicationConstants::SCREEN_DESKTOP, $siteLangId, 'DESKTOP')) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                            $desktop_url = $defaultUrl . ",";
                            break;
                    }
                }
            }

            if ($defaultUrl == '') {
                $defaultUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'slide', array($slide['slide_id'], applicationConstants::SCREEN_DESKTOP, $siteLangId, 'DESKTOP')), CONF_IMG_CACHE_TIME, '.jpg');
            }

            $out = '<div class="hero-slider__item">';
            if ($haveUrl) {
                if ($slide['promotion_id'] > 0) {
                    $slideUrl = UrlHelper::generateUrl('slides', 'track', array($slide['slide_id']));
                } else {
                    $slideUrl = CommonHelper::processUrlString($slide['slide_url']);
                }
            }
            if ($haveUrl) {
                $out .= '<a target="' . $slide['slide_target'] . '" href="' . $slideUrl . '">';
            }
            $out .= '<div class="hero_media">
				<picture>
					<source data-aspect-ratio="4:3" srcset="' . rtrim($mobile_url, ',') . '" media="(max-width: 767px)">
					<source data-aspect-ratio="4:3" srcset="' . rtrim($tablet_url, ',') . '" media="(max-width: 1024px)">
					<source data-aspect-ratio="2:1" srcset="' . rtrim($desktop_url, ',') . '">
					<img data-aspect-ratio="2:1" src="' . rtrim($desktop_url, ',') . '" alt="">
				</picture>
			</div>';
            if ($haveUrl) {
                $out .= '</a>';
            }
            $out .= '</div>';
            echo $out;
            if (isset($slide['promotion_id']) && $slide['promotion_id'] > 0) {
                Promotion::updateImpressionData($slide['promotion_id']);
            }
        }
        ?>
    </div>
    <script>
        $('.js--hero-slider').slick({
            autoplay: true,
            autoplaySpeed: 8000,
            draggable: true,
            arrows: false,
            dots: true,
            fade: true,
            speed: 900,
            infinite: true,
            cssEase: 'cubic-bezier(0.7, 0, 0.3, 1)',
            touchThreshold: 100
        });
    </script>
    <div class="site-search">
        <div class="container">
            <div class="search__form">
                <?php
                $searchForm->setFormTagAttribute('onSubmit', 'submitSiteSearch(this, 12); return(false);');
                $keywordFld = $searchForm->getField('keyword');
                $keywordFld->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Keyword_Search', $siteLangId));

                $locFld = $searchForm->getField('location');
                $locFld->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Location_Search', $siteLangId));

                $locFld = $searchForm->getField('rentaldates');
                $locFld->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Add_Dates', $siteLangId));
                ?>

                <div class="search__form-wrapper">
                    <div class="search-head">
                        <h1 class="search__form-heading"><?php echo Labels::getLabel("LBL_Search_for_products", $siteLangId); ?></h1>
                        <p class="pb-3"><?php echo Labels::getLabel("LBL_Try breezy, fun and more vibrant looks this season", $siteLangId); ?></p>
                    </div>
                    <div class="search-body">
                        <?php echo $searchForm->getFormTag(); ?>
                        <div class="site-search-form site-search-form-home">
                            <ul>
                                <li>
                                    <div class="form-group">
                                        <label class="field_label"><?php echo $searchForm->getField('keyword')->getCaption(); ?></label>
                                        <?php echo $searchForm->getFieldHtml('keyword'); ?>
                                    </div>

                                </li>
                                <li>
                                    <div class="form-group">
                                        <label class="field_label"><?php echo $searchForm->getField('location')->getCaption(); ?></label>
                                        <?php echo $searchForm->getFieldHtml('location'); ?>
                                    </div>
                                </li>
                                <li>
                                    <div class="form-group">
                                        <label class="field_label"><?php echo $searchForm->getField('rentaldates')->getCaption(); ?></label>
                                        <?php echo $searchForm->getFieldHtml('rentaldates'); ?>
                                        <?php echo $searchForm->getFieldHtml('rentalstart'); ?>
                                        <?php echo $searchForm->getFieldHtml('rentalend'); ?>

                                    </div>
                                </li>
                                <li>
                                    <div class="form-group">
                                        <?php echo $searchForm->getFieldHtml('searchButton'); ?>

                                    </div>
                                </li>

                            </ul>


                        </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>