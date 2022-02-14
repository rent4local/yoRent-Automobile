$(document).on('change', '.selprodoption_optionvalue_id', function () {
    var frm = document.frmSellerProduct;
    var data = fcom.frmData(frm);
    fcom.ajax(fcom.makeUrl('Seller', 'checkSellProdAvailableForUser'), data, function (t) {
        var ans = $.parseJSON(t);
        if (ans.status == 0) {
            $.mbsmessage(ans.msg, false, 'alert--danger');
            return;
        }
        $.mbsmessage.close();
    });
});

(function () {
    var runningAjaxReq = false;
    var runningAjaxMsg = 'some requests already running or this stucked into runningAjaxReq variable value, so try to relaod the page and update the same to WebMaster. ';
    /* var dv = '#sellerProductsForm'; */
    var dv = '#listing';

    checkRunningAjax = function () {
        if (runningAjaxReq == true) {
            console.log(runningAjaxMsg);
            return;
        }
        runningAjaxReq = true;
    };

    loadSellerProducts = function (frm) {
        sellerProducts($(frm.product_id).val());
    };


    sellerProductForm = function (product_id, selprod_id) {
        $("#tabs_001").html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('sellerInventories', 'sellerProductGeneralForm', [product_id, selprod_id]), '', function (t) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav-js  > li").removeClass('is-active');
            $("#tabs_001").show();
            $("a[rel='tabs_001']").parent().addClass('is-active');
            $("#tabs_001").html(t);
        });
    };

    translateData = function (item, defaultLang, toLangId) {
        var autoTranslate = $("input[name='auto_update_other_langs_data']:checked").length;
        var prodName = $("input[name='selprod_title" + defaultLang + "']").val();
        var prodDesc = $("textarea[name='selprod_comments" + defaultLang + "']").val();
        var alreadyOpen = $('.collapse-js-' + toLangId).hasClass('show');
        if (autoTranslate == 0 || prodName == "" || alreadyOpen == true) {
            return false;
        }
        var data = "product_name=" + prodName + '&product_description=' + prodDesc + "&toLangId=" + toLangId;
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'translatedProductData'), data, function (t) {
            if (t.status == 1) {
                $("input[name='selprod_title" + toLangId + "']").val(t.productName);
                $("textarea[name='selprod_comments" + toLangId + "']").val(t.productDesc);
            }
        });
    }

    setUpSellerProduct = function (frm) {
        if (!$(frm).validate())
            return;
        events.customizeProduct();
        runningAjaxReq = true;
        var selprodId = parseInt($('input[name="selprod_id"]').val());
        
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('sellerInventories', 'setUpSellerProduct'), data, function (t) {
            runningAjaxReq = false;
            $('input[name="is_rent"]').val(1);
            if (1 > selprodId) {
                window.history.pushState({}, '', fcom.makeUrl('sellerInventories', 'sellerProductForm', [t.product_id, t.selprod_id]));
                if (ALLOW_SALE) {
                    productSaleDetails(t.product_id, t.selprod_id);
                    return;
                } else {
                    setTimeout(function () {
                        window.location.href = fcom.makeUrl('sellerInventories', 'sellerProductForm', [t.product_id, t.selprod_id]);
                    }, 1000); 
                }
            }
            /* window.location.replace(fcom.makeUrl('Seller', 'products')); */
            /* setTimeout(function () {
                window.location.href = fcom.makeUrl('sellerInventories', 'products');
            }, 1000); */ 
        });
    };

    optionsAssocArr = function (formData) {
        var data = {};
        $.each(formData, function (key, obj) {
            if ('' != obj.value) {
                var a = obj.name.match(/(.*?)\[(.*?)\]\[(.*?)\]/);
                if (a !== null)
                {
                    var subName = a[1];
                    var subKey = a[2];
                    var options = a[3];

                    if (!data[subName]) {
                        data[subName] = [];
                    }

                    if (!data[subName][subKey]) {
                        data[subName][subKey] = [];
                    }

                    if (data[subName][subKey][options]) {
                        if ($.isArray(data[subName][subKey][options])) {
                            data[subName][subKey][options] = obj.value;
                        } else {
                            data[subName][subKey][options] = obj.value;
                        }
                    } else {
                        data[subName][subKey][options] = obj.value;
                    }
                } else {
                    if (data[obj.name]) {
                        if ($.isArray(data[obj.name])) {
                            data[obj.name].push(obj.value);
                        } else {
                            data[obj.name] = [];
                            data[obj.name].push(obj.value);
                        }
                    } else {
                        data[obj.name] = obj.value;
                    }
                }
            }
        });
        return data;
    };

    setUpMultipleSellerProducts = function (frm, i = 0, orignalData = []) {
        if (!$(frm).validate())
            return;

        if (1 > orignalData.length) {
            orignalData = optionsAssocArr($(frm).serializeArray());
        }

        var data = orignalData;
        var varients = data.varients;
        varients = varients.filter(function () {
            return true;
        });

        if (i < varients.length) {
            var chunk = varients[i];
            var final = {};
            $.extend(final, data, chunk);
            final.varients = [];
            var data = jQuery.param(final);

            $('.optionFld-js').each(function () {
                var $this = $(this);
                var errorInRow = false;
                $this.find('input').each(function () {
                    if ($(this).parent().hasClass('fldSku') && CONF_PRODUCT_SKU_MANDATORY != 1) {
                        return;
                    }
                    if ($(this).val().length == 0 || $(this).val() == 0) {
                        errorInRow = true;
                        return false;
                    }
                });
                if (errorInRow) {
                    $this.parent().addClass('invalid');
                } else {
                    $this.parent().removeClass('invalid');
                }
            });
            if ($("#optionsTable-js > tbody > tr.invalid").length == $("#optionsTable-js > tbody > tr").length) {
                $.systemMessage(LBL_MANDATORY_OPTION_FIELDS, 'alert--danger');
                return false;
            }

            fcom.updateWithAjax(fcom.makeUrl('sellerInventories', 'setUpMultipleSellerProducts'), data, function (t) {
                i++;
                if (i < varients.length) {
                    setUpMultipleSellerProducts(frm, i, orignalData);
                }

                if (i == varients.length && ALLOW_SALE) {
                    $('a[rel="tabs_003"]').addClass('tabs_003');
                    $('.tabs_003').trigger('click');
                }
            });
            var counterString = langLbl.processing_counter.replace("{counter}", (i + 1));
            counterString = counterString.replace("{count}", varients.length);
            counterString = langLbl.processing + " " + counterString;
            $.mbsmessage(counterString, false, 'alert--process alert');
        }
        if (i == (varients.length - 1) && !ALLOW_SALE) {
            setTimeout(function () {
                window.location.href = fcom.makeUrl('sellerInventories', 'products');
            }, 1000);
        }
    };

    updateDiscountString = function () {
        var splprice_display_list_price = 0;
        var splprice_display_dis_val = 0;
        var splprice_display_dis_type = 0;

        splprice_display_list_price = $("input[name='splprice_display_list_price']").val();
        if (splprice_display_list_price == '' || typeof splprice_display_list_price == undefined) {
            splprice_display_list_price = 0;
        }

        splprice_display_dis_val = $("input[name='splprice_display_dis_val']").val();
        if (splprice_display_dis_val == '' || typeof splprice_display_dis_val == undefined) {
            splprice_display_dis_val = 0;
        }

        splprice_display_dis_type = $("select[name='splprice_display_dis_type']").val();
        if (splprice_display_dis_type == 0 || typeof splprice_display_dis_type == undefined || typeof splprice_display_dis_type == '') {
            splprice_display_dis_type = FLAT;
        }
        var data = 'splprice_display_list_price=' + splprice_display_list_price + '&splprice_display_dis_val=' + splprice_display_dis_val + '&splprice_display_dis_type=' + splprice_display_dis_type;
        $("#special-price-discounted-string").html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'getSpecialPriceDiscountString'), data, function (res) {
            $("#special-price-discounted-string").html(res);
        });
    }

    gotToProucts = function () {
        window.location.href = fcom.makeUrl('sellerInventories', 'Products');
    }

    getUniqueSlugUrl = function (obj, str, recordId) {
        if (str == '') {
            return;
        }
        var data = {url_keyword: str, recordId: recordId}
        fcom.ajax(fcom.makeUrl('Seller', 'isProductRewriteUrlUnique'), data, function (t) {
            var ans = $.parseJSON(t);
            $(obj).next().html(ans.msg);
            if(ans.status == 0){
                $(obj).next().addClass('text-danger').removeClass('text-muted');                  
            }else{
                $(obj).next().removeClass('text-danger').addClass('text-muted');
            }
        });
    };

    /* [ Sale Details Functionality */
    productSaleDetails = function (productId, selprod_id) {
        $("#tabs_003").html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('sellerInventories', 'productSaleDetailsForm', [productId, selprod_id]), '', function (t) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav-js  > li").removeClass('is-active');
            $("#tabs_003").show();
            $("a[rel='tabs_003']").parent().addClass('is-active');
            $("#tabs_003").html(t);
        });
    };

    setupProductSaleDetails = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('sellerInventories', 'setupProdSaleDetails'), data, function (t) {
            $.mbsmessage.close();
            if (1 > selprod_id) {
                window.location.href = fcom.makeUrl('sellerInventories', 'Products');
            }
        });
    };

    /* [ PRODUCT MEMBERSHIP FORM UPDATES */
    productMembershipForm = function (productId, selprod_id) {
        $("#tabs_002").html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('sellerInventories', 'productMembershipDetailsForm', [productId, selprod_id]), '', function (t) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav-js  > li").removeClass('is-active');
            $("#tabs_002").show();
            $("a[rel='tabs_002']").parent().addClass('is-active');
            $("#tabs_002").html(t);
        });
    };

    setupProductMembershipDetails = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('sellerInventories', 'setupProductMembershipDetails'), data, function (t) {
            $.mbsmessage.close();
            window.location.href = fcom.makeUrl('sellerInventories', 'Products');
        });
    };

    setUpMultipleSelProdsMemberships = function (frm, i = 0, orignalData = []) {
        if (!$(frm).validate())
            return;

        if (1 > orignalData.length) {
            orignalData = optionsAssocArr($(frm).serializeArray());
        }

        var data = orignalData;
        var varients = data.varients;
        varients = varients.filter(function () {
            return true;
        });

        if (i < varients.length) {
            var chunk = varients[i];
            var final = {};
            $.extend(final, data, chunk);
            final.varients = [];
            var data = jQuery.param(final);

            fcom.updateWithAjax(fcom.makeUrl('sellerInventories', 'setupProductsMembershipDetails'), data, function (t) {
                i++;
                if (i < varients.length) {
                    setUpMultipleSelProdsMemberships(frm, i, orignalData);
                }

                if (i == varients.length) {
                    $('a[rel="tabs_003"]').addClass('tabs_003');
                    $('.tabs_003').trigger('click');
                }
            });
            var counterString = langLbl.processing_counter.replace("{counter}", (i + 1));
            counterString = counterString.replace("{count}", varients.length);
            counterString = langLbl.processing + " " + counterString;
            $.mbsmessage(counterString, false, 'alert--process alert');
    }

    };


    /* ] */

    translateRentalData = function (item, defaultLang, toLangId) {
        var autoTranslate = $("input[name='auto_update_other_langs_data']:checked").length;
        var sprodata_rental_terms = $("textarea[name='sprodata_rental_terms[" + defaultLang + "]']").val();
        var alreadyOpen = $('.collapse-js-' + toLangId).hasClass('show');
        if (autoTranslate == 0 || sprodata_rental_terms == "" || alreadyOpen == true) {
            return false;
        }
        var data = "sprodata_rental_terms=" + sprodata_rental_terms + "&toLangId=" + toLangId;
        fcom.updateWithAjax(fcom.makeUrl('sellerInventories', 'translatedProductRenatlData'), data, function (t) {
            if (t.status == 1) {
                $("textarea[name='sprodata_rental_terms[" + toLangId + "]']").val(t.sprodata_rental_terms);
            }
        });
    }

    sellerProductDurationDiscounts = function (selprod_id) {
        $("#tabs_004").html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('sellerInventories', 'sellerProductDurationDiscounts', [selprod_id]), '', function (t) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav-js  > li").removeClass('is-active');
            $("#tabs_004").show();
            $("a[rel='tabs_004']").parent().addClass('is-active');
            $("#tabs_004").html(t);
        });
    };
    /* ] */

})();

