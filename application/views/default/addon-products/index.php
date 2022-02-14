<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>


<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Rental_Addons', $siteLangId); ?><i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_Rental_addon_can_be_linked_with_a_product_for_rent,_which_will_be_rented_together.', $siteLangId); ?>"></i></h2>
                <small class="note">(<?php echo Labels::getLabel('LBL_Valid_Only_With_Rental_Products', $siteLangId); ?>)</small>
            </div>
            <?php if ($canEdit) { ?>
            <div class="col-auto">
                <div class="btn-group">
                    <a class="btn btn-outline-brand btn-sm" title="<?php echo Labels::getLabel('LBL_Add_Rental_Addons', $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('AddonProducts', 'form'); ?>"><?php echo Labels::getLabel('LBL_Add_Rental_Addons', $siteLangId); ?></a>
                </div>
            </div>
            <?php } ?>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="replaced">
                                <?php
                                $searchForm->setFormTagAttribute('id', 'frmSearchAddonProduct');
                                $searchForm->setFormTagAttribute('class', 'form');
                                $searchForm->setFormTagAttribute('onsubmit', 'searchAddonProducts(this); return(false);');
                                $searchForm->getField('keyword')->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Search_By_Addon_Name', $siteLangId));
                                $searchForm->developerTags['colClassPrefix'] = 'col-md-';
                                $searchForm->developerTags['fld_default_col'] = 12;

                                $keywordFld = $searchForm->getField('keyword');
                                $keywordFld->setWrapperAttribute('class', 'col-lg-6');
                                $keywordFld->developerTags['col'] = 6;
                                $keywordFld->developerTags['noCaptionTag'] = true;

                                $typeFld = $searchForm->getField('addonprod_active');
                                $typeFld->setWrapperAttribute('class', 'col-lg-2');
                                $typeFld->developerTags['col'] = 2;
                                $typeFld->developerTags['noCaptionTag'] = true;

                                $submitFld = $searchForm->getField('btn_submit');
                                $submitFld->setFieldTagAttribute('class', 'btn-block btn btn-brand');
                                $submitFld->setWrapperAttribute('class', 'col-lg-2');
                                $submitFld->developerTags['noCaptionTag'] = true;
                                $submitFld->developerTags['col'] = 2;                                

                                $fldClear = $searchForm->getField('btn_clear');
                                $fldClear->setFieldTagAttribute('onclick', 'clearSearch()');
                                $fldClear->setFieldTagAttribute('class', 'btn-block btn btn-outline-brand');
                                $fldClear->setWrapperAttribute('class', 'col-lg-2');
                                $fldClear->developerTags['noCaptionTag'] = true;
                                $fldClear->developerTags['col'] = 2;
                                
                                echo $searchForm->getFormHtml();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div id="addon-products-listing-js"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script type="text/javascript">
    (function() {
        var runningAjaxMsg = 'some requests already running or this stucked into runningAjaxReq variable value, so try to relaod the page and update the same to WebMaster. ';
        var runningAjaxReq = false;
        var dv = '#addon-products-listing-js';

        checkRunningAjax = function() {
            if (runningAjaxReq == true) {
                console.log(runningAjaxMsg);
                return;
            }
            runningAjaxReq = true;
        };

        searchAddonProducts = function(frm) {
            checkRunningAjax();
            /*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
            var data = fcom.frmData(frm);
            /*]*/
            $(dv).html(fcom.getLoader());

            fcom.ajax(fcom.makeUrl('AddonProducts', 'search'), data, function(res) {
                runningAjaxReq = false;
                $(dv).html(res);
            });
        };

        clearSearch = function() {
            document.frmSearchAddonProduct.reset();
            searchAddonProducts(document.frmSearchAddonProduct);
        };

        goToAddonProductSearchPage = function(page) {
            if (typeof page == undefined || page == null) {
                page = 1;
            }
            var frm = document.frmSearchAddonProduct;
            $(frm.page).val(page);
            searchAddonProducts(frm);
        }

    })();

    function changeStatus(addonProdId, status) {
        var data = 'addonProdId=' + addonProdId + '&status=' + status;
        fcom.updateWithAjax(fcom.makeUrl('AddonProducts', 'changeStatus'), data, function(t) {
            window.location.reload();
        });
    }


    searchAddonProducts();
</script>