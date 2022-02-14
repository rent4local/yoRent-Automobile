<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$btnFld = $frm->getField('btn_submit');
$btnFld->addFieldTagAttribute('class', 'btn btn-brand');
$minDuTypeFld = $frm->getField('sprodata_duration_type');

$shipFld = $frm->getField('shipping_profile');
$shipFld->htmlAfterField = '<span class="note text-danger">'. Labels::getLabel('LBL_Profile_will_Update_for_all_Invetories_of_Same_catalog_for_sale_and_rent', $siteLangId). '</span>';
?>
<div class="modal-dialog modal-dialog-centered modal-lg" role="document" id="clone-inventory-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?php echo Labels::getLabel('LBL_Clone_Inventory', $siteLangId); ?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="box__body">
                <?php
                $frm->setFormTagAttribute('class', 'form form--horizontal');
                $frm->developerTags['colClassPrefix'] = 'col-sm-12 col-md-12 col-lg-';
                $frm->developerTags['fld_default_col'] = 6;
                $frm->setFormTagAttribute('onsubmit', 'setUpSellerProductClone(this); return(false);');
                echo $frm->getFormHtml();
                ?>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).on('change', 'input[name="sprodata_is_for_sell"]', function(e) {
        if ($(this).is(":checked")) {
            $('.salefld--js').removeClass('hideSaleFlds');
        } else {
            $('.salefld--js').addClass('hideSaleFlds');
        }
    });

    /* [ TAGIFY FOR MEMBERSHIP PLAN */
    $("document").ready(function() {
        getMembershipPlanAutoComplete = function(e) {
            var keyword = e.detail.value;
            tagifyOption.loading(true).dropdown.hide.call(tagifyOption);
            var listOptions = [];
            fcom.ajax(fcom.makeUrl('sellerInventories', 'autoCompleteMembershipPlans'), '', function(t) {
                var ans = $.parseJSON(t);
                for (i = 0; i < ans.length; i++) {
                    listOptions.push({
                        "id": ans[i].id,
                        "value": ans[i].name + '(' + ans[i].plan_identifier + ')',
                    });
                }
                tagifyOption.settings.whitelist = listOptions;
                tagifyOption.loading(false).dropdown.show.call(tagifyOption, keyword);
            });
        };

        tagifyOption = new Tagify(document.querySelector('input[name=membership_plan]'), {
            whitelist: [],
            delimiters: "#",
            editTags: false,
        }).on('input', getMembershipPlanAutoComplete);
    });
    /* ] */
</script>