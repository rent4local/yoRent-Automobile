<?php defined('SYSTEM_INIT') or die('Invalid Usage');
	$shop_city = $shop['shop_city'];
	$shop_state = ( strlen($shop['shop_city']) > 0 ) ? ', '. $shop['shop_state_name'] : $shop['shop_state_name'];
	$shop_country = ( strlen($shop_state) > 0 ) ? ', '.$shop['shop_country_name'] : $shop['shop_country_name'];
	$shopLocation = $shop_city . $shop_state. $shop_country;

	$frm->setFormTagAttribute('class','form form--horizontal');
	$frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
	$frm->developerTags['fld_default_col'] = 12;
    $frm->setFormTagAttribute('onSubmit', 'setUpShopSpam(this); return false;');
    
    $btnFld = $frm->getField('btn_submit');
    if (null != $btnFld) {
        $btnFld->addFieldTagAttribute('class', 'btn btn-brand');
    }
?>

<div id="body" class="body">
<?php
        $variables= array(
            'shop' => $shop, 
            'siteLangId' => $siteLangId,
            'template_id' => $template_id,
            'collectionData' => [],
            'action' => $action,
            'shopTotalReviews' => $shopTotalReviews,
            'shopRating' => $shopRating, 
            'socialPlatforms' => $socialPlatforms 
        );
        $this->includeTemplate('shops/shop-header.php', $variables, false);
    ?>
 
<!--  <div class="bg-second pt-3 pb-3">
      <div class="container">
        <div class="row align-items-center justify-content-between">
          <div class="col-md-8">
          
          <div class="section-head section--white--head mb-0">
						<div class="section__heading">
							<h1><?php echo $shop['shop_name']; ?></h1>
							<p><?php echo $shopLocation; ?> <?php echo Labels::getLabel('LBL_Opened_on', $siteLangId); ?> <?php echo FatDate::format($shop['shop_created_on']); ?></p>
                        </div>
                    </div>
          </div>
          <div class="col-md-auto col-sm-auto"><a href="<?php echo UrlHelper::generateUrl('Shops', 'View', array($shop['shop_id'])); ?>" class="btn btn-outline-white btn-sm backToStore-js"><?php echo Labels::getLabel('LBL_Back_to_Shop', $siteLangId); ?></a></div>
        </div>
      </div>
    </div> -->
 
  <section class="section">
    <div class="container">
    <div class="row justify-content-center">
    <div class="col-md-7">       
            <div class="section__head">
              <h4><?php echo Labels::getLabel('LBL_Why_are_you_reporting_this_shop_as_spam', $siteLangId); ?></h4>
            </div>
            <div class="">
              <div class="bg-gray rounded p-5"> <?php echo $frm->getFormHtml(); ?> </div>
            </div>
       
    </div>
    </div>
    </div>
  </section>
  <div class="gap"></div>
</div>
