<div class="modal-dialog modal-dialog-centered" role="document" id="custom-catalog-info-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?php echo Labels::getLabel('LBL_Custom_catalog_info', $siteLangId); ?></h5>

            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">

            <div class="white--bg padding20">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xm-12 clearfix">
                        <div id="img-static" class="product-detail-gallery">
                            <img src="<?php echo CommonHelper::generateUrl('image', 'customProduct', array($product['preq_id'], 'MEDIUM', 0, 0, $siteLangId)) ?>">
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xm-12">
                        <div class="product-description">
                            <div class="product-description-inner">
                                <div class="products__title"><?php echo $product['product_name']; ?></div>
                                <div class="gap"></div>
                                <div class="cms">
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th><?php echo Labels::getLabel('LBL_Category', $siteLangId); ?>:</th>
                                                <td><?php echo $product['prodcat_name']; ?></td>
                                            </tr>
                                            <tr>
                                                <th><?php echo Labels::getLabel('LBL_Brand', $siteLangId); ?>:</th>
                                                <td><?php echo ($product['brand_name']) ? $product['brand_name'] : Labels::getLabel('LBL_N/A', $siteLangId); ?></td>
                                            </tr>
                                            <tr>
                                                <th><?php echo Labels::getLabel('LBL_Product_Model', $siteLangId); ?>:</th>
                                                <td><?php echo $product['product_model']; ?></td>
                                            </tr>
                                            <?php if(FatApp::getConfig("CONF_ALLOW_SALE", FatUtility::VAR_INT, 0)) { 
                                                if($product['product_min_selling_price'] > 0) { ?>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Minimum_Selling_Price', $siteLangId); ?>:</th>
                                                    <td><?php echo CommonHelper::displayMoneyFormat($product['product_min_selling_price']); ?></td>
                                                </tr>
                                                <?php }
                                                $saleTaxArr = Tax::getSaleTaxCatArr($siteLangId);
                                                if (array_key_exists($product['ptt_taxcat_id'], $saleTaxArr)) { ?>
                                                    <tr>
                                                        <th><?php echo Labels::getLabel('LBL_Tax_Category', $siteLangId); ?>:</th>
                                                        <td><?php echo $saleTaxArr[$product['ptt_taxcat_id']]; ?></td>
                                                    </tr>
                                                <?php } 
                                            } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>