$(document).on('click', '.tabs_001', function () {
    if (selprod_id > 0) {
        sellerProductForm(product_id, selprod_id);
    }
});

$(document).on('click', '.tabs_002', function () {
    productMembershipForm(product_id, selprod_id);
});

$(document).on('click', '.tabs_003', function () {
    productSaleDetails(product_id, selprod_id);
});

$(document).on('click', '.tabs_004', function () {
    var is_rent = $('input[name="is_rent"]').val();
    if (selprod_id > 0 && is_rent > 0) {
        sellerProductDurationDiscounts(selprod_id);
    }
});

$(document).on('click', '.tabs_005', function () {
    var is_rent = $('input[name="is_rent"]').val();
    if (selprod_id > 0 && is_rent > 0) {
        productRentalUnavailableDates(selprod_id);
    }
});

$(document).on('click', '.tabs_006', function () {
    var is_rent = $('input[name="is_rent"]').val();
    if (selprod_id > 0 && is_rent > 0) {
        addonProducts(selprod_id);
    }
});

$(document).on('click', 'input[name="sprodata_is_for_rent"]', function () {
    /* alert('working'); */
    if ($(this).prop('checked') == true) {
        var selprod_id = parseInt($("input[name='selprod_id']").val());
        $('.tabsForRentJs').removeClass('productRentTabs');
        $('.tabsForRentJs').addClass('fat-inactive');
        /* $('input[name="is_rent"]').val(1); */
        if (selprod_id <= 1) {
            $('.tabsForRentJs a').attr('onclick', '');
        }
    } else {
        $('input[name="is_rent"]').val(0);
        $('.tabsForRentJs').addClass('productRentTabs');
    }
});