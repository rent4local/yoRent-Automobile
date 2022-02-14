$(document).ready(function(){
	searchShippedProducts(document.frmShippedProductsSearch);

	$('input[name=\'user_name\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
		'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('Users', 'autoCompleteJson'),
				data: {keyword: request['term'], fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'] +'(' + item['username'] + ')', value: item['username'], id: item['id'] };
					}));
				},
			});
		},
		select: function(event, ui) {
			$("input[name='user_id']").val( ui.item.id );
		}
	});
});
(function() {
	var currentPage = 1;
	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page = 1;
		}
		var frm = document.frmShippedProductsPaging;
		$(frm.page).val(page);
		searchShippedProducts(frm);
	}

	reloadList = function() {
        var frm = document.frmShippedProductsPaging;
        searchShippedProducts(frm, currentPage);
    }

	searchShippedProducts = function(form, page){
		if (!page) {
			page = currentPage;
		}
		currentPage = page;
		var dv = $('#shippedProductsListing');
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		dv.html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('ShippedProducts','search'),data,function(res){
			dv.html(res);
		});
	};

	updateProductsShipping = function(productId, shipProfileId) {
		$.facebox(function() {
			updateProductsShippingForm(productId, shipProfileId);
		});
	}

	updateProductsShippingForm = function(productId, shipProfileId) {
		fcom.ajax(fcom.makeUrl('ShippedProducts', 'updateProductsShipping', [productId, shipProfileId]), '', function(t) {
			fcom.updateFaceboxContent(t);
		});
	};

    updateStatus = function (frm){
		if (!$(frm).validate()) { return; }
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('ShippedProducts', 'updateStatus'), data, function(t) {			
			reloadList();			
			$(document).trigger('close.facebox');
	    });
    };

	viewSellerShip = function(productId) {
		$.facebox(function() {
			viewSellerShipForm(productId);
		});
	}

	viewSellerShipForm = function(productId) {
		fcom.ajax(fcom.makeUrl('ShippedProducts', 'viewSellerList', [productId]), '', function(t) {
			fcom.updateFaceboxContent(t);
		});
	};

	viewAdminSellerShip = function(productId) {
		$.facebox(function() {
			viewAdminSellerShipForm(productId);
		});
	}

	viewAdminSellerShipForm = function(productId) {
		fcom.ajax(fcom.makeUrl('ShippedProducts', 'viewSellerList', [productId, 1]), '', function(t) {
			fcom.updateFaceboxContent(t);
		});
	};

	clearShippedProductsSearch = function(){
		document.frmShippedProductsSearch.reset();
		searchShippedProducts(document.frmShippedProductsSearch);
	};

})();