(function () {
    addSellerProductForm = function (productId, selprodId) {
        var data = '';
        fcom.ajax(fcom.makeUrl('SellerProducts', 'sellerProductForm', [productId, selprodId]), data, function (res) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav  > li > a").removeClass('active');
            $("#tabs_001").show();
            $("a[rel='tabs_001']").addClass('active');
            $("#tabs_001").html(res);
        });
    };

    setUpSellerProduct = function (frm) {
        if (!$(frm).validate())
            return;

        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setUpSellerProduct'), data, function (t) {
        
        });
    };
    
    translateData = function (item, defaultLang, toLangId) {
        var autoTranslate = $("input[name='auto_update_other_langs_data']:checked").length;
        var prodName = $("input[name='selprod_title" + defaultLang + "']").val();
        var prodDesc = $("textarea[name='selprod_comments" + defaultLang + "']").val();
        var rentalTerm = $("textarea[name='selprod_rental_terms" + defaultLang + "']").val();
        var alreadyOpen = $('.collapse-js-' + toLangId).hasClass('show');
        if (autoTranslate == 0 || prodName == "" || alreadyOpen == true) {
            return false;
        }
        /* var data = "product_name=" + prodName + '&product_description=' + prodDesc + "&toLangId=" + toLangId + "&rentalTerm="+ rentalTerm; */
        
        var data = {
            'product_name' : prodName,
            'product_description' : prodDesc,
            'toLangId' : toLangId,
            'rentalTerm' : rentalTerm
        };
        
        fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'translatedProductData'), data, function (t) {
            if (t.status == 1) {
                $("input[name='selprod_title" + toLangId + "']").val(t.productName);
                $("textarea[name='selprod_comments" + toLangId + "']").val(t.productDesc);
                $("textarea[name='selprod_rental_terms" + toLangId + "']").val(t.rentalTerm);
            }
        });
    }
    

   addSellerProductSaleForm = function (productId, selprod_id) {
        var data = '';
        fcom.ajax(fcom.makeUrl('SellerProducts', 'productSaleDetailsForm', [productId, selprod_id]), data, function (res) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav  > li > a").removeClass('active');
            $("#tabs_002").show();
            $("a[rel='tabs_002']").addClass('active');
            $("#tabs_002").html(res);
        });
    };
    
    setupProductSaleDetails = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setupProdSaleDetails'), data, function (t) {
            
        });
    }

    translateRentalData = function (item, defaultLang, toLangId) {
        var autoTranslate = $("input[name='auto_update_other_langs_data']:checked").length;
        var sprodata_rental_terms = $("textarea[name='sprodata_rental_terms[" + defaultLang + "]']").val();
        var alreadyOpen = $('.collapse-js-' + toLangId).hasClass('show');
        if (autoTranslate == 0 || sprodata_rental_terms == "" || alreadyOpen == true) {
            return false;
        }
        var data = "sprodata_rental_terms=" + sprodata_rental_terms + "&toLangId=" + toLangId;
        fcom.updateWithAjax(fcom.makeUrl('SellerRentalProducts', 'translatedProductRenatlData'), data, function (t) {
            if (t.status == 1) {
                $("textarea[name='sprodata_rental_terms[" + toLangId + "]']").val(t.sprodata_rental_terms);
            }
        });
    };

    /* [ Seller Products Rental Options ] */

    getUniqueSlugUrl = function(obj,str,recordId){
        if(str == ''){
            return;
        }
        var data = {url_keyword:str,recordId:recordId}
        fcom.ajax(fcom.makeUrl('SellerProducts', 'isProductRewriteUrlUnique'), data, function(t) { 
            var ans = $.parseJSON(t);
            $(obj).next().html(ans.msg);
            if(ans.status == 0){
                $(obj).next().addClass('text-danger');
            }else{
                $(obj).next().removeClass('text-danger');
            }
        });
    };
})();
$(document).on('click', '.tabs_002', function () {
    addSellerProductSaleForm(productId, selprodId);
});

$(document).on('click', '.tabs_001', function () {
    addSellerProductForm(productId, selprodId);
});