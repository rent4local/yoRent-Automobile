<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="addonModalLabel"><?php echo Labels::getLabel('LBL_Shipping_Locations', $siteLangId); ?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form name="searchShip" method="post" onSubmit="searchShipLocations(this); return false;"> 
                <div class="row">
                    <div class="col-sm-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                    <select name="country_id" class="form-control">
                                        <?php if (!empty($countriesArr)) {
                                            foreach($countriesArr as $countryId => $country) { ?>
                                                <option value="<?php echo $countryId;?>"><?php echo $country;?></option>
                                        <?php }
                                        }
                                        ?>
                                    </select>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label" style="width: 100%;">
                                    <select name="state_id" class="form-control" style="width: 100%;">
                                        <option value=""><?php echo Labels::getLabel('LBL_Select_State', $siteLangId); ?></option>
                                    </select>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <input type="hidden" name="selprodId" value="<?php echo $selprodId; ?>" />
                                <input type="hidden" name="productType" value="<?php echo $productType; ?>" />
                                <input type="submit" name="search" class="btn btn-brand btn-block" value="<?php echo Labels::getLabel('LBL_Search', $siteLangId); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <div class="row"><div class="col-sm-12 mt-4"><div id="search-result--js"></div></div></div>
        </div>
    </div>
</div>
<script>
    $(document).on('change', 'select[name="country_id"]', function() {
        getCountryStates($('select[name="country_id"]').val(), '', $('select[name="state_id"]'));
    });
</script>
