<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main">
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Link_Pickup_Addresses', $siteLangId); ?></h2>
            </div>
        </div>
        <div class="content-body">
            <?php if ($canEdit) { ?>
                <div class="row mb-4">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <?php $linkAddFrm->setFormTagAttribute('onsubmit', 'linkPickupAddress(this); return(false);');
                                $linkAddFrm->setFormTagAttribute('class', 'form form--horizontal');
                                $prodFld = $linkAddFrm->getField('pickup_address');
                                $prodFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Select_Pickup_Addresses', $siteLangId));

                                $attchFld = $linkAddFrm->getField('product_names');
                                $attchFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Attach_Products', $siteLangId));

                                $submitBtnFld = $linkAddFrm->getField('btn_submit');
                                $submitBtnFld->setFieldTagAttribute('class', 'btn btn-brand btn-block '); ?>
                                <?php echo $linkAddFrm->getFormTag(); ?>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="field-set">
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <?php echo $linkAddFrm->getFieldHTML('pickup_address'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="field-set">
                                            <div class="field-wraper">
                                                <div class="field_cover custom-tagify">
                                                    <?php echo $linkAddFrm->getFieldHTML('product_names'); ?>
                                                    <div class="list-tag-wrapper scroll scroll-y">
                                                        <ul class="list-tags" id="productName"></ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="field-set">
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <?php echo $linkAddFrm->getFieldHTML('btn_submit'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php echo $linkAddFrm->getFieldHTML('addr_id'); ?>
                                </form>
                                <?php echo $linkAddFrm->getExternalJS(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div id="listing">
                                <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    var selected_products = [];
    $(document).ready(function() {
        searchLinkedAddresses();
        $('#productName').delegate('.remove_product', 'click', function() {
            $(this).parents('li').remove();
            var remove_Item = $(this).siblings("input").val();

            selected_products = $.grep(selected_products, function(value) {
                return value != remove_Item;
            });
        });

        $("select[name='pickup_address']").on('change', function() {

            fcom.ajax(fcom.makeUrl('LinkPickupAddress', 'getLinkSelprodList', [this.value]), '', function(t) {
                var ans = $.parseJSON(t);
                $('#productName').empty();
                for (var key in ans.linkedProducts) {
                    $('#productName').append(
                        "<li id=productName" + ans.linkedProducts[key]['id'] + "><span>" + ans.linkedProducts[key]['name'] + "<i class=\"remove_product remove_param fas fa-times\"></i><input type=\"hidden\" name=\"product_names[]\" value=" + ans.linkedProducts[key]['id'] + " /></span></li>"
                    );
                }
            })

        });

        $("select[name='product_names']").select2({
            closeOnSelect: true,
            dir: langLbl.layoutDirection,
            allowClear: true,
            placeholder: $("select[name='product_names']").attr('placeholder'),
            ajax: {
                url: fcom.makeUrl('Seller', 'autoCompleteProducts', [0, 0, 1]),
                dataType: 'json',
                delay: 250,
                method: 'post',
                data: function(params) {
                    var parentForm = $("select[name='product_names']").closest('form').attr('id');
                    return {
                        keyword: params.term, // search term
                        page: params.page,
                        fIsAjax: 1,
                        selProdId: $("#" + parentForm + " input[name='product_id']").val(),
                        selected_products: selected_products
                    };
                },
                beforeSend: function(xhr, opts) {
                    var parentForm = $("select[name='product_names']").closest('form').attr('id');
                    var selprod_id = $("#" + parentForm + " input[name='products_id']").val();
                    if (1 > selprod_id) {
                        xhr.abort();
                    }
                    $('input[name="product_names[]"]').each(function() {
                        selected_products.push($(this).val());
                    });

                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.products,
                        pagination: {
                            more: params.page < data.pageCount
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 0,
            templateResult: function(result) {
                return result.name;
            },
            templateSelection: function(result) {
                return result.name;
            }
        }).on('select2:selecting', function(e) {
            var parentForm = $(this).closest('form').attr('id');
            var item = e.params.args.data;
            $('input[name=\'product_names\']').val('');
            $('#productName' + item.id).remove();
            $('#productName').append('<li id="productName' + item.id + '"><span> ' + item.name + '<i class="remove_product remove_param fas fa-times"></i><input type="hidden" name="product_names[]" value="' +
                item.id + '" /></span></li>');
            setTimeout(function() {
                $("select[name='product_names']").val('').trigger('change');
            }, 200);

        });
    });
    $(document).on('mouseover', "ul.list-tags li span i", function() {
        $(this).parents('li').addClass("hover");
    });
    $(document).on('mouseout', "ul.list-tags li span i", function() {
        $(this).parents('li').removeClass("hover");
    });

    (function() {
        var dv = '#listing';
        searchLinkedAddresses = function(frm) {

            /*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
            var data = '';
            if (frm) {
                data = fcom.frmData(frm);
            }
            /*]*/
            var dv = $('#listing');
            $(dv).html(fcom.getLoader());

            fcom.ajax(fcom.makeUrl('LinkPickupAddress', 'searchLinkedAddresses'), data, function(res) {
                $("#listing").html(res);
            });
        };
        clearSearch = function(product_id) {
            if (0 < product_id) {
                location.href = fcom.makeUrl('LinkPickupAddress', 'index');
            } else {
                document.frmSearch.reset();
                searchLinkedAddresses(document.frmSearch);
            }
        };

        goToSearchPage = function(page) {
            if (typeof page == undefined || page == null) {
                page = 1;
            }
            var frm = document.frmLinkedAddressPaging;
            $(frm.page).val(page);
            searchLinkedAddresses(frm);
        }

        reloadList = function() {
            var frm = document.frmLinkPickupAddFrm;
            searchLinkedAddresses(frm);
        }

        deleteLinkedProduct = function(addrId, selprodId) {
            var agree = confirm(langLbl.confirmDelete);
            if (!agree) {
                return false;
            }
            fcom.updateWithAjax(fcom.makeUrl('LinkPickupAddress', 'deleteLinkedProduct', [addrId, selprodId]), '', function(t) {
                searchLinkedAddresses(document.frmLinkPickupAddFrm);
            });
        }

        showElement = function(currObj, value) {
            var sibling = currObj.siblings('div');
            if ('' != value) {
                sibling.text(value);
            }
            sibling.fadeIn();
            currObj.addClass('hidden');
        };

        linkPickupAddress = function(frm) {
            // if (!$(frm).validate())
            //     return;
            var data = fcom.frmData(frm);
            fcom.updateWithAjax(fcom.makeUrl('LinkPickupAddress', 'linkPickupAddresses'), data, function(t) {
                document.frmLinkPickupAddFrm.reset();
                // $("input[name='product_id']").val('');
                $('#productName').empty();
                $("option:selected").removeAttr("selected");
                // $(frm).find("select[name='product_names']").trigger('change.select2');
                searchLinkedAddresses(document.frmLinkPickupAddFrm);
            });
        };
    })();

    $(document).on('click', ".js-addr-edit", function() {
        var addrId = $(this).attr('row-id');
        var addrHtml = $(this).children('.js-addr-name').html();
        var addrName = addrHtml.split('<br>');
        fcom.ajax(fcom.makeUrl('LinkPickupAddress', 'getLinkSelprodList', [addrId]), '', function(t) {
            $("option:selected").removeAttr("selected");
            $('#pickupAddress option[value=' + addrId + ']').attr('selected', 'selected');
            var ans = $.parseJSON(t);
            $('#productName').empty();
            for (var key in ans.linkedProducts) {
                $('#productName').append(
                    "<li id=productName" + ans.linkedProducts[key]['id'] + "><span>" + ans.linkedProducts[key]['name'] + "<i class=\"remove_product remove_param fas fa-times\"></i><input type=\"hidden\" name=\"product_names[]\" value=" + ans.linkedProducts[key]['id'] + " /></span></li>"
                );
            }
        });
    });
</script>