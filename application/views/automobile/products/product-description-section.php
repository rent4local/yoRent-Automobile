<div class="detail__body">
    <?php if (trim($product['product_description']) != '') { ?>
        <div class="detail-block cms">
            <div class="product-details">
                <h2 class="block-title"><?php echo Labels::getLabel('LBL_Description', $siteLangId); ?></h2>
                <div class="product-details__txt">
                    <?php echo CommonHelper::renderHtml($product['product_description']); ?>
                </div>
                <?php /* if (strlen($product['product_description']) > 300) { ?>
            <a href="javascript:void(0);"
                class="readmore--js"><?php echo Labels::getLabel('LBL_Read_More', $siteLangId); ?></a>
            <?php }  */ ?>
            </div>
        </div>
    <?php } ?>


    <?php /* <div class="detail-block cms">
      <h2>Meet your host</h2>
      <div class="host">
      <div class="host__img">
      <img src="/images/automobile/avtar-img.png"/>
      </div>
      <div class="host__detail">
      <h5>Katelyn</h5>
      <p>We love getting outdoors with our two daughters (ages 6 and 1) and seeing their faces light up at discovering new things in nature. Our RV is our ticket to making these outdoor adventures as comfortable, safe and epic as possible. We'd love to share with you about our past travels and our ever-expanding wish-list of future destinations!</p>
      </div>
      </div>
      </div> */ ?>
    <?php include(CONF_THEME_PATH_WITH_THEME_NAME . 'products/product-specifications.php'); ?>
    <?php $youtube_embed_code = UrlHelper::parseYoutubeUrl($product["product_youtube_video"]); ?>
    <?php if (trim($youtube_embed_code) != '') { ?>
        <div class="detail-block cms">
            <div class="mb-4 video-wrapper">
                <iframe width="100%" height="315" src="//www.youtube.com/embed/<?php echo $youtube_embed_code ?>" allowfullscreen></iframe>
            </div>
        </div>
    <?php } ?>

    <?php if ($shop['shop_payment_policy'] != '') {

        $shopPolicy = $shop['shop_payment_policy'];
        $shopPolicyTxt = "<span class='lessText'>" . CommonHelper::truncateCharacters($shopPolicy, 300, '', '', true) . "</span>";
        if (strlen($shopPolicyTxt) > 300) {
            $shopPolicyTxt .= "<span class='moreText hidden'>";
            $shopPolicyTxt .= nl2br($shopPolicy) . "</span>";
            $shopPolicyTxt .= "</br><a href='javascript:void(0);' class='link-plus readmore--js readMore'>" . Labels::getLabel('LBL_Read_More', $siteLangId) . "</a>";
        }
    ?>
        <div class="detail-block cms description">
            <h5><?php echo Labels::getLabel('LBL_Payment_Policy', $siteLangId) ?></h5>
            <div class="product-details__text"><?php echo nl2br($shopPolicyTxt); ?></div>
            <?php /* if (strlen($shop['shop_payment_policy']) > 300) { ?>
                <a href="javascript:void(0);" class="link-plus readmore--js">
                    <?php echo Labels::getLabel('LBL_Read_More', $siteLangId); ?>
                </a>
            <?php } */ ?>
        </div>
    <?php } ?>

    <?php if ($shop['shop_delivery_policy'] != '') { 
        $deliveryPolicy = $shop['shop_delivery_policy'];
        $deliveryPolicyTxt = "<span class='lessText'>" . CommonHelper::truncateCharacters($deliveryPolicy, 300, '', '', true) . "</span>";
        if (strlen($deliveryPolicyTxt) > 300) {
            $deliveryPolicyTxt .= "<span class='moreText hidden'>";
            $deliveryPolicyTxt .= nl2br($deliveryPolicy) ."</span>";
            $deliveryPolicyTxt .= "</br><a href='javascript:void(0);' class='link-plus readmore--js readMore'>" . Labels::getLabel('LBL_Read_More', $siteLangId) . "</a>";
        }
        ?>
        <div class="detail-block cms description">
            <h5><?php echo Labels::getLabel('LBL_Delivery_Policy', $siteLangId) ?></h5>
            <div class="product-details__text"><?php echo nl2br($deliveryPolicyTxt); ?></div>
            <?php /* if (strlen($shop['shop_delivery_policy']) > 300) { ?>
                <a href="javascript:void(0);" class="link-plus readmore--js"><?php echo Labels::getLabel('LBL_Read_More', $siteLangId); ?></a>
            <?php } */ ?>
        </div>
    <?php } ?>

    <?php if ($shop['shop_refund_policy'] != '') { 
        $refundPolicy = $shop['shop_refund_policy'];
        $refundPolicyTxt = "<span class='lessText'>" . CommonHelper::truncateCharacters($refundPolicy, 300, '', '', true) . "</span>";
        if (strlen($refundPolicyTxt) > 300) {
            $refundPolicyTxt .= "<span class='moreText hidden'>";
            $refundPolicyTxt .= nl2br($refundPolicy) ."</span>";
            $refundPolicyTxt .= "</br><a href='javascript:void(0);' class='link-plus readmore--js readMore'>" . Labels::getLabel('LBL_Read_More', $siteLangId) . "</a>";
        }
        ?>
        <div class="detail-block cms description">
            <h5><?php echo Labels::getLabel('LBL_Refund_Policy', $siteLangId) ?></h5>
            <div class="product-details__text"><?php echo nl2br($refundPolicyTxt); ?></div>
            <?php /* if (strlen($shop['shop_refund_policy']) > 300) { ?>
                <a href="javascript:void(0);" class="link-plus readmore--js"><?php echo Labels::getLabel('LBL_Read_More', $siteLangId); ?></a>
            <?php } */ ?>
        </div>
    <?php } ?>

    <?php if (!empty($product['selprodComments'])) { 
        $comments = $product['selprodComments'];
        $commentsTxt = "<span class='lessText'>" . CommonHelper::truncateCharacters($comments, 300, '', '', true) . "</span>";
        if (strlen($commentsTxt) > 300) {
            $commentsTxt .= "<span class='moreText hidden'>";
            $commentsTxt .= nl2br($refundPolicy) ."</span>";
            $commentsTxt .= "</br><a href='javascript:void(0);' class='link-plus readmore--js readMore'>" . Labels::getLabel('LBL_Read_More', $siteLangId) . "</a>";
        }
        ?>
        <div class="detail-block cms description">
            <h5 class="description-title "><?php echo Labels::getLabel('LBL_Extra_comments', $siteLangId); ?></h5>
            <div class="product-details__text">
                <?php echo CommonHelper::displayNotApplicable($siteLangId, nl2br($commentsTxt)); ?>
            </div>
            <?php /* if (strlen($product['selprodComments']) > 300) { ?>
                <a href="javascript:void(0);" class="link-plus readmore--js"><?php echo Labels::getLabel('LBL_Read_More', $siteLangId); ?></a>
            <?php } */ ?>
        </div>
    <?php } ?>
</div>