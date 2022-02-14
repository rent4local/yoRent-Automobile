$(document).ready(function(){
	loadSellerProducts(document.frmSearchSellerProducts);
});

$(document).on('change','.selprodoption_optionvalue_id',function(){
	var frm = document.frmSellerProduct;
	var selprodId = $( frm.selprod_id ).val();
	$( frm.selprod_id ).val('');
	var data = fcom.frmData(frm);
	fcom.ajax(fcom.makeUrl('Seller', 'checkSellProdAvailableForUser'), data, function(t) {
		var ans = $.parseJSON(t);
		$( frm.selprod_id ).val(selprodId);
		if( ans.status == 0 ){
			$.mbsmessage( ans.msg,false,'alert--danger');
			return;
		}
		$.mbsmessage.close();
	});
});

$(document).on("click", ".selectAll-js", function () {
	if ($(this).prop("checked") == false) {
		$(".formActionBtnJs").addClass('disabled');
	} else {
		$(".formActionBtnJs").removeClass('disabled');
	}
});

$(document).on("click", ".selectItem--js", function () {
	if ($(this).prop("checked") == false) {
		$(".formActionBtnJs").addClass('disabled');
	} else {
		$(".formActionBtnJs").removeClass('disabled');
	}
});

(function() {
	var runningAjaxReq = false;
	/* var dv = '#sellerProductsForm'; */
	var dv = '#listing';

	checkRunningAjax = function(){
		if( runningAjaxReq == true ){
			console.log(runningAjaxMsg);
			return;
		}
		runningAjaxReq = true;
	};

	loadSellerProducts = function(frm) {
		sellerProducts($(frm.product_id).val(), 0);
	};

	sellerProducts = function(product_id, page) {
		if (typeof page!==undefined && page == 1) {
			var frm = document.frmSearch;
			$(frm.page).val(page);
		}

		$('#listing').html(fcom.getLoader());
		if( typeof product_id == undefined || product_id == null || product_id == ''){
			product_id = 0;
		}
		
		var data = fcom.frmData(document.frmSearch);
		fcom.ajax(fcom.makeUrl('sellerInventories', 'sellerProducts', [ product_id, SELPROD_TYPE]), data, function(t) {
			/* showFormActionsBtns(); */
			$('#listing').html(t);
		});
	}

	goToSellerProductSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSearch;
		$(frm.page).val(page);
		loadSellerProducts(frm);
	}

	productInstructions = function( type ){
		/* $.facebox(function() { */
			fcom.ajax(fcom.makeUrl('Seller', 'productTooltipInstruction', [type]), '', function(t) {
				/* $.facebox(t,'medium-fb-width catalog-bg'); */
				$('#exampleModal').html(t);
                $('#exampleModal').modal('show');
			});
		/* }); */
	};

	sellerProductForm = function(product_id, selprod_id){
		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('sellerInventories', 'sellerProductForm', [ product_id, selprod_id ]), '', function(t) {
			$(dv).html(t);
		});
	};

	sellerProductDelete=function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.updateWithAjax(fcom.makeUrl('sellerInventories','sellerProductDelete'),data,function(res){
			loadSellerProducts(document.frmSearchSellerProducts);
		});
	};
    
    sellerProductDeleteSale = function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.updateWithAjax(fcom.makeUrl('sellerInventories','sellerProductDeleteSale'), data, function(res) {
			loadSellerProducts(document.frmSearchSellerProducts);
		});
	};
    

	deleteBulkSellerProducts = function(){
		if( !confirm(langLbl.confirmDelete) ){ return; }
		$("#frmSellerProductsListing").attr("action",fcom.makeUrl('sellerInventories','deleteBulkSellerProducts')).submit();
		$(".formActionBtnJs").addClass('disabled');
	};

	sellerProductCloneForm = function(product_id, selprod_id){
		/* $.facebox(function() { */
			fcom.ajax(fcom.makeUrl('sellerInventories', 'sellerProductCloneForm', [ product_id, selprod_id ]), '', function(t) {
				if(isJson(t)) {
					var ans = $.parseJSON(t);
					$.mbsmessage(ans.msg, true,'alert--danger');
				}else{
					$('#exampleModal').html(t);
					$('#exampleModal').modal('show');
				}
			});
		/* }); */
	};

	setUpSellerProductClone = function(frm){
		if (!$(frm).validate()) return;
		runningAjaxReq = true;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('sellerInventories', 'setUpSellerProductClone'), data, function(t) {
			runningAjaxReq = false;
			/* $("#facebox .close").trigger('click'); */
			$("#exampleModal .close").click();
			loadSellerProducts(document.frmSearchSellerProducts);
            setTimeout(function () {
                window.location.href = fcom.makeUrl('SellerInventories', 'sellerProductForm', [t.product_id, t.selprod_id]);
            }, 1000);
            
			/* if(t.selprod_id > 0){
				$(frm.splprice_selprod_id).val(t.selprod_id);
			}	 */
		});
	};

	reloadList = function() {
		var frm = document.frmSearch;
		loadSellerProducts(frm);
	};
	
	toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmSellerProductsListing input[name='status']").val(status);
        $("#frmSellerProductsListing").submit();

		$(".formActionBtnJs").addClass('disabled');
    };
	
	toggleSellerProductStatus = function(e, obj, prodType){
		if(!confirm(langLbl.confirmUpdateStatus)){
			e.preventDefault();
			return;
		}
		var selprodId = parseInt(obj.value);
		if( selprodId < 1 ){
			return false;
		}
        var status = 0;
        if ($(obj).prop('checked') == true) {
            status = 1;
        }
        data ='selprodId='+selprodId + '&productType='+ SELPROD_TYPE + '&status='+status;
		fcom.updateWithAjax(fcom.makeUrl('sellerInventories','changeProductStatus'), data, function(res) {
			loadSellerProducts(document.frmSearchSellerProducts);
		});
	};
	clearSearch = function(){
		document.frmSearch.reset();
		var frm = document.frmSearch;
		$(frm.page).val(1);
		$(frm.product_id).val(0);
		$(frm.keyword).val('');
		loadSellerProducts(document.frmSearch);
	};
	addSpecialPrice = function(){
		if (typeof $(".selectItem--js:checked").val() === 'undefined') {
	        $.mbsmessage(langLbl.atleastOneRecord, true,'alert--danger');
	        return false;
	    }
		$("#frmSellerProductsListing").attr({'action': fcom.makeUrl('Seller','specialPrice'), 'target':"_blank"}).removeAttr('onsubmit').submit();
		loadSellerProducts(document.frmSearch);
	}

	addVolumeDiscount = function(){
		if (typeof $(".selectItem--js:checked").val() === 'undefined') {
	        $.systemMessage(langLbl.atleastOneRecord, 'alert--danger');
	        return false;
	    }
		$("#frmSellerProductsListing").attr({'action': fcom.makeUrl('Seller','volumeDiscount'), 'target':"_blank"}).removeAttr('onsubmit').submit();
		loadSellerProducts(document.frmSearchSellerProducts);
	};
})();
