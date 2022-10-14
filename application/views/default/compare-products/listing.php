<?php if (!empty($productsDetail)) { ?>
    <section class="prod--compare-bar">
        <a href="javascript:void(0)" class="compare-toggle compare-toggle-js">
            <img src="<?php echo CONF_WEBROOT_URL; ?>images/retina/compare.svg" width="20px" height="20px" alt="">
            <?php echo Labels::getLabel('LBL_Compare', $siteLangId); ?>
            <span class="compare-count"> <?php echo count($productsDetail); ?> </span> </a>
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <div class="col-xl-10">
                    <div class="wrap-compare-items">
                        <?php
                        $rentalTypeArr = applicationConstants::rentalTypeArr($siteLangId);
                        foreach ($productsDetail as $selProdId => $productDetail) {
                            ?>
                            <div class="compare-items">
                                <img title="<?php echo $productDetail['product_name']; ?>" src="<?php echo CommonHelper::generateUrl('Image', 'product', array($productDetail['selprod_product_id'], 'THUMB80', $productDetail['selprod_product_id'])); ?>" />
                                <div class="prod-detail">
                                    <div class="product-heading">
                                        <a title="<?php echo $productDetail['selprod_title']; ?>" href="<?php echo CommonHelper::generateUrl('Products', 'View', array($selProdId)); ?>" tabindex="0"><?php echo $productDetail['selprod_title']; ?></a>
                                    </div>
                                    <div class="product-price">
                                        <div class="product-prices-per-day">
                                            <?php echo CommonHelper::displayMoneyFormat($productDetail['sprodata_rental_price']); ?>
                                            <span class="slash">/ <?php echo $rentalTypeArr[$productDetail['sprodata_duration_type']]; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="prod--cancel">
                                    <a href="javascript:void(0)" class="close-layer close-layer--sm" onclick="removeFromCompareList('<?php echo $selProdId; ?>', 1)"></a>
                                </div>
                            </div>
                        <?php } ?>
                        <?php
                        if (count($productsDetail) < CompareProduct::COMPARE_PRODUCTS_LIMIT) {
                            for ($i = 1; $i <= CompareProduct::COMPARE_PRODUCTS_LIMIT - count($productsDetail); $i++) {
                                ?>
                                <div class="compare-items compare_item_<?php echo $i; ?>--js" data-id="<?php echo $i; ?>">
                                    <input type="text" name="search_product" placeholder="<?php echo Labels::getLabel('LBL_Add_new_product', $siteLangId); ?>" id="search_product_js_<?php echo $i; ?>" />
                                </div>
                                <?php
                            }
                        }
                        ?>

                    </div>
                </div>
                <!-- d-flex justify-content-md-end-->
                <div class="col-xl-2 mt-3 mt-xl-0">
                    <div class="btn-groups">
                        <a href="<?php echo UrlHelper::generateFullUrl('CompareProducts', 'index'); ?>" onclick="add_to_compare()" class="btn btn-brand "><?php echo Labels::getLabel('LBL_Compare', $siteLangId); ?>&nbsp
                            <span class="compare-count"><?php echo count($productsDetail); ?></span>
                        </a>
                        <a href="javascript:void(0)" onclick="clearCompareList()" class="btn btn-outline-brand">
                            <?php echo Labels::getLabel('LBL_Clear', $siteLangId); ?></a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <script type="text/javascript">
        $('input[name=\'search_product\']').on('keypress', function () {
            var parentId = $(this).parents('.compare-items').data('id');
            var divToAppend = ".compare_item_" + parentId + "--js";
            $('input[name=\'search_product\']').autocomplete({
                'classes': {
                    "ui-autocomplete": "custom-ui-autocomplete"
                },
                appendTo: divToAppend,
                position: {
                    my: "left bottom",
                    at: "left top",
                },
                'source': function (request, response) {
                    $.ajax({
                        url: fcom.makeUrl('CompareProducts', 'autoComplete'),
                        data: {
                            keyword: request['term'],
                            fIsAjax: 1
                        },
                        dataType: 'json',
                        type: 'post',
                        success: function (json) {
                            response($.map(json, function (item) {
                                return {
                                    label: item['name'],
                                    value: item['name'],
                                    id: item['id']
                                };
                            }));
                        },
                    });
                },
                select: function (event, ui) {
                    addToCompareList(ui.item.id);
                }
            });

        });
    </script>
<?php } ?>