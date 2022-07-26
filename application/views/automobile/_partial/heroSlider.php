<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section p-0 home">
    <div class="home__slider js--hero-slider">
        <?php
        $uploadPath = UrlHelper::generateFullFileUrl() . CONF_UPLOADS_FOLDER_NAME .'/'. AttachedFile::FILETYPE_HOME_PAGE_BANNER_PATH;
        $attachmentArr = isset($slides['slidesImages']) ?  $slides['slidesImages'] : [];
        unset($slides['slidesImages']);
        foreach ($slides as $slide) {
            $desktop_url = '';
            $tablet_url = '';
            $mobile_url = '';
            $haveUrl = ($slide['slide_url'] != '') ? true : false;
            $defaultUrl = '';
            $slideArr = (isset($attachmentArr[$slide['slide_id']])) ? $attachmentArr[$slide['slide_id']] : [];
            if (!$slideArr) {
                continue;
            } else {
                foreach ($slideArr as $slideScreen) {
                    /* $uploadedTime = AttachedFile::setTimeParam($slideScreen['afile_updated_at']); */
                    switch ($slideScreen['afile_screen']) {
                        case applicationConstants::SCREEN_MOBILE:
                            $mobile_url = $uploadPath. $slideScreen['afile_physical_path']; 
                            break;
                        case applicationConstants::SCREEN_IPAD:
                           $tablet_url = $uploadPath. $slideScreen['afile_physical_path']; 
                           break;
                        case applicationConstants::SCREEN_DESKTOP:
                            $defaultUrl = $uploadPath . $slideScreen['afile_physical_path']; 
                            
                            $desktop_url = $defaultUrl . ",";
                            break;
                    }
                }
            }

            if ($defaultUrl == '') {
                $defaultUrl = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'slide', array($slide['slide_id'], applicationConstants::SCREEN_DESKTOP, $siteLangId, 'DESKTOP')), CONF_IMG_CACHE_TIME, '.jpg');
            }

            if ($haveUrl) {
                if ($slide['promotion_id'] > 0) {
                    $slideUrl = UrlHelper::generateUrl('slides', 'track', array($slide['slide_id']));
                } else {
                    $slideUrl = CommonHelper::processUrlString($slide['slide_url']);
                }
            }

            $out = "";
            if ($haveUrl) {
                $out = '<a target="' . $slide['slide_target'] . '" href="' . $slideUrl . '">';
            }

            $out .= '<div class="home__slider--item">';
            $out .= '<div class="home__slider--item">
				<picture>
					<source data-aspect-ratio="4:3" srcset="' . rtrim($mobile_url, ',') . '" media="(max-width: 767px)">
					<source data-aspect-ratio="4:3" srcset="' . rtrim($tablet_url, ',') . '" media="(max-width: 1024px)">
					<source data-aspect-ratio="2:1" srcset="' . rtrim($desktop_url, ',') . '">
					<img data-aspect-ratio="2:1" src="' . rtrim($desktop_url, ',') . '" alt="">
				</picture>
			</div>';

            $out .= '</div>';

            if ($haveUrl) {
                $out .= '</a>';
            }

            echo $out;
            if (isset($slide['promotion_id']) && $slide['promotion_id'] > 0) {
                Promotion::updateImpressionData($slide['promotion_id']);
            }
        }
        ?>
    </div>
	<?php 
	$dataToSend = ['searchForm' => $searchForm, 'siteLangId' =>$siteLangId, 'isHome' => true]; 
	echo $this->includeTemplate('_partial/header/site-search-form.php', $dataToSend);
	?>
</section>
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